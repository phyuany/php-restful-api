<?php
/**
 * Created by PhpStorm.
 * User: pan
 * Date: 19-3-4
 * Time: 上午7:17
 */

//使用pdo创建连接对象
require_once __DIR__ . "/conf.php";
return new PDO("mysql:host=" . HOST . ";dbname=".DB_NAME, DB_USER, DB_PASS);