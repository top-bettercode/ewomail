#!/bin/bash

service mysqld start

if [ ! -d "/ewomail/mysql/data/ewomail" ]; then
	./home/init.php $1
	rm -rf /ewomail/www/tz.php
fi

service clamd start
service spamassassin start
service amavisd start
service dovecot start
service httpd start
service postfix restart

tail -fn 0 /var/log/mail/mail.log
