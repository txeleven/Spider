<?php
declare (strict_types=1);

namespace app;

use extend\TxRedis;
use http\Client\Response;
use liliuwei\think\Jump;
use thans\jwt\facade\JWTAuth;
use think\App;
use think\exception\ValidateException;
use think\facade\Event;
use think\facade\View;
use think\Validate;

/**
 * 控制器基础类
 */
abstract class BaseController
{
    protected $view;
    /**
     * Request实例
     * @var \think\Request
     */
    protected $request;

    /**
     * 应用实例
     * @var \think\App
     */
    protected $app;

    /**
     * 是否批量验证
     * @var bool
     */
    protected $batchValidate = false;

    /**
     * 控制器中间件
     * @var array
     */
    protected $middleware = [];
    /**
     * @var string
     */
    public $is_https;

    /**
     * 构造方法
     * @access public
     * @param App $app 应用对象
     */
    public function __construct(App $app)
    {

        $this->app = $app;
        $this->request = $this->app->request;
        //Response
        $this->view = new View();




        // 控制器初始化
        $this->_initialize();

        //引入自定义类


    }

    // 初始化
    protected function _initialize()
    {

        require_once app()->getBasePath() . '../extend/Tx.php';

        $is_https='http';
        if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
            $is_https= 'https';
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            $is_https= 'https';
        } elseif (!empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off') {
            $is_https= 'https';
        }
        $this->is_https=$is_https;
        $this->assign(['is_https'=>$this->is_https]);


    }

    /**
     * 验证数据
     * @access protected
     * @param array $data 数据
     * @param string|array $validate 验证器名或者验证规则数组
     * @param array $message 提示信息
     * @param bool $batch 是否批量验证
     * @return array|string|true
     * @throws ValidateException
     */
    protected function validate(array $data, $validate, array $message = [], bool $batch = false)
    {
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                [$validate, $scene] = explode('.', $validate);
            }
            $class = false !== strpos($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);
            $v = new $class();
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        $v->message($message);

        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }

        return $v->failException(true)->check($data);
    }

    use \liliuwei\think\Jump;

    /**
     * 加载模板输出
     * @access protected
     * @param string $template 模板文件名
     * @param array $vars 模板输出变量
     * @param array $replace 模板替换
     * @param array $config 模板参数
     * @return mixed
     */

    protected function assign($name, $value = '')
    {

        return View::assign($name, $value);
        //  $this->view->assign($name, $value);
    }

    protected function fetch(string $template = '', array $vars = [])
    {

        return View::fetch($template, $vars);
        //   return $this->view->fetch($template, $vars, $replace, $config);
    }

    protected function error(string $content, string $url = '/')
    {
        $this->assign(['wait' => 5]);
        echo View::fetch(app()->getBasePath() . 'tpl/error.html', ['content' => $content, 'url' => $url]);
        exit;

    }

    protected function success(string $content, string $url = '/')
    {
        return View::fetch(app()->getBasePath() . 'tpl/success.html', ['content' => $content, 'url' =>$url]);
    }

    protected function redirect(string $url)
    {
        return redirect($url);
        //  Jump::
    }
}
