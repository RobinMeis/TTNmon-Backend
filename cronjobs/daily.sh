#!/bin/bash
cd "$(dirname "$0")"

shopt -s nullglob
mysqldump smrtnoob_ttnmon packets gateways | gzip -c > ../api/dumps/ttnmon_dump-$(date +%F).sql.gz
find ../api/dumps/ttnmon_dump-*.gz -mtime +7 -exec rm {} \;
sha256sum ../api/dumps/ttnmon_dump-*.sql.gz > ../api/dumps/sha256sums.txt
php log_table-size.php
