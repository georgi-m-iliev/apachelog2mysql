# apachelog2mysql

apachelog2mysql is a python script (and more) to upload apache logs to MySQL DB and identify the IP addresses, then visualise them on a beatiful map.

## Installation

1. Create the required database, user and tables.
```mysql
CREATE DATABASE ip_track;
USE ip_tack;
CREATE TABLE `test_ip_tack`.`geolocation` ( `id` INT NOT NULL AUTO_INCREMENT , `ip` TINYTEXT NOT NULL , `country_code` TINYTEXT NOT NULL , `country_name` TINYTEXT NOT NULL , `city` TEXT NOT NULL , `latitude` TEXT NOT NULL , `longitude` TEXT NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
CREATE TABLE `test_ip_tack`.`logs` ( `id` INT NOT NULL AUTO_INCREMENT , `server_name` TEXT NOT NULL , `ip` TINYTEXT NOT NULL , `user` TINYTEXT NOT NULL , `date` DATETIME NOT NULL , `request` TEXT NOT NULL , `request_url` TEXT NOT NULL , `response` TINYTEXT NOT NULL , `size` TINYTEXT NOT NULL , `request_referer_url` TEXT NOT NULL , `request_user_agent` TEXT NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
```
2. Copy the services from the services folder to /lib/systemd/system (or wherever you would like).
3. Put the python and shell scripts in /usr/share/apachelog2mysql (you can change the folder name and place by editing the services)
4. You need to create a [combined log from all apache VHosts](https://httpd.apache.org/docs/2.4/logs.html)
5. Edit the file python script:
```python
file_path = "PATH TO APACHE LOG FILE"

mydb = mysql.connector.connect(
    host="localhost",
    user="",
    password="",
	database=""
)
```
and PHP file:
```php
$servername = "";
$username = "";
$password = "";
$dbname = "";
```
to reflect your setup.
6. Enable the services
7. Place the ip-track folder on your web server

## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License
[GNU GPL v3](https://en.wikipedia.org/wiki/GNU_General_Public_License)