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
     * @param OutputInterface $output
     */
    public function configure($options, $direction, OutputInterface $output = null);

    public function up();

    public function down();

    public function update();

    /**
     * @param $direction
     */
    public function printComment($direction);
}