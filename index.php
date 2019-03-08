<?php
$db = require_once __DIR__ . "/lib/db.php";
require_once __DIR__ . "/class/User.php";
require_once __DIR__ . "/class/Article.php";
require_once __DIR__ . "/class/Rest.php";

//开启session
session_start();

$user = new User($db);
$art = new Article($db);
$api = new Rest($user, $art);

//启动api
$api->run();


/*
//$user->register('admin', 'admin');
//var_dump($user->login('admin', 'admin'));
//var_dump($article->create('biaoti', 'neirong', 5));
//var_dump($article->view(13));

var_dump($art->edit(13, '标题', '内容', 5));//*/

//var_dump($_SERVER);

