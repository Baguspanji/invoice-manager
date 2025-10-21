<?php
namespace App\Http\Controllers;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;

class InvoiceController extends Controller
{
    /**
     * Generate PDF invoice
     */
    public function generatePdf(Invoice $invoice, bool $isPublic = false)
    {
        // Load invoice with client and items
        $invoice->load(['client', 'items']);

        // Generate unique encrypted identifier for QR code
        $invoiceHash = $this->generateInvoiceHash($invoice);

        // Generate QR code for public access
        $qrcode = $this->generateQrCode($invoiceHash);

        // Generate PDF with A4 paper size
        $pdf = PDF::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'qrcode'  => $qrcode,
            'useBadge'    => true,
            'useQr'       => true,
        ]);

        $pdf->setPaper('a4', 'portrait');
        // $pdf->getDomPDF()->set_option('defaultFont', 'Poppins');
        // $pdf->getDomPDF()->fontMetrics->registerFont([
        //     'family' => 'Poppins',
        //     'style' => 'normal',
        //     'weight' => 'normal',
        //     'file' => public_path('fonts/Poppins-Regular.ttf'),
        // ]);

        // Return PDF for display in iframe
        return $pdf->stream("invoice-{$invoice->invoice_number}.pdf");
    }

    /**
     * Download PDF invoice
     */
    public function downloadPdf(Invoice $invoice)
    {
        // Load invoice with client and items
        $invoice->load(['client', 'items']);

        // Generate unique encrypted identifier for QR code
        $invoiceHash = $this->generateInvoiceHash($invoice);

        // Generate QR code for public access
        $qrcode = $this->generateQrCode($invoiceHash);

        // Generate PDF with A4 paper size
        $pdf = PDF::loadView('pdf.invoice', [
            'invoice'     => $invoice,
            'qrcode'      => $qrcode,
            'useBadge'    => true,
            'useQr'       => true,
        ]);
        $pdf->setPaper('a4', 'portrait');

        // Return PDF as download
        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }

    /**
     * Generate a unique hash for the invoice
     */
    protected function generateInvoiceHash(Invoice $invoice)
    {
        // Combine invoice ID with another unique attribute and timestamp for additional security
        $uniqueString = $invoice->id . $invoice->invoice_number . $invoice->created_at->timestamp;

        // Create a shorter hash (first 10 characters of md5)
        return substr(md5($uniqueString), 0, 10);
    }

    /**
     * Generate QR code for public invoice access
     */
    protected function generateQrCode($hash)
    {
        $url = route('invoice.public', ['hash' => $hash]);

        $qrCode = new QrCode(
            $url,
            new Encoding('UTF-8'),
            ErrorCorrectionLevel::High,
            150,
            0,
            RoundBlockSizeMode::Margin,
            new Color(0, 0, 0),
            new Color(255, 255, 255)
        );

        $logo = new Logo(
            path: public_path('images/logo-osi.png'),
            resizeToWidth: 50,
            punchoutBackground: true
        );

        $label = new Label(
            text: 'OSI Invoice',
            textColor: new Color(255, 0, 0)
        );

        $writer = new PngWriter();
        $result = $writer->write($qrCode, $logo);

        return $result->getDataUri();
    }

    /**
     * Public access to invoice via QR code
     */
    public function publicAccess($hash)
    {
        // Find invoice by hash
        // Note: You'll need to implement a way to look up invoices by hash
        // This could be adding a hash column to invoices table, or decoding the hash

        // Example implementation - search through invoices to find matching hash
        $invoices = Invoice::all();

        foreach ($invoices as $invoice) {
            if ($this->generateInvoiceHash($invoice) === $hash) {
                return $this->generatePdf($invoice);
            }
        }

        // Invoice not found
        abort(404);
    }
}
