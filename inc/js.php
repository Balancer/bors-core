<?
	function str2js($text)
	{
		$out = "with(document){";

		$skip = false;

	    foreach(split("\n", $text) as $s)
		{
			if($skip)
			{
				if(preg_match('!^(.*)</script>$!', $s, $m))
				{
					$out .= $m[1]."\n";
					$skip = false;
				}
				else
					$out .= $s."\n";
			}
			else
			{
				if(preg_match('!^<script>(.*)$!', $s, $m))
				{
					$out .= $m[1]."\n";
					$skip = true;
				}
				else
			        $out .= "write(\"".addslashes($s)."\");\n";
			}
		}
		
		$out = preg_replace('!<script>(.+?)</script>!', "\"+$1+\"", $out);

		return $out."}";
	}
