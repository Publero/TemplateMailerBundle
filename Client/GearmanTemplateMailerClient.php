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

    /**
     * @var string
     */
    private $functionName;

    /**
     * @param Client $client
     * @param string $functionName
     */
    public function __construct(Client $client, $functionName)
    {
        $this->client = $client;
        $this->functionName = $functionName;
    }

    public function send(TemplateMessage $message)
    {
        $this->client->doBackground($this->functionName, json_encode(array(
            'template' => $message->getTemplate(),
            'recipients' => $message->getTo(),
            'params' => $message->getParams(),
            'commonParams' => $message->getCommonParams()
        )));
    }
}
