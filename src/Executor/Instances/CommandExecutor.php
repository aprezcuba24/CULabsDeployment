<?php

/**
 * @author: Renier Ricardo Figueredo
 * @mail: aprezcuba24@gmail.com
 */
namespace CULabs\Executor\Instances;

use CULabs\Executor\Executor;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Process\Process;

class CommandExecutor extends Executor
{
    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function up(InputInterface $input, OutputInterface $output)
    {
        $process = new Process($this->options['command'], $this->options['cwd'], $this->options['env']);
        $process->run();
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
        $this->up($input, $output);
    }

    protected function setOptions(OptionsResolver $resolver, $direction)
    {
        $resolver
            ->setDefaults([
                'env' => [],
                'cwd' => null
            ])
            ->setRequired(['command'])
        ;
    }

    /**
     * @param $direction
     * @param OutputInterface $output
     * @return string
     */
    public function printComment($direction, OutputInterface $output)
    {
        $output->writeln(sprintf('<info>command:</info> <fg=cyan>execute</fg=cyan> <fg=yellow>%s</fg=yellow>', $this->options['command']));
    }
}