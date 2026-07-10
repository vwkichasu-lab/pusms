<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Scholarship Letter - {{ $student->full_name }}</title>
    <style>
        body { margin: 0; background: #f3f4f6; color: #2f2f2f; font-family: Georgia, "Times New Roman", serif; }
        .tools { max-width: 210mm; margin: 16px auto; background: #fff; border: 1px solid #cbd5e1; padding: 12px; font-family: Arial, sans-serif; }
        .tools textarea, .tools input { width: 100%; border: 1px solid #9ca3af; padding: 8px; margin: 4px 0 10px; font: 13px Arial, sans-serif; }
        .tools .grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
        .tools button { border: 1px solid #111827; background: #fff; padding: 8px 12px; cursor: pointer; }
        .page { width: 210mm; min-height: 297mm; margin: 16px auto; background: #fff; padding: 24mm 18mm 18mm; position: relative; box-shadow: 0 0 0 1px #d1d5db; }
        .letterhead { display: flex; align-items: center; justify-content: center; gap: 14px; margin-bottom: 26px; }
        .letterhead img { width: 36mm; height: 24mm; object-fit: contain; }
        .letterhead h1 { margin: 0; color: #1f3763; font-family: Arial, sans-serif; font-size: 27px; letter-spacing: .5px; }
        .letterhead p { margin: 2px 0; color: #1f2937; font-family: Arial, sans-serif; font-size: 12px; }
        .meta { display: flex; justify-content: space-between; font-weight: bold; font-size: 15px; margin-bottom: 22px; }
        .recipient { font-weight: bold; text-transform: uppercase; line-height: 1.15; margin-bottom: 22px; }
        .subject { text-align: center; font-weight: bold; text-transform: uppercase; font-size: 16px; margin: 16px 0; }
        .body { white-space: pre-line; font-size: 15px; line-height: 1.35; }
        .signature { margin-top: 24px; font-weight: bold; }
        .signature-line { width: 70mm; height: 18mm; border-bottom: 1px solid #111; margin-bottom: 8px; display: flex; align-items: flex-end; }
        .signature-line img { max-width: 64mm; max-height: 17mm; object-fit: contain; }
        .cc { margin-top: 4px; }
        .footer-band { position: absolute; bottom: 0; left: 0; right: 0; height: 13mm; background: #1f3763; border-top: 2mm solid #d7a928; }
        @page { size: A4; margin: 0; }
        @media print {
            body { background: #fff; }
            .tools { display: none; }
            .page { margin: 0; box-shadow: none; }
        }
    </style>
</head>
<body>
    <form class="tools" method="post" enctype="multipart/form-data">
        @csrf
        @if ($signaturePath)
            <input type="hidden" name="signature_path" value="{{ $signaturePath }}">
        @endif
        <div class="grid">
            <label>Reference <input name="reference" value="{{ $reference }}"></label>
            <label>Date <input name="letter_date" value="{{ $letterDate }}"></label>
            <label>Signatory Name <input name="signatory_name" value="{{ $signatoryName }}"></label>
            <label>Signatory Title <input name="signatory_title" value="{{ $signatoryTitle }}"></label>
            <label>Digital Signature <input type="file" name="signature" accept="image/*"></label>
        </div>
        <label>Letter Content <textarea name="body" rows="12">{{ $body }}</textarea></label>
        <button type="submit">Update Preview</button>
        <button type="button" onclick="window.print()">Print / Save PDF</button>
    </form>

    <main class="page">
        <header class="letterhead">
            <img src="{{ asset('images/pentvars-display-logo.png') }}" alt="Pentecost University">
            <div>
                <h1>PENTECOST UNIVERSITY</h1>
                <p>P.O. Box KN 1739, Kaneshie, Accra-Ghana</p>
                <p>Tel: 0302417057/8, Website: www.pentvars.edu.gh</p>
            </div>
        </header>

        <div class="meta">
            <span>{{ $reference }}</span>
            <span>{{ $letterDate }}</span>
        </div>

        <section class="recipient">
            {{ $student->full_name }} ({{ $student->student_id }})<br>
            {{ $student->programme?->name }}<br>
            PENTECOST UNIVERSITY<br>
            {{ $student->home_town ?: 'SOWUTUOM' }} - {{ $student->region ?: 'ACCRA' }}
        </section>

        <p>Dear {{ str($student->first_name)->title() }},</p>

        <div class="subject">
            Pentecost University Bursary Award {{ $award->academicYear?->name ?? '' }} Academic Year
        </div>

        <div class="body">{{ $body }}</div>

        <section class="signature">
            <p>Yours faithfully,</p>
            <div class="signature-line">
                @if ($signatureUrl)
                    <img src="{{ $signatureUrl }}" alt="Digital signature">
                @endif
            </div>
            <div>{{ $signatoryName }}</div>
            <div>{{ $signatoryTitle }}</div>
            <div class="cc">CC:</div>
            <ul>
                <li>Chairman, Scholarship</li>
                <li>Pro-VC</li>
                <li>Registrar</li>
                <li>Director of Finance</li>
            </ul>
        </section>

        <div class="footer-band"></div>
    </main>
</body>
</html>
