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
RUN apt-get install -y --force-yes redis-server
RUN wget https://bootstrap.pypa.io/get-pip.py && python get-pip.py
RUN pip install redis
RUN pip install awscli --ignore-installed six
COPY update-app.sh /update-app.sh
EXPOSE 81 9200 10000
ENTRYPOINT . /update-app.sh; tailf /dev/null
