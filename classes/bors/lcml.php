<?php

define('MAX_EXECUTE_S', 0.5);

class bors_lcml
{
	private $_params = array();
	private static $data;
	private $output_type	= 'html';
	private $input_type		= 'bb_code';

	function __construct($params = array())
	{
		$this->_params = $params;
		bors_lcml::init();
	}

	function p($key, $def = NULL) { return empty($this->_params[$key]) ? $def : $this->_params[$key]; }
	function set_p($key, $value) { $this->_params[$key] = $value; return $this; }

	private static function memcache()
	{
		static $mch = NULL;
		if($mch)
			return $mch;

		return $mch = new BorsMemCache();
	}

	static function init()
	{
		if(!empty(bors_lcml::$data))
			return;

		bors_lcml::$data['pre_functions'] = array();
		bors_lcml::actions_load('pre', bors_lcml::$data['pre_functions']);

		bors_lcml::$data['post_functions'] = array();
		bors_lcml::actions_load('post', bors_lcml::$data['post_functions']);

		bors_lcml::$data['post_whole_functions'] = array();
		bors_lcml::actions_load('post-whole', bors_lcml::$data['post_whole_functions']);

		bors_lcml::actions_load('tags');

		if(config('lcml_sharp_markup'))
			bors_lcml::actions_load('sharp');
	}

	private static function actions_load($rel_dir, &$functions = array())
	{
		foreach(bors_dirs() as $base_dir)
			bors_lcml::_actions_load(secure_path($base_dir.'/engines/lcml/'.$rel_dir), $functions);
	}

	private static function _actions_load($dir, &$functions = array())
	{
        if(!is_dir($dir))
			return;

		$files = self::memcache()->get('lcml_actions_'.BORS_SITE.'_3:'.$dir);
		if(!$files)
		{
	        $files = array();

    	    if($dh = opendir($dir)) 
        	    while(($file = readdir($dh)) !== false)
            	    if(is_file($dir.'/'.$file))
                	    $files[] = $file;

	        closedir($dh);

			sort($files);
			self::memcache()->set($files);
		}

        foreach($files as $file) 
        {
            if(preg_match("!(.+)\.php$!", $file, $m))
            {
                include_once("$dir/$file");

                $fn = "lcml_".($ffn=substr($file, 3, -4));
				$functions[] = $fn;
            }
        }
	}

	private function functions_do($functions, $text, $type)
	{
		$t0 = $text;
		$ts = microtime(true);

		$fns_list_enabled  = config('lcml_functions_enabled',  array());
		$fns_list_disabled = config('lcml_functions_disabled', array());

		foreach($functions as $fn)
		{
			$original = $text;

			if((!$fns_list_enabled || in_array($fn, $fns_list_enabled))
				&& !in_array($fn, $fns_list_disabled)
			)
				$text = $fn($text, $this);

			if(!trim($text) && trim($original))
				debug_hidden_log('lcml-error', "Drop on $fn convert '$original'");
		}

		if(($long = microtime(true) - $ts) > MAX_EXECUTE_S)
			debug_hidden_log('warning_lcml', "Too long ({$long}s) $type functions execute\nurl=".bors()->request()->url()."\ntext='$t0'", false);

		return $text;
	}


	private $params;
	function set_params($params) { $this->params = $params; }

	static function is_tag_enabled($tag_name, $default_enabled = true)
	{
		// Если тэг разрешён явно, то всё ок.
		if(config('lcml.tag.'.$tag_name.'.enable'))
			return true;

		$disabled_tags = config('lcml_tags_disabled',  array());
		$enabled_tags  = config('lcml_tags_enabled',  array());

		// Если тэг разрешён явно, то всё ок.
		if(in_array($tag_name, $enabled_tags))
			return true;

		// Если тэг отсутствует в запрещённых, то…
		if(!in_array($tag_name, $disabled_tags))
		{
			// Если при этом есть список разрешённых тэгов, то по умолчанию всё запрещено
			if($enabled_tags)
				return false;

			// В противном случае используем параметр указания, как реагировать на неявное:
			return $default_enabled;
		}

		return false;
	}

	function parse($text, $params = array())
	{
		$params = array_merge($this->params, $params);

		$text = str_replace("\r", '', $text);

		if(!trim($text))
			return '';

		if($this->_params['level'] == 1)
			$text = "\n{$text}\n";

		$need_prepare = popval($this->_params, 'prepare');

		if($this->_params['level'] == 1
			&& !config('lcml_cache_disable')
			&& config('cache_engine')
			&& empty($params['nocache'])
		)
		{

			$cache = new Cache();
			if($cache->get('lcml-cache-v'.config('lcml.cache_tag'), $text))
				return $cache->last();
		}
		else
			$cache = NULL;

		$GLOBALS['lcml']['params'] = $this->_params;
		$GLOBALS['lcml']['params']['html_disable'] = $this->p('html_disable');
		$GLOBALS['lcml']['cr_type'] = @$params['cr_type'];
//		echo "cr-type = {$GLOBALS['lcml']['cr_type']}\n";

		if($this->_params['level'] == 1 || $need_prepare)
		{
			$text = bors_lcml::parsers_do('pre', $text);
			$text = $this->functions_do(bors_lcml::$data['pre_functions'], $text, 'pre');
		}


		if($this->_params['level'] == 1)
			$this->output_type = popval($params, 'output_type', 'html');

		$mask = str_repeat('.', bors_strlen($text));

		// ******* Собственно, главная часть — обработка тэгов *******
		$ts = microtime(true);
		$text = lcml_tags($t0 = $text, $mask, $this);
		if(($long = microtime(true) - $ts) > MAX_EXECUTE_S)
			debug_hidden_log('warning_lcml', "Too long ({$long}s) tags execute\nurl=".bors()->request()->url()."\ntext='$t0'", false);

		if($this->p('only_tags'))
			return $cache ? $cache->set($text, 86400) : $text;

		if(config('lcml_sharp_markup'))
		{
			require_once('engines/lcml/sharp.php');
			$text = lcml_sharp($text, $mask);
		}

		$result = "";
		$start = 0;
		$can_modif = true;

		for($i=0, $stop=bors_strlen($text); $i<$stop; $i++)
		{
			if($mask[$i] == 'X')
			{
				if($can_modif)
				{
					if($start != $i)
					{
						$code = bors_substr($text, $start, $i-$start);
						$result .= bors_lcml::parsers_do('post', bors_lcml::functions_do(bors_lcml::$data['post_functions'], $code, 'post'));
					}

					$start = $i;
					$can_modif = false;
				}
			}
			else
			{
				if(!$can_modif)
				{
					$result .= bors_substr($text, $start, $i-$start);
					$start = $i;
					$can_modif = true;
				}
			}
		}

		if($start < bors_strlen($text))
		{
//			if(config('is_developer')) var_dump($can_modif, $this->_params['level'], $start, bors_strlen($text), $text);
			// Внимание! Уровень 1 тут добавлять нельзя. Проблема:
			// [quote]...lcml-код... [/quote]
			// Внутри quote level > 1, но после обработки для всего блока quote can_modif в маске == false
			// Нужно искать некорректный вызов post-функций в других местах. При правильном проектировании
			// этот вызов может быть только один раз, потом — блокируется.

			if($can_modif/* && $this->_params['level'] == 1*/)
			{
				$code = bors_substr($text, $start, bors_strlen($text) - $start);
				$result .= bors_lcml::parsers_do('post', $this->functions_do(bors_lcml::$data['post_functions'], $code, 'post'));
			}
			else
				$result .= bors_substr($text, $start, bors_strlen($text) - $start);
		}

		$text = $result;

		if($this->_params['level'] == 1)
		{
			if(preg_match("/^\n/", $text))
				$text = substr($text, 1);
			if(preg_match("/\n$/", $text))
				$text = substr($text, 0, strlen($text)-1);

			$text = $this->functions_do(bors_lcml::$data['post_whole_functions'], $text, 'post_whole');
		}

		return $cache ? $cache->set($text, 86400) : $text;
	}

	function output_type() { return $this->output_type; }

	function parsers_do($type, $text)
	{
		$t0 = $text;
		$ts = microtime(true);

		static $parser_classes = array();
		if(empty($parser_classes[$type]))
		{
			$classes = array();
			foreach(bors_dirs() as $dir)
			{
				if(file_exists($f = $dir.'/engines/lcml/'.$type.'_parsers.list'))
					foreach(file($f) as $s)
					{
						list($prio, $class_name) = preg_split("/\s+/", $s);
						if($prio && $class_name)
							$classes[$prio.':'.$class_name] = new $class_name(NULL);
					}
			}
			ksort($classes);
			$parser_classes[$type] = $classes;
		}

		foreach($parser_classes[$type] as $foo => $parser)
			$text = $parser->parse($text, $this);

		if(($long = microtime(true) - $ts) > MAX_EXECUTE_S)
			debug_hidden_log('warning_lcml', "Too long ({$long}s) $type parsers execute\nurl=".bors()->request()->url()."\ntext='$t0'", false);

		return $text;
	}

	// Постобработка выводимого HTML, имеющего разметку, которая должна быть
	// активна не в момент компиляции, а в момент показа. Например, указания
	// системе на подключение необходимых JS и CSS.
	// Фиксированный хардкод, расширение пока не предусмотрено. Теоретически
	// может быть реализовано через будущий механизм хуков.

	static function output_parse($html_bb)
	{
		// Обработка <!--[[use ...]]-->
		$html_bb = preg_replace_callback(array(
				'/<!--\[\[use\s+(\w+)\s*=\s*"([^"]+?)\s*"\s*\]\]-->/',
				"/<!--\[\[use\s+(\w+)\s*=\s*'([^']+?)\s*'\s*\]\]-->/",
				"/<!--\[\[use\s+(\w+)\s*=\s*([^\]]+?)\s*\]\]-->/",
			), 'bors_lcml::_output_parse_use', $html_bb);

		return $html_bb;
	}

	// Генерация html-кода для предыдущей функции
	static function make_use($type, $arg)
	{
		return "<!--[[use $type=\"$arg\"]]-->";
	}

	static function _output_parse_use($matches)
	{
		list($origin, $type, $arg) = $matches;
		switch($type)
		{
			case 'js':
				template_js_include($arg);
				break;
			case 'css':
				template_css($arg);
				break;
		}

		return '';
	}

	static function __unit_test($suite)
	{
		// Одиночные теги тестируются в соответствующих классах. Так что нам тут их проверять не надо
		// нужно проверять сочетания.
		$code = '[b][i]italic-bold[/i][/b]';
		$suite->assertEquals('<strong><i>italic-bold</i></strong>', lcml($code));

		$code = '[http://balancer.ru Сайт расходящихся тропок]';
		$suite->assertRegexp('#<a.+href="http://balancer.ru".*>Сайт расходящихся тропок</a>#', lcml($code));
//		Упс. Не работает. Сделать не прямой парсинг, а подмену тэга вначале, в зависимости от типа ссылки, [url или [img
//		$code = '[http://balancer.ru|[b]Сайт расходящихся тропок[/b]]';
//		$suite->assertRegexp('#<a.+href="http://balancer.ru".+>Сайт расходящихся тропок</a>#', lcml($code));

		$code = '[url http://balancer.ru|[b]Сайт расходящихся тропок[/b]]';
		$suite->assertRegexp('#<a.+href="http://balancer.ru".*><strong>Сайт расходящихся тропок</strong></a>#', lcml($code));

		$code = '[url=http://balancer.ru]Сайт расходящихся тропок[/url]';
		$suite->assertRegexp('#<a.+href="http://balancer.ru".*>Сайт расходящихся тропок</a>#', lcml($code));

		$code = '[b]Сайт расходящихся тропок: [url="http://balancer.ru"][/b]';
		$suite->assertRegexp('#<strong>Сайт расходящихся тропок: <a.+href="http://balancer.ru".*>balancer.ru</a></strong>#', lcml($code));

		$code = '[url=http://yandex.ru/yandsearch?text="оранжевые+зомби"]оранжевых зомби[/url]';
		$suite->assertRegexp('#<a rel="nofollow" href="http://yandex.ru/yandsearch\?text=&quot;оранжевые\+зомби&quot;" class="external">оранжевых зомби</a>#', lcml($code));

		// Обработка пайпов
		$code = '[url=http://www.n2yo.com/?s=25544|38348]Реалтаймовый мониторинг положения Dragin и МКС[/url]';
		$suite->assertRegexp('#<a rel="nofollow" href="http://www.n2yo.com/?s=25544|38348" class="external">Реалтаймовый мониторинг положения Dragin и МКС</a>#', lcml($code));

		$code = '[http://www.ru|WWW.RU]';
		$suite->assertRegexp('#<a rel="nofollow" href="http://www.ru" class="external">WWW.RU</a>#', lcml($code));

		$code = '[http://www.ru WWW.RU]';
		$suite->assertRegexp('#<a rel="nofollow" href="http://www.ru" class="external">WWW.RU</a>#', lcml($code));

		$code = '[/test/|Ещё тест]';
		$suite->assertRegexp('#<a.*href="/test/".*>Ещё тест</a>#', lcml($code));

		$code = '[/test/ Ещё тест]';
		$suite->assertRegexp('#<a.*href="/test/".*>Ещё тест</a>#', lcml($code));

		$code = '[test/ Ещё тест]';
		$suite->assertRegexp('#<a.*href="test/".*>Ещё тест</a>#', lcml($code));

		$code = '[test/|Ещё тест]';
		$suite->assertRegexp('#<a.*href="test/".*>Ещё тест</a>#', lcml($code));

	// Внутренние ошибочные теги не парсятся
		$code = '[b][i]italic[/b]bold[/i]';
		$suite->assertEquals('<strong>[i]italic</strong>bold[/i]', lcml($code));

		// Переводы строк.
		$code = "Раз, два, три, четыре, пять\nВышел зайчик погулять";
		$suite->assertEquals("Раз, два, три, четыре, пять<br />\nВышел зайчик погулять", trim(lcml_bb($code))); //?WTF? Это же не BB.

		// Проверки, использующие специфичные локальне ресурсы balancer.ru
		if(config('is_balancer_ru_tests'))
		{
			$code = '[url=http://balancer.ru/forum/punbb/viewtopic.php?pid=1248520#p1248520][img]http://balancer.ru/cache/img/forums/0708/468x468/1024x768-img_0599.jpg[/img][/url]';
			$suite->assertEquals("===", lcml($code));
		}

		self::output_parse('<!--[[use js="/_bors3rdp/js/foo.test.js"]]-->');
		$suite->assertContains('/_bors3rdp/js/foo.test.js', base_object::template_data('js_include'));
	}
}
