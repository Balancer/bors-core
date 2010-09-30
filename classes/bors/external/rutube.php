<?php

class bors_external_youtube extends bors_object
{
	function html($width, $height)
	{
		$id = $this->id();

		return "<OBJECT width=\"$width\" height=\"$height\">"
			."<PARAM name=\"movie\" value=\"http://video.rutube.ru/$id\"></PARAM>"
			."<PARAM name=\"wmode\" value=\"window\"></PARAM>"
			."<PARAM name=\"allowFullScreen\" value=\"true\"></PARAM>"
			."<EMBED src=\"http://video.rutube.ru/$id\" type=\"application/x-shockwave-flash\" wmode=\"window\" width=\"$width\" height=\"$height\" allowFullScreen=\"true\" ></EMBED>"
			."</OBJECT>";
	}
}
