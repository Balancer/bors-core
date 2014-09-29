<?php

class bors_layouts_bootstrap3_dropdown extends bors_layouts_html_dropdown
{
	function html_code()
	{
//		return "<div class=\"dropdown\">\n".self::draw_dropdown($this->args('menu'))."\n</div>";
		return self::draw_dropdown($this->args('menu'));
	}

	static function draw_dropdown($menu, $level=0)
	{
		$html = array();

		foreach($menu as $title => $submenu)
		{
			// Если указан параметр url, то это — прямая ссылка на корневой элемент меню
			if(!empty($submenu['url']))
			{
				$url = $submenu['url'];
				unset($submenu['url']);
			}
			else
				$url = '#';

			$indent = str_repeat("\t", $level);

			if($level == 0)
			{
				$dom_id = md5(rand());
				$html[] = "<a href=\"".($url?$url:'#')."\" data-toggle=\"dropdown\" class=\"dropdown-toggle\" xid=\"{$dom_id}\">{$title} <span class=\"caret\"></span></a>";
				$html[] = "<ul class=\"dropdown-menu multi-level\" role=\"menu\" xaria-labelledby=\"{$dom_id}\">";
			}
			else
			{
				$html[] = "{$indent}<ul class=\"dropdown-menu\">";
			}

			//	проход по вертикальному подменю
			foreach($submenu as $title => $items)
			{
				//	Если в нашем пунке есть подменю
				if(is_array($items))
				{
					// Рисуем подменю
					// Рекурсивно
					$html[] = "{$indent}\t<li role=\"presentation\" class=\"dropdown-submenu\"><a role=\"menuitem\" href=\"{$url}\" tabindex=\"-1\" class=\"dropdown-toggle\">{$title}</a>";
					$html[] = self::draw_dropdown(array('title' => $items), $level+2);
					$html[] = "{$indent}\t</li>";
				}
				elseif($items == '---')
				{
					$html[] = "{$indent}\t<li role=\"presentation\" class=\"divider\"></li>";
				}
				else
				{
					// Это обычный пункт меню
					// Преобразуем ссылки из одних символов в ссылки на корневой элемент сайта
					$items = preg_replace('/^(\w+)$/', '/$1/', $items);
					$html[] = "{$indent}\t<li role=\"presentation\"><a role=\"menuitem\" href=\"{$items}\">{$title}</a></li>";
				}
			}

			$html[] = "{$indent}</ul>";
		}

		return join("\n", $html);
	}

	static function __dev()
	{
		echo self::draw_dropdown([
			'Test dropdown' => [
				'Action' => '/action/',
				'More actions' => [
					'Second level link 1' => '/actions/1/',
					'Second level link 2' => '/actions/2/',
					'Second level link 3' => '/actions/3/',
				],
				'---',
				'Separated link' => '/more/',
			]
		]);
	}
}
