<?php
session_start();

// Check if the user is logged in and has the role 'admin'
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'employee'])) {
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
$action = isset($_GET['action']) ? $_GET['action'] : 'view';                // Default to View Only

// Set table and date column based on report type
$table = ($reportType === 'inventory') ? 'product' : 'orders';
$dateColumn = ($reportType === 'inventory') ? 'last_restocked_date' : 'date_ordered';

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

// Fetch data based on selected report type
if ($reportType === 'orders') {
    // Query for orders
    $sql = "
        SELECT orders.*, 
               product.product_name, 
               product.quantity_in_stock, 
               product.price_per_unit, 
               product.last_restocked_date,
               product.supplier_id, 
               supplier.supplier_name 
        FROM orders
        INNER JOIN product ON orders.product_id = product.product_id
        INNER JOIN supplier ON product.supplier_id = supplier.supplier_id
        WHERE $dateCondition
    ";
} else if ($reportType === 'inventory') {
    // Query for inventory (from product table)
    $sql = "
        SELECT product.*, 
               supplier.supplier_name 
        FROM product
        INNER JOIN supplier ON product.supplier_id = supplier.supplier_id
        WHERE $dateCondition
    ";
}

$result = mysqli_query($con, $sql);

// Handle actions
if ($action === 'download') {
    // Generate PDF
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);

    // Calculate table position
$totalWidth = 20 + 40 + 40 + 40; // Total table width = 140 mm
$pageWidth = $pdf->GetPageWidth();
$xPosition = ($pageWidth - $totalWidth) / 2;

// Set the X position for the table headers
$pdf->SetX($xPosition);

    // Generate timestamp
    $currentDateTime = date('d F Y, h:i A');
    $timezone = 'Malaysia/Kuala Lumpur';

    // Report title
    $reportTitle = "Houdstock Report";
    $pdf->Cell(0, 10, $reportTitle, 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, "Generated on: $currentDateTime (Timezone: $timezone)", 0, 1, 'C');
    $pdf->Ln(10); // Add spacing

    // Adjust title based on timeframe
    $title = ucfirst($reportType) . ' Report (' . ucfirst($timeframe) . ')';
    if ($timeframe === 'specific_date') {
        $title = ucfirst($reportType) . ' Report (Specific Date: ' . $specificDate . ')';
    }
    
    // Subtitle for specific date
    if ($timeframe === 'specific_date' && !empty($specificDate)) {
        $subtitle = "Report for Date: " . date('d F Y', strtotime($specificDate));
        $pdf->SetFont('Arial', 'I', 12);
        $pdf->Cell(0, 10, $subtitle, 0, 1);
        $pdf->Ln(10); // Add spacing
    }

    // Add column headers based on report type
    if ($reportType === 'orders')
     {
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetX($xPosition);
        $pdf->Cell(20, 10, 'Order ID', 1, 0, 'C');
        $pdf->Cell(40, 10, 'Supplier Name', 1, 0, 'C');
        $pdf->Cell(40, 10, 'Total Price', 1, 0, 'C');
        $pdf->Cell(40, 10, 'Date Ordered', 1, 0, 'C');
        $pdf->Ln();
    } else if ($reportType === 'inventory') {
        $totalWidth = 25 + 60 + 25 + 40 + 40; // Total = 190 mm
        $pageWidth = $pdf->GetPageWidth();
        $xPosition = ($pageWidth - $totalWidth) / 2;

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetX($xPosition);
        $pdf->Cell(25, 10, 'Product ID', 1, 0, 'C');
        $pdf->Cell(70, 10, 'Product Name', 1, 0, 'C');
        $pdf->Cell(10,10, 'Qty', 1, 0, 'C');
        $pdf->Cell(25, 10, 'Price', 1, 0, 'C');
        $pdf->Cell(40, 10, 'Last Updated', 1, 0, 'C');
        $pdf->Ln();
    }

    // Fetch data for report
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $pdf->SetFont('Arial', '', 12);
            if ($reportType === 'orders') {
                $pdf->SetX($xPosition);
                $pdf->Cell(20, 10, $row['order_id'], 1, 0);
                $pdf->Cell(40, 10, $row['supplier_name'], 1, 0); // Display Supplier Name
                $pdf->Cell(40, 10, $row['total_price'], 1, 0);
                $pdf->Cell(40, 10, $row['date_ordered'], 1, 0);
                $pdf->Ln();
            } else if ($reportType === 'inventory') {
                $totalWidth = 25 + 60 + 25 + 40 + 40; // Total = 190 mm
                $pageWidth = $pdf->GetPageWidth();
                $xPosition = ($pageWidth - $totalWidth) / 2;


                $pdf->SetX($xPosition);
                $pdf->Cell(25, 10, $row['product_id'], 1, 0);
                $pdf->Cell(70, 10, $row['product_name'], 1, 0);
                $pdf->Cell(10, 10, $row['quantity_in_stock'], 1, 0, 'C');
                $pdf->Cell(25, 10, $row['price_per_unit'], 1, 0, 'C');
                $pdf->Cell(40, 10, $row['last_restocked_date'], 1, 0);
                $pdf->Ln();
            }
        }
    } else {
        $pdf->Cell(0, 10, 'No data found for the selected timeframe.', 0, 1);
    }

    // Output the PDF
    $pdf->Output(ucfirst($reportType) . '_Report_' . ucfirst($timeframe) . '.pdf', 'D');
    exit();
} elseif ($action === 'delete' && $reportType === 'orders') {
    // Delete only for orders (not for inventory)
    $deleteSql = "DELETE FROM $table WHERE $dateCondition";
    if (mysqli_query($con, $deleteSql)) {
        $_SESSION['message'] = 'Data deleted successfully.';
    } else {
        $_SESSION['error'] = 'Error deleting data.';
    }
    header("Location: report.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Report</title>
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
    <form method="get" action="report.php" class="mb-4">
        <div class="row">
            <!-- Select Report Type -->
            <div class="col-md-4">
                <label for="report_type">Report Type:</label>
                <select name="report_type" id="report_type" class="form-control">
                    <option value="orders" <?php echo $reportType === 'orders' ? 'selected' : ''; ?>>Orders</option>
                    <option value="inventory" <?php echo $reportType === 'inventory' ? 'selected' : ''; ?>>Stock Inventory</option>
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
            <!-- Specific Date -->
<div class="col-md-4">
    <label for="specific_date">Specific Date:</label>
    <input type="date" name="specific_date" id="specific_date" class="form-control" value="<?php echo $specificDate; ?>">
</div>

            <!-- Select Action -->
            <div class="col-md-4">
                <label for="action">Action:</label>
                <select name="action" id="action" class="form-control">
                    <option value="view" <?php echo $action === 'view' ? 'selected' : ''; ?>>View Data</option>
                    <option value="download" <?php echo $action === 'download' ? 'selected' : ''; ?>>Download PDF</option>
                    <option value="delete" <?php echo $action === 'delete' ? 'selected' : ''; ?>>Delete Data</option>
                </select>
            </div>
        </div>

        <div class="text-center mt-4">
            <button type="submit" class="btn btn-primary">Generate Report</button>
        </div>
    </form>

    <!-- Report Display Table -->
    <div class="table-responsive">
        
        <table class="table table-bordered">
            <thead>
                <tr>
                    <?php if ($reportType === 'orders'): ?>
                        <th>Order ID</th>
                        <th>Supplier Name</th>
                        <th>Total Price</th>
                        <th>Date Ordered</th>
                    <?php elseif ($reportType === 'inventory'): ?>
                        <th>Product ID</th>
                        <th>Product Name</th>
                        <th>Quantity In Stock</th>
                        <th>Price Per Unit</th>
                        <th>Last Restocked Date</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '<tr>';
                        if ($reportType === 'orders') {
                            echo '<td>' . $row['order_id'] . '</td>';
                            echo '<td>' . $row['supplier_name'] . '</td>';
                            echo '<td>' . $row['total_price'] . '</td>';
                            echo '<td>' . $row['date_ordered'] . '</td>';
                        } else if ($reportType === 'inventory') {
                            echo '<td>' . $row['product_id'] . '</td>';
                            echo '<td>' . $row['product_name'] . '</td>';
                            echo '<td>' . $row['quantity_in_stock'] . '</td>';
                            echo '<td>' . $row['price_per_unit'] . '</td>';
                            echo '<td>' . $row['last_restocked_date'] . '</td>';
                        }
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="5">No data available for the selected timeframe.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
    <!-- Back Button -->
<div class="text-center mb-4">
<?php
    // Set the redirection URL based on the role
    $redirectUrl = ($_SESSION['role'] === 'employee') ? 'employee_dashboard.php' : 'admin_dashboard.php';
    ?>
    <button onclick="window.location.href='<?php echo $redirectUrl; ?>';" class="btn btn-secondary">Back</button>
</div>

</div>

</body>
</html>
