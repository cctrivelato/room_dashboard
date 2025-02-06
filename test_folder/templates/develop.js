import React, {} from 'react'

        class Table extends React.Component{
            constructor(timestamp, camera, sfvis, old_status, new_status, people_count, frame_rate, presence_change_total, presence_change_rate){
                this.timestamp = timestamp;
                this.camera = camera;
                this.sfvis = sfvis;
                this.old_status = old_status;
                this.new_status = new_status;
                this.people_count = people_count;
                this.frame_rate = frame_rate;
                this.presence_change_total = presence_change_total;
                this.presence_change_rate = presence_change_rate;
            }
        }

        const tables = {};

        fetch('/data')
            .then(response => response.json())
            .then(data => {
                let count_table = 0;

                for (let table in data) {
                    tables[count_table] = new Table ();
                    const tableData = data[table];
                    count_table = count_table + 1;
                    
                    // Populate table rows with data
                    tableData.forEach(row => {
                        tables[count_table].timestamp = row.Timestamp;
                        tables[count_table].camera = row.Workstation_Camera
                        tables[count_table].sfvis = row.Vision_System
                        tables[count_table].old_status = row.Old_Status
                        tables[count_table].new_status = row.New_Status
                        tables[count_table].people_count = row.People_Count
                        tables[count_table].frame_rate = row.Frame_Rate
                        tables[count_table].presence_change_total = row.Presence_Change_Total
                        tables[count_table].presence_change_rate = row.Presence_Change_Rate
                    });
                }
            })
            .catch(error => {
                console.error('Error fetching data:', error);
            });



            Last_good_testing: // Fetch JSON data from Flask backend
            fetch('/data')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('metricsTableBody');
                    for (let table in data) {
                        const tableData = data[table];
                        const tableElement = document.createElement('table');
                        const tableHeader = document.createElement('thead');
                        const tableBody = document.createElement('tbody');
    
                        // Create table header
                        let headerRow = document.createElement('tr');
                        headerRow.innerHTML = `<th>Timestamp</th><th>Workstation Camera</th><th>Vision System</th><th>Old Status</th><th>New Status</th><th>People Count</th><th>Frame Rate</th><th>Presence Change - Total</th><th>Presence Change - Rate</th>`;
                        tableHeader.appendChild(headerRow);
    
                        // Populate table rows with data
                        tableData.forEach(row => {
                            let rowElement = document.createElement('tr');
                            rowElement.innerHTML = `<td>${row.Timestamp}</td><td>${row.Workstation_Camera}</td><td>${row.Vision_System}</td><td>${row.Old_Status}</td><td>${row.New_Status}</td><td>${row.People_Count}</td><td>${row.Frame_Rate}</td><td>${row.Presence_Change_Total}</td><td>${row.Presence_Change_Rate}</td></tr>`;
                            tableBody.appendChild(rowElement);
                        });
    
                        // Append header and body to the table
                        tableElement.appendChild(tableHeader);
                        tableElement.appendChild(tableBody);
    
                        // Add a title for each table
                        const tableTitle = document.createElement('h2');
                        tableTitle.innerText = table;
                        container.appendChild(tableTitle);
    
                        // Append the table to the container
                        container.appendChild(tableElement);
                    }
                })
                .catch(error => {
                    console.error('Error fetching data:', error);
                });