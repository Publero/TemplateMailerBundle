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

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Publero\TemplateMailerBundle\Client\RemoteStorageClient;
use Publero\TemplateMailerBundle\Model\Template;
use Publero\TemplateMailerBundle\TemplateStorage\DoctrineTemplateStorage;

class DoctrineTemplateStorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DoctrineTemplateStorage
     */
    private $storage;

    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * @var ObjectRepository
     */
    private $repository;

    /**
     * @var RemoteStorageClient
     */
    private $client;

    protected function setUp()
    {
        $this->repository = $this->getMock(
            'Doctrine\Common\Persistence\ObjectRepository',
            array('find', 'findBy', 'findOneBy', 'findAll', 'getClassName', 'findOneByCode')
        );

        $this->manager = $this->getMock(
            'Doctrine\Common\Persistence\ObjectManager'//,
            //array('persist', 'remove', 'flush', 'find', 'clear', 'detach', 'refresh', 'getRepository', 'getClassMetadata', 'getMetadataFactory', 'merge', 'contains', 'initializeObject')
        );
        $this->manager
            ->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($this->repository))
        ;

        $this->client = $this->getMock('Publero\TemplateMailerBundle\Client\RemoteStorageClient', array('upload', 'remove'));

        $this->storage = new DoctrineTemplateStorage($this->client, $this->manager);
    }

    public function testGetHash()
    {
        $code = 'code';
        $hash = 'hash';
        $template = new Template();
        $template->setHash($hash);

        $this->repository
            ->expects($this->once())
            ->method('findOneByCode')
            ->with($code)
            ->will($this->returnValue($template))
        ;

        $this->assertEquals($hash, $this->storage->getHash($code));
    }

    /**
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage template 'template_code' is not stored
     */
    public function testGetHashNotStored()
    {
        $code = 'template_code';

        $this->repository
            ->expects($this->once())
            ->method('findOneByCode')
            ->with($code)
            ->will($this->returnValue(null))
        ;

        $this->storage->getHash($code);
    }

    public function testGetSource()
    {
        $code = 'code';
        $source = 'source';
        $template = new Template();
        $template->setSource($source);

        $this->repository
            ->expects($this->once())
            ->method('findOneByCode')
            ->with($code)
            ->will($this->returnValue($template))
        ;

        $this->assertEquals($source, $this->storage->getSource($code));
    }

    /**
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage template 'template_code' is not stored
     */
    public function testGetSourceNotStored()
    {
        $code = 'template_code';

        $this->repository
            ->expects($this->once())
            ->method('findOneByCode')
            ->with($code)
            ->will($this->returnValue(null))
        ;

        $this->storage->getSource($code);
    }

    public function testIsStoredTrue()
    {
        $code = 'code';
        $template = new Template();

        $this->repository
            ->expects($this->once())
            ->method('findOneByCode')
            ->with($code)
            ->will($this->returnValue($template))
        ;

        $this->assertTrue($this->storage->isStored($code));
    }

    public function testIsStoredFalse()
    {
        $code = 'code';

        $this->repository
            ->expects($this->once())
            ->method('findOneByCode')
            ->with($code)
            ->will($this->returnValue(null))
        ;

        $this->assertFalse($this->storage->isStored($code));
    }

    public function testIsFreshTrue()
    {
        $code = 'code';
        $template = new Template();
        $template->setCode($code);
        $template->setSource('source');
        $template->setHash('hash');
        $template->setDefaultParams(array());
        $template->setChecksum($this->storage->computeChecksum($template));

        $this->repository
            ->expects($this->once())
            ->method('findOneByCode')
            ->with($code)
            ->will($this->returnValue($template))
        ;

        $this->assertTrue($this->storage->isFresh($code));
    }

    public function testIsFreshFalse()
    {
        $code = 'code';
        $template = new Template();
        $template->setCode($code);
        $template->setSource('source');
        $template->setHash('hash');
        $template->setDefaultParams(array());
        $template->setChecksum('different checksum');

        $this->repository
            ->expects($this->once())
            ->method('findOneByCode')
            ->with($code)
            ->will($this->returnValue($template))
        ;

        $this->assertFalse($this->storage->isFresh($code));
    }

    public function testIsFreshNoHash()
    {
        $code = 'code';
        $template = new Template();
        $template->setCode($code);
        $template->setSource('source');
        $template->setDefaultParams(array());
        $template->setChecksum($this->storage->computeChecksum($template));

        $this->repository
            ->expects($this->once())
            ->method('findOneByCode')
            ->with($code)
            ->will($this->returnValue($template))
        ;

        $this->assertFalse($this->storage->isFresh($code));
    }

    public function testUpdateOneNotFresh()
    {
        $code = 'code';
        $hash = 'hash';
        $source = 'source';
        $params = array();

        $template = new Template();
        $template->setCode($code);
        $template->setSource($source);
        $template->setDefaultParams($params);
        $template->setChecksum('different checksum');

        $this->repository
            ->expects($this->once())
            ->method('findOneByCode')
            ->with($code)
            ->will($this->returnValue($template))
        ;
        $this->client
            ->expects($this->once())
            ->method('upload')
            ->with($source, $params, null)
            ->will($this->returnValue($hash))
        ;

        $this->storage->update($code);

        $this->assertEquals($hash, $template->getHash());
        $this->assertEquals($template->getChecksum(), $this->storage->computeChecksum($template));
    }

    public function testUpdateOneFresh()
    {
        $code = 'code';
        $hash = 'hash';
        $source = 'source';
        $params = array();

        $template = new Template();
        $template->setCode($code);
        $template->setHash($hash);
        $template->setSource($source);
        $template->setDefaultParams($params);
        $template->setChecksum($this->storage->computeChecksum($template));

        $this->repository
            ->expects($this->once())
            ->method('findOneByCode')
            ->with($code)
            ->will($this->returnValue($template))
        ;
        $this->client
            ->expects($this->never())
            ->method('upload')
        ;

        $this->storage->update($code);
    }

    public function testUpdateAll()
    {
        $params = array();
        $hash = 'hash';

        $template1 = new Template();
        $template1->setCode('code1');
        $template1->setSource('source1');
        $template1->setHash('hash1');
        $template1->setDefaultParams($params);
        $template1->setChecksum($this->storage->computeChecksum($template1));

        $template2 = new Template();
        $template2->setCode('code2');
        $template2->setSource('source2');
        $template2->setDefaultParams($params);
        $template2->setChecksum('different checksum');

        $this->repository
            ->expects($this->once())
            ->method('findAll')
            ->will($this->returnValue(array($template1, $template2)))
        ;
        $this->client
            ->expects($this->once())
            ->method('upload')
            ->with('source2', $params, null)
            ->will($this->returnValue($hash))
        ;

        $this->storage->update();

        $this->assertEquals($hash, $template2->getHash());
    }

    /**
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage template 'template_code' is not stored
     */
    public function testUpdateNotStored()
    {
        $code = 'template_code';

        $this->repository
            ->expects($this->once())
            ->method('findOneByCode')
            ->with($code)
            ->will($this->returnValue(null))
        ;

        $this->storage->update($code);
    }

    public function testAssignHash()
    {
        $code = 'code';
        $hash = 'hash';
        $template = new Template();

        $this->repository
            ->expects($this->once())
            ->method('findOneByCode')
            ->with($code)
            ->will($this->returnValue($template))
        ;

        $this->storage->assignHash($code, $hash);

        $this->assertEquals($hash, $template->getHash());
    }

    /**
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage template 'template_code' is not stored
     */
    public function testAssignHashNotStored()
    {
        $code = 'template_code';
        $hash = 'hash';

        $this->repository
            ->expects($this->once())
            ->method('findOneByCode')
            ->with($code)
            ->will($this->returnValue(null))
        ;

        $this->storage->assignHash($code, $hash);
    }

    public function testPersistUpdateStored()
    {
        $code = 'code';
        $source = 'source';
        $template = new Template();

        $this->repository
            ->expects($this->once())
            ->method('findOneByCode')
            ->with($code)
            ->will($this->returnValue($template))
        ;

        $this->manager
            ->expects($this->never())
            ->method('persist')
        ;
        $this->manager
            ->expects($this->once())
            ->method('flush')
        ;

        $this->storage->persist($code, $source);

        $this->assertEquals($source, $template->getSource());
    }

    public function testPersistCreateNew()
    {
        $code = 'code';
        $source = 'source';

        $this->repository
            ->expects($this->once())
            ->method('findOneByCode')
            ->with($code)
            ->will($this->returnValue(null))
        ;

        $this->manager
            ->expects($this->once())
            ->method('persist')
        ;
        $this->manager
            ->expects($this->once())
            ->method('flush')
        ;

        $this->storage->persist($code, $source);
    }

    public function testDelete()
    {
        $code = 'code';
        $template = new Template();

        $this->repository
            ->expects($this->once())
            ->method('findOneByCode')
            ->with($code)
            ->will($this->returnValue($template))
        ;

        $this->manager
            ->expects($this->once())
            ->method('remove')
            ->with($template)
        ;
        $this->manager
            ->expects($this->once())
            ->method('flush')
        ;

        $this->storage->delete($code);
    }

    /**
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage template 'template_code' is not stored
     */
    public function testDeleteNotStored()
    {
        $code = 'template_code';

        $this->repository
            ->expects($this->once())
            ->method('findOneByCode')
            ->with($code)
            ->will($this->returnValue(null))
        ;

        $this->storage->delete($code);
    }
}
