<?php
session_start();
require_once 'db_connection.php';

// Check if user is logged in
if (!isset($_SESSION["username"])) {
    header("Location: /brew+flex/auth/login.php");
    exit;
}

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Enable exception mode for errors
} catch (PDOException $e) {
    // Catch connection errors
    echo "Connection failed: " . $e->getMessage();
    die();
}

// Check if the required parameters are present
if (!isset($_GET['qr_code']) || !isset($_GET['member_id']) || !isset($_GET['name'])) {
    echo "Invalid access. Missing data.";
    exit;
}

// Sanitize GET parameters once
$member_id = htmlspecialchars($_GET['member_id']);
$name = htmlspecialchars($_GET['name']);
$qr_code = htmlspecialchars($_GET['qr_code']);

// Optional: Assuming 'generated_code' exists in the URL as well
$generated_code = isset($_GET['generated_code']) ? htmlspecialchars($_GET['generated_code']) : '';

// SQL query to fetch generated_code for the given member_id (if needed)
$sql = "SELECT generated_code FROM members WHERE member_id = :member_id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

// If 'generated_code' is not passed in the URL, use the one fetched from the database
if (empty($generated_code) && $result) {
    $generated_code = $result['generated_code'];
}

$qr_data = array(
    'member_id' => $member_id,
    'name' => $name,
    'generated_code' => $generated_code
);

// Convert the data into a JSON string
$qr_data_json = json_encode($qr_data);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Successfully Added - Brew + Flex Gym</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="/brew+flex/css/membersuccessadded.css">
</head>
<body>
    <div class="container">
        <main class="main-content">
            <div class="message-box">
                <div class="message">
                    <div class="icon-circle">
                        <i class="fas fa-user-check" style="color: #71d4fc;"></i>
                    </div>
                    <h1>Successfully Added</h1>
                    <p>The data has been successfully added to the system.</p>
                    
                    <div class="qr-code-container">
                        <img id="qrCodeImage" src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo urlencode($qr_data_json); ?>" alt="QR Code for <?php echo $name; ?>">
                    </div>
                    <!-- Action buttons for QR Code -->
                    <div class="qr-action-buttons">
                        <button onclick="printQRCode()" class="qr-btn">Print QR </button>
                        <button onclick="downloadQRCode()" class="qr-btn">Download QR </button>
                    </div>
                    <button class="home-btn" onclick="window.location.href='dashboard.php'">
    <i class="fas fa-home"></i> Home
</button>
                </div>
            </div>
        </main>
    </div>
    <script src="/brew+flex/js/successfullyadded.js"></script>
</body>
</html>
