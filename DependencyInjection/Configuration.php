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
                ->arrayNode('gearman_servers')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->prototype('scalar')
                        ->validate()
                            ->ifTrue(function($v) {
                                return !is_string($v) || !preg_match('/^[^:\/]*(\:\d+)?$/', $v);
                            })
                            ->thenInvalid('Gearman server must be in form host[:port]')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('template_storage')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->enumNode('type')->values(array(null, 'doctrine', 'service'))->defaultNull()->end()
                        ->enumNode('backend')->values(array('orm', 'mongodb'))->defaultValue('orm')->end()
                        ->scalarNode('id')->defaultNull()->end()
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
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
