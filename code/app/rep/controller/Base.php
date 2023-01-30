<?php
namespace app\rep\controller;

use app\BaseController;
use app\model\User;
use extend\Tx;
use think\facade\Db;
use think\facade\Request;

error_reporting(0);

class Base extends BaseController
{
    public $web=[]; //title keyword
    public $params; //参数
    public $user;
    public $loginCheck=1; //登录检查

    public function __construct(){
        //  if($_REQUEST['language']=='en') cookie('language',trim($_REQUEST['language']));
        parent::_initialize();
        header("Content-type: text/html; charset=utf-8");

    //    $this->url=config('other_config.server_url');
        $this->params['GET']=input('get.','','trim');
        $this->params['POST']=input('post.','','trim,htmlspecialchars');
        if(!$this->params['POST']){
            $post=file_get_contents('php://input')?file_get_contents('php://input'):$GLOBALS['HTTP_RAW_POST_DATA'];
            $this->params['POST']=json_decode($post,true);

        }

        $this->params['action']=$action=strtolower(Request::instance()->action());
        $this->params['controller']=$controller=strtolower(Request::instance()->controller());
        $web=Db::name('config')->where(['id'=>1])->find();
      //  var_dump($web);exit;
        $web['other']=json_decode($web['other'],true);
        // dump($web);exit;
        $this->web=$web;
        $this->assign(['web' => $web]);
        in_array($controller,['login','register']) && $this->loginCheck=0; // 登录/注册不需要验签
        //var_dump(Tx::$grade::$jwt->JwtData());exit;
       if($this->loginCheck==1){
            $rel=Tx::$grade::$jwt->JwtCheck(7200);
       //    var_dump($rel);exit;
            if($rel['code']!='000000'){
                echo  $this->error($rel['msg'],url('login/index',['uri'=>uri($_SERVER['REQUEST_URI'],'ENCODE')])->build());
              exit;
               //   return $this->redirect(url('error/index',['code'=>$rel['code'],'msg'=>$rel['msg']]));
            }
        }


         $user=Tx::$grade::$jwt->JwtData();
        // var_dump($user);exit;
         if($user['code']=='000000' && $user['data']){
           //  var_dump($user);exit;
             $this->user=User::GetUserInfo($user['data']['id']);
             $this->assign('user',$this->user);
         }


    }


    public function _empty(){
       // dump(Request::instance()->action());exit;
      //  dump(Request::instance()->action());exit;
        return $this->fetch(Request::instance()->action());
       // echo '<!----_empty--->';exit;
    }


    /** 返回json数据
     * @param $is_show  返回代码  000000 =成功，其它为失败
     * @param $msg  返成信息内容
     * @param $info 其它信息 array
     * @return mixed
     */
    public function json($error,$msg='',$info=[]){
        $code=(is_array($error)?$error['code']:$error);
        $msg=(is_array($error)?$error['msg']:$msg);
        $info=(is_array($error)?$error['info']:$info);

        $msg=(!empty($this->errorCode[$code]))?$this->errorCode[$code]:(''.$this->errorCode[$code].$msg);
        // header('Content-type:application/json;charset=utf8');
        //  $data=is_array($code)?$code:['code'=>$code,'msg'=>$msg,'info'=>$info];
        // echo json([1]); exit;
        // dump($code);dump($msg);
        //  echo Response::create($data,'json',200);
        // echo json();

        if($_REQUEST['callback']){
             return jsonp(['code'=>$code,'msg'=>$msg,'info'=>$info,'iframeName'=>input('get.iframeName','','trim')]);
        }else{
            return json(['code'=>$code,'msg'=>$msg,'info'=>$info,'iframeName'=>input('get.iframeName','','trim')]);
        }

        //echo json_encode(['code'=>$code,'msg'=>$msg,'info'=>$info],JSON_UNESCAPED_UNICODE);
        // dump($code);dump($msg);dump($info);exit;
        //dump($code);dump($msg);dump($info);exit;
        //JSON_UNESCAPED_UNICODE

    }

}
