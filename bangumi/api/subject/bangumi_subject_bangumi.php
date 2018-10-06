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
/*
 * [
    {
        "weekday": {
            "en": "Mon",
            "cn": "星期一",
            "ja": "月耀日",
            "id": 1
        },
        "items": [
            {
                "id": 109956,
                "url": "http://bgm.tv/subject/109956",
                "type": 2,
                "name": "魔法少女 俺",
                "name_cn": "魔法少女俺",
                "summary": "",
                "air_date": "2018-04-02",
                "air_weekday": 1,
                "rating": {
                    "total": 305,
                    "count": {
                        "1": 7,
                        "2": 3,
                        "3": 4,
                        "4": 9,
                        "5": 43,
                        "6": 113,
                        "7": 81,
                        "8": 37,
                        "9": 3,
                        "10": 5
                    },
                    "score": 6.2
                },
                "rank": 3753,
                "images": {
                    "large": "http://lain.bgm.tv/pic/cover/l/2d/0e/109956_b1Bnb.jpg",
                    "common": "http://lain.bgm.tv/pic/cover/c/2d/0e/109956_b1Bnb.jpg",
                    "medium": "http://lain.bgm.tv/pic/cover/m/2d/0e/109956_b1Bnb.jpg",
                    "small": "http://lain.bgm.tv/pic/cover/s/2d/0e/109956_b1Bnb.jpg",
                    "grid": "http://lain.bgm.tv/pic/cover/g/2d/0e/109956_b1Bnb.jpg"
                },
                "collection": {
                    "doing": 698
                }
            },
            {
                "id": 206700,
                "url": "http://bgm.tv/subject/206700",
                "type": 2,
                "name": "実験品家族 -クリーチャーズ・ファミリー・デイズ-",
                "name_cn": "实验品家庭",
                "summary": "",
                "air_date": "2018-04-09",
                "air_weekday": 1,
                "rating": {
                    "total": 72,
                    "count": {
                        "1": 1,
                        "2": 1,
                        "3": 2,
                        "4": 6,
                        "5": 8,
                        "6": 28,
                        "7": 17,
                        "8": 8,
                        "9": 0,
                        "10": 1
                    },
                    "score": 6
                },
                "rank": 3398,
                "images": {
                    "large": "http://lain.bgm.tv/pic/cover/l/3e/3b/206700_e1Izb.jpg",
                    "common": "http://lain.bgm.tv/pic/cover/c/3e/3b/206700_e1Izb.jpg",
                    "medium": "http://lain.bgm.tv/pic/cover/m/3e/3b/206700_e1Izb.jpg",
                    "small": "http://lain.bgm.tv/pic/cover/s/3e/3b/206700_e1Izb.jpg",
                    "grid": "http://lain.bgm.tv/pic/cover/g/3e/3b/206700_e1Izb.jpg"
                },
                "collection": {
                    "doing": 161
                }
            },
            {
                "id": 221127,
                "url": "http://bgm.tv/subject/221127",
                "type": 2,
                "name": "ゴールデンカムイ",
                "name_cn": "黄金神威",
                "summary": "",
                "air_date": "2018-04-09",
                "air_weekday": 1,
                "rating": {
                    "total": 183,
                    "count": {
                        "1": 2,
                        "2": 0,
                        "3": 1,
                        "4": 1,
                        "5": 7,
                        "6": 17,
                        "7": 79,
                        "8": 68,
                        "9": 6,
                        "10": 2
                    },
                    "score": 7.2
                },
                "rank": 1451,
                "images": {
                    "large": "http://lain.bgm.tv/pic/cover/l/0d/59/221127_9Gid3.jpg",
                    "common": "http://lain.bgm.tv/pic/cover/c/0d/59/221127_9Gid3.jpg",
                    "medium": "http://lain.bgm.tv/pic/cover/m/0d/59/221127_9Gid3.jpg",
                    "small": "http://lain.bgm.tv/pic/cover/s/0d/59/221127_9Gid3.jpg",
                    "grid": "http://lain.bgm.tv/pic/cover/g/0d/59/221127_9Gid3.jpg"
                },
                "collection": {
                    "doing": 655
                }
            },
            {
                "id": 225022,
                "url": "http://bgm.tv/subject/225022",
                "type": 2,
                "name": "宇宙戦艦ティラミス",
                "name_cn": "宇宙战舰提拉米斯",
                "summary": "",
                "air_date": "2018-04-02",
                "air_weekday": 1,
                "rating": {
                    "total": 185,
                    "count": {
                        "1": 1,
                        "2": 1,
                        "3": 0,
                        "4": 8,
                        "5": 21,
                        "6": 71,
                        "7": 61,
                        "8": 20,
                        "9": 1,
                        "10": 1
                    },
                    "score": 6.3
                },
                "rank": 3341,
                "images": {
                    "large": "http://lain.bgm.tv/pic/cover/l/94/21/225022_UWYZz.jpg",
                    "common": "http://lain.bgm.tv/pic/cover/c/94/21/225022_UWYZz.jpg",
                    "medium": "http://lain.bgm.tv/pic/cover/m/94/21/225022_UWYZz.jpg",
                    "small": "http://lain.bgm.tv/pic/cover/s/94/21/225022_UWYZz.jpg",
                    "grid": "http://lain.bgm.tv/pic/cover/g/94/21/225022_UWYZz.jpg"
                },
                "collection": {
                    "doing": 643
                }
            },
            {
                "id": 228380,
                "url": "http://bgm.tv/subject/228380",
                "type": 2,
                "name": "ピアノの森",
                "name_cn": "钢琴之森",
                "summary": "",
                "air_date": "2018-04-08",
                "air_weekday": 1,
                "rating": {
                    "total": 73,
                    "count": {
                        "1": 0,
                        "2": 0,
                        "3": 4,
                        "4": 1,
                        "5": 4,
                        "6": 17,
                        "7": 28,
                        "8": 16,
                        "9": 3,
                        "10": 0
                    },
                    "score": 6.7
                },
                "rank": 2262,
                "images": {
                    "large": "http://lain.bgm.tv/pic/cover/l/0b/7a/228380_XEVYv.jpg",
                    "common": "http://lain.bgm.tv/pic/cover/c/0b/7a/228380_XEVYv.jpg",
                    "medium": "http://lain.bgm.tv/pic/cover/m/0b/7a/228380_XEVYv.jpg",
                    "small": "http://lain.bgm.tv/pic/cover/s/0b/7a/228380_XEVYv.jpg",
                    "grid": "http://lain.bgm.tv/pic/cover/g/0b/7a/228380_XEVYv.jpg"
                },
                "collection": {
                    "doing": 338
                }
            },
            {
                "id": 229393,
                "url": "http://bgm.tv/subject/229393",
                "type": 2,
                "name": "かくりよの宿飯",
                "name_cn": "妖怪旅馆营业中",
                "summary": "",
                "air_date": "2018-04-02",
                "air_weekday": 1,
                "rating": {
                    "total": 47,
                    "count": {
                        "1": 0,
                        "2": 0,
                        "3": 1,
                        "4": 3,
                        "5": 7,
                        "6": 12,
                        "7": 22,
                        "8": 2,
                        "9": 0,
                        "10": 0
                    },
                    "score": 6.2
                },
                "images": {
                    "large": "http://lain.bgm.tv/pic/cover/l/af/fa/229393_666ce.jpg",
                    "common": "http://lain.bgm.tv/pic/cover/c/af/fa/229393_666ce.jpg",
                    "medium": "http://lain.bgm.tv/pic/cover/m/af/fa/229393_666ce.jpg",
                    "small": "http://lain.bgm.tv/pic/cover/s/af/fa/229393_666ce.jpg",
                    "grid": "http://lain.bgm.tv/pic/cover/g/af/fa/229393_666ce.jpg"
                },
                "collection": {
                    "doing": 152
                }
            },
            {
                "id": 231971,
                "url": "http://bgm.tv/subject/231971",
                "type": 2,
                "name": "ベイブレード バースト 超ゼツ",
                "name_cn": "战斗陀螺 爆烈 超绝",
                "summary": "",
                "air_date": "2018-04-02",
                "air_weekday": 1,
                "images": {
                    "large": "http://lain.bgm.tv/pic/cover/l/28/c2/231971_eAn8a.jpg",
                    "common": "http://lain.bgm.tv/pic/cover/c/28/c2/231971_eAn8a.jpg",
                    "medium": "http://lain.bgm.tv/pic/cover/m/28/c2/231971_eAn8a.jpg",
                    "small": "http://lain.bgm.tv/pic/cover/s/28/c2/231971_eAn8a.jpg",
                    "grid": "http://lain.bgm.tv/pic/cover/g/28/c2/231971_eAn8a.jpg"
                },
                "collection": {
                    "doing": 1
                }
            },
            {
                "id": 234378,
                "url": "http://bgm.tv/subject/234378",
                "type": 2,
                "name": "美男高校地球防衛部 HAPPY KISS!",
                "name_cn": "美男高校地球防卫部 HAPPY KISS！",
                "summary": "",
                "air_date": "2018-04-08",
                "air_weekday": 1,
                "rating": {
                    "total": 9,
                    "count": {
                        "1": 0,
                        "2": 0,
                        "3": 1,
                        "4": 0,
                        "5": 1,
                        "6": 2,
                        "7": 3,
                        "8": 1,
                        "9": 1,
                        "10": 0
                    },
                    "score": 6.4
                },
                "images": {
                    "large": "http://lain.bgm.tv/pic/cover/l/06/af/234378_aPGCY.jpg",
                    "common": "http://lain.bgm.tv/pic/cover/c/06/af/234378_aPGCY.jpg",
                    "medium": "http://lain.bgm.tv/pic/cover/m/06/af/234378_aPGCY.jpg",
                    "small": "http://lain.bgm.tv/pic/cover/s/06/af/234378_aPGCY.jpg",
                    "grid": "http://lain.bgm.tv/pic/cover/g/06/af/234378_aPGCY.jpg"
                },
                "collection": {
                    "doing": 38
                }
            },
            {
                "id": 235016,
                "url": "http://bgm.tv/subject/235016",
                "type": 2,
                "name": "パズドラ",
                "name_cn": "智龙迷城",
                "summary": "",
                "air_date": "2018-04-02",
                "air_weekday": 1,
                "rating": {
                    "total": 2,
                    "count": {
                        "1": 0,
                        "2": 0,
                        "3": 0,
                        "4": 0,
                        "5": 0,
                        "6": 1,
                        "7": 0,
                        "8": 1,
                        "9": 0,
                        "10": 0
                    },
                    "score": 7
                },
                "images": {
                    "large": "http://lain.bgm.tv/pic/cover/l/9c/e7/235016_S2b2O.jpg",
                    "common": "http://lain.bgm.tv/pic/cover/c/9c/e7/235016_S2b2O.jpg",
                    "medium": "http://lain.bgm.tv/pic/cover/m/9c/e7/235016_S2b2O.jpg",
                    "small": "http://lain.bgm.tv/pic/cover/s/9c/e7/235016_S2b2O.jpg",
                    "grid": "http://lain.bgm.tv/pic/cover/g/9c/e7/235016_S2b2O.jpg"
                },
                "collection": {
                    "doing": 9
                }
            },
            {
                "id": 236103,
                "url": "http://bgm.tv/subject/236103",
                "type": 2,
                "name": "踏切時間",
                "name_cn": "道口时间",
                "summary": "",
                "air_date": "2018-04-09",
                "air_weekday": 1,
                "rating": {
                    "total": 107,
                    "count": {
                        "1": 1,
                        "2": 0,
                        "3": 1,
                        "4": 1,
                        "5": 8,
                        "6": 33,
                        "7": 43,
                        "8": 18,
                        "9": 1,
                        "10": 1
                    },
                    "score": 6.6
                },
                "rank": 2505,
                "images": {
                    "large": "http://lain.bgm.tv/pic/cover/l/f4/c7/236103_kuvkS.jpg",
                    "common": "http://lain.bgm.tv/pic/cover/c/f4/c7/236103_kuvkS.jpg",
                    "medium": "http://lain.bgm.tv/pic/cover/m/f4/c7/236103_kuvkS.jpg",
                    "small": "http://lain.bgm.tv/pic/cover/s/f4/c7/236103_kuvkS.jpg",
                    "grid": "http://lain.bgm.tv/pic/cover/g/f4/c7/236103_kuvkS.jpg"
                },
                "collection": {
                    "doing": 385
                }
            },
            {
                "id": 237046,
                "url": "http://bgm.tv/subject/237046",
                "type": 2,
                "name": "お前はまだグンマを知らない",
                "name_cn": "你还是不懂群马",
                "summary": "",
                "air_date": "2018-04-02",
                "air_weekday": 1,
                "rating": {
                    "total": 61,
                    "count": {
                        "1": 0,
                        "2": 2,
                        "3": 1,
                        "4": 6,
                        "5": 19,
                        "6": 26,
                        "7": 7,
                        "8": 0,
                        "9": 0,
                        "10": 0
                    },
                    "score": 5.4
                },
                "rank": 3974,
                "images": {
                    "large": "http://lain.bgm.tv/pic/cover/l/6d/ab/237046_St35v.jpg",
                    "common": "http://lain.bgm.tv/pic/cover/c/6d/ab/237046_St35v.jpg",
                    "medium": "http://lain.bgm.tv/pic/cover/m/6d/ab/237046_St35v.jpg",
                    "small": "http://lain.bgm.tv/pic/cover/s/6d/ab/237046_St35v.jpg",
                    "grid": "http://lain.bgm.tv/pic/cover/g/6d/ab/237046_St35v.jpg"
                },
                "collection": {
                    "doing": 219
                }
            },
            {
                "id": 239747,
                "url": "http://bgm.tv/subject/239747",
                "type": 2,
                "name": "キャラとおたまじゃくし島",
                "name_cn": "",
                "summary": "",
                "air_date": "2018-04-02",
                "air_weekday": 1,
                "images": {
                    "large": "http://lain.bgm.tv/pic/cover/l/33/d5/239747_q05ea.jpg",
                    "common": "http://lain.bgm.tv/pic/cover/c/33/d5/239747_q05ea.jpg",
                    "medium": "http://lain.bgm.tv/pic/cover/m/33/d5/239747_q05ea.jpg",
                    "small": "http://lain.bgm.tv/pic/cover/s/33/d5/239747_q05ea.jpg",
                    "grid": "http://lain.bgm.tv/pic/cover/g/33/d5/239747_q05ea.jpg"
                }
            },
            {
                "id": 239962,
                "url": "http://bgm.tv/subject/239962",
                "type": 2,
                "name": "レディスポ",
                "name_cn": "Lady Sport",
                "summary": "",
                "air_date": "2018-04-09",
                "air_weekday": 1,
                "images": {
                    "large": "http://lain.bgm.tv/pic/cover/l/70/58/239962_g98e8.jpg",
                    "common": "http://lain.bgm.tv/pic/cover/c/70/58/239962_g98e8.jpg",
                    "medium": "http://lain.bgm.tv/pic/cover/m/70/58/239962_g98e8.jpg",
                    "small": "http://lain.bgm.tv/pic/cover/s/70/58/239962_g98e8.jpg",
                    "grid": "http://lain.bgm.tv/pic/cover/g/70/58/239962_g98e8.jpg"
                },
                "collection": {
                    "doing": 13
                }
            },
            {
                "id": 241879,
                "url": "http://bgm.tv/subject/241879",
                "type": 2,
                "name": "わしも-wasimo- 第6シリーズ",
                "name_cn": "",
                "summary": "",
                "air_date": "2018-04-02",
                "air_weekday": 1,
                "images": {
                    "large": "http://lain.bgm.tv/pic/cover/l/29/ad/241879_2uzku.jpg",
                    "common": "http://lain.bgm.tv/pic/cover/c/29/ad/241879_2uzku.jpg",
                    "medium": "http://lain.bgm.tv/pic/cover/m/29/ad/241879_2uzku.jpg",
                    "small": "http://lain.bgm.tv/pic/cover/s/29/ad/241879_2uzku.jpg",
                    "grid": "http://lain.bgm.tv/pic/cover/g/29/ad/241879_2uzku.jpg"
                }
            },
            {
                "id": 241967,
                "url": "http://bgm.tv/subject/241967",
                "type": 2,
                "name": "Four of a kind",
                "name_cn": "四牌士",
                "summary": "",
                "air_date": "2018-04-02",
                "air_weekday": 1,
                "rating": {
                    "total": 6,
                    "count": {
                        "1": 0,
                        "2": 0,
                        "3": 1,
                        "4": 0,
                        "5": 1,
                        "6": 0,
                        "7": 2,
                        "8": 2,
                        "9": 0,
                        "10": 0
                    },
                    "score": 6.3
                },
                "images": {
                    "large": "http://lain.bgm.tv/pic/cover/l/11/22/241967_S2EB2.jpg",
                    "common": "http://lain.bgm.tv/pic/cover/c/11/22/241967_S2EB2.jpg",
                    "medium": "http://lain.bgm.tv/pic/cover/m/11/22/241967_S2EB2.jpg",
                    "small": "http://lain.bgm.tv/pic/cover/s/11/22/241967_S2EB2.jpg",
                    "grid": "http://lain.bgm.tv/pic/cover/g/11/22/241967_S2EB2.jpg"
                },
                "collection": {
                    "doing": 23
                }
            },
            {
                "id": 242908,
                "url": "http://bgm.tv/subject/242908",
                "type": 2,
                "name": "帝王攻略",
                "name_cn": "帝王攻略",
                "summary": "",
                "air_date": "2018-04-30",
                "air_weekday": 1,
                "images": {
                    "large": "http://lain.bgm.tv/pic/cover/l/75/56/242908_Yh8hr.jpg",
                    "common": "http://lain.bgm.tv/pic/cover/c/75/56/242908_Yh8hr.jpg",
                    "medium": "http://lain.bgm.tv/pic/cover/m/75/56/242908_Yh8hr.jpg",
                    "small": "http://lain.bgm.tv/pic/cover/s/75/56/242908_Yh8hr.jpg",
                    "grid": "http://lain.bgm.tv/pic/cover/g/75/56/242908_Yh8hr.jpg"
                },
                "collection": {
                    "doing": 6
                }
            },
            {
                "id": 242963,
                "url": "http://bgm.tv/subject/242963",
                "type": 2,
                "name": "小兵・杨来西 第二部",
                "name_cn": "小兵杨来西 第二部",
                "summary": "",
                "air_date": "2018-04-16",
                "air_weekday": 1,
                "images": {
                    "large": "http://lain.bgm.tv/pic/cover/l/6e/20/242963_mLpuw.jpg",
                    "common": "http://lain.bgm.tv/pic/cover/c/6e/20/242963_mLpuw.jpg",
                    "medium": "http://lain.bgm.tv/pic/cover/m/6e/20/242963_mLpuw.jpg",
                    "small": "http://lain.bgm.tv/pic/cover/s/6e/20/242963_mLpuw.jpg",
                    "grid": "http://lain.bgm.tv/pic/cover/g/6e/20/242963_mLpuw.jpg"
                }
            },
            {
                "id": 242971,
                "url": "http://bgm.tv/subject/242971",
                "type": 2,
                "name": "飞天少年 第二季",
                "name_cn": "飞天少年之启航篇",
                "summary": "",
                "air_date": "2018-04-09",
                "air_weekday": 1,
                "rating": {
                    "total": 1,
                    "count": {
                        "1": 1,
                        "2": 0,
                        "3": 0,
                        "4": 0,
                        "5": 0,
                        "6": 0,
                        "7": 0,
                        "8": 0,
                        "9": 0,
                        "10": 0
                    },
                    "score": 1
                },
                "images": {
                    "large": "http://lain.bgm.tv/pic/cover/l/4c/8e/242971_qgUOQ.jpg",
                    "common": "http://lain.bgm.tv/pic/cover/c/4c/8e/242971_qgUOQ.jpg",
                    "medium": "http://lain.bgm.tv/pic/cover/m/4c/8e/242971_qgUOQ.jpg",
                    "small": "http://lain.bgm.tv/pic/cover/s/4c/8e/242971_qgUOQ.jpg",
                    "grid": "http://lain.bgm.tv/pic/cover/g/4c/8e/242971_qgUOQ.jpg"
                }
            },
            {
                "id": 243034,
                "url": "http://bgm.tv/subject/243034",
                "type": 2,
                "name": "天眼归来",
                "name_cn": "天眼归来",
                "summary": "",
                "air_date": "2018-04-16",
                "air_weekday": 1,
                "rating": {
                    "total": 1,
                    "count": {
                        "1": 1,
                        "2": 0,
                        "3": 0,
                        "4": 0,
                        "5": 0,
                        "6": 0,
                        "7": 0,
                        "8": 0,
                        "9": 0,
                        "10": 0
                    },
                    "score": 1
                },
                "images": {
                    "large": "http://lain.bgm.tv/pic/cover/l/14/e2/243034_QXP3m.jpg",
                    "common": "http://lain.bgm.tv/pic/cover/c/14/e2/243034_QXP3m.jpg",
                    "medium": "http://lain.bgm.tv/pic/cover/m/14/e2/243034_QXP3m.jpg",
                    "small": "http://lain.bgm.tv/pic/cover/s/14/e2/243034_QXP3m.jpg",
                    "grid": "http://lain.bgm.tv/pic/cover/g/14/e2/243034_QXP3m.jpg"
                }
            },
            {
                "id": 243097,
                "url": "http://bgm.tv/subject/243097",
                "type": 2,
                "name": "ゴールデン道画劇場",
                "name_cn": "黄金神威 小剧场",
                "summary": "",
                "air_date": "2018-04-16",
                "air_weekday": 1,
                "rating": {
                    "total": 7,
                    "count": {
                        "1": 0,
                        "2": 1,
                        "3": 0,
                        "4": 0,
                        "5": 0,
                        "6": 2,
                        "7": 4,
                        "8": 0,
                        "9": 0,
                        "10": 0
                    },
                    "score": 6
                },
                "images": {
                    "large": "http://lain.bgm.tv/pic/cover/l/e1/36/243097_X8qPp.jpg",
                    "common": "http://lain.bgm.tv/pic/cover/c/e1/36/243097_X8qPp.jpg",
                    "medium": "http://lain.bgm.tv/pic/cover/m/e1/36/243097_X8qPp.jpg",
                    "small": "http://lain.bgm.tv/pic/cover/s/e1/36/243097_X8qPp.jpg",
                    "grid": "http://lain.bgm.tv/pic/cover/g/e1/36/243097_X8qPp.jpg"
                },
                "collection": {
                    "doing": 23
                }
            },
            {
                "id": 243166,
                "url": "http://bgm.tv/subject/243166",
                "type": 2,
                "name": "三只松鼠",
                "name_cn": "三只松鼠",
                "summary": "",
                "air_date": "2018-04-09",
                "air_weekday": 1,
                "images": {
                    "large": "http://lain.bgm.tv/pic/cover/l/34/00/243166_yWH3q.jpg",
                    "common": "http://lain.bgm.tv/pic/cover/c/34/00/243166_yWH3q.jpg",
                    "medium": "http://lain.bgm.tv/pic/cover/m/34/00/243166_yWH3q.jpg",
                    "small": "http://lain.bgm.tv/pic/cover/s/34/00/243166_yWH3q.jpg",
                    "grid": "http://lain.bgm.tv/pic/cover/g/34/00/243166_yWH3q.jpg"
                }
            },
            {
                "id": 243887,
                "url": "http://bgm.tv/subject/243887",
                "type": 2,
                "name": "奇奇怪怪",
                "name_cn": "奇奇怪怪",
                "summary": "",
                "air_date": "2018-04-23",
                "air_weekday": 1,
                "images": {
                    "large": "http://lain.bgm.tv/pic/cover/l/f1/03/243887_O4OaC.jpg",
                    "common": "http://lain.bgm.tv/pic/cover/c/f1/03/243887_O4OaC.jpg",
                    "medium": "http://lain.bgm.tv/pic/cover/m/f1/03/243887_O4OaC.jpg",
                    "small": "http://lain.bgm.tv/pic/cover/s/f1/03/243887_O4OaC.jpg",
                    "grid": "http://lain.bgm.tv/pic/cover/g/f1/03/243887_O4OaC.jpg"
                }
            }
        ]
    },
    {
        "weekday": {
            "en": "Tue",
            "cn": "星期二",
            "ja": "火耀日",
            "id": 2
        },
        "items": [
            {
                "id": 143694,
                "url": "http://bgm.tv/subject/143694",
                "type": 2,
                "name": "銀河英雄伝説 Die Neue These 邂逅",
                "name_cn": "银河英雄传说 Die Neue These 邂逅",
                "summary": "",
                "air_date": "2018-04-03",
                "air_weekday": 2,
                "rating": {
                    "total": 311,
                    "count": {
                        "1": 1,
                        "2": 0,
                        "3": 0,
                        "4": 2,
                        "5": 4,
                        "6": 29,
                        "7": 100,
                        "8": 141,
                        "9": 25,
                        "10": 9
                    },
                    "score": 7.5
                },
                "rank": 790,
                "images": {
                    "large": "http://lain.bgm.tv/pic/cover/l/ba/ed/143694_Mb76T.jpg",
                    "common": "http://lain.bgm.tv/pic/cover/c/ba/ed/143694_Mb76T.jpg",
                    "medium": "http://lain.bgm.tv/pic/cover/m/ba/ed/143694_Mb76T.jpg",
                    "small": "http://lain.bgm.tv/pic/cover/s/ba/ed/143694_Mb76T.jpg",
                    "grid": "http://lain.bgm.tv/pic/cover/g/ba/ed/143694_Mb76T.jpg"
                },
                "collection": {
                    "doing": 1019
                }
            },
            {
                "id": 148481,
                "url": "http://bgm.tv/subject/148481",
                "type": 2,
                "name": "東京喰種トーキョーグール：re",
                "name_cn": "东京喰种:re",
                "summary": "",
                "air_date": "2018-04-03",
                "air_weekday": 2,
                "rating": {
                    "total": 217,
                    "count": {
                        "1": 11,
                        "2": 3,
                        "3": 6,
                        "4": 13,
                        "5": 18,
                        "6": 64,
                        "7": 64,
                        "8": 22,
                        "9": 4,
                        "10": 12
                    },
                    "score": 6.2
                },
                "rank": 3711,
                "images": {
                    "large": "http://lain.bgm.tv/pic/cover/l/eb/20/148481_v7x7A.jpg",
                    "common": "http://lain.bgm.tv/pic/cover/c/eb/20/148481_v7x7A.jpg",
                    "medium": "http://lain.bgm.tv/pic/cover/m/eb/20/148481_v7x7A.jpg",
                    "small": "http://lain.bgm.tv/pic/cover/s/eb/20/148481_v7x7A.jpg",
                    "grid": "http://lain.bgm.tv/pic/cover/g/eb/20/148481_v7x7A.jpg"
                },
                "collection": {
                    "doing": 565
                }
            },
            {
                "id": 195845,
                "url": "http://bgm.tv/subject/195845",
                "type": 2,
                "name": "ハイスクールD×D HERO",
                "name_cn": "High School D×D HERO",
                "summary": "",
                "air_date": "2018-04-10",
                "air_weekday": 2,
                "rating": {
                    "total": 76,
                    "count": {
                        "1": 0,
                        "2": 0,
                        "3": 1,
                        "4": 3,
                        "5": 9,
                        "6": 26,
                        "7": 20,
                        "8": 12,
                        "9": 1,
                        "10": 4
                    },
                    "score": 6.6
                },
                "rank": 2456,
                "images": {
                    "large": "http://lain.bgm.tv/pic/cover/l/cf/41/195845_z7Gr3.jpg",
                    "common": "http://lain.bgm.tv/pic/cover/c/cf/41/195845_z7Gr3.jpg",
                    "medium": "http://lain.bgm.tv/pic/cover/m/cf/41/195845_z7Gr3.jpg",
                    "small": "http://lain.bgm.tv/pic/cover/s/cf/41/195845_z7Gr3.jpg",
                    "grid": "http://lain.bgm.tv/pic/cover/g/cf/41/195845_z7Gr3.jpg"
                },
                "collection": {
                    "doing": 269
                }
            },
            {
                "id": 219164,
                "url": "http://bgm.tv/subject/219164",
                "type": 2,
                "name": "ルパン三世 PART5",
                "name_cn": "鲁邦三世 PART5",
                "summary": "",
                "air_date": "2018-04-03",
                "air_weekday": 2,
                "rating": {
                    "total": 73,
                    "count": {
                        "1": 0,
                        "2": 0,
                        "3": 0,
                        "4": 1,
                        "5": 1,
                        "6": 7,
                        "7": 18,
                        "8": 32,
                        "9": 10,
                        "10": 4
                    },
                    "score": 7.7
                },
                "rank": 776,
                "images": {
                    "large": "http://lain.bgm.tv/pic/cover/l/42/54/219164_TLkl2.jpg",
                    "common": "http://lain.bgm.tv/pic/cover/c/42/54/219164_TLkl2.jpg",
                    "medium": "http://lain.bgm.tv/pic/cover/m/42/54/219164_TLkl2.jpg",
                    "small": "http://lain.bgm.tv/pic/cover/s/42/54/219164_TLkl2.jpg",
                    "grid": "http://lain.bgm.tv/pic/cover/g/42/54/219164_TLkl2.jpg"
                },
                "collection": {
                    "doing": 278
                }
            },
            {
                "id": 227136,
                "url": "http://bgm.tv/subject/227136",
                "type": 2,
                "name": "鹿楓堂よついろ日和",
                "name_cn": "鹿枫堂",
                "summary": "",
                "air_date": "2018-04-10",
                "air_weekday": 2,
                "rating": {
                    "total": 37,
                    "count": {
                        "1": 0,
                        "2": 0,
                        "3": 0,
                        "4": 0,
                        "5": 3,
                        "6": 5,
                        "7": 20,
                        "8": 8,
                        "9": 0,
                        "10": 1
                    },
                    "score": 7
                },
                "images": {
                    "large": "http://lain.bgm.tv/pic/cover/l/02/06/227136_XEe2X.jpg",
                    "common": "http://lain.bgm.tv/pic/cover/c/02/06/227136_XEe2X.jpg",
                    "medium": "http://lain.bgm.tv/pic/cover/m/02/06/227136_XEe2X.jpg",
                    "small": "http://lain.bgm.tv/pic/cover/s/02/06/227136_XEe2X.jpg",
                    "grid": "http://lain.bgm.tv/pic/cover/g/02/06/227136_XEe2X.jpg"
                },
                "collection": {
                    "doing": 160
                }
            },
            {
                "id": 228259,
                "url": "http://bgm.tv/subject/228259",
                "type": 2,
                "name": "蒼天の拳 REGENESIS",
                "name_cn": "苍天之拳 REGENESIS",
                "summary": "",
                "air_date": "2018-04-09",
                "air_weekday": 2,
                "rating": {
                    "total": 42,
                    "count": {
                        "1": 2,
                        "2": 0,
                        "3": 0,
                        "4": 6,
                        "5": 6,
                        "6": 14,
                        "7": 9,
                        "8": 3,
                        "9": 1,
                        "10": 1
                    },
                    "score": 5.9
                },
                "images": {
                    "large": "http://lain.bgm.tv/pic/cover/l/2a/eb/228259_20MKT.jpg",
                    "common": "http://lain.bgm.tv/pic/cover/c/2a/eb/228259_20MKT.jpg",
                    "medium": "http://lain.bgm.tv/pic/cover/m/2a/eb/228259_20MKT.jpg",
                    "small": "http://lain.bgm.tv/pic/cover/s/2a/eb/228259_20MKT.jpg",
                    "grid": "http://lain.bgm.tv/pic/cover/g/2a/eb/228259_20MKT.jpg"
                },
                "collection": {
                    "doing": 118
                }
            },
            {
                "id": 229805,
                "url": "http://bgm.tv/subject/229805",
                "type": 2,
                "name": "3D彼女 リアルガール",
                "name_cn": "3D彼女",
                "summary": "",
                "air_date": "2018-04-03",
                "air_weekday": 2,
                "rating": {
                    "total": 94,
                    "count": {
                        "1": 5,
                        "2": 1,
                        "3": 6,
                        "4": 8,
                        "5": 13,
                        "6": 39,
                        "7": 16,
                        "8": 3,
                        "9": 1,
                        "10": 2
                    },
                    "score": 5.5
                },
                "rank": 4146,
                "images": {
                    "large": "http://lain.bgm.tv/pic/cover/l/9e/07/229805_ZZ1ww.jpg",
                    "common": "http://lain.bgm.tv/pic/cover/c/9e/07/229805_ZZ1ww.jpg",
                    "medium": "http://lain.bgm.tv/pic/cover/m/9e/07/229805_ZZ1ww.jpg",
                    "small": "http://lain.bgm.tv/pic/cover/s/9e/07/229805_ZZ1ww.jpg",
                    "grid": "http://lain.bgm.tv/pic/cover/g/9e/07/229805_ZZ1ww.jpg"
                },
                "collection": {
                    "doing": 257
                }
            },
            {
                "id": 232072,
                "url": "http://bgm.tv/subject/232072",
                "type": 2,
                "name": "キャプテン翼",
                "name_cn": "足球小将",
                "summary": "",
                "air_date": "2018-04-02",
                "air_weekday": 2,
                "rating": {
                    "total": 46,
                    "count": {
                        "1": 0,
                        "2": 0,
                        "3": 0,
                        "4": 2,
                        "5": 4,
                        "6": 9,
                        "7": 14,
                        "8": 14,
                        "9": 2,
                        "10": 1
                    },
                    "score": 7
                },
                "images": {
                    "large": "http://lain.bgm.tv/pic/cover/l/df/81/232072_rBO1a.jpg",
                    "common": "http://lain.bgm.tv/pic/cover/c/df/81/232072_rBO1a.jpg",
                    "medium": "http://lain.bgm.tv/pic/cover/m/df/81/232072_rBO1a.jpg",
                    "small": "http://lain.bgm.tv/pic/cover/s/df/81/232072_rBO1a.jpg",
                    "grid": "http://lain.bgm.tv/pic/cover/g/df/81/232072_rBO1a.jpg"
                },
                "collection": {
                    "doing": 166
                }
            },
            {
                "id": 232165,
                "url": "http://bgm.tv/subject/232165",
                "type": 2,
                "name": "立花館To Lieあんぐる",
                "name_cn": "立花馆恋爱三角铃",
                "summary": "",
                "air_date": "2018-04-03",
                "air_weekday": 2,
                "rating": {
                    "total": 62,
                    "count": {
                        "1": 1,
                        "2": 1,
                        "3": 1,
                        "4": 2,
                        "5": 11,
                        "6": 24,
                        "7": 18,
                        "8": 3,
                        "9": 0,
                        "10": 1
                    },
                    "score": 6
                },
                "rank": 3283,
                "images": {
                    "large": "http://lain.bgm.tv/pic/cover/l/57/d0/232165_PzxyY.jpg",
                    "common": "http://lain.bgm.tv/pic/cover/c/57/d0/232165_PzxyY.jpg",
                    "medium": "http://lain.bgm.tv/pic/cover/m/57/d0/232165_PzxyY.jpg",
                    "small": "http://lain.bgm.tv/pic/cover/s/57/d0/232165_PzxyY.jpg",
                    "grid": "http://lain.bgm.tv/pic/cover/g/57/d0/232165_PzxyY.jpg"
                },
                "collection": {
                    "doing": 275
                }
            },
            {
                "id": 236790,
                "url": "http://bgm.tv/subject/236790",
                "type": 2,
                "name": "ガンダムビルドダイバーズ",
                "name_cn": "高达创形者",
                "summary": "",
                "air_date": "2018-04-03",
                "air_weekday": 2,
                "rating": {
                    "total": 73,
                    "count": {
                        "1": 2,
                        "2": 1,
                        "3": 6,
                        "4": 2,
                        "5": 17,
                        "6": 27,
                        "7": 13,
                        "8": 5,
                        "9": 0,
                        "10": 0
                    },
                    "score": 5.6
                },
                "rank": 3956,
                "images": {
                    "large": "http://lain.bgm.tv/pic/cover/l/b3/ae/236790_601mn.jpg",
                    "common": "http://lain.bgm.tv/pic/cover/c/b3/ae/236790_601mn.jpg",
                    "medium": "http://lain.bgm.tv/pic/cover/m/b3/ae/236790_601mn.jpg",
                    "small": "http://lain.bgm.tv/pic/cover/s/b3/ae/236790_601mn.jpg",
                    "grid": "http://lain.bgm.tv/pic/cover/g/b3/ae/236790_601mn.jpg"
                },
                "collection": {
                    "doing": 264
                }
            },
            {
                "id": 237838,
                "url": "http://bgm.tv/subject/237838",
                "type": 2,
                "name": "黒猫モンロヲ",
                "name_cn": "",
                "summary": "",
                "air_date": "2018-04-17",
                "air_weekday": 2,
                "images": {
                    "large": "http://lain.bgm.tv/pic/cover/l/9a/4f/237838_bKb3g.jpg",
                    "common": "http://lain.bgm.tv/pic/cover/c/9a/4f/237838_bKb3g.jpg",
                    "medium": "http://lain.bgm.tv/pic/cover/m/9a/4f/237838_bKb3g.jpg",
                    "small": "http://lain.bgm.tv/pic/cover/s/9a/4f/237838_bKb3g.jpg",
                    "grid": "http://lain.bgm.tv/pic/cover/g/9a/4f/237838_bKb3g.jpg"
                },
                "collection": {
                    "doing": 1
                }
            },
            {
                "id": 240459,
                "url": "http://bgm.tv/subject/240459",
                "type": 2,
                "name": "少年アシベ GO!GO!ゴマちゃん 第３シリーズ",
                "name_cn": "少年阿贝GO！GO！小芝麻 第3季",
                "summary": "",
                "air_date": "2018-04-03",
                "air_weekday": 2,
                "images": {
                    "large": "http://lain.bgm.tv/pic/cover/l/6b/29/240459_5pgGh.jpg",
                    "common": "http://lain.bgm.tv/pic/cover/c/6b/29/240459_5pgGh.jpg",
                    "medium": "http://lain.bgm.tv/pic/cover/m/6b/29/240459_5pgGh.jpg",
                    "small": "http://lain.bgm.tv/pic/cover/s/6b/29/240459_5pgGh.jpg",
                    "grid": "http://lain.bgm.tv/pic/cover/g/6b/29/240459_5pgGh.jpg"
                },
                "collection": {
                    "doing": 5
                }
            },
            {
                "id": 247565,
                "url": "http://bgm.tv/subject/247565",
                "type": 2,
                "name": "少年师爷 第十一部",
                "name_cn": "少年师爷之神秘大盗",
                "summary": "",
                "air_date": "2018-04-24",
                "air_weekday": 2,
                "images": {
                    "large": "http://lain.bgm.tv/pic/cover/l/72/2f/247565_YaGKO.jpg",
                    "common": "http://lain.bgm.tv/pic/cover/c/72/2f/247565_YaGKO.jpg",
                    "medium": "http://lain.bgm.tv/pic/cover/m/72/2f/247565_YaGKO.jpg",
                    "small": "http://lain.bgm.tv/pic/cover/s/72/2f/247565_YaGKO.jpg",
                    "grid": "http://lain.bgm.tv/pic/cover/g/72/2f/247565_YaGKO.jpg"
                }
            }
        ]
    }
 * ]
 * */

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
            'text'=>"$air_date_msg  总计:  $list_num部\n\n"
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
            //$subject_img=$subject['images']['large'];
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
            $subject_air_weekday_fin=$subject_air_weekday==null?"":("\n放送星期:  ".$int2weekday[$subject_air_weekday]);
            $subject_type_id_fin="\n类型:  $subject_type      ID: $subject_id";
            $subject_summary_fin=$subject_summary==null?"":("\n简介:  $subject_summary");
            //最终结果
            //$subject_msg_part_fin=$subject_name_cn_fin.$subject_name_fin.$subject_eps_fin.$subject_air_date_fin.$subject_air_weekday_fin.$subject_type_id_fin.$subject_summary_fin;
            $subject_msg_part_fin="{$subject_name_cn_fin}{$subject_name_fin}{$subject_air_date_fin}{$subject_air_weekday_fin}{$subject_type_id_fin}{$subject_summary_fin}";

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
                            "$subject_msg_part_fin\n\n排名:  $subject_rank\n评分:  $subject_rating_average      评分数: $subject_rating_num\n在{$state}用户数:  $subject_collection_doing\n条目主页:  $subject_url\n_____\n"

                    )
                )
            );
            $msg=array_merge($msg,$subject_msg_all);
        }
        //发送第N段msg
        //send_message
        //require 'qq_search.php';
        $type="send_{$_GET['type']}_msg";
        $to=$_GET['to'];
        \access\send_msg($type,$to,$msg,constant('token'));
        //初始化msg
        $send_time=$send_num_id+1;
        $msg=array(
            array('type'=>"text",
                'data'=>array(
                    'text'=>"$air_date_msg  总计:  $list_num部--续<{$send_time}>\n\n"
                )
            ));
    }




?>