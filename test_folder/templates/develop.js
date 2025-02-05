        fetch('/data')
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('metricsTableBody');
                let count_table = 0;
                let count_metric
                for (let table in data) {
                    table_array[count_table] = data[table]
                    const tableData = data[table]

                    // Populate table rows with data
                    tableData.forEach(row => {
                        metric[count_metric] = 
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