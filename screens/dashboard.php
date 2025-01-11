<?php
session_start();
require_once 'db_connection.php';

// Redirect to login if the user isn't logged in
if (!isset($_SESSION["username"])) {
    header("location:/brew+flex/auth/login.php");
    exit;
}

// Retrieve session variables
$username = $_SESSION["username"];
$usertype = $_SESSION["usertype"];
$user_info = [];

// Fetch user information from the database
$query = "SELECT username, contact_no, email, profile_picture FROM users WHERE username = ? AND usertype = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $username, $usertype);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $user_info = $result->fetch_assoc();
} else {
    echo "User information not found.";
    exit;
}

// Dashboard Metrics
$dashboardData = [
    'total_members' => 0,
    'active_members' => 0,
    'inactive_members' => 0,
    'total_walkins' => 0,
    'total_walkins_today' => 0,
    'total_revenue' => 0.00,
    'total_revenue_today' => 0.00,
    'total_attendance' => 0,
];

try {
    // Fetch Total Members
    $membersQuery = "SELECT COUNT(*) AS total_members FROM members";
    if ($membersResult = $conn->query($membersQuery)) {
        $dashboardData['total_members'] = $membersResult->fetch_assoc()['total_members'];
    }

// Fetch Active Members (attended in the current month)
$activeMembersQuery = "
    SELECT COUNT(DISTINCT m.member_id) AS active_members
    FROM members m
    JOIN attendance a ON m.member_id = a.member_id
    WHERE MONTH(a.check_in_date) = MONTH(CURDATE()) AND YEAR(a.check_in_date) = YEAR(CURDATE())
";
if ($activeMembersResult = $conn->query($activeMembersQuery)) {
    $dashboardData['active_members'] = $activeMembersResult->fetch_assoc()['active_members'];
}

// Fetch Inactive Members (didn't attend in the current month but still have valid memberships)
$inactiveMembersQuery = "
    SELECT COUNT(*) AS inactive_members
    FROM members m
    WHERE m.member_id NOT IN (
        SELECT DISTINCT a.member_id
        FROM attendance a
        WHERE MONTH(a.check_in_date) = MONTH(CURDATE()) AND YEAR(a.check_in_date) = YEAR(CURDATE())
    )
    AND EXISTS (
        SELECT 1
        FROM payments p
        WHERE p.member_id = m.member_id
        AND p.monthly_plan_expiration_date >= CURDATE()
    )
    AND EXISTS (
        SELECT 1
        FROM attendance a
        WHERE a.member_id = m.member_id
    )
";
if ($inactiveMembersResult = $conn->query($inactiveMembersQuery)) {
    $dashboardData['inactive_members'] = $inactiveMembersResult->fetch_assoc()['inactive_members'];
}

    // Total Walk-ins
    $walkinsQuery = "SELECT COUNT(*) AS total_walkins FROM walkins";
    if ($walkinsResult = $conn->query($walkinsQuery)) {
        $dashboardData['total_walkins'] = $walkinsResult->fetch_assoc()['total_walkins'];
    }

    // Total Walk-ins Today
    $walkinsTodayQuery = "SELECT COUNT(*) AS total_walkins_today FROM walkins WHERE DATE(created_at) = CURDATE()";
    if ($walkinsTodayResult = $conn->query($walkinsTodayQuery)) {
        $dashboardData['total_walkins_today'] = $walkinsTodayResult->fetch_assoc()['total_walkins_today'];
    }

    $totalRevenue = 0.00;

    // Transaction Logs Table (All-Time Data)
    $transactionLogsAllTimeQuery = "
        SELECT 
            plan_type,
            payment_amount,
            payment_date
        FROM transaction_logs
    ";
    
    $transactionDetailsAllTime = []; // Array to store all-time transaction details
    
    if ($transactionLogsAllTimeResult = $conn->query($transactionLogsAllTimeQuery)) {
        while ($row = $transactionLogsAllTimeResult->fetch_assoc()) {
            $transactionDetailsAllTime[] = $row; // Collect each transaction log
            $totalRevenue += (float) $row['payment_amount']; // Add payment amount to total
        }
    } else {
        error_log("Transaction Logs All-Time Query Error: " . $conn->error); // Log query error
    }
    
    // Debugging
    error_log("Total Revenue (All-Time): " . $totalRevenue);
    

// Walkins Table
$walkinsQuery = "SELECT COALESCE(SUM(amount), 0) AS total_revenue FROM walkins";
if ($walkinsResult = $conn->query($walkinsQuery)) {
    $row = $walkinsResult->fetch_assoc();
    $totalRevenue += (float) ($row['total_revenue'] ?? 0.00);
} else {
    error_log("Walkins Query Error: " . $conn->error); // Log error
}

// Walkins Logs Table
$walkinsLogsQuery = "SELECT COALESCE(SUM(amount), 0) AS total_revenue FROM walkins_logs";
if ($walkinsLogsResult = $conn->query($walkinsLogsQuery)) {
    $row = $walkinsLogsResult->fetch_assoc();
    $totalRevenue += (float) ($row['total_revenue'] ?? 0.00);
} else {
    error_log("Walkins Logs Query Error: " . $conn->error); // Log error
}

// POS Logs Table
$posLogsQuery = "SELECT COALESCE(SUM(total_amount), 0) AS total_revenue FROM pos_logs";
if ($posLogsResult = $conn->query($posLogsQuery)) {
    $row = $posLogsResult->fetch_assoc();
    $totalRevenue += (float) ($row['total_revenue'] ?? 0.00);
} else {
    error_log("POS Logs Query Error: " . $conn->error); // Log error
}

// Assign to dashboard data
$dashboardData['total_revenue'] = $totalRevenue;

// Debugging
error_log("Total Revenue (All-Time): " . $totalRevenue);



// Initialize total revenue for today
$totalRevenueToday = 0.00;

// Transaction Logs Table (Today's data)
$transactionLogsTodayQuery = "
    SELECT 
        plan_type,
        payment_amount,
        payment_date
    FROM transaction_logs
    WHERE DATE(payment_date) = CURDATE()
";

$transactionDetails = []; // Array to store individual payment details

if ($transactionLogsTodayResult = $conn->query($transactionLogsTodayQuery)) {
    while ($row = $transactionLogsTodayResult->fetch_assoc()) {
        $transactionDetails[] = $row; // Collect each transaction log
        $totalRevenueToday += (float) $row['payment_amount']; // Add payment amount to total
    }
} else {
    error_log("Transaction Logs Today Query Error: " . $conn->error); // Log query error
}

// Debugging
error_log("Today's Total Revenue (From Transaction Logs): " . $totalRevenueToday);

// Output the total revenue
echo "Today's Total Revenue: ₱" . number_format($totalRevenueToday, 2);






// Walkins Table (Today's data)
$walkinsTodayQuery = "
    SELECT COALESCE(SUM(amount), 0) AS total_revenue 
    FROM walkins
    WHERE DATE(created_at) = CURDATE()
";

if ($walkinsTodayResult = $conn->query($walkinsTodayQuery)) {
    $row = $walkinsTodayResult->fetch_assoc();
    $totalRevenueToday += (float) ($row['total_revenue'] ?? 0.00);
    error_log("Today's Walk-ins Revenue: " . $totalRevenueToday); // Debug log
} else {
    error_log("Walk-ins Today Query Error: " . $conn->error); // Log query error
}

// Walkins Logs Table (Today's data)
$walkinsLogsTodayQuery = "
    SELECT COALESCE(SUM(amount), 0) AS total_revenue 
    FROM walkins_logs
    WHERE DATE(created_at) = CURDATE()
";

if ($walkinsLogsTodayResult = $conn->query($walkinsLogsTodayQuery)) {
    $row = $walkinsLogsTodayResult->fetch_assoc();
    $totalRevenueToday += (float) ($row['total_revenue'] ?? 0.00);
    error_log("Today's Walk-ins Logs Revenue: " . $totalRevenueToday); // Debug log
} else {
    error_log("Walk-ins Logs Today Query Error: " . $conn->error); // Log query error
}



// POS Logs Table (Today's data)
$posLogsTodayQuery = "
    SELECT COALESCE(SUM(total_amount), 0) AS total_revenue 
    FROM pos_logs
    WHERE DATE(created_at) = CURDATE()
";

if ($posLogsTodayResult = $conn->query($posLogsTodayQuery)) {
    $row = $posLogsTodayResult->fetch_assoc();
    $totalRevenueToday += (float) ($row['total_revenue'] ?? 0.00);
    error_log("Today's POS Logs Revenue: " . $totalRevenueToday); // Debug log
} else {
    error_log("POS Logs Today Query Error: " . $conn->error); // Log query error
}

// Assign to dashboard data
$dashboardData['total_revenue_today'] = $totalRevenueToday;


$newMembersQuery = "
    SELECT 
        m.member_id,
        m.first_name,
        m.last_name,
        m.email,
        m.contact_no,
        m.date_enrolled,
        IF(
            EXISTS (
                SELECT 1 FROM attendance a 
                WHERE a.member_id = m.member_id 
                AND DATE(a.check_in_date) = CURDATE()
            ), 
            'Active', 
            'Pending'
        ) AS status
    FROM members m
    WHERE DATE(m.date_enrolled) = CURDATE()
    ORDER BY m.date_enrolled DESC
    LIMIT 5 -- Show only 5 records
";

$newMembersCountQuery = "
    SELECT COUNT(*) AS total_new_members
    FROM members
    WHERE DATE(date_enrolled) = CURDATE()
";


$newMembers = []; // Initialize an empty array to store the results
$totalNewMembers = 0;

if ($newMembersResult = $conn->query($newMembersQuery)) {
    while ($row = $newMembersResult->fetch_assoc()) {
        $newMembers[] = $row; // Append each row to the $newMembers array
    }
}

if ($newMembersCountResult = $conn->query($newMembersCountQuery)) {
    $totalNewMembers = (int) $newMembersCountResult->fetch_assoc()['total_new_members'];
}

// Trigger a refresh if the count exceeds 100
if ($totalNewMembers >= 100) {
    echo "<script>setTimeout(function(){ location.reload(); }, 5000);</script>";
}



// Fetch all members along with their payment details
$membersQuery = "
    SELECT 
        m.member_id, 
        m.first_name, 
        m.last_name, 
        m.contact_no, 
        m.date_enrolled, 
        m.expiration_date, 
        p.coaching_payment_date, 
        p.monthly_plan_payment_date, 
        p.monthly_plan_expiration_date, 
        p.membership_renewal_payment_date, 
        p.membership_expiration_date, 
        p.locker_payment_date, 
        p.locker_expiration_date 
    FROM members m
    LEFT JOIN payments p ON m.member_id = p.member_id
";
$members = [];

if ($result = $conn->query($membersQuery)) {
    while ($row = $result->fetch_assoc()) {
        $members[] = $row; // Add each row to the $members array
    }
} else {
    error_log("Error fetching members: " . $conn->error); // Log query errors
}
$newWalkinsQuery = "
    SELECT 
        id AS walkin_id,
        CONCAT(name, ' ', lastname) AS full_name,
        contact_number,
        gender,
        DATE_FORMAT(join_date, '%Y-%m-%d') AS join_date,
        walkin_type,
        amount
    FROM walkins
    WHERE DATE(created_at) = CURDATE()
    ORDER BY join_date DESC
    LIMIT 5 -- Show only 5 records
";

$newWalkinsCountQuery = "
    SELECT COUNT(*) AS total_new_walkins
    FROM walkins
    WHERE DATE(created_at) = CURDATE()
";


$newWalkins = []; // Initialize an empty array to store the results
$totalNewWalkins = 0;

if ($newWalkinsResult = $conn->query($newWalkinsQuery)) {
    while ($row = $newWalkinsResult->fetch_assoc()) {
        $newWalkins[] = $row; // Append each row to the $newWalkins array
    }
}

if ($newWalkinsCountResult = $conn->query($newWalkinsCountQuery)) {
    $totalNewWalkins = (int) $newWalkinsCountResult->fetch_assoc()['total_new_walkins'];
}

// Trigger a refresh if the count exceeds 100
if ($totalNewWalkins >= 100) {
    echo "<script>setTimeout(function(){ location.reload(); }, 5000);</script>";
}


// Total Attendance Today
$totalAttendanceTodayQuery = "
    SELECT COUNT(*) AS total_attendance 
    FROM attendance 
    WHERE DATE(check_in_date) = CURDATE()
";

if ($totalAttendanceTodayResult = $conn->query($totalAttendanceTodayQuery)) {
    $dashboardData['total_attendance'] = $totalAttendanceTodayResult->fetch_assoc()['total_attendance'];
} else {
    error_log("Total Attendance Today Query Error: " . $conn->error); // Log query error
}


} catch (Exception $e) {
    echo "Error fetching dashboard data: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Brew + Flex Gym</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="/brew+flex/css/dashboard.css">
<style>
        .new-members-table {
    margin-top: 30px;
}

.new-members-table h2 {
    font-size: 24px;
    color: #2c3e50;
    margin-bottom: 10px;
}

.new-members-table table {
    width: 100%;
    border-collapse: collapse;
    background-color: #ffffff;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    margin-top: 10px;
}

.new-members-table th, .new-members-table td {
    border: 1px solid #ddd;
    padding: 10px;
    text-align: left;
}

.new-members-table th {
    background-color: #f4f4f4;
    color: #333;
    font-weight: bold;
}

.new-members-table tr:hover {
    background-color: #f9f9f9;
}

.new-members-table td {
    font-size: 14px;
    color: #2c3e50;
}

</style>
</head>
<body>
<div class="container">
    <aside class="sidebar">
        <div class="admin-info">
            <img src="<?php echo htmlspecialchars($user_info['profile_picture'] ?? 'default-profile.png'); ?>" alt="Admin Avatar">
            <h3><?php echo htmlspecialchars($user_info['username']); ?></h3>
            <p><?php echo htmlspecialchars($user_info['email']); ?></p>
        </div>
        <nav class="menu">
                <ul>
                    <li class="active"><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <?php if ($usertype === 'admin'): ?>
                        <li><a href="adminpage.php"><i class="fas fa-cogs"></i> Admin</a></li>
                        <li><a href="managestaff.php"><i class="fas fa-users"></i> Manage Staff</a></li>
                    <?php endif; ?>
                    <li><a href="registration.php"><i class="fas fa-clipboard-list"></i> Registration</a></li>
                    <li><a href="memberspage.php"><i class="fas fa-user-friends"></i> View Members</a></li>
                    <li ><a href="attendance.php"><i class="fas fa-calendar-check"></i> Attendance</a></li>
                    <li><a href="payment.php"><i class="fas fa-credit-card"></i> Payment</a></li>
                    <li><a href="inventory.php"><i class="fas fa-boxes"></i> Inventory</a></li>
                    <li><a href="pos.php"><i class="fas fa-money-bill"></i> Point of Sale</a></li>
                    <li><a href="coaches.php"><i class="fas fa-dumbbell"></i> Coaches</a></li>
                    <li><a href="walkins.php"><i class="fas fa-walking"></i> Walk-ins</a></li>
                    <li><a href="reports.php"><i class="fas fa-file-alt"></i> Report</a></li>
                    <li><a href="analytics.php"><i class="fas fa-chart-bar"></i> Reports Analytics</a></li>
                </ul>

                <div class="logout">
    <a href="#" onclick="showLogoutModal(); return true;">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
</div>
<div class="modal-logouts" id="logoutModal">
    <div class="logouts-content">
        <i class="fas fa-sign-out-alt modal-icon"></i>
        <h3>Are you sure you want to log out?</h3>
        <div class="modal-buttons">
            <button class="confirms-buttons" onclick="handleLogout()">Yes, Log Out</button>
            <button class="cancels-buttons" onclick="closeLogoutModal()">Cancel</button>
        </div>
    </div>
</div>
</aside>
</nav>

    <div class="main-content">
        <div class="dashboard">
            <h1>Welcome to the Dashboard</h1>

            <div class="stats">
            <a href="memberspage.php" style="text-decoration: none; color: inherit;">
    <div class="stat-box">
        <h2>Total Members</h2>
        <p><?php echo $dashboardData['total_members']; ?></p>
    </div>
</a>

<a href="walkins.php" style="text-decoration: none; color: inherit;">
    <div class="stat-box">
        <h2>Total Walk-ins</h2>
        <p><?php echo $dashboardData['total_walkins']; ?></p>
    </div>
</a>

    <div class="stat-box">
        <h2>Total Walk-ins Today</h2>
        <p><?php echo $dashboardData['total_walkins_today']; ?></p>
    </div>
    <a href="attendance.php?date_filter=today" style="text-decoration: none; color: inherit;">
    <div class="stat-box">
        <h2>Total Attendance Today</h2>
        <p><?php echo $dashboardData['total_attendance']; ?></p>
    </div>
</a>



<a href="analytics.php" style="text-decoration: none; color: inherit;">
    <div class="stat-box" style="cursor: pointer;">
        <h2>Total Revenue</h2>
        <p>₱<?php echo number_format($dashboardData['total_revenue'], 2); ?></p>
    </div>
</a>
<a href="analytics.php" style="text-decoration: none; color: inherit;">
    <div class="stat-box" style="cursor: pointer;">
        <h2>Total Revenue Today</h2>
        <p>₱<?php echo number_format($dashboardData['total_revenue_today'], 2); ?></p>
    </div>
    </a>

    <a href="memberspage.php" style="text-decoration: none; color: inherit;">
    <div class="stat-box" style="cursor: pointer;">
        <h2>Active Members</h2>
        <p><?php echo $dashboardData['active_members']; ?></p>
    </div>
    </a>
    <a href="memberspage.php" style="text-decoration: none; color: inherit;">
    <div class="stat-box" style="cursor: pointer;">
        <h2>Inactive Members</h2>
        <p><?php echo $dashboardData['inactive_members']; ?></p>
    </div>
    </a>
            </div>
            <div class="tabs">
    <button class="tab-button active" onclick="openTab(event, 'newMembersTab')">New Members</button>
    <button class="tab-button" onclick="openTab(event, 'expiredTodayTab')">Expired Today</button>
    <button class="tab-button" onclick="openTab(event, 'newWalkinsTab')">New Walk-ins</button>
</div>

<!-- New Members Tab -->
<div id="newMembersTab" class="tab-content active">
    <h2>New Members</h2>
    <?php if (!empty($newMembers)): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Contact No.</th>
                    <th>Date Enrolled</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($newMembers as $member): ?>
    <tr>
        <td><?php echo htmlspecialchars($member['member_id']); ?></td>
        <td><?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></td>
        <td><?php echo htmlspecialchars($member['email']); ?></td>
        <td><?php echo htmlspecialchars($member['contact_no']); ?></td>
        <td>
            <?php 
                // Format the date_enrolled using DateTime
                $formattedDate = (new DateTime($member['date_enrolled']))->format('F d, Y'); 
                echo htmlspecialchars($formattedDate); 
            ?>
        </td>
        <td><?php echo htmlspecialchars($member['status']); ?></td>
    </tr>
<?php endforeach; ?>

            </tbody>
        </table>
    <?php else: ?>
        <p>No new members have joined recently.</p>
    <?php endif; ?>
</div>

<!-- New Walk-ins Tab -->
<div id="newWalkinsTab" class="tab-content">
    <h2>New Walk-ins</h2>
    <?php if (!empty($newWalkins)): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Contact No.</th>
                    <th>Gender</th>
                    <th>Walk-in Type</th>
                    <th>Amount</th>
                    <th>Join Date</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($newWalkins as $walkin): ?>
    <tr>
        <td><?php echo htmlspecialchars($walkin['walkin_id']); ?></td>
        <td><?php echo htmlspecialchars($walkin['full_name']); ?></td>
        <td><?php echo htmlspecialchars($walkin['contact_number']); ?></td>
        <td><?php echo htmlspecialchars($walkin['gender']); ?></td>
        <td><?php echo htmlspecialchars(ucfirst($walkin['walkin_type'])); ?></td>
        <td>₱<?php echo number_format($walkin['amount'], 2); ?></td>
        <td>
            <?php 
                // Format the join_date using DateTime
                $formattedDate = (new DateTime($walkin['join_date']))->format('F d, Y'); 
                echo htmlspecialchars($formattedDate); 
            ?>
        </td>
    </tr>
<?php endforeach; ?>

            </tbody>
        </table>
    <?php else: ?>
        <p>No new walk-ins have been recorded today.</p>
    <?php endif; ?>
</div>

<!-- Expired Today Tab -->
<div id="expiredTodayTab" class="tab-content">
    <h2>Expired Today</h2>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Expired Membership</th>
                <th>Expired Monthly Plan</th>
                <th>Expired Locker</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $today = date('Y-m-d');
            foreach ($members as $member) {
                $renewed_membership = !empty($member['membership_renewal_payment_date']) && $member['membership_renewal_payment_date'] >= $today;
                $renewed_monthly_plan = !empty($member['monthly_plan_payment_date']) && $member['monthly_plan_payment_date'] >= $today;
                $renewed_locker = !empty($member['locker_payment_date']) && $member['locker_payment_date'] >= $today;

                $membership_expiration = !empty($member['membership_expiration_date']) ? $member['membership_expiration_date'] : $member['expiration_date'];
                $monthly_plan_expiration = $member['monthly_plan_expiration_date'] ?? 'N/A';
                $locker_expiration = $member['locker_expiration_date'] ?? 'N/A';

                if (
                    (!$renewed_membership && $membership_expiration === $today) ||
                    (!$renewed_monthly_plan && $monthly_plan_expiration === $today) ||
                    (!$renewed_locker && $locker_expiration === $today)
                ) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) . "</td>";
                    echo "<td class='expired-date'>" . ($membership_expiration === $today ? htmlspecialchars($today) : 'N/A') . "</td>";
                    echo "<td class='expired-date'>" . ($monthly_plan_expiration === $today ? htmlspecialchars($today) : 'N/A') . "</td>";
                    echo "<td class='expired-date'>" . ($locker_expiration === $today ? htmlspecialchars($today) : 'N/A') . "</td>";
                    echo "</tr>";
                }
            }
            ?>
        </tbody>
    </table>
</div>
</div>
<!-- CSS for Tabs -->
<style>








/* Tabs Section */
.tabs {
    display: flex;
    margin-bottom: 20px;
    margin-top: 10px;
}

.tab-content h2 {
    font-size: 24px;
    padding: 15px 30px;
    background-color: #f1f1f1;
    display: inline-block;
    color: #000;
    border-radius: 30px;
    font-weight: bold;
    margin: 0;
    margin-bottom: 10px;
}

.tab-button {
    background-color: #f1f1f1;
    border: 1px solid #ccc;
    padding: 10px 20px;
    cursor: pointer;
    margin-right: 5px;
    font-size: 16px;
    border-radius: 4px;
}

.tab-button.active {
    background-color: #71d4fc;
    border-bottom: 2px solid #2980b9;
    font-weight: bold;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

/* Table Styling */
.table-container {
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Optional: Add shadow for styling */
    margin-top: 10px;
}

table {
    border-collapse: separate; /* Allow rounded corners */
    border-spacing: 0; /* Ensure cells are close */
    width: 100%;
    background-color: #ffffff;
}

th, td {
    padding: 15px;
    border-bottom: 1px solid #ddd;
    text-align: center;
    font-size: 14px;
}

th {
    top: 0; /* Sticks to the top of the container */
    background-color: #f1f1f1; /* Match your header background color */
    z-index: 10; /* Ensure it stays on top of table rows */
    text-align: center; /* Align text properly */
    padding: 15px; /* Maintain padding consistency */
    border-bottom: 1px solid #ccc; /* Optional: Add a border for clarity */
    font-weight: bold;
    color: #000; /* Text color for headers */
}

/* Rounded Corners for Table Header */
th:first-child {
    border-top-left-radius: 8px;
}

th:last-child {
    border-top-right-radius: 8px;
}

tr:hover {
    background-color: #f9f9f9; /* Highlight row on hover */
}

.expired-date {
    color: #d9534f;
    font-weight: bold;
}

.logout-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: none; /* Initially hidden */
    align-items: center;
    justify-content: center;
    z-index: 1000;
    animation: fadeIn 0.3s ease-in-out; /* Animation for smooth appearance */
    }
    
    .modal-logouts {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: none; /* Initially hidden */
    align-items: center;
    justify-content: center;
    z-index: 1000;
    animation: fadeIn 0.3s ease-in-out;
}

/* Modal Content */
.logouts-content {
    background: linear-gradient(to bottom right, #bde3e8, #f2f2f2);
    padding: 30px;
    border-radius: 15px;
    text-align: center;
    width: 320px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
    animation: slideIn 0.4s ease;
    position: relative;
}

.modal-icon {
    font-size: 40px;
    color: #71d4fc;
    margin-bottom: 15px;
}

.logouts-content h3 {
    margin-top: 0;
    color: #333;
    font-size: 1.5rem;
    font-weight: bold;
}

/* Modal Buttons */
.modal-buttons {
    margin-top: 20px;
}

.logouts-content button {
    margin: 10px 5px;
    padding: 10px 20px;
    border: none;
    cursor: pointer;
    border-radius: 5px;
    font-weight: bold;
    transition: background-color 0.3s ease, transform 0.2s ease;
}

/* Confirm Button */
.confirms-buttons {
    background-color: #71d4fc;
    color: #ffffff;
}

.confirms-buttons:hover {
    background-color: #71d4fc;
    transform: scale(1.05);
}

/* Cancel Button */
.cancels-buttons {
    background-color: #ccc;
    color: #333;
}

.cancels-buttons:hover {
    background-color: #bbb;
    transform: scale(1.05);
}

/* Modal Animations */
@keyframes fadeIn {
    0% {
        opacity: 0;
    }
    100% {
        opacity: 1;
    }
}

@keyframes slideIn {
    0% {
        transform: translateY(-50px);
        opacity: 0;
    }
    100% {
        transform: translateY(0);
        opacity: 1;
    }
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}

</style>

<!-- JavaScript for Tab Functionality -->
<script>
    function openTab(event, tabId) {
        const tabContents = document.querySelectorAll('.tab-content');
        tabContents.forEach(content => content.classList.remove('active'));

        const tabButtons = document.querySelectorAll('.tab-button');
        tabButtons.forEach(button => button.classList.remove('active'));

        document.getElementById(tabId).classList.add('active');
        event.currentTarget.classList.add('active');
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



    
</script>
</body>
</html>
