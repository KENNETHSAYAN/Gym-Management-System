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
// Fetch inventory data
$inventory_query = "SELECT * FROM inventory";
$inventory_result = $conn->query($inventory_query);

// Handle inventory update or add request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["id"])) {
        // Update inventory
        $id = $_POST["id"];
        $name = $_POST["name"];
        $type = $_POST["type"];
        $quantity = $_POST["quantity"];
        $price = $_POST["price"];

        $update_query = "UPDATE inventory SET name = ?, type = ?, quantity = ?, price = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ssiii", $name, $type, $quantity, $price, $id);

        if ($stmt->execute()) {
            header("Location: inventory.php");
            exit;
        } else {
            echo "Error updating inventory: " . $stmt->error;
        }
    } elseif (isset($_POST["add_item"])) {
        // Add new item
        $name = $_POST["name"];
        $type = $_POST["type"];
        $quantity = $_POST["quantity"];
        $price = $_POST["price"];
        $date_acquired = $_POST["date_acquired"];

        $insert_query = "INSERT INTO inventory (name, type, quantity, price, date_acquired) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ssiss", $name, $type, $quantity, $price, $date_acquired);

        if ($stmt->execute()) {
            // Successfully added, redirect back to inventory view
            header("Location: inventory.php");
            exit;
        } else {
            echo "Error adding inventory item: " . $stmt->error;
        }
    }
}
// Default query
$inventory_query = "SELECT * FROM inventory WHERE 1";
// Apply category filter if selected
if (isset($_GET['type']) && !empty($_GET['type'])) {
    $category = $_GET['type'];
    $inventory_query .= " AND type = '$category'";
}
// Apply price sorting if selected
if (isset($_GET['price_sort']) && !empty($_GET['price_sort'])) {
    $price_sort = $_GET['price_sort'] === 'asc' ? 'ASC' : 'DESC';
    $inventory_query .= " ORDER BY price $price_sort";
}
// Execute the query
$inventory_result = $conn->query($inventory_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory View - Brew + Flex Gym</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="/brew+flex/css/inventory.css">
    <style>
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
.modal-buttonss {
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
.confirms-buttonss {
    background-color: #71d4fc;
    color: #ffffff;
}

.confirms-buttonss:hover {
    background-color: #71d4fc;
    transform: scale(1.05);
}

/* Cancel Button */
.cancels-buttonss {
    background-color: #ccc;
    color: #333;
}

.cancels-buttonss:hover {
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
                    <li ><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <?php if ($usertype === 'admin'): ?>
                        <li><a href="adminpage.php"><i class="fas fa-cogs"></i> Admin</a></li>
                        <li><a href="managestaff.php"><i class="fas fa-users"></i> Manage Staff</a></li>
                    <?php endif; ?>
                    <li><a href="registration.php"><i class="fas fa-clipboard-list"></i> Registration</a></li>
                    <li><a href="memberspage.php"><i class="fas fa-user-friends"></i> View Members</a></li>
                    <li ><a href="attendance.php"><i class="fas fa-calendar-check"></i> Attendance</a></li>
                    <li ><a href="payment.php"><i class="fas fa-credit-card"></i> Payment</a></li>
                    <li class="active"><a href="inventory.php"><i class="fas fa-boxes"></i> Inventory</a></li>
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
        <div class="modal-buttonss">
            <button class="confirms-buttonss" onclick="handleLogout()">Yes, Log Out</button>
            <button class="cancels-buttonss" onclick="closeLogoutModal()">Cancel</button>
        </div>
    </div>
</div>
</aside>
</nav>

        <!-- Main Content -->
        <div class="content-wrapper">
        <main class="main-content">
            <!-- Inventory Table -->
            <div class="form-container">
                <h3>Inventory List <i class="fas fa-boxes"></i> </h3>
                <div class="table-footer">
                    <button id="addItemButton" onclick="openAddItemModal()">Add Inventory Item<i class="fas fa-plus"></i></button>
                </div>
  <!-- Sort Button and Search Bar Positioned Below the Heading -->
  <div class="sort-container">
            <button id="sortButton" class="btn-sort">Sort <i class="fas fa-sort"></i></button>
            
            <!-- Search Bar -->
            <div class="search-container">
            
                <input type="text" id="searchInput" placeholder="Search by Item Name..."  onkeyup="searchItems()">
                <i class="fas fa-search"></i>
            </div>
        </div>
        

<!-- Sorting Modal -->
<div id="sortModal" class="sort-modal">
    <div class="sort-modal-content">
        <span class="close-modal" onclick="closeModal()">&times;</span>
        <h3>Sort Items</h3>
        <form method="GET" action="inventory.php">
            <!-- Category Sort -->
            <div class="sort-option">
                <label for="item_type">Category:</label>
                <select id="item_type" name="type">
                    <option value="">All Categories</option>
                    <option value="Gym Equipment" <?php echo isset($_GET['type']) && $_GET['type'] === 'Gym Equipment' ? 'selected' : ''; ?>>Gym Equipment</option>
                    <option value="Supplement & Drinks" <?php echo isset($_GET['type']) && $_GET['type'] === 'Supplement & Drinks' ? 'selected' : ''; ?>>Supplement & Drinks</option>
                    <option value="Apparel" <?php echo isset($_GET['type']) && $_GET['type'] === 'Apparel' ? 'selected' : ''; ?>>Apparel</option>
                    <option value="Accessories" <?php echo isset($_GET['type']) && $_GET['type'] === 'Accessories' ? 'selected' : ''; ?>>Accessories</option>
                </select>
            </div>

            <!-- Price Sort -->
            <div class="sort-option">
                <label for="price_sort">Sort by Price:</label>
                <select id="price_sort" name="price_sort">
                    <option value="">Select Sort Order</option>
                    <option value="asc" <?php echo isset($_GET['price_sort']) && $_GET['price_sort'] === 'asc' ? 'selected' : ''; ?>>Price Low to High</option>
                    <option value="desc" <?php echo isset($_GET['price_sort']) && $_GET['price_sort'] === 'desc' ? 'selected' : ''; ?>>Price High to Low</option>
                </select>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn-submit">Apply Sort</button>
        </form>
    </div>
</div>
</div>
<div class="table-container">
    <div class="table-wrapper">
    <div class="scrollable-container">

        <table>
            <thead>
                <tr>
                    <th>Item ID</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Stocks</th>
                    <th>Price</th>
                    
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="inventoryTableBody">
                <?php if ($inventory_result->num_rows > 0): ?>
                    <?php while ($row = $inventory_result->fetch_assoc()): ?>
                        <tr>
                            <td class="center-align"><?php echo htmlspecialchars($row['id']); ?></td>
                            <td class="center-align"><?php echo htmlspecialchars($row['name']); ?></td>
                            <td class="center-align"><?php echo htmlspecialchars($row['type']); ?></td>
                            <td class="center-align"><?php echo htmlspecialchars($row['quantity']); ?></td>
                            <td class="center-align"><?php echo number_format($row['price'], 2); ?></td>
                           
                            <td class="center-align">
                                <button class="btn-update" onclick="openUpdateModal(<?php echo $row['id']; ?>, '<?php echo addslashes($row['name']); ?>', '<?php echo addslashes($row['type']); ?>', <?php echo $row['quantity']; ?>, <?php echo $row['price']; ?>)">
                                    <i class="fas fa-edit"></i> Update
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr id="noResultsRow">
                        <td colspan="7" class="no-results">No inventory items found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
 
            </div>
            </div>
<!-- Update Modal -->
<div class="modal" id="updateModal">
    <div class="modal-content">
        <h3>Update Item <i class="fas fa-boxes"></i></h3>
        <form id="updateItemForm" method="POST" action="inventory.php" onsubmit="return confirmUpdate(event)">
            <input type="hidden" id="item_id" name="id">

            <label for="item_name">Name:</label>
            <input type="text" id="item_name" name="name" required>
            <label for="item_type">Category:</label>
            <select id="item_type" name="type" required>
                <option value="" disabled selected>Select Category</option>
                <option value="Gym Equipment">Gym Equipment</option>
                <option value="Supplement & Drinks">Supplement & Drinks</option>
                <option value="Apparel">Apparel</option>
                <option value="Accessories">Accessories</option>
            </select>
            <label for="item_quantity">Quantity:</label>
            <input type="number" id="item_quantity" name="quantity" required>

            <label for="item_price">Price:</label>
            <input type="number" id="item_price" name="price" required>

            <div class="modal-buttons">
                <button type="submit">Update</button>
                <button type="button" onclick="closeUpdateModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>
<!-- Add Item Modal -->
<div id="addItemModal" class="modal">
    <div class="add-item-modal-content">
        <h3>Add Item <i class="fas fa-boxes"></i></h3>
        <form id="addItemForm" action="inventory.php" method="POST" onsubmit="return confirmAddItem(event)">
            <input type="hidden" name="add_item" value="1">
            <label for="name">Item Name</label>
            <input type="text" id="name" name="name" placeholder="Enter item name" required pattern="[A-Za-z\s]+" title="Only letters and spaces are allowed">
            <label for="type">Category</label>
            <select id="type" name="type" required>
                <option value="" disabled selected>Select Category</option>
                <option value="Gym Equipment">Gym Equipment</option>
                <option value="Supplement & Drinks">Supplement & Drinks</option>
                <option value="Apparel">Apparel</option>
                <option value="Accessories">Accessories</option>
                <option value="Others">Others</option>
            </select>
            <label for="quantity">Quantity</label>
            <input type="number" id="quantity" name="quantity" min="1" required>

            <label for="price">Price</label>
            <input type="number" id="price" name="price" step="0.01" required>

            <div class="add-item-date-acquired-group" id="date-acquired-group">
    <div>
        <label for="date_acquired">Date Acquired</label>
        <input type="date" id="date_acquired" name="date_acquired" required>
    </div>
</div>
            <div class="add-item-modal-buttons">
                <button type="button" class="add-item-cancel-btn" onclick="closeAddItemModal()">Cancel</button>
                <button type="submit" class="add-item-save-btn">Save</button>
            </div>
        </form>
    </div>
</div>
<!-- Confirmation Modal -->
<div id="confirmationModal" class="confirmation-modal">
    <div class="confirmation-content">
        <i class="fas fa-question-circle modal-icon"></i> <!-- Icon for confirmation -->
        <h3>Are you sure you want to submit this action?</h3>
        <div class="modal-buttons">
            <button id="confirmYesBtn" class="confirm-btn">Yes</button>
            <button type="button" id="confirmNoBtn" class="cancel-btn" onclick="closeConfirmationModal()">No</button>
        </div>
    </div>
</div>

        </main>
    </div>
    </div>
    </main>
    </div>
    <script src="/brew+flex/js/inventory.js">
    </script>
<script>
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