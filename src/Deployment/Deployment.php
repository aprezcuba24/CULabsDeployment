<?php

/**
 * @author: Renier Ricardo Figueredo
 * @mail: aprezcuba24@gmail.com
 */

namespace CULabs\Deployment;

use CULabs\Executor\ExecutorInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Yaml\Yaml;

class Deployment implements DeploymentInterface
{
    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input  = $input;
        $this->output = $output;

        $direction = $input->getArgument('direction');
        $direction_options = [self::DIRECTION_DOWN, self::DIRECTION_UP, self::DIRECTION_UPDATE, self::DIRECTION_BATCH];
        if (!in_array($direction, $direction_options)) {
            throw new \InvalidArgumentException(sprintf('The direction must be %s', json_encode($direction_options)));
        }
        $dir_path    = getcwd().DIRECTORY_SEPARATOR.$input->getOption('config-path');
        $config_file = $input->getOption('config-file');

        if ($direction != self::DIRECTION_BATCH) {
            $this->executeOne($dir_path, $config_file, $direction);

            return;
        }
        $container = $this->buildContainer($dir_path, $config_file);
        $steps = $container->getParameter('deployment_steps');
        $batch = $steps['batch'];
        foreach ($batch as $file) {
            $output->writeln(sprintf('<info>>>> %s <<<</info>', $file));
            $this->executeOne($dir_path, $file, self::DIRECTION_UPDATE);
        }
    }

    protected function executeOne($dir_path, $config_file, $direction)
    {
        $executors = [];
        $container = $this->buildContainer($dir_path, $config_file);

        $steps = $container->getParameter('deployment_steps')[$direction];
        foreach ($steps as $key => $config) {
            $id = isset($config['service'])? $config['service']: $key;
            unset($config['service']);
            $executor = $container->get($id);
            if (!$executor instanceof ExecutorInterface) {
                throw new \InvalidArgumentException(sprintf('%s must be instance of CULabs\Executor\ExecutorInterface', $id));
            }
            $executor->configure($config, $direction);
            $executors[] = $executor;
        }

        /**@var $executor ExecutorInterface*/
        foreach ($executors as $executor) {
            $executor->printComment($direction, $this->output);
            $executor->$direction($this->input, $this->output);
        }
    }

    protected function buildContainer($path, $file)
    {
        $container = new ContainerBuilder();
        $container->registerExtension(new DeploymentContainerExtension());
        $container->setParameter('ROOT_DIR', __DIR__.'/../..');
        $container->setParameter('APP_DIR', getcwd());

        $loader = new YamlFileLoader($container, new FileLocator(
            __DIR__.'/../../config'
        ));
        $loader->load('services.yml');

        $loader = new YamlFileLoader($container, new FileLocator($path));
        $loader->load($file);

        $container->compile();

        return $container;
    }
} 