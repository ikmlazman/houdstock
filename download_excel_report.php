<?php
session_start();
require_once 'connect.php';

// Function to fetch report data
function fetchReportData($con) {
    $sql = "SELECT * FROM product"; // Example table; change it as per your project needs
    $result = mysqli_query($con, $sql);
    
    $data = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
    }
    return $data;
}

// Fetch data to generate the report
$reportData = fetchReportData($con);

// Load PHPExcel library
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Create new Spreadsheet object
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set header row
$sheet->setCellValue('A1', 'ID');
$sheet->setCellValue('B1', 'Item Name');
$sheet->setCellValue('C1', 'Quantity');
$sheet->setCellValue('D1', 'Price Per Unit');
$sheet->setCellValue('E1', 'Date Added');

// Fill data
$row = 2; // Start from the second row
foreach ($reportData as $data) {
    $sheet->setCellValue('A' . $row, $data['product_id']);
    $sheet->setCellValue('B' . $row, $data['product_name']);
    $sheet->setCellValue('C' . $row, $data['quantity_in_stock']);
    $sheet->setCellValue('D' . $row, $data['price_per_unit']);
    $sheet->setCellValue('E' . $row, $data['date_added']);
    $row++;
}

// Set header for download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="inventory_report.xlsx"');
header('Cache-Control: max-age=0');

// Create writer and save the file
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();
