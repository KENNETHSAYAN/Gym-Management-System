let scanner;

// Function to open QR Scanner modal
function openQRScanner() {
    document.getElementById('qrScannerModal').style.display = 'flex';
    startScanner();
}
// Function to close QR Scanner modal
function closeQRScanner() {
    document.getElementById('qrScannerModal').style.display = 'none';
    if (scanner) {
        scanner.stop();
    }
}

// Function to start QR Scanner
function startScanner() {
    scanner = new Instascan.Scanner({ video: document.getElementById('interactive') });

    scanner.addListener('scan', function (content) {
        // Process scanned QR Code
        markAttendanceWithQR(content);
        scanner.stop();
        closeQRScanner();
    });

    Instascan.Camera.getCameras()
        .then(function (cameras) {
            if (cameras.length > 0) {
                scanner.start(cameras[0]);
            } else {
                alert('No cameras found.');
            }
        })
        .catch(function (e) {
            alert('Camera access error: ' + e);
            console.error('camera error', e);
        });
}

// Function to show a modal for success or error messages
function showModal(message, type) {
    const existingModal = document.getElementById('messageModal');
    if (existingModal) {
        existingModal.remove();
    }

    const modal = document.createElement('div');
    modal.id = 'messageModal';
    modal.className = 'modal';

    const modalContent = document.createElement('div');
    modalContent.className = 'modal-content';

    const icon = document.createElement('i');
    icon.className = type === 'success' ? 'fas fa-check-circle success-icon' : 'fas fa-times-circle error-icon';

    const messageText = document.createElement('p');
    messageText.textContent = message;
    messageText.className = type === 'success' ? 'success-message' : 'error-message';

    const okButton = document.createElement('button');
    okButton.textContent = 'OK';
    okButton.className = 'modal-ok-btn';
    okButton.onclick = function () {
        if (type === 'success') {
            location.reload();
        } else {
            modal.style.display = 'none';
        }
    };

    modalContent.appendChild(icon);
    modalContent.appendChild(messageText);
    modalContent.appendChild(okButton);
    modal.appendChild(modalContent);
    document.body.appendChild(modal);

    modal.style.display = 'flex';
}

// Function to mark attendance with QR code
function markAttendanceWithQR(content) {
    const successBeepSound = document.getElementById('successBeepSound');
    const thankyouBeepSound = document.getElementById('thankyouBeepSound');
    const errorBeepSound = document.getElementById('errorBeepSound');

    try {
        const qrData = JSON.parse(content);
        const member_id = qrData.member_id;

        if (!member_id) {
            errorBeepSound.play();
            showModal("Invalid QR Code: Member ID missing.", 'error');
            return;
        }

        const formData = new FormData();
        formData.append('member_id', member_id);

        fetch('attendance.php', {
            method: 'POST',
            body: formData,
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    successBeepSound.play();
                    thankyouBeepSound.play();
                    showModal(data.message, 'success');
                } else {
                    errorBeepSound.play();
                    showModal(data.message, 'error');
                }
            })
            .catch(err => {
                errorBeepSound.play();
                showModal("An error occurred while processing the request.", 'error');
                console.error(err);
            });
    } catch (error) {
        errorBeepSound.play();
        showModal("Invalid QR Code format.", 'error');
        console.error(error);
    }
}

// Function to mark attendance
function markAttendance() {
    const memberId = document.getElementById('selectMember').value;

    if (memberId) {
        const formData = new FormData();
        formData.append('member_id', memberId);

        fetch('attendance.php', {
            method: 'POST',
            body: formData,
        })
            .then(response => response.json())
            .then(data => {
                showModal(data.message, data.status === 'success' ? 'success' : 'error');
            })
            .catch(err => {
                showModal("An error occurred while processing the request.", 'error');
                console.error(err);
            });
    } else {
        showModal("Please select a valid member.", 'error');
    }
}


// Function to dynamically add a row to the table
function addRowToTable(record) {
    const table = document.getElementById('attendanceTable');
    const row = table.insertRow();
    row.innerHTML = `
        <td>${record.member_id}</td>
        <td>${record.first_name} ${record.last_name}</td>
        <td>${record.check_in_date}</td>
    `;
}

function openAttendanceModal() {
    document.getElementById('modalAttendanceSearch').style.display = 'flex';
}

function closeAttendanceModal() {
    document.getElementById('modalAttendanceSearch').style.display = 'none';
}

// Highlight search matches
const searchQuery = "<?php echo htmlspecialchars($search); ?>";
if (searchQuery) {
    const rows = document.querySelectorAll('#attendanceTable tr td:nth-child(2)');
    rows.forEach(cell => {
        const originalText = cell.textContent;
        const regex = new RegExp(`(${searchQuery})`, 'gi');
        cell.innerHTML = originalText.replace(regex, '<span class="highlight">$1</span>');
    });
}

function showLogoutModal() {
    document.getElementById('logoutModal').style.display = 'flex';
}

function closeLogoutModal() {
    document.getElementById('logoutModal').style.display = 'none';
}

function handleLogout() {
    alert("You have been logged out.");
    window.location.href = "/brew+flex/logout.php";
}


// Filter attendance table based on search input
document.getElementById('searchInput').addEventListener('keyup', function () {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('#attendanceTable tr');
    const noResultsRow = document.getElementById('noResultsRow');

    let resultsFound = false;

    rows.forEach(row => {
        if (row.id === 'noResultsRow') return;

        const memberName = row.cells[1].textContent.toLowerCase();

        if (memberName.includes(filter)) {
            row.style.display = "";
            resultsFound = true;
        } else {
            row.style.display = "none";
        }
    });

    if (!resultsFound && filter !== "") {
        if (!noResultsRow) {
            const newRow = document.createElement('tr');
            newRow.id = 'noResultsRow';
            const td = document.createElement('td');
            td.colSpan = 4;
            td.className = 'no-results';
            td.textContent = 'No results found for "' + filter + '"';
            newRow.appendChild(td);
            document.querySelector('#attendanceTable').appendChild(newRow);
        }
    } else {
        if (noResultsRow) {
            noResultsRow.remove();
        }
    }
});
