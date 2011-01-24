<?php

/**
	Класс парсинга трансляций imhonet.ru в livejournal.com
*/

/*
<table cellpadding="3" cellspacing="0" border="0">
<tr>
<td>Давно не читал качественной _научной_ фантастики. А уж про _современную_ - давно спор идёт не умер ли жанр вовсе. Нет, не умер, оказывается. Действительно, НФ. Качественная НФ. С неожиданной идеей, хорошим сюжетом, вполне живыми персонажами. Для себя оценил где-то на 8⅔ из 10 (ИМХОНЕТ спрогнозировал 8,6), но поскольку дробной оценки не поставить, решил оценить на 9.<br /><br />Идея _такого_ освоения галактики - это, действительно, что-то новое :)</td>
</tr>
<tr><td valign="top"><br />	<img src="http://s.imhonet.ru/img/star_grey.gif" border="0" /> <nobr><br />отлично <br />	– 9</nobr><br /></td></tr>
<tr>
<td align="left" width="100%" valign="top">Книга: <a href="http://books.imhonet.ru/element/1114675/" target="_blank">Спин</a></td>
</tr>
<tr>
<td align="left" width="100%" valign="top"><a href="http://balancer.imhonet.ru/">Мой профиль</a> на <a href="http://imhonet.ru/">Имхонет</a></td>
</tr>
</table>
*/

class bors_external_parsers_imholj extends bors_object
{
	function __construct($text)
	{
		parent::__construct($text);

		$this->set_title = NULL;

		if(preg_match_all('/ #(\w+)/', $text, $m))
		{
			$this->set_keywords($m[1], false);
		}

		$this->set_text($text, false);
	}
}
