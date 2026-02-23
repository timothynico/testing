<?php

namespace App\Http\Controllers;

use App\Models\DirectAgreement;
use App\Models\NetworkAgreement;
use App\Models\Customer;
use App\Models\CustomerWarehouse;
use App\Models\Delivery;
use App\Mail\RequestRescheduleMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DeliveryController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $isAdmin = $user->isAdmin();

        // Base delivery query
        $query = Delivery::with('items')->orderByDesc('created_at');

        if (!$isAdmin) {
            if ($user->role === 'warehouseys' && $user->ckdcomp) {
                $query->where(function ($q) use ($user) {
                    $q->where('ckdcust_from', $user->ckdcomp)
                        ->orWhere('ckdcust_to', $user->ckdcomp);
                });
            } elseif ($user->ckdcust) {
                $query->where(function ($q) use ($user) {
                    $q->where('ckdcust_from', $user->ckdcust)
                        ->orWhere('ckdcust_to', $user->ckdcust);
                });
            }
        }

        $deliveries = $query->get();

        //Load mbasic pallet data for ALL items
        $allPalletCodes = $deliveries
            ->flatMap(fn($d) => $d->items->pluck('cpallet_type'))
            ->filter()
            ->unique()
            ->values();

        $mbasicMap = collect();

        if ($allPalletCodes->isNotEmpty()) {
            $mbasicMap = DB::table('mbasic')
                ->whereIn('ckdbrg', $allPalletCodes->all())
                ->get()
                ->keyBy('ckdbrg');
        }

        // Attach mbasic data into each item
        $deliveries->each(function ($delivery) use ($mbasicMap) {
            $delivery->setRelation('items', $delivery->items->map(function ($item) use ($mbasicMap) {
                $item->setAttribute(
                    'mbasic',
                    $mbasicMap->get($item->cpallet_type) ?? null
                );
                return $item;
            }));
        });

        $deliveriesJson = $deliveries->map(function ($d) {
            return [
                'id' => $d->nidsj,
                'delivery_note_number' => $d->cnosj,
                'delivery_date' => $d->dtgl_ship?->format('Y-m-d'),
                'est_arrival_date' => $d->dtgl_eta?->format('Y-m-d'),
                'from_customer' => $d->ccust_from,
                'from_city' => $d->ccity_from,
                'to_customer' => $d->ccust_to,
                'to_city' => $d->ccity_to,
                'logistics_company' => $d->clogistics,
                'driver_name' => $d->cdriver_name,
                'vehicle_license' => $d->cnoplat,
                'status' => $d->cstatus,
                'from_type' => $d->cfrom_type ?? 'customer',
                'to_type' => $d->cto_type ?? 'customer',

                'items' => $d->items->map(fn($item) => [
                    'product_code' => $item->cpallet_type,
                    'product_name' => $item->mbasic->cbasic
                        ?? $item->mbasic->cname
                        ?? $item->cpallet_type,
                    'quantity' => $item->nqty,
                ]),
            ];
        });

        // Build goods receipt query based on user role
        // $grQuery = DB::table('ytbpbhdr');

        // if (!$isAdmin) {
        //     if ($user->role === 'warehouseys' && $user->ckdcomp) {
        //         // YanaSurya warehouse staff: see receipts where their company is sender OR receiver
        //         $grQuery->where(function($q) use ($user) {
        //             $q->where('ckdcust_from', $user->ckdcomp)
        //               ->orWhere('ckdcust_to', $user->ckdcomp);
        //         });
        //     } elseif ($user->ckdcust) {
        //         // Customer users: see receipts where their customer is sender OR receiver
        //         $grQuery->where(function($q) use ($user) {
        //             $q->where('ckdcust_from', $user->ckdcust)
        //                 ->orWhere('ckdcust_to', $user->ckdcust);
        //         });
        //     }
        // }
        // // Admin sees all goods receipts
        // $goodReceipts = $grQuery->get()
        //     ->map(function ($goodReceipt) {
        //         $goodReceipt->items = DB::table('ytbpbdtl')
        //             ->where('cno_grn', $goodReceipt->cno_grn)
        //             ->get();
        //         return $goodReceipt;
        //     });

        return view('transaction.delivery.index', compact('deliveriesJson'));
    }


    public function create(): View
    {
        $isCreatedFromRequest = false;
        $user = Auth::user();

        // For non-admin users, generate DN number immediately
        // For admin users, it will be generated dynamically via JavaScript
        $nosj = '';
        if (!$user->isAdmin()) {
            if ($user->role === 'warehouseys' && $user->ckdcomp) {
                // YanaSurya warehouse user - use company's default code (YS)
                $nosj = Delivery::generateNosj('YS');
            } elseif ($user->ckdcust) {
                // Customer user - use customer's ckodea
                $ckodea = DB::table('ymcust')
                    ->where('ckdcust', $user->ckdcust)
                    ->value('ckodea');
                $nosj = Delivery::generateNosj($ckodea);
            }
        }

        // Get all active logistic companies with their drivers
        $q = \App\Models\LogisticCompany::with(['drivers' => function ($query) {
            $query->where('lnonaktif', 0); // Only active drivers
        }])
            ->where('lnonaktif', 0) // Only active companies
            ->orderBy('cnmlogistic', 'asc');
        // ->get();

        if (AUTH::user()->role == "customer") {
            $q->whereIn('ckdlogistic', function ($subquery) {
                $subquery->select('ckdlogistic')
                    ->from('ymlogisticcust')
                    ->where('ckdcust', AUTH::user()->ckdcust);
            });
        }

        $logisticCompanies = $q->get();
        // Transform to array for JavaScript
        $logisticsData = $logisticCompanies->mapWithKeys(function ($company) {
            return [
                $company->cnmlogistic => $company->drivers->map(function ($driver) {
                    return [
                        'name' => $driver->cnmdriver,
                        'phone' => str_replace('+62', '', $driver->cphone),
                        'vehicle' => $driver->cvehicle,
                    ];
                })->toArray()
            ];
        });

        // Prepare sender options (customers and companies) based on user role
        // Logic:
        // 1. Admin: Can see all companies + all customers, all addresses selectable
        // 2. Customer (finance/others): Can see their customer only, all their warehouses selectable
        // 3. Customer (warehouse_pic with ckdwh): Can see their customer only, address locked to their ckdwh
        // 4. YanaSurya warehouse (warehouseys): Can see their company only, address locked to their ckdwhcomp
        $isAdmin = $user->isAdmin();
        $fromCustomers = collect();
        $fromCompanies = collect();
        $userCustomer = null;
        $userCustomerAddresses = [];
        $userWarehouse = null;
        $isWarehousePic = false; // Customer's warehouse_pic with locked address
        $isCompanyWarehouse = false; // YanaSurya warehouse staff with locked address
        $isCustomerUser = false;
        $canCreateDelivery = false;

        if ($isAdmin) {
            $canCreateDelivery = true;
            // Admin: Get all active companies with warehouses
            $fromCompanies = \App\Models\Company::with(['warehouses' => function ($query) {
                $query->where('lnonaktif', false);
            }])
                ->where('lnonaktif', false)
                ->orderBy('cnmcomp')
                ->get()
                ->map(function ($company) {
                    return [
                        'type' => 'company',
                        'ckdcomp' => $company->ckdcomp,
                        'cnmcomp' => $company->cnmcomp,
                        'warehouses' => $company->warehouses->map(function ($wh) {
                            return [
                                'ckdwh' => $wh->ckdwh,
                                'cnmwh' => $wh->cnmwh,
                                'calmtwh' => $wh->calmtwh,
                                'ckotawh' => $wh->ckotawh,
                            ];
                        })->toArray()
                    ];
                });

            // Admin: Get all active customers
            $fromCustomers = Customer::where('lnonaktif', false)
                ->orderBy('cnmcust')
                ->get()
                ->map(fn($c) => [
                    'type' => 'customer',
                    'nidcust' => $c->nidcust,
                    'ckdcust' => $c->ckdcust,
                    'cnmcust' => $c->cnmcust,
                ]);
        } elseif ($user->role === 'warehouseys' && $user->ckdcomp) {
            // YanaSurya warehouse staff: locked to their company and warehouse
            $isCompanyWarehouse = true;
            $canCreateDelivery = true;

            // Get the company
            $userCompany = \App\Models\Company::where('ckdcomp', $user->ckdcomp)->first();
            if ($userCompany) {
                // Get the locked warehouse using ckdwhcomp
                $userWarehouse = \App\Models\CompanyWarehouse::where('ckdwh', $user->ckdwhcomp)->first();

                $fromCompanies = collect([[
                    'type' => 'company',
                    'ckdcomp' => $userCompany->ckdcomp,
                    'cnmcomp' => $userCompany->cnmcomp,
                    'warehouses' => $userWarehouse ? [[
                        'ckdwh' => $userWarehouse->ckdwh,
                        'cnmwh' => $userWarehouse->cnmwh,
                        'calmtwh' => $userWarehouse->calmtwh,
                        'ckotawh' => $userWarehouse->ckotawh,
                    ]] : []
                ]]);

                // Preload warehouse address for the view
                if ($userWarehouse) {
                    $userCustomerAddresses = [[
                        'type' => 'warehouse',
                        'label' => ($userWarehouse->cnmwh ?: 'Warehouse') . ' - ' . $userWarehouse->ckotawh,
                        'address' => $userWarehouse->calmtwh,
                        'city' => $userWarehouse->ckotawh,
                        'ckdwh' => $userWarehouse->ckdwh,
                    ]];
                }
            }
        } elseif ($user->nidcust || $user->ckdcust) {
            // Customer user: Get their own customer record
            $isCustomerUser = true;
            $canCreateDelivery = true;
            $userCustomer = Customer::where('nidcust', $user->nidcust)
                ->orWhere('ckdcust', $user->ckdcust)
                ->first();

            if ($userCustomer) {
                $fromCustomers = collect([[
                    'type' => 'customer',
                    'nidcust' => $userCustomer->nidcust,
                    'ckdcust' => $userCustomer->ckdcust,
                    'cnmcust' => $userCustomer->cnmcust,
                ]]);

                // Check if warehouse_pic with specific ckdwh (locked address)
                if ($user->customer_role === 'warehouse_pic' && $user->ckdwh) {
                    $isWarehousePic = true;
                    // Get only the locked warehouse address
                    $userWarehouse = \App\Models\CustomerWarehouse::where('ckdwh', $user->ckdwh)->first();
                    if ($userWarehouse) {
                        $userCustomerAddresses = [[
                            'type' => 'warehouse',
                            'label' => ($userWarehouse->cnmwh ?: 'Warehouse') . ' - ' . $userWarehouse->ckotawh,
                            'address' => $userWarehouse->calmtwh,
                            'city' => $userWarehouse->ckotawh,
                            'ckdwh' => $userWarehouse->ckdwh,
                        ]];
                    }
                } else {
                    // Other customer roles (finance, etc.): load all customer addresses
                    $userCustomerAddresses = $this->getCustomerAddressesArray($userCustomer->nidcust);
                }
            }
        }
        // else: Users without proper assignment cannot create deliveries
        $cthnbln = date('Ym');
        $arrbarang = array();
        if ($isAdmin) {
            $rowdatabarang = DB::select("SELECT SUM(st.nqty) as nqty, st.ckdwh, bsc.ckdbrg, st.cstatus as cstatus, bsc.cbasic, bsc.cgrade, bsc.cwarna, bsc.cmaterial, bsc.nmd, bsc.nmr
                        FROM mstock as st
                        JOIN mbasic as bsc ON st.ckdbrg=bsc.ckdbrg
                        WHERE st.cthnbln='$cthnbln'
                        GROUP BY st.cstatus,  st.ckdwh, bsc.cbasic, bsc.cgrade, bsc.cwarna, bsc.cmaterial, bsc.nmd, bsc.nmr, bsc.ckdbrg");
        } elseif ($user->role === 'warehouseys' && $user->ckdcomp) {
            // YanaSurya warehouse staff: query stock by company code (ctempat = ckdcomp)
            $ckdcomp = $user->ckdcomp;
            $rowdatabarang = DB::select("SELECT SUM(st.nqty) as nqty, st.ckdwh, bsc.ckdbrg, st.cstatus as cstatus, bsc.cbasic, bsc.cgrade, bsc.cwarna, bsc.cmaterial, bsc.nmd, bsc.nmr
                        FROM mstock as st
                        JOIN mbasic as bsc ON st.ckdbrg=bsc.ckdbrg
                        WHERE st.ctempat=? and st.cthnbln=?
                        GROUP BY st.cstatus,  st.ckdwh, bsc.cbasic, bsc.cgrade, bsc.cwarna, bsc.cmaterial, bsc.nmd, bsc.nmr, bsc.ckdbrg", [$ckdcomp, $cthnbln]);
        } else {
            // Customer user: query stock by customer code (ctempat = ckdcust)
            $ckdcust = $user->ckdcust;
            $rowdatabarang = DB::select("SELECT SUM(st.nqty) as nqty, st.ckdwh, bsc.ckdbrg, st.cstatus as cstatus, bsc.cbasic, bsc.cgrade, bsc.cwarna, bsc.cmaterial, bsc.nmd, bsc.nmr
                        FROM mstock as st
                        JOIN mbasic as bsc ON st.ckdbrg=bsc.ckdbrg
                        WHERE st.ctempat=? and st.cthnbln=?
                        GROUP BY st.cstatus,  st.ckdwh, bsc.cbasic, bsc.cgrade, bsc.cwarna, bsc.cmaterial, bsc.nmd, bsc.nmr, bsc.ckdbrg", [$ckdcust, $cthnbln]);
        }

        for ($i = 0; $i < count($rowdatabarang); $i++) {
            $itemDesc = $rowdatabarang[$i]->cbasic;

            if ($rowdatabarang[$i]->cstatus == "A" || $rowdatabarang[$i]->cstatus == "I" || $rowdatabarang[$i]->cstatus == "S") {
                if (!isset($arrbarang[$rowdatabarang[$i]->ckdbrg][$rowdatabarang[$i]->ckdwh])) {
                    $arrbarang[$rowdatabarang[$i]->ckdbrg][$rowdatabarang[$i]->ckdwh]['jumlah'] = 0;
                    $arrbarang[$rowdatabarang[$i]->ckdbrg][$rowdatabarang[$i]->ckdwh]['name'] = $itemDesc;
                }
                $arrbarang[$rowdatabarang[$i]->ckdbrg][$rowdatabarang[$i]->ckdwh]['jumlah'] += $rowdatabarang[$i]->nqty;
            } else if ($rowdatabarang[$i]->cstatus == "O") {
                if (!isset($arrbarang[$rowdatabarang[$i]->ckdbrg]))
                    $arrbarang[$rowdatabarang[$i]->ckdbrg] = array();
                if (!isset($arrbarang[$rowdatabarang[$i]->ckdbrg][$rowdatabarang[$i]->ckdwh])) {
                    $arrbarang[$rowdatabarang[$i]->ckdbrg][$rowdatabarang[$i]->ckdwh]['jumlah'] = 0;
                    $arrbarang[$rowdatabarang[$i]->ckdbrg][$rowdatabarang[$i]->ckdwh]['name'] = $itemDesc;
                }
                $arrbarang[$rowdatabarang[$i]->ckdbrg][$rowdatabarang[$i]->ckdwh]['jumlah'] -= $rowdatabarang[$i]->nqty;
            }
        }

        return view('transaction.delivery.create', compact(
            'nosj',
            'logisticsData',
            'isAdmin',
            'isCustomerUser',
            'isWarehousePic',
            'isCompanyWarehouse',
            'canCreateDelivery',
            'fromCustomers',
            'fromCompanies',
            'userCustomer',
            'userCustomerAddresses',
            'userWarehouse',
            'arrbarang',
            'isCreatedFromRequest'
        ));
    }

    public function create_id($id): View
    {
        $isCreatedFromRequest = true;
        $user = Auth::user();

        // Fetch the pallet request
        $palletRequest = DB::table('tpalletrequest')
            ->where('nid', $id)
            ->first();

        if (!$palletRequest) {
            abort(404, 'Pallet request not found');
        }

        // Check if request is approved
        $latestStatus = DB::table('tpalletrequeststatus')
            ->where('nid_pallet_request', $id)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$latestStatus || $latestStatus->cstatus !== 'Approved') {
            abort(403, 'Only approved requests can be converted to delivery notes');
        }

        // Determine request type
        $isOrderRequest = ($palletRequest->crequest_type === 'Order');

        // Get customer information from company name
        $customer = DB::table('ymcust')
            ->where('cnmcust', $palletRequest->ccompany_name)
            ->where('lnonaktif', false)
            ->first();

        if (!$customer) {
            abort(404, 'Customer not found for company: ' . $palletRequest->ccompany_name);
        }

        // Get pallet ckdbrg from mbasic table
        $palletBasic = DB::table('mbasic')
            ->where('cbasic', $palletRequest->cpallet_type)
            ->first();

        if (!$palletBasic) {
            abort(404, 'Pallet type not found: ' . $palletRequest->cpallet_type);
        }

        // Prepare pre-filled data
        $prefilledData = [
            'pallet_ckdbrg' => $palletBasic->ckdbrg,
            'pallet_name' => $palletRequest->cpallet_type,
            'pallet_size' => $palletRequest->cpallet_size,
            'pallet_color' => $palletRequest->cpallet_color,
            'quantity' => $isOrderRequest ? $palletRequest->nqty : $palletRequest->nqty_return,
        ];

        // Determine sender and receiver based on request type
        if ($isOrderRequest) {
            // ORDER: Sender = Company (YanaSurya), Receiver = Customer

            // Get sender warehouse (company warehouse)
            $senderWarehouse = DB::table('ymcompwarehouse')
                ->where('nidcompwh', $palletRequest->nidwh_from)
                ->where('ckdwh', $palletRequest->ckdwh_from)
                ->where('lnonaktif', false)
                ->first();

            if (!$senderWarehouse) {
                abort(404, 'Sender warehouse not found');
            }

            // Get receiver warehouse (customer warehouse)
            $receiverWarehouse = DB::table('ymcustwarehouse')
                ->where('nidwh', $palletRequest->nidwh_to)
                ->where('ckdwh', $palletRequest->ckdwh_to)
                // ->where('lnonaktif', false)
                ->first();

            if (!$receiverWarehouse) {
                abort(404, 'Receiver warehouse not found');
            }

            // Get company information
            $company = DB::table('ymcomp')
                ->where('ckdcomp', $senderWarehouse->ckdcomp)
                ->first();

            $prefilledData['from_type'] = 'company';
            $prefilledData['from_entity_id'] = 'company_' . $company->ckdcomp;
            $prefilledData['from_entity_name'] = $company->cnmcomp;
            $prefilledData['from_ckd'] = $company->ckdcomp;
            $prefilledData['from_warehouse_ckdwh'] = $senderWarehouse->ckdwh;
            $prefilledData['from_warehouse_name'] = $senderWarehouse->cnmwh;
            $prefilledData['from_warehouse_address'] = $senderWarehouse->calmtwh;
            $prefilledData['from_warehouse_city'] = $senderWarehouse->ckotawh;
            $prefilledData['from_nidwh'] = $senderWarehouse->nidcompwh;

            $prefilledData['to_type'] = 'customer';
            $prefilledData['to_entity_id'] = $customer->nidcust;
            $prefilledData['to_entity_name'] = $customer->cnmcust;
            $prefilledData['to_ckdcust'] = $customer->ckdcust;
            $prefilledData['to_nidcust'] = $customer->nidcust;
            $prefilledData['to_warehouse_ckdwh'] = $receiverWarehouse->ckdwh;
            $prefilledData['to_warehouse_name'] = $receiverWarehouse->cnmwh;
            $prefilledData['to_warehouse_address'] = $receiverWarehouse->calmtwh;
            $prefilledData['to_warehouse_city'] = $receiverWarehouse->ckotawh;
            $prefilledData['to_nidwh'] = $receiverWarehouse->nidwh;
        } else {
            // RETURN: Sender = Customer, Receiver = Company (YanaSurya)

            // Get sender warehouse (customer warehouse)
            $senderWarehouse = DB::table('ymcustwarehouse')
                ->where('nidwh', $palletRequest->nidwh_from)
                ->where('ckdwh', $palletRequest->ckdwh_from)
                // ->where('lnonaktif', false)
                ->first();

            if (!$senderWarehouse) {
                abort(404, 'Sender warehouse not found');
            }

            // Get receiver warehouse (company warehouse)
            $receiverWarehouse = DB::table('ymcompwarehouse')
                ->where('nidcompwh', $palletRequest->nidwh_to)
                ->where('ckdwh', $palletRequest->ckdwh_to)
                ->where('lnonaktif', false)
                ->first();

            if (!$receiverWarehouse) {
                abort(404, 'Receiver warehouse not found');
            }

            // Get company information
            $company = DB::table('ymcomp')
                ->where('ckdcomp', $receiverWarehouse->ckdcomp)
                ->first();

            $prefilledData['from_type'] = 'customer';
            $prefilledData['from_entity_id'] = 'customer_' . $customer->ckdcust;
            $prefilledData['from_entity_name'] = $customer->cnmcust;
            $prefilledData['from_ckdcust'] = $customer->ckdcust;
            $prefilledData['from_nidcust'] = $customer->nidcust;
            $prefilledData['from_warehouse_ckdwh'] = $senderWarehouse->ckdwh;
            $prefilledData['from_warehouse_name'] = $senderWarehouse->cnmwh;
            $prefilledData['from_warehouse_address'] = $senderWarehouse->calmtwh;
            $prefilledData['from_warehouse_city'] = $senderWarehouse->ckotawh;
            $prefilledData['from_nidwh'] = $senderWarehouse->nidwh;

            $prefilledData['to_type'] = 'company';
            $prefilledData['to_entity_id'] = 'company_' . $company->ckdcomp;
            $prefilledData['to_entity_name'] = $company->cnmcomp;
            $prefilledData['to_ckd'] = $company->ckdcomp;
            $prefilledData['to_warehouse_ckdwh'] = $receiverWarehouse->ckdwh;
            $prefilledData['to_warehouse_name'] = $receiverWarehouse->cnmwh;
            $prefilledData['to_warehouse_address'] = $receiverWarehouse->calmtwh;
            $prefilledData['to_warehouse_city'] = $receiverWarehouse->ckotawh;
            $prefilledData['to_nidwh'] = $receiverWarehouse->nidcompwh;
        }

        // Generate delivery note number based on sender
        if ($prefilledData['from_type'] === 'company') {
            // Use company code (default YS for YanaSurya)
            $nosj = Delivery::generateNosj('YS');
        } else {
            // Use customer's ckodea
            $nosj = Delivery::generateNosj($customer->ckodea);
        }

        // Get all active logistic companies with their drivers (same as create method)
        $q = \App\Models\LogisticCompany::with(['drivers' => function ($query) {
            $query->where('lnonaktif', 0);
        }])
            ->where('lnonaktif', 0)
            ->orderBy('cnmlogistic', 'asc');

        if ($user->role == "customer") {
            $q->whereIn('ckdlogistic', function ($subquery) {
                $subquery->select('ckdlogistic')
                    ->from('ymlogisticcust')
                    ->where('ckdcust', Auth::user()->ckdcust);
            });
        }

        $logisticCompanies = $q->get();
        $logisticsData = $logisticCompanies->mapWithKeys(function ($company) {
            return [
                $company->cnmlogistic => $company->drivers->map(function ($driver) {
                    return [
                        'name' => $driver->cnmdriver,
                        'phone' => str_replace('+62', '', $driver->cphone),
                        'license' => $driver->cplat ?? '',
                        'vehicle_type' => $driver->cvehicle ?? '',
                    ];
                })->toArray()
            ];
        });

        // Prepare user role flags (same as create method)
        $isAdmin = $user->isAdmin();
        $isCustomerUser = in_array($user->role, ['customer']) && ($user->nidcust || $user->ckdcust);
        $isWarehousePic = $user->customer_role === 'warehouse_pic' && $user->ckdwh;
        $isCompanyWarehouse = $user->role === 'warehouseys' && $user->ckdcomp;
        $canCreateDelivery = $isAdmin || $isCustomerUser || $isCompanyWarehouse;

        // Prepare from entities (only the relevant ones based on prefilled data)
        $fromCustomers = collect();
        $fromCompanies = collect();
        $userCustomerAddresses = [];

        if ($prefilledData['from_type'] === 'company') {
            // Sender is company
            $fromCompanies = collect([[
                'type' => 'company',
                'ckdcomp' => $prefilledData['from_ckd'],
                'cnmcomp' => $prefilledData['from_entity_name'],
                'warehouses' => [[
                    'ckdwh' => $prefilledData['from_warehouse_ckdwh'],
                    'cnmwh' => $prefilledData['from_warehouse_name'],
                    'calmtwh' => $prefilledData['from_warehouse_address'],
                    'ckotawh' => $prefilledData['from_warehouse_city'],
                ]]
            ]]);

            $userCustomerAddresses = [[
                'type' => 'warehouse',
                'label' => $prefilledData['from_warehouse_name'] . ' - ' . $prefilledData['from_warehouse_city'],
                'address' => $prefilledData['from_warehouse_address'],
                'city' => $prefilledData['from_warehouse_city'],
                'ckdwh' => $prefilledData['from_warehouse_ckdwh'],
            ]];
        } else {
            // Sender is customer
            $fromCustomers = collect([[
                'type' => 'customer',
                'nidcust' => $prefilledData['from_nidcust'],
                'ckdcust' => $prefilledData['from_ckdcust'],
                'cnmcust' => $prefilledData['from_entity_name'],
            ]]);

            $userCustomerAddresses = [[
                'type' => 'warehouse',
                'label' => $prefilledData['from_warehouse_name'] . ' - ' . $prefilledData['from_warehouse_city'],
                'address' => $prefilledData['from_warehouse_address'],
                'city' => $prefilledData['from_warehouse_city'],
                'ckdwh' => $prefilledData['from_warehouse_ckdwh'],
            ]];
        }

        // Prepare receiver options (for network dropdown)
        $receiverOptions = collect([[
            'nidcust' => $prefilledData['to_type'] === 'customer' ? $prefilledData['to_nidcust'] : null,
            'ckdcust' => $prefilledData['to_type'] === 'customer' ? $prefilledData['to_ckdcust'] : $prefilledData['to_ckd'],
            'cnmcust' => $prefilledData['to_entity_name'],
            'type' => $prefilledData['to_type'],
        ]]);

        // Prepare receiver warehouse options
        $receiverWarehouseOptions = [[
            'type' => 'warehouse',
            'label' => $prefilledData['to_warehouse_name'] . ' - ' . $prefilledData['to_warehouse_city'],
            'address' => $prefilledData['to_warehouse_address'],
            'city' => $prefilledData['to_warehouse_city'],
            'ckdwh' => $prefilledData['to_warehouse_ckdwh'],
        ]];

        // Get stock data (same as create method)
        $cthnbln = date('Ym');
        $arrbarang = array();

        if ($isAdmin) {
            $rowdatabarang = DB::select("SELECT SUM(st.nqty) as nqty, st.ckdwh, bsc.ckdbrg, st.cstatus as cstatus, bsc.cbasic, bsc.cgrade, bsc.cwarna, bsc.cmaterial, bsc.nmd, bsc.nmr
                    FROM mstock as st
                    JOIN mbasic as bsc ON st.ckdbrg=bsc.ckdbrg
                    WHERE st.cthnbln='$cthnbln'
                    GROUP BY st.cstatus,  st.ckdwh, bsc.cbasic, bsc.cgrade, bsc.cwarna, bsc.cmaterial, bsc.nmd, bsc.nmr, bsc.ckdbrg");
        } elseif ($user->role === 'warehouseys' && $user->ckdcomp) {
            $ckdcomp = $user->ckdcomp;
            $rowdatabarang = DB::select("SELECT SUM(st.nqty) as nqty, st.ckdwh, bsc.ckdbrg, st.cstatus as cstatus, bsc.cbasic, bsc.cgrade, bsc.cwarna, bsc.cmaterial, bsc.nmd, bsc.nmr
                    FROM mstock as st
                    JOIN mbasic as bsc ON st.ckdbrg=bsc.ckdbrg
                    WHERE st.ctempat=? and st.cthnbln=?
                    GROUP BY st.cstatus,  st.ckdwh, bsc.cbasic, bsc.cgrade, bsc.cwarna, bsc.cmaterial, bsc.nmd, bsc.nmr, bsc.ckdbrg", [$ckdcomp, $cthnbln]);
        } else {
            $ckdcust = $user->ckdcust;
            $rowdatabarang = DB::select("SELECT SUM(st.nqty) as nqty, st.ckdwh, bsc.ckdbrg, st.cstatus as cstatus, bsc.cbasic, bsc.cgrade, bsc.cwarna, bsc.cmaterial, bsc.nmd, bsc.nmr
                    FROM mstock as st
                    JOIN mbasic as bsc ON st.ckdbrg=bsc.ckdbrg
                    WHERE st.ctempat=? and st.cthnbln=?
                    GROUP BY st.cstatus,  st.ckdwh, bsc.cbasic, bsc.cgrade, bsc.cwarna, bsc.cmaterial, bsc.nmd, bsc.nmr, bsc.ckdbrg", [$ckdcust, $cthnbln]);
        }

        for ($i = 0; $i < count($rowdatabarang); $i++) {
            $itemDesc = $rowdatabarang[$i]->cbasic;

            if ($rowdatabarang[$i]->cstatus == "A" || $rowdatabarang[$i]->cstatus == "I" || $rowdatabarang[$i]->cstatus == "S") {
                if (!isset($arrbarang[$rowdatabarang[$i]->ckdbrg][$rowdatabarang[$i]->ckdwh])) {
                    $arrbarang[$rowdatabarang[$i]->ckdbrg][$rowdatabarang[$i]->ckdwh]['jumlah'] = 0;
                    $arrbarang[$rowdatabarang[$i]->ckdbrg][$rowdatabarang[$i]->ckdwh]['name'] = $itemDesc;
                }
                $arrbarang[$rowdatabarang[$i]->ckdbrg][$rowdatabarang[$i]->ckdwh]['jumlah'] += $rowdatabarang[$i]->nqty;
            } else if ($rowdatabarang[$i]->cstatus == "O") {
                if (!isset($arrbarang[$rowdatabarang[$i]->ckdbrg]))
                    $arrbarang[$rowdatabarang[$i]->ckdbrg] = array();
                if (!isset($arrbarang[$rowdatabarang[$i]->ckdbrg][$rowdatabarang[$i]->ckdwh])) {
                    $arrbarang[$rowdatabarang[$i]->ckdbrg][$rowdatabarang[$i]->ckdwh]['jumlah'] = 0;
                    $arrbarang[$rowdatabarang[$i]->ckdbrg][$rowdatabarang[$i]->ckdwh]['name'] = $itemDesc;
                }
                $arrbarang[$rowdatabarang[$i]->ckdbrg][$rowdatabarang[$i]->ckdwh]['jumlah'] -= $rowdatabarang[$i]->nqty;
            }
        }


        return view('transaction.delivery.create', compact(
            'nosj',
            'logisticsData',
            'isAdmin',
            'isCustomerUser',
            'isWarehousePic',
            'isCompanyWarehouse',
            'canCreateDelivery',
            'fromCustomers',
            'fromCompanies',
            'userCustomerAddresses',
            'arrbarang',
            'prefilledData',
            'receiverOptions',
            'receiverWarehouseOptions',
            'palletRequest',
            'isCreatedFromRequest'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'delivery_date' => 'required|date',
            'delivery_notes' => 'nullable|string',
            'agreement_id' => 'nullable|string|max:50', // NEW: Agreement contract number
            'pallet_request_id' => 'nullable|integer|exists:tpalletrequest,nid',
            // From entity fields (can be customer or company)
            'from_type' => 'required|string|in:customer,company',
            'nidcust_from' => 'nullable|integer', // Nullable for companies
            'ckdcust_from' => 'required|string|max:50', // Can be ckdcust or ckdcomp
            'from_customer_name' => 'required|string|max:255',
            'from_address' => 'required|string',
            'from_city' => 'nullable|string|max:255',
            'from_ckdwh' => 'nullable|string|max:255',
            // To entity fields (can be customer or company)
            'to_type' => 'required|string|in:customer,company',
            'nidcust_to' => 'nullable|integer', // Nullable for companies
            'ckdcust_to' => 'required|string|max:50', // Can be ckdcust or ckdcomp
            'to_customer_name' => 'required|string|max:255',
            'to_address' => 'required|string',
            'to_city' => 'nullable|string|max:255',
            'to_ckdwh' => 'nullable|string|max:255',
            // Logistics fields
            'logistics_company' => 'required|string|max:255',
            'driver_name' => 'required|string|max:255',
            'country_code' => 'required|string|max:10',
            'driver_phone' => 'required|string|max:20',
            'vehicle_license_number' => 'required|string|max:20',
            'is_export' => 'nullable',
            'container_number' => 'nullable|string|max:50',
            'seal_number' => 'nullable|string|max:50',
            'items' => 'required|array|min:1',
            'items.*.ckdbrg' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        // Authorization: Enforce warehouse locking for restricted users
        $user = Auth::user();

        // 1. Check YanaSurya warehouse staff (warehouseys) - must use assigned warehouse only
        if ($user->role === 'warehouseys' && $user->ckdwhcomp) {
            // Verify they're only using their assigned warehouse
            if ($validated['from_ckdwh'] !== $user->ckdwhcomp) {
                return back()->withErrors([
                    'from_address' => 'You are not authorized to create delivery from this warehouse. You can only use your assigned warehouse.'
                ])->withInput();
            }

            // Verify company code matches
            if ($validated['ckdcust_from'] !== $user->ckdcomp) {
                return back()->withErrors([
                    'from_customer_name' => 'You are not authorized to create delivery for this company. You can only create deliveries for your assigned company.'
                ])->withInput();
            }
        }

        // 2. Check customer warehouse_pic - must use assigned warehouse only
        if ($user->role === 'customer' && $user->customer_role === 'warehouse_pic' && $user->ckdwh) {
            // Verify they're only using their assigned warehouse
            if ($validated['from_ckdwh'] !== $user->ckdwh) {
                return back()->withErrors([
                    'from_address' => 'You are not authorized to create delivery from this warehouse. You can only use your assigned warehouse.'
                ])->withInput();
            }

            // Verify customer code matches
            if ($validated['ckdcust_from'] !== $user->ckdcust) {
                return back()->withErrors([
                    'from_customer_name' => 'You are not authorized to create delivery for this customer. You can only create deliveries for your assigned customer.'
                ])->withInput();
            }
        }

        // 3. Check customer (any role) - must use their own customer only
        if ($user->role === 'customer' && $user->ckdcust && $user->customer_role !== 'warehouse_pic') {
            // Verify customer code matches (can use any warehouse, but must be their customer)
            if ($validated['ckdcust_from'] !== $user->ckdcust) {
                return back()->withErrors([
                    'from_customer_name' => 'You are not authorized to create delivery for this customer. You can only create deliveries for your own customer.'
                ])->withInput();
            }
        }

        // Stock Validation: Check if requested quantity exceeds available stock
        $cthnbln = date('Ym');
        $stockErrors = [];

        foreach ($validated['items'] as $item) {
            // Query stock for this specific item and warehouse
            $stockData = DB::select("
                SELECT
                    SUM(CASE
                        WHEN st.cstatus IN ('A', 'I', 'S') THEN st.nqty
                        WHEN st.cstatus = 'O' THEN -st.nqty
                        ELSE 0
                    END) as available_qty,
                    bsc.cbasic,
                    bsc.cgrade
                FROM mstock as st
                JOIN mbasic as bsc ON st.ckdbrg = bsc.ckdbrg
                WHERE st.ckdbrg = ?
                  AND st.ckdwh = ?
                  AND st.ctempat = ?
                  AND st.cthnbln = ?
                GROUP BY bsc.cbasic, bsc.cgrade
            ", [
                $item['ckdbrg'],
                $validated['from_ckdwh'],
                $validated['ckdcust_from'],
                $cthnbln
            ]);

            $availableQty = $stockData[0]->available_qty ?? 0;
            $itemName = isset($stockData[0]) ? ($stockData[0]->cbasic . ' - ' . $stockData[0]->cgrade) : $item['ckdbrg'];

            // STRICT CHECK: Block if requested > available
            if ($item['quantity'] > $availableQty) {
                $stockErrors[] = sprintf(
                    '%s: Requested %d pcs, Available %d pcs',
                    $itemName,
                    $item['quantity'],
                    max(0, $availableQty)
                );
            }
        }

        // Reject creation if insufficient stock
        if (!empty($stockErrors)) {
            return back()->withErrors([
                'stock_validation' => 'Insufficient stock! Cannot create delivery note.',
                'items' => $stockErrors
            ])->withInput();
        }

        // Get ckodea based on sender type
        if ($validated['from_type'] === 'company') {
            // For company, use a default ckodea or get from company settings
            $ckodea = 'YS'; // YanaSurya default code
        } else {
            $ckodea = DB::table('ymcust')
                ->where('ckdcust', $validated['ckdcust_from'])
                ->value('ckodea');
        }

        $nosj = Delivery::generateNosj($ckodea);
        $palletRequestId = $validated['pallet_request_id'] ?? null;

        // Determine initial status (always pending on creation)
        $action = $request->input('action', 'save');
        $initialStatus = 'pending';

        $delivery = Delivery::create([
            'cnosj' => $nosj,
            'cnokontrak' => $validated['agreement_id'] ?? null, // NEW: Save agreement reference
            'cfrom_type' => $validated['from_type'], // NEW: Entity type
            'cto_type' => $validated['to_type'], // NEW: Entity type
            'dtgl_ship' => $validated['delivery_date'],
            'cnotes' => $validated['delivery_notes'] ?? null,
            // From entity (customer or company)
            'nidcust_from' => $validated['nidcust_from'], // Null for companies
            'ckdcust_from' => $validated['ckdcust_from'], // Works for both customer code and company code
            'ccust_from' => $validated['from_customer_name'],
            'caddr_from' => $validated['from_address'],
            'ccity_from' => $validated['from_city'] ?? null,
            'ckdwh_from' => $validated['from_ckdwh'] ?? null,
            // To entity (customer or company)
            'nidcust_to' => $validated['nidcust_to'], // Null for companies
            'ckdcust_to' => $validated['ckdcust_to'], // Works for both customer code and company code
            'ccust_to' => $validated['to_customer_name'],
            'caddr_to' => $validated['to_address'],
            'ccity_to' => $validated['to_city'] ?? null,
            'ckdwh_to' => $validated['to_ckdwh'] ?? null,
            'nidrequest' => $palletRequestId,
            // Logistics
            'clogistics' => $validated['logistics_company'],
            'cdriver_name' => $validated['driver_name'],
            'cdriver_phone' => $validated['driver_phone'],
            'ccountry_code' => $validated['country_code'],
            'cnoplat' => strtoupper($validated['vehicle_license_number']),
            'lexport' => $request->has('is_export'),
            'cnocontainer' => $validated['container_number'] ?? null,
            'cnoseal' => $validated['seal_number'] ?? null,
            'cstatus' => $initialStatus,
            'created_by' => Auth::id(),
            'dtgl_intransit' => null,
        ]);

        foreach ($validated['items'] as $item) {
            $data_billing_transfer_day = DB::select("
                SELECT agrdtl.nbilling_transfer_days
                FROM ytagrnethdr as agrhdr
                LEFT JOIN ytagrnetdtl as agrdtl ON agrhdr.cnokontrak = agrdtl.cnokontrak
                LEFT JOIN mbasic as bsc ON agrdtl.cpallettype = bsc.cbasic
                WHERE ((agrhdr.ckdcust_a='".$validated['ckdcust_to']."' and agrhdr.ckdcust_b='".$validated['ckdcust_from']."') or (agrhdr.ckdcust_a='".$validated['ckdcust_from']."' and agrhdr.ckdcust_b='".$validated['ckdcust_to']."')) 
                and bsc.ckdbrg='".$item['ckdbrg']."'");
            $nbilling_transfer_day=0;
            $dtglbilling = null;

            $cekCompany=DB::select("SELECT * FROM ymcomp where ckdcomp='".$validated['ckdcust_to']."'");
            if($user->ckdcomp){
                $nbilling_transfer_day=0;
                $dtglbilling = null;
            }
            else if(isset($cekCompany)){
                $nbilling_transfer_day=0;
                $dtglbilling = null;
            }
            else{
                $nbilling_transfer_day=$data_billing_transfer_day[0]->nbilling_transfer_days;
                $dtglbilling = date("Y-m-d", strtotime($validated['delivery_date']." +".$nbilling_transfer_day." days"));    
            }
            $delivery->items()->create([
                'cnosj' => $nosj,
                'cpallet_type' => $item['ckdbrg'],
                'nqty' => $item['quantity'],
            ]);

            DB::table("mstock")->insert(
                [
                    "ckdbrg" => $item['ckdbrg'],
                    "cstatus" => "O",
                    "ctempat" => $validated['ckdcust_from'],
                    "ckdwh" => $validated['from_ckdwh'],
                    "cnobukti" => $nosj,
                    "dtglbukti" => $validated['delivery_date'],
                    "cthnbln" => date('Ym'),
                    "nqty" => $item['quantity'],
                    "dtglakhirtagih" => $dtglbilling,
                    // nsisa dari bpb
                ]
            );

            // DB::update(
            //     "UPDATE mutasistockinv SET dtglakhirtagih ='".$dtglbilling."' WHERE ckdcust='".$validated['ckdcust_from']."' and ckdbrg='".$item['ckdbrg']."'"
            // );
        }

        if ($palletRequestId) {
            DB::table('tpalletrequest')
                ->where('nid', $palletRequestId)
                ->update([
                    'cnosj' => $nosj,
                ]);

            $this->insertPalletRequestStatusIfChanged(
                $palletRequestId,
                'Delivery Note Created',
                Auth::user()->name ?? null
            );

        }

        return redirect()
            ->route('delivery.index')
            ->with('success', 'Delivery note ' . $nosj . ' created successfully!');
    }

    public function show(string $id): View
    {
        // load delivery with items and creator
        $delivery = Delivery::with(['items', 'creator'])->findOrFail($id);

        // Attach mbasic (pallet) info to each item:
        // left-join semantics implemented by fetching mbasic rows for all cpallet_type values
        $palletCodes = $delivery->items
            ->pluck('cpallet_type')      // get cpallet_type from each item
            ->filter()                   // remove null / empty
            ->unique()
            ->values();

        if ($palletCodes->isNotEmpty()) {
            // fetch mbasic rows where ckdbrg in palletCodes and key by ckdbrg for O(1) lookup
            $mbasicRows = DB::table('mbasic')
                ->whereIn('ckdbrg', $palletCodes->all())
                ->get()
                ->keyBy('ckdbrg');

            // attach corresponding mbasic row (or null) to each item as ->mbasic attribute
            $delivery->setRelation('items', $delivery->items->map(function ($item) use ($mbasicRows) {
                // prefer setAttribute so blade / JSON can see it consistently
                $item->setAttribute('mbasic', $mbasicRows->has($item->cpallet_type) ? $mbasicRows->get($item->cpallet_type) : null);
                return $item;
            }));
        } else {
            // ensure each item has mbasic => null for consistency (optional)
            $delivery->setRelation('items', $delivery->items->map(function ($item) {
                $item->setAttribute('mbasic', null);
                return $item;
            }));
        }

        // Fetch Goods Receipt if exists (1 DN = 1 GR)
        $goodsReceipt = DB::table('ytbpbhdr')
            ->where('nidsj', $id)
            ->first();

        $goodsReceiptItems = collect();
        if ($goodsReceipt) {
            $goodsReceiptItems = DB::table('ytbpbdtl')
                ->where('nidbpb', $goodsReceipt->nidbpb)
                ->get()
                ->values();
        }

        // Calculate Quick Stats from real data
        $totalDeliveryQty = $delivery->items->sum('nqty');

        if ($goodsReceipt) {
            $receivedGoodQty = $goodsReceiptItems->sum('ngood_qty');
            $receivedRejectQty = $goodsReceiptItems->sum('nreject_qty');
            $missingQty = $goodsReceiptItems->sum('nmissing_qty');
        } else {
            $receivedGoodQty = 0;
            $receivedRejectQty = 0;
            $missingQty = 0;
        }

        $goodPercentage = $totalDeliveryQty > 0 ? ($receivedGoodQty / $totalDeliveryQty) * 100 : 0;
        $rejectPercentage = $totalDeliveryQty > 0 ? ($receivedRejectQty / $totalDeliveryQty) * 100 : 0;
        $missingPercentage = $totalDeliveryQty > 0 ? ($missingQty / $totalDeliveryQty) * 100 : 0;

        // Build Activity Timeline
        $timeline = [];

        // 1. DN Created (always exists)
        $timeline[] = [
            'title' => 'Delivery Note Created',
            'description' => $delivery->cnosj . ' - By ' . ($delivery->creator->name ?? 'System'),
            'timestamp' => $delivery->created_at,
            'status' => 'completed',
            'icon' => 'success'
        ];

        // 2. In Transit (if status >= in_transit)
        if (in_array($delivery->cstatus, ['in_transit', 'delivered'])) {
            $timeline[] = [
                'title' => 'In Transit (DN Printed)',
                'description' => 'By ' . $delivery->cdriver_name . ' (Driver)',
                'timestamp' => $delivery->dtgl_intransit ?? $delivery->updated_at,
                'status' => 'completed',
                'icon' => 'success'
            ];
        }

        // 3. Goods Received (if GR exists)
        if ($goodsReceipt) {
            $timeline[] = [
                'title' => 'Goods Received',
                'description' => $goodsReceipt->cno_grn . ' - By ' . $goodsReceipt->creceiver_name . ' (' . $goodsReceipt->creceiver_position . ')',
                'timestamp' => \Carbon\Carbon::parse($goodsReceipt->created_at),
                'status' => 'completed',
                'icon' => 'success',
                'highlight' => true
            ];
        }

        return view('transaction.delivery.show', compact(
            'delivery',
            'goodsReceipt',
            'goodsReceiptItems',
            'totalDeliveryQty',
            'receivedGoodQty',
            'receivedRejectQty',
            'missingQty',
            'goodPercentage',
            'rejectPercentage',
            'missingPercentage',
            'timeline'
        ));
    }


    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id): RedirectResponse
    {
        $delivery = Delivery::findOrFail($id);
        $delivery->delete();

        return redirect()->route('delivery.index')
            ->with('success', 'Delivery note deleted successfully.');
    }

    public function print(string $id): View
    {
        $delivery = Delivery::with('items')->findOrFail($id);
        $user = Auth::user();

        // Authorization: Only sender can print
        // 1. Admin can print any delivery
        // 2. Customer user: ckdcust_from must match user's ckdcust
        // 3. YanaSurya warehouse user: ckdcust_from must match user's ckdcomp
        $canPrint = $user->isAdmin()
            || ($delivery->ckdcust_from === $user->ckdcust && $user->ckdcust)
            || ($delivery->ckdcust_from === $user->ckdcomp && $user->role === 'warehouseys');

        if (!$canPrint) {
            abort(403, 'Unauthorized: Only sender can print delivery note.');
        }

        // Update status to in_transit when printing
        if ($delivery->cstatus === 'pending') {
            $delivery->update([
                'cstatus' => 'in_transit',
                'dtgl_intransit' => now(),
                'updated_at' => now(),
            ]);

        }

        return view('transaction.delivery.print', compact('delivery'));
    }

    public function job_instruction(string $id): View
    {
        $delivery = Delivery::with('items')->findOrFail($id);
        $user = Auth::user();

        // Authorization: Sender or receiver can access
        // 1. Admin can access any delivery
        // 2. Customer user: ckdcust_from or ckdcust_to must match user's ckdcust
        // 3. YanaSurya warehouse user: ckdcust_from or ckdcust_to must match user's ckdcomp
        $canView = $user->isAdmin()
            || ($delivery->ckdcust_from === $user->ckdcust && $user->ckdcust)
            || ($delivery->ckdcust_to === $user->ckdcust && $user->ckdcust)
            || (
                $user->role === 'warehouseys'
                && $user->ckdcomp
                && ($delivery->ckdcust_from === $user->ckdcomp || $delivery->ckdcust_to === $user->ckdcomp)
            );

        if (! $canView) {
            abort(403, 'Unauthorized: Only related parties can access job instruction.');
        }

        // Attach mbasic (pallet) info to each item (same as show)
        $palletCodes = $delivery->items
            ->pluck('cpallet_type')
            ->filter()
            ->unique()
            ->values();

        if ($palletCodes->isNotEmpty()) {
            $mbasicRows = DB::table('mbasic')
                ->whereIn('ckdbrg', $palletCodes->all())
                ->get()
                ->keyBy('ckdbrg');

            $delivery->setRelation('items', $delivery->items->map(function ($item) use ($mbasicRows) {
                $item->setAttribute('mbasic', $mbasicRows->has($item->cpallet_type) ? $mbasicRows->get($item->cpallet_type) : null);
                return $item;
            }));
        } else {
            $delivery->setRelation('items', $delivery->items->map(function ($item) {
                $item->setAttribute('mbasic', null);
                return $item;
            }));
        }

        return view('transaction.delivery.job_instruction', compact('delivery'));
    }


    // Order/Return Transactions
    public function request_email()
    {
        $ckdcust = Auth::user()->ckdcust;
        $role = Auth::user()->customer_role;

        // ambil warehouse customer, kalau pic munculkan warehouse pic tersebut saja
        if ($role == 'warehouse_pic'){
            $ckdwh = Auth::user()->ckdwh;
            $custwh = DB::table('ymcustwarehouse as custwh')
                ->join('mkota as m', 'm.ckota', '=', 'custwh.ckotawh')
                ->where('custwh.ckdcust', $ckdcust)
                ->where('custwh.ckdwh', $ckdwh)
                ->get();
        }else{
            $custwh = DB::table('ymcustwarehouse as custwh')
                ->join('mkota as m', 'm.ckota', '=', 'custwh.ckotawh')
                ->where('custwh.ckdcust', $ckdcust)
                ->get();
        }

        $yswh = DB::table('ymcompwarehouse')
            ->join('mkota as m', 'm.ckota', '=', 'ymcompwarehouse.ckotawh')
            ->get();
        
        dump($yswh);
        

        $request = DB::table('ymcust')
            ->where('ckdcust', $ckdcust)
            ->first();
        

        return view('transaction.order_return.request_email', compact('request', 'custwh', 'yswh', 'role'));
    }

    /**
     * POST /delivery/warehouse-token
     * body: { from: { nid: <>, ckd: <> } , to: { nid: <>, ckd: <> } }
     * returns: { token_from, token_to }
     */
    public function generateWarehouseToken(Request $request)
    {
        // Accept either application/json or form-encoded; don't rely solely on dot-validation here
        $input = $request->input(); // safe for both JSON and form data

        $from = is_array($input['from'] ?? null) ? $input['from'] : [];
        $to   = is_array($input['to'] ?? null)   ? $input['to']   : [];

        // Defensive normalizer: convert numeric strings to ints, trim strings
        $normalize = function ($item) {
            if (!is_array($item)) return null;
            $nid = isset($item['nid']) ? $item['nid'] : null;
            $ckd = isset($item['ckd']) ? $item['ckd'] : null;

            // treat empty strings as null; keep "0" if intentionally provided
            $nid = (is_string($nid) ? trim($nid) : $nid);
            $ckd = (is_string($ckd) ? trim($ckd) : $ckd);

            // null-out completely empty values
            if ($nid === '' && $ckd === '') return null;

            return [
                'nid' => ($nid === '' || $nid === null) ? null : (is_numeric($nid) ? (int)$nid : $nid),
                'ckd' => ($ckd === '' || $ckd === null) ? null : (string)$ckd,
            ];
        };

        $from = $normalize($from);
        $to   = $normalize($to);

        $make = function ($item) {
            if (!$item) {
                return null;
            }
            // require at least one non-empty id
            if (($item['nid'] === null || $item['nid'] === '') && ($item['ckd'] === null || $item['ckd'] === '')) {
                return null;
            }

            $payload = [
                'nidwh' => isset($item['nid']) && $item['nid'] !== null ? (int)$item['nid'] : null,
                'ckdwh' => isset($item['ckd']) && $item['ckd'] !== null ? (string)$item['ckd'] : null,
                'iat'   => now()->timestamp,
                // optional: add expiry if you plan to support TTL validation later
                // 'exp' => now()->addHours(48)->timestamp,
            ];

            try {
                return Crypt::encryptString(json_encode($payload));
            } catch (\Throwable $e) {
                Log::error('generateWarehouseToken: encrypt error', [
                    'err' => $e->getMessage(),
                    'payload' => $payload,
                ]);
                // bubble up null — caller handles missing token. Do not expose exception to client in production.
                return null;
            }
        };

        $out = [
            'token_from' => $make($from),
            'token_to'   => $make($to),
        ];

        // If both tokens are null because of encryption failure, return 500 for visibility
        if ($request->wantsJson() && $out['token_from'] === null && $out['token_to'] === null) {
            // If you want stricter behavior: return 500 with helpful message (but be careful in prod to not leak stack)
            // return response()->json(['error' => 'token_generation_failed'], 500);
        }

        return response()->json($out, 200);
    }


    public function order_return_monitoring(Request $request)
    {
        $search       = $request->get('search');
        $type         = $request->get('type');     // order | return
        $status       = $request->get('status');   // Pending | Approved | ...
        $dateFrom     = $request->get('date_from');
        $dateTo       = $request->get('date_to');

        $perPage      = 30;
        $currentPage  = max((int) $request->get('page', 1), 1);

        /* subquery: latest status per pallet request */
        $latestStatusSub = DB::table('tpalletrequeststatus')
            ->selectRaw('MAX(nid) as latest_status_id, nid_pallet_request')
            ->groupBy('nid_pallet_request');

        $query = DB::table('tpalletrequest as pr')
            ->leftJoinSub($latestStatusSub, 'latest_status', function ($join) {
                $join->on('pr.nid', '=', 'latest_status.nid_pallet_request');
            })
            ->leftJoin('tpalletrequeststatus as trs', 'trs.nid', '=', 'latest_status.latest_status_id')
            ->select(
                'pr.*',
                'trs.cstatus as latest_status',
                'trs.capproved_by',
            );

        /*
     * Role-based scoping
     * - admin         -> see ALL requests (no filter)
     * - warehouseys   -> use $user->ckdwhcomp to filter pr.ckdwh_from / pr.ckdwh_to
     * - customer      -> use $user->ckdwh to filter pr.ckdwh_from / pr.ckdwh_to
     */
        $user = Auth::user();

        $user_role = strtolower($user->role ?? '');
        if ($user_role === 'customer') {
            $query->where(function ($q) use ($user) {
                $q->where('pr.ckdwh_from', $user->ckdwh)
                    ->orWhere('pr.ckdwh_to', $user->ckdwh);
            });
        } elseif ($user_role === 'warehouseys') {
            $query->where(function ($q) use ($user) {
                $q->where('pr.ckdwh_from', $user->ckdwhcomp)
                    ->orWhere('pr.ckdwh_to', $user->ckdwhcomp);
            });
        }
        // If role is 'admin', no filtering is applied - they see ALL requests

        /* Search */
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('pr.cwarehouse_from', 'ilike', "%{$search}%")
                    ->orWhere('pr.cwaddr_from', 'ilike', "%{$search}%")
                    ->orWhere('pr.cwarehouse_to', 'ilike', "%{$search}%")
                    ->orWhere('pr.cwaddr_to', 'ilike', "%{$search}%")
                    ->orWhere('pr.cpallet_type', 'ilike', "%{$search}%")
                    ->orWhere('trs.cstatus', 'ilike', "%{$search}%");
            });
        }

        /* Type filter */
        if ($type && $type !== 'all') {
            $query->where('pr.crequest_type', ucfirst($type));
        }

        /* Status filter */
        if ($status) {
            $query->whereRaw('LOWER(trs.cstatus) = ?', [strtolower($status)]);
        }

        /* Date range filter */
        if ($dateFrom || $dateTo) {
            // prepare inclusive range
            $fromDt = $dateFrom ? Carbon::createFromFormat('Y-m-d', $dateFrom, 'Asia/Jakarta')->startOfDay()->toDateTimeString() : null;
            $toDt   = $dateTo   ? Carbon::createFromFormat('Y-m-d', $dateTo,   'Asia/Jakarta')->endOfDay()->toDateTimeString()   : null;

            $query->where(function ($q) use ($fromDt, $toDt) {
                // request qualifies if its required_date falls in range OR its return_date falls in range
                if ($fromDt && $toDt) {
                    $q->where(function ($q2) use ($fromDt, $toDt) {
                        $q2->whereNotNull('pr.drequired_date')
                            ->where('pr.drequired_date', '>=', $fromDt)
                            ->where('pr.drequired_date', '<=', $toDt);
                    })->orWhere(function ($q2) use ($fromDt, $toDt) {
                        $q2->whereNotNull('pr.dreturn_date')
                            ->where('pr.dreturn_date', '>=', $fromDt)
                            ->where('pr.dreturn_date', '<=', $toDt);
                    });
                    return;
                }

                if ($fromDt) {
                    $q->where(function ($q2) use ($fromDt) {
                        $q2->whereNotNull('pr.drequired_date')
                            ->where('pr.drequired_date', '>=', $fromDt);
                    })->orWhere(function ($q2) use ($fromDt) {
                        $q2->whereNotNull('pr.dreturn_date')
                            ->where('pr.dreturn_date', '>=', $fromDt);
                    });
                    return;
                }

                if ($toDt) {
                    $q->where(function ($q2) use ($toDt) {
                        $q2->whereNotNull('pr.drequired_date')
                            ->where('pr.drequired_date', '<=', $toDt);
                    })->orWhere(function ($q2) use ($toDt) {
                        $q2->whereNotNull('pr.dreturn_date')
                            ->where('pr.dreturn_date', '<=', $toDt);
                    });
                }
            });
        }


        /* Pagination */
        $total     = (clone $query)->count();
        $offset    = ($currentPage - 1) * $perPage;
        $lastPage  = (int) ceil($total / $perPage);

        $requests = $query
            ->orderBy('pr.nid', 'desc')
            ->offset($offset)
            ->limit($perPage)
            ->get();

        $from = $total ? $offset + 1 : 0;
        $to   = min($offset + $perPage, $total);

        return view('transaction.order_return.index', compact(
            'requests',
            'search',
            'total',
            'currentPage',
            'lastPage',
            'from',
            'to'
        ));
    }

    public function order_return_show($id)
    {
        $request = DB::table('tpalletrequest')
            ->where('nid', $id)
            ->first();

        if (! $request) {
            abort(404);
        }

        $statuses = DB::table('tpalletrequeststatus')
            ->where('nid_pallet_request', $id)
            ->orderBy('nid', 'asc')
            ->get();

        $latestStatus = $statuses->last();

        $sender = DB::table('users')
            ->where('email', $request->cfrom_email)
            ->first();

        return view('transaction.order_return.show', compact(
            'request',
            'statuses',
            'latestStatus',
            'sender'
        ));
    }

    public function order_return_job_instruction($id): View
    {
        $request = DB::table('tpalletrequest')
            ->where('nid', $id)
            ->first();

        if (! $request) {
            abort(404);
        }

        $latestStatus = DB::table('tpalletrequeststatus')
            ->where('nid_pallet_request', $id)
            ->orderBy('nid', 'desc')
            ->first();

        if (
            ! $latestStatus ||
            $request->crequest_type !== 'Order' ||
            $latestStatus->cstatus !== 'Approved'
        ) {
            abort(403, 'Job instruction is available only for approved order requests.');
        }

        return view('transaction.order_return.job_instruction', compact(
            'request',
            'latestStatus'
        ));
    }

    public function order_return_approve($id, $admin_name)
    {
        DB::table('tpalletrequeststatus')
            ->insert([
                'nid_pallet_request' => $id,
                'cstatus' => 'Approved',
                'capproved_by' => $admin_name,
                'updated_at' => now(),
            ]);

        return redirect()->route('delivery.order_return_monitoring')
            ->with('success', 'Pallet request approved successfully.');
    }

    public function order_return_reject(Request $request, $id, $admin_name)
    {
        $data = $request->validate([
            'creason' => 'required|string|max:500',
        ]);

        DB::table('tpalletrequeststatus')
            ->insert([
                'nid_pallet_request' => $id,
                'cstatus' => 'Rejected',
                'capproved_by' => $admin_name,
                'creason' => $data['creason'],
                'updated_at' => now(),
            ]);

        return redirect()->route('delivery.order_return_monitoring')
            ->with('success', 'Pallet request rejected successfully.');
    }

    public function order_return_reschedule(Request $request, $id, $admin_name)
    {
        $data = $request->validate([
            'rescheduled_date' => 'required|date|after:today',
            'creason' => 'required|string|max:500',
        ]);

        $palletRequest = DB::table('tpalletrequest')
            ->where('nid', $id)
            ->first();

        if (! $palletRequest) {
            abort(404, 'Pallet request not found');
        }

        // Only one active proposal at a time
        if (
            $palletRequest->creschedule_status === 'pending' &&
            $palletRequest->creschedule_expires_at &&
            Carbon::parse($palletRequest->creschedule_expires_at)->isFuture()
        ) {
            return back()->withErrors([
                'rescheduled_date' => 'A reschedule proposal is already pending customer response.'
            ]);
        }

        $rescheduledDate = Carbon::parse($data['rescheduled_date'])
            ->startOfDay()
            ->toDateTimeString();

        $originalDate = $palletRequest->crequest_type === 'Order'
            ? $palletRequest->drequired_date
            : $palletRequest->dreturn_date;

        $expiresAt = $originalDate
            ? Carbon::parse($originalDate)->endOfDay()->toDateTimeString()
            : now()->endOfDay()->toDateTimeString();

        $token = Str::random(40);

        DB::table('tpalletrequest')
            ->where('nid', $id)
            ->update([
                'dreschedule_proposed' => $rescheduledDate,
                'creschedule_reason' => $data['creason'],
                'creschedule_token' => $token,
                'creschedule_expires_at' => $expiresAt,
                'creschedule_status' => 'pending',
            ]);

        DB::table('tpalletrequeststatus')
            ->insert([
                'nid_pallet_request' => $id,
                'cstatus' => 'Reschedule Proposed',
                'capproved_by' => $admin_name,
                'creason' => $data['creason'],
                'updated_at' => now(),
            ]);

        try {
            Mail::mailer('invoice')
                ->to($palletRequest->cfrom_email)
                ->send(new RequestRescheduleMail(
                    requestId: $palletRequest->nid,
                    requestType: $palletRequest->crequest_type,
                    customerName: $palletRequest->ccompany_name,
                    customerEmail: $palletRequest->cfrom_email,
                    palletType: $palletRequest->cpallet_type,
                    palletSize: $palletRequest->cpallet_size,
                    palletColor: $palletRequest->cpallet_color,
                    quantity: $palletRequest->crequest_type === 'Order' ? $palletRequest->nqty : $palletRequest->nqty_return,
                    fromWarehouse: $palletRequest->cwarehouse_from,
                    fromAddress: $palletRequest->cwaddr_from,
                    toWarehouse: $palletRequest->cwarehouse_to,
                    toAddress: $palletRequest->cwaddr_to,
                    originalDate: $originalDate,
                    proposedDate: $rescheduledDate,
                    reason: $data['creason'],
                    token: $token,
                    expiresAt: $expiresAt
                ));
        } catch (\Exception $e) {
            Log::warning('Failed to send reschedule email to ' . $palletRequest->cfrom_email . ': ' . $e->getMessage());
        }

        return redirect()->route('delivery.order_return_show', $id)
            ->with('success', 'Reschedule proposal sent to customer for confirmation.');
    }

    public function order_return_reschedule_accept(string $token): View
    {
        [$palletRequest, $errorMessage] = $this->getPendingRescheduleRequestByToken($token);

        if (! $palletRequest) {
            return view('transaction.order_return.reschedule_response', [
                'title' => 'Reschedule Response',
                'status' => 'error',
                'message' => $errorMessage,
            ]);
        }

        $dateColumn = $palletRequest->crequest_type === 'Order' ? 'drequired_date' : 'dreturn_date';
        $rescheduledDate = Carbon::parse($palletRequest->dreschedule_proposed)->startOfDay()->toDateTimeString();

        DB::table('tpalletrequest')
            ->where('nid', $palletRequest->nid)
            ->update([
                $dateColumn => $rescheduledDate,
                'creschedule_status' => 'accepted',
                'creschedule_token' => null,
                'creschedule_expires_at' => null,
            ]);

        DB::table('tpalletrequeststatus')
            ->insert([
                'nid_pallet_request' => $palletRequest->nid,
                'cstatus' => 'Approved',
                'capproved_by' => $palletRequest->cfrom_email,
                'creason' => 'Customer accepted reschedule proposal.',
                'updated_at' => now(),
            ]);

        return view('transaction.order_return.reschedule_response', [
            'title' => 'Reschedule Accepted',
            'status' => 'success',
            'message' => 'Thank you. The rescheduled date has been accepted and the request is now approved.',
        ]);
    }

    public function order_return_reschedule_reject(string $token): View
    {
        [$palletRequest, $errorMessage] = $this->getPendingRescheduleRequestByToken($token);

        if (! $palletRequest) {
            return view('transaction.order_return.reschedule_response', [
                'title' => 'Reschedule Response',
                'status' => 'error',
                'message' => $errorMessage,
            ]);
        }

        DB::table('tpalletrequest')
            ->where('nid', $palletRequest->nid)
            ->update([
                'creschedule_status' => 'rejected',
                'creschedule_token' => null,
                'creschedule_expires_at' => null,
            ]);

        DB::table('tpalletrequeststatus')
            ->insert([
                'nid_pallet_request' => $palletRequest->nid,
                'cstatus' => 'Rejected',
                'capproved_by' => $palletRequest->cfrom_email,
                'creason' => 'Customer rejected reschedule proposal.',
                'updated_at' => now(),
            ]);

        return view('transaction.order_return.reschedule_response', [
            'title' => 'Reschedule Rejected',
            'status' => 'warning',
            'message' => 'The reschedule proposal has been rejected. The request remains unchanged.',
        ]);
    }

    public function goods_receipt_create()
    {
        $user = Auth::user();
        $cust = $user->name;

        $receiver_info = DB::table('users')
            ->where('name', $cust)
            ->first();

        // Determine user -> customer warehouse pic or yanasurya warehouse pic
        $receiverCode = $user->ckdwh ?? $user->ckdwhcomp;

        //not using eloquent model here
        $deliveries = DB::table('ytsjhdr')
            ->where(function ($query) use ($receiverCode) {
                $query->where('ckdwh_to', $receiverCode);
            })
            ->where('cstatus', 'in_transit')
            ->get()
            ->map(function ($delivery) {
                $delivery->items = DB::table('ytsjdtl')
                    ->where('cnosj', $delivery->cnosj)
                    ->get()
                    ->map(function ($pallet) {
                        $pallet->spec = DB::table('mbasic')
                            ->where('ckdbrg', $pallet->cpallet_type)
                            ->first();
                        return $pallet;
                    });
                return $delivery;
            });

        $count = $deliveries->count();

        // dump($deliveries);

        return view('transaction.delivery.goods_receipt_create', compact('receiver_info', 'deliveries', 'count'));
    }

    public function goods_receipt_create_id($id)
    {
        $user = Auth::user();
        $cust = $user->name;

        $receiver_info = DB::table('users')
            ->where('name', $cust)
            ->first();

        // Determine user -> customer warehouse pic or yanasurya warehouse pic
        $receiverCode = $user->ckdwh ?? $user->ckdwhcomp;

        //not using eloquent model here - verify user is authorized to receive this delivery
        $deliveries = DB::table('ytsjhdr')
            ->where('nidsj', $id)
            ->where(function ($query) use ($receiverCode) {
                $query->Where('ckdwh_to', $receiverCode);
            })
            ->where('cstatus', 'in_transit')
            ->get()
            ->map(function ($delivery) {
                $delivery->items = DB::table('ytsjdtl')
                    ->where('cnosj', $delivery->cnosj)
                    ->get()
                    ->map(function ($pallet) {
                        $pallet->spec = DB::table('mbasic')
                            ->where('ckdbrg', $pallet->cpallet_type)
                            ->first();
                        return $pallet;
                    });
                return $delivery;
            });
        $count = $deliveries->count();

        // dump($receiver_info);
        // dump($receiverCode);
        // dump($deliveries);
        return view('transaction.delivery.goods_receipt_create', compact('receiver_info', 'deliveries', 'count'));
    }

    public function goods_receipt_store(Request $request): RedirectResponse
    {
        DB::beginTransaction();

        try {
            // Get agreement reference and entity types from the delivery note
            $delivery = Delivery::where('nidsj', $request->delivery_note_id)->first();
            if (! $delivery) {
                throw new \RuntimeException('Delivery note not found.');
            }

            $deliveryNoteNumber = $delivery->cnosj;
            $ckdwhTo = $delivery->ckdwh_to;

            $cnokontrak = $delivery->cnokontrak;
            $cfrom_type = $delivery->cfrom_type ?? 'customer';
            $cto_type = $delivery->cto_type ?? 'customer';

            // INSERT MASTER (Goods Receipt Header)
            $nidbpb = DB::table('ytbpbhdr')->insertGetId([
                'cno_grn'           => $request->gr_number,
                'cnokontrak'        => $cnokontrak,  // Copy agreement reference from delivery
                'cfrom_type'        => $cfrom_type,  // NEW: Copy sender type from delivery
                'cto_type'          => $cto_type,    // NEW: Copy receiver type from delivery
                'nidsj'            => $request->delivery_note_id,      // FK
                'cnosj'            => $deliveryNoteNumber,  // FK
                'creceiver_name'    => $request->receiver_name,
                'creceiver_position' => $request->receiver_position,
                'dreceipt_date'     => $request->receipt_date,
                'ddelivery_date'    => $request->delivery_date,
                'creceipt_notes'    => $request->receipt_notes,
                'ccust_from'        => $request->cust_from,
                'caddr_from'        => $request->addr_from,
                'ccust_to'          => $request->cust_to,
                'caddr_to'          => $request->addr_to,
                'clogistics'       => $request->logistic_company,
                'cdriver_name'      => $request->driver,
                'created_at'       => now(),
                'created_by'        => Auth::id(),
                'ckdcust_from'      => $request->kdcust_from,
                'nidcust_from'      => $request->idcust_from,
                'ccity_from'        => $request->city_from,
                'ckdcust_to'        => $request->kdcust_to,
                'nidcust_to'        => $request->idcust_to,
                'ccity_to'          => $request->city_to,
                'cstatus'           => 'delivered',
                'cno_plat'         => $request->no_plat,
            ], 'nidbpb');

            if (! $ckdwhTo) {
                throw new \RuntimeException('Destination warehouse not found for delivery note.');
            }

            // INSERT DETAIL (Received Items)
            foreach ($request->items as $item) {
                DB::table('ytbpbdtl')->insert([
                    'nidbpb'           => $nidbpb,                        // FK to master
                    'cno_grn'           => $request->gr_number,
                    'nidsj'            => $request->delivery_note_id,   // FK
                    'cnosj'            => $deliveryNoteNumber,
                    'cpallet_type'      => $item['pallet_type'],
                    'ndelivered_qty'    => $item['delivered_qty'],
                    'ngood_qty'         => $item['good_qty'],
                    'nreject_qty'       => $item['reject_qty'],
                    'nmissing_qty'      => $item['missing_qty'],
                    'created_at'       => now(),
                ]);

                //cari aggrement antara cust a dan cust b
                $nbilling_transfer_day = DB::table('ytagrnethdr as agrhdr')
                    ->leftJoin('ytagrnetdtl as agrdtl', 'agrhdr.cnokontrak', '=', 'agrdtl.cnokontrak')
                    ->leftJoin('mbasic as bsc', 'agrdtl.cpallettype', '=', 'bsc.cbasic')
                    ->where(function ($query) use ($request) {
                        $query->where(function ($q) use ($request) {
                            $q->where('agrhdr.ckdcust_a', $request->kdcust_to)
                                ->where('agrhdr.ckdcust_b', $request->kdcust_from);
                        })->orWhere(function ($q) use ($request) {
                            $q->where('agrhdr.ckdcust_a', $request->kdcust_from)
                                ->where('agrhdr.ckdcust_b', $request->kdcust_to);
                        });
                    })
                    ->where('bsc.ckdbrg', $item['pallet_type'])
                    ->value('agrdtl.nbilling_transfer_days');

                $nbilling_transfer_day = $nbilling_transfer_day !== null ? (int) $nbilling_transfer_day : 0;
                $nbilling_transfer_day++;
                $dtglbilling = date("Y-m-d", strtotime($request->receipt_date." +".$nbilling_transfer_day." days"));
                // $agrnet=DB::select("
                //     SELECT * 
                //     FROM ytagrnethdr agrhdr 
                //     LEFT JOIN ytagrnetdtl agrdtl ON agrhdr.cnokontrak = agrdtl.cnokontrak
                //     WHERE cstatus='active' and (agrhdr.ckdcust_a ='".$valcust->ckdcust."' or agrhdr.ckdcust_b ='".$valcust->ckdcust."')
                // ");
                // foreach ($agrnet as $key => $valnet) {
                //     $arrbarang[$valnet->cpallettype]['nbilling_transfer_days']=$valnet->nbilling_transfer_days;
                // }
                // $nbillingafterdays=$data_billing_transfer_day[0]->nbilling_transfer_days;

                //bpbp untuk barang bagus
                DB::table("mstock")->insert(
                    [
                        "ckdbrg" => $item['pallet_type'],
                        "cstatus" => "I",
                        "ctempat" => $request->kdcust_to,
                        "ckdwh" => $ckdwhTo,
                        "cnobukti" => $request->gr_number,
                        "dtglbukti" => $request->receipt_date,
                        "cthnbln" => date('Ym'),
                        "nqty" => $item['good_qty'],
                        // "dtgltagih"=>date("Y-m-d", strtotime("$request->receipt_date +$nbillingafterdays days")),
                        "dtgltagih"=>$dtglbilling,
                    ]
                );
                if ((int)$item['reject_qty'] > 0) {
                    //bpb untuk barang rusak
                    DB::table("mstock")->insert(
                        [
                            "ckdbrg" => "02" . substr($item['pallet_type'], 2),
                            "cstatus" => "I",
                            "ctempat" => $request->kdcust_to,
                            "ckdwh" => $ckdwhTo,
                            "cnobukti" => $request->gr_number,
                            "dtglbukti" => $request->receipt_date,
                            "cthnbln" => date('Ym'),
                            "nqty" => $item['reject_qty'],
                        ]
                    );
                }

                // $nbillingafterdays++;
                // DB::table("mutasistockinv")->insert(
                //     [
                //         "ckdcust" => $request->kdcust_to,
                //         "ckdbrg"=>$item['pallet_type'],
                //         "nqty"=>$item['good_qty'],
                //         "dtgltagih"=>date("Y-m-d", strtotime("$request->receipt_date +$nbillingafterdays days")),
                //     ]
                // );
            }

            // HANDLE PHOTO UPLOADS
            $hasPhotos = false;
            if ($request->hasFile('main_photo')) {
                $hasPhotos = true;

                // Create folder name from GR number (replace / with -)
                $folderName = str_replace('/', '-', $request->gr_number);
                $folderPath = 'goods-receipts/' . $folderName;

                // Upload main photo
                $mainPhoto = $request->file('main_photo');
                $mainPhotoExtension = $mainPhoto->getClientOriginalExtension();
                $mainPhotoName = 'main.' . $mainPhotoExtension;

                try {
                    // Store with proper naming - use 'public' disk explicitly
                    $mainPhoto->storeAs($folderPath, $mainPhotoName, 'public');
                } catch (\Exception $e) {
                    DB::rollBack();
                    return back()->withErrors([
                        'error' => 'Failed to upload main photo: ' . $e->getMessage()
                    ]);
                }

                // Upload reject photos (if any)
                foreach ($request->items as $index => $item) {
                    if (isset($item['reject_qty']) && (int)$item['reject_qty'] > 0) {
                        // Check if reject photos exist for this item
                        $rejectPhotosKey = 'reject_photos_' . $index;

                        if ($request->hasFile($rejectPhotosKey)) {
                            $rejectPhotos = $request->file($rejectPhotosKey);
                            $photoNumber = 1;

                            foreach ($rejectPhotos as $rejectPhoto) {
                                if ($rejectPhoto && $rejectPhoto->isValid()) {
                                    $rejectPhotoExtension = $rejectPhoto->getClientOriginalExtension();
                                    $rejectPhotoName = 'reject_item' . $index . '_' . $photoNumber . '.' . $rejectPhotoExtension;

                                    try {
                                        $rejectPhoto->storeAs($folderPath, $rejectPhotoName, 'public');
                                        $photoNumber++;
                                    } catch (\Exception $e) {
                                        DB::rollBack();
                                        return back()->withErrors([
                                            'error' => 'Failed to upload reject photo: ' . $e->getMessage()
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // Update lhasphotos flag if photos were uploaded
            if ($hasPhotos) {
                DB::table('ytbpbhdr')
                    ->where('nidbpb', $nidbpb)
                    ->update(['lhasphotos' => true]);
            }

            DB::table('ytsjhdr')
                ->where('nidsj', $request->delivery_note_id)
                ->update([
                    'cstatus' => 'delivered',
                    'updated_at' => now(),
                    'dtgl_eta' => $request->receipt_date,
                ]);

            DB::commit();

            return redirect()
                ->route('delivery.index')
                ->with('success', 'Goods Receipt successfully created.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Failed to save Goods Receipt: ' . $e->getMessage()
            ]);
        }
    }

    private function insertPalletRequestStatusIfChanged(int $requestId, string $status, ?string $approvedBy = null): void
    {
        $latestStatus = DB::table('tpalletrequeststatus')
            ->where('nid_pallet_request', $requestId)
            ->orderBy('nid', 'desc')
            ->first();

        if ($latestStatus && $latestStatus->cstatus === $status) {
            return;
        }

        DB::table('tpalletrequeststatus')
            ->insert([
                'nid_pallet_request' => $requestId,
                'cstatus' => $status,
                'capproved_by' => $approvedBy,
                'updated_at' => now(),
            ]);
    }

    private function getPendingRescheduleRequestByToken(string $token): array
    {
        $palletRequest = DB::table('tpalletrequest')
            ->where('creschedule_token', $token)
            ->first();

        if (! $palletRequest) {
            return [null, 'Invalid or expired reschedule link.'];
        }

        if ($palletRequest->creschedule_status !== 'pending') {
            return [null, 'This reschedule request has already been processed.'];
        }

        if ($palletRequest->creschedule_expires_at && Carbon::parse($palletRequest->creschedule_expires_at)->isPast()) {
            return [null, 'This reschedule request has expired.'];
        }

        return [$palletRequest, null];
    }

    /**
     * CRITICAL: Get network partners for a customer
     * Queries BOTH DirectAgreement and NetworkAgreement tables
     * Works for both admin and customer users
     */
    public function getCustomerNetwork(int $customerId): JsonResponse
    {
        $user = Auth::user();

        // For customer users, verify they can only access their own network
        if (!$user->isAdmin() && $user->nidcust != $customerId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $partners = collect();

        // 1. Check Direct Agreements (Yanasurya → Customer)
        // Direct agreements allow customer to RECEIVE from Yanasurya (if user is admin creating delivery)
        if ($user->isAdmin()) {
            $directAgreements = DirectAgreement::where('cstatus', 'active')
                ->where('nidcust', $customerId)
                ->get();

            foreach ($directAgreements as $agreement) {
                // Admin (Yanasurya) can send TO this customer
                $partners->push([
                    'nidcust' => $agreement->nidcust,
                    'ckdcust' => $agreement->ckdcust,
                    'cnmcust' => $agreement->cnmcust,
                    'agreement_type' => 'direct',
                    'agreement_id' => $agreement->cnokontrak,
                ]);
            }
        }

        // 2. Check Network Agreements (Customer ↔ Customer)
        // Network agreements are bidirectional
        $networkAgreements = NetworkAgreement::where('cstatus', 'active')
            ->where(function ($query) use ($customerId) {
                $query->where('nidcust_a', $customerId)
                    ->orWhere('nidcust_b', $customerId);
            })
            ->get();

        foreach ($networkAgreements as $agreement) {
            // Determine partner based on which party the customer is
            if ($agreement->nidcust_a == $customerId) {
                // Customer is Party A, partner is Party B
                $partners->push([
                    'nidcust' => $agreement->nidcust_b,
                    'ckdcust' => $agreement->ckdcust_b,
                    'cnmcust' => $agreement->cnmcust_b,
                    'agreement_type' => 'network',
                    'agreement_id' => $agreement->cnokontrak,
                    'my_role' => $agreement->crole_a,
                    'partner_role' => $agreement->crole_b,
                ]);
            } elseif ($agreement->nidcust_b == $customerId) {
                // Customer is Party B, partner is Party A
                $partners->push([
                    'nidcust' => $agreement->nidcust_a,
                    'ckdcust' => $agreement->ckdcust_a,
                    'cnmcust' => $agreement->cnmcust_a,
                    'agreement_type' => 'network',
                    'agreement_id' => $agreement->cnokontrak,
                    'my_role' => $agreement->crole_b,
                    'partner_role' => $agreement->crole_a,
                ]);
            }
        }

        // Remove duplicates and filter active customers
        $uniquePartnerIds = $partners->pluck('nidcust')->unique();

        $customerDetails = Customer::whereIn('nidcust', $uniquePartnerIds)
            ->where('lnonaktif', false)
            ->orderBy('cnmcust')
            ->get()
            ->map(function ($c) use ($partners) {
                // Find agreement metadata for this partner
                $agreementInfo = $partners->firstWhere('nidcust', $c->nidcust);
                return [
                    'nidcust' => $c->nidcust,
                    'ckdcust' => $c->ckdcust,
                    'cnmcust' => $c->cnmcust,
                    'agreement_type' => $agreementInfo['agreement_type'] ?? null,
                    'agreement_id' => $agreementInfo['agreement_id'] ?? null,
                ];
            });

        return response()->json($customerDetails);
    }

    /**
     * Get addresses for a customer (main office + warehouses)
     */
    public function getCustomerAddresses(Request $request, int $customerId): JsonResponse
    {
        $addresses = $this->getCustomerAddressesArray($customerId, $request->query('city'));
        return response()->json($addresses);
    }

    /**
     * Helper function to get distance between request city & main office/warehouse city
     */
    private function getDistance($kotaUser, $kotaCustomer)
    {
        // ambil lat long kota dari request
        $requestLocation = DB::table('mkota')
            ->where('ckota', $kotaUser)
            ->first();

        // ambil lat dan long dari customerCity
        $customerLocation = DB::table('mkota')
            ->where('ckota', $kotaCustomer)
            ->first();

        if (!$requestLocation || !$customerLocation) {
            return null;
        }

        // return $requestLocation->nlat .' '. $requestLocation->nlong .' '. $customerLocation->nlat .' '. $customerLocation->nlong ;

        

        // hitung distance berdasarkan haversine
        $lat1 = deg2rad($requestLocation->nlat);
        $lon1 = deg2rad($requestLocation->nlong);
        $lat2 = deg2rad($customerLocation->nlat);
        $lon2 = deg2rad($customerLocation->nlong);

        $dlat = $lat2 - $lat1;
        $dlon = $lon2 - $lon1;

        $a = sin($dlat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($dlon / 2) ** 2;
        $c = 2 * asin(sqrt($a));

        $earthRadius = 6371; // km
        $distance = $c * $earthRadius;

        // kalau jarak sangat kecil / sama kota
        if (round($distance, 2) == 0) {
            return 'Same City';
        }

        // tambah margin error 10%
        $distanceWithMargin = $distance * 1.10;

        return round($distanceWithMargin, 2) . ' Km';
    }

    /**
     * Helper method to get customer addresses as array
     */
    private function getCustomerAddressesArray(int $customerId, string $fromCity = ''): array
    {
        $customer = Customer::where('nidcust', $customerId)->first();

        if (!$customer) {
            return [];
        }

        $addresses = [];

        // Add main office address
        $mainAddress = trim(implode(', ', array_filter([
            $customer->calmpt1,
            $customer->calmpt2,
            $customer->calmpt3,
        ])));

        if ($mainAddress && $mainAddress !== '-') {
            $addresses[] = [
                'type' => 'office',
                'label' => 'Main Office - ' . $customer->ckota,
                'address' => $mainAddress,
                'city' => $customer->ckota,
                'ckdwh' => '',
                'distance' => $this->getDistance($fromCity, $customer->ckota),
            ];
        }

        // Add warehouse addresses
        $warehouses = CustomerWarehouse::where('ckdcust', $customer->ckdcust)->get();

        foreach ($warehouses as $warehouse) {
            if ($warehouse->calmtwh) {
                $addresses[] = [
                    'type' => 'warehouse',
                    'label' => ($warehouse->cnmwh ?: 'Warehouse') . ' - ' . $warehouse->ckotawh,
                    'address' => $warehouse->calmtwh,
                    'city' => $warehouse->ckotawh,
                    'ckdwh' => $warehouse->ckdwh,
                    'distance' => $this->getDistance($fromCity, $warehouse->ckotawh),
                ];
            }
        }

        return $addresses;
    }

    /**
     * Get direct agreement customers for a company
     * Returns customers that have active direct agreements with this company
     */
    public function getCompanyDirectAgreements(string $companyCode): JsonResponse
    {
        $user = Auth::user();

        // Allow: admin, warehouseys (company warehouse staff), or customer warehouse_pic with ckdwh
        $isWarehouseYs = $user->role === 'warehouseys' && $user->ckdcomp;
        if (!$user->isAdmin() && !$isWarehouseYs && !$user->ckdwh) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Get all active direct agreements
        $directAgreements = DirectAgreement::where('cstatus', 'active')
            ->get();

        $customerIds = $directAgreements->pluck('nidcust')->unique();

        // Get customer details
        $customers = Customer::whereIn('nidcust', $customerIds)
            ->where('lnonaktif', false)
            ->orderBy('cnmcust')
            ->get()
            ->map(function ($c) use ($directAgreements) {
                // Find agreement for this customer
                $agreement = $directAgreements->firstWhere('nidcust', $c->nidcust);
                return [
                    'nidcust' => $c->nidcust,
                    'ckdcust' => $c->ckdcust,
                    'cnmcust' => $c->cnmcust,
                    'agreement_type' => 'direct',
                    'agreement_id' => $agreement->cnokontrak ?? null,
                ];
            });

        return response()->json($customers);
    }

    /**
     * Generate delivery note number based on entity type and code
     * Used for dynamic DN number generation when admin selects sender
     */
    public function generateDeliveryNoteNumber(Request $request): JsonResponse
    {
        $entityType = $request->input('entity_type'); // 'customer' or 'company'
        $entityCode = $request->input('entity_code'); // ckdcust or ckdcomp

        if ($entityType === 'company') {
            // For company, use default 'YS' code
            $ckodea = 'YS';
        } else {
            // For customer, get ckodea from ymcust
            $ckodea = DB::table('ymcust')
                ->where('ckdcust', $entityCode)
                ->value('ckodea');

            if (!$ckodea) {
                return response()->json(['error' => 'Customer not found'], 404);
            }
        }

        $nosj = Delivery::generateNosj($ckodea);

        return response()->json([
            'delivery_note_number' => $nosj,
            'ckodea' => $ckodea
        ]);
    }
}
