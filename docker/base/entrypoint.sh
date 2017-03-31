#!/bin/bash

if [ -d "/ewomail/mysql/data" ]; then
    rm -rf /ewomail/mysql/data
    /ewomail/mysql/scripts/mysql_install_db --user=mysql --datadir=/ewomail/mysql/data --basedir=/ewomail/mysql
fi

service mysqld start

if [ ! -e "/ewomail/mysql/data/ewomail" ]; then
    echo $1
	/ewomail/php/bin/php -f /home/init.php $1
	rm -rf /ewomail/www/tz.php
fi

service clamd start
service spamassassin start
service amavisd start
service dovecot start
service httpd start
service postfix restart

tail -fn 0 /var/log/maillog
