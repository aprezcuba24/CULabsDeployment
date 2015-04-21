<?php

/**
 * @author: Renier Ricardo Figueredo
 * @mail: aprezcuba24@gmail.com
 */
namespace CULabs\Executor;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface ExecutorInterface
{
    /**
     * @param $options
     * @param $direction
     */
    public function configure($options, $direction);

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function up(InputInterface $input, OutputInterface $output);

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function down(InputInterface $input, OutputInterface $output);

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function update(InputInterface $input, OutputInterface $output);

    /**
     * @param $direction
     * @param OutputInterface $output
     * @return string
     */
    public function printComment($direction, OutputInterface $output);
}