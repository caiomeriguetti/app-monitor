FROM ubuntu:14.04
RUN apt-get update
RUN apt-get upgrade -y --force-yes
RUN apt-get install -y --force-yes wget nano python-software-properties software-properties-common curl
RUN apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv EA312927
RUN add-apt-repository ppa:webupd8team/java
RUN apt-get update
RUN echo oracle-java8-installer shared/accepted-oracle-license-v1-1 select true | debconf-set-selections
RUN apt-get install -y --force-yes oracle-java8-installer
RUN apt-get install -y --force-yes oracle-java8-set-default
RUN curl -L -O https://download.elastic.co/elasticsearch/release/org/elasticsearch/distribution/tar/elasticsearch/2.3.4/elasticsearch-2.3.4.tar.gz
RUN tar -xvf elasticsearch-2.3.4.tar.gz
RUN apt-get install -y --force-yes supervisor
RUN mkdir -p /var/appmonitor/ && mkdir -p /var/log/udp-server/ && mkdir -p /var/elasticdata
RUN apt-get install -y --force-yes python-requests
RUN wget -qO - https://packages.elastic.co/GPG-KEY-elasticsearch | apt-key add -
RUN echo "deb http://packages.elastic.co/kibana/4.5/debian stable main" | tee -a /etc/apt/sources.list
RUN apt-get update && apt-get install kibana
RUN update-rc.d kibana defaults 95 10
COPY udp-server.py /var/appmonitor/udp-server.py
COPY udp-server.supervisor.conf /etc/supervisor/conf.d/udp-server.conf
EXPOSE 80 9200 10000 5601
ENTRYPOINT service kibana start && service supervisor start && cd elasticsearch-2.3.4/bin && ./elasticsearch -Dpath.data=/var/elasticdata -Des.insecure.allow.root=true -Dnetwork.bind_host="0.0.0.0" > /tmp/elasticout
