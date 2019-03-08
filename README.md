## PHPRestfulDemo

### 一、简介

使用PHP编写的Restful API示例

1. 代码基于千锋PHP视频教程：Restful接口开发，有的地方稍微作修改，视频链接[https://www.bilibili.com/video/av14886283](https://www.bilibili.com/video/av14886283)
2. 小幺鸡API文档地址：[http://www.xiaoyaoji.cn/doc/1iBz1Kw0xI](http://www.xiaoyaoji.cn/doc/1iBz1Kw0xI)

### 二、安装说明

1. **导入数据库**

   在mysql数据库中新建名为“rest”的数据库，导入程序目录下的rest.sql文件即可

2. **修改数据库配置**

   修改文件：/lib/config.php

   ```
   //数据库相关
   define("HOST", "db");
   define("DB_NAME", "rest");
   define("DB_USER", "root");
   define("DB_PASS", "root");
   
   //加密相关
   define("SALT", "api");
   ```

​      数据库用户和密码改成你自己的就好了

3. **配置虚拟主机：**

   Apache设置一个虚拟域名，并在你电脑系统的hosts文件设置解析到你本地。目的是通过域名顺利访问到你的项目根目录。我设置的域名为 ***api.jkdev.cn***，当然，你要设置自己的

4. **环境：**

   在我电脑上成功测试的环境是：Apache 2.4.33 + MySQL 8.0.11 + PHP 7.2，最好和此环境相近

