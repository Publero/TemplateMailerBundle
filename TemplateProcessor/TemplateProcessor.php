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

interface TemplateProcessor
{
    /**
     * Processes template source.
     *
     * @param string $source
     * @param array $params
     * @return string
     */
    public function processTemplate($source, array $params);
}
