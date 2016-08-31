#!/bin/bash

cd /var/app-monitor
git fetch --all
cp /var/app-monitor/apache2/dashboard.conf /etc/apache2/sites-available/dashboard.conf
a2ensite dashboard.conf 
git checkout origin/master -f
cp /var/app-monitor/supervisor.conf /etc/supervisor/conf.d/app-monitor.conf
service apache2 start
service redis-server start
service supervisor start