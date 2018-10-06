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
//接受参数
$subject_id=$_GET['subject_id'];
$type="send_{$_GET['type']}_msg";
$to=$_GET['to'];
$from=$_GET['from'];
//回复
$re_msg="暂无清理项...";
//需要回复
$need_reply=true;
//获得Subject id[list]
$site=strrpos($subject_id,"#");
if(false!==$site){
    $decode_subject_id=$subject_id[$site+1];
    $maybe_second_id=$subject_id[$site+2];
    if(is_numeric($maybe_second_id)){
        $decode_subject_id=$decode_subject_id*10+$maybe_second_id;
    }
    if(is_numeric($decode_subject_id)&&$decode_subject_id>=0&&$decode_subject_id<constant("max_list")){

    }else{
        $decode_subject_id=substr($subject_id,$site+1);
    }
    $subject_id=\access\read_save($type,$to,$from,$decode_subject_id);
    //\access\send_msg($type,$to,$subject_id." sui",constant('token'));
}else{
    //省略参数
    $subject_id=\access\get_last_subject($type,$to,$from);
}
if($subject_id!=0){
    //获得所有的SaveID
    $get_save_sql="select *
                              from bgm_subject_memory
                              where user_qq=$from";
    $result=\access\sql_query($type,$to,$get_save_sql);
    $row=mysqli_fetch_array($result,MYSQLI_NUM);
    //循环检查
    for($i=1;$i<constant("max_list");++$i){
    if($subject_id==$row[$i]){
        //请求~sql_save.php
        $save_id=$i;
        $save_url="http://127.0.0.1/bangumi/api/save/sql_save.php?subject_id=0&save_id={$save_id}&type={$_GET['type']}&to={$to}&from={$from}&access=".constant("password");
        $need_reply=false;
        file_get_contents($save_url);
    }
}
}


//回复qq消息
if($need_reply)
\access\send_msg($type,$to,$re_msg,constant('token'));
?>
