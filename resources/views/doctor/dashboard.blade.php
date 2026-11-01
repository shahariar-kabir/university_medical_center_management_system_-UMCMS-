@extends('layouts.app')
@section('title', 'Doctor Consultation Desk')

@push('styles')
<style>
    .doctor-grid {
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 1.4rem;
        align-items: start;
    }
    .active-consult-card {
        background: #f4fbf6;
        border: 2px solid var(--green);
        border-radius: var(--radius);
        padding: 1.2rem;
        margin-bottom: 1.4rem;
        box-shadow: var(--shadow);
    }
    @media (max-width: 860px) {
        .doctor-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<div class="page-title">Doctor Consultation Desk &mdash; {{ $doctor->name }}</div>
<div class="page-subtitle">{{ $doctor->designation ?? 'Medical Officer' }} &bull; Department: <strong>{{ $doctor->department ?? 'General Medicine' }}</strong></div>

{{-- Active Patient in Consultation Card --}}
@if($activePatientQueue)
    <div class="active-consult-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.6rem;">
            <span class="badge badge-green" style="font-size:11px;">PATIENT IN CONSULTATION NOW</span>
            <span style="font-size:12px;color:var(--muted);">Called at: {{ $activePatientQueue->called_at?->format('h:i A') }}</span>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
            <div>
                <div style="font-size:1.6rem;font-weight:700;color:var(--green-dk);">
                    Token {{ $activePatientQueue->token_number }} &mdash; {{ $activePatientQueue->patient->full_name }}
                </div>
                <div style="font-size:12px;color:var(--muted);margin-top:2px;">
                    University ID: <strong>{{ $activePatientQueue->patient->university_id }}</strong> &bull;
                    Blood Group: <strong>{{ $activePatientQueue->patient->blood_group ?? 'N/A' }}</strong> &bull;
                    Allergies: <strong style="color:#c0392b;">{{ $activePatientQueue->patient->allergies ?: 'None' }}</strong>
                </div>
            </div>
            <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
                <a href="{{ route('doctor.patients.history', $activePatientQueue->patient_id) }}" class="btn btn-outline btn-sm">View Medical History</a>
                <a href="{{ route('doctor.prescriptions.create', ['queue_id' => $activePatientQueue->id, 'patient_id' => $activePatientQueue->patient_id]) }}" class="btn btn-green btn-sm">Generate Digital Prescription</a>
                <form action="{{ route('doctor.queue.complete', $activePatientQueue->id) }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-gold btn-sm">Complete Consultation</button>
                </form>
            </div>
        </div>
    </div>
@endif

<div class="stats-grid">
    <div class="stat-card gold">
        <div class="stat-val">{{ $waitingCount }}</div>
        <div class="stat-label">Patients Waiting</div>
        <div class="stat-sub">In queue outside</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-val">{{ $completedTodayCount }}</div>
        <div class="stat-label">Consultations Done</div>
        <div class="stat-sub">Attended today</div>
    </div>
    <div class="stat-card">
        <div class="stat-val">{{ $recentPrescriptions->count() }}</div>
        <div class="stat-label">Prescriptions Issued</div>
        <div class="stat-sub">Recent clinical orders</div>
    </div>
    <div class="stat-card">
        <div class="stat-val" style="color:var(--green);font-size:1.4rem;">ONLINE</div>
        <div class="stat-label">Duty Status</div>
        <div class="stat-sub">Accepting consultation queue</div>
    </div>
</div>

<div class="doctor-grid">
    {{-- Left: Today's Consultation Queue --}}
    <div>
        <div class="card" style="margin-bottom:1.4rem;">
            <div class="card-header">
                <span>Today's Consultation Queue &mdash; {{ $doctor->department }}</span>
                <a href="{{ route('doctor.queue') }}" style="color:#fff;font-size:11px;text-decoration:underline;">Manage Queue &rarr;</a>
            </div>
            <div class="card-body" style="padding:0;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Token</th>
                            <th>Patient Name</th>
                            <th>ID &amp; Category</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($todayQueues as $q)
                            <tr style="{{ $q->status === 'in_consultation' ? 'background:#f0f9f3;' : '' }}">
                                <td><strong style="color:var(--green);font-size:13px;">{{ $q->token_number }}</strong></td>
                                <td>
                                    <strong>{{ $q->patient->full_name }}</strong>
                                    <div style="font-size:11px;color:var(--muted);">Allergies: {{ $q->patient->allergies ?: 'None' }}</div>
                                </td>
                                <td>
                                    {{ $q->patient->university_id }}<br>
                                    <small style="color:var(--muted);">{{ ucfirst($q->patient->role_category) }}</small>
                                </td>
                                <td>
                                    <span class="badge {{ $q->status === 'in_consultation' ? 'badge-green' : ($q->status === 'completed' ? 'badge-blue' : 'badge-yellow') }}">
                                        {{ str_replace('_', ' ', ucfirst($q->status)) }}
                                    </span>
                                </td>
                                <td>
                                    <div style="display:flex;gap:4px;">
                                        @if($q->status === 'waiting')
                                            <form action="{{ route('doctor.queue.call', $q->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-green btn-sm">Call Patient</button>
                                            </form>
                                        @elseif($q->status === 'in_consultation')
                                            <a href="{{ route('doctor.prescriptions.create', ['queue_id' => $q->id, 'patient_id' => $q->patient_id]) }}" class="btn btn-gold btn-sm">Write Rx</a>
                                        @else
                                            <span style="color:var(--muted);font-size:11px;">Completed</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align:center;padding:1.4rem;color:var(--muted);">No patients currently in your department queue.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Recent Digital Prescriptions Issued --}}
        <div class="card">
            <div class="card-header">
                <span>Recent Prescriptions Generated by You</span>
            </div>
            <div class="card-body" style="padding:0;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Rx Ref</th>
                            <th>Patient</th>
                            <th>Diagnosis</th>
                            <th>Medicines</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentPrescriptions as $rx)
                            <tr>
                                <td><strong>{{ $rx->prescription_number }}</strong></td>
                                <td>{{ $rx->patient->full_name }}</td>
                                <td style="max-width:200px;">{{ Str::limit($rx->diagnosis, 35) }}</td>
                                <td>{{ $rx->items->count() }} item(s)</td>
                                <td>
                                    <span class="badge {{ $rx->status === 'dispensed' ? 'badge-green' : 'badge-yellow' }}">
                                        {{ ucfirst($rx->status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('patient.prescriptions.show', $rx->id) }}" class="btn btn-outline btn-sm">View Rx</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align:center;padding:1.2rem;color:var(--muted);">No prescriptions issued today.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Right Sidebar: Quick Actions & Links --}}
    <div>
        <div class="card" style="margin-bottom:1.4rem;">
            <div class="card-header">Clinical Quick Actions</div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:.6rem;">
                <a href="{{ route('doctor.prescriptions.create') }}" class="btn btn-green" style="width:100%;">Generate Digital Prescription</a>
                <a href="{{ route('doctor.queue') }}" class="btn btn-outline" style="width:100%;">Open Full Queue Desk</a>
                <a href="{{ route('pharmacy.inventory') }}" class="btn btn-outline" style="width:100%;">Check Medicine Inventory</a>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Consultation Protocol</div>
            <div class="card-body" style="font-size:12px;color:var(--muted);line-height:1.6;">
                <ul style="padding-left:1.1rem;">
                    <li>Always verify student/staff identity and known drug allergies before prescribing.</li>
                    <li>Digital prescriptions are routed directly to the Central Pharmacy inventory desk.</li>
                    <li>Use standard dosage format (e.g. <code>1+0+1</code>) for clarity.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
