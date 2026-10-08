@extends('layouts.app')

@section('content')
<style>
    .split-wrapper {
        flex: 1;
        display: flex;
        align-items: center;
        min-height: calc(100vh - 65px);
        padding: 0;
        overflow: hidden;
    }

    .left-section {
        background: linear-gradient(135deg, rgba(6, 9, 19, 0.92) 0%, rgba(15, 23, 42, 0.95) 100%), url('{{ asset('images/3duiq_banner.png') }}');
        background-size: cover;
        background-position: center;
        border-right: 1px solid rgba(56, 189, 248, 0.2);
        padding: 50px 60px;
        min-height: calc(100vh - 65px);
        display: flex;
        flex-direction: column;
        justify-content: center;
        position: relative;
    }

    .right-section {
        background: #060913;
        padding: 50px 60px;
        min-height: calc(100vh - 65px);
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .brand-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(56, 189, 248, 0.12);
        border: 1px solid rgba(56, 189, 248, 0.3);
        color: #38bdf8;
        padding: 6px 16px;
        border-radius: 30px;
        font-size: 0.8rem;
        font-weight: 700;
        margin-bottom: 20px;
        letter-spacing: 0.5px;
        width: fit-content;
    }

    .banner-heading {
        font-size: 2.5rem;
        font-weight: 800;
        line-height: 1.2;
        letter-spacing: -0.5px;
        background: linear-gradient(135deg, #ffffff 0%, #38bdf8 60%, #818cf8 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 16px;
    }

    .banner-desc {
        color: #94a3b8;
        font-size: 1.05rem;
        margin-bottom: 35px;
        line-height: 1.6;
    }

    .feature-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }

    .feature-item {
        background: rgba(15, 23, 42, 0.75);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 12px;
        padding: 16px 20px;
        backdrop-filter: blur(10px);
    }

    .feature-item-title {
        color: #ffffff;
        font-size: 0.95rem;
        font-weight: 700;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .feature-item-desc {
        color: #64748b;
        font-size: 0.82rem;
        margin: 0;
        line-height: 1.4;
    }

    .login-box {
        max-width: 440px;
        width: 100%;
        margin: 0 auto;
        background: rgba(15, 23, 42, 0.85);
        border: 1px solid rgba(56, 189, 248, 0.25);
        border-radius: 20px;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6);
        padding: 40px;
        backdrop-filter: blur(16px);
    }

    .login-title {
        font-size: 1.6rem;
        font-weight: 800;
        color: #ffffff;
        margin-bottom: 6px;
    }

    .login-sub {
        color: #94a3b8;
        font-size: 0.88rem;
        margin-bottom: 30px;
    }

    .form-control-custom {
        background: rgba(30, 41, 59, 0.9);
        border: 1px solid rgba(255, 255, 255, 0.12);
        color: #ffffff;
        border-radius: 10px;
        padding: 12px 16px;
        font-size: 0.95rem;
        transition: all 0.3s ease;
    }

    .form-control-custom:focus {
        background: rgba(30, 41, 59, 1);
        border-color: #38bdf8;
        box-shadow: 0 0 14px rgba(56, 189, 248, 0.35);
        color: #ffffff;
    }

    .btn-3duiq-submit {
        background: linear-gradient(135deg, #0284c7 0%, #4f46e5 100%);
        border: none;
        color: #ffffff;
        font-weight: 700;
        padding: 13px;
        border-radius: 10px;
        font-size: 1rem;
        transition: all 0.3s ease;
        letter-spacing: 0.5px;
        width: 100%;
        margin-top: 10px;
    }

    .btn-3duiq-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(79, 70, 229, 0.4);
        color: #ffffff;
    }

    @media (max-width: 991px) {
        .split-wrapper {
            flex-direction: column;
            min-height: auto;
        }
        .left-section, .right-section {
            min-height: auto;
            padding: 30px 20px;
            width: 100%;
        }
    }
</style>

<div class="container-fluid p-0 split-wrapper">
    <div class="row no-gutters w-100 m-0">
        <!-- Left 50% Section: Banner, Title, and Static Features -->
        <div class="col-lg-6 left-section">
            <div class="brand-pill">
                🎧 3duiq Call Centre Portal
            </div>

            <h1 class="banner-heading">
                Welcome to 3duiq onboarding portal
            </h1>

            <p class="banner-desc">
                Streamlined call center operations management, admin onboarding, real-time call queue telemetry, and system access control.
            </p>

            <div class="feature-grid">
                <div class="feature-item">
                    <div class="feature-item-title">🎧 Omnichannel Telemetry</div>
                    <p class="feature-item-desc">Unified call center routing connecting voice, chat, and ticketing streams.</p>
                </div>
                <div class="feature-item">
                    <div class="feature-item-title">🚀 Automated Onboarding</div>
                    <p class="feature-item-desc">Rapidly provision credentials and assign queue tiers to new admins.</p>
                </div>
                <div class="feature-item">
                    <div class="feature-item-title">🛡️ Security Compliance</div>
                    <p class="feature-item-desc">Strict access control and automated admin action audit trails.</p>
                </div>
                <div class="feature-item">
                    <div class="feature-item-title">📊 Live Admin Analytics</div>
                    <p class="feature-item-desc">Real-time metrics on queue handle times and support SLA status.</p>
                </div>
            </div>
        </div>

        <!-- Right 50% Section: Login Form (Above the Fold) -->
        <div class="col-lg-6 right-section">
            <div class="login-box">
                <h3 class="login-title">3duiq Admin Login</h3>
                <p class="login-sub">Enter your credentials to access the onboarding portal</p>

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="form-group mb-3">
                        <label for="email" class="text-light small font-weight-bold">Admin Email</label>
                        <input id="email" type="email" class="form-control form-control-custom @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="admin@admin.com">

                        @error('email')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="password" class="text-light small font-weight-bold">Admin Password</label>
                        <input id="password" type="password" class="form-control form-control-custom @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="••••••••">

                        @error('password')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group d-flex justify-content-between align-items-center mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label text-secondary small" for="remember">
                                Remember session
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-3duiq-submit shadow">
                        Access Onboarding Portal →
                    </button>
                </form>

                <div class="text-center mt-4 pt-3 border-top border-secondary">
                    <small class="text-muted">3duiq Admin Support: <a href="mailto:support@3duiq.com" class="text-info">support@3duiq.com</a></small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
