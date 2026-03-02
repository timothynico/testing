<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Display the account transaction report
     */
    public function account_transaction()
    {
        $ckdcust = Auth::user()->ckdcust;
        $isAdminOrSuperAdmin = Auth::user()->isAdmin() || Auth::user()->isSuperAdmin();

        if ($isAdminOrSuperAdmin) {
            $arrcustomer = DB::table('ymcust')->select('ckdcust', 'cnmcust')->get();
            $customernetwork = DB::select("
                select *
                from ytagrnethdr
                left join ymcust on ytagrnethdr.ckdcust_a = ymcust.ckdcust OR ytagrnethdr.ckdcust_b = ymcust.ckdcust
            ");
        } else {
            $arrcustomer = DB::table('ymcust')->where('ckdcust', '=', $ckdcust)->select('ckdcust', 'cnmcust')->get();
            $customernetwork = DB::select("
                select *
                from ytagrnethdr
                left join ymcust on ytagrnethdr.ckdcust_a = ymcust.ckdcust OR ytagrnethdr.ckdcust_b = ymcust.ckdcust
                where (ckdcust_a='" . $ckdcust . "' or ckdcust_b='" . $ckdcust . "')
            ");
        }

        return view('report.account_transaction', compact('arrcustomer', 'customernetwork'));
    }
    /**
     * Display the stock movement report
     */
    public function stock_movement()
    {
        $arrcustomer = array();
        if (Auth::user()->isAdmin()) {
            $ckdcomp = Auth::user()->ckdcomp;
            $arrcustomer = DB::select("SELECT ckdcust, cnmcust FROM ymcust WHERE ckdcomp = '$ckdcomp'");
            $arrwarehouse = DB::select("SELECT ckdwh, cnmwh FROM ymcompwarehouse WHERE ckdcomp = '$ckdcomp'");
        } else if (Auth::user()->isSuperAdmin()) {
            // Gabungan ymcomp dan ymcust
            $arrcustomer = DB::select("
                SELECT ckdcomp as ckdcust, cnmcomp as cnmcust, 'comp' as tipe 
                FROM ymcomp
                UNION ALL
                SELECT ckdcust, cnmcust, 'cust' as tipe 
                FROM ymcust
                ORDER BY cnmcust asc
            ");

            // Gabungan ymcompwarehouse dan ymcustwarehouse
            $arrwarehouse = DB::select("
                SELECT ckdwh, cnmwh FROM ymcompwarehouse
                UNION ALL
                SELECT ckdwh, cnmwh FROM ymcustwarehouse
                ORDER BY cnmwh asc
            ");
        } else {
            $ckdcust = Auth::user()->ckdcust;
            $arrcustomer = DB::select("SELECT ckdcust, cnmcust FROM ymcust WHERE ckdcust = '$ckdcust'");
            $arrwarehouse = DB::select("SELECT ckdwh, cnmwh FROM ymcustwarehouse WHERE ckdcust = '$ckdcust'");
        }
        return view('report.stock_movement', compact('arrcustomer', 'arrwarehouse'));
    }

    /**
     * Export stock movement report to Excel
     * This will be called via AJAX when export functionality is needed
     */
    public function stock_movement_export(Request $request)
    {
        // TODO: Implement export logic when backend is ready
        // This method will handle server-side export generation
        // For now, exports are handled client-side in the blade file
    }

    public function onhand_inventory()
    {
        $user = AUTH::user();
        $ckdcust = $user->ckdcust;

        if ($user->isAdmin() || $user->isSuperAdmin()) {
            $arrdatacust = DB::select("SELECT ckdcust, cnmcust FROM ymcust");
            $arrdatawarehouse = DB::select("SELECT ckdwh, cnmwh FROM ymcustwarehouse");
        } else {
            $arrdatacust = DB::select("SELECT ckdcust, cnmcust FROM ymcust WHERE ckdcust = ?", [$ckdcust]);
            $arrdatawarehouse = DB::select("SELECT ckdwh, cnmwh FROM ymcustwarehouse WHERE ckdcust = ?", [$ckdcust]);
        }

        return view("report.onhand_inventory", compact("arrdatacust", "arrdatawarehouse"));
    }

    public function getOnhandInventory(string $ckdcust = null){
        // $firstdateoftheyear=date("Y-01-01");
        // $lastdateoftheyear=date("Y-12-31");
        $arrinventoryQuery = "SELECT ms.ckdbrg, bsc.cbasic, bsc.cgrade, bsc.cwarna, bsc.nmd, bsc.nmr, ms.nqty, ms.dtglbukti, ms.ckdwh, ms.cstatus, ms.cnobukti, ms.ctempat
            FROM mstock ms
            LEFT JOIN mbasic bsc ON ms.ckdbrg=bsc.ckdbrg";
        $arrinventoryBindings = [];

        if ($ckdcust !== null) {
            $arrinventoryQuery .= " WHERE ms.ctempat = ?";
            $arrinventoryBindings[] = $ckdcust;
        }

        $arrinventory = DB::select($arrinventoryQuery, $arrinventoryBindings);
        //usage
        $datausageQuery = "SELECT dtl.cpallettype,dtl.cusage
            FROM ytagrdirhdr hdr
            LEFT JOIN ytagrdirdtl dtl ON hdr.cnokontrak=dtl.cnokontrak";
        $datausageBindings = [];

        if ($ckdcust !== null) {
            $datausageQuery .= " WHERE hdr.ckdcust = ?";
            $datausageBindings[] = $ckdcust;
        }

        $datausage = DB::select($datausageQuery, $datausageBindings);

        if ($ckdcust !== null) {
            $arrckdwh = DB::select("SELECT ckdwh,cnmwh FROM ymcustwarehouse WHERE ckdcust = ?", [$ckdcust]);
        } else {
            $arrckdwh = DB::select("SELECT ckdwh,cnmwh FROM ymcustwarehouse");
        }
        $arrbasic=array();
        $arrbasic1=DB::select("SELECT ckdbrg,cbasic,cgrade,cwarna,nmd,nmr,npanjang, nlebar, ntinggi FROM mbasic");
        foreach ($arrbasic1 as $key => $value) {
            $cusage="";
            foreach ($datausage as $key1 => $value1) {
                if($value->cbasic==$value1->cpallettype){
                    $cusage=$value1->cusage;
                }  
            }
            $arrbasic[$value->ckdbrg]=array(
                "cbasic"=>$value->cbasic,
                "cgrade"=>$value->cgrade,
                "cwarna"=>$value->cwarna,
                "cusage"=>$cusage,
                "nmd"=>$value->nmd,
                "nmr"=>$value->nmr,
                "npanjang"=>$value->npanjang,
                "nlebar"=>$value->nlebar,
                "ntinggi"=>$value->ntinggi,
            );
        }
        //mulai tahun 2026 karena awal program dikembangkan pada 2026
        $year = 2026;
        $startDate = Carbon::create($year, 1, 1);
        // $endDate   = Carbon::create(date('Y'), 12, 31);
        $endDate   = date('Y-m-d');
        $period = CarbonPeriod::create($startDate, $endDate);
        $arrdatatanggal = [];
        foreach ($period as $date) {
            $arrdatatanggal[] = $date->format('Y-m-d');
        }
        // $arrdata=array();
        // foreach ($arrdatatanggal as $keytgl => $valuetgl) {
        //     $tglsama=false;
        //     foreach ($arrinventory as $keyinv => $valueinv) {
        //         if($valueinv->dtglbukti==$valuetgl){
        //             $tglsama=true;
        //             // $yesterdaynqty=0;
        //             $keyidx=-1;
        //             foreach ($arrdata as $key => $value) {
        //                 if($value['dtgl']==$valuetgl&&$value['ckdbrg']==$valueinv->ckdbrg){
        //                     // $yesterdaynqty=$value['nqty'];
        //                     $keyidx=$key;
        //                 }
        //             }

        //             $yesterdayidx=-1;
        //             foreach ($arrdata as $key2 => $value2) {
        //                 if($value2['dtgl']==$arrdatatanggal[$keytgl-1]&&$value2['ckdbrg']==$valueinv->ckdbrg){
        //                     // $yesterdaynqty=$value['nqty'];
        //                     $yesterdayidx=$key2;
                            
        //                 }
        //             }
        //             if($keyidx==-1){
        //                 // $yesterdayidx=-1;
        //                 // foreach ($arrdata as $key2 => $value2) {
        //                 //     if($value2['dtgl']==$arrdatatanggal[$keytgl-1]&&$value2['ckdbrg']==$valueinv->ckdbrg){
        //                 //         // $yesterdaynqty=$value['nqty'];
        //                 //         $yesterdayidx=$key2;
                                
        //                 //     }
        //                 // }
        //                 // if($yesterdayidx>-1){
        //                 //     array_push($arrdata, 
        //                 //         array(
        //                 //             "dtgl"=>$valueinv->dtglbukti,
        //                 //             "ckdbrg"=>$valueinv->ckdbrg,
        //                 //             "cbasic"=>$valueinv->cbasic,
        //                 //             "cgrade"=>$valueinv->cgrade,
        //                 //             "cwarna"=>$valueinv->cwarna,
        //                 //             "nmd"=>$valueinv->nmd,
        //                 //             "nmr"=>$valueinv->nmr,
        //                 //             "nqty"=>(int)$arrdata[$yesterdayidx]['nqty']+(int)$valueinv->nqty,
        //                 //         )
        //                 //     );
        //                 // }
        //                 // else{
        //                     array_push($arrdata, 
        //                         array(
        //                             "dtgl"=>$valueinv->dtglbukti,
        //                             "ckdbrg"=>$valueinv->ckdbrg,
        //                             "cbasic"=>$valueinv->cbasic,
        //                             "cgrade"=>$valueinv->cgrade,
        //                             "cwarna"=>$valueinv->cwarna,
        //                             "nmd"=>$valueinv->nmd,
        //                             "nmr"=>$valueinv->nmr,
        //                             "nqty"=>(int)$valueinv->nqty,
        //                         )
        //                     );
        //                 // }
        //             }
        //             // else if($yesterdayidx>-1){
        //             //     $arrdata[$keyidx]['nqty']+=(int)$arrdata[$yesterdayidx]['nqty']+(int)$valueinv->nqty;
        //             // }
        //             else{
        //                 $arrdata[$keyidx]['nqty']+=(int)$valueinv->nqty;
        //             }
        //         }
        //     }
        //     if(!$tglsama){
        //         foreach ($arrdata as $key => $value) {
        //             if($value['dtgl']==$arrdatatanggal[$keytgl-1]){
        //                 // $yesterdaynqty=$value['nqty'];
        //                 // array_push(
        //                 //     $arrdata, 
        //                 //     array(
        //                 //         "dtgl"=>$valuetgl,
        //                 //         "ckdbrg"=>$arrdata[$key]['ckdbrg'],
        //                 //         "cbasic"=>$arrdata[$key]['cbasic'],
        //                 //         "cgrade"=>$arrdata[$key]['cgrade'],
        //                 //         "cwarna"=>$arrdata[$key]['cwarna'],
        //                 //         "nmd"=>$arrdata[$key]['nmd'],
        //                 //         "nmr"=>$arrdata[$key]['nmr'],
        //                 //         "nqty"=>$arrdata[$key]['nqty'],
        //                 //     )
        //                 // );
        //             }
        //         }
        //     }
        // }






        $allstockbrg=array();
        // foreach ($arrdatatanggal as $keytgl => $valuetgl) {
        //     $allstockbrg[$valuetgl] = array();
        // }
        $arrdata = array();
        for ($w = 0; $w < count($arrckdwh); $w++) {
            $idxdataperubahan = -1;
            $ckdwh = $arrckdwh[$w]->ckdwh;
            foreach ($arrdatatanggal as $keytgl => $valuetgl) {
    
            // for ($i = 1; $i <= date('t', strtotime($startdatelastmonth)); $i++) {
                $allstockbrg[$valuetgl] = array();
                $arrdataperubahan = array();
                for ($j = 0; $j < count($arrinventory); $j++) {
                    $tempckdwh = "";
                    if ($arrinventory[$j]->cstatus == "A") {
                        $tempckdwh = $arrinventory[$j]->ckdwh;
                    } 
                    else if ($arrinventory[$j]->cstatus == "I") {
                        $tempckdwh = $arrinventory[$j]->ckdwh;
                    } 
                    else if ($arrinventory[$j]->cstatus == "O") {
                        $tempckdwh = $arrinventory[$j]->ckdwh;
                    } 
                    // else if ($arrinventory[$j]->cstatus == "OI") {
                    //     $tempckdwh = $arrinventory[$j]->ckdwh_to;
                    // }
                    if ($tempckdwh == $ckdwh) {
                        if ($arrinventory[$j]->dtglbukti == $valuetgl) {
                            if ($arrinventory[$j]->cstatus == "A") {
                                $adabrg = false;
                                $idxdataperubahan = -1;
                                for ($l = 0; $l < count($arrdataperubahan); $l++) {
                                    if ($arrdataperubahan[$l]['ckdbrg'] == $arrinventory[$j]->ckdbrg && $arrdataperubahan[$l]['ckdwh'] == $tempckdwh) {
                                        $idxdataperubahan = $l;
                                        $adabrg = true;
                                        break;
                                    }
                                }
    
                                if ($adabrg) {
                                    $arrdataperubahan[$idxdataperubahan]['nqty'] += $arrinventory[$j]->nqty;
                                    array_push(
                                        $arrdataperubahan[$idxdataperubahan]['arrbukti'],
                                        array(
                                            "bukti"=>$arrinventory[$j]->cnobukti,
                                            "nqty"=>$arrinventory[$j]->nqty,
                                            "cstatus"=>"A"
                                        )
                                    );
                                } else {
                                    array_push(
                                        $arrdataperubahan,
                                        array(
                                            'ckdbrg' => $arrinventory[$j]->ckdbrg,
                                            'dtgl' => $valuetgl,
                                            'nqty' => $arrinventory[$j]->nqty,
                                            // 'harga' => $arrinventory[$j]->harga,
                                            'ckdwh' => $tempckdwh,
                                            'arrbukti'=>array(
                                                array(
                                                    "bukti"=>$arrinventory[$j]->cnobukti,
                                                    "nqty"=>$arrinventory[$j]->nqty,
                                                    "cstatus"=>"A"
                                                )
                                            )
                                        )
                                    );
                                }
                            }
                            else if ($arrinventory[$j]->cstatus == "I") {
                                $adabrg = false;
                                $idxdataperubahan = -1;
                                for ($l = 0; $l < count($arrdataperubahan); $l++) {
                                    if ($arrdataperubahan[$l]['ckdbrg'] == $arrinventory[$j]->ckdbrg && $arrdataperubahan[$l]['ckdwh'] == $tempckdwh) {
                                        $idxdataperubahan = $l;
                                        $adabrg = true;
                                        break;
                                    }
                                }
    
                                if ($adabrg) {
                                    $arrdataperubahan[$idxdataperubahan]['nqty'] += $arrinventory[$j]->nqty;
                                    array_push(
                                        $arrdataperubahan[$idxdataperubahan]['arrbukti'],
                                        array(
                                            "bukti"=>$arrinventory[$j]->cnobukti,
                                            "nqty"=>$arrinventory[$j]->nqty,
                                            "cstatus"=>"I"
                                        )
                                    );
                                } else {
                                    array_push(
                                        $arrdataperubahan,
                                        array(
                                            'ckdbrg' => $arrinventory[$j]->ckdbrg,
                                            'dtgl' => $valuetgl,
                                            'nqty' => $arrinventory[$j]->nqty,
                                            // 'harga' => $arrinventory[$j]->harga,
                                            'ckdwh' => $tempckdwh,
                                            'arrbukti'=>array(
                                                array(
                                                    "bukti"=>$arrinventory[$j]->cnobukti,
                                                    "nqty"=>$arrinventory[$j]->nqty,
                                                    "cstatus"=>"I"
                                                )
                                            )
                                        )
                                    );
                                }
                            } else if ($arrinventory[$j]->cstatus == "O") {
                                $adabrg = false;
                                $idxdataperubahan = -1;
                                for ($l = 0; $l < count($arrdataperubahan); $l++) {
                                    if ($arrdataperubahan[$l]['ckdbrg'] == $arrinventory[$j]->ckdbrg && $arrdataperubahan[$l]['ckdwh'] == $tempckdwh) {
                                        $idxdataperubahan = $l;
                                        $adabrg = true;
                                        break;
                                    }
                                }
    
                                if ($adabrg) {
                                    $arrdataperubahan[$idxdataperubahan]['nqty'] -= $arrinventory[$j]->nqty;
                                    array_push(
                                        $arrdataperubahan[$idxdataperubahan]['arrbukti'],
                                        array(
                                            "bukti"=>$arrinventory[$j]->cnobukti,
                                            "nqty"=>$arrinventory[$j]->nqty,
                                            "cstatus"=>"O"
                                        )
                                    );
                                } else {
                                    array_push(
                                        $arrdataperubahan,
                                        array(
                                            'ckdbrg' => $arrinventory[$j]->ckdbrg,
                                            'dtgl' => $valuetgl,
                                            'nqty' => - ($arrinventory[$j]->nqty),
                                            // 'harga' => $arrinventory[$j]->harga,
                                            'ckdwh' => $tempckdwh,
                                            'arrbukti'=>array(
                                                array(
                                                    "bukti"=>$arrinventory[$j]->cnobukti,
                                                    "nqty"=>$arrinventory[$j]->nqty,
                                                    "cstatus"=>"O"
                                                )
                                            )
                                        )
                                    );
                                }
                            } 
                            // else if ($arrinventory[$j]->cstatus == "OI") {
                            //     $adabrg = false;
                            //     $idxdataperubahan = -1;
                            //     for ($l = 0; $l < count($arrdataperubahan); $l++) {
                            //         if ($arrdataperubahan[$l]['ckdbrg'] == $arrinventory[$j]->ckdbrg && $arrdataperubahan[$l]['ckdwh'] == $tempckdwh) {
                            //             $idxdataperubahan = $l;
                            //             $adabrg = true;
                            //             break;
                            //         }
                            //     }
    
                            //     if ($adabrg) {
                            //         $arrdataperubahan[$idxdataperubahan]['nqty'] += $arrinventory[$j]->nqty;
                            //         // array_push($arrdataperubahan[$idxdataperubahan]['arrbukti'],$arrdata[$j]->cnobukti);
                            //         array_push(
                            //             $arrdataperubahan[$idxdataperubahan]['arrbukti'],
                            //             array(
                            //                 "bukti"=>$arrinventory[$j]->cnobukti,
                            //                 "nqty"=>$arrinventory[$j]->nqty,
                            //                 "cstatus"=>"I"
                            //             )
                            //         );
                            //     } else {
                            //         array_push(
                            //             $arrdataperubahan,
                            //             array(
                            //                 'ckdbrg' => $arrinventory[$j]->ckdbrg,
                            //                 'dtgl' => $valuetgl,
                            //                 'nqty' => $arrinventory[$j]->nqty,
                            //                 // 'harga' => $arrinventory[$j]->harga,
                            //                 'ckdwh' => $tempckdwh,
                            //                 // 'arrbukti'=>array($arrdata[$j]->cnobukti)
                            //                 'arrbukti'=>array(
                            //                     array(
                            //                         "bukti"=>$arrinventory[$j]->cnobukti,
                            //                         "nqty"=>$arrinventory[$j]->nqty,
                            //                         "cstatus"=>"I"
                            //                     )
                            //                 )
                            //             )
                            //         );
                            //     }
                            // }
                        }
                    }
                }
                for ($k = 0; $k < count($arrdataperubahan); $k++) {
                    if (!isset($allstockbrg[$valuetgl][$arrdataperubahan[$k]['ckdbrg']][$tempckdwh])) {
                        $allstockbrg[$valuetgl][$arrdataperubahan[$k]['ckdbrg']][$tempckdwh] = array(
                            "jumlah" => $arrdataperubahan[$k]['nqty'],
                            // "harga" => $arrdataperubahan[$k]['harga'],
                            "arrbukti"=>$arrdataperubahan[$k]['arrbukti']
                        );
                    } else {
                        $allstockbrg[$valuetgl][$arrdataperubahan[$k]['ckdbrg']][$tempckdwh]["jumlah"] += $arrdataperubahan[$k]['nqty'];
                        // $allstockbrg[$valuetgl][$arrdataperubahan[$k]['ckdbrg']][$tempckdwh]["harga"] = $arrdataperubahan[$k]['harga'];
                        $allstockbrg[$valuetgl][$arrdataperubahan[$k]['ckdbrg']][$tempckdwh]["arrbukti"] = array_merge($allstockbrg[$valuetgl][$arrdataperubahan[$k]['ckdbrg']][$tempckdwh]["arrbukti"],$arrdataperubahan[$k]['arrbukti']);
                    }
                }
            }
           
            foreach ($allstockbrg as $keytgl => $value) {
                if ($allstockbrg[$keytgl] == null) {
                    //jika tidak ada isinya ambil dari hari sebelumnya
                    if (isset($allstockbrg[date("Y-m-d", strtotime($keytgl . " -1 day"))]))
                        foreach ($allstockbrg[date("Y-m-d", strtotime($keytgl . " -1 day"))] as $keybrg => $valuebrg) {
                            foreach ($valuebrg as $keywh => $valuewh) {
                                $allstockbrg[$keytgl][$keybrg][$keywh]["jumlah"] = $allstockbrg[date("Y-m-d", strtotime($keytgl . " -1 day"))][$keybrg][$keywh]["jumlah"];
                                // $allstockbrg[$keytgl][$keybrg][$keywh]["harga"] = $allstockbrg[$keytgl - 1][$keybrg][$keywh]["harga"];
                            }
                        }
                } else {
                    //jika ada isinya dijumlah dengan hari sebelumnya
                    foreach ($allstockbrg[$keytgl] as $keybrg => $valuebrg) {
                        foreach ($valuebrg as $keywh => $valuewh) {
                            if (isset($allstockbrg[date("Y-m-d", strtotime($keytgl . " -1 day"))][$keybrg][$keywh])) {
                                $allstockbrg[$keytgl][$keybrg][$keywh]["jumlah"] += $allstockbrg[date("Y-m-d", strtotime($keytgl . " -1 day"))][$keybrg][$keywh]["jumlah"];
                            }
                        }
                    }
                }
            }
            // foreach ($allstockbrg as $keytgl => $value) {
            //     foreach ($allstockbrg[$keytgl] as $keybrg => $valuebrg) {
            //         foreach ($valuebrg as $keywh => $valuewh) {
            //             $njmlbeforeppn+=(double)$valuewh["jumlah"]*(double)$valuewh["harga"];

            //             $day = $keytgl;

            //             // $lastMonth = date('Y-m', strtotime('first day of last month'));
            //             $lastMonth = date('Y-m', strtotime($startdatelastmonth));

            //             $finalDate = $lastMonth . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);
            //             DB::table('ytfakturpnjdtl')->insert([
            //                 'cnofakturpnj' => $cnofaktur,
            //                 'nno' => $ctrdtl,
            //                 'ctempat' => $ckdcust,
            //                 'ckdwh' => $keywh,
            //                 'cthnbln' => date('Ym'),
            //                 'nqty' => $valuewh["jumlah"],
            //                 'dtgl' => $finalDate,
            //                 'nharga' => $valuewh['harga'],
            //                 'ckdbrg' => $keybrg,
            //             ]);
            //             if(isset($valuewh['arrbukti'])){
            //                 for ($b=0; $b <count($valuewh['arrbukti']) ; $b++) { 
            //                     if(strtoupper(substr($valuewh['arrbukti'][$b]['bukti'],0,2))=="DN"){
            //                         DB::table('ytfakturpnjsjbpb')->insert([
            //                             'cnofakturpnj'=>$cnofaktur,
            //                             'dtglfakturpnj'=>date('Y-m-d'),
            //                             'nno'=>$ctrsjbpb,
            //                             'cnosj'=>$valuewh['arrbukti'][$b]['bukti'],
            //                             'cno_grn'=>"",
            //                             'cstatus'=>$valuewh['arrbukti'][$b]['cstatus'],
            //                             'nqty'=>$valuewh['arrbukti'][$b]['nqty'],
            //                             'nnofakturpnjdtl'=>$ctrdtl
            //                         ]);
            //                     }
            //                     else if(strtoupper(substr($valuewh['arrbukti'][$b]['bukti'],0,2))=="GR"){
            //                         DB::table('ytfakturpnjsjbpb')->insert([
            //                             'cnofakturpnj'=>$cnofaktur,
            //                             'dtglfakturpnj'=>date('Y-m-d'),
            //                             'nno'=>$ctrsjbpb,
            //                             'cnosj'=>"",
            //                             'cno_grn'=>$valuewh['arrbukti'][$b]['bukti'],
            //                             'cstatus'=>$valuewh['arrbukti'][$b]['cstatus'],
            //                             'nqty'=>$valuewh['arrbukti'][$b]['nqty'],
            //                             'nnofakturpnjdtl'=>$ctrdtl
            //                         ]);
            //                     }
            //                     else{
            //                         DB::table('ytfakturpnjsjbpb')->insert([
            //                             'cnofakturpnj'=>$cnofaktur,
            //                             'dtglfakturpnj'=>date('Y-m-d'),
            //                             'nno'=>$ctrsjbpb,
            //                             'cnosj'=>$valuewh['arrbukti'][$b]['bukti'],
            //                             'cno_grn'=>$valuewh['arrbukti'][$b]['bukti'],
            //                             'cstatus'=>$valuewh['arrbukti'][$b]['cstatus'],
            //                             'nqty'=>$valuewh['arrbukti'][$b]['nqty'],
            //                             'nnofakturpnjdtl'=>$ctrdtl
            //                         ]);
            //                     }
            //                     $ctrsjbpb++;
            //                 }                                
            //             }
            //             $ctrdtl++;
            //         }
            //     }
            // }
            foreach ($allstockbrg as $keytgl => $valuetgl) {
                foreach ($valuetgl as $keybrg => $valuebrg) {
                    foreach ($valuebrg as $keywh => $valuewh) {
                        array_push($arrdata, array(
                            'ckdbrg' => $keybrg,
                            'ckdwh' => $keywh,
                            'nqty' => $valuewh["jumlah"],
                            'dtgl' => $keytgl,
                            'ctempat' => $ckdcust,
                            'cbasic' => $arrbasic[$keybrg]['cbasic'],
                            'npanjang' => $arrbasic[$keybrg]['npanjang'],
                            'nlebar' => $arrbasic[$keybrg]['nlebar'],
                            'ntinggi' => $arrbasic[$keybrg]['ntinggi'],
                            'usage'=> $arrbasic[$keybrg]['cusage'],
                            'cwarna' => $arrbasic[$keybrg]['cwarna'],
                            'nmd' => $arrbasic[$keybrg]['nmd'],
                            'nmr' => $arrbasic[$keybrg]['nmr'],
                        ));
                    }
                }
            }
        }
        // dd($allstockbrg);
        // exit();    
        // $arrdata = array();
        // foreach ($allstockbrg as $keytgl => $valuetgl) {
        //     foreach ($valuetgl as $keybrg => $valuebrg) {
        //         foreach ($valuebrg as $keywh => $valuewh) {
        //             array_push($arrdata, array(
        //                 'ckdbrg' => $keybrg,
        //                 'ckdwh' => $keywh,
        //                 'nqty' => $valuewh["jumlah"],
        //                 'dtgl' => $keytgl,
        //                 'ctempat' => $ckdcust,
        //             ));
        //         }
        //     }
        // }
        // dd($arrdata);
        return response()->json($arrdata);




    }

    public function getCustomerStock(string $ckdcustomer = null)
    {
        $cthnbln = date('Ym');

        $baseSelect = "SELECT SUM(st.nqty) as nqty, st.ckdwh, bsc.ckdbrg, 
                        st.cstatus as cstatus, bsc.cbasic, bsc.cgrade, bsc.cwarna, 
                        bsc.cmaterial, bsc.nmd, bsc.nmr, st.cnobukti, st.dtglbukti, 
                        sj.ccust_from as sjccust_from, sj.ccust_to as sjccust_to, 
                        bpb.ccust_from as bpbccust_from, bpb.ccust_to as bpbccust_to,
                        st.ctempat, cust.cnmcust, custwh.cnmwh as warehouse, 
                        st.dtgltagih, st.dtglakhirtagih
                    FROM mstock as st
                    JOIN mbasic as bsc ON st.ckdbrg = bsc.ckdbrg
                    LEFT JOIN ymcustwarehouse as custwh ON st.ckdwh = custwh.ckdwh
                    LEFT JOIN (SELECT ccust_from, ccust_to, cnosj FROM ytsjhdr) as sj 
                        ON st.cnobukti = sj.cnosj
                    LEFT JOIN (SELECT ccust_from, ccust_to, cno_grn FROM ytbpbhdr) as bpb 
                        ON st.cnobukti = bpb.cno_grn
                    LEFT JOIN (SELECT cnmcust, ckdcust FROM ymcust) as cust 
                        ON st.ctempat = cust.ckdcust";

        $baseGroup = "GROUP BY st.cstatus, st.ckdwh, bsc.cbasic, bsc.cgrade, bsc.cwarna, 
                        bsc.cmaterial, bsc.nmd, bsc.nmr, bsc.ckdbrg, st.cnobukti, 
                        st.dtglbukti, sj.ccust_from, sj.ccust_to, bpb.ccust_from, 
                        bpb.ccust_to, st.ctempat, cust.cnmcust, custwh.cnmwh, 
                        st.dtgltagih, st.dtglakhirtagih
                    ORDER BY st.dtglbukti asc";

        $isSuperAdmin = Auth::user()->isSuperAdmin();
        $isAdmin      = Auth::user()->isAdmin();

        if ($isSuperAdmin) {
            // SuperAdmin: lihat semua, atau filter by customer jika dipilih
            if (!empty($ckdcustomer)) {
                $rowdatabarang = DB::select("$baseSelect WHERE st.ctempat = ? AND st.cthnbln = ? $baseGroup", 
                    [$ckdcustomer, $cthnbln]);
            } else {
                $rowdatabarang = DB::select("$baseSelect WHERE st.cthnbln = ? $baseGroup", 
                    [$cthnbln]);
            }

        } elseif ($isAdmin) {
            // Admin: hanya customer di company-nya
            $ckdcomp = Auth::user()->ckdcomp;
            if (!empty($ckdcustomer)) {
                $rowdatabarang = DB::select("$baseSelect 
                    INNER JOIN ymcust cm ON st.ctempat = cm.ckdcust AND cm.ckdcomp = ?
                    WHERE st.ctempat = ? AND st.cthnbln = ? $baseGroup", 
                    [$ckdcomp, $ckdcustomer, $cthnbln]);
            } else {
                $rowdatabarang = DB::select("$baseSelect 
                    INNER JOIN ymcust cm ON st.ctempat = cm.ckdcust AND cm.ckdcomp = ?
                    WHERE st.cthnbln = ? $baseGroup", 
                    [$ckdcomp, $cthnbln]);
            }

        } else {
            // Regular user: hanya bisa lihat data miliknya sendiri, ignore $ckdcustomer dari URL
            $ckdcustomer = Auth::user()->ckdcust;
            $rowdatabarang = DB::select("$baseSelect WHERE st.ctempat = ? AND st.cthnbln = ? $baseGroup", 
                [$ckdcustomer, $cthnbln]);
        }

        return response()->json($rowdatabarang);
    }
}
