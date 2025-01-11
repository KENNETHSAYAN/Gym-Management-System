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

  // Function to show the QR Code modal with the member's QR code
function showQRCodeModal(member_id, code, name) {
    const qrData = JSON.stringify({
        member_id: member_id,
        name: name,
        generated_code: code,
    });

    // Create the QR code URL dynamically
    const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(qrData)}`;

    // Set the modal title and image source dynamically
    document.getElementById('qrModalTitle').textContent = `QR Code for ${name}`;
    document.getElementById('qrCodeImage').src = qrUrl;

    // Display the modal
    document.getElementById('qrCodeModal').style.display = 'flex';
}

// Function to close the QR Code modal
function closeQRCodeModal() {
    document.getElementById('qrCodeModal').style.display = 'none';
}

// Function to print the QR Code
function printQRCode() {
    const qrCodeImage = document.getElementById("qrCodeImage");

    // Open a new window for printing
    const printWindow = window.open('', '', 'width=400,height=400');
    printWindow.document.write('<html><head><title>Print QR Code</title></head><body>');
    printWindow.document.write('<img src="' + qrCodeImage.src + '" style="max-width: 50%; height: 50%; margin-top: 250px; margin-left: 180px;">');
    printWindow.document.write();
    printWindow.document.close();

    // Wait for the image to load, then print
    printWindow.onload = function() {
        printWindow.print();
        printWindow.close();
    };
}
function downloadQRCode() {
    const qrCodeImage = document.getElementById("qrCodeImage");

    // Check if the image source is valid
    if (!qrCodeImage.src) {
        alert("QR Code is not available.");
        return;
    }

    // Create a new Image object to load the QR code image
    const image = new Image();
    image.crossOrigin = "Anonymous"; // This might help with CORS issues
    image.src = qrCodeImage.src;

    image.onload = function() {
        // Create a canvas element to draw the image
        const canvas = document.createElement("canvas");
        const ctx = canvas.getContext("2d");

        // Set the canvas width and height to the image dimensions
        canvas.width = image.width;
        canvas.height = image.height;

        // Fill the canvas with a white background
        ctx.fillStyle = "white";
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        // Draw the image on the canvas
        ctx.drawImage(image, 0, 0);

        // Convert the canvas to a data URL (base64)
        const dataUrl = canvas.toDataURL("image/png");

        // Create an anchor element for the download
        const downloadLink = document.createElement("a");
        downloadLink.href = dataUrl;
        downloadLink.download = "QRCode.png";  // Name the file to be downloaded

        // Trigger the download
        downloadLink.click();
    };

    // In case of an error loading the image
    image.onerror = function() {
        alert("Failed to load QR Code image.");
    };
}   

function toggleDateInputs() {
    const filter = document.getElementById('filter-dropdown').value;
    const dateJoinedFilters = document.getElementById('date-joined-filters');
    const dateExpiryFilters = document.getElementById('date-expiry-filters');
    // Hide all date range inputs by default
    dateJoinedFilters.style.display = 'none';
    dateExpiryFilters.style.display = 'none';
    // Show relevant date range inputs based on selected filter
    if (filter === 'date_joined') {
        dateJoinedFilters.style.display = 'block';
    } else if (filter === 'date_expiry') {
        dateExpiryFilters.style.display = 'block';
    }
}
// Call toggleDateInputs on page load to set the correct state
document.addEventListener('DOMContentLoaded', toggleDateInputs);
// Call toggleDateInputs on page load to set the correct state
document.addEventListener('DOMContentLoaded', toggleDateInputs);
// Search bar login ni chuy!
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('.search-bar input[name="search"]');
    const tableRows = document.querySelectorAll('#member-table-body tr');
    const noResultsRow = document.getElementById('no-results');
    searchInput.addEventListener('input', function() {
        const query = searchInput.value.toLowerCase();
        let hasVisibleRows = false;
        tableRows.forEach(row => {
            const fullNameCell = row.querySelector('td:nth-child(2)'); // Full name column
            const fullName = row.getAttribute('data-fullname') || ''; // Ensure no null values
            if (fullName.includes(query)) {
                row.style.display = ''; // Show the row
                hasVisibleRows = true;

                // Highlight the matched portion
                const regex = new RegExp(`(${query})`, 'gi');
                const originalText = fullNameCell.textContent;
                fullNameCell.innerHTML = originalText.replace(regex, '<span class="highlight">$1</span>');
            } else {
                row.style.display = 'none'; // Hide the row
                fullNameCell.innerHTML = fullNameCell.textContent; // Remove previous highlights
            }
        });

        // Show or hide "No results" row
        if (noResultsRow) {
            noResultsRow.style.display = hasVisibleRows ? 'none' : '';
        }
    });
});
function printTable() {
const tableContainer = document.querySelector('.table-container').innerHTML; // Extract table content
const originalContent = document.body.innerHTML;
document.body.innerHTML = `
<div style="padding: 20px;">
    ${tableContainer}
</div>
`;
window.print();
document.body.innerHTML = originalContent;
window.location.reload();
}
function downloadPDF() {
const { jsPDF } = window.jspdf;
// Initialize jsPDF
const pdf = new jsPDF();
// Table Header and Data
const table = document.querySelector('table'); // Get the table
const rows = Array.from(table.querySelectorAll('tr')).map(row => {
return Array.from(row.querySelectorAll('th, td')).slice(0, -3).map(cell => cell.textContent.trim());
});
// Format table using autoTable plugin
pdf.autoTable({
head: [rows[0]], // Use the first row as the header
body: rows.slice(1), // Remaining rows are the body
theme: 'grid',
styles: {
    fontSize: 10, // Adjust font size for better alignment
    cellPadding: 5, // Add padding for readability
},
});
// Save the PDF
pdf.save('Brew+Flex-MembersData- .pdf');
}