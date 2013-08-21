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
            array('getHash', 'getSender', 'getSubject', 'getBody', 'isStored', 'isFresh', 'update', 'assignHash', 'persist', 'delete'),
            array($this->client)
        );
    }

    public function testPersistRemote()
    {
        $sender = 'sender';
        $subject = 'subject';
        $body = 'body';
        $params = array();
        $hash = 'hash';
        $newHash = 'new_hash';

        $this->client
            ->expects($this->once())
            ->method('upload')
            ->with($sender, $subject, $body, $params, $hash)
            ->will($this->returnValue($newHash))
        ;

        $this->assertEquals($newHash, $this->storage->persistRemote($sender, $subject, $body, $params, $hash));
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
 