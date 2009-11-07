<dl class="box">
<dt>Лучшие <?=count($best)?> сообщений форума</dt>
<dd>
<?php foreach($best as $x) if($x && $x->target()):?>
&nbsp;&nbsp;&nbsp;<img src="http://balancer.ru/_bors/i/thumb_<?=$x->score() > 0 ? 'up' : 'down'?>.gif" />&nbsp;<?=$x->target()->titled_url()?> <?=$x->target()->score_colorized()?><br/>
<?php endif ?>
</dd>
</dl>

<dl class="box">
<dt>Последние <?=count($last)?> оценок из <?=$total?></dt>
<dd>
<?php foreach($last as $x):?>
&nbsp;&nbsp;&nbsp;<?=airbase_time($x->create_time())?><img src="http://balancer.ru/_bors/i/thumb_<?=$x->score() > 0 ? 'up' : 'down'?>.gif" />&nbsp;<?=$x->target()->titled_url()?><br/>
<?php endforeach ?>
</dd>
</dl>
