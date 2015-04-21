<?php

/**
 * @author: Renier Ricardo Figueredo
 * @mail: aprezcuba24@gmail.com
 */

namespace CULabs\Command;

use CULabs\Deployment\Deployment;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeploymentCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('deployment')
            ->setDescription('Deployment application')
            ->addArgument('direction', InputArgument::OPTIONAL, 'The direction of deployment')
            ->addOption('config-path', 'cp', InputArgument::OPTIONAL, 'Path of config file', 'deployment')
            ->addOption('config-file', 'cf', InputArgument::OPTIONAL, 'Config file', 'config.yml')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $deployment = new Deployment();
        $deployment->execute($input, $output);
    }
} 