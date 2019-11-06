<?php
namespace App\Db\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration of a DB connection
 */
class Configuration implements ConfigurationInterface
{
	public function getConfigTreeBuilder()
	{
		$tb = new TreeBuilder();
		$connectionsNode = new ArrayNodeDefinition('connections');
		$connectionsNode
			->example(array(
				array('name' => 'master', 'dsn' => 'mysql://user:password@host/db?option=value'),
				array('name' => 'slave', 'dsn' => 'mysql://user:password@slave-host/db?option=value'),
			))
			->normalizeKeys(false)
			->useAttributeAsKey('name')
			->arrayPrototype()
			->children()
            ->scalarNode('name')->end()
			->scalarNode('dsn')->end()
			->end()
			->end()
			->end();

		$tb->root('db_config')
			->children()
			->booleanNode('log_queries')
			->defaultFalse()
			->end()
			->append($connectionsNode)
			->end()
		;

		return $tb;
	}
}
