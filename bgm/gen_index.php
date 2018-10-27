<?php
//require_once '../../../bangumi/api/access.php';
require_once './update.php';
//access
if(empty($_GET['qq'])) {
    die("I need qq...");
}
$continue=\web\validate_user($_GET['qq']);
if(!$continue){
	die("仅限魔法少女哦!~");
}
//$user_IP = ($_SERVER["HTTP_VIA"]) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : $_SERVER["REMOTE_ADDR"];
//$user_IP = ($user_IP) ? $user_IP : $_SERVER["REMOTE_ADDR"];
//\access\send_msg('send_private_msg',597320012,"IP: {$user_IP}",constant('token')); 
//$qq='{qq}';
//$is_send=false;
//url for dmhy
//url for QQ reply
//$reply_url='http://127.0.0.1/bangumi/api/dmhy/dmhy_moe_search.php?dmhymoe=1&keyword=家庭教师HITMAN|REBORN!|家庭教師ヒットマンREBORN!&max=5&type=private&to=597320012&from=597320012&access=Sirokuma';

date_default_timezone_set("Asia/Tokyo");

$old_url='http://www.irisu.cc/res/bgm_pic.jpg';
//file_get_contents($reply_url);
$bgm_tv='http://bgm.tv/';
$my_bgm='http://bgm.irisu.cc/bgm/';

//user
//$bangumi_id='{bangumi_id}';
//$qq='{qq}';
$qq=$_GET['qq'];
$bangumi_id=\access\get_bangumi_id($qq);
// //subject
// $subject_name='{subject_name}';
// $subject_id='{subject_id}';
// $subject_img='{subject_img}';
// $dmhy_url='{dmhy_url}';

// //detail user for subject
// //[003]:▧▧▧
// $have_collect_num='{have_collect_num}';
// $have_air_num='{have_air_num}';
// $all_num='{all_num}';
//html
$html_top=<<<EOF

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>背包指南</title>
    <link rel="icon" type="image/x-icon" href="http://irisu.cc/res/favicon.ico"/>
    <link rel="stylesheet" type="text/css" href="../dmhy_moe_home.css" charset="utf-8">
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
	function to_timeline(){
		window.location.href='$bgm_tv'+'user/'+'$bangumi_id';
	}
	function to_dmhy(dmhy_url){
		window.location.href=dmhy_url;
	}
	function to_detail_subject(subject_id){
		window.location.href='$my_bgm'+'$qq/'+subject_id;
	}
	function to_detail_subject_bgm(subject_id){
		window.location.href='$bgm_tv'+"subject/"+subject_id;
	}
	function update_save(){
		//update
		window.location.href='$my_bgm'+'gen_index.php?qq='+'$qq';
	}
	function change_background(xxxx){
		var Back=document.getElementById("BackGround");
		if(xxxx!='null'){
			Back.src = xxxx;
			Back.className = 'blur_solid';
		}
		else{
			Back.className = 'blur_transparent';
		}
	}		

	</script>
</head>

<body>

	
	<ul class="nav">
		<li><button onclick="to_bangumi()">Bangumi番组计划</button></li>
		<li><button onclick="send_msg()">Bangumi娘</button></li>
		<li><button onclick="to_timeline()">时光机</button></li>
		<li><button onclick="update_save()">更新</button></li>
	</ul>
	<div class="outer" id = "body">
EOF;
// $html_contents=<<<EOF

// 		<div class="fix"  onmouseover="change_background('$subject_img')" onmouseout="change_background('null')">

// 			<p class="process" style=" background-color: #FFFFFF">标记<span style="color:#080808;">$have_collect_num</span></p>
// 			<p class="process" style=" background-color: #FF9999">放送<span style="color:#000F91;">$have_air_num</span></p>
// 			<p class="process" style=" background-color: #888888">总话<span style="color:#FEFEFE;">$all_num</span></p>

// 			<div class="img">
// 				<img class="detail" onclick="to_detail_subject('$subject_id')" src="$subject_img">
// 			</div>
			
// 			<div class="button_div">
// 				<button class="button" onclick="to_detail_subject_bgm('$subject_id')">Bangumi</button>
// 				&nbsp;
// 				&nbsp;
// 				&nbsp;
// 				&nbsp;
// 				&nbsp;
// 				<button class="button" onclick="to_dmhy()">动漫花园</button>
// 			</div>	

// 		</div>	
		


// EOF;
$html_buttom=<<<EOF
		<img class="blur" id="BackBackGround" onclick="return false" src='$old_url'>
		<img class="blur_transparent" id="BackGround" onclick="return false" src='$old_url'>
	
	</div>
	<script type="text/javascript">
   		//动态背景
		background1 = document.getElementById("BackGround");
		background2 = document.getElementById("BackBackGround");
		var body = document.getElementById("body");
		body.onmousemove=function(ev){
			ev=ev||window.event;
	 		var iX1=ev.clientX-(background1.offsetLeft+this.offsetWidth/2);
	 		var iY1=ev.clientY-(background1.offsetTop+this.offsetHeight/2);
	 		var xoff=-5*iX1/this.offsetWidth;
	 		var yoff=-5*iY1/this.offsetHeight;
	 		background1.style.left=-10+xoff+'%';
	 		background1.style.top=-10+yoff+'%';
	 		background2.style.left=-10+xoff+'%';
	 		background2.style.top=-10+yoff+'%';
		}


	</script>

</body>
</html>

EOF;
$file_path="./{$qq}".'/index.html';
$path="./{$qq}";
if(!is_dir($path)){
    mkdir($path, 0777);
}
file_put_contents($file_path, $html_top);
//file_put_contents('index.html', $html_contents, FILE_APPEND);
\web\filter_save($qq, $file_path);
file_put_contents($file_path, $html_buttom, FILE_APPEND);

$to_html=<<<EOF
<script type="text/javascript">
	window.location.href='$my_bgm'+'$qq/';
</script>
EOF;
echo $to_html;

?>

