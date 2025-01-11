<?php
session_start();
require_once 'db_connection.php';

// Check if user is logged in
if (!isset($_SESSION["username"])) {
    header("Location: /brew+flex/auth/login.php");
    exit;
}

// Fetch logged-in user information
$username = $_SESSION["username"];
$usertype = $_SESSION["usertype"];

// Fetch user information
$query = "SELECT username, email, profile_picture FROM users WHERE username = ? AND usertype = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $username, $usertype);
$stmt->execute();
$user_info = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch all members for datalist
$members_query = "SELECT member_id, CONCAT(first_name, ' ', last_name) AS full_name FROM members";
$members_result = $conn->query($members_query);
$members = $members_result->fetch_all(MYSQLI_ASSOC);

// Fetch all coaches for dropdown with expertise
$coaches_query = "SELECT coach_id, CONCAT(first_name, ' ', last_name) AS full_name, expertise FROM coaches";
$coaches_result = $conn->query($coaches_query);
$coaches = $coaches_result->fetch_all(MYSQLI_ASSOC);

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Reset payment-related variables to avoid residual values
    $monthly_amount = 0;
    $monthly_plan_payment_date = null;
    $monthly_plan_expiration_date = null;

    $renewal_amount = 0;
    $membership_renewal_payment_date = null;
    $membership_expiration_date = null;

    $locker_amount = 0;
    $locker_payment_date = null;
    $locker_expiration_date = null;

    $coaching_amount = 0;
    $coaching_payment_date = null;

    // Collect data from the form
    $member_id = $_POST['member_id'];
    $coach_id = $_POST['coach_id'] ?? null;

    $coaching_payment_date = $_POST['coaching_payment_date'] ?? null;
    $monthly_plan_payment_date = $_POST['monthly_plan_payment_date'] ?? null;
    $monthly_plan_expiration_date = $_POST['monthly_plan_expiration_date'] ?? null;
    $membership_renewal_payment_date = $_POST['membership_renewal_payment_date'] ?? null;
    $membership_expiration_date = $_POST['renewal_expiry_date'] ?? null;
    $locker_payment_date = $_POST['locker_payment_date'] ?? null;
    $locker_expiration_date = $_POST['locker_expiration_date'] ?? null;

    $coaching_amount = $_POST['coaching_amount'] ?? 0;
    $monthly_amount = $_POST['monthly_amount'] ?? 0;
    $renewal_amount = $_POST['renewal_amount'] ?? 0;
    $locker_amount = $_POST['locker_amount'] ?? 0;

    // Calculate total_amount
    $total_amount = $coaching_amount + $monthly_amount + $renewal_amount + $locker_amount;

    // Validate if the member exists
    $member_check_query = "SELECT member_id FROM members WHERE member_id = ?";
    $member_check_stmt = $conn->prepare($member_check_query);
    $member_check_stmt->bind_param("i", $member_id);
    $member_check_stmt->execute();
    $member_check_result = $member_check_stmt->get_result();

    if ($member_check_result->num_rows === 0) {
        echo "Error: The specified member does not exist.";
        exit;
    }
    $member_check_stmt->close();

    // Check if a payment record already exists for this member
    $check_query = "SELECT * FROM payments WHERE member_id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("i", $member_id);
    $check_stmt->execute();
    $existing_payment = $check_stmt->get_result()->fetch_assoc();
    $check_stmt->close();

    if ($existing_payment) {
        // Merge existing data with new inputs, preserving existing data if no new input is provided
        $coach_id = $coach_id ?? $existing_payment['coach_id'];
        $coaching_payment_date = $coaching_payment_date ?? $existing_payment['coaching_payment_date'];
        $monthly_plan_payment_date = $monthly_plan_payment_date ?? $existing_payment['monthly_plan_payment_date'];
        $monthly_plan_expiration_date = $monthly_plan_expiration_date ?? $existing_payment['monthly_plan_expiration_date'];
        $membership_renewal_payment_date = $membership_renewal_payment_date ?? $existing_payment['membership_renewal_payment_date'];
        $membership_expiration_date = $membership_expiration_date ?? $existing_payment['membership_expiration_date'];
        $locker_payment_date = $locker_payment_date ?? $existing_payment['locker_payment_date'];
        $locker_expiration_date = $locker_expiration_date ?? $existing_payment['locker_expiration_date'];
        $coaching_amount = $coaching_amount ?: $existing_payment['coaching_amount'];
        $monthly_amount = $monthly_amount ?: $existing_payment['monthly_amount'];
        $renewal_amount = $renewal_amount ?: $existing_payment['renewal_amount'];
        $locker_amount = $locker_amount ?: $existing_payment['locker_amount'];
        $total_amount = $coaching_amount + $monthly_amount + $renewal_amount + $locker_amount;

        // Update the existing record
        $update_query = "
            UPDATE payments
            SET 
                coach_id = ?, 
                coaching_payment_date = ?, 
                monthly_plan_payment_date = ?, 
                monthly_plan_expiration_date = ?, 
                membership_renewal_payment_date = ?, 
                membership_expiration_date = ?, 
                locker_payment_date = ?, 
                locker_expiration_date = ?, 
                coaching_amount = ?, 
                monthly_amount = ?, 
                renewal_amount = ?, 
                locker_amount = ?, 
                total_amount = ?
            WHERE member_id = ?
        ";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param(
            "issssssiiiiiii",
            $coach_id, $coaching_payment_date, $monthly_plan_payment_date, $monthly_plan_expiration_date,
            $membership_renewal_payment_date, $membership_expiration_date, $locker_payment_date, $locker_expiration_date,
            $coaching_amount, $monthly_amount, $renewal_amount, $locker_amount, $total_amount, $member_id
        );
    } else {
        // Insert a new record
        $insert_query = "
            INSERT INTO payments (
                member_id, coach_id,
                coaching_payment_date, monthly_plan_payment_date, monthly_plan_expiration_date,
                membership_renewal_payment_date, membership_expiration_date,
                locker_payment_date, locker_expiration_date,
                coaching_amount, monthly_amount, renewal_amount, locker_amount, total_amount
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param(
            "iissssssiiiiii",
            $member_id, $coach_id,
            $coaching_payment_date, $monthly_plan_payment_date, $monthly_plan_expiration_date,
            $membership_renewal_payment_date, $membership_expiration_date, $locker_payment_date, $locker_expiration_date,
            $coaching_amount, $monthly_amount, $renewal_amount, $locker_amount, $total_amount
        );
    }

    // Execute the statement and check if successful
    if ($stmt->execute()) {
        // Prepare log entries for each payment type based on current inputs
        $log_queries = [];

        if (isset($_POST['monthly_amount']) && !empty($_POST['monthly_plan_payment_date'])) {
            $log_queries[] = [
                'member_id' => $member_id,
                'transaction_type' => 'Monthly Plan Payment',
                'payment_date' => $_POST['monthly_plan_payment_date'], // Current input
                'payment_amount' => (float) $_POST['monthly_amount'], // Current input
                'plan_type' => 'Monthly Plan'
            ];
        }

        if (isset($_POST['renewal_amount']) && !empty($_POST['membership_renewal_payment_date'])) {
            $log_queries[] = [
                'member_id' => $member_id,
                'transaction_type' => 'Membership Renewal Payment',
                'payment_date' => $_POST['membership_renewal_payment_date'], // Current input
                'payment_amount' => (float) $_POST['renewal_amount'], // Current input
                'plan_type' => 'Membership Plan'
            ];
        }

        if (isset($_POST['locker_amount']) && !empty($_POST['locker_payment_date'])) {
            $log_queries[] = [
                'member_id' => $member_id,
                'transaction_type' => 'Locker Payment',
                'payment_date' => $_POST['locker_payment_date'], // Current input
                'payment_amount' => (float) $_POST['locker_amount'], // Current input
                'plan_type' => 'Locker'
            ];
        }

        if (isset($_POST['coaching_amount']) && !empty($_POST['coaching_payment_date'])) {
            $log_queries[] = [
                'member_id' => $member_id,
                'transaction_type' => 'Coaching Session Payment',
                'payment_date' => $_POST['coaching_payment_date'], // Current input
                'payment_amount' => (float) $_POST['coaching_amount'], // Current input
                'plan_type' => 'Coaching'
            ];
        }

        // Insert logs into transaction_logs table
        $log_query = "INSERT INTO transaction_logs (member_id, transaction_type, payment_date, payment_amount, customer_type, plan_type) 
                      VALUES (?, ?, ?, ?, 'Member', ?)";
        $log_stmt = $conn->prepare($log_query);

        foreach ($log_queries as $log) {
            $log_stmt->bind_param(
                "issds", 
                $log['member_id'], 
                $log['transaction_type'], 
                $log['payment_date'], 
                $log['payment_amount'], 
                $log['plan_type']
            );
            $log_stmt->execute();
        }

        $log_stmt->close();

        echo "Payment saved successfully.";
        header("Location: membersuccessfullyadded.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;
        error_log($stmt->error);
    }
    $stmt->close();
}

// Close the database connection
$conn->close();
?>







<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Brew + Flex Gym</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">


    <link rel="stylesheet" href="/brew+flex/css/payment.css">

    <style>

/* Sidebar Styles */
.sidebar {
    width: 270px;
    background: linear-gradient(to bottom right, #bde3e8, #f2f2f2);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 40px 0;
}

.admin-info {
    display: flex;
    align-items: center;
    flex-direction: column;
    color: #000;
}

.admin-info img {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    margin-bottom: 15px;
}

.admin-info h3 {
    margin-bottom: 5px;
    font-size: 20px;
    font-weight: normal;
}

.admin-info p {
    margin: 5px 0;
    font-size: 12px;
    color: #333;
}

nav.menu {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    padding: 0;
    margin-top: 30px;
}

nav.menu ul {
    list-style: none;
    padding: 0;
}

nav.menu ul li {
    margin-bottom: 20px;
}

nav.menu ul li a {
    text-decoration: none;
    font-size: 14px;
    display: flex;
    align-items: center;
    color: #000;
    padding: 10px 20px;
    transition: background-color 0.2s ease;
}

nav.menu ul li a:hover {
    background-color: #71d4fc;
    border-radius: 10px;
}

nav.menu ul li a i {
    margin-right: 15px;
}

nav.menu ul li.active a {
    background-color: #71d4fc;
    border-radius: 10px;
    font-weight: bold;
}

.logout {
    text-align: center;
    margin-bottom: 40px;
}

.logout a {
    text-decoration: none;
    color: #000;
    display: inline-flex;
    align-items: center;
}

.logout a i {
    margin-right: 10px;
}

/* Sidebar Styles */
.sidebar {
    width: 270px; /* Default width */
    background: linear-gradient(to bottom right, #bde3e8, #f2f2f2);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 20px 0;
    position: fixed; /* Fixed position */
    top: 0;
    left: 0;
    height: 100vh; /* Full height */
    overflow-y: auto; /* Scroll if content overflows */
    transition: width 0.3s ease;
}

.admin-info {
    display: flex;
    align-items: center;
    flex-direction: column;
    color: #000;
}

.admin-info img {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    margin-bottom: 15px;
}

.admin-info h3, .admin-info p {
    margin: 5px 0;
    font-size: 14px;
    text-align: center;
}

nav.menu {
    flex-grow: 1;
    margin-top: 30px;
}

nav.menu ul {
    list-style: none;
    padding: 0;
}

nav.menu ul li a {
    text-decoration: none;
    font-size: 14px;
    display: flex;
    align-items: center;
    color: #000;
    padding: 10px 20px;
    transition: background-color 0.3s ease;
}

nav.menu ul li a:hover,
nav.menu ul li.active a {
    background-color: #71d4fc;
    border-radius: 10px;
    font-weight: bold;
}

nav.menu ul li a i {
    margin-right: 10px;
}

.logout {
    text-align: center;
    margin-bottom: 20px;
}

.logout a {
    text-decoration: none;
    color: #000;
    display: inline-flex;
    align-items: center;
}

.logout a i {
    margin-right: 10px;
}

/* Main Content */
.main-content {
    margin-left: 270px; /* Respect sidebar width */
    flex: 1;
    padding: 20px;
    background-color: #fff;
    overflow-x: auto; /* Prevent content overflow */
    transition: margin-left 0.3s ease;
}

/* Responsive Sidebar */
@media (max-width: 768px) {
    .sidebar {
        width: 180px; /* Smaller sidebar on medium screens */
    }

    .main-content {
        margin-left: 180px; /* Adjust main content */
    }

    .admin-info h3,
    .admin-info p {
        font-size: 12px;
    }
}

@media (max-width: 480px) {
    .sidebar {
        width: 100px; /* Narrow sidebar for small screens */
        padding: 10px 0;
    }

    nav.menu ul li a {
        justify-content: center; /* Center icons */
        padding: 10px;
    }

    nav.menu ul li a i {
        margin: 0;
    }

    .admin-info h3,
    .admin-info p {
        display: none; /* Hide text for small screens */
    }

    .main-content {
        margin-left: 100px; /* Adjust content for small sidebar */
    }
}











       .hidden {
    display: none !important;
}

        .payment-header {
            font-size: 1.2rem;
            font-weight: bold;
            margin-top: 20px;
            color: #333;
            text-decoration: underline;
        }
        
/* Modern Styling for h4 in Sections */
#monthlySection h4, 
#renewalSection h4, 
#coachingSection h4, 
#lockerSection h4 {
    font-size: 20px; /* Slightly larger font size */
    font-weight: 600; /* Semi-bold text */
    color: #333; /* Darker text for contrast */
    background: #71d4fc; /* Subtle gradient */
    padding: 12px 16px; /* Inner spacing */
    margin: 15px auto; /* Center with auto margin horizontally */
    border-left: 5px solid #71d4fc; /* Modern blue border on the left */
    border-radius: 8px; /* Rounded corners */
    text-transform: capitalize; /* Capitalize words for elegance */
    letter-spacing: 0.5px; /* Subtle letter spacing */
    font-family: 'Poppins', Arial, sans-serif; /* Modern font */
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.15); /* Stronger shadow for depth */
    text-align: center; /* Center the text */
    width: fit-content; /* Adjust the width to fit the content */
    margin-left: auto; /* Center alignment trick */
    margin-right: auto; /* Center alignment trick */
    transition: all 0.3s ease; /* Smooth transition for hover effects */
    text-decoration: none; /* Remove underline */
}

/* Add Hover Effect for Interaction */
#monthlySection h4:hover, 
#renewalSection h4:hover, 
#coachingSection h4:hover, 
#lockerSection h4:hover {
    color: #71d4fc; /* Highlight the text color on hover */
    background: linear-gradient(to right, #f0f8ff, #e6f7ff); /* Light blue gradient */
    border-left: 5px solid #71d4fc; /* Darker blue border on hover */
    transform: translateY(-3px); /* Slight lift effect */
    box-shadow: 0 6px 10px rgba(0, 0, 0, 0.2); /* Deeper shadow for lift */
    cursor: pointer; /* Change cursor to pointer */
}
/* General Section Styling */
#monthlySection, 
#renewalSection, 
#coachingSection, 
#lockerSection {
    background: #ffffff; /* White background */
    border: 1px solid #ddd; /* Light gray border */
    border-radius: 8px; /* Rounded corners */
    padding: 20px; /* Inner spacing */
    margin: 20px 0; /* Spacing between sections */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
    transition: all 0.3s ease; /* Smooth transitions */
}

/* Section Hover Effect */
#monthlySection:hover, 
#renewalSection:hover, 
#coachingSection:hover, 
#lockerSection:hover {
    transform: translateY(-5px); /* Slight lift on hover */
    box-shadow: 0 8px 12px rgba(0, 0, 0, 0.2); /* Deeper shadow */
    border: 1px solid #71d4fc; /* Highlight border on hover */
}

/* Styling for Form Groups */
.form-group {
    display: flex; /* Align items in a row */
    flex-wrap: wrap; /* Wrap content */
    gap: 15px; /* Spacing between fields */
    margin-bottom: 15px; /* Spacing between groups */
}

.form-group > div {
    flex: 1 1 45%; /* Adjust field width (responsive) */
    min-width: 250px; /* Minimum field width */
}

/* Labels Styling */
label {
    font-weight: 600; /* Make labels bold */
    margin-bottom: 5px; /* Space below labels */
    display: block; /* Labels appear on top */
    color: #333; /* Darker text color */
}

/* Input and Select Styling */
input[type="text"],
input[type="date"],
select {
    width: 100%;
    padding: 10px; /* Add inner spacing */
    font-size: 14px;
    border: 1px solid #ccc; /* Light border */
    border-radius: 5px; /* Rounded corners */
    background: #f9f9f9; /* Light gray background */
    color: #333; /* Text color */
    transition: border 0.3s ease, box-shadow 0.3s ease;
}

input:focus,
select:focus {
    border-color: #71d4fc; /* Highlight border on focus */
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.4); /* Add shadow on focus */
    outline: none; /* Remove default outline */
}

/* Styling for Multiple Select */
select[multiple] {
    height: 100px; /* Fixed height for multiple select */
    overflow-y: auto; /* Add scrollbar if needed */
    background: #f5faff; /* Slightly blue background */
}

/* Styling for Amount Input */
.amount-input {
    text-align: right; /* Right-align the text */
    font-weight: bold; /* Bold text */
}


#totalAmountSection {
    display: inline-flex; /* Align items horizontally without taking full width */
    align-items: center; /* Vertically center the content */
    gap: 5px; /* Small spacing between label and span */
    padding: 10px; /* Small padding inside the container */
    font-size: 18px; /* Adjust font size */
    border: 1px solid #ddd; /* Add a border for visibility */
    background-color: #fafafa; /* Light background color */
    border-radius: 3px; /* Slightly rounded corners */
    width: fit-content; /* Adjust container width to fit content only */
    box-sizing: border-box; /* Ensure padding doesn't affect width */
    margin-top: 10px;
}
/* Prevent total amount div from stretching */
.form-group #totalAmountSection {
    flex: 0 0 auto; /* Disable flex-grow and shrink */
    width: fit-content; /* Fit the container content */
}


#totalAmountSection label {
    margin: 0; /* Remove extra margin */
    font-weight: bold; /* Bold for emphasis */
    white-space: nowrap; /* Prevent text wrapping */
}

#totalamount {
    font-weight: bold; /* Bold for the span */
    color: #000; /* Optional blue color for emphasis */
}








    </style>

</head>
<ssc>
    <div class="container">
        <aside class="sidebar">
            <div class="admin-info">
                <img src="<?php echo htmlspecialchars($user_info['profile_picture'] ?? 'default-profile.png'); ?>" alt="Admin Avatar">
                <h3><?php echo htmlspecialchars($user_info['username']); ?></h3>
                <p><?php echo htmlspecialchars($user_info['email']); ?></p>
            </div>
            <nav class="menu">
                <ul>
                    <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>

                    <?php if ($usertype === 'admin'): ?>
                        <li><a href="adminpage.php"><i class="fas fa-cogs"></i> Admin</a></li>
                        <li><a href="managestaff.php"><i class="fas fa-users"></i> Manage Staff</a></li>
                    <?php endif; ?>

                    <li><a href="registration.php"><i class="fas fa-clipboard-list"></i> Registration</a></li>
                    <li><a href="memberspage.php"><i class="fas fa-user-friends"></i> View Members</a></li>
                    <li><a href="attendance.php"><i class="fas fa-calendar-check"></i> Attendance</a></li>
                    <li class="active"><a href="payment.php"><i class="fas fa-credit-card"></i> Payment</a></li>
                    <li><a href="inventory.php"><i class="fas fa-boxes"></i> Inventory</a></li>
                    <li><a href="coaches.php"><i class="fas fa-dumbbell"></i> Coaches</a></li>
                    <li><a href="walkins.php"><i class="fas fa-walking"></i> Walk-ins</a></li>
                    <li><a href="#"><i class="fas fa-file-alt"></i> Report</a></li>
                </ul>
            </nav>
            <div class="logout">
                <a href="#" onclick="showLogoutModal(); return false;">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>

            <!-- Logout Modal -->
            <div class="modal" id="logoutModal">
                <div class="modal-content">
                    <i class="fas fa-sign-out-alt modal-icon"></i>
                    <h3>Are you sure you want to log out?</h3>
                    <div class="modal-buttons">
                        <button class="confirm-btn" onclick="handleLogout()">Yes, Log Out</button>
                        <button class="cancel-btn" onclick="closeLogoutModal()">Cancel</button>
                    </div>
                </div>
            </div>
        </aside>


        <main class="main-content">
            <div class="header">
                <img src="/brew+flex/assets/brewlogo2.png" class="logo">
            </div>
            <div class="form-container">
                <h3>Add Payment <i class="fas fa-credit-card"></i></h3>
                <form action="payment.php" method="POST">
                    <!-- Member Information -->
                    <div class="form-group">
                        <div>
                            <label for="memberName">Full Name</label>
                            <select id="memberName" required>
                                <option value="" disabled selected>Select member</option>
                                <?php foreach ($members as $member): ?>
                                    <option value="<?php echo htmlspecialchars($member['member_id']); ?>">
                                        <?php echo htmlspecialchars($member['full_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="memberId">Member ID</label>
                            <input type="text" id="memberId" name="member_id" readonly required>
                        </div>
                    </div>



                    <!-- Payment Type and Validity Selection -->
                    <div class="form-group">
                        <div>
                            <label for="paymentType">Payment for</label>
                            <select id="paymentType" name="payment_type[]" multiple="multiple">
                                <option value="monthly">Monthly Plan Payment</option>
                                <option value="renewal">Membership Renewal Payment</option>
                                <option value="coaching">Coaching Plan Payment</option>
                            </select>
                        </div>
                        <!-- Validity selections are hidden initially and shown based on type -->



                    </div>




                    <!--Monthly Plan Section -->
                    <div id="monthlySection" class="hidden">
                        <h4 class="payment-header">Monthly Plan</h4>
                        <div class="form-group">

                            <div id="monthlyPaymentDateContainer" class="hidden">

                                <label for="monthlyPaymentDate">Date of Payment (Monthly)</label>
                                <input type="date" id="monthlyPaymentDate" name="monthly_plan_payment_date" >
                            </div>

                            <div id="monthlyExpiryContainer" class="hidden">
                                <label for="monthlyExpiryDate">Expiry Date (Monthly)</label>
                                <input type="date" id="monthlyExpiryDate" name="monthly_plan_expiration_date">
                            </div>

                            <div id="monthlyValidity" class="hidden">
                                <label for="monthlyValiditySelect">Monthly Validity</label>
                                <select id="monthlyValiditySelect" name="monthlyValidity">
                                    <option value="" disabled selected>Select validity</option>
                                </select>
                            </div>

                            <div id="monthlyamount" class="hidden">
                                <label for="monthlyamountInput">Monthly Amount</label>
                                <input type="text" id="monthlyamountInput" name="monthly_amount" class="amount-input" value="0">
                            </div>
                        </div>

                    </div>


                    <!--Membership Renewal Plan Section -->
                    <div id="renewalSection" class="hidden">
                        <h4 class="payment-header">Membership Plan</h4>
                        <div class="form-group">

                            <div id="renewalPaymentDateContainer" class="hidden">
                                <label for="renewalPaymentDate">Date of Payment (Renewal)</label>
                                <input type="date" id="renewalPaymentDate" name="membership_renewal_payment_date" >
                            </div>

                            <div id="renewalExpiryContainer" class="hidden">
                                <label for="renewalExpiryDate">Expiry Date (Renewal)</label>
                                <input type="date" id="renewalExpiryDate" name="renewal_expiry_date">
                            </div>



                            <div id="renewalValidity" class="hidden">
                                <label for="renewalValiditySelect">Renewal Validity</label>
                                <select id="renewalValiditySelect" name="renewalValidity" >
                                    <option value="" disabled selected>Select validity</option>
                                </select>
                            </div>


                            <div id="renewalamount" class="hidden">
                                <label for="renewalamountInput">Renewal Amount</label>
                                <input type="text" id="renewalamountInput" name="renewal_amount" class="amount-input" value="0">
                            </div>


                        </div>

                    </div>



                    <!--Coaching Plan Section -->

                    <div id="coachingSection" class="hidden">
                        <h4 class="payment-header">Coaching Plan</h4>
                        <div class="form-group">


                            <div id="coachingPaymentDateContainer" class="hidden">
                                <label for="coachingPaymentDate">Date of Payment (Coaching)</label>
                                <input type="date" id="coachingPaymentDate" name="coaching_payment_date" >
                            </div>



                            <div id="coachingValidity" class="hidden">
                                <label for="coachingValiditySelect">Coaching Validity</label>
                                <select id="coachingValiditySelect" name="coachingValidity" >
                                    <option value="" disabled selected>Select validity</option>
                                </select>
                            </div>



                            <div id="coachField" class="hidden">
                                <label for="coachName">Select Coach</label>
                                <select id="coachName" name="coach_id">
                                    <option value="" disabled selected>Select coach</option>
                                    <?php foreach ($coaches as $coach): ?>
                                        <option value="<?php echo htmlspecialchars($coach['coach_id']); ?>">
                                            <?php echo htmlspecialchars($coach['full_name'] . ' (' . $coach['expertise'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div id="coachingamount" class="hidden">
                                <label for="coachingamountInput">Coaching Amount</label>
                                <input type="text" id="coachingamountInput" name="coaching_amount" class="amount-input" value="0">
                            </div>


                        </div>
                    </div>


                    <!--Locker Plan Section -->
                    <div id="lockerSection" class="hidden">
                        <h4 class="payment-header">Locker Plan</h4>

                        <div class="form-group " id="dateFields">


                            <div id="lockerPaymentDateContainer" class="hidden">
                                <label for="lockerPaymentDate">Date of Payment (Locker)</label>
                                <input type="date" id="lockerPaymentDate" name="locker_payment_date" disabled >
                            </div>


                            <div id="lockerExpiryContainer" class="hidden">
                                <label for="lockerExpiryDate">Expiry Date (Locker)</label>
                                <input type="date" id="lockerExpiryDate" name="locker_expiration_date"  disabled>
                            </div>

                            <div id="lockerValidity" class="hidden">
                                <label for="lockerValiditySelect">Locker Validity</label>
                                <select id="lockerValiditySelect" name="lockerValidity"  >
                                    <option value="" disabled selected>Select validity</option>
                                </select>
                            </div>

                            <div id="lockeramount" class="hidden">
                                <label for="lockeramountInput">Locker Amount</label>
                                <input type="text" id="lockeramountInput" name="locker_amount" class="amount-input" value="0">
                            </div>


                        </div>
                    </div>

                    <!-- Total Amount Section -->

                    <div class="form-group">
    <div id="totalAmountSection" class="hidden">
        <label for="totalamount">Total Amount:</label>
        <span id="totalamount" class="totalamount-display">0</span>
        <input type="hidden" id="totalamountInput" name="total_amount">
    </div>
</div>



                    <div class="form-buttons">
                        <button type="button" class="cancel-btn">Cancel</button>
                        <button type="submit" class="next-btn" onclick="return validateForm();">Save</button>
                       

                    </div>
                </form>
            </div>
        </main>
     
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
       
        <script>



// JavaScript to handle the Member Full Name and Member ID
document.getElementById('memberName').addEventListener('change', function () {
    const selectedMemberId = this.value;
    document.getElementById('memberId').value = selectedMemberId;
});




$(document).ready(function () {
    const validityData = {
        monthly: {
            validity: [
                { value: 1, text: '1 month', amount: 700 },
                { value: 2, text: '2 months', amount: 800 },
                { value: 3, text: '3 months', amount: 900 },
                { value: 4, text: '4 months', amount: 1000 },
                { value: 5, text: '5 months', amount: 1100 },
                { value: 6, text: '6 months', amount: 1200 },
                { value: 7, text: '7 months', amount: 1300 },
                { value: 8, text: '8 months', amount: 1400 },
                { value: 9, text: '9 months', amount: 1500 },
                { value: 10, text: '10 months', amount: 1600 },
                { value: 11, text: '11 months', amount: 1700 },
                { value: 12, text: '12 months', amount: 1800 },
            ],
        },
        renewal: {
            validity: [
                { value: 1, text: '1 year', amount: 1600 },
                { value: 2, text: '2 years', amount: 1700 },
                { value: 3, text: '3 years', amount: 1800 },
            ],
        },
        locker: {
            validity: [
                { value: 1, text: '1 month', amount: 700 },
                { value: 2, text: '2 months', amount: 800 },
                { value: 3, text: '3 months', amount: 900 },
                { value: 4, text: '4 months', amount: 1000 },
                { value: 5, text: '5 months', amount: 1100 },
                { value: 6, text: '6 months', amount: 1200 },
                { value: 7, text: '7 months', amount: 1300 },
                { value: 8, text: '8 months', amount: 1400 },
                { value: 9, text: '9 months', amount: 1500 },
                { value: 10, text: '10 months', amount: 1600 },
                { value: 11, text: '11 months', amount: 1700 },
                { value: 12, text: '12 months', amount: 1800 },
            ],
        },
        coaching: {
            validity: [
                { value: 1, text: '1 session', amount: 1600 },
                { value: 8, text: '8 sessions', amount: 1700 },
                { value: 12, text: '12 sessions', amount: 1800 },
            ],
        },
    };

    $('#paymentType').select2({
        placeholder: "Select payment type",
        allowClear: true,
    });

   

    function hideAllSections() {
        $('.payment-section').each(function () {
            const section = $(this);
            section.addClass('hidden')
                .find(':input')
                .each(function () {
                    $(this).prop('disabled', true).removeAttr('required');
                });
        });
        $('#totalAmountSection').addClass('hidden');
    }

    function showSection(selector) {
        $(selector).removeClass('hidden')
            .find(':input')
            .each(function () {
                $(this).prop('disabled', false).attr('required', true);
            });
    }





    function populateValidityOptions(type) {
        const options = validityData[type].validity;
        const select = $('#' + type + 'ValiditySelect');
        select.empty().append('<option value="" disabled selected>Select validity</option>');
        options.forEach((option) => {
            select.append($('<option>').val(option.value).text(option.text).data('amount', option.amount));
        });
        select.prop('disabled', false);
    }
    

    function calculateExpiryDate(validity, type) {
        const today = new Date();
        let expiryDate;
        if (type === 'renewal' || type === 'coaching') {
            expiryDate = new Date(today.setFullYear(today.getFullYear() + validity));
        } else {
            expiryDate = new Date(today.setMonth(today.getMonth() + validity));
        }
        return expiryDate.toISOString().split('T')[0];
    }

    function updateTotalAmount() {
        let total = 0;
        $('.amount-input:visible').each(function () {
            total += parseInt($(this).val()) || 0;
        });
        $('#totalamount').text(total);
        $('#totalamountInput').val(total);
    }

   window.validateForm = function () {
    const selectedOptions = $('#paymentType').val(); // Get selected payment types

    if (!selectedOptions || selectedOptions.length === 0) {
        alert("Please select at least one payment type.");
        $('#paymentType').focus();
        return false;
    }

    let isValid = true;

    // Validate all visible required fields
    $(':input:visible').each(function () {
        if ($(this).prop('required') && !$(this).val()) {
            isValid = false;
            alert(`Please fill out the ${$(this).attr('name')} field.`);
            $(this).focus();
            return false; // Break loop on first error
        }
    });

    return isValid;
};

    $('#paymentType').on('change', function () {
        hideAllSections();
        const selectedOptions = $(this).val() || [];

        selectedOptions.forEach((option) => {
            showSection('#' + option + 'Section');
            showSection('#' + option + 'PaymentDateContainer');
            showSection('#' + option + 'ExpiryContainer');
            showSection('#' + option + 'Validity');
            showSection('#' + option + 'amount');

            if (option === 'coaching') {
                showSection('#coachField');
            }

        
            populateValidityOptions(option);

            const today = new Date().toISOString().split('T')[0];
            $('#' + option + 'PaymentDate').val(today).prop('disabled', false);
            $('#' + option + 'ExpiryDate').prop('disabled', false);

        });

        if (selectedOptions.length > 0) {
            $('#totalAmountSection').removeClass('hidden');
        }
    });



    $('select[id$="ValiditySelect"]').on('change', function () {
        const selectedOption = $(this).find('option:selected');
        if (selectedOption.val()) {
            const amount = selectedOption.data('amount');
            const validity = parseInt(selectedOption.val());
            const type = this.id.replace('ValiditySelect', '');
            const expiryDate = calculateExpiryDate(validity, type);
                    // $('#lockerExpiryDate').val(expiryDate).prop('disabled', false); // Ensure enabled


            $('#' + type + 'ExpiryDate').val(expiryDate);
            $('#' + type + 'amountInput').val(amount).trigger('change');
        }
    });

    $(document).on('change', '.amount-input', function () {
        updateTotalAmount();
    });
    

   



 // Form submit logic
    $('form').on('submit', function (event) {
    const selectedOptions = $('#paymentType').val() || [];


    // Ensure only fields in the selected sections are enabled
    ['monthly', 'renewal', 'locker', 'coaching'].forEach((option) => {
        if (!selectedOptions.includes(option)) {
            $(`#${option}Section :input`).prop('disabled', true).removeAttr('required');

        } else {
            $(`#${option}Section :input`).prop('disabled', false).attr('required', true);
        }
    });

   

console.log($('#lockerExpiryDate').val());


    // Perform validation
    if (!window.validateForm()) {
        event.preventDefault();
    }

    
});





});




</script>







</html>