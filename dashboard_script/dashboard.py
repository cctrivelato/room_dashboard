"""
Loop working with gathering of data from database, analyzing, calculating, 
and updating collective data.

Developed by Caique Trivelato
Version 1.0 
Date: 01/29/2025
"""

from multi_table_service import MultiTableService

db_config = {
        'host': 'localhost',
        'user': 'your_username',
        'password': 'your_password',
        'database': 'your_database_name'
    }
    
service = MultiTableService(
        db_config=db_config,
        update_url='http://localhost/data_handler.php'
    )

service.run()