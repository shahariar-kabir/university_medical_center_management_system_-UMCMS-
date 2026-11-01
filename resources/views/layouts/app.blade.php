<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'UMC') | University Medical Centre</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --green:     #1a6b3a;
            --green-dk:  #134d2a;
            --green-lt:  #2d9156;
            --green-pale:#e8f5ee;
            --gold:      #c8922a;
            --gold-lt:   #fdf6e7;
            --bg:        #f4f8f5;
            --card:      #ffffff;
            --text:      #1c2b22;
            --muted:     #56735f;
            --border:    #c3dbc9;
            --radius:    6px;
            --shadow:    0 1px 4px rgba(0,0,0,.10);
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-weight: 400;
            font-size: 14px;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            line-height: 1.5;
        }

        /* ── TOP DEMO SWITCHER / TICKER ── */
        .top-bar {
            background: #0f3d21;
            color: #d1e7dd;
            font-size: 11px;
            padding: 5px 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: .5rem;
            border-bottom: 1px solid rgba(255,255,255,.1);
        }
        .demo-roles {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }
        .demo-roles strong { color: #fff; margin-right: 4px; }
        .demo-pill {
            display: inline-block;
            background: rgba(255,255,255,.12);
            color: #fff;
            padding: 2px 8px;
            border-radius: 3px;
            text-decoration: none;
            font-size: 11px;
            font-weight: 700;
            transition: all .15s;
        }
        .demo-pill:hover { background: var(--gold); color: #fff; }

        /* ── HEADER ── */
        .site-header {
            background: var(--green);
            padding: .9rem 2rem;
            display: flex;
            align-items: center;
            gap: 1.2rem;
            border-bottom: 3px solid var(--gold);
        }
        .site-header .logo-text { color: #fff; }
        .site-header .logo-text strong {
            display: block;
            font-size: 1.15rem;
            letter-spacing: .01em;
            font-weight: 700;
        }
        .site-header .logo-text span {
            font-size: 11px;
            opacity: .82;
            font-weight: 400;
            letter-spacing: .05em;
            text-transform: uppercase;
        }
        .header-right {
            margin-left: auto;
            color: rgba(255,255,255,.75);
            font-size: 12px;
            text-align: right;
            line-height: 1.7;
        }
        .header-right strong { color: #fff; font-size: 13px; display: block; }

        /* ── NAV BAR ── */
        nav {
            background: var(--green-dk);
            display: flex;
            align-items: center;
            padding: 0 2rem;
            position: sticky; top: 0; z-index: 100;
            box-shadow: 0 2px 6px rgba(0,0,0,.25);
            flex-wrap: wrap;
        }
        .nav-links {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
        }
        nav a {
            color: rgba(255,255,255,.82);
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
            padding: .75rem 1rem;
            display: block;
            border-bottom: 3px solid transparent;
            transition: color .15s, border-color .15s, background .15s;
            letter-spacing: .02em;
        }
        nav a:hover { color: #fff; background: rgba(255,255,255,.07); }
        nav a.active {
            color: #fff;
            border-bottom-color: var(--gold);
            background: rgba(255,255,255,.06);
        }

        .nav-auth {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: .8rem;
            padding: .4rem 0;
        }
        .user-tag {
            color: #fff;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* ── BREADCRUMB ── */
        .breadcrumb {
            background: #fff;
            border-bottom: 1px solid var(--border);
            padding: .45rem 2rem;
            font-size: 12px;
            color: var(--muted);
            display: flex;
            align-items: center;
            gap: .4rem;
        }
        .breadcrumb a { color: var(--green); text-decoration: none; font-weight: 700; }
        .breadcrumb a:hover { text-decoration: underline; }

        /* ── MAIN ── */
        main {
            flex: 1;
            padding: 1.8rem 2rem;
            max-width: 1220px;
            margin: 0 auto;
            width: 100%;
        }

        /* ── FOOTER ── */
        footer {
            background: var(--green-dk);
            color: rgba(255,255,255,.65);
            font-size: 12px;
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: auto;
            border-top: 3px solid var(--gold);
        }
        footer .footer-links { display: flex; gap: 1.2rem; }
        footer .footer-links a { color: rgba(255,255,255,.65); text-decoration: none; }
        footer .footer-links a:hover { color: #fff; text-decoration: underline; }

        /* ── SHARED COMPONENTS ── */
        .page-title {
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--green);
            margin-bottom: .25rem;
        }
        .page-subtitle {
            font-size: 13px;
            color: var(--muted);
            margin-bottom: 1.4rem;
            border-bottom: 1px solid var(--border);
            padding-bottom: .85rem;
        }

        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }
        .card-header {
            background: var(--green);
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            padding: .6rem 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .card-body { padding: 1rem; }

        .btn {
            display: inline-block;
            padding: .45rem 1.1rem;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 700;
            font-family: Arial, Helvetica, sans-serif;
            cursor: pointer; border: none;
            text-decoration: none;
            transition: background .15s, opacity .15s;
            letter-spacing: .02em;
            text-align: center;
        }
        .btn-green   { background: var(--green); color: #fff; }
        .btn-green:hover { background: var(--green-dk); }
        .btn-outline {
            background: transparent;
            color: var(--green);
            border: 1.5px solid var(--green);
        }
        .btn-outline:hover { background: var(--green); color: #fff; }
        .btn-gold { background: var(--gold); color: #fff; }
        .btn-gold:hover { background: #ab791c; }
        .btn-sm { padding: .25rem .65rem; font-size: 11px; }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        .badge-green  { background: #d4edda; color: #155724; }
        .badge-yellow { background: #fff3cd; color: #856404; }
        .badge-red    { background: #f8d7da; color: #721c24; }
        .badge-blue   { background: #cce5ff; color: #004085; }
        .badge-gray   { background: #e2e3e5; color: #383d41; }
        .badge-gold   { background: #fbf1d8; color: #8c6114; }

        .alert {
            padding: .75rem 1rem;
            border-radius: var(--radius);
            font-size: 13px;
            margin-bottom: 1.1rem;
            border-left: 4px solid;
        }
        .alert-success { background: #d4edda; color: #155724; border-color: #28a745; }
        .alert-error   { background: #f8d7da; color: #721c24; border-color: #dc3545; }
        .alert-info    { background: #d1ecf1; color: #0c5460; border-color: #17a2b8; }

        .form-label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 3px;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        .form-label .req { color: #c0392b; margin-left: 2px; }
        .form-control {
            width: 100%;
            border: 1px solid #b0c4b8;
            border-radius: 3px;
            padding: .42rem .6rem;
            font-size: 13px;
            font-family: Arial, Helvetica, sans-serif;
            color: var(--text);
            background: #fff;
            transition: border-color .15s, box-shadow .15s;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--green);
            box-shadow: 0 0 0 2px rgba(26,107,58,.15);
        }
        select.form-control { cursor: pointer; }
        textarea.form-control { resize: vertical; min-height: 70px; }

        /* stats grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.4rem;
        }
        .stat-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1rem;
            box-shadow: var(--shadow);
            border-top: 3px solid var(--green);
        }
        .stat-card.gold  { border-top-color: var(--gold); }
        .stat-card.red   { border-top-color: #c0392b; }
        .stat-card.blue  { border-top-color: #2980b9; }
        .stat-val { font-size: 1.8rem; font-weight: 700; color: var(--green-dk); line-height: 1.1; }
        .stat-card.gold .stat-val { color: var(--gold); }
        .stat-card.red  .stat-val { color: #c0392b; }
        .stat-card.blue .stat-val { color: #2980b9; }
        .stat-label { font-size: 11px; text-transform: uppercase; color: var(--muted); font-weight: 700; letter-spacing: .05em; margin-top: 4px; }
        .stat-sub { font-size: 11px; color: var(--muted); margin-top: 2px; }

        /* standard data table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            background: #fff;
        }
        .data-table th {
            background: var(--green-dk);
            color: #fff;
            padding: .5rem .75rem;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .05em;
            font-weight: 700;
        }
        .data-table td {
            padding: .55rem .75rem;
            border-bottom: 1px solid #ecf2ee;
            vertical-align: middle;
        }
        .data-table tr:last-child td { border-bottom: none; }
        .data-table tr:nth-child(even) td { background: #f9fbfa; }
        .data-table tr:hover td { background: #f0f8f3; }
    </style>
    @stack('styles')
</head>
<body>

{{-- Top Demo Switcher & Quick Access --}}
<div class="top-bar">
    <div class="demo-roles">
        <strong>Demo 1-Click Login:</strong>
        <a href="{{ route('login.demo', 'patient') }}" class="demo-pill" title="Login as Student Patient">Patient</a>
        <a href="{{ route('login.demo', 'doctor') }}" class="demo-pill" title="Login as Dr. Anisur Rahman">Doctor</a>
        <a href="{{ route('login.demo', 'pharmacist') }}" class="demo-pill" title="Login as Head Pharmacist">Pharmacist</a>
        <a href="{{ route('login.demo', 'admin') }}" class="demo-pill" title="Login as Administrator">Admin</a>
    </div>
    <div>
        <span>Hotline: <strong>0188888888</strong> &nbsp;|&nbsp; Central Medical Centre Building</span>
    </div>
</div>

{{-- Site header --}}
<div class="site-header">
    <a href="{{ url('/') }}" style="display:flex;align-items:center;gap:1.1rem;text-decoration:none;">
        <div style="line-height:1;">
            <span style="font-size:1.6rem;font-weight:700;color:#fff;letter-spacing:-.01em;">UniMed<span style="color:#c8d9c0;">Center</span></span>
        </div>
        <div style="width:1px;height:36px;background:rgba(255,255,255,.25);"></div>
        <div class="logo-text">
            <strong>University Medical Centre</strong>
            <span>Health &amp; Wellness Division &mdash; Central Campus</span>
        </div>
    </a>
    <div class="header-right">
        <strong>Emergency Hotline: 0188888888</strong>
        Mon–Fri 8:00am – 8:00pm &nbsp;|&nbsp; Sat 9:00am – 2:00pm
    </div>
</div>

{{-- Main Navigation Bar --}}
<nav>
    <div class="nav-links">
        <a href="{{ url('/') }}" class="{{ request()->is('/') ? 'active' : '' }}">Home</a>

        @auth
            @if(Auth::user()->isPatient())
                <a href="{{ route('patient.dashboard') }}" class="{{ request()->routeIs('patient.dashboard') ? 'active' : '' }}">My Dashboard</a>
                <a href="{{ route('patient.queue') }}" class="{{ request()->routeIs('patient.queue') ? 'active' : '' }}">Live Queue</a>
                <a href="{{ route('patient.appointments') }}" class="{{ request()->routeIs('patient.appointments*') ? 'active' : '' }}">Appointments</a>
                <a href="{{ route('patient.prescriptions') }}" class="{{ request()->routeIs('patient.prescriptions*') ? 'active' : '' }}">Prescriptions</a>
                <a href="{{ route('patient.profile') }}" class="{{ request()->routeIs('patient.profile') ? 'active' : '' }}">My Profile</a>
            @elseif(Auth::user()->isDoctor())
                <a href="{{ route('doctor.dashboard') }}" class="{{ request()->routeIs('doctor.dashboard') ? 'active' : '' }}">Doctor Desk</a>
                <a href="{{ route('doctor.queue') }}" class="{{ request()->routeIs('doctor.queue') ? 'active' : '' }}">Consultation Queue</a>
                <a href="{{ route('doctor.prescriptions.create') }}" class="{{ request()->routeIs('doctor.prescriptions.create') ? 'active' : '' }}">Generate Prescription</a>
            @elseif(Auth::user()->isPharmacist())
                <a href="{{ route('pharmacy.dashboard') }}" class="{{ request()->routeIs('pharmacy.dashboard') ? 'active' : '' }}">Pharmacy Desk</a>
                <a href="{{ route('pharmacy.inventory') }}" class="{{ request()->routeIs('pharmacy.inventory') ? 'active' : '' }}">Medicine Inventory</a>
                <a href="{{ route('pharmacy.dispense') }}" class="{{ request()->routeIs('pharmacy.dispense') ? 'active' : '' }}">Dispense Prescriptions</a>
            @elseif(Auth::user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Admin Analytics</a>
                <a href="{{ route('admin.users') }}" class="{{ request()->routeIs('admin.users*') ? 'active' : '' }}">User Management</a>
                <a href="{{ route('pharmacy.inventory') }}" class="{{ request()->routeIs('pharmacy.inventory') ? 'active' : '' }}">Medicine Stock</a>
                <a href="{{ route('doctor.queue') }}" class="{{ request()->routeIs('doctor.queue') ? 'active' : '' }}">Live Queues</a>
            @endif
        @else
            <a href="{{ route('patients.register') }}" class="{{ request()->routeIs('patients.register') ? 'active' : '' }}">Patient Registration</a>
            <a href="{{ route('appointments') }}" class="{{ request()->routeIs('appointments') ? 'active' : '' }}">Book Appointment</a>
        @endauth
    </div>

    <div class="nav-auth">
        @auth
            <div class="user-tag">
                <span>{{ Auth::user()->name }}</span>
                @php
                    $roleBadges = [
                        'admin' => 'badge-red',
                        'doctor' => 'badge-blue',
                        'pharmacist' => 'badge-gold',
                        'patient' => 'badge-green',
                    ];
                @endphp
                <span class="badge {{ $roleBadges[Auth::user()->role] ?? 'badge-gray' }}">{{ ucfirst(Auth::user()->role) }}</span>
            </div>
            <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-outline btn-sm" style="color:#fff;border-color:rgba(255,255,255,.4);">Logout</button>
            </form>
        @else
            <a href="{{ route('login') }}" class="btn btn-gold btn-sm">Staff &amp; Patient Login</a>
        @endauth
    </div>
</nav>

{{-- Breadcrumb --}}
<div class="breadcrumb">
    <a href="{{ url('/') }}">Home</a>
    <span>&rsaquo;</span>
    <span>@yield('title', 'Portal')</span>
</div>

<main>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif
    @if(session('info'))
        <div class="alert alert-info">{{ session('info') }}</div>
    @endif

    @yield('content')
</main>

<footer>
    <div>
        &copy; {{ date('Y') }} University Medical Centre &mdash; All rights reserved.<br>
        Medical Centre Building, Campus Road, Dhaka 1205 &nbsp;|&nbsp; Hotline: 0188888888
    </div>
    <div class="footer-links">
        <a href="{{ route('home') }}">Home</a>
        <a href="{{ route('patients.register') }}">Registration</a>
        <a href="{{ route('login') }}">Portal Login</a>
        <a href="#">Medical Emergency</a>
    </div>
</footer>

@stack('scripts')
</body>
</html>
