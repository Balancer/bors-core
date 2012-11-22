<?php

function debug_trace($skip = 0, $html = NULL, $level = -1, $traceArr = NULL)
{
	$MAXSTRLEN = 128;

	if(is_null($html))
	{
		bors_function_include('debug/in_console');
		$html = !debug_in_console();
	}

	if($html)
		$s = '<pre align="left">';
	else
		$s = '';

	if(is_null($traceArr))
		$traceArr = debug_backtrace();

	for($i = 1; $i <= $skip; $i++)
		array_shift($traceArr);

	if(is_numeric($level) && $level > 0)
		$traceArr = array_slice($traceArr, 0, $level);
//	if(is_numeric($level) && $level < 0)
//		$traceArr = array_slice($traceArr, -$level);

	$tabs = 0; //sizeof($traceArr)-1;
	for($pos=0, $stop=sizeof($traceArr); $pos<$stop; $pos++)
	{
		$arr = $traceArr[$stop-$pos-1];
		$indent = '';
		for ($i=0; $i < $tabs; $i++)
			$indent .= $html ? '&nbsp;' : ' ';

		$Line = (isset($arr['line'])? $arr['line'] : "unknown");
		$File = (isset($arr['file'])? $arr['file'] : "unknown");

		$s .= "\n$indent";

		$tabs++;
		if($html)
			$s .= '<span style="font-family:monospace;size:9pt;padding:0;margin:0">';

		if(isset($arr['class']))
			$s .= $arr['class'].'.';

		$args = array();
		if(!empty($arr['args']))
		{
			foreach($arr['args'] as $v)
			{
				if (is_null($v)) $args[] = 'null';
				else if (is_array($v)) $args[] = 'Array['.sizeof($v).']';
				else if (is_object($v)) $args[] = 'Object:'.get_class($v);
				else if (is_bool($v)) $args[] = $v ? 'true' : 'false';
				else
				{
					$v = (string) @$v;
					$str = htmlspecialchars(substr($v,0,$MAXSTRLEN));
					if (strlen($v) > $MAXSTRLEN) $str .= '...';
						$args[] = "\"".$str."\"";
				}
			}
		}
		if($html)
			$s .= "<b>{$arr['function']}</b>";
		else
			$s .= $arr['function'];

		$targs = implode(', ',$args);
		if($html)
		{
			$targs = preg_replace('/(".+?")/', '<font color="green">$1</font>', $targs);
			$targs = preg_replace('/(true|false)/', '<font color="brown">$1</font>', $targs);
			$s .= '<font color="#999">(</font>'.$targs.'<font color="#999">)</font>';
		}
		else
			$s .= '('.$targs.')';

		if($html)
			$s .= '</span>';
		$s .= "\n";

		if($html)
			$s .= "$indent<span style=\"font-size:8pt;margin:0;padding:0;color:#ccc\">{$File}:{$Line}</span>";
		else
			$s .= "[{$File}:{$Line}]";
	}

	if($html)
		$s .= '</pre>';

	return $s;
}
