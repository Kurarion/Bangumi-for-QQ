<?php
namespace access{
    define('password',"php_access_password");
    define('token',"cqhttp_token");
    define('client_id',"bangumi_client_id");
    define('client_secret',"bangumi_client_secret");
    define('sql_url',"sql_url");
    define('sql_user',"sql_user");
    define('sql_password',"sql_password");
    define('max_list',26);
    define('administrator',Owner_QQ);
    define('cache_file_path',Cache_Path);
    //数字隐藏转换<没有比这更单纯的加密方式了>
    $num2code=array(
        0 => 'z',
        1 => 's',
        2 => 'd',
        3 => 'w',
        4 => 'v',
        5 => '2',
        6 => 'o',
        7 => '4',
        8 => 'q',
        9 => '1'
    );
    $code2num=array(
        'z' => 0,
        's' => 1,
        'd' => 2,
        'w' => 3,
        'v' => 4,
        '2' => 5,
        'o' => 6,
        '4' => 7,
        'q' => 8,
        'l' => 9
    );
    //返回qq号数字
    function qq_decode($to_code){
        //已是字符串
        global  $code2num;
        $to=$to_code;
        $length=strlen($to_code);
        for($i=0;$i<$length;++$i){
            $to[$i]=$code2num[$to[$i]];
        }
        //转换为数字
        return (int)$to;
    }
    //返回加密qq号字符串
    function qq_encode($to){
        //先转换为字符串
        global  $num2code;
        $to_code=$to."";
        $length=strlen($to_code);
        for($i=0;$i<$length;++$i){
            $to_code[$i]=$num2code[$to_code[$i]];
        }
        return $to_code;
    }
    //sendtype隐藏转换<没有比这更单纯的加密方式了>
    //当前只限定了私聊注册，因此默认private
    //    $type2password=array(
    //        'private' => "2d31F",
    //        'group' => "6ds3d",
    //        'discuss' => "7sdf2"
    //    );
    //    $password2type=array(
    //        '2d31F' => "private",
    //        '6ds3d' => "group",
    //        '7sdf2' => "discuss"
    //    );
    /*
     * 1 = book
     * 2 = anime
     * 3 = music
     * 4 = game
     * 6 = real
     * */
    $type2name=array(
        1 => "书籍",
        2 => "动画",
        3 => "音乐",
        4 => "游戏",
        6 => "三次元"
    );
    $type2state=array(
        1 => "读",
        2 => "看",
        3 => "听",
        4 => "玩",
        6 => "看"
    );
    $int2weekday=array(
        1 => "星期一/月曜日",
        2 => "星期二/火曜日",
        3 => "星期三/水曜日",
        4 => "星期四/木曜日",
        5 => "星期五/金曜日",
        6 => "星期六/土曜日",
        7 => "星期日/日曜日"
    );
    $status2col=array(
        'wish' => "想看/玩/听/读",
        'collect' => "看/玩/听/读过",
        'do' => "在看/玩/听/读",
        'on_hold' => "搁置",
        'dropped' => "抛弃"
    );

    //qq消息发送函数

    function send_msg($send_func,$to,$msg,$token)
    {
        //url
        $url='http://xxxxxx.xx:????/'.$send_func;
        //根据发送对象调整json
        switch ($send_func){
            case 'send_private_msg':
            case 'send_private_msg_async':
                $to_key='user_id';
                break;
            case 'send_group_msg':
            case 'send_group_msg_async':
                $to_key='group_id';
                break;
            case 'send_discuss_msg':
            case 'send_discuss_msg_async':
                $to_key='discuss_id';
                break;
            default:
                $to_key=null;
                die("Error in 'send_func'");
        }
        $data = array (
            $to_key => $to,
            'message'=>$msg
        );

        $data=json_encode($data);

        //echo $data;
        //require '../access.php';
        $opts = array (
            'http' => array (
                'method' => 'POST',
                'header' => array("Content-Type: application/json",
                    "Authorization: Token $token"),
                'content' => $data
            )
        );

        $context = stream_context_create($opts);
        file_get_contents($url, false, $context);
        //echo $html;
    }
    //注意这里的$to （可以是群号）的那个 **与SQL语句中的那个不一样**
    function sql_query($type,$to,$sql){
        //搜索数据库
        $con=mysqli_connect(constant("sql_url"),constant("sql_user"),constant("sql_password"));
        $fail=false;
        //成功则不回复
        $reply=true;
        $re_msg="";
        if(!$con)
        {
            //连接数据库失败
            //die();
            $fail=true;
            $re_msg="啊哦~数据库访问失败...";
        }
        if(!mysqli_select_db($con,"bangumi"))
        {
            //数据库打开失败
            //die();
            $fail=true;
            $re_msg="啊哦~数据库打开失败...";
        }
        if(!$fail){
            //数据库搜索
            //经过测试：
            //delete 即使没有记录受影响也会返回正确的$result(ture)
            //update 即使没有相关set记录会返回true ，当set参数类型有冲突会返回false
            //select 即使没有对应记录也不会返回false 但会返回一个fetch_array之后会返回false的$result
            $result=mysqli_query($con,$sql);
            if($result!=false){
                //关闭connect
                mysqli_close($con);
                //返回数据库结果对象
                return $result;
                //成功则不回复
                //$reply=false;
            }
            else{
                //$re_msg="是我没找到你的数据？\n还是说你还不是魔法少女？";
                $low_sql=strtolower($sql);
                //select语句没有任何记录返回
                if(1==strpos($low_sql,"elect")){
                    //test
                    $re_msg="select失败";
                    //$reply=false;
                }else if(1==strpos($low_sql,"pdate")){
                    //test
                    $re_msg="update失败";
                    //$reply=false;
                }
                else{
                    //真正意义上的错误
                    $re_msg="Error...";
                }

            }

        }
        mysqli_close($con);
        if($reply){
            send_msg($type,$to,$re_msg,constant('token'));
        }
        return false;
    }
    /* 例子
     * {
        "grant_type": "refresh_token",
        "client_id": "bXXXX3",
        "client_secret": "XXXX",
        "refresh_token": "a52XXXXe3f6acdcd",
        "redirect_uri": ""
     * }
     * */
    //这里直接使用数据库的refresh_token当参数
    //因此需要在调用sql查寻函数时一同返回$refresh_token
    //这个直接返回access token
    //使用例子【算了 直接封装方法】
    /*
     * $raw_result=sql_query($type,$to,$sql);
     * $row=mysqli_fetch_array(raw_$result,MYSQLI_ASSOC);
     * $access_token=$row['user_access_token'];
     * ...
     * if($_POST['error']=="Unauthorized")
     * $access_token=refresh_token($row['user_refresh_token']);
     * ...
     * */
    function refresh_token($refresh_token){
        //注意这里的redirect_uri是后台设置的
        //不过实际没有用 好像，姑且一致吧
        $redirect_uri=urlencode("http://www.xxxxx.xxx/bangumi");
        $data = array (
            'grant_type' => "refresh_token",
            'client_id' => constant("client_id"),
            'client_secret' => constant("client_secret"),
            'refresh_token' => $refresh_token,
            'redirect_uri' => $redirect_uri
        );
        $data=json_encode($data);
        $opts = array (
            'http' => array (
                'method' => 'POST',
                'header' => "Content-Type: application/json",
                'content' => $data
            )
        );
        $url='https://bgm.tv/oauth/access_token';
        $context = stream_context_create($opts);
        $json = file_get_contents($url, false, $context);
        //处理数据
        /* 例子
         * {
            "access_token": "9XX209100b1",
            "expires_in": 86400,
            "token_type": "Bearer",
            "scope": null,
            "user_id": 92981,
            "refresh_token": "0851b8eXX19012120"
         * }
         * */
        $return_data=json_decode($json,true);
        $access_token=$return_data['access_token'];
        $token_type=$return_data['token_type'];
        $user_id=$return_data['user_id'];
        $refresh_token=$return_data['refresh_token'];

        $update_sql="UPDATE bgm_users
        SET user_access_token='$access_token',user_refresh_token='$refresh_token'
        WHERE user_bangumi=$user_id";
        //插入新token到数据库
        $con=mysqli_connect(constant("sql_url"),constant("sql_user"),constant("sql_password"));
        $fail=false;
        if(!$con)
        {
            //连接数据库失败
            //die();
        }
        if(!mysqli_select_db($con,"bangumi"))
        {
            //数据库打开失败
            //die();
        }
        if(!$fail){
            //数据库搜索
            $result=mysqli_query($con,$update_sql);
            if($result==false){
                //关闭connect
                mysqli_close($con);
                //失败
                return false;
            }

        }
        mysqli_close($con);
        return $access_token;

    }
    /*
     * {
    "error": "invalid_request",
    "error_description": "The content type for POST requests must be \"application/x-www-form-urlencoded\""
     * }
     *
     * {
    "access_token": "9XXX157c1fe",
    "client_id": "bgXXXX21e3",
    "user_id": 92981,
    "expires": 1529024116,
    "scope": null
     * }
     * */
    function get_access_token($type,$to,$from){
        $get_access_token_sql="select user_access_token,user_refresh_token
                              from bgm_users
                              where user_qq=$from";
         $raw_result=sql_query($type,$to,$get_access_token_sql);
         $row=mysqli_fetch_array($raw_result,MYSQLI_ASSOC);
         if($row==false){
             //没有找到相关用户
             //$re_msg="首先你需要成为魔法少女...";
             //send_msg($type,$to,$re_msg,constant('token'));
             return false;
         }
         else{
             //找到用户
             //向服务器首先验证有效性
//             $post_msg = array (
//                 'access_token' => $row['user_access_token']
//             );
//             $post_msg=json_encode($post_msg);
             $post_msg="access_token=".$row['user_access_token'];
             $opts = array (
                 'http' => array (
                     'method' => 'POST',
                     'header' => "Content-Type: application/x-www-form-urlencoded",
                     'content' => $post_msg
                 )
             );
             //bangumi api URL
             $url='https://bgm.tv/oauth/token_status';
             //bangumi JSON
             $context = stream_context_create($opts);
             $json = file_get_contents($url, false, $context);
             $data=json_decode($json,true);
             //判断是否过期
             if(array_key_exists("error",$data)||!array_key_exists("access_token",$data)){
             //if(true){
                 //失效，开始刷新
                 $access_token=refresh_token($row['user_refresh_token']);
                 if(false!=$access_token){
                     //成功刷新
                     $re_msg="Bangumi授权刷新成功！";
                     send_msg($type,$to,$re_msg,constant('token'));
                     return $access_token;
                 }else{
                     //刷新失败
                     $re_msg="Bangumi授权刷新失败...请重新~reg";
                     send_msg($type,$to,$re_msg,constant('token'));
                     return false;
                 }

             }else{
                 //没有过期
                 return $row['user_access_token'];
             }
         }
    }
    //用于获得最近一次subject id
    //只会返回false和正确的ID号
    function get_last_subject($type,$to,$from){
        $get_last_subject_sql="select user_last_searched
                              from bgm_users
                              where user_qq=$from";
        $result=\access\sql_query($type,$to,$get_last_subject_sql);
        $row=mysqli_fetch_array($result,MYSQLI_NUM);
        //如果之前没有记录（默认为0）或者没有注册即没有这个记录 返回false
        if($row==0||$row==false){
            return false;
        }
        return $row[0];
    }
    //用于获得save中指定id的subject id 这里为了方便如果使用了编号为0的save则重定向到last subject
    //只会返回false和正确的ID号
    function read_save($type,$to,$from,$save_id){
        if($save_id[0]!='.'&&is_numeric($save_id)&&$save_id>0&&$save_id<constant("max_list")){
            //$use_search=false;
            if((int)$save_id==0){
                return get_last_subject($type,$to,$from);
            }
            else{
                $get_save_sql="select subject_".$save_id."
                              from bgm_subject_memory
                              where user_qq=$from";
                $result=\access\sql_query($type,$to,$get_save_sql);
                $row=mysqli_fetch_array($result,MYSQLI_NUM);
                //如果之前没有记录（默认为0）或者没有注册即没有这个记录 返回false
                if($row==0||$row==false){
                    return false;//1
                }
                return $row[0];
            }
        }
        elseif($save_id[0]!='.'){
            $right_id=0;
            $right_num=-1;
            $get_save_sql="select *
                              from bgm_subject_memory
                              where user_qq=$from";
            $result=\access\sql_query($type,$to,$get_save_sql);
            $row=mysqli_fetch_array($result,MYSQLI_ASSOC);
            //查找具体的
            if($row==0||$row==false){
                    //$use_search=true;
                    return false;//2
            }
            //识别Right_ID
            //#>02Happy Suger Life<#02
            $list_name=$row['List_Name'];
            //找到名字
            $name_site=stripos($list_name,$save_id);

            //找到相应的编号的分割位置
            if($name_site!==false){
                $right_num_site=stripos($list_name,'<#',$name_site);
            }else{
                $right_num_site=false;
            }
           
            //编号位置
            if($right_num_site!==false){
                $right_num=$list_name[$right_num_site+2]*10+$list_name[$right_num_site+3];
            }else{
                $right_num=-1;
            }
            //debug
            //\access\send_msg('send_private_msg',597320012,$list_name.'@'.$name_site.'@'.$save_id.'@'.$right_num_site.'@'.$right_num,constant('token'));
            //
            if($right_num!=-1){
                $right_id=$row["subject_{$right_num}"];
                return $right_id;
            }else{
                //$use_search=true;
                return false;//3
            }
            
        }else{

            return search_subject_id($save_id);


        }


    }
    //请求Bangumi subject api
    function &request_subject($file_name,$subject_id,$find_from_file=true,$response_group='small'){
        $file_path=constant('cache_file_path')."{$subject_id}_{$file_name}.data";
        $b_serialize=$find_from_file;
        //test
        //\access\send_msg('send_private_msg',597320012,"access:".dirname(__FILE__),constant('token'));
        if($find_from_file&&file_exists($file_path)){
            //读取
            $serialize_data=file_get_contents($file_path);
            //
            $data_array=unserialize($serialize_data);
            //test
            //\access\send_msg('send_private_msg',597320012,"access:$serialize_data",constant('token'));
            //返回
            return array($data_array,$b_serialize);
        }else{
            //请求bangumi api
            $url="https://api.bgm.tv/subject/$subject_id?responseGroup=$response_group";
            //bangumi JSON
            $json=file_get_contents($url);
            $data=json_decode($json,true);
            //序列化
            $b_serialize=false;
            //返回
            return array($data,$b_serialize);            
        }

    }
    //序列化
    function &read_file($file_name,$subject_id){
        $file_path=constant('cache_file_path')."{$subject_id}_{$file_name}.data";

        if(file_exists($file_path)){
            //读取
            $serialize_data=file_get_contents($file_path);
            //
            $data_array=unserialize($serialize_data);
            //test
            //\access\send_msg('send_private_msg',597320012,"access:$serialize_data",constant('token'));
            //返回
            return $data_array;
        }else{
            return false;
        }
    }
    //search subject id
    function search_subject_id($str){
        //解析
        $para=explode('.', $str);
        $para[1]=str_replace('+', ' ', $para[1]);
        $search_string=urlencode($para[1]!=null?$para[1]:'XXXXXX');
        $search_type=$para[2]!=null?$para[2]:2;
        $search_start=$para[3]!=null?$para[3]:0;
        //url
        $url="https://api.bgm.tv/search/subject/{$search_string}?type={$search_type}&start={$search_start}&max_results=3";
        //request
        //bangumi JSON
        $json=file_get_contents($url,0,null,0,46);
        if(false!==strpos($json,'request')||false!==strpos($json,'null')||false!==strpos($json, 'html')){

            //test
            //\access\send_msg('send_private_msg',597320012,"url:$url ",constant('token'));
            return false;  
            
        }else{

            //$data=json_decode($json,true);
            $before_id=substr($json, strpos($json, "\"id\":")+5);           
            $para=explode(',', $before_id);
            $id=$para[0];
            //test
            //\access\send_msg('send_private_msg',597320012,"url:$url \nbefore_id:$before_id \njson:$json \nid:$id",constant('token'));
            return $id;

        }
        


    }
        //get subject name for dmhy
    function get_dmhy_name($name_cn,$name){
        $subject_name='';
        $copy_subject_name=$name_cn!=null?$name_cn:$name;
        //$copy_subject_name=$subject_name;

        $genn_name=$name;
        $subject_last_name='|'.str_replace(' ', '|', $genn_name);
        //
        //Anima Yell! 迷糊餐厅 第三季
        //mb_internal_encoding("UTF-8");
        $name_balce_site=strpos($copy_subject_name, ' ');
        if($name_balce_site==false){
            //XXXX的XXXX
            //$subject_name=substr_replace("的", '|', $subject_name);
            // $subject_name=substr_replace($subject_name, '|', 2, 0);
            // $subject_name=substr_replace($subject_name, '|', 5, 0);
            // $subject_name=substr_replace($subject_name, '|', 7, 0);
            $string_len=mb_strlen($copy_subject_name);
            //$no_string_len=strlen($copy_subject_name);
            //test
            //\access\send_msg($type,$to,"string_len: $string_len \nname_balce_site: $name_balce_site \nno_string_len: $no_string_len",constant('token'));
            $have_last_name=false;
            $temp_last_name='';
            $temp_name=array();
            //first
            switch ($string_len) {
                case 14:
                case 13:
                case 12:
                    //$subject_name=substr_replace($subject_name, '|', 10, 0);
                    $temp_name[3]='|'.mb_substr($copy_subject_name, 7,3);
                    if(!$have_last_name){
                        $temp_last_name='|'.mb_substr($copy_subject_name, 10);
                        $have_last_name=true;
                    }
                    //test
                    //\access\send_msg($type,$to,"temp_name: $temp_name",constant('token'));
                case 11:
                case 10:
                case 9:
                    //$subject_name=substr_replace($subject_name, '|', 7, 0);
                    $temp_name[2]='|'.mb_substr($copy_subject_name, 5,2);
                    if(!$have_last_name){
                        $temp_last_name='|'.mb_substr($copy_subject_name, 7);
                        $have_last_name=true;
                    }
                    //test
                    //\access\send_msg($type,$to,"temp_name: $temp_name",constant('token'));
                case 8:
                case 7:
                    //$subject_name=substr_replace($subject_name, '|', 5, 0);
                    $temp_name[1]='|'.mb_substr($copy_subject_name, 2,3);
                    if(!$have_last_name){
                        $temp_last_name='|'.mb_substr($copy_subject_name, 5);
                        $have_last_name=true;
                    }
                    //test
                    //\access\send_msg($type,$to,"temp_name: $temp_name",constant('token'));
                case 6:
                case 5:
                case 4:
                    //$subject_name=substr_replace($subject_name, '|', 2, 0);
                    $temp_name[0]=mb_substr($copy_subject_name, 0,2);
                    if(!$have_last_name){
                        $temp_last_name='|'.mb_substr($copy_subject_name, 2);
                        $have_last_name=true;
                    }
                    //test
                    //\access\send_msg($type,$to,"temp_name: $temp_name",constant('token'));
                case 3:
                case 2:
                case 1:
                    break;
                
                default:
                    // $subject_name=substr_replace($subject_name, '|', 10, 0);
                    // $subject_name=substr_replace($subject_name, '|', 7, 0);
                    // $subject_name=substr_replace($subject_name, '|', 5, 0);
                    // $subject_name=substr_replace($subject_name, '|', 2, 0);
                    $temp_last_name='|'.mb_substr($copy_subject_name, 10);
                    $temp_name[3]='|'.mb_substr($copy_subject_name, 7,3);
                    $temp_name[2]='|'.mb_substr($copy_subject_name, 5,2);
                    $temp_name[1]='|'.mb_substr($copy_subject_name, 2,3);
                    $temp_name[0]=mb_substr($copy_subject_name, 0,2);
                    break;
            }
            for($i=0;$i<count($temp_name);++$i){
                $subject_name.=$temp_name[$i];
            }
            $subject_name.=$temp_last_name;
            //second
            $have_last_name=false;
            if($string_len>5){
                switch ($string_len) {
                    case 14:
                    case 13:
                    case 12:
                        //$subject_name=substr_replace($subject_name, '|', 10, 0);
                        $temp_name[3]='|'.mb_substr($copy_subject_name, 8,2);
                        if(!$have_last_name){
                            $temp_last_name='|'.mb_substr($copy_subject_name, 10);
                            $have_last_name=true;
                        }
                        //test
                        //\access\send_msg($type,$to,"temp_name: $temp_name",constant('token'));
                    case 11:
                    case 10:
                    case 9:
                        //$subject_name=substr_replace($subject_name, '|', 7, 0);
                        $temp_name[2]='|'.mb_substr($copy_subject_name, 5,3);
                        if(!$have_last_name){
                            $temp_last_name='|'.mb_substr($copy_subject_name, 8);
                            $have_last_name=true;
                        }
                        //test
                        //\access\send_msg($type,$to,"temp_name: $temp_name",constant('token'));
                    case 8:
                    case 7:
                        //$subject_name=substr_replace($subject_name, '|', 5, 0);
                        $temp_name[1]='|'.mb_substr($copy_subject_name, 3,2);
                        if(!$have_last_name){
                            $temp_last_name='|'.mb_substr($copy_subject_name, 5);
                            $have_last_name=true;
                        }
                        //test
                        //\access\send_msg($type,$to,"temp_name: $temp_name",constant('token'));
                    case 6:
                    case 5:
                    case 4:
                        //$subject_name=substr_replace($subject_name, '|', 2, 0);
                        $temp_name[0]='|'.mb_substr($copy_subject_name, 0,3);
                        if(!$have_last_name){
                            $temp_last_name='|'.mb_substr($copy_subject_name, 3);
                            $have_last_name=true;
                        }
                        //test
                        //\access\send_msg($type,$to,"temp_name: $temp_name",constant('token'));
                    case 3:
                    case 2:
                    case 1:
                        break;
                    
                    default:
                        // $subject_name=substr_replace($subject_name, '|', 10, 0);
                        // $subject_name=substr_replace($subject_name, '|', 7, 0);
                        // $subject_name=substr_replace($subject_name, '|', 5, 0);
                        // $subject_name=substr_replace($subject_name, '|', 2, 0);
                        $temp_last_name='|'.mb_substr($copy_subject_name, 10);
                        $temp_name[3]='|'.mb_substr($copy_subject_name, 8,2);
                        $temp_name[2]='|'.mb_substr($copy_subject_name, 5,3);
                        $temp_name[1]='|'.mb_substr($copy_subject_name, 3,2);
                        $temp_name[0]='|'.mb_substr($copy_subject_name, 0,3);
                        break;
                }

                for($i=0;$i<count($temp_name);++$i){
                    $subject_name.=$temp_name[$i];
                }
                $subject_name.=$temp_last_name;
            }

        }else{
            $subject_name=str_replace(' ', '|', $copy_subject_name);
        }
        $subject_name.=$subject_last_name;

        return $subject_name;
    }
    //generate php for user to get reply of dmhy
    function gen_dmhy_php($to,$from,$keyword,$subject_id,$subject_name='',$subject_img=''){
        //$keyword=;
        //$to=;
        //$from=;
        $access=constant('password');
        //return url
        //$subject_id=;
        $php_path="../../../bgm/{$from}/";
        if(!is_dir($php_path)){
            mkdir($php_path, 0777);
        }
        $php_path.="{$subject_id}/";
        if(!is_dir($php_path)){
            mkdir($php_path, 0777);
        }
        $php_name="{$php_path}index.php";
        $url="http://bgm.irisu.cc/bgm/{$from}/{$subject_id}";
        //if there already has

        //access url
        $wget_url="http://127.0.0.1/bangumi/api/dmhy/dmhy_moe_search.php?dmhymoe=1&keyword={$keyword}&max=5&type=private&to={$to}&from={$from}&access={$access}";
        $dmhy_url="https://share.dmhy.org/topics/list?keyword={$keyword}";
        $php_file=file_get_contents('./dmhy_moe_php.dat');
        //modify the php contents
        $php_file=str_replace("{dmhy_url}",$dmhy_url,$php_file);
        $php_file=str_replace("{reply_url}",$wget_url,$php_file);
        $php_file=str_replace("{subject_name}",$subject_name,$php_file);
        $php_file=str_replace("{subject_img}",$subject_img,$php_file);

        file_put_contents($php_name, $php_file);
        return $url;

    }
}


?>