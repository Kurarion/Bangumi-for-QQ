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
$type='send_'.$_GET['type'].'_msg';
$to=$_GET['to'];
$from=$_GET['from'];
//一次发送几个
define("max_num",8);
//接受参数
$save_id=$_GET['save_id'];
$list_detail=false;
if($save_id=="*"){
    $list_detail=true;
}
//\access\send_msg($type,$to,$save_id." sai",constant('token'));
if(false!==strpos($save_id,"#")){
    $decode_save_id=$save_id[strpos($save_id,"#")+1];
    $maybe_second_id=$save_id[strpos($save_id,"#")+2];
    if(is_numeric($maybe_second_id)){
        $decode_save_id=$decode_save_id*10+$maybe_second_id;
    }
    $save_id=$decode_save_id;
    //\access\send_msg($type,$to,$save_id." sai",constant('token'));
}

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
        $re_msg=($data['images']['large']!=null?("[CQ:image,file=".$data['images']['large']."]"):"").
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
                            //放送
                            
                            $date1=date_create($subject_air_date);
                            $date2=date_create(date("Y-m-d"));
                            $diff=date_diff($date1,$date2);
                            $day=$diff->format("%a");
                            $aired_subject_eps=((intval($day/7.0))>$subject_eps)?$subject_eps:(intval($day/7.0));
                            //$user_watched_msg.=date("Y-m-d")."   ".$subject_air_date."   ".$aired_subject_eps;
                            //
                            for($user_watched_msg.="Δ",$j=1;$j<$su_ep;++$j){
                                $user_watched_msg.="-Δ";
                            }
                            if($final_subject_eps!="??"){
                                for($j=$su_ep;$j<$aired_subject_eps;++$j)
                                {
                                    $user_watched_msg.="-Х";
                                }
                                for ($j=0;$j<$subject_eps-$aired_subject_eps;++$j)
                                {
                                    $user_watched_msg .= "-Λ";
                                }
                            }
                            else{
                                $user_watched_msg.="......";
                            }

                        }
                // 的第 [".$su_status['id']."] 个"
                $user_subject_submsg="\n[".$su_user_nick."] 收藏为 [".$su_status['name']."]".
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
                $user_subject_msg="\n\n[CQ:image,file=".$su_user_avatar."]".$user_subject_submsg;
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
        $send_continue=true;
        $i=1;
        for($send_msg_id=0;$send_continue&&$send_msg_id<constant("max_list")/constant("max_num");++$send_msg_id){
            //默认是全列
            global $i;
            //$i<=min(constant("max_list")-1,($send_msg_id+1)*constant("max_num"))
            for($num=0;$num<($send_msg_id+1)*constant("max_num")&&$i<=constant("max_list");++$i){
                //如果检测到已经排查到最后一个list后退出两层循环
                if($i==constant("max_list")){
                    $send_continue=false;
                }
                //排除空位list
                if($row["subject_".$i]==0){
                    continue;
                }else{
                    $num+=1;
                }

                //请求bangumi api
                $url='https://api.bgm.tv/subject/'.$row["subject_".$i];
                //bangumi JSON
                $json=file_get_contents($url);
                $data=json_decode($json,true);

                //https://api.bgm.tv/subject/109956
                $re_msg.=($data['images']['large']!=null&&$list_detail?("[CQ:image,file=".$data['images']['large']."]"):"").
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
                            //放送
                            
                            $date1=date_create($subject_air_date);
                            $date2=date_create(date("Y-m-d"));
                            $diff=date_diff($date1,$date2);
                            $day=$diff->format("%a");
                            $aired_subject_eps=((intval($day/7.0))>$subject_eps)?$subject_eps:(intval($day/7.0));
                            //$user_watched_msg.=date("Y-m-d")."   ".$subject_air_date."   ".$aired_subject_eps;
                            //
                            for($user_watched_msg.="Δ",$j=1;$j<$su_ep;++$j){
                                $user_watched_msg.="-Δ";
                            }
                            if($final_subject_eps!="??"){
                                for($j=$su_ep;$j<$aired_subject_eps;++$j)
                                {
                                    $user_watched_msg.="-Х";
                                }
                                for ($j=0;$j<$subject_eps-$aired_subject_eps;++$j)
                                {
                                    $user_watched_msg .= "-Λ";
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
                            ($list_detail?("\n".$su_user_nick." 的主页:  ".$su_user_url):"");
                        $user_subject_msg=$user_subject_submsg;
                    }
                    else{
                        $user_subject_submsg="\n\n<未收藏>";
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
