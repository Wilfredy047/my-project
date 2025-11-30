<?php
session_start();
include "include/db.php";
require 'vendor/autoload.php'; // inahitajika PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$username = $_SESSION['username'];
$stmt = $conn->prepare("SELECT id FROM users WHERE username=?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($user_id);
$stmt->fetch();
$stmt->close();

// Pata data ya income
$income_data = [];
$stmt = $conn->prepare("SELECT amount, category, date, period FROM income WHERE user_id=? ORDER BY date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $income_data[] = $row;
}
$stmt->close();

// Pata data ya expenses
$expense_data = [];
$stmt = $conn->prepare("SELECT amount, category, date, period FROM expenses WHERE user_id=? ORDER BY date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $expense_data[] = $row;
}
$stmt->close();

// Create spreadsheet
$spreadsheet = new Spreadsheet();

// Income sheet
$spreadsheet->setActiveSheetIndex(0);
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Income');
$sheet->setCellValue('A1', 'Amount');
$sheet->setCellValue('B1', 'Category');
$sheet->setCellValue('C1', 'Date');
$sheet->setCellValue('D1', 'Period');

$row_num = 2;
foreach ($income_data as $data) {
    $sheet->setCellValue("A$row_num", $data['amount']);
    $sheet->setCellValue("B$row_num", $data['category']);
    $sheet->setCellValue("C$row_num", $data['date']);
    $sheet->setCellValue("D$row_num", ucfirst($data['period']));
    $row_num++;
}

// Expenses sheet
$sheet2 = $spreadsheet->createSheet();
$sheet2->setTitle('Expenses');
$sheet2->setCellValue('A1', 'Amount');
$sheet2->setCellValue('B1', 'Category');
$sheet2->setCellValue('C1', 'Date');
$sheet2->setCellValue('D1', 'Period');

$row_num = 2;
foreach ($expense_data as $data) {
    $sheet2->setCellValue("A$row_num", $data['amount']);
    $sheet2->setCellValue("B$row_num", $data['category']);
    $sheet2->setCellValue("C$row_num", $data['date']);
    $sheet2->setCellValue("D$row_num", ucfirst($data['period']));
    $row_num++;
}

// Download Excel
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="finance_report.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();
