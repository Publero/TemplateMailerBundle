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

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function remove($hash)
    {
        $this->client->doBackground('removeTemplate', json_encode(array('hash' => $hash)));
    }

    public function upload($code, $source)
    {
        return $this->client->doNormal('uploadTemplate', json_encode(array(
            'code' => $code,
            'source' => $source
        )));
    }
}
