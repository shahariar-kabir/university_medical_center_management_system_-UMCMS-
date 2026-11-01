# Feature 1: Core Authentication, Patient Module & Queue Management

Includes Multi-Role Authentication, Role Middleware, Patient Identity & Verification, Appointment Booking, and Live Queue Tracking.

## Packaged Files:
- `routes/web.php`
- `app/Http/Middleware/RoleMiddleware.php`
- `app/Http/Controllers/AuthController.php`
- `app/Http/Controllers/PatientController.php`
- `app/Models/User.php`
- `app/Models/Patient.php`
- `app/Models/Appointment.php`
- `app/Models/MedicalQueue.php`
- `database/migrations/2026_10_28_195418_add_role_and_fields_to_users_table.php`
- `database/migrations/2026_10_28_195423_create_patients_table.php`
- `database/migrations/2026_10_28_195425_create_appointments_table.php`
- `database/migrations/2026_10_28_195426_create_medical_queues_table.php`
- `resources/views/layouts/app.blade.php`
- `resources/views/auth/login.blade.php`
- `resources/views/patients/register.blade.php`
- `resources/views/patient/dashboard.blade.php`
- `resources/views/patient/profile.blade.php`
- `resources/views/patient/queue.blade.php`
- `resources/views/patient/appointments.blade.php`
- `resources/views/appointments/index.blade.php`
