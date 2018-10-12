<?php
require_once '../access.php';
require_once './dmhy.php';
//access
if(empty($_GET['access'])) {
    die("No auth");
}
else {
    //access
    constant('password')==$_GET['access']?:die("error auth");
    echo "access";
}
//关键字处理函数
function KeyDecode($keyword){
    return str_replace('+',' ',$keyword);
}
//接受参数
$type="send_{$_GET['type']}_msg";
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
            if(false!==strpos($parameter,"on")||false!==strpos($parameter,"ture")){
                $sql="UPDATE bgm_users
                                SET dmhy_open=1
                                WHERE user_qq=$from";
                //如果没有keyword      
                if($user_info['dmhy_keyword']==null){
                    $set_keyword_sql="UPDATE bgm_users
                                     SET dmhy_keyword='请设置订阅关键词'
                                     WHERE user_qq=$from";
                    \access\sql_query($type,$to,$set_keyword_sql);                    
                }
                //
                $open=true;
            }
            if(false!==strpos($parameter,"off")||false!==strpos($parameter,"false")){
                $sql="UPDATE bgm_users
                                SET dmhy_open=0
                                WHERE user_qq=$from";
            }
            if($sql!=""){
                $result=\access\sql_query($type,$to,$sql);
                if($result!==false){
                    $open_status=($open?"开启":"关闭");
                    $msg="变更成功!\n用户[ {$from} ]当前订阅状态: {$open_status}";
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
                $msg="变更成功!\n用户[ {$from} ]当前订阅关键字: [{$keyword}]";
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
                    $select_status=($dmhy_moe?"萌番组":"动漫花园");
                    $msg="变更成功!\n用户[ {$from} ]当前订阅源: $select_status";
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
                        $open_status=($row['dmhy_open']==1?"开启":"关闭");
                        $msg="第[{$row['user_id']}]位魔法少女[ {$from} ]\n当前订阅状态: {$open_status}";
                        break;
                    case 'keyword':
                        $msg="第[{$row['user_id']}]位魔法少女[ {$from} ]\n当前订阅关键字: [{$row['dmhy_keyword']}]";
                        break;
                    case 'select':
                        $select_status=($row['dmhy_moe']==1?"萌番组":"动漫花园");
                        $msg="第[{$row['user_id']}]位魔法少女[ {$from} ]\n当前订阅源: $select_status";
                        break;
                    case 'date':
                        $msg="第[{$row['user_id']}]位魔法少女[ {$from} ]\n上次更新时间: [{$row['dmhy_lastpubDate']}]";
                        break;
                    default:
                        $open_status=($row['dmhy_open']==1?"开启":"关闭");
                        $select_status=($row['dmhy_moe']==1?"萌番组":"动漫花园");
                        $msg="第[{$row['user_id']}]位魔法少女[ {$from} ]\n当前订阅状态: {$open_status}\n当前订阅关键字: [{$row['dmhy_keyword']}]\n当前订阅源: $select_status\n上次更新时间: [{$row['dmhy_lastpubDate']}]";
                        break;
                }
                break;
            }else{
                $msg="故障中...";
                break;
            }
        case 'update':
            global $is_update;
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
                $msg="第[{$id}]位魔法少女[{$from}]\n关键字:\n[{$keyword}]\n上次更新时间:\n[{$lastpubDate}]\n";
                //回复标志位
                $need_reply=true;
                $decode_keyword=urlencode($keyword);
                if($dmhy_moe){
                    //DMHY RSS
                    $url="https://share.dmhy.org/topics/rss/rss.xml?keyword=$decode_keyword";
                }else{
                    //Moe RSS
                    $url="https://bangumi.moe/rss/search/$decode_keyword";
                }

                //file name
                //$file_name="./RSS/{$decode_keyword}.xml";
                //file get
                $rss_file=file_get_contents($url,0,null,0,120000);
                //file_put_contents('test.xml',$rss_file);
                //处理xml
                if(false===strrpos($rss_file,"</rss>")){
                    $last_item=strrpos($rss_file,"<item>");
                    $over_last_item=strrpos($rss_file,"</item>");
                    if($over_last_item>$last_item){
                        $last_item=$over_last_item+7;
                    }
                    $rss_file=substr($rss_file,0,($last_item))."</channel></rss>";
                }


                //$rss_file=file_get_contents($file_name);
                //file_put_contents($file_name,$rss_file);
                if($xml=simplexml_load_string($rss_file)){
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
                            $currentItemMsg="\n----------------\n===编号 [ {$itemNum} ]===";
                            //一些参数
                            $itemOver=false;
                            //进行Item解析
                            foreach($channel->children() as $item){
                                //$msg=$item->getName().": ".$item;
                                //echo $item->getName() . ": " . $item . "\n\r";
                                switch ($item->getName()){
                                    case "title":
                                        $currentItemMsg.="\n$item";
                                        break;
                                    case "link":
                                        $currentItemMsg.="\nURL:\n$item";
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
                                        $currentItemMsg.="\n--------\n发布时间: $currentTime";
                                        //过时消息
                                        //$itemOver=true;
                                        break;
                                    case "description":
                                        //$currentItemMsg.="\n描述: ".$item;
                                        $pic_url=\dmhy\DescriptionDecode($item);
                                        if($pic_url!==false){
                                            $currentItemMsg="\n----------------\n[CQ:image,file={$pic_url}]{$currentItemMsg}";
                                        }

                                        break;
                                    case "enclosure":
                                        //echo $item->attributes()."\n";
                                        if($dmhy_moe){
                                            //dmhy
                                            $magnet=explode("&",$item->attributes());
                                            $currentItemMsg.="\n磁力链接:\n$magnet[0]";
                                        }else{
                                            //moe
                                            $TorrentEncode_result=\dmhy\TorrentEncode($item->attributes());
                                            $currentItemMsg.="\n种子链接:\n$TorrentEncode_result";
                                        }

                                        break;
                                    case "author":
                                        $currentItemMsg.="\n\n发布人: [ {$item} ]";
                                        break;
                                    case "category":
                                        $currentItemMsg.="\n资源分类: [ {$item} ]";
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

                            //用来发成功(有更新)的第一条
                            if($need_reply){
                                \access\send_msg($type,$to ,$msg,constant('token'));
                            }
                            $part_num=$itemNum/constant("once_items_num")+1;
                            $msg="\n关键字:\n[ {$keyword} ]\n上次更新时间:\n[{$lastpubDate}]\n第[{$part_num}]部分\n";
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
                //情况1是针对大于Max个资源
                if($need_reply&&$itemNum%constant("once_items_num")!=0){
                    \access\send_msg($type,$to ,$msg,constant('token'));
                }elseif($itemNum%constant("once_items_num")!=0){
                    //
                    $msg.="\n暂无更新...";
                    \access\send_msg($type,$to ,$msg,constant('token'));
                }
                //\access\send_msg($type,$to ,$msg,constant('token'));
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

