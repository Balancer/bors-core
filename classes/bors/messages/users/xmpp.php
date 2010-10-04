<?php

class bors_messages_users_xmpp extends bors_object
{
	static function queue($target_user, $object)
	{
		object_new_instance('bors_messages_queue', array(
			'recipient_class_name' => 'bors_messages_users_xmpp',
			'recipient_object_id' => $target_user->id(),
			'target_class_name' => $object->class_name(),
			'target_object_id' => $object->id(),
		));
	}
}
