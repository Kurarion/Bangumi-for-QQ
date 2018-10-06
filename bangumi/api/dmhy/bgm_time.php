<?php
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
//一条信息多少个条目 防止消息过长
define('max_num',10);
//date
date_default_timezone_set("Asia/Shanghai");
$today=date('w')==0?6:date('w')-1;
//bangumi api URL
$air_date=$_GET['air_date']==null?$today:(is_numeric($_GET['air_date'])?((int)$_GET['air_date']>0&&(int)$_GET['air_date']<8?(((int)($_GET['air_date']))-1):$today):$today);
$air_all_date=$_GET['air_date']=="all"?true:false;

$url='https://api.bgm.tv/calendar';
//bangumi JSON
$json=file_get_contents($url);
$data=json_decode($json,true);
//Example Json:


//有效
//echo '\n'.var_dump($today).'\n';
//确定查询的list
$air_date_msg=$int2weekday[$data[$air_date]['weekday']['id']];
//echo '\nair_date_msg: '.$air_date_msg.'\n';
$list=$data[$air_date]['items'];
//echo '\nair_date: '.$air_date.'\n';
//开始循环处理
$list_num=count($list);

//echo '\nlist_num: '.$list_num.'\n';
//echo '\ntoday: '.$today.'\n';
//起始信息
$msg=array(
    array('type'=>"text",
        'data'=>array(
            'text'=>"$air_date_msg  总计:  ".$list_num."部\n\n"
        )
    ));
for($send_num_id=0;$send_num_id<($list_num/constant('max_num'));++$send_num_id){
    //准备第N段消息
    for($list_id=$send_num_id*constant('max_num');$list_id<min($list_num,($send_num_id+1)*constant('max_num'));++$list_id){

        $subject=$list[$list_id];
        //条目基本信息
        $subject_id=$subject['id'];
        $subject_url=$subject['url'];
        $subject_type=$type2name[$subject['type']];
        $state=$type2state[$subject['type']];
        $subject_name=$subject['name'];
        //$subject_name_cn=$subject['name_cn']!=null?$subject['name_cn']:"暂无";
        $subject_name_cn=$subject['name_cn'];
        $subject_summary=$subject['summary'];
        //$subject_eps=$subject['eps_count']!=null?$subject['eps_count']:"无";
        //$subject_eps=$subject['eps_count'];
        //$subject_air_date=$subject['air_date']!=null?$subject['air_date']:"未知";
        $subject_air_date=$subject['air_date'];
        //$subject_air_weekday=$subject['air_weekday']!=null?$int2weekday[$subject['air_weekday']]:"未知";
        $subject_air_weekday=$subject['air_weekday'];

        $subject_rating=$subject['rating'];
        $subject_rating_num=$subject_rating['total']==null?"0":$subject_rating['total'];
        $subject_rating_average=$subject_rating['score']==null?"无":$subject_rating['score'];

        $subject_rank=$subject['rank']!=null?$subject['rank']:"无";
        if($subject['images']!=null){
            $subject_img=$subject['images']['large'];
        }else{
            $subject_img="http://www.irisu.cc/res/no_img.gif";
        }

        //条目收藏状态
        $subject_collection=$subject['collection'];
        //$subject_collection_wish=$subject_collection['wish']==null?"0":$subject_collection['wish'];
        $subject_collection_doing=$subject_collection['doing']==null?"0":$subject_collection['doing'];
        //$subject_collection_on_hold=$subject_collection['on_hold']==null?"0":$subject_collection['on_hold'];
        //$subject_collection_dropped=$subject_collection['dropped']==null?"0":$subject_collection['dropped'];
        //$subject_collection_collection=$subject_collection['collect']==null?"0":$subject_collection['collect'];
        //$subject_collection_over=$subject_collection_collection-$subject_collection_dropped-$subject_collection_on_hold-$subject_collection_doing-$subject_collection_wish;
        //条目部分的最终结果
        /*
         *              "\n中文名:  ".$subject_name_cn.
                        "\n原名:  ".$subject_name.
                        "\n话数:  ".$subject_eps.
                        "\n放送日期:  ".$subject_air_date.
                        "\n放送星期:  ".$subject_air_weekday.
                        "\n类型:  ".$subject_type."      ID: ".$subject_id.
                        "\n简介:  ".$subject_summary.
        */
        $subject_name_cn_fin=$subject_name_cn==null?"":("\n中文名:  $subject_name_cn");
        $subject_name_fin=$subject_name==null?"":("\n原名:  $subject_name");
        //$subject_eps_fin=$subject_eps==null?"":("\n话数:  ".$subject_eps);
        $subject_air_date_fin=$subject_air_date=="0000-00-00"?"":("\n放送日期:  $subject_air_date");
        $int2weekday_result=$int2weekday[$subject_air_weekday];
        $subject_air_weekday_fin=$subject_air_weekday==null?"":("\n放送星期:  $int2weekday_result");
        $subject_type_id_fin="\n类型:  $subject_type      ID: $subject_id";
        $subject_summary_fin=$subject_summary==null?"":("\n简介:  $subject_summary");
        //最终结果
        //$subject_msg_part_fin=$subject_name_cn_fin.$subject_name_fin.$subject_eps_fin.$subject_air_date_fin.$subject_air_weekday_fin.$subject_type_id_fin.$subject_summary_fin;
        $subject_msg_part_fin=$subject_name_cn_fin.$subject_name_fin.$subject_air_date_fin.$subject_air_weekday_fin.$subject_type_id_fin.$subject_summary_fin;

        //msg
        $subject_msg_all=array(
            array('type'=>"image",
                'data'=>array(
                    'file'=>$subject_img
                )
            ),
            array('type'=>"text",
                'data'=>array(
                    'text'=>
//                    "\n中文名:  ".$subject_name_cn.
//                    "\n原名:  ".$subject_name.
//                    "\n话数:  ".$subject_eps.
//                    "\n放送日期:  ".$subject_air_date.
//                    "\n放送星期:  ".$subject_air_weekday.
//                    "\n类型:  ".$subject_type."      ID: ".$subject_id.
//                    "\n简介:  ".$subject_summary.
                        "{$subject_msg_part_fin}\n\n排名:  {$subject_rank}\n评分:  {$subject_rating_average}      评分数: {$subject_rating_num}\n在{$state}用户数:  {$subject_collection_doing}\n条目主页:  {$subject_url}\n_____\n"

                )
            )
        );
        $msg=array_merge($msg,$subject_msg_all);
    }
    $end_msg=array(
        array('type'=>"text",
            'data'=>array(
                'text'=>"\n$air_date_msg  总计:  {$list_num}部\n\n---来自Bangumi娘的定时汇报！"
            )
        ));
    $msg=array_merge($msg,$end_msg);
    //发送第N段msg
    //send_message
    //require 'qq_search.php';
    $type='send_'.$argv[2].'_msg';
    $to=$argv[3];
    \access\send_msg($type,$to ,$msg,constant('token'));
    //初始化msg
    $continue_num=($send_num_id+1);
    $msg=array(
        array('type'=>"text",
            'data'=>array(
                'text'=>"$air_date_msg  总计:  {$list_num}部--续<{$continue_num}>\n\n"
            )
        ));
}




