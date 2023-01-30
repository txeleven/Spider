<?php

namespace app\rep\controller;

use app\console\controller\Spider;
use app\console\server\SpiderServer;
use app\server\Upload;
use think\facade\Db;

class Manage extends Base
{
    public function __construct()
    {
        // setSession('user_info',Db::name('user')->where('id',4)->find());
        $this->loginCheck = 1;
        parent::__construct();
        if($_GET['test']=='user'){
            dump($this->user);exit;
        }
    }
    public function index(){

        return $this->fetch();
    }


    //用户资料详细
    public function config(){
      //  $xyzCount=Db::name('spider')->count();
      //  $ppCount=Db::name('spider_pp')->where(['domain'=>'xyz_pp'])->count();
      //  $xCount=Db::name('spider_vy')->where(['domain'=>'vy_x'])->count();
      //  $imgCount=Db::name('spider_vy')->where(['domain'=>'vy_img'])->count();
        $this->assign(['title'=>'修改配置','xyzCount'=>$xyzCount,'ppCount'=>$ppCount,'xCount'=>$xCount,'imgCount'=>$imgCount]);
        //var_dump($this->user);
        return $this->fetch();
    }
    // 编辑用户信息
    public function Edit()
    {
        $post=$this->params['POST'];
        $data = [];
        $data['website']=$post['website'];
        $data['other']=$this->web['other'];
        $data['other']['xvideos']=$post['xvideos']; //开启xvideos
        $data['other']['xyzDomain']=$post['xyzDomain'];
        $data['other']['autoLogin']=$post['autoLogin'];
        $data['other']=json_encode($data['other'],JSON_UNESCAPED_UNICODE);
        Db::name('config')->where(['id' =>1])->update($data);
        return $this->json('000000', '修改成功');
    }



}
