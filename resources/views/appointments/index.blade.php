@extends('layouts.app')
@section('title', 'Appointments')

@push('styles')
<style>
    .appt-layout {
        display: grid;
        grid-template-columns: 360px 1fr;
        gap: 1.4rem;
        align-items: start;
    }

    /* Booking form */
    .book-card {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        overflow: hidden;
        position: sticky; top: 120px;
    }
    .book-head {
        background: var(--green);
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .07em;
        padding: .5rem .9rem;
    }
    .book-body { padding: 1rem; }
    .field-group { display: flex; flex-direction: column; gap: 3px; margin-bottom: .7rem; }
    .book-foot {
        background: #f4f8f5;
        border-top: 1px solid var(--border);
        padding: .65rem .9rem;
        display: flex;
        gap: .5rem;
        justify-content: flex-end;
    }

    /* time slots */
    .slot-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 4px;
        margin-top: 4px;
    }
    .slot {
        border: 1px solid #b0c4b8;
        border-radius: 3px;
        padding: .32rem .2rem;
        text-align: center;
        font-size: 11px;
        font-weight: 700;
        cursor: pointer;
        background: #fff;
        color: var(--text);
        transition: all .12s;
        font-family: Arial, Helvetica, sans-serif;
        letter-spacing: .02em;
    }
    .slot:hover    { border-color: var(--green); color: var(--green); background: var(--green-pale); }
    .slot.sel      { border-color: var(--green); background: var(--green); color: #fff; }
    .slot.taken    { background: #f0f0f0; color: #bbb; border-color: #ddd; cursor: not-allowed; text-decoration: line-through; font-weight: 400; }

    /* appointment list */
    .list-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: .7rem;
        flex-wrap: wrap;
        gap: .5rem;
    }
    .list-head h2 { font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--green); }

    .filter-row { display: flex; gap: 3px; }
    .f-btn {
        padding: 3px 10px;
        border-radius: 3px;
        font-size: 11px;
        font-weight: 700;
        cursor: pointer;
        border: 1px solid var(--border);
        background: #fff;
        color: var(--muted);
        font-family: Arial, Helvetica, sans-serif;
        transition: all .12s;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .f-btn.on { background: var(--green); color: #fff; border-color: var(--green); }
    .f-btn:hover:not(.on) { border-color: var(--green); color: var(--green); }

    .appt-table {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        overflow: hidden;
    }
    .appt-table table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .appt-table thead th {
        background: var(--green-dk);
        color: #fff;
        padding: .5rem .75rem;
        text-align: left;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .05em;
        font-weight: 700;
    }
    .appt-table tbody td {
        padding: .55rem .75rem;
        border-bottom: 1px solid #ecf2ee;
        vertical-align: middle;
    }
    .appt-table tbody tr:last-child td { border-bottom: none; }
    .appt-table tbody tr:nth-child(even) td { background: #f9fbfa; }
    .appt-table tbody tr:hover td { background: #f0f8f3; }

    .appt-table .patient-name { font-weight: 700; display: block; }
    .appt-table .patient-id   { font-size: 11px; color: var(--muted); }

    @media (max-width: 900px) {
        .appt-layout { grid-template-columns: 1fr; }
        .book-card { position: static; }
        .slot-grid { grid-template-columns: repeat(4,1fr); }
    }
</style>
@endpush

@section('content')

<div class="page-title">Appointment Booking</div>
<div class="page-subtitle">Use the form to book a new appointment. If you do not yet have a patient record, please <a href="{{ url('/patients/register') }}" style="color:var(--green);">register first</a>. Bring your university ID card on the day of your appointment.</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-error">
        <strong>Unable to complete booking. Please correct the following:</strong>
        <ul style="padding-left:1.2rem;margin-top:4px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="appt-layout">

    {{-- Booking Form --}}
    <div class="book-card">
        <div class="book-head">New Appointment Request</div>
        <div class="book-body">
            <form method="POST" action="{{ url('/appointments') }}">
                @csrf

                <div class="field-group">
                    <label class="form-label">University / Patient ID <span class="req">*</span></label>
                    <input type="text" name="university_id" class="form-control" placeholder="e.g. 2021-CSE-042" value="{{ old('university_id', Auth::check() ? Auth::user()->university_id : '') }}" required>
                </div>

                <div class="field-group">
                    <label class="form-label">Full Name <span class="req">*</span></label>
                    <input type="text" name="patient_name" class="form-control" value="{{ old('patient_name', Auth::check() ? Auth::user()->name : '') }}" required>
                </div>

                <div class="field-group">
                    <label class="form-label">Department / Clinic <span class="req">*</span></label>
                    <select name="department" class="form-control" required>
                        <option value="General Medicine" {{ old('department') === 'General Medicine' ? 'selected' : '' }}>General Medicine (Walk-in + Appt)</option>
                        <option value="Dental" {{ old('department') === 'Dental' ? 'selected' : '' }}>Dental Clinic</option>
                        <option value="Counselling" {{ old('department') === 'Counselling' ? 'selected' : '' }}>Counselling &amp; Mental Health</option>
                        <option value="Gynaecology" {{ old('department') === 'Gynaecology' ? 'selected' : '' }}>Gynaecology</option>
                        <option value="Laboratory" {{ old('department') === 'Laboratory' ? 'selected' : '' }}>Laboratory &amp; Diagnostics</option>
                    </select>
                </div>

                <div class="field-group">
                    <label class="form-label">Preferred Doctor</label>
                    <select name="doctor" class="form-control">
                        <option value="">— Any available Medical Officer —</option>
                        @if(isset($doctors) && count($doctors) > 0)
                            @foreach($doctors as $d)
                                <option value="{{ $d->id }}" {{ old('doctor') == $d->id ? 'selected' : '' }}>{{ $d->name }} ({{ $d->department }})</option>
                            @endforeach
                        @else
                            <option value="General Medicine">Dr. Anisur Rahman (General Medicine)</option>
                            <option value="Gynaecology">Dr. Nusrat Sultana (Gynaecology)</option>
                            <option value="Dental">Dr. Abdur Karim (Dental)</option>
                        @endif
                    </select>
                </div>

                <div class="field-group">
                    <label class="form-label">Appointment Date <span class="req">*</span></label>
                    <input type="date" name="date" class="form-control" min="{{ date('Y-m-d') }}" value="{{ old('date', date('Y-m-d')) }}" required>
                </div>

                <div class="field-group">
                    <label class="form-label">Time Slot <span class="req">*</span> <span style="font-size:10px;color:var(--muted);text-transform:none;font-weight:400;">(greyed = taken)</span></label>
                    @php
                        $slots = ['8:00','8:30','9:00','9:30','10:00','10:30','11:00','11:30','12:00','14:00','14:30','15:00','15:30','16:00','16:30','17:00'];
                        $taken = ['9:00','10:00','14:00','16:00'];
                        $currentSlot = old('time', '09:30');
                    @endphp
                    <div class="slot-grid">
                        @foreach($slots as $slot)
                            <button type="button"
                                class="slot {{ in_array($slot, $taken) ? 'taken' : '' }} {{ $currentSlot === $slot ? 'sel' : '' }}"
                                onclick="{{ in_array($slot, $taken) ? 'void(0)' : "pickSlot(this,'{$slot}')" }}">
                                {{ $slot }}
                            </button>
                        @endforeach
                    </div>
                    <input type="hidden" name="time" id="timeInput" value="{{ $currentSlot }}" required>
                    <p style="font-size:10px;color:var(--muted);margin-top:4px;font-weight:400;">Times are 24h. Selected time slot: <strong id="selectedSlotText">{{ $currentSlot }}</strong></p>
                </div>

                <div class="field-group">
                    <label class="form-label">Reason for Visit</label>
                    <textarea name="reason" class="form-control" rows="3" placeholder="Brief description of symptoms or reason. This helps the doctor prepare.">{{ old('reason') }}</textarea>
                </div>

        </div>
        <div class="book-foot">
            <button type="reset"  class="btn btn-outline" style="font-size:12px;padding:.38rem .8rem;">Clear</button>
            <button type="submit" class="btn btn-green"   style="font-size:12px;padding:.38rem .8rem;">Confirm Booking</button>
        </div>
        </form>
    </div>

    {{-- Appointment List --}}
    <div>
        <div class="list-head">
            <h2>Appointment Schedule</h2>
            <div class="filter-row">
                <button class="f-btn on">All</button>
                <button class="f-btn">Today</button>
                <button class="f-btn">This Week</button>
                <button class="f-btn">Pending</button>
            </div>
        </div>

        <div class="appt-table">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Patient</th>
                        <th>Clinic / Doctor</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $badge = ['Confirmed'=>'badge-green','Pending'=>'badge-yellow','Cancelled'=>'badge-red','confirmed'=>'badge-green','pending'=>'badge-yellow','cancelled'=>'badge-red','completed'=>'badge-blue'];
                    @endphp
                    @if(isset($recentAppointments) && $recentAppointments->count() > 0)
                        @foreach($recentAppointments as $a)
                            <tr>
                                <td style="font-size:11px;color:var(--muted);">{{ $a->appointment_number }}</td>
                                <td>
                                    <span class="patient-name">{{ $a->patient ? $a->patient->full_name : 'Patient' }}</span>
                                    <span class="patient-id">{{ $a->patient ? $a->patient->university_id : 'N/A' }}</span>
                                </td>
                                <td>
                                    <span style="font-weight:700;font-size:12px;">{{ $a->department }}</span><br>
                                    <span style="font-size:11px;color:var(--muted);">{{ $a->doctor ? $a->doctor->name : 'Attending Officer' }}</span>
                                </td>
                                <td style="font-size:12px;">{{ $a->appointment_date->format('d M Y') }}</td>
                                <td style="font-size:12px;font-weight:700;">{{ $a->time_slot }}</td>
                                <td><span class="badge {{ $badge[$a->status] ?? 'badge-green' }}">{{ ucfirst($a->status) }}</span></td>
                            </tr>
                        @endforeach
                    @else
                        @php
                            $list = [
                                ['id'=>'APT-2026-0041','name'=>'Rahman Al-Hasan',  'uid'=>'2022-CSE-011','dept'=>'General Medicine',   'doctor'=>'Dr. Anisur Rahman', 'date'=>'Today','time'=>'09:00', 'status'=>'Confirmed'],
                                ['id'=>'APT-2026-0042','name'=>'Nadia Akter',       'uid'=>'2023-BBA-044','dept'=>'General Medicine',   'doctor'=>'Dr. Anisur Rahman',  'date'=>'Today','time'=>'09:30','status'=>'Confirmed'],
                                ['id'=>'APT-2026-0043','name'=>'Tariq Hossain',     'uid'=>'2021-CSE-078','dept'=>'Dental',             'doctor'=>'Dr. Abdur Karim',   'date'=>'Today','time'=>'10:00','status'=>'Confirmed'],
                                ['id'=>'APT-2026-0044','name'=>'Sumaiya Begum',     'uid'=>'FAC-ENG-009', 'dept'=>'Gynaecology',        'doctor'=>'Dr. Nusrat Sultana','date'=>'Tomorrow','time'=>'11:00','status'=>'Confirmed'],
                            ];
                        @endphp
                        @foreach($list as $i => $a)
                        <tr>
                            <td style="font-size:11px;color:var(--muted);">{{ $a['id'] }}</td>
                            <td>
                                <span class="patient-name">{{ $a['name'] }}</span>
                                <span class="patient-id">{{ $a['uid'] }}</span>
                            </td>
                            <td>
                                <span style="font-weight:700;font-size:12px;">{{ $a['dept'] }}</span><br>
                                <span style="font-size:11px;color:var(--muted);">{{ $a['doctor'] }}</span>
                            </td>
                            <td style="font-size:12px;">{{ $a['date'] }}</td>
                            <td style="font-size:12px;font-weight:700;">{{ $a['time'] }}</td>
                            <td><span class="badge {{ $badge[$a['status']] }}">{{ $a['status'] }}</span></td>
                        </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>

        <p style="font-size:11px;color:var(--muted);margin-top:.6rem;font-weight:400;">
            Showing {{ (isset($recentAppointments) && $recentAppointments->count() > 0) ? $recentAppointments->count() : (isset($list) ? count($list) : 0) }} appointments. To cancel or reschedule, please contact the Medical Centre counter or call 0188888888.
        </p>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function pickSlot(el, val) {
        document.querySelectorAll('.slot:not(.taken)').forEach(s => s.classList.remove('sel'));
        el.classList.add('sel');
        document.getElementById('timeInput').value = val;
        var txt = document.getElementById('selectedSlotText');
        if (txt) txt.textContent = val;
    }
</script>
@endpush
