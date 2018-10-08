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
/*

$user_id=$data['user_id'];
$post_type=$data['post_type'];
$message_type=$data['message_type'];
$sub_type=$data['sub_type'];
*/
$orign_data=$data['message'];

$new_json_data=array(
	'post_type'=>$data['post_type'],
	'message_type'=>$data['message_type'],
	'sub_type'=>$data['sub_type'],
	'message'=>$orign_data,
	'user_id'=>$data['user_id'],
	'group_id'=>$data['group_id'],
	'discuss_id'=>$data['discuss_id']
);
//Report
if(constant('administrator')!=$data['user_id']){
    $report_message=null;
    $from=null;
    //qq号 群号 讨论组ID
    switch ($data['message_type']){
        case "private":
            //$from=$sub_from;
            break;
        case "group":
            $from=$data['group_id'];
            break;
        case "discuss":
            $from=$data['discuss_id'];
            break;
        default:
            //$from=null;
            die("error in switch(type)!") ;
            break;
    }
    $from_where=$from==null?'':"($from)";
    $report_message="{$data['user_id']}{$from_where} : [{$orign_data}]";
    \access\send_msg('send_private_msg',constant('administrator'),$report_message,constant("token"));
}


/*
switch ($data['sub_type']){
    case "private":
        break;
    case "group":
        $new_json_data['group_id']=$data['group_id'];
        break;
    case "discuss":
        $new_json_data['discuss_id']=$data['discuss_id'];
        break;
    default:
        $new_json_data=null;
        die("error in switch(type)!");

        break;
}
*/
$php="http://127.0.0.1/bangumi/bangumi.php";

//\access\send_msg('send_private_msg',597320012,"orign_data: ".$data['message'],constant("token"));
$symbol_file=$orign_data[0];
$orign_data=str_replace('!','~',$orign_data);
$orign_data=str_replace('
','',$orign_data);
$para=explode("~",$orign_data);
$size=count($para);
for($i=1;$i<$size;++$i)
{
	$new_json_data['message']="{$symbol_file}{$para[$i]}";
	
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











