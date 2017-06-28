### 
[EwoMail 开源企业邮件系统](http://www.ewomail.com/) 的docker镜像

[EwoMail 官方文档](http://doc.ewomail.com/ewomail/285649)

EwoMail-Admin版本为1.05

### 生成docker image
cd 到ewomail项目目录，执行
```
./build-docker-images.sh
```
会生成名为**bestwu/ewomailserver**的镜像。

### 运行docker

mail.ewomail.com 换成自己的域名

docker-compose方式

```yml
  mail:
    image: bestwu/ewomailserver
    hostname: mail.ewomail.com
    container_name: ewomail
    ports:
      - "25:25"
      - "143:143"
      - "587:587"
      - "993:993"
      - "109:109"
      - "110:110"
      - "465:465"
      - "995:995"
      - "8000:8000"
      - "8010:8010"
    volumes:
      - ./mysql:/ewomail/mysql/data
      - ./vmail:/ewomail/mail
```

或

```cmd
docker run  -d -h mail.ewomail.com  -p 25:25 -p 109:109 -p 110:110 -p 143:143 -p 465:465 -p 587:587 -p 993:993 -p 995:995  \
 -p 8000:8000 -p 8010:8010 -v /home/EwoMail/data/mysql/:/ewomail/mysql/data/ -v /home/EwoMail/data/vmail/:/ewomail/mail/ \
 --name ewomail bestwu/ewomailserver

```

运行成功后访问

[邮箱管理后台http://localhost:8010](http://localhost:8010)

默认用户: admin

默认密码: ewomail123

[Rainloop 管理端 http://localhost:8000/?admin](http://localhost:8000/?admin)

默认用户: admin

默认密码: ewomail123

[Rainloop 用户端 http://localhost:8000](http://localhost:8000)

### 设置域名DNS

这里使用万网DNS为参考

![](dns.png)

将mail.ewomail.cn 改成你的域名

红色部分请改为你的服务器IP

### DKIM设置

DKIM是电子邮件验证标准，域名密钥识别邮件标准，主要是用来防止被判定为垃圾邮件。

每个域名都需要添加一个dkim的key，EwoMail默认安装后已自动添加主域名dkim，只需要设置好dkim的dns即可。

    获取dkim key

执行查看代码

```
docker exec ewomail amavisd showkeys
```

若安装成功会输出以下信息：
```
; key#1, domain ewomail.com, /ewomail/dkim/mail.pem
dkim._domainkey.ewomail.com.	3600 TXT (
  "v=DKIM1; p="
  "MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC09HcLpwcdgWtzkrZDBRBYfQo5"
  "prSRyedA72wiD3vFGXLWHyy0KOXp+uwvkNzaBpvU2DDKNTTPdo1pNWtl/LkpRCVq"
  "+uRG+LhZBuic0GpDJnD7HckUbwsyGktb/6g5ogScNtPWB+pegENFDl8BuFn3zDiD"
  "nnGxbpj3emSxDlskzwIDAQAB")
```
整理后，设置DNS

| 域名 	 |     记录类型 	| 主机记录 	|  记录值 |
|---|---|---|---|
| ewomail.com |	TXT	| dkim._domainkey  | v=DKIM1;p=MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC09HcLpwcdgWtzkrZDBRBYfQo5prSRyedA72wiD3vFGXLWHyy0KOXp+uwvkNzaBpvU2DDKNTTPdo1pNWtl/LkpRCVq+uRG+LhZBuic0GpDJnD7HckUbwsyGktb/6g5ogScNtPWB+pegENFDl8BuFn3zDiDnnGxbpj3emSxDlskzwIDAQAB

等待10分钟后测试是否设置正确。

```
docker exec ewomail amavisd testkeys
```
```
TESTING#1: dkim._domainkey.ewomail.com       => pass
```
显示pass则正确。
