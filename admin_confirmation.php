<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer
require 'vendor/autoload.php'; // If using Composer. Adjust if manually downloaded.
require_once __DIR__ . '/vendor/autoload.php';

$host = 'localhost';
$dbname = 'enterprise';
$username = 'root';
$password = '';

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to send email to customer
function sendReceiptEmail($email, $full_name, $total_amount, $status, $receipt_image, $pdf_url) {
    $mail = new PHPMailer(true); // Create an instance of PHPMailer

    try {
        // Server settings
        $mail->isSMTP(); // Set mailer to use SMTP
        $mail->Host = 'smtp.gmail.com'; // Specify main and backup SMTP servers
        $mail->SMTPAuth = true; // Enable SMTP authentication
        $mail->Username = 'wongbiu627@gmail.com'; // SMTP username
        $mail->Password = 'kpzl strz yrnc ntlz'; // SMTP password
        $mail->SMTPSecure = 'tls'; // Enable TLS encryption
        $mail->Port = 587; // TCP port to connect to
        // Recipients
        $mail->setFrom('Game4Life@gmail.com', 'Game4Life');
        $mail->addAddress($email, $full_name); // Add a recipient
        // Content
        $mail->isHTML(true);
        $mail->Subject = "Payment " . ucfirst($status);
        $receipt_url = $receipt_image ? "uploads/$receipt_image" : "No receipt available";

        // Email body content
        $mail->Body = "
            <html>
            <head>
                <title>Receipt for Your Payment</title>
            </head>
            <body>
                <h2>Hello, $full_name!</h2>
                <p>Your payment has been <strong>$status</strong>.</p>
                <table>
                    <tr><th>Full Name:</th><td>$full_name</td></tr>
                    <tr><th>Total Amount:</th><td>RM $total_amount</td></tr>
                    <tr><th>Status:</th><td>$status</td></tr>
                    <tr><th>Receipt:</th><td><a href='$pdf_url'>Download Receipt</a></td></tr>
                </table>
                <p>Thank you for shopping with Game4Life!</p>
            </body>
            </html>
        ";

        $mail->send();
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

// Fetch all pending payments
$query = "SELECT * FROM payments WHERE status = 'pending'";
$result = mysqli_query($conn, $query);

if (isset($_POST['action'])) {
    $payment_id = $_POST['payment_id'];
    $action = $_POST['action'];

    // Fetch the customer's details for email
    $payment_query = "SELECT * FROM payments WHERE id = $payment_id";
    $payment_result = mysqli_query($conn, $payment_query);
    $payment_data = mysqli_fetch_assoc($payment_result);

    $email = $payment_data['email'];
    $full_name = $payment_data['full_name'];
    $total_amount = $payment_data['total_amount'];
    $receipt_image = $payment_data['receipt_image'];

    // Update payment status based on admin action
    if ($action == 'accept') {
        $update_query = "UPDATE payments SET status = 'accepted' WHERE id = $payment_id";
        $status = 'accepted';
    } elseif ($action == 'reject') {
        $update_query = "UPDATE payments SET status = 'rejected' WHERE id = $payment_id";
        $status = 'rejected';
    }

    mysqli_query($conn, $update_query);

    // Generate PDF Receipt
    $pdf_url = generatePdfReceipt($payment_id, $full_name, $total_amount, $status);

    // Send email notification to the customer
    sendReceiptEmail($email, $full_name, $total_amount, $status, $receipt_image, $pdf_url);

    // Refresh page to update the list of pending payments
    header("Location: admin_confirmation.php");
    exit;
}

// Function to generate PDF receipt
function generatePdfReceipt($payment_id, $full_name, $total_amount, $status) {
    // Create new PDF document
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');

    // Remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    // Set document information
    $pdf->SetCreator('Game4Life');
    $pdf->SetAuthor('Game4Life');
    $pdf->SetTitle('Payment Receipt');

    // Add a page
    $pdf->AddPage();

    // Set font
    $pdf->SetFont('helvetica', '', 11);

    // Set margins
    $pdf->SetMargins(20, 20, 20);

    // Add sender info (right-aligned)
    $pdf->Cell(0, 10, 'Sender: Game4Life', 0, 1, 'R');
    $pdf->Ln(5);

    // Add INVOICE title
    $pdf->SetFont('helvetica', 'B', 24);
    $pdf->Cell(0, 15, 'INVOICE', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 11);

    // Add invoice details (left side)
    $pdf->Cell(95, 7, 'Invoice: #' . $payment_id, 0, 0);
    // Add receiver details (right side)
    $pdf->Cell(95, 7, 'Receiver:', 0, 1, 'R');

    // Date and customer details
    $pdf->Cell(95, 7, 'Date: ' . date('d F Y'), 0, 0);
    $pdf->Cell(95, 7, $full_name, 0, 1, 'R');

    $pdf->Cell(95, 7, 'Payment Due: ' . date('d F Y', strtotime('+30 days')), 0, 0);
    $pdf->Cell(95, 7, 'Game4Life Customer', 0, 1, 'R');

    $pdf->Ln(10);

    // Create table header
    $pdf->SetFillColor(240, 240, 240);
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(80, 10, 'Item Description', 1, 0, 'L', true);
    $pdf->Cell(35, 10, 'Price (RM)', 1, 0, 'C', true);
    $pdf->Cell(25, 10, 'Quantity', 1, 0, 'C', true);
    $pdf->Cell(35, 10, 'Subtotal (RM)', 1, 1, 'C', true);

    // Reset font
    $pdf->SetFont('helvetica', '', 11);

    // Add item details
    // In a real application, you would loop through purchased items
    $pdf->Cell(80, 10, 'Game4Life Purchase', 1, 0, 'L');
    $pdf->Cell(35, 10, number_format($total_amount, 2), 1, 0, 'C');
    $pdf->Cell(25, 10, '1', 1, 0, 'C');
    $pdf->Cell(35, 10, number_format($total_amount, 2), 1, 1, 'C');

    // Add total
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(140, 10, 'Total (RM)', 1, 0, 'R');
    $pdf->Cell(35, 10, number_format($total_amount, 2), 1, 1, 'C');

    $pdf->Ln(10);

    // Add payment information
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(0, 10, 'Payment Information:', 0, 1);
    $pdf->SetFont('helvetica', '', 11);
    $pdf->Cell(0, 7, 'Status: ' . ucfirst($status), 0, 1);
    $pdf->Cell(0, 7, 'Payment Method: Online Banking', 0, 1);

    // Add note
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'I', 10);
    $pdf->Cell(0, 10, 'Note: This is a computer-generated receipt. No signature is required.', 0, 1);

    // Save PDF
    // Save PDF with absolute path
    $pdf_file_path = __DIR__ . "/receipts/receipt_$payment_id.pdf";
    $pdf->Output($pdf_file_path, 'F');

    return $pdf_file_path;
}
?>



<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Confirmation</title>
        <link rel="stylesheet" href="admin_confirmation.css">
    </head>
    <body>
        <header>
            <nav>
                <div class="logo">
                    <a href="homepage.php">
                        <img src="logo.png" alt="Game4Life Logo">
                    </a>
                </div>
                <ul>
                    <li><a href="admin_dashboard.php">Dashboard</a></li>
                    <li><a href="admineditproducts.php">Edit Product</a></li>
                    <li><a href="adminaddconsole.php">Add Console</a></li>
                    <li><a href="adminaddgame.php">Add Game</a></li>
                    <li><a href="adminaddaccessory.php">Add Accessory</a></li>
                    <li><a href="admin_confirmation.php">Confirmation</a></li>
                </ul>
                <div class="logout">
                    <a href="logout.php">Logout</a>
                </div>
            </nav>
        </header>

        <h1>Confirmation Page</h1>

        <div class="confirmation-container">
<?php if (mysqli_num_rows($result) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Total Amount</th>
                            <th>Receipt</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo $row['full_name']; ?></td>
                                <td><?php echo $row['email']; ?></td>
                                <td><?php echo $row['total_amount']; ?></td>
                                <td>
        <?php if ($row['receipt_image']): ?>
                                        <a href="uploads/<?php echo $row['receipt_image']; ?>" target="_blank" class="view-receipt">View Receipt</a>
        <?php else: ?>
                                        No Receipt
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <form method="POST" style="display: inline-block;">
                                        <input type="hidden" name="payment_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="action" value="accept">Accept</button>
                                        <button type="submit" name="action" value="reject">Reject</button>
                                    </form>
                                </td>
                            </tr>
    <?php endwhile; ?>
                    </tbody>
                </table>
<?php else: ?>
                <p>No pending payments.</p>
                    <?php endif; ?>
        </div>

    </body>
</html>
