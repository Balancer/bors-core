<?php

	function make_quote($author, $message)
	{
		$InsertPostMess = "";

		$author = preg_replace('!^\[(.+?)\]$!','$1',$author);
		$author = preg_replace('!^<(.+?)>$!','$1',$author);
		$author = preg_replace('!^&lt;(.+?)&gt;$!','$1',$author);

		foreach(split(' ', 'code quote pre') as $tag)
			$message = preg_replace("!\[".$tag."[^\]]*\].+\[/".$tag."\]!is", "", $message);
			
		$message = preg_replace("!\[img](.+?)\[/img\]!i", "[img \"$1\" x64]", $message);

		if(strpos($author,' ') !== false)
		{
			$parts = preg_split("!\s+!", $author);
			$author = "";
			foreach($parts as $i)
				$author .= preg_replace("!^[^\wа-яА-Я\d]*?([\wа-яА-Я\d]).*$!u", "$1.", $i);
		}


		foreach(preg_split("!\n!", $message) as $s)
		{
			if(preg_match("!^\s*$!", $s))
			{
				$InsertPostMess.="";
				continue;
			}

			if(preg_match("!^\S*>!",$s))
			{
				$s=preg_replace("!(^\S*>)!","$1>",$s);
				$InsertPostMess.=" $s\n";
				continue;
			}

			if(preg_match("!^\S*&gt;!",$s)) // HTML
			{
				$s=preg_replace("!(^\S*&gt;)!","$1&gt;",$s);
				$InsertPostMess.=" $s\n";
				continue;
			}

			if(preg_match("!^\S*&amp;gt;!",$s)) // HTML
			{
				$s=preg_replace("!(^\S*&amp;gt;)!","$1&amp;gt;",$s);
				$InsertPostMess.=" $s\n";
				continue;
			}

			$InsertPostMess .= "$author&gt; $s\n";
		}
	
		return trim($InsertPostMess)."\n";
	}
