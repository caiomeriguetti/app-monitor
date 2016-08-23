#!/bin/bash

sudo docker stop appmonitor
sudo docker rm appmonitor
sudo docker run -t -d -p 9201:9200 -p 6868:10000/udp -p 5601:5601 --name appmonitor monit:appmonitor /bin/bash
