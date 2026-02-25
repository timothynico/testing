@extends('layouts.app')

@section('title', __('Monthly Usage Details'))

@section('header-left')
    <div>
        <h2 class="h5 mb-0 text-dark fw-semibold">{{ __('Monthly Usage Details') }}</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('usage.index') }}">{{ __('Monthly Usage') }}</a></li>
                <li class="breadcrumb-item active">{{ __('January 2026') }} - PT Yanasurya Bhaktipersada</li>
            </ol>
        </nav>
    </div>
@endsection

@section('content')
    <!-- Usage Header Card -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-start gap-3">
                        <div class="usage-icon">
                            <i class="bi bi-bar-chart-line display-4 text-success"></i>
                        </div>
                        <div>
                            <h2 class="h5 mb-1 fw-semibold">PT Yanasurya Bhaktipersada</h2>
                            <p class="text-muted mb-2 small">{{ __('Monthly Pallet Usage Report') }}</p>
                            <div class="d-flex gap-2 flex-wrap">
                                <span class="badge bg-warning text-dark rounded-pill">
                                    <i class="bi bi-clock-history me-1"></i>{{ __('Waiting for Approval') }}
                                </span>
                                <span class="badge bg-secondary rounded-pill">
                                    <i class="bi bi-calendar me-1"></i>{{ __('Period') }}: January 2026
                                </span>
                                <span class="badge bg-info rounded-pill">
                                    <i class="bi bi-box-seam me-1"></i>{{ __('Total Days') }}: 31 {{__('days')}}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="bg-light rounded p-3">
                        <div class="text-muted small mb-1">{{ __('Submission Date') }}</div>
                        <div class="h5 mb-0 fw-bold text-success">01 Feb 2026</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="d-flex align-items-center flex-wrap gap-2 mb-3">
        <span class="text-muted small fw-semibold me-2">
            {{ __('Quick Actions') }}:
        </span>

        <button type="button" class="btn btn-sm btn-success" onclick="approveUsage()">
            <i class="bi bi-check-circle me-1"></i>
            {{ __('Approve') }}
        </button>

        <button type="button" class="btn btn-sm btn-warning" onclick="openComplaintModal()">
            <i class="bi bi-exclamation-triangle me-1"></i>
            {{ __('Revision Request') }}
        </button>
    </div>

    <!-- Daily Transactions Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center bg-success text-white">
            <span>
                <i class="bi bi-table me-2"></i>{{ __('Daily Transaction Details') }}
            </span>
            <span class="badge bg-white text-success">31 {{ __('days') }}</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 70vh; overflow-y: auto;">
                <table class="table table-hover align-middle mb-0" id="dailyTransactionTable">
                    <thead class="table-light" style="position: sticky; top: 0; z-index: 10;">
                        <tr>
                            <th class="px-3 py-2" rowspan="2" style="vertical-align: middle;">{{ __('Mut. Date') }}</th>
                            <th class="px-3 py-2" rowspan="2" style="vertical-align: middle;">{{ __('Type') }}</th>
                            <th class="px-3 py-2" rowspan="2" style="vertical-align: middle;">{{ __('Doc. Number') }}
                            </th>
                            <th class="px-3 py-2" rowspan="2" style="vertical-align: middle;">{{ __('Doc. Date') }}</th>
                            <th class="px-3 py-2" rowspan="2" style="vertical-align: middle;">{{ __('From/To') }}</th>
                            <th class="px-3 py-2 text-center" colspan="3">{{ __('PT1210AS (1200 x 1000 x 160)') }}</th>
                            <th class="px-3 py-2 text-center" colspan="3">{{ __('PF1210 (1200 x 1000 x 160)') }}</th>
                            <th class="px-3 py-2 text-center" colspan="3">{{ __('B325 (1300 x 1200 x 150)') }}</th>
                            <th class="px-3 py-2 text-center" colspan="3">{{ __('PT1212 (1200 x 1200 x 160)') }}</th>
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
                        <!-- Grand Total Row -->
                        <tr class="table-light fw-semibold"
                            style="background-color: #e9ecef !important; border-top: 2px solid #198754; border-bottom: 2px solid #198754;">
                            <td colspan="5" class="px-3 py-2 text-end">{{ __('Grand Total') }}</td>
                            <td class="px-3 py-2 text-center">870</td>
                            <td class="px-3 py-2 text-center">570</td>
                            <td class="px-3 py-2 text-center">4,400</td>
                            <td class="px-3 py-2 text-center">480</td>
                            <td class="px-3 py-2 text-center">370</td>
                            <td class="px-3 py-2 text-center">1,760</td>
                            <td class="px-3 py-2 text-center">130</td>
                            <td class="px-3 py-2 text-center">90</td>
                            <td class="px-3 py-2 text-center">1,600</td>
                            <td class="px-3 py-2 text-center">60</td>
                            <td class="px-3 py-2 text-center">50</td>
                            <td class="px-3 py-2 text-center">1,200</td>
                        </tr>
                    </tbody>
                    <tbody id="transactionTableBody">
                        <!-- Starting Stock Row -->
                        <tr class="table-warning">
                            <td colspan="5" class="px-3 py-2 fw-semibold text-end">{{ __('Starting Stock') }}</td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"><span class="fw-semibold">500</span></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"><span class="fw-semibold">300</span></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"><span class="fw-semibold">200</span></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"><span class="fw-semibold">150</span></td>
                        </tr>

                        <!-- Day 1: January 30, 2026 -->
                        <tr>
                            <td class="px-3 py-2">30/01/26</td>
                            <td class="px-3 py-2"><span class="badge bg-danger">DN</span></td>
                            <td class="px-3 py-2"><span class="fw-semibold text-primary">DN/2026/030</span></td>
                            <td class="px-3 py-2">29/01/26</td>
                            <td class="px-3 py-2">PT Gudang Garam Tbk</td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"><span class="text-danger fw-semibold">-150</span></td>
                            <td class="px-3 py-2 text-center"><span class="fw-semibold">350</span></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"><span class="text-danger fw-semibold">-80</span></td>
                            <td class="px-3 py-2 text-center"><span class="fw-semibold">220</span></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"><span class="fw-semibold">200</span></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"><span class="fw-semibold">150</span></td>
                        </tr>

                        <tr>
                            <td class="px-3 py-2">30/01/26</td>
                            <td class="px-3 py-2"><span class="badge bg-success">GR</span></td>
                            <td class="px-3 py-2"><span class="fw-semibold text-primary">GR/2026/028</span></td>
                            <td class="px-3 py-2">29/01/26</td>
                            <td class="px-3 py-2">PT Indofood CBP Sukses Makmur</td>
                            <td class="px-3 py-2 text-center"><span class="text-success fw-semibold">+200</span></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"><span class="fw-semibold">550</span></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"><span class="fw-semibold">220</span></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"><span class="fw-semibold">200</span></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"><span class="fw-semibold">150</span></td>
                        </tr>

                        <tr>
                            <td class="px-3 py-2">30/01/26</td>
                            <td class="px-3 py-2"><span class="badge bg-danger">DN</span></td>
                            <td class="px-3 py-2"><span class="fw-semibold text-primary">DN/2026/031</span></td>
                            <td class="px-3 py-2">30/01/26</td>
                            <td class="px-3 py-2">PT Nestle Indonesia</td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"><span class="text-danger fw-semibold">-100</span></td>
                            <td class="px-3 py-2 text-center"><span class="fw-semibold">450</span></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"><span class="fw-semibold">220</span></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"><span class="fw-semibold">200</span></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"><span class="fw-semibold">150</span></td>
                        </tr>

                        <!-- Daily Total Row -->
                        <tr class="table-info fw-semibold">
                            <td class="px-3 py-2">30/01/26</td>
                            <td colspan="4" class="px-3 py-2 text-end">{{ __('Daily Total') }}</td>
                            <td class="px-3 py-2 text-center">200</td>
                            <td class="px-3 py-2 text-center">250</td>
                            <td class="px-3 py-2 text-center">450</td>
                            <td class="px-3 py-2 text-center">0</td>
                            <td class="px-3 py-2 text-center">80</td>
                            <td class="px-3 py-2 text-center">220</td>
                            <td class="px-3 py-2 text-center">0</td>
                            <td class="px-3 py-2 text-center">0</td>
                            <td class="px-3 py-2 text-center">200</td>
                            <td class="px-3 py-2 text-center">0</td>
                            <td class="px-3 py-2 text-center">0</td>
                            <td class="px-3 py-2 text-center">150</td>
                        </tr>

                        <!-- Day 2: January 29, 2026 -->
                        <tr>
                            <td class="px-3 py-2">29/01/26</td>
                            <td class="px-3 py-2"><span class="badge bg-success">GR</span></td>
                            <td class="px-3 py-2"><span class="fw-semibold text-primary">GR/2026/027</span></td>
                            <td class="px-3 py-2">28/01/26</td>
                            <td class="px-3 py-2">PT Wings Surya</td>
                            <td class="px-3 py-2 text-center"><span class="text-success fw-semibold">+180</span></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"><span class="fw-semibold">680</span></td>
                            <td class="px-3 py-2 text-center"><span class="text-success fw-semibold">+120</span></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"><span class="fw-semibold">340</span></td>
                            <td class="px-3 py-2 text-center"><span class="text-success fw-semibold">+50</span></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"><span class="fw-semibold">250</span></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"><span class="fw-semibold">150</span></td>
                        </tr>

                        <tr>
                            <td class="px-3 py-2">29/01/26</td>
                            <td class="px-3 py-2"><span class="badge bg-danger">DN</span></td>
                            <td class="px-3 py-2"><span class="fw-semibold text-primary">DN/2026/029</span></td>
                            <td class="px-3 py-2">28/01/26</td>
                            <td class="px-3 py-2">PT Frisian Flag Indonesia</td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"><span class="text-danger fw-semibold">-90</span></td>
                            <td class="px-3 py-2 text-center"><span class="fw-semibold">590</span></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"><span class="text-danger fw-semibold">-60</span></td>
                            <td class="px-3 py-2 text-center"><span class="fw-semibold">280</span></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"><span class="fw-semibold">250</span></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"><span class="fw-semibold">150</span></td>
                        </tr>

                        <!-- Daily Total Row -->
                        <tr class="table-info fw-semibold">
                            <td class="px-3 py-2">29/01/26</td>
                            <td colspan="4" class="px-3 py-2 text-end">{{ __('Daily Total') }}</td>
                            <td class="px-3 py-2 text-center">180</td>
                            <td class="px-3 py-2 text-center">90</td>
                            <td class="px-3 py-2 text-center">590</td>
                            <td class="px-3 py-2 text-center">120</td>
                            <td class="px-3 py-2 text-center">60</td>
                            <td class="px-3 py-2 text-center">280</td>
                            <td class="px-3 py-2 text-center">50</td>
                            <td class="px-3 py-2 text-center">0</td>
                            <td class="px-3 py-2 text-center">250</td>
                            <td class="px-3 py-2 text-center">0</td>
                            <td class="px-3 py-2 text-center">0</td>
                            <td class="px-3 py-2 text-center">150</td>
                        </tr>

                        <!-- Day 3: January 28, 2026 -->
                        <tr>
                            <td class="px-3 py-2">28/01/26</td>
                            <td class="px-3 py-2"><span class="badge bg-danger">DN</span></td>
                            <td class="px-3 py-2"><span class="fw-semibold text-primary">DN/2026/028</span></td>
                            <td class="px-3 py-2">27/01/26</td>
                            <td class="px-3 py-2">PT Unilever Indonesia Tbk</td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"><span class="text-danger fw-semibold">-70</span></td>
                            <td class="px-3 py-2 text-center"><span class="fw-semibold">520</span></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"><span class="fw-semibold">280</span></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"><span class="fw-semibold">250</span></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"><span class="text-danger fw-semibold">-10</span></td>
                            <td class="px-3 py-2 text-center"><span class="fw-semibold">140</span></td>
                        </tr>

                        <tr>
                            <td class="px-3 py-2">28/01/26</td>
                            <td class="px-3 py-2"><span class="badge bg-success">GR</span></td>
                            <td class="px-3 py-2"><span class="fw-semibold text-primary">GR/2026/026</span></td>
                            <td class="px-3 py-2">27/01/26</td>
                            <td class="px-3 py-2">PT Kao Indonesia</td>
                            <td class="px-3 py-2 text-center"><span class="text-success fw-semibold">+160</span></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"><span class="fw-semibold">680</span></td>
                            <td class="px-3 py-2 text-center"><span class="text-success fw-semibold">+100</span></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"><span class="fw-semibold">380</span></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"><span class="fw-semibold">250</span></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"><span class="fw-semibold">140</span></td>
                        </tr>

                        <!-- Daily Total Row -->
                        <tr class="table-info fw-semibold">
                            <td class="px-3 py-2">28/01/26</td>
                            <td colspan="4" class="px-3 py-2 text-end">{{ __('Daily Total') }}</td>
                            <td class="px-3 py-2 text-center">160</td>
                            <td class="px-3 py-2 text-center">70</td>
                            <td class="px-3 py-2 text-center">680</td>
                            <td class="px-3 py-2 text-center">100</td>
                            <td class="px-3 py-2 text-center">0</td>
                            <td class="px-3 py-2 text-center">380</td>
                            <td class="px-3 py-2 text-center">0</td>
                            <td class="px-3 py-2 text-center">0</td>
                            <td class="px-3 py-2 text-center">250</td>
                            <td class="px-3 py-2 text-center">0</td>
                            <td class="px-3 py-2 text-center">10</td>
                            <td class="px-3 py-2 text-center">140</td>
                        </tr>

                        <!-- Day 4: January 27, 2026 -->
                        <tr>
                            <td class="px-3 py-2">27/01/26</td>
                            <td class="px-3 py-2"><span class="badge bg-success">GR</span></td>
                            <td class="px-3 py-2"><span class="fw-semibold text-primary">GR/2026/025</span></td>
                            <td class="px-3 py-2">26/01/26</td>
                            <td class="px-3 py-2">PT Gudang Garam Tbk</td>
                            <td class="px-3 py-2 text-center"><span class="text-success fw-semibold">+150</span></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"><span class="fw-semibold">670</span></td>
                            <td class="px-3 py-2 text-center"><span class="text-success fw-semibold">+80</span></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"><span class="fw-semibold">300</span></td>
                            <td class="px-3 py-2 text-center"><span class="text-success fw-semibold">+30</span></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"><span class="fw-semibold">230</span></td>
                            <td class="px-3 py-2 text-center"><span class="text-success fw-semibold">+20</span></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"><span class="fw-semibold">160</span></td>
                        </tr>

                        <tr>
                            <td class="px-3 py-2">27/01/26</td>
                            <td class="px-3 py-2"><span class="badge bg-danger">DN</span></td>
                            <td class="px-3 py-2"><span class="fw-semibold text-primary">DN/2026/027</span></td>
                            <td class="px-3 py-2">26/01/26</td>
                            <td class="px-3 py-2">PT Danone Indonesia</td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"><span class="text-danger fw-semibold">-120</span></td>
                            <td class="px-3 py-2 text-center"><span class="fw-semibold">550</span></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"><span class="text-danger fw-semibold">-70</span></td>
                            <td class="px-3 py-2 text-center"><span class="fw-semibold">230</span></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"><span class="text-danger fw-semibold">-30</span></td>
                            <td class="px-3 py-2 text-center"><span class="fw-semibold">200</span></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"><span class="text-danger fw-semibold">-10</span></td>
                            <td class="px-3 py-2 text-center"><span class="fw-semibold">150</span></td>
                        </tr>

                        <tr>
                            <td class="px-3 py-2">27/01/26</td>
                            <td class="px-3 py-2"><span class="badge bg-danger">DN</span></td>
                            <td class="px-3 py-2"><span class="fw-semibold text-primary">DN/2026/026</span></td>
                            <td class="px-3 py-2">26/01/26</td>
                            <td class="px-3 py-2">PT Nestle Indonesia</td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"><span class="text-danger fw-semibold">-50</span></td>
                            <td class="px-3 py-2 text-center"><span class="fw-semibold">500</span></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"><span class="fw-semibold">230</span></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"><span class="fw-semibold">200</span></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"><span class="fw-semibold">150</span></td>
                        </tr>

                        <!-- Daily Total Row -->
                        <tr class="table-info fw-semibold">
                            <td class="px-3 py-2">27/01/26</td>
                            <td colspan="4" class="px-3 py-2 text-end">{{ __('Daily Total') }}</td>
                            <td class="px-3 py-2 text-center">150</td>
                            <td class="px-3 py-2 text-center">170</td>
                            <td class="px-3 py-2 text-center">500</td>
                            <td class="px-3 py-2 text-center">80</td>
                            <td class="px-3 py-2 text-center">70</td>
                            <td class="px-3 py-2 text-center">230</td>
                            <td class="px-3 py-2 text-center">30</td>
                            <td class="px-3 py-2 text-center">30</td>
                            <td class="px-3 py-2 text-center">200</td>
                            <td class="px-3 py-2 text-center">20</td>
                            <td class="px-3 py-2 text-center">10</td>
                            <td class="px-3 py-2 text-center">150</td>
                        </tr>

                        <!-- More days indicator -->
                        <tr>
                            <td colspan="17" class="text-center text-muted py-3">
                                <i class="bi bi-three-dots"></i>
                                <span class="small ms-2">{{ __('23 more days of transactions') }}...</span>
                            </td>
                        </tr>

                        <!-- Closing Balance Row -->
                        <tr class="table-warning">
                            <td colspan="5" class="px-3 py-2 fw-semibold text-end">{{ __('Closing Balance') }}</td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"><span class="fw-semibold">800</span></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"><span class="fw-semibold">410</span></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"><span class="fw-semibold">240</span></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"></td>
                            <td class="px-3 py-2 text-center"><span class="fw-semibold">160</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-light">
            <div class="row">
                <div class="col-md-6">
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        {{ __('Total Transactions') }}: <strong>87 {{ __('records') }}</strong>
                    </small>
                </div>
                <div class="col-md-6 text-end">
                    <small class="text-muted">
                        {{ __('Period') }}: <strong>01 Jan - 31 Jan 2026</strong>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Complaint Modal -->
    <div class="modal fade" id="complaintModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle me-2 text-warning"></i>
                        {{ __('Revision Request') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="alert alert-warning small">
                        <i class="bi bi-info-circle me-2"></i>
                        {{ __('Please describe the issue with this usage report. The system will notify the relevant parties.') }}
                    </div>

                    <form id="complaintForm">
                        @csrf

                        <!-- Hidden Fields -->
                        <input type="hidden" name="ctype" value="usage">
                        <input type="hidden" name="creference" value="{{ $usage->id ?? '' }}">
                        <input type="hidden" name="curl" id="complaintUrl">

                        <!-- Issue Category -->
                        <div class="mb-3">
                            <label class="form-label">
                                {{ __('Issue Category') }} 
                                <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" name="cissue" required>
                                <option value="">{{ __('Select category') }}</option>
                                <option value="incorrect quantity">{{ __('Incorrect Quantity') }}</option>
                                <option value="missing transaction">{{ __('Missing Transaction') }}</option>
                                <option value="wrong date">{{ __('Wrong Date') }}</option>
                                <option value="duplicate entry">{{ __('Duplicate Entry') }}</option>
                                <option value="other">{{ __('Other') }}</option>
                            </select>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label class="form-label">
                                {{ __('Description') }} 
                                <span class="text-danger">*</span>
                            </label>
                            <textarea 
                                class="form-control" 
                                name="cdescription"
                                rows="4"
                                placeholder="{{ __('Please provide details about the issue...') }}"
                                required></textarea>
                        </div>
                    </form>
                </div>

                <div class="modal-footer">
                    <button type="button" 
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">
                        {{ __('Cancel') }}
                    </button>

                    <button type="button" 
                            class="btn btn-warning" 
                            onclick="submitComplaint()">
                        <i class="bi bi-send me-1"></i>
                        {{ __('Submit Complaint') }}
                    </button>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .breadcrumb {
            background: none;
            padding: 0;
            margin-top: 0.25rem;
        }

        .breadcrumb-item+.breadcrumb-item::before {
            content: "›";
        }

        .breadcrumb-item a {
            color: #6c757d;
            text-decoration: none;
        }

        .breadcrumb-item a:hover {
            color: #198754;
        }

        .usage-icon {
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #d1f2eb 0%, #a7f3d0 100%);
            border-radius: 0.5rem;
        }

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

        .table-warning {
            background-color: #fff3cd !important;
        }

        .table-warning:hover {
            background-color: #fff3cd !important;
        }

        .table-info {
            background-color: #cfe2ff !important;
        }

        .table-info:hover {
            background-color: #cfe2ff !important;
        }

        .badge {
            font-weight: 500;
            font-size: 0.75rem;
        }

        .rounded-pill {
            border-radius: 50rem !important;
        }
    </style>
@endpush

@push('scripts')
    <script>
        function approveUsage() {
            if (confirm('{{ __('Are you sure you want to approve this monthly usage report?') }}')) {
                console.log('Usage approved');
                alert('{{ __('Usage report has been approved successfully!') }}');
                // window.location.href = '/usage';
            }
        }

        function openComplaintModal() {
            const modal = new bootstrap.Modal(document.getElementById('complaintModal'));
            modal.show();
        }

        function submitComplaint() {

            // Set current URL
            document.getElementById('complaintUrl').value = window.location.pathname;

            let form = document.getElementById('complaintForm');
            let formData = new FormData(form);

            fetch("{{ route('chatrooms.store') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('input[name=\"_token\"]').value
                },
                body: formData
            })
            .then(async response => {
                const data = await response.json();

                if (!response.ok) {
                    throw data;
                }

                return data;
            })
            .then(data => {
                alert(data.message);
                window.location.href = '/chatrooms/';
            })
            .catch(error => {
                alert(error.message || 'Something went wrong.');
            });
        }
    </script>
@endpush
