<?php
require_once '../../../bangumi/api/access.php';
//access
// if(empty($_GET['ac'])) {
//     die("No auth");
// }
// else {
//     //access
//     \access\qq_encode();
//     constant('password')==$_GET['access']?:die("error auth");
//     echo "access";
// }
//$user_IP = ($_SERVER["HTTP_VIA"]) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : $_SERVER["REMOTE_ADDR"];
//$user_IP = ($user_IP) ? $user_IP : $_SERVER["REMOTE_ADDR"];
//\access\send_msg('send_private_msg',597320012,"IP: {$user_IP}",constant('token')); 
//$qq='597320012';
//$is_send=false;
//url for dmhy
$dmhy_url='https://share.dmhy.org/topics/list?keyword=寄宿|学校的|朱丽叶|寄宿学|校的|朱丽叶|寄宿学校のジュリエット';
//url for QQ reply
//$reply_url='{reply_url}';
//
$subject_name='寄宿学校的朱丽叶';
//
$subject_img='http://lain.bgm.tv/pic/cover/l/5a/b7/240346_LDL3v.jpg';
//
$qq='597320012';
//
$bangumi_id='';
//
$subject_id='240346';
$bgm_tv='http://bgm.tv/';
$my_bgm='http://bgm.irisu.cc/bgm/';
//$bgm_subject_url="{$bgm_tv}/subject/{$subject_id}";
//file_get_contents($reply_url);

//html
$html_contents=<<<EOF

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>$subject_name</title>
    <link rel="stylesheet" type="text/css" href="../../dmhy_moe.css" charset="utf-8">
    <script type="text/javascript">
	function send_msg(){
	    if( navigator.userAgent.match(/Android/i)
	    || navigator.userAgent.match(/webOS/i)
	    || navigator.userAgent.match(/iPhone/i)
	    || navigator.userAgent.match(/iPad/i)
	    || navigator.userAgent.match(/iPod/i)
	    || navigator.userAgent.match(/BlackBerry/i)
	    || navigator.userAgent.match(/Windows Phone/i)
	    ){

	    		window.location.href="mqqwpa://im/chat?chat_type=wpa&uin=1243000303&version=1&src_type=web&web_src=irisu.cc";

	    }
	    else {
	    	
	    		window.location.href="tencent://message/?uin=1243000303&Site=irisu.cc&Menu=yes";

	    }
	}
	function to_dmhy(){
		window.location.href="$dmhy_url";
	}
	function to_detail_subject_bgm(){
		window.location.href='$bgm_tv'+"subject/"+'$subject_id';
	}
	function to_bangumi(){
		window.location.href='$bgm_tv';
	}
	function to_timeline(){
		window.location.href='$bgm_tv'+'user/'+'$bangumi_id';
	}
	function return_home(){
		window.location.href='$my_bgm'+'$qq';
	}
	</script>
</head>

<body>

	<ul class="nav">
		<li><button onclick="to_bangumi()">Bangumi番组计划</button></li>
		<li><button onclick="send_msg()">Bangumi娘</button></li>
		<li><button onclick="to_timeline()">时光机</button></li>
		<li><button onclick="return_home()">主页</button></li>
	</ul>
	<img class="blur" onclick="return_home()" src="$subject_img">
	<div class="fix">
		
		<br>
		<div class="img">
			<img class="detail" src="$subject_img">
		</div>
		<br>
		<div class="button_div">
			<button class="button" onclick="to_detail_subject_bgm()">Bangumi</button>
			&nbsp;
			&nbsp;
			&nbsp;
			&nbsp;
			&nbsp;
			&nbsp;
			&nbsp;
			&nbsp;
			&nbsp;
			<button class="button" onclick="to_dmhy()">动漫花园</button>
		</div>		
		<br>
		<br>
		<br>
	</div>
		
</body>
</html>


EOF;

echo $html_contents;

?>

