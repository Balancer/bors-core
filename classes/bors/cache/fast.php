<?php

// Если в конфиге указан явный класс fast-cache, используем его.
if($x = config('cache.fast_engine'))
{
	eval("class bors_cache_fast extends {$x} { }");
	return;
}

// То же самое для совсестимости. Снести после проверок. Вроде бы, только на Авиабазе используется.
if($x = config('cache_fast_engine'))
{
	eval("class bors_cache_fast extends {$x} { }");
	return;
}

// Иначе — rediska. И подумать, как обойти, когда она не нужна
if(0 && config('rediska.include'))
{
	eval("class bors_cache_fast extends bors_cache_redis { }");
	return;
}

// Если определён memcached — то он
if(config('memcached') && class_exists('Memcache'))
{
	echo "Ok!";
	eval("class bors_cache_fast extends bors_cache_memcache { }");
	return;
}

// В противном случае — заглушка

bors_use('debug_hidden_log');
debug_hidden_log('optimize-tips', "Usage bors_cache_fast without backends!");
eval("class bors_cache_fast extends bors_cache_base { }");
