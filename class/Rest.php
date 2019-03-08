<?php
/**
 * Created by PhpStorm.
 * User: pan
 * Date: 19-3-5
 * Time: 下午6:00
 */

class Rest
{
    /**
     * User类的实例
     * @var
     */
    private $_user;

    /**
     * Article类的实例
     * @var
     */
    private $_article;

    /**
     * 请求方法
     * @var
     */
    private $_requestMethod;

    /**
     * 请求资源
     * @var
     */
    private $requestResource;

    /**
     * 允许请求的资源，分别为users和articles
     * @var array
     */
    private $_allowResource = ['users', 'articles'];

    /**
     * 允许被请求的方法
     * @var array
     */
    private $_allowMethod = ['GET', 'POST', 'PUT', 'DELETE'];

    /**
     * 版本号
     * @var
     */
    private $_version;

    /**
     * 资源标识
     * @var
     */
    private $_requestUri;

    /**
     * 常见的状态码
     * @var array
     */
    private $_statusCode = [
        200 => 'OK',
        204 => 'No Content',
        400 => 'Bad Request',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allow',
        500 => 'Server Internal Error'
    ];

    /**
     * Rest constructor.
     * @param $_user
     * @param $_article
     */
    public function __construct($_user, $_article)
    {
        $this->_user = $_user;
        $this->_article = $_article;
    }

    /**
     * api启动方法
     */
    public function run()
    {
        try {
            $this->setMethod();
            $this->setResource();

            //分发请求资源
            if ($this->requestResource == 'users') {
                //处理用户模块
                $this->sendUsers();
            } else {
                //处理文章模块

                $this->sendArticles();
            }
        } catch (Exception $exception) {
            $this->_json($exception->getMessage(), $exception->getCode());
        }

    }

    /**
     * 设置api请求方法
     * @throws Exception
     *
     */
    private function setMethod()
    {
        $this->_requestMethod = $_SERVER['REQUEST_METHOD'];//获取请求方法
        if (!in_array($this->_requestMethod, $this->_allowMethod)) {
            throw new Exception("请求方法不被允许", 405);
        }
    }

    /**
     * 数据输出
     * @param $message  string 提示信息
     * @param $code int 提示状态码
     */
    private function _json($message, $code)
    {
        if ($code != 200 && $code > 200) {
            header('HTTP/1.1 ' . $code . ' ' . $this->_statusCode[$code]);
        }
        header("Content-Type:application/json;charset:utf-8");

        if (!empty($message)) {
            echo json_encode(['message' => $message, 'code' => $code]);
        }
    }

    private function setResource()
    {
        /**
         * 实例URI:   http://api.jkdev.cn/1.0/user/1
         */

        $path = $_SERVER['PATH_INFO'];
        $params = explode('/', $path);

        /**
         * 获取请求资源,URI参数分解之后第二个下标对应的是请求资源
         * 上面例子的URI对应的资源为 user
         */
        $this->requestResource = $params[2];

        if (!in_array($this->requestResource, $this->_allowResource)) {
            throw new Exception('请求资源不被允许', 405);
        }

        /**
         * 获取URI中api版本
         * 示例中版本号为 1.0
         */
        $this->_version = $params[1];

        /**
         * 如果URI存在，则获取URI
         * 示例中URI为 1
         */
        if (!empty($params[3])) {
            $this->_requestUri = $params[3];
        }
    }

    /**
     * 处理用户逻辑
     */
    private function sendUsers()
    {
        /**
         * 用户资源中，请求方法只有注册和登录，请求方式必须是POST
         */
        if ($this->_requestMethod != "POST") {
            throw new Exception('请求方法不被允许', 405);
        }
        /**
         * 如果请求参数缺失，则抛出异常
         */
        if (empty($this->_requestUri)) {
            throw new Exception('请求参数缺失', 400);
        }

        if ($this->_requestUri == 'login') {
            $this->doLogin();
        } elseif ($this->_requestUri == 'register') {
            $this->doRegister();
        } else {
            throw new Exception('请求资源不被允许', 405);
        }

    }

    /**
     * 处理文章逻辑
     */
    private function sendArticles()
    {
        //判断请求方法是否被允许
        if (!in_array($this->_requestMethod, $this->_allowMethod)) {
            throw new Exception('请求方法不允许', 405);
        }
        //判断用户请求方式，确定用户执行的逻辑，即 增、删、改、查
        if ($this->_requestMethod == 'POST') {
            //发表文章
            $this->articleCreate();
        } elseif ($this->_requestMethod == 'GET') {
            //查看文章
            $this->articleView();
        } elseif ($this->_requestMethod == 'PUT') {
            //文章修改
            $this->articleEdit();
        } else {
            //文章删除
            $this->articleDelete();
        }
    }

    /**
     * 用户登录
     * @throws Exception
     */
    private function doLogin()
    {
        //获取用户请求体，即JSON数据
        $data = $this->getBody();
        if (empty($data['name'])) {
            throw new Exception('用户名不能为空', 400);
        }
        if (empty($data['password'])) {
            throw new Exception('用户密码不能为空', 400);
        }
        $user = $this->_user->login($data['name'], $data['password']);
        $data = [
            'data' => [
                'user_id' => $user['id'],
                'name' => $user['name'],
                'token' => session_id()
            ],
            'message' => '登录成功',
            'code' => 200
        ];

        //用SESSION保存登录用户的ID
        $_SESSION['userInfo']['id'] = $user['id'];

        echo json_encode($data);
    }

    /**
     * 用户注册接口
     * @throws Exception
     */
    private function doRegister()
    {
        //获取用户请求体，即JSON数据
        $data = $this->getBody();
        if (empty($data['name'])) {
            throw new Exception('用户名不能为空', 400);
        }
        if (empty($data['password'])) {
            throw new Exception('用户密码不能为空', 400);
        }
        $user = $this->_user->register($data['name'], $data['password']);
        if ($user) {
            $this->_json('注册成功', 200);
        }
    }

    private function getBody()
    {
        $data = file_get_contents("php://input");
        if (empty($data)) {
            throw new Exception('请求参数错误', 400);
        }
        //解析参数
        return json_decode($data, true);//解析为一个数组，否则解析为一个对象
    }

    /**
     * 判断用户是否登录
     * @param $token
     */
    private function isLogin($token)
    {
        $sessionId = session_id();
        if ($sessionId != $token) {
            return false;
        }

        return true;
    }

    /**
     * 发表文章
     * @throws Exception
     */
    private function articleCreate()
    {
        $data = $this->getBody();
        //判断用户是否已经登录
        if (!$this->isLogin($data['token'])) {
            throw new Exception('请重新登录', 403);
        }

        //判断文章标题和内容是否为空
        if (empty($data['title'])) {
            throw new Exception('文章标题不能为空', 400);
        }
        if (empty($data['content'])) {
            throw new Exception('文章的内容不能为空', 400);
        }

        $user_id = $_SESSION['userInfo']['id'];
        $return = $this->_article->create($data['title'], $data['content'], $user_id);
        if (!empty($return)) {
            $this->_json('发表成功', 200);
        } else {
            //文章发表失败
            $this->_json('发表失败', 500);
        }
    }

    /**
     * 查看文章接口
     * @throws Exception
     */
    private function articleView()
    {
        //获取请求头信息中用户携带的token
        $token = $_SERVER['HTTP_TOKEN'];

        //判断用户是否已经登录
        if (!$this->isLogin($token)) {
            throw new Exception('请重新登录', 403);
        }
        $article = $this->_article->view($this->_requestUri);
        if ($article['user_id'] != $_SESSION['userInfo']['id']) {
            //文章仅限创建用户查看
            throw new Exception('您无权查看此文章', 403);
        }
        //组装数据并返回
        $data = [
            "data" => [
                "title" => $article['title'],
                "content" => $article['content'],
                "user_id" => $article['user_id'],
                "create_time" => $article['create_time']
            ],
            "message" => "获取成功",
            "code" => 200
        ];
        //返回json数据
        echo json_encode($data);
    }

    /**
     * 文章修改的API
     */
    private function articleEdit()
    {
        $data = $this->getBody();//获取原始输入流
        if (!$this->isLogin($data['token'])) {
            throw new Exception('请重新登录', 403);
        }
        $article = $this->_article->view($this->_requestUri);
        if ($article['user_id'] != $_SESSION['userInfo']['id']) {
            //文章仅限创建用户修改
            throw new Exception('您无权修改此文章', 403);
        }
        $return = $this->_article->edit($this->_requestUri, $data['title'], $data['content'], $_SESSION['userInfo']['id']);
        //修改成功，返回文章修改之后的数据
        if ($return) {
            $data = [
                'data' => [
                    'title' => $data['title'],
                    'content' => $data['content'],
                    'user_id' => $article['user_id'],
                    'create_time' => $article['create_time']
                ],
                'message' => '修改成功',
                'code' => 200
            ];
            echo json_encode($data);
        } else {
            //如果没有修改成功也要进行返回,返回内容是文章原始数据
            $data = [
                'data' => [
                    'title' => $article['title'],
                    'content' => $article['content'],
                    'user_id' => $article['user_id'],
                    'create_time' => $article['create_time']
                ],
                'message' => '文章修改失败',
                'code' => 500
            ];
            echo json_encode($data);
        }
    }

    private function articleDelete()
    {
        $data = $this->getBody();//获取原始输入流
        if (!$this->isLogin($data['token'])) {
            throw new Exception('请重新登录', 403);
        }
        $article = $this->_article->view($this->_requestUri);
        if ($article['user_id'] != $_SESSION['userInfo']['id']) {
            //文章仅限创建用户删除
            throw new Exception('您无权删除此文章', 403);
        }

        $return = $this->_article->delete($this->_requestUri, $_SESSION['userInfo']['id']);

        if ($return) {
            $this->_json('删除成功', 200);
        } else {
            $this->_json('删除失败', 500);
        }

    }
}