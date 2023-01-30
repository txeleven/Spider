#!/bin/bash
path=$(pwd)
#cd $path
#pwd
cd  ../../tar
dateTime=$(date +%Y%m%d%H%m%s)
mv ./tp6cms.tar.gz ./tp6cms_$dateTime.tar.gz
cd ../data
cp -r ../default/database.php  ./config/database.php
cp -r ../default/cache.php  ./config/cache.php
rm -rf runtime
mkdir -p runtime/shell
chmod -R 0755 runtime
rm -rf vendor
composer install
cd shell
bash ./crontab.sh >> ../runtime/shell/crontab.log 2>&1
#cd shell
#./start.sh
#php think run -p 8011
#rm -rf ./config/database.php
#cp -r ../default/database.php  ./config/database.php
