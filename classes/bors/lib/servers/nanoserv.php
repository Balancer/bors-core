<?php

class bors_lib_servers_nanoserv extends bors_lib_servers_web
{
	function header($header)
	{
		$this->_web_server->Add_Header($header);
	}

	function assign_request_headers()
	{
		$headers = $this->_web_server->request_headers();
		$this->_user_agent = defval($headers, 'USER-AGENT');
		$this->_host       = defval($headers, 'HOST');
	}
}
