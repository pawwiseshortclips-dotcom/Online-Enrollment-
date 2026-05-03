<?php
require_once 'database.php';

// Check if dompdf is available
$autoload = __DIR__ . '/vendor/autoload.php';
if (!file_exists($autoload)) {
    // Fallback: Generate a print-friendly HTML that browser can convert to PDF
    $html = <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Enrollment Form - PDF</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: Arial, sans-serif; 
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            background: #f5f5f5;
        }
        .page {
            width: 8.5in;
            height: 11in;
            margin: 20px auto;
            padding: 0.5in;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            page-break-after: always;
        }
        h1 { 
            font-size: 18px; 
            text-align: center; 
            margin-bottom: 8px;
            font-weight: bold;
        }
        h2 { 
            font-size: 13px; 
            margin-top: 10px; 
            margin-bottom: 6px;
            font-weight: bold;
            border-bottom: 1px solid #000;
            padding-bottom: 3px;
        }
        .instruction-box {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 6px;
            margin: 8px 0;
            font-size: 11px;
            border-radius: 3px;
        }
        .section-title {
            font-weight: bold;
            font-size: 11px;
            margin-top: 8px;
            margin-bottom: 4px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 8px;
            margin: 8px 0;
        }
        .form-grid.full {
            grid-column: 1 / -1;
        }
        .form-field {
            display: flex;
            flex-direction: column;
        }
        .form-field label {
            font-weight: bold;
            font-size: 10px;
            margin-bottom: 2px;
        }
        .form-field input,
        .form-field select {
            border: 1px solid #000;
            padding: 4px;
            font-size: 10px;
            font-family: Arial, sans-serif;
        }
        .form-field input[type="text"],
        .form-field input[type="email"],
        .form-field input[type="date"],
        .form-field select {
            background: white;
        }
        .form-field textarea {
            border: 1px solid #000;
            padding: 4px;
            font-size: 10px;
            font-family: Arial, sans-serif;
            resize: none;
        }
        .checkbox-group {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 4px;
        }
        .checkbox-group label {
            display: flex;
            align-items: center;
            gap: 4px;
            font-weight: normal;
            font-size: 10px;
            margin: 0;
        }
        .radio-group {
            display: flex;
            gap: 12px;
            margin-top: 4px;
        }
        .radio-group label {
            display: flex;
            align-items: center;
            gap: 4px;
            font-weight: normal;
            font-size: 10px;
            margin: 0;
        }
        .signature-line {
            border-bottom: 1px solid #000;
            width: 100%;
            height: 20px;
            margin-top: 4px;
        }
        ol, ul {
            margin-left: 16px;
            font-size: 10px;
        }
        li {
            margin: 3px 0;
            line-height: 1.3;
        }
        .note {
            font-size: 9px;
            color: #666;
            margin-top: 2px;
        }
        @media print {
            body { background: white; }
            .page {
                width: 100%;
                height: auto;
                margin: 0;
                padding: 0.5in;
                box-shadow: none;
                page-break-after: always;
            }
            .no-print { display: none; }
        }
        .print-button {
            text-align: center;
            margin: 20px 0;
        }
        .print-button button {
            padding: 10px 20px;
            font-size: 14px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .print-button button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="print-button no-print">
        <button onclick="window.print()">🖨️ Print / Save as PDF</button>
    </div>

    <div class="page">
        <h1>ENROLLMENT FORM</h1>
        
        <div class="instruction-box">
            <strong>Instructions:</strong> Fill out this form carefully and legibly. For online payment, provide GCash reference number. For walk-in, leave payment fields blank.
        </div>

        <h2>ENROLLMENT INFORMATION</h2>
        
        <div class="form-grid">
            <div class="form-field">
                <label>Surname *</label>
                <input type="text" placeholder="_________________">
            </div>
            <div class="form-field">
                <label>Firstname *</label>
                <input type="text" placeholder="_________________">
            </div>
            <div class="form-field">
                <label>Middle Name</label>
                <input type="text" placeholder="_________________">
            </div>
        </div>

        <div class="form-grid full">
            <div class="form-field">
                <label>Home Address *</label>
                <input type="text" placeholder="________________________________________________">
            </div>
        </div>

        <div class="form-grid">
            <div class="form-field">
                <label>Sex *</label>
                <div class="radio-group">
                    <label><input type="radio" name="sex"> Male</label>
                    <label><input type="radio" name="sex"> Female</label>
                </div>
            </div>
            <div class="form-field">
                <label>Date of Birth *</label>
                <input type="text" placeholder="MM/DD/YYYY">
            </div>
            <div class="form-field">
                <label>Nationality *</label>
                <input type="text" placeholder="_________________">
            </div>
        </div>

        <div class="form-grid">
            <div class="form-field">
                <label>Place of Birth *</label>
                <input type="text" placeholder="_________________">
            </div>
            <div class="form-field">
                <label>Cellphone Number *</label>
                <input type="text" placeholder="09XXXXXXXXX">
            </div>
            <div class="form-field">
                <label>Email Address *</label>
                <input type="text" placeholder="student@example.com">
            </div>
        </div>

        <div class="form-grid full">
            <div class="form-field">
                <label>School Last Attended *</label>
                <input type="text" placeholder="________________________________________________">
            </div>
        </div>

        <h2>ACADEMIC INFORMATION</h2>

        <div class="form-grid">
            <div class="form-field">
                <label>Course *</label>
                <textarea rows="2" placeholder="Enter course name"></textarea>
            </div>
            <div class="form-field">
                <label>Year Level *</label>
                <input type="text" placeholder="1st / 2nd / 3rd / 4th / 5th">
            </div>
        </div>

        <h2>GUARDIAN INFORMATION</h2>

        <div class="form-grid">
            <div class="form-field full">
                <label>Parent / Guardian Name *</label>
                <input type="text" placeholder="________________________________________________">
            </div>
        </div>

        <div class="form-grid">
            <div class="form-field">
                <label>Relationship *</label>
                <input type="text" placeholder="Father / Mother / etc">
            </div>
        </div>

        <h2>VACCINATION STATUS</h2>
        <div class="form-grid full">
            <div class="checkbox-group">
                <label><input type="checkbox"> Fully Vaccinated w/ Booster</label>
                <label><input type="checkbox"> Fully Vaccinated</label>
                <label><input type="checkbox"> Partially Vaccinated</label>
                <label><input type="checkbox"> Unvaccinated</label>
            </div>
        </div>

        <h2>PAYMENT INFORMATION (Optional - for online payment only)</h2>
        
        <div class="form-grid">
            <div class="form-field full">
                <label>GCash Number</label>
                <input type="text" placeholder="09XXXXXXXXX (Leave blank for walk-in)">
            </div>
        </div>

        <div class="form-grid">
            <div class="form-field full">
                <label>GCash Reference Number</label>
                <input type="text" placeholder="Enter reference number (Leave blank for walk-in)">
            </div>
        </div>

        <div class="form-grid full">
            <div class="note">⚠️ Attach payment proof (screenshot) when submitting online. For walk-in, leave payment fields blank.</div>
        </div>

        <h2>AGREEMENT & SIGNATURE</h2>
        
        <p style="font-size: 10px; margin: 8px 0;">
            <strong>"I hereby signify to abide with the rules and regulations promulgated by this institution."</strong>
        </p>

        <div class="form-grid full">
            <div class="form-field">
                <label>Signature (Print Name) *</label>
                <div class="signature-line"></div>
                <div class="note" style="margin-top: 2px;">Sign or print your name above</div>
            </div>
        </div>

        <div style="margin-top: 16px; font-size: 9px; text-align: center; color: #666;">
            <p>Date Submitted: _______________</p>
            <p>For Office Use Only - Receipt #: _______________</p>
        </div>
    </div>

    <div class="print-button no-print">
        <button onclick="window.print()">🖨️ Print / Save as PDF</button>
    </div>

    <script>
        // Auto-print on load (optional - comment out to let user decide)
        // window.onload = function() { window.print(); };
    </script>
</body>
</html>
HTML;

    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: inline; filename="Enrollment_Form_' . date('Ymd') . '.html"');
    echo $html;
    exit;
}

// If dompdf is available, use it for better PDF generation
require $autoload;
use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('isRemoteEnabled', false);
$options->set('defaultFont', 'Arial');

$dompdf = new Dompdf($options);

$html = <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: Arial, sans-serif; 
            font-size: 11px;
            line-height: 1.4;
            color: #333;
        }
        page { page-break-after: always; }
        .page {
            width: 100%;
            padding: 20px;
            background: white;
        }
        h1 { 
            font-size: 18px; 
            text-align: center; 
            margin-bottom: 10px;
            font-weight: bold;
        }
        h2 { 
            font-size: 12px; 
            margin-top: 12px; 
            margin-bottom: 8px;
            font-weight: bold;
            border-bottom: 2px solid #000;
            padding-bottom: 4px;
        }
        .instruction-box {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 8px;
            margin: 10px 0;
            font-size: 10px;
            border-radius: 3px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 10px;
            margin: 10px 0;
        }
        .form-grid.full {
            grid-column: 1 / -1;
        }
        .form-field {
            display: flex;
            flex-direction: column;
        }
        .form-field label {
            font-weight: bold;
            font-size: 10px;
            margin-bottom: 4px;
        }
        .form-field input,
        .form-field select,
        .form-field textarea {
            border: 1px solid #000;
            padding: 5px;
            font-size: 10px;
            font-family: Arial, sans-serif;
        }
        .checkbox-group, .radio-group {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 4px;
        }
        .checkbox-group label, .radio-group label {
            display: flex;
            align-items: center;
            gap: 4px;
            font-weight: normal;
            font-size: 10px;
            margin: 0;
        }
        .signature-line {
            border-bottom: 1px solid #000;
            width: 100%;
            height: 25px;
            margin-top: 5px;
        }
        ol, ul {
            margin-left: 20px;
            font-size: 10px;
        }
        li {
            margin: 4px 0;
        }
        .note {
            font-size: 9px;
            color: #666;
            margin-top: 3px;
        }
        .footer {
            margin-top: 20px;
            font-size: 9px;
            text-align: center;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <page>
        <div class="page">
            <h1>ENROLLMENT FORM</h1>
            
            <div class="instruction-box">
                <strong>Instructions:</strong> Fill out this form carefully and legibly. For online payment, provide GCash reference number. For walk-in enrollment, leave payment fields blank.
            </div>

            <h2>ENROLLMENT INFORMATION</h2>
            
            <div class="form-grid">
                <div class="form-field">
                    <label>Surname *</label>
                    <input type="text">
                </div>
                <div class="form-field">
                    <label>Firstname *</label>
                    <input type="text">
                </div>
                <div class="form-field">
                    <label>Middle Name</label>
                    <input type="text">
                </div>
            </div>

            <div class="form-grid full">
                <div class="form-field">
                    <label>Home Address *</label>
                    <input type="text">
                </div>
            </div>

            <div class="form-grid">
                <div class="form-field">
                    <label>Sex *</label>
                    <div class="radio-group">
                        <label><input type="radio"> Male</label>
                        <label><input type="radio"> Female</label>
                    </div>
                </div>
                <div class="form-field">
                    <label>Date of Birth *</label>
                    <input type="text" placeholder="MM/DD/YYYY">
                </div>
                <div class="form-field">
                    <label>Nationality *</label>
                    <input type="text">
                </div>
            </div>

            <div class="form-grid">
                <div class="form-field">
                    <label>Place of Birth *</label>
                    <input type="text">
                </div>
                <div class="form-field">
                    <label>Cellphone Number *</label>
                    <input type="text" placeholder="09XXXXXXXXX">
                </div>
                <div class="form-field">
                    <label>Email Address *</label>
                    <input type="text">
                </div>
            </div>

            <div class="form-grid full">
                <div class="form-field">
                    <label>School Last Attended *</label>
                    <input type="text">
                </div>
            </div>

            <h2>ACADEMIC INFORMATION</h2>

            <div class="form-grid">
                <div class="form-field">
                    <label>Course *</label>
                    <textarea rows="2"></textarea>
                </div>
                <div class="form-field">
                    <label>Year Level *</label>
                    <input type="text" placeholder="1st / 2nd / 3rd / 4th / 5th">
                </div>
            </div>

            <h2>GUARDIAN INFORMATION</h2>

            <div class="form-grid full">
                <div class="form-field">
                    <label>Parent / Guardian Name *</label>
                    <input type="text">
                </div>
            </div>

            <div class="form-grid">
                <div class="form-field">
                    <label>Relationship *</label>
                    <input type="text">
                </div>
            </div>

            <h2>VACCINATION STATUS</h2>
            <div class="checkbox-group">
                <label><input type="checkbox"> Fully Vaccinated w/ Booster</label>
                <label><input type="checkbox"> Fully Vaccinated</label>
                <label><input type="checkbox"> Partially Vaccinated</label>
                <label><input type="checkbox"> Unvaccinated</label>
            </div>

            <h2>PAYMENT INFORMATION (Optional - for online payment only)</h2>
            
            <div class="form-grid full">
                <div class="form-field">
                    <label>GCash Number</label>
                    <input type="text" placeholder="09XXXXXXXXX (Leave blank for walk-in)">
                </div>
            </div>

            <div class="form-grid full">
                <div class="form-field">
                    <label>GCash Reference Number</label>
                    <input type="text" placeholder="Leave blank for walk-in">
                </div>
            </div>

            <h2>AGREEMENT & SIGNATURE</h2>
            
            <p style="font-size: 10px; margin: 10px 0;">
                <strong>"I hereby signify to abide with the rules and regulations promulgated by this institution."</strong>
            </p>

            <div class="form-field">
                <label>Signature (Print Name) *</label>
                <div class="signature-line"></div>
            </div>

            <div class="footer">
                <p>Date Submitted: _______________</p>
                <p>For Office Use Only - Receipt #: _______________</p>
                <p style="margin-top: 10px;">⚠️ For online payment: Attach screenshot of GCash payment receipt</p>
                <p>For walk-in: Leave payment fields blank and bring this form to the registrar</p>
            </div>
        </div>
    </page>
</body>
</html>
HTML;

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream('Enrollment_Form_' . date('Ymd') . '.pdf', ['Attachment' => false]);
exit;
?>
