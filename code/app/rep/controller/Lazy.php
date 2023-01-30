<?php

namespace app\rep\controller;

use app\model\Spider;
use extend\Tx;
use think\facade\Db;

class Lazy extends Base
{
    public function __construct()
    {
        $this->loginCheck = 1;
        parent::__construct();
        //    dump($this->user);exit;
        $this->assign(['user'=>$this->user]);
    }
    public function index(){
        if(in_array($_GET['d'],['vy','vy_img','vy_x','vy_video'])){
          //  print_r(Spider::VyTag($_GET['d']));exit;
            $this->assign(['tag'=>Spider::VyTag($_GET['d'])]);
        }else if(in_array($_GET['d'],['xyz'])){
            $this->assign(['tag'=>Spider::XyzTag()]);
        }else if(in_array($_GET['d'],['xyz_pp','xvideos','xvideo'])){
            $this->assign(['tag'=>Spider::XvideosTag()]);
        }else if(in_array($_GET['d'],['xnxx_video'])){
          //  var_dump(pider::XnxxTag());exit;
            $this->assign(['tag'=>Spider::XnxxTag()]);
        }else if(in_array($_GET['d'],['porn_video'])){
            $this->assign(['tag'=>Spider::PornTag()]);
        }else if(in_array($_GET['d'],['porn_gif','porn_img'])){
            $this->assign(['tag'=>Spider::PornGIFTag()]);
        }
        return $this->fetch();
    }
    public function ajax(){

        $search=$_GET['tag_id']?$_GET['tag_id']:$_GET['search'];
        if(in_array($_GET['d'],['xyz_pp','xvideos','xvideo'])) {
            $list = Spider::XvideosSearch(urlencode($search), input('get.page', '0', 'intval'), false);
        }elseif(in_array($_GET['d'],['xyz'])){
           // $search=$_GET['d']=='xyz_img' &&!$_GET['search']?'最新':$_GET['search'];
           // var_dump(!$search && ((int)$_GET['page'])<=1);exit;
            if($search || ((int)$_GET['page'])<=1){
               // var_dump($search);exit;
               $list=Spider::XyzSearch('cj',urlencode($search),input('get.page','1','intval'));
            }else{
                $list=[];
            }

        }elseif(in_array($_GET['d'],['vy_img','vy_x','vy_video'])){
         //   $search=$_GET['d']=='xyz_img' &&!$_GET['search']?'最新':$_GET['search'];
            $list=Spider::VyList($_GET['d'],input('get.tag_id','0','intval'),input('get.page','1','intval'));
        }elseif(in_array($_GET['d'],['xnxx_video'])){
            //   $search=$_GET['d']=='xyz_img' &&!$_GET['search']?'最新':$_GET['search'];
            $search=!$_GET['search']?$_GET['tag_id']:$_GET['search'];
            $list=Spider::XnxxSearch($_GET['d'],$search,input('get.page','1','intval'));
        }elseif(in_array($_GET['d'],['porn_video'])){
            //   $search=$_GET['d']=='xyz_img' &&!$_GET['search']?'最新':$_GET['search'];
            $list=Spider::PornSearch($_GET['d'],input('get.tag_id','0','intval'),urlencode($search),input('get.page','1','intval'));
        }elseif(in_array($_GET['d'],['porn_gif','porn_img'])){
            $search=!$_GET['search']?$_GET['tag_id']:$_GET['search'];
            $list=Spider::PornGIFSearch($_GET['d'],urlencode($search),input('get.page','1','intval'));
        }
      //  $list=[];
        //    echo '<pre>';print_r($list);exit;
            $data['list']=$list;
            //  echo '<pre>';print_r($data);
            return $this->json('000000', 'success', $data);
    //
    }


    public function tag(){
        /*
        $map=[];
        $map['is_show']=1;
        $map['domain']=input('get.d');
        $tag_list=Db::name('tag')->where($map)->order('view asc')->limit(1000)->select()->toArray();
*/
        $this->assign(['tag_list'=>$tag_list]);
        return $this->fetch();
    }


    public function Detail(){
        $map=[];
     //   $map['is_show']=[0,1,2,-2,-1];
        $map['orig_id']=input('get.orig_id');
        $map['domain'] = input('get.d', '');

        // $dbname=in_array($map['domain'],['xyz','xyz_ad'])?'spider':'spider_pp';
      //  $dbname=SpiderServer::Dbname($map['domain']);
      //  $product=Db::name($dbname)->field(true)->where($map)->json(['tag_ids','json'])->find();
        if(in_array( $map['domain'],['xyz'])){
             $data = Spider::XyzDetail('cj',$map['orig_id']);

        }elseif(in_array( $map['domain'],['xyz_pp','xvideos'])){
            $data = Spider::XvideosDetail($map['orig_id']);
        }elseif(in_array( $map['domain'],['vy_img','vy_x','vy_video'])){
            $data = Spider::VyDetail($_GET['d'],$map['orig_id']);
        }elseif(in_array( $map['domain'],['xnxx_video'])){
            $data = Spider::XnxxDetail($_GET['d'],$map['orig_id']);
        }elseif(in_array( $map['domain'],['porn_video'])){
            $data = Spider::PornDetail($_GET['d'],$map['orig_id']);
        }elseif(in_array( $map['domain'],['porn_gif'])){
            $data = Spider::PornGIFDetail($_GET['d'],$map['orig_id']);
        }

        $this->assign(['product'=>$data,'other'=>$data['other'],'title'=>$data['name']]);
        $view='detail';
        if(in_array($_GET['d'],['vy','vy_x','vy_img','porn_gif'])){
           $view='images';
        }
      //  var_dump($data);exit;

        // var_dump($view);exit;
        if($_GET['test']=='data'){
            echo '<pre>';print_r($data);

            exit;
        }

        //echo $view;exit;
        return $this->fetch($view);
    }

    public function porn(){
      //  echo 111;
       // echo '<pre>';   print_r($_GET);exit;
        $url=$_GET['url'];
     //   var_dump(strrpos($_GET['url'],'/'));
      //  var_dump(strrpos($_GET['url'],'?'));
        $str=substr($_GET['url'],0,strrpos($_GET['url'],'/')+1);//.'/xxxx'.substr($_GET['url'],strrpos($_GET['url'],'?'));
       // var_dump(substr($_GET['url'],strrpos($_GET['url'],'?')));
     // var_dump($url);
    //  var_dump($str);


        $redisKey='aabb:'.md5($url);
       //    $body=cache($redisKey);
        if(!$body) {
            $header=[];
            $header['user-agent']='Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.198 Safari/537.36';
        //    $header['accept-encoding']='gzip, deflate, br';
            $html = Tx::$grade::$curl->Http($url, [],  2,$header,1);
           // var_dump($html['header']);exit;
            if($html['header']){
                foreach (explode(PHP_EOL,$html['header']) as $v){
                    if(stripos($v,':')&& stripos($v,'content-length')===false){
                       // var_dump($v);
                        header($v);
                    }
                }
            }

            if(stripos($url,'.ts')!==false) {
              //  header('content-type: video/MP2T');
                echo $html['html'];exit;
              //  $body=gzdecode($html['html']);
            }else{
                $body=($html['html']);
            }
          //  cache($redisKey, $body);
        }
   //     var_dump($body);
//var_dump($html);exit;
/*
  //      echo $url;exit;
        if(stripos($_GET['url'],'.ts')!==false) {
        //    header('content-type: video/MP2T');
        }else{
        //    header('application/vnd.apple.mpegurl');
        }
*/
      //  var_dump($html['header']);exit;


      //  print_r($html['header']);exit;
        // echo '<pre>';var_dump($html);exit; //url('').'?url='.

       $list=  explode("\n",$body);
        foreach($list as $v){
            if(stripos($v,'index-')!==false || stripos($v,'seg-')!==false){
                if(stripos($v,'seg-')!==false) {
                 //   echo $str . $v;
                    echo  '/rep/lazy/porn?url='.urlencode($str.$v)."\n";
                }else {
                     echo  '/rep/lazy/porn?url='.urlencode($str.$v)."\n";
                }
            }else{
                echo $v."\n";
            }

        }
        exit;
        print_r($list);
//var_dump($body);
       // preg_match_all('/[index-|seg-](.*)\n/iUs',$body,$arr2);
       // echo '<pre>';print_r($arr2);exit;
//    var_dump($body);
   //echo '<pre>';   var_dump($body);    print_r($_GET);exit;
      //  var_dump($url);
//var_dump($html);exit;
       //

        $get=$_GET;
       // unset($get['url']);
      //  $str3=http_build_query(array_merge($get);

       // var_dump($str3);exit;
        $str3='';
        $body=str_ireplace(['index-','seg-','validfrom11='.$_GET['validfrom']],[url('').'?url='.urlencode($str.'index-'),url('').'?url='.urlencode($str.'seg-'),$str3],$body);
       // var_dump($body);
       // var_dump($html);exit;
     // var_dump($html);
        echo $body;exit;
        var_dump($html);exit;
    }


}
