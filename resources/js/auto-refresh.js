console.log("Auto Refresh Script Started");

function refreshAttendance() {
    console.log("Refreshing attendance data...");

    fetch('/face-attendance/latest')
        .then(response => response.json())
        .then(data => {
            console.log("Data received:", data);

            if (data.success && data.data) {
                updatePresentTable(data.data);
                updateHistoryTable(data.data);
                updateTotalPresent(data.data);

                // Show notification on new record
                if (data.data.length > (window.lastCount || 0)) {
                    showNotification('New attendance recorded!');
                }
                window.lastCount = data.data.length;
            }
        })
        .catch(error => console.error("Fetch error:", error));
}

function updatePresentTable(data) {
    const tbody = document.getElementById('present-tbody');
    if (!tbody) {
        console.log("Present table not found");
        return;
    }

    // Filter employees with check_in
    const present = data.filter(r => r.check_in !== '--');

    if (present.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align: center;">No attendance records for today</td></tr>';
        return;
    }

    tbody.innerHTML = '';
    present.forEach(record => {
        const row = `<tr>
            <td style="padding: 12px;">${record.employee_name}</td>
            <td style="padding: 12px;">${record.check_in}</td>
            <td style="padding: 12px;">${record.check_out}</td>
            <td style="padding: 12px;">${record.work_hours}</td>
            <td style="padding: 12px;"><span class="badge" style="background: #dcfce7; color: #166534; padding: 4px 10px;">${record.status}</span></td>
        </tr>`;
        tbody.insertAdjacentHTML('beforeend', row);
    });
}

function updateHistoryTable(data) {
    const tbody = document.getElementById('history-tbody');
    if (!tbody) {
        console.log("History table not found");
        return;
    }

    tbody.innerHTML = '';
    data.forEach(record => {
        const row = `<tr>
            <td style="padding: 12px;">${record.employee_name}</td>
            <td style="padding: 12px;">${record.date}</td>
            <td style="padding: 12px;">${record.check_in}</td>
            <td style="padding: 12px;">${record.check_out}</td>
        </tr>`;
        tbody.insertAdjacentHTML('beforeend', row);
    });
}

function updateTotalPresent(data) {
    const totalSpan = document.getElementById('total-present');
    if (totalSpan) {
        const count = data.filter(r => r.check_in !== '--').length;
        totalSpan.innerText = count;
    }
}

function showNotification(message) {
    const notification = document.createElement('div');
    notification.innerHTML = message;
    notification.style.cssText = 'position: fixed; top: 20px; right: 20px; background: green; color: white; padding: 10px 20px; border-radius: 5px; z-index: 9999;';
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 3000);
}

// Run immediately
refreshAttendance();

// Run every 5 seconds
setInterval(refreshAttendance, 5000);
