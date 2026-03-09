@extends('layouts.app')

@section('title', __('Order/Return Monitoring'))

@section('header-left')
    <div>
        <h2 class="h5 fw-semibold mb-0">{{ __('Order/Return Monitoring') }}</h2>
        <span class="text-muted small">{{ __('Track and manage order and return requests') }}</span>
    </div>
@endsection

@section('content')
    <!-- Search & Filter Bar -->
    <div class="card mb-3">
        <div class="card-body py-2">

            {{-- ROW 1 : DATE + SEARCH + ACTION --}}
            <div class="d-flex flex-row align-items-center gap-2 mb-3">
                <!-- Date Filter (Modern Range Input) -->
                <div class="input-group input-group-sm flex-shrink-0" style="width:220px">
                    <span class="input-group-text bg-white text-muted">
                        <i class="bi bi-calendar3"></i>
                    </span>

                    <input type="text" class="form-control date-range" id="dateRange" placeholder="{{ __('All Date') }}">

                    <button type="button" class="btn btn-outline-secondary" id="clearDateFilter">
                        <i class="bi bi-x"></i>
                    </button>
                </div>

                <!-- Search -->
                <form method="GET" action="{{ route('orderreturn.order_return_monitoring') }}" id="filterForm"
                    class="flex-grow-1 me-2">

                    <input type="hidden" name="type" id="typeInput" value="{{ request('type', 'all') }}">
                    <input type="hidden" name="status" id="statusInput" value="{{ request('status') }}">
                    <input type="hidden" name="date_from" id="dateFromInput" value="{{ request('date_from') }}">
                    <input type="hidden" name="date_to" id="dateToInput" value="{{ request('date_to') }}">

                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white">
                            <i class="bi bi-search"></i>
                        </span>

                        <input type="text" class="form-control" name="search" id="searchInput"
                            value="{{ request('search') }}" placeholder="{{ __('Search request...') }}">
                    </div>
                </form>


                <!-- Action -->
                <a href="{{ route('orderreturn.request_email') }}" class="btn btn-brand btn-sm flex-shrink-0"
                    id="btnGenerateRequestEmail">
                    <i class="bi bi-envelope me-1"></i>
                    {{ __('Generate Request Email') }}
                </a>
            </div>

            {{-- ROW 2 : TYPE & STATUS PILLS --}}
            <div class="d-flex align-items-center gap-2 flex-wrap pt-2 border-top" id="requestTypeFilters">
                <!-- Request Type Filter -->
                <span class="text-muted small fw-semibold me-2">{{ __('Type') }}</span>

                <button
                    class="btn btn-sm rounded-pill type-filter {{ request('type', 'all') === 'all' ? 'btn-brand active-filter' : 'btn-outline-secondary' }}"
                    data-type="all">
                    {{ __('All') }}
                </button>


                <button
                    class="btn btn-sm rounded-pill type-filter {{ request('type') === 'order' ? 'btn-brand active-filter' : 'btn-outline-secondary' }}"
                    data-type="order">
                    {{ __('Order') }}
                </button>

                <button
                    class="btn btn-sm rounded-pill type-filter {{ request('type') === 'return' ? 'btn-brand active-filter' : 'btn-outline-secondary' }}"
                    data-type="return">
                    {{ __('Return') }}
                </button>

                <span class="mx-2 text-muted">|</span>

                <!-- Status Filter -->
                <span class="text-muted small fw-semibold me-2">{{ __('Status') }}</span>

                <button
                    class="btn btn-sm rounded-pill status-filter {{ request('status') === 'Pending' ? 'btn-brand active-filter' : 'btn-outline-secondary' }}"
                    data-status="Pending">
                    {{ __('Pending') }}
                </button>

                <button
                    class="btn btn-sm rounded-pill status-filter {{ request('status') === 'Approved' ? 'btn-brand active-filter' : 'btn-outline-secondary' }}"
                    data-status="Approved">
                    {{ __('Approved') }}
                </button>

                <button
                    class="btn btn-sm rounded-pill status-filter {{ request('status') === 'Rejected' ? 'btn-brand active-filter' : 'btn-outline-secondary' }}"
                    data-status="Rejected">
                    {{ __('Rejected') }}
                </button>

                <button
                    class="btn btn-sm rounded-pill status-filter {{ request('status') === 'Reschedule Proposed' ? 'btn-brand active-filter' : 'btn-outline-secondary' }}"
                    data-status="Reschedule Proposed">
                    {{ __('Reschedule') }}
                </button>

                <button
                    class="btn btn-sm rounded-pill status-filter {{ request('status') === 'Delivery Note Created' ? 'btn-brand active-filter' : 'btn-outline-secondary' }}"
                    data-status="Delivery Note Created">
                    {{ __('DN Created') }}
                </button>

            </div>

        </div>
    </div>

    <!-- Table Card -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center bg-success text-white">
            <span>
                <i class="bi bi-box-seam me-2"></i>{{ __('Order/Return Requests List') }}
            </span>
            <div class="d-flex gap-2 align-items-center">
                <!-- Column Visibility Dropdown -->
                <div class="dropdown">
                    <button type="button" id="columnToggle"
                        class="header-action d-flex align-items-center gap-1 dropdown-toggle" data-bs-toggle="dropdown"
                        data-bs-display="static" aria-expanded="false">
                        <i class="bi bi-gear"></i>
                        {{ __('Columns') }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" id="columnMenu" aria-labelledby="columnToggle">
                        <li class="dropdown-header">{{ __('Show/Hide Columns') }}</li>
                        <li>
                            <label class="dropdown-item">
                                <input class="form-check-input me-2 column-toggle" type="checkbox" value="request_id"
                                    id="col_request_id" checked>
                                ID
                            </label>
                        </li>
                        <li>
                            <label class="dropdown-item">
                                <input class="form-check-input me-2 column-toggle" type="checkbox" value="company_name"
                                    id="col_company_name" checked>
                                {{ __('Company Name') }}
                            </label>
                        </li>
                        <li>
                            <label class="dropdown-item">
                                <input class="form-check-input me-2 column-toggle" type="checkbox" value="request_type"
                                    id="col_request_type" checked>
                                {{ __('Type') }}
                            </label>
                        </li>
                        <li>
                            <label class="dropdown-item">
                                <input class="form-check-input me-2 column-toggle" type="checkbox" value="request_date"
                                    id="col_request_date" checked>
                                {{ __('Request Date') }}
                            </label>
                        </li>
                        <li>
                            <label class="dropdown-item">
                                <input class="form-check-input me-2 column-toggle" type="checkbox" value="pallet_type"
                                    id="col_pallet_type" checked>
                                {{ __('Pallet Type') }}
                            </label>
                        </li>
                        <li>
                            <label class="dropdown-item">
                                <input class="form-check-input me-2 column-toggle" type="checkbox" value="from_warehouse"
                                    id="col_from_warehouse" checked>
                                {{ __('From Warehouse') }}
                            </label>
                        </li>
                        <li>
                            <label class="dropdown-item">
                                <input class="form-check-input me-2 column-toggle" type="checkbox" value="from_address"
                                    id="col_from_address" checked>
                                {{ __('From Address') }}
                            </label>
                        </li>
                        <li>
                            <label class="dropdown-item">
                                <input class="form-check-input me-2 column-toggle" type="checkbox" value="to_warehouse"
                                    id="col_to_warehouse" checked>
                                {{ __('To Warehouse') }}
                            </label>
                        </li>
                        <li>
                            <label class="dropdown-item">
                                <input class="form-check-input me-2 column-toggle" type="checkbox" value="to_address"
                                    id="col_to_address" checked>
                                {{ __('To Address') }}
                            </label>
                        </li>
                        <li>
                            <label class="dropdown-item">
                                <input class="form-check-input me-2 column-toggle" type="checkbox" value="quantity"
                                    id="col_quantity" checked>
                                {{ __('Quantity') }}
                            </label>
                        </li>
                        <li>
                            <label class="dropdown-item">
                                <input class="form-check-input me-2 column-toggle" type="checkbox" value="status"
                                    id="col_status" checked>
                                {{ __('Status') }}
                            </label>
                        </li>
                        <li>
                            <label class="dropdown-item">
                                <input class="form-check-input me-2 column-toggle" type="checkbox" value="approved_by"
                                    id="col_approved_by" checked>
                                {{ __('Approved By') }}
                            </label>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <button class="dropdown-item text-center small" id="resetColumns">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>{{ __('Reset to Default') }}
                            </button>
                        </li>
                    </ul>
                </div>

            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="orderReturnTable">
                    <thead class="table-light">
                        <tr>
                            <th class="px-3 py-2 col-request_id">ID</th>
                            <th class="px-3 py-2 text-center col-request_type">{{ __('Type') }}</th>
                            <th class="px-3 py-2 text-center col-request_date">{{ __('Request Date') }}</th>
                            <th class="px-3 py-2 col-company_name">{{ __('Company Name') }}</th>
                            <th class="px-3 py-2 col-pallet_type">{{ __('Pallet Type') }}</th>
                            <th class="px-3 py-2 col-from_warehouse">{{ __('From Warehouse') }}</th>
                            <th class="px-3 py-2 col-from_address">{{ __('From Address') }}</th>
                            <th class="px-3 py-2 col-to_warehouse">{{ __('To Warehouse') }}</th>
                            <th class="px-3 py-2 col-to_address">{{ __('To Address') }}</th>
                            <th class="px-3 py-2 text-center col-quantity">{{ __('Quantity') }}</th>
                            <th class="px-3 py-2 text-center col-status">{{ __('Status') }}</th>
                            <th class="px-3 py-2 col-approved_by">{{ __('Approved By') }}</th>
                        </tr>
                    </thead>
                    <tbody id="requestTableBody">
                        @forelse ($requests as $request)
                            @php
                                $statusMap = [
                                    'Pending' => ['bg-warning text-dark', 'bi-clock'],
                                    'Approved' => ['bg-success', 'bi-check-circle'],
                                    'Rejected' => ['bg-danger', 'bi-x-circle'],
                                    'Reschedule Proposed' => ['bg-warning text-dark', 'bi-calendar2-check'],
                                    'Delivery Note Created' => ['bg-info text-dark', 'bi-receipt'],
                                ];

                                $statusLabelMap = [
                                    'Pending' => 'Pending',
                                    'Approved' => 'Approved',
                                    'Rejected' => 'Rejected',
                                    'Reschedule Proposed' => 'Reschedulte',
                                    'Delivery Note Created' => 'DN Created',
                                ];

                                [$statusClass, $statusIcon] = $statusMap[$request->latest_status] ?? [
                                    'bg-secondary',
                                    'bi-question-circle',
                                ];
                                $statusLabel = $statusLabelMap[$request->latest_status] ?? ucfirst($request->latest_status);

                                $typeClass = $request->crequest_type === 'Order' ? 'bg-primary' : 'bg-danger';
                                $typeIcon =
                                    $request->crequest_type === 'Order' ? 'bi-cart-plus' : 'bi-box-arrow-in-down';
                            @endphp

                            <tr class="request-row-clickable" data-request-uuid="{{ $request->uuid }}" data-request-id="{{ $request->nid }}"
                                data-tour="request-row">
                                <td class="col-request_id">
                                    <span class="fw-semibold text-success">
                                        {{ $request->nid }}
                                    </span>
                                </td>

                                <td class="text-center col-request_type">
                                    <span class="badge {{ $typeClass }} rounded-pill">
                                        <i class="bi {{ $typeIcon }} me-1"></i>
                                        {{ ucfirst($request->crequest_type) }}
                                    </span>
                                </td>

                                <td class="text-center col-request_date">
                                    {{ $request->drequired_date
                                        ? str($request->drequired_date)->substr(0, 10)
                                        : str($request->dreturn_date)->substr(0, 10) }}
                                </td>

                                <td class="col-company_name">
                                    {{ $request->ccompany_name }}
                                </td>

                                <td class="col-pallet_type">
                                    {{ $request->cpallet_type }}
                                </td>

                                <td class="col-from_warehouse fw-medium">
                                    {{ $request->cwarehouse_from }}
                                </td>

                                <td class="col-from_address">
                                    <span class="address-truncate" title="{{ $request->cwaddr_from }}">
                                        {{ $request->cwaddr_from }}
                                    </span>
                                </td>

                                <td class="col-to_warehouse fw-medium">
                                    {{ $request->cwarehouse_to }}
                                </td>

                                <td class="col-to_address">
                                    <span class="address-truncate" title="{{ $request->cwaddr_to }}">
                                        {{ $request->cwaddr_to }}
                                    </span>
                                </td>

                                <td class="text-center col-quantity">
                                    <span class="badge bg-secondary rounded-pill">
                                        {{ $request->nqty ? $request->nqty : $request->nqty_return }} pcs
                                    </span>
                                </td>

                                <td class="text-center col-status">
                                    <span class="badge {{ $statusClass }} rounded-pill">
                                        <i class="bi {{ $statusIcon }} me-1"></i>
                                        {{ $statusLabel }}
                                    </span>
                                </td>

                                <td class="col-approved_by">
                                    {{ $request->capproved_by ?? '-' }}
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="12" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox display-4 d-block mb-3"></i>
                                    <p class="fw-semibold mb-1">{{ __('No requests found') }}</p>
                                    <p class="small mb-0">{{ __('Try adjusting your filters or search term') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center py-2">
            <!-- Left: Info text -->
            <p class="text-muted small mb-0" id="paginationInfo">
                Showing {{ $from }} to {{ $to }} of {{ $total }} requests
            </p>

            <!-- Right: Pagination -->
            <nav aria-label="Page navigation">
                <ul class="pagination pagination-sm mb-0" id="paginationControls">
                    <!-- Pagination will be populated by JavaScript -->
                </ul>
            </nav>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .table> :not(caption)>*>* {
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
        }

        .table thead th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            color: #6c757d;
            border-bottom: 2px solid #dee2e6;
        }

        .table tbody tr {
            transition: background-color 0.15s ease;
            cursor: pointer;
        }

        .table tbody tr:hover {
            background-color: #f1f8f4;
        }

        .request-row-clickable:hover {
            background-color: #e8f5e9 !important;
        }

        .badge {
            font-weight: 500;
            font-size: 0.75rem;
        }

        .rounded-pill {
            border-radius: 50rem !important;
        }

        .input-group-text {
            border-right: 0;
        }

        .input-group .form-control {
            border-left: 0;
        }

        .input-group .form-control:focus {
            border-left: 0;
            box-shadow: none;
        }

        .input-group:focus-within .input-group-text {
            border-color: #198754;
        }

        .input-group:focus-within .form-control {
            border-color: #198754;
        }

        .pagination-sm .page-link {
            padding: 0.25rem 0.5rem;
            font-size: 0.8125rem;
            line-height: 1.5;
        }

        .pagination {
            gap: 0.25rem;
        }

        .page-item .page-link {
            border: 1px solid #dee2e6;
            color: #495057;
            border-radius: 0.25rem;
            transition: all 0.15s ease;
        }

        .page-item .page-link:hover {
            background-color: #f8f9fa;
            border-color: #198754;
            color: #198754;
        }

        .page-item.active .page-link {
            background-color: #198754;
            border-color: #198754;
            color: white;
        }

        .page-item.disabled .page-link {
            background-color: #fff;
            border-color: #dee2e6;
            color: #6c757d;
            cursor: not-allowed;
        }

        .type-filter.active-filter,
        .status-filter.active-filter {
            background-color: #198754 !important;
            border-color: #198754 !important;
            color: white !important;
        }

        /* Column visibility dropdown styles */
        #columnMenu {
            min-width: 220px;
            max-width: 280px;
        }

        #columnMenu .dropdown-item {
            padding: 0.5rem 1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            font-size: 0.875rem;
        }

        #columnMenu .dropdown-item:hover {
            background-color: #f8f9fa;
        }

        #columnMenu .dropdown-item:active {
            background-color: #e9ecef;
        }

        #columnMenu .form-check-input {
            cursor: pointer;
            margin-top: 0;
            flex-shrink: 0;
        }

        #columnMenu .dropdown-header {
            padding: 0.5rem 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        #columnMenu .dropdown-divider {
            margin: 0.5rem 0;
        }

        /* Prevent text selection on checkbox labels */
        #columnMenu label {
            user-select: none;
            margin-bottom: 0;
        }

        /* Hidden columns */
        .col-hidden {
            display: none !important;
        }

        .header-action {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 0.875rem;
            padding: 0.25rem 0.5rem;
        }

        .header-action:hover {
            opacity: 0.8;
        }

        /* Address truncation */
        .address-truncate {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            display: inline-block;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // State management
            let currentPage = 1;
            let currentType = 'all';
            let currentStatus = '';
            let currentSearch = '';
            let dateFrom = '';
            let dateTo = '';
            const itemsPerPage = 10;

            // Column visibility state
            let visibleColumns = {
                request_id: true,
                request_type: true,
                request_date: true,
                from_warehouse: true,
                from_address: true,
                to_warehouse: true,
                to_address: true,
                company_name: true,
                pallet_type: true,
                quantity: true,
                required_date: true,
                address: true,
                status: true,
                approved_by: true
            };

            flatpickr('#dateRange', {
                mode: 'range',
                dateFormat: 'Y-m-d',

                onChange(selectedDates) {
                    const dateFromInput = document.getElementById('dateFromInput');
                    const dateToInput = document.getElementById('dateToInput');

                    const formatLocalDate = (date) => {
                        const year = date.getFullYear();
                        const month = String(date.getMonth() + 1).padStart(2, '0');
                        const day = String(date.getDate()).padStart(2, '0');
                        return `${year}-${month}-${day}`;
                    };

                    dateFromInput.value = selectedDates[0] ?
                        formatLocalDate(selectedDates[0]) :
                        '';

                    dateToInput.value = selectedDates[1] ?
                        formatLocalDate(selectedDates[1]) :
                        '';

                    // Submit ONLY when range is complete
                    if (selectedDates.length === 2) {
                        document.getElementById('filterForm').submit();
                    }
                }
            });

            // Customer row click handler
            document.querySelectorAll('.request-row-clickable').forEach(row => {
                row.addEventListener('click', function() {
                    const requestUuid = this.getAttribute('data-request-uuid');
                    window.location.href = `/delivery/order-return/${requestUuid}`;
                });
            });

            // DOM elements
            const tableBody = document.getElementById('requestTableBody');
            const paginationInfo = document.getElementById('paginationInfo');
            const paginationControls = document.getElementById('paginationControls');
            const searchInput = document.getElementById('searchInput');
            const clearSearchBtn = document.getElementById('clearSearch');
            const typeFilterButtons = document.querySelectorAll('.type-filter');
            const statusFilterButtons = document.querySelectorAll('.status-filter');
            const columnToggles = document.querySelectorAll('.column-toggle');
            const resetColumnsBtn = document.getElementById('resetColumns');
            const columnMenu = document.getElementById('columnMenu');
            const dateRangeInput = document.getElementById('dateRange');
            const clearDateBtn = document.getElementById('clearDateFilter');

            // Column visibility management
            function updateColumnVisibility() {
                Object.keys(visibleColumns).forEach(col => {
                    const elements = document.querySelectorAll(`.col-${col}`);
                    elements.forEach(el => {
                        if (visibleColumns[col]) {
                            el.classList.remove('col-hidden');
                        } else {
                            el.classList.add('col-hidden');
                        }
                    });
                });

                // Save to localStorage
                localStorage.setItem('orderReturnColumns', JSON.stringify(visibleColumns));
            }

            // Load saved column visibility
            const savedColumns = localStorage.getItem('orderReturnColumns');
            if (savedColumns) {
                visibleColumns = JSON.parse(savedColumns);
                // Update checkboxes
                Object.keys(visibleColumns).forEach(col => {
                    const checkbox = document.getElementById(`col_${col}`);
                    if (checkbox) {
                        checkbox.checked = visibleColumns[col];
                    }
                });
                updateColumnVisibility();
            }

            // Column toggle handlers
            columnToggles.forEach(toggle => {
                toggle.addEventListener('change', function(e) {
                    const column = this.value;
                    visibleColumns[column] = this.checked;
                    updateColumnVisibility();
                });
            });

            // Prevent dropdown from closing when clicking inside menu items
            columnMenu.addEventListener('click', function(e) {
                if (e.target.classList.contains('column-toggle') ||
                    e.target.closest('label.dropdown-item')) {
                    e.stopPropagation();
                }
            });

            // Reset columns button
            resetColumnsBtn.addEventListener('click', function(e) {
                e.stopPropagation();

                Object.keys(visibleColumns).forEach(col => {
                    visibleColumns[col] = true;
                    const checkbox = document.getElementById(`col_${col}`);
                    if (checkbox) {
                        checkbox.checked = true;
                    }
                });
                updateColumnVisibility();

                // Close the dropdown
                const dropdown = bootstrap.Dropdown.getInstance(document.getElementById('columnToggle'));
                if (dropdown) {
                    dropdown.hide();
                }
            });

            // Type filter buttons
            typeFilterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    document.getElementById('typeInput').value = this.dataset.type;
                    document.getElementById('filterForm').submit();
                });
            });

            // Status filter buttons
            statusFilterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const input = document.getElementById('statusInput');

                    if (this.classList.contains('active-filter')) {
                        input.value = '';
                    } else {
                        input.value = this.dataset.status;
                    }

                    document.getElementById('filterForm').submit();
                });
            });


            // Search input
            searchInput.addEventListener('input', function() {
                currentSearch = this.value.trim();
                currentPage = 1;
                clearSearchBtn.classList.toggle('d-none', !currentSearch);
            });

            // Clear search
            clearSearchBtn.addEventListener('click', function() {
                searchInput.value = '';
                currentSearch = '';
                currentPage = 1;
                this.classList.add('d-none');
            });

            clearDateBtn.addEventListener('click', function() {
                dateRangeInput._flatpickr.clear();
                dateFrom = '';
                dateTo = '';
                currentPage = 1;
            });
        });

        // Pagination render
        function renderPagination() {
            const pagination = document.getElementById('paginationControls');
            if (!pagination) return;

            const currentPage = {{ $currentPage }};
            const lastPage = {{ $lastPage }};

            let html = '';

            // Prev button
            html += `<li class="page-item ${currentPage <= 1 ? 'disabled' : ''}">
                <a class="page-link" href="?${buildQuery(currentPage - 1)}">
                    <i class="bi bi-chevron-left"></i>
                </a>
            </li>`;

            // Page numbers
            for (let i = 1; i <= lastPage; i++) {
                if (
                    i === 1 || i === lastPage ||
                    (i >= currentPage - 2 && i <= currentPage + 2)
                ) {
                    html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                        <a class="page-link" href="?${buildQuery(i)}">${i}</a>
                    </li>`;
                } else if (i === currentPage - 3 || i === currentPage + 3) {
                    html += `<li class="page-item disabled"><span class="page-link">…</span></li>`;
                }
            }

            // Next button
            html += `<li class="page-item ${currentPage >= lastPage ? 'disabled' : ''}">
                <a class="page-link" href="?${buildQuery(currentPage + 1)}">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>`;

            pagination.innerHTML = html;
        }

        function buildQuery(page) {
            const params = new URLSearchParams(window.location.search);
            params.set('page', page);
            return params.toString();
        }

        renderPagination();
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // =========================================================================
            // ORDER / RETURN MONITORING TOUR (ID-SAFE)
            // =========================================================================
            window.startOrderReturnMonitoringTour = function() {
                const driver = window.driver.js.driver;

                const driverObj = driver({
                    showProgress: true,
                    showButtons: ['next', 'previous', 'close'],
                    steps: [{
                            popover: {
                                title: '📊 {{__('Order / Return Monitoring')}}',
                                description: '{{__('This page shows pallet Order and Return requests that were created via the system-generated email.')}}'
                            }
                        },
                        {
                            popover: {
                                title: '✉️ {{__('How Requests Appear Here')}}',
                                description: '{{__('When you send a request email using the generated format, the system automatically records it and displays it on this page.')}}'
                            }
                        },
                        {
                            element: '#dateRange',
                            popover: {
                                title: '📅 {{__('Date Filter')}}',
                                description: '{{__('Filter requests by a specific date range.')}}',
                                side: 'bottom',
                                align: 'start'
                            }
                        },
                        {
                            element: '#searchInput',
                            popover: {
                                title: '🔍 {{__('Search Requests')}}',
                                description: '{{__('Search requests by ID, company name, pallet type, or warehouse.')}}',
                                side: 'bottom',
                                align: 'start'
                            }
                        },
                        {
                            element: '#requestTypeFilters',
                            popover: {
                                title: '🧭 {{__('Request Type Filter')}}',
                                description: '{{__('Filter between')}} <strong>{{__('Order')}}</strong>, <strong>{{__('Return')}}</strong>, {{__('or all request types.')}}',
                                side: 'top',
                                align: 'start'
                            }
                        },
                        {
                            element: '#requestStatusFilters',
                            popover: {
                                title: '📌 {{__('Status Filter')}}',
                                description: '{{__('Filter requests by their current status: Pending, Approved, Rejected, Reschedule Proposed, or Delivery Note Created.')}}',
                                side: 'top',
                                align: 'start'
                            }
                        },
                        {
                            element: '#orderReturnTable',
                            popover: {
                                title: '📋 {{__('Requests Table')}}',
                                description: '{{__('Each row represents a request that was sent and processed by the system.')}}',
                                side: 'top',
                                align: 'start'
                            }
                        },
                        {
                            popover: {
                                title: '🔄 {{__('Status Updates')}}',
                                description: '{{__('Request statuses are updated automatically based on responses from PT Yanasurya Bhaktipersada.')}}'
                            }
                        },
                        {
                            popover: {
                                title: '🖱️ {{__('View Request Details')}}',
                                description: '{{__('Click any row in the table to view full request details, history, and approval information.')}}'
                            }
                        },
                        {
                            element: '#btnGenerateRequestEmail',
                            popover: {
                                title: '➕ {{__('Create New Request')}}',
                                description: '{{__('Generate a new Order or Return request email from here.')}}',
                                side: 'left',
                                align: 'start'
                            }
                        },
                        {
                            popover: {
                                title: '🎉 {{__('You’re Ready')}}',
                                description: '{{__('You now know how to monitor and track your pallet order and return requests.')}}'
                            }
                        }
                    ]
                });

                driverObj.drive();
                return driverObj;
            };

            // =========================================================================
            // REGISTER THIS PAGE TOUR
            // =========================================================================
            window.startProductTour = window.startOrderReturnMonitoringTour;

        });
    </script>
@endpush
