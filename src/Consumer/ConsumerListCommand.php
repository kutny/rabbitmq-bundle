<?php

namespace Kutny\RabbitMqBundle\Consumer;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConsumerListCommand extends Command
{
    private $consumers;

    public function __construct(array $consumers = [])
    {
        $this->consumers = $consumers;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('rabbitmq:consumer:list');
        $this->setDescription('Show list of defined RabbitMQ consumers');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->consumers as $consumer) {
            echo $consumer . PHP_EOL;
        }
    }
}
