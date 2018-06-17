<?php
/**
 * Created by PhpStorm.
 * User: Cocoa
 * Date: 2018/6/3
 * Time: 23:08
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

$type='send_'.$_GET['type'].'_msg';
$to=$_GET['to'];
$from=$_GET['from'];
$msg="さようなら...";
//进行数据库操作
$delete_user_sql="DELETE FROM bgm_users WHERE user_qq = $from";
$delete_subject_sql="DELETE FROM bgm_subject_memory WHERE user_qq = $from";
$result1=\access\sql_query($type,$to,$delete_user_sql);
$result2=\access\sql_query($type,$to,$delete_subject_sql);
//$row1=mysqli_fetch_array($result1,MYSQLI_ASSOC);
//$row2=mysqli_fetch_array($result2,MYSQLI_ASSOC);
//if($row1==false||$row2==false){
//    $msg="是我没找到你的数据？\n还是说你还不是魔法少女？";
//}
//回复qq

\access\send_msg($type,$to,$msg,constant('token'));
?>