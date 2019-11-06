<?php
namespace App\Db;

/**
 * Factory that creates real DB connections from connection strings
 */
class PdoConnectionFactory {
	/**
	 * @var string
	 */
	public $connections;

	/**
	 * @param string[] $connections
	 */
	public function __construct(array $connections) {
		$this->connections = $connections['connections'];
	}

	/**
	 * @param string $name
	 * @throws \InvalidArgumentException
	 * @return \PDO
	 */
	public function getConnection(string $name): \PDO {
		if (!isset($this->connections[$name])) {
			throw new \InvalidArgumentException("Connection `$name` not configured");
		}
		$url = parse_url($this->connections[$name]['dsn']);
		$driverName = $url['scheme'] ?? '';
		if (strlen($driverName) <= 0) {
			throw new \InvalidArgumentException("Url must be string in format 'driverName://driverProperties'");
		}
		$host = $url['host'] ?? '';
		$dbName = trim($url['path'] ?? '', '/');
		$port = $url['port'] ?? '';
		$params = array(
			'host=' . $host,
			'dbname=' . $dbName,
		);

		if ($port) {
			$params[] = 'port=' . $port;
		}
		$params = array_merge($params, explode('&', $url['query'] ?? ''));
		$dsn = $driverName . ':' . implode(';', $params);

		$options = null;
		if (in_array('persistent', $params)) {
			$options = [\PDO::ATTR_PERSISTENT => true];
		}

		$connection = new \PDO($dsn, $url['user'] ?? '', $url['pass'] ?? '', $options);
		$connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		$connection->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_OBJ);
		$connection->_driverName = $driverName;

		if ($driverName == 'mysql') {
			$offset = timezone_transitions_get(timezone_open(date_default_timezone_get()), time());
			$offset = $offset[0]['offset'] / 3600;
			$offset = ($offset < 0 ? '' : '+') . $offset;
			$connection->exec("SET time_zone = '{$offset}:00'");
			$connection->exec("SET NAMES utf8");
		}

		return $connection;
	}
}
