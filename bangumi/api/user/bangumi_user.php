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

//bangumi api URL
$username=$_GET['username'];
$url='https://api.bgm.tv/user/'.$username;
//bangumi JSON
$json=file_get_contents($url);
$data=json_decode($json,true);
//Example Json:
/*
 * {
    "id": 92981,
    "url": "http://bgm.tv/user/wz97315",
    "username": "wz97315",
    "nickname": "Sirokuma",
    "avatar": {
        "large": "http://lain.bgm.tv/pic/user/l/000/09/29/92981.jpg?r=1518501309",
        "medium": "http://lain.bgm.tv/pic/user/m/000/09/29/92981.jpg?r=1518501309",
        "small": "http://lain.bgm.tv/pic/user/s/000/09/29/92981.jpg?r=1518501309"
    },
    "sign": ""
 * }
 *
 * {
    "request": "/user/wz973154",
    "code": 404,
    "error": "Not Found"
 * }
 */
if($username==null||array_key_exists('error',$data))
{
    //未找到相关用户...或者空参数
    $msg=array(
        array('type'=>"text",
            'data'=>array(
                'text'=>"用户名为空或未找到此用户..."
            )
        )
    );
}
else{
    $user_id=$data['id'];
    $user_url=$data['url'];
    $user_username=$data['username'];
    $user_nickname=$data['nickname'];
    $user_avatar=$data['avatar']['large'];
//msg
    $msg=array(
        array('type'=>"text",
            'data'=>array(
                'text'=>"<".$user_id.">".
                    "\n"
            )
        ),
        array('type'=>"image",
            'data'=>array(
                'file'=>$user_avatar
            )
        ),
        array('type'=>"text",
            'data'=>array(
                'text'=>
                    "\n".$user_nickname." @".$user_username.
                    "\n用户主页:".$user_url
            )
        )

    );
}
//send_message
//require '../access.php';
$type='send_'.$_GET['type'].'_msg';
$to=$_GET['to'];
//echo $type;
//echo $to;
//echo $msg;
//echo constant('token');
\access\send_msg($type,$to,$msg,constant('token'));
//echo "yes";
?>