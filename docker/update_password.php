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

class update{
    
    public $db;
    
    public $domain = 'ewomail.cn';
    
    //数据库名字
    public $mail_db = 'ewomail';
    //数据库账号
    public $mail_db_username = 'ewomail';
    
    public $root_pwd;
    public $mail_pwd;
    public $url;
    public $webmail_url;
    
    public function __construct($domain,$root_pwd,$mail_pwd,$url,$webmail_url){
        
        if(!$domain){
            die("Missing domain parameter");
        }
                
        $this->domain = $domain;       
        $this->root_pwd = $root_pwd;
        $this->mail_pwd = $mail_pwd;
        $this->url = $url;
        $this->webmail_url = $webmail_url;
        
        $this->update_password_file($mail_pwd);
        $this->update_file();
        $this->ending();
        
        echo "the configuration succeeds\n";
        
    }
    
    /**
     * 结束后更新文件
     * */
    public function ending()
    {
        $info = "domain：".$this->domain."\n";
        $info .= "mysql-root-password：".$this->root_pwd."\n";
        $info .= "mysql-ewomail-password：".$this->mail_pwd."\n";
        file_put_contents("/ewomail/config.ini",$info);
    }
      
    /**
     * 修改相关数据库的文件配置
     * */
    public function update_password_file($password)
    {
        //修改dovecot数据库配置
        $dovecot_conf = [
            '/etc/dovecot/dovecot-sql.conf.ext',
            '/etc/dovecot/dovecot-dict-sql.conf.ext',
        ];
        
        foreach($dovecot_conf as $conf){
            $this->op_file($conf,function($line)use($password){
                if (trim($line) == '') {  
                    return $line;
                }
                if(preg_match('/^connect/',$line)){
                    $c = preg_replace("/password=.+/","password=".$password,$line);
                }else{
                    $c = $line;
                }
                return $c;
            });
        }
        
        $postfix_conf = [
            '/etc/postfix/mysql/mysql_bcc_user.cf',
            '/etc/postfix/mysql/mysql-alias-maps.cf',
            '/etc/postfix/mysql/mysql-mailbox-domains.cf',
            '/etc/postfix/mysql/mysql-mailbox-maps.cf',
            '/etc/postfix/mysql/mysql-sender-login-maps.cf'
        ];
        
        foreach($postfix_conf as $conf){
            $this->op_file($conf,function($line)use($password){
                if (trim($line) == '') {  
                    return $line;
                }
                if(preg_match('/^password/',$line)){
                    $c = "password = ".$password."\n";
                }else{
                    $c = $line;
                }
                return $c;
            });
        }
        
        
        //修改ewomail配置文件
        $conf = '/ewomail/www/ewomail-admin/core/config.php';
        $this->op_file($conf,function($line)use($password){
            if (trim($line) == '') {  
                return $line;
            }
            if(preg_match("/'dbpw'/",$line)){
                $c = preg_replace("/'dbpw'.+/","'dbpw' => '".$password."',",$line);
            }else if(preg_match("/'code_key'/",$line)){
                $c = preg_replace("/'code_key'.+/","'code_key' => '".$this->create_password()."',",$line);
            }else if(preg_match("/'url'/",$line)){
                $c = preg_replace("/'url'.+/","'url' => '".$this->url."',",$line);
            }else if(preg_match("/'webmail_url'/",$line)){
                $c = preg_replace("/'webmail_url'.+/","'webmail_url' => '".$this->webmail_url."',",$line);
            }else{
                $c = $line;
            }
            return $c;
        });

    }

    /**
     * 修改配置文件
     * */
    public function update_file()
    {
        $amavisd_conf = '/etc/amavisd/amavisd.conf';
        $this->op_file($amavisd_conf,function($line){
            if (trim($line) == '') {  
                return $line;
            }

            if(preg_match('/^\\$mydomain/',$line)){
                $c = "\$mydomain = '{$this->domain}';\n";
            }else if(preg_match('/\\$myhostname/',$line)){
                $c = "\$myhostname = 'mail.{$this->domain}';\n";
            }else if(preg_match('/\\$final_banned_destiny/',$line)){
                $c = "\$final_banned_destiny = D_PASS;\n";
            }else if(preg_match('/\\$final_bad_header_destiny/',$line)){
                $c = "\$final_bad_header_destiny = D_PASS;\n";
            }else{
                $c = $line;
            }
            return $c;
        });
        
        $amavisd_str = file_get_contents($amavisd_conf);
        $amavisd_out = '$signed_header_fields{\'received\'} = 0;
$signed_header_fields{\'to\'} = 1;
$originating = 1;
                        
# Add dkim_key here.
dkim_key("'.$this->domain.'", "dkim", "/ewomail/dkim/mail.pem");
                        
@dkim_signature_options_bysender_maps = ({
# catchall defaults
\'.\' => {c => \'relaxed/simple\', ttl => 30*24*3600 },
} );';
        file_put_contents($amavisd_conf,$amavisd_str."\n".$amavisd_out);
        
        $postfix_conf = "/etc/postfix/main.cf";
        $this->op_file($postfix_conf,function($line){
            if (trim($line) == '') {  
                return $line;
            }

            if(preg_match('/^mydomain/',$line)){
                $c = "mydomain = {$this->domain}\n";
            }else if(preg_match('/^myhostname/',$line)){
                $c = "myhostname = mail.{$this->domain}\n";
            }else{
                $c = $line;
            }
            return $c;
        });
        
        //fail2ban
        $fail2ban_conf = "/etc/fail2ban/fail2ban.conf";
        $this->op_file($fail2ban_conf,function($line){
            if (trim($line) == '') {  
                return $line;
            }

            if(preg_match('/^logtarget/',$line)){
                $c = "logtarget = /var/log/fail2ban.log\n";
            }else{
                $c = $line;
            }
            return $c;
        });
        
        //apache
        $apache_conf = '/ewomail/apache/conf/extra/httpd-vhosts.conf';
        $apache_str = "
Listen 8000 
Listen 8010
Listen 8020

<VirtualHost *:8010>
ServerName localhost
DocumentRoot /ewomail/www/ewomail-admin/
DirectoryIndex index.php index.html index.htm
<Directory /ewomail/www/ewomail-admin/>
Options +Includes -Indexes
AllowOverride All
Order Deny,Allow
Allow from All
</Directory>
</VirtualHost>

<VirtualHost *:8000>
ServerName localhost
DocumentRoot /ewomail/www/rainloop/
DirectoryIndex index.php index.html index.htm
<Directory /ewomail/www/rainloop/>
Options +Includes -Indexes
AllowOverride All
Order Deny,Allow
Allow from All
</Directory>
</VirtualHost>

<VirtualHost *:8020>
ServerName localhost
DocumentRoot /ewomail/www/phpMyadmin/
DirectoryIndex index.php index.html index.htm
<Directory /ewomail/www/phpMyadmin/>
Options +Includes -Indexes
AllowOverride All
Order Deny,Allow
Allow from All
</Directory>
</VirtualHost>
        ";
        
        if(copy($apache_conf,$apache_conf.".backup")){
            file_put_contents($apache_conf,$apache_str);
        }
        
    }

    public function op_file($file,$fun)
    {
        $f = fopen($file,"r");
        $c = '';
        if($f){
            copy($file,$file.".backup");
            while (!feof($f)) {
                $line = fgets($f);
                $c .= $fun($line);
            }
            
            fclose($f);
            file_put_contents($file,$c);
        }
        
    }

    /**
     * 创建密码
     * */
    function create_password( $length = 16 ) {
        // 密码字符集，可任意添加你需要的字符
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    
        $password = '';
        for ( $i = 0; $i < $length; $i++ ) 
        {
            // 这里提供两种字符获取方式
            // 第一种是使用 substr 截取$chars中的任意一位字符；
            // 第二种是取字符数组 $chars 的任意元素
            // $password .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
            $password .= $chars[ mt_rand(0, strlen($chars) - 1) ];
        }
    
        return $password;
    }
    
}

$update = new update($argv[1],$argv[2],$argv[3],$argv[4],$argv[5]);
?>