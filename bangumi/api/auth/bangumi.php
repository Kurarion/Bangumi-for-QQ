<?php
/**
 * Created by PhpStorm.
 * User: Cocoa
 * Date: 2018/6/1
 * Time: 21:28
 */

//本php用于隐藏api地址信息
$user_code=$_GET['code'];
//$type=$_GET['tp'];
$to_code=$_GET['nu'];
$state=$_GET['state'];
$url='http://127.0.0.1/bangumi/api/auth/bangumi_apply_auth.php?code='.$user_code.
    '&to_code='.$to_code.'&state='.$state;
//传参数到bangumi_apply_auth处理[这里不会显示到主页上]
file_get_contents($url);
//跳转至其他页面
//原因是如果用户使用手机浏览器经常会二次请求
//html返回的页面


?>
<html>
    <script language="javascript" type="text/javascript">
    window.location.href='bangumi.html';
    </script>
</html>

