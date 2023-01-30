<?php

namespace app\rep\controller;


use think\Cache;
use think\captcha\Captcha;
use think\facade\Db;
use think\facade\Event;
use think\facade\Request;
use extend\Tx;
use UP\UpGrade;

class Login extends Base
{
    public function __construct()
    {
        //  setSession('user_info',Db::name('user')->where('id',4)->find());
        //  $this->loginCheck=1;
        parent::__construct();
      //  $str=Event::trigger('\extend\TxRedis');
       // var_dump(app()->getBasePath().'../extend/TxRedis.php');exit;
        //    dump($this->user);exit;

    }

    //首页
    public function index()
    {
        $this->assign(['title' => '登录']);
     //  $ss= Tx::$jwt->JwtAuth('aaaa');
        return $this->fetch();
    }



    //登录
    public function Login()
    {
        $post=$this->params['POST'];
      //  var_dump($this->params);exit;
        if (empty($post['phone'])) return $this->json('900001', '手机号不能为空');
        if (empty($post['password'])) return $this->json('900002', '密码不能为空');
        $where = [];
        $where['user_name'] =$post['phone'];
        $where['password'] = md5($post['password']);
        $where['is_show'] = 1;
        $user=Db::name('user')->field('id,user_name,last_time,is_audit')->where($where)->find();
        //  echo Db::name('user')->getLastSql();exit;
        if (empty($user)) {
            return $this->json('900003', '账号密码错误。');
        }
        if($this->web['other']['autoLogin']!='open' && $user['is_audit'] != 2){  //不开放自动登录
            return $this->json('900003', '未开放自动登录');
        }

        $rel=Tx::$grade::$jwt->JwtAuth($user,7200);
        return $this->json($rel);

    }

    //注销
    public function Logout()
    {
        $rel=Tx::$grade::$jwt->JwtAuth([]);
        return $this->json($rel);

    }


    //验证码
    public function verify()
    { //,'fontSize'=>30,'imageW'=>200,'imageH'=>60
        $Captcha = new Captcha(['length' => 4]);
       // var_dump($Captcha);exit;
        return $Captcha->entry('home');
    }

    //清理缓存及临时文件
    public function clear()
    {
        $dir=app()->getRuntimePath().'../';

       $return=  UpGrade::deltree($dir);
       return $this->json(['code'=>'000000','msg'=>$return]);
    }

}
