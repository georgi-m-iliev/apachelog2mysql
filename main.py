#!/usr/bin/python3
import time
from datetime import datetime, timedelta
from watchdog.observers import Observer
from watchdog.events import FileSystemEventHandler

import linecache
import mysql.connector
import apache_log_parser
import requests

import logging
from systemdlogging.toolbox import init_systemd_logging

init_systemd_logging()  # Returns True if initialization went fine.
logger = logging.getLogger(__name__)
logger.setLevel(logging.DEBUG)
logger.debug("Debugging...")

# this variable the last line that has been read from the file
last_line = 0

class MyHandler(FileSystemEventHandler):

    def __init__(self):
        self.last_modified = datetime.now()

    def on_modified(self, event):
        if datetime.now() - self.last_modified < timedelta(seconds=1):
            return
        else:
            self.last_modified = datetime.now()
        print(f'Event type: {event.event_type}  path : {event.src_path}')

        logger.debug(f'Event type: {event.event_type}  path : {event.src_path}')

        # here is the actual code that does all the work

        # wait a bit just to be sure
        time.sleep(2)

        line_parser = apache_log_parser.make_parser('%v %h %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\"')
        # file_path = "D:\\temp\\log.txt"
        file_path = "PATH TO APACHE LOG FILE"

        logger.debug('Connecting to MySQL database')
        mydb = mysql.connector.connect(
            host="localhost",
            user="ip_tracker",
            password="",
            database="ip_tracker"
        )

        mycursor = mydb.cursor()
        global last_line
        # last_line = 9488
        print("Last line:" + str(last_line))
        logger.debug("Last line was: " + str(last_line))

        logger.debug('Openning log file')
        file = open(file_path)

        # finds the total count of lines in the file
        num_lines = sum(1 for line in file)
        file.close()
        # print("Lines:" + str(num_lines))
        logger.debug('File has ' + str(num_lines) + ' lines')

        # all the entries of the log file
        entries = list()
        # set of IPs fetched from the log
        ip_set = set()
        linecache.clearcache()

        # fetching the data from the log and prepairing it for database
        for i in range(last_line + 1, num_lines + 1):
            line_content = linecache.getline(file_path, i)
            # print("num: " + str(i) + "  " + line_content)
            try:
                log_line_data = line_parser(line_content)
                # print(log_line_data)
                entry = {
                    'server_name': log_line_data['server_name'],
                    'ip': log_line_data['remote_host'],
                    'user': log_line_data['remote_user'],
                    'datetime': log_line_data['time_received_datetimeobj'],
                    'request': log_line_data['request_first_line'],
                    'request_url': log_line_data['request_url'],
                    'response': log_line_data['status'],
                    'size': log_line_data['response_bytes_clf'],
                    'request_referer_url': log_line_data['request_header_referer'],
                    'request_user_agent': log_line_data['request_header_user_agent']
                }
                ip_set.add(log_line_data['remote_host'])
                entries.append(entry)
            except apache_log_parser.LineDoesntMatchException:
                print("FUCKIN REGEX EXCEPTION")
                # print("Line:" + line_content)
                logger.warning('RegEx exception was caught on file line: ' + str(i))
                continue

        # inserting the data to MySQL DB
        for item in entries:
            query = "INSERT INTO `logs` (`id`, `server_name`, `ip`, `user`, `date`, `request`, `request_url`, `response`, `size`, `request_referer_url`, `request_user_agent`) VALUES (NULL, "
            query += "'" + item['server_name'] + "', "
            query += "'" + item['ip'] + "', "
            query += "'" + item['user'] + "', "
            query += "'" + item['datetime'].strftime('%Y-%m-%d %H:%M:%S') + "', "
            query += "'" + item['request'] + "', "
            query += "'" + item['request_url'] + "', "
            query += "'" + item['response'] + "', "
            query += "'" + item['size'] + "', "
            query += "'" + item['request_referer_url'] + "', "
            query += "'" + item['request_user_agent'] + "');"
            # print(query)
            mycursor.execute(query)

        # print(num_lines)
        last_line = num_lines

        # checking all the fetched IPs whether there is already an entry in the geolocation table, where the IP data is kept
        # if there is no data, then make a API request to retrieve the needed information
        # print(ip_set)
        for ip in ip_set:
            mycursor.execute("SELECT * FROM `geolocation` WHERE `ip` = '" + ip + "'")
            if not mycursor.fetchone():
                # print(ip + " is not in db")
                ip_info = requests.get("http://api.ipstack.com/" + ip +
                                       "?access_key=247bace3835006c8f292b1d53bb069ba" +
                                       "&fields=ip,country_code,country_name,city,latitude,longitude").json()

                if list(ip_info.keys())[1] == 'error':
                    # print(ip_info['error']['info'])
                    logger.warning('The IP API has returned an error: ' + ip_info['error']['info'])
                    continue
                # uploads the fetched data to the database
                if ip_info['country_code'] != None:
                    # print(ip_info)
                    query = "INSERT INTO `geolocation` (`id`, `ip`, `country_code`, `country_name`, `city`, `latitude`, `longitude`) VALUES (NULL, "
                    query += "'" + ip_info['ip'] + "', "
                    query += "'" + ip_info['country_code'] + "', "
                    query += "'" + ip_info['country_name'] + "', "
                    query += "'" + ip_info['city'] + "', "
                    query += "'" + str(round(ip_info['latitude'], 4)) + "', "
                    query += "'" + str(round(ip_info['longitude'], 4)) + "');"
                    # print(query)
                    mycursor.execute(query)
            # else:
            # print(ip + " is in db")
        mydb.commit()

        logger.debug('Everything looks good, closing database.')
        mycursor.close()
        mydb.close()


if __name__ == "__main__":
    event_handler = MyHandler()
    observer = Observer()
    # observer.schedule(event_handler, path="D:\\temp", recursive=False)
    observer.schedule(event_handler, path="PATH TO FOLDER WHERE THE APACHE LOG IS", recursive=False)
    observer.start()

    try:
        while True:
            time.sleep(5)
    except KeyboardInterrupt:
        observer.stop()
    observer.join()
