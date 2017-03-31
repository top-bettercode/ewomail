domain='ewomail.com'
docker run  -dit --net="host"  -p 25:25 -p 109:109 -p 110:110 -p 143:143 -p 465:465 -p 587:587 -p 993:993 -p 995:995  \
 -p 8000:8000 -p 8010:8010 -v /home/EwoMail/data/mysql/:/ewomail/mysql/data/ -v /home/EwoMail/data/vmail/:/ewomail/mail/ \
 --name ewomail ewo/ewomailserver  $domain

