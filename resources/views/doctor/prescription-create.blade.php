@extends('layouts.app')
@section('title', 'Generate Digital Prescription')

@push('styles')
<style>
    .rx-builder-grid {
        display: grid;
        grid-template-columns: 1fr 300px;
        gap: 1.5rem;
        align-items: start;
    }
    .med-row {
        background: #f9fbfa;
        border: 1px solid var(--border);
        border-radius: 4px;
        padding: .85rem;
        margin-bottom: .8rem;
    }
    .med-row-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: .6rem;
    }
    .vitals-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: .8rem;
        margin-bottom: .9rem;
    }
    .dosage-chips {
        display: flex;
        gap: 4px;
        margin-top: 3px;
        flex-wrap: wrap;
    }
    .chip {
        background: #e8f5ee;
        border: 1px solid #b0c4b8;
        border-radius: 3px;
        padding: 1px 6px;
        font-size: 10px;
        font-weight: 700;
        cursor: pointer;
        color: var(--green-dk);
    }
    .chip:hover { background: var(--green); color: #fff; }
    @media (max-width: 900px) {
        .rx-builder-grid { grid-template-columns: 1fr; }
        .vitals-grid { grid-template-columns: 1fr 1fr; }
    }
</style>
@endpush

@section('content')
<div class="page-title">Digital Prescription Generator</div>
<div class="page-subtitle">Prescribe medicines directly from Central Pharmacy stock, specify dosages, duration, and clinical advice.</div>

@if($errors->any())
    <div class="alert alert-error">
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<form action="{{ route('doctor.prescriptions.store') }}" method="POST" id="prescriptionForm">
    @csrf
    @if($queueId)
        <input type="hidden" name="queue_id" value="{{ $queueId }}">
    @endif

    <div class="rx-builder-grid">
        {{-- Main Builder --}}
        <div>
            {{-- Patient Selection & Clinical Vitals --}}
            <div class="card" style="margin-bottom:1.4rem;">
                <div class="card-header">1. Patient &amp; Clinical Examination</div>
                <div class="card-body">
                    <div style="margin-bottom:1rem;">
                        <label class="form-label">Consulting Patient <span class="req">*</span></label>
                        <select name="patient_id" id="patientSelect" class="form-control" required onchange="updatePatientInfo(this)">
                            <option value="">&mdash; Select Patient &mdash;</option>
                            @foreach($patients as $p)
                                <option value="{{ $p->id }}"
                                    data-allergies="{{ $p->allergies }}"
                                    data-id="{{ $p->university_id }}"
                                    data-blood="{{ $p->blood_group }}"
                                    {{ (old('patient_id', $selectedPatient?->id) == $p->id) ? 'selected' : '' }}>
                                    {{ $p->full_name }} ({{ $p->university_id }}) - Blood: {{ $p->blood_group ?? 'N/A' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Vitals --}}
                    <div class="vitals-grid">
                        <div>
                            <label class="form-label">BP (mmHg)</label>
                            <input type="text" name="bp" class="form-control" placeholder="120/80" value="{{ old('bp', '120/80') }}">
                        </div>
                        <div>
                            <label class="form-label">Pulse (bpm)</label>
                            <input type="text" name="pulse" class="form-control" placeholder="72" value="{{ old('pulse', '74') }}">
                        </div>
                        <div>
                            <label class="form-label">Temp (&deg;F)</label>
                            <input type="text" name="temperature" class="form-control" placeholder="98.6" value="{{ old('temperature', '98.6') }}">
                        </div>
                        <div>
                            <label class="form-label">Weight (kg)</label>
                            <input type="text" name="weight" class="form-control" placeholder="65" value="{{ old('weight', '68') }}">
                        </div>
                    </div>

                    <div>
                        <label class="form-label">Clinical Diagnosis <span class="req">*</span></label>
                        <textarea name="diagnosis" class="form-control" rows="2" placeholder="e.g. Acute Pharyngitis, Seasonal Rhinitis, Tension Headache..." required>{{ old('diagnosis') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Medicines from Stock --}}
            <div class="card" style="margin-bottom:1.4rem;">
                <div class="card-header">
                    <span>2. Prescribed Medicines (From Pharmacy Stock)</span>
                    <button type="button" class="btn btn-outline btn-sm" onclick="addMedicineRow()" style="color:#fff;border-color:#fff;">+ Add Another Medicine</button>
                </div>
                <div class="card-body">
                    <div id="medicinesContainer">
                        {{-- Row 1 --}}
                        <div class="med-row" id="row_0">
                            <div class="med-row-header">
                                <strong style="color:var(--green);font-size:12px;">MEDICINE #1</strong>
                                <button type="button" onclick="removeRow('row_0')" class="btn btn-outline btn-sm" style="color:#c0392b;border-color:#d99;padding:1px 6px;font-size:10px;">Remove</button>
                            </div>
                            <div style="display:grid;grid-template-columns:2fr 1fr;gap:.6rem;margin-bottom:.5rem;">
                                <div>
                                    <label class="form-label">Select Medicine from Stock <span class="req">*</span></label>
                                    <select name="medicines[0][medicine_id]" class="form-control med-select" required onchange="onMedSelect(this, 0)">
                                        <option value="">&mdash; Choose from Available Stock &mdash;</option>
                                        @foreach($medicines as $med)
                                            <option value="{{ $med->id }}" data-name="{{ $med->name }}" data-instructions="{{ $med->instructions_default }}" data-stock="{{ $med->stock_quantity }}">
                                                {{ $med->name }} ({{ $med->strength }}) &mdash; Stock: {{ $med->stock_quantity }} {{ $med->unit }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="medicines[0][medicine_name]" id="med_name_0">
                                </div>
                                <div>
                                    <label class="form-label">Total Qty <span class="req">*</span></label>
                                    <input type="number" name="medicines[0][quantity]" class="form-control" min="1" value="10" required>
                                </div>
                            </div>

                            <div style="display:grid;grid-template-columns:1fr 1fr 1.5fr;gap:.6rem;">
                                <div>
                                    <label class="form-label">Dosage (Pattern) <span class="req">*</span></label>
                                    <input type="text" name="medicines[0][dosage]" id="dosage_0" class="form-control" placeholder="e.g. 1+0+1" value="1+0+1" required>
                                    <div class="dosage-chips">
                                        <span class="chip" onclick="setDosage(0, '1+0+1')">1+0+1</span>
                                        <span class="chip" onclick="setDosage(0, '1+1+1')">1+1+1</span>
                                        <span class="chip" onclick="setDosage(0, '1+0+0')">1+0+0</span>
                                        <span class="chip" onclick="setDosage(0, '0+0+1')">0+0+1</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="form-label">Duration <span class="req">*</span></label>
                                    <input type="text" name="medicines[0][duration]" id="dur_0" class="form-control" placeholder="e.g. 5 days" value="5 days" required>
                                    <div class="dosage-chips">
                                        <span class="chip" onclick="setDuration(0, '3 days')">3d</span>
                                        <span class="chip" onclick="setDuration(0, '5 days')">5d</span>
                                        <span class="chip" onclick="setDuration(0, '7 days')">7d</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="form-label">Instructions / Advice</label>
                                    <input type="text" name="medicines[0][instructions]" id="inst_0" class="form-control" placeholder="e.g. After meal, drink warm water" value="After meal">
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-outline btn-sm" onclick="addMedicineRow()" style="margin-top:.4rem;">+ Add Another Medicine Row</button>
                </div>
            </div>

            {{-- Clinical Advice --}}
            <div class="card" style="margin-bottom:1.4rem;">
                <div class="card-header">3. Clinical Advice &amp; Follow-up Instructions</div>
                <div class="card-body">
                    <textarea name="clinical_advice" class="form-control" rows="3" placeholder="Dietary restrictions, rest instructions, follow-up date, or alarm symptoms...">Drink plenty of warm fluids. Take adequate bed rest. Avoid cold foods/drinks. Return to medical centre if symptoms worsen or fever persists past 3 days.</textarea>
                </div>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:.8rem;">
                <a href="{{ route('doctor.queue') }}" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-green" style="font-size:14px;padding:.6rem 1.6rem;">Issue &amp; Generate Prescription</button>
            </div>
        </div>

        {{-- Right: Patient Alert Box & Stock Helper --}}
        <div>
            <div class="card" style="margin-bottom:1.2rem;border-left:4px solid #c0392b;">
                <div class="card-header" style="background:#c0392b;">Patient Allergy Alert</div>
                <div class="card-body" style="font-size:12px;line-height:1.6;">
                    <div id="patientAllergyAlert">
                        @if($selectedPatient)
                            <strong>Patient:</strong> {{ $selectedPatient->full_name }}<br>
                            <strong>Allergies:</strong> <span style="color:#c0392b;font-weight:700;">{{ $selectedPatient->allergies ?: 'None reported' }}</span><br>
                            <strong>Blood Group:</strong> {{ $selectedPatient->blood_group ?? 'Not Set' }}
                        @else
                            <span style="color:var(--muted);">Select a patient to inspect their drug allergies.</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Dosage Legend</div>
                <div class="card-body" style="font-size:12px;color:var(--muted);line-height:1.6;">
                    <div><code>1+0+1</code> : Morning + Night (Twice daily)</div>
                    <div><code>1+1+1</code> : Morning + Noon + Night (Thrice daily)</div>
                    <div><code>1+0+0</code> : Morning only (Before/after breakfast)</div>
                    <div><code>0+0+1</code> : Night only (At bedtime)</div>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- Hidden Template for Dynamic Rows --}}
<template id="medicineRowTemplate">
    <div class="med-row" id="row_INDEX">
        <div class="med-row-header">
            <strong style="color:var(--green);font-size:12px;">MEDICINE #INDEX_PLUS</strong>
            <button type="button" onclick="removeRow('row_INDEX')" class="btn btn-outline btn-sm" style="color:#c0392b;border-color:#d99;padding:1px 6px;font-size:10px;">Remove</button>
        </div>
        <div style="display:grid;grid-template-columns:2fr 1fr;gap:.6rem;margin-bottom:.5rem;">
            <div>
                <label class="form-label">Select Medicine from Stock <span class="req">*</span></label>
                <select name="medicines[INDEX][medicine_id]" class="form-control med-select" required onchange="onMedSelect(this, INDEX)">
                    <option value="">&mdash; Choose from Available Stock &mdash;</option>
                    @foreach($medicines as $med)
                        <option value="{{ $med->id }}" data-name="{{ $med->name }}" data-instructions="{{ $med->instructions_default }}" data-stock="{{ $med->stock_quantity }}">
                            {{ $med->name }} ({{ $med->strength }}) &mdash; Stock: {{ $med->stock_quantity }} {{ $med->unit }}
                        </option>
                    @endforeach
                </select>
                <input type="hidden" name="medicines[INDEX][medicine_name]" id="med_name_INDEX">
            </div>
            <div>
                <label class="form-label">Total Qty <span class="req">*</span></label>
                <input type="number" name="medicines[INDEX][quantity]" class="form-control" min="1" value="10" required>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr 1.5fr;gap:.6rem;">
            <div>
                <label class="form-label">Dosage (Pattern) <span class="req">*</span></label>
                <input type="text" name="medicines[INDEX][dosage]" id="dosage_INDEX" class="form-control" placeholder="e.g. 1+0+1" value="1+0+1" required>
                <div class="dosage-chips">
                    <span class="chip" onclick="setDosage(INDEX, '1+0+1')">1+0+1</span>
                    <span class="chip" onclick="setDosage(INDEX, '1+1+1')">1+1+1</span>
                    <span class="chip" onclick="setDosage(INDEX, '1+0+0')">1+0+0</span>
                    <span class="chip" onclick="setDosage(INDEX, '0+0+1')">0+0+1</span>
                </div>
            </div>
            <div>
                <label class="form-label">Duration <span class="req">*</span></label>
                <input type="text" name="medicines[INDEX][duration]" id="dur_INDEX" class="form-control" placeholder="e.g. 5 days" value="5 days" required>
                <div class="dosage-chips">
                    <span class="chip" onclick="setDuration(INDEX, '3 days')">3d</span>
                    <span class="chip" onclick="setDuration(INDEX, '5 days')">5d</span>
                    <span class="chip" onclick="setDuration(INDEX, '7 days')">7d</span>
                </div>
            </div>
            <div>
                <label class="form-label">Instructions / Advice</label>
                <input type="text" name="medicines[INDEX][instructions]" id="inst_INDEX" class="form-control" placeholder="e.g. After meal" value="After meal">
            </div>
        </div>
    </div>
</template>
@endsection

@push('scripts')
<script>
    let rowIndex = 1;

    function onMedSelect(selectEl, idx) {
        const selected = selectEl.options[selectEl.selectedIndex];
        const medName = selected.getAttribute('data-name');
        const defaultInst = selected.getAttribute('data-instructions');

        const nameInput = document.getElementById('med_name_' + idx);
        if (nameInput) nameInput.value = medName || '';

        const instInput = document.getElementById('inst_' + idx);
        if (instInput && defaultInst) instInput.value = defaultInst;
    }

    function setDosage(idx, val) {
        const el = document.getElementById('dosage_' + idx);
        if (el) el.value = val;
    }

    function setDuration(idx, val) {
        const el = document.getElementById('dur_' + idx);
        if (el) el.value = val;
    }

    function addMedicineRow() {
        const container = document.getElementById('medicinesContainer');
        const template = document.getElementById('medicineRowTemplate').innerHTML;
        const newHtml = template
            .replace(/INDEX_PLUS/g, rowIndex + 1)
            .replace(/INDEX/g, rowIndex);

        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = newHtml;
        container.appendChild(tempDiv.firstElementChild);
        rowIndex++;
    }

    function removeRow(rowId) {
        const rows = document.querySelectorAll('.med-row');
        if (rows.length <= 1) {
            alert('At least one medicine is required on a digital prescription.');
            return;
        }
        const row = document.getElementById(rowId);
        if (row) row.remove();
    }

    function updatePatientInfo(selectEl) {
        const opt = selectEl.options[selectEl.selectedIndex];
        const alertBox = document.getElementById('patientAllergyAlert');
        if (!opt.value) {
            alertBox.innerHTML = '<span style="color:var(--muted);">Select a patient to inspect their drug allergies.</span>';
            return;
        }

        const allergies = opt.getAttribute('data-allergies') || 'None reported';
        const blood = opt.getAttribute('data-blood') || 'Not Set';
        const id = opt.getAttribute('data-id');

        alertBox.innerHTML = `
            <strong>Patient ID:</strong> ${id}<br>
            <strong>Allergies:</strong> <span style="color:#c0392b;font-weight:700;">${allergies}</span><br>
            <strong>Blood Group:</strong> ${blood}
        `;
    }

    // Auto-sync first select on load if pre-selected
    document.addEventListener('DOMContentLoaded', function() {
        const sel = document.querySelector('.med-select');
        if (sel && sel.selectedIndex > 0) {
            onMedSelect(sel, 0);
        }
    });
</script>
@endpush
