<?php
/**
 * Created by PhpStorm.
 * User: pan
 * Date: 19-3-4
 * Time: 上午9:27
 */

require_once __DIR__ . '/Errors.php';

class Article
{
    /**
     * 数据库操作对象
     * @var PDO
     */
    private $db;

    /**
     * Article constructor.
     * @param PDO $db
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * 文章发表
     * @param $title    标题
     * @param $content  内容
     * @param $user_id  发表用户的id
     * @return array    文章的信息
     * @throws Exception
     */
    public function create($title, $content, $user_id)
    {
        if (empty($title)) {
            throw new Exception("文章的内容不能为空", Errors::ARTICLE_TITLE_CANNOT_NULL);
        }
        if (empty($content)) {
            throw new Exception("文章内容不能为空", Errors::ARTICLE_CONTENT_CANNOT_NULL);
        }
        $sql = "insert into `article`(`title`,`content`,`user_id`,`create_time`) values(:title,:content,:user_id,:create_time)";
        //预处理
        $time = date("Y-m-d H:i:s");
        $sm = $this->db->prepare($sql);

        //绑定参数
        $sm->bindParam(':title', $title);
        $sm->bindParam(':content', $content);
        $sm->bindParam(':user_id', $user_id);
        $sm->bindParam(':create_time', $time);

        //执行语句
        if (!$sm->execute()) {
            throw new Exception("发表文章失败", Errors::ARTICLE_CREATE_FAIL);
        }

        //返回插入信息
        return [
            'title' => $title,
            'content' => $content,
            'article_id' => $this->db->lastInsertId(),
            'create_time' => $time,
            'user_id' => $user_id
        ];
    }

    /**
     * 查看文章
     * @param $article_id   文章id
     * @return mixed    文章信息
     * @throws Exception
     */
    public function view($article_id)
    {
        if (empty($article_id)) {
            throw new Exception("文章的id不能为空", Errors::ARTICLE_ID_CANNOT_NULL);
        }
        $sql = "select * from `article` where `id` = :id";
        //预处理
        $sm = $this->db->prepare($sql);
        $sm->bindParam(':id', $article_id);

        //执行语句
        if (!$sm->execute()) {
            throw new Exception("获取文章失败", Errors::ARTICLE_GET_FAIL);
        }
        $article = $sm->fetch(PDO::FETCH_ASSOC);

        //判断文章内容是不是存在
        if (empty($article)) {
            throw new Exception("文章不存在", Errors::ARTICLE_NOT_EXISTS);
        }

        return $article;
    }

    /**
     * 编辑文章
     * @param $article_id   int 文章id
     * @param $title    sring   文章标题
     * @param $content  string  文章内容
     * @param $user_id  int 用户的id
     * @return array|mixed
     * @throws Exception
     */
    public function edit($article_id, $title, $content, $user_id)
    {
        $article = $this->view($article_id);//获取文章信息

        /*echo '传入的信息:文档id:' . $article_id . ',标题：' . $title . ',内容：' . $content . '用户id:' . $user_id;
        echo '数据库中文章对应用户的id:' . $article['user_id'];
        exit();*/

        if ($user_id !== $article['user_id']) {
            throw new Exception("你无法操作此文章", Errors::PERMISSION_NOT_ALLOW);
        }
        $title = empty($title) ? $article['title'] : $title;//使用三元运算符过滤空标题
        $content = empty($content) ? $article['content'] : $content;//使用三元运算符过滤空内容
        //如果要修改的标题和内容是一样的，则不必要修改
        if ($title == $article['title'] && $content == $article['content']) {
            return $article;
        }

        //定义修改语句
        $sql = "update `article` set `title` = :title, `content` = :content where `id` = :id";
        //预处理
        $sm = $this->db->prepare($sql);
        //绑定参数
        $sm->bindParam(':title', $title);
        $sm->bindParam(':content', $content);
        $sm->bindParam(':id', $article_id);

        //执行语句
        if (!$sm->execute()) {
            throw new Exception("编辑文章失败", Errors::ARTICLE_EDIT_ERROR);
        }
        //执行成功之后返回文章具体内容
        return [
            'title' => $title,
            'content' => $content,
            'article_id' => $article_id,
            'user_id' => $user_id
        ];
    }

    public function delete($article_id, $user_id)
    {
        $article = $this->view($article_id);
        if ($user_id != $article['user_id']) {
            throw new Exception("你无权操作此文章", Errors::PERMISSION_NOT_ALLOW);
        }

        //定义修改语句
        $sql = "delete from `article` where `id` = :id and `user_id` = :user_id";
        //预处理
        $sm = $this->db->prepare($sql);
        //绑定参数
        $sm->bindParam(':id', $article_id);
        $sm->bindParam(':user_id', $user_id);

        //执行语句
        if (!$sm->execute()) {
            throw new Exception("编辑删除失败", Errors::ARTICLE_DELETE_FAIL);
        }
        //删除成功后，返回删除肚饿article信息
        return $article;
    }

    public function _list()
    {

    }


}