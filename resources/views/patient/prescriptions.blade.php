@extends('layouts.app')
@section('title', 'My Prescriptions')

@section('content')
<div class="page-title">Digital Prescription Portal &amp; PDF Downloads</div>
<div class="page-subtitle">Access all digital medical prescriptions issued by University Medical Centre doctors. View medicines, clinical instructions, and download official printable PDF documents.</div>

<div class="card">
    <div class="card-header">
        <span>Prescription History</span>
        <span style="font-size:11px;font-weight:400;text-transform:none;">Total Records: {{ $prescriptions->count() }}</span>
    </div>
    <div class="card-body" style="padding:0;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Prescription #</th>
                    <th>Date Issued</th>
                    <th>Prescribing Doctor</th>
                    <th>Diagnosis</th>
                    <th>Medicines Prescribed</th>
                    <th>Pharmacy Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($prescriptions as $rx)
                    <tr>
                        <td>
                            <strong style="color:var(--green);">{{ $rx->prescription_number }}</strong>
                        </td>
                        <td>{{ $rx->created_at->format('d M Y, h:i A') }}</td>
                        <td>
                            <strong>{{ $rx->doctor->name }}</strong>
                            <div style="font-size:11px;color:var(--muted);">{{ $rx->doctor->department }}</div>
                        </td>
                        <td style="max-width:220px;">
                            <strong>{{ $rx->diagnosis }}</strong>
                            @if($rx->bp || $rx->temperature)
                                <div style="font-size:11px;color:var(--muted);margin-top:2px;">
                                    BP: {{ $rx->bp ?? 'N/A' }} | Temp: {{ $rx->temperature ?? 'N/A' }}
                                </div>
                            @endif
                        </td>
                        <td>
                            <ul style="padding-left:1rem;font-size:12px;margin:0;">
                                @foreach($rx->items as $item)
                                    <li><strong>{{ $item->medicine_name }}</strong> ({{ $item->dosage }}, {{ $item->duration }})</li>
                                @endforeach
                            </ul>
                        </td>
                        <td>
                            @if($rx->status === 'dispensed')
                                <span class="badge badge-green">Dispensed</span>
                                <div style="font-size:10px;color:var(--muted);margin-top:2px;">From Central Pharmacy</div>
                            @else
                                <span class="badge badge-yellow">Active / Ready to Dispense</span>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex;gap:5px;flex-wrap:wrap;">
                                <a href="{{ route('patient.prescriptions.show', $rx->id) }}" class="btn btn-outline btn-sm">View Details</a>
                                <a href="{{ route('patient.prescriptions.pdf', $rx->id) }}" class="btn btn-green btn-sm" title="Download official PDF format">
                                    PDF &darr;
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center;padding:1.6rem;color:var(--muted);">No prescriptions found in your medical records.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
