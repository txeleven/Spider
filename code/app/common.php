<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
use think\facade\Db;
use think\Request;


/*********************************************************************
 * 函数名称:encode
 * 函数作用:加密解密字符串
 * 使用方法:
 * 加密     :encode('str','ENCODE','nowamagic');                    encode('str');
 * 解密     :encode('被加密过的字符串','DECODE','nowamagic');        decode('被加密过的字符串');
 * 参数说明:
 * $string   :需要加密解密的字符串
 * $operation:判断是加密还是解密:ENCODE:加密   DECODE:解密
 * $key      :加密的钥匙(密匙);
 *********************************************************************/
//解密
function decode($string, $operation = 'DECODE', $key = 'tx-eleven')
{
    return encode($string, $operation, $key);
}

//加密
function encode($string, $operation = 'ENCODE', $key = 'tx-eleven')
{  //把所有的+替换成为 %2F, /替换为%2F 未打开
    $key = md5($key);
    $key_length = strlen($key);
    $string = $operation == 'DECODE' ? base64_decode(urldecode($string)) : substr(md5($string . $key), 0, 8) . $string; //str_replace(array('%2B','%2F'),array('+','/'),$string)
    $string_length = strlen($string);
    $rndkey = $box = array();
    $result = '';
    for ($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($key[$i % $key_length]);
        $box[$i] = $i;
    }
    for ($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }
    for ($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }
    if ($operation == 'DECODE') {
        if (substr($result, 0, 8) == substr(md5(substr($result, 8) . $key), 0, 8)) {
            return substr($result, 8);
        } else {
            return '';
        }
    } else {
        return urlencode(str_replace(array('='), array(''), base64_encode($result)));//,'+','/' //,'%2B','%2F'
    }
}

function put()
{ //put json 数据转换成post 数据
    $put = input('put.', '', 'trim');
    $key = key($put);
    if (count($put) == 1) {
        $data = empty($put[$key]) ? $key : $put[$key];
        $putData = @json_decode($data, true);
        $put = is_array($putData) ? $putData : $put;
    }
    $_POST = $put;
}

/**列表页面操作转换
 * @param array $opt ['url'=>['参数1','参数2']]
 * @param $type
 * @return bool|string
 */
function opt($opt = array(), $type)
{
    //dump(count($opt));
    if ($type == 'a') {
        switch (count($opt)) {
            case 0:
                $content = false;
                break;
            case 1:
                $content = '';
                foreach ($opt as $k => $l) {
                    $content .= '<a href="' . $k . '"  class="btn btn-primary btn-xs ' . $l[1] . '">' . $l[0] . '</a> ';
                }
                break;
            case 2:
                $content = '';
                foreach ($opt as $k => $l) {
                    $content .= '<a href="' . $k . '"  class="btn btn-primary btn-xs ' . $l[1] . '">' . $l[0] . '</a> ';
                }
                break;
           case 3:
               $content='';
               foreach($opt as $k=>$l){
                   $content.='<a href="'.$k.'"  class="btn btn-primary btn-xs '.$l[1].'">'.$l[0].'</a> ';
               }
               break;
                  case 4:
                      $content='';
                      foreach($opt as $k=>$l){
                          $content.='<a href="'.$k.'"  class="btn btn-primary btn-xs '.$l[1].'">'.$l[0].'</a> ';
                      }
                      break;
               case 5:
                    $content='';
                    foreach($opt as $k=>$l){
                        $content.='<a href="'.$k.'"  class="btn btn-primary btn-xs '.$l[1].'">'.$l[0].'</a> ';
                    }
                    break;
                case 6:
                    $content='';
                    foreach($opt as $k=>$l){
                        $content.='<a href="'.$k.'"  class="btn btn-primary btn-xs '.$l[1].'">'.$l[0].'</a> ';
                    }
                    break;
            /*       case 7:
                      $content='';
                      foreach($opt as $k=>$l){
                          $content.='<a href="'.$k.'"  class="btn btn-primary btn-xs '.$l[1].'">'.$l[0].'</a> ';
                      }
                      break;
                  case 8:
                      $content='';
                      foreach($opt as $k=>$l){
                          $content.='<a href="'.$k.'"  class="btn btn-primary btn-xs '.$l[1].'">'.$l[0].'</a> ';
                      }
                      break;
                      */
            default:
                $content = '<div class="btn-group">';
                $content .= '<button data-toggle="dropdown" class="btn btn-primary btn-xs dropdown-toggle">操作';
                $content .= '<span class="caret"></span></button>';
                $content .= '<ul class="dropdown-menu">';
                foreach ($opt as $k => $l) {
                    $content .= '<li><a href="' . $k . '" class="' . $l[1] . '">' . $l[0] . '</a></li>';
                }
                $content .= '</ul></div>';
        }
    } elseif ($type == 'image') {
        $content = '';
        foreach ($opt as $k => $l) {
            $content .= '<img src="' . $k . '" file_id="' . $l[0] . '" height="30" class="' . $l[1] . '">';
        }
    } elseif ($type == 'type') {
        $content = '';
        foreach ($opt as $k => $l) {
            $content .= ' <b class="btn btn-primary btn-xs">' . $l . '</b>';
            //   $content.='<img src="'.$k.'" file_id="'.$l[0].'" height="30" class="'.$l[1].'">';
        }
    }
    // dump($content);exit;
    return $content;
}

/**文件读取
 * @param $file_id = 文件 id
 * @param bool $type =image/images/file/files
 * @return array
 */
function fileList($file_id, $file_type = false)
{
    //  dump($file_type);
    if (empty($file_id)) return [];
    $map = ['is_show' => 1];
    $map['file_id'] = (string)$file_id;
    !empty($file_type) && $map['file_type'] = $file_type;
    $relust = Db::name('file')->where($map)->order('id desc')->select()->toArray();
    //dump($map);
    // dump($relust);exit;
    return (array)$relust;
}

/**文件读取值
 * @param $file_id = 文件 id
 * @param bool $value ='filesrc'
 * @return 返回值
 */
function fileValue($file_id, $value)
{
    if (empty($file_id)) return false;
    $map = ['is_show' => 1];
    if (is_array($file_id)) $map = array_merge($map, $file_id);
    else  $map['file_id'] = $file_id;
    $relust = think\facade\Db::name('file')->where($map)->value($value);

    return str_ireplace('\\', '/', $relust);
}

//查找主键
function getPk($dbname)
{
    //  return Db::getTableInfo(config('database.prefix').$dbname, 'pk');
    $name = 'primary_key_list';
    $prefix = config('database.prefix') . $dbname;

    $list = (array)cache($name); //查找缓存
    if (empty($list[$prefix])) {
        $tableResult = \think\facade\Db::query('show tables like "%' . config('database.prefix') . $dbname . '%"');
        if ($tableResult) {
            $info = \think\facade\Db::name($dbname)->getPk();   //查找主键
            $list[$prefix] = $info;
            cache($name, $list, config('cache_time'));
        } else {
            $list[$prefix] = 'id';    //默认主键
        }
    }
    return $list[$prefix];
}

//查找表下面字段并缓存
function filed($dbname)
{
    $name = 'filed_list';
    $prefix = config('database.prefix') . $dbname;
   // $list = (array)cache($name); //查找缓存
    if (empty($list[$prefix])) {
        // $info=Db::getTableInfo('think_user', 'type');
        $info = \think\facade\Db::name($dbname)->getFieldsType();
        $list[$prefix] = $info;
        cache($name, $list, config('cache_time'));
    }
    return $list[$prefix];
}



// 分割栏目参数
function parameter($val, $parameter = [])
{//$val['parameter']
    if (empty($val)) return $parameter;
    $par = explode('&', $val);
    foreach ($par as $l) {
        $parAttr = explode('=', $l);
        $parameter[$parAttr[0]] = $parAttr[1];
    }

    return (array)$parameter;
}

//redis_server 封装
function serverCache($serverIp, $cacheKey, $cacheValue = '', $path = '', $redis_time = false)
{
    $path = $path ? $path : ['path' => RUNTIME_PATH . 'redis_cache/server/' . $serverIp . '/'];
    if ($redis_time) $path['time'] = $redis_time;
    return cache($cacheKey, $cacheValue, $path);
}

function serverInfoCache($cacheKey, $cacheValue = '', $path = '')
{
    $path = $path ? $path : ['path' => RUNTIME_PATH . 'redis_cache/server_info/'];
    return cache($cacheKey, $cacheValue, $path);
}


//日志记录
function recordLog($isAjax)
{
    $dbname = 'recordLog';
    $get = input('get.', '', 'trim');
    $post = input('post.', '', 'trim');
    $input = input();
    $data = [];
    $data['type'] = 5;
    $data['menu_id'] = input('menu_id', '', 'intval');
    // $data['uid']=session('user_info.id');
    $data['admin_id'] = session('admin_info.id');
    $data['module_name'] = Request::instance()->controller() . DIRECTORY_SEPARATOR . Request::instance()->action();
    $data['is_ajax'] = $isAjax ? 1 : 0;
    $data['get'] = $get;
    $data['post'] = $post;
    $data['other'] = $input;
    $data['add_ip'] = Request::instance()->ip(1);
    $data['add_time'] = time();
    $data['url'] = $_SERVER['REQUEST_URI'];
    $data['is_show'] = 1;
    return $data;
    //  $data=$this->create($data,$dbname);
    return $relust = Db::name($dbname)->strict(true)->data($data)->insert();

    //日志记录，不用返回任何结果
}

function menu($id, $value)
{
    if (empty($id)) return [];
    $map = ['is_show' => 1];
    $map['id'] = $id;
    !empty($file_type) && $map['file_type'] = $file_type;
    $relust = Db::name('menu')->where($map)->value($value);
    //   echo Db::name('file')->getLastSql();
    return $relust;
}

// 获取区域信息
function area($id, $value)
{
    if (empty($id)) return [];
    $map = ['is_show' => 1];
    $map['id'] = $id;
    $relust = Db::name('area')->where($map)->value($value);
    //   echo Db::name('file')->getLastSql();
    return $relust;
}

/** 获取当前时间戳，精确到毫秒 */
function microtime_float($num = 4)
{
//    /$mic=microtime();
    // dump($mic);
    list($usec, $sec) = explode(" ", microtime());
    // dump($usec); dump($sec);exit;
    // return ((float)$usec + float($sec));
    return $sec . substr($usec . '000000', 2, $num);
}

/*
 *  直接插入排序,插入排序的思想是：当前插入位置之前的元素有序，
 *  若插入当前位置的元素比有序元素最后一个元素大，则什么也不做，
 *  否则在有序序列中找到插入的位置，并插入
 */
function insertSort($arr)
{
    $len = count($arr);
    for ($i = 1; $i < $len; $i++) {
        if ($arr[$i - 1] > $arr[i]) {
            for ($j = $i - 1; $j >= 0; $j--) {
                $tmp = $arr[$j + 1];
                if ($tmp < $arr[$j]) {
                    $arr[$j + 1] = $arr[$j];
                    $arr[$j] = $tmp;
                } else {
                    break;
                }
            }
        }
    }
    return $arr;
}

function extracimg($content, $upload = false)
{
    $time = microtime_float();
    $server = \think\Request::instance()->server();
    $html = dirname(\think\Request::instance()->root());
    $html = $html == '\\' ? '\Public' : $html;

    $upload = dirname($server['SCRIPT_FILENAME']) . DIRECTORY_SEPARATOR . 'Public' . DIRECTORY_SEPARATOR . 'Upload' . DIRECTORY_SEPARATOR . 'summernote';
    if (!is_dir($upload)) {
        mkdir($upload, 0777);
        @chmod($upload, 0777);
    }

    $upload = $upload . DIRECTORY_SEPARATOR . date('Ymd', substr($time, 0, -4)) . DIRECTORY_SEPARATOR;
    $upload = str_ireplace(['\\', '/'], DIRECTORY_SEPARATOR, $upload);
    if (!is_dir($upload)) {
        mkdir($upload, 0777);
        @chmod($upload, 0777);
    }
    $html = str_ireplace(['\\', '/'], DIRECTORY_SEPARATOR, $html);

    // $imgurl=strstr($upload,$html);
    $imgurl = strstr($upload, $html ? $html : config('view.tpl_replace_string.__UPLOAD__'));
    //dump($html);  dump($imgurl);dump($upload);exit;
    //dump($content);exit;
    if (preg_match_all('/<img\s+[^>]*?src="(.*)"/iUs', $content, $matches)) {
        //	dump($matches);exit;
        if ($matches[1]) {
            $re = [];
            foreach ($matches[1] as $img) {
                $imgs = substr($img, strpos($img, ",") + 1);
                $images = base64_decode($imgs);

                if ($images && !strstr($imgs, 'summernote')) {
                    // dump($imgs) ;dump($images);;exit;
                    $md5 = md5($imgs);
                    $dimg = date('YmdHis', substr($time, 0, -4)) . '_' . substr($time, -4) . '_' . rand(10000, 99999) . '.jpg';
                    if (file_put_contents($upload . $dimg, $images)) {
                        $re['img'][] = $img;
                        $re['loaclurl'][] = $upload . $dimg;
                        $re['url'][] = $imgurl . $dimg;

                        //写入文件
                        $data = [];
                        $data['menu_id'] = input('menu_id', 0, 'intval');
                        $data['file_id'] = input('image_id', 0, 'trim');   //唯一标识,随机15位字符串',
                        $data['file_type'] = 'summernote';   //image images file files other
                        // $data['type']=in_array($data['file_type'],['image','images'])?1:5;   //1为图片,2视频,3音频,5为其它文件 ',
                        $data['oriname'] = '';   //原始文件名',
                        $data['filesize'] = '';   //文件大小(B字节)',
                        $data['filesrc'] = $imgurl . $dimg;   //文件路径',
                        $data['filesrcsmall'] = $imgurl . $dimg;   //文件缩略图路径',
                        $data['fileame'] = $dimg;   //文件名',
                        $data['info'] = '';   //原始文件json信息',
                        $data['is_show'] = 1;   //1为显示',
                        $data['add_time'] = substr($time, 0, -4);   //添加时间',
                        $data['is_speed'] = 1;
                        $data['file_md5'] = $md5;
                        $relust = Db::name('file')->data($data)->insert();


                    }
                }
            }
            $content = str_ireplace($re['img'], $re['url'], $content);
            // dump($re);dump($content);exit;
        }
    }
    return $content;
    // dump($content);exit;
}


/*
* 功能：循环检测并创建文件夹
* 参数：$path 文件夹路径
* 返回：
*/
//$filepath = "test/upload/2010/image.gif";
//createDir(dirname($filepath));

function createDir($path)
{
    // dump($path);
    if (!file_exists($path)) {
        createDir(dirname($path));
        mkdir($path, 0777);
        @chmod($path, 0777);
    }
}

//移动文件
function moveFile($oldFile, $class, $product, $type = '照片')
{//,$old,$new
    //  dump($oldFile);exit;
    // $post['photo']
    //报名成功，复制照片到指定目录下，准备打包
    $e = explode('.', $oldFile);

    $ext = end($e);   //扩展名
    $path = $_SERVER['DOCUMENT_ROOT'] . '/../system' . DIRECTORY_SEPARATOR . 'product' . DIRECTORY_SEPARATOR . $class['title'] . path($product, $type) . DIRECTORY_SEPARATOR;//.$product['number']
    //$fileName='../system/product/'.$class['title'].path($product,'登记表').'/'.$fileName.'.xlsx';
    // $filename=$opt['name'];
    //   dump($post);exit;
    $filename = $product['number'] . '-' . $product['name'] . '.' . $ext;
    //  dump($post);exit;
    createDir(dirname($path . DIRECTORY_SEPARATOR . $filename));
    // dump($_SERVER['DOCUMENT_ROOT'].$post['photo']);exit;
    $old = $_SERVER['DOCUMENT_ROOT'] . $oldFile;//'../'.
    //  dump(is_file($old));
    // echo $old;exit;
    //  dump(file_exists($old));exit;
    if (!is_file($old)) {
        return ['code' => '999991', 'msg' => '文件不存在', 'old' => $old];
    }
    //  echo $old;exit;

    // echo $path.DIRECTORY_SEPARATOR.$filename;exit;

    $d = copy($old, $path . DIRECTORY_SEPARATOR . $filename);
    return ['code' => ($d ? '000000' : '999999'), 'old' => $oldFile, 'new' => $path . DIRECTORY_SEPARATOR . $filename];
}

//循环删除目录和文件函数 //delDirAndFile( ‘upload');
function delDirAndFile($dirName)
{
    // dump($dirName);
    //  $dirName=$dirName;
    @chmod($dirName, 0777);
    // var_dump($dirName);
    // var_dump(dirname('down'));
    /// echo $dirName;exit;
    //   var_dump(dirname($dirName).'/');exit;
    // var_dump(opendir(dirname($dirName).'/'));exit;
    // echo $dirName;exit;
    // dump($dirName);
    // dump(opendir($dirName));
    if ($handle = opendir($dirName . '/')) {

        while (false !== ($item = readdir($handle))) {
            // dump($item);
            if ($item != '.' && $item != '..') {
                if (is_dir($dirName . '/' . $item)) {
                    //  echo $dirName.'/'.$item .'--';
                    delDirAndFile($dirName . '/' . $item);
                } else {
                    @chmod($dirName . '/' . $item, 0777);
                    if (@unlink($dirName . '/' . $item))
                        // echo '成功删除文件： '.$dirName.'/'.$item.'<br />n';
                        return true;
                }
            }
        }
        closedir($handle);
        //  exit;
        if (@rmdir($dirName)) return true;//  echo '成功删除文件： '.$dirName.'/'.$item.'<br />n';
    }
}


function card($card)
{
    $arr = [];
    //php从身份证获取出生日期方法(兼容15位与18位身份证号)：
    $birthday = strlen($card) == 15 ? ('19' . substr($card, 6, 6)) : substr($card, 6, 8);
    $arr['birthday'] = date('Y-m-d', strtotime($birthday));
    //php从身份证获取性别方法：
    /*
    dump( (strlen($card)==15 ? -2 : -2));
    dump(substr($card,1, 1));
    $arr['sex']= substr($card, (strlen($card)==15 ? -2 : -1), 1) % 2 ? '2' : '1';
    */
    $sexint = (int)substr($card, 16, 1);
    $arr['sex'] = $sexint % 2 === 0 ? '2' : '1';
    // dump($arr);exit;
    //  return $sexint % 2 === 0 ? '2' : '1';
    // echo $sex//1=男 2=女
    return $arr;
}


function phone($phone)
{
    return substr($phone, 0, 3) . '****' . substr($phone, 7);

}

//生成邀请码
function newInviteCode()
{
    $invite_code = rand(100000, 999999);
    if (think\facade\Db::name('user')->where('invite_code', $invite_code)->count() > 0) {
        return newInviteCode();
    }
    return $invite_code;
}

//下一开放时间为T+1
function nextTime()
{
    return strtotime(date('Y-m-d', strtotime('+1 day')) . ' 08:30:00');
//    if(date('H')>=14){ //明天8点
//        return strtotime(date('Y-m-d',strtotime('+1 d')).' 08:30:00');
//    }else if(date('H')>=8){ //当天下午12点半开放
//        return strtotime(date('Y-m-d').' 12:30:00');
//    }else { //当天早上8点半
//        return strtotime(date('Y-m-d').' 08:30:00');
//    }
}

//配置信息
function Setting($field = false)
{
    return Db::name('config')->field($field ? $field : true)->find();
}

/** 生成新的订单号
 * @param $type
 * @return string
 */
function NewOrderNumber($type)
{
    $prevId = Db::name('order')->order('id desc')->value('id');
    return 'RTPM' . substr('00' . intval($type), -2) . substr(('00000000000000000000' . rand(1000, 9999) . '0000' . strval(intval($prevId) + 1)), -12);
}


// DECODE=解密
// ENCODE=加密
function uri($url,$operation='DECODE')
{
    if(!$url){
        return '';
    }
    if($operation=='ENCODE'){
        $key= md5($url);
        cache($key,$url);
        return $key;
    }

    return cache($url);
}
