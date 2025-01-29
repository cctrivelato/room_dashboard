"""
Class designed to hold a Vision System parameters.
Developed by Caique Trivelato
Version 1.0 
Date: 01/29/2025
"""

class SFVis:

    def __init__(self):
        self.functioning
        self.workstation
        self.sfvis
        self.status
        self.people_count
        self.presence_change

    def add_database_connection(self, db_name, connection_param):
        try:
            self.connections[db_name] = connection_param
            self.data_sources[db_name] = {}
            return True
        except Exception as e:
            print(f"Error adding database connection {db_name}: {str(e)}")
            return False
        
    def collect_data(self, db_name, query, parameters=None):
        if db_name not in self.connections:
            raise ValueError(f"Database {db_name} not found in connections")
        
        try:
            data = {"query": query, "parameters": parameters}
            self.data_sources[db_name] = data
            return data
        except Exception as e:
            print(f"Error collecting data from {db_name}: {str(e)}")
            return None
    
    def add_(self, param_name, param_value, db_name=None):
        