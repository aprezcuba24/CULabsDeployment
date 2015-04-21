<?php

/**
 * @author: Renier Ricardo Figueredo
 * @mail: aprezcuba24@gmail.com
 */
namespace CULabs\Executor\Instances;

use CULabs\Deployment\DeploymentInterface;
use CULabs\Executor\Executor;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Process\Process;

class SupervisorExecutor extends Executor
{
    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function up(InputInterface $input, OutputInterface $output)
    {
        $fs = new Filesystem();
        $file_name = $this->options['filename'];
        $content = '';

        if ($this->options['append'] && $fs->exists($file_name)) {
            $content  = file_get_contents($file_name);
            $content .= "\n";
        }
        $content .= $this->render('supervisor/supervisor.conf.twig', [
            'key'              => $this->options['key'],
            'vars_environment' => $this->options['vars_environment'],
            'command'          => $this->options['command'],
            'options'          => $this->options['options'],
        ]);
        $fs->dumpFile($file_name, $content);
        $this->restartSupervisor();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function update(InputInterface $input, OutputInterface $output)
    {
        $this->up($input, $output);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function down(InputInterface $input, OutputInterface $output)
    {
        $fs = new Filesystem();
        $fs->remove($this->options['filename']);
        $this->restartSupervisor();
    }

    protected function restartSupervisor()
    {
        if (!$this->options['restart_supervisor']) {
            return;
        }
        $process = new Process('service supervisor restart');
        $process->run();
    }

    protected function setOptions(OptionsResolver $resolver, $direction)
    {
        $resolver
            ->setDefaults([
                'append'             => false,
                'restart_supervisor' => false,
                'options'            => [],
                'vars_environment'   => [],
            ])
            ->setRequired(['filename'])
        ;
        if (in_array($direction, [DeploymentInterface::DIRECTION_UPDATE, DeploymentInterface::DIRECTION_UP])) {
            $resolver->setRequired([
                'key',
                'command'
            ]);
        }
    }

    /**
     * @param $direction
     * @param OutputInterface $output
     * @return string
     */
    public function printComment($direction, OutputInterface $output)
    {
        $action = 'create';
        if ($direction == DeploymentInterface::DIRECTION_UPDATE) {
            $action = 'update';
        }
        if ($direction == DeploymentInterface::DIRECTION_DOWN) {
            $action = 'remove';
        }
        $output->writeln(sprintf('<info>supervisor:</info> <fg=cyan>%s</fg=cyan> <fg=yellow>%s</fg=yellow>', $action, $this->options['filename']));
    }
}