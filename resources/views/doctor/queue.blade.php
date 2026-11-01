@extends('layouts.app')
@section('title', 'Consultation Queue Management')

@section('content')
<div class="page-title">Patient Consultation Queue Management</div>
<div class="page-subtitle">Department: <strong>{{ $doctor->department ?? 'General Medicine' }}</strong> &bull; Attending: <strong>{{ $doctor->name }}</strong></div>

<div class="card">
    <div class="card-header">
        <span>Active Queue &mdash; {{ date('d M Y') }}</span>
        <span style="font-size:11px;font-weight:400;text-transform:none;">Total in line: {{ $todayQueues->count() }}</span>
    </div>
    <div class="card-body" style="padding:0;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Token #</th>
                    <th>Patient Name</th>
                    <th>University ID &amp; Dept</th>
                    <th>Allergies &amp; Vitals</th>
                    <th>Wait Time</th>
                    <th>Queue Status</th>
                    <th>Clinical Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($todayQueues as $q)
                    <tr style="{{ $q->status === 'in_consultation' ? 'background:#f0f9f3;border-left:4px solid var(--green);' : '' }}">
                        <td>
                            <strong style="color:var(--green);font-size:15px;">{{ $q->token_number }}</strong>
                        </td>
                        <td>
                            <strong>{{ $q->patient->full_name }}</strong>
                            <div style="font-size:11px;color:var(--muted);">{{ ucfirst($q->patient->role_category) }} ({{ $q->patient->gender }})</div>
                        </td>
                        <td>
                            <div>{{ $q->patient->university_id }}</div>
                            <div style="font-size:11px;color:var(--muted);">{{ $q->patient->department ?? 'General' }}</div>
                        </td>
                        <td>
                            @if($q->patient->allergies)
                                <span class="badge badge-red" style="font-size:10px;">{{ $q->patient->allergies }}</span>
                            @else
                                <span style="font-size:11px;color:var(--muted);">No allergies reported</span>
                            @endif
                            <div style="font-size:11px;margin-top:2px;">Blood: <strong>{{ $q->patient->blood_group ?? 'N/A' }}</strong></div>
                        </td>
                        <td style="font-size:12px;">
                            {{ $q->created_at->diffForHumans(null, true) }} ago
                        </td>
                        <td>
                            @php
                                $statusBadges = [
                                    'in_consultation' => 'badge-green',
                                    'waiting' => 'badge-yellow',
                                    'completed' => 'badge-blue',
                                    'skipped' => 'badge-red',
                                ];
                            @endphp
                            <span class="badge {{ $statusBadges[$q->status] ?? 'badge-gray' }}">
                                {{ str_replace('_', ' ', ucfirst($q->status)) }}
                            </span>
                        </td>
                        <td>
                            <div style="display:flex;gap:4px;flex-wrap:wrap;">
                                <a href="{{ route('doctor.patients.history', $q->patient_id) }}" class="btn btn-outline btn-sm" title="View past history">History</a>

                                @if($q->status === 'waiting')
                                    <form action="{{ route('doctor.queue.call', $q->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-green btn-sm">Call Patient</button>
                                    </form>
                                    <form action="{{ route('doctor.queue.skip', $q->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-outline btn-sm" style="color:#c0392b;border-color:#e0a098;">Skip</button>
                                    </form>
                                @elseif($q->status === 'in_consultation')
                                    <a href="{{ route('doctor.prescriptions.create', ['queue_id' => $q->id, 'patient_id' => $q->patient_id]) }}" class="btn btn-green btn-sm">Write Rx</a>
                                    <form action="{{ route('doctor.queue.complete', $q->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-gold btn-sm">Complete</button>
                                    </form>
                                @else
                                    <span style="font-size:11px;color:var(--muted);">Attended</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center;padding:1.6rem;color:var(--muted);">No patients currently in queue.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
