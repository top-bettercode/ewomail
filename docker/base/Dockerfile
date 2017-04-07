FROM scratch
MAINTAINER mincau  <mincau@163.com>
ADD docker/base/centos-6-docker.tar.xz /

LABEL name="CentOS Base Image" \
    vendor="CentOS" \
    license="GPLv2" \
    build-date="20170315"

# 设置时区
ENV TZ "Asia/Shanghai"
ENV TERM xterm

# 添加阿里云yum源
ADD install/soft/epel-6.repo /etc/yum.repos.d/epel.repo

# 安装软件
RUN yum -y install wget && \
    wget -P /etc/yum.repos.d https://repos.fedorapeople.org/repos/mstevens/postfix/epel-postfix.repo && \
    yum -y install postfix

#RUN wget -P /etc/yum.repos.d/ http://repos.fedorapeople.org/repos/mstevens/dovecot/epel-dovecot.repo && \
#    yum -y install dovecot dovecot-devel dovecot-mysql pam-devel

RUN yum -y install perl-DBI perl-JSON-XS perl-NetAddr-IP perl-Mail-SPF perl-Sys-Hostname-Long && \
    yum -y install freetype* libpng* libjpeg*  amavisd-new && \
    yum clean all

# 安装supervisor
#RUN yum -y install --enablerepo=epel pwgen python-pip
#RUN pip install supervisor

# 反垃圾邮件设置
RUN chmod -R 770 /var/spool/amavisd/tmp && \
    usermod -G amavis clam

# 安装apache，mysql套件
ADD install/soft/ewomail-lamp-1.0-el6.x86_64.rpm /home/
RUN rpm -ivh /home/ewomail-lamp-1.0-el6.x86_64.rpm

# 拷贝admin和rainloop
RUN mkdir -p                 /ewomail/www/default
ADD ewomail-admin/      /ewomail/www/ewomail-admin
ADD rainloop/           /ewomail/www/rainloop

# 安装dovecot
ADD install/soft/dovecot-2.2.24-el6.x86_64.rpm /home/
RUN rpm -ivh /home/dovecot-2.2.24-el6.x86_64.rpm

ADD install/soft/postfix-policyd-spf-perl /usr/libexec/postfix/
RUN chmod -R 755 /usr/libexec/postfix/postfix-policyd-spf-perl

# 集中清理安装包
RUN rm -rf /home/ewomail-lamp-1.0-el6.x86_64.rpm


# 添加配置文件
ADD install/config/dovecot    /etc/dovecot
ADD install/config/postfix    /etc/postfix
ADD install/soft/httpd.init   /etc/rc.d/init.d/httpd
ADD install/soft/php.ini      /ewomail/php/etc/
ADD install/soft/php-cli.ini  /ewomail/php/etc/
ADD install/soft/dovecot.init /etc/rc.d/init.d/dovecot
ADD install/config/mail/*     /ewomail/mail/

# 清理换行
RUN sed -i 's/\r$//' /etc/rc.d/init.d/httpd && \
    sed -i 's/\r$//' /etc/rc.d/init.d/dovecot

RUN ln -s /etc/amavisd/amavisd.conf /etc && \
    mv /etc/clamd.conf /etc/clamd.conf.backup && \
    cp -rf /etc/clamd.d/amavisd.conf /etc/clamd.conf  && \
    mkdir -p /etc/ssl/certs && \
    mkdir -p /etc/ssl/private && \
    chmod -R 755 /etc/rc.d/init.d/httpd && \
    chmod -R 755 /etc/rc.d/init.d/dovecot    

# 生成签名
RUN chmod 755 /usr/local/dovecot/share/doc/dovecot/mkcert.sh && \
    cd /usr/local/dovecot/share/doc/dovecot/ && \
    sh mkcert.sh
#RUN cd /usr/libexec/dovecot && \
#    sh mkcert.sh

RUN groupadd -g 5000 vmail && \
    useradd -M -u 5000 -g vmail -s /sbin/nologin vmail && \   
    chown -R vmail:vmail /ewomail/mail && \
    chmod -R 700 /ewomail/mail && \    
    chown -R www:www /ewomail/www && \
    chmod -R 770 /ewomail/www && \    
    mkdir -p /ewomail/dkim && \
    chown -R amavis:amavis /ewomail/dkim && \
    amavisd genrsa /ewomail/dkim/mail.pem && \
    chown -R amavis:amavis /ewomail/dkim

ADD install/init.php                  /home/
ADD docker/base/entrypoint.sh         /home/
RUN chmod -R 700 /home/init.php && \    
    chmod -R 700 /home/entrypoint.sh

#RUN /ewomail/mysql/bin/mysqld --user=mysql && \
#    /ewomail/php/bin/php -f /home/init.php
#    chmod -R 440 /ewomail/config.ini 

EXPOSE 25 109 110 143 465 587 993 995 8000 8010

VOLUME ["/ewomail/mysql/data"]
VOLUME ["/ewomail/mail"]

ENTRYPOINT ["/home/entrypoint.sh"]

