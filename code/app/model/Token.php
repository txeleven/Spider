<?php
/** 登录验证检查
 * Created by PhpStorm.
 * User: TX.Z
 * Date: 2017/12/11
 * Time: 14:03
 */

namespace app\model;
use think\facade\Db;
use think\Request;

class Token
{
    private static $url='http://tx.cms.eleven.com';
    //token 验证
    private static function check($token){
        if(!self::cache($token))  return ['code'=>'990000','token'=>true,'msg'=>'token 验证失败'];                  //验证失败


        return ['code'=>'990000','token'=>true,'msg'=>'验证通过']; //验证通过
    }

    //请求远程创建token信息
    public static function createTokenUrl($data=[]){

       $url=self::$url.'/index.php/api/session/createToken';

        return  Curl::post($url,['redis'=>'refresh','map'=>json_encode($data,true)]);
    }

    //创建 token
    public static function createToken($data=[]){
        if(empty($data['app_id']) || empty($data['app_key'])) return ['code'=>'990012','msg'=>'app_id app_key 不能为空']; //验证通过
        $session=Db::name('admin')->where(['app_id'=>$data['app_id'],'app_key'=>$data['app_key']])->find();
        if(empty($session)) return ['code'=>'990013','msg'=>'app_id app_key 不存在'];
      //  if(!is_array($data)) return false;
        $time=date('YmdH'); //有效时间请重新做方法 现在有效期为一小时，建议扩大
       // $admin=$data['id'];
        $info=$data['app_id'].$data['app_key'];
        $host=Request::instance()->host();
        $ip=Request::instance()->ip();
        //$module=Request::instance()->module();
        $string=$time.$info.$host.$ip;
        $api_token=md5($string);
        self::cache('server_'.$api_token,$session);

        if($data['is_session']) session($data['is_session'],$session); //写入session
        return ['code'=>'000000','msg'=>'成功','token'=>$api_token];
       // return $api_token;
        //dump($api_token);exit;
       // api_token = md5 ('模块名' + '控制器名' + '方法名' + '2013-12-18' + '加密密钥') = 770fed4ca2aabd20ae9a5dd774711de2

    }


    //验证token信息
    public static function sessionCheck($token,$data){
        $check=self::check($token) ;  //签名验证
        if($check && $check['code']=='000000'){ //token 验证通过
            $data['token']=$token;
            $data=authcode($data,'DECODE');
            $tokencache=(array)self::cache($token);
            $tokencache['session']=$data;
            return ['code'=>'000000','msg'=>'session 写入成功']; //验证通过
        }
        return ['code'=>'990000','msg'=>'验证通过']; //验证通过

    }


    //数据验证 验证
    public static function checkData($token,$data=false){
        if(!self::cache($token))  return ['code'=>'990000','token'=>true,'msg'=>'token 验证失败'];                  //验证失败
        if($data){
            $session=authcode($data,'DECODE');
            if($session['token']!=$token)   return ['code'=>'990001','token'=>true,'msg'=>'token 验证失败'];
        }
        return ['code'=>'000000','token'=>true,'msg'=>'验证通过']; //验证通过
    }



    //缓存token信息
    public static function cache($key,$val='',$time=false){
        $cache_path=['path'=>app()->getRuntimePath().'redis_cache/token/','time'=>($time?$time:config('login_time'))];
        return cache($key,$val,$cache_path);
    }

}
