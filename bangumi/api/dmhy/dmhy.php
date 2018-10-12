<?php
namespace dmhy{
    //常量
	define("max_items",20);
	define("once_items_num",5);
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


}


?>