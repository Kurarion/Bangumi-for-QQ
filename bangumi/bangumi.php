<?php
require_once './api/access.php';
//json
$json=file_get_contents('php://input');
$data=json_decode($json,true);
//Example
/*
 * {
    "time": 1515204254,
    "post_type": "message",
    "message_type": "private",
    "sub_type": "friend",
    "message_id": 12,
    "user_id": 12345678,
    "message": "~user",
    "font": 456
 * }
 */
//请求的php地址
$php="/api";
//消息来自的qq号
$sub_from=$data['user_id'];
//消息类型 "private"、"group"、"discuss"
$type=$data['message_type'];
//qq号 群号 讨论组ID
switch ($type){
    case "private":
        $from=$sub_from;
        break;
    case "group":
        $from=$data['group_id'];
        break;
    case "discuss":
        $from=$data['discuss_id'];
        break;
    default:
        $from=null;
        die("error in switch(type)!") ;
        break;
}
//交互信息
$re_msg=array(
    array('type'=>"text",
            'data'=>array(
            'text'=>"这个我看不懂..."
            )
    )
);
//msg
$msg=$data['message'];
//从文件中读取
$file=$msg[0]=='~'?1:0;
//处理msg
/*
 * 初始计划：
 * ~user wz97315 #查看用户名为wz97315的信息
 * ~me  #查看与发言者自己QQ号绑定的bangumi账号信息
 * ~search '电脑'(urlencode) 'type' 'start' 'max'  #搜索条目
 * ~subject 127573  #指定条目
 * ~bangumi #每日放送
 * ~radio / ~fm #返回fm电台pls地址  （注意：通过修改死亡回复实现）
 * ~reg #注册（绑定qq号与bangumi号）
 * ~unreg #删除注册信息
 * ~save id 1-9 #存储subject ID到1-9号仓库
 * ~save 1-9 #存储last subject ID到1-9号仓库
 * ~list #列出自己仓库的subject ID 并显示ID部分的信息
 * #[1-9] #用于~su 的参数 【注意：[]必须非字符的任意个字母占位符】
 * ~up #更新进度
 * ~co #更新收藏
 * 注意凡是用到了仓库的#传递参数时都要进行url编码
 * */

$low_msg=strtolower($msg);
//标记是否识别阵亡
$dead=false;
switch ($low_msg[1]){
    case 'u':
        if(1==strpos($low_msg,"user")){
            $php.="/user/bangumi_user.php?";
            $para=explode(" ",$msg);
            $size=count($para);
            $username=$size>1?$para[1]:null;
            //echo $username;
            $php.="username={$username}&";
        }else if(1==strpos($low_msg,"unreg")){
            $php.="/auth/bangumi_unreg.php?";
        }else if(1==strpos($low_msg,"up")){
            $php.="/status/bangumi_update_status.php?";
            $para=explode(" ",$msg);
            $size=count($para);
            //echo "size: ".$size."\n";
            //echo "var_dump: ".var_dump($para[2])."\n";
            $subject_id=$size>1?urlencode($para[1]):null;
            $subject_eps=$size>2?$para[2]:null;
            $subject_detail=$size>3?$para[3]:null;
            //echo $para;
            $php.="subject_id={$subject_id}&subject_eps={$subject_eps}&subject_detail={$subject_detail}&";
        }
        else{
            //die();
            $re_msg[0]['data']['text']="比如这样: ~user ID/用户名 @#$%^?!&* 我才能看懂...\n又或者你想和我解除契约么...但我才不会告诉你是使用~unreg的！\n当然如果你是魔法少女的话，你可以使用~up 来更新条目的进度\n【----具体使用----】\n~user <参数1 *>[必填:Bangumi UID或用户名]\n~unreg <空>\n~up <参数 1 !>[可省略:所要更新的条目ID(三种方式，例如:222001,#2,“空”)]\n<参数 2 ^*>[必填:所需更新的章节数，例如:12 表示将12话及其之前的全部标记完成]\n<参数 3 ^~>[可选:是否列出条目详细的标志参数，只识别“*”，若是“*”则会自动请求一次~subject]";
            $dead=true;
        }
        break;
    case 's':
        if(1==strpos($low_msg,"search")||$low_msg[2]=="e"){
            $php.="/search/bangumi_search.php?";
            $para=explode(" ",$msg);
            $size=count($para);
            //echo "size: ".$size."\n";
            //echo "var_dump: ".var_dump($para[2])."\n";
            $search_string=$size>1?$para[1]:null;
            $search_type=$size>2?$para[2]:null;
            $search_start=$size>3?$para[3]:null;
            $search_max=$size>4?($para[4]>25?25:$para[4]):null;
            //echo $para;
            $php.="search_string={$search_string}&search_type={$search_type}&search_start={$search_start}&search_max={$search_max}&";
        }else if(1==strpos($low_msg,"subject")||$low_msg[2]=="u"){
            $php.="/subject/bangumi_subject.php?";
            $para=explode(" ",$msg);
            $size=count($para);
            //echo "size: ".$size."\n";
            //echo "var_dump: ".var_dump($para[2])."\n";
            $subject_id=$size>1?urlencode($para[1]):null;
            $subject_group=$size>2?$para[2]:null;
            //echo $para;
            $php.="subject_id={$subject_id}&subject_group={$subject_group}&";
        }else if(1==strpos($low_msg,"save")||$low_msg[2]=="a"){
            $php.="/save/sql_save.php?";
            $para=explode(" ",$msg);
            $size=count($para);
            $subject_id=$size>1?urlencode($para[1]):null;
            $save_id=$size>2?urlencode($para[2]):null;
            $php.="subject_id={$subject_id}&save_id={$save_id}&";
        }else{
            //die();
            $re_msg[0]['data']['text']="~subject 或 ~su 之后跟上条目ID...\n"."~search 或 ~se 之后跟上你想找的关键字(+隐藏的参数),关键字中的空格无视!但说不定UrlEncode行得通哦...\n~save 是用来给吉祥物(背包)收集魔力的（只限魔法少女哦）\n【----具体使用----】\n~search/se <参数 1 *>[必填: 想要搜索条目的关键字]\n<参数 2 ~>[可选: 想要搜索条目的类型，1 => 书籍, 2 => 动画, 3 => 音乐, 4 => 游戏, 6 => 三次元，默认所有类型]\n<参数 3 ~>[可选: 搜索结果的开始标号，默认从0开始]\n<参数 4 ~>[可选: 搜索结果的最大显示数(上限25个)，默认5个]\n~subject/su <参数 1 !>[可省略: 想要搜索的条目ID(三种方式，例如:222001, #2,“空”)]\n<参数 2 ~^>[可选: 条目信息的详细度参数，* => 增加收录的角色, ** => 增加收录的角色以及cv，默认只有条目信息]\n~save/sa <参数 1 !>[可省略: 想要保存的条目ID(三种方式，例如:222001, #2,“空”)]\n<参数 2 *^>[必填: 想要放入吉祥物的位置编号，1~25选一个吧，然而经常会不自觉地加一个#变成 #1-25，所以这样也没问题]";
            $dead=true;
        }
        break;
    case 'c':
        if(1==strpos($low_msg,"co")){
            $php.="/status/bangumi_update_collect.php?";
            $para=explode(" ",$msg);
            $size=count($para);
            //echo "size: ".$size."\n";
            //echo "var_dump: ".var_dump($para[2])."\n";
            $subject_id=$size>1?urlencode($para[1]):null;
            $subject_col=$size>2?$para[2]:null;
            $subject_detail=$size>3?$para[3]:null;
            $subject_rating=$size>4?$para[4]:null;
            $subject_comment=$size>5?$para[5]:null;
            //echo $para;
            $php.="subject_id={$subject_id}&subject_col={$subject_col}&subject_detail={$subject_detail}&subject_rating={$subject_rating}&subject_comment={$subject_comment}&";
        }elseif(1==strpos($low_msg,"cl")){
            $php.="/save/sql_save_clear.php?";
            $para=explode(" ",$msg);
            $size=count($para);
            $subject_id=$size>1?urlencode($para[1]):null;
            $php.="subject_id=".$subject_id."&";
        }else{
            //die();
            $re_msg[0]['data']['text']="~co 可以收藏指定条目哦~\n可选的收藏状态有{wish/collect/do/on_hold/dropped}\n【----具体使用----】\n~collect/co <参数 1 !>[可省略: 想要收藏的条目ID(三种方式，例如:222001, #2,“空”)]\n<参数 2 ^*>[必填: 想要收藏的状态，wish => 想看/玩/听/读, collect => 看/玩/听/读过, do => 在看/玩/听/读, on_hold => 搁置, dropped => 抛弃，输入有误或为空则为wish]\n<参数 3 ^~>[可选: 是否列出条目详细的标志参数，只识别“*”，若是“*”则会自动请求一次~subject]\n<参数 4 ^^~>[可选: 想要给此条目的评分，1-10，否则没有打分]\n<参数 5 ^^~>[可选: 想要对此条目的吐槽，请注意空格问题]";
            $dead=true;
        }
        break;
    case 'b':
        if(1==strpos($low_msg,"bangumi")||1==strpos($low_msg,"bgm")){
            $php.="/subject/bangumi_subject_bangumi.php?";
            $para=explode(" ",$msg);
            $size=count($para);
            //echo "size: ".$size."\n";
            //echo "var_dump: ".var_dump($para[2])."\n";
            $air_date=$size>1?$para[1]:null;
            //echo $para;
            $php.="air_date={$air_date}&";
        }
        else{
            $re_msg[0]['data']['text']="~bangumi 或 ~bgm 以外我都看不见！\n如果不加参数(1-7)的话我就默认给你今天的放送表咯~\n【----具体使用----】\n~bangumi/bgm <参数 1 ~>[可选: 想要查询放送表的日期，1-7代表 星期一 到 星期天，默认给予当天的放送表]";
            $dead=true;
        }
        break;
    case 'l':
        if(1==strpos($low_msg,"list")||$low_msg[2]=="i"){
            $php.="/save/sql_list.php?";
            $para=explode(" ",$msg);
            $size=count($para);
            //echo "size: ".$size."\n";
            //echo "var_dump: ".var_dump($para[2])."\n";
            $save_id=$size>1?urlencode($para[1]):null;
            //echo $para;
            $php.="save_id={$save_id}&";
        }
        else{
            $re_msg[0]['data']['text']="~list 或 ~li 用来显示当前吉祥物(背包)拥有的魔力\n当然这只能给魔法少女使用哦~\n【----具体使用----】\n~list/li <参数 1 ~>[可选: 想要搜索的背包位，范围是1-25，如果不填或有误默认全部，如果输入”*”则为有封面图版，否则只有文字]";
            $dead=true;
        }
        break;
    case 'h':
        $re_msg[0]['data']['text']="你需要帮助么？\n类似这样首字母可以获得相关使用方法...\n更详细的可以阅读指南: http://www.irisu.cc/bangumi";
        $dead=true;
        break;
    case 'r':
        if(1==strpos($low_msg,"reg")){
            $php.="/auth/bangumi_send_auth_site.php?";
        }elseif(1==strpos($low_msg,"rss")){
        	$php.="/dmhy/dmhy_moe.php?";
            $para=explode(" ",$msg);
            $size=count($para);
            $command=$size>1?urlencode($para[1]):null;
            $parameter=$size>2?urlencode($para[2]):null;
            $php.="command={$command}&parameter={$parameter}&";     
        }
        break;
        //没有else以便识别radio，顺便在之后的re_msg中加入相关提示
    case 'f':
        if(1==strpos($low_msg,"radio")||1==strpos($low_msg,"fm")){
            $re_msg[0]['data']['text']="AnimeNfo Radio:".
                "\nhttp://momori.animenfo.com:8000/listen.pls (Pembroke, Ontario, Canada) - 192kbps MP3".
                "\nhttp://emaru.animenfo.com:443/listen.pls (Montreal, Quebec, Canada) - 64kbps AAC+".
                "\nhttp://momori.animenfo.com:8080/listen.pls (Pembroke, Ontario, Canada) - 64kbps AAC+".
                "\nhttp://itori.animenfo.com:443/listen.pls (Montreal, Quebec, Canada) - 192kbps MP3".
                "\n\nGhost Anime Radio:".
                "\nhttp://animeradio.su/playlists/gar.pls".
                "\n\nAnime Layer:".
                "\nhttp://animelayer.ru:5190/play.m3u".
                "\n\nRadio Nami:".
                "\nhttp://radionami.com/play_radio.m3u".
                "\n\nJapan A Radio:".
                "\nhttp://www.japanaradio.com/free/48kaacp.pls";
        }
        else{
            $re_msg[0]['data']['text']="~radio 或 ~fm 你想说?\n又或者是想用~reg 绑定bangumi账号?\n但我不想猜......\n【----具体使用----】\n~reg <无>\n~fm <空>\n~radio <空>";
        }

        $dead=true;
        break;
    case 'w':
    	$web_path="../bgm/{$sub_from}/index.html";
    	if(file_exists($web_path)){
    		$web="http://bgm.irisu.cc/bgm/{$sub_from}/";
    	}else{
    		$web="http://bgm.irisu.cc/bgm/";
    	}
		$re_msg[0]['data']['text']='WEB: '.$web;
        $dead=true;
        break;
    case 'd':
        if(1==strpos($low_msg,"dmhy")){
            $php.="/dmhy/dmhy_moe_search.php?";
            $para=explode(" ",$msg);
            $size=count($para);
            $keyword=$size>1?urlencode($para[1]):null;
            $max_items=$size>2?urlencode($para[2]):null;
            $php.="dmhymoe=1&dmhynyaa=0&keyword={$keyword}&max={$max_items}&";
        }else{
            $re_msg[0]['data']['text']="~dmhy是相关订阅资源的功能\n当然这只能给魔法少女使用哦~\n【----具体使用----】\n施工中...";
            $dead=true;
        }
        break;
    case 'm':
        if(1==strpos($low_msg,"moe")){
            $php.="/dmhy/dmhy_moe_search.php?";
            $para=explode(" ",$msg);
            $size=count($para);
            $keyword=$size>1?urlencode($para[1]):null;
            $max_items=$size>2?urlencode($para[2]):null;
            $php.="dmhymoe=0&dmhynyaa=0&keyword={$keyword}&max={$max_items}&";
        }else{
            $re_msg[0]['data']['text']="~moedl搜索萌番组资源的功能...\n【----具体使用----】\n施工中...";
            $dead=true;
        }
        break;
    case 'n':
        if(1==strpos($low_msg, "nyaa")){
            $php.="/dmhy/dmhy_moe_search.php?";
            $para=explode(" ",$msg);
            $size=count($para);
            $keyword=$size>1?urlencode($para[1]):null;
            $max_items=$size>2?urlencode($para[2]):null;
            $php.="dmhymoe=0&dmhynyaa=1&keyword={$keyword}&max={$max_items}&";
        }else{
            $re_msg[0]['data']['text']="~nyaa是相关订阅资源的功能\n当然这只能给魔法少女使用哦~\n【----具体使用----】\n施工中...";
            $dead=true;
        }
        break;
    case 'q':
        if(1==strpos($low_msg,'q')){
            if(constant('administrator')==$sub_from){
                $php.="/report/report.php?";
                $para=explode(" ",$msg);
                $size=count($para);
                $report_from=$size>1?urlencode($para[1]):null;
                $report_msg=$size>2?urlencode($para[2]):null;
                $report_type='last_msg';
                switch ($low_msg[2]) {
                    case 'p':
                        $report_type='send_private_msg';
                        break;
                    case 'd':
                        $report_type='send_discuss_msg';
                        break;
                    case 'g':
                        $report_type='send_group_msg';
                        break;
                    case 'h':
                        $report_type='get_msg';
                        break;
                    default:
                        break;
                }
                $php.="report_type={$report_type}&report_from={$report_from}&report_msg={$report_msg}&";
            }else
            {
                $re_msg[0]['data']['text']="无权限...";
                $dead=true;
            }
            break;
        }else{
            //$re_msg[0]['data']['text']="~是相关订阅资源的功能\n当然这只能给魔法少女使用哦~\n【----具体使用----】\n施工中...";
            $dead=true;
        }
    default:
        //匹配不能
        //die();
        $dead=true;
        break;
}
//宣布死亡 or 传递简单消息
if($dead)
{
    \access\send_msg("send_{$type}_msg",$from,$re_msg,constant("token"));
    die();
}
echo $php;

//选择相应的bangumi-api
/*
 * user
 * subject
 * search
 * status
 * collection
 * auth
 * */
//user->user
//$php.="/user/bangumi_user.php?";
//$username="wz97315";
//$php.="username=".$username;

//选择相应的cqhttp-api
/*
 * send_private_msg
 * send_group_msg
 * send_discuss_msg
 * */
//考虑到可能没有前置参数，这里去掉了type之前的&，改到switch里面加入
$php.="type={$type}";



//至此 $php=./api/user/bangumi_user.php?username=???&type=private
//加入 access to
$php.="&to={$from}";
echo "\n".$php;
//消息发送者的QQ号码
$php.="&from={$sub_from}";
$php.='&access='.constant("password");
//是否读取缓存
$php.="&file={$file}";
echo "\n".$php;
//至此 $php=./api/user/bangumi_user.php?username=???&type=private&to=???&access=???
//跳转对应php处理
$url="http://127.0.0.1/bangumi{$php}";
echo "\n".$url;
//提示开始处理
//$re_msg[0]['data']['text']="受理中~\n但...也许会有神秘电波干扰...祈祷中...";
$re_msg[0]['data']['text']="祈祷中...";
\access\send_msg("send_{$type}_msg",$from,$re_msg,constant("token"));
//test
//\access\send_msg("send_{$type}_msg",$from,$url,constant("token"));
file_get_contents($url);


?>




