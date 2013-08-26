<?php

/*
 * This file is part of the PubleroTemplateMailerBundle package.
 *
 * (c) Tomas Pecserke <tomas@pecserke.eu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Publero\TemplateMailerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('publero_template_mailer');

        $rootNode
            ->children()
                ->arrayNode('client')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->append($this->addMailerClientNode('plain'))
                        ->append($this->addMailerClientNode('template'))
                        ->append($this->addRemoteStorageClientNode())
                    ->end()
                ->end()
                ->append($this->addGearmanServersNode())
                ->append($this->addTemplateStorageNode())
            ->end()
        ;

        return $treeBuilder;
    }

    /**
     * @return ArrayNodeDefinition
     */
    public function addTemplateStorageNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('template_storage');

        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->enumNode('type')->values(array(null, 'doctrine', 'service'))->defaultNull()->end()
                ->enumNode('backend')->values(array('orm', 'mongodb'))->defaultValue('orm')->end()
                ->scalarNode('id')->defaultNull()->end()
                ->scalarNode('template_processor')->defaultNull()->end()
            ->end()
            ->validate()
                ->ifTrue(function($v) {
                    return 'doctrine' === $v['type'] && empty($v['backend']);
                })
             ->thenInvalid('doctrine template storage must have configured backend')
            ->end()
            ->validate()
                ->ifTrue(function($v) {
                    return 'service' === $v['type'] && empty($v['id']);
                })
                ->thenInvalid('custom template storage must have configured service id')
            ->end()
        ;

        return $node;
    }

    /**
     * @return ArrayNodeDefinition
     */
    public function addGearmanServersNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('gearman_servers');

        $node
            ->prototype('scalar')
            ->validate()
            ->ifTrue(function($v) {
                return !is_string($v) || !preg_match('/^[^:\/]*(\:\d+)?$/', $v);
            })
            ->thenInvalid('Gearman server must be in form host[:port]')
            ->end()
            ->end()
            ->defaultValue(array())
        ;

        return $node;
    }

    /**
     * @param string $mailerName
     * @return ArrayNodeDefinition
     */
    public function addMailerClientNode($mailerName)
    {
        $builder = new TreeBuilder();
        $node = $builder->root($mailerName . '_mailer');

        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->enumNode('type')->values(array('gearman', 'service'))->defaultValue('gearman')->end()
                ->append($this->addGearmanServersNode())
                ->scalarNode('function_name')
                    ->defaultValue('publero_template_mailer.send.' . $mailerName)
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('id')->defaultNull()->end()
            ->end()
        ;

        $this->appendCustomServiceIdValidation($node, "custom $mailerName mailer client must have specified service id");

        return $node;
    }

    /**
     * @return ArrayNodeDefinition
     */
    public function addRemoteStorageClientNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('remote_storage');

        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->enumNode('type')->values(array('gearman', 'service'))->defaultValue('gearman')->end()
                ->append($this->addGearmanServersNode())
                ->scalarNode('upload_function_name')
                    ->defaultValue('publero_template_mailer.template.upload')
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('remove_function_name')
                    ->defaultValue('publero_template_mailer.template.remove')
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('id')->defaultNull()->end()
            ->end()
        ;

        $this->appendCustomServiceIdValidation($node, 'custom remote storage client must have specified service id');

        return $node;
    }

    public function appendGearmanServersValidation(NodeDefinition $node)
    {
        $node
            ->validate()
                ->ifTrue(function($v) {
                    if (!empty($v['gearman_servers'])) {
                        return false;
                    }

                    foreach (array('plain_mailer', 'template_mailer', 'remote_storage') as $client) {
                        if (isset($v['client'][$client]['type']) && 'gearman' === $v['client'][$client]['type']) {
                            return true;
                        }
                    }

                    return  false;
                })
                ->thenInvalid('at least gearman one server must be specified')
            ->end()
        ;
    }

    /**
     * @param NodeDefinition $node
     * @param string $message
     */
    public function appendCustomServiceIdValidation(NodeDefinition $node, $message)
    {
        $node
            ->validate()
                ->ifTrue(function($v) {
                    return 'service' === $v['type'] && empty($v['id']);
                })
                ->thenInvalid($message)
            ->end()
        ;
    }
}
