"""
Class designed to hold a Vision System parameters
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