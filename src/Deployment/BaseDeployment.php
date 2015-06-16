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

/**
 * Class BaseDeployment
 * @package CULabs\Deployment
 */
abstract class BaseDeployment implements DeploymentInterface
{
    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->output    = $output;
        $this->container = $this->buildContainer();
    }

    /**
     * @param $direction
     */
    public function execute($direction)
    {
        $direction_options = [self::DIRECTION_DOWN, self::DIRECTION_UP, self::DIRECTION_UPDATE, self::DIRECTION_BATCH];
        if (!in_array($direction, $direction_options)) {
            throw new \InvalidArgumentException(sprintf('The direction must be %s', json_encode($direction_options)));
        }
        if ($direction != self::DIRECTION_BATCH) {
            $this->executeOne($direction);

            return;
        }
        $steps = $this->container->getParameter('deployment_steps');
        $batch = $steps['batch'];
        foreach ($batch as $file) {
            $this->writeln(sprintf('<info>>>> %s <<<</info>', $file));
            $this->executeOne(self::DIRECTION_UPDATE);
        }
    }

    protected function executeOne($direction)
    {
        $executors = [];
        $steps = $this->container->getParameter('deployment_steps')[$direction];
        foreach ($steps as $key => $config) {
            $id = isset($config['service'])? $config['service']: $key;
            unset($config['service']);
            $executor = $this->container->get($id);
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

    protected function buildContainer()
    {
        $container = new ContainerBuilder();
        $container->registerExtension(new DeploymentContainerExtension());
        $container->setParameter('ROOT_DIR', $this->getRootDir());
        $container->setParameter('APP_DIR', $this->getAppDir());

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        $loader->load('services.yml');

        $this->customBuildContainer($container);

        $container->compile();

        return $container;
    }

    protected abstract function customBuildContainer(ContainerBuilder $containerBuilder);

    /**
     * @return String
     */
    protected abstract function getRootDir();

    /**
     * @return String
     */
    protected abstract function getAppDir();

    /**
     * @param $text String
     */
    protected function writeln($text)
    {
        if (!$this->output) {
            return;
        }
        $this->output->writeln($text);
    }
} 