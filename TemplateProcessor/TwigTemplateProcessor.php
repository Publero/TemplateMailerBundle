<?php

/*
 * This file is part of the PubleroTemplateMailerBundle package.
 *
 * (c) Tomas Pecserke <tomas@pecserke.eu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Publero\TemplateMailerBundle\TemplateProcessor;

class TwigTemplateProcessor implements TemplateProcessor
{
    /**
     * @var \Twig_Environment
     */
    private $twig;

    public function __construct(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    public function processTemplate($name, array $params)
    {
        return $this->twig->loadTemplate($name)->render($params);
    }

    public function isTemplateFresh($name, $time)
    {
        $this->twig->isTemplateFresh($name, $time);
    }
}
