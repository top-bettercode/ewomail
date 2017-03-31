groupadd -g 5000 vmail
useradd -M -u 5000 -g vmail -s /sbin/nologin vmail

groupadd -g 501 mysql
useradd -M -u 501 -g mysql -s /sbin/nologin mysql

# 创建存放mysql和邮件的数据目录
mkdir -p /home/EwoMail/data/mysql
mkdir -p /home/EwoMail/data/vmail

chown -R mysql:mysql /home/EwoMail/data/mysql
chown -R vmail:vmail /home/EwoMail/data/vmail