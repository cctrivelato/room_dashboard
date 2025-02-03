import mysql.connector
import json

class DataTable:
    def __init__(self, connection, table_name):
        self.connection = connection
        self.table_name = table_name
        self.data_points = {}

    def retrieve_data_points(self):
        """Retrieve 10 specific data points from the table"""
        cursor = self.connection.cursor(dictionary=True)
        query = f"""
        SELECT 
            Timestamp, Workstation_Camera, Vision_System, Old_Status, Period_Status_Last,
            New_Status, People_Count, Frame_Rate, Presence_Change_Total, Presence_Change_Rate
        FROM {self.table_name}
        LIMIT 1
        """
        cursor.execute(query)
        result = cursor.fetchone()
        
        if result:
            self.data_points = {
                'point1': result['Timestamp'],
                'point2': result['Workstation_Camera'],
                'point3': result['Vision_System'],
                'point4': result['Old_Status'],
                'point5': result['Period_Status_Last'],
                'point6': result['New_Status'],
                'point7': result['People_Count'],
                'point8': result['Frame_Rate'],
                'point9': result['Presence_Change_Total'],
                'point10': result['Presence_Change_Rate']
            }
        
        cursor.close()
        return self.data_points

class DatabaseManager:
    def __init__(self, host, user, password, database):
        try:
            self.connection = mysql.connector.connect(
                host=host,
                user=user,
                password=password,
                database=database
            )
            self.tables = [
                'sfvis_cam01', 'sfvis_cam02', 'sfvis_cam03', 'sfvis_cam04', 'sfvis_cam05',
                'sfvis_cam06', 'sfvis_cam07', 'sfvis_cam08', 'sfvis_cam09', 'sfvis_cam10',
                'sfvis_cam11', 'sfvis_cam12', 'sfvis_cam13', 'sfvis_cam14', 'sfvis_cam15'
            ]
            self.data_collections = {}

        except mysql.connector.Error as err:
            raise Exception(f"Failed to connect: {err}")

    def __del__(self):
        if hasattr(self, 'connection') and self.connection.is_connected():
            self.connection.close()

    def collect_data(self):
        """Collect data from all 15 tables"""
        for table_name in self.tables:
            table_obj = DataTable(self.connection, table_name)
            self.data_collections[table_name] = table_obj.retrieve_data_points()
        
        return self.data_collections

    def save_to_json(self, filename='C:/xampp/htdocs/database_data.json'):
        """Save collected data to JSON file"""
        with open(filename, 'w') as f:
            json.dump(self.data_collections, f, indent=4)

def main():
    # Database connection parameters - replace with your actual credentials
    db_manager = DatabaseManager(
        host='sfmysql02.sf.local', 
        user='admin', 
        password='CEll6505563!', 
        database='test'
    )
    
    while True:
        # Collect and save data
        collected_data = db_manager.collect_data()
        db_manager.save_to_json()

if __name__ == '__main__':
    main()