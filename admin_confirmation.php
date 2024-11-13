<?php
// Check connection
session_start(); // Ensure session is started to access session variables
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: LoginRegister.php");
    exit;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

// Function to send email
function sendReceiptEmail($email, $full_name, $total_amount, $status, $products, $address)
{
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'wongbiu627@gmail.com';
        $mail->Password = 'kpzl strz yrnc ntlz';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('no-reply@example.com', 'Game4Life');
        $mail->addAddress($email, $full_name);

        // Email content based on status
        $mail->isHTML(true);
        $mail->Subject = "Payment Status - $status";

        $productDetails = '';
        foreach ($products as $product) {
            $productDetails .= "<li>{$product['name']} (x{$product['quantity']})</li>";
        }

        $mail->Body = "
            <h1>Payment $status</h1>
            <p>Dear $full_name,</p>
            <p>Your payment of <strong>RM$total_amount</strong> has been <strong>$status</strong>.</p>
            <p><strong>Products Purchased:</strong></p>
            <ul>$productDetails</ul>
            <p><strong>Delivery Address:</strong><br>$address</p>
            <p>Thank you for your purchase!</p>";

        // Send the email
        $mail->send();
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

function sendLowStockEmail($admin_email, $product_name, $new_stock)
{
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'wongbiu627@gmail.com';
        $mail->Password = 'kpzl strz yrnc ntlz';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('no-reply@example.com', 'Game4Life');
        $mail->addAddress($admin_email);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = "Low Stock Alert for $product_name";

        $mail->Body = "
            <h1>Low Stock Alert</h1>
            <p>Dear Admin,</p>
            <p>The stock for <strong>$product_name</strong> has dropped to $new_stock units. Please review the inventory.</p>
            <p>Best regards, <br>Game4Life Team</p>";

        // Send the email
        $mail->send();
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

// Database connection
$host = 'localhost';
$dbname = 'enterprise';
$username = 'root';
$password = '';
$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['action'])) {
    $payment_id = $_POST['payment_id'];
    $action = $_POST['action'];

    // Fetch payment details
    $payment_query = "SELECT * FROM payments WHERE id = $payment_id";
    $payment_result = mysqli_query($conn, $payment_query);
    $payment_data = mysqli_fetch_assoc($payment_result);

    // Check if the payment status is already 'accepted'
    if ($payment_data['status'] === 'accepted') {
        header("Location: admin_confirmation.php");
        exit;
    }

    // Fetch product details for this payment
    $products_query = "SELECT pi.product_id, pi.quantity, pi.table_name 
                      FROM payment_items pi
                      WHERE pi.payment_id = $payment_id";
    $products_result = mysqli_query($conn, $products_query);

    $products = array();
    while ($product_item = mysqli_fetch_assoc($products_result)) {
        $table_name = $product_item['table_name'];
        $product_id = $product_item['product_id'];

        $name_query = "SELECT name FROM $table_name WHERE id = $product_id";
        $name_result = mysqli_query($conn, $name_query);
        $product_data = mysqli_fetch_assoc($name_result);

        $products[] = array(
            'name' => $product_data['name'],
            'quantity' => $product_item['quantity']
        );
    }

    $status = $action === 'accept' ? 'accepted' : 'rejected';

    mysqli_data_seek($products_result, 0);

    $insufficient_stock = false;
    $insufficient_message = '';

    // Check if stock is sufficient and deduct if 'accept'
    if ($status === 'accepted') {
        while ($product = mysqli_fetch_assoc($products_result)) {
            $table_name = $product['table_name'];
            $product_id = $product['product_id'];
            $quantity_purchased = $product['quantity'];

            $stock_query = "SELECT stock, name FROM $table_name WHERE id = $product_id";
            $stock_result = mysqli_query($conn, $stock_query);
            $stock_data = mysqli_fetch_assoc($stock_result);

            // If stock is insufficient
            if ($stock_data['stock'] < $quantity_purchased) {
                $insufficient_stock = true;
                $insufficient_message = "Insufficient stock for " . $stock_data['name'] . ". Only " . $stock_data['stock'] . " left.";
                break;
            }

            // Update stock query
            $update_stock_query = "UPDATE $table_name SET stock = stock - $quantity_purchased WHERE id = $product_id";
            mysqli_query($conn, $update_stock_query);

            // Check if stock is low after update and send alert if necessary
            $new_stock = $stock_data['stock'] - $quantity_purchased;
            if ($new_stock <= 5) { 
                sendLowStockEmail('reiishubby@gmail.com', $stock_data['name'], $new_stock);
            }
        }
    }

    // If insufficient stock, redirect with a message
    if ($insufficient_stock) {
        $_SESSION['error_message'] = $insufficient_message;
        header("Location: admin_confirmation.php");
        exit;
    }

    // Update payment status if stock was sufficient
    $update_query = "UPDATE payments SET status = '$status' WHERE id = $payment_id";
    mysqli_query($conn, $update_query);

    // Prepare the full address for the email
    $full_address = $payment_data['address'] . ', ' .
        $payment_data['city'] . ' ' .
        $payment_data['zip'] . ', ' .
        $payment_data['state'];

    // Send email notification
    sendReceiptEmail(
        $payment_data['email'],
        $payment_data['full_name'],
        $payment_data['total_amount'],
        $status,
        $products,
        $full_address
    );

    // Redirect after processing
    header("Location: admin_confirmation.php");
    exit;
}

// Fetch all pending payments with items, with optional search filtering
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$query = "
    SELECT p.*, pi.product_id, pi.table_name, SUM(pi.quantity) as total_quantity, p.receipt_image
    FROM payments p
    LEFT JOIN payment_items pi ON p.id = pi.payment_id
";

if (!empty($search)) {
    $query .= " WHERE (p.id LIKE '%$search%' 
                OR p.full_name LIKE '%$search%' 
                OR p.email LIKE '%$search%' 
                OR p.status LIKE '%$search%')";
}

$query .= " GROUP BY p.id, pi.product_id, pi.table_name";
$result = mysqli_query($conn, $query);
?>

<!-- HTML Portion for the confirmation page -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation</title>
    <link rel="stylesheet" href="admin_confirmation.css">
</head>
<header>
    <nav>
        <div class="logo">
            <img src="logo.png" alt="Game4Life Logo">
        </div>
        <ul>
            <li><a href="admin_dashboard.php">Dashboard</a></li>
            <li><a href="admin_banner_upload.php">Banner</a></li>
            <li><a href="admin_confirmation.php">Orders</a></li>
            <li><a href="admineditproducts.php">Edit Product</a></li>
            <li class="dropdown">
                <a href="javascript:void(0)" class="dropbtn">Add</a>
                <div class="dropdown-content">
                    <a href="adminaddconsole.php">Add Console</a>
                    <a href="adminaddgame.php">Add Game</a>
                    <a href="adminaddaccessory.php">Add Accessory</a>
                </div>
            </li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
</header>

<body>
    <?php
    if (isset($_SESSION['error_message'])) {
        echo "<p class='error-message'>" . $_SESSION['error_message'] . "</p>";
        unset($_SESSION['error_message']);
    }
    ?>

    <div class="content">
        <h1>Pending Payments</h1>
        <form method="GET" action="">
            <input type="text" name="search" placeholder="Search by name, email, payment ID or status"
                value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <button type="submit">Search</button>
        </form>

        <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Payment ID</th>
                        <th>Customer Name</th>
                        <th>Email</th>
                        <th>Total Amount</th>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Address</th>
                        <th>Status</th>
                        <th>Receipt</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $previous_payment_id = null;
                    $items = [];

                    while ($payment = mysqli_fetch_assoc($result)) {
                        $product_name = '';
                        $table_name = $payment['table_name'];
                        $product_id = $payment['product_id'];
                        $quantity = $payment['total_quantity'];

                        if ($table_name == 'console') {
                            $product_query = "SELECT name FROM console WHERE id = $product_id";
                        } elseif ($table_name == 'games') {
                            $product_query = "SELECT name FROM games WHERE id = $product_id";
                        } elseif ($table_name == 'accessories') {
                            $product_query = "SELECT name FROM accessories WHERE id = $product_id";
                        }

                        $product_result = mysqli_query($conn, $product_query);
                        if ($product_result && mysqli_num_rows($product_result) > 0) {
                            $product = mysqli_fetch_assoc($product_result);
                            $product_name = $product['name'];
                        } else {
                            $product_name = 'Unknown Product';
                        }

                        if ($payment['id'] != $previous_payment_id) {
                            if ($previous_payment_id !== null) {
                                $address_details = $payment_data['address'] . ', ' . $payment_data['city'] . ' ' . $payment_data['zip'] . ', ' . $payment_data['state'];
                                echo "<tr>
                                    <td>$previous_payment_id</td>
                                    <td>{$payment_data['full_name']}</td>
                                    <td>{$payment_data['email']}</td>
                                    <td>" . number_format($total_amount, 2) . "</td>
                                    <td>" . implode(', ', $items['product_names']) . "</td>
                                    <td>" . implode(', ', $items['quantities']) . "</td>
                                    <td>$address_details</td>
                                    <td>{$payment_data['status']}</td>
                                    <td><button onclick=\"window.open('uploads/{$payment_data['receipt_image']}', '_blank')\">View Receipt</button></td>
                                    <td>
                                        <form method='POST' style='display: inline-block;'>
                                            <input type='hidden' name='payment_id' value='$previous_payment_id'>
                                            <button type='submit' name='action' value='accept'>Accept</button>
                                            <button type='submit' name='action' value='reject'>Reject</button>
                                            <button type='submit' name='action' value='received'>Received</button>
                                        </form>
                                    </td>
                                </tr>";
                            }

                            $previous_payment_id = $payment['id'];
                            $items = ['product_names' => [$product_name], 'quantities' => [$quantity]];
                            $total_amount = $payment['total_amount'];
                            $payment_data = $payment;
                        } else {
                            $items['product_names'][] = $product_name;
                            $items['quantities'][] = $quantity;
                        }
                    }

                    if ($previous_payment_id !== null) {
                        $address_details = $payment_data['address'] . ', ' . $payment_data['city'] . ' ' . $payment_data['zip'] . ', ' . $payment_data['state'];
                        echo "<tr>
                            <td>$previous_payment_id</td>
                            <td>{$payment_data['full_name']}</td>
                            <td>{$payment_data['email']}</td>
                            <td>" . number_format($total_amount, 2) . "</td>
                            <td>" . implode(', ', $items['product_names']) . "</td>
                            <td>" . implode(', ', $items['quantities']) . "</td>
                            <td>$address_details</td>
                            <td>{$payment_data['status']}</td>
                            <td><button onclick=\"window.open('uploads/{$payment_data['receipt_image']}', '_blank')\">View Receipt</button></td>
                            <td>
                                <form method='POST' style='display: inline-block;'>
                                    <input type='hidden' name='payment_id' value='$previous_payment_id'>
                                    <button type='submit' name='action' value='accept'>Accept</button>
                                    <button type='submit' name='action' value='reject'>Reject</button>
                                    <button type='submit' name='action' value='received'>Received</button>
                                </form>
                            </td>
                        </tr>";
                    }
                    ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No payments found.</p>
        <?php endif; ?>
    </div>
</body>

</html>