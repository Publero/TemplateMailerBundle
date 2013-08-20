<?php

/*
 * This file is part of the PubleroTemplateMailerBundle package.
 *
 * (c) Tomas Pecserke <tomas@pecserke.eu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Publero\TemplateMailerBundle\Client;

use Publero\TemplateMailerBundle\Message\TemplateMessage;

interface TemplateMailerClient
{
    public function send(TemplateMessage $message);
}
