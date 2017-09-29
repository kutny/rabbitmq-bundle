<?php

namespace Kutny\RabbitMqBundle\Consumer;

use Doctrine\ORM\EntityManager;
use Kutny\RabbitMqBundle\Consumer\Message\MessageParametersGetter;
use Kutny\RabbitMqBundle\Queue\IQueueConfig;
use Kutny\RabbitMqBundle\Queue\IRetryingQueueConfig;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class ConsumerRunner
{
    private $queueConfig;
    private $paramConverter;
    private $consumer;
    private $entityManager;
    private $channel;
    private $messageParametersGetter;
    private $parametersResolver;

    private $consumerTag;
    private $idleTimeout;
    private $forceStop;
    private $memoryLimit;

    public function __construct(
        IQueueConfig $queueConfig,
        IParamConverter $paramConverter,
        $consumer,
        EntityManager $entityManager,
        AMQPChannel $channel,
        MessageParametersGetter $messageParametersGetter,
        ParametersResolver $parametersResolver
    ) {
        $this->queueConfig = $queueConfig;
        $this->paramConverter = $paramConverter;
        $this->consumer = $consumer;
        $this->entityManager = $entityManager;
        $this->channel = $channel;
        $this->messageParametersGetter = $messageParametersGetter;
        $this->parametersResolver = $parametersResolver;

        $this->consumerTag = sprintf('PHPPROCESS_%s_%s', gethostname(), getmypid());
        $this->idleTimeout = 0;
    }

    public function consume()
    {
        $this->channel->basic_qos($this->queueConfig->getPrefetchSize(), $this->queueConfig->getPrefetchCount(), $this->queueConfig->getQosOptionsGlobal());

        $this->channel->basic_consume($this->queueConfig->getQueueName(), $this->consumerTag, false, false, false, false, [$this, 'consumeMessage']);

        while (count($this->channel->callbacks)) {
            $this->maybeStopConsumer();
            $this->channel->wait(null, false, $this->idleTimeout);
        }
    }

    public function consumeMessage(AMQPMessage $message)
    {
        if (!$this->maxTriesReached($message)) {
            $this->consumerConsume($message);
        }
        else {
            $this->consumerMaxTriesReached($message);
        }

        $this->maybeStopConsumer();

        if ($this->getMemoryLimit() !== null && $this->isRamAlmostOverloaded()) {
            $this->stopConsuming();
        }

        $this->entityManager->clear();
        gc_collect_cycles();
    }

    public function stopConsuming()
    {
        $this->channel->basic_cancel($this->consumerTag);
    }

    public function forceStopConsumer()
    {
        $this->forceStop = true;
    }

    // for compatibility with OldSound\RabbitMqBundle\Command\ConsumerCommand
    public function setRoutingKey($routingKey)
    {
        if ($routingKey) {
            throw new \Exception('Setting routing key is not supported right now');
        }
    }

    public function setMemoryLimit($memoryLimit)
    {
        $this->memoryLimit = $memoryLimit;
    }

    public function getMemoryLimit()
    {
        return $this->memoryLimit;
    }

    public function __destruct()
    {
        $this->channel->close();

        $connection = $this->channel->getConnection();

        if ($connection && $connection->isConnected()) {
            $connection->close();
        }
    }

    private function consumerConsume(AMQPMessage $amqpMessage)
    {
        try {
            $parameters = $this->parametersResolver->getParameters($this->paramConverter, $this->consumer, 'consume', $amqpMessage);

            call_user_func_array([$this->consumer, 'consume'], $parameters);

            $amqpMessage->delivery_info['channel']->basic_ack($amqpMessage->delivery_info['delivery_tag']);
        }
        catch (UnableToProcessMessageException $e) {
            $amqpMessage->delivery_info['channel']->basic_reject($amqpMessage->delivery_info['delivery_tag'], $e->getRequeue());
        }
        catch (\Exception $e) {
            if ($this->queueConfig instanceof IRetryingQueueConfig && $this->queueConfig->rejectOnException()) {
                $amqpMessage->delivery_info['channel']->basic_reject($amqpMessage->delivery_info['delivery_tag'], false);
            }

            throw $e;
        }
    }

    private function consumerMaxTriesReached(AMQPMessage $amqpMessage)
    {
        try {
            $parameters = $this->parametersResolver->getParameters($this->paramConverter, $this->consumer, 'maxTriesReached', $amqpMessage);

            call_user_func_array([$this->consumer, 'maxTriesReached'], $parameters);

            $amqpMessage->delivery_info['channel']->basic_ack($amqpMessage->delivery_info['delivery_tag']);
        }
        catch (UnableToProcessMessageException $e) {
            $amqpMessage->delivery_info['channel']->basic_ack($amqpMessage->delivery_info['delivery_tag']);
        }
        catch (\Exception $e) {
            $amqpMessage->delivery_info['channel']->basic_reject($amqpMessage->delivery_info['delivery_tag'], false);

            throw $e;
        }
    }

    private function maxTriesReached(AMQPMessage $message)
    {
        return
            $this->queueConfig instanceof IRetryingQueueConfig
            && $this->queueConfig->getMaxTries() !== null
            && $this->queueConfig->getMaxTries() <= $this->messageParametersGetter->getTry($message);
    }

    private function maybeStopConsumer()
    {
        if (extension_loaded('pcntl') && (defined('AMQP_WITHOUT_SIGNALS') ? !AMQP_WITHOUT_SIGNALS : true)) {
            if (!function_exists('pcntl_signal_dispatch')) {
                throw new \BadFunctionCallException("Function 'pcntl_signal_dispatch' is referenced in the php.ini 'disable_functions' and can't be called.");
            }

            pcntl_signal_dispatch();
        }

        if ($this->forceStop) {
            $this->stopConsuming();
        }
    }

    /**
     * Checks if memory in use is greater or equal than memory allowed for this process
     *
     * @return boolean
     */
    private function isRamAlmostOverloaded()
    {
        if (memory_get_usage(true) >= ($this->getMemoryLimit() * 1024 * 1024)) {
            return true;
        }
        else {
            return false;
        }
    }
}
