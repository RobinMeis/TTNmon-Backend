#!/bin/bash
cd "$(dirname "$0")"

mysqldump smrtnoob_ttnmon packets gateways > ../dumps/ttnmon_dump-$(date +%F).sql
