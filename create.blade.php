@extends('layouts.app')

@section('title', 'New Delivery Note')

@section('header-left')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2 class="h5 mb-0 text-dark fw-semibold">{{__('New Delivery Note')}}</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('delivery.index') }}">{{__('Delivery Monitoring')}}</a>
                    </li>
                    <li class="breadcrumb-item active">{{__('Create')}}</li>
                </ol>
            </nav>
        </div>
    </div>
@endsection

@section('content')
    @if (!$canCreateDelivery)
        <div class="alert alert-warning" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Access Restricted:</strong> You do not have permission to create delivery notes.
            Only Admin, Warehouse Staff, or Customer users can create delivery notes.
        </div>
    @endif

    <form id="deliveryNoteForm" method="POST" action="{{ route('delivery.store') }}"
        @if (!$canCreateDelivery) class="d-none" @endif>
        @csrf
        @if (!empty($palletRequest))
            <input type="hidden" name="pallet_request_id" value="{{ $palletRequest->nid }}">
        @endif

        <!-- Delivery Information -->
        <div class="card mb-3">
            <div class="card-header bg-brand text-white">
                <i class="bi bi-info-circle me-2"></i>{{ __('Delivery Information') }}
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <!-- Row 1: Delivery Note Number, Delivery Date, Delivery Notes -->
                    <div class="col-3 col-md-3">
                        <label for="deliveryNoteNumber" class="form-label fw-semibold small">{{__('Delivery Note Number')}} <span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="deliveryNoteNumber"
                            name="delivery_note_number" value="{{ $nosj }}" placeholder="SJ/2RD108/31" required
                            readonly>
                    </div>

                    <div class="col-3 col-md-3">
                        <label for="deliveryDate" class="form-label fw-semibold small">{{__('Delivery Date')}} <span
                                class="text-danger">*</span></label>
                        <input type="date" class="form-control form-control-sm" id="deliveryDate" name="delivery_date"
                            required>
                    </div>

                    <div class="col-6 col-md-6">
                        <label for="deliveryNotes" class="form-label fw-semibold small">{{__('Delivery Notes')}}</label>
                        <textarea class="form-control form-control-sm" id="deliveryNotes" name="delivery_notes" rows="1"
                            placeholder="{{__('Enter delivery notes')}}"></textarea>
                    </div>

                    <!-- Row 2: From Entity (Customer or Company), From Address -->
                    <div class="col-6 col-md-3">
                        <label for="fromEntity" class="form-label fw-semibold small">{{__('Sender')}} <span
                                class="text-danger">*</span></label>
                        {{-- Admin: enabled dropdown with all options (companies + customers) --}}
                        {{-- Customer User: disabled dropdown, locked to their customer --}}
                        {{-- Company Warehouse User: disabled dropdown, locked to their company --}}
                        <select class="form-select form-select-sm" id="fromEntity" name="from_entity_id" required
                            @if (!$isAdmin) disabled @endif>
                            <option value="">{{__('Select sender')}}</option>

                            {{-- Companies: Show for Admin (all) or Company Warehouse User (their company, selected) --}}
                            @if ($isAdmin || $isCompanyWarehouse)
                                @if (isset($fromCompanies) && count($fromCompanies) > 0)
                                    @if ($isAdmin)
                                        <optgroup label="{{__('Companies')}}">
                                    @endif
                                    @foreach ($fromCompanies as $company)
                                        <option value="company_{{ $company['ckdcomp'] }}" data-type="company"
                                            data-ckd="{{ $company['ckdcomp'] }}" data-name="{{ $company['cnmcomp'] }}"
                                            data-warehouses="{{ json_encode($company['warehouses']) }}"
                                            @if ($isCompanyWarehouse) selected @endif>
                                            {{ $company['cnmcomp'] }}
                                        </option>
                                    @endforeach
                                    @if ($isAdmin)
                                        </optgroup>
                                    @endif
                                @endif
                            @endif

                            {{-- Customers: Show for Admin (all) or Customer User (their own, selected) --}}
                            @if ($isAdmin || $isCustomerUser)
                                @if (isset($fromCustomers) && count($fromCustomers) > 0)
                                    @if ($isAdmin)
                                        <optgroup label="{{__('Customers')}}">
                                    @endif
                                    @foreach ($fromCustomers as $customer)
                                        <option value="customer_{{ $customer['ckdcust'] }}" data-type="customer"
                                            data-nidcust="{{ $customer['nidcust'] }}"
                                            data-ckdcust="{{ $customer['ckdcust'] }}"
                                            data-cnmcust="{{ $customer['cnmcust'] }}"
                                            @if ($isCustomerUser) selected @endif>
                                            {{ $customer['cnmcust'] }}
                                        </option>
                                    @endforeach
                                    @if ($isAdmin)
                                        </optgroup>
                                    @endif
                                @endif
                            @endif
                        </select>
                        <!-- Hidden fields for from entity -->
                        <input type="hidden" id="fromType" name="from_type"
                            value="{{ $isCompanyWarehouse ? 'company' : 'customer' }}">
                        <input type="hidden" id="fromCustomerNid" name="nidcust_from">
                        <input type="hidden" id="fromCustomerKd" name="ckdcust_from">
                        <input type="hidden" id="fromCustomerName" name="from_customer_name">
                    </div>

                    <div class="col-6 col-md-3">
                        <label for="fromAddressSelect" class="form-label fw-semibold small">{{__('Sender Address')}} <span
                                class="text-danger">*</span></label>
                        <select class="form-select form-select-sm" id="fromAddressSelect" required
                            @if (($isWarehousePic || $isCompanyWarehouse) && count($userCustomerAddresses) === 1) disabled @endif>
                            <option value="">{{__('Select address')}}</option>
                            @if (!$isAdmin && count($userCustomerAddresses) > 0)
                                @foreach ($userCustomerAddresses as $addr)
                                    <option value="{{ $addr['ckdwh'] }}" data-city="{{ $addr['city'] }}"
                                        data-address="{{ $addr['address'] }}" data-type="{{ $addr['type'] }}"
                                        @if (($isWarehousePic || $isCompanyWarehouse) && count($userCustomerAddresses) === 1) selected @endif>
                                        {{ $addr['label'] }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        <input type="hidden" id="fromAddress" name="from_address">
                        <input type="hidden" id="fromCity" name="from_city">
                        <input type="hidden" id="fromCkdwh" name="from_ckdwh">
                    </div>

                    <!-- Row 3: To Customer Network, To Address -->
                    <div class="col-6 col-md-3">
                        <label for="toCustomerNetwork" class="form-label fw-semibold small">{{__('Receiver')}}
                            <span class="text-danger">*</span></label>
                        <select class="form-select form-select-sm" id="toCustomerNetwork" name="to_customer_id" required>
                            <option value="">{{__('Select sender first')}}</option>
                        </select>
                        <!-- Hidden fields for to entity -->
                        <input type="hidden" id="toType" name="to_type" value="customer">
                        <input type="hidden" id="toCustomerNid" name="nidcust_to">
                        <input type="hidden" id="toCustomerKd" name="ckdcust_to">
                        <input type="hidden" id="toCustomerName" name="to_customer_name">
                        <input type="hidden" id="agreementId" name="agreement_id">
                    </div>

                    <div class="col-6 col-md-3">
                        <label for="toAddressSelect" class="form-label fw-semibold small">{{__('Receiver Address')}} <span
                                class="text-danger">*</span></label>
                        <select class="form-select form-select-sm" id="toAddressSelect" required>
                            <option value="">{{__('Select receiver first')}}</option>
                        </select>
                        <input type="hidden" id="toAddress" name="to_address">
                        <input type="hidden" id="toCity" name="to_city">
                        <input type="hidden" id="toCkdwh" name="to_ckdwh">
                    </div>
                </div>
            </div>
        </div>

        <!-- Logistics Information -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-2 mb-2">
                    <!-- Row with Logistics and Export in same area -->
                    <div class="col-3 col-md-3">
                        <label for="logisticsCompany" class="form-label fw-semibold small">{{__('Logistic Company')}}
                            <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control form-control-sm" id="logisticsCompanyInput"
                                name="logistics_company" placeholder="{{__('Type or select')}}" required>
                            <button class="btn btn-brand dropdown-toggle" type="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="bi bi-chevron-down"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" id="logisticsDropdown">
                                @foreach ($logisticsData as $key => $logistic)
                                    <li><a class="dropdown-item" href="#"
                                            data-value="{{ $key }}">{{ $key }}</a></li>
                                @endforeach
                                {{-- <li><a class="dropdown-item" href="#" data-value="PT Global Kencana Express">PT
                                        Global Kencana Express</a></li>
                                <li><a class="dropdown-item" href="#" data-value="PT Hiba Logistic">PT Hiba
                                        Logistic</a></li>
                                <li><a class="dropdown-item" href="#" data-value="PT Bintang Transportasi">PT
                                        Bintang Transportasi</a></li> --}}
                            </ul>
                        </div>
                    </div>

                    <!-- Driver's Name with dropdown/manual input -->
                    <div class="col-2 col-md-2">
                        <label for="driverName" class="form-label fw-semibold small">{{__("Driver's Name")}} <span
                                class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control form-control-sm" id="driverNameInput"
                                name="driver_name" placeholder="Type or select" required>
                            <button class="btn btn-brand dropdown-toggle" type="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="bi bi-chevron-down"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" id="driverDropdown">
                                <!-- Will be populated based on logistics company -->
                            </ul>
                        </div>
                    </div>

                    <div class="col-2 col-md-2">
                        <label for="driverPhone" class="form-label fw-semibold small">{{__("Driver's Phone")}} <span
                                class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <select class="form-select" id="countryCode" name="country_code"
                                style="max-width: 85px; flex: 0 0 85px;">
                                <option value="+62" selected>+62</option>
                                <option value="+60">+60</option>
                                <option value="+65">+65</option>
                                <option value="+66">+66</option>
                                <option value="+84">+84</option>
                                <option value="+1">+1</option>
                            </select>
                            <input type="text" class="form-control form-control-sm" id="driverPhone"
                                name="driver_phone" placeholder="812..." required>
                        </div>
                    </div>

                    <div class="col-2 col-md-2">
                        <label for="vehicleType" class="form-label fw-semibold small">{{__('Vehicle Type')}} <span
                                class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control form-control-sm" id="vehicleTypeInput"
                                name="vehicle_type" placeholder="{{__('Type or select')}}" required>
                            <button class="btn btn-brand dropdown-toggle" type="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="bi bi-chevron-down"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" id="vehicleTypeDropdown">
                                <li><a class="dropdown-item" href="#" data-value="Motorcycle">{{__('Motorcycle')}}</a></li>
                                <li><a class="dropdown-item" href="#" data-value="Car">{{__('Car')}}</a></li>
                                <li><a class="dropdown-item" href="#" data-value="Pickup Truck">{{__('Pickup Truck')}}</a>
                                </li>
                                <li><a class="dropdown-item" href="#" data-value="Van">{{__('Van')}}</a></li>
                                <li><a class="dropdown-item" href="#" data-value="Truck">{{__('Truck')}}</a></li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-3 col-md-3">
                        <label for="vehicleLicense" class="form-label fw-semibold small">{{__('Vehicle License Number')}} <span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="vehicleLicense"
                            name="vehicle_license_number" placeholder="DK 1234 AA" required>
                    </div>
                </div>
                <div class="row g-2 align-items-end">
                    {{-- Container Toggle --}}
                    <div class="col-3 col-md-3">
                        <label class="form-label fw-semibold small mb-1">{{__('Is this a container shipment?')}}</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="isExport" name="is_export">
                            <label class="form-check-label small" for="isExport">
                                {{__('Yes')}}
                            </label>
                        </div>
                    </div>

                    <!-- Container Number -->
                    <div class="col-3 d-none" id="containerNumberGroup">
                        <label class="form-label fw-semibold small mb-1">{{__('Container No.')}}</label>
                        <input type="text" class="form-control form-control-sm" id="containerNumber"
                            name="container_number" placeholder="ABCD1234567">
                    </div>

                    <!-- Seal Number -->
                    <div class="col-3 d-none" id="sealNumberGroup">
                        <label class="form-label fw-semibold small mb-1">{{__('Seal No.')}}</label>
                        <input type="text" class="form-control form-control-sm" id="sealNumber" name="seal_number"
                            placeholder="SL123456">
                    </div>
                </div>
            </div>
        </div>

        <!-- Delivery Items -->
        <div class="card mb-3">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold">
                    <i class="bi bi-box-seam me-2"></i>{{__('Delivery Items')}}
                </span>
                <button type="button" id="addItemBtn" class="header-action d-flex align-items-center gap-1">
                    <i class="bi bi-plus"></i>
                    {{__('Add Item')}}
                </button>
            </div>

            <div class="card-body p-2">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0" id="deliveryItemsTable">
                        <thead class="table-light">
                            <tr class="small text-muted">
                                <th style="width:60%">{{__('Pallet Type')}} *</th>
                                <th style="width:30%">{{__('Quantity')}} *</th>
                                <th style="width:10%" class="text-center"></th>
                            </tr>
                        </thead>
                        <tbody id="deliveryItemsContainer">
                            <!-- Items go here -->
                        </tbody>
                    </table>
                </div>

                <div id="noItemsPlaceholder" class="text-center py-2 text-muted small">
                    {{__('No items added yet')}}. {{__('Click')}} <strong>+ {{__('Add Item')}}</strong> {{__('to begin')}}.
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="card">
            <div class="card-body p-2">
                <div class="d-flex gap-2 justify-content-end align-items-center">
                    @if (!$isCreatedFromRequest)
                        <button type="button" class="btn btn-sm btn-danger" id="resetBtn">
                            <i class="bi bi-arrow-clockwise me-1"></i>{{__('Reset')}}
                        </button>
                    @endif
                    <button type="submit" class="btn btn-sm btn-brand" name="action" value="save">
                        <i class="bi bi-check-circle me-1"></i>{{__('Create')}}
                    </button>
                    <div class="d-flex align-items-center gap-2">
                        <label for="printQuantity" class="mb-0 small text-muted">{{__('Copies')}}:</label>
                        <input type="number" class="form-control form-control-sm" id="printQuantity" min="1"
                            max="10" value="2" style="width: 60px;">
                        <button type="button" class="btn btn-sm btn-success" id="btnCreateAndPrint">
                            <i class="bi bi-printer me-1"></i>{{__('Create & Print')}}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Item Template (Hidden) -->
    <template id="itemTemplate">
        <tr class="delivery-item">
            <td>
                <select class="form-select form-select-sm pallet-select" name="items[INDEX][ckdbrg]" required>
                    <option value="">{{__('Select pallet type')}}</option>
                </select>
                <!-- Hidden input for prefilled mode -->
                <input type="hidden" class="pallet-hidden-input" name="" value="">
                <small class="text-muted stock-warning" style="display:none;"></small>
            </td>

            <td>
                <div class="input-group input-group-sm">
                    <input type="number" class="form-control form-control-sm text-end quantity-input"
                        name="items[INDEX][quantity]" min="1" value="1" required>
                    <span class="input-group-text">pcs</span>
                </div>
                <!-- Hidden field to track received quantity (initially 0) -->
                <input type="hidden" name="items[INDEX][received_qty]" value="0">
            </td>

            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger remove-item-btn">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
    </template>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title fw-bold" id="confirmationModalLabel">
                        <i class="bi bi-exclamation-triangle me-2"></i>{{__('Confirm Delivery Note Submission')}}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning mb-3">
                        <strong>{{__('Warning')}}:</strong> {{__('This action will record the delivery note and affect inventory and
                        billing. Please confirm all information is correct before proceeding.')}}
                    </div>

                    <h6 class="fw-bold mb-2">{{__('Delivery Information')}}</h6>
                    <table class="table table-sm table-bordered mb-3">
                        <tr>
                            <td class="fw-semibold" style="width: 30%">{{__('DN Number')}}</td>
                            <td id="confirm-dn-number"></td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">{{__('Delivery Date')}}</td>
                            <td id="confirm-delivery-date"></td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">{{__('Delivery Notes')}}</td>
                            <td id="confirm-delivery-notes"></td>
                        </tr>
                    </table>

                    <h6 class="fw-bold mb-2">{{__('Shipment Details')}}</h6>
                    <table class="table table-sm table-bordered mb-3">
                        <tr>
                            <td class="fw-semibold" style="width: 30%">{{__('Sender')}}</td>
                            <td id="confirm-sender"></td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">{{__('Sender Address')}}</td>
                            <td id="confirm-sender-address"></td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">{{__('Receiver')}}</td>
                            <td id="confirm-receiver"></td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">{{__('Receiver Address')}}</td>
                            <td id="confirm-receiver-address"></td>
                        </tr>
                    </table>

                    <h6 class="fw-bold mb-2">{{__('Logistics Information')}}</h6>
                    <table class="table table-sm table-bordered mb-3">
                        <tr>
                            <td class="fw-semibold" style="width: 30%">{{__('Logistics Company')}}</td>
                            <td id="confirm-logistics-company"></td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">{{__('Driver Name')}}</td>
                            <td id="confirm-driver-name"></td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">{{__('Driver Phone')}}</td>
                            <td id="confirm-driver-phone"></td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">{{__('Vehicle Type')}}</td>
                            <td id="confirm-vehicle-type"></td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">{{__('Vehicle License')}}</td>
                            <td id="confirm-vehicle-license"></td>
                        </tr>
                        <tr id="confirm-container-row" style="display: none;">
                            <td class="fw-semibold">{{__('Container Number')}}</td>
                            <td id="confirm-container-number"></td>
                        </tr>
                        <tr id="confirm-seal-row" style="display: none;">
                            <td class="fw-semibold">{{__('Seal Number')}}</td>
                            <td id="confirm-seal-number"></td>
                        </tr>
                    </table>

                    <h6 class="fw-bold mb-2">{{ __("Delivery Items") }}</h6>
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 10%">{{ __("No") }}</th>
                                <th style="width: 60%">{{ __("Pallet Type") }}</th>
                                <th style="width: 30%">{{ __("Quantity") }}</th>
                            </tr>
                        </thead>
                        <tbody id="confirm-items-list">
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold">
                                <td colspan="2" class="text-end">{{ __("TOTAL:") }}</td>
                                <td id="confirm-total-qty"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>{{__('Cancel')}}
                    </button>
                    <button type="button" class="btn btn-success" id="confirmSubmitBtn">
                        <i class="bi bi-check-circle me-1"></i>{{__('Confirm & Submit')}}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden print container -->
    <div id="printContainer"></div>

@endsection

@push('styles')
    <style>
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
        }

        .delivery-note-print {
            font-family: 'Arial', sans-serif;
            font-size: 9pt;
            color: #000;
            background: white;
            page-break-after: always;
            padding: 1cm;
        }

        .delivery-note-print:last-child {
            page-break-after: auto;
        }

        .delivery-note-print .header {
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }

        .delivery-note-print .header-content {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 15px;
        }

        .delivery-note-print .company-logo {
            height: 40px;
            width: auto;
            object-fit: contain;
        }

        .delivery-note-print .company-name {
            font-size: 18pt;
            font-weight: bold;
            color: #000;
            letter-spacing: 2px;
        }

        .delivery-note-print .document-title {
            font-size: 12pt;
            font-weight: bold;
            text-align: center;
            margin: 8px 0;
            text-transform: uppercase;
            border-bottom: 1px solid #000;
            padding-bottom: 3px;
        }

        .delivery-note-print table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .delivery-note-print table th,
        .delivery-note-print table td {
            border: 1px solid #000;
            padding: 3px 5px;
            font-size: 8pt;
            line-height: 1.2;
        }

        .delivery-note-print table th {
            background-color: #e0e0e0;
            font-weight: bold;
        }

        .delivery-note-print .info-grid {
            display: grid;
            grid-template-columns: 120px 1fr;
            gap: 3px 8px;
            margin-bottom: 8px;
            font-size: 8pt;
        }

        .delivery-note-print .info-label {
            font-weight: bold;
        }

        .delivery-note-print .signature-section {
            margin-top: 15px;
            display: flex;
            justify-content: space-around;
        }

        .delivery-note-print .signature-box {
            text-align: center;
            width: 140px;
        }

        .delivery-note-print .signature-box .title {
            font-weight: bold;
            font-size: 8pt;
            margin-bottom: 2px;
        }

        .delivery-note-print .signature-box .subtitle {
            font-style: italic;
            font-size: 7pt;
            color: #666;
            margin-bottom: 25px;
        }

        .delivery-note-print .signature-line {
            border-top: 1px solid #000;
            padding-top: 3px;
            font-size: 8pt;
        }

        .delivery-note-print .footer-note {
            margin-top: 10px;
            font-size: 7pt;
            text-align: center;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 5px;
        }

        .delivery-note-print .section-title {
            font-weight: bold;
            font-size: 9pt;
            margin: 8px 0 4px 0;
            padding: 2px 5px;
            background-color: #f5f5f5;
            border-left: 3px solid #333;
        }

        .delivery-note-print .two-column {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 8px;
        }

        .delivery-note-print .info-box {
            border: 1px solid #000;
            padding: 5px;
        }

        .delivery-note-print .info-box-title {
            font-weight: bold;
            font-size: 8pt;
            margin-bottom: 3px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 2px;
        }

        .delivery-note-print .info-row {
            display: grid;
            grid-template-columns: 100px 1fr;
            font-size: 8pt;
            margin-bottom: 2px;
        }

        .stock-warning {
            color: #dc3545;
            font-size: 11px;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add this at the beginning of DOMContentLoaded, before other initializations
            const prefilledData = @json($prefilledData ?? null);
            const receiverOptions = @json($receiverOptions ?? null);
            const receiverWarehouseOptions = @json($receiverWarehouseOptions ?? null);

            const fromEntity = document.getElementById('fromEntity');
            const fromAddressSelect = document.getElementById('fromAddressSelect');
            const fromAddress = document.getElementById('fromAddress');
            const fromCity = document.getElementById('fromCity');
            const fromCkdwh = document.getElementById('fromCkdwh');
            const toCustomerNetwork = document.getElementById('toCustomerNetwork');
            const toAddressSelect = document.getElementById('toAddressSelect');
            const toAddress = document.getElementById('toAddress');
            const toCity = document.getElementById('toCity');
            const toCkdwh = document.getElementById('toCkdwh');
            const addItemBtn = document.getElementById('addItemBtn');
            const deliveryItemsContainer = document.getElementById('deliveryItemsContainer');
            const noItemsPlaceholder = document.getElementById('noItemsPlaceholder');
            const itemTemplate = document.getElementById('itemTemplate');
            const deliveryDate = document.getElementById('deliveryDate');
            const createPrintBtn = document.getElementById('btnCreateAndPrint');
            const resetBtn = document.getElementById('resetBtn');
            const isExport = document.getElementById('isExport');
            const containerNumberGroup = document.getElementById('containerNumberGroup');
            const sealNumberGroup = document.getElementById('sealNumberGroup');

            let itemIndex = 0;
            let prefilledMode = false; // Track if we're in prefill mode

            const isAdmin = @json($isAdmin);
            const isCustomerUser = @json($isCustomerUser ?? false);
            const isWarehousePic = @json($isWarehousePic ?? false);
            const isCompanyWarehouse = @json($isCompanyWarehouse ?? false);
            const canCreateDelivery = @json($canCreateDelivery ?? false);
            const preloadedAddresses = @json($userCustomerAddresses ?? []);

            // Real drivers data from database by logistics company
            const driversData = @json($logisticsData);

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
                    const baseLabel = option.dataset.baseLabel || option.textContent || '';
                    const distanceLabel = option.dataset.distanceLabel || '';
                    if (!distanceLabel) {
                        return `<span class="d-flex justify-content-between align-items-center w-100"><span>${baseLabel}</span></span>`;
                    }
                    return `<span class="d-flex justify-content-between align-items-center w-100"><span>${baseLabel}</span><span style="color:#dc3545; margin-left:12px; white-space:nowrap;">${distanceLabel}</span></span>`
                };

                const sync = () => {
                    const selectedOption = selectEl.options[selectEl.selectedIndex] || selectEl.options[0];
                    const selectedText = selectedOption ? renderLabel(selectedOption) : '';
                    trigger.innerHTML = `<span>${selectedText}</span>`;

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
                sync();

                return { sync };
            }

            const toAddressStyledDropdown = initStyledDistanceDropdown(toAddressSelect);

            // Helper functions first...

            function clearToCustomerFields() {
                document.getElementById('toCustomerNid').value = '';
                document.getElementById('toCustomerKd').value = '';
                document.getElementById('toCustomerName').value = '';
                document.getElementById('agreementId').value = '';
                toAddress.value = '';
                toCkdwh.value = '';
                toCity.value = '';
            }

            async function reloadReceiverAddressesBySenderCity() {
                const receiverCustomerId = toCustomerNetwork.value;

                if (!receiverCustomerId) {
                    toAddressSelect.innerHTML = '<option value="">{{__("Select receiver first")}}</option>';
                    return;
                }

                toAddressSelect.innerHTML = '<option value="">Loading...</option>';
                clearToCustomerFields();
                await loadCustomerAddresses(receiverCustomerId, 'to');
            }

            async function loadCustomerAddresses(customerId, type) {
                const selectElement = type === 'from' ? fromAddressSelect : toAddressSelect;
                selectElement.innerHTML = '<option value="">Loading...</option>';
                const selectedFromAddressOption = fromAddressSelect.options[fromAddressSelect.selectedIndex];
                const senderCity = selectedFromAddressOption?.dataset?.city || fromCity.value || '';

                try {
                    const response = await fetch(`/api/customers/${customerId}/addresses?city=${encodeURIComponent(senderCity)}`);
                    const addresses = await response.json();

                    selectElement.innerHTML = '<option value="">Select address</option>';

                    addresses.forEach(addr => {
                        const option = document.createElement('option');
                        option.value = addr.ckdwh;
                        option.dataset.address = addr.address;
                        option.dataset.city = addr.city;
                        option.dataset.type = addr.type;
                        option.dataset.baseLabel = addr.label;
                        option.dataset.distanceLabel = addr.distance;
                        option.innerHTML = `${addr.label} <span style="color:#198754;">${addr.distance}</span>`;
                        selectElement.appendChild(option);
                    });

                    if (addresses.length >= 1) {
                        selectElement.selectedIndex = 1;
                        selectElement.dispatchEvent(new Event('change'));
                    }

                } catch (error) {
                    console.error('Error loading addresses:', error);
                    selectElement.innerHTML = '<option value="">Error loading addresses</option>';
                }
            }

            async function loadCustomerNetwork(customerId, companyCode = null) {
                toCustomerNetwork.innerHTML = '<option value="">Loading...</option>';
                toAddressSelect.innerHTML = '<option value="">{{__("Select receiver first")}}</option>';
                clearToCustomerFields();

                try {
                    let response;
                    if (companyCode) {
                        response = await fetch(`/api/companies/${companyCode}/direct-agreements`);
                    } else {
                        response = await fetch(`/api/customers/${customerId}/network`);
                    }

                    const partners = await response.json();

                    toCustomerNetwork.innerHTML = '<option value="">{{__("Select receiver")}}</option>';

                    if (partners.length === 0) {
                        toCustomerNetwork.innerHTML = '<option value="">{{__("No receivers found")}}</option>';
                        return;
                    }

                    partners.forEach(partner => {
                        const option = document.createElement('option');
                        option.value = partner.nidcust;
                        option.dataset.ckdcust = partner.ckdcust;
                        option.dataset.cnmcust = partner.cnmcust;
                        option.dataset.agreementId = partner.agreement_id || '';
                        option.dataset.agreementType = partner.agreement_type || '';
                        option.textContent = partner.cnmcust;
                        option.dataset.type = 'customer';
                        toCustomerNetwork.appendChild(option);
                    });
                } catch (error) {
                    console.error('Error loading network:', error);
                    toCustomerNetwork.innerHTML = '<option value="">Error loading network</option>';
                }
            }

            async function generateDeliveryNoteNumber(entityType, entityCode) {
                const deliveryNoteNumberInput = document.getElementById('deliveryNoteNumber');

                try {
                    const response = await fetch('/api/delivery/generate-number', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            entity_type: entityType,
                            entity_code: entityCode
                        })
                    });

                    const data = await response.json();

                    if (data.delivery_note_number) {
                        deliveryNoteNumberInput.value = data.delivery_note_number;
                    }
                } catch (error) {
                    console.error('Error generating delivery note number:', error);
                }
            }

            function setStockItemToSend() {
                let ckdwh = document.getElementById('fromCkdwh').value;

                const stockData = <?php echo json_encode($arrbarang); ?>;
                console.log('Stock data:', stockData);
                console.log('Selected warehouse:', ckdwh);


                let strhtml = '<option value="">{{__("Select pallet type")}}</option>';

                Object.keys(stockData).forEach(ckdbrg => {
                    const warehouseData = stockData[ckdbrg];

                    if (warehouseData[ckdwh] && warehouseData[ckdwh]['jumlah']) {
                        const stock = warehouseData[ckdwh]['jumlah'];
                        const itemName = warehouseData[ckdwh]['name'] || ckdbrg;

                        if (stock > 0) {
                            strhtml +=
                                `<option value="${ckdbrg}" data-stock="${stock}">${itemName} - Stock: ${stock} pcs</option>`;
                        }
                    }
                });

                document.querySelectorAll('.pallet-select').forEach(select => {
                    const currentValue = select.value;
                    select.innerHTML = strhtml;

                    if (currentValue) {
                        const optionExists = Array.from(select.options).some(opt => opt.value ===
                            currentValue);
                        if (optionExists) {
                            select.value = currentValue;
                        }
                    }
                });
            }


            // PREFILLED DATA HANDLING - DO THIS FIRST
            if (prefilledData) {
                prefilledMode = true;
                console.log('Prefilling form with data:', prefilledData);

                // Step 1: Manually populate sender warehouse dropdown
                if (prefilledData.from_warehouse_ckdwh) {
                    fromAddressSelect.innerHTML = '<option value="">{{__("Select address")}}</option>';
                    const fromOption = document.createElement('option');
                    fromOption.value = prefilledData.from_warehouse_ckdwh;
                    fromOption.dataset.address = prefilledData.from_warehouse_address;
                    fromOption.dataset.city = prefilledData.from_warehouse_city;
                    fromOption.dataset.type = 'warehouse';
                    fromOption.textContent = prefilledData.from_warehouse_name + ' - ' + prefilledData
                        .from_warehouse_city;
                    fromOption.selected = true;
                    fromAddressSelect.appendChild(fromOption);
                    fromAddressSelect.disabled = true;

                    // Manually set hidden fields
                    fromAddress.value = prefilledData.from_warehouse_address;
                    fromCity.value = prefilledData.from_warehouse_city;
                    fromCkdwh.value = prefilledData.from_warehouse_ckdwh;
                }

                // Step 2: Set sender entity
                if (prefilledData.from_entity_id) {
                    fromEntity.value = prefilledData.from_entity_id;
                    fromEntity.disabled = true;

                    // Set hidden fields
                    document.getElementById('fromType').value = prefilledData.from_type;
                    if (prefilledData.from_type === 'company') {
                        document.getElementById('fromCustomerKd').value = prefilledData.from_ckd;
                    } else {
                        document.getElementById('fromCustomerNid').value = prefilledData.from_nidcust;
                        document.getElementById('fromCustomerKd').value = prefilledData.from_ckdcust;
                    }
                    document.getElementById('fromCustomerName').value = prefilledData.from_entity_name;
                }

                // Step 3: Manually populate receiver dropdown
                if (receiverOptions && receiverOptions.length > 0) {
                    toCustomerNetwork.innerHTML = '<option value="">{{__("Select receiver")}}</option>';
                    receiverOptions.forEach(receiver => {
                        const option = document.createElement('option');
                        option.value = receiver.nidcust || receiver.ckdcust;
                        option.dataset.ckdcust = receiver.ckdcust;
                        option.dataset.cnmcust = receiver.cnmcust;
                        option.dataset.type = receiver.type || 'customer';
                        option.textContent = receiver.cnmcust;
                        option.selected = true;
                        toCustomerNetwork.appendChild(option);
                    });
                    toCustomerNetwork.disabled = true;

                    // Set hidden fields
                    const firstReceiver = receiverOptions[0];
                    document.getElementById('toType').value = firstReceiver.type || 'customer';
                    document.getElementById('toCustomerNid').value = firstReceiver.nidcust;
                    document.getElementById('toCustomerKd').value = firstReceiver.ckdcust;
                    document.getElementById('toCustomerName').value = firstReceiver.cnmcust;
                }

                // Step 4: Manually populate receiver warehouse dropdown
                if (receiverWarehouseOptions && receiverWarehouseOptions.length > 0) {
                    toAddressSelect.innerHTML = '<option value="">{{__("Select address")}}</option>';
                    receiverWarehouseOptions.forEach(wh => {
                        const option = document.createElement('option');
                        option.value = wh.ckdwh;
                        option.dataset.address = wh.address;
                        option.dataset.city = wh.city;
                        option.dataset.type = wh.type;
                        option.textContent = wh.label;
                        option.selected = true;
                        toAddressSelect.appendChild(option);
                    });
                    toAddressSelect.disabled = true;

                    // Set hidden fields
                    const firstWh = receiverWarehouseOptions[0];
                    toCkdwh.value = firstWh.ckdwh;
                    toAddress.value = firstWh.address;
                    toCity.value = firstWh.city;
                }

                // Step 5: Call setStockItemToSend to populate options
                setStockItemToSend();

                // Step 6: Add and prefill item
                setTimeout(() => {
                    deliveryItemsContainer.innerHTML = '';
                    addItemBtn.click();

                    setTimeout(() => {
                        const firstRow = deliveryItemsContainer.querySelector('.delivery-item');
                        if (firstRow && prefilledData.pallet_ckdbrg) {
                            const palletSelect = firstRow.querySelector('.pallet-select');
                            const quantityInput = firstRow.querySelector('.quantity-input');

                            console.log('Setting pallet select to:', prefilledData.pallet_ckdbrg);
                            console.log('Available options:', Array.from(palletSelect.options).map(
                                o => o.value));

                            // Set the select value (for display purposes)
                            palletSelect.value = prefilledData.pallet_ckdbrg;
                            quantityInput.value = prefilledData.quantity;

                            if (palletSelect.value !== prefilledData.pallet_ckdbrg) {
                                console.error('Failed to select pallet type:', prefilledData
                                    .pallet_ckdbrg);
                                console.log('Pallet select current value:', palletSelect.value);
                            }

                            // SOLUTION: Disable the select and add a hidden input with the actual value
                            palletSelect.disabled = true;
                            palletSelect.removeAttribute(
                            'required'); // Remove required from disabled select
                            palletSelect.style.backgroundColor = '#e9ecef';
                            palletSelect.style.cursor = 'not-allowed';

                            // Create hidden input that will be submitted instead
                            const hiddenPalletInput = document.createElement('input');
                            hiddenPalletInput.type = 'hidden';
                            hiddenPalletInput.name = palletSelect
                            .name; // Use the same name as the select
                            hiddenPalletInput.value = prefilledData.pallet_ckdbrg;
                            hiddenPalletInput.className =
                            'prefilled-pallet-input'; // Mark it for identification
                            palletSelect.parentNode.appendChild(hiddenPalletInput);

                            // Disable quantity input and add hidden input
                            quantityInput.disabled = true;
                            quantityInput.removeAttribute(
                            'required'); // Remove required from disabled input
                            quantityInput.style.backgroundColor = '#e9ecef';
                            quantityInput.style.cursor = 'not-allowed';

                            // Create hidden input for quantity
                            const hiddenQuantityInput = document.createElement('input');
                            hiddenQuantityInput.type = 'hidden';
                            hiddenQuantityInput.name = quantityInput.name; // Use the same name
                            hiddenQuantityInput.value = prefilledData.quantity;
                            hiddenQuantityInput.className =
                            'prefilled-quantity-input'; // Mark it for identification
                            quantityInput.parentNode.appendChild(hiddenQuantityInput);

                            const removeBtn = firstRow.querySelector('.remove-item-btn');
                            if (removeBtn) {
                                removeBtn.style.display = 'none';
                            }
                        }
                    }, 200);
                }, 100);
            }

            // Toggle export container fields
            isExport.addEventListener('change', function() {
                if (this.checked) {
                    containerNumberGroup.classList.remove('d-none');
                    sealNumberGroup.classList.remove('d-none');
                } else {
                    containerNumberGroup.classList.add('d-none');
                    sealNumberGroup.classList.add('d-none');
                    document.getElementById('containerNumber').value = '';
                    document.getElementById('sealNumber').value = '';
                }
            });

            // From Entity change handler
            fromEntity.addEventListener('change', async function() {
                const selectedOption = this.options[this.selectedIndex];

                if (this.value) {
                    const entityType = selectedOption.dataset.type;
                    document.getElementById('fromType').value = entityType;

                    if (entityType === 'company') {
                        const ckdcomp = selectedOption.dataset.ckd;
                        const companyName = selectedOption.dataset.name;
                        const warehouses = JSON.parse(selectedOption.dataset.warehouses || '[]');

                        document.getElementById('fromCustomerNid').value = '';
                        document.getElementById('fromCustomerKd').value = ckdcomp;
                        document.getElementById('fromCustomerName').value = companyName;

                        if (isAdmin) {
                            await generateDeliveryNoteNumber(entityType, ckdcomp);
                        }

                        if (isAdmin) {
                            fromAddressSelect.innerHTML = '<option value="">{{__("Select warehouse")}}</option>';
                            warehouses.forEach(wh => {
                                const option = document.createElement('option');
                                option.value = wh.ckdwh;
                                option.textContent = wh.cnmwh;
                                option.dataset.address = wh.calmtwh;
                                option.dataset.city = wh.ckotawh;
                                option.dataset.type = 'warehouse';
                                fromAddressSelect.appendChild(option);
                            });
                        }

                        loadCustomerNetwork(null, ckdcomp);

                    } else {
                        const nidcust = selectedOption.dataset.nidcust;
                        const ckdcust = selectedOption.dataset.ckdcust;
                        const cnmcust = selectedOption.dataset.cnmcust;

                        document.getElementById('fromCustomerNid').value = nidcust;
                        document.getElementById('fromCustomerKd').value = ckdcust;
                        document.getElementById('fromCustomerName').value = cnmcust;

                        if (isAdmin) {
                            await generateDeliveryNoteNumber(entityType, ckdcust);
                        }

                        if (isAdmin) {
                            loadCustomerAddresses(nidcust, 'from');
                        }

                        loadCustomerNetwork(nidcust);
                    }
                } else {
                    document.getElementById('fromType').value = 'customer';
                    document.getElementById('fromCustomerNid').value = '';
                    document.getElementById('fromCustomerKd').value = '';
                    document.getElementById('fromCustomerName').value = '';
                    fromAddressSelect.innerHTML = '<option value="">{{__("Select sender first")}}</option>';
                    fromAddress.value = '';
                    fromCity.value = '';
                    fromCkdwh.value = '';

                    toCustomerNetwork.innerHTML = '<option value="">{{__("Select sender first")}}</option>';
                    toAddressSelect.innerHTML = '<option value="">{{__("Select receiver first")}}</option>';
                    clearToCustomerFields();
                }
            });

            fromAddressSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (this.value) {
                    fromAddress.value = selectedOption.dataset.address || '';
                    fromCity.value = selectedOption.dataset.city || '';
                    fromCkdwh.value = selectedOption.value;
                } else {
                    fromAddress.value = '';
                    fromCity.value = '';
                    fromCkdwh.value = '';
                }
                reloadReceiverAddressesBySenderCity();
                setStockItemToSend();
            });

            toCustomerNetwork.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];

                if (this.value) {
                    const receiverType = selectedOption.dataset.type || 'customer';
                    document.getElementById('toType').value = receiverType;
                    document.getElementById('toCustomerNid').value = this.value;
                    document.getElementById('toCustomerKd').value = selectedOption.dataset.ckdcust || '';
                    document.getElementById('toCustomerName').value = selectedOption.dataset.cnmcust ||
                        selectedOption.text;
                    document.getElementById('agreementId').value = selectedOption.dataset.agreementId || '';

                    loadCustomerAddresses(this.value, 'to');
                } else {
                    clearToCustomerFields();
                    toAddressSelect.innerHTML = '<option value="">{{__("Select receiver first")}}</option>';
                }
            });

            toAddressSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (this.value) {
                    toCkdwh.value = selectedOption.value;
                    toAddress.value = selectedOption.dataset.address;
                    toCity.value = selectedOption.dataset.city || '';
                } else {
                    toCkdwh.value = '';
                    toAddress.value = '';
                    toCity.value = '';
                }
            });

            // For non-admin users initialization (non-prefilled mode)
            if (!prefilledMode && ((isCustomerUser || isCompanyWarehouse) && fromEntity.value)) {
                if ((isWarehousePic || isCompanyWarehouse) && fromAddressSelect.options.length > 1) {
                    if (fromAddressSelect.selectedIndex <= 0) {
                        fromAddressSelect.selectedIndex = 1;
                    }
                    fromAddressSelect.dispatchEvent(new Event('change'));
                }

                const selectedOption = fromEntity.options[fromEntity.selectedIndex];
                const entityType = selectedOption.dataset.type;

                if (entityType === 'company') {
                    document.getElementById('fromType').value = 'company';
                    document.getElementById('fromCustomerNid').value = '';
                    document.getElementById('fromCustomerKd').value = selectedOption.dataset.ckd;
                    document.getElementById('fromCustomerName').value = selectedOption.dataset.name;
                    loadCustomerNetwork(null, selectedOption.dataset.ckd);
                } else {
                    document.getElementById('fromType').value = 'customer';
                    document.getElementById('fromCustomerNid').value = selectedOption.dataset.nidcust;
                    document.getElementById('fromCustomerKd').value = selectedOption.dataset.ckdcust;
                    document.getElementById('fromCustomerName').value = selectedOption.dataset.cnmcust;
                    loadCustomerNetwork(selectedOption.dataset.nidcust);
                }
            }

            // Logistics handling
            const logisticsInput = document.getElementById('logisticsCompanyInput');
            const logisticsDropdownItems = document.querySelectorAll('#logisticsDropdown .dropdown-item');
            const driverNameInput = document.getElementById('driverNameInput');
            const driverPhone = document.getElementById('driverPhone');
            const vehicleLicense = document.getElementById('vehicleLicense');
            const vehicleTypeInput = document.getElementById('vehicleTypeInput');

            logisticsDropdownItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    logisticsInput.value = this.getAttribute('data-value');
                    updateDriverDropdown(this.getAttribute('data-value'));
                    driverNameInput.value = '';
                    driverPhone.value = '';
                    vehicleLicense.value = '';
                    vehicleTypeInput.value = '';
                    driverPhone.removeAttribute('readonly');
                    vehicleLicense.removeAttribute('readonly');
                    vehicleTypeInput.removeAttribute('readonly');
                });
            });

            const vehicleTypeDropdownItems = document.querySelectorAll('#vehicleTypeDropdown .dropdown-item');
            vehicleTypeDropdownItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    vehicleTypeInput.value = this.getAttribute('data-value');
                });
            });

            function updateDriverDropdown(logisticsCompany) {
                const driverDropdown = document.getElementById('driverDropdown');
                driverDropdown.innerHTML = '';

                if (driversData[logisticsCompany]) {
                    driversData[logisticsCompany].forEach(driver => {
                        const li = document.createElement('li');
                        const a = document.createElement('a');
                        a.className = 'dropdown-item';
                        a.href = '#';
                        a.setAttribute('data-driver', JSON.stringify(driver));
                        a.textContent = driver.name;
                        li.appendChild(a);
                        driverDropdown.appendChild(li);

                        a.addEventListener('click', function(e) {
                            e.preventDefault();
                            const driverData = JSON.parse(this.getAttribute('data-driver'));
                            driverNameInput.value = driverData.name;
                            document.getElementById('countryCode').value = '+62';
                            driverPhone.value = driverData.phone;
                            vehicleLicense.value = driverData.license;
                            vehicleTypeInput.value = driverData.vehicle_type;
                            driverPhone.setAttribute('readonly', true);
                            vehicleLicense.setAttribute('readonly', true);
                            vehicleTypeInput.setAttribute('readonly', true);
                        });
                    });
                }
            }

            logisticsInput.addEventListener('input', function() {
                const existingCompany = Array.from(logisticsDropdownItems).find(
                    item => item.getAttribute('data-value') === this.value
                );

                if (!existingCompany) {
                    document.getElementById('driverDropdown').innerHTML = '';
                    driverNameInput.value = '';
                    driverPhone.value = '';
                    vehicleLicense.value = '';
                    vehicleTypeInput.value = '';
                    driverPhone.removeAttribute('readonly');
                    vehicleLicense.removeAttribute('readonly');
                    vehicleTypeInput.removeAttribute('readonly');
                }
            });

            driverNameInput.addEventListener('input', function() {
                const currentLogistics = logisticsInput.value;
                if (driversData[currentLogistics]) {
                    const existingDriver = driversData[currentLogistics].find(
                        driver => driver.name === this.value
                    );

                    if (!existingDriver) {
                        driverPhone.removeAttribute('readonly');
                        vehicleLicense.removeAttribute('readonly');
                        vehicleTypeInput.removeAttribute('readonly');
                    }
                }
            });

            // Add item functionality
            addItemBtn.addEventListener('click', function() {
                const template = itemTemplate.content.cloneNode(true);
                const row = template.querySelector('.delivery-item');

                row.innerHTML = row.innerHTML.replace(/INDEX/g, itemIndex);
                deliveryItemsContainer.appendChild(row);

                noItemsPlaceholder.style.display = 'none';

                const removeBtn = row.querySelector('.remove-item-btn');
                removeBtn.addEventListener('click', function() {
                    row.remove();
                    if (deliveryItemsContainer.children.length === 0) {
                        noItemsPlaceholder.style.display = 'block';
                    }
                });

                const palletSelect = row.querySelector('.pallet-select');
                const quantityInput = row.querySelector('.quantity-input');
                const stockWarning = row.querySelector('.stock-warning');

                function validateStock() {
                    const selectedOption = palletSelect.options[palletSelect.selectedIndex];
                    const stock = parseInt(selectedOption.getAttribute('data-stock')) || 0;
                    const quantity = parseInt(quantityInput.value) || 0;

                    if (quantity > stock && stock > 0) {
                        stockWarning.textContent = `⚠️ Warning: Only ${stock} pcs available in stock!`;
                        stockWarning.style.display = 'block';
                        quantityInput.classList.add('is-invalid');
                    } else {
                        stockWarning.style.display = 'none';
                        quantityInput.classList.remove('is-invalid');
                    }
                }

                palletSelect.addEventListener('change', validateStock);
                quantityInput.addEventListener('input', validateStock);

                itemIndex++;
                setStockItemToSend();
            });

            // Add one item by default ONLY if not in prefilled mode
            if (!prefilledMode) {
                addItemBtn.click();
            }

            // Form submission and other handlers...
            let isConfirmedSubmit = false;

            document.getElementById('deliveryNoteForm').addEventListener('submit', function(e) {
                if (deliveryItemsContainer.children.length === 0) {
                    e.preventDefault();
                    alert('Please add at least one delivery item.');
                    return false;
                }

                if (!isConfirmedSubmit) {
                    e.preventDefault();
                    showConfirmationModal('submit');
                }
            });

            document.getElementById('confirmSubmitBtn').addEventListener('click', function() {
                isConfirmedSubmit = true;
                const modal = bootstrap.Modal.getInstance(document.getElementById('confirmationModal'));
                modal.hide();
                document.getElementById('deliveryNoteForm').submit();
            });

            if (resetBtn) {
                resetBtn.addEventListener('click', function() {
                    if (confirm('{{__("Are you sure you want to reset the form? All entered data will be lost.")}}')) {
                        document.getElementById('deliveryNoteForm').reset();
                        deliveryItemsContainer.innerHTML = '';
                        noItemsPlaceholder.style.display = 'block';

                        fromAddress.value = '';
                        fromCity.value = '';
                        fromCkdwh.value = '';
                        toAddress.value = '';
                        toCity.value = '';
                        toCkdwh.value = '';

                        driverPhone.removeAttribute('readonly');
                        vehicleLicense.removeAttribute('readonly');
                        vehicleTypeInput.removeAttribute('readonly');

                        containerNumberGroup.classList.add('d-none');
                        sealNumberGroup.classList.add('d-none');

                        document.getElementById('fromCustomerNid').value = '';
                        document.getElementById('fromCustomerKd').value = '';
                        document.getElementById('fromCustomerName').value = '';
                        document.getElementById('toCustomerNid').value = '';
                        document.getElementById('toCustomerKd').value = '';
                        document.getElementById('toCustomerName').value = '';

                        if (isAdmin) {
                            fromAddressSelect.innerHTML = '<option value="">{{__("Select sender first")}}</option>';
                        }
                        toCustomerNetwork.innerHTML = '<option value="">{{__("Select sender first")}}</option>';
                        toAddressSelect.innerHTML = '<option value="">{{__("Select receiver first")}}</option>';

                        if (!isAdmin && fromEntity.value) {
                            loadCustomerNetwork(fromEntity.value);
                        }
                    }
                });
            }

            if (createPrintBtn) {
                createPrintBtn.addEventListener('click', function() {
                    if (deliveryItemsContainer.children.length === 0) {
                        alert('Please add at least one delivery item.');
                        return;
                    }

                    const form = document.getElementById('deliveryNoteForm');

                    if (!form.checkValidity()) {
                        form.reportValidity();
                        return;
                    }

                    showConfirmationModal('print');
                });
            }

            function generatePrintContent(copies) {
                const printContainer = document.getElementById('printContainer');
                printContainer.innerHTML = '';

                for (let i = 0; i < copies; i++) {
                    printContainer.innerHTML += generateSingleDeliveryNote();
                }
            }

            // Generate single delivery note
            function generateSingleDeliveryNote() {
                const deliveryNoteNumber = document.getElementById('deliveryNoteNumber').value;
                const deliveryDateVal = document.getElementById('deliveryDate').value;
                const deliveryNotesVal = document.getElementById('deliveryNotes').value;
                const fromCustomerText = fromEntity.options[fromEntity.selectedIndex]?.text || '';
                const fromAddressVal = fromAddress.value;
                const fromCityVal = fromCity.value;
                const toCustomerText = toCustomerNetwork.options[toCustomerNetwork.selectedIndex]?.text || '';
                const toAddressVal = toAddress.value;
                const toCityVal = toCity.value;
                const logisticsCompany = logisticsInput.value;
                const driverName = driverNameInput.value;
                const driverPhoneVal = driverPhone.value;
                const countryCode = document.getElementById('countryCode').value;
                const vehicleLicenseVal = vehicleLicense.value;
                const vehicleTypeVal = vehicleTypeInput.value;
                const isExportChecked = isExport.checked;
                const containerNumberVal = document.getElementById('containerNumber').value;
                const sealNumberVal = document.getElementById('sealNumber').value;

                // Get items
                const items = [];
                const rows = deliveryItemsContainer.querySelectorAll('.delivery-item');
                rows.forEach(row => {
                    const palletSelect = row.querySelector('.pallet-select');
                    const quantity = row.querySelector('.quantity-input').value;
                    items.push({
                        pallet: palletSelect.options[palletSelect.selectedIndex].text.split(' - ')[
                            0],
                        quantity: quantity
                    });
                });

                // Generate items HTML
                let itemsHtml = '';
                let totalQty = 0;
                items.forEach((item, index) => {
                    const qty = parseInt(item.quantity);
                    totalQty += qty;
                    itemsHtml += `
                    <tr>
                        <td style="text-align: center;">${index + 1}</td>
                        <td>${item.pallet}</td>
                        <td style="text-align: center;">${qty}</td>
                    </tr>
                `;
                });

                // Format date
                const dateObj = new Date(deliveryDateVal);
                const formattedDate = dateObj.toLocaleDateString('en-GB', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric'
                });

                return `
                <div class="delivery-note-print">
                    <div class="header">
                        <div class="header-content">
                            <img src="/images/logo-bw.png" alt="Yanarent Logo" class="company-logo">
                            <div class="company-name">YANARENT</div>
                        </div>
                    </div>

                    <div class="document-title">DELIVERY NOTE</div>

                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 8pt;">
                        <div><strong>DN Number:</strong> ${deliveryNoteNumber}</div>
                        <div><strong>Date:</strong> ${formattedDate}</div>
                    </div>

                    <div class="section-title">Shipment Details</div>
                    <div class="two-column">
                        <div class="info-box">
                            <div class="info-box-title">SENDER</div>
                            <div style="font-size: 8pt; margin-bottom: 2px; font-weight: bold;">${fromCustomerText}</div>
                            <div style="font-size: 8pt;">${fromAddressVal}</div>
                            <div style="font-size: 8pt; font-weight: bold;">${fromCityVal}</div>
                        </div>
                        <div class="info-box">
                            <div class="info-box-title">RECEIVER</div>
                            <div style="font-size: 8pt; margin-bottom: 2px; font-weight: bold;">${toCustomerText}</div>
                            <div style="font-size: 8pt;">${toAddressVal}</div>
                            <div style="font-size: 8pt; font-weight: bold;">${toCityVal}</div>
                        </div>
                    </div>

                    <div class="section-title">Logistics Information</div>
                    <div style="border: 1px solid #000; padding: 5px; margin-bottom: 8px;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 8pt;">
                            <div>
                                <strong>Logistics Company:</strong><br>
                                ${logisticsCompany}
                            </div>
                            <div>
                                <strong>Driver Name:</strong><br>
                                ${driverName}
                            </div>
                            <div>
                                <strong>Driver Phone:</strong><br>
                                ${countryCode}${driverPhoneVal}
                            </div>
                            <div>
                                <strong>Vehicle Type:</strong><br>
                                ${vehicleTypeVal}
                            </div>
                            <div>
                                <strong>Vehicle License:</strong><br>
                                ${vehicleLicenseVal}
                            </div>
                            ${isExportChecked ? `
                                                                                                                        <div>
                                                                                                                            <strong>Container No.:</strong><br>
                                                                                                                            ${containerNumberVal || '-'}
                                                                                                                        </div>
                                                                                                                        <div>
                                                                                                                            <strong>Seal No.:</strong><br>
                                                                                                                            ${sealNumberVal || '-'}
                                                                                                                        </div>
                                                                                                                    ` : ''}
                        </div>
                    </div>

                    ${deliveryNotesVal ? `
                                                                                                                                                        <div class="section-title">Notes</div>
                                                                                                                                                        <div style="font-size: 8pt; margin-bottom: 8px; padding: 3px 5px; border: 1px solid #ccc;">${deliveryNotesVal}</div>
                                                                                                                                                    ` : ''}

                    <div class="section-title">Delivery Items</div>
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 8%; text-align: center;">No</th>
                                <th style="width: 72%;">Pallet Type</th>
                                <th style="width: 20%; text-align: center;">Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${itemsHtml}
                            <tr style="font-weight: bold;">
                                <td colspan="2" style="text-align: right;">TOTAL:</td>
                                <td style="text-align: center;">${totalQty} pcs</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="signature-section">
                        <div class="signature-box">
                            <div class="title">Sender</div>
                            <div class="subtitle">Pengirim</div>
                            <div class="signature-line">
                                (________________)
                            </div>
                        </div>
                        <div class="signature-box">
                            <div class="title">Driver</div>
                            <div class="subtitle">Supir</div>
                            <div class="signature-line">
                                ${driverName}
                            </div>
                        </div>
                        <div class="signature-box">
                            <div class="title">Receiver</div>
                            <div class="subtitle">Penerima</div>
                            <div class="signature-line">
                                (________________)
                            </div>
                        </div>
                    </div>

                    <div class="footer-note">
                        This document is electronically generated and valid without wet signature
                    </div>
                </div>
            `;
            }

            const today = new Date();
            deliveryDate.value = today.toISOString().split('T')[0];

            driverPhone.addEventListener('input', function() {
                let value = this.value.replace(/\D/g, '');
                if (value.startsWith('0')) {
                    value = value.substring(1);
                }
                this.value = value;
            });

            vehicleLicense.addEventListener('input', function() {
                this.value = this.value.toUpperCase();
            });

            const containerNumber = document.getElementById('containerNumber');
            if (containerNumber) {
                containerNumber.addEventListener('input', function() {
                    this.value = this.value.toUpperCase();
                });
            }

            const sealNumber = document.getElementById('sealNumber');
            if (sealNumber) {
                sealNumber.addEventListener('input', function() {
                    this.value = this.value.toUpperCase();
                });
            }

            // Show Confirmation Modal Function
            function showConfirmationModal(action) {
                // Populate modal with form data
                const deliveryNoteNumber = document.getElementById('deliveryNoteNumber').value;
                const deliveryDateVal = document.getElementById('deliveryDate').value;
                const deliveryNotesVal = document.getElementById('deliveryNotes').value;
                const fromCustomerText = fromEntity.options[fromEntity.selectedIndex]?.text || '';
                const fromAddressText = fromAddressSelect.options[fromAddressSelect.selectedIndex]?.text || '';
                const fromAddressVal = fromAddress.value;
                const fromCityVal = fromCity.value;
                const toCustomerText = toCustomerNetwork.options[toCustomerNetwork.selectedIndex]?.text || '';
                const toAddressText = toAddressSelect.options[toAddressSelect.selectedIndex]?.text || '';
                const toAddressVal = toAddress.value;
                const toCityVal = toCity.value;
                const logisticsCompany = logisticsInput.value;
                const driverName = driverNameInput.value;
                const driverPhoneVal = driverPhone.value;
                const countryCode = document.getElementById('countryCode').value;
                const vehicleTypeVal = vehicleTypeInput.value;
                const vehicleLicenseVal = vehicleLicense.value;
                const isExportChecked = isExport.checked;
                const containerNumberVal = document.getElementById('containerNumber').value;
                const sealNumberVal = document.getElementById('sealNumber').value;

                // Format date
                const dateObj = new Date(deliveryDateVal);
                const formattedDate = dateObj.toLocaleDateString('en-GB', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric'
                });

                // Fill in delivery information
                document.getElementById('confirm-dn-number').textContent = deliveryNoteNumber;
                document.getElementById('confirm-delivery-date').textContent = formattedDate;
                document.getElementById('confirm-delivery-notes').textContent = deliveryNotesVal || '-';

                // Fill in shipment details
                document.getElementById('confirm-sender').textContent = fromCustomerText;
                document.getElementById('confirm-sender-address').textContent = fromAddressVal + (fromCityVal ?
                    ' (' + fromCityVal + ')' : '');
                document.getElementById('confirm-receiver').textContent = toCustomerText;
                document.getElementById('confirm-receiver-address').textContent = toAddressVal + (toCityVal ? ' (' +
                    toCityVal + ')' : '');

                // Fill in logistics information
                document.getElementById('confirm-logistics-company').textContent = logisticsCompany;
                document.getElementById('confirm-driver-name').textContent = driverName;
                document.getElementById('confirm-driver-phone').textContent = countryCode + driverPhoneVal;
                document.getElementById('confirm-vehicle-type').textContent = vehicleTypeVal;
                document.getElementById('confirm-vehicle-license').textContent = vehicleLicenseVal;

                // Handle container fields
                if (isExportChecked) {
                    document.getElementById('confirm-container-row').style.display = '';
                    document.getElementById('confirm-seal-row').style.display = '';
                    document.getElementById('confirm-container-number').textContent = containerNumberVal || '-';
                    document.getElementById('confirm-seal-number').textContent = sealNumberVal || '-';
                } else {
                    document.getElementById('confirm-container-row').style.display = 'none';
                    document.getElementById('confirm-seal-row').style.display = 'none';
                }

                // Fill in delivery items
                const itemsList = document.getElementById('confirm-items-list');
                itemsList.innerHTML = '';
                let totalQty = 0;

                const rows = deliveryItemsContainer.querySelectorAll('.delivery-item');
                rows.forEach((row, index) => {
                    const palletSelect = row.querySelector('.pallet-select');
                    const quantity = row.querySelector('.quantity-input').value;
                    const qty = parseInt(quantity);
                    totalQty += qty;

                    const tr = document.createElement('tr');
                    tr.innerHTML = `
            <td>${index + 1}</td>
            <td>${palletSelect.options[palletSelect.selectedIndex].text}</td>
            <td>${qty} pcs</td>
        `;
                    itemsList.appendChild(tr);
                });

                document.getElementById('confirm-total-qty').textContent = totalQty + ' pcs';

                // Update confirm button based on action
                const confirmBtn = document.getElementById('confirmSubmitBtn');
                if (action === 'print') {
                    confirmBtn.innerHTML = '<i class="bi bi-printer me-1"></i>Confirm & Print';
                    confirmBtn.onclick = function() {
                        const modalElement = document.getElementById('confirmationModal');
                        const modal = bootstrap.Modal.getInstance(modalElement);

                        const form = document.getElementById('deliveryNoteForm');
                        const existingAction = form.querySelector('input[name="action"][value="save_and_print"]');
                        if (!existingAction) {
                            const printInput = document.createElement('input');
                            printInput.type = 'hidden';
                            printInput.name = 'action';
                            printInput.value = 'save_and_print';
                            form.appendChild(printInput);
                        }

                        isConfirmedSubmit = true;
                        form.submit();

                        // Hide modal AFTER submit starts
                        modal.hide();
                    };
                } else {
                    confirmBtn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Confirm & Submit';
                    confirmBtn.onclick = function() {
                        isConfirmedSubmit = true;
                        const modal = bootstrap.Modal.getInstance(document.getElementById('confirmationModal'));
                        modal.hide();
                        document.getElementById('deliveryNoteForm').submit();
                    };
                }

                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('confirmationModal'));
                modal.show();
            }
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // =========================================================================
            // NEW DELIVERY NOTE TOUR
            // =========================================================================
            window.startNewDeliveryNoteTour = function() {
                const driver = window.driver.js.driver;

                const driverObj = driver({
                    showProgress: true,
                    showButtons: ['next', 'previous', 'close'],
                    steps: [{
                            popover: {
                                title: '📝 {{__("Create New Delivery Note")}}',
                                description: '{{__('This form is used to record a new delivery of pallets. Let’s go through the key steps in the correct order.')}}'
                            }
                        },
                        {
                            element: '#deliveryNoteNumber',
                            popover: {
                                title: '📄 {{__("Delivery Note Number")}}',
                                description: '{{__("This number is generated automatically by the system and cannot be edited.")}}',
                                side: 'bottom',
                                align: 'start'
                            }
                        },
                        {
                            element: '#deliveryDate',
                            popover: {
                                title: '📅 {{__("Delivery Date")}}',
                                description: '{{__("Select the date when the delivery is dispatched.")}}',
                                side: 'bottom',
                                align: 'start'
                            }
                        },
                        {
                            element: '#fromEntity',
                            popover: {
                                title: '🏭 {{__("Sender")}}',
                                description: '{{ __("Choose who is sending the pallets.") }}<br><br>{{ __("This can be a company warehouse or a customer, depending on your role.") }}',
                                side: 'bottom',
                                align: 'start'
                            }
                        },
                        {
                            element: '#fromAddressSelect',
                            popover: {
                                title: '📍 {{__("Sender Address")}}',
                                description: '{{ __("Select the warehouse or address where the pallets are dispatched from.") }}',
                                side: 'bottom',
                                align: 'start'
                            }
                        },
                        {
                            element: '#toCustomerNetwork',
                            popover: {
                                title: '👤 {{__("Receiver")}}',
                                description: '{{ __("Select the customer who will receive the pallets.") }}<br><br>{{ __("Available receivers depend on the selected sender and agreements.") }}',
                                side: 'bottom',
                                align: 'start'
                            }
                        },
                        {
                            element: '#toAddressSelect',
                            popover: {
                                title: '📍 {{__("Receiver Address")}}',
                                description: '{{ __("Choose the destination address for this delivery.") }}',
                                side: 'bottom',
                                align: 'start'
                            }
                        },
                        {
                            element: '#logisticsCompanyInput',
                            popover: {
                                title: '🚚 {{__("Logistics Company")}}',
                                description: '{{ __("Enter or select the logistics company handling this delivery.") }}',
                                side: 'bottom',
                                align: 'start'
                            }
                        },
                        {
                            element: '#driverNameInput',
                            popover: {
                                title: '🧑‍✈️ {{__("Driver Information")}}',
                                description: '{{ __("Select an existing driver or enter a new one. Driver phone and vehicle details may auto-fill.") }}',
                                side: 'bottom',
                                align: 'start'
                            }
                        },
                        {
                            element: '#isExport',
                            popover: {
                                title: '📦 {{__("Container Shipment")}}',
                                description: '{{ __("Enable this if the delivery uses a container. Additional container and seal fields will appear.") }}',
                                side: 'right',
                                align: 'start'
                            }
                        },
                        {
                            element: '#addItemBtn',
                            popover: {
                                title: '➕ {{__("Delivery Items")}}',
                                description: '{{ __("Add pallet types and quantities to be delivered.") }}<br><br>{{ __("At least one item is required.") }}',
                                side: 'left',
                                align: 'start'
                            }
                        },
                        {
                            element: '#deliveryItemsTable',
                            popover: {
                                title: '📦 {{__("Item Details")}}',
                                description: '{{ __("Each row represents a pallet type and quantity being delivered.") }}',
                                side: 'top',
                                align: 'start'
                            }
                        },
                        {
                            element: '#btnCreateDelivery',
                            popover: {
                                title: '✅ {{__("Create Delivery Note")}}',
                                description: '{{ __("Save the delivery note after reviewing all information.") }}<br><br>{{ __("You will be asked to confirm before submission.") }}',
                                side: 'top',
                                align: 'end'
                            }
                        },
                        {
                            element: '#btnCreateAndPrint',
                            popover: {
                                title: '🖨️ {{__("Create & Print")}}',
                                description: '{{ __("Save the delivery note and immediately print the document in one step.") }}',
                                side: 'top',
                                align: 'end'
                            }
                        },
                        {
                            popover: {
                                title: '🎉 {{__("You’re Ready!")}}',
                                description: '{{ __("You now know how to create a delivery note.") }}<br><br><strong>{{__("Tip:")}}</strong> {{ __("Fill the form from top to bottom for the smoothest experience.") }}'
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
            window.startProductTour = window.startNewDeliveryNoteTour;

        });
    </script>
@endpush
