@extends('layouts.app')
@section('title', 'Prescription ' . $prescription->prescription_number)

@push('styles')
<style>
    .rx-paper {
        max-width: 800px;
        margin: 1rem auto;
        background: #fff;
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 2.5rem;
        box-shadow: 0 4px 15px rgba(0,0,0,.08);
        font-family: Arial, Helvetica, sans-serif;
    }
    .rx-header {
        border-bottom: 2px solid var(--green);
        padding-bottom: 1.2rem;
        margin-bottom: 1.2rem;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }
    .rx-brand h2 { font-size: 1.4rem; color: var(--green); font-weight: 700; }
    .rx-brand p { font-size: 12px; color: var(--muted); margin-top: 2px; }
    .rx-meta { text-align: right; font-size: 12px; color: var(--muted); }
    .rx-meta strong { font-size: 14px; color: var(--text); display: block; }

    .rx-parties {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        background: var(--bg);
        border: 1px solid var(--border);
        border-radius: 4px;
        padding: .85rem 1rem;
        margin-bottom: 1.2rem;
        font-size: 12px;
        line-height: 1.6;
    }

    .vitals-strip {
        display: flex;
        gap: 1.5rem;
        flex-wrap: wrap;
        background: #fdfaf3;
        border: 1px solid #fae6c3;
        border-radius: 4px;
        padding: .6rem 1rem;
        font-size: 12px;
        margin-bottom: 1.2rem;
    }
    .vitals-strip span { font-weight: 700; color: var(--gold); }

    .rx-symbol {
        font-size: 2rem;
        font-weight: 700;
        font-family: 'Times New Roman', serif;
        color: var(--green);
        margin-bottom: .6rem;
    }

    .rx-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 1.5rem;
        font-size: 13px;
    }
    .rx-table th {
        background: var(--green-pale);
        color: var(--green-dk);
        text-align: left;
        padding: .6rem .8rem;
        font-size: 11px;
        text-transform: uppercase;
        border-bottom: 2px solid var(--green);
    }
    .rx-table td {
        padding: .7rem .8rem;
        border-bottom: 1px solid #ecf2ee;
        vertical-align: middle;
    }

    .rx-footer {
        margin-top: 2.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
    }
    .sig-line {
        border-top: 1px dashed #333;
        width: 220px;
        text-align: center;
        font-size: 11px;
        padding-top: 4px;
        color: var(--muted);
    }

    @media print {
        body * { visibility: hidden; }
        .rx-paper, .rx-paper * { visibility: visible; }
        .rx-paper { position: absolute; left: 0; top: 0; width: 100%; border: none; box-shadow: none; padding: 0; }
        .no-print { display: none !important; }
    }
</style>
@endpush

@section('content')
<div class="no-print" style="max-width:800px;margin:0 auto 1rem auto;display:flex;justify-content:space-between;align-items:center;">
    <a href="javascript:history.back()" class="btn btn-outline btn-sm">&larr; Back to Portal</a>
    <div style="display:flex;gap:.5rem;">
        <button onclick="window.print()" class="btn btn-outline btn-sm">Print Prescription</button>
        <a href="{{ route('patient.prescriptions.pdf', $prescription->id) }}" class="btn btn-green btn-sm">Download Official PDF</a>
    </div>
</div>

<div class="rx-paper">
    <div class="rx-header">
        <div class="rx-brand">
            <h2>UniMedCenter &bull; University Medical Centre</h2>
            <p>Health &amp; Wellness Division &bull; Central Campus, Dhaka &bull; Hotline: 0188888888</p>
        </div>
        <div class="rx-meta">
            <strong>{{ $prescription->prescription_number }}</strong>
            <div>Date: {{ $prescription->created_at->format('d M Y, h:i A') }}</div>
            <div style="margin-top:2px;">
                <span class="badge {{ $prescription->status === 'dispensed' ? 'badge-green' : 'badge-yellow' }}">
                    {{ ucfirst($prescription->status) }}
                </span>
            </div>
        </div>
    </div>

    {{-- Doctor & Patient Info --}}
    <div class="rx-parties">
        <div>
            <strong style="color:var(--green);font-size:13px;">PATIENT DETAILS</strong>
            <div>Name: <strong>{{ $prescription->patient->full_name }}</strong></div>
            <div>University ID: <strong>{{ $prescription->patient->university_id }}</strong></div>
            <div>Age / Gender: {{ $prescription->patient->dob ? $prescription->patient->dob->age . ' yrs' : 'N/A' }} / {{ $prescription->patient->gender }}</div>
            <div>Blood Group: <strong>{{ $prescription->patient->blood_group ?? 'Not Set' }}</strong></div>
            <div>Allergies: <span style="color:#c0392b;font-weight:700;">{{ $prescription->patient->allergies ?: 'None reported' }}</span></div>
        </div>
        <div>
            <strong style="color:var(--green);font-size:13px;">ATTENDING PHYSICIAN</strong>
            <div>Doctor: <strong>{{ $prescription->doctor->name }}</strong></div>
            <div>Designation: {{ $prescription->doctor->designation ?? 'Medical Officer' }}</div>
            <div>Department: {{ $prescription->doctor->department ?? 'General Medicine' }}</div>
            <div>Phone: {{ $prescription->doctor->phone ?? 'University Ext.' }}</div>
        </div>
    </div>

    {{-- Vitals Strip --}}
    <div class="vitals-strip">
        <div>BP: <span>{{ $prescription->bp ?: '120/80 mmHg' }}</span></div>
        <div>Pulse: <span>{{ $prescription->pulse ?: '72 bpm' }}</span></div>
        <div>Temp: <span>{{ $prescription->temperature ?: '98.6 °F' }}</span></div>
        <div>Weight: <span>{{ $prescription->weight ?: '65 kg' }}</span></div>
    </div>

    <div style="margin-bottom:1.2rem;">
        <div class="form-label" style="color:var(--green);">CLINICAL DIAGNOSIS</div>
        <div style="font-size:14px;font-weight:700;color:var(--text);background:#f9fbfa;border:1px solid var(--border);border-radius:4px;padding:.5rem .8rem;">
            {{ $prescription->diagnosis }}
        </div>
    </div>

    <div class="rx-symbol">&#8478;</div>

    {{-- Medicines Table --}}
    <table class="rx-table">
        <thead>
            <tr>
                <th style="width:30px;">#</th>
                <th>Medicine / Formulation</th>
                <th>Dosage Pattern</th>
                <th>Duration</th>
                <th>Instructions</th>
                <th>Qty</th>
            </tr>
        </thead>
        <tbody>
            @foreach($prescription->items as $idx => $item)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>
                        <strong style="font-size:13px;">{{ $item->medicine_name }}</strong>
                        @if($item->medicine)
                            <div style="font-size:11px;color:var(--muted);">{{ $item->medicine->generic_name }} ({{ $item->medicine->strength }})</div>
                        @endif
                    </td>
                    <td><span class="badge badge-gold" style="font-size:12px;">{{ $item->dosage }}</span></td>
                    <td>{{ $item->duration }}</td>
                    <td style="font-size:12px;">{{ $item->instructions ?: 'As directed' }}</td>
                    <td><strong>{{ $item->quantity }}</strong></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Clinical Advice / Special Notes --}}
    @if($prescription->clinical_advice)
        <div style="background:#f9fbfa;border:1px solid var(--border);border-radius:4px;padding:.8rem;margin-bottom:1.5rem;">
            <div class="form-label" style="color:var(--green);">CLINICAL ADVICE &amp; INSTRUCTIONS</div>
            <div style="font-size:12px;line-height:1.6;white-space:pre-line;">{{ $prescription->clinical_advice }}</div>
        </div>
    @endif

    <div class="rx-footer">
        <div style="font-size:11px;color:var(--muted);max-width:320px;">
            * This digital prescription is electronically certified and verifiable at Central Pharmacy.
        </div>
        <div style="text-align:center;">
            <div style="font-family:'Brush Script MT', cursive;font-size:22px;color:var(--green-dk);margin-bottom:3px;">
                {{ $prescription->doctor->name }}
            </div>
            <div class="sig-line">
                Prescribing Medical Officer Signature &amp; Seal
            </div>
        </div>
    </div>
</div>
@endsection
