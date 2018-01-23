#!/bin/bash
cd "$(dirname "$0")"

php preprocess_gateway-list.php
php preprocess_links.php
