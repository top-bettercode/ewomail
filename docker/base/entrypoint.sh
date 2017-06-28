#!/bin/bash

domain=`hostname --domain`
if [ ! $domain ]; then
    echo '必须设置domainname'
    exit;
fi
echo $domain
if [ ! $URL ]; then
    echo '必须设置URL'
    exit;
fi
if [ ! $WEBMAIL_URL ]; then
    echo '必须设置WEBMAIL_URL'
    exit;
fi

chown -R vmail:vmail /ewomail/mail
chmod -R 700 /ewomail/mail
chown -R mysql:mysql /ewomail/mysql/data
chmod -R 700 /ewomail/mysql/data

if [ -d "/ewomail/mysql/data" -a ! -e "/ewomail/mail/first.runed" ]; then
    rm -rf /ewomail/mysql/data/*
    /ewomail/mysql/scripts/mysql_install_db --user=mysql --datadir=/ewomail/mysql/data --basedir=/ewomail/mysql
fi

service mysqld start

if [ ! -d "/ewomail/mysql/data/ewomail" -a ! -e "/ewomail/mail/first.runed" ]; then
        /home/init.php $domain $MYSQL_ROOT_PASSWORD $MYSQL_MAIL_PASSWORD $URL $WEBMAIL_URL
        rm -rf /ewomail/www/tz.php
        touch /ewomail/mail/first.runed
    else
        /home/update_password.php $domain $MYSQL_ROOT_PASSWORD $MYSQL_MAIL_PASSWORD $URL $WEBMAIL_URL
fi

service rsyslog start
service clamd start
service spamassassin start
service amavisd start
service dovecot start
service httpd start
service postfix start
service fail2ban start

tail -fn 0 /var/log/maillog
