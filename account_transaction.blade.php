@extends('layouts.app')

@section('title', __('Account Transaction Report'))

@section('header-left')
    <div>
        <h2 class="h5 fw-semibold mb-0">{{ __('Account Transaction Report') }}</h2>
        <span class="text-muted small">{{ __('Daily pallet movements and rental charge calculation by pallet type') }}</span>
    </div>

    <!-- Hidden print container -->
    <div id="printContainer"></div>
@endsection

@section('content')
    <!-- Filters Card -->
    <div class="card mb-3">
        <div class="card-body py-3" id="accountTxnFilters">
            <form id="filterForm">
                <div class="row g-3 align-items-end">
                    <!-- Company Filter -->
                    <div class="col-6 col-md-3">
                        <label for="filterCompany" class="form-label small fw-semibold">
                            {{ __('Company') }} <span class="text-danger">*</span>
                        </label>
                        <select class="form-select form-select-sm searchable-select" id="filterCompany" name="company">
                            <option value="">{{ __('Select Company...') }}</option>
                            @foreach ($arrcustomer as $cust)
                                <option value="{{ $cust->ckdcust }}">{{ $cust->cnmcust }}</option>
                            @endforeach
                            {{-- <option value="{{ $cust->ckdcust }}">{{ $cust->cnmcust }}</option> --}}
                            {{-- <option value="PT Yanasurya Bhaktipersada">PT Yanasurya Bhaktipersada</option>
                            <option value="PT Yanaprima Hastapersada">PT Yanaprima Hastapersada (Subsidiary)</option>
                            <option value="PT Forindoprima Perkasa">PT Forindoprima Perkasa (Subsidiary)</option> --}}
                        </select>
                    </div>

                    <!-- Month Filter -->
                    <div class="col-3 col-md-1" id="accountTxnPeriodFilter">
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
                    <div class="col-3 col-md-1">
                        <label for="filterYear" class="form-label small fw-semibold">{{ __('Year') }}</label>
                        <select class="form-select form-select-sm" id="filterYear" name="year">
                            <option value="">{{ __('All Years') }}</option>
                        </select>
                    </div>

                    <div class="w-100 d-md-none"></div>

                    <!-- Customer Filter -->
                    <div class="col-4 col-md-2">
                        <label for="filterCustomer" class="form-label small fw-semibold">{{ __('Customer') }}</label>
                        <select class="form-select form-select-sm searchable-select" id="filterCustomer" name="customer">
                            <option value="">{{ __('All Customers') }}</option>
                            {{-- <option value="PT Gudang Garam Tbk">PT Gudang Garam Tbk</option>
                            <option value="PT Indofood CBP Sukses Makmur">PT Indofood CBP Sukses Makmur</option>
                            <option value="PT Nestle Indonesia">PT Nestle Indonesia</option>
                            <option value="PT Wings Surya">PT Wings Surya</option>
                            <option value="PT Frisian Flag Indonesia">PT Frisian Flag Indonesia</option> --}}
                            @foreach ($customernetwork as $cust)
                                <option value="{{ $cust->cnmcust }}">{{ $cust->cnmcust }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Search -->
                    <div class="col-6 col-md-4">
                        <label for="searchInput" class="form-label small fw-semibold">{{ __('Search') }}</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" class="form-control" id="searchInput"
                                placeholder="{{ __('Search DN/GR, Customer...') }}">
                            <button type="button" class="btn btn-outline-secondary d-none" id="clearSearch">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="col-2 col-md-1 d-flex gap-1">
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
                <i class="bi bi-table me-2"></i>{{ __('Account Transaction Details') }}
            </span>
            <div class="d-flex gap-2" id="accountTxnActions">
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
                <table class="table table-hover align-middle mb-0" id="accountTransactionTable">
                    <thead class="table-light">
                        <tr>
                            <th class="px-3 py-2" rowspan="2">{{ __('Mut. Date') }}</th>
                            <th class="px-3 py-2" rowspan="2">{{ __('Type') }}</th>
                            <th class="px-3 py-2" rowspan="2">{{ __('Doc. Number') }}</th>
                            <th class="px-3 py-2" rowspan="2">{{ __('Doc. Date') }}</th>
                            <th class="px-3 py-2" rowspan="2">{{ __('From/To') }}</th>
                            <th class="px-3 py-2 text-center" id="theadbasic1" colspan="3">
                                {{ __('PT1210AS (1200 x 1000 x 160)') }}</th>
                            <th class="px-3 py-2 text-center" id="theadbasic2" colspan="3">
                                {{ __('PF1210 (1200 x 1000 x 160)') }}</th>
                            <th class="px-3 py-2 text-center" id="theadbasic3" colspan="3">
                                {{ __('B325 (1300 x 1200 x 150)') }}</th>
                            <th class="px-3 py-2 text-center" id="theadbasic4" colspan="3">
                                {{ __('PT1212 (1200 x 1200 x 160)') }}</th>
                            <th class="px-3 py-2 text-end" rowspan="2">{{ __('Amount') }}</th>
                        </tr>
                        <tr>
                            <th class="px-3 py-2 text-center">{{ __('In') }}</th>
                            <th class="px-3 py-2 text-center">{{ __('Out') }}</th>
                            <th class="px-3 py-2 text-center">{{ __('Stock') }}</th>
                            <th class="px-3 py-2 text-center">{{ __('In') }}</th>
                            <th class="px-3 py-2 text-center">{{ __('Out') }}</th>
                            <th class="px-3 py-2 text-center">{{ __('Stock') }}</th>
                            <th class="px-3 py-2 text-center">{{ __('In') }}</th>
                            <th class="px-3 py-2 text-center">{{ __('Out') }}</th>
                            <th class="px-3 py-2 text-center">{{ __('Stock') }}</th>
                            <th class="px-3 py-2 text-center">{{ __('In') }}</th>
                            <th class="px-3 py-2 text-center">{{ __('Out') }}</th>
                            <th class="px-3 py-2 text-center">{{ __('Stock') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Grand Total Row - Appears at Top -->
                        <tr class="table-light fw-semibold" id="grandTotalRow" style="display: none;">
                            <td colspan="5" class="px-3 py-2 text-end">{{ __('Grand Total') }}</td>
                            <td class="px-3 py-2 text-center" id="headerPallet1In">0</td>
                            <td class="px-3 py-2 text-center" id="headerPallet1Out">0</td>
                            <td class="px-3 py-2 text-center" id="headerPallet1Stock">0</td>
                            <td class="px-3 py-2 text-center" id="headerPallet2In">0</td>
                            <td class="px-3 py-2 text-center" id="headerPallet2Out">0</td>
                            <td class="px-3 py-2 text-center" id="headerPallet2Stock">0</td>
                            <td class="px-3 py-2 text-center" id="headerPallet3In">0</td>
                            <td class="px-3 py-2 text-center" id="headerPallet3Out">0</td>
                            <td class="px-3 py-2 text-center" id="headerPallet3Stock">0</td>
                            <td class="px-3 py-2 text-center" id="headerPallet4In">0</td>
                            <td class="px-3 py-2 text-center" id="headerPallet4Out">0</td>
                            <td class="px-3 py-2 text-center" id="headerPallet4Stock">0</td>
                            <td class="px-3 py-2 text-end" id="headerTotalAmount">Rp 0</td>
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
        // Sample data with CORRECTED amounts based on stock holdings
        // const accountTransactionData = [
        //     // January 30, 2026 - PT Yanasurya Bhaktipersada
        //     {
        //         id: 1,
        //         finance_mutation_date: '2026-01-30',
        //         document_date: '2026-01-29',
        //         type: 'DN',
        //         document_no: 'DN/2026/030',
        //         customer: 'PT Gudang Garam Tbk',
        //         company: 'PT Yanasurya Bhaktipersada',
        //         pallet1_in: 0,
        //         pallet1_out: 150,
        //         pallet2_in: 0,
        //         pallet2_out: 80,
        //         pallet3_in: 0,
        //         pallet3_out: 0,
        //         pallet4_in: 0,
        //         pallet4_out: 0,
        //         // Stock after this transaction: P1=550, P2=220, P3=200, P4=150
        //         // Amount = (550*500 + 220*450 + 200*750 + 150*550) = 656,500
        //         amount: 656500
        //     },
        //     {
        //         id: 2,
        //         finance_mutation_date: '2026-01-30',
        //         document_date: '2026-01-29',
        //         type: 'GR',
        //         document_no: 'GR/2026/028',
        //         customer: 'PT Indofood CBP Sukses Makmur',
        //         company: 'PT Yanasurya Bhaktipersada',
        //         pallet1_in: 200,
        //         pallet1_out: 0,
        //         pallet2_in: 0,
        //         pallet2_out: 0,
        //         pallet3_in: 0,
        //         pallet3_out: 0,
        //         pallet4_in: 0,
        //         pallet4_out: 0,
        //         // Stock after this transaction: P1=750, P2=220, P3=200, P4=150
        //         // Amount = (750*500 + 220*450 + 200*750 + 150*550) = 756,500
        //         amount: 756500
        //     },
        //     {
        //         id: 3,
        //         finance_mutation_date: '2026-01-30',
        //         document_date: '2026-01-30',
        //         type: 'DN',
        //         document_no: 'DN/2026/031',
        //         customer: 'PT Nestle Indonesia',
        //         company: 'PT Yanasurya Bhaktipersada',
        //         pallet1_in: 0,
        //         pallet1_out: 100,
        //         pallet2_in: 0,
        //         pallet2_out: 0,
        //         pallet3_in: 0,
        //         pallet3_out: 0,
        //         pallet4_in: 0,
        //         pallet4_out: 0,
        //         // Stock after this transaction: P1=650, P2=220, P3=200, P4=150
        //         // Amount = (650*500 + 220*450 + 200*750 + 150*550) = 706,500
        //         amount: 706500
        //     },

        //     // January 29, 2026
        //     {
        //         id: 4,
        //         finance_mutation_date: '2026-01-29',
        //         document_date: '2026-01-28',
        //         type: 'GR',
        //         document_no: 'GR/2026/027',
        //         customer: 'PT Wings Surya',
        //         company: 'PT Yanasurya Bhaktipersada',
        //         pallet1_in: 180,
        //         pallet1_out: 0,
        //         pallet2_in: 120,
        //         pallet2_out: 0,
        //         pallet3_in: 50,
        //         pallet3_out: 0,
        //         pallet4_in: 0,
        //         pallet4_out: 0,
        //         // Stock after: P1=700, P2=300, P3=200, P4=150
        //         // Amount = (700*500 + 300*450 + 200*750 + 150*550) = 767,500
        //         amount: 767500
        //     },
        //     {
        //         id: 5,
        //         finance_mutation_date: '2026-01-29',
        //         document_date: '2026-01-28',
        //         type: 'DN',
        //         document_no: 'DN/2026/029',
        //         customer: 'PT Frisian Flag Indonesia',
        //         company: 'PT Yanasurya Bhaktipersada',
        //         pallet1_in: 0,
        //         pallet1_out: 90,
        //         pallet2_in: 0,
        //         pallet2_out: 60,
        //         pallet3_in: 0,
        //         pallet3_out: 0,
        //         pallet4_in: 0,
        //         pallet4_out: 0,
        //         // Stock after: P1=610, P2=240, P3=200, P4=150
        //         // Amount = (610*500 + 240*450 + 200*750 + 150*550) = 640,500
        //         amount: 640500
        //     },

        //     // January 28, 2026
        //     {
        //         id: 6,
        //         finance_mutation_date: '2026-01-28',
        //         document_date: '2026-01-27',
        //         type: 'DN',
        //         document_no: 'DN/2026/028',
        //         customer: 'PT Unilever Indonesia Tbk',
        //         company: 'PT Yanasurya Bhaktipersada',
        //         pallet1_in: 0,
        //         pallet1_out: 200,
        //         pallet2_in: 0,
        //         pallet2_out: 0,
        //         pallet3_in: 0,
        //         pallet3_out: 0,
        //         pallet4_in: 0,
        //         pallet4_out: 50,
        //         // Stock after: P1=520, P2=180, P3=150, P4=150
        //         // Amount = (520*500 + 180*450 + 150*750 + 150*550) = 624,500
        //         amount: 624500
        //     },
        //     {
        //         id: 7,
        //         finance_mutation_date: '2026-01-28',
        //         document_date: '2026-01-27',
        //         type: 'GR',
        //         document_no: 'GR/2026/026',
        //         customer: 'PT Kao Indonesia',
        //         company: 'PT Yanasurya Bhaktipersada',
        //         pallet1_in: 160,
        //         pallet1_out: 0,
        //         pallet2_in: 100,
        //         pallet2_out: 0,
        //         pallet3_in: 0,
        //         pallet3_out: 0,
        //         pallet4_in: 0,
        //         pallet4_out: 0,
        //         // Stock after: P1=680, P2=280, P3=150, P4=150
        //         // Amount = (680*500 + 280*450 + 150*750 + 150*550) = 680,000
        //         amount: 680000
        //     },

        //     // January 27, 2026
        //     {
        //         id: 8,
        //         finance_mutation_date: '2026-01-27',
        //         document_date: '2026-01-26',
        //         type: 'GR',
        //         document_no: 'GR/2026/025',
        //         customer: 'PT Gudang Garam Tbk',
        //         company: 'PT Yanasurya Bhaktipersada',
        //         pallet1_in: 150,
        //         pallet1_out: 0,
        //         pallet2_in: 100,
        //         pallet2_out: 0,
        //         pallet3_in: 80,
        //         pallet3_out: 0,
        //         pallet4_in: 60,
        //         pallet4_out: 0,
        //         // Stock after: P1=720, P2=300, P3=200, P4=200
        //         // Amount = (720*500 + 300*450 + 200*750 + 200*550) = 845,000
        //         amount: 845000
        //     },
        //     {
        //         id: 9,
        //         finance_mutation_date: '2026-01-27',
        //         document_date: '2026-01-26',
        //         type: 'DN',
        //         document_no: 'DN/2026/027',
        //         customer: 'PT Danone Indonesia',
        //         company: 'PT Yanasurya Bhaktipersada',
        //         pallet1_in: 0,
        //         pallet1_out: 120,
        //         pallet2_in: 0,
        //         pallet2_out: 70,
        //         pallet3_in: 0,
        //         pallet3_out: 40,
        //         pallet4_in: 0,
        //         pallet4_out: 30,
        //         // Stock after: P1=600, P2=230, P3=160, P4=170
        //         // Amount = (600*500 + 230*450 + 160*750 + 170*550) = 627,000
        //         amount: 627000
        //     },
        //     {
        //         id: 10,
        //         finance_mutation_date: '2026-01-27',
        //         document_date: '2026-01-26',
        //         type: 'DN',
        //         document_no: 'DN/2026/026',
        //         customer: 'PT Nestle Indonesia',
        //         company: 'PT Yanasurya Bhaktipersada',
        //         pallet1_in: 0,
        //         pallet1_out: 75,
        //         pallet2_in: 0,
        //         pallet2_out: 0,
        //         pallet3_in: 0,
        //         pallet3_out: 0,
        //         pallet4_in: 0,
        //         pallet4_out: 0,
        //         // Stock after: P1=525, P2=230, P3=160, P4=170
        //         // Amount = (525*500 + 230*450 + 160*750 + 170*550) = 589,500
        //         amount: 589500
        //     },

        //     // Continue with more data following the same pattern...
        //     // I'll add a few more for different companies

        //     // PT Yanaprima Hastapersada - Starting stock: P1=400, P2=250, P3=180, P4=120
        //     {
        //         id: 21,
        //         finance_mutation_date: '2026-01-30',
        //         document_date: '2026-01-29',
        //         type: 'DN',
        //         document_no: 'DN/YPH/2026/005',
        //         customer: 'PT Nestle Indonesia',
        //         company: 'PT Yanaprima Hastapersada',
        //         pallet1_in: 0,
        //         pallet1_out: 110,
        //         pallet2_in: 0,
        //         pallet2_out: 90,
        //         pallet3_in: 0,
        //         pallet3_out: 0,
        //         pallet4_in: 0,
        //         pallet4_out: 0,
        //         // Stock after: P1=290, P2=160, P3=180, P4=120
        //         // Amount = (290*500 + 160*450 + 180*750 + 120*550) = 428,000
        //         amount: 428000
        //     },
        //     {
        //         id: 22,
        //         finance_mutation_date: '2026-01-30',
        //         document_date: '2026-01-30',
        //         type: 'GR',
        //         document_no: 'GR/YPH/2026/005',
        //         customer: 'PT Astra International',
        //         company: 'PT Yanaprima Hastapersada',
        //         pallet1_in: 140,
        //         pallet1_out: 0,
        //         pallet2_in: 0,
        //         pallet2_out: 0,
        //         pallet3_in: 0,
        //         pallet3_out: 0,
        //         pallet4_in: 0,
        //         pallet4_out: 0,
        //         // Stock after: P1=430, P2=160, P3=180, P4=120
        //         // Amount = (430*500 + 160*450 + 180*750 + 120*550) = 498,000
        //         amount: 498000
        //     },

        //     // PT Forindoprima Perkasa - Starting stock: P1=350, P2=200, P3=150, P4=100
        //     {
        //         id: 26,
        //         finance_mutation_date: '2026-01-30',
        //         document_date: '2026-01-29',
        //         type: 'DN',
        //         document_no: 'DN/FPP/2026/005',
        //         customer: 'PT Pertamina',
        //         company: 'PT Forindoprima Perkasa',
        //         pallet1_in: 0,
        //         pallet1_out: 130,
        //         pallet2_in: 0,
        //         pallet2_out: 0,
        //         pallet3_in: 0,
        //         pallet3_out: 60,
        //         pallet4_in: 0,
        //         pallet4_out: 0,
        //         // Stock after: P1=220, P2=200, P3=90, P4=100
        //         // Amount = (220*500 + 200*450 + 90*750 + 100*550) = 332,500
        //         amount: 332500
        //     },
        //     {
        //         id: 27,
        //         finance_mutation_date: '2026-01-30',
        //         document_date: '2026-01-29',
        //         type: 'GR',
        //         document_no: 'GR/FPP/2026/005',
        //         customer: 'PT Semen Indonesia',
        //         company: 'PT Forindoprima Perkasa',
        //         pallet1_in: 170,
        //         pallet1_out: 0,
        //         pallet2_in: 0,
        //         pallet2_out: 0,
        //         pallet3_in: 90,
        //         pallet3_out: 0,
        //         pallet4_in: 0,
        //         pallet4_out: 0,
        //         // Stock after: P1=390, P2=200, P3=180, P4=100
        //         // Amount = (390*500 + 200*450 + 180*750 + 100*550) = 475,000
        //         amount: 475000
        //     },
        // ];
        let accountTransactionData = [];
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
            vertical-align: middle;
        }

        .table tbody tr {
            transition: background-color 0.15s ease;
        }

        .table tbody tr:hover {
            background-color: #f1f8f4;
        }

        /* Starting Stock Row Styling */
        .table-warning {
            background-color: #fff3cd !important;
        }

        .table-warning:hover {
            background-color: #fff3cd !important;
        }

        /* Daily Total Row Styling */
        .table-info {
            background-color: #cfe2ff !important;
        }

        .table-info:hover {
            background-color: #cfe2ff !important;
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
        #printContainer {
            position: absolute;
            left: -9999px;
            top: 0;
            width: 100%;
        }

        @media print {
            body * {
                visibility: hidden;
            }

            #printContainer,
            #printContainer * {
                visibility: visible;
            }

            #printContainer {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }

            html,
            body {
                margin: 0 !important;
                padding: 0 !important;
                height: auto !important;
            }

            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            @page {
                size: A4 landscape;
                margin: 0;
            }
        }

        /* Print content styles */
        .account-report-print {
            font-family: 'Arial', sans-serif;
            font-size: 9pt;
            color: #000;
            background: white;
            padding: 1cm;
        }

        .account-report-print .header {
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }

        .account-report-print .header-content {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 15px;
        }

        .account-report-print .company-logo {
            height: 40px;
            width: auto;
            object-fit: contain;
        }

        .account-report-print .company-name {
            font-size: 18pt;
            font-weight: bold;
            color: #000;
            letter-spacing: 2px;
        }

        .account-report-print .report-title {
            font-size: 14pt;
            font-weight: bold;
            text-align: center;
            margin: 10px 0;
            text-transform: uppercase;
        }

        .account-report-print .report-info {
            display: grid;
            grid-template-columns: 150px 1fr;
            gap: 3px 10px;
            margin-bottom: 15px;
            font-size: 9pt;
        }

        .account-report-print .info-label {
            font-weight: bold;
        }

        .account-report-print table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .account-report-print table th,
        .account-report-print table td {
            border: 1px solid #000;
            padding: 4px 6px;
            font-size: 8pt;
            line-height: 1.3;
        }

        .account-report-print table th {
            background-color: #e0e0e0;
            font-weight: bold;
            text-align: center;
        }

        .account-report-print table td.text-center {
            text-align: center;
        }

        .account-report-print table td.text-right {
            text-align: right;
        }

        .account-report-print table td.text-end {
            text-align: right;
        }

        .account-report-print .footer-note {
            margin-top: 15px;
            font-size: 7pt;
            text-align: center;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 5px;
        }

        .account-report-print tfoot td {
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
            let startedAcountTransactions = [];
            let arrckdpallet=[];
            // State management
            let currentFilters = {
                month: '',
                year: '',
                company: '',
                customer: '',
                search: ''
            };

            // Starting stock configuration per company
            let startingStockData = {};
            // const startingStockData = {
            //     'PT Yanasurya Bhaktipersada': {
            //         pallet1: 500,
            //         pallet2: 300,
            //         pallet3: 200,
            //         pallet4: 150
            //     },
            //     'PT Yanaprima Hastapersada': {
            //         pallet1: 400,
            //         pallet2: 250,
            //         pallet3: 180,
            //         pallet4: 120
            //     },
            //     'PT Forindoprima Perkasa': {
            //         pallet1: 350,
            //         pallet2: 200,
            //         pallet3: 150,
            //         pallet4: 100
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

            $('#filterCustomer').select2({
                width: '100%',
                placeholder: '{{ __('All Customers') }}',
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
                updateStockAwal();
                filterAndRender();
            });

            document.getElementById('filterYear').addEventListener('change', function() {
                currentFilters.year = this.value;
                updateStockAwal();
                filterAndRender();
            });

            $('#filterCompany').on('change', function() {
                currentFilters.company = this.value;
                filterAndRender();
            });

            $('#filterCustomer').on('change', function() {
                currentFilters.customer = this.value;
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
                $('#filterCustomer').val('').trigger('change');
                searchInput.value = '';
                clearSearchBtn.classList.add('d-none');
                currentFilters = {
                    month: '',
                    year: '',
                    company: '',
                    customer: '',
                    search: ''
                };
                filterAndRender();
            });

            // Filter and render data
            function filterAndRender() {
                // Check if company is selected
                if (!currentFilters.company) {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="18" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-building display-4 d-block mb-3"></i>
                                    <p class="mb-1 fw-semibold">{{ __('Please select a company') }}</p>
                                    <p class="small mb-0">{{ __('Choose a company from the filter above to view account transactions') }}</p>
                                </div>
                            </td>
                        </tr>
                    `;
                    grandTotalRow.style.display = 'none';
                    paginationInfo.textContent = '{{ __('Please select a company to view data') }}';

                    return;
                }

                let filteredData = accountTransactionData.filter(item => {
                    const itemDate = new Date(item.finance_mutation_date);
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

                    // Customer filter
                    if (currentFilters.customer && item.customer !== currentFilters.customer) {
                        return false;
                    }

                    // Search filter
                    if (currentFilters.search) {
                        const searchLower = currentFilters.search.toLowerCase();
                        return (
                            item.document_no.toLowerCase().includes(searchLower) ||
                            item.customer.toLowerCase().includes(searchLower)
                        );
                    }

                    return true;
                });

                // Sort by date ascending
                filteredData.sort((a, b) => new Date(a.finance_mutation_date) - new Date(b.finance_mutation_date));

                // Update grand total row
                updateGrandTotalRow(filteredData);

                // Render all data
                renderTable(filteredData);
                
                // Update info text
                const total = filteredData.length;
                paginationInfo.textContent = `{{ __('Showing') }} ${total} {{ __('records') }}`;
            }

            // Update grand total row (at top)
            function updateGrandTotalRow(data) {
                if (data.length === 0) {
                    grandTotalRow.style.display = 'none';
                    return;
                }

                const pallet1In = data.reduce((sum, item) => sum + item.pallet1_in, 0);
                const pallet1Out = data.reduce((sum, item) => sum + item.pallet1_out, 0);
                const pallet2In = data.reduce((sum, item) => sum + item.pallet2_in, 0);
                const pallet2Out = data.reduce((sum, item) => sum + item.pallet2_out, 0);
                const pallet3In = data.reduce((sum, item) => sum + item.pallet3_in, 0);
                const pallet3Out = data.reduce((sum, item) => sum + item.pallet3_out, 0);
                const pallet4In = data.reduce((sum, item) => sum + item.pallet4_in, 0);
                const pallet4Out = data.reduce((sum, item) => sum + item.pallet4_out, 0);
                const totalAmount = data.reduce((sum, item) => sum + item.amount, 0);

                // Get starting stock for the selected company
                const startingStock = startingStockData[currentFilters.company] || {
                    pallet1: 0,
                    pallet2: 0,
                    pallet3: 0,
                    pallet4: 0
                };

                // Calculate cumulative stock sum (sum of daily stock values)
                let pallet1Stock = startingStock.pallet1;
                let pallet2Stock = startingStock.pallet2;
                let pallet3Stock = startingStock.pallet3;
                let pallet4Stock = startingStock.pallet4;

                let pallet1StockTotal = 0;
                let pallet2StockTotal = 0;
                let pallet3StockTotal = 0;
                let pallet4StockTotal = 0;

                // Group by date and calculate daily stock sums
                const groupedByDate = {};
                data.forEach(item => {
                    const date = item.finance_mutation_date;
                    if (!groupedByDate[date]) {
                        groupedByDate[date] = [];
                    }
                    groupedByDate[date].push(item);
                });

                // Sort dates
                const sortedDates = Object.keys(groupedByDate).sort((a, b) => new Date(a) - new Date(b));

                // Build full date range (same logic as renderTable)
                function buildDateRangeForTotal() {
                    const dates = [];
                    let startDate, endDate;

                    if (currentFilters.month && currentFilters.year) {
                        const y = parseInt(currentFilters.year);
                        const m = parseInt(currentFilters.month) - 1;
                        startDate = new Date(y, m, 1);
                        endDate = new Date(y, m + 1, 0);
                    } else if (currentFilters.year) {
                        const y = parseInt(currentFilters.year);
                        startDate = new Date(y, 0, 1);
                        endDate = new Date(y, 11, 31);
                    } else {
                        startDate = new Date(sortedDates[0]);
                        endDate = new Date(sortedDates[sortedDates.length - 1]);
                    }

                    const cursor = new Date(startDate);
                    while (cursor <= endDate) {
                        const y = cursor.getFullYear();
                        const m = String(cursor.getMonth() + 1).padStart(2, '0');
                        const d = String(cursor.getDate()).padStart(2, '0');
                        dates.push(`${y}-${m}-${d}`);
                        cursor.setDate(cursor.getDate() + 1);
                    }
                    return dates;
                }

                const allDatesForTotal = buildDateRangeForTotal();

                // Walk every day in range accumulating stock and summing daily rental
                let runningP1 = startingStock.pallet1;
                let runningP2 = startingStock.pallet2;
                let runningP3 = startingStock.pallet3;
                let runningP4 = startingStock.pallet4;

                let grandTotalAmount = 0;
                const hargaPerUnit = data.length > 0 ? (data[0].harga || 0) : 0;

                allDatesForTotal.forEach(date => {
                    const dayTxns = groupedByDate[date] || [];

                    dayTxns.forEach(item => {
                        runningP1 += item.pallet1_in - item.pallet1_out;
                        runningP2 += item.pallet2_in - item.pallet2_out;
                        runningP3 += item.pallet3_in - item.pallet3_out;
                        runningP4 += item.pallet4_in - item.pallet4_out;
                    });

                    // Each day counts rental on current stock regardless of transactions
                    grandTotalAmount += (runningP1 + runningP2 + runningP3 + runningP4) * hargaPerUnit;
                });

                document.getElementById('headerPallet1In').textContent = pallet1In.toLocaleString();
                document.getElementById('headerPallet1Out').textContent = pallet1Out.toLocaleString();
                document.getElementById('headerPallet1Stock').textContent = pallet1StockTotal.toLocaleString();
                document.getElementById('headerPallet2In').textContent = pallet2In.toLocaleString();
                document.getElementById('headerPallet2Out').textContent = pallet2Out.toLocaleString();
                document.getElementById('headerPallet2Stock').textContent = pallet2StockTotal.toLocaleString();
                document.getElementById('headerPallet3In').textContent = pallet3In.toLocaleString();
                document.getElementById('headerPallet3Out').textContent = pallet3Out.toLocaleString();
                document.getElementById('headerPallet3Stock').textContent = pallet3StockTotal.toLocaleString();
                document.getElementById('headerPallet4In').textContent = pallet4In.toLocaleString();
                document.getElementById('headerPallet4Out').textContent = pallet4Out.toLocaleString();
                document.getElementById('headerPallet4Stock').textContent = pallet4StockTotal.toLocaleString();
                document.getElementById('headerTotalAmount').textContent = formatCurrency(grandTotalAmount);

                grandTotalRow.style.display = '';
            }

            // Format currency
            function formatCurrency(amount) {
                return 'Rp ' + amount.toLocaleString('id-ID');
            }

            // Render table with daily sections
            function renderTable(data) {
                if (data.length === 0) {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="18" class="text-center py-5">
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

                const startingStock = startingStockData[currentFilters.company] || {
                    pallet1: 0,
                    pallet2: 0,
                    pallet3: 0,
                    pallet4: 0,
                    nprice1: 0,
                    nprice2: 0,
                    nprice3: 0,
                    nprice4: 0
                };
                

                let pallet1Stock = startingStock.pallet1;
                let pallet1Price = startingStock.nprice1;
                let pallet2Stock = startingStock.pallet2;
                let pallet2Price = startingStock.nprice2;
                let pallet3Stock = startingStock.pallet3;
                let pallet3Price = startingStock.nprice3;
                let pallet4Stock = startingStock.pallet4;
                let pallet4Price = startingStock.nprice4;
                
                const startingStockRow = `
                    <tr class="table-warning">
                        <td colspan="5" class="px-3 py-2 fw-semibold text-end">{{ __('Starting Stock') }}</td>
                        <td class="px-3 py-2 text-center"></td>
                        <td class="px-3 py-2 text-center"></td>
                        <td class="px-3 py-2 text-center"><span class="fw-semibold">${pallet1Stock}</span></td>
                        <td class="px-3 py-2 text-center"></td>
                        <td class="px-3 py-2 text-center"></td>
                        <td class="px-3 py-2 text-center"><span class="fw-semibold">${pallet2Stock}</span></td>
                        <td class="px-3 py-2 text-center"></td>
                        <td class="px-3 py-2 text-center"></td>
                        <td class="px-3 py-2 text-center"><span class="fw-semibold">${pallet3Stock}</span></td>
                        <td class="px-3 py-2 text-center"></td>
                        <td class="px-3 py-2 text-center"></td>
                        <td class="px-3 py-2 text-center"><span class="fw-semibold">${pallet4Stock}</span></td>
                        <td class="px-3 py-2 text-end"></td>
                    </tr>
                `;

                // Group actual transactions by date
                const groupedByDate = {};
                data.forEach(item => {
                    const date = item.finance_mutation_date;
                    if (!groupedByDate[date]) groupedByDate[date] = [];
                    groupedByDate[date].push(item);
                });

                // Build full date range from filter or from data range
                function buildDateRange() {
                    const dates = [];

                    let startDate, endDate;

                    if (currentFilters.month && currentFilters.year) {
                        // Full month range
                        const y = parseInt(currentFilters.year);
                        const m = parseInt(currentFilters.month) - 1;
                        startDate = new Date(y, m, 1);
                        endDate = new Date(y, m + 1, 0); // last day of month
                    } else if (currentFilters.year) {
                        // Full year range
                        const y = parseInt(currentFilters.year);
                        startDate = new Date(y, 0, 1);
                        endDate = new Date(y, 11, 31);
                    } else {
                        // Fallback: use data's own min/max dates
                        const sortedKeys = Object.keys(groupedByDate).sort();
                        startDate = new Date(sortedKeys[0]);
                        endDate = new Date(sortedKeys[sortedKeys.length - 1]);
                    }

                    const cursor = new Date(startDate);
                    while (cursor <= endDate) {
                        const y = cursor.getFullYear();
                        const m = String(cursor.getMonth() + 1).padStart(2, '0');
                        const d = String(cursor.getDate()).padStart(2, '0');
                        dates.push(`${y}-${m}-${d}`);
                        cursor.setDate(cursor.getDate() + 1);
                    }

                    return dates;
                }

                const allDates = buildDateRange();

                const formatDate = (dateStr) => {
                    const date = new Date(dateStr);
                    const day = String(date.getDate()).padStart(2, '0');
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const year = String(date.getFullYear()).slice(-2);
                    return `${day}/${month}/${year}`;
                };

                // Get rental rate per pallet - derive from amount data or fallback
                // We'll calculate daily amount as: current stock * rate
                // Rate is derived from the data's amount field where possible
                // For no-transaction days, we reuse last known rate or calculate from stock
                // Since your amount calculation is: (in-out)*harga, we need per-unit rate
                // Best approach: track daily rental amount as stock * dailyRate
                // We'll extract daily rate from the data element's harga field via a lookup
                const hargaMap = {}; // ckdbrg -> harga, populated from raw API data
                // We'll pass harga info through accountTransactionData by adding it
                // For now, calculate daily amount from last transaction's implied rate
                // Alternative simpler approach: store harga per pallet in accountTransactionData

                let allRows = '';
                //get price
                allDates.forEach(date => {
                    const dailyTransactions = groupedByDate[date] || [];
                    const hasTransactions = dailyTransactions.length > 0;
                    dailyTransactions.forEach(item => {
                        if(item.nprice>0){
                            pallet1Price = item.nprice;
                        }
                        if(item.nprice2>0){
                            pallet2Price = item.nprice2;
                        }
                        if(item.nprice3>0){
                            pallet3Price = item.nprice3;
                        }
                        if(item.nprice4>0){
                            pallet4Price = item.nprice4;
                        }
                    })
                });
                allDates.forEach(date => {
                    const dailyTransactions = groupedByDate[date] || [];
                    const hasTransactions = dailyTransactions.length > 0;

                    let dailyPallet1In = 0,
                        dailyPallet1Out = 0;
                    let dailyPallet2In = 0,
                        dailyPallet2Out = 0;
                    let dailyPallet3In = 0,
                        dailyPallet3Out = 0;
                    let dailyPallet4In = 0,
                        dailyPallet4Out = 0;
                    let dailyTransactionAmount = 0;

                    // Render each transaction row for this date
                    
                    dailyTransactions.forEach(item => {
                        pallet1Stock += item.pallet1_in - item.pallet1_out;
                        pallet2Stock += item.pallet2_in - item.pallet2_out;
                        pallet3Stock += item.pallet3_in - item.pallet3_out;
                        pallet4Stock += item.pallet4_in - item.pallet4_out;

                        dailyPallet1In += item.pallet1_in;
                        dailyPallet1Out += item.pallet1_out;
                        dailyPallet2In += item.pallet2_in;
                        dailyPallet2Out += item.pallet2_out;
                        dailyPallet3In += item.pallet3_in;
                        dailyPallet3Out += item.pallet3_out;
                        dailyPallet4In += item.pallet4_in;
                        dailyPallet4Out += item.pallet4_out;
                        dailyTransactionAmount += item.amount;

                        const typeBadge = item.type === 'DN' ?
                            '<span class="badge bg-danger">DN</span>' :
                            '<span class="badge bg-success">GR</span>';

                        allRows += `
                            <tr>
                                <td class="px-3 py-2">${formatDate(item.finance_mutation_date)}</td>
                                <td class="px-3 py-2">${typeBadge}</td>
                                <td class="px-3 py-2">
                                    <span class="fw-semibold text-primary">${item.document_no}</span>
                                    ${item.type === 'DN' && !item.has_gr
                                        ? `<i class="bi bi-hourglass-split text-danger ms-1"
                                                                    data-bs-toggle="tooltip"
                                                                    data-bs-placement="right"
                                                                    title="Pending GR — DN not yet received by customer"></i>`
                                        : ''}
                                </td>
                                <td class="px-3 py-2">${formatDate(item.document_date)}</td>
                                <td class="px-3 py-2">${item.customer}</td>
                                <td class="px-3 py-2 text-center">
                                    ${item.pallet1_in > 0 ? `<span class="text-success fw-semibold">+${item.pallet1_in}</span>` : ''}
                                </td>
                                <td class="px-3 py-2 text-center">
                                    ${item.pallet1_out > 0 ? `<span class="text-danger fw-semibold">-${item.pallet1_out}</span>` : ''}
                                </td>
                                <td class="px-3 py-2 text-center"><span class="fw-semibold">${pallet1Stock}</span></td>
                                <td class="px-3 py-2 text-center">
                                    ${item.pallet2_in > 0 ? `<span class="text-success fw-semibold">+${item.pallet2_in}</span>` : ''}
                                </td>
                                <td class="px-3 py-2 text-center">
                                    ${item.pallet2_out > 0 ? `<span class="text-danger fw-semibold">-${item.pallet2_out}</span>` : ''}
                                </td>
                                <td class="px-3 py-2 text-center"><span class="fw-semibold">${pallet2Stock}</span></td>
                                <td class="px-3 py-2 text-center">
                                    ${item.pallet3_in > 0 ? `<span class="text-success fw-semibold">+${item.pallet3_in}</span>` : ''}
                                </td>
                                <td class="px-3 py-2 text-center">
                                    ${item.pallet3_out > 0 ? `<span class="text-danger fw-semibold">-${item.pallet3_out}</span>` : ''}
                                </td>
                                <td class="px-3 py-2 text-center"><span class="fw-semibold">${pallet3Stock}</span></td>
                                <td class="px-3 py-2 text-center">
                                    ${item.pallet4_in > 0 ? `<span class="text-success fw-semibold">+${item.pallet4_in}</span>` : ''}
                                </td>
                                <td class="px-3 py-2 text-center">
                                    ${item.pallet4_out > 0 ? `<span class="text-danger fw-semibold">-${item.pallet4_out}</span>` : ''}
                                </td>
                                <td class="px-3 py-2 text-center"><span class="fw-semibold">${pallet4Stock}</span></td>
                                <td class="px-3 py-2 text-end"></td>
                            </tr>
                        `;
                    });

                    // Calculate daily rental amount from current stock * per-unit rate
                    // Pull harga from the item if available, otherwise use item.harga stored on push
                    // const dailyRentalAmount = hasTransactions ? (dailyTransactions[0].harga ?
                    //     (pallet1Stock * dailyTransactions[0].harga) : dailyTransactionAmount) :
                    //     (data[0]?.harga ? (pallet1Stock * data[0].harga) : 0);
                    // const dailyRentalAmount = hasTransactions ? 
                    // ((pallet1Stock * pallet1Price) + (pallet2Stock * pallet2Price) + (pallet3Stock * pallet3Price) + (pallet4Stock * pallet4Price)) :
                    //     (pallet1Price || pallet2Price || pallet3Price || pallet4Price ? ((pallet1Stock * pallet1Price)+(pallet2Stock * pallet2Price)+(pallet3Stock * pallet3Price)+(pallet4Stock * pallet4Price) ) : 0);
                    const dailyRentalAmount =((pallet1Stock * pallet1Price) + (pallet2Stock * pallet2Price) + (pallet3Stock * pallet3Price) + (pallet4Stock * pallet4Price)) ;
                    
                    
                    // const dailyRentalAmount = hasTransactions ? ((pallet1Stock * pallet1Price) + (pallet2Stock * pallet2Price) + (pallet3Stock * pallet3Price) + (pallet4Stock * pallet4Price)):;

                    // No-transaction day: show italic muted label
                    const dailyLabel = hasTransactions ?
                        '{{ __('Daily Total') }}' :
                        '<span class="text-muted fst-italic small">{{ __('No Transaction') }}</span>';

                    const noTxnRowClass = hasTransactions ? 'table-info fw-semibold' :
                        'table-secondary fw-semibold';
                    const amountDisplay =
                        `<span class="text-success">${formatCurrency(dailyRentalAmount)}</span>`;

                    // Daily total / no-transaction row — always rendered for every date
                    allRows += `
                        <tr class="${noTxnRowClass}">
                            <td class="px-3 py-2">${formatDate(date)}</td>
                            <td colspan="4" class="px-3 py-2 text-end">${dailyLabel}</td>
                            <td class="px-3 py-2 text-center">${dailyPallet1In || ''}</td>
                            <td class="px-3 py-2 text-center">${dailyPallet1Out || ''}</td>
                            <td class="px-3 py-2 text-center">${pallet1Stock}</td>
                            <td class="px-3 py-2 text-center">${dailyPallet2In || ''}</td>
                            <td class="px-3 py-2 text-center">${dailyPallet2Out || ''}</td>
                            <td class="px-3 py-2 text-center">${pallet2Stock}</td>
                            <td class="px-3 py-2 text-center">${dailyPallet3In || ''}</td>
                            <td class="px-3 py-2 text-center">${dailyPallet3Out || ''}</td>
                            <td class="px-3 py-2 text-center">${pallet3Stock}</td>
                            <td class="px-3 py-2 text-center">${dailyPallet4In || ''}</td>
                            <td class="px-3 py-2 text-center">${dailyPallet4Out || ''}</td>
                            <td class="px-3 py-2 text-center">${pallet4Stock}</td>
                            <td class="px-3 py-2 text-end">${amountDisplay}</td>
                        </tr>
                    `;
                });

                tableBody.innerHTML = startingStockRow + allRows;

                // Re-init tooltips after DOM update
                document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                    new bootstrap.Tooltip(el, {
                        trigger: 'hover'
                    });
                });
            }

            // Print functionality
            document.getElementById('printReport').addEventListener('click', function() {
                if (!currentFilters.company) {
                    alert('{{ __('Please select a company first') }}');
                    return;
                }

                generatePrintContent();

                const originalTitle = document.title;
                document.title = '';

                setTimeout(() => {
                    const style = document.createElement('style');
                    style.textContent = '@page { margin: 0; } body { margin: 0; }';
                    document.head.appendChild(style);
                    window.print();

                    setTimeout(() => {
                        document.title = originalTitle;
                    }, 100);
                }, 200);
            });

            // Generate print content
            function generatePrintContent() {
                const printContainer = document.getElementById('printContainer');

                let filteredData = accountTransactionData.filter(item => {
                    const itemDate = new Date(item.finance_mutation_date);
                    const itemMonth = itemDate.getMonth() + 1;
                    const itemYear = itemDate.getFullYear();

                    if (item.company !== currentFilters.company) return false;
                    if (currentFilters.month && itemMonth !== parseInt(currentFilters.month)) return false;
                    if (currentFilters.year && itemYear !== parseInt(currentFilters.year)) return false;
                    if (currentFilters.customer && item.customer !== currentFilters.customer) return false;

                    if (currentFilters.search) {
                        const searchLower = currentFilters.search.toLowerCase();
                        return (
                            item.document_no.toLowerCase().includes(searchLower) ||
                            item.customer.toLowerCase().includes(searchLower)
                        );
                    }

                    return true;
                });

                filteredData.sort((a, b) => new Date(a.finance_mutation_date) - new Date(b.finance_mutation_date));

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
                if (currentFilters.customer) {
                    filterText += ` | Customer: ${currentFilters.customer}`;
                }

                const printDate = new Date().toLocaleString('en-GB', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });

                // Calculate totals
                const pallet1In = filteredData.reduce((sum, item) => sum + item.pallet1_in, 0);
                const pallet1Out = filteredData.reduce((sum, item) => sum + item.pallet1_out, 0);
                const pallet2In = filteredData.reduce((sum, item) => sum + item.pallet2_in, 0);
                const pallet2Out = filteredData.reduce((sum, item) => sum + item.pallet2_out, 0);
                const pallet3In = filteredData.reduce((sum, item) => sum + item.pallet3_in, 0);
                const pallet3Out = filteredData.reduce((sum, item) => sum + item.pallet3_out, 0);
                const pallet4In = filteredData.reduce((sum, item) => sum + item.pallet4_in, 0);
                const pallet4Out = filteredData.reduce((sum, item) => sum + item.pallet4_out, 0);
                const totalAmount = filteredData.reduce((sum, item) => sum + item.amount, 0);

                const revenue = filteredData
                    .filter(item => item.type === 'DN')
                    .reduce((sum, item) => sum + item.amount, 0);

                const cost = Math.abs(filteredData
                    .filter(item => item.type === 'GR')
                    .reduce((sum, item) => sum + item.amount, 0));

                const netProfit = revenue - cost;

                // Get starting stock for the selected company
                const startingStock = startingStockData[currentFilters.company] || {
                    pallet1: 0,
                    pallet2: 0,
                    pallet3: 0,
                    pallet4: 0
                };

                // Generate table rows with stock calculation
                let tableRows = '';
                let pallet1Stock = startingStock.pallet1;
                let pallet2Stock = startingStock.pallet2;
                let pallet3Stock = startingStock.pallet3;
                let pallet4Stock = startingStock.pallet4;

                // Add starting stock row
                tableRows += `
                    <tr style="background-color: #fff3cd;">
                        <td colspan="5" style="text-align: right; font-weight: bold;">STARTING STOCK:</td>
                        <td class="text-center">-</td>
                        <td class="text-center">-</td>
                        <td class="text-center" style="font-weight: bold;">${pallet1Stock}</td>
                        <td class="text-center">-</td>
                        <td class="text-center">-</td>
                        <td class="text-center" style="font-weight: bold;">${pallet2Stock}</td>
                        <td class="text-center">-</td>
                        <td class="text-center">-</td>
                        <td class="text-center" style="font-weight: bold;">${pallet3Stock}</td>
                        <td class="text-center">-</td>
                        <td class="text-center">-</td>
                        <td class="text-center" style="font-weight: bold;">${pallet4Stock}</td>
                        <td class="text-end">-</td>
                    </tr>
                `;

                // Group data by date
                const groupedByDate = {};
                filteredData.forEach(item => {
                    const date = item.finance_mutation_date;
                    if (!groupedByDate[date]) {
                        groupedByDate[date] = [];
                    }
                    groupedByDate[date].push(item);
                });

                // Sort dates
                const sortedDates = Object.keys(groupedByDate).sort((a, b) => new Date(a) - new Date(b));

                sortedDates.forEach(date => {
                    const dailyTransactions = groupedByDate[date];

                    // Track daily totals
                    let dailyPallet1In = 0;
                    let dailyPallet1Out = 0;
                    let dailyPallet2In = 0;
                    let dailyPallet2Out = 0;
                    let dailyPallet3In = 0;
                    let dailyPallet3Out = 0;
                    let dailyPallet4In = 0;
                    let dailyPallet4Out = 0;
                    let dailyAmount = 0;

                    dailyTransactions.forEach(item => {
                        pallet1Stock += item.pallet1_in - item.pallet1_out;
                        pallet2Stock += item.pallet2_in - item.pallet2_out;
                        pallet3Stock += item.pallet3_in - item.pallet3_out;
                        pallet4Stock += item.pallet4_in - item.pallet4_out;

                        // Track daily totals
                        dailyPallet1In += item.pallet1_in;
                        dailyPallet1Out += item.pallet1_out;
                        dailyPallet2In += item.pallet2_in;
                        dailyPallet2Out += item.pallet2_out;
                        dailyPallet3In += item.pallet3_in;
                        dailyPallet3Out += item.pallet3_out;
                        dailyPallet4In += item.pallet4_in;
                        dailyPallet4Out += item.pallet4_out;
                        dailyAmount += item.amount;

                        const typeText = item.type === 'DN' ? 'DN' : 'GR';
                        tableRows += `
                            <tr>
                                <td>${formatDateForPrint(item.finance_mutation_date)}</td>
                                <td style="text-align: center;">${typeText}</td>
                                <td>${item.document_no}</td>
                                <td>${formatDateForPrint(item.document_date)}</td>
                                <td>${item.customer}</td>
                                <td class="text-center">${item.pallet1_in > 0 ? item.pallet1_in : ''}</td>
                                <td class="text-center">${item.pallet1_out > 0 ? item.pallet1_out : ''}</td>
                                <td class="text-center">${pallet1Stock}</td>
                                <td class="text-center">${item.pallet2_in > 0 ? item.pallet2_in : ''}</td>
                                <td class="text-center">${item.pallet2_out > 0 ? item.pallet2_out : ''}</td>
                                <td class="text-center">${pallet2Stock}</td>
                                <td class="text-center">${item.pallet3_in > 0 ? item.pallet3_in : ''}</td>
                                <td class="text-center">${item.pallet3_out > 0 ? item.pallet3_out : ''}</td>
                                <td class="text-center">${pallet3Stock}</td>
                                <td class="text-center">${item.pallet4_in > 0 ? item.pallet4_in : ''}</td>
                                <td class="text-center">${item.pallet4_out > 0 ? item.pallet4_out : ''}</td>
                                <td class="text-center">${pallet4Stock}</td>
                                <td class="text-end">${formatCurrency(item.amount)}</td>
                            </tr>
                        `;
                    });

                    // Add daily section end row
                    tableRows += `
                        <tr style="background-color: #cfe2ff; font-weight: bold;">
                            <td>${formatDateForPrint(date)}</td>
                            <td colspan="4" style="text-align: right;">DAILY TOTAL:</td>
                            <td class="text-center">${dailyPallet1In > 0 ? '+' + dailyPallet1In : ''}</td>
                            <td class="text-center">${dailyPallet1Out > 0 ? '-' + dailyPallet1Out : ''}</td>
                            <td class="text-center">${pallet1Stock}</td>
                            <td class="text-center">${dailyPallet2In > 0 ? '+' + dailyPallet2In : ''}</td>
                            <td class="text-center">${dailyPallet2Out > 0 ? '-' + dailyPallet2Out : ''}</td>
                            <td class="text-center">${pallet2Stock}</td>
                            <td class="text-center">${dailyPallet3In > 0 ? '+' + dailyPallet3In : ''}</td>
                            <td class="text-center">${dailyPallet3Out > 0 ? '-' + dailyPallet3Out : ''}</td>
                            <td class="text-center">${pallet3Stock}</td>
                            <td class="text-center">${dailyPallet4In > 0 ? '+' + dailyPallet4In : ''}</td>
                            <td class="text-center">${dailyPallet4Out > 0 ? '-' + dailyPallet4Out : ''}</td>
                            <td class="text-center">${pallet4Stock}</td>
                            <td class="text-end">${formatCurrency(dailyAmount)}</td>
                        </tr>
                    `;
                });

                printContainer.innerHTML = `
                    <div class="account-report-print">
                        <div class="header">
                            <div class="header-content">
                                <img src="/images/logo-bw.png" alt="Company Logo" class="company-logo">
                                <div class="company-name">${currentFilters.company.toUpperCase()}</div>
                            </div>
                        </div>

                        <div class="report-title">ACCOUNT TRANSACTION REPORT</div>

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
                                    <th rowspan="2" style="width: 8%;">Finance Mut. Date</th>
                                    <th rowspan="2" style="width: 5%;">Type</th>
                                    <th rowspan="2" style="width: 10%;">Doc. No.</th>
                                    <th rowspan="2" style="width: 8%;">Doc. Date</th>
                                    <th rowspan="2" style="width: 14%;">From/To</th>
                                    <th colspan="3" style="width: 11%;">PT1210AS</th>
                                    <th colspan="3" style="width: 11%;">PF1210</th>
                                    <th colspan="3" style="width: 11%;">B325</th>
                                    <th colspan="3" style="width: 11%;">PT1212</th>
                                    <th rowspan="2" style="width: 11%;">Amount</th>
                                </tr>
                                <tr>
                                    <th style="width: 3.6%;">In</th>
                                    <th style="width: 3.6%;">Out</th>
                                    <th style="width: 3.8%;">Stock</th>
                                    <th style="width: 3.6%;">In</th>
                                    <th style="width: 3.6%;">Out</th>
                                    <th style="width: 3.8%;">Stock</th>
                                    <th style="width: 3.6%;">In</th>
                                    <th style="width: 3.6%;">Out</th>
                                    <th style="width: 3.8%;">Stock</th>
                                    <th style="width: 3.6%;">In</th>
                                    <th style="width: 3.6%;">Out</th>
                                    <th style="width: 3.8%;">Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${tableRows}
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="5" style="text-align: right; font-weight: bold;">GRAND TOTAL:</td>
                                    <td class="text-center">${pallet1In.toLocaleString()}</td>
                                    <td class="text-center">${pallet1Out.toLocaleString()}</td>
                                    <td class="text-center">${(pallet1In - pallet1Out).toLocaleString()}</td>
                                    <td class="text-center">${pallet2In.toLocaleString()}</td>
                                    <td class="text-center">${pallet2Out.toLocaleString()}</td>
                                    <td class="text-center">${(pallet2In - pallet2Out).toLocaleString()}</td>
                                    <td class="text-center">${pallet3In.toLocaleString()}</td>
                                    <td class="text-center">${pallet3Out.toLocaleString()}</td>
                                    <td class="text-center">${(pallet3In - pallet3Out).toLocaleString()}</td>
                                    <td class="text-center">${pallet4In.toLocaleString()}</td>
                                    <td class="text-center">${pallet4Out.toLocaleString()}</td>
                                    <td class="text-center">${(pallet4In - pallet4Out).toLocaleString()}</td>
                                    <td class="text-end">${formatCurrency(totalAmount)}</td>
                                </tr>
                            </tfoot>
                        </table>

                        <div class="summary-section">
                            <div class="summary-item">
                                <div class="summary-label">Total Revenue</div>
                                <div class="summary-value">${formatCurrency(revenue)}</div>
                            </div>
                            <div class="summary-item">
                                <div class="summary-label">Total Cost</div>
                                <div class="summary-value">${formatCurrency(cost)}</div>
                            </div>
                            <div class="summary-item">
                                <div class="summary-label">Net Profit</div>
                                <div class="summary-value">${formatCurrency(netProfit)}</div>
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

                let exportData = getFilteredData();

                const worksheetData = [
                    ['Account Transaction Report'],
                    ['Company:', currentFilters.company],
                    ['Generated:', new Date().toLocaleString()],
                    [],
                    ['Finance Mut. Date', 'Type', 'Doc. No.', 'From/To',
                        'PT1210AS In', 'PT1210AS Out', 'PT1210AS Stock',
                        'PF1210 In', 'PF1210 Out', 'PF1210 Stock',
                        'B325 In', 'B325 Out', 'B325 Stock',
                        'PT1212 In', 'PT1212 Out', 'PT1212 Stock',
                        'Amount'
                    ]
                ];

                let pallet1Stock = 0;
                let pallet2Stock = 0;
                let pallet3Stock = 0;
                let pallet4Stock = 0;

                exportData.forEach(item => {
                    pallet1Stock += item.pallet1_in - item.pallet1_out;
                    pallet2Stock += item.pallet2_in - item.pallet2_out;
                    pallet3Stock += item.pallet3_in - item.pallet3_out;
                    pallet4Stock += item.pallet4_in - item.pallet4_out;

                    worksheetData.push([
                        formatDate(item.finance_mutation_date),
                        item.type,
                        item.document_no,
                        item.customer,
                        item.pallet1_in || 0,
                        item.pallet1_out || 0,
                        pallet1Stock,
                        item.pallet2_in || 0,
                        item.pallet2_out || 0,
                        pallet2Stock,
                        item.pallet3_in || 0,
                        item.pallet3_out || 0,
                        pallet3Stock,
                        item.pallet4_in || 0,
                        item.pallet4_out || 0,
                        pallet4Stock,
                        item.amount
                    ]);
                });

                const pallet1In = exportData.reduce((sum, item) => sum + item.pallet1_in, 0);
                const pallet1Out = exportData.reduce((sum, item) => sum + item.pallet1_out, 0);
                const pallet2In = exportData.reduce((sum, item) => sum + item.pallet2_in, 0);
                const pallet2Out = exportData.reduce((sum, item) => sum + item.pallet2_out, 0);
                const totalAmount = exportData.reduce((sum, item) => sum + item.amount, 0);

                worksheetData.push([]);
                worksheetData.push(['', '', '', 'TOTAL', pallet1In, pallet1Out, pallet2In, pallet2Out, '',
                    totalAmount
                ]);

                const wb = XLSX.utils.book_new();
                const ws = XLSX.utils.aoa_to_sheet(worksheetData);

                ws['!cols'] = [{
                        wch: 15
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
                        wch: 12
                    }, {
                        wch: 12
                    }, {
                        wch: 12
                    }, {
                        wch: 12
                    }, {
                        wch: 15
                    }
                ];

                XLSX.utils.book_append_sheet(wb, ws, 'Account Transaction');
                XLSX.writeFile(wb,
                    `Account_Transaction_${currentFilters.company}_${new Date().getTime()}.xlsx`);
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
                const doc = new jsPDF('l', 'mm', 'a4');

                let exportData = getFilteredData();

                doc.setFontSize(16);
                doc.text('Account Transaction Report', 14, 15);

                doc.setFontSize(10);
                doc.text(`Company: ${currentFilters.company}`, 14, 22);
                doc.text(`Generated: ${new Date().toLocaleString()}`, 14, 28);

                const tableData = exportData.map(item => [
                    formatDate(item.finance_mutation_date),
                    item.type,
                    item.document_no,
                    item.customer.substring(0, 20),
                    item.pallet1_in || '-',
                    item.pallet1_out || '-',
                    item.pallet2_in || '-',
                    item.pallet2_out || '-',
                    formatCurrency(item.amount)
                ]);

                const pallet1In = exportData.reduce((sum, item) => sum + item.pallet1_in, 0);
                const pallet1Out = exportData.reduce((sum, item) => sum + item.pallet1_out, 0);
                const pallet2In = exportData.reduce((sum, item) => sum + item.pallet2_in, 0);
                const pallet2Out = exportData.reduce((sum, item) => sum + item.pallet2_out, 0);
                const totalAmount = exportData.reduce((sum, item) => sum + item.amount, 0);

                doc.autoTable({
                    startY: 34,
                    head: [
                        [{
                                content: 'Finance Mut.',
                                rowSpan: 2
                            },
                            {
                                content: 'Type',
                                rowSpan: 2
                            },
                            {
                                content: 'Doc No.',
                                rowSpan: 2
                            },
                            {
                                content: 'From/To',
                                rowSpan: 2
                            },
                            {
                                content: 'PT1210AS',
                                colSpan: 3
                            },
                            {
                                content: 'PT1210BS',
                                colSpan: 3
                            },
                            {
                                content: 'Amount',
                                rowSpan: 2
                            }
                        ],
                        ['In', 'Out', 'Stock', 'In', 'Out', 'Stock']
                    ],
                    body: tableData,
                    foot: [
                        ['', '', '', 'TOTAL', pallet1In, pallet1Out, pallet2In, pallet2Out, '',
                            formatCurrency(totalAmount)
                        ]
                    ],
                    theme: 'grid',
                    headStyles: {
                        fillColor: [25, 135, 84],
                        fontSize: 7
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
                            cellWidth: 12
                        },
                        2: {
                            cellWidth: 25
                        },
                        3: {
                            cellWidth: 45
                        },
                        4: {
                            cellWidth: 15,
                            halign: 'center'
                        },
                        5: {
                            cellWidth: 15,
                            halign: 'center'
                        },
                        6: {
                            cellWidth: 15,
                            halign: 'center'
                        },
                        7: {
                            cellWidth: 15,
                            halign: 'center'
                        },
                        8: {
                            cellWidth: 25,
                            halign: 'right'
                        },
                        9: {
                            cellWidth: 30,
                            halign: 'right'
                        }
                    }
                });

                doc.save(
                    `Account_Transaction_${currentFilters.company}_${new Date().getTime()}.pdf`);
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
                    'Finance Mut. Date,Type,Doc. No.,From/To,PT1210AS In,PT1210AS Out,PT1210AS Stock,PF1210 In,PF1210 Out,PF1210 Stock,B325 In,B325 Out,B325 Stock,PT1212 In,PT1212 Out,PT1212 Stock,Amount\n';

                let pallet1Stock = 0;
                let pallet2Stock = 0;
                let pallet3Stock = 0;
                let pallet4Stock = 0;

                exportData.forEach(item => {
                    pallet1Stock += item.pallet1_in - item.pallet1_out;
                    pallet2Stock += item.pallet2_in - item.pallet2_out;
                    pallet3Stock += item.pallet3_in - item.pallet3_out;
                    pallet4Stock += item.pallet4_in - item.pallet4_out;

                    csv +=
                        `${formatDate(item.finance_mutation_date)},${item.type},"${item.document_no}","${item.customer}",${item.pallet1_in || 0},${item.pallet1_out || 0},${pallet1Stock},${item.pallet2_in || 0},${item.pallet2_out || 0},${pallet2Stock},${item.pallet3_in || 0},${item.pallet3_out || 0},${pallet3Stock},${item.pallet4_in || 0},${item.pallet4_out || 0},${pallet4Stock},${item.amount}\n`;
                });

                const pallet1In = exportData.reduce((sum, item) => sum + item.pallet1_in, 0);
                const pallet1Out = exportData.reduce((sum, item) => sum + item.pallet1_out, 0);
                const pallet2In = exportData.reduce((sum, item) => sum + item.pallet2_in, 0);
                const pallet2Out = exportData.reduce((sum, item) => sum + item.pallet2_out, 0);
                const totalAmount = exportData.reduce((sum, item) => sum + item.amount, 0);

                csv += `\n,,,TOTAL,${pallet1In},${pallet1Out},${pallet2In},${pallet2Out},,${totalAmount}\n`;

                const blob = new Blob([csv], {
                    type: 'text/csv'
                });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download =
                    `Account_Transaction_${currentFilters.company}_${new Date().getTime()}.csv`;
                a.click();
                window.URL.revokeObjectURL(url);
            });

            // Helper function to get filtered data
            function getFilteredData() {
                let filteredData = accountTransactionData.filter(item => {
                    const itemDate = new Date(item.finance_mutation_date);
                    const itemMonth = itemDate.getMonth() + 1;
                    const itemYear = itemDate.getFullYear();

                    if (item.company !== currentFilters.company) return false;
                    if (currentFilters.month && itemMonth !== parseInt(currentFilters.month)) return false;
                    if (currentFilters.year && itemYear !== parseInt(currentFilters.year)) return false;
                    if (currentFilters.customer && item.customer !== currentFilters.customer) return false;

                    if (currentFilters.search) {
                        const searchLower = currentFilters.search.toLowerCase();
                        return (
                            item.document_no.toLowerCase().includes(searchLower) ||
                            item.customer.toLowerCase().includes(searchLower)
                        );
                    }

                    return true;
                });

                filteredData.sort((a, b) => new Date(a.finance_mutation_date) - new Date(b.finance_mutation_date));

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


            function updateStockAwal() {
                let dataMonth=$("#filterMonth").val();
                let dataYear=$("#filterYear").val();

                const date = new Date(dataYear, dataMonth);
                date.setMonth(date.getMonth() - 2);

                let cthnbln=date.getFullYear()+String(date.getMonth() + 1).padStart(2, '0');
                
                // if (!startingStockData[company]) {
                //     startingStockData[company] = {}; // create company object
                // }
                // startingStockData[company]['pallet1'] = 0;
                // startingStockData[company]['pallet2'] = 0;
                // startingStockData[company]['pallet3'] = 0;
                // startingStockData[company]['pallet4'] = 0;
                            // let arrckdpallet=[];
                let isadadata=false;
                startedAcountTransactions[cthnbln]?.forEach(element => {
                    isadadata=true;
                    let tempidx=arrckdpallet.findIndex(x => x == element.ckdbrg);
                    
                    if (!startingStockData[element.ckdcust]) {
                        startingStockData[element.ckdcust] = {}; // create company object
                        startingStockData[element.ckdcust]['pallet1'] = 0;
                        startingStockData[element.ckdcust]['nprice1'] = 0;
                        startingStockData[element.ckdcust]['pallet2'] = 0;
                        startingStockData[element.ckdcust]['nprice2'] = 0;
                        startingStockData[element.ckdcust]['pallet3'] = 0;
                        startingStockData[element.ckdcust]['nprice3'] = 0;
                        startingStockData[element.ckdcust]['pallet4'] = 0;
                        startingStockData[element.ckdcust]['nprice4'] = 0;
                    }                   
                    if(tempidx==0){
                        startingStockData[element.ckdcust]['pallet1']=element.nqty;
                        startingStockData[element.ckdcust]['nprice1']=element.nprice;
                    } 
                    else if(tempidx==1){
                        startingStockData[element.ckdcust]['pallet2']=element.nqty;
                        startingStockData[element.ckdcust]['nprice2']=element.nprice;
                    } 
                    else if(tempidx==2){
                        startingStockData[element.ckdcust]['pallet3']=element.nqty;
                        startingStockData[element.ckdcust]['nprice3']=element.nprice;
                    } 
                    else if(tempidx==3){
                        startingStockData[element.ckdcust]['pallet4']=element.nqty;
                        startingStockData[element.ckdcust]['nprice4']=element.nprice;
                    }
                })
                if(isadadata==false){
                    let ckdc=$("#filterCompany").val();
                    startingStockData[ckdc] = {}; // create company object
                    startingStockData[ckdc]['pallet1'] = 0;
                    startingStockData[ckdc]['nprice1'] = 0;
                    startingStockData[ckdc]['pallet2'] = 0;
                    startingStockData[ckdc]['nprice2'] = 0;
                    startingStockData[ckdc]['pallet3'] = 0;
                    startingStockData[ckdc]['nprice3'] = 0;
                    startingStockData[ckdc]['pallet4'] = 0;
                    startingStockData[ckdc]['nprice4'] = 0;
                }
                
            }

            async function loadStokMovement() {
                const bodyTableStock = document.getElementById('reportTableBody');
                // const selectElement = type === 'from' ? fromAddressSelect : toAddressSelect;
                bodyTableStock.innerHTML = '<tr><td>Loading...</td></tr>';
                let ckdcust = '{{ Auth::user()->ckdcust }}';

                try {
                    const response = await fetch(`/api/started_acount_transactions/getData/` + ckdcust);
                    // const response = await fetch(`/api/account-transactions/` + ckdcust);
                    
                    startedAcountTransactions = await response.json();
                } catch (error) {
                }


                try {
                    const response = await fetch(`/api/account-transactions/` + ckdcust);
                    
                    const accountTransactions = await response.json();
                    let ctr=0;
                    // let arrckdpallet=[];
                    let arrcbasic=[];
                    // startingStockData
                    accountTransactions.forEach(element => {
                        let type="";
                        let idxpallet=arrckdpallet.findIndex(element1 => element1 == element.ckdbrg);
                        
                        let idxtable=-1;
                        if(idxpallet >-1){
                            idxtable=idxpallet;
                            // arrckdpallet[idxpallet]=element.ckdbrg;
                            arrcbasic[idxpallet]=element.cbasic+" ("+element.npanjang+" x "+element.nlebar+" x "+element.ntinggi+")";
                        }
                        else{
                            arrckdpallet.push(element.ckdbrg);
                            idxtable=arrckdpallet.findIndex(element1 => element1 == element.ckdbrg);
                            arrcbasic.push(element.cbasic+" ("+element.npanjang+" x "+element.nlebar+" x "+element.ntinggi+")");
                        }
                        
                        for (let i = 1; i <= 4; i++) {
                            $("#theadbasic"+i).html(arrcbasic[i-1]);
                        }
                        
                        
                        
                        let njumlahIn=0;
                        let njumlahOut=0;
                        let nprice=0;
                        let njumlahIn2=0;
                        let njumlahOut2=0;
                        let nprice2=0;
                        let njumlahIn3=0;
                        let njumlahOut3=0;
                        let nprice3=0;
                        let njumlahIn4=0;
                        let njumlahOut4=0;
                        let nprice4=0;
                        
                        let customer="";
                        let company="";
                        let finance_mutation_date="";
                        let has_gr=true;
                        finance_mutation_date=element.finance_mutation_date;
                        if(element.cnobukti.substr(0,2).toUpperCase() == "SJ" || element.cnobukti.substr(0,2).toUpperCase() == "DN"){
                            if(element.cstatus == "OI"){
                                type = "DN";
                                // company=element.sjcnmcust_to;
                                company=element.sjckdcust_to;
                                customer=element.sjcnmcust_from;
                                has_gr=false;
                            }
                            else{
                                type = "DN";
                                // company=element.sjcnmcust_from;
                                company=element.sjckdcust_to;    
                                customer=element.sjcnmcust_to;
                            }
                        }
                        else{
                            type = "GR";
                            // company=element.bpbcnmcust_to;
                            company=element.bpbckdcust_to;
                            customer=element.bpbcnmcust_from;
                            // customer=element.ckdcust_from_bpb;
                        }
                        

                        // if (!startingStockData[company]) {
                        //     startingStockData[company] = {}; // create company object
                        // }
                        // startingStockData[company]['pallet1'] = 0;
                        // startingStockData[company]['pallet2'] = 0;
                        // startingStockData[company]['pallet3'] = 0;
                        // startingStockData[company]['pallet4'] = 0;


                        //  if (!startingStockData[customer]) {
                        //     startingStockData[customer] = {}; // create company object
                        // }
                        // startingStockData[customer]['pallet1'] = 0;
                        // startingStockData[customer]['pallet1'] = 0;
                        // startingStockData[customer]['pallet2'] = 0;
                        // startingStockData[customer]['pallet3'] = 0;
                        // startingStockData[customer]['pallet4'] = 0;

                        // if(element.cstatus=="A"){
                        //     // startingStockData[element.cnmcust][element.cbasic]=parseInt(element.nqty);
                        //     if (!startingStockData[customer]) {
                        //         startingStockData[customer] = {}; // create company object
                        //     }
                        //     if(idxtable == 0){
                        //         startingStockData[customer]['pallet1'] = parseInt(element.nqty);
                        //         startingStockData[customer]['pallet2'] = 0;
                        //         startingStockData[customer]['pallet3'] = 0;
                        //         startingStockData[customer]['pallet4'] = 0;
                        //     }
                        //     if(idxtable == 1){
                        //         startingStockData[customer]['pallet2'] = parseInt(element.nqty);
                        //     }
                        //     if(idxtable == 2){
                        //         startingStockData[customer]['pallet3'] = parseInt(element.nqty);
                        //     }
                        //     if(idxtable == 3){
                        //         startingStockData[customer]['pallet4'] = parseInt(element.nqty);
                        //     }
                        // }

                        if(idxtable == 0){   
                            if(type=="DN"){
                                if(element.cstatus=="OI"){
                                    njumlahIn=element.nqty;
                                    
                                }else{
                                    njumlahOut=element.nqty;
                                }
                            }
                            else if(type=="GR"){
                                njumlahIn=parseInt(element.ngood_qty);
                            }
                            nprice=element.harga;
                        }
                        else if(idxtable == 1){  
                            if(type=="DN"){
                                if(element.cstatus=="OI"){
                                    njumlahIn2=element.nqty;
                                }else{
                                    njumlahOut2=element.nqty;
                                }
                                // njumlahOut2=element.nqty;
                            }
                            else if(type=="GR"){
                                njumlahIn2=element.ngood_qty;
                            }
                            nprice2=element.harga;
                        }
                        else if(idxtable == 2){  
                            if(type=="DN"){
                                if(element.cstatus=="OI"){
                                    njumlahOut3=element.nqty;
                                }
                                else{
                                    njumlahOut3=element.nqty;
                                }
                            }
                            else if(type=="GR"){
                                njumlahIn3=element.ngood_qty;
                            }
                            nprice3=element.harga;
                        }
                        else if(idxtable == 3){  
                            if(type=="DN"){
                                if(element.cstatus=="OI"){
                                    njumlahIn4=element.nqty;
                                }else{
                                    njumlahOut4=element.nqty;
                                }
                            }
                            else if(type=="GR"){
                                njumlahIn4=element.ngood_qty;
                            }
                            nprice4=element.harga;
                        }        
                                        
                        ctr++;
                        accountTransactionData.push({
                            id: ctr,
                            finance_mutation_date: finance_mutation_date,
                            document_date: element.dtglbukti,
                            type: type,
                            document_no: element.cnobukti,
                            // customer: 'PT Gudang Garam Tbk',
                            customer: customer,
                            // company: 'PT Yanasurya Bhaktipersada',
                            // company: element.cnmcust,
                            company: company,
                            pallet1_in: parseInt(njumlahIn),
                            pallet1_out: parseInt(njumlahOut),
                            pallet2_in: parseInt(njumlahIn2),
                            pallet2_out: parseInt(njumlahOut2),
                            pallet3_in: parseInt(njumlahIn3),
                            pallet3_out: parseInt(njumlahOut3),
                            pallet4_in: parseInt(njumlahIn4),
                            pallet4_out: parseInt(njumlahOut4),
                            nprice: parseInt(nprice),
                            nprice2: parseInt(nprice2),
                            nprice3: parseInt(nprice3),
                            nprice4: parseInt(nprice4),
                            // Stock after this transaction: P1=550, P2=220, P3=200, P4=150
                            // Amount = (550*500 + 220*450 + 200*750 + 150*550) = 656,500
                            // amount: 656500
                            amount: (((njumlahIn-njumlahOut)*element.harga)+((njumlahIn2-njumlahOut2)*element.harga)+((njumlahIn3-njumlahOut3)*element.harga)+((njumlahIn4-njumlahOut4)*element.harga)),
                            has_gr: has_gr
                        });
                        
                    });                    
                    
                    filterAndRender();
                    updateStockAwal();

                } catch (error) {
                }



                
            }
            loadStokMovement();

            setTimeout(() => {
                let ckdcust="{{ Auth::user()->ckdcust }}";
                $("#filterCompany").val(ckdcust).change();
            }, 3000);
            // Initial render
            // filterAndRender();
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            window.startAccountTransactionTour = function() {
                const driver = window.driver.js.driver;

                const driverObj = driver({
                    showProgress: true,
                    showButtons: ['next', 'previous', 'close'],
                    steps: [{
                            popover: {
                                title: '📊 {{ __('Account Transaction Report') }}',
                                description: '{{ __('This report shows daily pallet movements and rental charge calculations per pallet type.') }}'
                            }
                        },
                        {
                            element: '#filterCompany',
                            popover: {
                                title: '🏢 {{ __('Company Selection (Required)') }}',
                                description: '{{ __('You must select a company first. All calculations, stock balances, and charges depend on the selected company.') }}',
                                side: 'bottom',
                                align: 'start'
                            }
                        },
                        {
                            element: '#accountTxnPeriodFilter',
                            popover: {
                                title: '📅 {{ __('Period Filter') }}',
                                description: '{{ __('Filter transactions by month and year to analyze a specific accounting period.') }}',
                                side: 'bottom',
                                align: 'start'
                            }
                        },
                        {
                            element: '#filterCustomer',
                            popover: {
                                title: '👥 {{ __('Customer Filter') }}',
                                description: '{{ __('Limit the report to transactions involving a specific customer.') }}',
                                side: 'bottom',
                                align: 'start'
                            }
                        },
                        {
                            element: '#searchInput',
                            popover: {
                                title: '🔍 {{ __('Quick Search') }}',
                                description: '{{ __('Search by document number (DN / GR) or customer name.') }}',
                                side: 'bottom',
                                align: 'start'
                            }
                        },
                        {
                            element: '#resetFilters',
                            popover: {
                                title: '♻️ {{ __('Reset Filters') }}',
                                description: '{{ __('Clear all filters and return to the default view.') }}',
                                side: 'top',
                                align: 'center'
                            }
                        },
                        {
                            element: '#accountTransactionTable',
                            popover: {
                                title: '📋 {{ __('Transaction Details') }}',
                                description: `
                            {{ __('Each row represents a pallet transaction:') }}<br><br>
                            • <strong>DN</strong> = {{ __('Delivery Note') }} (Pallets Out)<br>
                            • <strong>GR</strong> = {{ __('Goods Receipt') }} (Pallets In)<br><br>
                            {{ __('Stock is recalculated after each transaction.') }}
                        `,
                                side: 'top',
                                align: 'start'
                            }
                        },
                        {
                            popover: {
                                title: '🧮 {{ __('Stock & Amount Calculation') }}',
                                description: `
                            {{ __('The') }} <strong>{{ __('Amount') }}</strong> {{ __('column is calculated from the ending stock value per day:') }}<br><br>
                            {{ __('Stock × Rental Rate per pallet type.') }}
                        `
                            }
                        },
                        {
                            element: '#accountTxnActions',
                            popover: {
                                title: '⬇️ {{ __('Export & Print') }}',
                                description: '{{ __('Export this report to Excel, PDF, or CSV — or print it in official accounting format.') }}',
                                side: 'left',
                                align: 'start'
                            }
                        },
                        {
                            popover: {
                                title: '🎉 {{ __('You’re Done!') }}',
                                description: `
                            {{ __('You now understand how to analyze daily pallet movements and rental charges.') }}<br><br>
                            <strong>{{ __('Tip:') }}</strong> {{ __('This report is the accounting backbone for monthly billing.') }}
                        `
                            }
                        }
                    ]
                });

                driverObj.drive();
                return driverObj;
            };

            // Register as page tour
            window.startProductTour = window.startAccountTransactionTour;

        });
    </script>
@endpush
