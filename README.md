# ITVB23OWS Development Pipelines starter code

This repository contains starter code for the course ITVB23OWS Development pipelines,
which is part of the HBO-ICT program at Hanze University of Applied Sciences in
Groningen.

This is a deliberately poor software project, containing bugs and missing features. It
is not intended as a demonstration of proper software engineering techniques.

The application contains PHP 5.6 code and should run using the built-in PHP server,
which can be started using the following command.

```
php -S localhost:8000
```

In addition to PHP 5.6 or higher, the code requires the mysqli extension and a MySQL
or compatible server. The application assumes a root user without password, and tries
to access the database `hive`. The file `hive.sql` contains the database schema.

This application is licensed under the MIT license, see `LICENSE.md`. Questions
and comments can be directed to
[Ralf van den Broek](https://github.com/ralfvandenbroek).

-------------------------------

docker network create jenkins

docker run --name jenkins-docker --rm --detach --privileged --network jenkins --network-alias docker --env DOCKER_TLS_CERTDIR=/certs --volume jenkins-docker-certs:/certs/client --volume jenkins-data:/var/jenkins_home --publish 2376:2376 docker:dind

docker build -t myjenkins-blueocean:lts .

docker run --name jenkins-blueocean --restart=on-failure --detach --network jenkins --env DOCKER_HOST=tcp://docker:2376 --env DOCKER_CERT_PATH=/certs/client --env DOCKER_TLS_VERIFY=1 --volume jenkins-data:/var/jenkins_home --volume jenkins-docker-certs:/certs/client:ro --publish 8080:8080 --publish 50000:50000 myjenkins-blueocean:2.426.2-1

        environment:
          - DOCKER_HOST=tcp://docker:2376
          - DOCKER_CERT_PAT=/certs/client
          - DOCKER_TLS_VERIFY=1