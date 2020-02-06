<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;

class CertificateController extends Controller
{
    public function generate(Request $request) {
        define('FPDF_FONTPATH', 'fonts');

        $name = $request->get('name');
        $name_length = strlen($name);

        // Create new Landscape PDF
        $pdf = new Fpdi('l');

        if ($request->get('type') == "blank") {
            
            !\App::environment('local') ? $pdf_path = 'public/pdf/cert-blank.pdf' : $pdf_path = 'pdf/cert-blank.pdf';

            $pagecount = $pdf->setSourceFile($pdf_path);

        }

        else {
        
            // Reference the PDF you want to use (use relative path)
            $pagecount = $pdf->setSourceFile('pdf/cert-format.pdf');
        
        }

        // Import the first page from the PDF and add to dynamic PDF
        $tpl = $pdf->importPage(1);
        $pdf->AddPage();

        // Use the imported page as the template
        $pdf->useTemplate($tpl);

        $pdf->AddFont('Roboto', 'B', 'robotob.php');
        // Set the default font to use
        $pdf->SetFont('Roboto', 'B');

        // First box - the user's Name
        if ($name_length < 30) {
            $pdf->SetFontSize('40'); // set font size
        }
        elseif ($name_length < 40) {
            $pdf->SetFontSize('30');
        }
        else {
            $pdf->SetFontSize('20');
        }

        $pdf->SetXY(30.8, 115); // set the position of the box
        $pdf->Cell(0, 15, $name, 0, 0, 'C'); // add the text, align to Center of cell

        // render PDF to browser
        $pdf->Output();

    }
}
