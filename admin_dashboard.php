<?php
session_start(); // Start the session at the beginning of the script

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$host = 'localhost';
$dbname = 'enterprise';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

// Fetch and aggregate sales data for each category along with product names and quantities
$query = "SELECT 
                p.table_name AS category,
                IF(p.table_name = 'console', c.name, IF(p.table_name = 'games', g.name, a.name)) AS product_name,
                SUM(p.quantity) AS quantity_sold,
                SUM(p.quantity * 
                IF(p.table_name = 'console', c.price, 
                   IF(p.table_name = 'games', g.price, a.price))) AS total_sales
          FROM payment_items p
          LEFT JOIN console c ON p.product_id = c.id AND p.table_name = 'console'
          LEFT JOIN games g ON p.product_id = g.id AND p.table_name = 'games'
          LEFT JOIN accessories a ON p.product_id = a.id AND p.table_name = 'accessories'
          GROUP BY p.table_name, product_name
          ORDER BY p.table_name, product_name";

$stmt = $conn->prepare($query);
$stmt->execute();
$salesData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare data for the pie chart
$categories = [];
$totalSales = 0;

foreach ($salesData as $row) {
    $categories[$row['category']] = ($categories[$row['category']] ?? 0) + $row['total_sales'];
    $totalSales += $row['total_sales'];
}

// Prepare data for chart
$chartLabels = array_keys($categories);
$chartData = array_values($categories);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report</title>
    <link rel="stylesheet" href="salesreport.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script> <!-- Plugin for data labels -->
</head>

<body>
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

    <h1>Sales Report</h1>

    <!-- Pie Chart for overall sales distribution by category -->
    <div class="chart-container">
        <canvas id="salesChart"></canvas>
    </div>

    <h2>Sales Data Table</h2>
    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th>Product Name</th>
                <th>Quantity Sold</th>
                <th>Total Sales (RM)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($salesData as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['category']) ?></td>
                    <td><?= htmlspecialchars($row['product_name']) ?></td>
                    <td><?= htmlspecialchars($row['quantity_sold']) ?></td>
                    <td><?= number_format($row['total_sales'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Add the Print button -->
    <button id="printReport" class="styled-button">Download Report as PDF</button>

    <style>
        /* Style for the Print button */
        .styled-button {
            background-color: #4CAF50; /* Green background */
            color: white; /* White text */
            font-size: 16px; /* Font size */
            font-family: 'Arial', sans-serif; /* Font family */
            padding: 10px 20px; /* Padding around the text */
            border: none; /* No border */
            border-radius: 5px; /* Rounded corners */
            cursor: pointer; /* Pointer cursor on hover */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2); /* Subtle shadow */
            transition: background-color 0.3s ease, transform 0.2s ease; /* Smooth transitions */
        }

        /* Hover effect */
        .styled-button:hover {
            background-color: #45a049; /* Slightly darker green on hover */
            transform: scale(1.05); /* Slight zoom effect */
        }

        /* Active state effect */
        .styled-button:active {
            background-color: #388e3c; /* Even darker green */
            transform: scale(0.95); /* Slight shrink effect */
        }
    </style>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>
        // Data for the pie chart
        const chartLabels = <?= json_encode($chartLabels) ?>;
        const chartData = <?= json_encode($chartData) ?>;

        // Calculate percentages for each category
        const totalSales = chartData.reduce((acc, curr) => acc + curr, 0);
        const chartDataPercentages = chartData.map(value => ((value / totalSales) * 100).toFixed(2));

        // Create the pie chart
        const ctx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: chartLabels,
                datasets: [{
                    label: 'Sales Distribution by Category',
                    data: chartData,
                    backgroundColor: [
                        '#FF6384',
                        '#36A2EB',
                        '#FFCE56',
                    ],
                    borderColor: '#fff',
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Sales Distribution by Category'
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                const label = context.label || '';
                                const value = context.raw;
                                const percentage = chartDataPercentages[context.dataIndex];
                                return `${label}: RM ${value.toFixed(2)} (${percentage}%)`;
                            }
                        }
                    },
                    datalabels: {
                        formatter: (value, context) => {
                            const percentage = chartDataPercentages[context.dataIndex];
                            return `RM ${value.toFixed(2)}\n(${percentage}%)`;
                        },
                        color: '#fff',
                        font: {
                            weight: 'bold'
                        }
                    }
                }
            },
            plugins: [ChartDataLabels] // Enable the plugin for datalabels
        });

        // Generate PDF on button click
        document.getElementById('printReport').addEventListener('click', function () {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            const chartContainer = document.querySelector('.chart-container');
            const tableElement = document.querySelector('table');

            // Capture the chart with a higher scale for better resolution
            html2canvas(chartContainer, { scale: 2 }).then(chartCanvas => {
                const chartImage = chartCanvas.toDataURL('image/png');

                // Add the chart to the PDF
                const pdfWidth = 190; // Width in mm for A4 paper
                const aspectRatio = chartCanvas.width / chartCanvas.height;
                const pdfHeight = pdfWidth / aspectRatio;

                doc.addImage(chartImage, 'PNG', 10, 10, pdfWidth, pdfHeight);

                // Capture the table with a higher scale
                html2canvas(tableElement, { scale: 2 }).then(tableCanvas => {
                    const tableImage = tableCanvas.toDataURL('image/png');

                    // Add a new page for the table
                    doc.addPage();
                    doc.addImage(tableImage, 'PNG', 10, 10, 190, 0); // Adjust height if needed

                    // Save the PDF
                    doc.save('Sales_Report.pdf');
                });
            });
        });
    </script>
</body>

</html>
