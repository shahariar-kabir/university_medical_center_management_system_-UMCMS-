@extends('layouts.app')
@section('title', 'Live Queue Tracking')

@push('styles')
<style>
    .queue-board-top {
        display: grid;
        grid-template-columns: 320px 1fr;
        gap: 1.4rem;
        margin-bottom: 1.5rem;
    }
    .current-token-display {
        background: linear-gradient(135deg, var(--green-dk) 0%, var(--green) 100%);
        color: #fff;
        border-radius: var(--radius);
        padding: 1.5rem;
        text-align: center;
        border-bottom: 4px solid var(--gold);
        box-shadow: var(--shadow);
    }
    .current-token-display .large-token {
        font-size: 3.5rem;
        font-weight: 700;
        color: #fff;
        line-height: 1;
        margin: .6rem 0;
        letter-spacing: .02em;
    }
    .my-token-box {
        background: #fff;
        border: 2px solid var(--border);
        border-radius: var(--radius);
        padding: 1.2rem;
        box-shadow: var(--shadow);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .my-token-box.active {
        border-color: var(--gold);
        background: #fffcf5;
    }
    @media (max-width: 820px) {
        .queue-board-top { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<div class="page-title">Live Consultation Queue Tracking</div>
<div class="page-subtitle">Real-time status of university medical centre consultation rooms and patient queuing.</div>

<div class="queue-board-top">
    {{-- Now Serving Token --}}
    <div class="current-token-display">
        <div style="font-size:11px;text-transform:uppercase;letter-spacing:.08em;opacity:.85;font-weight:700;">CURRENTLY CALLING TOKEN</div>
        <div class="large-token">
            {{ $nowCalling ? $nowCalling->token_number : 'NONE' }}
        </div>
        <div style="font-size:13px;font-weight:700;">
            @if($nowCalling)
                Room 1 &mdash; {{ $nowCalling->department }}
                <div style="font-size:11px;opacity:.8;font-weight:400;margin-top:2px;">Patient: {{ $nowCalling->patient->full_name }}</div>
            @else
                Doctor's Desk Ready for Next Patient
            @endif
        </div>
    </div>

    {{-- My Token Status / Join Queue --}}
    <div class="my-token-box {{ $myToken ? 'active' : '' }}">
        @if($myToken)
            <div>
                <div style="display:flex;align-items:center;justify-content:space-between;">
                    <span class="badge badge-gold">MY ACTIVE TOKEN</span>
                    <span class="badge {{ $myToken->status === 'in_consultation' ? 'badge-green' : 'badge-yellow' }}">
                        {{ $myToken->status === 'in_consultation' ? 'NOW IN CONSULTATION' : 'WAITING' }}
                    </span>
                </div>
                <div style="display:flex;align-items:baseline;gap:1rem;margin-top:.6rem;">
                    <span style="font-size:2.4rem;font-weight:700;color:var(--green);">{{ $myToken->token_number }}</span>
                    <span style="font-size:13px;color:var(--muted);">Department: <strong>{{ $myToken->department }}</strong></span>
                </div>
                <div style="font-size:13px;margin-top:.4rem;">
                    @if($myToken->status === 'in_consultation')
                        <strong style="color:var(--green);">Your token is being called right now! Please step inside the doctor's room.</strong>
                    @else
                        <span><strong>{{ $patientsAhead }}</strong> patient(s) ahead of you in line.</span>
                        <span style="color:var(--muted);margin-left:6px;">Estimated waiting time: <strong>~{{ max(5, $patientsAhead * 8) }} minutes</strong></span>
                    @endif
                </div>
            </div>
            <div style="font-size:11px;color:var(--muted);border-top:1px solid #ecf2ee;padding-top:.5rem;margin-top:.8rem;">
                Issued today at {{ $myToken->created_at->format('h:i A') }}. Please remain in the reception waiting area.
            </div>
        @else
            <div>
                <span class="badge badge-gray">WALK-IN QUEUE REGISTRATION</span>
                <h3 style="font-size:15px;color:var(--green-dk);margin-top:.4rem;">Visiting the Medical Centre Today?</h3>
                <p style="font-size:12px;color:var(--muted);margin-top:4px;line-height:1.5;">
                    If you do not have an advance appointment, you can generate a walk-in queue token instantly for general consultation today.
                </p>
                <form action="{{ route('patient.queue.join') }}" method="POST" style="margin-top:.8rem;display:flex;gap:.6rem;flex-wrap:wrap;">
                    @csrf
                    <select name="department" class="form-control" style="max-width:220px;" required>
                        <option value="General Medicine">General Medicine</option>
                        <option value="Dental">Dental Clinic</option>
                        <option value="Counselling">Counselling &amp; Mental Health</option>
                    </select>
                    <button type="submit" class="btn btn-green">Get Walk-in Queue Token</button>
                </form>
            </div>
            <div style="font-size:11px;color:var(--muted);margin-top:.8rem;">
                * Walk-in tokens are valid for the day of issue only.
            </div>
        @endif
    </div>
</div>

{{-- Queue Overview Stats --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-val">{{ $todayQueues->count() }}</div>
        <div class="stat-label">Total Tokens Today</div>
        <div class="stat-sub">Patients registered</div>
    </div>
    <div class="stat-card gold">
        <div class="stat-val">{{ $waitingCount }}</div>
        <div class="stat-label">Currently Waiting</div>
        <div class="stat-sub">In queue line</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-val">{{ $completedCount }}</div>
        <div class="stat-label">Completed Consultations</div>
        <div class="stat-sub">Attended by doctor today</div>
    </div>
    <div class="stat-card">
        <div class="stat-val">~8 min</div>
        <div class="stat-label">Avg. Consultation Time</div>
        <div class="stat-sub">Standard medical review</div>
    </div>
</div>

{{-- Full Queue Board Table --}}
<div class="card">
    <div class="card-header">
        <span>Today's Live Queue Board &mdash; {{ date('d M Y') }}</span>
        <span style="font-size:11px;font-weight:400;text-transform:none;">Auto-refreshes with consultation room activity</span>
    </div>
    <div class="card-body" style="padding:0;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Token</th>
                    <th>Patient Name</th>
                    <th>Department</th>
                    <th>Assigned Doctor</th>
                    <th>Issued At</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($todayQueues as $q)
                    <tr style="{{ $myToken && $myToken->id === $q->id ? 'background:#fdf9ed;font-weight:700;' : '' }}">
                        <td>
                            <strong style="color:var(--green);font-size:14px;">{{ $q->token_number }}</strong>
                            @if($myToken && $myToken->id === $q->id)
                                <span class="badge badge-gold" style="font-size:9px;margin-left:4px;">YOU</span>
                            @endif
                        </td>
                        <td>{{ $q->patient->full_name }}</td>
                        <td>{{ $q->department }}</td>
                        <td>{{ $q->doctor?->name ?? 'Next available' }}</td>
                        <td style="font-size:11px;color:var(--muted);">{{ $q->created_at->format('h:i A') }}</td>
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
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align:center;padding:1.4rem;color:var(--muted);">No queue tokens issued yet today.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
