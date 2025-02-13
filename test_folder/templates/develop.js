function updateRandomData(metric) {
    let data_chosen = 0;
    let count = 0;
    const randomIndex = Math.floor(Math.random() * table_amount);

    for (let tableKey in tables) {
        const selectedTable = tables[tableKey];
        selectedTable.forEach(row => {                    
            data_chosen = row[metric];
            count++;
            console.log(data_chosen);
        });
    }
    return data_chosen;
}

for (let i = 0; i < table_index; i++) {
    switch(metric){
        case 'people_count':
            selectedTable = parseFloat(tables[i].people_count);
            break;
        
        case 'frame_rate':
            selectedTable = parseFloat(tables[i].frame_rate);
            break;

        case 'presence_change_total':
            selectedTable = parseFloat(tables[i].presence_change_total);
            break;

        case 'presence_change_rate':
            selectedTable = parseFloat(tables[i].presence_change_rate);
            break;
        
    }
    total += selectedTable;
    count++;
}
console.log(count, total);


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



