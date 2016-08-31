#!/bin/bash

sudo docker stop appmonitor
sudo docker rm appmonitor
sudo docker run -t -d -v /var/app-monitor-data:/var/app-monitor-data -p 9201:9200 -p 81:81 -p 6868:10000/udp -p 5601:5601 --name appmonitor monit:appmonitor /bin/bash
