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

    sonarscanner:
      image: sonarsource/sonar-scanner-cli:latest
      volumes:
        - sonarscanner-data:/usr/src
      environment:
        - SONAR_HOST_URL="http://sonarqube:9000"
        - SONAR_SCANNER_OPTS="-Dsonar.projectKey=Hive"
        - SONAR_TOKEN="sqp_sqp_95ec0aa97b17a116a718b1ce1f5240e2dc006d9e"


docker run --rm -e SONAR_HOST_URL="http://localhost:9000" -e SONAR_SCANNER_OPTS="-Dsonar.projectKey=Hive" -e SONAR_TOKEN="sqp_sqp_95ec0aa97b17a116a718b1ce1f5240e2dc006d9e" -v ".:/usr/src" --network host sonarsource/sonar-scanner-cli 