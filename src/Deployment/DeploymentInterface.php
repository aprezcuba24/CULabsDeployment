<?php

/**
 * @author: Renier Ricardo Figueredo
 * @mail: aprezcuba24@gmail.com
 */
namespace CULabs\Deployment;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface DeploymentInterface
{
    const DIRECTION_UP     = 'up';
    const DIRECTION_DOWN   = 'down';
    const DIRECTION_UPDATE = 'update';
    const DIRECTION_BATCH  = 'batch';

    /**
     * @param $direction
     */
    public function execute($direction);
}