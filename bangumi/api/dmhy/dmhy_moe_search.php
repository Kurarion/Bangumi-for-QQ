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
//接受参数
$type="send_{$_GET['type']}_msg";
$to=$_GET['to'];
$from=$_GET['from'];
//para
$dmhy_moe=$_GET['dmhymoe']==1?true:false;
$keyword=str_replace('+',' ',$_GET['keyword']);
$max_items=$_GET['max']!=null?$_GET['max']:5;

//init for para
$subject_id=0;
$ex_keyword='';
$use_trans=false;
//explode keyword
if($keyword!=null&&$_GET['keyword'][0]!='.'){
	$black_site=strpos($keyword, ' ');
	if($black_site!=false){
		$origin_subject_id=substr($keyword, 0, $black_site);
		$ex_keyword=substr($keyword, $black_site);
	}else{
		$origin_subject_id=$keyword;
	}
	//test
	//\access\send_msg($type,$to,"black_site: $black_site \norigin_subject_id: $origin_subject_id \nex_keyword: $ex_keyword",constant('token'));
	// $ex_para=explode(' ', $keyword);
	// $ex_size=count($ex_para);
	// for($i=1;$i<$ex_size;++$i){
	// 	$ex_keyword.=$ex_para[$i];
	// }
}
//纯ID
if(is_numeric($origin_subject_id)&&$_GET['keyword'][0]!='.'){
	$subject_id=$origin_subject_id;
	$use_trans=true;
}
//省略参数:考虑到二义性，如果省略第一个参数，并且没有+则第二个参数前置需加入.以区别
elseif($keyword==null||$_GET['keyword'][0]=='.'||$_GET['keyword'][0]=='+'){
	if($_GET['keyword'][0]=='.'){
		//first para
		$subject_id=\access\get_last_subject($type,$to,$from);
		//second para
		$max_items=substr($keyword, 1);		
	}else{
		//first para
		$subject_id=\access\get_last_subject($type,$to,$from);
		//ex keyword
		$ex_keyword=$keyword;
	}
	$use_trans=true;
}
//表示进行特殊查询
elseif($_GET['keyword'][0]=='#'){
	//#本田 BIG XX字幕组
	//#.本田 BIG XX字幕组
	$subject_id=\access\read_save($type,$to,$from,substr($origin_subject_id,1));
	$use_trans=true;
}
if($use_trans){
	//get name of this subject
	$subject_name='';
	if($subject_id!=0){
	    //请求bangumi api
	    $urlx="https://api.bgm.tv/subject/$subject_id";
	    //bangumi JSON
	    $jsonx=file_get_contents($urlx);
	    $datax=json_decode($jsonx,true);

		$subject_name=\access\get_dmhy_name($datax['name_cn'],$datax['name']);
	    //
	}else{
		//不存在
		\access\send_msg($type,$to,"我不认为你有用过相关背包位或没能搜到这样的条目...",constant('token'));
		die();
	}
	$keyword="{$subject_name}{$ex_keyword}";
	//test
	//\access\send_msg($type,$to,"subject_id: $subject_id \nsubject_name: $subject_name \nex_keyword: $ex_keyword \nmax_items: $max_items",constant('token'));
}


//api
$decode_keyword=urlencode($keyword);
//msg
$site=$dmhy_moe?'动漫花园':'萌番组';
$msg="资源来源:[{$site}]\n关键字:[{$keyword}]\n";
$fail_msg='没有相关结果...';
$need_reply=true;

//
if($dmhy_moe){
    //DMHY RSS
    $url="https://share.dmhy.org/topics/rss/rss.xml?keyword=$decode_keyword";
}else{
    //Moe RSS
    $url="https://bangumi.moe/rss/search/$decode_keyword";
}
//test
//\access\send_msg($type,$from,$url,constant("token"));
//file name
//$file_name="./RSS/{$decode_keyword}.xml";
//file get
$rss_file=file_get_contents($url,0,null,0,180000);
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
//test
//file_put_contents('test.xml',$rss_file);

//名字集合
$name_list="-----<全部资源>-----";
//链接集合
$torrent_list="\n\n-----<链接>-----";

if($xml=simplexml_load_string($rss_file)){
    //将 SimpleXMLElement 转化为普通数组
    //$jsonStr = json_encode($xml);
    //$xmlArray = json_decode($jsonStr,true);
    //最后要更新的最新Date
    //$need_set_date=$lastpubDate;
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
            //$itemOver=false;
            //进行Item解析
            foreach($channel->children() as $item){
                //$msg=$item->getName().": ".$item;
                //echo $item->getName() . ": " . $item . "\n\r";
                switch ($item->getName()){
                    case "title":
                        //
                        if($itemNum<=$max_items){
                        $name_list.="\n$item";}
                        //
                        $currentItemMsg.="\n$item";
                        break;
                    case "link":
                        $currentItemMsg.="\nURL:\n$item";
                        break;
                    case "pubDate":
                        //第一个item最新
                        // if($itemNum===1){
                        //     //$time=strtotime($item);
                        //     //$need_set_date=date("Y-m-d H:i:s",$time);
                        //     //echo  date("Y-m-d H:i:s",$time);
                        // }
                        $time=strtotime($item);
                        $currentTime=date("Y-m-d H:i:s",$time);
                        // if($time<=strtotime($lastpubDate)){
                        //     //如果没有任何更新则无需回复
                        //     if($itemNum===1)
                        //         $need_reply=false;
                        //     //$itemOver=true;
                        // }elseif($itemNum===1){
                        //     $need_set_date=date("Y-m-d H:i:s",$time);
                        // }
                        $currentItemMsg.="\n--------\n发布时间: $currentTime";
                        //过时消息
                        //$itemOver=true;
                        break;
                    case "description":
                        //$currentItemMsg.="\n描述: ".$item;
                        $pic_url=\dmhy\DescriptionDecode($item);
                        if($pic_url!==false){
                            $currentItemMsg="\n----------------\n[CQ:image,file={$pic_url}]{$currentItemMsg}";
                            //test
                            //\access\send_msg($type,$to ,$pic_url,constant('token'));
                        }

                        break;
                    case "enclosure":
                        //echo $item->attributes()."\n";
                        if($dmhy_moe){
                            //dmhy
                            $magnet=explode("&",$item->attributes());
                            $currentItemMsg.="\n磁力链接:\n$magnet[0]";
                            if($itemNum<=$max_items){
                            $torrent_list.="\n$magnet[0]";}
                        }else{
                            //moe
                            $TorrentEncode_result=\dmhy\TorrentEncode($item->attributes());
                            $currentItemMsg.="\n种子链接:\n$TorrentEncode_result";
                            if($itemNum<=$max_items){
                            $torrent_list.="\n$TorrentEncode_result";}
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
            if($itemNum>$max_items){
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
        //$msg.=$torrent_list;
        if(($itemNum-1)%constant("once_items_num")==0&&$itemNum!=1){

            //用来发成功(有更新)的第一条
            if($need_reply){
                \access\send_msg($type,$to ,$msg,constant('token'));
            }
            $part_num=($itemNum-1)/constant("once_items_num")+1;
            $msg="资源来源:[{$site}]\n关键字:[{$keyword}]\n第[{$part_num}]部分\n";
        }
    }
    //避免没有结果时还会回复
    if($itemNum==1){
        $need_reply=false;
    }
}else{
    die("xml获取失败");
}
// if(!$need_reply){
//     //reply
//     \access\send_msg($type,$to,$fail_msg,constant('token')); 
// }
if($need_reply&&($itemNum-1)%constant("once_items_num")!=0){
    \access\send_msg($type,$to ,$msg,constant('token'));
}elseif($itemNum==1){
    //
    $msg.="\n$fail_msg";
    \access\send_msg($type,$to ,$msg,constant('token'));
}
if($need_reply){
    $name_list.=$torrent_list;
    \access\send_msg($type,$to ,$name_list,constant('token'));
}
