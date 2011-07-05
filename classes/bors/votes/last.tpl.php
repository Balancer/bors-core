<dl class="box">
<dt>Последние <?=count($last)?> оценённых сообщений</dt>
<dd>
<?= bors_module::show_mod("balancer_board_modules_dated", array('items'=>$last)); ?>
</dd>
</dl>

<table class="null"><tr><td>
<dl class="box">
<dt>Лучшие <?=count($best)?> сообщений форума за месяц</dt>
<dd>
<?= bors_module::show_mod("balancer_board_modules_dated", array('items'=>$best_of_month)); ?>
</dd>
</dl>
</td><td>

<dl class="box">
<dt>Лучшие <?=count($best)?> сообщений форума за всю историю</dt>
<dd>
<?= bors_module::show_mod("balancer_board_modules_dated", array('items'=>$best)); ?>
</dd>
</dl>

</td></tr></table>

<dl class="box">
<dt>Самые неоднозначные <?=count($differents)?> сообщений за месяц</dt>
<dd>
<?= bors_module::show_mod("balancer_board_modules_dated", array('items'=>$differents)); ?>
</dd>
</dl>
