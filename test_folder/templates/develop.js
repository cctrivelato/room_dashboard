        class Table {
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
        let average;

        fetch('/data')
            .then(response => response.json())
            .then(data => {
                let table_amount = 0;

                for (let table in data) {
                    tables[table_amount] = new Table ();
                    const tableData = data[table];
                    table_amount = table_amount + 1;
                    
                    // Populate table rows with data
                    tableData.forEach(row => {
                        tables[table_amount].timestamp = row.Timestamp;
                        tables[table_amount].camera = row.Workstation_Camera
                        tables[table_amount].sfvis = row.Vision_System
                        tables[table_amount].old_status = row.Old_Status
                        tables[table_amount].new_status = row.New_Status
                        tables[table_amount].people_count = row.People_Count
                        tables[table_amount].frame_rate = row.Frame_Rate
                        tables[table_amount].presence_change_total = row.Presence_Change_Total
                        tables[table_amount].presence_change_rate = row.Presence_Change_Rate
                    });
                }
            })
            .catch(error => {
                console.error('Error fetching data:', error);
            });

            function calculateAverage(table, metric) {
                let total = 0;

                switch(metric){
                    case 'people_count':
                        for (let i = 0; i < table_amount; i++) {
                            total = total + table[i].people_count;
                        }
                        break;
                    case 'frame_rate':
                        for (let i = 0; i < table_amount; i++) {
                            total = total + table[i].frame_rate;
                        }
                        break;
                    case 'presence_change_total':
                        for (let i = 0; i < table_amount; i++) {
                            total = total + table[i].presence_change_total;
                        }
                        break;
                    case 'presence_change_rate':
                        for (let i = 0; i < table_amount; i++) {
                            total = total + table[i].presence_change_rate;
                        }
                        break;
                    default:
                        total = 0;
                }
                return average = total/table_amount
            }



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