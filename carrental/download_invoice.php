<?php
session_start();
include('includes/config.php');

if(strlen($_SESSION['login'])==0) {
    header('location:index.php');
} else {
    // Check if booking_id is provided in the URL
    if(isset($_GET['booking_id'])) {
        $booking_id = $_GET['booking_id'];
        
        // Fetch booking details from the database
        $sql = "SELECT tblvehicles.VehiclesTitle, tblbrands.BrandName, tblbooking.FromDate, tblbooking.ToDate, tblVehicles.PricePerDay, DATEDIFF(tblbooking.ToDate, tblbooking.FromDate) as totaldays, tblbooking.BookingNumber
                FROM tblbooking
                JOIN tblvehicles ON tblbooking.VehicleId = tblvehicles.id
                JOIN tblbrands ON tblbrands.id = tblvehicles.VehiclesBrand
                WHERE tblbooking.BookingNumber = :booking_id";
        
        $query = $dbh->prepare($sql);
        $query->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
        $query->execute();
        $booking = $query->fetch(PDO::FETCH_ASSOC);
        
        if($booking) {
            // Generate Invoice in PDF format (Sample code)
            // You need to implement your own logic here to generate PDF invoice
            
            // Sample PDF generation using TCPDF library
            require_once('tcpdf/tcpdf.php');
            
            // Create new PDF document
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            
            // Set document information
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('Your Company');
            $pdf->SetTitle('Invoice');
            $pdf->SetSubject('Invoice');
            $pdf->SetKeywords('Invoice, Car Rental');
            
            // Set default header data
            $pdf->SetHeaderData('', 0, 'Invoice', '');
            
            // Set header and footer fonts
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
            
            // Set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            
            // Set margins
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
            
            // Set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
            
            // Add a page
            $pdf->AddPage();
            
            // Content
            $html = '<h1>Invoice</h1>';
            $html .= '<table border="1" cellpadding="5" cellspacing="0">
                        <tr>
                            <th>Car Name</th>
                            <th>From Date</th>
                            <th>To Date</th>
                            <th>Total Days</th>
                            <th>Rent / Day</th>
                            <th>Total Amount</th>
                        </tr>';
            
            // Display booking details in the invoice
            $html .= '<tr>
                        <td>'.$booking['VehiclesTitle'].', '.$booking['BrandName'].'</td>
                        <td>'.$booking['FromDate'].'</td>
                        <td>'.$booking['ToDate'].'</td>
                        <td>'.$booking['totaldays'].'</td>
                        <td>'.$booking['PricePerDay'].'</td>
                        <td>'.($booking['totaldays'] * $booking['PricePerDay']).'</td>
                    </tr>';
            
            $html .= '</table>';
            
            // Output the HTML content
            $pdf->writeHTML($html, true, false, true, false, '');
            
            // Close and output PDF document
            $pdf->Output('invoice.pdf', 'D'); // D for download
            
            // Exit script to prevent further output
            exit;
            
        } else {
            // Handle case where booking ID doesn't exist
            echo 'Booking not found.';
        }
    } else {
        // Handle case where booking_id is not provided
        echo 'Booking ID is required.';
    }
}
?>