<?php

class bors_external_referer
{
	static function register($target_url, $referer, $object = false)
	{
		if(!$target_url || !$referer)
			return;

		if($object === false) // если на входе NULL - то уже была неудачная попытка загрузить у вызывающего, так что пропускаем
			$object = object_load($target_url);

		$norm_referer = self::normalize($referer);

		if(preg_match('!(/translate_c\?hl=|search\?q=cache:|translate\.google\.com|cgi\-bin/readmsg\?id|/translate\?hl)!', $referer))
			return;

		if(preg_match('!^https?://([^/]+)!', $referer, $m))
			$ref_domain = $m[1];
		else
		{
			debug_hidden_log('log-parser-error', "Unknown referer {$referer}", false);
			return;
		}

		$url_data = parse_url($target_url);
		$host = $url_data['host'];

		$skip_ref_domains = config('ref_count_skip_domains', array());
		$skip_target_domains = config('ref_count_skip_target_domains', array());

		if(in_array($host, $skip_target_domains))
			return;

		if(in_array($ref_domain, $skip_ref_domains))
			return;

		if($re = config('ref_count_skip_target_regexp'))
			if(preg_match($re, $target_url))
				return;

		$data = array(
			'target_class_name' => object_property($object, 'class_name'),
			'target_object_id' => object_property($object, 'id'),
			'target_page' => object_property($object, 'page'),
		);

		if($q = bors_external_search::query_extract($referer))
		{
			echo '?';
			// Это вход из поисковой системы
			$data['query'] = $q;

			$search = objects_first('bors_referer_search', $data);
			if(!$search)
			{
				$search = object_new_instance('bors_referer_search', $data);
				$search->set_create_time(time(), true);
				$ref_data = parse_url($referer);
				$search->set_search_engine($ref_data['host'], true);
				$search->set_count(0, true);
			}

			$search->set_modify_time(time(), true);
			$search->set_target_url($target_url, true);
			$search->set_search_url($referer, true);

			$search->set_count($search->count()+1, true);
			$search->store();
		}
		else
		{
			// Это переход по ссылке
			echo '>';

			$data['referer_normalized_url'] = $norm_referer;

			$ref_obj = objects_first('bors_referer_link', $data);
			if(!$ref_obj)
			{
				$ref_obj = object_new_instance('bors_referer_link', $data);
				$ref_obj->set_create_time(time(), true);
				$ref_obj->set_count(0, true);
			}

			$ref_obj->set_modify_time(time(), true);
			$ref_obj->set_target_url($target_url, true);
			$ref_obj->set_referer_original_url($referer, true);

			$ref_obj->set_count($ref_obj->count()+1, true);
			$ref_obj->store();
		}
	}

	static function normalize($url)
	{
		$url = preg_replace('!http://(www|win)\.!', 'http://', $url);
		$url = preg_replace('!\?PHPSESSID=[0-9a-f]+&!', '?', $url);
		$url = preg_replace('!#\w+$!', '', $url);
		return $url;
	}
}
