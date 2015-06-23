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

class VHostExecutor extends Executor
{
    public function up()
    {
        $content = $this->getContent();
        $fs = new Filesystem();
        $fs->dumpFile($this->getVhostConfigFilePath(), $content);
        $this->enabledSite();
        $this->reloadApache();
    }

    public function update()
    {
        $fs = new Filesystem();
        $content = $this->getContent();
        $fs->dumpFile($this->getVhostConfigFilePath(), $content);
        $this->reloadApache();
    }

    public function down()
    {
        $this->disabledSite();
        $fs = new Filesystem();
        $fs->remove($this->getVhostConfigFilePath());
        $this->reloadApache();
    }

    protected function getContent()
    {
        return $this->render($this->options['template'], [
            'port'           => $this->options['port'],
            'ServerName'     => $this->options['ServerName'],
            'DirectoryIndex' => $this->options['DirectoryIndex'],
            'SetEnv'         => $this->options['SetEnv'],
            'DocumentRoot'   => $this->options['DocumentRoot'],
        ]);
    }

    protected function getVhostConfigFilePath()
    {
        return $this->options['apache2-sites-config'].DIRECTORY_SEPARATOR.$this->getVhostConfigFileName();
    }

    protected function getVhostConfigFileName()
    {
        return $this->options['ServerName'].'.conf';
    }

    protected function enabledSite()
    {
        $process = new Process(sprintf('a2ensite %s', $this->getVhostConfigFileName()));
        $process->run();
    }

    protected function disabledSite()
    {
        $process = new Process(sprintf('a2dissite %s', $this->getVhostConfigFileName()));
        $process->run();
    }

    protected function reloadApache()
    {
        $process = new Process('service apache2 reload');
        $process->run();
    }

    protected function setOptions(OptionsResolver $resolver, $direction)
    {
        $resolver
            ->setDefaults([
                'SetEnv'               => [],
                'port'                 => 80,
                'DirectoryIndex'       => 'app.php',
                'apache2-sites-config' => '/etc/apache2/sites-available/',
                'template'             => 'vhost/base_vhost.conf.twig',
            ])
        ;
        if (in_array($direction, [DeploymentInterface::DIRECTION_UP, DeploymentInterface::DIRECTION_UPDATE])) {
            $resolver->setRequired(['DocumentRoot', 'ServerName']);
        } else {
            $resolver->setRequired(['ServerName']);
        }
    }

    /**
     * @param $direction
     */
    public function printComment($direction)
    {
        $action = 'create';
        if ($direction == DeploymentInterface::DIRECTION_UPDATE) {
            $action = 'update';
        }
        if ($direction == DeploymentInterface::DIRECTION_DOWN) {
            $action = 'remove';
        }
        $this->writeln(sprintf('<info>vhost:</info> <fg=cyan>%s</fg=cyan> <fg=yellow>%s</fg=yellow>', $action, $this->getVhostConfigFilePath()));
    }
} 