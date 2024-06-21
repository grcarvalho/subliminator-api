Run composer install to install all dependencies,

The database is pgsql, so be sure to have your PHP PDO configured

To start the database, at the root of the project run:

docker-compose up


to run the command that will import the data into the database:

php bin/console LoadData


After that you can run:

symfony server:start

and it should run the api without problems.

Explanation:
The perfect practice here would be separate the data in three databases: Customers, Addresses and Orders, but for the lack of time, i was not able to separate it.