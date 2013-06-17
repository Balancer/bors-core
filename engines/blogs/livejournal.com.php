<?php

require_once(config('3rdp_xmlrpc_path').'/lib/xmlrpc.inc');
require_once('inc/texts/unicode.php');
$GLOBALS['xmlrpc_internalencoding'] = 'UTF-8';

function bors_blog_signature(&$title_obj, &$url_obj)
{
	return "<div style=\"font-size:8pt;border-top-style:dotted;border-top-width:1px;margin-top:10px;\"><i>Оригинальное сообщение: <a href=\"{$url_obj->url()}\">{$title_obj->title()}</a> на форумах Balancer'а</i></div>";
}

function bors_blog_message(&$title_obj, &$url_obj, &$body_obj)
{
	$message = $body_obj->body();
//	if(strlen($message) > 4000)
	$message .= bors_blog_signature($title_obj, $url_obj);

	return $message;
}

function bors_blog_livejournal_com_post($user_id, $title_obj, $url_obj, $body_obj, $keyword_obj)
{
	$db = new driver_mysql('AB_BORS');

	$x = $db->select('external_blogs', '*', array('bors_user_id='=>$user_id, 'blog='=>'livejournal.com', 'active='=>1));
	if(empty($x))
		return;

	$time		= $body_obj->create_time();
	$message	= bors_blog_message($title_obj, $url_obj, $body_obj);
	$subject	= $title_obj->title();
	if($desc = $title_obj->description())
		$subject .= " ($desc)";

	$year	= strftime('%Y', $time);
	$month	= strftime('%m', $time);
	$day	= strftime('%d', $time);
	$hour	= strftime('%H', $time);
	$minute	= strftime('%M', $time);

	$lj_userid = $x['login'];
	$lj_passwd = $x['password'];
   
	$client = new xmlrpc_client("/interface/xmlrpc", "www.livejournal.com", 80);
    
    $params = new xmlrpcval( array(
		'username' => new xmlrpcval($lj_userid,'string'),
		'password' => new xmlrpcval($lj_passwd,'string'),
		'ver' => new xmlrpcval('1','string'),
		'lineendings' => new xmlrpcval('unix','string'),
		'event' => new xmlrpcval($message,'string'),
		'subject' => new xmlrpcval($subject,'string'),
		'year' => new xmlrpcval($year,'int'),
		'mon' => new xmlrpcval($month,'int'),
		'day' => new xmlrpcval($day,'int'),
		'hour' => new xmlrpcval($hour,'int'),
		'min' => new xmlrpcval($minute,'int'),
		'props' => new xmlrpcval( array(
//			'opt_backdated' => new xmlrpcval(1, 'boolean'),
			'taglist' => new xmlrpcval($keyword_obj->keywords_string(), 'string'),
			'opt_preformatted' => new xmlrpcval(1, 'boolean'),
		), 'struct'),
	),'struct');

	$msg = new xmlrpcmsg('LJ.XMLRPC.postevent');
	$msg->addparam($params);
	$client->setDebug(0);
	$result = $client->send($msg);
	if ($result->faultCode() != 0)
	{
		print "Ошибка добавления в ЖЖ: " . $result->faultString();
		exit();
	}

	$v = $result->value();
	
	$itemid_xml = $v->structMem('itemid');
	$itemid = $itemid_xml->scalarVal();
	
	$db->replace('external_blogs_maps', array(
		'class_id' => $body_obj->class_id(),
		'object_id' => $body_obj->id(),
		'blog_type_id' => 1,
		'blog_object_id' => $itemid,
	));
}

function bors_blog_livejournal_com_edit($user_id, $title_obj, $url_obj, $body_obj, $keyword_obj)
{
	$db = new driver_mysql('AB_BORS');

	$x = $db->select('external_blogs', '*', array('bors_user_id='=>$user_id, 'blog='=>'livejournal.com', 'active='=>1));
	if(empty($x))
		return;

	$itemid = intval($db->select('external_blogs_maps', 'blog_object_id', array(
		'class_id=' => $body_obj->class_id(),
		'object_id=' => $body_obj->id(),
		'blog_type_id=' => 1,
	)));

	if(!$itemid)
		return bors_blog_livejournal_com_post($user_id, $title_obj, $url_obj, $body_obj, $keyword_obj);

	$time		= $body_obj->create_time();
	$message	= bors_blog_message($title_obj, $url_obj, $body_obj);
	
	$title		= $title_obj->title();
	if($desc = $title_obj->description())
		$title .= " ($desc)";

	$year	= strftime('%Y', $time);
	$month	= strftime('%m', $time);
	$day	= strftime('%d', $time);
	$hour	= strftime('%H', $time);
	$minute	= strftime('%M', $time);

	$lj_userid = $x['login'];
	$lj_passwd = $x['password'];
    
    $client=new xmlrpc_client("/interface/xmlrpc", "www.livejournal.com", 80);
	 
	$params = new xmlrpcval( array(
		'username' => new xmlrpcval($lj_userid,'string'),
		'password' => new xmlrpcval($lj_passwd,'string'),
		'ver' => new xmlrpcval('1','string'),
		'itemid' => new xmlrpcval($itemid,'int'),
		'event' => new xmlrpcval($message,'string'),
		'lineendings' => new xmlrpcval('unix','string'),
		'subject' => new xmlrpcval($title,'string'),
		'year' => new xmlrpcval($year,'int'),
		'mon' => new xmlrpcval($month,'int'),
		'day' => new xmlrpcval($day,'int'),
		'hour' => new xmlrpcval($hour,'int'),
		'min' => new xmlrpcval($minute,'int'),
		'props' => new xmlrpcval( array(
//			'opt_backdated' => new xmlrpcval(1, 'boolean'),
			'opt_preformatted' => new xmlrpcval(1, 'boolean'),
			'taglist' => new xmlrpcval($keyword_obj->keywords_string(), 'string'),
		), 'struct'),
	),'struct');

	$msg = new xmlrpcmsg('LJ.XMLRPC.editevent');
	$msg->addparam($params);
	$client->setDebug(0);
	$result = $client->send($msg);
	if ($result->faultCode() != 0)
	{
		print "Couldn't process request: ".$result->faultString();
		exit();
	}
}

function bors_blog_livejournal_com_delete($user_id, $body_obj)
{
	$db = new driver_mysql('AB_BORS');

	$x = $db->select('external_blogs', '*', array('bors_user_id='=>$user_id, 'blog='=>'livejournal.com', 'active='=>1));
	if(empty($x))
		return;

	$x = intval($db->select('external_blogs_maps', 'id, blog_object_id', array(
		'class_id=' => $body_obj->class_id(),
		'object_id=' => $body_obj->id(),
		'blog_type_id=' => 1,
	)));

	$dbid	= @$x['id'];
	$itemid	= @$x['blog_object_id'];

	if(!$itemid)
		return;

	$db->query("DELETE FROM external_blogs_maps WHERE id = $dbid");

	$lj_userid = $x['login'];
	$lj_passwd = $x['password'];
    
    $client=new xmlrpc_client("/interface/xmlrpc", "www.livejournal.com", 80);
	 
	$params = new xmlrpcval( array(
		'username' => new xmlrpcval($lj_userid,'string'),
		'password' => new xmlrpcval($lj_passwd,'string'),
		'ver' => new xmlrpcval('1','string'),
		'itemid' => new xmlrpcval($itemid,'int'),
		'event' => new xmlrpcval('', 'string'),
		'lineendings' => new xmlrpcval('unix','string'),
		'subject' => new xmlrpcval('','string'),
		'year' => new xmlrpcval(0,'int'),
		'mon' => new xmlrpcval(0,'int'),
		'day' => new xmlrpcval(0,'int'),
		'hour' => new xmlrpcval(0,'int'),
		'min' => new xmlrpcval(0,'int'),
	),'struct');

	$msg = new xmlrpcmsg('LJ.XMLRPC.editevent');
	$msg->addparam($params);
	$client->setDebug(0);
	$result = $client->send($msg);
	if ($result->faultCode() != 0)
		debug_exit("Couldn't process request: ".$result->faultString());
}
