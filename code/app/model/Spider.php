<?php

namespace app\model;

use extend\Tx;
use QL\QueryList;
use think\facade\Cache;
use think\facade\Db;
use UP\UpGrade;

class Spider
{

    public static $config;
    public static $dbname;

    //不同的来源不同库
    public static function Config($domain)
    {

        self::$config = config('spider');

        // echo '<pre>';print_r($other);exit;
        $domainKey='spider:domain:xyz:curl:';
        self::$config['xyzDomain'] =cache($domainKey);
        if(!self::$config['xyzDomain'] || $_GET['redis']=='refresh') {
            $web = Db::name('config')->where(['id' => 1])->find();
            $other = json_decode($web['other'], true);
            self::$config['xyzDomain'] =$other['xyzDomain'];
            $html = Tx::$grade::$curl->Http(self::$config['xyzDomain'], [], 2, [], 1);//httpcode
            if (in_array($html['httpcode'], [301, 302, 307]) && strpos($html['header'], "location:")) {
                $headArr = explode(PHP_EOL, $html['header']);

                foreach ($headArr as $loop) {
                    if (strpos($loop, "location:") !== false) {
                        $other['xyzDomain']=self::$config['xyzDomain'] = trim(substr($loop, strlen("location:")));
                        Db::name('config')->where(['id' =>1])->update(['other'=>json_encode($other,JSON_UNESCAPED_UNICODE)]);
                        break;
                    }
                }
            }
            if($_GET['tnxyz']=='d'){
                var_dump($html['header']); var_dump($other['xyzDomain']);var_dump(self::$config['xyzDomain']);
                echo '<pre>';print_r($headArr);exit;
            }
            cache($domainKey,self::$config['xyzDomain'],86400);
        }
      //  var_dump(self::$config['xyzDomain']);exit;


        if (in_array($domain, ['xyz', 'xzy_ad'])) {
            self::$dbname = 'spider';
        } else if (in_array($domain, ['xyz_pp', 'xvideo', 'xvideos'])) {
            self::$dbname = 'spider_pp';
        } else if (in_array($domain, ['vy', 'vy_x', 'vy_img'])) {
            self::$dbname = 'spider_vy';
        } else if (in_array($domain, ['porn','porn_video','porn_gif'])) {
            self::$dbname = 'porn_vy';
        }
        if($_GET['t3']=='d'){
            echo '<pre>';print_r(self::$config);exit;
        }
        // $dbname=self::Dbname('xyz_pp');
        // $config= config('spider');
        //  echo '<pre>'; print_r($config);exit;


        // return $dbname;
    }
    public static function RedirectBody($d,$http,$header=[]){
        $newUrl='';
        if(in_array($http['httpcode'],[301,302,303,307])){
            $headArr = explode(PHP_EOL, $http['header']);
            foreach ($headArr as $loop) {
                if (strpos(strtolower($loop), "location:") !== false) {
                    $newUrl= trim(substr(strtolower($loop), strlen("location:")));
                    break;
                }
            }
        }
        if($newUrl){
           $http = Tx::$grade::$curl->Http($newUrl, [], 3, $header,1);
            return self::RedirectBody($d,$http,$header);
        }
        return $http;
    }
    //Xvideos 列表搜索
    public static function PornTag()
    {
        self::Config('porn');
        $url = self::$config['pornDomain'];
        $typeKey = 'spider:porn:tag';
        $typeKeySt = 'spider:porn:tagst';

        if (Cache::has($typeKeySt) && Cache::has($typeKey) && $_GET['redis'] != 'refresh') {
            $typeList = json_decode( base64_decode(Cache::get($typeKey)),true);
        }

        if (!$typeList) {
            $header=[];
            $header['user-agent']='Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.198 Safari/537.36';
            $header['Accept-Language'] = 'zh-CN,zh';
            $http = Tx::$grade::$curl->Http($url, [], 3,$header,1);
         //   echo $url;
         //   var_dump($http);exit;
            $http=self::RedirectBody('porn',$http,$header);
            if ($_GET['tnv'] == 'v') {
                echo $url;
                echo '<pre>';
                print_r($http);
                exit;
            }

            $typeList = QueryList::html($http['html'])->find('.catHeaderSubMenu li.video')->map(function ($item) {
                //   $type_id = $item->find('a')->href;
             //   $name = $item->find('a')->title;
            //    $src = $item->find('a img')->src;
                $name = $item->find('a strong')->text();
                $href = $item->find('a')->href;
                 $type_id=str_ireplace('/video?c=','',$href);
              //  var_dump($href);
              // var_dump();exit;
                //$orig=base64_decode(str_ireplace('param=','',$param[0]));
                // $img = $item->find('.thumb img')->eq(0)->attrs('data-src')->toArray();
                if(is_numeric($type_id)) {
                    return ['type_name' => $name, 'type_id' => $type_id];//'type_id' => $type_id,
                }
            })->toArray();
         //  print_r($typeList);exit;

            if ($_GET['tnv'] == 'v2') {
           //     $ss= QueryList::html($xvideosHtml)->find('.ordered-label-list ul li.sub-list ul')->html();
                echo '<pre>';
                print_r($ss);
                print_r($typeList);
                exit;
            }
            Cache::set($typeKey, base64_encode(json_encode($typeList)), 86400 * 30);
            Cache::set($typeKeySt, 1, 86400 );

        }
        return $typeList;
    }

    //tags
    public static function PornGIFTag()
    {
        self::Config('porn');
        $url = self::$config['pornDomain'].'/gifs';
        $typeKey = 'spider:porngif:tag';
        $typeKeySt = 'spider:porngif:tagst';

        if (Cache::has($typeKeySt) && Cache::has($typeKey) && $_GET['redis'] != 'refresh') {
            $typeList = json_decode( base64_decode(Cache::get($typeKey)),true);
        }

        if (!$typeList) {
            $header=[];
            $header['user-agent']='Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.198 Safari/537.36';
            $header['Accept-Language'] = 'zh-CN,zh';
            $http = Tx::$grade::$curl->Http($url, [], 3,$header,1);

         //   echo $url;
         //   var_dump($http);exit;
            $http=self::RedirectBody('porn',$http,$header);
            if ($_GET['tnv'] == 'v') {
                echo $url;
                echo '<pre>';
                print_r($http);
                exit;
            }
           // var_dump( QueryList::html($http['html'])->find('.tags a')->html());exit;
            $typeList = QueryList::html($http['html'])->find('.tags a')->map(function ($item) {

                $name = $item->text();
                return ['type_name' => $name, 'type_id' => $name];//'type_id' => $type_id,
            })->toArray();
         //  print_r($typeList);exit;

            if ($_GET['tnv'] == 'v2') {
           //     $ss= QueryList::html($xvideosHtml)->find('.ordered-label-list ul li.sub-list ul')->html();
                echo '<pre>';
                print_r($ss);
                print_r($typeList);
                exit;
            }
            Cache::set($typeKey, base64_encode(json_encode($typeList)), 86400 * 30);
            Cache::set($typeKeySt, 1, 86400 );

        }
       // var_dump($typeList);exit;
        return $typeList;
    }

    //tags
    public static function XnxxTag()
    {
        self::Config('xnxx');
         $url = self::$config['xnxxDomain'].'/tags';
        $typeKey = 'spider:xnxx:tag';
        $typeKeySt = 'spider:xnxx:tagst';

        if (Cache::has($typeKeySt) && Cache::has($typeKey) && $_GET['redis'] != 'refresh') {
            $typeList = json_decode( base64_decode(Cache::get($typeKey)),true);
        }

        if (!$typeList) {
            $header=[];
            $header['user-agent']='Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.198 Safari/537.36';
            $header['Accept-Language'] = 'zh-CN,zh';
            $http = Tx::$grade::$curl->Http($url, [], 3,$header,1);

       //   echo $url;   var_dump($http);exit;
            $http=self::RedirectBody('xnxx',$http,$header);
            if ($_GET['tnv'] == 'v') {
                echo $url;
                echo '<pre>';
                print_r($http);
                exit;
            }
           // var_dump( QueryList::html($http['html'])->find('.tags a')->html());exit;
            $typeList = QueryList::html($http['html'])->find('#tags li')->map(function ($item) {
                $name = $item->find('a')->text();
                return ['type_name' => $name, 'type_id' => $name];//'type_id' => $type_id,
            })->toArray();
        //   print_r($typeList);exit;

            if ($_GET['tnv'] == 'v2') {
           //     $ss= QueryList::html($xvideosHtml)->find('.ordered-label-list ul li.sub-list ul')->html();
                echo '<pre>';
                print_r($ss);
                print_r($typeList);
                exit;
            }
            Cache::set($typeKey, base64_encode(json_encode($typeList)), 86400 * 30);
            Cache::set($typeKeySt, 1, 86400 );

        }
       // var_dump($typeList);exit;
        return $typeList;
    }


    public static function XnxxSearch($d, $search='', $page = 1)
    {

        self::Config('xnxx');
        $url = self::$config['xnxxDomain'] . ($search?('/search?search='.$search):('/best/'.date('Y-m')) );
        if($page>1) {
            $url .= (stripos($url, '?') ? '&' : '?') . ('page=' . ($page));
        }
        //echo $url;exit;
        $header=[];
        $header['user-agent']='Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.198 Safari/537.36';
        $header['accept-encoding']='gzip';
        $header['Accept-Language'] = 'zh-CN,zh';
        $header['Sec-Fetch-Dest'] = 'document';
        $header['Sec-Fetch-Mode'] = 'navigate';
        $header['Sec-Fetch-User'] = '?1';
        $header['Upgrade-Insecure-Requests'] = '1';
        $redisKey = 'nxxx:orig:search' . $search.$page;

           $body=cache($redisKey);
        if(!$body || $_GET['redis']=='refresh') {
            $html = Tx::$grade::$curl->Http($url, [], 5, $header, 1);
            $html = self::RedirectBody('xnxx', $html); //
            $body = gzdecode($html['html']); //
          //  cache($redisKey,$body,600);
        }
            // echo $url; var_dump($html);var_dump($body); //exit;

                //    var_dump(QueryList::html($body)->find('.dropdownHottestVideos ')->html());exit;
        $list = QueryList::html($body)->find('.mozaique .thumb-block')->map(function ($item) {
            $orig_id = $item->find('.thumb-inside a')->href;
            $name = $item->find('.thumb-under a')->title;
            $image = $item->find('img')->attr('data-src');
          //  var_dump($image);exit;
            return ['orig_id' =>$orig_id, 'image' =>  $image, 'name' => $name];
        })->toArray();
        if($_GET['tx']=='x3'){
            echo '<pre>';print_r($list);print_r($body);exit;
        }
        return $list;
    }


    public static function XnxxDetail($d,$orig_id)
    {

        self::Config('xnxx');
        $url = self::$config['xnxxDomain'] .$orig_id;
        //echo $url;exit;
        $header=[];
        $header['user-agent']='Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.198 Safari/537.36';
        $header['accept-encoding']='gzip';
        $header['Accept-Language'] = 'zh-CN,zh';
        $header['Sec-Fetch-Dest'] = 'document';
        $header['Sec-Fetch-Mode'] = 'navigate';
        $header['Sec-Fetch-User'] = '?1';
        $header['Upgrade-Insecure-Requests'] = '1';
        $redisKey = 'nxxx:orig:id' . md5($orig_id);

        $body=cache($redisKey);
        if(!$body || $_GET['redis']=='refresh') {
            $html = Tx::$grade::$curl->Http($url, [], 5, $header, 1);
            $html = self::RedirectBody('xnxx', $html); //
            $body = gzdecode($html['html']); //
          //  cache($redisKey,$body,600);
        }
//             echo $url; var_dump($html);var_dump($body); exit;

        $script_list = QueryList::html($body)->find('script')->texts()->toArray();
//$data = QueryList::Query($url,$reg)->jsonArr;
        // echo '<pre>';
        $json = [];
        $other = [];
     //
        foreach ($script_list as $v) {
            //   var_dump($script_list);exit;
            if (preg_match('/html5player.setVideoTitle\(\'(.+)\'\);/', $v, $arr)) {
               // $more = json_decode($arr[1], true);
              //  echo '---';var_dump();//exit;
                $name=$arr[1];

            }
            if (preg_match('/html5player.setVideoHLS\(\'(.+)\'\);/', $v, $arr)) {
                $json['source']['HD'] = $arr[1];
            }
            if (preg_match('/html5player.setVideoUrlHigh\(\'(.+)\'\);/', $v, $arr)) {
                $json['source']['SD'] = $arr[1];
            }
            if (preg_match('/html5player.setThumbSlideBig\(\'(.+)\'\);/', $v, $arr)) {
                $image = $arr[1];
            } if (preg_match('/video_related=(.+);w/', $v, $arr)) {
                $other = json_decode($arr[1],true);
                $more_list=[];
                foreach ($other as $v){
                    $more_list[]=['orig_id' =>$v['u'], 'image' =>  $v['i'], 'name' => $v['tf']];
                }
            }
        }
        if ($name) {
            $data['name'] = $name;
        }
        $data['json'] = $json;
        $data['image'] = $image;
        $data['other'] = $more_list;

  //      echo '<pre>';print_r($data);exit;
        return $data;
    }



    public static function PornGIFSearch($d, $search='', $page = 1)
    {

        self::Config('porn');
        $url = self::$config['pornDomain'] . ($search ? '/gifs/search?search=' . $search : '/gifs');
        $url.=(stripos($url,'?')?'&':'?').('page=' . ($page ));
       //echo $url;exit;
        $header=[];
        $header['user-agent']='Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.198 Safari/537.36';
        $header['accept-encoding']='gzip, deflate';

        $html = Tx::$grade::$curl->Http($url, [],  2,$header,1);

        $html=self::RedirectBody('porn',$html); //

        $body=gzdecode($html['html']);
   //    var_dump(QueryList::html($body)->find('.dropdownHottestVideos ')->html());exit;
        $list = QueryList::html($body)->find('.gifsWrapper .gifs li.gifVideoBlock')->map(function ($item) {
           $name = $item->find('a span.title')->text();
           $image = $item->find('video')->attr('data-poster');
           $href = $item->find('a')->href;
           $e=explode('/',$href);
           return ['orig_id' =>end($e), 'image' =>  $image, 'name' => $name];
        })->toArray();
        if($_GET['tx']=='x3'){
            echo '<pre>';print_r($list);print_r($body);exit;
        }
        return $list;
    }
    public static function PornSearch($d,$typeId=0, $search='', $page = 1)
    {

        self::Config('porn');
        $url = self::$config['pornDomain'] . ($search ? '/video/search?search=' . $search : ($typeId?'/video?c='.$typeId:'/video'));
        $url.=(stripos($url,'?')?'&':'?').('page=' . ($page ));
       //echo $url;exit;
        $header=[];
        $header['user-agent']='Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.198 Safari/537.36';
        $header['accept-encoding']='gzip, deflate';

        $html = Tx::$grade::$curl->Http($url, [],  2,$header,1);

        $html=self::RedirectBody('porn',$html); //

        $body=gzdecode($html['html']);
   //    var_dump(QueryList::html($body)->find('.dropdownHottestVideos ')->html());exit;
        $list = QueryList::html($body)->find('li.pcVideoListItem')->map(function ($item) {
           $name = $item->find('a')->title;
           $image = $item->find('img')->src;
            $orig_id = $item->attr('data-video-vkey');
            return ['orig_id' =>$orig_id, 'image' =>  $image, 'name' => $name];
        })->toArray();
        if($_GET['tx']=='x3'){
            echo '<pre>';print_r($list);print_r($body);exit;
        }
        return $list;
    }

    public static function PornGIFDetail($d,$orig_id)
    {

        self::Config('porn');
        $url = self::$config['pornDomain'] .'/gif/' .$orig_id;
        //echo $url;exit;
        $header=[];
        $header['user-agent']='Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.198 Safari/537.36';
        $header['accept-encoding']='gzip, deflate';

        $html = Tx::$grade::$curl->Http($url, [],  2,$header,1);

        $html=self::RedirectBody('porn',$html); //

        $body=gzdecode($html['html']);
        //    var_dump(QueryList::html($body)->find('.dropdownHottestVideos ')->html());exit;
        $name = QueryList::html($body)->find('.gifTitle h1')->text();
        $gif = QueryList::html($body)->find('#gifImageSection .centerImage')->attr('data-gif');
        $mp4 = QueryList::html($body)->find('#gifImageSection .centerImage')->attr('data-mp4');

        $other = QueryList::html($body)->find('.imageNav a')->map(function ($item) {
            $name = $item->find('img')->alt;
            $image = $item->find('img')->src;
            $href = $item->find('a')->href;
            $e=explode('/',$href);
            return ['orig_id' =>end($e), 'image' =>  $image, 'name' => $name];
        })->toArray();

        return ['name'=>$name,'json'=>['image'=>[$gif],'mp4'=>$mp4],'other'=>$other];
    }
    //view-source:https://cn.pornhub.com/view_video.php?viewkey=ph6196107385809

    public static function PornDetail($d,$orig_id)
    {

        self::Config('porn');
         $url = self::$config['pornDomain'] . '/view_video.php?viewkey=' . $orig_id ;
        if(in_array($d,['porn_gif','porn_img'])){
          //  $url = self::$config['pornDomain'] . '/vodata/' . $orig_id . '/play.html';
        }
        $redisKey = 'porn:orig:id' . $orig_id;
     //   $body=cache($redisKey);
        if(!$body) {
            $header=[];
            $header['user-agent']='Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.198 Safari/537.36';
            $header['accept-encoding']='gzip, deflate, br';
            $html = Tx::$grade::$curl->Http($url, [],  2,$header,1);
            $html = self::RedirectBody('porn', $html); //
            $body=gzdecode($html['html']);
            cache($redisKey, $body);
        }
      //  $tag_list = QueryList::html($body)->find('.title h1,.cat_pos_l')->texts()->toArray();
      //  $name = explode('»', $tag_list[0]);
     //   $name = trim(end($name));

        $newData = [];

        //var_dump($body);
        $js= QueryList::html($body)->find('.mainPlayerDiv script')->eq(0)->html();
     //   preg_match('/\"video_title\":\"(.+)\",\"/', $js, $n);
        if (preg_match('/video_title\":\"(.+)\",/U', $js, $n)) {
         //   echo '<pre>';
       //     print_r($n);
          //  var_dump( ord($n[1]));
            $newData['name'] =  json_decode('"'.$n[1].'"',true);
        //    var_dump();
           // var_dump( iconv('UCS-2', 'UTF-8', 'u6211'));
        }

        if (preg_match('/var player_mp4_seek(.+)nextVideoPlaylistObject/s', $js, $arr)) {
            preg_match_all('/var(.*);/iUs', $arr[1],$arr2);
            $list=[];
            $media=[];
            foreach($arr2[1] as $v){
                $vName=trim($v);
             //   var_dump(strstr($vName,'=',true));
                if(stripos($vName,'media_')!==false){
                    $media[strstr($vName,'=',true)] = strstr($vName,'=');
                }else {
                    $list[strstr($vName,'=',true)] = str_ireplace(['" + "','"'],'',substr(strstr($vName,'='),1));
                }
            }
            $path=[];
            foreach ($media as $k=>$v){
              // $val= explode('+',$v);
                if(stripos($v,'media')===false) {
                    preg_match_all('/\/\*(.*)\*\//iUs', $v, $val);
                    $a = str_ireplace(array_merge($val[0], ['+', '=', ' ']), ';', $v);
                    // var_dump($k);
                    // var_dump($v);
                    if ($a ) {
                        $s = explode(';', $a);
                        $p = '';
                        foreach ($s as $domain) {
                            if ($domain) {
                                $p .= $list[$domain];
                            }
                        }
                        if(stripos($p,'m3u8')!==false){
                            $path[$k] = $p;
                        }

                    }
                }

               // print_r($s); exit;
            }

          //  echo '<pre>';
          // print_r($path);
          //  print_r($list);
           // print_r($arr);
        }
       // var_dump($js);

      //  echo '<pre>';
     //  print_r($newData);
      //  exit;

        if(in_array($d,['porn_video'])){ //showvod
            if($path) {
                $newData['json']['source']=$path;
                $newData['json']['source']['SD'] = '/rep/lazy/porn?url='.urlencode(end($path));
           //     $newData['json']['source']['HD'] = end($path);
                $newData['json']['source']['HD'] = '/rep/lazy/porn?url='.urlencode($path[array_keys($path)[0]]);
              //  $newData['json']['source']['HD'] = 'https://dv-h.phncdn.com/hls/videos/202210/21/417962121/1080P_4000K_417962121.mp4/index-f1-v1-a1.m3u8?ttl=1674020247&l=0&ipa=54.164.120.181&hash=78ce84f3de9697d134d5c38ab9226e49';
            }
        }else {
            /*
            $img_list = QueryList::html($html)->find('.t_msgfont>img')->attrs('src')->toArray();
            if (count($img_list) == 0) {
                $img_list = QueryList::html($html)->find('.nbodys>img.lazyload')->attrs('data-original')->toArray();
            }


            if (count($img_list) > 0) {
                foreach ($img_list as &$v) {
                    $v = self::$config['vyImgTrans'] . str_ireplace(['pic.xxappxx.xyz', 'pic.xxpicxx.xyz'], 'pic.readpicz.com', $v);
                }
                //  $newData['domain'] = 'vy_img';
                $newData['json'] = ['image' => $img_list];
            } else {
                //  $newData['domain'] = 'vy_x';
                $newData['html'] = QueryList::html($html)->find('.content')->html();
                $newData['html'] = $newData['html'] ? $newData['html'] : QueryList::html($html)->find('.nbodys')->html();
            }
            */
        }
       //   echo '<pre>';     print_r($newData);exit;
        //更多 //"related_url": "https://cn.pornhub.com/video/player_related_datas?id=417962121",
        return $newData;

    }


    //Xvideos 详细页面
    public static function XvideosDetail($orig_id)
    {
        self::Config('xvideos');
        $key = 'spider:xvideos:' . md5($orig_id);
        if (Cache::has($key) && $_GET['redis'] != 'refresh') {
            return Cache::get($key);
        }
        $url = self::$config['xvideosDomain'] . $orig_id;
        //  echo $xnxxDomain. $spider['orig_id'];exit;
        $header = [];
        /*
        $header['Host']='www.xvideos.com';
        $header['User-Agent']='Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.198 Safari/537.36';
         $header['Pragma']='no-cache';
        $header['Sec-Fetch-Dest']='document';
        $header['Sec-Fetch-Mode']='navigate';
        $header['Sec-Fetch-Site']='none';
        $header['Sec-Fetch-User']='?1';
        $header['Upgrade-Insecure-Requests']='1';
        */
        $http = Tx::$grade::$curl->Http($url, [],  3,$header,1);
        $http=self::RedirectBody('xvideos',$http,[]);
        $xvideosHtml=$http['html'];

     //   var_dump($xvideosHtml);exit;
   // var_dump($url);exit;
        $len = stripos($xvideosHtml, '<!-- Url : ');

        if ($len !== false) {
            $str = substr($xvideosHtml, stripos($xvideosHtml, '<!-- Url : ') + 11, -4);
            $e = explode(' ', $str);
            if ($orig_id == $e[1]) {
                //   echo $xnxxDomain. $e[0];
                $data['orig_id'] = $e[0];
                $xvideosHtml = Tx::$grade::$curl->Http(self::$config['xvideosDomain'] . $e[0], [], $header, 3);
            }
            //  var_dump($spider['orig_id']);
            //  var_dump($e);
        }

       //    var_dump($xvideosHtml);exit;
        if ($_GET['tnv'] == 'v') {
            var_dump($e);
            echo self::$config['xvideosDomain'] . $e[0];
            echo '<pre>';
            print_r($xvideosHtml);
            exit;
        }

        $script_list = QueryList::html($xvideosHtml)->find('script')->texts()->toArray();
//$data = QueryList::Query($url,$reg)->jsonArr;
        // echo '<pre>';
        $json = [];
        $other = [];
        // print_r($xvideosHtml);exit;

        foreach ($script_list as $v) {
            //   var_dump($script_list);exit;
            if (preg_match('/var video_related=(.+);window.wpn_categories/', $v, $arr)) {
                $more = json_decode($arr[1], true);
                // var_dump($more);exit;
                if ($more) {
                    foreach ($more as $v1) {
                        $newData = [];
                        $newData['orig_id'] = $v1['u'];
                        if (strlen($newData['orig_id']) >= 200) {
                            $newData['orig_id'] = substr($newData['orig_id'], 0, 190);
                        }
                        //  echo '<pre>';var_dump();

                        $newData['name'] = $v1['tf'];
                        if (strlen($newData['name']) >= 200) {
                            $newData['name'] = substr($newData['name'], 0, 190);
                        }

                      //  $newData['json'] = ['cover' => $v1['i']];
                        $newData['image'] = $v1['i'];
                        $newData['domain'] = 'xyz_pp';
                        // print_r($newData);

                        $other[] = $newData;
                    }

                }
            }
            // echo 111;exit;
            // echo '<pre>';print_r($other);exit;
            $data['other'] = $other;
            //  var_dump($script_list);exit;
            if (preg_match('/html5player.setVideoHLS\([\',\"](.+)[\',\"]\)/', $v, $arr)) {
                $json['source']['HD'] = $arr[1];
            }
            if (preg_match('/html5player.setVideoUrlHigh\([\',\"](.+)[\',\"]\)/', $v, $arr)) {
                $json['source']['SD'] = $arr[1];
            }

            //大图
            if (preg_match('/html5player.setThumbUrl169\([\',\"](.+)[\',\"]\)/', $v, $arr)) {
                $json['cover'] = $arr[1];
            }            //大图
            if (preg_match('/html5player.setVideoTitle\([\',\"](.+)[\',\"]\)/', $v, $arr)) {
                $data['name'] = $arr[1];
            }

            if (!$json['cover']) { //小图
                if (preg_match('/html5player.setThumbUrl169\([\',\"](.+)[\',\"]\)/', $v, $arr)) {
                    $json['cover'] = $arr[1];
                }
            }
        }
        if (!$json['source']['HD'] && !$json['source']['SD']) {
            //self::Config('xyz');
          //  var_dump(self::$config['xyzDomain']);
           //exit;
            $url = self::$config['xyzDomain'] . '/pp?param=' . $orig_id . '&type=pachong';
            if ($_GET['x'] == 'u2') {
                echo '<pre>';
                print_r($url);
                echo PHP_EOL;
                print_r(base64_decode($orig_id));
                exit;
            }
            $xyzHtml = Tx::$grade::$curl->Http($url, [], [], 5);
            $xyz_script_list = QueryList::html($xyzHtml)->find('script')->texts()->toArray();
            foreach ($xyz_script_list as $val) {
                if (preg_match('/"source": JSON.stringify\((.+)\)/', $val, $arr)) {
                    // print_r($arr);
                    $json['source'] = json_decode($arr[1], true);
                }
            }
        }

        $data['json'] = $json;
        $tag_list = QueryList::html($xvideosHtml)->find('.video-tags-list>ul>li>a>span.name')->texts()->toArray();
        $tag = QueryList::html($xvideosHtml)->find('.video-tags-list>ul>li>a')->texts()->toArray();
        foreach ($tag as $k => $v) {
            if ($tag_list[$k]) {
                $tag[$k] = $tag_list[$k];
            }
        }


        $newSpData = ['is_show' => 1, 'json' => $json, 'json' => $json, 'update_time' => time()];
        if ($data['orig_id']) {
            $newSpData['orig_id'] = $data['orig_id'];
        }
        if (strlen($newSpData['orig_id']) >= 200) {
            $newSpData['orig_id'] = substr($newSpData['orig_id'], 0, 190);
        }
        if (strlen($newSpData['name']) >= 200) {
            $newSpData['name'] = substr($newSpData['name'], 0, 190);
        }

        if ($json) {
            Cache::set($key, $data, 600);
        }
        return $data;
        // var_dump($string2);exit;
    }


    //Xvideos 列表搜索
    public static function XvideosSearch($search = '', $p = 1, $insert = true)
    {

        self::Config('xvideos');
        $path=($search?'/?k=' . $search :'/lang/chinese');
        $url = self::$config['xvideosDomain'] .$path.(stripos($path,'?')?'&':'?') . 'p=' . ($p - 1);
      //  var_dump($search); var_dump($url);exit;
        $header = [];
        $header['Accept'] = 'text/html,application/xhtml+xml,application/xml';
        $header['Accept-Language'] = 'zh-CN,zh';
        $xvideosHtml = Tx::$grade::$curl->Http($url, [], 3, $header);

        if ($_GET['tnv'] == 'v') {
            echo $url;
            echo '<pre>';
            print_r($xvideosHtml);
            exit;
        }
        $list = QueryList::html($xvideosHtml)->find('.mozaique .thumb-block ')->map(function ($item) {
            $orig = $item->find('.thumb a')->href;
            //$param= explode('&',$href['query']);
            //$orig=base64_decode(str_ireplace('param=','',$param[0]));
            $img = $item->find('.thumb img')->eq(0)->attrs('data-src')->toArray();
            return ['orig_id' => $orig, 'image' =>$img[0], 'name' => $item->find('.title a')->title];

        });
        $newList = [];
        if ($list) {
            foreach ($list as $v1) {
                $newData = [];
                $newData['orig_id'] = $v1['orig_id'];
                $newData['name'] = $v1['name'];
                $newData['image'] = $v1['image'];
                $newData['domain'] = 'xyz_pp';
                if (!$newData['orig_id']) {
                    continue;
                }
           //     $newData['image'] = $newData['json']['cover'];
                $newList[] = $newData;
            }
        }
        return $newList;
    }


    //Xvideos 列表搜索
    public static function XvideosTag()
    {
        self::Config('xvideos');
        $url = self::$config['xvideosDomain'];
        $typeKey = 'spider:xvideos:tag';
        $typeKeySt = 'spider:xvideos:tagst';
        if (Cache::has($typeKeySt) && Cache::has($typeKey) && $_GET['redis'] != 'refresh') {
            $typeList = json_decode( base64_decode(Cache::get($typeKey)),true);
        }
       // var_dump($typeList);exit;
        if (!$typeList) {
            $header = [];
            $header['Accept'] = 'text/html,application/xhtml+xml,application/xml';
            $header['Accept-Language'] = 'zh-CN,zh';
            $xvideosHtml = Tx::$grade::$curl->Http($url, [], 3, $header);

            if ($_GET['tnv'] == 'v') {
                echo $url;
                echo '<pre>';
                print_r($xvideosHtml);
                exit;
            }

            $typeList = QueryList::html($xvideosHtml)->find('.ordered-label-list ul li.sub-list ul li.topterm')->map(function ($item) {
             //   $type_id = $item->find('a')->href;
                $name = $item->find('a')->text();
                //$param= explode('&',$href['query']);
                //$orig=base64_decode(str_ireplace('param=','',$param[0]));
                // $img = $item->find('.thumb img')->eq(0)->attrs('data-src')->toArray();
                return [ 'type_name' => $name];//'type_id' => $type_id,

            })->toArray();
            if ($_GET['tnv'] == 'v2') {
                $ss= QueryList::html($xvideosHtml)->find('.ordered-label-list ul li.sub-list ul')->html();
                echo '<pre>';
                print_r($ss);
                print_r($typeList);
                exit;
            }
            Cache::set($typeKey, base64_encode(json_encode($typeList)), 86400 * 30);
            Cache::set($typeKeySt, 1, 86400 );

        }
        return $typeList;
    }

    public static function XyzTag()
    {
        self::Config('xyz');
        $typeKey = 'spider:xyz:tag';
        if (Cache::has($typeKey) && $_GET['redis'] != 'refresh') {
            $typeList = json_decode( base64_decode(Cache::get($typeKey)),true);
        }
        if (!$typeList) {
            $headers=[];

            $headers['user-agent']='Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.198 Safari/537.36';

            $html = Tx::$grade::$curl->Http(self::$config['xyzDomain'], [], 2, $headers);
            /*
            $typeList = QueryList::html($html)->find('.type-box .column a')->map(function ($item) {
                $href = parse_url($item->href);
                $param = explode('=', $href['query']);
                $name = $item->text();
                return ['type_id' => $param[1], 'type_name' => $name];
            })->toArray();
            */
            $typeList = QueryList::html($html)->find('.tags a')->map(function ($item) {
                $href = parse_url($item->href);
              //  $param = explode('=', $href['query']);
                $name = $item->text();
                return ['type_name' => $name];
            })->toArray();
            if($_GET['tx']=='x1'){
                echo '<pre>';print_r($typeList);print_r($html);exit;
            }
            Cache::set($typeKey, base64_encode(json_encode($typeList)), 86400 * 30);
        }
        return $typeList;
    }


    public static function XyzSearch($type = 'cj', $search, $page = 1)
    {
        self::Config('xyz');
        //$type=self::XyzType();
        //   $url=self::$config['xyzDomain'];/.'https://s64nkximmm.top/s?k={key}&type=cj';
        //   $html = Tx::$grade::$curl->Http(self::$config['xyzDomain'], [], [], 2);
        $url = self::$config['xyzDomain'] . ($search ? '/s?k=' . $search  .'&type='.$type: '');
        $url.=(stripos($url,'?')?'&':'?').('page=' . ($page ));
        $headers=[];
        $headers['user-agent']='Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.198 Safari/537.36';
        $html = Tx::$grade::$curl->Http($url, [],  2,$headers);
        $list = QueryList::html($html)->find('.vod-box .column a')->map(function ($item) {
            $href = parse_url($item->href);
            $param = explode('=', $href['query']);
            $name = $item->find('img')->alt;
            $image = $item->find('img')->src;
            return ['orig_id' => $param[1], 'image' =>  $image, 'name' => $name];
        })->toArray();
        if($_GET['tx']=='x3'){
            echo '<pre>';print_r($list);print_r($html);exit;
        }

        return $list;
    }

    /**
     * @param $type =cj,pachong
     * @param $orig_id
     * @return void
     * @throws \think\db\exception\DbException
     */
    public static function XyzDetail($type = 'cj', $orig_id)
    {
        self::Config('xyz');
        //  $orig_id=$spider['orig_id'];
        // $orig_id = base64_encode($orig_id);
        $key = 'spider:xyz:' . $type . ':' . md5($orig_id);
        if (Cache::has($key) && $_GET['redis'] != 'refresh') {
           // return Cache::get($key);
        }
        if ($type == 'cj') {
            $url = self::$config['xyzDomain'] . '/p?v=' . $orig_id;
        } else if ($type == 'pachong') {
            $url = self::$config['xyzDomain'] . '/pp?param=' . $orig_id . '&type=' . $type;
        } else {
            return [];
        }
        $headers=[];
        $headers['user-agent']='Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.198 Safari/537.36';
        $html = Tx::$grade::$curl->Http($url, [],  2,$headers);//httpcode
        if ($_GET['tnx'] == 'x') { //565179
            echo($url . PHP_EOL);
            echo '<pre>';
            print_r($html);
            exit;
        }
        $xyz_more_list = QueryList::html($html)->find('.vod-box .column a')->map(function ($item) {
            $href = parse_url($item->href);
            $param = explode('=', $href['query']);
            $name = $item->find('img')->alt;
            $image = $item->find('img')->src;
            return ['orig_id' => $param[1], 'image' => $image, 'name' => $name];
        })->toArray();

       //  echo '<pre';print_r($xyz_more_list);exit;
        $xyz_script_list = QueryList::html($html)->find('script')->texts()->toArray();
        $json = [];
        foreach ($xyz_script_list as $val) {
            if (stripos($val, 'Aliplayer')) {
                if (preg_match('/"source": JSON.stringify\((.+)\)/', $val, $arr)) {
                    $json['source'] = json_decode($arr[1], true);
                }
                if (!$json['source'] && preg_match('/"source": "(.+)"/', $val, $arr)) {

                    $json['source'] = ['HD' => $arr[1]];
                }
                if (preg_match('/"cover": "(.+)"/', $val, $arr)) {
                    //   print_r($arr);
                    $image= $arr[1];
                }
            }
        }
        //  echo '<pre>';print_r($json);exit;
        $name = QueryList::html($html)->find('.play-box .vod-name')->text();
        if ($name) {
            $data['name'] = $name;
        }
        $data['json'] = $json;
        $data['image'] = $image;
        $data['other'] = $xyz_more_list;
        if ($json['image']) {
            Cache::set($key, $data, 600);
        }
        //  echo '<pre>';print_r($data);exit;
        return $data;
        // var_dump($string2);exit;
    }

    public static function VyTag($type='vy')
    {
        self::Config($type);
        $typeKey = 'spider:vy:tag:'.$type;
        if (Cache::has($typeKey) && $_GET['redis'] != 'refresh') {
            $typeList = json_decode( base64_decode(Cache::get($typeKey)),true);
        }
        if (!$typeList) {
            $html = Tx::$grade::$curl->Http(self::$config['vyDomain'] . '/index.html', [], [], 2);
        //    var_dump($html);exit;
            if($type=='vy_img'){
                $query=  QueryList::html($html)->find('#menu ul')->eq(4);
            }else if($type=='vy_x'){
                $query=  QueryList::html($html)->find('#menu ul')->eq(5);
            }else if($type=='vy_video'){
                $query=  QueryList::html($html)->find('#menu ul')->eq(1);
            }else{
                $query= QueryList::html($html)->find('#menu ul');
            }
          //  var_dump($type);exit;
            $typeList =$query->find('a')->map(function ($item) {
                //  $href = parse_url($item->href);
                $param = explode('/', $item->href);
                $name = $item->text();
                if ((int)end($param)) {
                    return ['type_id' => (int)end($param), 'type_name' => $name];
                }
            })->toArray();
            Cache::set($typeKey, base64_encode(json_encode($typeList)), 86400 * 30);
        }
        //  echo '<pre>'; print_r($typeList);
        return $typeList;

    }

    public static function VyList($d,$typeId,$page=1)
    {
      //  $typeId = 13;
        $typeId = $typeId.($page>1?'-'.$page:'');
        //  $domain ='https://www.gvyryoktxo.xyz:55443';
        $domain = '';
        //https://www.gvyryoktxo.xyz:55443/arts/204060.html
        //   $type=rand(22,30);
        self::Config('vy');
        $url = self::$config['vyDomain'] . '/artlist/' . $typeId . '.html';
        if(in_array($d,['vy_video'])){
            $url = self::$config['vyDomain'] . '/vodlist/' . $typeId . '.html';
        }
        // echo $url;exit;
        $redisKey = 'vy:orig:type' . $typeId;
        if (Cache::has($redisKey) && $_GET['redis'] != 'refresh') {
            $list = Cache::get($redisKey);
        }
        if (!$list) {
            $html = Tx::$grade::$curl->Http($url, [], [], 2);
            //视频
            if(in_array($d,['vy_video'])) {
                $list = QueryList::html($html)->find('.vodlist .listpic')->map(function ($item) {
                    $param = explode('/', $item->find('a')->href);
                    $image = self::$config['vyImgTrans'] . $item->find('.vodpic')->attr('data-original');
                   // var_dump($param);exit;

                    return ['orig_id' => (int)($param[2]), 'image' => $image, 'name' => $item->find('.vodname')->text()];
                })->toArray();
            }else {
                $list = QueryList::html($html)->find('.piclist .listpic')->map(function ($item) {
                    $param = explode('/', $item->find('a')->href);
                    $image = self::$config['vyImgTrans'] . $item->find('.vodpic')->attr('data-original');
                    return ['orig_id' => (int)end($param), 'image' => $image, 'name' => $item->find('.vodname')->text()];
                })->toArray();


                if (!$list) { //图片为空
                    $list = QueryList::html($html)->find('.newslist ul')->map(function ($item) {
                        //   var_dump($item->find('a')->href);
                        $param = explode('/', $item->find('a')->href);
                        return ['orig_id' => (int)end($param), 'name' => $item->find('a')->text()];
                    })->toArray();
                }
            }
           if($list){
               Cache::set($redisKey, $list, 600);
           }
        }
        return $list;
        //  print_r($list);exit;


    }

    public static function VyDetail($d,$orig_id)
    {

        //  $domain ='https://www.gvyryoktxo.xyz:55443';
        $domain = '';
        //https://www.gvyryoktxo.xyz:55443/arts/204060.html
        self::Config('vy_img');
        $url = self::$config['vyDomain'] . '/arts/' . $orig_id . '.html';
        if(in_array($d,['vy_video'])){
            $url = self::$config['vyDomain'] . '/vodata/' . $orig_id . '/play.html';
        }
        $redisKey = 'vy:orig:id' . $orig_id;
        $html = Tx::$grade::$curl->Http($url, [], [], 2);

        $tag_list = QueryList::html($html)->find('.title h1,.cat_pos_l')->texts()->toArray();
        $name = explode('»', $tag_list[0]);
        $name = trim(end($name));
        $newData = [];
        $newData['name'] = $name;
        if(in_array($d,['vy_video'])){ //showvod
          //
           $src= QueryList::html($html)->find('#showvod source')->src;
            $newData['json']['source']['HD'] =$src;
            return $newData;
        }else {
            $img_list = QueryList::html($html)->find('.t_msgfont>img')->attrs('src')->toArray();
            if (count($img_list) == 0) {
                $img_list = QueryList::html($html)->find('.nbodys>img.lazyload')->attrs('data-original')->toArray();
            }


            if (count($img_list) > 0) {
                foreach ($img_list as &$v) {
                    $v = self::$config['vyImgTrans'] . str_ireplace(['pic.xxappxx.xyz', 'pic.xxpicxx.xyz'], 'pic.readpicz.com', $v);
                }
                //  $newData['domain'] = 'vy_img';
                $newData['json'] = ['image' => $img_list];
            } else {
                //  $newData['domain'] = 'vy_x';
                $newData['html'] = QueryList::html($html)->find('.content')->html();
                $newData['html'] = $newData['html'] ? $newData['html'] : QueryList::html($html)->find('.nbodys')->html();
            }
        }
        return $newData;

    }


    //锁
    public static function Lock($type = 'add', $domain)
    {
        $statusKey = 'xyz:' . $domain . ':status';
        if ($type == 'del') {
            return Tx::$redis->client->del($statusKey);
        } else if ($type == 'add') {
            while (1) {
                $count = Tx::$redis->setnx($statusKey, 1);
                if ($count === 1) {
                    Tx::$redis->client->expire($statusKey, 10);
                    break;
                }
                echo 'usleep' . PHP_EOL;
                usleep(50); //暂停100毫秒
            }
            return $count;
        }

    }

    //标签
    public static function Tag($tags = [], $domain, $lock = true)
    {
        $ids = [];
        $key = 'xyz:tag:' . $domain;
        // 去重
        //UPDATE `cms_tag` SET `is_show` = 0 WHERE  id in(select a.id from (SELECT *,count(1) as c  FROM `cms_tag` where is_show=1 GROUP BY tag_name,domain HAVING count(1)>=2 )as a)
        //UPDATE cms_spider_pp AS sp ,
        // (SELECT t1.id as oldTagId,t2.id as newTagId  FROM `cms_tag` as t1
        //	left join cms_tag  as t2 on t2.tag_name =t1.tag_name and t2.is_show=1
        //	where t1.is_show=0)  as tag
        //SET sp.tag_ids = REPLACE(sp.tag_ids,tag.oldTagId,tag.newTagId)
        //where sp.tag_ids like CONCAT('%"',tag.oldTagId,'"%')


        foreach ($tags as $tag_name) {
            $tagid = Tx::$redis->client->get($key . md5($tag_name));
            // var_dump($tagid);exit;
            $tagid = Db::name('tag')->where('tag_name', $tag_name)->where(['is_show' => 1])->where('domain', $domain)->value('id');
            if (!$tagid) {
                if ($lock == true) {
                    self::Lock('add', $domain);
                }
                //   var_dump($status);exit;
                Db::name('tag')->insert(['tag_name' => $tag_name, 'domain' => $domain, 'add_time' => time()]);
                $tagid = Db::name('tag')->getLastInsID();
                $ids[$tagid] = $tag_name;
                Tx::$redis->setnx($key . md5($tag_name), $tagid);
                if ($lock == true) {
                    self::Lock('del', $domain);
                }
            } else {
                $ids[$tagid] = $tag_name;
            }
        }
        return $ids;
    }
}
