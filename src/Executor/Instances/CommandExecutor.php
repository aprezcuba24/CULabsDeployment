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
    public function up()
    {
        $process = new Process($this->options['command'], $this->options['cwd'], $this->options['env']);
        $process->run();
    }

    public function update()
    {
        $this->up();
    }

    public function down()
    {
        $this->up();
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
     */
    public function printComment($direction)
    {
        $this->writeln(sprintf('<info>command:</info> <fg=cyan>execute</fg=cyan> <fg=yellow>%s</fg=yellow>', $this->options['command']));
    }
}