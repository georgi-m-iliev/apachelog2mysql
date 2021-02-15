import linecache
import mysql.connector
import apache_log_parser
import requests

line_parser = apache_log_parser.make_parser("%h %l %u %t \"%r\" %>s %b")
file_path = "PATH_TO_FILE"

mydb = mysql.connector.connect(
    host="localhost",
    user="ip_tracker",
    password="",
    database="ip_tracker"
)

mycursor = mydb.cursor()

mycursor.execute("SELECT line FROM temp")
last_line = mycursor.fetchone()[0]
# last_line = 9488
print("Last line:"+str(last_line))

file = open(file_path)
num_lines = sum(1 for line in file)
file.close()
print("Lines:"+str(num_lines))

entries = list()
ip_set = set()
linecache.clearcache()

for i in range(last_line + 1, num_lines + 1):
    line_content = linecache.getline(file_path, i)
    print("num: " + str(i) + "  " + line_content)
    try:
        log_line_data = line_parser(line_content)
        entry = {
            'ip': log_line_data['remote_host'],
            'user': log_line_data['remote_user'],
            'datetime': log_line_data['time_received_datetimeobj'],
            'request': log_line_data['request_first_line'],
            'request_url': log_line_data['request_url'],
            'response': log_line_data['status'],
            'size': log_line_data['response_bytes_clf']
        }
        ip_set.add(log_line_data['remote_host'])
        entries.append(entry)
    except apache_log_parser.LineDoesntMatchException:
        print("FUCKIN REGEX EXCEPTION")
        print("Line:" + line_content)


for item in entries:
    query = "INSERT INTO `logs` (`id`, `ip`, `user`, `date`, `request`, `request_url`, `response`, `size`) VALUES (NULL, "
    query += "'" + item['ip'] + "', "
    query += "'" + item['user'] + "', "
    query += "'" + item['datetime'].strftime('%Y-%m-%d %H:%M:%S') + "', "
    query += "'" + item['request'] + "', "
    query += "'" + item['request_url'] + "', "
    query += "'" + item['response'] + "', "
    query += "'" + item['size'] + "');"
    # print(query)
    mycursor.execute(query)

# print(num_lines)
mycursor.execute("UPDATE `temp` SET `line` = '" + str(num_lines) + "' WHERE `temp`.`id` = 1;")
mydb.commit()

# print(ip_set)
for ip in ip_set:
    mycursor.execute("SELECT * FROM `geolocation` WHERE `ip` = '" + ip + "'")
    if not mycursor.fetchone():
        # print(ip + " is not in db")
        ip_info = requests.get("http://api.ipstack.com/" + ip +
                               "?access_key=" + "API_KEY" +
                               "&fields=ip,country_code,country_name,city,latitude,longitude").json()
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

mycursor.close()
mydb.close()