from flask import Flask, jsonify, render_template
from configparser import ConfigParser
import pymysql

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

@app.route('/')
def index():
    return render_template('test.html')

@app.route('/data', methods=['GET'])
def get_data():
    db = pymysql.connect(**db_config)
    cursor = db.cursor(pymysql.cursors.DictCursor)

    tables = [
            'sfvis_cam1', 'sfvis_cam2', 'sfvis_cam3',
            'sfvis_cam4', 'sfvis_cam5', 'sfvis_cam6',
            'sfvis_cam7', 'sfvis_cam8', 'sfvis_cam9',
            'sfvis_cam10', 'sfvis_cam11', 'sfvis_cam12',
            'sfvis_cam13', 'sfvis_cam14', 'sfvis_cam15'
        ]
        
    result = {}

    for table in tables:
        cursor.execute(f"SELECT Timestamp, Workstation_Camera, Vision_System, Old_Status, New_Status, People_Count, Frame_Rate, Presence_Change_Total, Presence_Change_Rate FROM {table} ORDER BY Timestamp DESC LIMIT 1")
        result[table] = cursor.fetchall()

    return jsonify(result)    

if __name__ == '__main__':
    app.run(host = '0.0.0.0', debug=True)