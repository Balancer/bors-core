Записная книжка
===============

Соглашения наименований
-----------------------

 * keyword - ключевое слово, тэг. Каждый объект может иметь тэги, приписанные к нему
 * labels - метки. Автономные слова, к которым могут привязываться объекты.
 * synonym - синоним. Отдельное слово, к которому могут привязываться объекты.

Примеры документаций

 * http://www.symfony-project.org/doc/1_4/


Посмотреть/реализовать/протестировать
----------------------

 * http://highload.com.ua/index.php/2010/07/09/gearman-и-php-асинхронные-задачи/
 * http://highload.com.ua/index.php/2010/01/26/rediska-php-client-for-redis/
 * http://balancer.ru/xbenchmarks/cache?loops=1000&class=bors_cache_apc
 * https://sites.google.com/a/van-steenbeek.net/archive/php_pcntl_fork
 * http://wiki.triangle-solutions.com/index.php/PEAR_Benchmark
 * http://www.linux.org.ru/forum/web-development/5128695
 * http://docs.jquery.com/Plugins/Autocomplete/autocomplete#url_or_dataoptions

MongoDB
-------

 * http://highload.com.ua/index.php/2010/04/24/делаем-ленту-обновлений-на-mongodb-php/
 * http://highload.com.ua/index.php/2010/05/27/полнотекстовый-поиск-в-mongodb-используя-sphinx/
 * http://code.google.com/p/rock-php/wiki/rock_mongo#Screenshots
 * http://www.phpmoadmin.com/
 * http://www.php.net/manual/en/mongocollection.find.php
 * http://www.mongodb.org/display/DOCS/PHP+Language+Center#

spl_autoload_register  — Register given function as __autoload() implementation
-------------------------------------------------------------------------------

http://us3.php.net/manual/en/function.spl-autoload-register.php

get_browser — Tells what the user's browser is capable of
http://php.net/manual/en/function.get-browser.php

sys_getloadavg — Gets system load average
http://www.php.net/manual/en/function.sys-getloadavg.php

A HTML DOM parser written in PHP5+ let you manipulate HTML in a very easy way!
http://simplehtmldom.sourceforge.net/

APC/memcached/etc - performance
-------------------------------

 * http://www.mysqlperformanceblog.com/2006/08/09/cache-performance-comparison/
 * http://blogs.sun.com/natarajan/entry/how_to_check_your_apc
 * http://www.mysqlperformanceblog.com/2006/09/27/apc-or-memcached/

Полезные ресурсы
----------------

 * http://www.phpbench.com/
 * http://avnetlabs.com/php/php-framework-comparison-benchmarks
 * http://www.php.lt/benchmark/phpbench.php

Итераторы
---------

 * http://jacobsantos.com/2006/general/patterns-for-php-iterator/
 * http://articles.sitepoint.com/article/php5-standard-library
 * http://www.devshed.com/c/a/PHP/Iterators-in-the-Simplest-Sense-Traversing-Data-Structures-in-PHP-5/3/

OpenID и др. системы авторизации
--------------------------------

 * http://habrahabr.ru/blogs/wordpress/93658/
 * http://shakin.ru/other/social-buttons.html
 * http://habrahabr.ru/blogs/webdev/97380/
 * http://habrahabr.ru/tag/loginza/
 * https://loginza.ru/api-overview
 * http://balancer.endofinternet.net/bors-post-redirector.php?go=http%3A%2F%2Fbalancer.endofinternet.net%2Ftests%2Floginza%2F%3Fxx%3Dyy
 * http://forum.loginza.ru/viewtopic.php?f=6&t=2&p=35&hilit=%D0%B0%D0%B2%D0%B0%D1%82%D0%B0%D1%80#p35
 * http://balancer.ru/personal/avatars/
 * http://habrahabr.ru/tag/facebook/page4/
 * http://habrahabr.ru/tag/vkonakte/
 * http://habrahabr.ru/tag/вконтакте/
 * http://habrahabr.ru/blogs/php/92518/
 * http://www.phpbbguru.net/community/topic28955.html

JS-оптимизация
--------------

 * Lazy Loading Asyncronous Javascript http://friendlybit.com/js/lazy-loading-asyncronous-javascript/ 
 * NBL – a tiny non-blocking JavaScript lazy loader http://berklee.github.com/nbl/
 * Use jQuery with Google Analytics to Track Clicks on Outgoing Links From Your Site. http://think2loud.com/use-jquery-with-google-analytics-to-track-clicks-on-outgoing-links-from-your-site/
 * gaTracker - Google Analytics Integration for jQuery http://plugins.jquery.com/project/gaTracker

JS-фреймворки
-------------

 * http://www.prototypejs.org/
 * Google Libraries API - Developer's Guide http://code.google.com/intl/ru/apis/libraries/devguide.html 

jQuery
------

 * http://docs.jquery.com/UI/Tabs
 * HashMask http://lab.arc90.com/2009/07/09/hashmask-another-more-secure-experiment-in-password-masking/

Описание проекта
----------------

project.name — не уникальное. У одного проекта может быть более одного репозитория 				Является ключом для прописи роутинга.
routing.php — собственно роутинг. Предварительно надо задать хост и корень (base).
routes.conf — /planes/{id}/?	/* def: project_plane_view */

static function route() { '{project_base}/planes/{id}/'; }
