<?php

/**
	Базовый класс для генерации превьюшек по URL через CutyCapt
	Сайт утилиты: http://cutycapt.sourceforge.net/
	Документация по установке на Ubuntu: http://mattaustin.me.uk/2009/01/installing-thummer-on-ubuntu-804-lts-server/

	Настройки:
		tools.cutycap.bin = CutyCapt

 -----------------------------------------------------------------------------
 Usage: CutyCapt --url=http://www.example.org/ --out=localfile.png            
 -----------------------------------------------------------------------------
  --help                         Print this help page and exit                
  --url=<url>                    The URL to capture (http:...|file:...|...)   
  --out=<path>                   The target file (.png|pdf|ps|svg|jpeg|...)   
  --out-format=<f>               Like extension in --out, overrides heuristic 
  --min-width=<int>              Minimal width for the image (default: 800)   
  --min-height=<int>             Minimal height for the image (default: 600)  
  --max-wait=<ms>                Don't wait more than (default: 90000, inf: 0)
  --delay=<ms>                   After successful load, wait (default: 0)     
  --user-style-path=<path>       Location of user style sheet file, if any    
  --user-style-string=<css>      User style rules specified as text           
  --header=<name>:<value>        request header; repeatable; some can't be set
  --method=<get|post|put>        Specifies the request method (default: get)  
  --body-string=<string>         Unencoded request body (default: none)       
  --body-base64=<base64>         Base64-encoded request body (default: none)  
  --app-name=<name>              appName used in User-Agent; default is none  
  --app-version=<version>        appVers used in User-Agent; default is none  
  --user-agent=<string>          Override the User-Agent header Qt would set  
  --javascript=<on|off>          JavaScript execution (default: on)           
  --java=<on|off>                Java execution (default: unknown)            
  --plugins=<on|off>             Plugin execution (default: unknown)          
  --private-browsing=<on|off>    Private browsing (default: unknown)          
  --auto-load-images=<on|off>    Automatic image loading (default: on)        
  --js-can-open-windows=<on|off> Script can open windows? (default: unknown)  
  --js-can-access-clipboard=<on|off> Script clipboard privs (default: unknown)
  --print-backgrounds=<on|off>   Backgrounds in PDF/PS output (default: off)  
  --zoom-factor=<float>          Page zoom factor (default: no zooming)       
  --zoom-text-only=<on|off>      Whether to zoom only the text (default: off) 
  --http-proxy=<url>             Address for HTTP proxy server (default: none)
 -----------------------------------------------------------------------------
  <f> is svg,ps,pdf,itext,html,rtree,png,jpeg,mng,tiff,gif,bmp,ppm,xbm,xpm    
 -----------------------------------------------------------------------------
 http://cutycapt.sf.net - (c) 2003-2010 Bjoern Hoehrmann - bjoern@hoehrmann.de


*/

class bors_tools_cutycapt
{
	static function make($url, $args = array())
	{
		if($target_image_file = defval($args, 'out'))
			$out = $target_image_file;
		else
			$out = tempnam(config('cache_dir'), 'cutycapt');

		$CutyCapt = config('tools.cutycap.bin', 'CutyCapt');
		$cmd = array("$CutyCapt");
		$cmd[] = "--url=".escapeshellcmd($url);
		$cmd[] = "--out=".escapeshellcmd($out);
		if($width = defval($args, 'width'))
			$cmd[] = "--min-width=".escapeshellcmd($width);

        system(join(' ', $cmd));

		if(!file_exists($out))
			return false;

		if(!$target_image_file)
		{
			$image = file_get_contents($out);
			unlink($out);
			return $image;
		}

		return true;
	}

	static function image($url, $args = array())
	{
		
	}
}
