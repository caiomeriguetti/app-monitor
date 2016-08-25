#!/bin/bash

cp /var/app-monitor/apache2/dashboard.conf /etc/apache2/sites-available/dashboard.conf
a2ensite dashboard.conf 
cd /var/app-monitor 
git fetch --all 
git checkout origin/master -f 
service apache2 start 
service supervisor start 
