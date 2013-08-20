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

interface RemoteStorageClient
{
    /**
     * Removes template specified by hash from the remote server.
     *
     * @param string $hash
     */
    public function remove($hash);

    /**
     * Uploads template to the remote server and returns hash of the template.
     *
     * @param string $code
     * @param string $source
     * @return string
     */
    public function upload($code, $source);
}
