"""
Class designed to hold a Vision System parameters.
Developed by Caique Trivelato
Version 1.0 
Date: 01/29/2025
"""

import time
import requests
import time
import datetime
from table_metrics import TableMetrics
from data_point import DataPoint
from mysqlconnector import MySQLConnector
from typing import List, Dict, Any


class MultiTableService:
    """Main service class for collecting and publishing table metrics"""
    
    def __init__(self, db_config: Dict[str, Any], update_url: str):
        self.db_connector = MySQLConnector(db_config)
        self.table_metrics: List[TableMetrics] = []
        self.update_url = update_url
        self.tables = [
            'sfvis01',
            'sfvis02',
            'sfvis03',
            'sfvis04',
            'sfvis05',
            'sfvis06',
            'sfvis07',
            'sfvis08',
            'sfvis09',
            'sfvis10',
            'sfvis11',
            'sfvis12',
            'sfvis13',
            'sfvis14',
            'sfvis15'
        ]
        
    def setup_metrics_containers(self):
        """Initialize metrics containers for each table"""
        for table_name in self.tables:
            self.table_metrics.append(TableMetrics(table_name))
    
    def collect_table_metrics(self, table_name: str) -> List[DataPoint]:
        """Collect metrics from a specific table"""
        metrics = []
        current_time = datetime.datetime.now()
        
        try:
            conn = self.db_connector.get_connection()
            cursor = conn.cursor()
            
            # Collection of different metrics for the table
            metrics_queries = {
                'workstation': f'''
                    SELECT Workstation_Camera
                    FROM test.{table_name}
                    ORDER BY timestamp_column DESC
                    LIMIT 1;
                ''',
                'vision_system': f'''
                    SELECT Vision_System
                    FROM test.{table_name}
                    ORDER BY timestamp_column DESC
                    LIMIT 1;'
                ''',
                'old_status': f' SELECT Old_Status
                    FROM test.{table_name} ORDER BY timestamp_column DESC
                    LIMIT 1;',
                'new_status': f'''
                    SELECT New_Status
                    FROM test.{table_name}
                    ORDER BY timestamp_column DESC
                    LIMIT 1;
                ''',
                'people_count': f'''
                    SELECT People_Count
                    FROM test.{table_name}
                    ORDER BY timestamp_column DESC
                    LIMIT 1;
                ''',
                'frame_rate': f'''
                    SELECT Frame_Rate
                    FROM test.{table_name}
                    ORDER BY timestamp_column DESC
                    LIMIT 1;
                ''',
                'presence_change_total': f'''
                    SELECT Presence_Change_Total
                    FROM test.{table_name}
                    ORDER BY timestamp_column DESC
                    LIMIT 1;'
                ''',
                'presence_change_rate': f'''
                    SELECT Presence_Change_Rate
                    FROM test.{table_name}
                    ORDER BY timestamp_column DESC
                    LIMIT 1;'
                '''
            }
            
            for metric_name, query in metrics_queries.items():
                try:
                    cursor.execute(query)
                    result = cursor.fetchone()
                    if result and result[0] is not None:
                        metrics.append(DataPoint(
                            value=float(result[0]),
                            timestamp=current_time,
                            metric_name=metric_name,
                            table_name=table_name
                        ))
                except Exception as query_error:
                    print(f"Error collecting metric {metric_name} from {table_name}: {str(query_error)}")
                    
            cursor.close()
            
        except Exception as e:
            print(f"Error collecting metrics from table {table_name}: {str(e)}")
            
        return metrics
    
    def update_metrics(self):
        """Collect and update metrics from all tables"""
        for table_metrics in self.table_metrics:
            metrics = self.collect_table_metrics(table_metrics.table_name)
            for metric in metrics:
                table_metrics.add_metric(metric)
    
    def publish_metrics(self):
        """Publish metrics to the update URL"""
        try:
            data = {
                'tables': [table_metrics.to_dict() for table_metrics in self.table_metrics],
                'timestamp': datetime.datetime.now().isoformat()
            }
            response = requests.post(self.update_url, json=data)
            if response.status_code == 200:
                print(f"Metrics published successfully at {datetime.datetime.now()}")
            else:
                print(f"Error publishing metrics: {response.status_code}")
        except Exception as e:
            print(f"Error publishing metrics: {str(e)}")
    
    def run(self, db_config, interval: int = 60):
        """Run the service with specified update interval"""
        print(f"Starting multi-table monitoring service with {interval} second interval...")
        self.setup_metrics_containers()
        
        while True:
            try:
                self.update_metrics()
                self.publish_metrics()
            except Exception as e:
                print(f"Error in main service loop: {str(e)}")
                # Try to reconnect to database
                try:
                    self.db_connector = MySQLConnector(db_config)
                except:
                    print("Failed to reconnect to database")
            
            time.sleep(interval)