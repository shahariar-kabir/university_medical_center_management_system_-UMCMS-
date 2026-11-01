<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Prescription {{ $prescription->prescription_number }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #222;
            line-height: 1.4;
            padding: 10px;
        }
        .header {
            border-bottom: 2px solid #1a6b3a;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .header table { width: 100%; }
        .header h1 {
            color: #1a6b3a;
            font-size: 18px;
            margin: 0;
            padding: 0;
        }
        .header p {
            color: #555;
            font-size: 10px;
            margin: 2px 0 0 0;
        }
        .meta-table {
            width: 100%;
            border: 1px solid #c3dbc9;
            background: #f8faf8;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .meta-table td {
            padding: 6px 8px;
            vertical-align: top;
            font-size: 10.5px;
        }
        .section-title {
            color: #1a6b3a;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .vitals {
            background: #fdfaf3;
            border: 1px solid #fae6c3;
            padding: 5px 8px;
            margin-bottom: 12px;
            font-size: 10px;
        }
        .diagnosis-box {
            border: 1px solid #c3dbc9;
            background: #f4f8f5;
            padding: 6px 10px;
            margin-bottom: 12px;
            font-size: 11px;
            font-weight: bold;
        }
        .rx-symbol {
            font-size: 22px;
            font-family: serif;
            font-weight: bold;
            color: #1a6b3a;
            margin-bottom: 5px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .items-table th {
            background: #1a6b3a;
            color: #fff;
            padding: 6px 8px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
        }
        .items-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 10.5px;
        }
        .advice-box {
            border: 1px solid #c3dbc9;
            background: #f9fbfa;
            padding: 8px 10px;
            margin-bottom: 25px;
            font-size: 10px;
        }
        .footer-table {
            width: 100%;
            margin-top: 30px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>

<div class="header">
    <table>
        <tr>
            <td>
                <h1>UniMedCenter &bull; University Medical Centre</h1>
                <p>Health &amp; Wellness Division &bull; Central Campus, Dhaka &bull; Hotline: 0188888888</p>
                <p>Official Digital Prescription Record</p>
            </td>
            <td style="text-align: right;">
                <div style="font-size: 13px; font-weight: bold; color: #1a6b3a;">{{ $prescription->prescription_number }}</div>
                <div style="font-size: 9.5px; color: #555;">Date: {{ $prescription->created_at->format('d/m/Y, h:i A') }}</div>
                <div style="font-size: 9.5px; color: #555; text-transform: uppercase;">Status: {{ $prescription->status }}</div>
            </td>
        </tr>
    </table>
</div>

<table class="meta-table">
    <tr>
        <td style="width: 50%; border-right: 1px solid #c3dbc9;">
            <div class="section-title">PATIENT INFORMATION</div>
            <strong>Name:</strong> {{ $prescription->patient->full_name }}<br>
            <strong>University ID:</strong> {{ $prescription->patient->university_id }}<br>
            <strong>Category:</strong> {{ ucfirst($prescription->patient->role_category) }} ({{ $prescription->patient->department ?? 'General' }})<br>
            <strong>Age / Gender:</strong> {{ $prescription->patient->dob ? $prescription->patient->dob->age . ' yrs' : 'N/A' }} / {{ $prescription->patient->gender }}<br>
            <strong>Blood Group:</strong> {{ $prescription->patient->blood_group ?? 'N/A' }}<br>
            <strong>Allergies:</strong> <span style="color:#c0392b;">{{ $prescription->patient->allergies ?: 'None reported' }}</span>
        </td>
        <td style="width: 50%;">
            <div class="section-title">ATTENDING PHYSICIAN</div>
            <strong>Doctor:</strong> {{ $prescription->doctor->name }}<br>
            <strong>Designation:</strong> {{ $prescription->doctor->designation ?? 'Medical Officer' }}<br>
            <strong>Department:</strong> {{ $prescription->doctor->department ?? 'General Medicine' }}<br>
            <strong>Contact:</strong> {{ $prescription->doctor->phone ?? 'University Hotline' }}
        </td>
    </tr>
</table>

<div class="vitals">
    <strong>VITALS:</strong> &nbsp;&nbsp;
    BP: <strong>{{ $prescription->bp ?: '120/80 mmHg' }}</strong> &nbsp;|&nbsp;
    Pulse: <strong>{{ $prescription->pulse ?: '72 bpm' }}</strong> &nbsp;|&nbsp;
    Temp: <strong>{{ $prescription->temperature ?: '98.6 °F' }}</strong> &nbsp;|&nbsp;
    Weight: <strong>{{ $prescription->weight ?: '65 kg' }}</strong>
</div>

<div class="diagnosis-box">
    DIAGNOSIS: {{ $prescription->diagnosis }}
</div>

<div class="rx-symbol">&#8478;</div>

<table class="items-table">
    <thead>
        <tr>
            <th style="width: 25px;">#</th>
            <th>Medicine &amp; Formulation</th>
            <th>Dosage</th>
            <th>Duration</th>
            <th>Instructions</th>
            <th style="width: 40px; text-align: center;">Qty</th>
        </tr>
    </thead>
    <tbody>
        @foreach($prescription->items as $idx => $item)
            <tr>
                <td>{{ $idx + 1 }}</td>
                <td>
                    <strong>{{ $item->medicine_name }}</strong>
                    @if($item->medicine)
                        <br><span style="font-size: 9px; color: #555;">{{ $item->medicine->generic_name }}</span>
                    @endif
                </td>
                <td><strong>{{ $item->dosage }}</strong></td>
                <td>{{ $item->duration }}</td>
                <td>{{ $item->instructions ?: 'As directed' }}</td>
                <td style="text-align: center;"><strong>{{ $item->quantity }}</strong></td>
            </tr>
        @endforeach
    </tbody>
</table>

@if($prescription->clinical_advice)
    <div class="advice-box">
        <div class="section-title">CLINICAL ADVICE &amp; INSTRUCTIONS</div>
        {!! nl2br(e($prescription->clinical_advice)) !!}
    </div>
@endif

<table class="footer-table">
    <tr>
        <td style="font-size: 9px; color: #777; width: 60%;">
            * Electronically verified university digital prescription. Subsidized medicine can be collected from Central Pharmacy.<br>
            Verification ID: {{ $prescription->prescription_number }} &bull; Print Date: {{ date('d M Y, h:i A') }}
        </td>
        <td style="text-align: right; width: 40%;">
            <br><br>
            <div style="border-top: 1px solid #333; display: inline-block; width: 180px; text-align: center; font-size: 9.5px; padding-top: 3px;">
                <strong>{{ $prescription->doctor->name }}</strong><br>
                Prescribing Medical Officer
            </div>
        </td>
    </tr>
</table>

</body>
</html>
