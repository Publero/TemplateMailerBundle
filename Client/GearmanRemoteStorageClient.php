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

        var_dump($uploadFunctionName, $removeFunctionName); die;
    }

    public function upload($source, array $defaultParams = array(), $hash = null)
    {
        $response = $this->client->doNormal($this->uploadFunctionName, json_encode(array(
            'source' => $source,
            'default_parameters' => $defaultParams,
            'hash' => $hash
        )));

        $response = json_decode($response);

        return $response['id'];
    }

    public function remove($hash)
    {
        $this->client->doBackground($this->removeFunctionName, json_encode(array('hash' => $hash)));
    }
}
