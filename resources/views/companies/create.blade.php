@extends('layouts.app')

@section('content')
<style>
    .onboard-card {
        background: rgba(15, 23, 42, 0.85);
        border: 1px solid rgba(56, 189, 248, 0.25);
        border-radius: 20px;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(16px);
        padding: 40px;
    }

    .form-control-custom {
        background: rgba(30, 41, 59, 0.9);
        border: 1px solid rgba(255, 255, 255, 0.12);
        color: #ffffff;
        border-radius: 10px;
        padding: 13px 18px;
        font-size: 0.95rem;
        transition: all 0.3s ease;
    }

    .form-control-custom:focus {
        background: rgba(30, 41, 59, 1);
        border-color: #38bdf8;
        box-shadow: 0 0 14px rgba(56, 189, 248, 0.35);
        color: #ffffff;
    }

    .instance-card-option {
        background: rgba(30, 41, 59, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        padding: 20px;
        cursor: pointer;
        transition: all 0.3s ease;
        height: 100%;
    }

    .instance-card-option:hover {
        border-color: #38bdf8;
        background: rgba(30, 41, 59, 0.9);
    }

    .custom-check-input:checked ~ .instance-card-option {
        border-color: #38bdf8;
        background: rgba(56, 189, 248, 0.12);
        box-shadow: 0 0 15px rgba(56, 189, 248, 0.3);
    }

    .info-box {
        background: rgba(30, 41, 59, 0.5);
        border-left: 4px solid #38bdf8;
        border-radius: 8px;
        padding: 18px 22px;
    }

    .btn-onboard {
        background: linear-gradient(135deg, #0284c7 0%, #4f46e5 100%);
        border: none;
        color: #ffffff;
        font-weight: 700;
        padding: 14px 30px;
        border-radius: 10px;
        font-size: 1rem;
        transition: all 0.3s ease;
        letter-spacing: 0.5px;
    }

    .btn-onboard:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(79, 70, 229, 0.4);
        color: #ffffff;
    }
</style>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="onboard-card">
                <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom border-secondary">
                    <div>
                        <span class="badge badge-info px-3 py-2 mb-2 font-weight-bold">🏢 3duiq Multi-Tenant Setup</span>
                        <h3 class="text-white font-weight-bold mb-1">Onboard New Company</h3>
                        <p class="text-secondary small mb-0">Provision virtual hosts and databases for a new company instance.</p>
                    </div>
                    <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-sm">
                        ← Back to Dashboard
                    </a>
                </div>

                <form method="POST" action="{{ route('companies.store') }}" enctype="multipart/form-data">
                    @csrf

                    <!-- Company Info Section -->
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label for="name" class="text-light small font-weight-bold">Company Name <span class="text-danger">*</span></label>
                            <input id="name" type="text" class="form-control form-control-custom @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autofocus placeholder="e.g. Acme Corp">

                            @error('name')
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="logo" class="text-light small font-weight-bold">Company Logo</label>
                            <input id="logo" type="file" class="form-control form-control-custom @error('logo') is-invalid @enderror" name="logo" accept="image/*">
                            <small class="text-muted">Optional (PNG, JPG, SVG up to 2MB)</small>

                            @error('logo')
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <!-- Instance Selection Section -->
                    <div class="form-group mb-4">
                        <label class="text-light small font-weight-bold mb-3 d-block">Select Projects / Instances to Provision <span class="text-danger">*</span></label>

                        <div class="row">
                            <!-- Option 1: CRM -->
                            <div class="col-md-4 mb-3">
                                <label class="w-100 mb-0">
                                    <input type="checkbox" name="instances[]" value="crm" class="d-none custom-check-input" {{ in_array('crm', (array) old('instances', [])) ? 'checked' : '' }}>
                                    <div class="instance-card-option">
                                        <div class="mb-2">
                                            <h5 class="text-white font-weight-bold mb-2">💻 CRM Instance</h5>
                                            <span class="badge badge-primary">instance.3duiq.com</span>
                                        </div>
                                        <p class="text-secondary small mb-0">Creates virtual host domain <code>companyname.3duiq.com</code> pointing to the company's dedicated CRM application.</p>
                                    </div>
                                </label>
                            </div>

                            <!-- Option 2: Reporting -->
                            <div class="col-md-4 mb-3">
                                <label class="w-100 mb-0">
                                    <input type="checkbox" name="instances[]" value="reporting" class="d-none custom-check-input" {{ in_array('reporting', (array) old('instances', [])) ? 'checked' : '' }}>
                                    <div class="instance-card-option">
                                        <div class="mb-2">
                                            <h5 class="text-white font-weight-bold mb-2">📊 Reporting Instance</h5>
                                            <span class="badge badge-info">instancereport.3duiq.com</span>
                                        </div>
                                        <p class="text-secondary small mb-0">Creates virtual host domain <code>companynamereport.3duiq.com</code> for reporting and telemetry analytics.</p>
                                    </div>
                                </label>
                            </div>
                            <!-- Option 3: 3duiq api -->
                            <div class="col-md-4 mb-3">
                                <label class="w-100 mb-0">
                                    <input type="checkbox" name="instances[]" value="api" class="d-none custom-check-input" {{ in_array('api', (array) old('instances', [])) ? 'checked' : '' }}>
                                    <div class="instance-card-option">
                                        <div class="mb-2">
                                            <h5 class="text-white font-weight-bold mb-2">3duiq api</h5>
                                            <span class="badge badge-info">instanceapi.3duiq.com</span>
                                        </div>
                                        <p class="text-secondary small mb-0">Creates virtual host domain <code>companynameapi.3duiq.com</code> to host the company's API application.</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        @error('instances.*')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror

                        @error('instances')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <!-- Automatic Database Provisioning Notice -->
                    <div class="info-box mb-4">
                        <h6 class="text-info font-weight-bold mb-2">🗄️ Automated Database Provisioning</h6>
                        <p class="text-secondary small mb-2">Submitting this form will automatically generate 3 isolated MySQL databases for this company:</p>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge badge-dark border border-secondary text-info px-3 py-2 mr-2 mb-2">tracking_{company}</span>
                            <span class="badge badge-dark border border-secondary text-info px-3 py-2 mr-2 mb-2">lcr_{company}</span>
                            <span class="badge badge-dark border border-secondary text-info px-3 py-2 mb-2">lcapi_{company}</span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-between align-items-center pt-2">
                        <a href="{{ route('home') }}" class="text-muted">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-onboard shadow">
                            🚀 Launch & Provision Company Instances
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
