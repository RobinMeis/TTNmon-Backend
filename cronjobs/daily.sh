#!/bin/bash
cd "$(dirname "$0")"

mysqldump smrtnoob_ttnmon packets gateways > ../api/dumps/ttnmon_dump-$(date +%F).sql
find ../api/dumps/ttnmon_dump-*.sql -mtime +5 -exec rm {} \;
