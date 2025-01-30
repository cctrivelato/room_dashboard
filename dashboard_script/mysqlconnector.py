"""
Class designed to hold mysql connection parameters.

Developed by Caique Trivelato
Version 1.0 
Date: 01/29/2025
"""

import mysql.connector
from typing import Dict, Any

class MySQLConnector:
    """Handles connection to the MySQL database"""
    
    def __init__(self, db_config: Dict[str, Any]):
        try:
            self.connection = mysql.connector.connect(**db_config)
            print("Successfully connected to MySQL database")
        except Exception as e:
            print(f"Error connecting to MySQL database: {str(e)}")
            raise
    
    def get_connection(self):
        if not self.connection.is_connected():
            self.connection.reconnect()
        return self.connection