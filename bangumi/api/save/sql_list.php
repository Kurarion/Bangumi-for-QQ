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
$type="send_{$_GET['type']}_msg";
$to=$_GET['to'];
$from=$_GET['from'];
//一次发送几个
define("max_num",8);
//接受参数
$save_id=$_GET['save_id'];
//
$list_detail=false;
$list_current=false;
$list_day=0;
$list_to_look=false;
$list_current_day=date("w")==0?7:date("w");
//是否读取缓存
$cache_file=$_GET['file']==1?true:false;

if($save_id=="*"){
    $list_detail=true;
}elseif($save_id[0]=="-"){
    $list_detail=true;
    $list_current=true;
    
    if($save_id[1]!=null&&$save_id[1]>0&&$save_id[1]<8){


        //$list_day=7-($save_id[1]+$list_current_day)%7;
        $list_day=$save_id[1]>$list_current_day?$save_id[1]-7-$list_current_day:$save_id[1]-$list_current_day;

        $list_current_day=$save_id[1];
    }
    
}elseif($save_id=="?"){
    $list_detail=true;
    $list_to_look=true;
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
date_default_timezone_set("Asia/Tokyo");
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
if($save_id=="/")
{
		//qq回复msg
		$re_msg="用户 [{$from}]";
	    //遍历查找空位
        $black_msg=null;
        for($h=1;$h<constant("max_list");++$h)
        {

            if($row["subject_{$h}"]==0)
                $black_msg.="[{$h}] ";
        }
        if($black_msg!=null)
        {
            $re_msg.="\n背包空位: {$black_msg}";
        }
        //发送消息
        \access\send_msg($type,$to,$re_msg,constant('token'));
        //结束php
        die();
}
//如果不是空的，表示这是可行的
if($row!=false){
    //如果指定正确的Id
    if($save_id>0&&$save_id<constant("max_list")){
        //subject_id
        $subject_id=$row["subject_{$save_id}"];
        //替换为access中的函数
        /*
        //请求bangumi api
        $url="https://api.bgm.tv/subject/$subject_id";
        //bangumi JSON
        $json=file_get_contents($url);
        $data=json_decode($json,true);
        */

        //test
        //\access\send_msg($type,$to,"old:".($cache_file?'true':'false'),constant('token'));

        $file_last_name='list_one';
        list($data,$cache_file)=\access\request_subject($file_last_name,$subject_id,$cache_file);
        //test
        //\access\send_msg($type,$to,"old:{$_GET['file']}_new:".($cache_file?'true':'false'),constant('token'));
        //test
        //\access\send_msg($type,$to,"path:".constant('cache_file_path')."{$subject_id}_{$file_last_name}.data",constant('token'));
        //test
        //\access\send_msg('send_private_msg',597320012,"sql_save:".dirname(__FILE__),constant('token'));
        $re_msg0="[List]<$save_id>:\n";
        if($cache_file){

            //test
            //\access\send_msg('send_private_msg',597320012,"test:true",constant('token'));

            //从文件中读取
            $re_msg=&$data['msg'];

        }else{
            //https://api.bgm.tv/subject/109956
            $re_msg1=$data['images']['large']!=null?"[CQ:image,file={$data['images']['large']}]":"";
            //$re_msg2="\n[List]<$save_id>: $subject_id";
            $re_msg3=$data['name_cn']!=""?"\n中文名:  {$data['name_cn']}":"";
            $re_msg4=$data['name']!=""?"\n原名:  {$data['name']}":"";
            $type2name_result=$type2name[$data['type']];
            $re_msg5=$data['type']!=""?"\n类型:  {$type2name_result}      ID: $subject_id":"\nID: $subject_id";
            $re_msg6=$data['air_date']=="0000-00-00"||$data['air_date']==null?"":"\n放送日期:  {$data['air_date']}";

            if($data['air_weekday']==null){
                $re_msg7=null;
            }else{
                $int2weekday_result=$int2weekday[$data['air_weekday']];
                $re_msg7="\n放送星期:   $int2weekday_result";
            }
            
            $re_msg8=$data['url']!=""?"\nUrl:  {$data['url']}":"";
            //dmhy
            $dmhy_keyword=\access\get_dmhy_name($data['name_cn'],$data['name']);
            $php_subject_name=$data['name_cn']!=null?$data['name_cn']:$data['name'];
            $dmhy_url=\access\gen_dmhy_php($to,$from,$dmhy_keyword,$subject_id,$php_subject_name,$data['images']['large']);
            $re_msg9="\n# DMHY:  {$dmhy_url} #";

            $re_msg="{$re_msg1}{$re_msg3}{$re_msg4}{$re_msg5}{$re_msg6}{$re_msg7}{$re_msg8}{$re_msg9}";

            //test
            //\access\send_msg('send_private_msg',597320012,"test:false",constant('token'));
            //array
            $data_array=array('msg'=>$re_msg,'eps_count'=>$data['eps_count'],'rating'=>$data['rating'],'air_date'=>$data['air_date'],'name'=>$data['name'],'name_cn'=>$data['name_cn']);
            $serialize_data=serialize($data_array);
            //序列化
            file_put_contents(constant('cache_file_path')."{$subject_id}_{$file_last_name}.data",$serialize_data);
        }
        //if its not for private then delete the DMHY_url
        if($_GET['type']!='private'){
	       $re_msg=preg_replace('/\s#.*#/','',$re_msg);	
        }
 

        $re_msg="{$re_msg0}{$re_msg}";
        //此处添加用户对条目的信息
        //$user_access_token=\access\get_access_token($type,$to,$from);
        if($user_access_token!=false){
            //有token
            //请求bangumi api

            $url_user='https://api.bgm.tv/collection/'.$row["subject_{$save_id}"]."?access_token={$user_access_token}";
            //bangumi JSON
            $json_user=file_get_contents($url_user);
            $data_user=json_decode($json_user,true);
            //echo $data_user;
            //\access\send_msg($type,$to,$json_user." ",constant('token'));
            //如果有收藏
            if(!array_key_exists("error",$data_user)){
                //status{id type name}
                $su_status=&$data_user['status'];
                //rating
                $su_rating=&$data_user['rating'];
                //comment
                $su_comment=&$data_user['comment'];
                //ep_status
                $su_ep=&$data_user['ep_status'];
                //user
                $su_user=&$data_user['user'];
                $su_user_nick=&$su_user['nickname'];
                $su_user_avatar=&$su_user['avatar']['large'];
                $su_user_url=&$su_user['url'];
                //subject
                $subject_eps=&$data['eps_count'];
                $subject_rating=&$data['rating'];
                $subject_air_date=&$data['air_date'];

                $final_subject_rating=$subject_rating['score']==null?"":"<平均: {$subject_rating['score']}>";
                $final_subject_eps=$subject_eps==null?"??":$subject_eps;
                $user_rating_msg=$su_rating==0?"":"\n评分:  $su_rating   {$final_subject_rating}";
                $user_comment_msg=$su_comment==""?"":"\n吐槽:  $su_comment";
                $user_watched_msg=$su_ep==0?"":"\n完成度: $su_ep/$final_subject_eps \n";
                if($su_ep!=0){
                            //放送
                            
                            $date1=date_create($subject_air_date);
                            $date2=date_create(date("Y-m-d"));
                            $diff=date_diff($date1,$date2);
                            $day=$diff->format("%a");
                            if($diff->format("%R")=='+'){
                                $aired_subject_eps=((1+intval($day/7.0))>$subject_eps)?$subject_eps:(1+intval($day/7.0));
                            }
                            else
                            {
                                $aired_subject_eps=0;
                            }
                            //$user_watched_msg.=date("Y-m-d")."   ".$subject_air_date."   ".$aired_subject_eps;
                            //
                            if($aired_subject_eps==$su_ep)
                            {
                                if($su_ep!=1){
                                    for($user_watched_msg.="Δ",$j=1;$j<$su_ep-1;++$j)
                                    {
                                        $user_watched_msg.="-Δ";
                                    }
                                    $user_watched_msg.="-₳";
                                }else{
                                    $user_watched_msg.="₳";
                                }

                            }
                            else
                            {
                                for($user_watched_msg.="Δ",$j=1;$j<$su_ep;++$j)
                                {
                                    $user_watched_msg.="-Δ";
                                }
                                
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
                $user_subject_submsg="[{$su_user_nick}] 收藏为 [{$su_status['name']}]{$user_watched_msg}{$user_rating_msg}{$user_comment_msg}\n{$su_user_nick} 的主页:  {$su_user_url}";
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
                $user_subject_msg="\n\n[CQ:image,file={$su_user_avatar}]\n$user_subject_submsg";
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
            $re_msg.=$user_subject_msg;


        }
        //回复qq消息 分段
        \access\send_msg($type,$to,$re_msg,constant('token'));
        //$re_msg="";

    }
    else{
        $send_continue=true;
        $i=1;
        $num=0;
        $re_msg="吉祥物[背包]列表:\n";
        //遍历查找空位
        $black_msg=null;
        for($h=1;$h<constant("max_list");++$h)
        {

            if($row["subject_{$h}"]==0)
                $black_msg.="[{$h}] ";
        }
        if($black_msg!=null)
        {
            $re_msg.="\n空位: {$black_msg}\n";
        }
        for($send_msg_id=0;$send_continue&&$send_msg_id<constant("max_list")/constant("max_num");++$send_msg_id){
            //默认是全列
            global $i;
            global $num;
            //$i<=min(constant("max_list")-1,($send_msg_id+1)*constant("max_num"))
            for(;$num<($send_msg_id+1)*constant("max_num")&&$i<=constant("max_list");++$i){
                //如果检测到已经排查到最后一个list后退出两层循环
                if($i==constant("max_list")){
                    $send_continue=false;
                }
                //排除空位list
                if($row["subject_{$i}"]==0){
                    continue;
                }else{
                    //$num+=1;
                }
                //请求bangumi api
                $subject_id=$row["subject_{$i}"];
                //$url="https://api.bgm.tv/subject/{$subject_id}";
                //bangumi JSON
                //$json=file_get_contents($url);
                //$data=json_decode($json,true);


                $file_last_name='list_one';
                list($data,$cache_file)=\access\request_subject($file_last_name,$subject_id,$cache_file);
                //test
                //\access\send_msg($type,$to,"old:{$_GET['file']}_new:".($cache_file?'true':'false'),constant('token'));
                //test
                //\access\send_msg($type,$to,"path:".constant('cache_file_path')."{$subject_id}_{$file_last_name}.data",constant('token'));
                //test
                //\access\send_msg('send_private_msg',597320012,"sql_save:".dirname(__FILE__),constant('token'));


                //
                $date1=date_create($data['air_date']);
                $date2=date_create(date("Y-m-d"));
                $diff=date_diff($date1,$date2);
                $day=$diff->format("%a");
                //用户token
                $data_user=null;
                if($user_access_token!=false)
                {
                        $url_user="https://api.bgm.tv/collection/{$subject_id}?access_token={$user_access_token}";
                        //bangumi JSON
                        $json_user=file_get_contents($url_user);
                        $data_user=json_decode($json_user,true);

                        //自动清零看完的动画
                        //调用sql_save

                        if($data_user['ep_status']!=null&&$data['eps_count']!=null&&$data_user['ep_status']>=$data['eps_count'])
                        {
                            $save_id=$i;
                            $save_url="http://127.0.0.1/bangumi/api/save/sql_save.php?subject_id=0&save_id={$save_id}&type={$_GET['type']}&to={$to}&from={$from}&access=".constant("password");

                            file_get_contents($save_url);
                            //提示用户 另发送
                            $subject_last_name=$data['name']==null?'':("({$data['name']})");
                            $subject_name=$data['name_cn']==null?'':("[{$data['name_cn']}]{$subject_last_name}");
                            \access\send_msg($type,$to,"发现[{$i}]号位{$subject_name}已完成，清零之~",constant('token'));
                            //
                            continue;
                        }
                            
                        
                }
                
                
                //排除非当天的番组
                if($list_current)
                {
                    if($diff->format("%R")=='+'){
                        if(($day+$list_day)%7==0){
                            if((1+intval($day/7.0))<=$data['eps_count']||$data['eps_count']==null)
                            {
                                $int2weekday_result=$int2weekday[$list_current_day];
                                $re_msg.="<{$int2weekday_result}>\n";
                            }
                            else
                            {
                                continue;
                            }
                        }
                        else
                        {
                            continue;
                        }


                    }
                    else{
                        continue;
                    }
                }
                //排除看到最新的番
                if($list_to_look)
                {
                    if($user_access_token!=false)
                    {
                        //$url_user='https://api.bgm.tv/collection/'.$row["subject_".$i]."?access_token=".$user_access_token;
                        //bangumi JSON
                        //$json_user=file_get_contents($url_user);
                        //$data_user=json_decode($json_user,true);
                        if(!array_key_exists("error",$data_user))
                        {
                            $su_ep=$data_user['ep_status'];
                            $aired_subject_eps=((1+intval($day/7.0))>$data['eps_count'])?$data['eps_count']:(1+intval($day/7.0));
                            if($su_ep<$aired_subject_eps)
                            {
                                
                                $re_msg.="<待看>\n";
                            }
                            else
                            {
                                continue;
                            }
                        }
                        elseif(array_key_exists("code",$data_user))
                        {
                            $re_msg.="<尚未收藏>\n";
                        }else{
                        	continue;
                        }
                    }
                    else
                    {
                        continue;
                    }
                }
                //
                $num+=1;


                //
                $re_msg0="[List]<$i>:\n";
                $re_msg.=$re_msg0;
                if($cache_file){

                    //test
                    //\access\send_msg('send_private_msg',597320012,"test:true",constant('token'));

                    //从文件中读取
                    if($_GET['type']=='private'){
                        $re_msg.=$data['msg'];
                    }else{
                        $re_msg.=preg_replace('/\s#.*#/','',$data['msg']);
                    }

                }else{
                    //https://api.bgm.tv/subject/109956
                    $re_msg1=$data['images']['large']!=null&&$list_detail?"[CQ:image,file={$data['images']['large']}]":"";
                    //$re_msg2="\n[List]<$i>: $subject_id";
                    $re_msg3=$data['name_cn']!=""?"\n中文名:  {$data['name_cn']}":"";
                    $re_msg4=$data['name']!=""?"\n原名:  {$data['name']}":"";
                    $type2name_result=$type2name[$data['type']];
                    $re_msg5=$data['type']!=""?"\n类型:  {$type2name_result}      ID: $subject_id":"\nID: $subject_id";
                    $re_msg6=($data['air_date']=="0000-00-00"||$data['air_date']==null)?"":"\n放送日期:  {$data['air_date']}";

                    if($data['air_weekday']==null){
                        $re_msg7=null;
                    }else{
                        $int2weekday_result=$int2weekday[$data['air_weekday']];
                        $re_msg7="\n放送星期:   $int2weekday_result";
                    }
                    
                    $re_msg8=$data['url']!=""?"\nUrl:  {$data['url']}":"";
                    //dmhy
		            $dmhy_keyword=\access\get_dmhy_name($data['name_cn'],$data['name']);
                    $php_subject_name=$data['name_cn']!=null?$data['name_cn']:$data['name'];
                    $dmhy_url=\access\gen_dmhy_php($to,$from,$dmhy_keyword,$subject_id,$php_subject_name,$data['images']['large']);
		            $re_msg9="\n# DMHY:  {$dmhy_url} #";

                    $sub_re_msg="{$re_msg1}{$re_msg2}{$re_msg3}{$re_msg4}{$re_msg5}{$re_msg6}{$re_msg7}{$re_msg8}{$re_msg9}\n";
                    if($_GET['type']=='private'){
                        $re_msg.=$sub_re_msg;
                    }else{
                        $re_msg.=preg_replace('/\s#.*#/','',$sub_re_msg);
                    }   
                    //test
                    //\access\send_msg('send_private_msg',597320012,"test:false",constant('token'));
                    //array
                    $data_array=array('msg'=>$sub_re_msg,'eps_count'=>$data['eps_count'],'rating'=>$data['rating'],'air_date'=>$data['air_date'],'name'=>$data['name'],'name_cn'=>$data['name_cn']);
                    $serialize_data=serialize($data_array);
                    //序列化
                    file_put_contents(constant('cache_file_path')."{$subject_id}_{$file_last_name}.data",$serialize_data);
                }
                //$re_msg="{$re_msg0}{$re_msg}";

                //
                //https://api.bgm.tv/subject/109956
                // $re_msg.=($data['images']['large']!=null&&$list_detail?("[CQ:image,file=".$data['images']['large']."]"):"").
                //     "\n[List]<$i>: ".$row["subject_".$i].
                //     ($data['name_cn']!=""?"\n中文名:  ".$data['name_cn']:("")).
                //     ($data['name']!=""?("\n原名:  ".$data['name']):("")).
                //     ($data['type']!=""?("\n类型:  ".$type2name[$data['type']]):("")).
                //     (($data['air_date']=="0000-00-00"||$data['air_date']==null)?"":("\n放送日期:  ".$data['air_date'])).
                //     ($data['air_weekday']==null?"":("\n放送星期:  ".$int2weekday[$data['air_weekday']])).
                //     ($data['url']!=""?("\nUrl:  ".$data['url']):(""));
                //\access\send_msg($type,$to,"i=".$i."  num=".$num,constant('token'));
                // $re_msg1=$data['images']['large']!=null&&$list_detail?"[CQ:image,file={$data['images']['large']}]":"";
                // $re_msg2="\n[List]<$i>: $subject_id";
                // $re_msg3=$data['name_cn']!=""?"\n中文名:  {$data['name_cn']}":"";
                // $re_msg4=$data['name']!=""?"\n原名:  {$data['name']}":"";
                // $type2name_result=$type2name[$data['type']];
                // $re_msg5=$data['type']!=""?"\n类型:  {$type2name_result}":"";
                // $re_msg6=($data['air_date']=="0000-00-00"||$data['air_date']==null)?"":"\n放送日期:  {$data['air_date']}";

                // if($data['air_weekday']==null){
                //     $re_msg7=null;
                // }else{
                //     $int2weekday_result=$int2weekday[$data['air_weekday']];
                //     $re_msg7="\n放送星期:   $int2weekday_result";
                // }
                
                // $re_msg8=$data['url']!=""?"\nUrl:  {$data['url']}":"";

                // $re_msg.="{$re_msg1}{$re_msg2}{$re_msg3}{$re_msg4}{$re_msg5}{$re_msg6}{$re_msg7}{$re_msg8}";
                //此处添加用户对条目的信息
                //$user_access_token=\access\get_access_token($type,$to,$from);
                if($user_access_token!=false){
                    //有token
                    //请求bangumi api
                    
                        //$url_user='https://api.bgm.tv/collection/'.$row["subject_".$i]."?access_token=".$user_access_token;
                        //bangumi JSON
                        //$json_user=file_get_contents($url_user);
                        //$data_user=json_decode($json_user,true); 
                    
                    
                    //echo $data_user;
                    //\access\send_msg($type,$to,$json_user." ",constant('token'));
                    //如果有收藏
                    if(!array_key_exists("error",$data_user)){
                        //status{id type name}
                        $su_status=&$data_user['status'];
                        //rating
                        $su_rating=&$data_user['rating'];
                        //comment
                        $su_comment=&$data_user['comment'];
                        //ep_status
                        $su_ep=&$data_user['ep_status'];
                        //user
                        $su_user=&$data_user['user'];
                        $su_user_nick=&$su_user['nickname'];
                        $su_user_avatar=&$su_user['avatar']['large'];
                        $su_user_url=&$su_user['url'];
                        //subject
                        $subject_eps=&$data['eps_count'];
                        $subject_rating=&$data['rating'];
                        //$subject_air_date=$data['air_date'];

                        // $final_subject_rating=$subject_rating['score']==null?"":"<平均: ".$subject_rating['score'].">";
                        // $final_subject_eps=$subject_eps==null?"??":$subject_eps;
                        // $user_rating_msg=$su_rating==0?"":"\n评分:  $su_rating   ".$final_subject_rating;
                        // $user_comment_msg=$su_comment==""?"":"\n吐槽:  $su_comment";
                        // $user_watched_msg=$su_ep==0?"":"\n完成度: $su_ep/$final_subject_eps \n";
                        $final_subject_rating=$subject_rating['score']==null?"":"<平均: {$subject_rating['score']}>";
                        $final_subject_eps=$subject_eps==null?"??":$subject_eps;
                        $user_rating_msg=$su_rating==0?"":"\n评分:  $su_rating   {$final_subject_rating}";
                        $user_comment_msg=$su_comment==""?"":"\n吐槽:  $su_comment";
                        $user_watched_msg=$su_ep==0?"":"\n完成度: $su_ep/$final_subject_eps \n";
                        if($su_ep!=0){
                            //放送                        
                            if($diff->format("%R")=='+'){
                                $aired_subject_eps=((1+intval($day/7.0))>$subject_eps)?$subject_eps:(1+intval($day/7.0));
                            }
                            else
                            {
                                $aired_subject_eps=0;
                            }
                            //$user_watched_msg.=date("Y-m-d")."   ".$subject_air_date."   ".$aired_subject_eps;
                            //
                            if($aired_subject_eps==$su_ep)
                            {
                                if($su_ep!=1){
                                    for($user_watched_msg.="Δ",$j=1;$j<$su_ep-1;++$j)
                                    {
                                        $user_watched_msg.="-Δ";
                                    }
                                    $user_watched_msg.="-₳";
                                }else{
                                    $user_watched_msg.="₳";
                                }

                            }
                            else
                            {
                                for($user_watched_msg.="Δ",$j=1;$j<$su_ep;++$j)
                                {
                                    $user_watched_msg.="-Δ";
                                }
                                
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
                        $user_subject_submsg="\n[{$su_user_nick}] 收藏为 [{$su_status['name']}]{$user_watched_msg}{$user_rating_msg}{$user_comment_msg}";
                        if($list_detail){
                            $user_subject_submsg.="\n{$su_user_nick} 的主页:  {$su_user_url}";
                        }
                        $user_subject_msg=$user_subject_submsg;
                    }
                    else{
                        $user_subject_submsg="\n<未收藏>";
                        $user_subject_msg=$user_subject_submsg;
                    }
                    $user_subject_msg.= "\n----------------\n";
                    //\access\send_msg($type,$to,"i=".$i." \n".$url_user,constant('token'));
                    //global $re_msg;
                    $re_msg.=$user_subject_msg;


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
