<?php
session_start();

// Check if the user is logged in and has the role 'supplier'
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'supplier') {
    header('Location: login1.php');
    exit();
}

require_once 'connect.php'; // Include your database connection
require_once 'fpdf/fpdf.php'; // Adjust the path as per your project

// Set timezone to Malaysia
date_default_timezone_set('Asia/Kuala_Lumpur');

// Default parameters
$reportType = isset($_GET['report_type']) ? $_GET['report_type'] : 'orders'; // Default to Orders
$timeframe = isset($_GET['timeframe']) ? $_GET['timeframe'] : 'all';         // Default to All Time
$action = isset($_GET['action']) ? $_GET['action'] : 'view';                  // Default to View Only

// Get the logged-in supplier's ID from session
$supplierId = $_SESSION['supplier_id']; // Assuming you store supplier ID in session

// Set table and date column based on report type
$table = ($reportType === 'orders') ? 'orders' : '';
$dateColumn = ($reportType === 'orders') ? 'date_ordered' : '';

// Build date condition
$dateCondition = "1"; // Default condition (all data)
switch ($timeframe) {
    case 'day':
        $dateCondition = "DATE($dateColumn) = CURDATE()";
        break;
    case 'week':
        $dateCondition = "YEARWEEK($dateColumn, 1) = YEARWEEK(CURDATE(), 1)";
        break;
    case 'month':
        $dateCondition = "MONTH($dateColumn) = MONTH(CURDATE()) AND YEAR($dateColumn) = YEAR(CURDATE())";
        break;
    case 'year':
        $dateCondition = "YEAR($dateColumn) = YEAR(CURDATE())";
        break;
    case 'last_month':
        $dateCondition = "MONTH($dateColumn) = MONTH(CURDATE() - INTERVAL 1 MONTH) AND YEAR($dateColumn) = YEAR(CURDATE() - INTERVAL 1 MONTH)";
        break;
    case 'specific_date':
        $specificDate = isset($_GET['specific_date']) ? $_GET['specific_date'] : '';
        $dateCondition = "DATE($dateColumn) = '$specificDate'";
        break;
}

// Query for orders related to the supplier's products
$sql = "
    SELECT orders.*, 
           product.product_name, 
           product.price_per_unit,
           supplier.supplier_name 
    FROM orders
    INNER JOIN product ON orders.product_id = product.product_id
    INNER JOIN supplier ON product.supplier_id = supplier.supplier_id
    WHERE supplier.supplier_id = $supplierId AND $dateCondition
";

$result = mysqli_query($con, $sql);

// Get current timestamp for report generation
$timestamp = date('d M Y, H:i:s'); // Format: 08 Dec 2024, 14:23:15

// Handle actions
if ($action === 'download') {
    // Generate PDF
    $pdf = new FPDF();
    $pdf->AddPage();

    // Set the title for the report
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'Houdstock Report', 0, 1, 'C');  // Title for the report
    $pdf->Ln();

    // Add the report type and timeframe to the title
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, ucfirst($reportType) . ' Report (' . ucfirst($timeframe) . ')', 0, 1, 'C');
    $pdf->Ln();

    // Add timestamp at the top of the PDF
    $pdf->SetFont('Arial', 'I', 12);
    $pdf->Cell(0, 10, 'Generated At: ' . $timestamp, 0, 1, 'C');
    $pdf->Ln();

    // Add column headers based on report type
    if ($reportType === 'orders') {
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(40, 10, 'Order ID', 1);
        $pdf->Cell(40, 10, 'Product Name', 1);
        $pdf->Cell(40, 10, 'Quantity', 1);
        $pdf->Cell(40, 10, 'Total Price', 1);
        $pdf->Cell(40, 10, 'Date Ordered', 1);
        $pdf->Ln();
    }

    // Fetch data for report
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $pdf->SetFont('Arial', '', 12);
            if ($reportType === 'orders') {
                $pdf->Cell(40, 10, $row['order_id'], 1);
                $pdf->Cell(40, 10, $row['product_name'], 1);
                $pdf->Cell(40, 10, $row['order_quantity'], 1);
                $pdf->Cell(40, 10, $row['total_price'], 1);
                $pdf->Cell(40, 10, $row['date_ordered'], 1);
                $pdf->Ln();
            }
        }
    } else {
        $pdf->Cell(0, 10, 'No data found for the selected timeframe.', 0, 1);
    }

    // Output the PDF
    $pdf->Output(ucfirst($reportType) . '_Report_' . ucfirst($timeframe) . '.pdf', 'D');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Supplier Report</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
<div class="container my-5">
    <h2 class="text-center">Generate Reports</h2>

    <!-- Messages -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
    <?php elseif (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <!-- Report Form -->
    <form method="get" action="report_supplier.php" class="mb-4">
        <div class="row">
            <!-- Select Report Type -->
            <div class="col-md-4">
                <label for="report_type">Report Type:</label>
                <select name="report_type" id="report_type" class="form-control">
                    <option value="orders" <?php echo $reportType === 'orders' ? 'selected' : ''; ?>>Orders</option>
                </select>
            </div>

            <!-- Select Timeframe -->
            <div class="col-md-4">
                <label for="timeframe">Timeframe:</label>
                <select name="timeframe" id="timeframe" class="form-control">
                    <option value="all" <?php echo $timeframe === 'all' ? 'selected' : ''; ?>>All Time</option>
                    <option value="day" <?php echo $timeframe === 'day' ? 'selected' : ''; ?>>Today</option>
                    <option value="week" <?php echo $timeframe === 'week' ? 'selected' : ''; ?>>This Week</option>
                    <option value="month" <?php echo $timeframe === 'month' ? 'selected' : ''; ?>>This Month</option>
                    <option value="year" <?php echo $timeframe === 'year' ? 'selected' : ''; ?>>This Year</option>
                    <option value="last_month" <?php echo $timeframe === 'last_month' ? 'selected' : ''; ?>>Last Month</option>
                    <option value="specific_date" <?php echo $timeframe === 'specific_date' ? 'selected' : ''; ?>>Specific Date</option>
                </select>
            </div>

            <!-- Select Action -->
            <div class="col-md-4">
                <label for="action">Action:</label>
                <select name="action" id="action" class="form-control">
                    <option value="view" <?php echo $action === 'view' ? 'selected' : ''; ?>>View</option>
                    <option value="download" <?php echo $action === 'download' ? 'selected' : ''; ?>>Download PDF</option>
                </select>
            </div>
        </div>
        <!-- Specific Date -->
<div class="col-md-4">
    <label for="specific_date">Specific Date:</label>
    <input type="date" name="specific_date" id="specific_date" class="form-control" value="<?php echo $specificDate; ?>">
</div>

        <div class="text-center mt-4">
            <button type="submit" class="btn btn-primary">Generate Report</button>
        </div>
    </form>

    
    <!-- Display Report Table (only when 'view' action is selected) -->
    <?php if ($action === 'view'): ?>
        <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <p class="text-center"><strong>Report Generated At: <?php echo $timestamp; ?></strong></p>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Total Price</th>
                        <th>Date Ordered</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo $row['order_id']; ?></td>
                            <td><?php echo $row['product_name']; ?></td>
                            <td><?php echo $row['order_quantity']; ?></td>
                            <td><?php echo $row['total_price']; ?></td>
                            <td><?php echo $row['date_ordered']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No data found for the selected timeframe.</p>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Back Button -->
<div class="text-center mb-4">
    <?php
        // Set the redirection URL based on the role
        if ($_SESSION['role'] === 'supplier') {
            $redirectUrl = 'supplier_dashboard.php'; // Redirect to supplier dashboard
        } else {
            $redirectUrl = ($_SESSION['role'] === 'employee') ? 'employee_dashboard.php' : 'admin_dashboard.php';
        }
    ?>
    <button onclick="window.location.href='<?php echo $redirectUrl; ?>';" class="btn btn-secondary">Back</button>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
</body>
</html>
