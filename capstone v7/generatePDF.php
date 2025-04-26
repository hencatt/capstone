<?php 
require('fpdf/fpdf.php');
include('variables.php');
include('gad_portal.php');

$pdf = new FPDF('P', 'mm', 'legal');  
$pdf->AddPage();

// Add left logo
$pdf->Image('assets/neust_logo-1-1799093319.png', 10, 10, 20, 20); // X=10, Y=10, Width=25, Height=25

// Add right logo
$pdf->Image('assets/GADlogo.png', 180, 8, 25, 25); // X=175, Y=10, Width=25, Height=25

// Add text in the center
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(25, 71, 145);
$pdf->Cell(91, 5, 'Republic of the Philippines', 0, 1, 'C'); // Centered text

$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 5, 'NUEVA ECIJA UNIVERSITY OF SCIENCE AND TECHNOLOGY', 0, 1, 'C'); // Centered text

$pdf->SetLineWidth(0.5);
$pdf->SetDrawColor(25, 71, 145); // Black color
$pdf->Line(32, 22, 180, 22); // Horizontal line


$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(116, 10, 'Cabanatuan City, Nueva Ecija, Philippines', 0, 1, 'C'); // Centered text
$pdf->Cell(90, 0, 'ISO 9001:2015 CERTIFIED', 0, 1, 'C'); // Centered text

// Add horizontal lines
$pdf->SetDrawColor(25, 71, 145);
$pdf->SetLineWidth(5);
$pdf->Line(1, 40, 250, 40); 

$pdf->SetLineWidth(5);
$pdf->SetDrawColor(255, 165, 0); // Orange color
$pdf->Line(1, 45, 250, 45); 

$pdf->SetLineWidth(0.5);
$pdf->SetDrawColor(0, 0, 0); 
$pdf->Line(0, 48, 250, 48); 




$pdf->Output();
?>