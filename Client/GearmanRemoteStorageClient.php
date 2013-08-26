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

class GearmanRemoteStorageClient implements RemoteStorageClient
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $uploadFunctionName;

    /**
     * @var string
     */
    private $removeFunctionName;

    /**
     * @param Client $client
     * @param string $uploadFunctionName
     * @param string $removeFunctionName
     */
    public function __construct(Client $client, $uploadFunctionName, $removeFunctionName)
    {
        $this->client = $client;
        $this->uploadFunctionName = $uploadFunctionName;
        $this->removeFunctionName = $removeFunctionName;
    }

    public function upload($senderSource, $subjectSource, $bodySource, array $defaultParams = array(), $hash = null)
    {
        $response = $this->client->doNormal($this->uploadFunctionName, json_encode(array(
            'sender' => $senderSource,
            'subject' => $subjectSource,
            'template' => $bodySource,
            'parameters' => $defaultParams,
            'hash' => $hash
        )));

        $response = json_decode($response, true);

        return $response['id'];
    }

    public function remove($hash)
    {
        $this->client->doBackground($this->removeFunctionName, json_encode(array('hash' => $hash)));
    }
}
