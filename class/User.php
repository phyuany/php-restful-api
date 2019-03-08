<?php
/**
 * Created by PhpStorm.
 * User: pan
 * Date: 19-3-4
 * Time: 上午7:22
 */

require_once __DIR__ . '/Errors.php';

class User
{
    /*
     * 数据库连接对象
     * @var PDO
     */
    private $_db;

    /**
     * User constructor.
     * @param $_db
     */
    public function __construct($_db)
    {
        $this->_db = $_db;
    }

    /**
     * 用户注册方法
     * @param $username string 用户名
     * @param $password string 用户注册密码
     * @throws Exception
     */
    public function register($username, $password)
    {
        //判断用户名是否为空
        if (empty($username)) {
            throw new Exception("用户名不能为空", Errors::USERNAME_CANTNOT_NULL);
        }
        //判断用户密码是否为空
        if (empty($password)) {
            throw new Exception("用户密码不能为空", Errors::USERPASS_CANTNOT_NULL);
        }
        //判断用户名是否已经存在
        if ($this->_isUsernameExists($username)) {
            throw new Exception("用户名已存在", Errors::USERNAME_EXISTS);
        }

        $sql = "insert into `user`(`name`,`password`,`create_time`) values(:username,:password,:addtime)";
        //预变量的定义
        $addtime = date("Y-m-d H:i:s", time());
        //预处理
        $sm = $this->_db->prepare($sql);
        //密码进行md5加密
        $password = $this->_md5($password);
        //绑定参数
        $sm->bindParam(":username", $username);
        $sm->bindParam(":password", $password);
        $sm->bindParam(":addtime", $addtime);

        //执行sql
        if (!$sm->execute()) {
            throw new Exception("注册失败", Errors::USERNAME_EXISTS);
        }
        return [
            'username' => $username,
            'user_id' => $this->_db->lastInsertId(),
            'addtime' => $addtime
        ];
    }

    /**
     * 用户登录功能
     * @param $username 用户名
     * @param $password 用户密码
     * @return mixed 返回用户登录信息
     * @throws Exception 异常：抛出用户名或者密码错误
     */
    public function login($username, $password)
    {
        //判断用户名是否为空
        if (empty($username)) {
            throw new Exception("用户名不能为空", Errors::USERNAME_CANTNOT_NULL);
        }
        //判断用户密码是否为空
        if (empty($password)) {
            throw new Exception("用户密码不能为空", Errors::USERPASS_CANTNOT_NULL);
        }
        $sql = "select * from `user` where `name` = :username and `password` = :password";
        //md5加密
        $password = $this->_md5($password);
        //进行预处理
        $sm = $this->_db->prepare($sql);
        //绑定参数
        $sm->bindParam(":username", $username);
        $sm->bindParam(":password", $password);
        //执行sql
        if (!$sm->execute()) {
            throw new Exception("登录失败", Errors::LOGIN_FAIL);
        }
        $re = $sm->fetch(PDO::FETCH_ASSOC);//使用关联数组的形式进行返回
        //判断是否登录成功
        if (!$re) {
            throw new Exception("用户名或者密码错误", Errors::USERNAME_OR_PASSWORD_ERROR);
        }
        return $re;
    }

    /**
     * 判断用户名是否已经存在
     * @param $username
     * @return bool
     */
    private function _isUsernameExists($username)
    {
        $sql = "select * from `user` where `name` = :username";
        //预处理
        $sm = $this->_db->prepare($sql);
        $sm->bindParam(":username", $username);

        //执行语句
        $sm->execute();
        $result = $sm->fetch(PDO::FETCH_ASSOC);//使用关联数组进行返回
        return !empty($result);
    }

    /**
     * 自定义加盐md5
     * @param $password 用户未加密密码
     * @return string 返回加密密码
     */
    private function _md5($password)
    {
        return md5($password . SALT);
    }

}