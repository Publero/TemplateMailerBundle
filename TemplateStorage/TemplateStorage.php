<?php

/*
 * This file is part of the PubleroTemplateMailerBundle package.
 *
 * (c) Tomas Pecserke <tomas@pecserke.eu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Publero\TemplateMailerBundle\TemplateStorage;

use Publero\TemplateMailerBundle\Client\RemoteStorageClient;
use Publero\TemplateMailerBundle\TemplateProcessor\TemplateProcessor;

abstract class TemplateStorage
{
    /**
     * @var RemoteStorageClient
     */
    private $remoteClient;

    /**
     * @var TemplateProcessor
     */
    protected $templateProcessor;

    public function __construct(RemoteStorageClient $remoteClient, TemplateProcessor $templateProcessor = null)
    {
        $this->remoteClient = $remoteClient;
        $this->templateProcessor = $templateProcessor;
    }

    /**
     * Returns hash of template specified by the code.
     *
     * @param string $code
     * @return string
     *
     * @throws \OutOfBoundsException If no template with specified code is stored
     */
    public abstract function getHash($code);

    /**
     * Returns sender of template specified by the code.
     *
     * @param string $code
     * @return string
     *
     * @throws \OutOfBoundsException If no template with specified code is stored
     */
    public abstract function getSender($code);

    /**
     * Returns subject of template specified by the code.
     *
     * @param string $code
     * @return string
     *
     * @throws \OutOfBoundsException If no template with specified code is stored
     */
    public abstract function getSubject($code);

    /**
     * Returns body of template specified by the code.
     *
     * @param string $code
     * @return string
     *
     * @throws \OutOfBoundsException If no template with specified code is stored
     */
    public abstract function getBody($code);

    /**
     * Returns whether the template specified by the code is stored in this storage.
     *
     * @param string $code
     * @return bool
     */
    public abstract function isStored($code);

    /**
     * Returns whether the local copy of template is fresh.
     *
     * @param string $code
     * @return bool
     *
     * @throws \OutOfBoundsException If no template with specified code is stored
     */
    public abstract function isFresh($code);

    /**
     * Uploads the local version of template (or all templates if no code specified),
     * and updates template's hash in local storage.
     *
     * @param string|null $code
     *
     * @throws \OutOfBoundsException If no template with specified code is stored
     */
    public abstract function update($code = null);

    /**
     * Persists the template on the server and returns template hash.
     *
     * @param string $sender
     * @param string $subject
     * @param string $body
     * @param array $defaultParams
     * @param string|null $hash
     * @return string
     */
    public function persistRemote($sender, $subject, $body, array $defaultParams = array(), $hash = null)
    {
        return $this->remoteClient->upload($sender, $subject, $body, $defaultParams, $hash);
    }

    /**
     * Removes the template from the server.
     *
     * @param string $hash
     */
    public function deleteRemote($hash)
    {
        $this->remoteClient->remove($hash);
    }

    /**
     * @param string $code
     * @param string $hash
     *
     * @throws \OutOfBoundsException If no template with specified code is stored
     */
    public abstract function assignHash($code, $hash);

    /**
     * Persists the template into local storage.
     *
     * @param string $code
     * @param string $sender
     * @param string $subject
     * @param string $body
     * @param array $defaultParams
     */
    public abstract function persist($code, $sender, $subject, $body, array $defaultParams = array());

    /**
     * Removes the template from local storage.
     *
     * @param string $code
     *
     * @throws \OutOfBoundsException If no template with specified code is stored
     */
    public abstract function delete($code);
}
