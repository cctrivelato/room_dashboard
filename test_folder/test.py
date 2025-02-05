from flask import Flask, jsonify
from configparser import ConfigParser
import mysql.connector

app = Flask(__name__)

def read_db_config(filename='dbconfig.ini', section = 'database'):
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

@app.route('/data', methods=['GET'])
def get_data():
    db = mysql.connector.connect(**db_config)
    cursor = db.cursor(dictionary=True)

    tables = [
            'sfvis01', 'sfvis02', 'sfvis03',
            'sfvis04', 'sfvis05', 'sfvis06',
            'sfvis07', 'sfvis08', 'sfvis09',
            'sfvis10', 'sfvis11', 'sfvis12',
            'sfvis13', 'sfvis14', 'sfvis15'
        ]
        
    result = {}

    for table in tables:
        cursor.execute(f"SELECT Timestamp, Workstation_Camera, Vision_System, Old_Status, New_Status, People_Count, Frame_Rate, Presence_Change_Total, Presence_Change_Rate FROM {table} ORDER BY Timestamp DESC LIMIT 1")
        result[table] = cursor.fetchall()

    return jsonify(result)    

if __name__ == '__main__':
    app.run(host = '0.0.0.0', debug=True)