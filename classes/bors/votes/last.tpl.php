<table class="null"><tr><td>
<dl class="box">
<dt>Лучшие <?=count($best)?> сообщений форума за месяц</dt>
<dd>
<?php foreach($best_of_month as $x) if($x && $x->target()):?>
&nbsp;&nbsp;&nbsp;<img src="http://balancer.ru/_bors/i/thumb_<?=$x->score() > 0 ? 'up' : 'down'?>.gif" />&nbsp;<?=$x->target()->titled_url()?> <?=$x->target()->score_colorized()?><br/>
<?php endif ?>
</dd>
</dl>
</td><td>

<dl class="box">
<dt>Лучшие <?=count($best)?> сообщений форума за всю историю</dt>
<dd>
<?php foreach($best as $x) if($x && $x->target()):?>
&nbsp;&nbsp;&nbsp;<img src="http://balancer.ru/_bors/i/thumb_<?=$x->score() > 0 ? 'up' : 'down'?>.gif" />&nbsp;<?=$x->target()->titled_url()?> <?=$x->target()->score_colorized()?><br/>
<?php endif ?>
</dd>
</dl>

</td></tr></table>

<dl class="box">
<dt>Самые неоднозначные <?=count($differents)?> сообщений за месяц</dt>
<dd>
<?php foreach($differents as $x):?>
&nbsp;&nbsp;&nbsp;<img src="http://balancer.ru/_bors/i/thumb_<?=$x->score() > 0 ? 'up' : 'down'?>.gif" />&nbsp;<?=$x->target()->titled_url()?> <?=$x->target()->score_colorized()?><br/>
<?php endforeach ?>
</dd>
</dl>

<dl class="box">
<dt>Последние <?=count($last)?> оценённых топиков</dt>
<dd>
<?php foreach($last as $x):?>
&nbsp;&nbsp;&nbsp;<?=airbase_time($x->create_time())?><img src="http://balancer.ru/_bors/i/thumb_<?=$x->score() > 0 ? 'up' : 'down'?>.gif" />&nbsp;<?=$x->target()->titled_url_in_container()?>&nbsp;<?=$x->target()->score_colorized()?><br/>
<?php endforeach ?>
</dd>
</dl>
