<?php
/**
 * Created by PhpStorm.
 * User: Cocoa
 * Date: 2018/6/2
 * Time: 18:04
 */
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

$client_id=constant("client_id");
//只有私聊
$type='send_'.$_GET['type'].'_msg';
$to=$_GET['to'];
//加密qq号
$to_code=\access\qq_encode($to);
//随机的state{根据qq号加密后的2635位三个字符}
$state=$to_code[1].$to_code[5].$to_code[2].$to_code[4];
$redirect_url="http://www.xxxx.xx/bangumi.php?nu=$to_code";
//注意redirect_url必须url_encode否则会自动删除参数
$encode_url=urlencode($redirect_url);
if($_GET['type']=='private'){
    //加密参数 type
    //$pra_type=\access\$type2password[$_GET['type']];
    $msg="点击下面这个链接与我签订契约吧！\n".
        "https://bgm.tv/oauth/authorize?client_id=".$client_id."&response_type=code"."&redirect_uri=".
        $encode_url."&state=$state";
//[废弃:还要涉及发送消息的参数传递]由于bangumi已有可选的回调地址，此处省略
//&redirect_uri=http://www.xxxxxx.xxx/bangumi.php
}
else{
    $msg="嘘~魔法少女的身份怎么能被人知道呢！";
}
access\send_msg($type,$to,$msg,constant('token'));
?>