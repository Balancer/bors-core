<?php

bors_url_submap(array(
	'/ajax/validate => _ajax_validate',
	'/ajax/setkeywords => _ajax_setkeywords',
	'/ajax/keyword\-remove => _ajax_keywordRemove',
	'/favorites/ajax => bors_user_favorites_ajax',
	'/ajax/call/(.+) => _ajax_call(1)',
	'/ajax/module/(.+) => _ajax_module(1)',

	'/act/pub/(.+) => _action_public(1)',
	'/actions/edit(/.+)$ => _actions_edit(1)',
));
