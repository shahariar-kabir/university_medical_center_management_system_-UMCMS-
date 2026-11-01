@extends('layouts.app')
@section('title', 'Appointments')

@push('styles')
<style>
    .appt-layout {
        display: grid;
        grid-template-columns: 360px 1fr;
        gap: 1.5rem;
        align-items: start;
    }
    .slot-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 5px;
        margin-top: 5px;
    }
    .slot-btn {
        border: 1px solid #b0c4b8;
        border-radius: 3px;
        padding: .4rem .2rem;
        text-align: center;
        font-size: 11px;
        font-weight: 700;
        cursor: pointer;
        background: #fff;
        color: var(--text);
        font-family: Arial, Helvetica, sans-serif;
        transition: all .12s;
    }
    .slot-btn:hover { background: var(--green-pale); border-color: var(--green); color: var(--green); }
    .slot-btn.selected { background: var(--green); border-color: var(--green); color: #fff; }

    @media (max-width: 860px) {
        .appt-layout { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<div class="page-title">Appointment Booking &amp; Schedule</div>
<div class="page-subtitle">Schedule advance consultations with university medical officers and specialists. Appointments booked for today automatically generate a queue token.</div>

<div class="appt-layout">
    {{-- Booking Form --}}
    <div class="card">
        <div class="card-header">Book New Appointment</div>
        <div class="card-body">
            <form action="{{ route('patient.appointments.store') }}" method="POST">
                @csrf

                <div style="margin-bottom:.8rem;">
                    <label class="form-label">Clinic / Department <span class="req">*</span></label>
                    <select name="department" class="form-control" required>
                        <option value="General Medicine">General Medicine</option>
                        <option value="Dental">Dental Clinic</option>
                        <option value="Counselling">Counselling &amp; Mental Health</option>
                        <option value="Gynaecology">Gynaecology</option>
                        <option value="Diagnostics">Laboratory &amp; Diagnostics</option>
                    </select>
                </div>

                <div style="margin-bottom:.8rem;">
                    <label class="form-label">Preferred Doctor (Optional)</label>
                    <select name="doctor_id" class="form-control">
                        <option value="">&mdash; Any Available Medical Officer &mdash;</option>
                        @foreach($doctors as $doc)
                            <option value="{{ $doc->id }}">{{ $doc->name }} ({{ $doc->department }})</option>
                        @endforeach
                    </select>
                </div>

                <div style="margin-bottom:.8rem;">
                    <label class="form-label">Date <span class="req">*</span></label>
                    <input type="date" name="appointment_date" class="form-control" min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}" required>
                </div>

                <div style="margin-bottom:.8rem;">
                    <label class="form-label">Preferred Time Slot <span class="req">*</span></label>
                    <input type="hidden" name="time_slot" id="timeSlotInput" value="09:00" required>
                    <div class="slot-grid">
                        @foreach(['08:30','09:00','09:30','10:00','10:30','11:00','11:30','14:00','14:30','15:00','15:30','16:00'] as $slot)
                            <button type="button" class="slot-btn {{ $slot === '09:00' ? 'selected' : '' }}" onclick="selectSlot(this, '{{ $slot }}')">
                                {{ $slot }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div style="margin-bottom:1.1rem;">
                    <label class="form-label">Reason for Consultation</label>
                    <textarea name="reason" class="form-control" rows="2" placeholder="Briefly describe your symptoms or reason for visit..."></textarea>
                </div>

                <button type="submit" class="btn btn-green" style="width:100%;">Confirm &amp; Book Appointment</button>
            </form>
        </div>
    </div>

    {{-- Appointment History Table --}}
    <div class="card">
        <div class="card-header">
            <span>My Appointment History</span>
            <span style="font-size:11px;font-weight:400;text-transform:none;">Total booked: {{ $appointments->count() }}</span>
        </div>
        <div class="card-body" style="padding:0;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Department</th>
                        <th>Doctor</th>
                        <th>Date &amp; Time</th>
                        <th>Reason</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointments as $apt)
                        <tr>
                            <td><strong>{{ $apt->appointment_number }}</strong></td>
                            <td>{{ $apt->department }}</td>
                            <td>{{ $apt->doctor?->name ?? 'First Available' }}</td>
                            <td>
                                <div>{{ $apt->appointment_date->format('d M Y') }}</div>
                                <div style="font-size:11px;color:var(--muted);font-weight:700;">{{ $apt->time_slot }}</div>
                            </td>
                            <td style="max-width:200px;font-size:12px;">{{ Str::limit($apt->reason ?: 'General review', 45) }}</td>
                            <td>
                                <span class="badge {{ $apt->status === 'completed' ? 'badge-blue' : ($apt->status === 'cancelled' ? 'badge-red' : 'badge-green') }}">
                                    {{ ucfirst($apt->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center;padding:1.4rem;color:var(--muted);">No appointments booked yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function selectSlot(el, slot) {
        document.querySelectorAll('.slot-btn').forEach(b => b.classList.remove('selected'));
        el.classList.add('selected');
        document.getElementById('timeSlotInput').value = slot;
    }
</script>
@endpush
