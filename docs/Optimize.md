Советы по оптимизации
=====================

Можно вручную в своём конфиге заполнить переменную
config('cache.stash.pool'), тогда серверу не нужно будет тратить время на
автоопределение кеша. Например (BORS_SITE/config.php):

	$driver = new Stash\Driver\Redis();
	$servers = [['server' => '127.0.0.1', 'port' => 6379, 'ttl' => 86400]];
	$driver->setOptions(['servers' => $servers]);
	$pool = new Stash\Pool($driver);
	config_set('cache.stash.pool', $pool);
