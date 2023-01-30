<?php
/**
 * 1. 支持FTP/SFTP远程增删除诹文件
 * 2. 支持每天一次定时升级
 * 3. 支持手动强制升级
 * 4. 支持文件批量打包及解压(限制是5MB以下)
 * 5. 能对code分目录上传
 *
 * 配置文件信息在 up.lock 文件信息下面
 */

namespace UP;

class  UpGrade
{
    public static $ftp;
    public static $sftp;
    public static $curl;
    public static $file;
    public static $zip;
    public static $mail;
    public static $redis;
    public static $jwt;

    public static function Init($option = [])
    {
        self::$ftp = new Ftp();
        self::$sftp = new SFtp();
        self::$curl = new Curl();
        self::$file = new File();
        self::$zip = new Zip();
        self::$mail = new Email();
        self::$redis = new ClientRedis();
        self::$jwt = new JWTCookie();
        return self::class;
        //  return self::$client;
        //   return $this;
    }

    //目录循环
    public static function dirloop($dir, &$list = [])
    {
        if (substr($dir, -1) != '/') {
            $dir .= '/';
        }
        $handle = opendir($dir);
        while (($file = readdir($handle)) !== false) {
            if (!in_array($file, ['.', '..', './', '../'])) {
                if (!is_dir($dir . $file)) {
                    array_push($list, $dir . $file);
                } else {
                    self::dirloop($dir . $file, $list);
                }
            }
        }
        return $list;
    }

    //删除目录及下面的所有文件
    public static function deltree($pathdir): bool
    {
        //    echo $pathdir;//调试时用的
        if (self::isEmptyDir($pathdir))//如果是空的
        {
           rmdir($pathdir);//直接删除
        } else { //否则读这个目录，除了.和..外
            $d = dir($pathdir);

            while ($a = $d->read()) {

                if(in_array($a, ['.', '..'])){
                    continue;
                }
                if (is_file($pathdir . '/' . $a)) {
                    unlink($pathdir . '/' . $a);
                }
                //如果是文件就直接删除
                if (is_dir($pathdir . '/' . $a)) {
                    if (!self::isEmptyDir($pathdir . '/' . $a))//是否为空
                    { //如果不是，调用自身，d不过是原来的路径+他下级的目录名
                        self::deltree($pathdir . '/' . $a);
                    }
                    if (self::isEmptyDir($pathdir . '/' . $a)) {//如果是空就直接删除
                        $s=rmdir($pathdir . '/' . $a);
                    }
                }

                //  echo "必须先删除目录下的所有文件";//我调试时用的
            }
            $d->close();
        }
        return true;
    }

    public static function isEmptyDir($pathdir): bool
    {
        //判断目录是否为空
        $d = opendir($pathdir);
        $i = 0;
        while ($a = readdir($d)) {
            $i++;
        }
        closedir($d);
        if ($i > 2) {
            return false;
        } else {
            return true;
        }
    }
}

// Ftp 操作方法
class Ftp
{
    private $connect;
    public $status;

    public function __construct() //ftp配置
    {
    }

    public function client($config = []) //ftp配置
    {
        //连接服务器
        if ($connect = ftp_connect($config['host'], $config['port'])) {
            if ($connect == false) {
                $this->status = false;
            };
            //登录服务器
            if (!ftp_login($connect, $config['user'], $config['password'])) {
                $this->status = false;
            } else {
                $this->status = true;
            }
            //打开被动模式，数据的传送由客户机启动，而不是由服务器开始
            ftp_pasv($connect, false);
            $this->connect = $connect;
        } else {
            $this->status = false;
        }
        return $this;
        //返回连接标识
        //  return $this->connect=$connect;
    }


    /**
     * 关闭服务器
     */
    public function close()
    {
        ftp_close($this->connect);
        $this->status = false;
        return 'close';
    }


    /**
     * 创建目录并将目录定位到当请目录
     *
     * @param resource $connect 连接标识
     * @param string $dirPath 目录路径
     * @return mixed
     *       2：创建目录失败
     *       true：创建目录成功
     */
    public function mkdir($dirPath)
    {

        $dirPath = '/' . trim($dirPath, '/');
        $dirPath = explode('/', $dirPath);
        foreach ($dirPath as $dir) {
            if ($dir == '') $dir = '/';
            //判断目录是否存在

            if (!@ftp_chdir($this->connect, $dir)) {
                //判断目录是否创建成功
                if (@ftp_mkDir($this->connect, $dir) == false) {
                    //return $this;
                    $this->status = false;
                }
                @ftp_chdir($this->connect, $dir);
            }
        }

        return $this;
    }

    /**
     * 上传文件
     *
     * @param string $flag 服务器标识
     * @param string $local 上传文件的本地路径
     * @param string $remote 上传文件的远程路径
     * @return int
     */
    public function upload($local, $remote)
    {
        //上传文件目录处理
        $mdr = $this->mkdir(dirname($remote));
        if ($mdr === false) {
            $this->status = false;
            return $this;
        };

        if (!file_exists($local)) {
            $this->status = false;
            return $this;
        }

        //上传文件
        $this->status = ftp_put($this->connect, $remote, $local, FTP_BINARY);

        return $this;
        // return (!$result) ? false : true;
    }

    /**
     * 删除文件
     *
     * @param string $flag 服务器标识
     * @param string $remote 文件的远程路径
     * @return int
     */
    public function remove($remote)
    {
        //删除
        $result = ftp_delete($this->connect, $remote);
        return $this;
        //返回结果
        //  return (!$result) ? false : true;
    }

    /**
     * 读取文件
     *
     * @param string $flag 服务器标识
     * @param string $remote 文件的远程路径
     * @return mixed
     */
    public function read($remote)
    {
        //读取
        $result = ftp_nlist($this->connect, $remote);

        //  echo "<pre>";
        //  print_r($result);exit;
        //返回结果
        foreach ($result as $key => $value) {
            if (in_array($value, array('.', '..'))) unset($result[$key]);
        }
        $this->close($this->connect);
        return array_values($result);
    }

    /**
     * 下载文件
     */
    public function down($local, $remote)
    {
        $result = ftp_get($this->connect, $local, $remote, FTP_BINARY);
        $this->close($this->connect);
        return $result;
    }
}

/** SFTP 还没有经过测试 */
class SFtp
{
    private $connect;
    public $status;

    public function client($config)
    {

        if (@$config['pubkey_file']) {
            /*
            $methods['hostkey'] = $config['pubkey_file'] && $config['privkey_file'] && $config['passphrase']?'ssh-rsa':[];
            $connect = ssh2_connect($config['host'], $config['port'], $methods);
            //(1) 使用秘钥的时候  用户认证协议
            $rc = ssh2_auth_pubkey_file($connect, $config['user'], $config['pubkey_file'], $config['privkey_file'], $config['passphrase']);
            */
        } else {
            //  echo '<pre>';print_r($config);
            $connection = ssh2_connect($config['host'], $config['port']);
            if (ssh2_auth_password($connection, $config['user'], $config['password'])) {
                $this->status = true;
            } else {
                throw new Exception('Unable to connect.');
                $this->status = false;
            }

            //(2) 使用登陆用户名字和登陆密码
            //$rc = ssh2_auth_password($connect, $config['user'], $config['password']);

            //  var_dump($connection);exit;
        }
        $this->connect = ssh2_sftp($connection);
    }

    // 传输数据 传输层协议,获得数据
    public function download($remote, $local)
    {
        return ssh2_scp_recv($this->connect, $remote, $local);
    }


    //传输数据 传输层协议,写入ftp服务器数据
    public function upload($remote, $local, $file_mode = 0664)
    {
        return ssh2_scp_send($this->connect, $local, $remote, $file_mode);
    }


    //创建目录
    public function mkdir($remote, $file_mode = 0664)
    {
        return ssh2_sftp_mkdir($this->connect, $remote, $file_mode);
    }


    // 删除文件

    public function delete($remote)
    {
        $sftp = ssh2_sftp($this->connect);
        $rc = false;
        if (is_dir("ssh2.sftp://{$sftp}/{$remote}")) {
            //      $rc = false ;
            // ssh 删除文件夹
            $rc = ssh2_sftp_rmdir($sftp, $remote);
        } else {
            // 删除文件
            $rc = ssh2_sftp_unlink($sftp, $remote);
        }
        return $rc;
    }

}

class Curl
{
    public $log;

    public function __construct($option = [])
    {   //['curlLog']
        if ($option['isLog']) {
            $this->log = $option['isLog'];
        }
    }

    /**
     * @param $url 网址
     * @param $posts post参数
     * @param $timeout 超时时间
     * @param $headers 头部信息
     * @param $refheader 是否返回头部信息
     * @return array|mixed
     */
    public function Http($url, $posts = [], $timeout = 10, $headers = [],$refheader=0)
    {

        /*
        $key='cms:curl:http'.md5($url.json_encode($posts).);
        if(Cache::has($key) && $_GET['redis']!='refresh'){
            return Cache::get($key);
        }
        */

        $httpheader = [];
        foreach ($headers as $k => $v) {
            array_push($httpheader, $k . ':' . $v);
        }
        $postfields = (is_array($posts) || is_object($posts)) ? json_encode($posts, JSON_UNESCAPED_UNICODE) : $posts;
        $oCurl = curl_init();
        if (stripos($url, "https://") !== false) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
        }
        if ($httpheader) curl_setopt($oCurl, CURLOPT_HTTPHEADER, $httpheader);
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($oCurl, CURLOPT_TIMEOUT, $timeout);
        if ($posts) {
            curl_setopt($oCurl, CURLOPT_POSTFIELDS, $postfields);
        }
        curl_setopt($oCurl, CURLOPT_HEADER, 1);
        //追踪句柄的请求字符串（允许查看请求header）
        curl_setopt($oCurl, CURLINFO_HEADER_OUT, true);
        $sContent = curl_exec($oCurl);
        //解决返回的json字符串中返回了BOM头的不可见字符（某些编辑器默认会加上BOM头）
        $result = trim($sContent,chr(239).chr(187).chr(191));
        //获取状态码
        $httpcode = curl_getinfo ($oCurl, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($oCurl, CURLINFO_HEADER_SIZE);
        if(stripos($headers['accept-encoding'],'gzip')!==false) {
            curl_setopt($oCurl, CURLOPT_ENCODING, 'gzip');
        }

        curl_close($oCurl);
        //关闭URL请求
        $header = substr($result, 0, $headerSize);
        $html = substr($result, $headerSize);
        $rel=array(
            "httpcode" => $httpcode,
            "header" => $header,
            "html" => $html
        );

        /*
                if ($sContent){
                    Cache::set($key,$rel,3600);
                }
                */
        if ($this->log) {
            $data = [];
            $data['time'] = date('Y-m-d H:i:s');
            $data['url'] = $url;
            $data['post'] = $posts;
            $data['relContent'] = $rel;
            (new File())->name('./runtime_log_' . date('Ymd') . '.txt')->write(json_encode($data, JSON_UNESCAPED_UNICODE) . PHP_EOL);
        }

        if($refheader==1){
            return $rel;
        }else{
            return $rel['html'];
        }

    }
}

//文件操作(缓存类使用)
class File
{
    private $file;
    public $status;
    public $fileName;

    /**
     * “r”:只能读取文件，不能写入文件（写入操作被忽略）
     * “w”:只能写入文件，不能读取文件（读取操作被忽略）
     * “a”:只追加文件，与“w”类似，区别是“w”删除原有的内容，“a”不删除原有内容，只追加内容
     */
    public function __construct()
    {

    }

    public function name($name, $type = 'a+')
    {
        $this->fileName = $name;
        if ($this->file = fopen($this->fileName, $type)) {
            $this->status = true;
        } else {
            $this->status = false;
        }
        return $this;
    }

    //读取文件
    public function read()
    {
        $line = fgets($this->file);
        fpassthru($this->file);
        //   $this->close();
        return $line;
    }

    //写入文件
    public function write($content, $type = 'w+')
    {
        if ($type == 'w') {
            $this->file = fopen($this->fileName, 'w');
        }
        // var_dump($this->file);exit;
        fwrite($this->file, $content);
        return $this;
    }

    //关闭
    public function close()
    {
        $this->status = false;
        return fclose($this->file);
    }
}

//文件压缩
class Zip
{
    private $zip;
    public $zipName;

    public function __construct()
    {
    }

    public function name($zipName)
    {
        $this->zipName = $zipName;
        return $this;
    }

    //ZIP压缩文件
    public function nar($fileList = [])
    {
        // var_dump($fileList);exit;
        $this->zip = new \ZipArchive();
        $this->zip->open($this->zipName, \ZipArchive::CREATE);   //打开压缩包
        foreach ($fileList as $key => $file) {
            if (is_numeric($key)) {
                $this->zip->addFile(substr($file, 0, 2) == './' ? substr($file, 2) : $file);   //向压缩包中添加文件
            } else {
                $this->zip->addFile(substr($file, 0, 2) == './' ? substr($file, 2) : $file, $key);   //向压缩包中添加文件
            }
        }
        return $this->zip->close();  //关闭压缩包
    }

    //解压文件 $dest=解压目录
    public function dec($dest)
    {
        $this->zip = new \ZipArchive();
        $this->zip->open($this->zipName);   //打开压缩包
        $this->zip->extractTo($dest);
        return $this->zip->close();
    }

}

class Email
{
    public $from;

    /*
    * @param string $from  配置信息
    $from['Helo']='Server';
    $from['FromName']='aabb';// '天下第一帅';
    $from['Username']='support@aa.cn';//
    $from['Password']='';// '密码';
    $from['From']='support';//
    $from['Host']='imap.exmail.qq.com';//
    $from['Port']='465';//
    */
    public function From($from)
    {
        $this->from = $from;
    }

    /**
     * 发送邮件方法
     * @param string $to：接收者邮箱地址
     * @param string $title：邮件的标题
     * @param string $content：邮件内容
     * @return boolean  true:发送成功 false:发送失败
     */
    public function sendMail($to, $title, $content, $from = [], $SMTPDebug = 0)
    {
        $from = array_merge($this->from, $from);
        //实例化PHPMailer核心类
        $mail = new PHPMailer();
        //是否启用smtp的debug进行调试 开发环境建议开启 生产环境注释掉即可 默认关闭debug调试模式
        $mail->SMTPDebug = $SMTPDebug;
        //使用smtp鉴权方式发送邮件
        $mail->isSMTP();
        //smtp需要鉴权 这个必须是true
        $mail->SMTPAuth = true;
        //链接qq域名邮箱的服务器地址
        $mail->Host = $from['Host'];//'imap.exmail.qq.com';
        //设置使用ssl加密方式登录鉴权
        $mail->SMTPSecure = 'ssl';
        //设置ssl连接smtp服务器的远程服务器端口号，以前的默认是25，但是现在新的好像已经不可用了 可选465或587
        $mail->Port = $from['Port'];// 465;
        //设置smtp的helo消息头 这个可有可无 内容任意
        $mail->Helo = $from['Helo'];//'Hello smtp.qq.com Server';
        //设置发件人的主机域 可有可无 默认为localhost 内容任意，建议使用你的域名
        $mail->Hostname = $_SERVER['HTTP_HOST'];// 'localhost';
        //设置发送的邮件的编码 可选GB2312 我喜欢utf-8 据说utf8在某些客户端收信下会乱码
        $mail->CharSet = 'UTF-8';
        //设置发件人姓名（昵称） 任意内容，显示在收件人邮件的发件人邮箱地址前的发件人姓名
        $mail->FromName = $from['FromName'];// '天下第一帅';
        //smtp登录的账号 这里填入字符串格式的qq号即可
        $mail->Username = $from['Username'];//'aa';
        //smtp登录的密码 使用生成的授权码 你的最新的授权码
        $mail->Password = $from['Password'];// '密码';
        //设置发件人邮箱地址 这里填入上述提到的“发件人邮箱”
        $mail->From = $from['From'];// 'aa@qq.com';
        //邮件正文是否为html编码 注意此处是一个方法 不再是属性 true或false
        $mail->isHTML(true);
        //设置收件人邮箱地址 该方法有两个参数 第一个参数为收件人邮箱地址 第二参数为给该地址设置的昵称 不同的邮箱系统会自动进行处理变动 这里第二个参数的意义不大
        $mail->addAddress($to, $to);
        //添加多个收件人 则多次调用方法即可
        // $mail->addAddress('xxx@qq.com','lsgo在线通知');
        //添加该邮件的主题
        $mail->Subject = $title;
        //添加邮件正文 上方将isHTML设置成了true，则可以是完整的html字符串 如：使用file_get_contents函数读取本地的html文件
        $mail->Body = $content;

        //为该邮件添加附件 该方法也有两个参数 第一个参数为附件存放的目录（相对目录、或绝对目录均可） 第二参数为在邮件附件中该附件的名称
        // $mail->addAttachment('./d.jpg','mm.jpg');
        //同样该方法可以多次调用 上传多个附件
        // $mail->addAttachment('./Jlib-1.1.0.js','Jlib.js');

        $status = $mail->send();
        //简单的判断与提示信息
        if ($status) {
            return true;
        } else {
            return false;
        }
    }

}

class ClientRedis
{
    /*
     // 1.Connection
        $redis->ping(); //检查是否还再链接,[+pong]
        $redis->ttl('key');//查看失效时间[-1 | timestamps]
        $redis->persist('key');//移除失效时间[ 1 | 0]
        $redis->sort('key',[$array]);//返回或保存给定列表、集合、有序集合key中经过排序的元素，$array为参数limit等！【配合$array很强大】 [array|false]
    // 2.共性的运算归类
        $redis->expire('key',10);//设置失效时间[true | false]
        $redis->move('key',15);//把当前库中的key移动到15库中[0|1]
        //string
        $redis->strlen('key');//获取当前key的长度
        $redis->append('key','string');//把string追加到key现有的value中[追加后的个数]
        $redis->incr('key');//自增1，如不存在key,赋值为1(只对整数有效,存储以10进制64位，redis中为str)[new_num | false]
        $redis->incrby('key',$num);//自增$num,不存在为赋值,值需为整数[new_num | false]
        $redis->decr('key');//自减1，[new_num | false]
        $redis->decrby('key',$num);//自减$num，[ new_num | false]
        $redis->setex('key',10,'value');//key=value，有效期为10秒[true]
        //list
        $redis->llen('key');//返回列表key的长度,不存在key返回0， [ len | 0]
        //set
        $redis->scard('key');//返回集合key的基数(集合中元素的数量)。[num | 0]
        $redis->sMove('key1', 'key2', 'member');//移动，将member元素从key1集合移动到key2集合。[1 | 0]
        //Zset
        $redis->zcard('key');//返回集合key的基数(集合中元素的数量)。[num | 0]
        $redis->zcount('key',0,-1);//返回有序集key中，score值在min和max之间(默认包括score值等于min或max)的成员。[num | 0]
        //hash
        $redis->hexists('key','field');//查看hash中是否存在field,[1 | 0]
        $redis->hincrby('key','field',$int_num);//为哈希表key中的域field的值加上量(+|-)num,[new_num | false]
        $redis->hlen('key');//返回哈希表key中域的数量。[ num | 0]

    // 3.Server
        $redis->dbSize();//返回当前库中的key的个数
        $redis->flushAll();//清空整个redis[总true]
        $redis->flushDB();//清空当前redis库[总true]
        $redis->save();//同步??把数据存储到磁盘-dump.rdb[true]
        $redis->bgsave();//异步？？把数据存储到磁盘-dump.rdb[true]
        $redis->info();//查询当前redis的状态 [verson:2.4.5....]
        $redis->lastSave();//上次存储时间key的时间[timestamp]
        $redis->watch('key','keyn');//监视一个(或多个) key ，如果在事务执行之前这个(或这些) key 被其他命令所改动，那么事务将被打断 [true]
        $redis->unwatch('key','keyn');//取消监视一个(或多个) key [true]
        $redis->multi(Redis::MULTI);//开启事务，事务块内的多条命令会按照先后顺序被放进一个队列当中，最后由 EXEC 命令在一个原子时间内执行。
        $redis->multi(Redis::PIPELINE);//开启管道，事务块内的多条命令会按照先后顺序被放进一个队列当中，最后由 EXEC 命令在一个原子时间内执行。
        $redis->exec();//执行所有事务块内的命令，；【事务块内所有命令的返回值，按命令执行的先后顺序排列，当操作被打断时，返回空值 false】

    // 4.String，键值对，创建更新同操作
        $redis->setOption(Redis::OPT_PREFIX,'hf_');//设置表前缀为hf_
        $redis->set('key',1);//设置key=aa value=1 [true]
        $redis->mset($arr);//设置一个或多个键值[true]
        $redis->setnx('key','value');//key=value,key存在返回false[|true]
        $redis->get('key');//获取key [value]
        $redis->mget($arr);//(string|arr),返回所查询键的值
        $redis->del($key_arr);//(string|arr)删除key，支持数组批量删除【返回删除个数】
        $redis->delete($key_str,$key2,$key3);//删除keys,[del_num]
        $redis->getset('old_key','new_value');//先获得key的值，然后重新赋值,[old_value | false]

    // 5.List栈的结构,注意表头表尾,创建更新分开操作
        $redis->lpush('key','value');//增，只能将一个值value插入到列表key的表头，不存在就创建 [列表的长度 |false]
        $redis->rpush('key','value');//增，只能将一个值value插入到列表key的表尾 [列表的长度 |false]
        $redis->lInsert('key', Redis::AFTER, 'value', 'new_value');//增，将值value插入到列表key当中，位于值value之前或之后。[new_len | false]
        $redis->lpushx('key','value');//增，只能将一个值value插入到列表key的表头，不存在不创建 [列表的长度 |false]
        $redis->rpushx('key','value');//增，只能将一个值value插入到列表key的表尾，不存在不创建 [列表的长度 |false]
        $redis->lpop('key');//删，移除并返回列表key的头元素,[被删元素 | false]
        $redis->rpop('key');//删，移除并返回列表key的尾元素,[被删元素 | false]
        $redis->lrem('key','value',0);//删，根据参数count的值，移除列表中与参数value相等的元素count=(0|-n表头向尾|+n表尾向头移除n个value) [被移除的数量 | 0]
        $redis->ltrim('key',start,end);//删，列表修剪，保留(start,end)之间的值 [true|false]
        $redis->lset('key',index,'new_v');//改，从表头数，将列表key下标为第index的元素的值为new_v, [true | false]
        $redis->lindex('key',index);//查，返回列表key中，下标为index的元素[value|false]
        $redis->lrange('key',0,-1);//查，(start,stop|0,-1)返回列表key中指定区间内的元素，区间以偏移量start和stop指定。[array|false]
    // 6.Set，没有重复的member，创建更新同操作
        $redis->sadd('key','value1','value2','valuen');//增，改，将一个或多个member元素加入到集合key当中，已经存在于集合的member元素将被忽略。[insert_num]
        $redis->srem('key','value1','value2','valuen');//删，移除集合key中的一个或多个member元素，不存在的member元素会被忽略 [del_num | false]
        $redis->smembers('key');//查，返回集合key中的所有成员 [array | '']
        $redis->sismember('key','member');//判断member元素是否是集合key的成员 [1 | 0]
        $redis->spop('key');//删，移除并返回集合中的一个随机元素 [member | false]
        $redis->srandmember('key');//查，返回集合中的一个随机元素 [member | false]
        $redis->sinter('key1','key2','keyn');//查，返回所有给定集合的交集 [array | false]
        $redis->sunion('key1','key2','keyn');//查，返回所有给定集合的并集 [array | false]
        $redis->sdiff('key1','key2','keyn');//查，返回所有给定集合的差集 [array | false]

    // 7.Zset，没有重复的member，有排序顺序,创建更新同操作* /
        $redis->zAdd('key',$score1,$member1,$scoreN,$memberN);//增，改，将一个或多个member元素及其score值加入到有序集key当中。[num | 0]
        $redis->zrem('key','member1','membern');//删，移除有序集key中的一个或多个成员，不存在的成员将被忽略。[del_num | 0]
        $redis->zscore('key','member');//查,通过值反拿权 [num | null]
        $redis->zrange('key',$start,$stop);//查，通过(score从小到大)【排序名次范围】拿member值，返回有序集key中，【指定区间内】的成员 [array | null]
        $redis->zrevrange('key',$start,$stop);//查，通过(score从大到小)【排序名次范围】拿member值，返回有序集key中，【指定区间内】的成员 [array | null]
        $redis->zrangebyscore('key',$min,$max[,$config]);//查，通过scroe权范围拿member值，返回有序集key中，指定区间内的(从小到大排)成员[array | null]
        $redis->zrevrangebyscore('key',$max,$min[,$config]);//查，通过scroe权范围拿member值，返回有序集key中，指定区间内的(从大到小排)成员[array | null]
        $redis->zrank('key','member');//查，通过member值查(score从小到大)排名结果中的【member排序名次】[order | null]
        $redis->zrevrank('key','member');//查，通过member值查(score从大到小)排名结果中的【member排序名次】[order | null]
        $redis->ZINTERSTORE();//交集
        $redis->ZUNIONSTORE();//差集
    // 8.Hash，表结构，创建更新同操作
        $redis->hset('key','field','value');//增，改，将哈希表key中的域field的值设为value,不存在创建,存在就覆盖【1 | 0】
        $redis->hget('key','field');//查，取值【value|false】
        $arr = array('one'=>1,2,3);$arr2 = array('one',0,1);
        $redis->hmset('key',$arr);//增，改，设置多值$arr为(索引|关联)数组,$arr[key]=field, [ true ]
        $redis->hmget('key',$arr2);//查，获取指定下标的field，[$arr | false]
        $redis->hgetall('key');//查，返回哈希表key中的所有域和值。[当key不存在时，返回一个空表]
        $redis->hkeys('key');//查，返回哈希表key中的所有域。[当key不存在时，返回一个空表]
        $redis->hvals('key');//查，返回哈希表key中的所有值。[当key不存在时，返回一个空表]
        $redis->hdel('key',$arr2);//删，删除指定下标的field,不存在的域将被忽略,[num | false]
     */
    public $config;
    private $client;

    public function __construct()
    {
    }

    // 配置信息
    public function config($config)
    {
        if (!$config['port']) {
            $config['port'] = 6379;
        }
        $redis = new \Redis();
        if (!$config['short']) { //默认使用短链接
            $redis->connect($config['host'], $config['port'], 1);//短链接，本地host，端口为6379，超过1秒放弃链接
        } else {
            $redis->pconnect($config['host'], $config['port'], 1);//长链接，本地host，端口为6379，超过1秒放弃链接
        }
        //  $redis->open('127.0.0.1',6379,1);//短链接(同上)
        // $redis->popen($config['host'],$config['port'],1);//长链接(同上)
        if ($config['password']) {
            $redis->auth($config['password']);//登录验证密码，返回【true | false】
        }
        $redis->select((int)$config['select']);//选择redis库,0~15 共16个库
        $this->client = $redis;
        return $this->client;
    }
}

class JWTCookie
{
    public $token_key = 'user_token';

    //写数据
    public function JwtSet($key, $data, $expire = 3600)
    {
        $value = is_array($data) ? json_encode($data) : $data;
        $status = setcookie($key, encode($value), time() + $expire, '/');
        if ($data) {
            return ['code' => '000000', 'msg' => '成功', 'status' => $status];
        } else {
            return ['code' => '120009', 'msg' => 'error', 'status' => $status];
        }
    }

    //取数据
    public function JwtGet($key)
    {
        $data = cookie($key);
        $data = decode($data);
        if (!$data) {
            return ['code' => '120008', 'msg' => 'error'];
        }
        $json = json_decode($data, true);
        $rel = ['code' => '000000', 'msg' => '成功', 'data' => is_array($json) ? $json : $data];
        return $rel;
    }

    /** 登录 鉴权
     * @param array $data
     * @param int $expire
     * @return array
     */
    public function JwtAuth($data, $expire = 3600)
    {
        return self::JwtSet($this->token_key, $data, $expire);
    }

    //jwt 取数据
    public function JwtData()
    {
        return self::JwtGet($this->token_key);
    }

    //定时刷新
    public function JwtCheck($expire = 3600): array
    {
        $data = $this->JwtData();

        if (!$data || $data['code'] != '000000') {
            return $data;
        }
        if (!$data['data']['id']) {
            return ['code' => '990021', 'msg' => '失败'];
        }
        return  $this->JwtAuth($data['data'],$expire);

    }

    //jwt 取数据
    public function JwtRefresh()
    {
        $status = setcookie($this->token_key, null, time(), '/');
        $rel = ['code' => '000000', 'msg' => '成功', 'status' => $status];
        return $rel;
    }
}

UpGrade::Init([]);
/*

//解压文件
if ($_GET['z'] == 'un') {
    $zip = UpGrade::$zip;
    $s=$zip->name('./'.$_GET['zip'])->dec('./');
    var_dump($s);
}
//压缩 根目录文件
if ($_GET['z'] == 'set') {
    $list = UpGrade::dirloop('./');
    if (count($list) > 0) {
        $size = 0;
        $zip = UpGrade::$zip;
        $zipName = 'code';
        $i = 0;
        $fileList = [];
     //   echo '<pre>';
     //   var_dump(count($list));
        foreach ($list as $v) {
            $s=filesize($v);
            $size += ($s<2000?2000:$s);

           // if($i==4){
           //     echo '<pre>';echo PHP_EOL.'FILE----'.PHP_EOL;
           //     echo $v.'--'.filesize($v).'---'.$size;

                echo PHP_EOL.'----'.PHP_EOL;
            }

            if (ceil(($size) / 1000 / 10) / 100 >= 5) {
                $s = $zip->name('./zip/' . $zipName . '_' . $i . '.zip')->nar($fileList);
           //     var_dump(count($fileList));

              //  echo '<pre>';echo PHP_EOL.'----'.$i.PHP_EOL;
             //   var_dump($size);
             //   var_dump($s);

                $size = 0;
               // $i++;
            //    echo '<pre>';print_r($fileList);
                $fileList = [];
            }
            $fileList[str_ireplace(['../','./'], '', $v)] = $v;
        }
        $s = $zip->name('./zip/' . $zipName . '_' . $i . '.zip')->nar($fileList);
    }

}
*/


/*
echo '<pre>';
//redis
$redis = UpGrade::$redis->config(['host' => '127.0.0.1']);
//$s=$redis->set('aa','bb');
$s = $redis->get('aa');
var_dump($s);
exit;
*/
//$list=[];
//遍历目录压缩
/*
$list = UpGrade::dirloop('../');
if (count($list) > 0) {
    $size = 0;
    $zip = UpGrade::$zip;
    $zipName = 'test';
    $i = 0;
    $fileList = [];
    foreach ($list as $v) {
        $size += (filesize($v));
        if (ceil($size / 1000 / 10) / 100 >= 5) {
            $s = $zip->name('./zip/' . $zipName . '_' . $i . '.zip')->nar($fileList);
            $size = 0;
            $i++;
            $fileList = [];
        }
        $fileList[str_ireplace('../', '', $v)] = $v;

    }
}
var_dump($list);
exit;
*/
/*
$curl=UpGrade::$curl;
//$curl->log=true; //开启日志记录
$content=$curl->Http('http://baidu.com/');

*/
/*
//ZIP压缩
$zip=UpGrade::$zip;
echo '<pre>';
$s=$zip->name('./t.zip')->nar(['./test.txt','./Public/xlsx/readme.md']);
var_dump($s);exit;
*/


/*
//文件读写
$file=UpGrade::$file;
$file->name('./test.txt')->write('ccccc');
$content=$file->name('./test.txt')->read();
//$file=UpGrade::Init(['client'=>'file','filePath'=>'./test.txt'])->read();
var_dump($content);exit;
*/
/*
$option=[];
$option['host']='69.197.166.35';
$option['port']='21';
$option['user']='f001.txmk.tk';
$option['password']='zheng1234';
$client=UpGrade::$ftp;
$client->client($option);
$mk=$client->mkdir('test');
var_dump($mk->status);
$mkdir=$client->mkdir('test2');
$client->close();
$mkdir3=$client->mkdir('test3');
echo '<pre>';var_dump($mkdir);
*/

/*
$option=[];
$option['host']='54.227.226.119';
$option['port']='20022';
$option['user']='root';
$option['password']='zheng1234';

$client=UpGrade::$sftp;
$client->client($option);
$mk=$client->mkdir('test');
var_dump($mk->status);
$mkdir=$client->mkdir('test2');
$client->close();
$mkdir3=$client->mkdir('test3');
echo '<pre>';var_dump($mkdir);

echo '<pre>';print_r($mkdir);
*/
