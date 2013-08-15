<?php

/*
 * This file is part of the PubleroTemplateMailerBundle package.
 *
 * (c) Tomas Pecserke <tomas@pecserke.eu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Publero\TemplateMailerBundle\Mailer;

interface Mailer
{
    /**
     * @param string $template      The name of template to send
     * @param string|array $to      Recipients or array of recipients
     * @param array $params         Template parameters
     */
    public function send($template, $to, array $params);
}
