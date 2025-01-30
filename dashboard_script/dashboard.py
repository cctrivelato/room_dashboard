"""
Loop working with gathering of data from database, analyzing, calculating, 
and updating collective data.

Developed by Caique Trivelato
Version 1.0 
Date: 01/29/2025
"""

from configparser import ConfigParser
from multi_table_service import MultiTableService

def read_db_config(filename='C:/Users/ctrivelato/Documents/GitHub/room_dashboard/dashboard_script/dbconfig.ini', section='database'):
    parser = ConfigParser()
    parser.read(filename)
    db = {}
    if parser.has_section(section):
        items = parser.items(section)
        for item in items:
            db[item[0]] = item[1]
    else:
        raise Exception(f'Section {section} not found in {filename}')
    return db

db_config = read_db_config()
    
service = MultiTableService(
        db_config=db_config,
        update_url='http://localhost/data_handler.php'
    )

service.run()