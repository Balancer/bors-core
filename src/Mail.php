<?php

namespace B2;

class Mail extends Page
{
	function send($email)
	{
		$body = $this->body();
		echo $body;
	}
}
