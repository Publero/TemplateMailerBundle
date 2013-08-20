<?php

/*
 * This file is part of the PubleroTemplateMailerBundle package.
 *
 * (c) Tomas Pecserke <tomas@pecserke.eu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Publero\TemplateMailerBundle\Tests\TemplateStorage;

use Publero\TemplateMailerBundle\Client\RemoteStorageClient;
use Publero\TemplateMailerBundle\TemplateStorage\TemplateStorage;

class TemplateStorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TemplateStorage
     */
    private $storage;

    /**
     * @var RemoteStorageClient
     */
    private $client;

    protected function setUp()
    {
        $this->client = $this->getMock('Publero\TemplateMailerBundle\Client\RemoteStorageClient', array('upload', 'remove'));
        $this->storage = $this->getMock(
            'Publero\TemplateMailerBundle\TemplateStorage\TemplateStorage',
            array('getHash', 'getSource', 'isStored', 'isFresh', 'update', 'assignHash', 'persist', 'delete'),
            array($this->client)
        );
    }

    public function testPersistRemote()
    {
        $code = 'code';
        $source = 'source';
        $hash = 'hash';
        $newHash = 'new_hash';

        $this->storage
            ->expects($this->once())
            ->method('isStored')
            ->with($code)
            ->will($this->returnValue(true))
        ;

        $this->storage
            ->expects($this->once())
            ->method('getHash')
            ->with($code)
            ->will($this->returnValue($hash))
        ;

        $this->client
            ->expects($this->once())
            ->method('upload')
            ->with($source, array(), $hash)
            ->will($this->returnValue($newHash))
        ;

        $this->storage
            ->expects($this->once())
            ->method('assignHash')
            ->with($code, $newHash)
        ;

        $this->storage->persistRemote($code, $source);
    }

    public function testDeleteRemote()
    {
        $hash = 'hash';

        $this->client
            ->expects($this->once())
            ->method('remove')
            ->with($hash)
        ;

        $this->storage->deleteRemote($hash);
    }
}
 