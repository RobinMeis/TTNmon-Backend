#!/bin/bash
cd "$(dirname "$0")"

php gateways_ttn-details.php
shopt -s nullglob
mysqldump --defaults-file=/home/www-data/.my.cnf ttnmon packets gateways networks | gzip -c > ../api/dumps/ttnmon_dump-$(date +%F).sql.gz
find ../api/dumps/ttnmon_dump-*.gz -mtime +1 -exec rm {} \;
sha256sum ../api/dumps/ttnmon_dump-*.sql.gz > ../api/dumps/sha256sums.txt
php log_table-size.php
