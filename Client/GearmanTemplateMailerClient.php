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

use Net\Gearman\Client;
use Publero\TemplateMailerBundle\Message\TemplateMessage;

class GearmanTemplateMailerClient implements TemplateMailerClient
{
    /**
     * @var Client
     */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function send(TemplateMessage $message)
    {
        $this->client->doBackground('sendTemplateMail', json_encode(array(
            'template' => $message->getTemplate(),
            'to' => $message->getTo(),
            'params' => $message->getParams(),
            'commonParams' => $message->getCommonParams()
        )));
    }
}
