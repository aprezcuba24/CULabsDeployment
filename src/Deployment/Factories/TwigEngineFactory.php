<?php

/**
 * @author: Renier Ricardo Figueredo
 * @mail: aprezcuba24@gmail.com
 */
namespace CULabs\Deployment\Factories;

use Symfony\Bridge\Twig\TwigEngine;
use Symfony\Component\Templating\TemplateNameParser;

class TwigEngineFactory
{
    public function create($view_path_default, $view_paths = [])
    {
        $view_paths[] = $view_path_default;
        $environment = new \Twig_Environment(new \Twig_Loader_Filesystem($view_paths));
        $engine = new TwigEngine($environment, new TemplateNameParser());

        return $engine;
    }
} 