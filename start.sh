# Start containers
docker-compose up --build -d
# Wait For mysql to be up
until docker exec -i mysql mysql -u superuser --password=superuser app  -e ";" ; do
  sleep 1
done
# Drop existing database.
docker exec -i mysql mysql -u superuser --password=superuser app -e "drop database app; create database app"
# Import database dump.
docker exec -i mysql mysql -u superuser --password=superuser app < database/dump.sql

