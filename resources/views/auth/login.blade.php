<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZenClock — Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --ink: #0f0f0f;
            --ink-soft: #6b6b6b;
            --ink-muted: #a8a8a8;
            --surface: #ffffff;
            --surface-2: #f7f7f5;
            --accent: #1a1a2e;
            --accent-warm: #e8e0d5;
            --line: #e8e8e8;
        }

        html,
        body {
            height: 100%;
            font-family: 'DM Sans', sans-serif;
            background: var(--surface);
        }

        .page {
            display: grid;
            grid-template-columns: 1fr 1fr;
            height: 100vh;
            overflow: hidden;
        }

        /* LEFT */
        .left {
            position: relative;
            background: var(--accent);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 3rem;
        }

        .left-bg {
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse 80% 60% at 20% 80%, rgba(232, 224, 213, 0.12) 0%, transparent 60%),
                radial-gradient(ellipse 60% 80% at 80% 20%, rgba(255, 255, 255, 0.04) 0%, transparent 50%);
        }

        .left-grid {
            position: absolute;
            inset: 0;
            background-image: linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
            background-size: 48px 48px;
        }

        .clock-deco {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 280px;
            height: 280px;
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 50%;
            animation: spinSlow 40s linear infinite;
        }

        .clock-deco::before {
            content: '';
            position: absolute;
            inset: 20px;
            border: 1px solid rgba(255, 255, 255, 0.04);
            border-radius: 50%;
        }

        .clock-deco::after {
            content: '';
            position: absolute;
            inset: 50px;
            border: 1px dashed rgba(255, 255, 255, 0.06);
            border-radius: 50%;
        }

        @keyframes spinSlow {
            to {
                transform: translate(-50%, -50%) rotate(360deg);
            }
        }

        .brand {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-icon {
            width: 36px;
            height: 36px;
            border: 1.5px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .brand-name {
            font-family: 'DM Serif Display', serif;
            color: white;
            font-size: 1.35rem;
            letter-spacing: -0.02em;
        }

        .left-content {
            position: relative;
            z-index: 2;
        }

        .left-tag {
            display: inline-block;
            font-size: 0.65rem;
            font-weight: 600;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: rgba(232, 224, 213, 0.7);
            background: rgba(232, 224, 213, 0.08);
            border: 1px solid rgba(232, 224, 213, 0.15);
            padding: 5px 12px;
            border-radius: 100px;
            margin-bottom: 1.5rem;
        }

        .left-title {
            font-family: 'DM Serif Display', serif;
            font-size: 2.8rem;
            line-height: 1.1;
            color: white;
            margin-bottom: 1.25rem;
            letter-spacing: -0.02em;
        }

        .left-title em {
            font-style: italic;
            color: var(--accent-warm);
        }

        .left-desc {
            font-size: 0.9rem;
            line-height: 1.7;
            color: rgba(255, 255, 255, 0.45);
            max-width: 320px;
        }

        .left-stats {
            position: relative;
            z-index: 2;
            display: flex;
            gap: 2rem;
        }

        .stat {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .stat-num {
            font-family: 'DM Serif Display', serif;
            font-size: 1.5rem;
            color: white;
            letter-spacing: -0.03em;
        }

        .stat-label {
            font-size: 0.7rem;
            color: rgba(255, 255, 255, 0.35);
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .stat-divider {
            width: 1px;
            background: rgba(255, 255, 255, 0.1);
            align-self: stretch;
        }

        /* RIGHT */
        .right {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 4rem 5rem;
            background: var(--surface);
            position: relative;
            overflow-y: auto;
        }

        .form-eyebrow {
            font-size: 0.65rem;
            font-weight: 600;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: var(--ink-muted);
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-eyebrow::before {
            content: '';
            display: block;
            width: 20px;
            height: 1px;
            background: var(--ink-muted);
        }

        .form-title {
            font-family: 'DM Serif Display', serif;
            font-size: 2.2rem;
            color: var(--ink);
            letter-spacing: -0.03em;
            line-height: 1.1;
            margin-bottom: 0.5rem;
        }

        .form-sub {
            font-size: 0.875rem;
            color: var(--ink-muted);
            line-height: 1.6;
            margin-bottom: 2.5rem;
        }

        /* Session error */
        .alert-error {
            background: #fff5f5;
            border: 1px solid #fecaca;
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .alert-error svg {
            flex-shrink: 0;
            margin-top: 1px;
            color: #ef4444;
        }

        .alert-error ul {
            list-style: none;
        }

        .alert-error li {
            font-size: 0.83rem;
            color: #dc2626;
            line-height: 1.5;
        }

        .field {
            margin-bottom: 1.25rem;
        }

        .field-label {
            display: block;
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--ink-soft);
            margin-bottom: 0.5rem;
        }

        .field-wrap {
            position: relative;
        }

        .field-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--ink-muted);
            pointer-events: none;
            display: flex;
        }

        .field-input {
            width: 100%;
            padding: 13px 14px 13px 42px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.9rem;
            color: var(--ink);
            background: var(--surface-2);
            border: 1.5px solid transparent;
            border-radius: 12px;
            outline: none;
            transition: all 0.2s;
        }

        .field-input::placeholder {
            color: var(--ink-muted);
            font-size: 0.875rem;
        }

        .field-input:focus {
            background: white;
            border-color: var(--ink);
            box-shadow: 0 0 0 4px rgba(15, 15, 15, 0.06);
        }

        .field-input.is-invalid {
            border-color: #ef4444 !important;
            background: #fff5f5 !important;
        }

        .invalid-feedback {
            font-size: 0.72rem;
            color: #ef4444;
            margin-top: 5px;
            display: block;
        }

        .field-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px;
            color: var(--ink-muted);
            display: flex;
            transition: color 0.15s;
        }

        .field-toggle:hover {
            color: var(--ink);
        }

        .options-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.75rem;
        }

        .checkbox-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .checkbox-wrap input[type="checkbox"] {
            display: none;
        }

        .checkbox-custom {
            width: 18px;
            height: 18px;
            border: 1.5px solid var(--line);
            border-radius: 5px;
            background: var(--surface-2);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s;
            flex-shrink: 0;
        }

        .checkbox-wrap input:checked+.checkbox-custom {
            background: var(--ink);
            border-color: var(--ink);
        }

        .checkbox-wrap input:checked+.checkbox-custom::after {
            content: '';
            display: block;
            width: 4px;
            height: 7px;
            border: 1.5px solid white;
            border-top: none;
            border-left: none;
            transform: rotate(42deg) translateY(-1px);
        }

        .checkbox-label {
            font-size: 0.83rem;
            color: var(--ink-soft);
            user-select: none;
        }

        .forgot-link {
            font-size: 0.83rem;
            color: var(--ink-soft);
            text-decoration: none;
            border-bottom: 1px solid transparent;
            transition: all 0.15s;
        }

        .forgot-link:hover {
            color: var(--ink);
            border-color: var(--ink);
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.875rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            color: white;
            background: var(--ink);
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            position: relative;
            overflow: hidden;
        }

        .btn-submit:hover {
            background: #2a2a2a;
            transform: translateY(-1px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .btn-arrow {
            display: flex;
            align-items: center;
            transition: transform 0.2s;
        }

        .btn-submit:hover .btn-arrow {
            transform: translateX(3px);
        }

        .form-footer {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--line);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .footer-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #22c55e;
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.15);
            animation: pulse 2s ease-in-out infinite;
        }

        .footer-text {
            font-size: 0.75rem;
            color: var(--ink-muted);
        }

        /* Animations */
        .left {
            animation: fadeIn 0.6s ease both;
        }

        .right {
            animation: fadeIn 0.6s ease 0.1s both;
        }

        .form-eyebrow {
            animation: slideUp 0.5s ease 0.25s both;
        }

        .form-title {
            animation: slideUp 0.5s ease 0.3s both;
        }

        .form-sub {
            animation: slideUp 0.5s ease 0.35s both;
        }

        .alert-error {
            animation: slideUp 0.3s ease both;
        }

        .field:nth-child(1) {
            animation: slideUp 0.5s ease 0.4s both;
        }

        .field:nth-child(2) {
            animation: slideUp 0.5s ease 0.45s both;
        }

        .options-row {
            animation: slideUp 0.5s ease 0.5s both;
        }

        .btn-submit {
            animation: slideUp 0.5s ease 0.55s both;
        }

        .form-footer {
            animation: slideUp 0.5s ease 0.6s both;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(14px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {

            0%,
            100% {
                box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.15);
            }

            50% {
                box-shadow: 0 0 0 5px rgba(34, 197, 94, 0.07);
            }
        }

        @keyframes spinLoad {
            to {
                transform: rotate(360deg);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .page {
                grid-template-columns: 1fr;
            }

            .left {
                display: none;
            }

            .right {
                padding: 2.5rem 2rem;
            }
        }
    </style>
</head>

<body>
    <div class="page">

        {{-- ── PANEL KIRI ── --}}
        <div class="left">
            <div class="left-bg"></div>
            <div class="left-grid"></div>
            <div class="clock-deco"></div>

            <div class="brand">
                <div class="brand-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8">
                        <circle cx="12" cy="12" r="10" />
                        <polyline points="12,6 12,12 16,14" />
                    </svg>
                </div>
                <span class="brand-name">ZenClock</span>
            </div>

            <div class="left-content">
                <div class="left-tag">Sistem Manajemen Kehadiran</div>
                <h1 class="left-title">Catat waktu,<br>ukur <em>dedikasi</em></h1>
                <p class="left-desc">Pantau kehadiran dan penilaian karyawan secara akurat dengan teknologi QR Code dan GPS real-time.</p>
            </div>

            <div class="left-stats">
                <div class="stat"><span class="stat-num">99%</span><span class="stat-label">Akurasi GPS</span></div>
                <div class="stat-divider"></div>
                <div class="stat"><span class="stat-num">10s</span><span class="stat-label">Token QR</span></div>
                <div class="stat-divider"></div>
                <div class="stat"><span class="stat-num">24/7</span><span class="stat-label">Monitoring</span></div>
            </div>
        </div>

        {{-- ── PANEL KANAN (FORM) ── --}}
        <div class="right">
            <div class="form-eyebrow">Selamat datang kembali</div>
            <h2 class="form-title">Masuk ke<br>akun Anda</h2>
            <p class="form-sub">Masukkan email dan password untuk melanjutkan.</p>

            {{-- Error dari session --}}
            @if ($errors->any())
            <div class="alert-error">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="12" y1="8" x2="12" y2="12" />
                    <line x1="12" y1="16" x2="12.01" y2="16" />
                </svg>
                <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf

                {{-- Email --}}
                <div class="field">
                    <label class="field-label" for="email">Email</label>
                    <div class="field-wrap">
                        <span class="field-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <rect x="2" y="4" width="20" height="16" rx="3" />
                                <polyline points="2,4 12,13 22,4" />
                            </svg>
                        </span>
                        <input
                            class="field-input @error('email') is-invalid @enderror"
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="nama@perusahaan.com"
                            autocomplete="email"
                            autofocus>
                    </div>
                    @error('email')
                    <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="field">
                    <label class="field-label" for="password">Password</label>
                    <div class="field-wrap">
                        <span class="field-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <rect x="3" y="11" width="18" height="11" rx="2" />
                                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                            </svg>
                        </span>
                        <input
                            class="field-input @error('password') is-invalid @enderror"
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Masukkan password"
                            autocomplete="current-password">
                        <button type="button" class="field-toggle" onclick="togglePw()">
                            <svg id="eye-show" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                            <svg id="eye-hide" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="display:none">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" />
                                <line x1="1" y1="1" x2="23" y2="23" />
                            </svg>
                        </button>
                    </div>
                    @error('password')
                    <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Remember + Forgot --}}
                <div class="options-row">
                    <label class="checkbox-wrap">
                        <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <div class="checkbox-custom"></div>
                        <span class="checkbox-label">Ingat saya</span>
                    </label>
                    @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="forgot-link">Lupa password?</a>
                    @endif
                </div>

                {{-- Submit --}}
                <button type="submit" class="btn-submit" id="submitBtn">
                    Masuk Sekarang
                    <span class="btn-arrow">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                            <line x1="5" y1="12" x2="19" y2="12" />
                            <polyline points="12,5 19,12 12,19" />
                        </svg>
                    </span>
                </button>
            </form>

            <div class="form-footer">
                <div class="footer-dot"></div>
                <span class="footer-text">Sistem aman &amp; terenkripsi</span>
            </div>
        </div>

    </div>

    <script>
        function togglePw() {
            const inp = document.getElementById('password');
            const s = document.getElementById('eye-show');
            const h = document.getElementById('eye-hide');
            if (inp.type === 'password') {
                inp.type = 'text';
                s.style.display = 'none';
                h.style.display = 'block';
            } else {
                inp.type = 'password';
                s.style.display = 'block';
                h.style.display = 'none';
            }
        }

        // Loading state saat submit
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('submitBtn');
            btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="animation:spinLoad 0.8s linear infinite"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>&nbsp; Memverifikasi...';
            btn.disabled = true;
            btn.style.opacity = '0.75';
        });
    </script>
</body>

</html>