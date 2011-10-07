<?php

$twig_inc = config('twig_include_dir');
require_once($twig_inc.'/Autoloader.php');
Twig_Autoloader::register();

class bors_templates_twig
{
	static function fetch($template, $data)
	{
		$cache_dir = config('cache_dir').'/twig-'.config('internal_charset').'/';
		mkpath($cache_dir);

		$template_file = preg_replace('!tpl://!', '/', $template);

		$paths = file_exists($template_file) ? array(dirname($template_file)) : array();
		foreach(bors_dirs() as $dir)
			if(is_dir($dir = "$dir/templates/"))
				$paths[] = $dir;

		$loader = new Twig_Loader_Filesystem($paths);
		$twig = new Twig_Environment($loader, array(
			'cache' => $cache_dir,
			'auto_reload' => true,
		));

		$twig->addExtension(new bors_twig_extension());

//		$template = $twig->loadTemplate(basename($template_file));
		$template = $twig->loadTemplate($template_file);
		$result = $template->render($data);

		return $result;
	}
}

class bors_twig_extension extends Twig_Extension
{
	function getFilters()
	{
		return array(
			'lcml_bbh' => array('lcml_bbh', false),
		);
	}
	function getName() { return 'project'; }
	function getTokenParsers() { return array(new bors_twig_parser_module()); }
}

class bors_twig_parser_module extends Twig_TokenParser
{
	public function getTag() { return 'module'; }

	function parse(Twig_Token $token)
	{
		$lineno = $token->getLine();

		$params = array();
		do
		{
    		$next = $this->parser->getStream()->next()->getValue();
			if($next)
			{
				$operator = $this->parser->getStream()->expect(Twig_Token::OPERATOR_TYPE)->getValue();
//				$value = $this->parser->getExpressionParser()->parseExpression();
			    $expr = $this->parser->getExpressionParser()->parseExpression();
//			    list(, $values) = $this->parser->getExpressionParser()->parseMultitargetExpression();
				$params[$next] = $expr;
			}
		} while($next);

//		var_dump($params);
//		echo "???";

	    return new bors_twig_node_module($token->getLine(), $params);
	}

}

class bors_twig_node_module extends Twig_Node
{
	protected $params;

	public function __construct($lineno, $params)
	{
		parent::__construct($lineno);
		$this->params = $params;
	}

	public function __toString() { return get_class($this); }

	function compile(Twig_Compiler $compiler)
	{
//		var_dump($this->params);

		$class_name = $this->params['class'];
		$object_id  = $this->params['id'];
		unset($this->params['class']);
		unset($this->params['id']);

/*		foreach($this->params as $key => $expr)
		{
		    $compiler
				->addDebugInfo($this)
				->write("\$obj = object_load();")
			;
		}
*/
	    $compiler
			->addDebugInfo($this)
			->write("echo object_load(")
			->subcompile($class_name)
			->write(",")
			->subcompile($object_id)
			->write(")->body();")
			;
	}
}
