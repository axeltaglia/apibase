<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Psr\Log\LoggerInterface;

class ReloadDatabaseCommand extends Command
{
    private $logger;

    public function __construct(
        LoggerInterface $logger
    )
    {
        $this->logger = $logger;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('database:reload')
            ->setDescription('Reload all the database')
            ->setHelp('Reload all the database');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        // doctrine:database:drop
        $command = $this->getApplication()->find('doctrine:database:drop');
        
        $arguments = [
            'command' => 'doctrine:database:drop',
            '--force' => true,
        ];

        $input = new ArrayInput($arguments);
        $returnCode = $command->run($input, $output);

        // doctrine:database:create
        $command = $this->getApplication()->find('doctrine:database:create');

        $arguments = [
            'command' => 'doctrine:database:drop'
        ];

        $input = new ArrayInput($arguments);
        $returnCode = $command->run($input, $output);

        //doctrine:migrations:migrate
        
        $command = $this->getApplication()->find('doctrine:migrations:migrate');

        $arguments = [
            'command' => 'doctrine:migrations:migrate',
            '--no-interaction' => true
        ];

        $input = new ArrayInput($arguments);
        $input->setInteractive(false);
        $returnCode = $command->run($input, $output);

        //doctrine:fixtures:load
        
        $command = $this->getApplication()->find('doctrine:fixtures:load');
        
        $arguments = [
            'command' => 'doctrine:fixtures:load',
            '--no-interaction' => true
        ];
        
        $input = new ArrayInput($arguments);
        $input->setInteractive(false);
        $returnCode = $command->run($input, $output);
    }
}
