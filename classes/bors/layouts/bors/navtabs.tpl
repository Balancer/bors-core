{if $links}
<ul class="{$layout()->ul_tab_class()}">
{foreach $links as $title => $link}
	<li><a href="?{$this->getsort('a',true)}"{if $this->request()->is_active_tab('a', true)} class="{$this->layout()->ul_tab}"{/if}>{$title}</a></li>
{/foreach}
</ul>
{/if}
