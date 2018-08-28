#!/ewomail/php54/bin/php
<?php
// +----------------------------------------------------------------------
// | EwoMail
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://ewomail.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://ewomail.com/license.html)
// +----------------------------------------------------------------------
// | Author: Jun <gyxuehu@163.com>
// +----------------------------------------------------------------------

class init_sql{
    
    public $db;
    
    public $domain = 'ewomail.cn';
    
    //数据库名字
    public $mail_db = 'ewomail';
    //数据库账号
    public $mail_db_username = 'ewomail';
    
    public $root_pwd;
    public $mail_pwd;
    
    public function __construct($domain,$root_pwd,$mail_pwd){
        
        if(!$domain){
            die("Missing domain parameter");
        }

        $this->domain = $domain;
        $this->db = new mysqli('127.0.0.1','root','','mysql');
        if ($this->db->connect_error) {
            die('Connect Error('.$this->db->connect_errno.')'.$this->db->connect_error);
        }
        if (!$this->db->set_charset("utf8")) {
            die("Error loading character set utf8: ".$this->db->error);
        }
        
        $this->import_sql();
        $this->update_mail_config();
        $this->update_password($root_pwd,$mail_pwd);
    }
    
    /**
     * 更新数据库密码
     * */
    public function update_password($root_pwd,$mail_pwd)
    {
        $this->db->select_db('mysql');
        $this->root_pwd = $root_pwd;
        $this->mail_pwd = $mail_pwd;
        
        $this->db->query("GRANT all privileges on *.* TO '{$this->mail_db_username}'@'localhost' IDENTIFIED BY '$mail_pwd'");
        $this->db->query("GRANT all privileges on *.* TO '{$this->mail_db_username}'@'127.0.0.1' IDENTIFIED BY '$mail_pwd'");
        
        $this->db->query("UPDATE user SET password=PASSWORD('$root_pwd') WHERE user='root'");
        $this->db->query("FLUSH PRIVILEGES");
        
    }
    
    
    /**
     * 导入备份
     * */
    public function import_sql()
    {
        $sql_file = '/ewomail/www/ewomail-admin/upload/install.sql';
        $file = fopen($sql_file,"r");
        if(!$file){
            die("Data file read failed");
        }
        $sqlArr = [];
        $sql = '';
        $t = false;
        while (!feof($file)) {
            $line = fgets($file);
            if (trim($line) == '') {  
                continue;
            }
            
            if(preg_match('/^DROP TABLE IF EXISTS.+;/i',$line)){
                $sqlArr[] = $line;
            }
            
            if(preg_match('/^CREATE TABLE.+/i',$line)){
                $t = true;
            }
            if($t){
                $sql .= $line;
                if(preg_match('/ENGINE.+;/i',$line)){
                    $sqlArr[] = $sql;
                    $sql = '';
                    $t = false;
                }
            }
            
            if(preg_match('/^INSERT.+;/i',$line)){
                $sqlArr[] = $line;
            }
            
        }
        
        $r = $this->db->query("CREATE DATABASE IF NOT EXISTS ".$this->mail_db." DEFAULT CHARSET utf8 COLLATE utf8_general_ci");
        if(!$r){
            die('Database creation failed');
        }
        
        if(!$this->db->select_db($this->mail_db)){
            die('Database switch failed');
        }
        
        foreach($sqlArr as $v){
            if(!$this->db->query($v)){
                echo $v."\n";
                echo $this->db->error;
                exit;
            }
        }
        
        @unlink($sql_file);
    }
    
    /**
     * 修改数据里的mail配置
     * */
    public function update_mail_config()
    {
        //修改相关配置数据
        $imap = 'imap.'.$this->domain;
        $smtp = 'smtp.'.$this->domain;
        $mydomain = $this->domain;
        $myhostname = 'mail.'.$this->domain;
        $this->db->query("update i_mail_config set value='$imap' where name='imap'");
        $this->db->query("update i_mail_config set value='$smtp' where name='smtp'");
        $this->db->query("update i_mail_config set value='$mydomain' where name='mydomain'");
        $this->db->query("update i_mail_config set value='$myhostname' where name='myhostname'");
        $this->db->query("INSERT INTO i_domains (name,active,ctime) VALUES('$mydomain',1,NOW())");
        $this->db->query("create database if not exists rainloop");
        $this->db->query("create user rainloop@localhost identified by 'rainloop'");
        $this->db->query("grant all privileges on rainloop.* to rainloop@localhost");
        $this->db->query("flush privileges");
    }
    
}

$init_sql = new init_sql($argv[1],$argv[2],$argv[3]);
$init_sql->db->close();
?>