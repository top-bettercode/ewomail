### EwoMail开源邮件服务器软件  


EwoMail是基于Linux的开源邮件服务器软件，集成了众多优秀稳定的组件，是一个快速部署、简单高效、多语言、安全稳定的邮件解决方案，帮助你提升运维效率，降低 IT 成本，兼容主流的邮件客户端，同时支持电脑和手机邮件客户端。

### 集成组件


Postfix：邮件服务器

Dovecot：IMAP/POP3/邮件存储

Amavisd：反垃圾和反病毒

LAMP：apache2.2，mysql5.5，php5.4

EwoMail-Admin：WEB邮箱管理后台

Rainloop：webmail

### 安装环境

centos6系列，需要服务器的全新软环境。

最低配置要求

CPU：1核

内存：1G

硬盘：40G

### 检查swap

安装前需要swap缓存，请务必先检查swap是否已经启动。

### 手动安装

下载并重新命名为ewomail.zip


```
解压安装
unzip -o ewomail.zip
cd EwoMail/install
#需要输入一个邮箱域名，不需要前缀，列如下面的ewomail.cn
sh ./start.sh ewomail.cn
```

### 文档教程

在线安装、配置等等的更多详细教程请查看

[EwoMail在线文档](http://doc.ewomail.com/ewomail)

### EwoMail-Admin

EwoMail-Admin是一个邮箱管理后台，用PHP语言开发，开源免费。

自主原生开发，没有采用第三方框架，简单高效、易二次开发。

需要搭配EwoMail邮件服务器软件使用。

[EwoMail-Admin开发教程](http://doc.ewomail.com/ewomail-admin)


Email：services@ewomail.cn

官方群：458861632
