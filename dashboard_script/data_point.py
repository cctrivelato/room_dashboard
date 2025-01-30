"""
Class designed to hold a Vision System parameters.
Developed by Caique Trivelato
Version 1.0 
Date: 01/29/2025
"""

import datetime
from typing import List, Dict, Any

class DataPoint:
    """Represents a single data point from a table"""
    
    def __init__(self, value: Any, timestamp: datetime.datetime, 
                 metric_name: str, table_name: str):
        self.value = value
        self.timestamp = timestamp
        self.metric_name = metric_name
        self.table_name = table_name
        
    def to_dict(self) -> Dict[str, Any]:
        return {
            'value': self.value,
            'timestamp': self.timestamp.isoformat(),
            'metric_name': self.metric_name,
            'table_name': self.table_name
        }