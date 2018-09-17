<?php
/**
 * Created by PhpStorm.
 * User: Cocoa
 * Date: 2018/6/4
 * Time: 23:28
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
//接受参数
$subject_id=$_GET['subject_id'];
$save_id=$_GET['save_id'];
$type='send_'.$_GET['type'].'_msg';
$to=$_GET['to'];
$from=$_GET['from'];
//回复，合法标志
$re_msg="这参数...\n真想让你好好看看《Bangumi娘的食用方法》...".
    "\n虽然根本没有这种东西...";
$para_ok=false;
$have_before="";
$old_id=0;
//\access\send_msg($type,$to,$subject_id."  ".$save_id,constant('token'));
//如果第一个参数不是数字 而是list
if(false!==strpos($subject_id,"#")&&$save_id!=null){
    $decode_subject_id=$subject_id[strpos($subject_id,"#")+1];
    $maybe_second_id=$subject_id[strpos($subject_id,"#")+2];
    if(is_numeric($maybe_second_id)){
        $decode_subject_id=$decode_subject_id*10+$maybe_second_id;
    }
    $subject_id=\access\read_save($type,$to,$from,$decode_subject_id);
    //\access\send_msg($type,$to,$subject_id." sui",constant('token'));
}
if(false!==strpos($save_id,"#")){
    $decode_save_id=$save_id[strpos($save_id,"#")+1];
    $maybe_second_id=$save_id[strpos($save_id,"#")+2];
    if(is_numeric($maybe_second_id)){
        $decode_save_id=$decode_save_id*10+$maybe_second_id;
    }
    $save_id=$decode_save_id;
    //\access\send_msg($type,$to,$save_id." sai",constant('token'));
}
//第二个参数不在或者不合法
//一个参数
if($save_id==null||!is_numeric($save_id)){
    //则判定是第一个参数是仓库ID,则默认subject id是last subject
    if($subject_id!=null&&is_numeric($subject_id)&&$subject_id>0&&$subject_id<constant("max_list")){
        //并且参数一合法
        $save_id=$subject_id;
        $subject_id=\access\get_last_subject($type,$to,$from);
        //\access\send_msg($type,$to,$subject_id." sui ".$save_id,constant('token'));
        //如果不存在合法的last subject
        if($subject_id==false){
            //总之先赋值0，尽管用不上
            $subject_id=0;
            $re_msg="只有使用过~subject 的魔法少女才能驾驭这样的魔法";
        }
        else{
            //存在合法的last subject
            //开始判断是否有这个记录
//            $had_user_sql="select subject_".$save_id." from bgm_subject_memory where user_qq=$from";
//            //只有搜索失败才会$result=false，空值为true
//            $select_result=\access\sql_query($type,$to,$had_user_sql);
//            $row=mysqli_fetch_array($select_result,MYSQLI_NUM);
//            //如果不是空的，表示这是可行的
//            if($row!=false){
//                if($row[0]!=0){
//                    $have_before="（原存放ID为 $row[0] ）";
//                }
//                $para_ok=true;
//            }else{
//                $re_msg="不考虑成为魔法少女么？";
//            }
            //使用已有的函数
            //合法的两个参数
            //开始判断是否有这个记录
            $old_id=\access\read_save($type,$to,$from,$save_id);
            //如果不是空的，表示这是可行的
            if($old_id!==false){
                if($old_id!=0){

                    //$have_before="（原存放ID为 $old_id ）";
                }
                $para_ok=true;
            }else{
                $re_msg="不考虑成为魔法少女么？";
            }

        }

    }
}else{
    //两个参数
    if($save_id!=null&&is_numeric($save_id)&&$save_id>0&&$save_id<constant("max_list")){
        if($subject_id!=null&&is_numeric($subject_id)){
//            //合法的两个参数
//            //开始判断是否有这个记录
//            $had_user_sql="select subject_".$save_id." from bgm_subject_memory where user_qq=$from";
//            //只有搜索失败才会$result=false，空值为true
//            $select_result=\access\sql_query($type,$to,$had_user_sql);
//            $row=mysqli_fetch_array($select_result,MYSQLI_NUM);
//            //如果不是空的，表示这是可行的
//            if($row!=false){
//                if($row[0]!=0){
//                    $have_before="（原存放ID为 $row[0] ）";
//                }
//                $para_ok=true;
//            }else{
//                $re_msg="不考虑成为魔法少女么？";
//            }
            //使用已有的函数
            //合法的两个参数
            //开始判断是否有这个记录
            //\access\send_msg($type,$to,$save_id." sai   ".$subject_id,constant('token'));
            $old_id=\access\read_save($type,$to,$from,$save_id);
            //如果不是空的，表示这是可行的
            if($old_id!==false){
                if($old_id!=0){

                    //$have_before="（原存放ID为 $old_id ）";
                }
                $para_ok=true;
            }else{
                $re_msg="不考虑成为魔法少女么？";
            }

        }
    }
}
if($para_ok){
//
    $get_save_sql="select List_Name
                              from bgm_subject_memory
                              where user_qq=$from";
            $result2=\access\sql_query($type,$to,$get_save_sql);
            $row=mysqli_fetch_array($result2,MYSQLI_NUM);
            $old_name=$row[0];
            //#>02Happy Suger Life<#02
    //修改old
    //计算List_Name位置
    $search_id=$save_id>9?$save_id:('0'.$save_id);
    $front_site=strpos($old_name,'#>'.$search_id);
    $back_site=strpos($old_name,'<#'.$search_id)+4;
    $replacement='#>'.$search_id.'<#'.$search_id;

    if($subject_id!=0){
                //请求bangumi api
        $url='https://api.bgm.tv/subject/'.$subject_id;
        //bangumi JSON
        $json=file_get_contents($url);
        $data=json_decode($json,true);

        $replacement='#>'.$search_id.$data['name_cn'].'('.$data['name'].')<#'.$search_id;
    }
    
    if($front_site!==false){
        $new_name=substr_replace($old_name, $replacement, $front_site,$back_site-$front_site);
    }else{
        $new_name=$old_name.$replacement;
    }
    
    //更新List Name
        $set_save_sql="UPDATE bgm_subject_memory
        SET subject_".$save_id."=$subject_id,List_Name='$new_name'
        WHERE user_qq=$from";
        $result=\access\sql_query($type,$to,$set_save_sql);

    //have before
        if($old_id!=0){
            if($front_site!==false){
                $old_id_name=substr($old_name, $front_site+4, $back_site-$front_site-8);
            }else
            {
                $old_id_name='';
            }
            

            $have_before='（原存放 '.$old_id_name.'['.$old_id.'] ）';
        }
    if($result!=false){
        $re_msg="确认 ".($data['name_cn']!=null?$data['name_cn']:'').($data['name']!=null?('('.$data['name'].')'):'').'['.$subject_id."] 收入至 ".$save_id." 位！".$have_before;
                //"\n$set_save_sql";
    }else{
        $re_msg="save error...";//.$old_name." dddd ".$replacement." xxxx ".$new_name
        //$re_msg="不考虑成为魔法少女么？";
            //"\n$set_save_sql";
    }

}


//回复qq消息
\access\send_msg($type,$to,$re_msg,constant('token'));
?>
