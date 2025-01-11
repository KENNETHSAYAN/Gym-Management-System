 // Function to print the QR Code
 function printQRCode() {
    const qrCodeImage = document.getElementById("qrCodeImage");

    // Open a new window for printing
    const printWindow = window.open('', '', 'width=400,height=400');
    printWindow.document.write('<html><head><title>Print QR Code</title></head><body>');
    printWindow.document.write('<img src="' + qrCodeImage.src + '" style="max-width: 50%; height: 50%; margin-top: 250px; margin-left: 180px;">');
    printWindow.document.write('</body></html>');
    printWindow.document.close();

    // Wait for the image to load, then print
    printWindow.onload = function() {
        printWindow.print();
        printWindow.close();
    };
}

// Function to download the QR Code
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