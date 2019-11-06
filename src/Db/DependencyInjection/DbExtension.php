<?php
namespace App\Db\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Container extension to configure DB connections
 * Adds the following container services:
 *   connection_manager - DB connection manager holding all DB connections.
 *   {dsn_name}_connection - specific DB connection with credentials read from configuration.
 *
 * Example:
 * db_config:
 *   connections:
 *     - { name: master, dsn: 'mysql://user1:pwd1@host1/db?codepage=cp1251&forceNewLink=1&readOnly=1&description=wow' }
 *     - { name: slave, dsn: 'mysql://user2:pwd2@host2:4404/db?codepage=cp1251&forceNewLink=1&readOnly=1&description=wow' }
 *     - { name: bulletin_statistics, dsn: 'mysql://user3:pwd3@host2:4048/bul_stats?codepage=cp1251&forceNewLink=1&readOnly=1' }
 * In the result, you'll get the following container services defined:
 *   connection_manager, master_connection, slave_connection и bulletin_statistics_connection
 *
 */
class DbExtension extends Extension
{
    /**
     * Loads a specific configuration.
     * @param array $configs
     * @param ContainerBuilder $container A ContainerBuilder instance
     * @throws \Exception
     */
	public function load(array $configs, ContainerBuilder $container): void
	{
		$configuration = $this->getConfiguration($configs, $container);
		$config = $this->processConfiguration($configuration, $configs);
		$loader = new YamlFileLoader($container, new FileLocator(dirname(__DIR__).'/Resources/config'));
		$loader->load('services.yml');
		$container->setParameter('db.config', $config);
		foreach ($config['connections'] as $name => $connection) {
			$container->register($name.'_connection')
				->setClass('object')
                ->setPublic(true)
				->setFactory([
					new Reference('connection_manager'),
					'getConnection',
				])
				->setArguments(array($name));
		}
	}

    /**
     * @return string
     */
	public function getAlias(): string
	{
		return 'db_config';
	}
}
