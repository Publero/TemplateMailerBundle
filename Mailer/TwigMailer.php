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

use Publero\TemplateMailerBundle\Client\MessageMailerClient;
use Publero\TemplateMailerBundle\Message\Message;

class TwigMailer implements Mailer
{
    /**
     * @var MessageMailerClient
     */
    private $client;

    /**
     * @var \Twig_Environment
     */
    private $twig;

    public function __construct(MessageMailerClient $client, \Twig_Environment $twig)
    {
        $this->client = $client;
        $this->twig = $twig;
    }

    public function send($template, $to, array $params)
    {
        $template = $this->twig->loadTemplate($template);

        $params = $this->twig->mergeGlobals($params);
        $subject = $template->renderBlock('subject', $params);
        $htmlBody = $template->renderBlock('html_body', $params);
        $textBody = $template->renderBlock('text_body', $params);
        $sender = $template->renderBlock('sender', $params);

        $this->client->send(new Message($subject, $htmlBody, $textBody, $to, $sender));
    }
}
