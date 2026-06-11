<?php
require('../lib/fpdf186/fpdf.php');
require_once("../config/database.php");

// Set headers for PDF display
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="invoice.pdf"');
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

// Security: Use intval to prevent SQL Injection
$booking_id = intval($_GET['booking_id']);

$sql = "SELECT b.*, u.name, u.email, u.phone, c.car_name, c.brand 
        FROM bookings b 
        JOIN users u ON b.user_id = u.id 
        JOIN cars c ON b.car_id = c.id 
        WHERE b.id = '$booking_id'";

$result = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    die("Booking not found.");
}

class PDF extends FPDF {
    function Header() {
        // Brand Identity
        $this->SetFillColor(0, 0, 0); // Black Header
        $this->Rect(0, 0, 210, 40, 'F');
        
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 24);
        $this->Cell(0, 10, 'DriveEase', 0, 1, 'L');
        
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 5, 'Premium Car Rental Service', 0, 1, 'L');
        
        $this->SetY(15);
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(0, 10, 'INVOICE', 0, 1, 'R');
        $this->Ln(20);
    }

    function Footer() {
        $this->SetY(-30);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128);
        $this->Cell(0, 5, 'Thank you for choosing DriveEase. Drive Safely!', 0, 1, 'C');
        $this->Cell(0, 5, 'Page '.$this->PageNo().'/{nb}', 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

// --- Customer & Booking Info ---
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(100, 7, "BILL TO:", 0, 0);
$pdf->Cell(0, 7, "BOOKING DETAILS:", 0, 1);

$pdf->SetFont('Arial', '', 11);
$pdf->Cell(100, 6, htmlspecialchars($data['name']), 0, 0);
$pdf->Cell(0, 6, "Booking Code: " . $data['booking_code'], 0, 1);

$pdf->Cell(100, 6, htmlspecialchars($data['email']), 0, 0);
$pdf->Cell(0, 6, "Date: " . date('d M, Y', strtotime($data['created_at'])), 0, 1);

$pdf->Ln(10);

// --- Table Header ---
$pdf->SetFillColor(240, 240, 240);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(80, 10, ' Car Description', 1, 0, 'L', true);
$pdf->Cell(40, 10, 'Location', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Days', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Total Price', 1, 1, 'C', true);

// --- Table Body ---
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(80, 12, " ". $data['brand'] . " " . $data['car_name'], 1, 0, 'L');
$pdf->Cell(40, 12, $data['pickup_location'], 1, 0, 'C');
$pdf->Cell(30, 12, $data['total_days'], 1, 0, 'C');
$pdf->Cell(40, 12, "Rs " . number_format($data['total_price'], 2), 1, 1, 'R');

$pdf->Ln(5);

// --- Summary & Totals ---
$pdf->SetX(130);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(40, 8, 'Status:', 0, 0);
$pdf->SetTextColor($data['payment_status'] == 'Success' ? 0 : 200, 100, 0); // Green for success
$pdf->Cell(0, 8, $data['payment_status'], 0, 1, 'R');

$pdf->SetX(130);
$pdf->SetTextColor(0);
$pdf->SetFillColor(0, 0, 0);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(40, 10, 'GRAND TOTAL', 1, 0, 'L', true);
$pdf->Cell(30, 10, "Rs " . number_format($data['total_price'], 2), 1, 1, 'R', true);

// --- Final Output ---
$pdf->Output('I', 'DriveEase_Invoice_'.$data['booking_code'].'.pdf');
?>