#!/bin/bash

domain=`hostname --domain`
if [ ! "$domain" ]; then
    echo '必须设置domainname'
    exit;
fi

echo "$domain"

if [ ! "$URL" ]; then
    echo '必须设置URL'
    exit;
fi
echo "$URL"
if [ ! "$WEBMAIL_URL" ]; then
    echo '必须设置WEBMAIL_URL'
    exit;
fi
echo "$WEBMAIL_URL"

chown -R vmail:vmail /ewomail/mail
chmod -R 700 /ewomail/mail
chown -R mysql:mysql /ewomail/mysql/data
chmod -R 700 /ewomail/mysql/data

if [ -d "/ewomail/mysql/data" -a ! -e "/ewomail/mail/first.runed" ]; then
    rm -rf /ewomail/mysql/data/*
    /ewomail/mysql/scripts/mysql_install_db --user=mysql --datadir=/ewomail/mysql/data --basedir=/ewomail/mysql
fi

service mysqld start

/home/update_file.php "$domain" "$MYSQL_ROOT_PASSWORD" "$MYSQL_MAIL_PASSWORD" "$URL" "$WEBMAIL_URL"
if [ ! -d "/ewomail/mysql/data/ewomail" -a ! -e "/ewomail/mail/first.runed" ]; then
        /home/init_sql.php "$domain" "$MYSQL_ROOT_PASSWORD" "$MYSQL_MAIL_PASSWORD"
        # rm -f /etc/ssl/certs/dovecot.pem /etc/ssl/private/dovecot.pem
        # cd /usr/local/dovecot/share/doc/dovecot/ && sh mkcert.sh
        touch /ewomail/mail/first.runed
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
