<?php
namespace web{
    require_once '../bangumi/api/access.php';
	function validate_user($from){
		$saved_id=\access\get_last_subject('send_private_msg',$from,$from);
		if($saved_id==false){
			return false;
		}else{
			return true;
		}
	}

	function get_user_bangumi_id($from,$bangumi_id){
		return \access\get_bangumi_id($from);
	}
	function get_user_save($from){
		$had_user_sql="select * from bgm_subject_memory where user_qq=$from";
		//只有搜索失败才会$result=false，空值为true
		$select_result=\access\sql_query('send_private_msg',$from,$had_user_sql);
		$row=mysqli_fetch_array($select_result,MYSQLI_ASSOC);
		return $row;
	}
	function filter_save($from,$file){
		$row=\web\get_user_save($from);
		$user_access_token=\access\get_access_token('send_private_msg',$from,$from);
		$div_data=\web\read_div();
		$i=1;
        $fixed_co=0;
        $fixed_air=0;
        $fixed_all=0;
		if($row!=false){
			for(;$i<constant("max_list");++$i){
                if($row["subject_{$i}"]==0){
                    continue;
                }
				list($data,$cache_file)=\access\request_subject('null',$row["subject_{$i}"],false);
				//date
                $date1=date_create($data['air_date']);
                $date2=date_create(date("Y-m-d"));
                $diff=date_diff($date1,$date2);
                $day=$diff->format("%a");
                //subject
                $true_subject_name=$data['name_cn']!=null?$data['name_cn']:$data['name'];
                $this_div_data=str_replace('{subject_name}',$true_subject_name,$div_data);
                $this_div_data=str_replace('{subject_id}',$row["subject_{$i}"],$this_div_data);
                $this_div_data=str_replace('{subject_img}',$data['images']['large'],$this_div_data);
                $keyword=\access\get_dmhy_name($data['name_cn'],$data['name']);
                $dmhy_url="https://share.dmhy.org/topics/list?keyword={$keyword}";
                $this_div_data=str_replace('{dmhy_url}',$dmhy_url,$this_div_data);
				//token
                if($user_access_token!=false){

                    $url_user="https://api.bgm.tv/collection/{$row["subject_{$i}"]}?access_token={$user_access_token}";
                    //bangumi JSON
                    $json_user=file_get_contents($url_user);
                    $data_user=json_decode($json_user,true);
                	if(!array_key_exists("error",$data_user)){
                		$subject_eps=$data['eps_count'];
                        //放送                        
                        if($diff->format("%R")=='+'){
                            if($subject_eps!=null)
                                $aired_subject_eps=((1+intval($day/7.0))>$subject_eps)?$subject_eps:(1+intval($day/7.0));
                            else{
                                $aired_subject_eps=1+intval($day/7.0);
                            }
                        }
                        else
                        {
                            $aired_subject_eps=0;
                        }
                        $su_ep=$data_user['ep_status'];
                        
                        $fixed_co=str_pad($su_ep, 4, '0', STR_PAD_LEFT);
                        $fixed_air=str_pad($aired_subject_eps, 4, '0', STR_PAD_LEFT);
                        $fixed_all=str_pad($subject_eps, 4, '0', STR_PAD_LEFT);

	                	$have_collect_num="[$fixed_co]";
	                	$have_air_num="[$fixed_air]";
	                	$all_num="[$fixed_all]";
                        for($j=0;$j<$su_ep&&$j<12;++$j){
                        	$have_collect_num.='▧';
                        }
                        for($j=0;$j<$aired_subject_eps&&$j<12;++$j){
                            $have_air_num.='▧';
                        }
                        if($subject_eps!=null){
                            for($j=0;$j<$subject_eps&&$j<12;++$j){
                                $all_num.='▧';
                            }
                        }else{
                            $all_num="[????]";
                            $all_num.='???';
                        }



                	}else{
	                	$have_collect_num='[FAILED]未收藏';
	                	$have_air_num='[FAILED]未收藏';
	                	$all_num='[FAILED]未收藏';
                	}

                }else{
                	$have_collect_num='[FAILED]Token失效...';
                	$have_air_num='[FAILED]Token失效...';
                	$all_num='[FAILED]Token失效...';
                }
            	$this_div_data=str_replace('{have_collect_num}',$have_collect_num,$this_div_data);
            	$this_div_data=str_replace('{have_air_num}',$have_air_num,$this_div_data);
            	$this_div_data=str_replace('{all_num}',$all_num,$this_div_data);

            	//if($i==1){
            		//file_put_contents("./{$qq}/index.html", $data);
            	//}else{
        		file_put_contents($file, $this_div_data, FILE_APPEND);
        		//}
			}//over for 
		}



		//detail user for subject
		//[003]:▧▧▧


	}
	function &read_div(){
		return file_get_contents('./div.dat');
	}
	function write_div($qq,$data,$append=false){
		if($append){
			file_put_contents("./{$qq}/index.html", $data, FILE_APPEND);
		}else{
			file_put_contents("./{$qq}/index.html", $data);
		}
		
	}
}
