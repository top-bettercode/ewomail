#!/bin/bash

domain=`hostname --domain`
if [ ! "$domain" ]; then
    echo '必须设置domainname'
    exit;
fi

echo "domain:$domain"

if [ ! "$URL" ]; then
    echo '必须设置URL'
    exit;
fi
echo "url:$URL"
if [ ! "$WEBMAIL_URL" ]; then
    echo '必须设置WEBMAIL_URL'
    exit;
fi
echo "webmail_url:$WEBMAIL_URL"
echo "title:$TITLE"
echo "copyright:$COPYRIGHT"
echo "icp:$ICP"
echo "lang:$LANGUAGE"

chown -R vmail:vmail /ewomail/mail
chmod -R 700 /ewomail/mail
chown -R mysql:mysql /ewomail/mysql/data
chmod -R 700 /ewomail/mysql/data
chown -R www:www /ewomail/www/rainloop/data
chmod -R 770 /ewomail/www/rainloop/data

if [ -d "/ewomail/mysql/data" -a ! -e "/ewomail/mysql/data/first.runed" ]; then
    echo '初始化数据库'
    rm -rf /ewomail/mysql/data/*
    /ewomail/mysql/scripts/mysql_install_db --user=mysql --datadir=/ewomail/mysql/data --basedir=/ewomail/mysql
    touch /ewomail/mysql/data/first.runed
fi

service mysqld start

if [ ! -e "/etc/first.runed" ]; then
    echo '初始配置'
    /home/update_file.php "$domain" "$MYSQL_ROOT_PASSWORD" "$MYSQL_MAIL_PASSWORD" "$URL" "$WEBMAIL_URL"
    sed -i "s/\$mydomain/$domain/" /etc/monit/monit.d/server.cfg
    echo -e "
allow $MONIT_USER:$MONIT_PASSWORD # web登录的用户名和密码
"  >> /etc/monit/monitrc

        if [ -n "$MONIT_MAILSERVER" -a -n "$MONIT_MAIL_USER" -a -n "$MONIT_MAIL_PASSWORD" -a -n "$MONIT_MAIL_ALERT" ];then
        echo -e "

# 邮箱设置
set mailserver $MONIT_MAILSERVER port $MONIT_MAIL_PORT
username \"$MONIT_MAIL_USER\" password \"$MONIT_MAIL_PASSWORD\"
# using ssl
set alert $MONIT_MAIL_ALERT

set mail-format {
from: $MONIT_MAIL_USER
subject: [\$SERVICE] \$EVENT
message:
[\$SERVICE] \$EVENT

Date: \$DATE
Action: \$ACTION
Host: \$HOST
Description: \$DESCRIPTION

Your faithful employee,
Monit }"  >> /etc/monit/monitrc
    fi
    touch /etc/first.runed
fi

#      初始化ewomail数据
if [ ! -d "/ewomail/mysql/data/ewomail" ]; then
    echo '初始化ewomail数据'
    sed -i "s/Copyright.*版权所有/$COPYRIGHT/" /ewomail/www/ewomail-admin/upload/install.sql
    sed -i "s/ICP证.*号/$ICP/" /ewomail/www/ewomail-admin/upload/install.sql
    sed -i "s/ewomail\\.com/$TITLE/" /ewomail/www/ewomail-admin/upload/install.sql
    lang=`echo $LANGUAGE | tr [:upper:] [:lower:]  | tr _ -`
    echo "lang:$lang"
    sed -i "s/zh-cn/$lang/" /ewomail/www/ewomail-admin/upload/install.sql

    /home/init_sql.php "$domain" "$MYSQL_ROOT_PASSWORD" "$MYSQL_MAIL_PASSWORD"
fi

if [ ! -e "/etc/ssl/certs/dovecot.pem" -o ! -e "/etc/ssl/private/dovecot.pem" ]; then
    echo '生成dovecot.pem'
    rm -f /etc/ssl/certs/dovecot.pem /etc/ssl/private/dovecot.pem
    cd /usr/local/dovecot/share/doc/dovecot/ && sh mkcert.sh
fi

if [ ! -e "/ewomail/dkim/mail.pem" ]; then
    echo '生成 dkim mail.pem'
    amavisd genrsa /ewomail/dkim/mail.pem
fi


# 初始化rainloop配置文件
if [ ! -d "/ewomail/www/rainloop/data/_data_" ]; then
    echo '初始化rainloop配置文件'
    mv /ewomail/www/rainloop_data_ /ewomail/www/rainloop/data/_data_
    sed -i "s/'123456'/'$MYSQL_MAIL_PASSWORD'/" /ewomail/www/rainloop/data/_data_/_default_/plugins/ewomail-change-password/index.php
    sed -i "s/\$mydomain/$domain/" /ewomail/www/rainloop/data/_data_/_default_/plugins/ewomail-change-password/index.php

    echo "lang:$LANGUAGE"
    sed -i "s/\$mydomain/$domain/" /ewomail/www/rainloop/data/_data_/_default_/configs/application.ini
    sed -i "s/title = \"ewomail\\.com\"/title = \"$TITLE\"/" /ewomail/www/rainloop/data/_data_/_default_/configs/application.ini
    sed -i "s/zh_CN/$LANGUAGE/" /ewomail/www/rainloop/data/_data_/_default_/configs/application.ini
    sed -i "s/loading_description = \"ewomail\\.com\"/loading_description = \"$TITLE\"/" /ewomail/www/rainloop/data/_data_/_default_/configs/application.ini

    mkdir /ewomail/www/rainloop/data/_data_/_default_/domains
    echo -e 'imap_host = "127.0.0.1"
imap_port = 143
imap_secure = "TLS"
imap_short_login = Off
sieve_use = Off
sieve_allow_raw = Off
sieve_host = ""
sieve_port = 4190
sieve_secure = "None"
smtp_host = "127.0.0.1"
smtp_port = 587
smtp_secure = "TLS"
smtp_short_login = Off
smtp_auth = On
smtp_php_mail = Off
white_list = ""' > /ewomail/www/rainloop/data/_data_/_default_/domains/$domain.ini

fi

echo ""
echo "the configuration succeeds"

service rsyslog start
service clamd start
service spamassassin start
service amavisd start
service dovecot start
service httpd start
service postfix start

monit -c /etc/monit/monitrc

tail -fn 0 /var/log/maillog
