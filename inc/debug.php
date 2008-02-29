<?php

function debug_test()
{
	return bors()->user() && bors()->user()->id() == 10000;
}

	function debug_exit($message)
	{
		echo DBG_GetBacktrace();
		exit($message);
	}

	function debug_trace()
	{
		echo DBG_GetBacktrace();
	}

	function debug_xmp($text)
	{
		if(!empty($_SERVER['HTTP_HOST']))
			echo "<xmp>{$text}</xmp>\n";
		else
			echo $text;
	}

	function debug_pre($text)
	{
		if(!empty($_SERVER['HTTP_HOST']))
			echo "<xmp>{$text}</xmp>\n";
		else
			echo $text;
	}
	
	function print_d($data) { debug_xmp(print_r($data, true)); }

	function set_loglevel($n, $file=false)
	{
		$GLOBALS['log_level'] = $_GET['log_level'] = $n;
		if($file === false)
			return;
		
		$GLOBALS['echofile'] = $file;
	}
	
	function loglevel($check) { return $check <= max(@$GLOBALS['log_level'], @$_GET['log_level']); }

	function debug_only_one_time($mark, $trace=true, $times = 1)
	{
		if(@$GLOBALS['debug']['onetime'][$mark] >= $times)
			debug_exit('Second call of '.$mark);
		
		@$GLOBALS['debug']['onetime'][$mark]++;
		
		if($trace)
			debug_trace();
	}

    function echolog($message, $level=3)
    {
		$log_level = max(@$GLOBALS['log_level'], @$_GET['log_level']);
	
        if(!$log_level)
            return;

        if($log_level >= $level)
        {
            if(!empty($GLOBALS['echofile']))
            {
                $fh=fopen($GLOBALS['echofile'],"at");
//				$txt = "uri: http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}\n";
//				$txt. = "query: ".@$_SERVER['QUERY_STRING']."\n";
//				$txt .= "ref: ".@$_SERVER['HTTP_REFERER']."\n";
				$txt = "$level: $message\n";
//				if($log_level > 10)
//					$txt .= "backtrace:".print_r(debug_backtrace(),true)."\n";
//				$txt .= "-------------------------------\n";
                fputs($fh, $txt);
                fclose($fh);
				@chmod($GLOBALS['echofile'], 0666);
            }
            else
			{
				if($level<3)
				{
					if(!empty($_SERVER['HTTP_HOST']))
						echo '<span style="color: red;">';
					else
						echo "=== ";
				}
		
				if(!empty($_SERVER['HTTP_HOST']))
	                echo "<span style=\"font-size: 8pt;\">".substr($message,0,2048).(strlen($message)>2048?"...":"")."</span><br />\n";
				else
	                echo substr($message,0,2048).(strlen($message)>2048?"...":"")."\n";

				if($level<3)
				{
					if(!empty($_SERVER['HTTP_HOST']))
						echo "</span>\n";
					else
						echo " ===\n";
				}
			}
            if($level==1)
            {
                echo "Backtrace error:<br/ >\n";
                echo DBG_GetBacktrace();
            }

            if(empty($GLOBALS['echofile']))
                echo "<hr />";
        }
    }

    function debug($message,$comment='',$level=3)
    {
//        return;
		$trace = debug_backtrace();
		$caller = $trace[0];
		$file = $caller['file'];
		$line = $caller['line'];
		
        $fh=@fopen($GLOBALS['cms']['base_dir'].'/logs/debug.log','at');
        @fwrite($fh,strftime("***	%Y-%m-%d %H:%M:%S			").($comment?"$comment:\n":"{$file}[$line]\n")."$message\n----------------------\n");
        @fclose($fh);
    }

    function DBG_GetBacktrace()
    {
        $MAXSTRLEN = 64;
   
		if(!empty($_SERVER['HTTP_HOST']))
			$s = '<pre align="left">';
		else
	        $s = '';
		
        $traceArr = debug_backtrace();
        array_shift($traceArr);
        $tabs = 0; //sizeof($traceArr)-1;
        for($pos=0; $pos<sizeof($traceArr); $pos++)
        {
			$arr = $traceArr[sizeof($traceArr)-$pos-1];
            for ($i=0; $i < $tabs; $i++)
				$s .= empty($_SERVER['HTTP_HOST']) ? ' ' : '&nbsp;';
            $tabs++;
			if(!empty($_SERVER['HTTP_HOST']))
	            $s .= '<font face="Courier New,Courier">';
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
            $s .= $arr['function'].'('.implode(', ',$args).')';
			if(!empty($_SERVER['HTTP_HOST']))
				$s .= '</font>';
            $Line = (isset($arr['line'])? $arr['line'] : "unknown");
            $File = (isset($arr['file'])? $arr['file'] : "unknown");
			if(!empty($_SERVER['HTTP_HOST']))
    	        $s .= sprintf("<span style=\"font-size: 8pt;\">[<a href=\"file:/%s\">%s</a>:%d]</span>", $File, $File, $Line);
			else
    	        $s .= sprintf("[%s:%d]", $File, $Line);
            $s .= "\n";
        }    

		if(!empty($_SERVER['HTTP_HOST']))
	        $s .= '</pre>';

        return $s;
    }

    function debug_page_stat()
    {
?>
<noindex>
Новых mysql-соединений:<?echo $GLOBALS['global_db_new_connections'];?><br />
Продолженных mysql-соединений:<?echo $GLOBALS['global_db_resume_connections'];?><br />
Всего запросов <?echo $GLOBALS['global_db_queries'];?><br />
Попадений в кеш данных: <?echo $GLOBALS['global_key_count_hit'];?><br />
Промахов в кеш данных: <?echo $GLOBALS['global_key_count_miss'];?><br />
Время генерации страницы: <?
list($usec, $sec) = explode(" ",microtime());
echo ((float)$usec + (float)$sec) - $GLOBALS['stat']['start_microtime'];
?> сек.<br />
<?
	if($GLOBALS['cms']['cache_copy'])
		echo "Кешированная версия от ".strftime("%Y-%d-%m %H:%I", $GLOBALS['cms']['cache_copy']);
	else
		echo "Перекомпилированная версия";
?>
<br />
</noindex>
<?
    }
?>
