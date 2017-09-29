<?php

namespace Kutny\RabbitMqBundle\Worker;

use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

declare(ticks = 1);

class WorkerRunner extends Command
{
    private $container;
    private $entityManager;
    private $run;

    public function __construct(
        ContainerInterface $container,
        EntityManager $entityManager
    ) {
        $this->container = $container;
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('worker:start');
        $this->addArgument('workerService', InputArgument::REQUIRED);
        $this->setDescription('Starts a worker');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $worker = $this->container->get($input->getArgument('workerService'));

        $output->writeln('Starting (PID: ' . getmypid() . ')...');
        $this->registerSignalHandlers();
        $this->run = true;

        while ($this->run) {
            $worker->run();
            $this->collectCycles();
        }

        $output->writeln('Done. Exiting now.');
    }

    public function registerSignalHandlers()
    {
        if (PHP_OS === 'Linux') {
            pcntl_signal(SIGTERM, array($this, 'signalHandler'));
            pcntl_signal(SIGINT, array($this, 'signalHandler'));
        }
    }

    public function signalHandler($signal, OutputInterface $output)
    {
        $output->writeln('Daemon was asked to die nicely. (signal ' . $signal . ')');
        $this->run = false;
    }

    protected function collectCycles()
    {
        $this->entityManager->clear();
        gc_collect_cycles();
    }
}
