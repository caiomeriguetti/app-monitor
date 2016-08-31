#!/bin/bash


service redis-server restart

git clone https://github.com/caiomeriguetti/app-monitor.git /var/app-monitor
cd /var/app-monitor
git fetch --all
git checkout origin/master -f

cp /var/app-monitor/apache2/dashboard.conf /etc/apache2/sites-available/dashboard.conf
a2ensite dashboard.conf
service apache2 restart

cp /var/app-monitor/supervisor.conf /etc/supervisor/conf.d/app-monitor.conf
service supervisor restart

cd /elasticsearch-2.3.4/bin && ./elasticsearch -Dpath.data=/var/app-monitor-data -Des.insecure.allow.root=true -Dnetwork.bind_host="0.0.0.0" > /tmp/elasticout