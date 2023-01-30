<?php

namespace app\model;

use extend\Tx;
use think\facade\Db;

class User
{

    //获取用户信息
    public static function GetUserInfo(int $uid,bool $redis=false) :array{
        $user=Tx::$redis->get('user:'.$uid);
        !$redis && $redis=$_GET['redis']=='refresh'?true:false;
        if(!$user || $redis==true){
            $user=Db::name('user')->where(['id'=>$uid,'is_show'=>1])->find();
            $user['headimgurl']=json_decode($user['headimgurl'],true);
        //    $user['user_service_money']=Db::name('user_service_money')->where(['id'=>$user['id'],'is_show'=>1])->sum('service_money');
            Tx::$redis->set('user:'.$uid,$user,600);
        }
        //var_dump($user);exit;

        if(!$user){
            return [];
        }
        return $user;
    }
}
