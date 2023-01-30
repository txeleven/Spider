#!/bin/bash

#crontabName= crontab -l | awk '{print $8}'
cd ../
php think action console/guard/crontab
