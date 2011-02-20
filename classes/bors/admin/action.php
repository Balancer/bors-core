<?php

class bors_admin_action extends bors_object
{
	function loaded() { return bors_load('bors_admin_'.$this->id(), NULL); }
}
