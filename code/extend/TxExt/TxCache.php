<?php
namespace extend\TxExt;


use think\facade\Cache;

/**
 * 使用cache 代替redis
 */
class TxCache
{
    //     $rel= $redis ->set("aaa",'bb')->getPayload();
    public $client;
    function __construct(){
        $config=config('cache.stores.redis');

        $this->client=Cache::store('file');
    }

    /** //添加数据
     * @param $key
     * @param $val
     * @param $expire
     * @return void
     */
    public function set($key,$value,$expire=3600){
       return  $this->client->set($key, $value, $expire);

    }
    public function setnx($key,$value,$expire=3600){
       return  $this->client->set($key, $value, $expire);

    }
    public function incr($key){
      //  Cache::inc($key);
       return  $this->client->inc($key);

    }

    //查询数据
    public function get($key){
        return  $this->client->get($key);

    }

    //删除数据
    public function del($key){
        return  $this->client->delete($key);

    }

    //设置有效期
    public function expire($key,$expire=3600){
        $value=Cache::get($key);
        return  $this->client->set($key, $value, $expire);
      //  return  $this->client->expire($key);
     //   return $this->client->expire($key,$expire);//->getPayload();
    }

}
