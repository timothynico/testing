@extends('layouts.app')

@section('title', __('Dashboard'))

@section('header-left')
    <div>
        <h2 class="h5 fw-semibold mb-0">{{ __('Dashboard') }}</h2>
        <span class="text-muted small">{{ __('Real-time overview') }}</span>
    </div>
@endsection

@section('content')
    {{-- Quick Actions --}}
    <div class="dashboard-section">
        
        <div class="text-uppercase text-secondary fw-semibold mb-2"
            style="font-size: 0.85rem; letter-spacing: 0.3px;">
            {{ __('Quick Actions') }}
        </div>

        <div class="row g-2">
            <div class="col-6 col-md-auto">
                <a href="{{ route('orderreturn.request_email') }}" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-envelope me-1"></i>{{ __('Request Order/Return') }}
                </a>
            </div>

            <div class="col-6 col-md-auto">
                <a href="{{ route('delivery.create') }}" class="btn btn-success btn-sm w-100">
                    <i class="bi bi-truck me-1"></i>{{ __('New Delivery Note') }}
                </a>
            </div>

            <div class="col-6 col-md-auto">
                <a href="{{ route('logisticcompany.create') }}" class="btn btn-danger btn-sm w-100">
                    <i class="bi bi-buildings me-1"></i>{{ __('New Logistic Company') }}
                </a>
            </div>

            <div class="col-6 col-md-auto">
                <a href="{{ route('invoice.index') }}" class="btn btn-warning btn-sm w-100">
                    <i class="bi bi-receipt me-1"></i>{{ __('Monitor Invoice') }}
                </a>
            </div>
        </div>

    </div>

    {{-- Stats Cards Row --}}
    <div class="dashboard-section">
        <div class="row g-2">
            <div class="col-12 col-md-4">
                <div class="card border shadow-sm h-100">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="d-flex align-items-center gap-1">
                                <small class="text-uppercase text-secondary fw-semibold"
                                    style="font-size: 0.75rem; letter-spacing: 0.3px;">{{ __('On-Hand Pallet Inventory') }}</small>
                                <i class="bi bi-info-circle text-secondary" style="font-size: 0.75rem; cursor: help;"
                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="{{ __('Total number of pallets currently available in your warehouses and storage facilities') }}"></i>
                                @if ($onHandPalletTotalPercentChange > 0)
                                    <span class="badge rounded-pill ms-1"
                                        style="font-size: 0.7rem; background-color: rgba(25, 135, 84, 0.1); color: #198754;">▲{{ $onHandPalletTotalPercentChange }}%</span>
                                @elseif ($onHandPalletTotalPercentChange < 0)
                                    <span class="badge rounded-pill ms-1"
                                        style="font-size: 0.7rem; background-color: rgba(220, 53, 69, 0.1); color: #dc3545;">▼{{ abs($onHandPalletTotalPercentChange) }}%</span>
                                @else
                                    <span class="badge rounded-pill ms-1"
                                        style="font-size: 0.7rem; background-color: rgba(108, 117, 125, 0.1); color: #6c757d;">0%</span>
                                @endif
                            </div>
                            <button type="button" class="btn btn-success p-1 text-decoration-none" data-bs-toggle="modal"
                                data-bs-target="#onHandDetailModal" style="font-size: 0.7rem; line-height: 1;">
                                {{ __('View All') }} <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="h6 fw-bold mb-0" style="font-size: 1.15rem;">{{ $onHandPalletTotal }} <span
                                    class="text-secondary" style="font-size: 1rem">{{ __('Pallets') }}</span></div>
                            <div class="d-flex gap-2" style="font-size: 0.7rem;">
                                @foreach ($onHandPalletSummary as $type => $qty)
                                    <div class="d-flex align-items-baseline gap-1">
                                        <span class="text-muted" style="font-size: 0.75rem;">{{ $type }}</span>
                                        <span class="fw-bold" style="font-size: 0.95rem;">{{ $qty }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="card border shadow-sm h-100">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="d-flex align-items-center gap-1">
                                <small class="text-uppercase text-secondary fw-semibold"
                                    style="font-size: 0.75rem; letter-spacing: 0.3px;">{{ __('In-Transit Pallet Monitoring') }}</small>
                                <i class="bi bi-info-circle text-secondary" style="font-size: 0.75rem; cursor: help;"
                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="{{ __('Pallets currently being transported between locations, including incoming and outgoing shipments') }}"></i>
                                @if ($inTransitPercentChange < 0)
                                    <span class="badge rounded-pill ms-1"
                                        style="font-size: 0.7rem; background-color: rgba(220, 53, 69, 0.1); color: #dc3545;">▼{{ $inTransitPercentChange }}%</span>
                                @elseif($inTransitPercentChange > 0)
                                    <span class="badge rounded-pill ms-1"
                                        style="font-size: 0.7rem; background-color: rgba(25, 135, 84, 0.1); color: #198754;">▲{{ $inTransitPercentChange }}%</span>
                                @else
                                    <span class="badge rounded-pill ms-1"
                                        style="font-size: 0.7rem; background-color: rgba(108, 117, 125, 0.1); color: #6c757d;">0%</span>
                                @endif
                            </div>
                            <button type="button" class="btn btn-success p-1 text-decoration-none" data-bs-toggle="modal"
                                data-bs-target="#inTransitDetailModal" style="font-size: 0.7rem; line-height: 1;">
                                {{ __('View All') }} <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="h6 fw-bold mb-0" style="font-size: 1.15rem;">{{ $incomingTotal + $outgoingTotal }}
                                <span class="text-secondary" style="font-size: 1rem">{{ __('Pallets') }}</span>
                            </div>
                            <div class="d-flex gap-2" style="font-size: 0.7rem;">
                                <span class="text-success"><i class="bi bi-arrow-down-circle me-1"></i>{{ __('In') }}
                                    {{ $incomingTotal }}</span>
                                <span class="text-danger"><i class="bi bi-arrow-up-circle me-1"></i>{{ __('Out') }}
                                    {{ $outgoingTotal }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="card border shadow-sm h-100">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="d-flex align-items-center gap-1">
                                <small class="text-uppercase text-secondary fw-semibold"
                                    style="font-size: 0.75rem; letter-spacing: 0.3px;">{{ __('Customer-Liable Pallet') }}</small>
                                <i class="bi bi-info-circle text-secondary" style="font-size: 0.75rem; cursor: help;"
                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="{{ __('Total pallets currently chargeable to your account today, including pallets recorded in your on-hand inventory and pallets delivered to your customers that are still within the agreed liability transition period') }}"></i>
                                <span class="badge rounded-pill ms-1"
                                    style="font-size: 0.7rem; background-color: rgba(255, 193, 7, 0.1); color: #ffc107;">▲14%</span>
                            </div>
                            <button type="button" class="btn btn-success p-1 text-decoration-none"
                                data-bs-toggle="modal" data-bs-target="#customerLiableDetailModal"
                                style="font-size: 0.7rem; ; line-height: 1;">
                                {{ __('View All') }} <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>
                        <div class="h6 fw-bold mb-0" style="font-size: 1.15rem;">1,285 <span class="text-secondary"
                                style="font-size: 1rem">{{ __('Pallets') }}</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Stats Card Detailed Modals --}}
    {{-- On-Hand Inventory Detail Modal --}}
    <div class="modal fade" id="onHandDetailModal" tabindex="-1" aria-labelledby="onHandDetailModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-0" id="onHandDetailModalLabel">
                            {{ __('On-Hand Pallet Inventory Details') }}</h5>
                        <small class="text-muted">{{ __('Current inventory breakdown by location') }}</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0" style="font-size: 0.85rem;">
                            <thead class="table-light">
                                <tr>
                                    <th class="py-2 px-3">{{ __('Pallet Type') }}</th>
                                    <th class="py-2 px-3">{{ __('Location') }}</th>
                                    <th class="py-2 px-3">{{ __('Warehouse') }}</th>
                                    <th class="py-2 px-3 text-end">{{ __('Quantity') }}</th>
                                    <th class="py-2 px-3">{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rowdatabarang as $inventory)
                                    <tr>
                                        <td class="py-2 px-3 fw-semibold">{{ $inventory->cbasic }}</td>
                                        <td class="py-2 px-3 text-muted">{{ $inventory->ckotawh }}</td>
                                        <td class="py-2 px-3 text-muted">{{ $inventory->cnmwh }}</td>
                                        <td class="py-2 px-3 text-end fw-semibold">{{ number_format($inventory->nqty) }}
                                        </td>
                                        <td class="py-2 px-3">
                                            @if ($inventory->nqty >= 250)
                                                <span class="badge bg-success"
                                                    style="font-size: 0.75rem;">{{ __('Normal') }}</span>
                                            @elseif($inventory->nqty >= 150)
                                                <span class="badge bg-warning"
                                                    style="font-size: 0.75rem;">{{ __('Low') }}</span>
                                            @else
                                                <span class="badge bg-danger"
                                                    style="font-size: 0.75rem;">{{ __('Critical') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="3" class="py-2 px-3 fw-bold">{{ __('Total') }}</td>
                                    <td class="py-2 px-3 text-end fw-bold">{{ $onHandPalletTotal }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm"
                        data-bs-dismiss="modal">{{ __('Close') }}</button>
                    <button type="button" class="btn btn-primary btn-sm">
                        <i class="bi bi-download me-1"></i>{{ __('Export') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    {{-- In-Transit Detail Modal --}}
    <div class="modal fade" id="inTransitDetailModal" tabindex="-1" aria-labelledby="inTransitDetailModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 95vw;">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-0" id="inTransitDetailModalLabel">{{ __('In-Transit Pallet Details') }}
                        </h5>
                        <small class="text-muted">{{ __('Current shipments in transit') }}</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0 sticky-table-modal" style="font-size: 0.85rem;">
                            <thead class="table-light">
                                <tr>
                                    <th class="py-2 px-3 sticky-col-1">{{ __('Transaction ID') }}</th>
                                    <th class="py-2 px-3 sticky-col-2">{{ __('Sender') }}</th>
                                    <th class="py-2 px-3">{{ __('Sender City') }}</th>
                                    <th class="py-2 px-3">{{ __('Receiver') }}</th>
                                    <th class="py-2 px-3">{{ __('Receiver City') }}</th>
                                    <th class="py-2 px-3">{{ __('Pallet Type') }}</th>
                                    <th class="py-2 px-3 text-center">{{ __('Quantity') }}</th>
                                    <th class="py-2 px-3">{{ __('ETD') }}</th>
                                    <th class="py-2 px-3">{{ __('ETA') }}</th>
                                    <th class="py-2 px-3">{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Incoming subheader --}}
                                <tr class="table-success">
                                    <td colspan="10" class="py-2 px-3 fw-semibold">
                                        <i class="bi bi-arrow-down-circle me-1"></i>{{ __('Incoming Shipments') }}
                                        ({{ $incomingTotal }} {{ __('Pallets') }})
                                    </td>
                                </tr>
                                @foreach ($rowdataincoming as $incoming)
                                    <tr>
                                        <td class="py-2 px-3 fw-semibold text-primary sticky-col-1">{{ $incoming->cnosj }}</td>
                                        <td class="py-2 px-3 sticky-col-2">{{ $incoming->ccust_from }}</td>
                                        <td class="py-2 px-3 text-muted">{{ $incoming->ccity_from }}</td>
                                        <td class="py-2 px-3">{{ $incoming->ccust_to }}</td>
                                        <td class="py-2 px-3 text-muted">{{ $incoming->ccity_to }}</td>
                                        <td class="py-2 px-3"><span class="badge bg-secondary"
                                                style="font-size: 0.75rem;">{{ $incoming->cbasic }}</span></td>
                                        <td class="py-2 px-3 text-center fw-semibold">{{ number_format($incoming->nqty) }}
                                        </td>
                                        <td class="py-2 px-3 text-muted">
                                            {{ \Carbon\Carbon::parse($incoming->dtgl_ship)->format('d/m/y') }}</td>
                                        <td class="py-2 px-3 text-muted">
                                            {{ $incoming->estimated_eta !== '-' ? \Carbon\Carbon::parse($incoming->estimated_eta)->format('d/m/y') : '-' }}
                                        </td>
                                        <td class="py-2 px-3">
                                            @if ($incoming->estimated_eta >= \Carbon\Carbon::now()->format('Y-m-d'))
                                                <span class="badge bg-primary" style="font-size: 0.75rem;"><i
                                                        class="bi bi-truck"></i> {{ __('In Transit') }}</span>
                                            @else
                                                <span class="badge bg-warning" style="font-size: 0.75rem;"><i
                                                        class="bi bi-exclamation-triangle"></i> {{ __('Delayed') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach

                                {{-- Outgoing subheader --}}
                                <tr class="table-danger">
                                    <td colspan="10" class="py-2 px-3 fw-semibold">
                                        <i class="bi bi-arrow-up-circle me-1"></i>{{ __('Outgoing Shipments') }}
                                        ({{ $outgoingTotal }} {{ __('Pallets') }})
                                    </td>
                                </tr>
                                @foreach ($rowdataoutgoing as $outgoing)
                                    <tr>
                                        <td class="py-2 px-3 fw-semibold text-primary sticky-col-1">{{ $outgoing->cnosj }}</td>
                                        <td class="py-2 px-3 sticky-col-2">{{ $outgoing->ccust_from }}</td>
                                        <td class="py-2 px-3 text-muted">{{ $outgoing->ccity_from }}</td>
                                        <td class="py-2 px-3">{{ $outgoing->ccust_to }}</td>
                                        <td class="py-2 px-3 text-muted">{{ $outgoing->ccity_to }}</td>
                                        <td class="py-2 px-3"><span class="badge bg-secondary"
                                                style="font-size: 0.75rem;">{{ $outgoing->cbasic }}</span></td>
                                        <td class="py-2 px-3 text-center fw-semibold">{{ number_format($outgoing->nqty) }}
                                        </td>
                                        <td class="py-2 px-3 text-muted">
                                            {{ \Carbon\Carbon::parse($outgoing->dtgl_ship)->format('d/m/y') }}</td>
                                        <td class="py-2 px-3 text-muted">
                                            {{ $outgoing->estimated_eta !== '-' ? \Carbon\Carbon::parse($outgoing->estimated_eta)->format('d/m/y') : '-' }}
                                        </td>
                                        <td class="py-2 px-3">
                                            @if ($outgoing->estimated_eta >= \Carbon\Carbon::now()->format('Y-m-d'))
                                                <span class="badge bg-primary" style="font-size: 0.75rem;"><i
                                                        class="bi bi-truck"></i> {{ __('In Transit') }}</span>
                                            @else
                                                <span class="badge bg-warning" style="font-size: 0.75rem;"><i
                                                        class="bi bi-exclamation-triangle"></i> {{ __('Delayed') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm"
                        data-bs-dismiss="modal">{{ __('Close') }}</button>
                    <button type="button" class="btn btn-primary btn-sm">
                        <i class="bi bi-download me-1"></i>{{ __('Export') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    {{-- Customer-Liable Detail Modal --}}
    <div class="modal fade" id="customerLiableDetailModal" tabindex="-1"
        aria-labelledby="customerLiableDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-0" id="customerLiableDetailModalLabel">
                            {{ __('Customer-Liable Pallet Details') }}</h5>
                        <small class="text-muted">{{ __('Pallets currently with customers') }}</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0" style="font-size: 0.85rem;">
                            <thead class="table-light">
                                <tr>
                                    <th class="py-2 px-3">{{ __('Customer') }}</th>
                                    <th class="py-2 px-3">{{ __('Location') }}</th>
                                    <th class="py-2 px-3 text-center">{{ __('Quantity') }}</th>
                                    <th class="py-2 px-3">{{ __('Delivery Date') }}</th>
                                    <th class="py-2 px-3">{{ __('Due Date') }}</th>
                                    <th class="py-2 px-3">{{ __('Days Outstanding') }}</th>
                                    <th class="py-2 px-3">{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ([['customer' => 'PT Unilever Indonesia', 'location' => 'Cikarang', 'qty' => 320, 'delivery' => '15/12/24', 'due' => '15/01/25', 'days' => 14, 'status' => 'Overdue'], ['customer' => 'PT Nestle Indonesia', 'location' => 'Sidoarjo', 'qty' => 285, 'delivery' => '05/01/25', 'due' => '05/02/25', 'days' => 7, 'status' => 'On Track'], ['customer' => 'PT Garuda Food', 'location' => 'Bandung', 'qty' => 240, 'delivery' => '10/01/25', 'due' => '10/02/25', 'days' => 2, 'status' => 'On Track'], ['customer' => 'PT Frisian Flag', 'location' => 'Bekasi', 'qty' => 195, 'delivery' => '20/12/24', 'due' => '20/01/25', 'days' => 9, 'status' => 'Overdue'], ['customer' => 'PT Kapal Api', 'location' => 'Semarang', 'qty' => 145, 'delivery' => '12/01/25', 'due' => '12/02/25', 'days' => 5, 'status' => 'On Track'], ['customer' => 'PT Wings Group', 'location' => 'Surabaya', 'qty' => 100, 'delivery' => '15/01/25', 'due' => '15/02/25', 'days' => 8, 'status' => 'On Track']] as $customer)
                                    <tr>
                                        <td class="py-2 px-3 fw-semibold">{{ $customer['customer'] }}</td>
                                        <td class="py-2 px-3 text-muted">{{ $customer['location'] }}</td>
                                        <td class="py-2 px-3 text-center fw-semibold">
                                            {{ number_format($customer['qty']) }}</td>
                                        <td class="py-2 px-3 text-muted">{{ $customer['delivery'] }}</td>
                                        <td class="py-2 px-3 text-muted">{{ $customer['due'] }}</td>
                                        <td class="py-2 px-3 text-center">{{ $customer['days'] }} {{ __('days') }}
                                        </td>
                                        <td class="py-2 px-3">
                                            @if ($customer['status'] === 'Overdue')
                                                <span class="badge bg-danger" style="font-size: 0.75rem;">
                                                    <i class="bi bi-exclamation-circle"></i> {{ __('Overdue') }}
                                                </span>
                                            @else
                                                <span class="badge bg-success" style="font-size: 0.75rem;">
                                                    <i class="bi bi-check-circle"></i> {{ __('On Track') }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="2" class="py-2 px-3 fw-bold">{{ __('Total') }}</td>
                                    <td class="py-2 px-3 text-center fw-bold">1,285</td>
                                    <td colspan="4"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm"
                        data-bs-dismiss="modal">{{ __('Close') }}</button>
                    <button type="button" class="btn btn-primary btn-sm">
                        <i class="bi bi-download me-1"></i>{{ __('Export') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Dashboard Content --}}
    <div class="dashboard-section">
        <div class="row g-2">
            {{-- Left Column - Charts --}}
            <div class="col-12 col-xl-8">
                {{-- Stock Forecast Chart --}}
                <div class="card border shadow-sm mb-2" id="stockForecastCard" style="height: 267px;">
                    <div class="card-header bg-white border-bottom py-1 px-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <h6 class="mb-0 fw-semibold d-flex align-items-center gap-2" style="font-size: 0.95rem;">
                                    <i class="bi bi-graph-up" style="color: #0d6efd; font-size: 0.9rem;"></i>
                                    {{ __('Daily Stock Movement (14 Days)') }}
                                    <i class="bi bi-info-circle text-secondary" style="font-size: 0.75rem; cursor: help;"
                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="{{ __('Historical and forecasted daily stock levels showing inventory trends and predictions') }}"></i>
                                </h6>
                            </div>
                            <div class="btn-group btn-group-sm" role="group" aria-label="Pallet type filter">
                                @foreach ($stockChartData as $type => $data)
                                    @php
                                        $id = 'pallet' . str_replace(' ', '', $type);
                                    @endphp
                                    <input type="radio" class="btn-check" name="palletType" id="{{ $id }}"
                                        value="{{ $type }}" autocomplete="off"
                                        {{ $loop->first ? 'checked' : '' }}>
                                    <label class="btn btn-outline-success" for="{{ $id }}"
                                        style="font-size: 0.9rem; padding: 0.15rem 0.5rem;">
                                        {{ $type }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-2 d-flex flex-column" style="flex: 1; min-height: 0;">
                        <div style="flex: 1; position: relative;">
                            <canvas id="stockForecastChart"></canvas>
                        </div>
                    </div>
                </div>

                {{-- Recent Pallet Movements --}}
                <div class="card border shadow-sm" style="height: 253px;">
                    <div class="card-header bg-white border-bottom py-1 px-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-semibold d-flex align-items-center gap-2" style="font-size: 0.95rem;">
                                <i class="bi bi-arrow-left-right" style="color: #198754; font-size: 0.9rem;"></i>
                                {{ __('Recent Pallet Movements') }}
                                <i class="bi bi-info-circle text-secondary" style="font-size: 0.75rem; cursor: help;"
                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="{{ __('Latest pallet transactions including deliveries, returns, and transfers between locations') }}"></i>
                            </h6>
                            <a href="{{ route('orderreturn.order_return_monitoring') }}"
                                class="text-decoration-none fw-medium"
                                style="font-size: 0.8rem; color: #146C43;">{{ __('View All') }}</a>
                        </div>
                    </div>
                    <div class="card-body p-0" style="overflow-y: auto; max-height: calc(100% - 41px);">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0 sticky-table" style="font-size: 0.85rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th class="py-2 px-2 sticky-col-1" style="font-weight: 600;">{{ __('TYPE') }}</th>
                                        <th class="py-2 px-2 sticky-col-2" style="font-weight: 600;">{{ __('DOC. NUMBER') }}</th>
                                        <th class="py-2 px-2" style="font-weight: 600;">{{ __('ETD') }}</th>
                                        <th class="py-2 px-2" style="font-weight: 600;">{{ __('ETA') }}</th>
                                        <th class="py-2 px-2" style="font-weight: 600;">{{ __('SENDER') }}</th>
                                        <th class="py-2 px-2" style="font-weight: 600;">{{ __('Snd Addr') }}</th>
                                        <th class="py-2 px-2" style="font-weight: 600;">{{ __('RECEIVER') }}</th>
                                        <th class="py-2 px-2" style="font-weight: 600;">{{ __('Rcv Addr') }}</th>
                                        <th class="py-2 px-2" style="font-weight: 600;">{{ __('LOGISTIC') }}</th>
                                        <th class="py-2 px-2 text-center" style="font-weight: 600;">{{ __('QTY') }}
                                        </th>
                                        <th class="py-2 px-2 text-center" style="font-weight: 600;">{{ __('STATUS') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($recentMovements->isEmpty())
                                        <tr>
                                            <td colspan="9" class="text-center py-4 text-muted">
                                                <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                                <p class="mb-0 mt-2">{{ __('No recent movements') }}</p>
                                            </td>
                                        </tr>
                                    @else
                                        @foreach ($recentMovements as $movement)
                                            <tr class="clickable-row"
                                                onclick="window.location='{{ route('delivery.show', $movement->uuid) }}'"
                                                style="cursor: pointer;">
                                                <td class="py-2 px-2 sticky-col-1">
                                                    @if ($movement->type === 'DN')
                                                        <span class="badge bg-danger">DN</span>
                                                    @else
                                                        <span class="badge bg-success">GR</span>
                                                    @endif
                                                </td>
                                                <td class="py-2 px-2 sticky-col-2">
                                                    <span class="fw-semibold text-primary">{{ $movement->doc_no }}</span>
                                                </td>
                                                <td class="py-2 px-2 text-muted">{{ $movement->etd }}</td>
                                                <td class="py-2 px-2 text-muted">{{ $movement->eta }}</td>
                                                <td class="py-2 px-2">{{ $movement->sender }}</td>
                                                <td class="py-2 px-2">{{ $movement->sender_city }}</td>
                                                <td class="py-2 px-2">{{ $movement->receiver }}</td>
                                                <td class="py-2 px-2">{{ $movement->receiver_city }}</td>
                                                <td class="py-2 px-2 text-muted">{{ $movement->logistic ?? '-' }}</td>
                                                <td class="py-2 px-2 text-center fw-semibold">
                                                    {{ number_format($movement->qty) }}
                                                </td>
                                                <td class="py-2 px-2 text-center">
                                                    @if ($movement->status === 'In Transit')
                                                        <span class="badge bg-primary" style="font-size: 0.75rem;">
                                                            <i class="bi bi-truck"></i> {{ __('In Transit') }}
                                                        </span>
                                                    @elseif($movement->status === 'Delivered')
                                                        <span class="badge bg-success" style="font-size: 0.75rem;">
                                                            <i class="bi bi-check-circle"></i> {{ __('Delivered') }}
                                                        </span>
                                                    @else
                                                        <span class="badge bg-warning" style="font-size: 0.75rem;">
                                                            <i class="bi bi-clock"></i> {{ __('Pending') }}
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column - Sidebar --}}
            <div class="col-12 col-xl-4">
                {{-- Alerts & Exceptions --}}
                <div class="card border shadow-sm mb-2 d-flex flex-column" id="alertsCard">
                    <div class="card-header bg-white border-bottom py-1 px-2 flex-shrink-0">
                        <h6 class="mb-0 fw-semibold d-flex align-items-center gap-2" style="font-size: 0.95rem;">
                            <i class="bi bi-exclamation-triangle" style="color: #ffc107; font-size: 0.9rem;"></i>
                            {{ __('Alerts & Exceptions') }}
                            <i class="bi bi-info-circle text-secondary" style="font-size: 0.75rem; cursor: help;"
                                data-bs-toggle="tooltip" data-bs-placement="top"
                                title="{{ __('Alerts for expired agreements, deliveries that have arrived but goods receipt has not been created, and monthly usage awaiting approval') }}">
                            </i>
                        </h6>
                    </div>
                    <div class="card-body p-2 flex-grow-1" style="min-height: 0; overflow: hidden;">
                        <div class="d-flex flex-column gap-1 alert-scroll" style="height: 100%; overflow-y: auto; overflow-x: hidden; padding-right: 2px;">
                            @forelse($alerts as $alert)
                                @php
                                    $border = 'info';
                                    $badge  = 'bg-info';
                                    $label  = 'Low';

                                    if($alert['title'] === 'Expired Network Agreement' || $alert['title'] === 'Expired Direct Agreement'){
                                        $border = 'danger';
                                        $badge  = 'bg-danger';
                                        $label  = 'High';
                                    }

                                    if($alert['title'] === 'Missing Goods Receipt'){
                                        $border = 'warning';
                                        $badge  = 'bg-warning text-dark';
                                        $label  = 'Medium';
                                    }
                                @endphp

                                <a href="{{ $alert['url'] }}" class="text-decoration-none text-dark alert-item-link">
                                    <div class="p-2 border-start border-{{ $border }} border-3 bg-light rounded alert-item alert-item-{{ $border }}">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold" style="font-size: 0.8rem;">
                                                    {{ $alert['title'] }}
                                                </div>
                                                <div class="text-muted" style="font-size: 0.7rem;">
                                                    {{ $alert['text'] }}
                                                </div>
                                            </div>
                                            <i class="bi bi-chevron-right text-muted ms-2 flex-shrink-0 alert-chevron" style="font-size: 0.75rem; margin-top: 2px;"></i>
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <div class="d-flex justify-content-center align-items-center h-100 text-muted" style="font-size: 0.8rem;">
                                    {{ __('No alerts available') }}
                                </div>
                            @endempty
                        </div>
                    </div>
                </div>

                {{-- Billing Overview --}}
                @if (Auth::user()->customer_role == 'finance' || Auth::user()->customer_role == 'purchasing')
                    <div class="card border shadow-sm mb-2">
                        <div class="card-header bg-white border-bottom py-1 px-2">
                            <h6 class="mb-0 fw-semibold d-flex align-items-center gap-2" style="font-size: 0.95rem;">
                                <i class="bi bi-currency-dollar" style="color: #198754; font-size: 0.9rem;"></i>
                                {{ __('Billing Overview (6 Months)') }}
                                <i class="bi bi-info-circle text-secondary" style="font-size: 0.75rem; cursor: help;"
                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="{{ __('Monthly billing trends showing pallet rental charges over the past 6 months') }}"></i>
                            </h6>
                        </div>
                        <div class="card-body p-2">
                            <div style="height: 220px; position: relative;">
                                <canvas id="billingChart"></canvas>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Welcome Modal (First Login) --}}
    @if (Auth::user()->lfirstlogin)
        <div class="modal fade" id="welcomeModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
            data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 520px;">
                <div class="modal-content border-0 shadow-lg overflow-hidden"
                    style="border-radius: 16px; font-family: 'Figtree', sans-serif;">

                    {{-- ── Top: dark green brand panel ── --}}
                    <div
                        style="background: linear-gradient(160deg, #0f2818 0%, #14532d 45%, #1a6b3c 100%); padding: 2rem 2rem 0; position: relative; overflow: hidden; text-align: center;">
                        {{-- Grid texture --}}
                        <div
                            style="
                    position: absolute; inset: 0; pointer-events: none; background-image: linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
                        linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
                    background-size: 28px 28px;
                ">
                        </div>

                        {{-- Glow --}}
                        <div
                            style="
                    position: absolute; bottom: -60px; left: 50%; transform: translateX(-50%);
                    width: 340px; height: 340px; pointer-events: none;
                    background: radial-gradient(ellipse, rgba(74,222,128,0.10) 0%, transparent 65%);
                ">
                        </div>

                        {{-- Brand logo + name --}}
                        <div
                            style="position: relative; z-index: 2; display: flex; align-items: center; justify-content: center; gap: 0.6rem; margin-bottom: 1rem;">
                            @if (file_exists(public_path('images/logo.png')))
                                <img src="{{ asset('images/logo.png') }}" alt="Logo"
                                    style="width: 28px; height: 28px; object-fit: contain; filter: brightness(0) invert(1); flex-shrink: 0;">
                            @endif
                            <div style="text-align: left;">
                                <div
                                    style="font-size: 1rem; font-weight: 700; color: #fff; letter-spacing: -0.01em; line-height: 1;">
                                    YANAPAL</div>
                                <div style="font-size: 0.65rem; color: #bbf7d0; font-weight: 400; letter-spacing: 0.04em;">
                                    Your Pal for Pallet Pooling</div>
                            </div>
                        </div>

                        {{-- Mascot --}}
                        <div style="position: relative; z-index: 2;">
                            <img src="{{ asset('images/yanapal-sip.png') }}" alt="Yanapal Mascot"
                                style="
                            width: 160px; height: 160px;
                            object-fit: contain; object-position: bottom;
                            filter: drop-shadow(0 8px 20px rgba(0,0,0,0`.35));
                            animation: welcomeFloat 3.5s ease-in-out infinite;
                            display: block; margin: 0 auto;
                        ">
                        </div>
                    </div>

                    {{-- ── Bottom: white content area ── --}}
                    <div style="background: #fff; padding: 1.75rem 2rem 2rem; text-align: center;">

                        {{-- Greeting --}}
                        <div
                            style="
                    display: inline-block;
                    font-size: 0.65rem; font-weight: 700; letter-spacing: 0.12em;
                    text-transform: uppercase; color: #16a34a;
                    background: #f0fdf4; border: 1px solid #bbf7d0;
                    border-radius: 999px; padding: 0.2rem 0.75rem;
                    margin-bottom: 0.75rem;
                ">
                            Thanks for joining us 🤝</div>

                        <h4
                            style="font-size: 1.35rem; font-weight: 700; color: #0f172a; letter-spacing: -0.02em; margin: 0 0 0.4rem;">
                            Welcome aboard, <span style="color: #16a34a;">{{ Auth::user()->name }}</span>!
                        </h4>
                        <p style="font-size: 0.85rem; color: #64748b; margin: 0 0 1.25rem; line-height: 1.6;">
                            Thank you for trusting YANAPAL with your pallet operations. Let’s take a quick guided tour to
                            get you familiar with your dashboard.
                        </p>

                        {{-- Feature badges --}}
                        <div
                            style="display: flex; flex-wrap: wrap; gap: 0.35rem; justify-content: center; margin-bottom: 1.5rem;">
                            <span
                                style="font-size: 0.65rem; font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase; color: #15803d; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 999px; padding: 0.2rem 0.6rem;">📦
                                On-Hand Inventory</span>
                            <span
                                style="font-size: 0.65rem; font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase; color: #15803d; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 999px; padding: 0.2rem 0.6rem;">🚚
                                In-Transit Monitoring</span>
                            <span
                                style="font-size: 0.65rem; font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase; color: #15803d; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 999px; padding: 0.2rem 0.6rem;">📈
                                Stock Forecast</span>
                            <span
                                style="font-size: 0.65rem; font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase; color: #15803d; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 999px; padding: 0.2rem 0.6rem;">💵 Manage Billing</span>
                            <span
                                style="font-size: 0.65rem; font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase; color: #15803d; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 999px; padding: 0.2rem 0.6rem;">⚠️
                                Alerts & Exceptions</span>
                        </div>

                        {{-- Buttons --}}
                        <div style="display: flex; gap: 0.6rem;">
                            <button type="button" id="welcomeSkipBtn"
                                style="
                            flex: 1; height: 42px;
                            font-family: inherit; font-size: 0.85rem; font-weight: 600;
                            color: #64748b; background: #f8fafc;
                            border: 1.5px solid #e2e8f0; border-radius: 8px;
                            cursor: pointer; transition: background 0.15s, border-color 0.15s;
                        "
                                onmouseover="this.style.background='#f1f5f9'; this.style.borderColor='#cbd5e1';"
                                onmouseout="this.style.background='#f8fafc'; this.style.borderColor='#e2e8f0';">
                                Skip for now
                            </button>
                            <button type="button" id="welcomeStartTourBtn"
                                style="
                            flex: 2; height: 42px;
                            font-family: inherit; font-size: 0.875rem; font-weight: 700;
                            color: #fff; background: #16a34a;
                            border: none; border-radius: 8px;
                            cursor: pointer; letter-spacing: 0.01em;
                            display: flex; align-items: center; justify-content: center; gap: 0.4rem;
                            transition: background 0.15s, box-shadow 0.15s;
                        "
                                onmouseover="this.style.background='#15803d'; this.style.boxShadow='0 4px 16px rgba(22,163,74,0.3)';"
                                onmouseout="this.style.background='#16a34a'; this.style.boxShadow='none';">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <polygon points="5 3 19 12 5 21 5 3" />
                                </svg>
                                Start Tour
                            </button>
                        </div>

                        <p style="font-size: 0.72rem; color: #94a3b8; margin: 0.85rem 0 0;">
                            You can always restart the tour from the <strong style="color:#64748b;">Take Tour</strong>
                            button in the header.
                        </p>
                    </div>

                </div>
            </div>
        </div>

        {{-- Mascot float animation --}}
        <style>
            @keyframes welcomeFloat {

                0%,
                100% {
                    transform: translateY(0);
                }

                50% {
                    transform: translateY(-8px);
                }
            }
        </style>
    @endif
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

        .btn-sm {
            font-size: 0.85rem;
        }

        /* Fix warning button text color */
        .btn-warning {
            color: white !important;
        }

        /* Table hover effect */
        .table-hover tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }

        /* Clickable table row hover effect */
        .clickable-row:hover {
            background-color: #f1f8f4 !important;
            transition: background-color 0.15s ease;
        }

        /* Responsive */
        @media (max-width: 767.98px) {
            .table {
                font-size: 0.75rem;
            }

            .table th,
            .table td {
                padding: 0.5rem;
            }
        }

        /* Alert item hover styling */
        .alert-item-link {
            display: block;
        }

        .alert-item {
            transition: background-color 0.15s ease, border-left-width 0.1s ease;
            cursor: pointer;
        }

        .alert-item-link:hover .alert-item-danger {
            background-color: rgba(220, 53, 69, 0.06) !important;
        }

        .alert-item-link:hover .alert-item-warning {
            background-color: rgba(255, 193, 7, 0.08) !important;
        }

        .alert-item-link:hover .alert-item-info {
            background-color: rgba(13, 202, 240, 0.06) !important;
        }

        .alert-chevron {
            opacity: 0;
            transition: opacity 0.15s ease, transform 0.15s ease;
        }

        .alert-item-link:hover .alert-chevron {
            opacity: 1;
            transform: translateX(2px);
        }

        /* Custom scrollbar for alert list */
        .alert-scroll::-webkit-scrollbar {
            width: 4px;
        }

        .alert-scroll::-webkit-scrollbar-track {
            background: transparent;
        }

        .alert-scroll::-webkit-scrollbar-thumb {
            background-color: #dee2e6;
            border-radius: 4px;
        }

        .alert-scroll::-webkit-scrollbar-thumb:hover {
            background-color: #adb5bd;
        }

        /* Sticky columns for Recent Pallet Movements table */
        .sticky-table .sticky-col-1,
        .sticky-table .sticky-col-2 {
            position: sticky;
            background-color: #fff;
            z-index: 2;
        }

        .sticky-table .sticky-col-1 {
            left: 0;
            min-width: 55px;
        }

        .sticky-table .sticky-col-2 {
            left: 55px;
            min-width: 110px;
            box-shadow: 2px 0 6px -2px rgba(0, 0, 0, 0.12);
        }

        /* Sticky header cells */
        .sticky-table thead .sticky-col-1,
        .sticky-table thead .sticky-col-2 {
            background-color: #f8f9fa;
            z-index: 3;
        }

        /* Hover state for sticky cols */
        .sticky-table tbody tr:hover .sticky-col-1,
        .sticky-table tbody tr:hover .sticky-col-2 {
            background-color: #f1f8f4;
        }

        /* Sticky columns for In-Transit modal table */
        .sticky-table-modal .sticky-col-1,
        .sticky-table-modal .sticky-col-2 {
            position: sticky;
            background-color: #fff;
            z-index: 2;
        }

        .sticky-table-modal .sticky-col-1 {
            left: 0;
            min-width: 110px;
        }

        .sticky-table-modal .sticky-col-2 {
            left: 110px;
            min-width: 130px;
            box-shadow: 2px 0 6px -2px rgba(0, 0, 0, 0.12);
        }

        /* Sticky header cells */
        .sticky-table-modal thead .sticky-col-1,
        .sticky-table-modal thead .sticky-col-2 {
            background-color: #f8f9fa;
            z-index: 3;
        }

        /* Hover state */
        .sticky-table-modal tbody tr:hover .sticky-col-1,
        .sticky-table-modal tbody tr:hover .sticky-col-2 {
            background-color: #f1f8f4;
        }

        /* Override hover for subheader rows */
        .sticky-table-modal tbody tr.table-success:hover td,
        .sticky-table-modal tbody tr.table-danger:hover td {
            background-color: inherit;
        }
        
    </style>
@endpush

@push('scripts')
    <script>
        // Chart.js Global Configuration
        Chart.defaults.font.family = "'Figtree', sans-serif";
        Chart.defaults.font.size = 11;
        Chart.defaults.color = '#6c757d';

        // Stock & Forecast Chart - 14 Days with Stock In/Out
        const stockForecastCtx = document.getElementById('stockForecastChart').getContext('2d');

        // Get pallet type data from controller and default All type
        const palletTypeData = @json($stockChartData);
        let currentPalletType = Object.keys(palletTypeData)[0];

        // Get data from backend (PHP)
        // Labels for all 14 days (7 actual + 7 forecast)
        const baseLabels = palletTypeData[currentPalletType].labels;

        // Clone labels actual (7 hari)
        const chartLabels = [...baseLabels];

        const today = new Date();
        const lastDate = new Date(today);

        // Add next 7 days for forecast labels
        for (let i = 1; i <= 7; i++) {
            const nextDate = new Date(lastDate);
            nextDate.setDate(lastDate.getDate() + i);

            const weekday = nextDate.toLocaleDateString('en-US', {
                weekday: 'short'
            });
            const day = nextDate.toLocaleDateString('en-US', {
                day: '2-digit'
            });

            chartLabels.push(`${weekday} ${day}`);
        }

        // Data for actual and forecast stock
        const actualRaw = palletTypeData[currentPalletType].saldo;
        const forecastRaw = palletTypeData[currentPalletType].forecastSaldo;

        // Actual: fill 7 last days with null
        const actualStockArray = actualRaw.concat(Array(7).fill(null));

        // Forecast: fill 6 first days with null
        const lastActual = actualRaw[actualRaw.length - 1];

        // Forecast: 6 null + last actual + forecast
        const forecastStockArray =
            Array(6).fill(null)
            .concat([lastActual])
            .concat(forecastRaw);

        // Index of the last actual data point (today) within the combined labels
        const lastActualIndex = baseLabels.length - 1;

        const stockInArray = palletTypeData[currentPalletType].stockIn;
        const stockOutArray = palletTypeData[currentPalletType].stockOut;

        // Stock In/Out for all 7 days
        const stockInData = stockInArray;
        const stockOutData = stockOutArray;

        const stockForecastChart = new Chart(stockForecastCtx, {
            type: 'line',
            data: {
                labels: chartLabels,
                datasets: [{
                    label: '{{ __('Actual Stock') }}',
                    data: actualStockArray,
                    borderColor: '#198754',
                    backgroundColor: 'rgba(25, 135, 84, 0.08)',
                    borderWidth: 3,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: '#198754',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    spanGaps: false
                }, {
                    label: '{{ __('Forecast') }}',
                    data: forecastStockArray,
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.08)',
                    borderWidth: 3,
                    borderDash: [8, 4],
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: '#0d6efd',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    spanGaps: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'nearest',
                    intersect: false
                },
                hover: {
                    mode: 'nearest',
                    intersect: false
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        align: 'end',
                        labels: {
                            padding: 15,
                            font: {
                                size: 11
                            },
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.85)',
                        padding: 12,
                        titleFont: {
                            size: 12,
                            weight: '600'
                        },
                        bodyFont: {
                            size: 11
                        },
                        // Filter to avoid showing the forecast dataset label on today's (last actual) index
                        filter: function(tooltipItem) {
                            // Hide the forecast dataset (datasetIndex 1) when hovering the last actual index
                            if (tooltipItem.datasetIndex === 1 && tooltipItem.dataIndex === lastActualIndex) {
                                return false;
                            }
                            return true;
                        },
                        callbacks: {
                            title: function(context) {
                                return context[0].label;
                            },
                            // Keep a simple per-dataset label (value) so tooltip still shows dataset values
                            label: function(context) {
                                const val = context.parsed && context.parsed.y;
                                if (val === null || typeof val === 'undefined') return '';
                                return `${context.dataset.label}: ${new Intl.NumberFormat('en-US').format(Math.round(val))} {{ __('pallets') }}`;
                            },
                            // Show the Stock In / Stock Out / Ending Balance details once in the footer
                            footer: function(context) {
                                if (!context || context.length === 0) return '';
                                const item = context[0];
                                const dataIndex = item.dataIndex;
                                const typeData = palletTypeData[currentPalletType];
                                const endingBalance = item.parsed && item.parsed.y;

                                if (endingBalance === null || typeof endingBalance === 'undefined') return '';

                                const isForecast = dataIndex >= 7;

                                const stockIn = isForecast ?
                                    typeData.forecastIn[dataIndex - 7] :
                                    typeData.stockIn[dataIndex];

                                const stockOut = isForecast ?
                                    typeData.forecastOut[dataIndex - 7] :
                                    typeData.stockOut[dataIndex];

                                const inLabel = isForecast ? '{{ __('Estimated In') }}' :
                                    '{{ __('Stock In') }}';
                                const outLabel = isForecast ? '{{ __('Estimated Out') }}' :
                                    '{{ __('Stock Out') }}';

                                return [
                                    `${inLabel}: ${new Intl.NumberFormat('en-US').format(stockIn)} {{ __('pallets') }}`,
                                    `${outLabel}: ${new Intl.NumberFormat('en-US').format(stockOut)} {{ __('pallets') }}`,
                                    `{{ __('Ending Balance') }}: ${new Intl.NumberFormat('en-US').format(Math.round(endingBalance))} {{ __('pallets') }}`
                                ];
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        ticks: {
                            callback: function(value) {
                                if (value >= 1000) {
                                    return (value / 1000) + 'k';
                                }
                                return value;
                            },
                            font: {
                                size: 10
                            }
                        },
                        grid: {
                            color: '#f0f0f0',
                            drawBorder: false
                        },
                        border: {
                            display: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 10
                            }
                        },
                        border: {
                            display: false
                        }
                    }
                }
            }
        });

        // Function to update chart based on selected pallet type
        function updateChartByPalletType(palletType) {
            currentPalletType = palletType;
            const typeData = palletTypeData[palletType];

            stockForecastChart.data.labels = chartLabels;

            stockForecastChart.data.datasets[0].data =
                typeData.saldo.concat(Array(7).fill(null));

            const lastActual = typeData.saldo[typeData.saldo.length - 1];

            stockForecastChart.data.datasets[1].data =
                Array(6).fill(null)
                .concat([lastActual])
                .concat(typeData.forecastSaldo);

            stockForecastChart.update();
        }


        // Event listeners for pallet type buttons
        document.addEventListener('DOMContentLoaded', function() {
            const palletTypeButtons = document.querySelectorAll('input[name="palletType"]');
            palletTypeButtons.forEach(button => {
                button.addEventListener('change', function() {
                    updateChartByPalletType(this.value);
                });
            });
        });
        
        @if(in_array(Auth::user()->customer_role, ['finance', 'purchasing']))
        // Billing Chart
        const billingLabels = @json($billings['labels']);
        const billingData = @json($billings['data']);

        const billingCtx = document.getElementById('billingChart').getContext('2d');

        const minValue = Math.min(...billingData);
        const maxValue = Math.max(...billingData);

        const minY = Math.floor(minValue / 1000000) * 1000000;
        const maxY = Math.ceil(maxValue / 1000000) * 1000000;

        const billingChart = new Chart(billingCtx, {
            type: 'bar',
            data: {
                labels: billingLabels,
                datasets: [{
                    label: '{{ __('Billed Amount') }}',
                    data: billingData,
                    backgroundColor: 'rgba(25, 135, 84, 0.8)',
                    borderColor: '#198754',
                    borderWidth: 2,
                    borderRadius: 6,
                    barThickness: 32
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.85)',
                        padding: 12,
                        titleFont: {
                            size: 12,
                            weight: '600'
                        },
                        bodyFont: {
                            size: 11
                        },
                        callbacks: {
                            label: function(context) {
                                return 'Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                            },
                            font: {
                                size: 10
                            }
                        },
                        grid: {
                            color: '#f0f0f0',
                            drawBorder: false
                        },
                        border: {
                            display: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 11
                            }
                        },
                        border: {
                            display: false
                        }
                    }
                }
            }
        });
        @endif

        // ============================================================================
        // DASHBOARD TOUR with Driver.js - Interactive Modal Version
        // ============================================================================
        window.startProductTour = function() {
            const driver = window.driver.js.driver;

            const driverObj = driver({
                showProgress: true,
                showButtons: ['next', 'previous', 'close'],
                steps: [{
                        popover: {
                            title: '👋 {{ __('Welcome to Your Dashboard!') }}',
                            description: '{{ __('Let me show you around. This interactive tour takes about 3 minutes. You can exit anytime by pressing ESC or clicking outside.') }}'
                        }
                    },
                    {
                        element: '.dashboard-section:first-child',
                        popover: {
                            title: '⚡ {{ __('Quick Actions') }}',
                            description: '{{ __('These buttons give you instant access to your most common tasks like creating deliveries, requesting orders, and monitoring invoices.') }}',
                            side: 'bottom',
                            align: 'start'
                        }
                    },
                    {
                        element: '.col-4.col-md-4:nth-child(1) .card',
                        popover: {
                            title: '📦 {{ __('On-Hand Inventory') }}',
                            description: '{{ __('See all pallets currently in your warehouses') }}. <br><br><strong>💡 {{ __('Interactive Features') }}:</strong><br>• {{ __('Hover over the') }} <i class="bi bi-info-circle"></i> {{ __('icon to see what this metric means') }}<br>• {{ __('the_label_button') }}<span class="badge bg-primary">{{ __('View All') }}</span> {{ __('button shows detailed breakdown') }}<br><br><strong>🎯 {{ __("In the next step, we'll explore the detailed view together!") }}</strong>',
                            side: 'bottom',
                            align: 'start'
                        }
                    },
                    {
                        element: 'button[data-bs-target="#onHandDetailModal"]',
                        popover: {
                            title: '🔍 {{ __('View Detailed Breakdown') }}',
                            description: '<strong>👆 {{ __('Click this') }} "{{ __('View All') }}" {{ __('button now') }}</strong> {{ __('to see the detailed inventory breakdown by location and pallet type') }}.<br><br>{{ __('After the modal opens, click') }} "{{ __('Next') }}" {{ __('to continue the tour inside the modal') }}.',
                            side: 'left',
                            align: 'start'
                        },
                        onHighlightStarted: () => {
                            const viewAllBtn = document.querySelector(
                                'button[data-bs-target="#onHandDetailModal"]');
                            if (viewAllBtn) {
                                viewAllBtn.classList.add('tour-pulse-animation');
                            }
                        },
                        onDeselected: () => {
                            const viewAllBtn = document.querySelector(
                                'button[data-bs-target="#onHandDetailModal"]');
                            if (viewAllBtn) {
                                viewAllBtn.classList.remove('tour-pulse-animation');
                            }
                            // Don't do anything automatic - let user control flow with Next button
                        }
                    },
                    {
                        element: '#onHandDetailModal .modal-header',
                        popover: {
                            title: '📊 {{ __('Inventory Details Modal') }}',
                            description: '{{ __('This modal shows your complete inventory breakdown. You can see quantities by warehouse location, pallet type, and status') }} (Normal/Low/Critical).',
                            side: 'bottom',
                            align: 'start'
                        },
                        onHighlightStarted: () => {
                            const modal = document.getElementById('onHandDetailModal');
                            if (modal && !modal.classList.contains('show')) {
                                const modalInstance = new bootstrap.Modal(modal);
                                modalInstance.show();
                            }
                        }
                    },
                    {
                        element: '#onHandDetailModal tbody tr:first-child',
                        popover: {
                            title: '📋 {{ __('Inventory Row Details') }}',
                            description: '{{ __('Each row shows') }}:<br>• <strong>{{ __('Pallet Type') }}</strong> (PT1210AS, PT1212AS, {{ __('etc') }}.)<br>• <strong>{{ __('Location') }}</strong> ({{ __('City') }})<br>• <strong>{{ __('Warehouse') }}</strong> {{ __('code') }}<br>• <strong>{{ __('Quantity') }}</strong><br>• <strong>{{ __('Status') }}</strong> {{ __('indicator') }}',
                            side: 'bottom',
                            align: 'start'
                        }
                    },
                    {
                        element: '#onHandDetailModal .modal-footer .btn-primary',
                        popover: {
                            title: '📥 {{ __('Export Data') }}',
                            description: '{{ __('You can export this data to Excel/CSV for further analysis or reporting') }}.<br><br><strong>👉 {{ __('Click') }} "Close" {{ __('to continue the tour') }}</strong>',
                            side: 'top',
                            align: 'end'
                        },
                        onDeselected: () => {
                            const modal = document.getElementById('onHandDetailModal');
                            if (modal) {
                                const modalInstance = bootstrap.Modal.getInstance(modal);
                                if (modalInstance) {
                                    // Just close the modal, don't auto-advance
                                    // Let the user click "Next" button naturally
                                    const handleModalHidden = () => {
                                        modal.removeEventListener('hidden.bs.modal',
                                            handleModalHidden);
                                        // Removed: driverObj.moveNext();
                                    };
                                    modal.addEventListener('hidden.bs.modal', handleModalHidden);
                                    modalInstance.hide();
                                }
                            }
                        }
                    },
                    {
                        element: '.col-4.col-md-4:nth-child(2) .card',
                        popover: {
                            title: '🚚 {{ __('In-Transit Monitoring') }}',
                            description: '{{ __('Track pallets currently being shipped') }}.<br><br><strong>{{ __('Color Code') }}:</strong><br>• <span class="text-success">{{ __('Green') }} ({{ __('In') }})</span> = {{ __('Incoming shipments') }}<br>• <span class="text-danger">{{ __('Red') }} ({{ __('Out') }})</span> = {{ __('Outgoing shipments') }}<br><br><strong>💡 {{ __('Tip') }}:</strong> {{ __('Hover over') }} <i class="bi bi-info-circle"></i> {{ __('for more details!') }}',
                            side: 'bottom',
                            align: 'start'
                        }
                    },
                    {
                        element: '.col-4.col-md-4:nth-child(3) .card',
                        popover: {
                            title: '💰 {{ __('Customer-Liable Pallets') }}',
                            description: "{{ __('Pallets you\'re currently responsible for paying') }}.<br><br><strong>⚠️ {{ __('Important') }}:</strong> {{ __('Monitor this to avoid unexpected charges!') }}<br><br><strong>💡 {{ __('Tip') }}:</strong> {{ __('Hover over') }} <i class='bi bi-info-circle'></i> {{ __('to understand the calculation') }}.",
                            side: 'bottom',
                            align: 'start'
                        }
                    },
                    {
                        element: '#stockForecastChart',
                        popover: {
                            title: '📈 {{ __('Stock Movement Forecast') }}',
                            description: '{{ __('See your inventory trends over 14 days.') }}<br><br><strong>{{ __('Legend:') }}</strong><br>• <span style="color: #198754;">━━━</span> {{ __('Solid green = Actual stock') }}<br>• <span style="color: #0d6efd;">┅┅┅</span> {{ __('Dashed blue = AI forecast') }}<br><br><strong>💡 {{ __('Interactive:') }}</strong> {{ __('Hover over any point to see stock in/out details!') }}',
                            side: 'top',
                            align: 'start'
                        }
                    },
                    {
                        element: '.clickable-row:first-child',
                        popover: {
                            title: '📋 {{ __('Recent Transactions') }}',
                            description: '{{ __('Latest pallet movements.') }}<br><br><strong>{{ __('Badge Colors:') }}</strong><br>• <span class="badge bg-danger">DN</span> = {{ __('DN = Delivery Note (outgoing)') }}<br>• <span class="badge bg-success">GR</span> = {{ __('GR = Goods Receipt (incoming)') }}<br><br><strong>💡 {{ __('Try this:') }}</strong> {{ __('Click any row to view full details!') }}',
                            side: 'top',
                            align: 'start'
                        }
                    },
                    {
                        element: '.col-4.col-xl-4 .card:first-child',
                        popover: {
                            title: '⚠️ {{ __('Alerts & Exceptions') }}',
                            description: '{{ __('Critical notifications requiring attention.') }}<br><br><strong>{{ __('Priority Levels:') }}</strong><br>• <span class="badge bg-danger">{{ __('High') }}</span> = {{ __('Urgent') }}<br>• <span class="badge bg-warning text-dark">{{ __('Medium') }}</span> = {{ __('Review soon') }}<br>• <span class="badge bg-info">{{ __('Low') }}</span> = {{ __('Awareness') }}<br><br><strong>💡 {{ __('Best Practice:') }}</strong> {{ __('Check every morning!') }}',
                            side: 'left',
                            align: 'start'
                        }
                    },
                    {
                        element: '.col-4.col-xl-4 .card:last-child',
                        popover: {
                            title: '💵 {{ __('Billing Overview') }}',
                            description: '{{ __('6-month trend of pallet rental charges.') }}<br><br><strong>💡 {{ __('Interactive:') }}</strong> {{ __('Hover over bars to see exact amounts.') }}',
                            side: 'left',
                            align: 'start'
                        }
                    },
                    {
                        element: '#desktopMenuList [data-tour="menu-network"]',
                        popover: {
                            title: '📋 {{ __('Network') }}',
                            description: '{{ __('Manage rental agreements and customer contracts.') }}<br><br>{{ __('This is where commercial relationships are defined.') }}',
                            side: 'bottom'
                        }
                    },
                    {
                        element: '#desktopMenuList [data-tour="menu-transaction"]',
                        popover: {
                            title: '🔄 {{ __('Transaction') }}',
                            description: '{{ __('Handle daily pallet movements such as deliveries, returns, and goods receipts.') }}',
                            side: 'bottom'
                        }
                    },
                    {
                        element: '#desktopMenuList [data-tour="menu-finance"]',
                        popover: {
                            title: '💰 {{ __('Finance') }}',
                            description: '{{ __('Invoices, billing, and monthly usage calculations live here.') }}',
                            side: 'bottom'
                        }
                    },
                    {
                        element: '#desktopMenuList [data-tour="menu-report"]',
                        popover: {
                            title: '📊 {{ __('Report') }}',
                            description: '{{ __('Generate stock, transaction, and financial reports for analysis.') }}',
                            side: 'bottom'
                        }
                    },
                    {
                        element: '#desktopMenuList [data-tour="menu-management"]',
                        popover: {
                            title: '⚙️ {{ __('Management') }}',
                            description: '{{ __('Master data configuration: customers, pallets, logistics, marketing, and companies.') }}',
                            side: 'bottom'
                        }
                    },
                    {
                        element: '#desktopMenuList [data-tour="menu-help"]',
                        popover: {
                            title: '❓ {{ __('Help & Feedback') }}',
                            description: '{{ __('Need help or want to give feedback? Start here anytime.') }}',
                            side: 'bottom'
                        }
                    },
                    {
                        popover: {
                            title: '✅ {{ __('You’re all set!') }}',
                            description: '{{ __('Now you know where everything lives. You can explore at your own pace — no pressure') }} 😊'
                        },
                        onDeselected: () => {
                            localStorage.setItem('dashboardTourCompleted', 'true');
                        }
                    }
                ],
                onDestroyed: () => {
                    const modals = document.querySelectorAll('.modal.show');
                    modals.forEach(modal => {
                        const instance = bootstrap.Modal.getInstance(modal);
                        if (instance) instance.hide();
                    });
                }
            });

            driverObj.drive();
            return driverObj;
        };

        // ============================================================================
        // FIRST LOGIN WELCOME MODAL
        // ============================================================================
        @if (Auth::user()->lfirstlogin)
            (function() {
                const welcomeModalEl = document.getElementById('welcomeModal');
                const welcomeModal = new bootstrap.Modal(welcomeModalEl);

                function dismissWelcome() {
                    fetch('{{ route('dashboard.dismiss-welcome') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        }
                    });
                }

                // Mark as "start tour" before hiding
                document.getElementById('welcomeStartTourBtn').addEventListener('click', function() {
                    welcomeModalEl.dataset.startTour = 'true';
                    dismissWelcome();
                    welcomeModal.hide();
                });

                document.getElementById('welcomeSkipBtn').addEventListener('click', function() {
                    dismissWelcome();
                    welcomeModal.hide();
                });

                // After modal fully closes, start tour if flagged
                welcomeModalEl.addEventListener('hidden.bs.modal', function() {
                    if (welcomeModalEl.dataset.startTour === 'true') {
                        welcomeModalEl.dataset.startTour = '';
                        window.startProductTour();
                    }
                });

                // Show on load
                window.addEventListener('DOMContentLoaded', function() {
                    welcomeModal.show();
                });
            })();
        @endif

        // ============================================================================
        // Initialize Bootstrap Tooltips
        // ============================================================================
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });

        // ============================================================================
        // Sync Alerts Card height with Daily Stock Movement card
        // ============================================================================
        function syncAlertsCardHeight() {
            const stockCard = document.getElementById('stockForecastCard');
            const alertsCard = document.getElementById('alertsCard');
            if (stockCard && alertsCard) {
                const h = stockCard.offsetHeight;
                alertsCard.style.height = h + 'px';
            }
        }

        document.addEventListener('DOMContentLoaded', syncAlertsCardHeight);
        window.addEventListener('resize', syncAlertsCardHeight);
    </script>
@endpush
