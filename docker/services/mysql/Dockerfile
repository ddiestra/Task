FROM mysql:5.7

RUN apt-get update \
  && apt-get install -y \
     nano \
  && rm -rf /var/lib/apt/lists/*

COPY ./src/my.cnf /etc/mysql/my.cnf

RUN chmod 644 /etc/mysql/my.cnf

WORKDIR /var/www/html