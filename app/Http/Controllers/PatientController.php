<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\MedicalQueue;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PatientController extends Controller
{
    private function getPatient(): Patient
    {
        $user = Auth::user();
        $patient = $user->patient ?? Patient::where('email', $user->email)->orWhere('university_id', $user->university_id)->first();

        if (! $patient) {
            // Auto-provision a patient record for the user if not exists
            $nameParts = explode(' ', $user->name, 2);
            $patient = Patient::create([
                'user_id' => $user->id,
                'first_name' => $nameParts[0],
                'last_name' => $nameParts[1] ?? 'User',
                'university_id' => $user->university_id ?? 'STU-'.str_pad((string) $user->id, 4, '0', STR_PAD_LEFT),
                'dob' => '2002-01-01',
                'gender' => 'Male',
                'blood_group' => 'B+',
                'nationality' => 'Bangladeshi',
                'role_category' => 'student',
                'department' => $user->department ?? 'General',
                'email' => $user->email,
                'phone' => $user->phone ?? '0188888888',
                'is_verified' => true,
            ]);
        }

        return $patient;
    }

    public function dashboard(): View
    {
        $patient = $this->getPatient();
        $today = Carbon::today();

        $activeQueue = MedicalQueue::where('patient_id', $patient->id)
            ->whereDate('queue_date', $today)
            ->whereIn('status', ['waiting', 'in_consultation'])
            ->first();

        $upcomingAppointments = Appointment::with('doctor')
            ->where('patient_id', $patient->id)
            ->whereDate('appointment_date', '>=', $today)
            ->orderBy('appointment_date')
            ->take(5)
            ->get();

        $prescriptions = Prescription::with(['doctor', 'items'])
            ->where('patient_id', $patient->id)
            ->latest()
            ->take(5)
            ->get();

        // Calculate queue position if active
        $patientsAhead = 0;
        if ($activeQueue && $activeQueue->status === 'waiting') {
            $patientsAhead = MedicalQueue::whereDate('queue_date', $today)
                ->where('status', 'waiting')
                ->where('id', '<', $activeQueue->id)
                ->count();
        }

        return view('patient.dashboard', compact('patient', 'activeQueue', 'patientsAhead', 'upcomingAppointments', 'prescriptions'));
    }

    public function profile(): View
    {
        $patient = $this->getPatient();

        return view('patient.profile', compact('patient'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $patient = $this->getPatient();

        $validated = $request->validate([
            'phone' => 'required|string|max:20',
            'emergency_name' => 'nullable|string|max:100',
            'emergency_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'allergies' => 'nullable|string|max:500',
            'medications' => 'nullable|string|max:500',
        ]);

        $patient->update($validated);

        return back()->with('success', 'Profile and emergency contact information updated successfully.');
    }

    public function queue(): View
    {
        $patient = $this->getPatient();
        $today = Carbon::today();

        $todayQueues = MedicalQueue::with(['patient', 'doctor'])
            ->whereDate('queue_date', $today)
            ->orderByRaw("CASE WHEN status = 'in_consultation' THEN 1 WHEN status = 'waiting' THEN 2 ELSE 3 END")
            ->orderBy('id')
            ->get();

        $nowCalling = $todayQueues->where('status', 'in_consultation')->first();
        $waitingCount = $todayQueues->where('status', 'waiting')->count();
        $completedCount = $todayQueues->where('status', 'completed')->count();

        $myToken = $todayQueues->where('patient_id', $patient->id)->whereIn('status', ['waiting', 'in_consultation'])->first();

        $patientsAhead = 0;
        if ($myToken && $myToken->status === 'waiting') {
            $patientsAhead = $todayQueues->where('status', 'waiting')->where('id', '<', $myToken->id)->count();
        }

        return view('patient.queue', compact('patient', 'todayQueues', 'nowCalling', 'waitingCount', 'completedCount', 'myToken', 'patientsAhead'));
    }

    public function joinQueue(Request $request): RedirectResponse
    {
        $patient = $this->getPatient();
        $today = Carbon::today();

        // Check if already in queue today
        $existing = MedicalQueue::where('patient_id', $patient->id)
            ->whereDate('queue_date', $today)
            ->whereIn('status', ['waiting', 'in_consultation'])
            ->first();

        if ($existing) {
            return back()->with('info', "You already have an active queue token for today: {$existing->token_number}");
        }

        $department = $request->input('department', 'General Medicine');

        // Generate next token number e.g. T-105
        $lastToken = MedicalQueue::whereDate('queue_date', $today)->max('token_number');
        $nextNum = 101;
        if ($lastToken && preg_match('/T-(\d+)/', $lastToken, $matches)) {
            $nextNum = ((int) $matches[1]) + 1;
        }
        $tokenNumber = "T-{$nextNum}";

        // Assign to first available doctor for that department if any
        $doctor = User::where('role', 'doctor')
            ->where('department', 'like', "%{$department}%")
            ->first() ?? User::where('role', 'doctor')->first();

        $queue = MedicalQueue::create([
            'token_number' => $tokenNumber,
            'queue_date' => $today,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor?->id,
            'department' => $department,
            'status' => 'waiting',
        ]);

        return redirect()->route('patient.queue')->with('success', "Queue token generated: {$queue->token_number}. Please proceed to the waiting area.");
    }

    public function appointments(): View
    {
        $patient = $this->getPatient();
        $doctors = User::where('role', 'doctor')->where('is_active', true)->get();

        $appointments = Appointment::with('doctor')
            ->where('patient_id', $patient->id)
            ->latest('appointment_date')
            ->get();

        return view('patient.appointments', compact('patient', 'doctors', 'appointments'));
    }

    public function storeAppointment(Request $request): RedirectResponse
    {
        $patient = $this->getPatient();

        $validated = $request->validate([
            'department' => 'required|string',
            'doctor_id' => 'nullable|exists:users,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'time_slot' => 'required|string',
            'reason' => 'nullable|string|max:500',
        ]);

        $aptCount = Appointment::count() + 1;
        $appointmentNumber = 'APT-'.date('Y').'-'.str_pad((string) $aptCount, 4, '0', STR_PAD_LEFT);

        $appointment = Appointment::create([
            'appointment_number' => $appointmentNumber,
            'patient_id' => $patient->id,
            'doctor_id' => $validated['doctor_id'] ?? null,
            'department' => $validated['department'],
            'appointment_date' => $validated['appointment_date'],
            'time_slot' => $validated['time_slot'],
            'reason' => $validated['reason'] ?? null,
            'status' => 'confirmed',
        ]);

        // If booked for today, also issue a live queue token
        if (Carbon::parse($validated['appointment_date'])->isToday()) {
            $lastToken = MedicalQueue::whereDate('queue_date', Carbon::today())->max('token_number');
            $nextNum = 101;
            if ($lastToken && preg_match('/T-(\d+)/', $lastToken, $matches)) {
                $nextNum = ((int) $matches[1]) + 1;
            }

            MedicalQueue::create([
                'token_number' => "T-{$nextNum}",
                'queue_date' => Carbon::today(),
                'patient_id' => $patient->id,
                'doctor_id' => $appointment->doctor_id,
                'appointment_id' => $appointment->id,
                'department' => $appointment->department,
                'status' => 'waiting',
            ]);
        }

        return redirect()->route('patient.appointments')->with('success', "Appointment booked successfully! Reference: {$appointment->appointment_number}");
    }

    public function prescriptions(): View
    {
        $patient = $this->getPatient();

        $prescriptions = Prescription::with(['doctor', 'items.medicine'])
            ->where('patient_id', $patient->id)
            ->latest()
            ->get();

        return view('patient.prescriptions', compact('patient', 'prescriptions'));
    }

    public function showPrescription(Prescription $prescription): View
    {
        $patient = $this->getPatient();

        // Ensure patient is authorized or user is staff
        if (Auth::user()->isPatient() && $prescription->patient_id !== $patient->id) {
            abort(403, 'Unauthorized access to prescription.');
        }

        $prescription->load(['doctor', 'patient', 'items.medicine']);

        return view('prescriptions.show', compact('prescription'));
    }

    public function downloadPrescriptionPdf(Prescription $prescription): Response
    {
        $patient = $this->getPatient();

        if (Auth::user()->isPatient() && $prescription->patient_id !== $patient->id) {
            abort(403, 'Unauthorized access to prescription.');
        }

        $prescription->load(['doctor', 'patient', 'items.medicine']);

        $pdf = Pdf::loadView('prescriptions.pdf', compact('prescription'))
            ->setPaper('a4', 'portrait');

        $fileName = "Prescription-{$prescription->prescription_number}.pdf";

        return $pdf->download($fileName);
    }
}
