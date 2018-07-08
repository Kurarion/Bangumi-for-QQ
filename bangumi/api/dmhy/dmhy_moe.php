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
//常量
define("max_items",20);
define("once_items_num",4);
//描述文本处理函数
function DescriptionDecode($description){
    //$decode_subject_id=$description[strpos($description," src=\"")+1];
    if(strpos($description,"src=\"")!==false){
        $first_sub=substr($description,strpos($description,"src=\"")+5);
        $pic_url=substr($first_sub,0,strpos($first_sub,"\""));
        //echo $first_sub."55555\n";
        //echo "66666\n".$pic_url."55555\n";
        return $pic_url;
    }else{
        return "http://www.irisu.cc/res/no_img.gif";
    }

}
//将种子中文转换URLENCODE
function TorrentEncode($torrent){
    if(strrpos($torrent,"/")!==false){
        $start=strrpos($torrent,"/")+1;
        $torrentName=substr($torrent,$start);
        //echo "\n".$start;
        //echo "\n".$torrentName;
        //echo "\n".$torrent;
        //echo "\n".urlencode($torrentName);
        //echo "\n".substr_replace($torrent,urlencode($torrentName),$start);
        return substr_replace($torrent,urlencode($torrentName),$start);
    }else{
        return $torrent;
    }
}
//关键字处理函数
function KeyDecode($keyword){
    return str_replace('+',' ',$keyword);
}
//接受参数
$type='send_'.$_GET['type'].'_msg';
$to=$_GET['to'];
$from=$_GET['from'];
//参数
$command=$_GET['command'];
$parameter=$_GET['parameter'];
//回复的消息
$msg="不是魔法少女不可用哦~";
//标志是否update
$is_update=false;
//判断是否魔法少女
$if_sql="select user_id,dmhy_keyword,dmhy_lastpubDate,dmhy_moe
                              from bgm_users
                              where user_qq=$from";
$result=\access\sql_query($type,$to,$if_sql);
$user_info=mysqli_fetch_array($result,MYSQLI_ASSOC);
if($user_info!=false){
    //处理
    switch ($command){
        case 'open':
            //sql语句
            $sql="";
            //标志
            $open=false;
            if(false!==strpos($parameter,"true")){
                $sql="UPDATE bgm_users
                                SET dmhy_open=1
                                WHERE user_qq=$from";
                $set_keyword_sql="UPDATE bgm_users
                                SET dmhy_keyword='请设置订阅关键词'
                                WHERE user_qq=$from";
                $result=\access\sql_query($type,$to,$set_keyword_sql);
                $open=true;
            }
            if(false!==strpos($parameter,"false")){
                $sql="UPDATE bgm_users
                                SET dmhy_open=0
                                WHERE user_qq=$from";
            }
            if($sql!=""){
                $result=\access\sql_query($type,$to,$sql);
                if($result!==false){
                    $msg="变更成功!\n用户[ ".$from." ]当前订阅状态: ".($open?"开启":"关闭");
                }else{
                    $msg="发生了点小故障...订阅状态变更失败";
                }
                break;
            }else{
                $msg="呃阿~最后一个参数看不懂!";
                break;
            }
        case 'keyword':
            $keyword=KeyDecode($parameter);
            $sql="UPDATE bgm_users
                                SET dmhy_keyword='$keyword'
                                WHERE user_qq=$from";
            $result=\access\sql_query($type,$to,$sql);
            if($result!==false){
                $msg="变更成功!\n用户[ ".$from." ]当前订阅关键字: [".$keyword."]";
            }else{
                $msg="发生了点小故障...订阅关键字变更失败";
            }
            break;
        case 'select':
            //sql语句
            $sql="";
            //标志
            $dmhy_moe=false;
            if(false!==strpos($parameter,"moe")){
                $sql="UPDATE bgm_users
                                SET dmhy_moe=1
                                WHERE user_qq=$from";
                $dmhy_moe=true;
            }
            if(false!==strpos($parameter,"dmhy")){
                $sql="UPDATE bgm_users
                                SET dmhy_moe=0
                                WHERE user_qq=$from";
            }
            if($sql!=""){
                $result=\access\sql_query($type,$to,$sql);
                if($result!==false){
                    $msg="变更成功!\n用户[ ".$from." ]当前订阅源: ".($dmhy_moe?"萌番组":"动漫花园");
                }else{
                    $msg="发生了点小故障...订阅源变更失败";
                }
                break;
            }else{
                $msg="呃阿~最后一个参数看不懂!";
                break;
            }
        case 'get':
            $sql="select user_id,user_qq,dmhy_open,dmhy_keyword,dmhy_lastpubDate,dmhy_moe
                              from bgm_users
                              where user_qq=$from";
            $result=\access\sql_query($type,$to,$sql);
            $row=mysqli_fetch_array($result,MYSQLI_ASSOC);
            if($row!=false){
                switch ($parameter){
                    case'open':
                        $msg="第[".$row['user_id']."]位魔法少女[ ".$from." ]"."\n当前订阅状态: ".($row['dmhy_open']==1?"开启":"关闭");
                        break;
                    case 'keyword':
                        $msg="第[".$row['user_id']."]位魔法少女[ ".$from." ]"."\n当前订阅关键字: [".$row['dmhy_keyword']."]";
                        break;
                    case 'select':
                        $msg="第[".$row['user_id']."]位魔法少女[ ".$from." ]"."\n当前订阅源: ".($row['dmhy_moe']==1?"萌番组":"动漫花园");
                        break;
                    case 'date':
                        $msg="第[".$row['user_id']."]位魔法少女[ ".$from." ]"."\n上次更新时间: [".$row['dmhy_lastpubDate']."]";
                        break;
                    default:
                        $msg="第[".$row['user_id']."]位魔法少女[ ".$from." ]"
                            ."\n当前订阅状态: ".($row['dmhy_open']==1?"开启":"关闭")
                            ."\n当前订阅关键字: [".$row['dmhy_keyword']."]"
                            ."\n当前订阅源: ".($row['dmhy_moe']==1?"萌番组":"动漫花园")
                            ."\n上次更新时间: [".$row['dmhy_lastpubDate']."]";
                        break;
                }
                break;
            }else{
                $msg="故障中...";
                break;
            }
        case 'update':
            $is_update=true;
            //手动更新
            {
                //sql读取dmhy信息
                $id=$user_info['user_id'];
                //$to=$user_info['user_qq'];
                $keyword=$user_info['dmhy_keyword'];
                $lastpubDate=$user_info['dmhy_lastpubDate'];
                //True代表dmhy,False代表Moe
                $dmhy_moe=$user_info['dmhy_moe']==0?true:false;
                //msg
                $msg="第[".$id."]位魔法少女[".$from."]"
                    ."\n关键字:\n[".$keyword."]"
                    ."\n上次更新时间:\n[".$lastpubDate."]"
                    ."\n";
                //回复标志位
                $need_reply=true;
                if($dmhy_moe){
                    //DMHY RSS
                    $url='https://share.dmhy.org/topics/rss/rss.xml?keyword='.urlencode($keyword);
                }else{
                    //Moe RSS
                    $url='https://bangumi.moe/rss/search/'.urlencode($keyword);
                }


                if($xml=simplexml_load_file($url)){
                    //将 SimpleXMLElement 转化为普通数组
                    //$jsonStr = json_encode($xml);
                    //$xmlArray = json_decode($jsonStr,true);
                    //最后要更新的最新Date
                    $need_set_date=$lastpubDate;
                    //item计数器
                    $itemNum=1;
                    //只进行一次
                    //当前进度到channel标签下
                    foreach($xml->children()->children() as $channel){
                        //itemsMsg
                        $itemsMsg="";
                        //标志全部结束
                        $channelOver=false;
                        //当前进度到item标签下

                        if($channel->getName()=="item"){
                            //当前ItemMsg
                            $currentItemMsg="\n----------------\n===编号 [ ".$itemNum." ]===";
                            //一些参数
                            $itemOver=false;
                            //进行Item解析
                            foreach($channel->children() as $item){
                                //$msg=$item->getName().": ".$item;
                                //echo $item->getName() . ": " . $item . "\n\r";
                                switch ($item->getName()){
                                    case "title":
                                        $currentItemMsg.="\n".$item;
                                        break;
                                    case "link":
                                        $currentItemMsg.="\nURL:\n".$item;
                                        break;
                                    case "pubDate":
                                        //第一个item最新
                                        if($itemNum===1){
                                            $time=strtotime($item);
                                            $need_set_date=date("Y-m-d H:i:s",$time);
                                            //echo  date("Y-m-d H:i:s",$time);
                                        }
                                        $time=strtotime($item);
                                        $currentTime=date("Y-m-d H:i:s",$time);
                                        if($time<=strtotime($lastpubDate)){
                                            //如果没有任何更新则无需回复
                                            if($itemNum===1)
                                                $need_reply=false;
                                            $itemOver=true;
                                        }elseif($itemNum===1){
                                            $need_set_date=date("Y-m-d H:i:s",$time);
                                        }
                                        $currentItemMsg.="\n--------\n发布时间: ".$currentTime;
                                        //过时消息
                                        //$itemOver=true;
                                        break;
                                    case "description":
                                        //$currentItemMsg.="\n描述: ".$item;
                                        $pic_url=DescriptionDecode($item);
                                        if($pic_url!==false){
                                            $currentItemMsg="\n----------------\n"
                                                ."[CQ:image,file=".$pic_url."]"
                                                .$currentItemMsg;
                                        }

                                        break;
                                    case "enclosure":
                                        //echo $item->attributes()."\n";
                                        if($dmhy_moe){
                                            //dmhy
                                            $magnet=explode("&",$item->attributes());
                                            $currentItemMsg.="\n磁力链接:\n".$magnet[0];
                                        }else{
                                            //moe
                                            $currentItemMsg.="\n种子链接:\n".TorrentEncode($item->attributes());
                                        }

                                        break;
                                    case "author":
                                        $currentItemMsg.="\n\n发布人: [ ".$item." ]";
                                        break;
                                    case "category":
                                        $currentItemMsg.="\n资源分类: [ ".$item." ]";
                                        break;
                                    default:
                                        break;

                                }
                                //echo $itemNum;
                            }
                            //echo $currentItemMsg."\n\n\n";
                            //完成一个Item
                            if($itemOver||$itemNum>constant("max_items")){
                                $channelOver=true;
                                break;
                            }else{
                                $itemsMsg.=$currentItemMsg;
                                ++$itemNum;
                            }

                        }else{
                            continue;
                        }
                        if($channelOver){
                            break;
                        }
                        $msg.=$itemsMsg;
                        if($itemNum%constant("once_items_num")==0){

                            //分条发送
                            if($need_reply){
                                \access\send_msg($type,$to ,$msg,constant('token'));
                            }
                            $msg="\n关键字:\n[".$keyword."]"
                                ."\n上次更新时间:\n[".$lastpubDate."]"
                                ."\n第[".($itemNum/constant("once_items_num")+1)."]部分\n";
                        }
                    }
                    //避免没有结果时还会回复
                    if($itemNum==1){
                        $need_reply=false;
                    }
                }else{
                    die("xml获取失败");
                }
                //消息内容
                //$msg="XXXX";

                //最后更新LastDate
                if($need_set_date!=$lastpubDate){
                    $update_sql="UPDATE bgm_users
                                SET dmhy_lastpubDate='$need_set_date'
                                WHERE user_qq=$from";
                    \access\sql_query($type,$to,$update_sql);
                }
                //回复QQ
                if($need_reply&&$itemNum%constant("once_items_num")!=0){
                    //\access\send_msg($type,$to ,$msg,constant('token'));
                }else{
                    //
                    $msg.="\n暂无更新...";
                    //\access\send_msg($type,$to ,$msg,constant('token'));
                }
                \access\send_msg($type,$to ,$msg,constant('token'));
            }
            break;
        default:
            $msg="你肯定输错了些什么...";
            break;
    }
}
if(!$is_update){
    \access\send_msg($type,$to,$msg,constant('token'));
}

?>