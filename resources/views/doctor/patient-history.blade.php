@extends('layouts.app')
@section('title', 'Patient Medical History - ' . $patient->full_name)

@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.5rem;flex-wrap:wrap;gap:.5rem;">
    <div>
        <div class="page-title">Medical Record &amp; History &mdash; {{ $patient->full_name }}</div>
        <div class="page-subtitle" style="margin-bottom:0;border-bottom:none;padding-bottom:0;">
            University ID: <strong>{{ $patient->university_id }}</strong> &bull;
            Department: <strong>{{ $patient->department ?? 'General' }}</strong> &bull;
            Blood Group: <strong style="color:#c0392b;">{{ $patient->blood_group ?? 'Not Set' }}</strong>
        </div>
    </div>
    <div>
        <a href="{{ route('doctor.prescriptions.create', ['patient_id' => $patient->id]) }}" class="btn btn-green btn-sm">Generate New Prescription</a>
        <a href="javascript:history.back()" class="btn btn-outline btn-sm">&larr; Back to Queue</a>
    </div>
</div>

<hr style="border:none;border-bottom:1px solid var(--border);margin:1rem 0 1.4rem 0;">

{{-- Medical Alerts Banner --}}
<div style="background:#fff;border:1px solid var(--border);border-left:4px solid #c0392b;border-radius:var(--radius);padding:1rem 1.2rem;margin-bottom:1.4rem;">
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:1rem;font-size:12px;">
        <div>
            <strong style="color:#c0392b;text-transform:uppercase;font-size:11px;">Known Allergies</strong>
            <p style="margin-top:2px;font-weight:700;">{{ $patient->allergies ?: 'No known drug or food allergies' }}</p>
        </div>
        <div>
            <strong style="color:var(--text);text-transform:uppercase;font-size:11px;">Pre-existing Medical Conditions</strong>
            <p style="margin-top:2px;">
                @if(!empty($patient->conditions) && is_array($patient->conditions))
                    {{ implode(', ', $patient->conditions) }}
                @else
                    None reported
                @endif
            </p>
        </div>
        <div>
            <strong style="color:var(--text);text-transform:uppercase;font-size:11px;">Current Routine Medications</strong>
            <p style="margin-top:2px;">{{ $patient->medications ?: 'None reported' }}</p>
        </div>
        <div>
            <strong style="color:var(--text);text-transform:uppercase;font-size:11px;">Emergency Contact</strong>
            <p style="margin-top:2px;">{{ $patient->emergency_name ?? 'N/A' }} ({{ $patient->emergency_phone ?? 'N/A' }})</p>
        </div>
    </div>
</div>

{{-- Past Prescriptions History --}}
<div class="card" style="margin-bottom:1.4rem;">
    <div class="card-header">
        <span>Prescription History ({{ $patient->prescriptions->count() }})</span>
    </div>
    <div class="card-body" style="padding:0;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Rx Reference</th>
                    <th>Date</th>
                    <th>Doctor</th>
                    <th>Diagnosis &amp; Vitals</th>
                    <th>Prescribed Medicines</th>
                    <th>Clinical Advice</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($patient->prescriptions as $rx)
                    <tr>
                        <td>
                            <strong style="color:var(--green);">{{ $rx->prescription_number }}</strong><br>
                            <span class="badge {{ $rx->status === 'dispensed' ? 'badge-green' : 'badge-yellow' }}" style="font-size:9px;">
                                {{ ucfirst($rx->status) }}
                            </span>
                        </td>
                        <td>{{ $rx->created_at->format('d M Y') }}</td>
                        <td>{{ $rx->doctor->name }}</td>
                        <td>
                            <strong>{{ $rx->diagnosis }}</strong>
                            <div style="font-size:11px;color:var(--muted);margin-top:2px;">
                                BP: {{ $rx->bp ?: 'N/A' }} | Temp: {{ $rx->temperature ?: 'N/A' }} | Wt: {{ $rx->weight ?: 'N/A' }}
                            </div>
                        </td>
                        <td>
                            <ul style="padding-left:1rem;margin:0;font-size:12px;">
                                @foreach($rx->items as $item)
                                    <li><strong>{{ $item->medicine_name }}</strong> &mdash; {{ $item->dosage }} ({{ $item->duration }})</li>
                                @endforeach
                            </ul>
                        </td>
                        <td style="max-width:200px;font-size:12px;color:var(--muted);">
                            {{ Str::limit($rx->clinical_advice, 60) }}
                        </td>
                        <td>
                            <a href="{{ route('patient.prescriptions.show', $rx->id) }}" class="btn btn-outline btn-sm">Full Rx</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center;padding:1.4rem;color:var(--muted);">No past prescriptions recorded for this patient.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Past Appointments History --}}
<div class="card">
    <div class="card-header">
        <span>Consultation &amp; Appointment History</span>
    </div>
    <div class="card-body" style="padding:0;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Ref #</th>
                    <th>Date</th>
                    <th>Department</th>
                    <th>Doctor</th>
                    <th>Reason for Consultation</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($patient->appointments as $apt)
                    <tr>
                        <td><strong>{{ $apt->appointment_number }}</strong></td>
                        <td>{{ $apt->appointment_date->format('d M Y') }} at {{ $apt->time_slot }}</td>
                        <td>{{ $apt->department }}</td>
                        <td>{{ $apt->doctor?->name ?? 'Attending Officer' }}</td>
                        <td>{{ $apt->reason ?: 'General health evaluation' }}</td>
                        <td><span class="badge badge-green">{{ ucfirst($apt->status) }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align:center;padding:1.2rem;color:var(--muted);">No previous appointments found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
