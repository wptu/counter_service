// Simple client-side filtering and interactions

document.addEventListener('DOMContentLoaded', function() {
    // Staff filter
    const staffFilter = document.getElementById('staff-filter');
    const typeFilter = document.getElementById('type-filter');
    
    if (staffFilter) {
        staffFilter.addEventListener('change', filterStaffDetails);
    }
    
    if (typeFilter) {
        typeFilter.addEventListener('change', filterStaffDetails);
    }
    
    // Table row highlighting
    const tableRows = document.querySelectorAll('table tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('click', function() {
            this.style.backgroundColor = this.style.backgroundColor === 'rgb(255, 248, 225)' ? '' : '#fff8e1';
        });
    });
});

function filterStaffDetails() {
    const staffFilter = document.getElementById('staff-filter');
    const typeFilter = document.getElementById('type-filter');
    const rows = document.querySelectorAll('#staff-details-table tbody tr');
    
    const selectedStaff = staffFilter ? staffFilter.value : '';
    const selectedType = typeFilter ? typeFilter.value : '';
    
    rows.forEach(row => {
        const staffCell = row.cells[1].textContent; // Staff code/name column
        const typeCell = row.cells[3].textContent;  // Type column
        
        let showRow = true;
        
        if (selectedStaff && !staffCell.includes(selectedStaff)) {
            showRow = false;
        }
        
        if (selectedType && selectedType !== 'ทั้งหมด') {
            if (selectedType === 'ทพ.' && !typeCell.includes('ทพ.')) {
                showRow = false;
            } else if (selectedType === 'รส.' && !typeCell.includes('รส.')) {
                showRow = false;
            }
        }
        
        row.style.display = showRow ? '' : 'none';
    });
}

// Print function
function printSchedule() {
    window.print();
}
