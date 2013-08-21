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
use Publero\TemplateMailerBundle\Message\Message;

class GearmanMessageMailerClient implements MessageMailerClient
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

    public function send(Message $message)
    {
        $this->client->doBackground($this->functionName, json_encode(array(
            'subject' => $message->getSubject(),
            'html_body' => $message->getHtmlBody(),
            'text_body' => $message->getTextBody(),
            'recipients' => $message->getTo(),
            'sender' => $message->getSender()
        )));
    }
}
