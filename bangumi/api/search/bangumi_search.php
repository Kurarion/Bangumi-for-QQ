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
//当前存在问题：Bangumi API 限制搜索次数，Json返回html页面
$type='send_'.$_GET['type'].'_msg';
$to=$_GET['to'];
//$from=$_GET['from'];
//bangumi api URL
$search_string=$_GET['search_string'];
$search_type=$_GET['search_type']!=null?$_GET['search_type']:0;
$search_start=$_GET['search_start']!=null?$_GET['search_start']:0;
$search_max=$_GET['search_max']!=null?$_GET['search_max']:5;
//$user_access_token=\access\get_access_token($type,$to,$from);
//.'&access_token='.$user_access_token
$url='https://api.bgm.tv/search/subject/'.$search_string.'?type='.$search_type.'&start='.$search_start.'&max_results='.$search_max;
//bangumi JSON
$json=file_get_contents($url);
$data=json_decode($json,true);
//Example Json:
/*
 * {
    "results": 856,
    "list": [
        {
            "id": 68618,
            "url": "http://bgm.tv/subject/68618",
            "type": 2,
            "name": "小电脑和老箱子",
            "name_cn": "小电脑和老箱子",
            "summary": "",
            "air_date": "",
            "air_weekday": 0,
            "images": {
                "large": "http://lain.bgm.tv/pic/cover/l/65/52/68618_cyD99.jpg",
                "common": "http://lain.bgm.tv/pic/cover/c/65/52/68618_cyD99.jpg",
                "medium": "http://lain.bgm.tv/pic/cover/m/65/52/68618_cyD99.jpg",
                "small": "http://lain.bgm.tv/pic/cover/s/65/52/68618_cyD99.jpg",
                "grid": "http://lain.bgm.tv/pic/cover/g/65/52/68618_cyD99.jpg"
            }
        },
        {
            "id": 57998,
            "url": "http://bgm.tv/subject/57998",
            "type": 4,
            "name": "Virtual-On Force",
            "name_cn": "电脑战机 Force",
            "summary": "",
            "air_date": "",
            "air_weekday": 0,
            "images": {
                "large": "http://lain.bgm.tv/pic/cover/l/7f/dd/57998_sT4b6.jpg",
                "common": "http://lain.bgm.tv/pic/cover/c/7f/dd/57998_sT4b6.jpg",
                "medium": "http://lain.bgm.tv/pic/cover/m/7f/dd/57998_sT4b6.jpg",
                "small": "http://lain.bgm.tv/pic/cover/s/7f/dd/57998_sT4b6.jpg",
                "grid": "http://lain.bgm.tv/pic/cover/g/7f/dd/57998_sT4b6.jpg"
            }
        },
 * }
 *
 * {
    "request": "/search/subject/%E7%94%B5%E8%84%91fhghghg?type=&start=&max_results=",
    "code": 404,
    "error": "Not Found"
 * }
 */
//echo $json;
//\access\send_msg($type,$to,$url."  \n ".var_export($json),constant('token'));
//判断是否无效
//注意这里result默认存在一个Null，因此实际结果数是number-1
if($search_string==null||array_key_exists('error',$data)||(array_key_exists('results',$data)?$data['results']<2:ture))
{
    //未找到...
    $msg=array(
        array('type'=>"text",
            'data'=>array(
                'text'=>"未找到相关条目..."
            )
        )
    );
}
else{
    //总的结果数目
    $results_num=$data['results'];
    //$data['list']
    $msg=array(
        array('type'=>"text",
            'data'=>array(
                'text'=>"总计相符条目数: $results_num\n\n"
            )
        )
    );
    for($num=0;$num<count($data['list']);){
        $subject=$data['list'][$num];
        $subject_id=$subject['id'];
        $subject_url=$subject['url'];
        $subject_type=$type2name[$subject['type']];
        $subject_name=$subject['name'];
        $subject_name_cn=$subject['name_cn'];
        if($subject['images']!=null){
            $subject_img=$subject['images']['large'];
        }else{
            $subject_img="http://www.irisu.cc/res/no_img.gif";
        }
        ++$num;
        //msg
        $subject_msg=array(
            array('type'=>"text",
                'data'=>array(
                    'text'=>"_____<$num>_____\n"
                )
            ),
            array('type'=>"image",
                'data'=>array(
                    'file'=>$subject_img
                )
            ),
            array('type'=>"text",
                'data'=>array(
                    // 'text'=>"\n".$subject_name_cn.
                    //     "\n"."< ".$subject_name." >".
                    //     "\n<-".$subject_type."->   ID: ".$subject_id.
                    //     "\n条目主页:".$subject_url.
                    //     "\n\n"
                    'text'=>"\n$subject_name_cn\n< $subject_name >\n<-$subject_type->   ID: $subject_id\n条目主页:$subject_url\n\n"
                )
            )

        );
        $msg=array_merge($msg,$subject_msg);
    }
    $end_msg=array(
        array('type'=>"text",
            'data'=>array(
                'text'=>"总计相符条目数: $results_num"
            )
        )
    );
    $msg=array_merge($msg,$end_msg);
}
//send_message
//require 'qq_search.php';
\access\send_msg($type,$to,$msg,constant('token'));
?>