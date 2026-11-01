@extends('layouts.app')
@section('title', 'Patient Dashboard')

@push('styles')
<style>
    .patient-grid {
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 1.4rem;
        align-items: start;
    }
    .queue-status-card {
        background: #fff;
        border: 2px solid var(--gold);
        border-radius: var(--radius);
        padding: 1.2rem;
        margin-bottom: 1.4rem;
        box-shadow: var(--shadow);
    }
    .token-display {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
    }
    .token-num {
        font-size: 2.2rem;
        font-weight: 700;
        color: var(--green);
        line-height: 1;
    }
    .token-sub { font-size: 12px; color: var(--muted); margin-top: 4px; }

    @media (max-width: 860px) {
        .patient-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<div class="page-title">Patient Portal &mdash; {{ $patient->full_name }}</div>
<div class="page-subtitle">University ID: <strong>{{ $patient->university_id }}</strong> &nbsp;|&nbsp; Category: <strong>{{ ucfirst($patient->role_category) }}</strong> &nbsp;|&nbsp; Department: <strong>{{ $patient->department ?? 'General' }}</strong></div>

{{-- Live Queue Banner if in Queue Today --}}
@if($activeQueue)
    <div class="queue-status-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.5rem;">
            <span class="badge badge-gold" style="font-size:11px;">TODAY'S LIVE QUEUE TICKET</span>
            <span class="badge {{ $activeQueue->status === 'in_consultation' ? 'badge-green' : 'badge-yellow' }}">
                {{ $activeQueue->status === 'in_consultation' ? 'NOW IN CONSULTATION' : 'WAITING FOR DOCTOR' }}
            </span>
        </div>
        <div class="token-display">
            <div>
                <div class="token-num">{{ $activeQueue->token_number }}</div>
                <div class="token-sub">Department: <strong>{{ $activeQueue->department }}</strong> &nbsp;|&nbsp; Clinic Counter 1</div>
            </div>
            <div style="text-align:right;">
                @if($activeQueue->status === 'in_consultation')
                    <div style="font-size:14px;font-weight:700;color:var(--green);">Please proceed to Doctor's Consultation Room</div>
                    <div style="font-size:12px;color:var(--muted);">Doctor is attending you now.</div>
                @else
                    <div style="font-size:14px;font-weight:700;color:var(--text);">{{ $patientsAhead }} patient(s) ahead of you</div>
                    <div style="font-size:12px;color:var(--muted);">Estimated wait: approx. {{ max(5, $patientsAhead * 8) }} minutes</div>
                @endif
            </div>
            <div>
                <a href="{{ route('patient.queue') }}" class="btn btn-green btn-sm">Open Live Queue Board &rarr;</a>
            </div>
        </div>
    </div>
@endif

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-val">{{ $patient->blood_group ?? 'Not Set' }}</div>
        <div class="stat-label">Blood Group</div>
        <div class="stat-sub">Medical Profile</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-val">{{ $upcomingAppointments->count() }}</div>
        <div class="stat-label">Upcoming Visits</div>
        <div class="stat-sub">Confirmed appointments</div>
    </div>
    <div class="stat-card gold">
        <div class="stat-val">{{ $prescriptions->count() }}</div>
        <div class="stat-label">Total Prescriptions</div>
        <div class="stat-sub">Digital Rx on file</div>
    </div>
    <div class="stat-card">
        <div class="stat-val" style="color:var(--green);font-size:1.4rem;">VERIFIED</div>
        <div class="stat-label">Identity Status</div>
        <div class="stat-sub">Authorized University Member</div>
    </div>
</div>

<div class="patient-grid">
    {{-- Left: Prescriptions & Upcoming Appointments --}}
    <div>
        {{-- Recent Digital Prescriptions --}}
        <div class="card" style="margin-bottom:1.4rem;">
            <div class="card-header">
                <span>Digital Prescriptions &amp; Printable PDF</span>
                <a href="{{ route('patient.prescriptions') }}" style="color:#fff;font-size:11px;text-decoration:underline;">View All &rarr;</a>
            </div>
            <div class="card-body" style="padding:0;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Rx Number</th>
                            <th>Doctor</th>
                            <th>Diagnosis</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($prescriptions as $rx)
                            <tr>
                                <td><strong>{{ $rx->prescription_number }}</strong><br><small style="color:var(--muted);">{{ $rx->created_at->format('d M Y') }}</small></td>
                                <td>{{ $rx->doctor->name }}</td>
                                <td style="max-width:240px;">{{ Str::limit($rx->diagnosis, 45) }}</td>
                                <td>
                                    <span class="badge {{ $rx->status === 'dispensed' ? 'badge-green' : 'badge-yellow' }}">
                                        {{ ucfirst($rx->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div style="display:flex;gap:4px;">
                                        <a href="{{ route('patient.prescriptions.show', $rx->id) }}" class="btn btn-outline btn-sm">View</a>
                                        <a href="{{ route('patient.prescriptions.pdf', $rx->id) }}" class="btn btn-green btn-sm" title="Download printable PDF">PDF &darr;</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align:center;padding:1.2rem;color:var(--muted);">No digital prescriptions recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Upcoming Appointments --}}
        <div class="card">
            <div class="card-header">
                <span>My Appointments</span>
                <a href="{{ route('patient.appointments') }}" style="color:#fff;font-size:11px;text-decoration:underline;">Book New Visit &rarr;</a>
            </div>
            <div class="card-body" style="padding:0;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Ref #</th>
                            <th>Department</th>
                            <th>Doctor</th>
                            <th>Date &amp; Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($upcomingAppointments as $apt)
                            <tr>
                                <td><strong>{{ $apt->appointment_number }}</strong></td>
                                <td>{{ $apt->department }}</td>
                                <td>{{ $apt->doctor?->name ?? 'Assigned on arrival' }}</td>
                                <td>{{ $apt->appointment_date->format('d M Y') }} at {{ $apt->time_slot }}</td>
                                <td><span class="badge badge-green">{{ ucfirst($apt->status) }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align:center;padding:1.2rem;color:var(--muted);">No upcoming appointments scheduled.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Right Sidebar: Identity & Quick Actions --}}
    <div>
        <div class="card" style="margin-bottom:1.4rem;">
            <div class="card-header">Identity &amp; Profile Summary</div>
            <div class="card-body" style="font-size:13px;line-height:1.7;">
                <div><strong>University ID:</strong> {{ $patient->university_id }}</div>
                <div><strong>Email:</strong> {{ $patient->email }}</div>
                <div><strong>Mobile:</strong> {{ $patient->phone }}</div>
                <div><strong>Blood Group:</strong> <span class="badge badge-red">{{ $patient->blood_group ?? 'Not set' }}</span></div>
                <div style="margin-top:.5rem;border-top:1px solid #ecf2ee;padding-top:.5rem;">
                    <strong>Known Allergies:</strong><br>
                    <span style="color:#c0392b;font-weight:700;">{{ $patient->allergies ?: 'No known drug allergies reported' }}</span>
                </div>
                <div style="margin-top:.5rem;border-top:1px solid #ecf2ee;padding-top:.5rem;">
                    <strong>Emergency Contact:</strong><br>
                    {{ $patient->emergency_name ?? 'Not set' }} &ndash; {{ $patient->emergency_phone ?? 'N/A' }}
                </div>
                <div style="margin-top:1rem;">
                    <a href="{{ route('patient.profile') }}" class="btn btn-outline btn-sm" style="width:100%;">Edit Profile &amp; Contact Info</a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Quick Actions</div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:.6rem;">
                @if(!$activeQueue)
                    <form action="{{ route('patient.queue.join') }}" method="POST">
                        @csrf
                        <input type="hidden" name="department" value="General Medicine">
                        <button type="submit" class="btn btn-green" style="width:100%;">Get Today's Walk-in Queue Token</button>
                    </form>
                @endif
                <a href="{{ route('patient.queue') }}" class="btn btn-outline" style="width:100%;">View Live Queue Tracking</a>
                <a href="{{ route('patient.appointments') }}" class="btn btn-outline" style="width:100%;">Book Doctor Appointment</a>
                <a href="{{ route('patient.prescriptions') }}" class="btn btn-outline" style="width:100%;">Download Prescriptions (PDF)</a>
            </div>
        </div>
    </div>
</div>
@endsection
