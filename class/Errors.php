<?php
/**
 * Created by PhpStorm.
 * User: pan
 * Date: 19-3-4
 * Time: 上午7:25
 */

/*
 * 定义错误代码
 */

class Errors
{
    /*
     * 用户模块
     */
    const USERNAME_CANTNOT_NULL = 001;//用户名不能为空
    const USERPASS_CANTNOT_NULL = 002;//用户密码不能为空
    const USERNAME_EXISTS = 003;//用户名已经存在
    const REGISTER_FAIL = 004;//注册失败
    const LOGIN_FAIL = 005;//登录失败
    const USERNAME_OR_PASSWORD_ERROR = 006;//用户名或者密码错误


    /**
     * 文章模块
     */
    const ARTICLE_TITLE_CANNOT_NULL = 101;//文章标题不能为空
    const ARTICLE_CONTENT_CANNOT_NULL = 102;//文章内容不能为空
    const ARTICLE_CREATE_FAIL = 103;//发表文章失败
    const ARTICLE_ID_CANNOT_NULL = 104;//文章的id不能为空
    const ARTICLE_GET_FAIL = 105;//文章获取失败
    const ARTICLE_NOT_EXISTS = 106;//文章不存在
    const PERMISSION_NOT_ALLOW = 107;//文章不存在
    const ARTICLE_EDIT_ERROR = 108;//文章编辑失败
    const ARTICLE_DELETE_FAIL = 109;//文章删除失败
}