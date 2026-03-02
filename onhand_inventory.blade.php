@extends('layouts.app')

@section('title', __('On-hand Inventory'))

@section('header-left')
    <div>
        <h2 class="h5 fw-semibold mb-0">{{ __('On-hand Inventory') }}</h2>
        <span class="text-muted small">{{ __('Summary of available pallets by specification and quantity') }}</span>
    </div>

    <!-- Hidden print container -->
    <div id="printContainer"></div>
@endsection

@section('content')
    <!-- Filters Card -->
    <div class="card mb-3">
        <div class="card-body py-3" id="onHandFilters">
            <form id="filterForm">
                <div class="row g-3 align-items-end">
                    <!-- Date Filter -->
                    <div class="col-2 col-md-2">
                        <label for="filterDate" class="form-label small fw-semibold">
                            {{ __('As Of Date') }} <span class="text-danger">*</span>
                        </label>
                        <input type="date" class="form-control form-control-sm" id="filterDate" name="date">
                    </div>

                    <!-- Company Filter -->
                    <div class="col-3 col-md-3" id="companyFilterWrap">
                        <label for="filterCompany" class="form-label small fw-semibold">
                            {{ __('Company') }} <span class="text-danger">*</span>
                        </label>
                        <select class="form-select form-select-sm searchable-select" id="filterCompany" name="company">
                            <option value="">{{ __('Select Company...') }}</option>
                            @foreach ($arrdatacust as $cust)
                                <option value="{{ $cust->ckdcust }}">{{ $cust->cnmcust }}</option>
                            @endforeach
                            {{-- <option value="PT Yanasurya Bhaktipersada">PT Yanasurya Bhaktipersada</option>
                            <option value="PT Yanaprima Hastapersada">PT Yanaprima Hastapersada (Subsidiary)</option>
                            <option value="PT Forindoprima Perkasa">PT Forindoprima Perkasa (Subsidiary)</option> --}}
                        </select>
                    </div>

                    <!-- Warehouse Filter -->
                    <div class="col-2 col-md-2" id="warehouseFilterWrap">
                        <label for="filterWarehouse" class="form-label small fw-semibold">{{ __('Warehouse') }}</label>
                        <select class="form-select form-select-sm searchable-select" id="filterWarehouse" name="warehouse">
                            <option value="">{{ __('All Warehouses') }}</option>
                            <option value="WH-A">WH-A (Main)</option>
                            <option value="WH-B">WH-B (Secondary)</option>
                            <option value="WH-C">WH-C (Transit)</option>
                        </select>
                    </div>

                    <!-- Search -->
                    <div class="col-4 col-md-4">
                        <label for="searchInput" class="form-label small fw-semibold">{{ __('Search') }}</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" class="form-control" id="searchInput"
                                placeholder="{{ __('Search ID Pallet, Usage, Basic...') }}">
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
        <div class="card-header d-flex justify-content-between align-items-center bg-brand text-white">
            <span>
                <i class="bi bi-box-seam me-2"></i>{{ __('On-Hand Inventory Details') }}
            </span>
            <div class="d-flex gap-2" id="onHandActions">
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
                <table class="table table-hover align-middle mb-0" id="inventoryTable">
                    <thead class="table-light">
                        <tr>
                            <th class="px-3 py-2 text-center" style="width: 5%;">{{ __('No') }}</th>
                            <th class="px-3 py-2" style="width: 10%;">{{ __('ID Pallet') }}</th>
                            <th class="px-3 py-2" style="width: 12%;">{{ __('Usage') }}</th>
                            <th class="px-3 py-2" style="width: 12%;">{{ __('Basic') }}</th>
                            <th class="px-3 py-2 text-center" style="width: 14%;">{{ __('Size (mm)') }}</th>
                            {{-- <th class="px-3 py-2 text-center" style="width: 8%;">{{ __('MD') }}</th>
                            <th class="px-3 py-2 text-center" style="width: 8%;">{{ __('MR') }}</th> --}}
                            <th class="px-3 py-2 text-center" style="width: 10%;">{{ __('Color') }}</th>
                            <th class="px-3 py-2 text-center" style="width: 12%;">{{ __('Quantity (Pcs)') }}</th>
                        </tr>
                    </thead>
                    <tbody id="reportTableBody">
                        <!-- Content will be populated by JavaScript -->
                    </tbody>
                    <tfoot class="table-light">
                        <tr class="fw-semibold" id="totalRow" style="display: none;">
                            <td colspan="6" class="px-3 py-2 text-end">{{ __('Total Quantity') }}</td>
                            <td class="px-3 py-2 text-center" id="totalQuantity">0</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center py-2">
            <p class="text-muted small mb-0" id="paginationInfo">
                {{ __('Please select a company and date to view data') }}
            </p>
        </div>
    </div>

    <!-- Sample Data -->
    <script>
        const inventoryData = [];
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
            background-color: #f1f5f9;
        }

        .table tfoot tr {
            background-color: #e9ecef !important;
            border-top: 2px solid #198754;
        }

        .badge {
            font-weight: 500;
            font-size: 0.75rem;
            padding: 0.35em 0.65em;
        }

        /* Print Styles */
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

        .inventory-report-print {
            font-family: 'Arial', sans-serif;
            font-size: 9pt;
            color: #000;
            background: white;
            padding: 1cm;
        }

        .inventory-report-print .header {
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }

        .inventory-report-print .header-content {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 15px;
        }

        .inventory-report-print .company-logo {
            height: 40px;
            width: auto;
            object-fit: contain;
        }

        .inventory-report-print .company-name {
            font-size: 18pt;
            font-weight: bold;
            color: #000;
            letter-spacing: 2px;
        }

        .inventory-report-print .report-title {
            font-size: 14pt;
            font-weight: bold;
            text-align: center;
            margin: 10px 0;
            text-transform: uppercase;
        }

        .inventory-report-print .report-info {
            display: grid;
            grid-template-columns: 150px 1fr;
            gap: 3px 10px;
            margin-bottom: 15px;
            font-size: 9pt;
        }

        .inventory-report-print .info-label {
            font-weight: bold;
        }

        .inventory-report-print table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .inventory-report-print table th,
        .inventory-report-print table td {
            border: 1px solid #000;
            padding: 4px 6px;
            font-size: 8pt;
            line-height: 1.3;
        }

        .inventory-report-print table th {
            background-color: #e0e0e0;
            font-weight: bold;
            text-align: center;
        }

        .inventory-report-print table td.text-center {
            text-align: center;
        }

        .inventory-report-print tfoot td {
            background-color: #e8e8e8 !important;
            font-weight: bold;
        }

        .inventory-report-print .footer-note {
            margin-top: 15px;
            font-size: 7pt;
            text-align: center;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 5px;
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
                date: '',
                company: '',
                warehouse: '',
                search: ''
            };

            // DOM elements
            const tableBody = document.getElementById('reportTableBody');
            const totalRow = document.getElementById('totalRow');
            const paginationInfo = document.getElementById('paginationInfo');
            const searchInput = document.getElementById('searchInput');
            const clearSearchBtn = document.getElementById('clearSearch');

            // Initialize Select2
            $('#filterCompany').select2({
                width: '100%',
                placeholder: '{{ __('Select Company...') }}'
            });

            $('#filterWarehouse').select2({
                width: '100%',
                placeholder: '{{ __('All Warehouses') }}'
            });

            // Set default date to today
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('filterDate').value = today;
            currentFilters.date = today;

            // Set default company
            $('#filterCompany').val('PT Yanasurya Bhaktipersada').trigger('change');
            currentFilters.company = 'PT Yanasurya Bhaktipersada';

            // Filter handlers
            document.getElementById('filterDate').addEventListener('change', function() {
                currentFilters.date = this.value;
                filterAndRender();
            });

            $('#filterCompany').on('change', function() {
                currentFilters.company = this.value;
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
                document.getElementById('filterDate').value = today;
                $('#filterCompany').val('').trigger('change');
                $('#filterWarehouse').val('').trigger('change');
                searchInput.value = '';
                clearSearchBtn.classList.add('d-none');
                currentFilters = {
                    date: today,
                    company: '',
                    warehouse: '',
                    search: ''
                };
                filterAndRender();
            });

            // Filter and render data
            function filterAndRender() {
                // Check if required filters are selected
                if (!currentFilters.company || !currentFilters.date) {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-filter-circle display-4 d-block mb-3"></i>
                                    <p class="mb-1 fw-semibold">{{ __('Please select company and date') }}</p>
                                    <p class="small mb-0">{{ __('Choose company and date from filters above to view inventory') }}</p>
                                </div>
                            </td>
                        </tr>
                    `;
                    totalRow.style.display = 'none';
                    paginationInfo.textContent = '{{ __('Please select a company and date to view data') }}';
                    return;
                }

                let filteredData = inventoryData.filter(item => {
                    // Company filter (MANDATORY)
                    if (item.company !== currentFilters.company) {
                        return false;
                    }

                    // Date filter (MANDATORY)
                    if (item.date !== currentFilters.date) {
                        return false;
                    }

                    // Warehouse filter
                    if (currentFilters.warehouse && item.warehouse !== currentFilters.warehouse) {
                        return false;
                    }

                    if (currentFilters.search) {
                        const searchLower = currentFilters.search.toLowerCase();
                        return (
                            item.pallet_id.toLowerCase().includes(searchLower) ||
                            item.usage.toLowerCase().includes(searchLower) ||
                            item.basic.toLowerCase().includes(searchLower) ||
                            item.size.toLowerCase().includes(searchLower) ||
                            item.color.toLowerCase().includes(searchLower)
                        );
                    }

                    return true;
                });

                // Render table
                renderTable(filteredData);

                // Update total row
                updateTotalRow(filteredData);

                // Update info text
                const total = filteredData.length;
                paginationInfo.textContent = `{{ __('Showing') }} ${total} {{ __('records') }}`;
            }

            // Render table
            function renderTable(data) {
                if (data.length === 0) {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-inbox display-4 d-block mb-3"></i>
                                    <p class="mb-1 fw-semibold">{{ __('No inventory found') }}</p>
                                    <p class="small mb-0">{{ __('Try adjusting your filters') }}</p>
                                </div>
                            </td>
                        </tr>
                    `;
                    return;
                }

                let rows = '';
                data.forEach((item, index) => {
                    // Badge color based on usage type
                    let usageBadgeClass = 'bg-secondary';
                    if (item.usage === 'raw_material') usageBadgeClass = 'bg-info';
                    else if (item.usage === 'finished_goods') usageBadgeClass = 'bg-success';
                    else if (item.usage === 'work_in_process') usageBadgeClass = 'bg-warning text-dark';
                    else if (item.usage === 'packaging') usageBadgeClass = 'bg-primary';
                    else if (item.usage === 'general_storage') usageBadgeClass = 'bg-secondary';

                    rows += `
                        <tr>
                            <td class="px-3 py-2 text-center">${index + 1}</td>
                            <td class="px-3 py-2">
                                <span class="fw-semibold text-success">${item.pallet_id}</span>
                            </td>
                            <td class="px-3 py-2"><span class="badge ${usageBadgeClass}">${item.usage.replaceAll('_', ' ')}</span></td>
                            <td class="px-3 py-2">${item.basic}</td>
                            <td class="px-3 py-2 text-center"><span class="badge bg-secondary">${item.size}</span></td>
                            <td class="px-3 py-2 text-center">${item.color}</td>
                            <td class="px-3 py-2 text-center">
                                <span class="fw-semibold">${item.quantity.toLocaleString()}</span>
                            </td>
                        </tr>
                    `;
                });

                tableBody.innerHTML = rows;
            }

            // Update total row
            function updateTotalRow(data) {
                if (data.length === 0) {
                    totalRow.style.display = 'none';
                    return;
                }

                const totalQty = data.reduce((sum, item) => sum + item.quantity, 0);
                document.getElementById('totalQuantity').textContent = totalQty.toLocaleString();
                totalRow.style.display = '';
            }

            // Print functionality
            document.getElementById('printReport').addEventListener('click', function() {
                if (!currentFilters.company || !currentFilters.date) {
                    alert('{{ __('Please select company and date first') }}');
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
                let filteredData = getFilteredData();

                const formatDate = (dateStr) => {
                    const date = new Date(dateStr);
                    const day = String(date.getDate()).padStart(2, '0');
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const year = date.getFullYear();
                    return `${day}/${month}/${year}`;
                };

                let filterText = `Company: ${currentFilters.company}`;
                if (currentFilters.warehouse) {
                    filterText += ` | Warehouse: ${currentFilters.warehouse}`;
                }

                const printDate = new Date().toLocaleString('en-GB', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });

                const totalQty = filteredData.reduce((sum, item) => sum + item.quantity, 0);

                let tableRows = '';
                filteredData.forEach((item, index) => {
                    tableRows += `
                        <tr>
                            <td style="text-align: center;">${index + 1}</td>
                            <td>${item.pallet_id}</td>
                            <td>${item.usage}</td>
                            <td>${item.basic}</td>
                            <td style="text-align: center;">${item.size}</td>
                            <td style="text-align: center;">${item.color}</td>
                            <td style="text-align: center;">${item.quantity.toLocaleString()}</td>
                        </tr>
                    `;
                });

                printContainer.innerHTML = `
                    <div class="inventory-report-print">
                        <div class="header">
                            <div class="header-content">
                                <img src="/images/logo-bw.png" alt="Company Logo" class="company-logo">
                                <div class="company-name">${currentFilters.company.toUpperCase()}</div>
                            </div>
                        </div>

                        <div class="report-title">ON-HAND INVENTORY REPORT</div>

                        <div class="report-info">
                            <div class="info-label">As Of Date:</div>
                            <div>${formatDate(currentFilters.date)}</div>
                            <div class="info-label">Filters:</div>
                            <div>${filterText}</div>
                            <div class="info-label">Printed:</div>
                            <div>${printDate}</div>
                            <div class="info-label">Total Items:</div>
                            <div>${filteredData.length} pallet(s)</div>
                        </div>

                        <table>
                            <thead>
                                <tr>
                                    <th style="width: 5%;">No</th>
                                    <th style="width: 12%;">ID Pallet</th>
                                    <th style="width: 13%;">Usage</th>
                                    <th style="width: 12%;">Basic</th>
                                    <th style="width: 14%;">Size (mm)</th>
                                    <th style="width: 10%;">Color</th>
                                    <th style="width: 13%;">Quantity (Pcs)</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${tableRows}
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="8" style="text-align: right; font-weight: bold;">TOTAL QUANTITY:</td>
                                    <td style="text-align: center;">${totalQty.toLocaleString()}</td>
                                </tr>
                            </tfoot>
                        </table>

                        <div class="footer-note">
                            This report is electronically generated and valid without wet signature
                        </div>
                    </div>
                `;
            }

            // Export to Excel
            document.getElementById('exportExcel').addEventListener('click', function(e) {
                e.preventDefault();

                if (!currentFilters.company || !currentFilters.date) {
                    alert('{{ __('Please select company and date first') }}');
                    return;
                }

                let exportData = getFilteredData();

                const worksheetData = [
                    ['On-Hand Inventory Report'],
                    ['Company:', currentFilters.company],
                    ['As Of Date:', currentFilters.date],
                    ['Generated:', new Date().toLocaleString()],
                    [],
                    ['No', 'ID Pallet', 'Usage', 'Basic', 'Size (mm)', 'Color',
                        'Quantity (Pcs)'
                    ]
                ];

                exportData.forEach((item, index) => {
                    worksheetData.push([
                        index + 1,
                        item.pallet_id,
                        item.usage,
                        item.basic,
                        item.size,
                        item.color,
                        item.quantity
                    ]);
                });

                const totalQty = exportData.reduce((sum, item) => sum + item.quantity, 0);
                worksheetData.push([]);
                worksheetData.push(['', '', '', '', '', '', 'TOTAL', totalQty]);

                const wb = XLSX.utils.book_new();
                const ws = XLSX.utils.aoa_to_sheet(worksheetData);

                ws['!cols'] = [{
                    wch: 5
                }, {
                    wch: 12
                }, {
                    wch: 15
                }, {
                    wch: 15
                }, {
                    wch: 15
                }, {
                    wch: 8
                }, {
                    wch: 8
                }, {
                    wch: 12
                }, {
                    wch: 12
                }];

                XLSX.utils.book_append_sheet(wb, ws, 'On-Hand Inventory');
                XLSX.writeFile(wb,
                    `OnHand_Inventory_${currentFilters.company}_${new Date().getTime()}.xlsx`);
            });

            // Export to PDF
            document.getElementById('exportPDF').addEventListener('click', function(e) {
                e.preventDefault();

                if (!currentFilters.company || !currentFilters.date) {
                    alert('{{ __('Please select company and date first') }}');
                    return;
                }

                const {
                    jsPDF
                } = window.jspdf;
                const doc = new jsPDF('l', 'mm', 'a4');

                let exportData = getFilteredData();

                doc.setFontSize(16);
                doc.text('On-Hand Inventory Report', 14, 15);

                doc.setFontSize(10);
                doc.text(`Company: ${currentFilters.company}`, 14, 22);
                doc.text(`As Of Date: ${currentFilters.date}`, 14, 28);
                doc.text(`Generated: ${new Date().toLocaleString()}`, 14, 34);

                const tableData = exportData.map((item, index) => [
                    index + 1,
                    item.pallet_id,
                    item.usage,
                    item.basic,
                    item.size,
                    item.color,
                    item.quantity
                ]);

                const totalQty = exportData.reduce((sum, item) => sum + item.quantity, 0);

                doc.autoTable({
                    startY: 40,
                    head: [
                        ['No', 'ID Pallet', 'Usage', 'Basic', 'Size (mm)', 'Color',
                            'Quantity (Pcs)'
                        ]
                    ],
                    body: tableData,
                    foot: [
                        ['', '', '', '', '', '', '', 'TOTAL', totalQty]
                    ],
                    theme: 'grid',
                    headStyles: {
                        fillColor: [13, 110, 253],
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
                    }
                });

                doc.save(`OnHand_Inventory_${currentFilters.company}_${new Date().getTime()}.pdf`);
            });

            // Export to CSV
            document.getElementById('exportCSV').addEventListener('click', function(e) {
                e.preventDefault();

                if (!currentFilters.company || !currentFilters.date) {
                    alert('{{ __('Please select company and date first') }}');
                    return;
                }

                let exportData = getFilteredData();

                let csv = 'No,ID Pallet,Usage,Basic,Size (mm),Color,Quantity (Pcs)\n';

                exportData.forEach((item, index) => {
                    csv +=
                        `${index + 1},"${item.pallet_id}","${item.usage}","${item.basic}","${item.size}","${item.color}",${item.quantity}\n`;
                });

                const totalQty = exportData.reduce((sum, item) => sum + item.quantity, 0);
                csv += `\n,,,,,,,,TOTAL,${totalQty}\n`;

                const blob = new Blob([csv], {
                    type: 'text/csv'
                });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `OnHand_Inventory_${currentFilters.company}_${new Date().getTime()}.csv`;
                a.click();
                window.URL.revokeObjectURL(url);
            });

            // Helper function to get filtered data
            function getFilteredData() {
                return inventoryData.filter(item => {
                    if (item.company !== currentFilters.company) return false;
                    if (item.date !== currentFilters.date) return false;
                    if (currentFilters.warehouse && item.warehouse !== currentFilters.warehouse)
                        return false;

                    if (currentFilters.search) {
                        const searchLower = currentFilters.search.toLowerCase();
                        return (
                            item.pallet_id.toLowerCase().includes(searchLower) ||
                            item.usage.toLowerCase().includes(searchLower) ||
                            item.basic.toLowerCase().includes(searchLower) ||
                            item.size.toLowerCase().includes(searchLower) ||
                            item.color.toLowerCase().includes(searchLower)
                        );
                    }

                    return true;
                });
            }

            async function getData() {
                const bodyTableStock = document.getElementById('reportTableBody');
                // const selectElement = type === 'from' ? fromAddressSelect : toAddressSelect;
                bodyTableStock.innerHTML = '<tr><td>Loading...</td></tr>';
                let ckdcust = @json((Auth::user()->isAdmin() || Auth::user()->isSuperAdmin()) ? null : Auth::user()->ckdcust);
                try {
                    const response = await fetch(`/api/inventory/getData/` + (ckdcust ?? ''));
                    const data = await response.json();
                    let strhtml = "";
                    let idx = 1;
                    data.forEach(inventory => {

                        inventoryData.push({
                            id: idx,
                            pallet_id: inventory.ckdbrg,
                            usage: inventory.usage,
                            basic: inventory.cbasic,
                            size: inventory.npanjang + " x " + inventory.nlebar + " x " + inventory.ntinggi,
                            color: inventory.cwarna,
                            quantity: inventory.nqty,
                            warehouse: inventory.ckdwh,
                            company: inventory.ctempat,
                            date: inventory.dtgl
                        });
                        // inventoryData.push({
                        //     id: idx,
                        //     pallet_id: inventory.ckdbrg,
                        //     usage: "",
                        //     basic: "",
                        //     size: ""+" x "+""+" x "+"",
                        //     md: "",
                        //     mr: "",
                        //     color: "",
                        //     quantity: inventory.nqty,
                        //     warehouse: inventory.ckdwh,
                        //     company: inventory.ctempat,
                        //     date: inventory.dtgl
                        // });
                        idx++;

                    });


                    filterAndRender();

                } catch (error) {

                }
            }

            getData();
            // Initial render
            // filterAndRender();
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            window.startOnHandInventoryTour = function() {
                const driver = window.driver.js.driver;

                const driverObj = driver({
                    showProgress: true,
                    showButtons: ['next', 'previous', 'close'],
                    steps: [{
                            popover: {
                                title: "📦 {{ __('On-Hand Inventory') }}",
                                description: "{{ __('This report shows the quantity of pallets physically available at a specific point in time.') }}"
                            }
                        },
                        {
                            element: '#filterDate',
                            popover: {
                                title: "📅 {{ __('As Of Date') }}",
                                description: "{{ __('Inventory is calculated as of this date.') }}",
                                side: 'bottom',
                                align: 'start'
                            }
                        },
                        {
                            element: '#companyFilterWrap',
                            popover: {
                                title: "🏢 {{ __('Company') }}",
                                description: "{{ __('Select the company whose inventory you want to view.') }}" +
                                    "<br><br>{{ __('This filter is mandatory.') }}",
                                side: 'bottom',
                                align: 'start'
                            }
                        },
                        {
                            element: '#warehouseFilterWrap',
                            popover: {
                                title: "🏭 {{ __('Warehouse') }}",
                                description: "{{ __('Optionally limit inventory to a specific warehouse.') }}",
                                side: 'bottom',
                                align: 'start'
                            }
                        },
                        {
                            element: '#searchInput',
                            popover: {
                                title: "🔍 {{ __('Search Inventory') }}",
                                description: "{{ __('Search by pallet ID, usage type, pallet basic, size, or color.') }}",
                                side: 'bottom',
                                align: 'start'
                            }
                        },
                        {
                            element: '#resetFilters',
                            popover: {
                                title: "♻️ {{ __('Reset Filters') }}",
                                description: "{{ __('Clear all filters and reload the inventory snapshot.') }}",
                                side: 'top',
                                align: 'center'
                            }
                        },
                        {
                            element: '#inventoryTable',
                            popover: {
                                title: "📋 {{ __('Inventory Details') }}",
                                description: "{{ __('Each row represents available pallets based on the selected date and filters.') }}" +
                                    "<br><br>• <strong>{{ __('Usage') }}</strong> {{ __('indicates how the pallet is used') }}" +
                                    "<br>• <strong>{{ __('Basic') }}</strong> {{ __('defines the pallet specification') }}" +
                                    "<br>• <strong>{{ __('Quantity') }}</strong> {{ __('shows how many pallets are available') }}",
                                side: 'top',
                                align: 'start'
                            }
                        },
                        {
                            popover: {
                                title: "🧮 {{ __('Total Quantity') }}",
                                description: "{{ __('The total row sums all visible inventory quantities.') }}" +
                                    "<br><br>{{ __('This value updates automatically when filters change.') }}"
                            }
                        },
                        {
                            element: '#onHandActions',
                            popover: {
                                title: "⬇️ {{ __('Export & Print') }}",
                                description: "{{ __('Export the inventory snapshot or print it for reporting and reconciliation.') }}",
                                side: 'left',
                                align: 'start'
                            }
                        },
                        {
                            popover: {
                                title: "✅ {{ __('Done') }}",
                                description: "{{ __('You now know how to read and export on-hand inventory data.') }}" +
                                    "<br><br><strong>{{ __('Reminder:') }}</strong> " +
                                    "{{ __('Always double-check the “As Of Date”.') }}"
                            }
                        }
                    ]
                });

                driverObj.drive();
                return driverObj;
            };

            window.startProductTour = window.startOnHandInventoryTour;

        });
    </script>
@endpush
