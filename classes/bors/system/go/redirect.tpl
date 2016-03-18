<p>Вы пытаетесь перейти к сообщению в теме, где есть более ранние нечитанные сообщения. Можете выбрать, куда Вам перейти:</p>

<ul>
{if $this->get('reply_url')}
<li><a href="{$this->reply_url()}">{$this->reply_title()} — сообщение, на которое вы отвечали</a></li>
{/if}
{if $this->get('old_url')}
<li><a href="{$this->old_url()}">{$this->old_title()} — первое нечитанное сообщение в теме</a></li>
{/if}
<li><a href="{$this->direct_url()}">{$this->direct_title()} — выбранное для перехода сообщение</a></li>
</ul>
