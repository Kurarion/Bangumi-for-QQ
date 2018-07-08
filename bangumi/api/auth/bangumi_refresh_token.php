<?php
/**
 * Created by PhpStorm.
 * User: Cocoa
 * Date: 2018/7/8
 * Time: 17:41
 */
require_once '../access.php';
//access
if(empty($argv[1])) {
    die("No auth");
}
else {
    //access
    constant('password')==$argv[1]?:die("error auth");
    echo "access";
}
//发送到个人
$type='send_private_msg';
//列出所有token
$sql="select user_refresh_token
                              from bgm_users";
$result=\access\sql_query($type,"597320012",$sql);
while($row=mysqli_fetch_array($result,MYSQLI_ASSOC)){
    $refresh_user[]=$row;
}
for($i=0;$i<count($refresh_user);++$i){
    //echo "\nXXXX\n".$refresh_user[$i]['user_refresh_token']."\nYYYY\n";
    \access\refresh_token($refresh_user[$i]['user_refresh_token']);
}

?>