<?php
/**
 * Created by PhpStorm.
 * User: Cocoa
 * Date: 2018/6/10
 * Time: 18:31
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
//参数
$type='send_'.$_GET['type'].'_msg';
$to=$_GET['to'];
$from=$_GET['from'];

$subject_id=$_GET['subject_id'];
$subject_col=$_GET['subject_col'];
$subject_detail=$_GET['subject_detail']=="*"?true:false;
if(!$subject_detail){
    $subject_rating=$_GET['subject_detail'];
    $subject_comment=$_GET['subject_rating'];
}else{
    $subject_rating=$_GET['subject_rating'];
    $subject_comment=$_GET['subject_comment'];
}
//\access\send_msg($type,$to,$_GET['subject_detail']."  ".$subject_col,constant('token'));
//\access\send_msg($type,$to,"subject_id=".$subject_id."&subject_col=".$subject_col."&subject_detail=".$subject_detail."&subject_rating=".$subject_rating."&subject_comment".$subject_comment,constant('token'));
//事先判断用户的权限
$user_access_token=\access\get_access_token($type,$to,$from);
if($user_access_token===false){
    //没有用户或者其他情况
    \access\send_msg($type,$to,"不考虑成为魔法少女么？",constant('token'));
    die();
}
//进行subject id decode
//是否使用last
$use_last=false;
//是否使用[]
$use_save=false;
//是否进行请求bangumi API
$need_bgm_api=true;
//decode_subject_id
$decode_subject_id=0;
//\access\send_msg($type,$to,"$subject_id use_save=true",constant('token'));
//这里是用作情况二的解码识别
if($subject_id!=null&&!is_numeric($subject_id)){
    //\access\send_msg($type,$to,"$subject_id".strpos($subject_id,"#"),constant('token'));
    $site=strrpos($subject_id,"#");
    if(false!==$site){
        $decode_subject_id=$subject_id[$site+1];
        $maybe_second_id=$subject_id[$site+2];
        if(is_numeric($maybe_second_id)){
            $decode_subject_id=$decode_subject_id*10+$maybe_second_id;
        }
        //\access\send_msg($type,$to,"$decode_subject_id----",constant('token'));
        if(is_numeric($decode_subject_id)&&$decode_subject_id>=0&&$decode_subject_id<constant("max_list")){
            $use_save=true;
        }//否则识别List_Name
        else{
            $use_save=true;
            $decode_subject_id=substr($subject_id,$site+1);
        }
}
}

//情况一：省略$subject_id （用last代替）
if($subject_col==null||((!is_numeric($subject_id)||$_GET['subject_detail']!=null?(!$subject_detail):false)&&!$use_save)){
    //\access\send_msg($type,$to,"情况一：省略subject_id",constant('token'));
    $subject_id=\access\get_last_subject($type,$to,$from);
    //\access\send_msg($type,$to,"情况一：省略subject_id",constant('token'));
    //如果存在上次的搜索ID
    if($subject_id!=false){
        //直接忽略第二个参数将其代替为第三个参数
        //$re_with_cv=($_GET['subject_id']=="all"||$_GET['subject_id']=="dd")?true:false;
        //$subject_group=($_GET['subject_id']=="detail"||$_GET['subject_id']=="d"||$re_with_cv)?"medium":"small";
        $subject_col=$_GET['subject_id'];
        $subject_detail=$_GET['subject_col']=="*"?true:false;
        if(!$subject_detail){
            $subject_rating=$_GET['subject_col'];
            $subject_comment=$_GET['subject_detail'];
        }else{
            $subject_rating=$_GET['subject_detail'];
            $subject_comment=$_GET['subject_rating'];
        }
        //\access\send_msg($type,$to,"subject_id=".$subject_id."&subject_col=".$subject_col."&subject_detail=".$subject_detail."&subject_rating=".$subject_rating."&subject_comment".$subject_comment,constant('token'));

        //需要请求API
        $need_bgm_api=true;
    }
    else{
        //修正一下原来是Null 这样会导致update 类型不同最终导致query返回false
        //这条支线是有注册/没注册 之前没用过subject
        //因为注定搜不到条目，这里顺手改成0
        $use_last=true;
        $subject_id=0;
        //不需要进行请求api
        $need_bgm_api=false;
    }
    //只要判定了语法是使用上次subid就不进行update操作
    //$search_last=true;
}
//情况二：使用[]获取仓库subject ID
if($use_save){
    //\access\send_msg($type,$to,"情况二：使用[]获取仓库",constant('token'));
    $subject_id=\access\read_save($type,$to,$from,$decode_subject_id);
    //\access\send_msg($type,$to,"情况二：使用[]获取仓库d",constant('token'));
    //需要请求API
    $need_bgm_api=true;
    //如果该值不合法
    if($subject_id==false){
        //设置为0
        $subject_id=0;
        //取消记录
        //$search_last=true;
        //不需要进行请求api
        $need_bgm_api=false;
    }
}
//判断是否继续请求API
if($need_bgm_api){
    //向Bangumi API 请求
    //{wish/collect/do/on_hold/dropped}
    $url_rating=($subject_rating==null)?"":"&rating=".$subject_rating;
    $url_comment=($subject_comment==null)?"":"&comment=".$subject_comment;
    $subject_col=($subject_col==null)?"wish":$subject_col;
    if(!array_key_exists($subject_col,$status2col)){
        $subject_col="wish";
    }
    $data="status="."$subject_col".$url_rating.$url_comment;
    $opts = array (
        'http' => array (
            'method' => 'POST',
            'header' => array("content-type: application/x-www-form-urlencoded",
                "Authorization: Bearer ".$user_access_token),
            'content' => $data
        )
    );
    $url="https://api.bgm.tv/collection/$subject_id/update";
    $context = stream_context_create($opts);
    $json = file_get_contents($url, false, $context);

    $return_data=json_decode($json,true);
    if(array_key_exists("status",$return_data)){
        //可能更新成功
        \access\send_msg($type,$to, $return_data['user']['nickname']." 已收藏条目 ".$subject_id." 为 ".$status2col[$subject_col],constant('token'));
        //\access\send_msg($type,$to,$data,constant('token'));
        //如果需要，直接调用subject
        if($subject_detail){
            \access\send_msg($type,$to,"条目信息祈祷中...",constant('token'));
            $php="/api/subject/bangumi_subject.php?subject_id=".$subject_id;
            $php.="&type=".$_GET['type'];
            $php.="&to=".$to;
            $php.="&from=".$from;
            $php.="&access=".constant("password");
            $url='http://127.0.0.1/bangumi'.$php;


            file_get_contents($url);
        }

    }else{
        //失败
        \access\send_msg($type,$to,"条目 ".$subject_id." 收藏失败...",constant('token'));
    }

}
else{
    \access\send_msg($type,$to,"至少我看不懂你在说什么...",constant('token'));
}
?>