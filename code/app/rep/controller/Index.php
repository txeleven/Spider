<?php
namespace app\rep\controller;


use app\server\OrderServer;
use extend\Tx;
use think\Cache;
use think\facade\Db;
use think\Exception;
use think\facade\Request;

class Index extends Base
{
    public function __construct(){
        $this->loginCheck=0;
        if($this->web['other']['autoLogin']!='open'){ //不开放自动登录
            $this->loginCheck=1;
        }
        parent::__construct();
        $this->assign('params',$this->params);
        $this->assign('user',$this->user);
        $this->assign('web',$this->web);

    }
    private function autoLogin(){
      //  echo '<pre>';print_r();exit;
        if($this->web['other']['autoLogin']!='open'){ //不开放自动登录
            return ;
        }
        if(!$this->user) {
            $openid= md5(md5(Request::ip()));
            $user=Db::name('user')->where('openid',$openid)->find();
            if(!$user) {
                //自动注册
                $user = [];
                $user['user_name'] = 'UN' . rand(10, 99) .(Db::name('user')->order('id desc')->value('id') + 1);
                $user['password'] = md5('123456');
                $user['last_time'] = $user['add_time'] = time();
                $user['is_show'] = 1;
                $user['is_audit'] = 0;
               // $user['openid'] = $openid;
                Db::name('user')->strict(true)->data($user)->insert();
                $user=Db::name('user')->where('openid',$openid)->find();
            }else{
                Db::name('user')->where(['id' => $user['id']])->update(['last_time' =>time()]);
            }

            Tx::$jwt->JwtAuth($user);
          return   $this->redirect(url(''));
        }
     // echo '<pre>';  print_r($user);exit;

    }
    //home
    public function index(){

        $this->autoLogin();
        return $this->redirect(url('lazy/index',['d'=>'xvideos'])->build());

    }

}
