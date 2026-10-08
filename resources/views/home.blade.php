@extends('layouts.app')

@section('content')
<style>
    .home-hero-banner {
        background: linear-gradient(135deg, rgba(15, 23, 42, 0.92) 0%, rgba(30, 27, 75, 0.95) 100%), url('{{ asset('images/3duiq_banner.png') }}');
        background-size: cover;
        background-position: center;
        border-bottom: 1px solid rgba(56, 189, 248, 0.25);
        padding: 45px 0;
        margin-bottom: 40px;
    }

    .action-card {
        background: rgba(15, 23, 42, 0.85);
        border: 1px solid rgba(56, 189, 248, 0.25);
        border-radius: 20px;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(16px);
        padding: 40px 30px;
        text-align: center;
        transition: all 0.35 ease;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        align-items: center;
        text-decoration: none !important;
    }

    .action-card:hover {
        transform: translateY(-8px);
        border-color: #38bdf8;
        box-shadow: 0 30px 60px rgba(56, 189, 248, 0.3);
    }

    .action-icon-wrapper {
        width: 85px;
        height: 85px;
        border-radius: 20px;
        background: rgba(56, 189, 248, 0.12);
        border: 1px solid rgba(56, 189, 248, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        margin-bottom: 24px;
        transition: all 0.3s ease;
    }

    .action-card:hover .action-icon-wrapper {
        background: rgba(56, 189, 248, 0.25);
        transform: scale(1.08);
    }

    .action-title {
        font-size: 1.6rem;
        font-weight: 800;
        color: #ffffff;
        margin-bottom: 10px;
    }

    .action-desc {
        color: #94a3b8;
        font-size: 0.95rem;
        margin-bottom: 25px;
        line-height: 1.5;
    }

    .btn-action-custom {
        width: 100%;
        padding: 14px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 1rem;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
    }

    .btn-user {
        background: linear-gradient(135deg, #0284c7 0%, #3b82f6 100%);
        border: none;
        color: #ffffff;
    }

    .btn-company {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        border: none;
        color: #ffffff;
    }

    .success-panel {
        background: rgba(16, 185, 129, 0.1);
        border: 1px solid rgba(16, 185, 129, 0.4);
        border-radius: 16px;
        padding: 24px 30px;
        margin-bottom: 40px;
    }
</style>

<!-- Top Welcome Banner -->
<div class="home-hero-banner text-center">
    <div class="container">
        <span class="badge badge-info px-3 py-2 mb-2 font-weight-bold">⚡ 3duiq Call Centre Systems</span>
        <h1 class="text-white font-weight-bold mb-2" style="font-size: 2.4rem;">Welcome to 3duiq onboarding portal</h1>
        <p class="text-secondary mb-0">Select an action below to manage system users or onboard a new company instance.</p>
    </div>
</div>

<div class="container pb-5">
    <!-- Onboarding Success Alert -->
    @if (session('onboard_success'))
        @php $data = session('onboard_success'); @endphp
        <div class="success-panel">
            <div class="d-flex align-items-center mb-3">
                <div style="font-size: 1.8rem; margin-right: 12px;">🎉</div>
                <div>
                    <h4 class="text-success font-weight-bold mb-0">Company "{{ $data['company'] }}" Onboarded Successfully!</h4>
                    <p class="text-secondary small mb-0">Virtual hosts and databases have been created and mapped.</p>
                </div>
            </div>

            <div class="row text-left mt-3">
                <div class="col-md-6 mb-3 mb-md-0">
                    <h6 class="text-light font-weight-bold">🌐 CREATED VIRTUAL HOSTS:</h6>
                    <ul class="mb-0 pl-3 text-info">
                        @if ($data['crm_vhost'])
                            <li><a href="{{ $data['crm_vhost'] }}" target="_blank" class="text-info font-weight-bold">{{ $data['crm_vhost'] }}</a> (CRM)</li>
                        @endif
                        @if ($data['reporting_vhost'])
                            <li><a href="{{ $data['reporting_vhost'] }}" target="_blank" class="text-info font-weight-bold">{{ $data['reporting_vhost'] }}</a> (Reporting)</li>
                        @endif
                        @if (!empty($data['api_vhost']))
                            <li><a href="{{ $data['api_vhost'] }}" target="_blank" rel="noopener noreferrer" class="text-info font-weight-bold">{{ $data['api_vhost'] }}</a> (3duiq api)</li>
                        @endif
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6 class="text-light font-weight-bold">🗄️ CREATED DATABASES:</h6>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach ($data['databases'] as $db)
                            <span class="badge badge-dark border border-secondary text-success px-2 py-1 mr-1 mb-1">{{ $db }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if (session('status'))
        <div class="alert alert-success bg-dark text-success border-success mb-4" role="alert">
            {{ session('status') }}
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success bg-dark text-success border-success mb-4" role="alert">
            {{ session('success') }}
        </div>
    @endif

    <!-- Two Primary Action Buttons -->
    <div class="row justify-content-center">
        <!-- Button 1: Create User -->
        <div class="col-md-5 mb-4">
            <a href="{{ route('users.create') }}" class="action-card">
                <div>
                    <div class="action-icon-wrapper mx-auto">
                        👤
                    </div>
                    <div class="action-title">Create User</div>
                    <div class="action-desc">
                        Add and provision new admin user accounts to access the 3duiq onboarding portal.
                    </div>
                </div>
                <button class="btn btn-action-custom btn-user">
                    + Create User →
                </button>
            </a>
        </div>

        <!-- Button 2: Onboard Company -->
        <div class="col-md-5 mb-4">
            <a href="{{ route('companies.create') }}" class="action-card">
                <div>
                    <div class="action-icon-wrapper mx-auto">
                        🏢
                    </div>
                    <div class="action-title">Onboard Company</div>
                    <div class="action-desc">
                        Provision new company instances, virtual host domains (CRM & Reporting), and isolated databases.
                    </div>
                </div>
                <button class="btn btn-action-custom btn-company">
                    + Onboard Company →
                </button>
            </a>
        </div>
    </div>
</div>
@endsection
