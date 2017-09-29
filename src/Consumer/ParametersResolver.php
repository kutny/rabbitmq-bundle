<?php

namespace Kutny\RabbitMqBundle\Consumer;

use Kutny\RabbitMqBundle\Json\JsonDecoder;
use Kutny\RabbitMqBundle\Consumer\Message\MessageParametersGetter;
use PhpAmqpLib\Message\AMQPMessage;

class ParametersResolver
{
    private $jsonDecoder;
    private $messageParametersGetter;

    public function __construct(
        JsonDecoder $jsonDecoder,
        MessageParametersGetter $messageParametersGetter
    ) {
        $this->jsonDecoder = $jsonDecoder;
        $this->messageParametersGetter = $messageParametersGetter;
    }

    public function getParameters(IParamConverter $paramConverter, $consumer, $methodName, AMQPMessage $message)
    {
        $consumerClass = get_class($consumer);
        $consumeMethod = $this->getConsumeMethod($consumerClass, $methodName);
        $definitionsOfParameters = $this->getDefinitionsOfParameters($consumeMethod);

        $messageBodyJson = $this->jsonDecoder->decode($message->body);
        $convertedParameters = $paramConverter->convert($messageBodyJson, $this->messageParametersGetter->getTry($message), $this->messageParametersGetter->getPriority($message));

        try {
            return $this->processParameters($definitionsOfParameters, $convertedParameters, $message);
        }
        catch (UnableToResolveParameterException $e) {
            throw new \Exception('Unable to resolve parameter $' . $e->getParameterName() . ' of the ' . $consumerClass . '::' . $methodName . '() method');
        }
    }

    private function getConsumeMethod($consumerClass, $methodName)
    {
        $reflectionClass = new \ReflectionClass($consumerClass);
        $consumeMethod = $reflectionClass->getMethod($methodName);

        if (!$consumeMethod->isPublic()) {
            throw new \Exception($methodName . '() method must be public');
        }

        return $consumeMethod;
    }

    private function getDefinitionsOfParameters(\ReflectionMethod $consumeMethod)
    {
        $reflectionParameters = $consumeMethod->getParameters();
        $parameters = [];

        foreach ($reflectionParameters as $reflectionParameter) {
            $parameters[$reflectionParameter->getName()] = $reflectionParameter->getClass() ? $reflectionParameter->getClass()->getName() : null;
        }

        return $parameters;
    }

    private function processParameters(array $definitionsOfParameters, array $convertedParameters, AMQPMessage $message)
    {
        $parameters = [];

        foreach ($definitionsOfParameters as $parameterName => $parameterClass) {
            $parameters[] = $this->resolveParameter($parameterName, $parameterClass, $convertedParameters, $message);
        }

        return $parameters;
    }

    private function resolveParameter($parameterName, $parameterClass, array $convertedParameters, AMQPMessage $message)
    {
        if ($parameterClass === AMQPMessage::class) {
            return $message;
        }
        else if ($parameterName === 'try') {
            return $this->messageParametersGetter->getTry($message);
        }
        else if ($parameterName === 'priority') {
            return $this->messageParametersGetter->getPriority($message);
        }
        else if (array_key_exists($parameterName, $convertedParameters)) {
            return $convertedParameters[$parameterName];
        }
        else {
            throw new UnableToResolveParameterException($parameterName);
        }
    }
}
