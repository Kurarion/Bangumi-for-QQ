<?php
require_once '../access.php';
//access
if(empty($_GET['access'])) {
    die("No auth");
}
else {
    //access
    constant('password')==$_GET['access']?:die("error auth");
    echo "access";
}

//report
$from=$_GET['report_from'];
$msg=$_GET['report_msg'];
$type=$_GET['report_type'];

$file_path='msg.log';

//file_put_contents($file_path, $msg_data);
//send_message
switch ($type) {
	case 'last_msg':
		if(file_exists($file_path)){
			$data=file_get_contents($file_path);
			$para=explode(" ",$data);
			$r_type=$para[0];
	        $r_from=$para[1];
			\access\send_msg($r_type,$r_from,$from,constant('token'));
		}else{
			\access\send_msg('send_private_msg',constant('administrator'),"No Save...",constant('token'));
		}	
		break;
	case 'get_msg':
		if(file_exists($file_path)){
			$data=file_get_contents($file_path);
			$para=explode(" ",$data);
			$r_type=$para[0];
	        $r_from=$para[1];
	        \access\send_msg('send_private_msg',constant('administrator'),"Type=$r_type\nQQ=$r_from",constant('token'));
   		}else{
   			\access\send_msg('send_private_msg',constant('administrator'),"No Save...",constant('token'));
   		}
		break;
	default:
		//save
		$msg_data="$type $from";
		file_put_contents($file_path, $msg_data);
		//reply
		\access\send_msg($type,$from,$msg,constant('token'));
		\access\send_msg('send_private_msg',constant('administrator'),'OK~',constant('token'));
		break;
}


//echo "yes";
