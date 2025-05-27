<?php
require_once __DIR__ . '/fpdf/fpdf.php';
include '../db.php';

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',12);

$pdf->Cell(0, 10, 'Student Data Report', 0, 1, 'C');
$pdf->Ln(5);

// Table headers
$pdf->SetFont('Arial','B',10);
$pdf->Cell(30,10,'Student No.',1);
$pdf->Cell(30,10,'Last Name',1);
$pdf->Cell(30,10,'First Name',1);
$pdf->Cell(50,10,'Email',1);
$pdf->Cell(50,10,'Course',1);
$pdf->Cell(20,10,'Year',1);
$pdf->Cell(10,10,'Blk',1);
$pdf->Cell(30,10,'Phone No.',1);
$pdf->Ln();

$pdf->SetFont('Arial','',9);
$query = "SELECT * FROM student_registration";
$result = mysqli_query($conn, $query);

while ($row = mysqli_fetch_assoc($result)) {
    $pdf->Cell(30,10,$row['studentno'],1);
    $pdf->Cell(30,10,$row['lastname'],1);
    $pdf->Cell(30,10,$row['firstname'],1);
    $pdf->Cell(50,10,$row['email'],1);
    $pdf->Cell(50,10,$row['course'],1);
    $pdf->Cell(20,10,$row['yearlevel'],1);
    $pdf->Cell(10,10,'Blk',1);
    $pdf->Cell(30,10,$row['phone'],1);
    $pdf->Ln();
}

$pdf->Output('D', 'student_data.pdf');
exit();
?>
