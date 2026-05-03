<?php
require_once 'database.php';

// Generate a clean printable enrollment form as PDF/HTML
$html = <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Enrollment Form - Printable</title>
    <style>
        :root{--paper-bg:#faf9f1;--border:#ccc;--text:#1a1a1a;--muted:#555;--heading:#0b2240;--accent:#1d4ed8;--shadow:0 3px 12px rgba(0,0,0,.08)}
        *{box-sizing:border-box}
        body{margin:0;background:#fff;font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,sans-serif;color:var(--text)}
        .page{max-width:900px;margin:0 auto;padding:20mm;background:#fff;border:none}
        .page h1{margin:0 0 12px;font-size:20px;text-align:center;letter-spacing:.04em;color:var(--heading)}
        .page h2{margin:18px 0 10px;font-size:14px;color:var(--heading);border-bottom:2px solid var(--border);padding-bottom:4px}
        .procs{margin:12px 0 18px;padding-left:18px;font-size:12px}
        .procs ol{margin:0;padding-left:18px}
        .procs ul{margin:0;padding-left:18px}
        .procs li{margin:6px 0;line-height:1.4}
        .note{font-size:11px;color:var(--muted);margin-top:-8px;}
        .form-section{margin-top:16px}
        .form-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}
        .form-grid .full{grid-column:1/-1}
        label{display:block;font-weight:600;margin-bottom:4px;letter-spacing:.02em;font-size:12px}
        input[type=text], input[type=date], input[type=email], select{width:100%;padding:6px;border:1px solid var(--border);border-radius:3px;background:#fff;font-size:11px}
        .radio-grid{display:flex;gap:14px;align-items:center;margin-top:4px;font-size:11px}
        .radio-grid label{font-weight:500;display:flex;align-items:center;gap:4px}
        .checkbox-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;margin-top:6px;font-size:11px}
        .checkbox-grid label{display:flex;align-items:center;gap:6px;font-weight:500}
        .signature{margin-top:16px}
        .signature input{border-bottom:1px solid var(--border);border-radius:0;width:100%;padding:4px 2px;font-size:11px}
        .actions{display:flex;justify-content:flex-end;gap:10px;margin-top:20px}
        .actions button{padding:8px 14px;font-weight:600;border-radius:4px;border:none;cursor:pointer;letter-spacing:.03em;font-size:12px}
        .actions .submit{background:var(--accent);color:#fff}
        .actions .reset{background:#f3f4f6;color:#1f2937}
        .note-box{background:#fff3cd;border:1px solid #ffc107;padding:8px;margin:10px 0;font-size:11px;border-radius:3px}
        @media print{
            body{background:#fff}
            .page{box-shadow:none;border:none;margin:0;padding:15mm;}
            .actions{display:none}
            .form-grid{grid-template-columns:repeat(3,1fr)}
        }
    </style>
</head>
<body>
    <article class="page">
        <h1>ENROLMENT FORM</h1>
        <div class="note-box"><strong>Instructions:</strong> Print this form and fill it out carefully and legibly. Submit with payment proof (GCash/Bank Transfer) or bring this form for walk-in enrollment at the registrar.</div>

        <section class="procs">
            <h2 style="margin-top:0">ENROLMENT PROCEDURE</h2>
            <ol>
                <li><strong>REGISTRAR</strong>
                    <ol type="a">
                        <li>Secure for Registration / Enrolment form. Fill up the form carefully and legibly.</li>
                        <li>Submit Registration Form/Enrollment form together with the following requirements to the registrar.
                            <div class="note">HIGH SCHOOL GRADUATES</div>
                            <ul>
                                <li>Form 138 or High School Card</li>
                                <li>Certificate of Good Moral Character</li>
                                <li>Certificate of Honors gained (Valedictorian, Salutatorian, etc.)</li>
                                <li>NCEE (optional)</li>
                            </ul>
                            <div class="note">COLLEGE FRESHMEN / TRANSFEREES</div>
                            <ul>
                                <li>Honorable Dismissal</li>
                                <li>Certificate of Good Moral Character</li>
                                <li>Transcript of Records</li>
                            </ul>
                        </li>
                    </ol>
                </li>
                <li>Proceed to the Accounting Department for assessment of fees.</li>
                <li>After assessment, go to the cashier and pay your fees.</li>
                <li>Return to the Dean/Head for approval and present the enrollment form and receipts.</li>
                <li>Submit class cards to professors on the first day of classes.</li>
                <li>Secure your ID and Library Card from Accounting after payment.</li>
                <li>Classes start as scheduled.</li>
            </ol>
        </section>

        <h2>FILL OUT CAREFULLY AND LEGIBLY</h2>
        <form method="POST" action="submit.php" enctype="multipart/form-data">
            <section class="form-section">
                <div class="form-grid">
                    <div>
                        <label for="surname">Surname</label>
                        <input id="surname" name="surname" type="text" required oninput="this.value=this.value.toUpperCase()">
                    </div>
                    <div>
                        <label for="firstname">Firstname</label>
                        <input id="firstname" name="firstname" type="text" required oninput="this.value=this.value.toUpperCase()">
                    </div>
                    <div>
                        <label for="middlename">Middle Name</label>
                        <input id="middlename" name="middlename" type="text" placeholder="Optional (type N/A if none)" oninput="this.value=this.value.toUpperCase()">
                    </div>
                    <div class="full">
                        <label for="address">Home Address</label>
                        <input id="address" name="address" type="text" required oninput="this.value=this.value.toUpperCase()">
                    </div>
                    <div>
                        <label>Sex</label>
                        <div class="radio-grid">
                            <label><input type="radio" name="sex" value="Male" required> Male</label>
                            <label><input type="radio" name="sex" value="Female" required> Female</label>
                        </div>
                    </div>
                    <div>
                        <label for="cellphone">Cellphone Number</label>
                        <input id="cellphone" name="cellphone" type="text" required maxlength="13" pattern="^(09\d{9}|\+639\d{9})$" placeholder="09XXXXXXXXX or +639XXXXXXXXX">
                        <div class="note">Enter Philippine mobile number (e.g., 09171234567 or +639171234567).</div>
                    </div>
                    <div>
                        <label for="email">Email Address</label>
                        <input id="email" name="email" type="email" required placeholder="student@example.com">
                    </div>
                    <div>
                        <label for="nationality">Nationality</label>
                        <input id="nationality" name="nationality" type="text" required oninput="this.value=this.value.toUpperCase()">
                    </div>
                    <div>
                        <label for="birthplace">Place of Birth</label>
                        <input id="birthplace" name="birthplace" type="text" required oninput="this.value=this.value.toUpperCase()">
                    </div>
                    <div>
                        <label for="birthdate">Date of Birth</label>
                        <input id="birthdate" name="birthdate" type="date" required>
                    </div>
                    <div class="full">
                        <label for="school">School Last Attended</label>
                        <input id="school" name="school" type="text" required oninput="this.value=this.value.toUpperCase()">
                    </div>
                    <div>
                        <label for="course">Course</label>
                        <select id="course" name="course" required>
                            <option value="">-- Select course --</option>
                            <option value="Bachelor of Science in Information Systems (BSIS)">Bachelor of Science in Information Systems (BSIS)</option>
                            <option value="Bachelor of Science in Information Technology (BSIT)">Bachelor of Science in Information Technology (BSIT)</option>
                            <option value="Bachelor of Science in Computer Science (BSCS)">Bachelor of Science in Computer Science (BSCS)</option>
                            <option value="Associate in Computer Technology (ACT)">Associate in Computer Technology (ACT)</option>
                            <option value="Bachelor of Engineering Technology (BET) Major in Electrical and Electronics">Bachelor of Engineering Technology (BET) Major in Electrical and Electronics</option>
                            <option value="Diploma in Software Development and Programming">Diploma in Software Development and Programming</option>
                            <option value="Diploma in Electronic and Computer Technology">Diploma in Electronic and Computer Technology</option>
                        </select>
                    </div>
                    <div>
                        <label for="year_level">Year Level</label>
                        <select id="year_level" name="year_level" required>
                            <option value="">-- Select year level --</option>
                            <option value="1st Year">1st Year</option>
                            <option value="2nd Year">2nd Year</option>
                            <option value="3rd Year">3rd Year</option>
                            <option value="4th Year">4th Year</option>
                            <option value="5th Year">5th Year</option>
                        </select>
                    </div>
                    <div>
                        <label for="guardian">Parent &amp; Guardian</label>
                        <input id="guardian" name="guardian" type="text" required oninput="this.value=this.value.toUpperCase()">
                    </div>
                    <div>
                        <label for="relationship">Relationship to Student</label>
                        <input id="relationship" name="relationship" type="text" required oninput="this.value=this.value.toUpperCase()">
                    </div>
                    <div class="full">
                        <label>Covid Vaccination Status</label>
                        <div class="checkbox-grid">
                            <label><input type="checkbox" name="vax_status[]" value="Fully Vaccinated w/ Booster"> Fully Vaccinated w/ Booster</label>
                            <label><input type="checkbox" name="vax_status[]" value="Fully Vaccinated"> Fully Vaccinated</label>
                            <label><input type="checkbox" name="vax_status[]" value="Partially Vaccinated"> Partially Vaccinated</label>
                            <label><input type="checkbox" name="vax_status[]" value="Unvaccinated"> Unvaccinated</label>
                        </div>
                    </div>
                    <div class="full">
                        <label for="gcash_number">GCash Number (for online payment)</label>
                        <input id="gcash_number" name="gcash_number" type="text" maxlength="13" pattern="^(09\d{9}|\+639\d{9})$" placeholder="09XXXXXXXXX or +639XXXXXXXXX">
                        <div class="note">Optional if paying via walk-in</div>
                    </div>
                    <div class="full">
                        <label for="reference_number">GCash Reference Number (for online payment)</label>
                        <input id="reference_number" name="reference_number" type="text" placeholder="Enter payment reference number">
                        <div class="note">Optional if paying via walk-in</div>
                    </div>
                    <div class="full">
                        <label for="payment_proof">Payment Screenshot (for online payment)</label>
                        <input id="payment_proof" name="payment_proof" type="file" accept="image/png,image/jpeg">
                        <div class="note">Optional if paying via walk-in - Upload a screenshot of the GCash payment receipt (JPG/PNG).</div>
                    </div>
                    <div class="full">
                        <p><strong>Agreement:</strong> "I hereby signify to abide with the rules and regulations promulgated by this institution."</p>
                    </div>
                    <div class="full signature">
                        <label for="signature">Signature</label>
                        <input id="signature" name="signature" type="text" placeholder="(Type your name as signature)" required>
                    </div>
                </div>
            </section>
            <div class="actions">
                <button type="reset" class="reset">Clear Form</button>
                <button type="submit" class="submit">Submit Enrollment (Online Payment)</button>
            </div>
        </form>
    </article>
</body>
</html>
HTML;

header('Content-Type: text/html; charset=utf-8');
header('Content-Disposition: attachment; filename="Enrollment_Form_' . date('Ymd') . '.html"');
echo $html;
exit;
?>
