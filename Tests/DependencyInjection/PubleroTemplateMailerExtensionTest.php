<?php

/*
 * This file is part of the PubleroTemplateMailerBundle package.
 *
 * (c) Tomas Pecserke <tomas@pecserke.eu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Publero\TemplateMailerBundle\Tests\DependencyInjection;

use Net\Gearman\Client;
use Publero\TemplateMailerBundle\DependencyInjection\PubleroTemplateMailerExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Yaml\Parser;

class PubleroTemplateMailerExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var Extension
     */
    private $extension;

    /**
     * @var Parser
     */
    private $parser;

    protected function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->extension = new PubleroTemplateMailerExtension();
        $this->parser = new Parser();
    }

    public function testGearmanServerConfiguration()
    {
        $config = $this->parser->parse('
publero_template_mailer:
    gearman_servers:
        - localhost
        - localhost:12345
        ');

        $this->extension->load($config, $this->container);
        /* @var Client $client */
        $client = $this->container->get('publero_template_mailer.gearman_client');

        $this->assertEquals($client->getServers(), array('localhost:4730', 'localhost:12345'));
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Invalid configuration for path "publero_template_mailer.gearman_servers.0": Gearman server must be in form host[:port]
     */
    public function testGearmanServerConfigurationInvalidAddressFormat()
    {
        $config = $this->parser->parse('
publero_template_mailer:
    gearman_servers:
        - host:notANumber
        ');

        $this->extension->load($config, $this->container);
    }

    public function testTemplateStorageConfigurationNull()
    {
        $config = $this->parser->parse('
publero_template_mailer:
    gearman_servers:
        - localhost
    template_storage:
        type: ~
        ');

        $this->extension->load($config, $this->container);

        $this->assertFalse($this->container->has('publero_template_mailer.mailer.stored_template'));
    }

    public function testTemplateStorageConfigurationService()
    {
        $config = $this->parser->parse('
publero_template_mailer:
    gearman_servers:
        - localhost
    template_storage:
        type: service
        id: test.service_id
        ');

        $this->extension->load($config, $this->container);

        $this->assertTrue($this->container->hasAlias('publero_template_mailer.template.storage'));
        $this->assertEquals('test.service_id', (string) $this->container->getAlias('publero_template_mailer.template.storage'));
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Invalid configuration for path "publero_template_mailer.template_storage": custom template storage must have configured service id
     */
    public function testTemplateStorageConfigurationServiceNoId()
    {
        $config = $this->parser->parse('
publero_template_mailer:
    gearman_servers:
        - localhost
    template_storage:
        type: service
        ');

        $this->extension->load($config, $this->container);
    }

    /**
     * @dataProvider backendProvider
     */
    public function testTemplateStorageConfigurationDoctrine($backend)
    {
        $config = $this->parser->parse("
publero_template_mailer:
    gearman_servers:
        - localhost
    template_storage:
        type: doctrine
        backend: $backend
        ");

        $this->extension->load($config, $this->container);

        $this->assertTrue($this->container->hasAlias('publero_template_mailer.template.storage'));
        $this->assertTrue($this->container->hasParameter("publero_template_mailer.backend.$backend"));
        $this->assertEquals('publero_template_mailer.template.storage.doctrine', (string) $this->container->getAlias('publero_template_mailer.template.storage'));

        $definition = $this->container->getDefinition('publero_template_mailer.template.storage.doctrine');
        $this->assertNotNull($definition->getArgument(2));
    }

    public function testTemplateStorageConfigurationDoctrineNoBackend()
    {
        $config = $this->parser->parse('
publero_template_mailer:
    gearman_servers:
        - localhost
    template_storage:
        type: doctrine
        ');

        $this->extension->load($config, $this->container);

        $this->assertTrue($this->container->hasAlias('publero_template_mailer.template.storage'));
        $this->assertTrue($this->container->hasParameter("publero_template_mailer.backend.orm"));
        $this->assertEquals('publero_template_mailer.template.storage.doctrine', (string) $this->container->getAlias('publero_template_mailer.template.storage'));

        $definition = $this->container->getDefinition('publero_template_mailer.template.storage.doctrine');
        $this->assertNotNull($definition->getArgument(2));
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The value "invalid" is not allowed for path "publero_template_mailer.template_storage.backend". Permissible values: "orm", "mongodb"
     */
    public function testTemplateStorageConfigurationDoctrineInvalidBackend()
    {
        $config = $this->parser->parse('
publero_template_mailer:
    gearman_servers:
        - localhost
    template_storage:
        type: doctrine
        backend: invalid
        ');

        $this->extension->load($config, $this->container);
    }

    public function backendProvider()
    {
        return array(
            array('orm'),
            array('mongodb'),
        );
    }
}
 