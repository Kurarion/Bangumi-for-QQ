<?php
/**
 * Created by PhpStorm.
 * User: Cocoa
 * Date: 2018/6/1
 * Time: 21:18
 */
require_once '../access.php';
$user_code=$_GET['code'];
$type='send_private_msg';
$to_code=$_GET['to_code'];
//正确的state
$right_state=$to_code[1].$to_code[5].$to_code[2].$to_code[4];
//解密qq号
$to=\access\qq_decode($to_code);
$state=$_GET['state'];
//测试用代码
//\access\send_msg($type,597320012,"test_ok: to=$to usercode=$user_code state=$state ",constant('token'));
if($user_code==null||$right_state!=$state)
{
    //注意这里state只是顺手用来先验，真正的用途适用于get token的post参数中
    //空的code或者错误的state
    die();
}

//向auth服务器发送用户code取得token
/* 例子
 * {
    "grant_type": "authorization_code",
    "client_id": "bgm243XXXX1e3",
    "client_secret": "6e27cc9XXXXX8X549f01f",
    "code": "fcXXXXXXXb05903b7c2",
    "redirect_uri": "http://www.XXXXXXXumi/"
 * }
 *
 */
//注意这里的redirect_uri是用来验证的，并非真正回调，[废弃：只需填写在bangumiAPP上设置的回调地址即可]
//由于现在使用有参数并且encode后的回调url
$redirect_uri=urlencode("http://www.XXXXX.cXXXX/bangumi.php?nu=$to_code");
$data = array (
    'grant_type' => "authorization_code",
    'client_id' => constant("client_id"),
    'client_secret' => constant("client_secret"),
    'code' => $user_code,
    'redirect_uri' => $redirect_uri,
    'state' => $right_state
);

$data=json_encode($data);

//echo $data;
//require '../access.php';
$opts = array (
    'http' => array (
        'method' => 'POST',
        'header' => "Content-Type: application/json",
        'content' => $data
    )
);
$url='https://bgm.tv/oauth/access_token';
$context = stream_context_create($opts);
$json = file_get_contents($url, false, $context);

//处理返回的数据
/* 例子
 * {
    "access_token": "b076deXXXXXXXXeb7a58",
    "expires_in": 86400,
    "token_type": "Bearer",
    "scope": null,
    "user_id": 92981,
    "refresh_token": "e8efa360XXXXXXa302ba28"
 * }
 * */
$return_data=json_decode($json,true);
$access_token=$return_data['access_token'];
$token_type=$return_data['token_type'];
$user_id=$return_data['user_id'];
$refresh_token=$return_data['refresh_token'];
//测试用代码
//\access\send_msg($type,597320012,"test: access_token=$access_token userid=$user_id refresh_token=$refresh_token ",constant('token'));
//写入数据库中
$con=mysqli_connect(constant("sql_url"),constant("sql_user"),constant("sql_password"));
$fail=false;
//多次注册更新token
$refresh=false;
$re_msg="";
if(!$con)
{
    //连接数据库失败
    //die();
    $fail=true;
    $re_msg="啊哦~数据库访问失败...";
}
if(!mysqli_select_db($con,"bangumi"))
{
    //数据库打开失败
    //die();
    $fail=true;
    $re_msg="啊哦~数据库打开失败...";
}
//先验证是否重复注册
if(!$fail){
    $had_user_sql="select user_id from bgm_users where user_qq=$to";
    //只有搜索失败才会$result=false，空值为true
    $result=mysqli_query($con,$had_user_sql);
    $row=mysqli_fetch_array($result,MYSQLI_ASSOC);
    if($row!=false){
        $user_num=$row['user_id'];
        $re_msg="你不是第".$user_num."位魔法少女么...";
        $fail=true;
        $refresh=true;
    }
}
if($refresh){
    $refresh_sql="UPDATE bgm_users
        SET user_access_token='$access_token',user_refresh_token='$refresh_token'
        WHERE user_bangumi=$user_id";
    if(mysqli_query($con,$refresh_sql)){
        $re_msg.="\n嘛，不过重新加深一下也没关系";
    }else{
        $re_msg.="\n然而这次没这么幸运了，契约失败";
    }
}
if(!$fail){
    $reg_user_sql="insert into bgm_users(user_qq,user_bangumi,user_access_token,user_refresh_token)
          values ($to,$user_id,'$access_token','$refresh_token')";
    //$get_user_id_sql="select user_id from bgm_users where user_qq=$to";
    $reg_subject_sql="insert into bgm_subject_memory(user_qq)
                values ($to)";
    if(mysqli_query($con,$reg_user_sql)){

        if(mysqli_query($con,$reg_subject_sql)){
                $re_msg="QQ：$to 与 Bangumi-ID：$user_id 完成缔约！\n".
                    "新的魔法少女诞生！";
        }
        else{
                $re_msg="法杖明明在这里但吉祥物却丢了\n".
                    "缔约失败......";
        }
    }
    else{


        $re_msg="法杖丢了还是被人拿走了呢？\n".
                "缔约失败......";
        //"\n".$sql.
        //"\n".mysqli_error($con);


    }

}
mysqli_close($con);


//qq回复用户说明绑定成功
\access\send_msg($type,$to,$re_msg,constant('token'));
?>