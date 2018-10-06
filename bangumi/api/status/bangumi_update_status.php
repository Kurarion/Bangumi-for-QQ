<?php
/**
 * Created by PhpStorm.
 * User: Cocoa
 * Date: 2018/6/10
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
//参数
$type="send_{$_GET['type']}_msg";
$to=$_GET['to'];
$from=$_GET['from'];

$subject_id=$_GET['subject_id'];
$subject_eps=$_GET['subject_eps'];
$subject_detail=$_GET['subject_detail']=="*"?true:false;
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
if($subject_eps==null||(!is_numeric($subject_eps)&&!$use_save)){
    //\access\send_msg($type,$to,"情况一：省略subject_id",constant('token'));
    $subject_id=\access\get_last_subject($type,$to,$from);
    //\access\send_msg($type,$to,"情况一：省略subject_id",constant('token'));
    //如果存在上次的搜索ID
    if($subject_id!=false){
        //直接忽略第二个参数将其代替为第三个参数
        //$re_with_cv=($_GET['subject_id']=="all"||$_GET['subject_id']=="dd")?true:false;
        //$subject_group=($_GET['subject_id']=="detail"||$_GET['subject_id']=="d"||$re_with_cv)?"medium":"small";
        $subject_eps=$_GET['subject_id'];
        $subject_detail=$_GET['subject_eps']=="*"?true:false;
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
    $data="watched_eps={$subject_eps}&watched_vols={$subject_eps}";
    $opts = array (
        'http' => array (
            'method' => 'POST',
            'header' => array("content-type: application/x-www-form-urlencoded",
                "Authorization: Bearer $user_access_token"),
            'content' => $data
        )
    );
    $url="https://api.bgm.tv/subject/$subject_id/update/watched_eps";
    $context = stream_context_create($opts);
    $json = file_get_contents($url, false, $context);

    //条目搜索
    $subject_name='';
    if($subject_id!=0){
                //请求bangumi api
        $urlx="https://api.bgm.tv/subject/{$subject_id}";
        //bangumi JSON
        $jsonx=file_get_contents($urlx);
        $datax=json_decode($jsonx,true);

        $subject_name=($datax['name_cn']!=null?$datax['name_cn']:'').($datax['name']!=null?("({$datax['name']})"):'');
    }

    $return_data=json_decode($json,true);
    if($return_data['error']=="Accepted"){
        //可能更新成功
        \access\send_msg($type,$to,"条目 {$subject_name}[{$subject_id}] 进度更新成功~",constant('token'));
        //\access\send_msg($type,$to,$_GET['subject_detail']." ",constant('token'));
        //如果需要，直接调用subject
        if($subject_detail){
            \access\send_msg($type,$to,"条目信息祈祷中...",constant('token'));
            $php="/api/subject/bangumi_subject.php?subject_id=$subject_id";
            $php.="&type={$_GET['type']}";
            $php.="&to=$to";
            $php.="&from=$from";
            $php.="&access=".constant("password");
            $url="http://127.0.0.1/bangumi{$php}";


            file_get_contents($url);
        }

    }else{
        //失败
        \access\send_msg($type,$to,"条目 {$subject_name}[{$subject_id}] 进度更新失败...\n请确保有收藏过此条目并且更新的进度与上次不同",constant('token'));
    }

}
else{
    \access\send_msg($type,$to,"至少我看不懂你在说什么...",constant('token'));
}

?>