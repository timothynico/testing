@extends('layouts.app')

@section('title', __('Generate Email - Order or Return Pallet'))

@section('header-left')
    <div>
        <h2 class="h5 mb-0 text-dark fw-semibold">{{ __('Generate Request Email - Order or Return Pallet') }}</h2>
        <p class="text-muted small mb-0 mt-1">{{ __('Create email request to PT Yanasurya Bhaktipersada') }}</p>
    </div>
@endsection

@section('content')
    <!-- Request Type Selection -->
    <div class="card">
        <div class="card-body">
            <div class="btn-group w-100" role="group">
                <input type="radio" class="btn-check" name="requestType" id="requestTypeOrder" value="order" checked>
                <label class="btn btn-outline-success" for="requestTypeOrder">
                    <i class="bi bi-cart-plus me-2"></i>{{ __('Order Pallets') }}
                </label>

                <input type="radio" class="btn-check" name="requestType" id="requestTypePull" value="return">
                <label class="btn btn-outline-success" for="requestTypePull">
                    <i class="bi bi-box-arrow-in-down me-2"></i>{{ __('Return Pallets') }}
                </label>
            </div>
        </div>
    </div>

    <!-- Order/Pull Details Form -->
    <div class="card mb-3">
        <div class="card-header bg-success text-white">
            <i class="bi bi-file-text me-2"></i><span id="formHeaderText">{{ __('Order Details') }}</span>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <!-- Left side: Form inputs (3/4) -->
                <div class="col-6 col-md-9">
                    <div class="row g-3">
                        <!-- Required/ETA Return Date -->
                        <div class="col-6 col-md-4">
                            <label for="requiredDate" class="form-label fw-semibold">
                                <span id="dateLabel">{{ __('Required Date') }}</span> <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control" id="requiredDate" required>
                        </div>

                        <!-- Usage Selection (main driver) -->
                        <div class="col-6 col-md-4">
                            <label for="usageSelect" class="form-label fw-semibold">
                                {{ __('Usage') }} <span class="text-danger">*</span>
                            </label>
                            <select class="form-select form-select-sm" id="usageSelect" required>
                                <option value="">{{ __('Select usage') }}...</option>
                                <!-- Mock data based on agreement contract items -->
                                <option value="finished_goods" data-pallet="PT1210AS" data-size="1200×1000×160"
                                    data-color="Blue" data-grade="HL">{{ __('Finished Goods') }}</option>
                                <option value="raw_material" data-pallet="PT1212" data-size="1200×1200×150" data-color="Red"
                                    data-grade="HL">{{ __('Raw Material') }}</option>
                                <option value="packaging" data-pallet="PT0806" data-size="800×600×140" data-color="Green"
                                    data-grade="HL">{{ __('Packaging') }}</option>
                            </select>
                        </div>

                        <!-- Quantity (fillable) -->
                        <div class="col-6 col-md-4">
                            <label for="quantity" class="form-label fw-semibold">
                                {{ __('Quantity (Pcs)') }} <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control form-control-sm" id="quantity" min="1"
                                placeholder="{{ __('Enter quantity...') }}" required>
                        </div>

                        <!-- Pallet Type (readonly, auto-filled based on usage) -->
                        <div class="col-6 col-md-4">
                            <label for="palletType" class="form-label fw-semibold">
                                {{ __('Type') }} <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control form-control-sm" id="palletTypeDisplay" readonly
                                placeholder="Auto-filled from usage">
                            <input type="hidden" id="palletType" name="pallet_type">
                        </div>

                        <!-- Pallet Type for Pull Requests (manual input when no usage) -->
                        <div class="col-6 col-md-3 d-none" id="pullPalletTypeField">
                            <label for="pullPalletType" class="form-label fw-semibold">
                                {{ __('Pallet Type') }} <span class="text-danger">*</span>
                            </label>
                            <select class="form-select form-select-sm" id="pullPalletType">
                                <option value="">{{ __('Select type') }}...</option>
                                <option value="PT1210AS">PT1210AS (1200×1000×160)</option>
                                <option value="PT1212">PT1212 (1200×1200×150)</option>
                                <option value="PT0806">PT0806 (800×600×140)</option>
                            </select>
                        </div>

                        <!-- From Warehouse Selection -->
                        <div class="col-6 col-md-4">
                            <label for="warehouseFromSelect" class="form-label fw-semibold">
                                <span id="warehouseFromLabel">{{ __('Delivery From Warehouse') }}</span> <span
                                    class="text-danger">*</span>
                            </label>
                            <select class="form-select form-select-sm" id="warehouseFromSelect" required>
                                <option value="">{{ __('Select warehouse') }}...</option>

                                {{-- Customer warehouse option --}}
                                @foreach ($custwh as $wh)
                                    <option value="{{ $wh->nidwh }}"
                                        data-customer="1"
                                        data-address="{{ $wh->calmtwh }}"
                                        data-nlat="{{$wh->nlat}}"
                                        data-nlong="{{$wh->nlong}}"
                                        data-nid="{{ $wh->nidwh }}"
                                        data-ckd="{{ $wh->ckdwh }}"
                                        data-city="{{ $wh->ckotawh }}">
                                        {{ $wh->cnmwh }}
                                    </option>
                                @endforeach

                                {{-- Yanasurya warehouses --}}
                                @if (isset($yswh) && is_iterable($yswh))
                                    @foreach ($yswh as $w)
                                        <option value="{{ $w->nidcompwh ?? $loop->index }}"
                                            data-nlong="{{$w->nlong}}"  data-nlat="{{$w->nlat}}"
                                            data-address="{{ $w->calmtwh }}" data-nid="{{ $w->nidcompwh }}"
                                            data-ckd="{{ $w->ckdwh }}" data-city="{{$w->ckotawh}}" > 
                                            {{ $w->cnmwh }}
                                        </option>
                                    @endforeach
                                @endif

                            </select>
                            <small class="text-muted mt-1 d-block" id="warehouseFromAddressDisplay"></small>
                        </div>

                        <!-- To Warehouse Selection -->
                        <div class="col-6 col-md-4">
                            <label for="warehouseToSelect" class="form-label fw-semibold">
                                <span id="warehouseToLabel">{{ __('Delivery To Warehouse') }}</span> <span
                                    class="text-danger">*</span>
                            </label>
                            <select class="form-select form-select-sm" id="warehouseToSelect" required>
                                <option value="">{{ __('Select warehouse') }}...</option>

                                {{-- Customer warehouse option --}}
                                @foreach ($custwh as $wh)
                                    <option value="{{ $wh->nidwh }}"
                                        data-customer="1"
                                        data-nlat="{{$wh->nlat}}"
                                        data-long="{{$wh->nlong}}"
                                        data-address="{{ $wh->calmtwh }}"
                                        data-nid="{{ $wh->nidwh }}"
                                        data-ckd="{{ $wh->ckdwh }}"
                                        data-city="{{ $wh->ckotawh }}">
                                        {{ $wh->cnmwh }}
                                    </option>
                                @endforeach

                                {{-- Yanasurya warehouses --}}
                                @if (isset($yswh) && is_iterable($yswh))
                                    @foreach ($yswh as $w)
                                        <option value="{{ $w->nidcompwh ?? $loop->index }}"
                                            data-nlat="{{$w->nlat}}" data-nlong="{{$w->nlong}}"
                                            data-address="{{ $w->calmtwh }}" data-nid="{{ $w->nidcompwh }}"
                                            data-ckd="{{ $w->ckdwh }}" data-city="{{$w->ckotawh}}">
                                            {{ $w->cnmwh }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            <small class="text-muted mt-1 d-block" id="warehouseToAddressDisplay"></small>
                        </div>

                        <!-- Return Condition (only for pulls) -->
                        <div class="col-6 col-md-4 d-none" id="conditionField">
                            <label for="palletCondition" class="form-label fw-semibold">
                                {{ __('Pallet Condition') }}
                            </label>
                            <select class="form-select mb-2" id="palletCondition">
                                <option value="">- {{ __('Select Condition') }} -</option>
                                <option value="Good">{{ __('Good') }} - {{ __('Ready for reuse') }}</option>
                                <option value="Fair">{{ __('Fair') }} - {{ __('Minor wear') }}</option>
                                <option value="Poor">{{ __('Poor') }} - {{ __('Needs repair') }}</option>
                                <option value="Damaged">{{ __('Damaged') }} - {{ __('Not reusable') }}</option>
                            </select>
                            <textarea class="form-control" id="conditionNotes" rows="2"
                                placeholder="{{ __('Additional condition notes') }}..."></textarea>
                        </div>

                        <!-- Additional Notes -->
                        <div class="col-6 col-md-4">
                            <label for="additionalNotes" class="form-label fw-semibold">
                                {{ __('Additional Notes') }}
                            </label>
                            <textarea class="form-control" id="additionalNotes" rows="3"
                                placeholder="{{ __('Enter additional notes') }}..."></textarea>
                        </div>

                        <!-- Load Capacity Section (only for orders) -->
                        {{-- ... (unchanged; commented-out code omitted for brevity) --}}
                    </div>
                </div>

                <!-- Right side: Pallet Image (1/4) -->
                <div class="col-6 col-md-3">
                    <div class="text-center h-100 d-flex flex-column justify-content-center">
                        <label class="form-label fw-semibold">{{ __('Pallet Visualization') }}</label>
                        <div class="pallet-image-container bg-light rounded border p-3">
                            <div id="palletImagePlaceholder" class="d-flex align-items-center justify-content-center"
                                style="min-height: 200px;">
                                <div class="text-muted">
                                    <i class="bi bi-box-seam display-4 d-block mb-2"></i>
                                    <small>{{ __('Select pallet type to view') }}</small>
                                </div>
                            </div>
                            <img id="palletImage" src="" alt="Pallet Type" class="img-fluid d-none"
                                style="max-height: 300px;">
                        </div>
                        <small class="text-muted mt-2" id="palletTypeName"></small>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    {{ __('All fields marked with') }} <span class="text-danger">*</span> {{ __('are required') }}
                </small>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="clearFormBtn">
                    <i class="bi bi-x-circle me-1"></i>{{ __('Clear Form') }}
                </button>
            </div>
        </div>
    </div>

    <!-- Generated Email Content -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center bg-success text-white">
            <span>
                <i class="bi bi-envelope me-2"></i>{{ __('Generated Email Content') }}
            </span>
            <button type="button" class="btn btn-sm btn-brand border" id="copyAllBtn">
                <i class="bi bi-clipboard me-1"></i>{{ __('Copy All to Clipboard') }}
            </button>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <!-- Recipient -->
                <div class="col-12">
                    <label for="emailRecipient" class="form-label fw-semibold">
                        {{ __('To: (Recipient)') }}
                        <button type="button" class="btn btn-link btn-sm p-0 ms-2"
                            onclick="copyToClipboard('emailRecipient')">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </label>
                    <input type="text" class="form-control" id="emailRecipient" readonly
                        value="rent.invoice@yanasurya.com">
                    <small
                        class="text-muted">{{ __('Default recipient: PT Yanasurya Bhaktipersada Sales Department') }}</small>
                </div>

                <!-- Subject -->
                <div class="col-12">
                    <label for="emailSubject" class="form-label fw-semibold">
                        {{ __('Subject:') }}
                        <button type="button" class="btn btn-link btn-sm p-0 ms-2"
                            onclick="copyToClipboard('emailSubject')">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </label>
                    <input type="text" class="form-control" id="emailSubject" readonly
                        placeholder="{{ __('Fill in details to generate subject') }}">
                </div>

                <!-- Body -->
                <div class="col-12">
                    <label for="emailBody" class="form-label fw-semibold">
                        {{ __('Email Body:') }}
                        <button type="button" class="btn btn-link btn-sm p-0 ms-2"
                            onclick="copyToClipboard('emailBody')">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </label>
                    <textarea class="form-control font-monospace" id="emailBody" rows="16" readonly
                        placeholder="{{ __('Fill in details to generate email body') }}"></textarea>
                    <small
                        class="text-muted">{{ __('This email body is formatted and ready to paste into your email client.') }}</small>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <div class="d-flex gap-2 justify-content-end">
                <button type="button" class="btn btn-outline-secondary" id="resetAllBtn">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>{{ __('Reset All') }}
                </button>

                <button type="button" class="btn btn-success" id="openMailBtn">
                    <i class="bi bi-envelope-paper me-1"></i>{{ __('Open Email App') }}
                </button>
            </div>
        </div>
    </div>

    <!-- Hidden data container -->
    <script>
        // Current logged-in user data - replace with actual data from backend
        const currentUser = {
            name: '{{ Auth::user()->name }}',
            email: '{{ Auth::user()->email }}',
            company: '{{ $request->cnmcust }}'
        };
        const CURRENT_ROLE = @json($role ?? null);

        // Optional: Provide JS copies of server data (defensive)
        const SERVER_CUSTWH = @json(isset($custwh) ? $custwh : null);
        const SERVER_YSWH = @json(isset($yswh) ? $yswh : []);
    </script>
@endsection

@push('styles')
    <style>
        .form-label {
            font-size: 0.875rem;
            margin-bottom: 0.375rem;
        }

        .form-select,
        .form-control {
            font-size: 0.875rem;
        }

        textarea.form-control {
            resize: vertical;
        }

        .font-monospace {
            font-size: 0.8125rem;
            line-height: 1.6;
        }

        .card-body .row {
            align-items: start;
        }

        .card-body textarea.form-control {
            min-height: 75px;
        }

        .pallet-image-container {
            transition: all 0.3s ease;
        }

        .pallet-image-container img {
            object-fit: contain;
            transition: opacity 0.3s ease;
        }

        #palletImagePlaceholder {
            transition: opacity 0.3s ease;
        }

        .card.bg-light {
            background-color: #f8f9fa !important;
        }

        .form-control-sm {
            font-size: 0.8125rem;
        }

        .btn-check:checked+.btn-outline-success {
            background-color: #198754;
            border-color: #198754;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // DOM elements
            const requestTypeOrder = document.getElementById('requestTypeOrder');
            const requestTypePull = document.getElementById('requestTypePull');
            const formHeaderText = document.getElementById('formHeaderText');
            const dateLabel = document.getElementById('dateLabel');
            const warehouseFromLabel = document.getElementById('warehouseFromLabel');
            const warehouseToLabel = document.getElementById('warehouseToLabel');

            const usageSelect = document.getElementById('usageSelect');
            const palletType = document.getElementById('palletType');
            const palletTypeDisplay = document.getElementById('palletTypeDisplay');
            const pullPalletTypeSelect = document.getElementById('pullPalletType');
            const quantity = document.getElementById('quantity');
            const requiredDate = document.getElementById('requiredDate');

            // NEW: warehouse from/to
            const warehouseFromSelect = document.getElementById('warehouseFromSelect');
            const warehouseFromAddressDisplay = document.getElementById('warehouseFromAddressDisplay');
            const warehouseToSelect = document.getElementById('warehouseToSelect');
            const warehouseToAddressDisplay = document.getElementById('warehouseToAddressDisplay');

            const additionalNotes = document.getElementById('additionalNotes');
            const palletCondition = document.getElementById('palletCondition');
            const conditionNotes = document.getElementById('conditionNotes');

            // Load capacity fields
            const palletImage = document.getElementById('palletImage');
            const palletImagePlaceholder = document.getElementById('palletImagePlaceholder');
            const palletTypeName = document.getElementById('palletTypeName');

            const emailRecipient = document.getElementById('emailRecipient');
            const emailSubject = document.getElementById('emailSubject');
            const emailBody = document.getElementById('emailBody');

            const clearFormBtn = document.getElementById('clearFormBtn');
            const resetAllBtn = document.getElementById('resetAllBtn');
            const copyAllBtn = document.getElementById('copyAllBtn');

            const conditionField = document.getElementById('conditionField');

            // Set minimum date to today
            const today = new Date().toISOString().split('T')[0];
            requiredDate.setAttribute('min', today);

            // ----- PREP: normalize warehouse source data from backend -----
            const normalizeAsArray = source => Array.isArray(source) ? source : (source ? Object.values(source) : []);
            const customerWarehouseList = normalizeAsArray(SERVER_CUSTWH);
            const yanasuryaWarehouseList = normalizeAsArray(SERVER_YSWH);

            const fromPlaceholderTemplate = warehouseFromSelect.options[0] ? warehouseFromSelect.options[0].cloneNode(true) : null;
            const toPlaceholderTemplate = warehouseToSelect.options[0] ? warehouseToSelect.options[0].cloneNode(true) : null;

            function initStyledDistanceDropdown(selectEl) {
                const wrapper = document.createElement('div');
                wrapper.className = 'dropdown w-100';

                const trigger = document.createElement('button');
                trigger.type = 'button';
                trigger.className = 'form-select form-select-sm text-start d-flex justify-content-between align-items-center';
                trigger.setAttribute('data-bs-toggle', 'dropdown');
                trigger.setAttribute('aria-expanded', 'false');
                trigger.innerHTML = '<span></span>';

                const menu = document.createElement('ul');
                menu.className = 'dropdown-menu w-100';
                menu.style.maxHeight = '260px';
                menu.style.overflowY = 'auto';

                wrapper.appendChild(trigger);
                wrapper.appendChild(menu);
                selectEl.insertAdjacentElement('afterend', wrapper);
                selectEl.classList.add('d-none');

                const renderLabel = option => {
                    const baseLabel = option.dataset.baseName || option.textContent || '';
                    const distanceLabel = option.dataset.distanceLabel || '';

                    if (!distanceLabel) {
                        return `<span class="d-flex justify-content-between align-items-center w-100"><span>${baseLabel}</span></span>`;
                    }

                    return `<span class="d-flex justify-content-between align-items-center w-100"><span>${baseLabel}</span><span style="color:#dc3545; margin-left:12px; white-space:nowrap;">${distanceLabel}</span></span>`;
                };

                const sync = () => {
                    const selectedOption = selectEl.options[selectEl.selectedIndex] || selectEl.options[0];
                    const selectedText = selectedOption ? renderLabel(selectedOption) : '';
                    trigger.innerHTML = `<span>${selectedText}</span>`;
                    trigger.disabled = selectEl.disabled;

                    menu.innerHTML = '';
                    Array.from(selectEl.options).forEach((option, index) => {
                        const item = document.createElement('li');
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'dropdown-item';
                        btn.innerHTML = renderLabel(option);

                        if (option.disabled) {
                            btn.disabled = true;
                        }

                        btn.addEventListener('click', () => {
                            selectEl.selectedIndex = index;
                            selectEl.dispatchEvent(new Event('change', { bubbles: true }));
                        });

                        item.appendChild(btn);
                        menu.appendChild(item);
                    });
                };

                const observer = new MutationObserver(sync);
                observer.observe(selectEl, { childList: true, subtree: true, characterData: true, attributes: true });
                selectEl.addEventListener('change', sync);
                selectEl.addEventListener('input', sync);
                sync();

                return { sync };
            }

            const warehouseFromStyledDropdown = initStyledDistanceDropdown(warehouseFromSelect);
            const warehouseToStyledDropdown = initStyledDistanceDropdown(warehouseToSelect);

            function buildWarehouseOption(warehouse, optionType) {
                const option = document.createElement('option');
                const isCustomer = optionType === 'customer';

                option.value = isCustomer ? String(warehouse.nidwh ?? '') : String(warehouse.nidcompwh ?? '');
                option.dataset.baseName = warehouse.cnmwh || '';
                option.textContent = warehouse.cnmwh || '';
                option.dataset.address = warehouse.calmtwh || '';
                option.dataset.nid = isCustomer ? String(warehouse.nidwh ?? '') : String(warehouse.nidcompwh ?? '');
                option.dataset.ckd = warehouse.ckdwh || '';
                option.dataset.city = warehouse.ckotawh || '';
                option.dataset.nlat = warehouse.nlat;
                option.dataset.nlong = warehouse.nlong;

                if (isCustomer) {
                    option.dataset.customer = '1';
                }

                return option;
            }

            function setWarehouseOptions(selectEl, optionType) {
                const currentValue = selectEl.value;
                const placeholderTemplate = selectEl === warehouseFromSelect ? fromPlaceholderTemplate : toPlaceholderTemplate;
                const sourceList = optionType === 'customer' ? customerWarehouseList : yanasuryaWarehouseList;

                selectEl.innerHTML = '';

                if (placeholderTemplate) {
                    selectEl.appendChild(placeholderTemplate.cloneNode(true));
                }

                sourceList.forEach(warehouse => {
                    selectEl.appendChild(buildWarehouseOption(warehouse, optionType));
                });

                const hasCurrentValue = Array.from(selectEl.options).some(option => option.value === currentValue);
                selectEl.value = hasCurrentValue ? currentValue : '';
            }

            // Add date constraints based on request type
            function updateDateConstraints() {
                const isOrder = requestTypeOrder.checked;
                const minDate = new Date();

                if (isOrder) {
                    // Orders can be requested for today
                } else {
                    // D+2 for returns
                    minDate.setDate(minDate.getDate() + 2);
                }

                requiredDate.setAttribute('min', minDate.toISOString().split('T')[0]);
                requiredDate.value = ''; // Clear selected date when switching
            }

            // Configure warehouses: lock/select customer warehouse depending on type
            function configureWarehouses() {
                const isOrder = requestTypeOrder.checked;
                const isWarehousePic = ['warehouse_pic', 'pic_warehouse'].includes(CURRENT_ROLE);

                if (isOrder) {
                    // Order: From = Yanasurya (yswh), To = Customer (custwh)
                    setWarehouseOptions(warehouseFromSelect, 'yanasurya');
                    setWarehouseOptions(warehouseToSelect, 'customer');

                    if (!warehouseToSelect.value && warehouseToSelect.options.length > 1) {
                        warehouseToSelect.value = warehouseToSelect.options[1].value;
                    }

                    // For warehouse PIC, source Yanasurya warehouse is fixed
                    warehouseFromSelect.disabled = false;
                    warehouseFromSelect.required = true;

                    warehouseToSelect.disabled = isWarehousePic;
                    warehouseToSelect.required = true;
                } else {
                    // Return: From = Customer (custwh), To = Yanasurya (yswh)
                    setWarehouseOptions(warehouseFromSelect, 'customer');
                    setWarehouseOptions(warehouseToSelect, 'yanasurya');

                    if (!warehouseFromSelect.value && warehouseFromSelect.options.length > 1) {
                        warehouseFromSelect.value = warehouseFromSelect.options[1].value;
                    }

                    // For warehouse PIC, return source customer warehouse is fixed
                    warehouseFromSelect.disabled = isWarehousePic;
                    warehouseFromSelect.required = false;

                    warehouseToSelect.disabled = false;
                    warehouseToSelect.required = true;
                }

                // Update address displays immediately
                updateWarehouseAddressDisplay(warehouseFromSelect, warehouseFromAddressDisplay);
                updateWarehouseAddressDisplay(warehouseToSelect, warehouseToAddressDisplay);
                updateWarehouseDistanceLabels(true);
            }

            function updateWarehouseAddressDisplay(selectEl, addressDisplayEl) {
                const opt = selectEl.options[selectEl.selectedIndex];
                const address = opt ? (opt.dataset.address || opt.text) : '';
                addressDisplayEl.textContent = address ? `📍 ${address}` : '';
            }

            function calculateDistanceKm(lat1, lon1, lat2, lon2) {
                const toRadians = degree => (degree * Math.PI) / 180;
                const earthRadiusKm = 6371;

                const deltaLat = toRadians(lat2 - lat1);
                const deltaLon = toRadians(lon2 - lon1);

                const a =
                    Math.sin(deltaLat / 2) * Math.sin(deltaLat / 2) +
                    Math.cos(toRadians(lat1)) * Math.cos(toRadians(lat2)) *
                    Math.sin(deltaLon / 2) * Math.sin(deltaLon / 2);
                const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

                return (earthRadiusKm * c) * 1.1;
            }

            function resetDistanceLabels(selectEl) {
                Array.from(selectEl.options).forEach((option, index) => {
                    if (index === 0) return;

                    const baseName = option.dataset.baseName || option.textContent.split(' (')[0].trim();
                    option.dataset.baseName = baseName;
                    option.dataset.distanceLabel = '';
                    option.dataset.distanceValue = '';
                    option.textContent = baseName;
                });
            }

            function sortSelectOptionsByDistance(selectEl, autoSelectSmallest = false) {
                const options = Array.from(selectEl.options);
                const previousValue = selectEl.value;

                if (options.length <= 2) return;

                const placeholder = options[0];
                const sortableOptions = options.slice(1);

                sortableOptions.sort((optionA, optionB) => {
                    const distanceA = Number(optionA.dataset.distanceValue);
                    const distanceB = Number(optionB.dataset.distanceValue);
                    const safeDistanceA = Number.isFinite(distanceA) ? distanceA : Number.POSITIVE_INFINITY;
                    const safeDistanceB = Number.isFinite(distanceB) ? distanceB : Number.POSITIVE_INFINITY;

                    if (safeDistanceA !== safeDistanceB) {
                        return safeDistanceA - safeDistanceB;
                    }

                    return (optionA.dataset.baseName || '').localeCompare(optionB.dataset.baseName || '');
                });

                selectEl.innerHTML = '';
                selectEl.appendChild(placeholder);
                sortableOptions.forEach(option => selectEl.appendChild(option));

                const hasPreviousValue = previousValue && sortableOptions.some(option => option.value === previousValue);

                if (hasPreviousValue) {
                    selectEl.value = previousValue;
                } else if (autoSelectSmallest && sortableOptions.length > 0) {
                    selectEl.value = sortableOptions[0].value;
                }
            }

            function setDistanceLabels(targetSelect, sourceSelect, autoSelectSmallest = false) {
                const sourceOption = sourceSelect.options[sourceSelect.selectedIndex];
                const sourceLat = Number(sourceOption?.dataset.nlat);
                const sourceLon = Number(sourceOption?.dataset.nlong ?? sourceOption?.dataset.long);

                Array.from(targetSelect.options).forEach((targetOption, index) => {
                    if (index === 0) return;

                    const baseName = targetOption.dataset.baseName || targetOption.textContent.split(' (')[0].trim();
                    targetOption.dataset.baseName = baseName;

                    const targetLat = Number(targetOption.dataset.nlat);
                    const targetLon = Number(targetOption.dataset.nlong ?? targetOption.dataset.long);

                    if (!Number.isFinite(sourceLat) || !Number.isFinite(sourceLon) || !Number.isFinite(targetLat) || !Number.isFinite(targetLon)) {
                        targetOption.dataset.distanceLabel = '';
                        targetOption.dataset.distanceValue = '';
                        targetOption.textContent = baseName;
                        return;
                    }

                    const distanceKm = calculateDistanceKm(sourceLat, sourceLon, targetLat, targetLon);
                    const roundedDistance = Math.round(distanceKm * 10) / 10;
                    const distanceLabel = roundedDistance === 0 ? '(Same city)' : `(${roundedDistance} Km)`;

                    targetOption.dataset.distanceLabel = distanceLabel;
                    targetOption.dataset.distanceValue = String(roundedDistance);
                    targetOption.textContent = baseName;
                });

                sortSelectOptionsByDistance(targetSelect, autoSelectSmallest);
            }

            function updateWarehouseDistanceLabels(autoSelectSmallest = false) {
                const isOrder = requestTypeOrder.checked;

                if (isOrder) {
                    setDistanceLabels(warehouseFromSelect, warehouseToSelect, autoSelectSmallest);
                    resetDistanceLabels(warehouseToSelect);
                } else {
                    setDistanceLabels(warehouseToSelect, warehouseFromSelect, autoSelectSmallest);
                    resetDistanceLabels(warehouseFromSelect);
                }

                warehouseFromStyledDropdown.sync();
                warehouseToStyledDropdown.sync();
            }

            // Request type change handler
            function handleRequestTypeChange() {
                window.i18n = {
                    orderDetails: @json(__('Order Details')),
                    pullRequestDetails: @json(__('Return Details')),
                    requiredDate: @json(__('Required Date')),
                    pickupDate: @json(__('ETA Return Date')),
                    deliveryFromWarehouse: @json(__('Delivery From Warehouse (Yanasurya)')),
                    deliveryToWarehouse: @json(__('Delivery To Warehouse (Customer)')),
                    pickupFromWarehouse: @json(__('Return From Warehouse (Customer)')),
                    pickupToWarehouse: @json(__('Return To Warehouse (Yanasurya)')),
                };

                const isOrder = requestTypeOrder.checked;
                const pullPalletTypeField = document.getElementById('pullPalletTypeField');

                // Update labels
                formHeaderText.textContent = isOrder ? window.i18n.orderDetails : window.i18n.pullRequestDetails;
                dateLabel.textContent = isOrder ? window.i18n.requiredDate : window.i18n.pickupDate;
                warehouseFromLabel.textContent = isOrder ? window.i18n.deliveryFromWarehouse : window.i18n
                    .pickupFromWarehouse;
                warehouseToLabel.textContent = isOrder ? window.i18n.deliveryToWarehouse : window.i18n
                    .pickupToWarehouse;

                // Show/hide fields based on request type
                if (isOrder) {
                    usageSelect.parentElement.classList.remove('d-none');
                    palletTypeDisplay.parentElement.classList.remove('d-none');

                    conditionField.classList.add('d-none');
                    pullPalletTypeField.classList.add('d-none');

                    palletCondition.value = '';
                    conditionNotes.value = '';
                    pullPalletTypeSelect.value = '';

                    usageSelect.setAttribute('required', 'required');
                    pullPalletTypeSelect.removeAttribute('required');
                } else {
                    usageSelect.parentElement.classList.remove('d-none');
                    palletTypeDisplay.parentElement.classList.remove('d-none');

                    conditionField.classList.add('d-none');
                    pullPalletTypeField.classList.add('d-none');

                    palletCondition.value = '';
                    conditionNotes.value = '';
                    pullPalletTypeSelect.value = '';

                    usageSelect.setAttribute('required', 'required');
                    pullPalletTypeSelect.removeAttribute('required');
                }

                updateDateConstraints();
                configureWarehouses();
                // generateEmail is async; call it (no await needed here)
                generateEmail();
            }

            requestTypeOrder.addEventListener('change', handleRequestTypeChange);
            requestTypePull.addEventListener('change', handleRequestTypeChange);

            // Usage change handler - auto-fill pallet details
            usageSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];

                if (this.value) {
                    const palletTypeValue = selectedOption.dataset.pallet || '';
                    const palletSize = selectedOption.dataset.size || '';
                    const palletColor = selectedOption.dataset.color || '';
                    const palletGrade = selectedOption.dataset.grade || '';

                    palletTypeDisplay.value = `${palletTypeValue} (${palletSize}) - ${palletColor}`;
                    palletType.value = palletTypeValue;

                    palletTypeDisplay.dataset.color = palletColor;
                    palletTypeDisplay.dataset.grade = palletGrade;
                    palletTypeDisplay.dataset.size = palletSize;

                    updatePalletImage(palletTypeValue);

                    palletTypeName.textContent = `${palletTypeValue} - ${palletColor} - ${palletGrade}`;
                } else {
                    palletTypeDisplay.value = '';
                    palletType.value = '';
                    palletTypeName.textContent = '';
                    palletImage.classList.add('d-none');
                    palletImagePlaceholder.classList.remove('d-none');
                }

                generateEmail();
            });

            // Pull pallet type change handler
            pullPalletTypeSelect.addEventListener('change', function() {
                if (this.value) {
                    palletType.value = this.value;
                    updatePalletImage(this.value);
                    palletTypeName.textContent = this.value;
                } else {
                    palletType.value = '';
                    palletImage.classList.add('d-none');
                    palletImagePlaceholder.classList.remove('d-none');
                    palletTypeName.textContent = '';
                }
                generateEmail();
            });

            function updatePalletImage(palletTypeValue) {
                const imageMap = {
                    'PT1210AS': 'static.png',
                    'PT1212': 'dynamic.png',
                    'PT0806': 'static.png'
                };

                const imageName = imageMap[palletTypeValue];
                if (imageName) {
                    palletImage.src = `/images/pallets/${imageName}`;
                    palletImage.classList.remove('d-none');
                    palletImagePlaceholder.classList.add('d-none');
                }
            }

            // Warehouse change handlers - display addresses (both From & To)
            warehouseFromSelect.addEventListener('change', function() {
                updateWarehouseAddressDisplay(this, warehouseFromAddressDisplay);
                updateWarehouseDistanceLabels(false);
                generateEmail();
            });

            warehouseToSelect.addEventListener('change', function() {
                updateWarehouseAddressDisplay(this, warehouseToAddressDisplay);
                updateWarehouseDistanceLabels(false);
                generateEmail();
            });

            // Input change listeners
            const formInputs = [
                usageSelect, quantity, requiredDate, warehouseFromSelect, warehouseToSelect,
                additionalNotes, palletCondition, conditionNotes, pullPalletTypeSelect
            ];
            formInputs.forEach(input => {
                input.addEventListener('input', generateEmail);
                input.addEventListener('change', generateEmail);
            });

            // --- token cache and helpers ---
            const tokenCache = {}; // key => token

            function getOptionMeta(selectEl) {
                const opt = selectEl.options[selectEl.selectedIndex];
                if (!opt) return null;
                return {
                    nid: opt.dataset.nid || null,
                    ckd: opt.dataset.ckd || null,
                    text: opt.textContent || opt.innerText || ''
                };
            }

            async function fetchTokensIfNeeded(fromMeta, toMeta) {
                // build keys
                const fromKey = fromMeta && (fromMeta.nid || fromMeta.ckd) ?
                    `f:${fromMeta.nid || ''}:${fromMeta.ckd || ''}` : null;
                const toKey = toMeta && (toMeta.nid || toMeta.ckd) ?
                    `t:${toMeta.nid || ''}:${toMeta.ckd || ''}` : null;

                let tokenFrom = fromKey ? tokenCache[fromKey] : null;
                let tokenTo = toKey ? tokenCache[toKey] : null;

                // ask server only if not cached and meta available
                if ((!tokenFrom && fromKey) || (!tokenTo && toKey)) {
                    // POST payload: include entries only if available
                    const payload = {
                        from: {},
                        to: {}
                    };
                    if (fromMeta) {
                        payload.from.nid = fromMeta.nid || null;
                        payload.from.ckd = fromMeta.ckd || null;
                    }
                    if (toMeta) {
                        payload.to.nid = toMeta.nid || null;
                        payload.to.ckd = toMeta.ckd || null;
                    }

                    try {
                        const resp = await fetch("{{ route('delivery.generateWarehouseToken') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify(payload),
                            credentials: 'same-origin'
                        });
                        if (!resp.ok) {
                            console.warn('Failed to fetch warehouse token', await resp.text());
                        } else {
                            const j = await resp.json();
                            if (fromKey && j.token_from) {
                                tokenCache[fromKey] = j.token_from;
                                tokenFrom = j.token_from;
                            }
                            if (toKey && j.token_to) {
                                tokenCache[toKey] = j.token_to;
                                tokenTo = j.token_to;
                            }
                        }
                    } catch (err) {
                        console.error('Error fetching tokens', err);
                    }
                }

                return {
                    tokenFrom,
                    tokenTo,
                    fromKey,
                    toKey
                };
            }

            // Generate email content (async so we can await token fetch)
            async function generateEmail() {
                const isOrder = requestTypeOrder.checked;

                // require both From & To warehouses (for validity we still expect selects to have value)
                let isValid = usageSelect.value && quantity.value && requiredDate.value && warehouseFromSelect
                    .value && warehouseToSelect.value;

                if (!isValid) {
                    emailSubject.value = '';
                    emailBody.value = '';
                    return;
                }

                // Get warehouse addresses/options
                const warehouseFromOption = warehouseFromSelect.options[warehouseFromSelect.selectedIndex];
                const warehouseFromAddress = warehouseFromOption ? (warehouseFromOption.dataset.address ||
                    warehouseFromOption.text) : '';

                const warehouseToOption = warehouseToSelect.options[warehouseToSelect.selectedIndex];
                const warehouseToAddress = warehouseToOption ? (warehouseToOption.dataset.address ||
                    warehouseToOption.text) : '';

                // Format date
                const dateObj = new Date(requiredDate.value);
                const formattedDate = dateObj.toLocaleDateString('en-GB', {
                    day: '2-digit',
                    month: 'long',
                    year: 'numeric'
                });

                const currentDate = new Date().toLocaleDateString('en-GB', {
                    day: '2-digit',
                    month: 'long',
                    year: 'numeric'
                });

                // Get pallet details
                const palletTypeValue = palletType.value;
                const palletColor = palletTypeDisplay.dataset.color || '';
                const palletGrade = palletTypeDisplay.dataset.grade || '';
                const palletSize = palletTypeDisplay.dataset.size || '';

                // Generate subject
                if (isOrder) {
                    emailSubject.value =
                        `Pallet Order Request - ${currentUser.company} - ${palletTypeValue} - ${currentDate}`;
                } else {
                    emailSubject.value =
                        `Pallet Return Request - ${currentUser.company} - ${palletTypeValue} - ${currentDate}`;
                }

                // Generate body
                let body = `Dear PT Yanasurya Bhaktipersada,\n\n`;

                if (isOrder) {
                    body += `ORDER DETAILS\n`;
                } else {
                    body += `RETURN REQUEST DETAILS\n`;
                }

                body += `${'-'.repeat(70)}\n`;

                if (isOrder) {
                    body += `Pallet Type         : ${palletTypeValue}\n`;
                    body += `Pallet Size         : ${palletSize}\n`;
                    body += `Pallet Color        : ${palletColor}\n`;
                    body += `Usage               : ${usageSelect.options[usageSelect.selectedIndex].text}\n`;
                    body += `Quantity            : ${parseInt(quantity.value).toLocaleString()} units\n`;
                    body += `Required Date       : ${formattedDate}\n`;
                } else {
                    body += `Pallet Type         : ${palletTypeValue}\n`;
                    body += `Pallet Size         : ${palletSize}\n`;
                    body += `Pallet Color        : ${palletColor}\n`;
                    body += `Usage               : ${usageSelect.options[usageSelect.selectedIndex].text}\n`;
                    body += `Quantity to Return  : ${parseInt(quantity.value).toLocaleString()} units\n`;
                    body += `ETA Return Date     : ${formattedDate}\n`;
                }

                body += `${'-'.repeat(70)}\n\n`;

                // DELIVERY / RETURN INFORMATION : include From AND To
                if (isOrder) {
                    body += `DELIVERY INFORMATION\n`;
                } else {
                    body += `RETURN INFORMATION\n`;
                }
                body += `${'-'.repeat(70)}\n`;
                body += `From Warehouse           : ${warehouseFromOption ? warehouseFromOption.text : ''}\n`;
                body += `From Address             : \n${warehouseFromAddress}\n \n`;
                body += `To Warehouse             : ${warehouseToOption ? warehouseToOption.text : ''}\n`;
                body += `To Address               : \n${warehouseToAddress}\n`;
                body += `${'-'.repeat(70)}\n\n`;

                // Additional notes
                body += `ADDITIONAL NOTES\n`;
                body += `${'-'.repeat(70)}\n`;
                if (additionalNotes.value.trim()) {
                    body += `${additionalNotes.value.trim()}\n`;
                } else {
                    body += `-\n`;
                }
                body += `${'-'.repeat(70)}\n\n`;

                body += `Please confirm at your earliest convenience.\n\n`;
                body += `Thank you for your attention.\n\n`;
                body += `Best regards,\n`;
                body += `${currentUser.name}\n`;
                body += `${currentUser.company}\n`;
                body += `Email: ${currentUser.email}\n`;

                // fetch tokens (if meta available) - safe-guarded
                const fromMeta = getOptionMeta(warehouseFromSelect);
                const toMeta = getOptionMeta(warehouseToSelect);

                try {
                    const tokens = await fetchTokensIfNeeded(fromMeta, toMeta);

                    // append tokens section after delivery block so GmailIngestController can extract
                    // In generateEmail():
                    body += `\n${'-'.repeat(70)}\n`;
                    body += `WID-TOKEN-FROM: ${encodeURIComponent(tokens.tokenFrom || '')}\n`;
                    body += `WID-TOKEN-TO:   ${encodeURIComponent(tokens.tokenTo || '')}\n`;
                    body += `${'-'.repeat(70)}\n\n`;

                } catch (err) {
                    console.error('Token fetch failed; continuing without tokens', err);
                    // In generateEmail():
                    body += `\n${'-'.repeat(70)}\n`;
                    body += `WID-TOKEN-FROM: ${encodeURIComponent(tokens.tokenFrom || '')}\n`;
                    body += `WID-TOKEN-TO:   ${encodeURIComponent(tokens.tokenTo || '')}\n`;
                    body += `${'-'.repeat(70)}\n\n`;

                }

                emailBody.value = body;
            }

            clearFormBtn.addEventListener('click', function() {
                if (confirm('Are you sure you want to clear all form fields?')) {
                    usageSelect.value = '';
                    palletTypeDisplay.value = '';
                    palletType.value = '';
                    pullPalletTypeSelect.value = '';
                    quantity.value = '';
                    requiredDate.value = '';
                    // clear non-locked warehouse (we'll reconfigure below)
                    if (!warehouseFromSelect.disabled) warehouseFromSelect.value = '';
                    if (!warehouseToSelect.disabled) warehouseToSelect.value = '';

                    warehouseFromAddressDisplay.textContent = '';
                    warehouseToAddressDisplay.textContent = '';
                    additionalNotes.value = '';
                    palletImage.classList.add('d-none');
                    palletImagePlaceholder.classList.remove('d-none');
                    palletTypeName.textContent = '';
                    configureWarehouses(); // reapply lock/select customer option
                    generateEmail();
                }
            });

            // Reset all button
            resetAllBtn.addEventListener('click', function() {
                if (confirm('Are you sure you want to reset everything? All data will be lost.')) {
                    clearFormBtn.click();
                    requestTypeOrder.checked = true;
                    handleRequestTypeChange();
                }
            });

            // Copy to clipboard functionality
            window.copyToClipboard = function(elementId) {
                const element = document.getElementById(elementId);
                if (element.select) {
                    element.select();
                    document.execCommand('copy');
                    window.getSelection().removeAllRanges();
                } else {
                    const ta = document.createElement('textarea');
                    ta.value = element.textContent || '';
                    document.body.appendChild(ta);
                    ta.select();
                    document.execCommand('copy');
                    document.body.removeChild(ta);
                }
                try {
                    const btn = event.target.closest('button');
                    const originalHTML = btn.innerHTML;
                    btn.innerHTML = '<i class="bi bi-check"></i>';
                    btn.classList.add('text-success');
                    setTimeout(() => {
                        btn.innerHTML = originalHTML;
                        btn.classList.remove('text-success');
                    }, 1500);
                } catch (e) {
                    /* ignore */
                }
            };

            // Copy all button
            copyAllBtn.addEventListener('click', function() {
                if (!emailSubject.value) {
                    alert('{{__("Please fill in the required details first.")}}');
                    return;
                }
                const allContent =
                    `To: ${emailRecipient.value}\n\nSubject: ${emailSubject.value}\n\n${emailBody.value}`;
                const textarea = document.createElement('textarea');
                textarea.value = allContent;
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                const originalHTML = this.innerHTML;
                this.innerHTML = '<i class="bi bi-check me-1"></i>Copied!';
                this.classList.add('btn-success');
                this.classList.remove('btn-brand');
                setTimeout(() => {
                    this.innerHTML = originalHTML;
                    this.classList.remove('btn-success');
                    this.classList.add('btn-brand');
                }, 2000);
            });

            // Open in mail client (mailto)
            document.getElementById('openMailBtn').addEventListener('click', openMailClient);

function openMailClient() {
    // 1. Validate inputs
    if (!emailSubject.value || !emailBody.value) {
        alert('{{__("Please generate the email first.")}}');
        return;
    }

    // 2. Prepare data
    const to = encodeURIComponent(emailRecipient.value);
    const subject = encodeURIComponent(emailSubject.value);
    const body = encodeURIComponent(emailBody.value.replace(/\n/g, '\r\n'));

    // 3. Construct URLs
    const mailtoUrl = `mailto:${to}?subject=${subject}&body=${body}`;
    const gmailUrl = `https://mail.google.com/mail/?view=cm&fs=1&to=${to}&su=${subject}&body=${body}`;

    // 4. Device & Length Logic
    const isMobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
    const isTooLongForDesktop = mailtoUrl.length > 1800;

    // --- EXECUTION LOGIC ---

    if (isMobile) {
        /**
         * MOBILE STRATEGY: 
         * Always use mailto. Mobile OSs (iOS/Android) handle this perfectly 
         * by opening an app-chooser or the default mail app.
         */
        window.location.href = mailtoUrl;
    } else {
        /**
         * DESKTOP STRATEGY:
         * If the URL is too long (common Outlook/Browser crash), go straight to Gmail Web.
         * Otherwise, try the native app with a delayed fallback.
         */
        if (isTooLongForDesktop) {
            window.open(gmailUrl, '_blank');
        } else {
            // Attempt to open native app (Outlook, Apple Mail, etc.)
            window.location.href = mailtoUrl;

            // Optional: Only fallback if the user is still on this page after 2 seconds
            // This prevents the "double open" annoyance you experienced on your phone.
            setTimeout(() => {
                const userConfirmed = confirm("We tried opening your mail app. If it didn't open, would you like to use Gmail in the browser instead?");
                if (userConfirmed) {
                    window.open(gmailUrl, '_blank');
                }
            }, 2500);
        }
    }
}


            // Initial check
            handleRequestTypeChange();
            // call generateEmail once to populate initial body (it is async)
            generateEmail();
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // =========================================================================
            // GENERATE ORDER / RETURN EMAIL TOUR
            // =========================================================================
            window.startGenerateRequestEmailTour = function() {
                const driver = window.driver.js.driver;

                const driverObj = driver({
                    showProgress: true,
                    showButtons: ['next', 'previous', 'close'],
                    steps: [{
                            popover: {
                                title: '✉️ {{__("Generate Request Email")}}',
                                description: '{{__("This page helps you generate a ready-to-send email for ordering or returning pallets. Some sections will appear based on your selections.")}}'
                            }
                        },
                        {
                            element: '#requestTypeOrder',
                            popover: {
                                title: '🔄 {{__("Request Type")}}',
                                description: '{{__("Choose whether you want to")}} <strong>{{__("Order Pallets")}}</strong> {{(__('or'))}} <strong>{{__("Return Pallets")}}</strong>.<br><br>{{__("The form behavior and labels will adjust automatically.")}}',
                                side: 'bottom',
                                align: 'start'
                            }
                        },
                        {
                            element: '#requiredDate',
                            popover: {
                                title: '📅 {{__("Required / ETA Date")}}',
                                description: '{{__("Select the required delivery date (Order) or estimated return date (Return).")}}<br><br>{{__("The minimum date changes automatically based on request type.")}}',
                                side: 'bottom',
                                align: 'start'
                            }
                        },
                        {
                            element: '#usageSelect',
                            popover: {
                                title: '📦 {{__("Usage")}}',
                                description: '{{__("Select how the pallets will be used.")}}<br><br>{{__("This determines the pallet type, size, and visualization automatically.")}}',
                                side: 'bottom',
                                align: 'start'
                            }
                        },
                        {
                            popover: {
                                title: '🧩 {{__("Pallet Details (Auto Filled)")}}',
                                description: '{{__("After selecting a usage, the pallet type, dimensions, color, and image will appear automatically on the right side.")}}'
                            }
                        },
                        {
                            element: '#quantity',
                            popover: {
                                title: '🔢 {{__("Quantity")}}',
                                description: '{{__("Enter the total number of pallets to order or return.")}}',
                                side: 'bottom',
                                align: 'start'
                            }
                        },
                        {
                            element: '#warehouseFromSelect',
                            popover: {
                                title: '🏭 {{__("From Warehouse")}}',
                                description: '{{__("Select where the pallets will be shipped from.")}}<br><br>{{__("This field may be locked automatically depending on the request type.")}}',
                                side: 'bottom',
                                align: 'start'
                            }
                        },
                        {
                            element: '#warehouseToSelect',
                            popover: {
                                title: '📍 {{__("To Warehouse")}}',
                                description: '{{__("Select the destination warehouse.")}}<br><br>{{__("The available options depend on whether this is an Order or Return request.")}}',
                                side: 'bottom',
                                align: 'start'
                            }
                        },
                        {
                            popover: {
                                title: '⚠️ {{__("Return Condition (Return Only)")}}',
                                description: '{{__("When creating a")}} <strong>{{__("Return")}}</strong> {{__("request, an additional section will appear to describe the pallet condition and notes.")}}'
                            }
                        },
                        {
                            element: '#additionalNotes',
                            popover: {
                                title: '📝 {{__("Additional Notes")}}',
                                description: '{{__("Use this field to add any special instructions or remarks that should be included in the email.")}}',
                                side: 'bottom',
                                align: 'start'
                            }
                        },
                        {
                            element: '#emailSubject',
                            popover: {
                                title: '✉️ {{__("Generated Email Subject")}}',
                                description: '{{__("The email subject is generated automatically once all required fields are filled.")}}',
                                side: 'top',
                                align: 'start'
                            }
                        },
                        {
                            element: '#emailBody',
                            popover: {
                                title: '📄 {{__("Generated Email Body")}}',
                                description: '{{__("This is the full email content, already formatted and ready to paste into your email client.")}}',
                                side: 'top',
                                align: 'start'
                            }
                        },
                        {
                            element: '#copyAllBtn',
                            popover: {
                                title: '📋 {{__("Copy Email")}}',
                                description: '{{__("Copy the complete email (recipient, subject, and body) to your clipboard in one click.")}}',
                                side: 'left',
                                align: 'start'
                            }
                        },
                        {
                            element: '#openMailBtn',
                            popover: {
                                title: '📨 {{__("Open Email App")}}',
                                description: '{{__("Open your default email client or Gmail with the generated email content pre-filled.")}}',
                                side: 'top',
                                align: 'end'
                            }
                        },
                        {
                            popover: {
                                title: '🎉 {{__("All Set!")}}',
                                description: '{{__("You now know how to generate pallet order or return request emails.")}}<br><br><strong>{{__("Tip:")}}</strong> {{__("Complete the form from top to bottom to ensure the email is generated correctly.")}}'
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
            window.startProductTour = window.startGenerateRequestEmailTour;

        });
    </script>
@endpush
