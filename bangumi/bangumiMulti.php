<?php
require_once './api/access.php';
//json
$json=file_get_contents('php://input');
$data=json_decode($json,true);
//Example
/*
 * {
    "time": 1515204254,
    "post_type": "message",
    "message_type": "private",
    "sub_type": "friend",
    "message_id": 12,
    "user_id": 12345678,
    "message": "~user",
    "font": 456
 * }
 */


$orign_data=$data['message'];
$user_id=$data['user_id'];
$post_type=$data['post_type'];
$message_type=$data['message_type'];
$sub_type=$data['sub_type'];

$new_json_data=array(
	'post_type'=>$post_type,
	'message_type'=>$message_type,
	'sub_type'=>$sub_type,
	'message'=>$orign_data,
	'user_id'=>$user_id
);

$php="http://127.0.0.1/bangumi/bangumi.php";

//\access\send_msg('send_private_msg',597320012,"orign_data: ".$data['message'],constant("token"));
$para=explode("~",$orign_data);
$size=count($para);
for($i=1;$i<$size;++$i)
{
	$new_json_data['message']='~'.$para[$i];
	
	//\access\send_msg('send_private_msg',597320012,"para[$i]: ".$para[$i],constant("token"));
	//\access\send_msg('send_private_msg',597320012,"data['message']: ".$new_json_data['message'],constant("token"));

	$new_data=json_encode($new_json_data);
	
	//
	//$new_data_decode=json_decode($new_data);
	//\access\send_msg('send_private_msg',597320012,"new_data['message']: ".$new_data['message'],constant("token"));
	
	//\access\send_msg('send_private_msg',597320012,"new_data['user_id']: ".$new_data['user_id'],constant("token"));
	

	//echo $data;
	//require '../access.php';
	$opts = array (
	    'http' => array (
	        'method' => 'POST',
	        'header' => "Content-Type: application/json",
	        'content' => $new_data
	    )
	);
	$context = stream_context_create($opts);
	file_get_contents($php, false, $context);
}











