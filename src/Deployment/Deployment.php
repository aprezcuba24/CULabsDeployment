<?php

/**
 * @author: Renier Ricardo Figueredo
 * @mail: aprezcuba24@gmail.com
 */

namespace CULabs\Deployment;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class Deployment extends BaseDeployment
{
    protected $configPath;
    protected $configFile;

    public function __construct($configPath, $configFile, OutputInterface $output)
    {
        parent::__construct($output);

        $this->configPath = $configPath;
        $this->configFile = $configFile;
    }

    protected function customBuildContainer(ContainerBuilder $containerBuilder)
    {
        $loader = new YamlFileLoader($containerBuilder, new FileLocator($this->configPath));
        $loader->load($this->configFile);
    }

    /**
     * @return String
     */
    protected function getAppDir()
    {
        return getcwd();
    }
} 