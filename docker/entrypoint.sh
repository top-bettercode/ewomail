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

if [ -d "/ewomail/mysql/data" -a ! -e "/ewomail/mail/first.runed" ]; then
    rm -rf /ewomail/mysql/data/*
    /ewomail/mysql/scripts/mysql_install_db --user=mysql --datadir=/ewomail/mysql/data --basedir=/ewomail/mysql
fi

service mysqld start

/home/update_file.php "$domain" "$MYSQL_ROOT_PASSWORD" "$MYSQL_MAIL_PASSWORD" "$URL" "$WEBMAIL_URL"
if [ ! -d "/ewomail/mysql/data/ewomail" -a ! -e "/ewomail/mail/first.runed" ]; then

#      初始化ewomail数据
        sed -i "s/Copyright.*版权所有/$COPYRIGHT/" /ewomail/www/ewomail-admin/upload/install.sql
        sed -i "s/ICP证.*号/$ICP/" /ewomail/www/ewomail-admin/upload/install.sql
        sed -i "s/ewomail\\.com/$TITLE/" /ewomail/www/ewomail-admin/upload/install.sql
        lang=`echo $LANGUAGE | tr [:upper:] [:lower:]  | tr _ -`
        echo "lang:$lang"
        sed -i "s/zh-cn/$lang/" /ewomail/www/ewomail-admin/upload/install.sql


        /home/init_sql.php "$domain" "$MYSQL_ROOT_PASSWORD" "$MYSQL_MAIL_PASSWORD"

        if [ ! -d "/etc/ssl/certs/dovecot.pem" -a ! -e "/etc/ssl/private/dovecot.pem" ]; then
          rm -f /etc/ssl/certs/dovecot.pem /etc/ssl/private/dovecot.pem
          cd /usr/local/dovecot/share/doc/dovecot/ && sh mkcert.sh
        fi

#        初始化rainloop配置文件
        mv /ewomail/www/rainloop_data_ /ewomail/www/rainloop/data/_data_
        sed -i "s/'123456'/'$MYSQL_MAIL_PASSWORD'/" /ewomail/www/rainloop/data/_data_/_default_/plugins/ewomail-change-password/index.php
        sed -i "s/\$mydomain/$domain/" /ewomail/www/rainloop/data/_data_/_default_/plugins/ewomail-change-password/index.php

        echo "lang:$LANGUAGE"
        sed -i "s/\$mydomain/$domain/" /ewomail/www/rainloop/data/_data_/_default_/configs/application.ini
        sed -i "s/title = \"ewomail\\.com\"/title = \"$TITLE\"/" /ewomail/www/rainloop/data/_data_/_default_/configs/application.ini
        sed -i "s/zh_CN/$LANGUAGE/" /ewomail/www/rainloop/data/_data_/_default_/configs/application.ini
        sed -i "s/loading_description = \"ewomail\\.com\"/loading_description = \"$TITLE\"/" /ewomail/www/rainloop/data/_data_/_default_/configs/application.ini

        mkdir /ewomail/www/rainloop/data/_data_/_default_/domains
        echo -e 'imap_host = "127.0.0.1"\nimap_port = 143\nimap_secure = "TLS"\nimap_short_login = Off\nsieve_use = Off\nsieve_allow_raw = Off\nsieve_host = ""\nsieve_port = 4190\nsieve_secure = "None"\nsmtp_host = "127.0.0.1"\nsmtp_port = 587\nsmtp_secure = "TLS"\nsmtp_short_login = Off\nsmtp_auth = On\nsmtp_php_mail = Off\nwhite_list = ""' > /ewomail/www/rainloop/data/_data_/_default_/domains/$domain.ini

        touch /ewomail/mail/first.runed
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
service fail2ban start

tail -fn 0 /var/log/maillog
