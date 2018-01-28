#!/bin/bash
cd "$(dirname "$0")"

mysqldump smrtnoob_ttnmon packets gateways > ../api/dumps/ttnmon_dump-$(date +%F).sql
find ../api/dumps/ttnmon_dump-*.sql -mtime +7 -exec rm {} \;
sha256sum ../api/dumps/ttnmon_dump-*.sql > ../api/dumps/sha256sums.txt
php log_table-size.php
