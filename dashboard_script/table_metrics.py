"""
Class designed to hold a Vision System parameters.
Developed by Caique Trivelato
Version 1.0 
Date: 01/29/2025
"""

from typing import List, Dict, Any
from data_point import DataPoint

class TableMetrics:
    """Represents metrics collected from a single table"""
    
    def __init__(self, table_name: str):
        self.table_name = table_name
        self.metrics: List[DataPoint] = []
    
    def to_dict(self) -> Dict[str, Any]:
        return {
            'table_name': self.table_name,
            'metrics': [metric.to_dict() for metric in self.metrics]
        }