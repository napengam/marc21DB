function sortTable(idobj) {
    'use strict';
    var getValue, compareValues;
    var table, tBody, rows;
    if (typeof idobj === 'string') {
        table = document.getElementById(idobj);
    } else if (typeof idobj === 'object') {
        table = idobj;
    }
    if (table === null || table.querySelector('thead') === null) {
        return;
    }

    //
    // build in default function to extract a value from table cell    
    //
    function defG(cell) {
        return cell.textContent.trim();
    }
    getValue = defG;
    //
    // build in default function to compare values    
    //
    function defC(v1, v2) {
        return v1.localeCompare(v2);
    }
    compareValues = defC;

    function sortCore( col, dir) {

        table.style.visibility = 'hidden';
        tBody = table.tBodies[0];
        rows = Array.from(tBody.rows);
        table.removeChild(tBody);
        rows.sort((a, b) => {
            const cellA = getValue(a.cells[col]);
            const cellB = getValue(b.cells[col]);
            return compareValues(cellA, cellB) * dir;
        });
        let tbody = document.createElement("tbody");
        rows.forEach(row => tbody.appendChild(row));
        table.appendChild(tbody);
        table.style.visibility = '';
        getValue = defG;
        compareValues = defC;
    }
    //
    // overwrites default build in function 
    //
    function setGetValue(funcName) {
        getValue = funcName;
    }
    //
    // overwrites default build in function
    //
    function setCompareValues(funcName) {
        compareValues = funcName;
    }
    return {
        sortCore: sortCore, //(table, col, dir)
        setGetValue: setGetValue, //(funcName)
        setCompareValues: setCompareValues //(funcName)
    };
}