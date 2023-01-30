<?php

namespace app\rep\controller;

use think\facade\Db;

class User extends Base
{
    public function __construct()
    {
        // setSession('user_info',Db::name('user')->where('id',4)->find());
        $this->loginCheck = 1;
        parent::__construct();
        if($_GET['test']=='user'){
            dump($this->user);exit;
        }
        $this->assign(['user'=>$this->user]);
        $this->assign(['title'=>'用户中心']);
    }
    public function index(){
        //print_r($this->user);exit;
    //    $this->assign(['classlist'=>$classlist,'productCount'=>$productCount]);
        return $this->fetch();
    }


    //用户资料详细
    public function Detail(){
        $this->assign(['title'=>'个人资料']);
        //var_dump($this->user);
        return $this->fetch();
    }

    //上传用户logo
    public function EditUserLogo(){

        $upload = Upload::Image('image', '', '');
        $upload['msg']= $upload['message'];
        $user=Db::name('user')->field('headimgurl')->where(['id'=>$this->user['id']])->find();
        $user['headimgurl']=json_decode($user['headimgurl'],true);
        $user['headimgurl']['logo']=$upload['info']['fileSrc'];
        $user['headimgurl']=json_encode($user['headimgurl'],JSON_UNESCAPED_UNICODE);
        $rel=Db::name('user')->where(['id'=>$this->user['id']])->update($user );
    // echo    Db::name('user')->getLastSql();
        $upload['userupload']=$rel;
      //  var_dump($rel);exit;
        return $this->json($upload);
    }


}
