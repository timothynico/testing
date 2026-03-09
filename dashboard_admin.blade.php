@extends('layouts.app')

@section('title', __('Admin Dashboard'))

@section('header-left')
    <div>
        <h2 class="h5 fw-semibold mb-0">{{ __('Admin Dashboard') }}</h2>
        <span class="text-muted small">{{ __('Real-time overview') }}</span>
    </div>
@endsection

@section('content')
    {{-- Quick Actions --}}
    <div class="dashboard-section">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="text-uppercase text-secondary fw-semibold me-1"
                style="font-size: 0.85rem; letter-spacing: 0.3px;">{{ __('Quick Actions') }}</span>

            <a href="{{ route('agreement.create') }}" class="btn btn-primary btn-sm px-3 py-1">
                <i class="bi bi-plus me-1"></i>{{ __('Agreement') }}
            </a>

            <a href="{{ route('delivery.create') }}" class="btn btn-success btn-sm px-3 py-1">
                <i class="bi bi-plus me-1"></i>{{ __('Delivery') }}
            </a>

            <a href="{{ route('customers.create') }}" class="btn btn-info btn-sm px-3 py-1">
                <i class="bi bi-plus me-1"></i>{{ __('Customer') }}
            </a>

            <a href="{{ route('logisticcompany.create') }}" class="btn btn-danger btn-sm px-3 py-1 text-white">
                <i class="bi bi-plus me-1"></i>{{ __('Logistic') }}
            </a>

            <a href="{{ route('company.create') }}" class="btn btn-warning btn-sm px-3 py-1">
                <i class="bi bi-plus me-1"></i>{{ __('Company') }}
            </a>

        </div>
    </div>

    {{-- Stats Cards Row 1 - 6 columns --}}
    <div class="dashboard-section">
        <div class="row g-2">
            <div class="col-4 col-md-2">
                <div class="card border shadow-sm h-100">
                    <div class="card-body p-2">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <small class="text-uppercase text-secondary fw-semibold"
                                style="font-size: 0.75rem; letter-spacing: 0.3px;">{{ __('Total Pallets') }}</small>
                            <span class="badge rounded-pill"
                                style="font-size: 0.7rem; background-color: rgba(25, 135, 84, 0.1); color: #198754;">{{ $totalPalletsChange }}</span>
                        </div>
                        <div class="h6 fw-bold mb-0" style="font-size: 1.15rem;">{{ number_format($totalPallets) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-4 col-md-2">
                <div class="card border shadow-sm h-100">
                    <div class="card-body p-2">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <small class="text-uppercase text-secondary fw-semibold"
                                style="font-size: 0.75rem; letter-spacing: 0.3px;">{{ __('In Transit') }}</small>
                            <span class="badge rounded-pill"
                                style="font-size: 0.7rem; background-color: rgba(220, 53, 69, 0.1); color: #dc3545;">{{ $inTransitChange }}</span>
                        </div>
                        <div class="h6 fw-bold mb-0" style="font-size: 1.15rem;">{{ number_format($inTransit) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-4 col-md-2">
                <div class="card border shadow-sm h-100">
                    <div class="card-body p-2">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <small class="text-uppercase text-secondary fw-semibold"
                                style="font-size: 0.75rem; letter-spacing: 0.3px;">{{ __('Customers') }}</small>
                            <span class="badge rounded-pill"
                                style="font-size: 0.7rem; background-color: rgba(25, 135, 84, 0.1); color: #198754;">{{ $customersChange }}</span>
                        </div>
                        <div class="h6 fw-bold mb-0" style="font-size: 1.15rem;">{{ number_format($customers) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-4 col-md-2">
                <div class="card border shadow-sm h-100">
                    <div class="card-body p-2">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <small class="text-uppercase text-secondary fw-semibold"
                                style="font-size: 0.75rem; letter-spacing: 0.3px;">{{ __('Agreements') }}</small>
                            <span class="badge rounded-pill"
                                style="font-size: 0.7rem; background-color: rgba(25, 135, 84, 0.1); color: #198754;">{{ $agreementsChange }}</span>
                        </div>
                        <div class="h6 fw-bold mb-0" style="font-size: 1.15rem;">{{ number_format($agreements) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-4 col-md-2">
                <div class="card border shadow-sm h-100">
                    <div class="card-body p-2">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <small class="text-uppercase text-secondary fw-semibold"
                                style="font-size: 0.75rem; letter-spacing: 0.3px;">{{ __('Deliveries') }}*</small>
                            <span class="badge rounded-pill"
                                style="font-size: 0.7rem; background-color: rgba(25, 135, 84, 0.1); color: #198754;">{{ $deliveriesChange }}</span>
                        </div>
                        <div class="h6 fw-bold mb-0" style="font-size: 1.15rem;">{{ number_format($deliveries) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-4 col-md-2">
                <div class="card border shadow-sm h-100">
                    <div class="card-body p-2">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <small class="text-uppercase text-secondary fw-semibold"
                                style="font-size: 0.75rem; letter-spacing: 0.3px;">{{ __('Revenue') }}*</small>
                            <span class="badge rounded-pill"
                                style="font-size: 0.7rem; background-color: rgba(25, 135, 84, 0.1); color: #198754;">{{ $revenueChange }}</span>
                        </div>
                        <div class="h6 fw-bold mb-0" style="font-size: 1.15rem;">{{ $revenue }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content Row --}}
    <div class="dashboard-section">
        <div class="row g-2">
            {{-- Left Column --}}
            <div class="col-8">
                {{-- Pallet Distribution --}}
                <div class="card border shadow-sm mb-2">
                    <div class="card-header bg-white border-bottom py-1 px-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-semibold d-flex align-items-center gap-2" style="font-size: 0.95rem;">
                                <i class="bi bi-pie-chart-fill"
                                    style="color: #0d6efd; font-size: 0.9rem;"></i>{{ __('Pallet Distribution') }}*
                            </h6>
                            <a href="#" class="text-decoration-none fw-medium"
                                style="font-size: 0.8rem; color: #0d6efd;">{{ __('Details') }}</a>
                        </div>
                    </div>
                    <div class="card-body p-2">
                        <div class="row g-2">
                            <div class="col-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-semibold" style="font-size: 0.9rem;">PT1210AS</span>
                                    <span class="badge"
                                        style="font-size: 0.75rem; background-color: #0d6efd; color: white;">70%</span>
                                </div>
                                <div class="progress mb-1" style="height: 8px; background-color: #e9ecef;">
                                    <div class="progress-bar"
                                        style="width: 70%; background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-secondary" style="font-size: 0.8rem;">122,850</span>
                                    <span style="font-size: 0.75rem; color: #198754;">
                                        <i class="bi bi-arrow-up-short"></i>5%
                                    </span>
                                </div>
                            </div>

                            <div class="col-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-semibold" style="font-size: 0.9rem;">PT1212</span>
                                    <span class="badge"
                                        style="font-size: 0.75rem; background-color: #198754; color: white;">20%</span>
                                </div>
                                <div class="progress mb-1" style="height: 8px; background-color: #e9ecef;">
                                    <div class="progress-bar"
                                        style="width: 20%; background: linear-gradient(135deg, #198754 0%, #146c43 100%);">
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-secondary" style="font-size: 0.8rem;">35,100</span>
                                    <span style="font-size: 0.75rem; color: #6c757d;">
                                        <i class="bi bi-dash"></i>0%
                                    </span>
                                </div>
                            </div>

                            <div class="col-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-semibold" style="font-size: 0.9rem;">B325</span>
                                    <span class="badge"
                                        style="font-size: 0.75rem; background-color: #ffc107; color: white;">7%</span>
                                </div>
                                <div class="progress mb-1" style="height: 8px; background-color: #e9ecef;">
                                    <div class="progress-bar"
                                        style="width: 7%; background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%);">
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-secondary" style="font-size: 0.8rem;">12,285</span>
                                    <span style="font-size: 0.75rem; color: #dc3545;">
                                        <i class="bi bi-arrow-down-short"></i>2%
                                    </span>
                                </div>
                            </div>

                            <div class="col-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-semibold" style="font-size: 0.9rem;">PT1208</span>
                                    <span class="badge"
                                        style="font-size: 0.75rem; background-color: #6c757d; color: white;">3%</span>
                                </div>
                                <div class="progress mb-1" style="height: 8px; background-color: #e9ecef;">
                                    <div class="progress-bar bg-secondary" style="width: 3%;"></div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-secondary" style="font-size: 0.8rem;">5,265</span>
                                    <span style="font-size: 0.75rem; color: #198754;">
                                        <i class="bi bi-arrow-up-short"></i>1%
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Pending Requests --}}
                <div class="card border shadow-sm mb-2">
                    <div class="card-header bg-white border-bottom py-1 px-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-semibold d-flex align-items-center gap-2" style="font-size: 0.95rem;">
                                <i class="bi bi-clock-history"
                                    style="color: #ffc107; font-size: 0.9rem;"></i>{{ __('Pending Requests') }}
                            </h6>
                            <span class="badge rounded-pill px-2 py-1"
                                style="font-size: 0.75rem; background-color: rgba(255, 193, 7, 0.15); color: #ffc107; font-weight: 600;">
                                {{ $pendingRequestsCount }} {{ __('pending') }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-2">
                        @if ($pendingRequests->isEmpty())
                            <div class="text-center py-4 text-muted">
                                <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                <p class="mb-0 mt-2">{{ __('No pending requests') }}</p>
                            </div>
                        @else
                            <div class="row g-2">
                                @foreach ($pendingRequests as $request)
                                    <div class="col-3">
                                        <a href="{{ route('orderreturn.order_return_show', $request->nid) }}"
                                            class="text-decoration-none">
                                            <div class="request-card p-2 h-100 rounded border"
                                                style="background: linear-gradient(135deg, #fffbf0 0%, #ffffff 100%); transition: all 0.3s ease; cursor: pointer;">
                                                <div class="d-flex justify-content-between align-items-start mb-1">
                                                    <div class="fw-bold" style="font-size: 0.9rem; color: #212529;">
                                                        {{ $request->ccompany_name }}
                                                    </div>
                                                    <div class="text-secondary" style="font-size: 0.75rem;">
                                                        {{ $request->hours_ago }}h
                                                    </div>
                                                </div>
                                                <div>
                                                    <span class="fw-semibold"
                                                        style="font-size: 0.8rem; color: #495057;">
                                                        {{ $request->cpallet_type }} ({{ $request->cpallet_size }}) |
                                                        {{ $request->nqty }} Pcs -
                                                    </span>
                                                    <span class="text-semibold"
                                                        style="font-size: 0.75rem; color: #495057;">{{ $request->cpallet_color }}
                                                    </span>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Recent Activity --}}
                <div class="card border shadow-sm">
                    <div class="card-header bg-white border-bottom py-1 px-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-semibold d-flex align-items-center gap-2" style="font-size: 0.95rem;">
                                <i class="bi bi-activity"
                                    style="color: #0d6efd; font-size: 0.9rem;"></i>{{ __('Recent Activity') }}
                            </h6>
                            <a href="#" class="text-decoration-none fw-medium"
                                style="font-size: 0.8rem; color: #0d6efd;">{{ __('View All') }}</a>
                        </div>
                    </div>
                    <div class="card-body p-2">
                        <div class="row g-2">
                            @foreach ([
            [
                'icon' => 'check-circle-fill',
                'color' => '#198754',
                'title' => 'Delivery Completed',
                'desc' => 'SJ/25RD0108/142 - 150 pallets',
                'time' => '2h ago',
            ],
            [
                'icon' => 'file-earmark-plus-fill',
                'color' => '#0d6efd',
                'title' => 'New Agreement',
                'desc' => 'AGR-2025-847 - PT Ultrajaya',
                'time' => '5h ago',
            ],
            [
                'icon' => 'arrow-return-left',
                'color' => '#0dcaf0',
                'title' => 'Pallets Returned',
                'desc' => '85 PT1210AS from PT Yanaprima',
                'time' => '1d ago',
            ],
            [
                'icon' => 'check-circle-fill',
                'color' => '#198754',
                'title' => 'Delivery Completed',
                'desc' => 'SJ/25RD0107/089 - 300 pallets',
                'time' => '2d ago',
            ],
            [
                'icon' => 'people-fill',
                'color' => '#6f42c1',
                'title' => 'New Customer',
                'desc' => 'PT Indofood CBP joined',
                'time' => '3d ago',
            ],
            [
                'icon' => 'box-seam-fill',
                'color' => '#fd7e14',
                'title' => 'Stock Replenished',
                'desc' => '5000 PT1212 added to inventory',
                'time' => '4d ago',
            ],
        ] as $activity)
                                <div class="col-4">
                                    <div class="d-flex align-items-start gap-2 p-2 border rounded"
                                        style="background: #f8f9fa;">
                                        <div class="rounded d-flex align-items-center justify-content-center flex-shrink-0 text-white"
                                            style="width: 32px; height: 32px; background-color: {{ $activity['color'] }};">
                                            <i class="bi bi-{{ $activity['icon'] }}" style="font-size: 0.9rem;"></i>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden">
                                            <div class="fw-semibold" style="font-size: 0.85rem;">
                                                {{ $activity['title'] }}</div>
                                            <div class="text-secondary text-truncate" style="font-size: 0.75rem;">
                                                {{ $activity['desc'] }}</div>
                                            <div class="text-secondary" style="font-size: 0.7rem;">
                                                {{ $activity['time'] }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column --}}
            <div class="col-4">
                {{-- Top Customers --}}
                <div class="card border shadow-sm mb-2">
                    <div class="card-header bg-white border-bottom py-1 px-2">
                        <h6 class="mb-0 fw-semibold d-flex align-items-center gap-2" style="font-size: 0.95rem;">
                            <i class="bi bi-star-fill"
                                style="color: #ffc107; font-size: 0.9rem;"></i>{{ __('Top Customers') }}
                        </h6>
                    </div>
                    <div class="card-body p-2">
                        @foreach ([['name' => 'PT Unilever Indonesia', 'pallets' => '45.2K', 'change' => '+12%', 'color' => '#198754'], ['name' => 'PT Nestle Indonesia', 'pallets' => '38.7K', 'change' => '+8%', 'color' => '#198754'], ['name' => 'PT Mayora Indah', 'pallets' => '32.1K', 'change' => '+5%', 'color' => '#198754'], ['name' => 'PT Wings Group', 'pallets' => '28.5K', 'change' => '-2%', 'color' => '#dc3545'], ['name' => 'PT Indofood CBP', 'pallets' => '24.3K', 'change' => '+15%', 'color' => '#198754']] as $customer)
                            <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                                <div class="flex-grow-1">
                                    <div class="fw-semibold text-truncate" style="font-size: 0.85rem;">
                                        {{ $customer['name'] }}</div>
                                    <div class="text-secondary" style="font-size: 0.75rem;">{{ $customer['pallets'] }}
                                        {{ __('pallets') }}</div>
                                </div>
                                <span class="badge" style="font-size: 0.75rem; color: {{ $customer['color'] }};">
                                    {{ $customer['change'] }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Logistics Performance --}}
                <div class="card border shadow-sm mb-2">
                    <div class="card-header bg-white border-bottom py-1 px-2">
                        <h6 class="mb-0 fw-semibold d-flex align-items-center gap-2" style="font-size: 0.95rem;">
                            <i class="bi bi-truck" style="color: #0d6efd; font-size: 0.9rem;"></i>{{ __('Logistics') }}
                        </h6>
                    </div>
                    <div class="card-body p-2">
                        @foreach ([['name' => 'JNE Logistics', 'status' => 'On Time', 'rate' => '98%', 'color' => '#198754'], ['name' => 'TIKI Express', 'status' => 'On Time', 'rate' => '95%', 'color' => '#198754'], ['name' => 'Pos Indonesia', 'status' => 'Delayed', 'rate' => '87%', 'color' => '#ffc107'], ['name' => 'SiCepat', 'status' => 'On Time', 'rate' => '96%', 'color' => '#198754']] as $logistic)
                            <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                                <div>
                                    <div class="fw-semibold" style="font-size: 0.85rem;">{{ $logistic['name'] }}</div>
                                    <div class="text-secondary" style="font-size: 0.75rem;">{{ $logistic['status'] }}
                                    </div>
                                </div>
                                <span class="badge rounded-pill"
                                    style="font-size: 0.75rem; background-color: {{ $logistic['color'] }}; color: white;">
                                    {{ $logistic['rate'] }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* Consistent vertical spacing between dashboard sections */
        .dashboard-section {
            margin-bottom: 0.75rem;
        }

        /* Remove extra spacing from Bootstrap rows */
        .dashboard-section .row {
            margin-bottom: 0;
        }

        /* Ensure cards don't add extra margin */
        .dashboard-section .card {
            margin-bottom: 0;
        }

        /* Request Cards */
        .request-card {
            border-color: rgba(255, 193, 7, 0.2) !important;
        }

        .request-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 193, 7, 0.15);
            border-color: rgba(255, 193, 7, 0.4) !important;
        }

        /* Minimal custom styles - most styling is Bootstrap */
        .card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.1) !important;
        }

        .btn-sm {
            font-size: 0.85rem;
        }

        /* Fix warning button text color */
        .btn-warning {
            color: white !important;
        }

        /* Border only on medium screens and up */
        .border-end-md {
            border-right: none !important;
        }

        @media (min-width: 768px) {
            .border-end-md {
                border-right: 1px solid #dee2e6 !important;
            }
        }
    </style>
@endpush
