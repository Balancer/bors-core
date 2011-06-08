<?php
    function lp_snapback($txt)
    {
		if(!empty($GLOBALS['lcml']['forum_type']))
		{
			if($GLOBALS['lcml']['forum_type'] == 'punbb')
			{
				return "<small><a href=\"{$GLOBALS['lcml']['forum_base_uri']}/viewtopic.php?pid=$txt#p$txt\">$txt&#187;&#187;&#187;</a></small>";
			}

			if($GLOBALS['lcml']['forum_type'] == 'ipb')
			{
				return "<small><a href=\"{$GLOBALS['lcml']['forum_base_uri']}/index.php?act=findpost&pid=$txt\"><img src=\"{$GLOBALS['lcml']['forum_base_uri']}\/style_images/1rus/post_snapback.gif\" width=\"10\" height=\"10\" border=\"0\" alt=\"*\"></a></small>";
			}
		}

		$post = bors_load('balancer_board_post', $txt);
		if($post)
			return "[<a href=\"{$post->url_for_igo()}\">»</a>]";

		return "<small>snap: $txt</small>";
    }
