<?php
//require_once '../../../bangumi/api/access.php';
//require_once '../update.php';
//access
//$user_IP = ($_SERVER["HTTP_VIA"]) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : $_SERVER["REMOTE_ADDR"];
//$user_IP = ($user_IP) ? $user_IP : $_SERVER["REMOTE_ADDR"];
//\access\send_msg('send_private_msg',597320012,"IP: {$user_IP}",constant('token')); 
//$qq='{qq}';
//$is_send=false;
//url for dmhy
//url for QQ reply
//$reply_url='http://127.0.0.1/bangumi/api/dmhy/dmhy_moe_search.php?dmhymoe=1&keyword=家庭教师HITMAN|REBORN!|家庭教師ヒットマンREBORN!&max=5&type=private&to=597320012&from=597320012&access=Sirokuma';

//date_default_timezone_set("Asia/Tokyo");
if(!empty($_GET['qq'])) {
	$qq=$_GET['qq'];
	$url="http://bgm.irisu.cc/bgm/gen_index.php?qq={$qq}";
    $to_html=<<<EOF
	<script type="text/javascript">
		window.location.href='$url';
	</script>
EOF;
	echo $to_html;
}else{

	$old_url='http://www.irisu.cc/res/bgm_pic.jpg';
	//file_get_contents($reply_url);
	$bgm_tv='http://bgm.tv/';
	$my_bgm='http://bgm.irisu.cc/bgm/';

	//user
	//$bangumi_id='{bangumi_id}';
	//$qq='{qq}';
	//$qq=597320012;

	//$bangumi_id=\access\get_bangumi_id($qq);



	$html=<<<EOF

	<!DOCTYPE html>
	<html>
	<head>
	    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	    <title>BGM</title>
        <link rel="icon" type="image/x-icon" href="http://irisu.cc/res/favicon.ico"/>
	    <link rel="stylesheet" type="text/css" href="./dmhy_moe_home.css" charset="utf-8">
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
		function to_bangumi(){
			window.location.href='$bgm_tv';
		}
		function generate_homepage(){
			
			var qq=document.getElementById("qq").value;
			var url="http://bgm.irisu.cc/bgm/gen_index.php?qq="+qq;
			window.location.href=url;
		}
		</script>
	</head>

	<body>

		
		<ul class="nav">
			<li><button onclick="to_bangumi()">Bangumi番组计划</button></li>
			<li><button onclick="send_msg()">Bangumi娘</button></li>
			<li><input id="qq" type="text" value="在此输入QQ号" onfocus="javascript:if(this.value=='在此输入QQ号')this.value='';" onblur="javascript:if(this.value=='')this.value='在此输入QQ号';"><li>
			<li><input type="submit" value="生成主页" onclick="generate_homepage()"></li>
		</ul>
		<div class="outer" id = "body">

			<div class="fix">

			</div>
			


			<img class="blur" id="BackGround" onclick="return false" src='$old_url'>
		
		</div>
		<script type="text/javascript">
	   		//动态背景
			background1 = document.getElementById("BackGround");
			var body = document.getElementById("body");
			body.onmousemove=function(ev){
				ev=ev||window.event;
		 		var iX1=ev.clientX-(background1.offsetLeft+this.offsetWidth/2);
		 		var iY1=ev.clientY-(background1.offsetTop+this.offsetHeight/2);
		 		var xoff=-5*iX1/this.offsetWidth;
		 		var yoff=-5*iY1/this.offsetHeight;
		 		background1.style.left=-10+xoff+'%';
		 		background1.style.top=-10+yoff+'%';
			}


		</script>

	</body>
	</html>

EOF;

	//file_put_contents('index.html', $html_top);
	//file_put_contents('index.html', $html_contents, FILE_APPEND);
	//\web\filter_save($qq);
	//file_put_contents('index.html', $html_buttom, FILE_APPEND);

	// $to_html=<<<EOF
	// <script type="text/javascript">
	// 	window.location.href='$my_bgm'+'$qq/';
	// </script>
	// EOF;
	echo $html;
}



?>

