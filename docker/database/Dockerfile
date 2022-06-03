FROM mysql
COPY *.sql /docker-entrypoint-initdb.d/
ENV TZ=Europe/London
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone
