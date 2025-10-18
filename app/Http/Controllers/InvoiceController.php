<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    /**
     * Generate PDF invoice
     */
    public function generatePdf(Invoice $invoice)
    {
        // Load invoice with client and items
        $invoice->load(['client', 'items']);

        // Generate PDF with A4 paper size
        $pdf = PDF::loadView('pdf.invoice', ['invoice' => $invoice]);
        $pdf->setPaper('a4', 'portrait');

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

        // Generate PDF with A4 paper size
        $pdf = PDF::loadView('pdf.invoice', ['invoice' => $invoice]);
        $pdf->setPaper('a4', 'portrait');

        // Return PDF as download
        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }
}
