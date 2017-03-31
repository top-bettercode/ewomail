#!/bin/bash

if [ -d "/ewomail/mysql/data" -a ! -e "/ewomail/mail/first.runed" ]; then
    rm -rf /ewomail/mysql/data
    /ewomail/mysql/scripts/mysql_install_db --user=mysql --datadir=/ewomail/mysql/data --basedir=/ewomail/mysql
fi

service mysqld start

if [ ! -d "/ewomail/mysql/data/ewomail" -a ! -e "/ewomail/mail/first.runed" ]; then
    echo $1
	/ewomail/php/bin/php -f /home/init.php $1
	rm -rf /ewomail/www/tz.php
	touch /ewomail/mail/first.runed
fi

service clamd start
service spamassassin start
service amavisd start
service dovecot start
service httpd start
service postfix restart

tail -fn 0 /var/log/maillog
