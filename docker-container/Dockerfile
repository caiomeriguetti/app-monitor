FROM ubuntu:14.04
RUN apt-get update
RUN apt-get upgrade -y --force-yes
RUN apt-get install -y --force-yes wget nano python-software-properties software-properties-common curl apache2 php5 libapache2-mod-php5 php5-mcrypt
RUN apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv EA312927
RUN add-apt-repository ppa:webupd8team/java
RUN apt-get update
RUN echo oracle-java8-installer shared/accepted-oracle-license-v1-1 select true | debconf-set-selections
RUN apt-get install -y --force-yes oracle-java8-installer
RUN apt-get install -y --force-yes oracle-java8-set-default
RUN curl -L -O https://download.elastic.co/elasticsearch/release/org/elasticsearch/distribution/tar/elasticsearch/2.3.4/elasticsearch-2.3.4.tar.gz
RUN tar -xvf elasticsearch-2.3.4.tar.gz
RUN apt-get install -y --force-yes supervisor
RUN mkdir -p /var/log/app-monitor/ && mkdir -p /var/app-monitor-data
RUN apt-get install -y --force-yes python-requests
RUN apt-get install -y --force-yes git
RUN apt-get install -y --force-yes php5-curl
RUN currentDir=$(pwd)
RUN cd /var && git clone https://github.com/caiomeriguetti/app-monitor.git ;
RUN cp /var/app-monitor/apache2/dashboard.conf /etc/apache2/sites-available/dashboard.conf; a2ensite dashboard.conf
RUN cp /var/app-monitor/udp-server.supervisor.conf /etc/supervisor/conf.d/udp-server.conf
RUN cd $currentDir
RUN apt-get install -y --force-yes redis-server
RUN wget https://bootstrap.pypa.io/get-pip.py && python get-pip.py
RUN pip install redis
EXPOSE 80 9200 10000
ENTRYPOINT currentDir=$(pwd); cd /var/app-monitor; git fetch --all; git checkout origin/master -f; cd $currentDir; service apache2 start; service redis-server start; service supervisor start; cd elasticsearch-2.3.4/bin && ./elasticsearch -Dpath.data=/var/app-monitor-data -Des.insecure.allow.root=true -Dnetwork.bind_host="0.0.0.0" > /tmp/elasticout
