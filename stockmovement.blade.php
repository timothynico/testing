@extends('layouts.app')

@section('title', __('Stock Movement Report'))

@section('header-left')
    <div>
        <h2 class="h5 fw-semibold mb-0">{{ __('Stock Movement Report') }}</h2>
        <span class="text-muted small">{{ __('Monitor stock in and out movements') }}</span>
    </div>

    <!-- Hidden print container -->
    <div id="printContainer"></div>
@endsection

@section('content')
    <!-- Filters Card -->
    <div class="card mb-3">
        <div class="card-body py-3" id="stockMovementFilters">

            <form id="filterForm">
                <div class="row g-3 align-items-end">
                    <!-- Company Filter -->
                    <div class="col-3 col-md-2">
                        <label for="filterCompany" class="form-label small fw-semibold">
                            {{ __('Company') }} <span class="text-danger">*</span>
                        </label>
                        <select class="form-select form-select-sm searchable-select" id="filterCompany" name="company">
                            <option value="">{{ __('Select Company...') }}</option>
                            @foreach ($arrcustomer as $customer)
                                <option value="{{ $customer->ckdcust }}" data-ckdcust="{{ $customer->ckdcust }}">{{ $customer->cnmcust }}</option>
                            @endforeach
                            {{-- <option value="PT Yanasurya Bhaktipersada">PT Yanasurya Bhaktipersada</option>
                            <option value="PT Yanaprima Hastapersada">PT Yanaprima Hastapersada (Subsidiary)</option>
                            <option value="PT Forindoprima Perkasa">PT Forindoprima Perkasa (Subsidiary)</option> --}}
                        </select>
                    </div>

                    <!-- Warehouse Filter -->
                    <div class="col-2 col-md-2">
                        <label for="filterWarehouse" class="form-label small fw-semibold">{{ __('Warehouse') }}</label>
                        <select class="form-select form-select-sm searchable-select" id="filterWarehouse" name="warehouse">
                            <option value="">{{ __('All Warehouses') }}</option>
                            <!-- Options will be populated dynamically -->
                        </select>
                    </div>
                    <!-- Month Filter -->
                    <div class="col-2 col-md-1" id="stockMovementPeriod">
                        <label for="filterMonth" class="form-label small fw-semibold">{{ __('Month') }}</label>
                        <select class="form-select form-select-sm" id="filterMonth" name="month">
                            <option value="">{{ __('All Months') }}</option>
                            <option value="1">{{ __('January') }}</option>
                            <option value="2">{{ __('February') }}</option>
                            <option value="3">{{ __('March') }}</option>
                            <option value="4">{{ __('April') }}</option>
                            <option value="5">{{ __('May') }}</option>
                            <option value="6">{{ __('June') }}</option>
                            <option value="7">{{ __('July') }}</option>
                            <option value="8">{{ __('August') }}</option>
                            <option value="9">{{ __('September') }}</option>
                            <option value="10">{{ __('October') }}</option>
                            <option value="11">{{ __('November') }}</option>
                            <option value="12">{{ __('December') }}</option>
                        </select>
                    </div>

                    <!-- Year Filter -->
                    <div class="col-2 col-md-1">
                        <label for="filterYear" class="form-label small fw-semibold">{{ __('Year') }}</label>
                        <select class="form-select form-select-sm" id="filterYear" name="year">
                            <option value="">{{ __('All Years') }}</option>
                        </select>
                    </div>

                    <!-- Product Filter -->
                    <div class="col-2 col-md-2">
                        <label for="filterProduct" class="form-label small fw-semibold">{{ __('Product') }}</label>
                        <select class="form-select form-select-sm searchable-select" id="filterProduct" name="product">
                            <option value="">{{ __('All Products') }}</option>
                            {{-- <option value="PT1210AS">PT1210AS</option>
                            <option value="PT1210BS">PT1210BS</option>
                            <option value="PT1212AS">PT1212AS</option> --}}
                            @foreach ($arrbasic as $key => $bsc)
                                <option value="{{ $bsc->cbasic }}">{{ $bsc->cbasic }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Search -->
                    <div class="col-3 col-md-3">
                        <label for="searchInput" class="form-label small fw-semibold">{{ __('Search') }}</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" class="form-control" id="searchInput"
                                placeholder="{{ __('Search DN/GR, From/To...') }}">
                            <button type="button" class="btn btn-outline-secondary d-none" id="clearSearch">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="col-1 col-md-1 d-flex gap-1">
                        <button type="button" class="btn btn-sm btn-outline-secondary w-100" id="resetFilters"
                            title="{{ __('Reset Filters') }}">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Report Table Card -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center bg-success text-white">
            <span>
                <i class="bi bi-table me-2"></i>{{ __('Stock Movement Details') }}
            </span>
            <div class="d-flex gap-2" id="stockMovementActions">
                <!-- Export Dropdown -->
                <div class="dropdown">
                    <button type="button" class="header-action dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="bi bi-download"></i>
                        {{ __('Export') }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="#" id="exportExcel">
                                <i class="bi bi-file-earmark-excel text-success me-2"></i>{{ __('Export to Excel') }}
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#" id="exportPDF">
                                <i class="bi bi-file-earmark-pdf text-danger me-2"></i>{{ __('Export to PDF') }}
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#" id="exportCSV">
                                <i class="bi bi-file-earmark-text text-primary me-2"></i>{{ __('Export to CSV') }}
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Print Button -->
                <button type="button" class="header-action" id="printReport">
                    <i class="bi bi-printer"></i>
                    {{ __('Print') }}
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="stockMovementTable">
                    <thead class="table-light">
                        <tr>
                            <th class="px-3 py-2">{{ __('Doc. Date') }}</th>
                            <th class="px-3 py-2">{{ __('Type') }}</th>
                            <th class="px-3 py-2">{{ __('Doc. Number') }}</th>
                            <th class="px-3 py-2">{{ __('From/To') }}</th>
                            <th class="px-3 py-2">{{ __('Warehouse') }}</th>
                            <th class="px-3 py-2">{{ __('Product') }}</th>
                            <th class="px-3 py-2 text-center">{{ __('Pcs In') }}</th>
                            <th class="px-3 py-2 text-center">{{ __('Pcs Out') }}</th>
                            <th class="px-3 py-2 text-center">{{ __('Balance') }}</th>
                            <th class="px-3 py-2 text-center">{{ __('Mut. Date') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Grand Total Row - Appears at Top -->
                        <tr class="table-light fw-semibold" id="grandTotalRow" style="display: none;">
                            <td colspan="6" class="px-3 py-2 text-end">{{ __('Grand Total') }}</td>
                            <td class="px-3 py-2 text-center" id="headerTotalIn">0</td>
                            <td class="px-3 py-2 text-center" id="headerTotalOut">0</td>
                            <td class="px-3 py-2 text-center" id="headerBalance">0</td>
                            <td class="px-3 py-2"></td>
                        </tr>
                    </tbody>
                    <tbody id="reportTableBody">
                        <!-- Content will be populated by JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center py-2">
            <p class="text-muted small mb-0" id="paginationInfo">
                {{ __('Please select a company to view data') }}
            </p>
        </div>
    </div>

    <!-- Sample Data (Replace with actual backend data later) -->
    <script>
        // Sample data - focused on January 2026
        // let stockMovementData = [
        //     // ===== PT YANASURYA BHAKTIPERSADA - January 2026 (20 entries) =====

        //     // January 30, 2026
        //     {
        //         id: 1,
        //         date: '2026-01-30',
        //         type: 'DN',
        //         document_no: 'DN/2026/030',
        //         customer: 'PT Gudang Garam Tbk',
        //         company: 'PT Yanasurya Bhaktipersada',
        //         product: 'PT1210AS',
        //         pcs_in: 0,
        //         pcs_out: 150,


        //     },
        //     {
        //         id: 2,
        //         date: '2026-01-30',
        //         type: 'DN',
        //         document_no: 'DN/2026/030B',
        //         customer: 'PT Gudang Garam Tbk',
        //         company: 'PT Yanasurya Bhaktipersada',
        //         product: 'PT1210BS',
        //         pcs_in: 0,
        //         pcs_out: 80,
        //         finance_mutation_date: '2026-01-02'
        //     },
        //     {
        //         id: 3,
        //         date: '2026-01-30',
        //         type: 'GR',
        //         document_no: 'GR/2026/028',
        //         customer: 'PT Indofood CBP Sukses Makmur',
        //         company: 'PT Yanasurya Bhaktipersada',
        //         product: 'PT1210AS',
        //         pcs_in: 200,
        //         pcs_out: 0,
        //         finance_mutation_date: '2026-01-02'
        //     },
        //     {
        //         id: 4,
        //         date: '2026-01-30',
        //         type: 'DN',
        //         document_no: 'DN/2026/031',
        //         customer: 'PT Nestle Indonesia',
        //         company: 'PT Yanasurya Bhaktipersada',
        //         product: 'PT1210AS',
        //         pcs_in: 0,
        //         pcs_out: 100,
        //         finance_mutation_date: '2026-01-02'
        //     },

        //     // January 29, 2026
        //     {
        //         id: 5,
        //         date: '2026-01-29',
        //         type: 'GR',
        //         document_no: 'GR/2026/027',
        //         customer: 'PT Wings Surya',
        //         company: 'PT Yanasurya Bhaktipersada',
        //         product: 'PT1210AS',
        //         pcs_in: 180,
        //         pcs_out: 0,
        //         finance_mutation_date: '2026-01-01'
        //     },
        //     {
        //         id: 6,
        //         date: '2026-01-29',
        //         type: 'GR',
        //         document_no: 'GR/2026/027B',
        //         customer: 'PT Wings Surya',
        //         company: 'PT Yanasurya Bhaktipersada',
        //         product: 'PT1210BS',
        //         pcs_in: 120,
        //         pcs_out: 0,
        //         finance_mutation_date: '2026-01-01'
        //     },
        //     {
        //         id: 7,
        //         date: '2026-01-29',
        //         type: 'GR',
        //         document_no: 'GR/2026/027C',
        //         customer: 'PT Wings Surya',
        //         company: 'PT Yanasurya Bhaktipersada',
        //         product: 'PT1212AS',
        //         pcs_in: 50,
        //         pcs_out: 0,
        //         finance_mutation_date: '2026-01-01'
        //     },
        //     {
        //         id: 8,
        //         date: '2026-01-29',
        //         type: 'DN',
        //         document_no: 'DN/2026/029',
        //         customer: 'PT Frisian Flag Indonesia',
        //         company: 'PT Yanasurya Bhaktipersada',
        //         product: 'PT1210AS',
        //         pcs_in: 0,
        //         pcs_out: 90,
        //         finance_mutation_date: '2026-01-01'
        //     },
        //     {
        //         id: 9,
        //         date: '2026-01-29',
        //         type: 'DN',
        //         document_no: 'DN/2026/029B',
        //         customer: 'PT Frisian Flag Indonesia',
        //         company: 'PT Yanasurya Bhaktipersada',
        //         product: 'PT1210BS',
        //         pcs_in: 0,
        //         pcs_out: 60,
        //         finance_mutation_date: '2026-01-01'
        //     },

        //     // January 28, 2026
        //     {
        //         id: 10,
        //         date: '2026-01-28',
        //         type: 'DN',
        //         document_no: 'DN/2026/028',
        //         customer: 'PT Unilever Indonesia Tbk',
        //         company: 'PT Yanasurya Bhaktipersada',
        //         product: 'PT1210AS',
        //         pcs_in: 0,
        //         pcs_out: 200,
        //         finance_mutation_date: '2026-01-30'
        //     },
        //     {
        //         id: 11,
        //         date: '2026-01-28',
        //         type: 'DN',
        //         document_no: 'DN/2026/028B',
        //         customer: 'PT Unilever Indonesia Tbk',
        //         company: 'PT Yanasurya Bhaktipersada',
        //         product: 'PT1212AS',
        //         pcs_in: 0,
        //         pcs_out: 50,
        //         finance_mutation_date: '2026-01-30'
        //     },
        //     {
        //         id: 12,
        //         date: '2026-01-28',
        //         type: 'GR',
        //         document_no: 'GR/2026/026',
        //         customer: 'PT Kao Indonesia',
        //         company: 'PT Yanasurya Bhaktipersada',
        //         product: 'PT1210AS',
        //         pcs_in: 160,
        //         pcs_out: 0,
        //         finance_mutation_date: '2026-01-30'
        //     },
        //     {
        //         id: 13,
        //         date: '2026-01-28',
        //         type: 'GR',
        //         document_no: 'GR/2026/026B',
        //         customer: 'PT Kao Indonesia',
        //         company: 'PT Yanasurya Bhaktipersada',
        //         product: 'PT1210BS',
        //         pcs_in: 100,
        //         pcs_out: 0,
        //         finance_mutation_date: '2026-01-30'
        //     },

        //     // January 27, 2026
        //     {
        //         id: 14,
        //         date: '2026-01-27',
        //         type: 'GR',
        //         document_no: 'GR/2026/025',
        //         customer: 'PT Gudang Garam Tbk',
        //         company: 'PT Yanasurya Bhaktipersada',
        //         product: 'PT1210AS',
        //         pcs_in: 150,
        //         pcs_out: 0,
        //         finance_mutation_date: '2026-01-29'
        //     },
        //     {
        //         id: 15,
        //         date: '2026-01-27',
        //         type: 'GR',
        //         document_no: 'GR/2026/025B',
        //         customer: 'PT Gudang Garam Tbk',
        //         company: 'PT Yanasurya Bhaktipersada',
        //         product: 'PT1210BS',
        //         pcs_in: 100,
        //         pcs_out: 0,
        //         finance_mutation_date: '2026-01-29'
        //     },
        //     {
        //         id: 16,
        //         date: '2026-01-27',
        //         type: 'GR',
        //         document_no: 'GR/2026/025C',
        //         customer: 'PT Gudang Garam Tbk',
        //         company: 'PT Yanasurya Bhaktipersada',
        //         product: 'PT1212AS',
        //         pcs_in: 80,
        //         pcs_out: 0,
        //         finance_mutation_date: '2026-01-29'
        //     },
        //     {
        //         id: 17,
        //         date: '2026-01-27',
        //         type: 'GR',
        //         document_no: 'GR/2026/025D',
        //         customer: 'PT Gudang Garam Tbk',
        //         company: 'PT Yanasurya Bhaktipersada',
        //         product: 'PT1212AS',
        //         pcs_in: 60,
        //         pcs_out: 0,
        //         finance_mutation_date: '2026-01-29'
        //     },
        //     {
        //         id: 18,
        //         date: '2026-01-27',
        //         type: 'DN',
        //         document_no: 'DN/2026/027',
        //         customer: 'PT Danone Indonesia',
        //         company: 'PT Yanasurya Bhaktipersada',
        //         product: 'PT1210AS',
        //         pcs_in: 0,
        //         pcs_out: 120,
        //         finance_mutation_date: '2026-01-29'
        //     },
        //     {
        //         id: 19,
        //         date: '2026-01-27',
        //         type: 'DN',
        //         document_no: 'DN/2026/027B',
        //         customer: 'PT Danone Indonesia',
        //         company: 'PT Yanasurya Bhaktipersada',
        //         product: 'PT1210BS',
        //         pcs_in: 0,
        //         pcs_out: 70,
        //         finance_mutation_date: '2026-01-29'
        //     },
        //     {
        //         id: 20,
        //         date: '2026-01-27',
        //         type: 'DN',
        //         document_no: 'DN/2026/027C',
        //         customer: 'PT Danone Indonesia',
        //         company: 'PT Yanasurya Bhaktipersada',
        //         product: 'PT1212AS',
        //         pcs_in: 0,
        //         pcs_out: 40,
        //         finance_mutation_date: '2026-01-29'
        //     },

        //     // ===== PT YANAPRIMA HASTAPERSADA - January 2026 (5 entries) =====
        //     {
        //         id: 21,
        //         date: '2026-01-30',
        //         type: 'DN',
        //         document_no: 'DN/YPH/2026/005',
        //         customer: 'PT Nestle Indonesia',
        //         company: 'PT Yanaprima Hastapersada',
        //         product: 'PT1210AS',
        //         pcs_in: 0,
        //         pcs_out: 110,
        //         finance_mutation_date: '2026-01-02'
        //     },
        //     {
        //         id: 22,
        //         date: '2026-01-30',
        //         type: 'DN',
        //         document_no: 'DN/YPH/2026/005B',
        //         customer: 'PT Nestle Indonesia',
        //         company: 'PT Yanaprima Hastapersada',
        //         product: 'PT1210BS',
        //         pcs_in: 0,
        //         pcs_out: 90,
        //         finance_mutation_date: '2026-01-02'
        //     },
        //     {
        //         id: 23,
        //         date: '2026-01-30',
        //         type: 'GR',
        //         document_no: 'GR/YPH/2026/005',
        //         customer: 'PT Astra International',
        //         company: 'PT Yanaprima Hastapersada',
        //         product: 'PT1210AS',
        //         pcs_in: 140,
        //         pcs_out: 0,
        //         finance_mutation_date: '2026-01-02'
        //     },
        //     {
        //         id: 24,
        //         date: '2026-01-28',
        //         type: 'DN',
        //         document_no: 'DN/YPH/2026/004',
        //         customer: 'PT Kalbe Farma',
        //         company: 'PT Yanaprima Hastapersada',
        //         product: 'PT1210AS',
        //         pcs_in: 0,
        //         pcs_out: 95,
        //         finance_mutation_date: '2026-01-30'
        //     },
        //     {
        //         id: 25,
        //         date: '2026-01-28',
        //         type: 'GR',
        //         document_no: 'GR/YPH/2026/004',
        //         customer: 'PT Ajinomoto Indonesia',
        //         company: 'PT Yanaprima Hastapersada',
        //         product: 'PT1210AS',
        //         pcs_in: 165,
        //         pcs_out: 0,
        //         finance_mutation_date: '2026-01-30'
        //     },

        //     // ===== PT FORINDOPRIMA PERKASA - January 2026 (5 entries) =====
        //     {
        //         id: 26,
        //         date: '2026-01-30',
        //         type: 'DN',
        //         document_no: 'DN/FPP/2026/005',
        //         customer: 'PT Pertamina',
        //         company: 'PT Forindoprima Perkasa',
        //         product: 'PT1210AS',
        //         pcs_in: 0,
        //         pcs_out: 130,
        //         finance_mutation_date: '2026-02-02'
        //     },
        //     {
        //         id: 27,
        //         date: '2026-01-30',
        //         type: 'DN',
        //         document_no: 'DN/FPP/2026/005B',
        //         customer: 'PT Pertamina',
        //         company: 'PT Forindoprima Perkasa',
        //         product: 'PT1212AS',
        //         pcs_in: 0,
        //         pcs_out: 60,
        //         finance_mutation_date: '2026-02-02'
        //     },
        //     {
        //         id: 28,
        //         date: '2026-01-30',
        //         type: 'GR',
        //         document_no: 'GR/FPP/2026/005',
        //         customer: 'PT Semen Indonesia',
        //         company: 'PT Forindoprima Perkasa',
        //         product: 'PT1210AS',
        //         pcs_in: 170,
        //         pcs_out: 0,
        //         finance_mutation_date: '2026-02-02'
        //     },
        //     {
        //         id: 29,
        //         date: '2026-01-28',
        //         type: 'DN',
        //         document_no: 'DN/FPP/2026/004',
        //         customer: 'PT Garuda Indonesia',
        //         company: 'PT Forindoprima Perkasa',
        //         product: 'PT1210AS',
        //         pcs_in: 0,
        //         pcs_out: 85,
        //         finance_mutation_date: '2026-01-30'
        //     },
        //     {
        //         id: 30,
        //         date: '2026-01-28',
        //         type: 'GR',
        //         document_no: 'GR/FPP/2026/004',
        //         customer: 'PT Santos Jaya Abadi',
        //         company: 'PT Forindoprima Perkasa',
        //         product: 'PT1210AS',
        //         pcs_in: 145,
        //         pcs_out: 0,
        //         finance_mutation_date: '2026-01-30'
        //     },
        // ];
        let stockMovementData = [];
    </script>
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
            white-space: nowrap;
        }

        .table tbody tr {
            transition: background-color 0.15s ease;
        }

        .table tbody tr:hover {
            background-color: #f1f8f4;
        }

        /* Daily Total Row Styling */
        .table-info {
            background-color: #cfe2ff !important;
        }

        .table-info:hover {
            background-color: #cfe2ff !important;
        }

        /* Starting Stock Row Styling */
        .table-warning {
            background-color: #fff3cd !important;
        }

        .table-warning:hover {
            background-color: #fff3cd !important;
        }

        /* Grand Total Row Styling */
        #grandTotalRow {
            background-color: #e9ecef !important;
            border-top: 2px solid #198754;
            border-bottom: 2px solid #198754;
        }

        #grandTotalRow:hover {
            background-color: #e9ecef !important;
        }

        .badge {
            font-weight: 500;
            font-size: 0.75rem;
            padding: 0.35em 0.65em;
        }

        .pagination-sm .page-link {
            padding: 0.25rem 0.5rem;
            font-size: 0.8125rem;
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

        /* Print Styles - Professional ERP Report */
        /* Screen styles - hide print container off-screen */
        #printContainer {
            position: absolute;
            left: -9999px;
            top: 0;
            width: 100%;
        }

        @media print {

            /* Hide everything on screen */
            body * {
                visibility: hidden;
            }

            /* Show only print container and its contents */
            #printContainer,
            #printContainer * {
                visibility: visible;
            }

            /* Position print container */
            #printContainer {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }

            /* Force proper spacing */
            html,
            body {
                margin: 0 !important;
                padding: 0 !important;
                height: auto !important;
            }

            /* Exact color printing */
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            /* Page setup */
            @page {
                size: A4 landscape;
                margin: 0;
            }
        }

        /* Print content styles */
        .stock-report-print {
            font-family: 'Arial', sans-serif;
            font-size: 9pt;
            color: #000;
            background: white;
            padding: 1cm;
        }

        .stock-report-print .header {
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }

        .stock-report-print .header-content {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 15px;
        }

        .stock-report-print .company-logo {
            height: 40px;
            width: auto;
            object-fit: contain;
        }

        .stock-report-print .company-name {
            font-size: 18pt;
            font-weight: bold;
            color: #000;
            letter-spacing: 2px;
        }

        .stock-report-print .report-title {
            font-size: 14pt;
            font-weight: bold;
            text-align: center;
            margin: 10px 0;
            text-transform: uppercase;
        }

        .stock-report-print .report-info {
            display: grid;
            grid-template-columns: 150px 1fr;
            gap: 3px 10px;
            margin-bottom: 15px;
            font-size: 9pt;
        }

        .stock-report-print .info-label {
            font-weight: bold;
        }

        .stock-report-print table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .stock-report-print table th,
        .stock-report-print table td {
            border: 1px solid #000;
            padding: 4px 6px;
            font-size: 8pt;
            line-height: 1.3;
        }

        .stock-report-print table th {
            background-color: #e0e0e0;
            font-weight: bold;
            text-align: center;
        }

        .stock-report-print table td.text-center {
            text-align: center;
        }

        .stock-report-print table td.text-right {
            text-align: right;
        }

        .stock-report-print .summary-section {
            margin-top: 15px;
            padding: 10px;
            border: 2px solid #000;
            display: flex;
            justify-content: space-around;
            background-color: #f5f5f5;
        }

        .stock-report-print .summary-item {
            text-align: center;
        }

        .stock-report-print .summary-label {
            font-size: 8pt;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .stock-report-print .summary-value {
            font-size: 12pt;
            font-weight: bold;
        }

        .stock-report-print .footer-note {
            margin-top: 15px;
            font-size: 7pt;
            text-align: center;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 5px;
        }

        .stock-report-print tfoot td {
            background-color: #e8e8e8 !important;
            font-weight: bold;
        }

        /* Export dropdown styles */
        .header-action.dropdown-toggle::after {
            margin-left: 0.5em;
        }

        .dropdown-menu .dropdown-item {
            display: flex;
            align-items: center;
            padding: 0.5rem 1rem;
        }

        .dropdown-menu .dropdown-item:hover {
            background-color: #f8f9fa;
        }

        /* Match Select2 to Bootstrap form-select-sm */
        .select2-container--default .select2-selection--single {
            height: calc(1.5em + 0.5rem + 4px);
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            line-height: 1.5;

            display: flex;
            align-items: center;

            box-sizing: border-box;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            padding-left: 0;
            padding-right: 0;
            line-height: 1.5;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 100%;
            display: flex;
            align-items: center;
        }
    </style>
@endpush

@push('scripts')
    {{-- SheetJS for Excel Export --}}
    <script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>

    {{-- jsPDF for PDF Export --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // State management
            let currentFilters = {
                month: '',
                year: '',
                company: '',
                product: '',
                ckdcust: '',
                warehouse: '',
                search: ''
            };

            // Starting stock configuration per company and product
            let startingStockData = {};
            // startingStockData = {
            //     'PT Yanasurya Bhaktipersada': {
            //         'PT1210AS': 500,
            //         'PT1210BS': 300,
            //         'PT1212AS': 200
            //     },
            //     'PT Yanaprima Hastapersada': {
            //         'PT1210AS': 400,
            //         'PT1210BS': 250,
            //         'PT1212AS': 180
            //     },
            //     'PT Forindoprima Perkasa': {
            //         'PT1210AS': 350,
            //         'PT1210BS': 200,
            //         'PT1212AS': 150
            //     }
            // };

            // DOM elements
            const tableBody = document.getElementById('reportTableBody');
            const grandTotalRow = document.getElementById('grandTotalRow');
            const paginationInfo = document.getElementById('paginationInfo');
            const searchInput = document.getElementById('searchInput');
            const clearSearchBtn = document.getElementById('clearSearch');

            // Initialize Select2
            $('#filterCompany').select2({
                width: '100%',
                placeholder: '{{ __('Select Company...') }}'
            });

            // Set default company to PT Yanasurya Bhaktipersada
            $('#filterCompany').val('PT Yanasurya Bhaktipersada').trigger('change');
            currentFilters.company = 'PT Yanasurya Bhaktipersada';

            $('#filterProduct').select2({
                width: '100%',
                placeholder: '{{ __('All Products') }}',
                allowClear: true
            });

            $('#filterWarehouse').select2({
                width: '100%',
                placeholder: '{{ __('All Warehouses') }}',
                allowClear: true
            });

            // Populate year dropdown and set current year as default
            const yearSelect = document.getElementById('filterYear');
            const currentYear = new Date().getFullYear();
            for (let year = currentYear; year >= currentYear - 5; year--) {
                const option = document.createElement('option');
                option.value = year;
                option.textContent = year;
                yearSelect.appendChild(option);
            }
            yearSelect.value = currentYear; // Set default to current year

            // Set default month to current month
            const currentMonth = new Date().getMonth() + 1;
            document.getElementById('filterMonth').value = currentMonth;

            // Update initial filters with current month and year
            currentFilters.month = currentMonth.toString();
            currentFilters.year = currentYear.toString();

            // Filter handlers
            document.getElementById('filterMonth').addEventListener('change', function() {
                currentFilters.month = this.value;
                filterAndRender();
            });

            document.getElementById('filterYear').addEventListener('change', function() {
                currentFilters.year = this.value;
                filterAndRender();
            });

            $('#filterCompany').on('change', function() {
                currentFilters.company = this.value;
                
                // Ambil ckdcust dari selected option
                const selectedOption = $(this).find('option:selected');
                currentFilters.ckdcust = selectedOption.data('ckdcust') || '';
                
                loadStokMovement();
                filterAndRender();
            });

            $('#filterProduct').on('change', function() {
                currentFilters.product = this.value;
                filterAndRender();
            });

            $('#filterWarehouse').on('change', function() {
                currentFilters.warehouse = this.value;
                filterAndRender();
            });

            searchInput.addEventListener('input', function() {
                currentFilters.search = this.value.trim();
                filterAndRender();
                clearSearchBtn.classList.toggle('d-none', !currentFilters.search);
            });

            clearSearchBtn.addEventListener('click', function() {
                searchInput.value = '';
                currentFilters.search = '';
                this.classList.add('d-none');
                filterAndRender();
            });

            document.getElementById('resetFilters').addEventListener('click', function() {
                document.getElementById('filterMonth').value = '';
                document.getElementById('filterYear').value = '';
                $('#filterCompany').val('').trigger('change');
                $('#filterProduct').val('').trigger('change');
                $('#filterWarehouse').val('').trigger('change');
                searchInput.value = '';
                clearSearchBtn.classList.add('d-none');
                currentFilters = {
                    month: '',
                    year: '',
                    company: '',
                    product: '',
                    warehouse: '',
                    search: ''
                };
                filterAndRender();
            });

            // Filter and render data
            function filterAndRender() {
                // Check if company is selected
                if (!currentFilters.company) {
                    // Hide data and show message
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-building display-4 d-block mb-3"></i>
                                    <p class="mb-1 fw-semibold">{{ __('Please select a company') }}</p>
                                    <p class="small mb-0">{{ __('Choose a company from the filter above to view stock movements') }}</p>
                                </div>
                            </td>
                        </tr>
                    `;
                    grandTotalRow.style.display = 'none';
                    paginationInfo.textContent = '{{ __('Please select a company to view data') }}';

                    return;
                }

                let filteredData = stockMovementData.filter(item => {
                    const itemDate = new Date(item.date);
                    const itemMonth = itemDate.getMonth() + 1;
                    const itemYear = itemDate.getFullYear();

                    // Company filter (MANDATORY)
                    if (item.company !== currentFilters.company) {
                        return false;
                    }

                    // Month filter
                    if (currentFilters.month && itemMonth !== parseInt(currentFilters.month)) {
                        return false;
                    }

                    // Year filter
                    if (currentFilters.year && itemYear !== parseInt(currentFilters.year)) {
                        return false;
                    }

                    // Product filter
                    if (currentFilters.product && item.product !== currentFilters.product) {
                        return false;
                    }

                    // Warehouse filter
                    if (currentFilters.warehouse && item.warehouse !== currentFilters.warehouse) {
                        return false;
                    }

                    // Search filter
                    if (currentFilters.search) {
                        const searchLower = currentFilters.search.toLowerCase();
                        return (
                            item.document_no.toLowerCase().includes(searchLower) ||
                            item.customer.toLowerCase().includes(searchLower) ||
                            item.product.toLowerCase().includes(searchLower)
                        );
                    }

                    return true;
                });

                // Sort by date descending
                filteredData.sort((a, b) => new Date(b.date) - new Date(a.date));

                // Calculate running balance
                let runningBalance = 0;
                filteredData.forEach(item => {
                    runningBalance += item.pcs_in - item.pcs_out;
                    item.balance = runningBalance;
                });

                // Update grand total row
                updateGrandTotalRow(filteredData);

                // Render all data
                renderTable(filteredData);

                // Update info text
                const total = filteredData.length;
                paginationInfo.textContent = `{{ __('Showing') }} ${total} {{ __('records') }}`;

                // Update print data attributes
                updatePrintAttributes(filteredData);
            }

            // Update grand total row (at top)
            function updateGrandTotalRow(data) {
                if (data.length === 0) {
                    grandTotalRow.style.display = 'none';
                    return;
                }

                const totalIn = data.reduce((sum, item) => sum + item.pcs_in, 0);
                const totalOut = data.reduce((sum, item) => sum + item.pcs_out, 0);

                // Get starting stock
                const startingStock = getStartingStock();

                // Calculate cumulative stock sum (sum of daily stock values)
                let runningBalance = startingStock;
                let balanceTotal = 0;

                // Group by date and calculate daily balance sums
                const groupedByDate = {};
                data.forEach(item => {
                    const date = item.date;
                    if (!groupedByDate[date]) {
                        groupedByDate[date] = [];
                    }
                    groupedByDate[date].push(item);
                });

                // Sort dates
                const sortedDates = Object.keys(groupedByDate).sort((a, b) => new Date(a) - new Date(b));

                sortedDates.forEach(date => {
                    const dailyTransactions = groupedByDate[date];

                    // Process all transactions for this day
                    dailyTransactions.forEach(item => {
                        runningBalance += item.pcs_in - item.pcs_out;
                    });

                    // Add this day's ending balance to the cumulative total
                    balanceTotal += runningBalance;
                });

                document.getElementById('headerTotalIn').textContent = totalIn.toLocaleString();
                document.getElementById('headerTotalOut').textContent = totalOut.toLocaleString();
                document.getElementById('headerBalance').textContent = balanceTotal.toLocaleString();

                grandTotalRow.style.display = '';
            }

            // Get starting stock based on filters
            function getStartingStock() {
                if (!currentFilters.company || !currentFilters.year || !currentFilters.month) return 0;
                
                const companyStock = startingStockData[currentFilters.company] || {};
                const yearMonth = currentFilters.year+currentFilters.month.padStart(2, '0');
                
                if (currentFilters.product) {
                    // Specific product
                    const productStock = companyStock[currentFilters.product] || {};
                    
                    return productStock[currentFilters.year+currentFilters.month.padStart(2, '0')] || 0;
                } 
                else {
                    // Sum all products for selected yearMonth
                    return Object.values(companyStock).reduce((sum, product) => {
                        return sum + (product[currentFilters.year+currentFilters.month.padStart(2, '0')] || 0);
                    }, 0);
                }
            }

            // Update print data attributes for header
            function updatePrintAttributes(data) {
                const tableResponsive = document.querySelector('.table-responsive');
                if (tableResponsive) {
                    let period = '';
                    if (currentFilters.month && currentFilters.year) {
                        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                            'July', 'August', 'September', 'October', 'November', 'December'
                        ];
                        period = `${monthNames[currentFilters.month - 1]} ${currentFilters.year}`;
                    } else if (currentFilters.year) {
                        period = `Year ${currentFilters.year}`;
                    } else {
                        period = 'All Periods';
                    }

                    if (currentFilters.company) {
                        period += ` - ${currentFilters.company}`;
                    }
                    if (currentFilters.product) {
                        period += ` - ${currentFilters.product}`;
                    }

                    const printDate = new Date().toLocaleString('en-GB', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });

                    tableResponsive.setAttribute('data-period', period);
                    tableResponsive.setAttribute('data-print-date', printDate);
                }
            }

            // Render table with daily sections
            function renderTable(data) {
                if (data.length === 0) {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-inbox display-4 d-block mb-3"></i>
                                    <p class="mb-1 fw-semibold">{{ __('No records found') }}</p>
                                    <p class="small mb-0">{{ __('Try adjusting your filters') }}</p>
                                </div>
                            </td>
                        </tr>
                    `;
                    return;
                }

                // Get starting stock
                const startingStock = getStartingStock();
                let runningBalance = startingStock;

                // Build starting stock row
                const startingStockRow = `
                    <tr class="table-warning">
                        <td colspan="6" class="px-3 py-2 fw-semibold text-end">{{ __('Starting Stock') }}</td>
                        <td class="px-3 py-2 text-center"></td>
                        <td class="px-3 py-2 text-center"></td>
                        <td class="px-3 py-2 text-center"><span class="fw-semibold">${startingStock}</span></td>
                        <td class="px-3 py-2 text-center"></td>
                    </tr>
                `;

                // Group data by date
                const groupedByDate = {};
                data.forEach(item => {
                    const date = item.date;
                    if (!groupedByDate[date]) {
                        groupedByDate[date] = [];
                    }
                    groupedByDate[date].push(item);
                });

                // Sort dates ascending
                const sortedDates = Object.keys(groupedByDate).sort((a, b) => new Date(a) - new Date(b));

                const formatDate = (dateStr) => {
                    const date = new Date(dateStr);
                    const day = String(date.getDate()).padStart(2, '0');
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const year = String(date.getFullYear()).slice(-2);
                    return `${day}/${month}/${year}`;
                };

                let allRows = '';

                sortedDates.forEach(date => {
                    const dailyTransactions = groupedByDate[date];

                    // Track daily totals
                    let dailyIn = 0;
                    let dailyOut = 0;

                    // Render each transaction for this date
                    dailyTransactions.forEach(item => {
                        // Update balance
                        runningBalance += item.pcs_in - item.pcs_out;

                        // Track daily totals
                        dailyIn += item.pcs_in;
                        dailyOut += item.pcs_out;

                        const typeBadge = item.type === 'DN' ?
                            '<span class="badge bg-danger">DN</span>' :
                            '<span class="badge bg-success">GR</span>';

                        const balanceClass = runningBalance >= 0 ? 'text-success' : 'text-danger';

                        allRows += `
                            <tr>
                                <td class="px-3 py-2">${formatDate(item.date)}</td>
                                <td class="px-3 py-2">${typeBadge}</td>
                                <td class="px-3 py-2">
                                    <span class="fw-semibold text-primary">${item.document_no}</span>
                                </td>
                                <td class="px-3 py-2">${item.customer}</td>
                                <td class="px-3 py-2">${item.warehouse || '-'}</td>
                                <td class="px-3 py-2">${item.product}</td>
                                <td class="px-3 py-2 text-center">
                                    ${item.pcs_in > 0 ? `<span class="text-success fw-semibold">+${item.pcs_in}</span>` : ''}
                                </td>
                                <td class="px-3 py-2 text-center">
                                    ${item.pcs_out > 0 ? `<span class="text-danger fw-semibold">-${item.pcs_out}</span>` : ''}
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <span class="fw-semibold ${balanceClass}">${runningBalance}</span>
                                </td>
                                <td class="px-3 py-2 text-center">
                                    ${item.finance_mutation_date ? formatDate(item.finance_mutation_date) : '-'}
                                </td>
                            </tr>
                        `;
                    });

                    // Add daily section end row
                    const balanceClass = runningBalance >= 0 ? 'text-success' : 'text-danger';
                    allRows += `
                        <tr class="table-info fw-semibold">
                            <td class="px-3 py-2">${formatDate(date)}</td>
                            <td colspan="5" class="px-3 py-2 text-end">{{ __('Daily Total') }}</td>
                            <td class="px-3 py-2 text-center">${dailyIn}</td>
                            <td class="px-3 py-2 text-center">${dailyOut}</td>
                            <td class="px-3 py-2 text-center">
                                <span class="${balanceClass}">${runningBalance}</span>
                            </td>
                            <td class="px-3 py-2 text-center"></td>
                        </tr>
                    `;
                });

                tableBody.innerHTML = startingStockRow + allRows;
            }

            // Print functionality
            document.getElementById('printReport').addEventListener('click', function() {
                if (!currentFilters.company) {
                    alert('{{ __('Please select a company first') }}');
                    return;
                }

                // Generate print content
                generatePrintContent();

                // Temporarily change page title to blank to avoid header text
                const originalTitle = document.title;
                document.title = '';

                // Small delay to ensure content is rendered before print
                setTimeout(() => {
                    const style = document.createElement('style');
                    style.textContent = '@page { margin: 0; } body { margin: 0; }';
                    document.head.appendChild(style);
                    window.print();

                    // Restore original title after print dialog
                    setTimeout(() => {
                        document.title = originalTitle;
                    }, 100);
                }, 200);
            });

            // Generate print content
            function generatePrintContent() {
                const printContainer = document.getElementById('printContainer');

                // Get filtered data
                let filteredData = stockMovementData.filter(item => {
                    const itemDate = new Date(item.date);
                    const itemMonth = itemDate.getMonth() + 1;
                    const itemYear = itemDate.getFullYear();

                    if (item.company !== currentFilters.company) return false;
                    if (currentFilters.month && itemMonth !== parseInt(currentFilters.month)) return false;
                    if (currentFilters.year && itemYear !== parseInt(currentFilters.year)) return false;
                    if (currentFilters.product && item.product !== currentFilters.product) return false;

                    if (currentFilters.search) {
                        const searchLower = currentFilters.search.toLowerCase();
                        return (
                            item.document_no.toLowerCase().includes(searchLower) ||
                            item.customer.toLowerCase().includes(searchLower)
                        );
                    }

                    return true;
                });

                // Sort by date descending
                filteredData.sort((a, b) => new Date(b.date) - new Date(a.date));

                // Calculate running balance
                let runningBalance = 0;
                filteredData.forEach(item => {
                    runningBalance += item.pcs_in - item.pcs_out;
                    item.balance = runningBalance;
                });

                // Generate period text
                let periodText = '';
                if (currentFilters.month && currentFilters.year) {
                    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                        'July', 'August', 'September', 'October', 'November', 'December'
                    ];
                    periodText = `${monthNames[currentFilters.month - 1]} ${currentFilters.year}`;
                } else if (currentFilters.year) {
                    periodText = `Year ${currentFilters.year}`;
                } else {
                    periodText = 'All Periods';
                }

                let filterText = `Company: ${currentFilters.company}`;
                if (currentFilters.product) {
                    filterText += ` | Product: ${currentFilters.product}`;
                }

                const printDate = new Date().toLocaleString('en-GB', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });

                // Calculate totals
                const totalIn = filteredData.reduce((sum, item) => sum + item.pcs_in, 0);
                const totalOut = filteredData.reduce((sum, item) => sum + item.pcs_out, 0);
                const netMovement = totalIn - totalOut;

                // Generate table rows
                let tableRows = '';
                filteredData.forEach(item => {
                    const typeText = item.type === 'DN' ? 'DN' : 'GR';
                    tableRows += `
                        <tr>
                            <td>${formatDateForPrint(item.date)}</td>
                            <td style="text-align: center;">${typeText}</td>
                            <td>${item.document_no}</td>
                            <td>${item.customer}</td>
                            <td style="text-align: center;">${item.warehouse || '-'}</td>
                            <td style="text-align: center;">${item.product}</td>
                            <td class="text-center">${item.pcs_in > 0 ? item.pcs_in : '-'}</td>
                            <td class="text-center">${item.pcs_out > 0 ? item.pcs_out : '-'}</td>
                            <td class="text-center">${item.balance}</td>
                            <td class="text-center">${item.finance_mutation_date ? formatDateForPrint(item.finance_mutation_date) : '-'}</td>
                        </tr>
                    `;
                });

                printContainer.innerHTML = `
                    <div class="stock-report-print">
                        <div class="header">
                            <div class="header-content">
                                <img src="/images/logo-bw.png" alt="Company Logo" class="company-logo">
                                <div class="company-name">${currentFilters.company.toUpperCase()}</div>
                            </div>
                        </div>

                        <div class="report-title">STOCK MOVEMENT REPORT</div>

                        <div class="report-info">
                            <div class="info-label">Period:</div>
                            <div>${periodText}</div>
                            <div class="info-label">Filters:</div>
                            <div>${filterText}</div>
                            <div class="info-label">Printed:</div>
                            <div>${printDate}</div>
                            <div class="info-label">Total Records:</div>
                            <div>${filteredData.length} transactions</div>
                        </div>

                        <table>
                            <thead>
                                <tr>
                                    <th style="width: 10%;">Date</th>
                                    <th style="width: 7%;">Type</th>
                                    <th style="width: 13%;">Document No.</th>
                                    <th style="width: 25%;">From/To</th>
                                    <th style="width: 10%;">Warehouse</th>
                                    <th style="width: 12%;">Product</th>
                                    <th style="width: 9%;">Pcs In</th>
                                    <th style="width: 9%;">Pcs Out</th>
                                    <th style="width: 8%;">Balance</th>
                                    <th style="width: 10%;">Mut. Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${tableRows}
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="6" style="text-align: right; font-weight: bold;">GRAND TOTAL:</td>
                                    <td class="text-center">${totalIn.toLocaleString()}</td>
                                    <td class="text-center">${totalOut.toLocaleString()}</td>
                                    <td class="text-center">${netMovement.toLocaleString()}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>

                        <div class="summary-section">
                            <div class="summary-item">
                                <div class="summary-label">Total In</div>
                                <div class="summary-value">${totalIn.toLocaleString()} pcs</div>
                            </div>
                            <div class="summary-item">
                                <div class="summary-label">Total Out</div>
                                <div class="summary-value">${totalOut.toLocaleString()} pcs</div>
                            </div>
                            <div class="summary-item">
                                <div class="summary-label">Net Movement</div>
                                <div class="summary-value">${netMovement.toLocaleString()} pcs</div>
                            </div>
                            <div class="summary-item">
                                <div class="summary-label">Transactions</div>
                                <div class="summary-value">${filteredData.length}</div>
                            </div>
                        </div>

                        <div class="footer-note">
                            This report is electronically generated and valid without wet signature
                        </div>
                    </div>
                `;
            }

            // Helper function to format date for print
            function formatDateForPrint(dateStr) {
                const date = new Date(dateStr);
                const day = String(date.getDate()).padStart(2, '0');
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const year = String(date.getFullYear()).slice(-2);
                return `${day}/${month}/${year}`;
            }

            // Export to Excel
            document.getElementById('exportExcel').addEventListener('click', function(e) {
                e.preventDefault();

                if (!currentFilters.company) {
                    alert('{{ __('Please select a company first') }}');
                    return;
                }

                // Get filtered data
                let exportData = getFilteredData();

                // Prepare data for Excel
                const worksheetData = [
                    ['Stock Movement Report'],
                    ['Company:', currentFilters.company],
                    ['Generated:', new Date().toLocaleString()],
                    [],
                    ['Date', 'Type', 'Document No.', 'From/To', 'Warehouse', 'Product', 'Pcs In',
                        'Pcs Out', 'Balance', 'Finance Mutation Date'
                    ]
                ];

                exportData.forEach(item => {
                    worksheetData.push([
                        formatDate(item.date),
                        item.type,
                        item.document_no,
                        item.customer,
                        item.warehouse || '-',
                        item.product,
                        item.pcs_in || 0,
                        item.pcs_out || 0,
                        item.balance || 0,
                        item.finance_mutation_date ? formatDate(item
                            .finance_mutation_date) : ''
                    ]);
                });

                // Add totals
                const totalIn = exportData.reduce((sum, item) => sum + item.pcs_in, 0);
                const totalOut = exportData.reduce((sum, item) => sum + item.pcs_out, 0);
                worksheetData.push([]);
                worksheetData.push(['', '', '', '', '', 'TOTAL', totalIn, totalOut, totalIn - totalOut,
                    ''
                ]);

                const wb = XLSX.utils.book_new();
                const ws = XLSX.utils.aoa_to_sheet(worksheetData);

                // Set column widths
                ws['!cols'] = [{
                        wch: 12
                    }, {
                        wch: 8
                    }, {
                        wch: 15
                    }, {
                        wch: 30
                    },
                    {
                        wch: 12
                    }, {
                        wch: 10
                    }, {
                        wch: 10
                    }, {
                        wch: 10
                    }, {
                        wch: 15
                    }
                ];

                XLSX.utils.book_append_sheet(wb, ws, 'Stock Movement');
                XLSX.writeFile(wb, `Stock_Movement_${currentFilters.company}_${new Date().getTime()}.xlsx`);
            });

            // Export to PDF
            document.getElementById('exportPDF').addEventListener('click', function(e) {
                e.preventDefault();

                if (!currentFilters.company) {
                    alert('{{ __('Please select a company first') }}');
                    return;
                }

                const {
                    jsPDF
                } = window.jspdf;
                const doc = new jsPDF('l', 'mm', 'a4'); // landscape

                // Get filtered data
                let exportData = getFilteredData();

                // Add title
                doc.setFontSize(16);
                doc.text('Stock Movement Report', 14, 15);

                doc.setFontSize(10);
                doc.text(`Company: ${currentFilters.company}`, 14, 22);
                doc.text(`Generated: ${new Date().toLocaleString()}`, 14, 28);

                // Prepare table data
                const tableData = exportData.map(item => [
                    formatDate(item.date),
                    item.type,
                    item.document_no,
                    item.customer.substring(0, 25),
                    item.warehouse || '-',
                    item.product,
                    item.pcs_in || '-',
                    item.pcs_out || '-',
                    item.balance || 0,
                    item.finance_mutation_date ? formatDate(item.finance_mutation_date) : '-'
                ]);

                // Calculate totals
                const totalIn = exportData.reduce((sum, item) => sum + item.pcs_in, 0);
                const totalOut = exportData.reduce((sum, item) => sum + item.pcs_out, 0);

                doc.autoTable({
                    startY: 34,
                    head: [
                        ['Date', 'Type', 'Doc No.', 'From/To', 'Warehouse', 'Product', 'In',
                            'Out', 'Balance', 'Mut. Date'
                        ]
                    ],
                    body: tableData,
                    foot: [
                        ['', '', '', '', 'TOTAL', totalIn, totalOut, totalIn - totalOut, '']
                    ],
                    theme: 'grid',
                    headStyles: {
                        fillColor: [25, 135, 84],
                        fontSize: 8
                    },
                    footStyles: {
                        fillColor: [248, 249, 250],
                        textColor: [0, 0, 0],
                        fontStyle: 'bold'
                    },
                    styles: {
                        fontSize: 7,
                        cellPadding: 2
                    },
                    columnStyles: {
                        0: {
                            cellWidth: 22
                        },
                        1: {
                            cellWidth: 15
                        },
                        2: {
                            cellWidth: 28
                        },
                        3: {
                            cellWidth: 55
                        },
                        4: {
                            cellWidth: 22
                        },
                        5: {
                            cellWidth: 18,
                            halign: 'center'
                        },
                        6: {
                            cellWidth: 18,
                            halign: 'center'
                        },
                        7: {
                            cellWidth: 18,
                            halign: 'center'
                        },
                        8: {
                            cellWidth: 22,
                            halign: 'center'
                        }
                    }
                });

                doc.save(`Stock_Movement_${currentFilters.company}_${new Date().getTime()}.pdf`);
            });

            // Export to CSV
            document.getElementById('exportCSV').addEventListener('click', function(e) {
                e.preventDefault();

                if (!currentFilters.company) {
                    alert('{{ __('Please select a company first') }}');
                    return;
                }

                let exportData = getFilteredData();

                let csv =
                    'Date,Type,Document No.,From/To,Warehouse,Product,Pcs In,Pcs Out,Balance,Finance Mutation Date\n';

                exportData.forEach(item => {
                    csv +=
                        `${formatDate(item.date)},${item.type},"${item.document_no}","${item.customer}","${item.warehouse || '-'}",${item.product},${item.pcs_in || 0},${item.pcs_out || 0},${item.balance || 0},${item.finance_mutation_date ? formatDate(item.finance_mutation_date) : ''}\n`;
                });

                // Calculate totals
                const totalIn = exportData.reduce((sum, item) => sum + item.pcs_in, 0);
                const totalOut = exportData.reduce((sum, item) => sum + item.pcs_out, 0);
                csv += `\n,,,,TOTAL,${totalIn},${totalOut},${totalIn - totalOut},\n`;

                const blob = new Blob([csv], {
                    type: 'text/csv'
                });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `Stock_Movement_${currentFilters.company}_${new Date().getTime()}.csv`;
                a.click();
                window.URL.revokeObjectURL(url);
            });

            // Helper function to get filtered data
            function getFilteredData() {
                let filteredData = stockMovementData.filter(item => {
                    const itemDate = new Date(item.date);
                    const itemMonth = itemDate.getMonth() + 1;
                    const itemYear = itemDate.getFullYear();

                    if (item.company !== currentFilters.company) return false;
                    if (currentFilters.month && itemMonth !== parseInt(currentFilters.month)) return false;
                    if (currentFilters.year && itemYear !== parseInt(currentFilters.year)) return false;
                    if (currentFilters.product && item.product !== currentFilters.product) return false;

                    if (currentFilters.search) {
                        const searchLower = currentFilters.search.toLowerCase();
                        return (
                            item.document_no.toLowerCase().includes(searchLower) ||
                            item.customer.toLowerCase().includes(searchLower)
                        );
                    }

                    return true;
                });

                // Sort by date descending
                filteredData.sort((a, b) => new Date(b.date) - new Date(a.date));

                // Calculate running balance
                let runningBalance = 0;
                filteredData.forEach(item => {
                    runningBalance += item.pcs_in - item.pcs_out;
                    item.balance = runningBalance;
                });

                return filteredData;
            }

            // Helper function to format date
            function formatDate(dateStr) {
                const date = new Date(dateStr);
                const day = String(date.getDate()).padStart(2, '0');
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const year = String(date.getFullYear()).slice(-2);
                return `${day}/${month}/${year}`;
            }


            async function loadStokMovement() {
                let month=$("#filterMonth").val();
                let year=$("#filterYear").val();
                let cthnbln=year+(month.padStart(2, '0'));
                const bodyTableStock = document.getElementById('reportTableBody');
                // const selectElement = type === 'from' ? fromAddressSelect : toAddressSelect;
                bodyTableStock.innerHTML = '<tr><td>Loading...</td></tr>';
                let ckdcust = currentFilters.ckdcust || '{{ Auth::user()->ckdcust }}';
                try {
                    const response = await fetch(`/api/stockmovements/stock/${ckdcust ?? ''}/${cthnbln ?? ''}`);
                    const stocks = await response.json();
                    let strhtml = "";
                    let idx = 1;
                    stocks.forEach(stock => {
                        let cstatus = stock.cstatus;
                        if (cstatus == "A") {
                            if (!startingStockData[stock.ckdcust]) {
                                startingStockData[stock.ckdcust] = {}; // create company object
                            }
                            const date = new Date(stock.dtglbukti);
                            const result = date.getFullYear().toString() + String(date.getMonth() + 1).padStart(2, '0');
                            
                            if (!startingStockData[stock.ckdcust][stock.cbasic]) {
                                startingStockData[stock.ckdcust][stock.cbasic] = {}; // create basic object
                            }
                            
                            if (!startingStockData[stock.ckdcust][stock.cbasic][result]) {
                                startingStockData[stock.ckdcust][stock.cbasic][result] = 0; // create basic object
                            }

                            startingStockData[stock.ckdcust][stock.cbasic][result] += parseInt(stock.nqty);
                            return;
                        }
                        
                        let issj = false;
                        if (stock.cnobukti != null) {
                            if ((stock.cnobukti).substr(0, 2).toUpperCase() == "SJ" || (stock.cnobukti)
                                .substr(0, 2).toUpperCase() == "DN") {
                                issj = true;
                            }
                        }
                        let finance_mutation_date="";
                        if(stock.dtgltagih !== null){
                            finance_mutation_date = stock.dtgltagih;
                        }
                        else if(stock.dtglakhirtagih !== null){
                            finance_mutation_date = stock.dtglakhirtagih;
                        }
                        
                        let pcs_in = 0;
                        let pcs_out = 0;
                        let type = "";
                        let customer = "";
                        let company = "";
                        if ((stock.cnobukti).substr(0, 2).toUpperCase() == "SJ" || (stock.cnobukti)
                            .substr(0, 2).toUpperCase() == "DN") {
                            type = "DN";
                            pcs_out = stock.nqty;
                            // company = stock.sjccust_from;
                            company = stock.sjckdcust_from;
                            customer = stock.sjccust_to;
                        } else if ((stock.cnobukti).substr(0, 2).toUpperCase() == "GR" || (stock
                                .cnobukti).substr(0, 3).toUpperCase() == "BPB") {
                            type = "GR";
                            pcs_in = stock.nqty;
                            // company = stock.bpbccust_to;
                            company = stock.bpbckdcust_to;
                            customer = stock.bpbccust_from;
                        }
                        
                        stockMovementData.push({
                            id: idx,
                            date: stock.dtglbukti,
                            type: type,
                            document_no: stock.cnobukti,
                            customer: customer,
                            company: company,
                            warehouse: stock.cwarehouse || stock.warehouse || '-',
                            product: stock.cbasic,
                            pcs_in: parseInt(pcs_in),
                            pcs_out: parseInt(pcs_out),
                            finance_mutation_date: finance_mutation_date
                        });
                        idx++;

                    });
                    
                    
                    // Populate warehouse filter with unique warehouses
                    populateWarehouseFilter();
                    filterAndRender();

                } catch (error) {

                }
            }

            function populateWarehouseFilter() {
                const warehouses = new Set();
                stockMovementData.forEach(item => {
                    if (item.warehouse && item.warehouse !== '-') {
                        warehouses.add(item.warehouse);
                    }
                });

                const warehouseSelect = document.getElementById('filterWarehouse');
                const currentValue = warehouseSelect.value;

                // Clear existing options except "All Warehouses"
                warehouseSelect.innerHTML = '<option value="">{{ __('All Warehouses') }}</option>';

                // Add unique warehouses
                Array.from(warehouses).sort().forEach(warehouse => {
                    const option = document.createElement('option');
                    option.value = warehouse;
                    option.textContent = warehouse;
                    warehouseSelect.appendChild(option);
                });

                // Restore previous selection if it exists
                if (currentValue && Array.from(warehouses).includes(currentValue)) {
                    warehouseSelect.value = currentValue;
                }

                // Re-initialize Select2
                $('#filterWarehouse').select2('destroy').select2({
                    width: '100%',
                    placeholder: '{{ __('All Warehouses') }}',
                    allowClear: true
                });
            }

            // loadStokMovement();
            setTimeout(() => {
                console.log("after 2 second");
                
                let ckdcust="{{ Auth::user()->ckdcust }}";
                $("#filterCompany").val(ckdcust).change();
            }, 2000);
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            window.startStockMovementTour = function() {
                const driver = window.driver.js.driver;

                const driverObj = driver({
                    showProgress: true,
                    showButtons: ['next', 'previous', 'close'],
                    steps: [
                        {
                            popover: {
                                title: '📦 {{ __("Stock Movement Report") }}',
                                description: '{{ __("This report tracks pallet stock movements (IN & OUT) and calculates the running balance over time.") }}'
                            }
                        },
                        {
                            element: '#filterCompany',
                            popover: {
                                title: '🏢 {{ __("Company (Required)") }}',
                                description: '{{ __("Select a company first. Stock balances, starting stock, and movement calculations depend on the selected company.") }}',
                                side: 'bottom',
                                align: 'start'
                            }
                        },
                        {
                            element: '#stockMovementPeriod',
                            popover: {
                                title: '📅 {{ __("Period Filter") }}',
                                description: '{{ __("Filter stock movements by month and year to analyze a specific accounting period.") }}',
                                side: 'bottom',
                                align: 'start'
                            }
                        },
                        {
                            element: '#filterProduct',
                            popover: {
                                title: '📦 {{ __("Product Filter") }}',
                                description: '{{ __("Limit movements to a specific pallet type (e.g. PT1210AS).") }}',
                                side: 'bottom',
                                align: 'start'
                            }
                        },
                        {
                            element: '#searchInput',
                            popover: {
                                title: '🔍 {{ __("Search Transactions") }}',
                                description: '{{ __("Search by document number (DN / GR), customer, or product.") }}',
                                side: 'bottom',
                                align: 'start'
                            }
                        },
                        {
                            element: '#resetFilters',
                            popover: {
                                title: '♻️ {{ __("Reset Filters") }}',
                                description: '{{ __("Clear all filters and reload the report.") }}',
                                side: 'top',
                                align: 'center'
                            }
                        },
                        {
                            element: '#stockMovementTable',
                            popover: {
                                title: '📋 {{ __("Stock Movement Details") }}',
                                description: `
                                    {{ __("Each row represents a stock transaction:") }}<br><br>
                                    • <strong>{{ __("DN = Pallets OUT") }}</strong><br>
                                    • <strong>{{ __("GR = Pallets IN") }}</strong><br><br>
                                    {{ __("The Balance column shows the running stock after each transaction.") }}
                                `,
                                side: 'top',
                                align: 'start'
                            }
                        },
                        {
                            popover: {
                                title: '🧮 {{ __("Starting Stock & Daily Totals") }}',
                                description: `
                                    • <strong>{{ __("Starting Stock is the opening balance for the selected period.") }}</strong><br>
                                    • <strong>{{ __("Daily Total rows summarize movements per day.") }}</strong><br><br>
                                    {{ __("These rows are calculated automatically and may change based on filters.") }}
                                `
                            }
                        },
                        {
                            popover: {
                                title: '📅 {{ __("Finance Mutation Date") }}',
                                description: '{{ __("The mutation date indicates when the transaction impacts financial accounting, which may differ from the document date.") }}'
                            }
                        },
                        {
                            element: '#stockMovementActions',
                            popover: {
                                title: '⬇️ {{ __("Export & Print") }}',
                                description: '{{ __("Export the report to Excel, PDF, CSV, or print it in official ERP format.") }}',
                                side: 'left',
                                align: 'start'
                            }
                        },
                        {
                            popover: {
                                title: '✅ {{ __("You’re All Set") }}',
                                description: `
                                    {{ __("You now understand how stock movements, balances, and totals are calculated.") }}<br><br>
                                    <strong>{{ __("Tip: This report feeds into financial reconciliation and billing.") }}</strong>
                                `
                            }
                        }
                    ]
                });

                driverObj.drive();
                return driverObj;
            };

            // Register as page tour
            window.startProductTour = window.startStockMovementTour;

        });
    </script>
@endpush
