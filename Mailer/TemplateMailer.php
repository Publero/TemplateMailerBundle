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

use Publero\TemplateMailerBundle\Client\TemplateMailerClient;
use Publero\TemplateMailerBundle\Message\TemplateMessage;

class TemplateMailer
{
    /**
     * @var TemplateMailerClient
     */
    private $client;

    public function __construct(TemplateMailerClient $client)
    {
        $this->client = $client;
    }

    public function send($template, $to, array $params, $commonParams)
    {
        $templateHash = $template; // TODO get template hash
        $this->client->send(new TemplateMessage($templateHash, $to, $params, $commonParams));
    }
}
