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

class TemplateMessage
{
    /**
     * @var string
     */
    private $template;

    /**
     * @var string[]
     */
    private $to;

    /**
     * @var array
     */
    private $params;

    /**
     * @var bool
     */
    private $commonParams;

    /**
     * @param string $template      The name of template to send
     * @param string|array $to      Recipients or array of recipients
     * @param array $params         Template parameters
     * @param bool $commonParams    Whether the parameters are common for all recipients (if there are more),
     *                              if true array of arrays is expected as params
     */
    public function __construct($template, $to, array $params, $commonParams = true)
    {
        $this->template = $template;
        $this->to = $to;
        $this->params = $params;
        $this->commonParams = (bool) $commonParams;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param string $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * @return string|array
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @param string|array $to
     */
    public function setTo(array $to)
    {
        $this->to = $to;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param array $params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * @return bool
     */
    public function getCommonParams()
    {
        return $this->commonParams;
    }

    /**
     * @param bool $commonParams
     */
    public function setCommonParams($commonParams)
    {
        $this->commonParams = (bool) $commonParams;
    }
}
