<?php
require_once 'connect.php';
require('fpdf/fpdf.php'); // Include FPDF

// Set the timezone to Malaysia
date_default_timezone_set('Asia/Kuala_Lumpur');

// Fetch data from database
function fetchReportData($con) {
    $sql = "SELECT * FROM product";
    $result = mysqli_query($con, $sql);

    $data = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
    }
    return $data;
}

// Check if the user requested to download the PDF
if (isset($_GET['download']) && $_GET['download'] === 'pdf') {
    $reportData = fetchReportData($con);

    // Create instance of FPDF
    $pdf = new FPDF();
    $pdf->AddPage();

    // Set font for the title
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'Product Inventory Report', 0, 1, 'C');
    $pdf->Ln(5); // Line break

    // Set the current date and time
    $currentDateTime = date("Y-m-d H:i:s");
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, 'Generated on: ' . $currentDateTime, 0, 1, 'C');
    $pdf->Ln(10); // Line break

    // Set font for table headers
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(30, 10, 'Product ID', 1);
    $pdf->Cell(60, 10, 'Product Name', 1);
    $pdf->Cell(30, 10, 'Quantity', 1);
    $pdf->Cell(30, 10, 'Price', 1);
    $pdf->Cell(40, 10, 'Date Added', 1);
    $pdf->Ln(); // Line break for new row

    // Set font for table content
    $pdf->SetFont('Arial', '', 12);

    // Loop through data and add rows to the PDF
    foreach ($reportData as $row) {
        $pdf->Cell(30, 10, $row['product_id'], 1);
        $pdf->Cell(60, 10, $row['product_name'], 1);
        $pdf->Cell(30, 10, $row['quantity_in_stock'], 1);
        $pdf->Cell(30, 10, $row['price_per_unit'], 1);
        $pdf->Cell(40, 10, $row['date_added'], 1);
        $pdf->Ln();
    }

    // Output the PDF for download
    $pdf->Output('D', 'product_inventory_report.pdf');
    exit;
}
?>
