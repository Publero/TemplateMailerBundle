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

use Publero\TemplateMailerBundle\TemplateStorage\TemplateStorage;

class StoredTemplateMailer extends TemplateMailer
{
    /**
     * @var TemplateStorage
     */
    private $storage;

    public function __construct(TemplateMailerClient $client, TemplateStorage $storage)
    {
        parent::__construct($client);

        $this->storage = $storage;
    }

    /**
     * Translates template code to ITLogica hash and sends message with this template.
     *
     * @param string $template      The name of template to send
     * @param string|array $to      Recipients or array of recipients
     * @param array $params         Template parameters
     * @param bool $commonParams    Whether the params are common for all recipients (array of arrays is expected)
     *
     * @throws \OutOfBoundsException If template doesn't exist
     */
    public function send($template, $to, array $params, $commonParams = true)
    {
        $this->sendByHash($this->storage->getHashByCode($template), $to, $params, $commonParams);
    }

    /**
     * @param string $templateHash  The name of template to send
     * @param string|array $to      Recipients or array of recipients
     * @param array $params         Template parameters
     * @param bool $commonParams    Whether the params are common for all recipients (array of arrays is expected)
     */
    public function sendByHash($templateHash, $to, array $params, $commonParams = true)
    {
        parent::send($templateHash, $to, $params, $commonParams);
    }
}
