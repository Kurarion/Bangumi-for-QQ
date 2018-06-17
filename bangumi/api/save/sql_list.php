<?php
/**
 * Created by PhpStorm.
 * User: Cocoa
 * Date: 2018/6/4
 * Time: 23:46
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
//一次发送几个
define("max_num",8);
//接受参数
$save_id=$_GET['save_id'];
$type='send_'.$_GET['type'].'_msg';
$to=$_GET['to'];
$from=$_GET['from'];
//qq回复msg
$re_msg="";
//sql查寻
//用户对条目的信息
$user_access_token=\access\get_access_token($type,$to,$from);
//开始判断是否有这个记录
$had_user_sql="select * from bgm_subject_memory where user_qq=$from";
//只有搜索失败才会$result=false，空值为true
$select_result=\access\sql_query($type,$to,$had_user_sql);
$row=mysqli_fetch_array($select_result,MYSQLI_ASSOC);
//如果不是空的，表示这是可行的
if($row!=false){
    //如果指定正确的Id
    if($save_id>0&&$save_id<constant("max_list")){
        //请求bangumi api
        $url='https://api.bgm.tv/subject/'.$row["subject_".$save_id];
        //bangumi JSON
        $json=file_get_contents($url);
        $data=json_decode($json,true);

        //https://api.bgm.tv/subject/109956
        $re_msg="[CQ:image,file=".$data['images']['large']."]".
                "\n[List]<$save_id>: ".$row["subject_".$save_id].
                ($data['name_cn']!=""?"\n中文名:  ".$data['name_cn']:("")).
                ($data['name']!=""?("\n原名:  ".$data['name']):("")).
                ($data['type']!=""?("\n类型:  ".$type2name[$data['type']]):("")).
                ($data['url']!=""?("\nUrl:  ".$data['url']):(""));
        //此处添加用户对条目的信息
        //$user_access_token=\access\get_access_token($type,$to,$from);
        if($user_access_token!=false){
            //有token
            //请求bangumi api

            $url_user='https://api.bgm.tv/collection/'.$row["subject_".$save_id]."?access_token=".$user_access_token;
            //bangumi JSON
            $json_user=file_get_contents($url_user);
            $data_user=json_decode($json_user,true);
            //echo $data_user;
            //\access\send_msg($type,$to,$json_user." ",constant('token'));
            //如果有收藏
            if(!array_key_exists("error",$data_user)){
                //status{id type name}
                $su_status=$data_user['status'];
                //rating
                $su_rating=$data_user['rating'];
                //comment
                $su_comment=$data_user['comment'];
                //ep_status
                $su_ep=$data_user['ep_status'];
                //user
                $su_user=$data_user['user'];
                $su_user_nick=$su_user['nickname'];
                $su_user_avatar=$su_user['avatar']['large'];
                $su_user_url=$su_user['url'];
                //subject
                $subject_eps=$data['eps_count'];
                $subject_rating=$data['rating'];

                $final_subject_rating=$subject_rating['score']==null?"":"<平均: ".$subject_rating['score'].">";
                $final_subject_eps=$subject_eps==null?"??":$subject_eps;
                $user_rating_msg=$su_rating==0?"":"\n评分:  $su_rating   ".$final_subject_rating;
                $user_comment_msg=$su_comment==""?"":"\n吐槽:  $su_comment";
                $user_watched_msg=$su_ep==0?"":"\n完成度: $su_ep/$final_subject_eps \n";
                if($su_ep!=0){
                    for($i=0;$i<$su_ep;++$i){
                        $user_watched_msg.="★";
                    }
                    if($final_subject_eps!="??"){
                        for ($i=0;$i<$subject_eps-$su_ep;++$i) {
                            $user_watched_msg .= "☆";
                        }
                    }
                    else{
                        $user_watched_msg.="......";
                    }

                }
                // 的第 [".$su_status['id']."] 个"
                $user_subject_submsg="\n\n[".$su_user_nick."] 收藏为 [".$su_status['name']."]".
                    $user_watched_msg.
                    $user_rating_msg.
                    $user_comment_msg.
                    "\n".$su_user_nick." 的主页:  ".$su_user_url;
//                $user_subject_msg=array(
//                    array('type'=>"text",
//                        'data'=>array(
//                            'text'=>"\n\n"
//                        )
//                    ),
//                    array('type'=>"image",
//                        'data'=>array(
//                            'file'=>$su_user_avatar
//                        )
//                    ),
//                    array('type'=>"text",
//                        'data'=>array(
//                            'text'=>$user_subject_submsg
//                        )
//                    )
//                );
                $user_subject_msg="\n\n".$user_subject_submsg;
            }
            else{
                $user_subject_submsg="\n\n<未收藏>";
//                $user_subject_msg=array(
//                    array('type'=>"text",
//                        'data'=>array(
//                            'text'=>$user_subject_submsg
//                        )
//                    )
//                );
                $user_subject_msg=$user_subject_submsg;
            }
            global $re_msg;
            $re_msg=$re_msg.$user_subject_msg;


        }
        //回复qq消息 分段
        \access\send_msg($type,$to,$re_msg,constant('token'));
        //$re_msg="";

    }
    else{
        for($send_msg_id=0;$send_msg_id<constant("max_list")/constant("max_num");++$send_msg_id){
            //默认是全列
            for($i=$send_msg_id*constant("max_num")+1;$i<=min(constant("max_list")-1,($send_msg_id+1)*constant("max_num"));++$i){

                //请求bangumi api
                $url='https://api.bgm.tv/subject/'.$row["subject_".$i];
                //bangumi JSON
                $json=file_get_contents($url);
                $data=json_decode($json,true);

                //https://api.bgm.tv/subject/109956
                $re_msg.="[CQ:image,file=".$data['images']['large']."]".
                    "\n[List]<$i>: ".$row["subject_".$i].
                    ($data['name_cn']!=""?"\n中文名:  ".$data['name_cn']:("")).
                    ($data['name']!=""?("\n原名:  ".$data['name']):("")).
                    ($data['type']!=""?("\n类型:  ".$type2name[$data['type']]):("")).
                    ($data['url']!=""?("\nUrl:  ".$data['url']):(""));
                //此处添加用户对条目的信息
                //$user_access_token=\access\get_access_token($type,$to,$from);
                if($user_access_token!=false){
                    //有token
                    //请求bangumi api

                    $url_user='https://api.bgm.tv/collection/'.$row["subject_".$i]."?access_token=".$user_access_token;
                    //bangumi JSON
                    $json_user=file_get_contents($url_user);
                    $data_user=json_decode($json_user,true);
                    //echo $data_user;
                    //\access\send_msg($type,$to,$json_user." ",constant('token'));
                    //如果有收藏
                    if(!array_key_exists("error",$data_user)){
                        //status{id type name}
                        $su_status=$data_user['status'];
                        //rating
                        $su_rating=$data_user['rating'];
                        //comment
                        $su_comment=$data_user['comment'];
                        //ep_status
                        $su_ep=$data_user['ep_status'];
                        //user
                        $su_user=$data_user['user'];
                        $su_user_nick=$su_user['nickname'];
                        $su_user_avatar=$su_user['avatar']['large'];
                        $su_user_url=$su_user['url'];
                        //subject
                        $subject_eps=$data['eps_count'];
                        $subject_rating=$data['rating'];

                        $final_subject_rating=$subject_rating['score']==null?"":"<平均: ".$subject_rating['score'].">";
                        $final_subject_eps=$subject_eps==null?"??":$subject_eps;
                        $user_rating_msg=$su_rating==0?"":"\n评分:  $su_rating   ".$final_subject_rating;
                        $user_comment_msg=$su_comment==""?"":"\n吐槽:  $su_comment";
                        $user_watched_msg=$su_ep==0?"":"\n完成度: $su_ep/$final_subject_eps \n";
                        if($su_ep!=0){
                            for($j=0;$j<$su_ep;++$j){
                                $user_watched_msg.="★";
                            }
                            if($final_subject_eps!="??"){
                                for ($j=0;$j<$subject_eps-$su_ep;++$j) {
                                    $user_watched_msg .= "☆";
                                }
                            }
                            else{
                                $user_watched_msg.="......";
                            }

                        }
                        // 的第 [".$su_status['id']."] 个"
                        $user_subject_submsg="\n\n[".$su_user_nick."] 收藏为 [".$su_status['name']."]".
                            $user_watched_msg.
                            $user_rating_msg.
                            $user_comment_msg.
                            "\n".$su_user_nick." 的主页:  ".$su_user_url;
                        $user_subject_msg=$user_subject_submsg;
                    }
                    else{
                        $user_subject_submsg="\n<未收藏>";
                        $user_subject_msg=$user_subject_submsg;
                    }
                    $user_subject_msg.= "\n----------------\n";
                    //\access\send_msg($type,$to,"i=".$i." \n".$url_user,constant('token'));
                    //global $re_msg;
                    $re_msg=$re_msg.$user_subject_msg;


                }
            }
            //$user_subject_msg.= "\n----------------\n";
            //回复qq消息 分段
            \access\send_msg($type,$to,$re_msg,constant('token'));
            $re_msg="";
        }

    }

}else{
    $re_msg="不考虑成为魔法少女么？";
    //回复qq消息
    \access\send_msg($type,$to,$re_msg,constant('token'));
}

?>