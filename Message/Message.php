<?php

/*
 * This file is part of the PubleroTemplateMailerBundle package.
 *
 * (c) Tomas Pecserke <tomas@pecserke.eu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Publero\TemplateMailerBundle\Message;

class Message
{
    /**
     * @var string
     */
    private $subject;

    /**
     * @var string
     */
    private $htmlBody;

    /**
     * @var string
     */
    private $textBody;

    /**
     * @var string|array
     */
    private $to;

    /**
     * @var string
     */
    private $sender;

    /**
     * @param string $subject   Message subject
     * @param string $htmlBody  Message HTML body
     * @param string $textBody  Message text body
     * @param string|array $to  Recipients or array of recipients
     * @param string $sender    Message sender
     */
    public function __construct($subject, $htmlBody, $textBody, $to, $sender)
    {
        $this->subject = $subject;
        $this->htmlBody = $htmlBody;
        $this->textBody = $textBody;
        $this->to = $to;
        $this->sender = $sender;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * @return string
     */
    public function getHtmlBody()
    {
        return $this->htmlBody;
    }

    /**
     * @param string $htmlBody
     */
    public function setHtmlBody($htmlBody)
    {
        $this->htmlBody = $htmlBody;
    }

    /**
     * @return string
     */
    public function getTextBody()
    {
        return $this->textBody;
    }

    /**
     * @param string $textBody
     */
    public function setTextBody($textBody)
    {
        $this->textBody = $textBody;
    }

    /**
     * @return string[]
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @param string[] $to
     */
    public function setTo(array $to)
    {
        $this->to = $to;
    }

    /**
     * @return string
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @param string $sender
     */
    public function setSender($sender)
    {
        $this->sender = $sender;
    }
}
