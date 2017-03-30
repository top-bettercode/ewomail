docker run  -dit -p 25:25 -p 109:109 -p 110:110 -p 143:143 -p 465:465 -p 587:587 -p 993:993 -p 995:995  -v /home/EwoMail/data:/ewomail/mysql/data -v /home/EwoMail/data:/ewomail/mail  ewo/ewomailserver  'baidu.com'

