<?php

/**
	Модель с внешним JSON-бэкендом

	В качестве ID принимает полный bors-uri ресурса:
	bors://project-name/class_name/id


*/

class bors_models_rpc_json extends bors_model
{
	function storage_engine() { return 'bors_storage_rpc_json'; }
}
