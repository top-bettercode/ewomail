### 生成docker image
cd 到ewomail项目目录，执行
```
./build-docker-images.sh
```
会生成名为**ewo/ewomailserver**的镜像。

### 运行docker

1. 创建数据目录
运行**docker**目录下的
```
./create_user.sh
```
会创建mysql和vmail用户，以及/home/EwoMail/data/mysql和/home/EwoMail/data/vmail 用来分别放
mysql的数据和mail的数据

2. 修改域名
域名在docker目录下run-docker.sh中定义，测试写的“ewomail.com”根据自身情况修改
```
./run-docker.sh
```
来运行docker

3. 进去docker查看
```
docker exec -it 容器名或容器id /bin/bash
```
