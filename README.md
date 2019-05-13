
# Task Application

With Docker (Recommended)
-------------------------

#### Requirements

1. Docker

#### Install
1. Start Docker Daemon.
2. Add this folder to File Sharing.
3. Run ```sh start.sh```.
4. Access application in ```http://localhost:8080```.

Without Docker
-------------------------

#### Requirements

1. PHP
1. Mysql
3. Apache

#### Install
1. Make the front folder web accessible.
2. Update the file ```api/src/config.php``` with the correct database information.
3. Import the dump file ```database/dump.sql```

Improvements
-------------------------
1. The ```model.php``` shoud only contain database functions, moved the create function to the app or controller.
2. This couldbe more organize if it is possible to use small libraries like flight.
3. The Api only have one route trying to emulate a REST API.
4. Impement test with PHPUnit.