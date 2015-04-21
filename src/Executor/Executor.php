<?php

/**
 * @author: Renier Ricardo Figueredo
 * @mail: aprezcuba24@gmail.com
 */

namespace CULabs\Executor;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Templating\EngineInterface;

abstract class Executor implements ExecutorInterface
{
    protected $options;

    /**
     * @var EngineInterface
     */
    protected $templating;

    /**
     * @param $options
     * @param $direction
     */
    public function configure($options, $direction)
    {
        $optionsResolver = new OptionsResolver();
        $this->setOptions($optionsResolver, $direction);
        $this->options = $optionsResolver->resolve($options);
    }

    abstract protected function setOptions(OptionsResolver $resolver, $direction);

    public function setTemplating(EngineInterface $templating)
    {
        $this->templating = $templating;
    }

    public function render($name, $parameters = [])
    {
        return $this->templating->render($name, $parameters);
    }
} 