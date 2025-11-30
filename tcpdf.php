<?php
require_once('tcpdf/tcpdf.php'); // hakikisha hii path ipo sahihi

// Create new PDF document
$pdf = new TCPDF();
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 14);

// Andika title
$pdf->Cell(0, 10, 'Hello World with TCPDF!', 0, 1, 'C');

// Andika paragraph ndogo
$pdf->SetFont('helvetica', '', 10);
$pdf->MultiCell(0, 10, "Huu ni mfano rahisi wa PDF inayotengenezwa kwa kutumia TCPDF.\nUnaweza kuongeza maandishi, tables, picha na graphs.");

// Output file (I = inline view kwenye browser)
$pdf->Output('example.pdf', 'I');
