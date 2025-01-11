<?php
session_start();
require_once 'db_connection.php';

// Set PHP timezone to Philippine Standard Time
date_default_timezone_set('Asia/Manila');

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
    echo json_encode(["status" => "error", "message" => "User information not found."]);
    exit;
}

// Handle adding attendance
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['member_id'])) {
    $member_id = intval($_POST['member_id']);

    // Fetch member information, including expiration dates
    $query = "
        SELECT 
            m.first_name, 
            m.last_name, 
            m.expiration_date AS membership_expiration_date, 
            p.monthly_plan_expiration_date 
        FROM members m
        LEFT JOIN payments p ON m.member_id = p.member_id 
        WHERE m.member_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $member_result = $stmt->get_result();

    if ($member_result->num_rows == 1) {
        $member = $member_result->fetch_assoc();
        $first_name = $member['first_name'] ?: 'Unknown';
        $last_name = $member['last_name'] ?: 'Unknown';
        $membership_expiration_date = $member['membership_expiration_date'];
        $monthly_plan_expiration_date = $member['monthly_plan_expiration_date'];
        $today_date = date('Y-m-d');

        // Check if membership has expired
        if ($membership_expiration_date && $membership_expiration_date < $today_date) {
            echo json_encode([
                "status" => "error",
                "message" => "Your membership has expired on " . date('F j, Y', strtotime($membership_expiration_date)) . ". Please renew your membership to attend today."
            ]);
            exit;
        }

        // Check if no monthly plan was purchased
        if (!$monthly_plan_expiration_date) {
            echo json_encode([
                "status" => "error",
                "message" => "You have not purchased a monthly plan. Please purchase a plan to mark attendance."
            ]);
            exit;
        }

        // Check if the monthly plan has expired
        if ($monthly_plan_expiration_date < $today_date) {
            echo json_encode([
                "status" => "error",
                "message" => "Your monthly plan has expired on " . date('F j, Y', strtotime($monthly_plan_expiration_date)) . ". Please purchase a new plan to attend today."
            ]);
            exit;
        }

        // Check if the member already has attendance for today
        $check_query = "SELECT * FROM attendance WHERE member_id = ? AND DATE(check_in_date) = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("is", $member_id, $today_date);
        $stmt->execute();
        $check_result = $stmt->get_result();

        if ($check_result->num_rows > 0) {
            echo json_encode(["status" => "error", "message" => "This member has already been marked for attendance today."]);
        } else {
            // Move existing attendance to logs if exists
            $existing_attendance_query = "SELECT * FROM attendance WHERE member_id = ?";
            $stmt = $conn->prepare($existing_attendance_query);
            $stmt->bind_param("i", $member_id);
            $stmt->execute();
            $existing_result = $stmt->get_result();

            if ($existing_result->num_rows > 0) {
                $existing_attendance = $existing_result->fetch_assoc();

                // Move to logs
                $log_query = "
                    INSERT INTO attendance_logs (attendance_id, member_id, first_name, last_name, check_in_date)
                    VALUES (?, ?, ?, ?, ?)";
                $log_stmt = $conn->prepare($log_query);
                $log_stmt->bind_param(
                    "iisss",
                    $existing_attendance['attendance_id'],
                    $existing_attendance['member_id'],
                    $existing_attendance['first_name'],
                    $existing_attendance['last_name'],
                    $existing_attendance['check_in_date']
                );
                $log_stmt->execute();

                // Update attendance with new check-in date
                $check_in_date = date('Y-m-d H:i:s');
                $update_query = "UPDATE attendance SET check_in_date = ?, first_name = ?, last_name = ? WHERE member_id = ?";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bind_param("sssi", $check_in_date, $first_name, $last_name, $member_id);
                $update_stmt->execute();
            } else {
                // Insert new record if no previous attendance exists
                $check_in_date = date('Y-m-d H:i:s');
                $insert_query = "INSERT INTO attendance (member_id, first_name, last_name, check_in_date) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($insert_query);
                $stmt->bind_param("isss", $member_id, $first_name, $last_name, $check_in_date);
                $stmt->execute();
            }
            echo json_encode(["status" => "success", "message" => "Attendance added successfully."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Member not found."]);
    }
    exit;
}
// Fetch date filter and set default to 'all'
$date_filter = isset($_GET['date_filter']) ? $_GET['date_filter'] : 'all';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Prepare the query based on the date filter
$date_condition = '';
$today_date = date('Y-m-d');
switch ($date_filter) {
    case 'today':
        $date_condition = "AND DATE(check_in_date) = '$today_date'";
        break;
    case 'yesterday':
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $date_condition = "AND DATE(check_in_date) = '$yesterday'";
        break;
    case 'last_3_days':
        $date_condition = "AND DATE(check_in_date) >= CURDATE() - INTERVAL 3 DAY";
        break;
    case 'last_week':
        $date_condition = "AND check_in_date >= CURDATE() - INTERVAL 1 WEEK";
        break;
    case 'last_month':
        $date_condition = "AND check_in_date >= CURDATE() - INTERVAL 1 MONTH";
        break;
    case 'custom':
        if (!empty($start_date) && !empty($end_date)) {
            $date_condition = "AND DATE(check_in_date) BETWEEN '$start_date' AND '$end_date'";
        }
        break;
    case 'all':
    default:
        $date_condition = ''; // No date filtering
        break;
}
// Set the limit for the results
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchWildcard = '%' . $search . '%';

// Update the query to include the date filter condition
$query = "
    SELECT *, DATE_FORMAT(check_in_date, '%M %d, %Y %h:%i %p') as formatted_date_time
    FROM attendance
    WHERE CONCAT(first_name, ' ', last_name) LIKE ?
    $date_condition
    ORDER BY check_in_date DESC
    LIMIT ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("si", $searchWildcard, $limit);
$stmt->execute();
$result = $stmt->get_result();
$attendance_records = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $attendance_records[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance - Brew + Flex Gym</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="/brew+flex/css/attendance.css">
    <script src="https://rawgit.com/schmich/instascan-builds/master/instascan.min.js"></script>
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            width: 300px;
        }

        .success-message {
            color: green;
            font-weight: bold;
        }

        .error-message {
            color: red;
            font-weight: bold;
        }

        .modal-ok-btn {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .success-icon {
            color: #28a745;
            /* Green color for success */
            font-size: 40px;
            /* Adjust icon size */
            margin-bottom: 10px;
        }

        .error-icon {
            color: #dc3545;
            /* Red color for error */
            font-size: 40px;
            /* Adjust icon size */
            margin-bottom: 10px;
        }


        .modal-ok-btn:hover {
            background-color: #0056b3;
        }

        /* Date Filter Section */
        .datefilter {
            align-items: center;
            /* Aligns the label and select vertically */
            margin-bottom: 10px;
            flex-direction: column;
            /* Aligns the label and select vertically */

        }

        .datefilter label {
            font-weight: bold;
            font-size: 16px;
            margin-right: 10px;
            /* Adds some space between the label and the select */
            white-space: nowrap;
            /* Prevents text from wrapping */
        }

        .datefilter select {
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ccc;
            background-color: #fff;
            color: #333;
            font-size: 14px;
            cursor: pointer;
            margin-bottom: 7px;

        }

        /* Custom Date Range Section */
        .custom-date-ranges {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .custom-date-ranges input {
            padding: 8px;
            font-size: 14px;
            border-radius: 5px;
            border: 1px solid #ccc;
            background-color: #fff;
        }

        .custom-date-ranges button {
            padding: 8px 12px;
            background-color: #00bfff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .custom-date-ranges button:hover {
            background-color: #009acd;
        }

        /* No Results Message */
        #attendance-no-results-message {
            display: none;
            /* Initially hidden */
            font-weight: bold;
            font-size: 16px;
            padding: 10px;
            text-align: center;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
        }

        /* Responsive Design */
        @media (max-width: 600px) {
            .modal-content-history {
                width: 95%;
                padding: 10px;
            }

            .custom-date-ranges {
                flex-direction: column;
                gap: 5px;
            }

            .custom-date-ranges input,
            .custom-date-ranges button {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Sidebar -->
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
                    <li class="active"><a href="attendance.php"><i class="fas fa-calendar-check"></i> Attendance</a></li>
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



        <div class="content-wrapper">
            <main class="main-content">
                <div class="header">
                    <h2>Attendance <i class="fas fa-calendar-check"></i></h2>
                </div>
                <button class="attendance-btn" onclick="openAttendanceModal()">Add Attendance <i class="fas fa-plus"></i></button>
                <div class="controls">
                    <form method="GET" action="attendance.php" class="entities-control">
                        <div class="filter-row">
                            <label for="filterByDate">Filter by Date:</label>
                            <select name="date_filter" id="filterByDate" onchange="this.form.submit()">
                                <option value="all" <?php echo ($date_filter == 'all') ? 'selected' : ''; ?>>All</option>
                                <option value="today" <?php echo ($date_filter == 'today') ? 'selected' : ''; ?>>Today</option>
                                <option value="yesterday" <?php echo ($date_filter == 'yesterday') ? 'selected' : ''; ?>>Yesterday</option>
                                <option value="last_3_days" <?php echo ($date_filter == 'last_3_days') ? 'selected' : ''; ?>>Last 3 Days</option>
                                <option value="last_week" <?php echo ($date_filter == 'last_week') ? 'selected' : ''; ?>>Last Week</option>
                                <option value="last_month" <?php echo ($date_filter == 'last_month') ? 'selected' : ''; ?>>Last Month</option>
                                <option value="custom" <?php echo ($date_filter == 'custom') ? 'selected' : ''; ?>>Custom Range</option>
                            </select>
                        </div>

                        <!-- Custom date range picker -->
                        <div id="customDateRange" class="custom-date-range" style="display: <?php echo ($date_filter == 'custom') ? 'block' : 'none'; ?>;">
                            <div class="date-field">
                                <label for="startDate"></label>
                                <input type="date" name="start_date" id="startDate" value="<?php echo isset($_GET['start_date']) ? $_GET['start_date'] : ''; ?>">
                                <label for="endDate"></label>
                                <input type="date" name="end_date" id="endDate" value="<?php echo isset($_GET['end_date']) ? $_GET['end_date'] : ''; ?>">
                                <button type="submit" class="apply-btn">Apply Filter</button>
                            </div>
                        </div>

                    </form>

                    <div>
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                        <form class="search-bar" id="searchForm">
                            <input type="text" id="searchInput" placeholder="Search by name..." value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit"><i class="fas fa-search"></i></button>
                        </form>
                        <audio id="successBeepSound" src="/brew+flex/assets/Barcode_scanner_beep_sound_sound_effect_[_YouConvert.net_].mp3"></audio>
                        <audio id="thankyouBeepSound" src="/brew+flex/assets/THANK_YOU_ATTENDANCE.mp3"></audio>
                        <audio id="errorBeepSound" src="/brew+flex/assets/Error_-_Sound_Effect_Non_copyright_sound_effects_FeeSou_[_YouConvert.net_].mp3"></audio>

                    </div>
                </div>
<!-- Attendance Table -->
<div class="table-container">
    <?php if ($date_filter == 'today'): ?>
    <?php endif; ?>
    <div class="table-wrapper">
        <div class="scrollable-container">
            <table>
                <thead>
                    <tr>
                        <th>Member ID</th>
                        <th>Name</th>
                        <th>Check-In Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="attendanceTable">
                    <?php if (empty($attendance_records)): ?>
                        <tr id="noResultsRow">
                            <td colspan="4" class="no-results">No results found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($attendance_records as $record): ?>
                            <tr>
                                <td class="center-align"><?php echo htmlspecialchars($record['member_id']); ?></td>
                                <td class="center-align"><?php echo htmlspecialchars($record['first_name'] . ' ' . $record['last_name']); ?></td>
                                <td class="center-align"><?php echo htmlspecialchars($record['formatted_date_time']); ?></td>
                                <td class="center-align">
                                    <button class="action-btn view-btn" onclick="viewAttendanceHistory(<?php echo $record['attendance_id']; ?>)">View</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

                <!-- Total Attendance Summary -->
                <div class="attendance-summary">
                    <span>Total Attendance: <?php echo count($attendance_records); ?></span>
                </div>
        </div>
        <!-- Modal for Attendance -->
        <div id="modalAttendanceSearch" class="modal-attendance">
            <div class="modal-content-attendance">
                <span class="close" onclick="closeAttendanceModal()">&times;</span>
                <h3>Select Member to Mark Attendance</h3>

                <!-- Search Bar -->
                <div class="search-container">
                    <input type="text" id="searchMember" placeholder="Search for a member..." onkeyup="filterMembers()" />
                    <i class="fas fa-search search-icon"></i>
                </div>

                <!-- Member Dropdown List -->
                <select id="selectMember" name="selectMember" onchange="showSelectedMember()" size="5" style="width: 100%; height: 150px; overflow-y: auto; font-size: 14px; border: 1px solid #ccc; border-radius: 5px;">
                    <?php
                    // Fetch all members from the database
                    $query = "SELECT member_id, CONCAT(first_name, ' ', last_name) AS full_name FROM members";
                    $result = $conn->query($query);
                    while ($member = $result->fetch_assoc()) {
                        echo "<option value='{$member['member_id']}' data-name='{$member['full_name']}'>"
                            . htmlspecialchars($member['full_name']) . "</option>";
                    }
                    ?>
                </select>

                <!-- Container to display selected member's data -->
                <div id="selectedMemberDetails" style="margin-top: 10px; padding: 1px; border-top: 1px solid #ccc;">
                    <p style="color: #555; font-size: 14px;">Selected Member Details:</p>
                    <div id="memberDetailsContent" style="font-size: 16px; font-weight: bold; color: #333;"></div>
                </div>

                <button class="attendance-btns" onclick="markAttendance()">Mark Attendance</button>
                <button class="scanner-btn" onclick="openQRScanner()">Scan QR Code</button>
            </div>
        </div>



        <!-- Modal for QR Scanner -->
        <div id="qrScannerModal" class="modal-scanner">
            <div class="qrscanner-modal">
                <span class="close" onclick="closeQRScanner()">&times;</span>
                <h3>Scan QR Code</h3>
                <video id="interactive" class="viewport" autoplay></video>
            </div>
        </div>
        <!-- Attendance History Modal -->
        <div id="attendanceHistoryModal" class="modal-attendance-history">
            <div class="modal-content-history">
                <span class="close-btn" onclick="closeAttendanceHistoryModal()">&times;</span>
                <h3>Attendance History <i class="fas fa-calendar-check"></i></h3>
                <!-- Date Filter Section -->
                <div id="dateFilterSection" class="datefilter">
                    <label for="attendance-date-range">Filter by Date:</label>
                    <select id="attendance-date-range" onchange="applyAttendanceDateFilter()">
                        <option value="all">All</option>
                        <option value="today">Today</option>
                        <option value="yesterday">Yesterday</option>
                        <option value="last3days">Last 3 Days</option>
                        <option value="lastweek">Last Week</option>
                        <option value="lastmonth">Last Month</option>
                        <option value="custom">Custom Range</option>
                    </select>

                    <!-- Custom Date Range Inputs -->
                    <div id="attendance-custom-range" class="custom-date-ranges" style="display: none;">
                        <input type="date" id="attendance-start-date" />
                        <input type="date" id="attendance-end-date" />
                        <button onclick="applyCustomAttendanceDate()">Apply</button>
                    </div>
                </div>



                <!-- Attendance Table -->
                <table>
                    <thead>
                        <tr>
                            <th>Member ID</th>
                            <th>Name</th>
                            <th>Check-In Date</th>
                        </tr>
                    </thead>
                    <tbody id="attendanceHistoryDetails">
                        <!-- Dynamic rows will be inserted here -->
                    </tbody>
                </table>
                <div id="attendance-no-results-message" style="display: none; color: red; font-weight: bold;">
                    No results found for <span id="date-range-text">the selected date range</span>.
                </div>

                <!-- Total Attendance Section -->
                <div id="totalAttendanceSection" style="
            display: flex; 
    justify-content: center; 
    margin-top: 10px; 
    font-weight: bold; 
    font-size: 16px; 
    background: #f8f9fa; 
    padding: 10px; 
    border: 1px solid #ddd; 
    border-radius: 5px; 
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    ">
                    Total Attendance: <span id="totalAttendance">0</span>
                </div>

            </div>
        </div>

        <script>
            // Function to apply the selected date filter
            function applyAttendanceDateFilter() {
                const filterValue = document.getElementById('attendance-date-range').value;
                const rows = document.querySelectorAll('#attendanceHistoryDetails tr');
                const noResultsMessage = document.getElementById('attendance-no-results-message');
                const customRange = document.getElementById('attendance-custom-range');
                const dateRangeText = document.getElementById('date-range-text');

                // Show or hide custom date inputs when 'Custom Range' is selected
                if (filterValue === 'custom') {
                    customRange.style.display = 'block';
                } else {
                    customRange.style.display = 'none';
                }

                let filteredRows = [];
                rows.forEach(row => {
                    const dateCell = row.cells[2]; // Check-In Date is in the 3rd column
                    const checkInDate = dateCell ? new Date(dateCell.textContent) : null;
                    let showRow = false;

                    // Filter based on selected range
                    switch (filterValue) {
                        case 'all':
                            showRow = true; // Show all rows
                            break;
                        case 'today':
                            showRow = isToday(checkInDate);
                            break;
                        case 'yesterday':
                            showRow = isYesterday(checkInDate);
                            break;
                        case 'last3days':
                            showRow = isWithinLastNDays(checkInDate, 3);
                            break;
                        case 'lastweek':
                            showRow = isWithinLastNDays(checkInDate, 7);
                            break;
                        case 'lastmonth':
                            showRow = isWithinLastMonth(checkInDate);
                            break;
                    }

                    row.style.display = showRow ? '' : 'none';
                    if (showRow) filteredRows.push(row); // Collect filtered rows
                });

                // Update attendance totals and show no results message if applicable
                updateAttendanceTotals(filteredRows);
                if (filteredRows.length > 0) {
                    noResultsMessage.style.display = 'none';
                } else {
                    noResultsMessage.style.display = 'block';
                    dateRangeText.textContent = getDateRangeText(filterValue); // Update the range text
                }
            }

            // Function to apply custom date filter
            function applyCustomAttendanceDate() {
                const startDate = document.getElementById('attendance-start-date').value;
                const endDate = document.getElementById('attendance-end-date').value;
                const rows = document.querySelectorAll('#attendanceHistoryDetails tr');
                const noResultsMessage = document.getElementById('attendance-no-results-message');

                // Validate custom date range
                if (!startDate || !endDate) {
                    alert('Please select both start and end dates.');
                    return;
                }

                const start = new Date(startDate);
                const end = new Date(endDate);
                let filteredRows = [];

                rows.forEach(row => {
                    const dateCell = row.cells[2]; // Check-In Date column
                    const checkInDate = dateCell ? new Date(dateCell.textContent) : null;

                    if (checkInDate >= start && checkInDate <= end) {
                        row.style.display = ''; // Show the row if it's within the date range
                        filteredRows.push(row);
                    } else {
                        row.style.display = 'none'; // Hide the row if it's outside the range
                    }
                });

                // Update attendance totals and display no results message if applicable
                updateAttendanceTotals(filteredRows);
                if (filteredRows.length > 0) {
                    noResultsMessage.style.display = 'none';
                } else {
                    noResultsMessage.style.display = 'block';
                    document.getElementById('date-range-text').textContent = `${formatDate(start)} to ${formatDate(end)}`; // Update custom range text
                }
            }

            // Helper function to get the date range text
            function getDateRangeText(filterValue) {
                switch (filterValue) {
                    case 'today':
                        return 'today';
                    case 'yesterday':
                        return 'yesterday';
                    case 'last3days':
                        return 'the last 3 days';
                    case 'lastweek':
                        return 'last week';
                    case 'lastmonth':
                        return 'last month';
                    case 'custom':
                        return 'the selected custom range';
                    default:
                        return 'the selected date range';
                }
            }

            // Helper function to check if a date is today
            function isToday(date) {
                const today = new Date();
                return date.getDate() === today.getDate() &&
                    date.getMonth() === today.getMonth() &&
                    date.getFullYear() === today.getFullYear();
            }

            // Helper function to check if a date is yesterday
            function isYesterday(date) {
                const yesterday = new Date();
                yesterday.setDate(yesterday.getDate() - 1);
                return date.getDate() === yesterday.getDate() &&
                    date.getMonth() === yesterday.getMonth() &&
                    date.getFullYear() === yesterday.getFullYear();
            }

            // Helper function to check if a date is within the last N days
            function isWithinLastNDays(date, n) {
                const now = new Date();
                now.setDate(now.getDate() - n);
                return date >= now;
            }

            // Helper function to check if a date is within the last month
            function isWithinLastMonth(date) {
                const now = new Date();
                now.setMonth(now.getMonth() - 1);
                return date >= now;
            }

            // Helper function to format date as MM/DD/YYYY
            function formatDate(date) {
                const month = (date.getMonth() + 1).toString().padStart(2, '0');
                const day = date.getDate().toString().padStart(2, '0');
                const year = date.getFullYear();
                return `${month}/${day}/${year}`;
            }

            // Function to update the total attendance count
            function updateAttendanceTotals(filteredRows) {
                const totalAttendance = filteredRows.length;
                document.getElementById('totalAttendance').textContent = totalAttendance;
            }

            // Function to fetch and view attendance history
            function viewAttendanceHistory(attendanceId) {
                const xhr = new XMLHttpRequest();
                xhr.open('GET', 'fetch_attendance_history.php?attendance_id=' + attendanceId, true);

                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4) {
                        const attendanceDetails = document.getElementById('attendanceHistoryDetails');

                        if (xhr.status == 200) {
                            // Insert fetched data into the table body
                            attendanceDetails.innerHTML = xhr.responseText.trim();

                            // Handle empty response
                            if (xhr.responseText.trim() === "") {
                                attendanceDetails.innerHTML = "<tr><td colspan='3' style='text-align: center;'>No attendance history available for this record.</td></tr>";
                                document.getElementById('totalAttendance').textContent = "0"; // Set total attendance to 0
                            } else {
                                // Calculate total attendance
                                updateTotalAttendance();
                            }

                            // Display the modal
                            document.getElementById('attendanceHistoryModal').style.display = 'flex';
                        } else {
                            console.error('Error:', xhr.statusText);
                            attendanceDetails.innerHTML = "<tr><td colspan='3' style='text-align: center; color: red;'>An error occurred while fetching attendance data.</td></tr>";
                        }
                    }
                };
                xhr.send();
            }

            // Function to calculate and update total attendance
            function updateTotalAttendance() {
                const rows = document.querySelectorAll('#attendanceHistoryDetails tr');
                const totalAttendance = rows.length; // Count the number of rows

                // Update the total attendance in the UI
                document.getElementById('totalAttendance').textContent = totalAttendance;
            }

            // Function to close Attendance History Modal and reset filters
            function closeAttendanceHistoryModal() {
                document.getElementById('attendanceHistoryModal').style.display = 'none';

                // Reset the filters when the modal is closed
                resetAttendanceFilter();
            }

            // Function to reset the attendance filters
            function resetAttendanceFilter() {
                // 1. Reset the filter dropdown to 'all' (show all rows)
                document.getElementById('attendance-date-range').value = 'all';

                // 2. Hide the custom date range input fields and clear the date inputs
                document.getElementById('attendance-custom-range').style.display = 'none';
                document.getElementById('attendance-start-date').value = '';
                document.getElementById('attendance-end-date').value = '';

                // 3. Get all the rows and show them (reset the filter to show all rows)
                const rows = document.querySelectorAll('#attendanceHistoryDetails tr');
                rows.forEach(row => {
                    row.style.display = ''; // Show all rows
                });

                // 4. Update the attendance totals (without any filters applied)
                updateAttendanceTotals(rows);

                // 5. Hide the "No results found" message if any rows exist
                const noResultsMessage = document.getElementById('attendance-no-results-message');
                if (rows.length > 0) {
                    noResultsMessage.style.display = 'none'; // Hide "No results found" message
                } else {
                    noResultsMessage.style.display = 'block'; // Display "No results found" if no rows
                }

                // 6. Clear the date range text
                document.getElementById('date-range-text').textContent = ''; // Clear the date range message
            }


            // Function to filter members in the dropdown based on search query
            function filterMembers() {
                const searchQuery = document.getElementById("searchMember").value.toLowerCase();
                const memberList = document.querySelectorAll("#selectMember option");

                memberList.forEach(option => {
                    const memberName = option.textContent.toLowerCase();
                    if (memberName.includes(searchQuery)) {
                        option.style.display = "";
                    } else {
                        option.style.display = "none";
                    }
                });
            }

            // Display selected member details
            function showSelectedMember() {
                const selectedMember = document.getElementById('selectMember');
                const memberDetails = document.getElementById('memberDetailsContent');
                const selectedOption = selectedMember.options[selectedMember.selectedIndex];

                if (selectedOption && selectedOption.dataset.name) {
                    memberDetails.textContent = `Name: ${selectedOption.dataset.name} (ID: ${selectedOption.value})`;
                } else {
                    memberDetails.textContent = "";
                }
            }
        </script>
        <script src="/brew+flex/js/attendance.js"></script>
</body>

</html>