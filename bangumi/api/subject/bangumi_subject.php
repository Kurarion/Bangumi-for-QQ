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

//bangumi api URL
$subject_id=$_GET['subject_id'];
$re_with_cv=($_GET['subject_group']=="all"||$_GET['subject_group']=="**")?true:false;
$subject_group=($_GET['subject_group']=="detail"||$_GET['subject_group']=="*"||$re_with_cv)?"medium":"small";

$type='send_'.$_GET['type'].'_msg';
$to=$_GET['to'];
$from=$_GET['from'];
//是否sql的标志
$search_last=false;
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

//    $start_pos=1;
//    $end_pos=strlen($subject_id)-1;
//    $length=$end_pos-$start_pos;
//    //\access\send_msg($type,$to,"$start_pos $end_pos $length ",constant('token'));
//    if($length>0){
//        $decode_subject_id=substr($subject_id,$start_pos,$length);
//        //$var=(string)var_export($decode_subject_id);
//        \access\send_msg($type,$to,"$decode_subject_id----",constant('token'));
//        if(is_numeric($decode_subject_id)&&((int)$decode_subject_id)>=0&&((int)$decode_subject_id)<10){
//            $use_save=true;
//            //\access\send_msg($type,$to,"$decode_subject_id use_save=true",constant('token'));
//        }
//    }

//    $first_decode_subject_id=strstr($subject_id,'[');
//    if($first_decode_subject_id!=false&&strpos($first_decode_subject_id,"]")){
//        if(0!=strpos($first_decode_subject_id,"]")){
//            $para=explode("]",$first_decode_subject_id);
//            $decode_subject_id=str_replace('[','',$para[0]);
//            if(is_numeric($decode_subject_id)&&$decode_subject_id>0&&$decode_subject_id<10){
//                $use_save=true;
//            }
//
//        }
//    }
}

//情况一：省略$subject_id （用last代替）
if($subject_id==null||(!is_numeric($subject_id)&&!$use_save)){
    //\access\send_msg($type,$to,"情况一：省略subject_id",constant('token'));
    $subject_id=\access\get_last_subject($type,$to,$from);
    //\access\send_msg($type,$to,"情况一：省略subject_id",constant('token'));
    //如果存在上次的搜索ID
    if($subject_id!=false){
        //直接忽略第二个参数将其代替为第三个参数
        $re_with_cv=($_GET['subject_id']=="all"||$_GET['subject_id']=="**")?true:false;
        $subject_group=($_GET['subject_id']=="detail"||$_GET['subject_id']=="*"||$re_with_cv)?"medium":"small";
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
    $search_last=true;
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
        $search_last=true;
        //不需要进行请求api
        $need_bgm_api=false;
    }
}
//在这里将$subject_id录入数据库中 如果不是搜索上次ID
if(!$search_last){

//bgm_users(user_qq,user_bangumi,user_access_token,user_refresh_token)
    $set_last_subject_sql="UPDATE bgm_users
        SET user_last_searched=$subject_id
        WHERE user_qq=$from";
    \access\sql_query($type,$to,$set_last_subject_sql);
}
//如果已经判定了参数有误或者没有找到相关的subject id 则取消请求url
$url=null;
$json=null;
$data=null;
if($need_bgm_api){
    global $url;
    global $json;
    global $data;
    //请求bangumi api
    $url='https://api.bgm.tv/subject/'.$subject_id.'?responseGroup='.$subject_group;
    //bangumi JSON
    $json=file_get_contents($url);
    $data=json_decode($json,true);
}else{
    $subject_id=0;
}

//Example Json:
/*
 * {
    "id": 127573,
    "url": "http://bgm.tv/subject/127573",
    "type": 2,
    "name": "ゆるゆり さん☆ハイ！",
    "name_cn": "摇曳百合 3☆High!",
    "summary": "",
    "eps": 12,
    "eps_count": 12,
    "air_date": "2015-10-05",
    "air_weekday": 1,
    "rating": {
        "total": 1333,
        "count": {
            "1": 0,
            "2": 0,
            "3": 0,
            "4": 7,
            "5": 31,
            "6": 151,
            "7": 466,
            "8": 528,
            "9": 107,
            "10": 43
        },
        "score": 7.5
    },
    "rank": 825,
    "images": {
        "large": "http://lain.bgm.tv/pic/cover/l/3e/f2/127573_HfPRJ.jpg",
        "common": "http://lain.bgm.tv/pic/cover/c/3e/f2/127573_HfPRJ.jpg",
        "medium": "http://lain.bgm.tv/pic/cover/m/3e/f2/127573_HfPRJ.jpg",
        "small": "http://lain.bgm.tv/pic/cover/s/3e/f2/127573_HfPRJ.jpg",
        "grid": "http://lain.bgm.tv/pic/cover/g/3e/f2/127573_HfPRJ.jpg"
    },
    "collection": {
        "wish": 335,
        "collect": 1787,
        "doing": 182,
        "on_hold": 115,
        "dropped": 49
    },
    "crt": [
        {
            "id": 13004,
            "url": "http://bgm.tv/character/13004",
            "name": "赤座あかり",
            "name_cn": "赤座灯里",
            "role_name": "主角",
            "images": {
                "large": "http://lain.bgm.tv/pic/crt/l/19/44/13004_crt_kiafp.jpg",
                "medium": "http://lain.bgm.tv/pic/crt/m/19/44/13004_crt_kiafp.jpg",
                "small": "http://lain.bgm.tv/pic/crt/s/19/44/13004_crt_kiafp.jpg",
                "grid": "http://lain.bgm.tv/pic/crt/g/19/44/13004_crt_kiafp.jpg"
            },
            "comment": 95,
            "collects": 257,
            "info": {
                "name_cn": "赤座灯里",
                "alias": {
                    "en": "Akaza Akari",
                    "kana": "あかざ あかり",
                    "romaji": "Akaza Akari",
                    "nick": "阿卡林/アッカリ〜ン"
                },
                "gender": "女",
                "birth": "7月24日",
                "bloodtype": "A型",
                "height": "153cm",
                "年龄": "13"
            },
            "actors": [
                {
                    "id": 5105,
                    "url": "http://bgm.tv/person/5105",
                    "name": "三上枝織",
                    "images": {
                        "large": "http://lain.bgm.tv/pic/crt/l/4b/40/5105_prsn_oG7o7.jpg",
                        "medium": "http://lain.bgm.tv/pic/crt/m/4b/40/5105_prsn_oG7o7.jpg",
                        "small": "http://lain.bgm.tv/pic/crt/s/4b/40/5105_prsn_oG7o7.jpg",
                        "grid": "http://lain.bgm.tv/pic/crt/g/4b/40/5105_prsn_oG7o7.jpg"
                    }
                }
            ]
        },
        {
            "id": 13005,
            "url": "http://bgm.tv/character/13005",
            "name": "歳納京子",
            "name_cn": "岁纳京子",
            "role_name": "主角",
            "images": {
                "large": "http://lain.bgm.tv/pic/crt/l/05/98/13005_crt_o8PHg.jpg?r=1444170198",
                "medium": "http://lain.bgm.tv/pic/crt/m/05/98/13005_crt_o8PHg.jpg?r=1444170198",
                "small": "http://lain.bgm.tv/pic/crt/s/05/98/13005_crt_o8PHg.jpg?r=1444170198",
                "grid": "http://lain.bgm.tv/pic/crt/g/05/98/13005_crt_o8PHg.jpg?r=1444170198"
            },
            "comment": 27,
            "collects": 188,
            "info": {
                "name_cn": "岁纳京子",
                "alias": {
                    "kana": "としのう きょうこ",
                    "romaji": "Toshinou Kyouko"
                },
                "gender": "女",
                "birth": "3月28日",
                "bloodtype": "B",
                "height": "156cm",
                "source": "萌娘百科",
                "年龄": "14"
            },
            "actors": [
                {
                    "id": 6706,
                    "url": "http://bgm.tv/person/6706",
                    "name": "大坪由佳",
                    "images": {
                        "large": "http://lain.bgm.tv/pic/crt/l/55/4b/6706_prsn_Jhsg7.jpg",
                        "medium": "http://lain.bgm.tv/pic/crt/m/55/4b/6706_prsn_Jhsg7.jpg",
                        "small": "http://lain.bgm.tv/pic/crt/s/55/4b/6706_prsn_Jhsg7.jpg",
                        "grid": "http://lain.bgm.tv/pic/crt/g/55/4b/6706_prsn_Jhsg7.jpg"
                    }
                }
            ]
        },
        {
            "id": 13006,
            "url": "http://bgm.tv/character/13006",
            "name": "船見結衣",
            "name_cn": "船见结衣",
            "role_name": "主角",
            "images": {
                "large": "http://lain.bgm.tv/pic/crt/l/7f/f6/13006_crt_44395.jpg?r=1445379779",
                "medium": "http://lain.bgm.tv/pic/crt/m/7f/f6/13006_crt_44395.jpg?r=1445379779",
                "small": "http://lain.bgm.tv/pic/crt/s/7f/f6/13006_crt_44395.jpg?r=1445379779",
                "grid": "http://lain.bgm.tv/pic/crt/g/7f/f6/13006_crt_44395.jpg?r=1445379779"
            },
            "comment": 9,
            "collects": 76,
            "info": {
                "name_cn": "船见结衣",
                "alias": {
                    "romaji": "Funami Yui"
                },
                "gender": "女",
                "年龄": "14"
            },
            "actors": [
                {
                    "id": 5736,
                    "url": "http://bgm.tv/person/5736",
                    "name": "津田美波",
                    "images": {
                        "large": "http://lain.bgm.tv/pic/crt/l/b8/b0/5736_prsn_6MaTt.jpg",
                        "medium": "http://lain.bgm.tv/pic/crt/m/b8/b0/5736_prsn_6MaTt.jpg",
                        "small": "http://lain.bgm.tv/pic/crt/s/b8/b0/5736_prsn_6MaTt.jpg",
                        "grid": "http://lain.bgm.tv/pic/crt/g/b8/b0/5736_prsn_6MaTt.jpg"
                    }
                }
            ]
        },
        {
            "id": 13007,
            "url": "http://bgm.tv/character/13007",
            "name": "吉川ちなつ",
            "name_cn": "吉川千夏",
            "role_name": "主角",
            "images": {
                "large": "http://lain.bgm.tv/pic/crt/l/4c/ee/13007_crt_U3Pqv.jpg?r=1444773044",
                "medium": "http://lain.bgm.tv/pic/crt/m/4c/ee/13007_crt_U3Pqv.jpg?r=1444773044",
                "small": "http://lain.bgm.tv/pic/crt/s/4c/ee/13007_crt_U3Pqv.jpg?r=1444773044",
                "grid": "http://lain.bgm.tv/pic/crt/g/4c/ee/13007_crt_U3Pqv.jpg?r=1444773044"
            },
            "comment": 6,
            "collects": 43,
            "info": {
                "name_cn": "吉川千夏",
                "alias": {
                    "romaji": "Yoshikawa Chinatsu",
                    "nick": "China-chan、Chinacchi(ゆるゆり♪♪ep.2自述)",
                    "又nick": "China(ゆるゆり♪♪ep.2)"
                },
                "gender": "女",
                "年龄": "13"
            },
            "actors": [
                {
                    "id": 6090,
                    "url": "http://bgm.tv/person/6090",
                    "name": "大久保瑠美",
                    "images": {
                        "large": "http://lain.bgm.tv/pic/crt/l/80/86/6090_prsn_X0AD0.jpg?r=1524487222",
                        "medium": "http://lain.bgm.tv/pic/crt/m/80/86/6090_prsn_X0AD0.jpg?r=1524487222",
                        "small": "http://lain.bgm.tv/pic/crt/s/80/86/6090_prsn_X0AD0.jpg?r=1524487222",
                        "grid": "http://lain.bgm.tv/pic/crt/g/80/86/6090_prsn_X0AD0.jpg?r=1524487222"
                    }
                }
            ]
        },
        {
            "id": 13008,
            "url": "http://bgm.tv/character/13008",
            "name": "杉浦綾乃",
            "name_cn": "杉浦绫乃",
            "role_name": "配角",
            "images": {
                "large": "http://lain.bgm.tv/pic/crt/l/69/60/13008_crt_4B7Zn.jpg?r=1447203401",
                "medium": "http://lain.bgm.tv/pic/crt/m/69/60/13008_crt_4B7Zn.jpg?r=1447203401",
                "small": "http://lain.bgm.tv/pic/crt/s/69/60/13008_crt_4B7Zn.jpg?r=1447203401",
                "grid": "http://lain.bgm.tv/pic/crt/g/69/60/13008_crt_4B7Zn.jpg?r=1447203401"
            },
            "comment": 5,
            "collects": 53,
            "info": {
                "name_cn": "杉浦绫乃",
                "alias": {
                    "romaji": "Sugiura Ayano"
                },
                "gender": "女",
                "年龄": "14"
            },
            "actors": [
                {
                    "id": 5014,
                    "url": "http://bgm.tv/person/5014",
                    "name": "藤田咲",
                    "images": {
                        "large": "http://lain.bgm.tv/pic/crt/l/1b/65/5014_prsn_nYD1a.jpg?r=1514287347",
                        "medium": "http://lain.bgm.tv/pic/crt/m/1b/65/5014_prsn_nYD1a.jpg?r=1514287347",
                        "small": "http://lain.bgm.tv/pic/crt/s/1b/65/5014_prsn_nYD1a.jpg?r=1514287347",
                        "grid": "http://lain.bgm.tv/pic/crt/g/1b/65/5014_prsn_nYD1a.jpg?r=1514287347"
                    }
                }
            ]
        },
        {
            "id": 13010,
            "url": "http://bgm.tv/character/13010",
            "name": "池田千歳",
            "name_cn": "池田千岁",
            "role_name": "配角",
            "images": {
                "large": "http://lain.bgm.tv/pic/crt/l/72/33/13010_crt_Z2IdD.jpg?r=1447801919",
                "medium": "http://lain.bgm.tv/pic/crt/m/72/33/13010_crt_Z2IdD.jpg?r=1447801919",
                "small": "http://lain.bgm.tv/pic/crt/s/72/33/13010_crt_Z2IdD.jpg?r=1447801919",
                "grid": "http://lain.bgm.tv/pic/crt/g/72/33/13010_crt_Z2IdD.jpg?r=1447801919"
            },
            "comment": 5,
            "collects": 34,
            "info": {
                "name_cn": "池田千岁",
                "alias": {
                    "romaji": "Ikeda Chitose"
                },
                "gender": "女"
            },
            "actors": [
                {
                    "id": 5001,
                    "url": "http://bgm.tv/person/5001",
                    "name": "豊崎愛生",
                    "images": {
                        "large": "http://lain.bgm.tv/pic/crt/l/24/b3/5001_prsn_5O2pu.jpg?r=1499593693",
                        "medium": "http://lain.bgm.tv/pic/crt/m/24/b3/5001_prsn_5O2pu.jpg?r=1499593693",
                        "small": "http://lain.bgm.tv/pic/crt/s/24/b3/5001_prsn_5O2pu.jpg?r=1499593693",
                        "grid": "http://lain.bgm.tv/pic/crt/g/24/b3/5001_prsn_5O2pu.jpg?r=1499593693"
                    }
                }
            ]
        },
        {
            "id": 13012,
            "url": "http://bgm.tv/character/13012",
            "name": "大室櫻子",
            "name_cn": "大室樱子",
            "role_name": "配角",
            "images": {
                "large": "http://lain.bgm.tv/pic/crt/l/67/49/13012_crt_xrLRL.jpg?r=1445999756",
                "medium": "http://lain.bgm.tv/pic/crt/m/67/49/13012_crt_xrLRL.jpg?r=1445999756",
                "small": "http://lain.bgm.tv/pic/crt/s/67/49/13012_crt_xrLRL.jpg?r=1445999756",
                "grid": "http://lain.bgm.tv/pic/crt/g/67/49/13012_crt_xrLRL.jpg?r=1445999756"
            },
            "comment": 8,
            "collects": 52,
            "info": {
                "name_cn": "大室樱子",
                "alias": {
                    "romaji": "Ohmuro Sakurako"
                },
                "gender": "女",
                "年龄": "13"
            },
            "actors": [
                {
                    "id": 4850,
                    "url": "http://bgm.tv/person/4850",
                    "name": "加藤英美里",
                    "images": {
                        "large": "http://lain.bgm.tv/pic/crt/l/d0/75/4850_prsn_949ql.jpg",
                        "medium": "http://lain.bgm.tv/pic/crt/m/d0/75/4850_prsn_949ql.jpg",
                        "small": "http://lain.bgm.tv/pic/crt/s/d0/75/4850_prsn_949ql.jpg",
                        "grid": "http://lain.bgm.tv/pic/crt/g/d0/75/4850_prsn_949ql.jpg"
                    }
                }
            ]
        },
        {
            "id": 13011,
            "url": "http://bgm.tv/character/13011",
            "name": "古谷向日葵",
            "name_cn": "古谷向日葵",
            "role_name": "配角",
            "images": {
                "large": "http://lain.bgm.tv/pic/crt/l/52/68/13011_crt_Q0abK.jpg?r=1446599422",
                "medium": "http://lain.bgm.tv/pic/crt/m/52/68/13011_crt_Q0abK.jpg?r=1446599422",
                "small": "http://lain.bgm.tv/pic/crt/s/52/68/13011_crt_Q0abK.jpg?r=1446599422",
                "grid": "http://lain.bgm.tv/pic/crt/g/52/68/13011_crt_Q0abK.jpg?r=1446599422"
            },
            "comment": 8,
            "collects": 41,
            "info": {
                "name_cn": "古谷向日葵",
                "alias": {
                    "romaji": "Furutani Himawari"
                },
                "gender": "女",
                "年龄": "13"
            },
            "actors": [
                {
                    "id": 6707,
                    "url": "http://bgm.tv/person/6707",
                    "name": "三森すずこ",
                    "images": {
                        "large": "http://lain.bgm.tv/pic/crt/l/ed/4c/6707_prsn_L9cV9.jpg?r=1453948196",
                        "medium": "http://lain.bgm.tv/pic/crt/m/ed/4c/6707_prsn_L9cV9.jpg?r=1453948196",
                        "small": "http://lain.bgm.tv/pic/crt/s/ed/4c/6707_prsn_L9cV9.jpg?r=1453948196",
                        "grid": "http://lain.bgm.tv/pic/crt/g/ed/4c/6707_prsn_L9cV9.jpg?r=1453948196"
                    }
                }
            ]
        },
        {
            "id": 27906,
            "url": "http://bgm.tv/character/27906",
            "name": "松本 りせ",
            "name_cn": "松本理世",
            "role_name": "配角",
            "images": {
                "large": "http://lain.bgm.tv/pic/crt/l/d8/7e/27906_crt_R9WqN.jpg?r=1450230204",
                "medium": "http://lain.bgm.tv/pic/crt/m/d8/7e/27906_crt_R9WqN.jpg?r=1450230204",
                "small": "http://lain.bgm.tv/pic/crt/s/d8/7e/27906_crt_R9WqN.jpg?r=1450230204",
                "grid": "http://lain.bgm.tv/pic/crt/g/d8/7e/27906_crt_R9WqN.jpg?r=1450230204"
            },
            "comment": 13,
            "collects": 27,
            "info": {
                "name_cn": "松本理世",
                "alias": {
                    "jp": "松本りせ",
                    "romaji": "Matsumoto Rise"
                },
                "gender": "女"
            },
            "actors": [
                {
                    "id": 4772,
                    "url": "http://bgm.tv/person/4772",
                    "name": "後藤沙緒里",
                    "images": {
                        "large": "http://lain.bgm.tv/pic/crt/l/0b/b0/4772_prsn_cu0h1.jpg",
                        "medium": "http://lain.bgm.tv/pic/crt/m/0b/b0/4772_prsn_cu0h1.jpg",
                        "small": "http://lain.bgm.tv/pic/crt/s/0b/b0/4772_prsn_cu0h1.jpg",
                        "grid": "http://lain.bgm.tv/pic/crt/g/0b/b0/4772_prsn_cu0h1.jpg"
                    }
                }
            ]
        }
    ],
    "staff": [
        {
            "id": 6709,
            "url": "http://bgm.tv/person/6709",
            "name": "なもり",
            "name_cn": "",
            "role_name": "",
            "images": {
                "large": "http://lain.bgm.tv/pic/crt/l/7f/51/6709_prsn_y3n44.jpg",
                "medium": "http://lain.bgm.tv/pic/crt/m/7f/51/6709_prsn_y3n44.jpg",
                "small": "http://lain.bgm.tv/pic/crt/s/7f/51/6709_prsn_y3n44.jpg",
                "grid": "http://lain.bgm.tv/pic/crt/g/7f/51/6709_prsn_y3n44.jpg"
            },
            "comment": 3,
            "collects": 0,
            "info": {
                "alias": {
                    "kana": "なもり",
                    "romaji": "Namori"
                },
                "gender": "女",
                "birth": "1987-06-25",
                "bloodtype": "B型",
                "twitter": "@_namori_",
                "HP": "http://www.geocities.jp/elegy_syndrome/",
                "Pixiv ID": "124923"
            },
            "jobs": [
                "原作"
            ]
        },
        {
            "id": 13338,
            "url": "http://bgm.tv/person/13338",
            "name": "畑博之",
            "name_cn": "畑博之",
            "role_name": "",
            "images": null,
            "comment": 1,
            "collects": 0,
            "info": {
                "name_cn": "畑博之",
                "alias": {
                    "kana": "はた ひろゆき",
                    "romaji": "hata hiroyuki"
                }
            },
            "jobs": [
                "导演",
                "脚本"
            ]
        },
        {
            "id": 20820,
            "url": "http://bgm.tv/person/20820",
            "name": "加百優喜雄",
            "name_cn": "加百优喜雄",
            "role_name": "",
            "images": null,
            "comment": 0,
            "collects": 0,
            "info": {
                "name_cn": "加百优喜雄"
            },
            "jobs": [
                "脚本"
            ]
        },
        {
            "id": 20819,
            "url": "http://bgm.tv/person/20819",
            "name": "滝川廉治",
            "name_cn": "泷川廉治",
            "role_name": "",
            "images": null,
            "comment": 0,
            "collects": 0,
            "info": {
                "name_cn": "泷川廉治"
            },
            "jobs": [
                "脚本"
            ]
        },
        {
            "id": 8267,
            "url": "http://bgm.tv/person/8267",
            "name": "深見真",
            "name_cn": "深见真",
            "role_name": "",
            "images": {
                "large": "http://lain.bgm.tv/pic/crt/l/48/b6/8267_prsn_iEYMG.jpg",
                "medium": "http://lain.bgm.tv/pic/crt/m/48/b6/8267_prsn_iEYMG.jpg",
                "small": "http://lain.bgm.tv/pic/crt/s/48/b6/8267_prsn_iEYMG.jpg",
                "grid": "http://lain.bgm.tv/pic/crt/g/48/b6/8267_prsn_iEYMG.jpg"
            },
            "comment": 5,
            "collects": 0,
            "info": {
                "name_cn": "深见真",
                "alias": {
                    "kana": "ふかみまこと",
                    "romaji": "Fukami Makoto"
                },
                "gender": "男",
                "birth": "1977年8月5日"
            },
            "jobs": [
                "脚本"
            ]
        },
        {
            "id": 13069,
            "url": "http://bgm.tv/person/13069",
            "name": "平牧大輔",
            "name_cn": "平牧大辅",
            "role_name": "",
            "images": null,
            "comment": 0,
            "collects": 0,
            "info": {
                "name_cn": "平牧大辅",
                "alias": {
                    "kana": "ひらまき だいすけ",
                    "romaji": "hiramaki daisuke"
                }
            },
            "jobs": [
                "分镜"
            ]
        },
        {
            "id": 731,
            "url": "http://bgm.tv/person/731",
            "name": "相澤昌弘",
            "name_cn": "相泽昌弘",
            "role_name": "",
            "images": null,
            "comment": 0,
            "collects": 0,
            "info": {
                "name_cn": "相泽昌弘",
                "alias": {
                    "0": "相澤伽月",
                    "kana": "あいざわ まさひろ",
                    "romaji": "Aizawa Masahiro"
                },
                "gender": "男"
            },
            "jobs": [
                "分镜"
            ]
        },
        {
            "id": 12879,
            "url": "http://bgm.tv/person/12879",
            "name": "柴田彰久",
            "name_cn": "柴田彰久",
            "role_name": "",
            "images": null,
            "comment": 0,
            "collects": 0,
            "info": {
                "name_cn": "柴田彰久"
            },
            "jobs": [
                "分镜"
            ]
        }
    ]
}
 *
 * {
    "request": "/subject/127573dd?responseGroup=medium",
    "code": 404,
    "error": "Not Found"
 * }
 */
//判断是否无效
if($subject_id==null||array_key_exists('error',$data)||!(array_key_exists('id',$data)))
{
    //未找到...
    $msg=array(
        array('type'=>"text",
            'data'=>array(
                'text'=>"该条目不存在的..."
            )
        )
    );
    if($use_save){
        $msg[0]['data']['text']="我不认为你有用过这个位置...";
    }
    if($use_last){
        $msg[0]['data']['text']="只有使用过~subject 的魔法少女才能驾驭这样的魔法";
    }
    \access\send_msg($type,$to,$msg,constant('token'));
}
else{

    //条目基本信息
    $subject_url=$data['url'];
    $subject_type=$type2name[$data['type']];
    $state=$type2state[$data['type']];
    $subject_name=$data['name'];
    //$subject_name_cn=$data['name_cn']!=null?$data['name_cn']:"暂无";
    $subject_name_cn=$data['name_cn'];
    $subject_summary=$data['summary'];
    //$subject_eps=$data['eps_count']!=null?$data['eps_count']:"无";
    $subject_eps=$data['eps_count'];
    //$subject_air_date=$data['air_date']!=null?$data['air_date']:"未知";
    $subject_air_date=$data['air_date'];
    //$subject_air_weekday=$data['air_weekday']!=null?$int2weekday[$data['air_weekday']]:"未知";
    $subject_air_weekday=$data['air_weekday'];

    $subject_rating=$data['rating'];
    $subject_rating_num=$subject_rating['total']==null?"0":$subject_rating['total'];
    $subject_rating_average=$subject_rating['score']==null?"无":$subject_rating['score'];

    $subject_rank=$data['rank']!=null?$data['rank']:"无";
    if($data['images']!=null){
        $subject_img=$data['images']['large'];
    }else{
        $subject_img="http://www.irisu.cc/res/no_img.gif";
    }


    //条目收藏状态
    $subject_collection=$data['collection'];
    $subject_collection_wish=$subject_collection['wish']==null?"0":$subject_collection['wish'];
    $subject_collection_doing=$subject_collection['doing']==null?"0":$subject_collection['doing'];
    $subject_collection_on_hold=$subject_collection['on_hold']==null?"0":$subject_collection['on_hold'];
    $subject_collection_dropped=$subject_collection['dropped']==null?"0":$subject_collection['dropped'];
    $subject_collection_collection=$subject_collection['collect']==null?"0":$subject_collection['collect'];
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
    $subject_name_cn_fin=$subject_name_cn==null?"":("\n中文名:  ".$subject_name_cn);
    $subject_name_fin=$subject_name==null?"":("\n原名:  ".$subject_name);
    $subject_eps_fin=$subject_eps==null?"":("\n话数:  ".$subject_eps);
    $subject_air_date_fin=$subject_air_date=="0000-00-00"?"":("\n放送日期:  ".$subject_air_date);
    $subject_air_weekday_fin=$subject_air_weekday==null?"":("\n放送星期:  ".$int2weekday[$subject_air_weekday]);
    $subject_type_id_fin="\n类型:  ".$subject_type."      ID: ".$subject_id;
    $subject_summary_fin=$subject_summary==null?"":("\n简介:  ".$subject_summary);
    //最终结果
    $subject_msg_part_fin=$subject_name_cn_fin.$subject_name_fin.$subject_eps_fin.$subject_air_date_fin.$subject_air_weekday_fin.$subject_type_id_fin.$subject_summary_fin;

    //msg
    $msg=array(
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
                    $subject_msg_part_fin.
                    "\n\n排名:  ".$subject_rank.
                    "\n评分:  ".$subject_rating_average."      评分数: ".$subject_rating_num.
                    "\n\n想".$state."用户数:  ".$subject_collection_wish.
                    "\n在".$state."用户数:  ".$subject_collection_doing.
                    "\n".$state."过用户数:  ".$subject_collection_collection.
                    "\n搁置用户数:  ".$subject_collection_on_hold.
                    "\n抛弃用户数:  ".$subject_collection_dropped.
                    //"\n总收藏用户数:  ".$subject_collection_collection.
                    "\n条目主页:  ".$subject_url

            )
        )
    );
    //此处添加用户对条目的信息
    $user_access_token=\access\get_access_token($type,$to,$from);
    if($user_access_token!=false){
        //有token
        //请求bangumi api

        $url_user='https://api.bgm.tv/collection/'.$subject_id."?access_token=".$user_access_token;
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

            $final_subject_rating=$subject_rating['score']==null?"":"<平均: ".$subject_rating['score'].">";
            $final_subject_eps=$subject_eps==null?"??":$subject_eps;
            $user_rating_msg=$su_rating==0?"":"\n评分:  $su_rating   ".$final_subject_rating;
            $user_comment_msg=$su_comment==""?"":"\n吐槽:  $su_comment";
            $user_watched_msg=$su_ep==0?"":"\n完成度: $su_ep/$final_subject_eps \n";
            if($su_ep!=0){
            	//放送
            	date_default_timezone_set("Asia/Tokyo");
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
                	for($i=$su_ep;$i<$aired_subject_eps;++$i)
                	{
                		$user_watched_msg.="-Х";
                	}
                    for ($i=0;$i<$subject_eps-$aired_subject_eps;++$i)
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
                "\n".$su_user_nick." 的主页:  ".$su_user_url;
            $user_subject_msg=array(
                array('type'=>"text",
                    'data'=>array(
                        'text'=>"\n\n"
                    )
                ),
                array('type'=>"image",
                    'data'=>array(
                        'file'=>$su_user_avatar
                    )
                ),
                array('type'=>"text",
                    'data'=>array(
                        'text'=>$user_subject_submsg
                    )
                )
            );
        }
        else{
            $user_subject_submsg="\n\n<未收藏>";
            $user_subject_msg=array(
                array('type'=>"text",
                    'data'=>array(
                        'text'=>$user_subject_submsg
                    )
                )
            );
        }
        $msg=array_merge($msg,$user_subject_msg);


    }
    //分开发送
    \access\send_msg($type,$to,$msg,constant('token'));
    $msg=array(
        array('type'=>"text",
            'data'=>array(
                'text'=>$subject_name_cn_fin.$subject_name_fin
            )
        ));
    //以下medium具备
    //条目角色
    if($subject_group=='medium'){
        $subject_crt=$data['crt'];
        $character_num=count($subject_crt);
        $start_msg=array(
            array('type'=>"text",
                'data'=>array(
                    'text'=>"\n___________".
                        "\n"."目前收录的角色数: ".$character_num.
                        "\n\n"
                )
            )
        );
        $msg=array_merge($msg,$start_msg);
        for($num=0;$num<$character_num;++$num) {
            $character=$subject_crt[$num];
            $character_url=$character['url'];
            $character_name=$character['name'];
            $character_name_cn=$character['name_cn'];
            $character_role=$character['role_name'];
            if($character['images']!=null){
                $character_img=$character['images']['large'];
            }else{
                $character_img="http://www.irisu.cc/res/no_img.gif";
            }

            $character_comment_num=$character['comment'];
            $character_collect_num=$character['collects'];

            $character_info=$character['info'];

            //名字
            $character_name_final=($character_name==null||$character_name=="")?"":("\n日文名:  ".$character_name);
            $character_name_cn_final=($character_name_cn==null||$character_name_cn=="")?"":("\n中文名:  ".$character_name_cn);

            $character_info_alias=$character_info['alias'];
            $character_alias_en=$character_info_alias['en']==null?"":("\n英文名:  ".$character_info_alias['en']);
            $character_alias_kana=$character_info_alias['kana']==null?"":("\n假名:  ".$character_info_alias['kana']);
            $character_alias_romaji=$character_info_alias['romaji']==null?"":("\n罗马音:  ".$character_info_alias['romaji']);
            $character_alias_nick=$character_info_alias['nick']==null?"":("\n别名:  ".$character_info_alias['nick']);
            $start_msg_character_alias_nick=$character_info_alias['nick']==null?"别名:":"        ";
            $character_alias_nick_0=$character_info_alias['0']==null?"": ("\n".$start_msg_character_alias_nick."  ".$character_info_alias['0']);
            $character_alias_nick_1=$character_info_alias['1']==null?"":("\n         ".$character_info_alias['1']);
            $character_alias_nick_2=$character_info_alias['2']==null?"":("\n         ".$character_info_alias['2']);
            //名字的结果字符串
            $character_alias=$character_name_final.$character_name_cn_final.$character_alias_en.
                                $character_alias_kana.$character_alias_romaji.$character_alias_nick.
                                    $character_alias_nick_0.$character_alias_nick_1.$character_alias_nick_2;


            $character_gender=$character_info['gender'];
            $character_birth=$character_info['birth'];
            $character_bloodtype=$character_info['bloodtype'];
            $character_height=$character_info['height'];
            $character_years=$character_info['年龄'];

            //个人资料
            $character_gender_fin=$character_gender==null?"":("\n性别:  ".$character_gender);
            $character_years_fin=$character_years==null?"":("\n年龄:  ".$character_years);
            $character_birth_fin=$character_birth==null?"":("\n生日:  ".$character_birth);
            $character_bloodtype_fin=$character_bloodtype==null?"":("\n血型:  ".$character_bloodtype);
            $character_height_fin=$character_height==null?"":("\n身高:  ".$character_height);
            //个人资料的结果字符串
            $character_infomation_fin=$character_gender_fin.$character_years_fin.$character_birth_fin.$character_bloodtype_fin.$character_height_fin;

            //可能多个CV
            $actor_msg_fin=array( array(
                'type'=>"text",
                'data'=>array(
                    'text'=>""
                )
            ));
            $actor_size=count($character['actors']);
            //应对CV为空的情况
            if($re_with_cv&&$actor_size==0){
                $actor_msg_fin=array(
                    array(
                        'type'=>"text",
                        'data'=>array(
                            'text'=>"CV:  "
                        )
                    ),
                    array(
                        'type'=>"text",
                        'data'=>array(
                            'text'=>"未收录\n\n"
                        )
                    )
                );
            }
            for($actor_num=0;$re_with_cv&&$actor_num<$actor_size;++$actor_num){
                $character_actor=$character['actors'][$actor_num];
                $actor_url=$character_actor['url'];
                $actor_name=$character_actor['name'];
                if($character_actor['images']!=null){
                    $actor_img=$character_actor['images']['large'];
                }else{
                    $actor_img="http://www.irisu.cc/res/no_img.gif";
                }


                //声优
                //声优最终头像
                $actor_msg_img_fin=array(
                    array(
                        'type'=>"text",
                        'data'=>array(
                            'text'=>"CV:  "
                        )
                    ),
                    array(
                        'type'=>"text",
                        'data'=>array(
                            'text'=>"未收录"
                        )
                    )
                );
                //处理声优最终头像
                if($actor_url!=null)
                {
                    $actor_msg_img_fin[0]['data']['text']="CV".($actor_size>1?(" (".($actor_num+1).")"):"").":\n";
                    $actor_msg_img_fin[1]['type']="image";
                    $actor_msg_img_fin[1]['data']=array(
                        'file'=>$actor_img
                    );
                }
                //声优其他信息
                $actor_name_fin=$actor_name==null?"":("\n姓名:  ".$actor_name);
                $actor_url_fin=$actor_url==null?"":("\nCV主页:  ".$actor_url);
                //声优信息的结果字符串
                $actor_msg_text_fin=array(
                    array('type'=>"text",
                        'data'=>array(
                            'text'=>
                                $actor_name_fin.
                                $actor_url_fin.
                                "\n".($actor_size-1==$actor_num?"----------------\n\n":"\n")
                        )
                    ),
                );

                $actor_msg_fin=array_merge($actor_msg_fin,array_merge($actor_msg_img_fin,$actor_msg_text_fin));

            }


            $character_msg=array(
                array('type'=>"image",
                    'data'=>array(
                        'file'=>$character_img
                    )
                ),
                array('type'=>"text",
                    'data'=>array(
                        'text'=>
                            $character_alias.
                            $character_infomation_fin.
                            "\n角色主页:  ".$character_url.
                            "\n"
                    )
                )
//                array('type'=>"image",
//                    'data'=>array(
//                        'file'=>$actor_img
//                    )
//                ),
//                array('type'=>"text",
//                    'data'=>array(
//                        'text'=>
//                            "\n姓名:  ".$acteor_nam.
//                            "\n主页:  ".$actor_url.
//                            "\n_________\n\n"
//                    )
//                )
            );
            //如果是附加cv信息
            if($re_with_cv){
                //合并角色和声优msg到角色msg中
                $character_msg=array_merge($character_msg,$actor_msg_fin);
            }
            else{
                $character_msg[1]['data']['text'].="\n";
            }


            $msg=array_merge($msg,$character_msg);

        }
        \access\send_msg($type,$to,$msg,constant('token'));
    }




    //条目staff <-搁置->
    //显示关联的用户的进度情况
    //首先判断是否是魔法少女
//    $had_user_sql="select user_qq,user_bangumi from bgm_users where user_qq=$to";
//    //只有搜索失败才会$result=false，空值为true
//    $result=mysqli_query($con,$had_user_sql);
//    $row=mysqli_fetch_array($result,MYSQLI_ASSOC);
//    if($row!=false){
//        //是魔法少女
//        $row['user_qq'];
//        $row['user_bangumi'];
//
//        $msg.="";
//    }
    //

}

//send_message
//require 'qq_search.php';
//$type='send_'.$_GET['type'].'_msg';
//$to=$_GET['to'];

?>