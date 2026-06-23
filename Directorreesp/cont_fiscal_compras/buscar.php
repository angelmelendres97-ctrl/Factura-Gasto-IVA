<?php

include_once('../../Include/config.inc.php');
include_once(path(DIR_INCLUDE) . 'conexiones/db_conexion.php');
include_once(path(DIR_INCLUDE) . 'comun.lib.php');

try {

    session_start();
    global $DSN_Ifx, $DSN;

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfxA = new Dbo;
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    if (isset($_REQUEST['empresa'])) {
        $empresa = $_REQUEST['empresa'];
    } else {
        $empresa = $_SESSION['U_EMPRESA'];
    }

    if (isset($_REQUEST['sucursal'])) {
        $sucursal = $_REQUEST['sucursal'];
    } else {
        $sucursal = $_SESSION['U_SUCURSAL'];
    }

    if (isset($_REQUEST['fecha_inicio'])) {
        $fecha_inicio = fecha_informix_func($_REQUEST['fecha_inicio']);
    } else {
        $fecha_inicio = date("Y-m-d");
    }

    if (isset($_REQUEST['fecha_fin'])) {
        $fecha_fin = fecha_informix_func($_REQUEST['fecha_fin']);
    } else {
        $fecha_fin = date("Y-m-d");
    }

    if (isset($_REQUEST['opFactura'])) {
        $opFactura = $_REQUEST['opFactura'];
    } else {
        $opFactura = 0;
    }


    $tabla = '';
    $cero = 0;

    //lectura sucia
    //////////////$oIfx->QueryT('set isolation to dirty read;');

    $tmpSucursal = "";
    $tmpSucursalRete = "";
    $tmpSucursalFprv = "";
    $tmpSucursaliq = "";
    if (!empty($sucursal)) {
        $tmpSucursal = " and m.minv_cod_sucu = $sucursal";
        $tmpSucursaliq =" and minv_cod_sucu = $sucursal";
        $tmpSucursalRete = " and r.asto_cod_sucu = $sucursal";
        $tmpSucursalFprv = " and fprv_cod_sucu = $sucursal";
    }

    $sql = "select tran_cod_tran, tran_des_tran from saetran where tran_cod_empr = $empresa";
    if($oIfx->Query($sql)){
        if($oIfx->NumFilas() > 0){
            unset($arrayTran);
            do{
                $arrayTran[$oIfx->f('tran_cod_tran')] = $oIfx->f('tran_des_tran');
            }while($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();

    $d = 1;

    $sql = "select r.rete_cod_asto, r.asto_cod_sucu, r.asto_cod_ejer, r.asto_num_prdo, 
            r.ret_cta_ret, r.ret_num_ret, r.ret_bas_imp, r.ret_valor
            from saeret r, saeasto a
            where a.asto_cod_empr = r.asto_cod_empr and
            a.asto_cod_sucu = r.asto_cod_sucu and
            a.asto_cod_asto = r.rete_cod_asto and
            a.asto_cod_ejer = r.asto_cod_ejer and
            a.asto_num_prdo = r.asto_num_prdo and 
            r.asto_cod_empr = $empresa and
            a.asto_fec_asto between '$fecha_inicio' and '$fecha_fin' and
            a.asto_est_asto != 'AN'
            $tmpSucursalRete";
    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            unset($arrayRete);
            do {
                $arrayRete[$oIfx->f('asto_cod_sucu')][$oIfx->f('rete_cod_asto')][$oIfx->f('asto_cod_ejer')][$oIfx->f('asto_num_prdo')][] = array($oIfx->f('ret_cta_ret'),
                    $oIfx->f('ret_num_ret'),
                    $oIfx->f('ret_bas_imp'),
                    $oIfx->f('ret_valor'));
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();

    if($opFactura == 1 || $opFactura == 2){
        //consulta facturas de inventario
        $sql = "select m.minv_num_comp, c.clpv_ruc_clpv, c.clpv_nom_clpv, m.minv_fmov, 
				m.minv_fac_prov, m.minv_ser_docu, m.minv_cm1_minv, m.minv_tot_minv,
				minv_dge_valo, minv_cod_clpv, minv_cod_tran, 
				minv_cod_sucu, minv_cod_ejer, minv_num_prdo, minv_comp_cont,
				round(((COALESCE(minv_iva_valo,0) * 100 ) / 15 ),2) as minv_con_iva,
				round(((minv_tot_minv - COALESCE(minv_dge_valo, 0) + COALESCE(minv_otr_valo, 0) + COALESCE(minv_fle_minv, 0)) -  round((( COALESCE(minv_iva_valo, 0) * 100 ) / 15 ),2)),2) as minv_sin_iva,
				round(COALESCE(minv_iva_valo,0),2) as minv_iva_valo,
				round((minv_tot_minv -COALESCE(minv_dge_valo,0) + COALESCE(minv_otr_valo,0 ) + COALESCE(minv_fle_minv,0) +COALESCE(minv_val_ice,0) + COALESCE(minv_iva_valo,0)),2)  as total,
				COALESCE(minv_val_noi, '0') as no_ojeto_iva,
				COALESCE(minv_val_exe, '0') as exento_iva,
				COALESCE(minv_val_ice, '0') as ice
				from saeminv m, saeclpv c
				where
				m.minv_cod_empr = c.clpv_cod_empr and
				m.minv_cod_clpv = c.clpv_cod_clpv and
				m.minv_cod_empr = $empresa and
				M.MINV_COD_TRAN IN ('002', 'I15', '020') AND
				m.minv_est_minv <> '0' and
				m.minv_fmov between '$fecha_inicio' and '$fecha_fin'
				$tmpSucursal
				order by 4";

                
        //echo $sql;
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                do {
                    $minv_num_comp = $oIfx->f('minv_num_comp');
                    $clpv_ruc_clpv = $oIfx->f('clpv_ruc_clpv');
                    $clpv_nom_clpv = $oIfx->f('clpv_nom_clpv');
                    $minv_fmov = ($oIfx->f('minv_fmov'));
                    //$array_fec= explode('/', $minv_fmov);
                    //$minv_fmov = $array_fec[1].'/'.$array_fec[0].'/'.$array_fec[2];

                    $minv_ser_docu = $oIfx->f('minv_ser_docu');
                    $minv_fac_prov = $oIfx->f('minv_fac_prov');
                    $minv_cm1_minv = $oIfx->f('minv_cm1_minv');
                    $minv_tot_minv = $oIfx->f('minv_tot_minv');
                    $minv_con_iva = $oIfx->f('minv_con_iva');
                    $minv_sin_iva = $oIfx->f('minv_sin_iva');
                    $minv_dge_valo = $oIfx->f('minv_dge_valo');
                    $minv_iva_valo = $oIfx->f('minv_iva_valo');
                    $minv_comp_cont = $oIfx->f('minv_comp_cont');
                    $minv_cod_sucu = $oIfx->f('minv_cod_sucu');
                    $minv_cod_ejer = $oIfx->f('minv_cod_ejer');
                    $minv_num_prdo = $oIfx->f('minv_num_prdo');
                    $minv_cod_clpv = $oIfx->f('minv_cod_clpv');
                    $minv_cod_tran = $oIfx->f('minv_cod_tran');
                    $totalMinv = $oIfx->f('total');
                    $minv_val_ice = $oIfx->f('minv_val_ice');

                    $clpv_ruc_clpv = mberegi_replace("[\n|\r|\n\r|\t|\0|\x0B]", "", $clpv_ruc_clpv);
                    $clpv_ruc_clpv = str_replace('"', "", $clpv_ruc_clpv);
                    $clpv_ruc_clpv = str_replace("'", "", $clpv_ruc_clpv);

                    $clpv_nom_clpv = mberegi_replace("[\n|\r|\n\r|\t|\0|\x0B]", "", $clpv_nom_clpv);
                    $clpv_nom_clpv = str_replace('"', "", $clpv_nom_clpv);
                    $clpv_nom_clpv = str_replace("'", "", $clpv_nom_clpv);

                    $minv_cm1_minv = mberegi_replace("[\n|\r|\n\r|\t|\0|\x0B]", "", $minv_cm1_minv);
                    $minv_cm1_minv = str_replace('"', "", $minv_cm1_minv);
                    $minv_cm1_minv = str_replace("'", "", $minv_cm1_minv);

                    $vacio = '';
                    $countTmp = 0;

                    unset($arrayTmpRete);
                    $arrayTmpRete = $arrayRete[$minv_cod_sucu][$minv_comp_cont][$minv_cod_ejer][$minv_num_prdo];
                    $countTmp = count($arrayTmpRete);

                    $i = 1;
                    $ret_cta_ret = '';
                    $ret_num_ret = '';
                    $ret_bas_imp = 0;
                    $ret_valor = 0;
                    if ($countTmp > 0) {
                        foreach ($arrayTmpRete as $val) {
                            if ($i == 1) {
                                $ret_cta_ret = $val[0];
                                $ret_num_ret = $val[1];
                                $ret_bas_imp = $val[2];
                                $ret_valor = $val[3];
                            }
                            $i++;
                        }
                    }

                    $valorReal = 0;
                    $valorReal = $totalMinv - $ret_valor;

                    $divAsto = '';
                    $divAsto = '<span style=\"cursor: pointer; font-weigth: bold;\" onclick=\"verDiarioContable(' . $empresa . ', ' . $minv_cod_sucu . ', ' . $minv_cod_ejer . ', ' . $minv_num_prdo . ', \'' . $minv_comp_cont . '\')\">' . $minv_comp_cont . '</span>';

                    $divRetencion = '';
                    $divRetencion = '<span style=\"cursor: pointer; font-weigth: bold;\" onclick=\"genera_documento(6, \''.$minv_num_comp.'\', \''.$minv_clav_sri.'\', \''.$minv_cod_clpv.'\', \''.$minv_fac_prov.'\', \''.$minv_cod_ejer.'\', \''.$minv_comp_cont.'\', \''.$minv_fmov.'\', '.$minv_cod_sucu.')\">' . $ret_num_ret . '</span>';

                    $tabla .= '{
								"col":"<font color=blue>' . $d . '</font>",
								"col_a":"<font color=blue>' . $clpv_ruc_clpv . '</font>",
								"col_b":"<font color=blue>' . $clpv_nom_clpv . '</font>",
								"col_c":"<font color=blue>'.$minv_fmov.'</font>",
								"col_d":"<font color=blue>' . $arrayTran[$minv_cod_tran] . '</font>",
								"col_e":"<font color=blue>' . $minv_ser_docu . '</font>",
								"col_f":"<font color=blue>' . $minv_fac_prov . '</font>",
								"col_g":"<font color=blue>' . $minv_cm1_minv . '</font>",
								"col_h":"'.$signo.'' . number_format($minv_con_iva,2,'.','') . '</font>",
								"col_i":"'.$signo.'' . number_format($minv_sin_iva,2,'.','') . '</font>",
								"col_j":"'.$signo.'' . number_format($minv_iva_valo,2,'.','') . '</font>",
								"col_jj":"'.$signo.'' . number_format($minv_val_ice,2,'.','') . '</font>",
								"col_k":"'.$signo.'' . number_format($minv_dge_valo,2,'.','') . '</font>",
								"col_l":"'.$signo.'' . number_format($totalMinv,2,'.','') . '</font>",
								"col_m":"<font color=blue>' . $ret_cta_ret . '</font>",
								"col_n":"<font color=blue>' . $divRetencion . '</font>",
								"col_o":"' . number_format($ret_bas_imp,2,'.','') . '",
								"col_p":"' . number_format($ret_valor,2,'.','') . '",
								"col_q":"' . number_format($valorReal,2,'.','') . '",
								"col_r":"<font color=blue>' . $divAsto . '</font>"
							  },';
                              
                    $d++;

                    $i = 1;
                    if ($countTmp > 0) {
                        foreach ($arrayTmpRete as $val) {
                            if ($i > 1) {
                                $ret_cta_ret = $val[0];
                                $ret_num_ret = $val[1];
                                $ret_bas_imp = $val[2];
                                $ret_valor = $val[3];

                                $tabla .= '{
											"col":"' . $d . '",
											"col_a":"",
											"col_b":"",
											"col_c":"",
											"col_d":"",
											"col_e":"",
											"col_f":"",
											"col_g":"",
											"col_h":"' . $cero . '",
											"col_i":"' . $cero . '",
											"col_j":"' . $cero . '",
											"col_jj":"' . $cero . '",
											"col_k":"' . $cero . '",
											"col_l":"' . $cero . '",
											"col_m":"' . $ret_cta_ret . '",
											"col_n":"' . $divRetencion . '",
											"col_o":"' . number_format($ret_bas_imp,2,'.','') . '",
											"col_p":"' . number_format($ret_valor,2,'.','') . '",
											"col_q":"' . number_format($valorReal,2,'.','') . '",
											"col_r":""
										},';
                                $d++;
                            }
                            $i++;
                        }
                    }
                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();
    }

    if($opFactura == 1 || $opFactura == 3){
        //consulta facturas de gastos
        $sql = "select clpv_ruc_clpv, clpv_nom_clpv, fprv_fec_emis, 
				fprv_num_fact, fprv_num_seri, fprv_det_fprv, fprv_val_totl,
				fprv_cod_sucu, fprv_cod_ejer, fprv_mes_fprv, fprv_cod_sucu,
				fprv_cod_asto, fprv_cod_clpv, fprv_cod_tran,
				(COALESCE(fprv_val_grab, 0) + COALESCE(fprv_val_grbs, 0)) as total_graba_12,
				(COALESCE(fprv_val_gra0, 0) + COALESCE(fprv_val_gr0s, 0)) as total_graba_0,
				COALESCE(fprv_val_viva, '0') as valor_iva,
				(COALESCE(fprv_val_grab, 0) + COALESCE(fprv_val_grbs, 0) + COALESCE(fprv_val_gra0, 0) + COALESCE(fprv_val_gr0s, 0) + + COALESCE(fprv_val_vice, 0) + COALESCE(fprv_val_viva, 0)) as total,
				COALESCE(fprv_val_vice,0) as fprv_val_vice
				from saefprv f, saeclpv c, saeasto
				where
				asto_cod_empr = fprv_cod_empr and
				asto_cod_sucu = fprv_cod_sucu and
				asto_cod_asto = fprv_cod_asto and
				asto_cod_ejer = fprv_cod_ejer and
				cast(asto_num_prdo as text) = fprv_mes_fprv and
				asto_cod_empr = clpv_cod_empr and
				fprv_cod_empr = clpv_cod_empr and
				fprv_cod_clpv = clpv_cod_clpv and
				asto_est_asto != 'AN' and
				fprv_cod_empr = $empresa and
				fprv_cod_tran in ('FAC', 'NDB', 'NVE', 'LQC', 'TIC') and
				fprv_fec_emis between '$fecha_inicio' and '$fecha_fin'
				$tmpSucursalFprv
				order by 3";
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                do {
                    $minv_num_comp = $oIfx->f('minv_num_comp');
                    $clpv_ruc_clpv = $oIfx->f('clpv_ruc_clpv');
                    $clpv_nom_clpv = $oIfx->f('clpv_nom_clpv');
                    $minv_fmov = $oIfx->f('fprv_fec_emis');
                    //$array_fec= explode('/', $minv_fmov);
                    //$minv_fmov = $array_fec[1].'/'.$array_fec[0].'/'.$array_fec[2];
                    $minv_ser_docu = $oIfx->f('fprv_num_seri');
                    $minv_fac_prov = $oIfx->f('fprv_num_fact');
                    $minv_cm1_minv = $oIfx->f('fprv_det_fprv');
                    $minv_tot_minv = $oIfx->f('fprv_val_totl');
                    $fprv_val_grab = $oIfx->f('fprv_val_grab');
                    $fprv_val_gra0 = $oIfx->f('fprv_val_gra0');
                    $fprv_val_grbs = $oIfx->f('fprv_val_grbs');
                    $fprv_val_gr0s = $oIfx->f('fprv_val_gr0s');
                    $minv_iva_valo = $oIfx->f('fprv_val_viva');
                    $minv_comp_cont = $oIfx->f('fprv_cod_asto');
                    $minv_cod_sucu = $oIfx->f('fprv_cod_sucu');
                    $minv_cod_ejer = $oIfx->f('fprv_cod_ejer');
                    $minv_num_prdo = $oIfx->f('fprv_mes_fprv');
                    $minv_cod_clpv = $oIfx->f('fprv_cod_clpv');
                    $minv_con_iva = $oIfx->f('total_graba_12');
                    $minv_sin_iva = $oIfx->f('total_graba_0');
                    $minv_iva_valo = $oIfx->f('valor_iva');
                    $minv_cod_tran = $oIfx->f('fprv_cod_tran');
                    $fprv_val_vice = $oIfx->f('fprv_val_vice');

                    $totalMinv = $oIfx->f('total');

                    $clpv_ruc_clpv = mberegi_replace("[\n|\r|\n\r|\t|\0|\x0B]", "", $clpv_ruc_clpv);
                    $clpv_ruc_clpv = str_replace('"', "", $clpv_ruc_clpv);
                    $clpv_ruc_clpv = str_replace("'", "", $clpv_ruc_clpv);

                    $clpv_nom_clpv = mberegi_replace("[\n|\r|\n\r|\t|\0|\x0B]", "", $clpv_nom_clpv);
                    $clpv_nom_clpv = str_replace('"', "", $clpv_nom_clpv);
                    $clpv_nom_clpv = str_replace("'", "", $clpv_nom_clpv);

                    $minv_cm1_minv = mberegi_replace("[\n|\r|\n\r|\t|\0|\x0B]", "", $minv_cm1_minv);
                    $minv_cm1_minv = str_replace('"', "", $minv_cm1_minv);
                    $minv_cm1_minv = str_replace("'", "", $minv_cm1_minv);

                    $vacio = '';
                    $countTmp = 0;

                    unset($arrayTmpRete);
                    $arrayTmpRete = $arrayRete[$minv_cod_sucu][$minv_comp_cont][$minv_cod_ejer][$minv_num_prdo];
                    $countTmp = count($arrayTmpRete);

                    $i = 1;
                    $ret_cta_ret = '';
                    $ret_num_ret = '';
                    $ret_bas_imp = 0;
                    $ret_valor = 0;
                    if ($countTmp > 0) {
                        foreach ($arrayTmpRete as $val) {
                            if ($i == 1) {
                                $ret_cta_ret = $val[0];
                                $ret_num_ret = $val[1];
                                $ret_bas_imp = $val[2];
                                $ret_valor = $val[3];
                            }
                            $i++;
                        }
                    }

                    $valorReal = 0;
                    $valorReal = $totalMinv - $ret_valor;

                    $divAsto = '';
                    $divAsto = '<span style=\"cursor: pointer; font-weigth: bold;\" onclick=\"verDiarioContable(' . $empresa . ', ' . $minv_cod_sucu . ', ' . $minv_cod_ejer . ', ' . $minv_num_prdo . ', \'' . $minv_comp_cont . '\')\">' . $minv_comp_cont . '</span>';

                    $divRetencion = '';
                    $divRetencion = '<span style=\"cursor: pointer; font-weigth: bold;\" onclick=\"genera_documento(5, \''.$vacio.'\',\''.$minv_clav_sri.'\' , \''.$minv_cod_clpv.'\'  , \''.$minv_fac_prov.'\', \''.$minv_cod_ejer.'\', \''.$minv_num_comp.'\',  \''.$minv_fmov.'\', '.$minv_cod_sucu.')\">' . $ret_num_ret . '</span>';

                    $tabla .= '{
								"col":"<font color=blue>' . $d . '</font>",
								"col_a":"<font color=blue>' . $clpv_ruc_clpv . '</font>",
								"col_b":"<font color=blue>' . $clpv_nom_clpv . '</font>",
								"col_c":"<font color=blue>'.$minv_fmov.'",
								"col_d":"<font color=blue>' . $arrayTran[$minv_cod_tran] . '</font>",
								"col_e":"<font color=blue>' . $minv_ser_docu . '</font>",
								"col_f":"<font color=blue>' . $minv_fac_prov . '</font>",
								"col_g":"<font color=blue>' . $minv_cm1_minv . '</font>",
								"col_h":"' . number_format($minv_con_iva,2,'.','') . '",
								"col_i":"' . number_format($minv_sin_iva,2,'.','') . '",
								"col_j":"' . number_format($minv_iva_valo,2,'.','') . '",
								"col_jj":"' . number_format($fprv_val_vice,2,'.','') . '",
								"col_k":"' . number_format($minv_dge_valo,2,'.','') . '",
								"col_l":"' . number_format($totalMinv,2,'.','') . '",
								"col_m":"<font color=blue>' . $ret_cta_ret . '</font>",
								"col_n":"<font color=blue>' . $divRetencion . '</font>",
								"col_o":"' . number_format($ret_bas_imp,2,'.','') . '",
								"col_p":"' . number_format($ret_valor,2,'.','') . '",
								"col_q":"' . number_format($valorReal,2,'.','') . '",
								"col_r":"<font color=blue>' . $divAsto . '</font>"
							  },';
                    //FACTURAS PEQUEÑAS
                    $sqlf="select fprr_cod_tran,fprr_num_fact,fprr_num_seri,fprr_fec_regc,fprr_det_fprr, fprr_fec_emis,fprr_num_esta,
                    fprr_val_tots,fprr_val_gras,fprr_val_grab,fprr_val_gra0, fprr_val_grs0,fprr_val_vivs,fprr_val_vivb, fprr_val_ices,fprr_val_totl,fprr_ruc_prov, fprr_nom_prov
                     from saefprr where  fprr_fac_fprv='$minv_fac_prov' and fprr_clpv_fprv=$minv_cod_clpv order by fprr_cod_fprr";
                    if ($oIfxA->Query($sqlf)) {
                        if ($oIfxA->NumFilas() > 0) {
                            $k=1;
                            do {
                                $fprr_cod_tran = $oIfxA->f('fprr_cod_tran');
                                $fprr_num_fact= $oIfxA->f('fprr_num_fact');
                                $fprr_num_seri=$oIfxA->f('fprr_num_seri');
                                $fprr_num_esta=$oIfxA->f('fprr_num_esta');

                                $fprr_serie=$fprr_num_seri.$fprr_num_esta;
                                $fprr_fec_emis=$oIfxA->f('fprr_fec_emis');
                                $fprr_det_fprr=$oIfxA->f('fprr_det_fprr');
                                $fprr_val_tots=$oIfxA->f('fprr_val_tots');

                                $fprr_val_gras=$oIfxA->f('fprr_val_gras');
                                $fprr_val_grab=$oIfxA->f('fprr_val_grab');

                                $fprr_con_iva=$fprr_val_gras+$fprr_val_grab;

                                $fprr_val_gra0=$oIfxA->f('fprr_val_gra0');
                                $fprr_val_grs0=$oIfxA->f('fprr_val_grs0');

                                $fprr_sin_iva=$fprr_val_gra0+$fprr_val_grs0;




                                $fprr_val_vivs=$oIfxA->f('fprr_val_vivs');
                                $fprr_val_vivb=$oIfxA->f('fprr_val_vivb');

                                $fprr_iva=$fprr_val_vivs+$fprr_val_vivb;
                                
                                
                                $fprr_val_ices=$oIfxA->f('fprr_val_ices');
                                //total general
                                $fprr_val_totl=$oIfxA->f('fprr_val_totl');
                                $fprr_ruc_prov=$oIfxA->f('fprr_ruc_prov');
                                $fprr_nom_prov=$oIfxA->f('fprr_nom_prov');

                                $fprr_ruc_prov = mberegi_replace("[\n|\r|\n\r|\t|\0|\x0B]", "", $fprr_ruc_prov);
                                $fprr_ruc_prov = str_replace('"', "", $fprr_ruc_prov);
                                $fprr_ruc_prov = str_replace("'", "", $fprr_ruc_prov);

                                $fprr_nom_prov = mberegi_replace("[\n|\r|\n\r|\t|\0|\x0B]", "", $fprr_nom_prov);
                                $fprr_nom_prov = str_replace('"', "", $fprr_nom_prov);
                                $fprr_nom_prov = str_replace("'", "", $fprr_nom_prov);

                                $fprr_det_fprr = mberegi_replace("[\n|\r|\n\r|\t|\0|\x0B]", "", $fprr_det_fprr);
                                $fprr_det_fprr = str_replace('"', "", $fprr_det_fprr);
                                $fprr_det_fprr = str_replace("'", "", $fprr_det_fprr);

                                $fminv_dge_valo=0;
                                $fret_cta_ret='';
                                $fdivRetencion = '';
                                $ret_bas_imp=0;
                                $ret_valor=0;
                                $fdivAsto='';
                                $tabla .= '{
                                    "col":"' . $d.'",
                                    "col_a":"' . $fprr_ruc_prov . '",
                                    "col_b":"' . $fprr_nom_prov . '",
                                    "col_c":"'. $fprr_fec_emis.'",
                                    "col_d":"' . $arrayTran[$fprr_cod_tran] . '",
                                    "col_e":"' . $fprr_serie . '",
                                    "col_f":"' . $fprr_num_fact . '",
                                    "col_g":"' . $fprr_det_fprr . '",
                                    "col_h":"' . number_format($fprr_con_iva,2,'.','') . '",
                                    "col_i":"' . number_format($fprr_sin_iva,2,'.','') . '",
                                    "col_j":"' . number_format($fprr_iva,2,'.','') . '",
                                    "col_jj":"' . number_format($fprr_val_ices,2,'.','') . '",
                                    "col_k":"' . number_format($fminv_dge_valo,2,'.','') . '",
                                    "col_l":"' . number_format($fprr_val_totl,2,'.','') . '",
                                    "col_m":"' . $fret_cta_ret . '",
                                    "col_n":"' . $fdivRetencion . '",
                                    "col_o":"' . number_format($ret_bas_imp,2,'.','') . '",
                                    "col_p":"' . number_format($ret_valor,2,'.','') . '",
                                    "col_q":"' . number_format($fprr_val_totl,2,'.','') . '",
                                    "col_r":"' . $fdivAsto . '"
                                  },';
                                  $k++;
                                

                            }while ($oIfxA->SiguienteRegistro());
                        }
                    }
                    $oIfxA->Free();

                    $d++;

                    $i = 1;
                    if ($countTmp > 0) {
                        foreach ($arrayTmpRete as $val) {
                            if ($i > 1) {
                                $ret_cta_ret = $val[0];
                                $ret_num_ret = $val[1];
                                $ret_bas_imp = $val[2];
                                $ret_valor = $val[3];

                                $tabla .= '{
											"col":"' . $d . '",
											"col_a":"",
											"col_b":"",
											"col_c":"",
											"col_d":"",
											"col_e":"",
											"col_f":"",
											"col_g":"",
											"col_h":"' . $cero . '",
											"col_i":"' . $cero . '",
											"col_j":"' . $cero . '",
											"col_jj":"' . $cero . '",
											"col_k":"' . $cero . '",
											"col_l":"' . $cero . '",
											"col_m":"' . $ret_cta_ret . '",
											"col_n":"' . $divRetencion . '",
											"col_o":"' . number_format($ret_bas_imp,2,'.','') . '",
											"col_p":"' . number_format($ret_valor ,2,'.',''). '",
											"col_q":"' . number_format($valorReal,2,'.','') . '",
											"col_r":""
										},';
                                $d++;
                            }
                            $i++;
                        }
                    }
                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();
    }


    //REEMBOLSOS

    if($opFactura == 1 || $opFactura == 5){
        //consulta facturas de reembolso
        $sql = "select clpv_ruc_clpv, clpv_nom_clpv, fprv_fec_emis, 
				fprv_num_fact, fprv_num_seri, fprv_det_fprv, fprv_val_totl,
				fprv_cod_sucu, fprv_cod_ejer, fprv_mes_fprv, fprv_cod_sucu,
				fprv_cod_asto, fprv_cod_clpv, fprv_cod_tran,
				(COALESCE(fprv_val_grab, 0) + COALESCE(fprv_val_grbs, 0)) as total_graba_12,
				(COALESCE(fprv_val_gra0, 0) + COALESCE(fprv_val_gr0s, 0)) as total_graba_0,
				COALESCE(fprv_val_viva, '0') as valor_iva,
				(COALESCE(fprv_val_grab, 0) + COALESCE(fprv_val_grbs, 0) + COALESCE(fprv_val_gra0, 0) + COALESCE(fprv_val_gr0s, 0) + + COALESCE(fprv_val_vice, 0) + COALESCE(fprv_val_viva, 0)) as total,
				COALESCE(fprv_val_vice,0) as fprv_val_vice
				from saefprv f, saeclpv c, saeasto
				where
				asto_cod_empr = fprv_cod_empr and
				asto_cod_sucu = fprv_cod_sucu and
				asto_cod_asto = fprv_cod_asto and
				asto_cod_ejer = fprv_cod_ejer and
				cast(asto_num_prdo as text) = fprv_mes_fprv and
				asto_cod_empr = clpv_cod_empr and
				fprv_cod_empr = clpv_cod_empr and
				fprv_cod_clpv = clpv_cod_clpv and
				asto_est_asto != 'AN' and
				fprv_cod_empr = $empresa and
				fprv_cod_tran like '%CRM%' and
				fprv_fec_emis between '$fecha_inicio' and '$fecha_fin'
				$tmpSucursalFprv
				order by 3";

        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                do {
                    $minv_num_comp = $oIfx->f('minv_num_comp');
                    $clpv_ruc_clpv = $oIfx->f('clpv_ruc_clpv');
                    $clpv_nom_clpv = $oIfx->f('clpv_nom_clpv');
                    $minv_fmov = $oIfx->f('fprv_fec_emis');
                    //$array_fec= explode('/', $minv_fmov);
                    //$minv_fmov = $array_fec[1].'/'.$array_fec[0].'/'.$array_fec[2];
                    $minv_ser_docu = $oIfx->f('fprv_num_seri');
                    $minv_fac_prov = $oIfx->f('fprv_num_fact');
                    $minv_cm1_minv = $oIfx->f('fprv_det_fprv');
                    $minv_tot_minv = $oIfx->f('fprv_val_totl');
                    $fprv_val_grab = $oIfx->f('fprv_val_grab');
                    $fprv_val_gra0 = $oIfx->f('fprv_val_gra0');
                    $fprv_val_grbs = $oIfx->f('fprv_val_grbs');
                    $fprv_val_gr0s = $oIfx->f('fprv_val_gr0s');
                    $minv_iva_valo = $oIfx->f('fprv_val_viva');
                    $minv_comp_cont = $oIfx->f('fprv_cod_asto');
                    $minv_cod_sucu = $oIfx->f('fprv_cod_sucu');
                    $minv_cod_ejer = $oIfx->f('fprv_cod_ejer');
                    $minv_num_prdo = $oIfx->f('fprv_mes_fprv');
                    $minv_cod_clpv = $oIfx->f('fprv_cod_clpv');
                    $minv_con_iva = $oIfx->f('total_graba_12');
                    $minv_sin_iva = $oIfx->f('total_graba_0');
                    $minv_iva_valo = $oIfx->f('valor_iva');
                    $minv_cod_tran = $oIfx->f('fprv_cod_tran');
                    $fprv_val_vice = $oIfx->f('fprv_val_vice');

                    $totalMinv = $oIfx->f('total');

                    $clpv_ruc_clpv = mberegi_replace("[\n|\r|\n\r|\t|\0|\x0B]", "", $clpv_ruc_clpv);
                    $clpv_ruc_clpv = str_replace('"', "", $clpv_ruc_clpv);
                    $clpv_ruc_clpv = str_replace("'", "", $clpv_ruc_clpv);

                    $clpv_nom_clpv = mberegi_replace("[\n|\r|\n\r|\t|\0|\x0B]", "", $clpv_nom_clpv);
                    $clpv_nom_clpv = str_replace('"', "", $clpv_nom_clpv);
                    $clpv_nom_clpv = str_replace("'", "", $clpv_nom_clpv);

                    $minv_cm1_minv = mberegi_replace("[\n|\r|\n\r|\t|\0|\x0B]", "", $minv_cm1_minv);
                    $minv_cm1_minv = str_replace('"', "", $minv_cm1_minv);
                    $minv_cm1_minv = str_replace("'", "", $minv_cm1_minv);

                    $vacio = '';
                    $countTmp = 0;

                    unset($arrayTmpRete);
                    $arrayTmpRete = $arrayRete[$minv_cod_sucu][$minv_comp_cont][$minv_cod_ejer][$minv_num_prdo];
                    $countTmp = count($arrayTmpRete);

                    $i = 1;
                    $ret_cta_ret = '';
                    $ret_num_ret = '';
                    $ret_bas_imp = 0;
                    $ret_valor = 0;
                    if ($countTmp > 0) {
                        foreach ($arrayTmpRete as $val) {
                            if ($i == 1) {
                                $ret_cta_ret = $val[0];
                                $ret_num_ret = $val[1];
                                $ret_bas_imp = $val[2];
                                $ret_valor = $val[3];
                            }
                            $i++;
                        }
                    }

                    $valorReal = 0;
                    $valorReal = $totalMinv - $ret_valor;

                    $divAsto = '';
                    $divAsto = '<span style=\"cursor: pointer; font-weigth: bold;\" onclick=\"verDiarioContable(' . $empresa . ', ' . $minv_cod_sucu . ', ' . $minv_cod_ejer . ', ' . $minv_num_prdo . ', \'' . $minv_comp_cont . '\')\">' . $minv_comp_cont . '</span>';

                    $divRetencion = '';
                    $divRetencion = '<span style=\"cursor: pointer; font-weigth: bold;\" onclick=\"genera_documento(5, \''.$vacio.'\',\''.$minv_clav_sri.'\' , \''.$minv_cod_clpv.'\'  , \''.$minv_fac_prov.'\', \''.$minv_cod_ejer.'\', \''.$minv_num_comp.'\',  \''.$minv_fmov.'\', '.$minv_cod_sucu.')\">' . $ret_num_ret . '</span>';

                    $tabla .= '{
                        "col":"<font color=blue>' . $d . '</font>",
                        "col_a":"<font color=blue>' . $clpv_ruc_clpv . '</font>",
                        "col_b":"<font color=blue>' . $clpv_nom_clpv . '</font>",
                        "col_c":"<font color=blue>'.$minv_fmov.'",
                        "col_d":"<font color=blue>' . $arrayTran[$minv_cod_tran] . '</font>",
                        "col_e":"<font color=blue>' . $minv_ser_docu . '</font>",
                        "col_f":"<font color=blue>' . $minv_fac_prov . '</font>",
                        "col_g":"<font color=blue>' . $minv_cm1_minv . '</font>",
                        "col_h":"' . number_format($minv_con_iva,2,'.','') . '",
                        "col_i":"' . number_format($minv_sin_iva,2,'.','') . '",
                        "col_j":"' . number_format($minv_iva_valo,2,'.','') . '",
                        "col_jj":"' . number_format($fprv_val_vice,2,'.','') . '",
                        "col_k":"' . number_format($minv_dge_valo,2,'.','') . '",
                        "col_l":"' . number_format($totalMinv,2,'.','') . '",
                        "col_m":"<font color=blue>' . $ret_cta_ret . '</font>",
                        "col_n":"<font color=blue>' . $divRetencion . '</font>",
                        "col_o":"' . number_format($ret_bas_imp,2,'.','') . '",
                        "col_p":"' . number_format($ret_valor,2,'.','') . '",
                        "col_q":"' . number_format($valorReal,2,'.','') . '",
                        "col_r":"<font color=blue>' . $divAsto . '</font>"
                      },';

                     //FACTURAS PEQUEÑAS
                     $sqlf="select fprr_cod_tran,fprr_num_fact,fprr_num_seri,fprr_fec_regc,fprr_det_fprr, fprr_fec_emis,fprr_num_esta,
                     fprr_val_tots,fprr_val_gras,fprr_val_grab,fprr_val_gra0, fprr_val_grs0,fprr_val_vivs,fprr_val_vivb, fprr_val_ices,fprr_val_totl,fprr_ruc_prov, fprr_nom_prov
                      from saefprr where  fprr_fac_fprv='$minv_fac_prov' and fprr_clpv_fprv=$minv_cod_clpv order by fprr_cod_fprr";
                     if ($oIfxA->Query($sqlf)) {
                         if ($oIfxA->NumFilas() > 0) {
                             $k=1;
                             do {
                                 $fprr_cod_tran = $oIfxA->f('fprr_cod_tran');
                                 $fprr_num_fact= $oIfxA->f('fprr_num_fact');
                                 $fprr_num_seri=$oIfxA->f('fprr_num_seri');
                                 $fprr_num_esta=$oIfxA->f('fprr_num_esta');
 
                                 $fprr_serie=$fprr_num_seri.$fprr_num_esta;
                                 $fprr_fec_emis=$oIfxA->f('fprr_fec_emis');
                                 $fprr_det_fprr=$oIfxA->f('fprr_det_fprr');
                                 $fprr_val_tots=$oIfxA->f('fprr_val_tots');
 
                                 $fprr_val_gras=$oIfxA->f('fprr_val_gras');
                                 $fprr_val_grab=$oIfxA->f('fprr_val_grab');
 
                                 $fprr_con_iva=$fprr_val_gras+$fprr_val_grab;
 
                                 $fprr_val_gra0=$oIfxA->f('fprr_val_gra0');
                                 $fprr_val_grs0=$oIfxA->f('fprr_val_grs0');
 
                                 $fprr_sin_iva=$fprr_val_gra0+$fprr_val_grs0;
 
 
 
 
                                 $fprr_val_vivs=$oIfxA->f('fprr_val_vivs');
                                 $fprr_val_vivb=$oIfxA->f('fprr_val_vivb');
 
                                 $fprr_iva=$fprr_val_vivs+$fprr_val_vivb;
                                 
                                 
                                 $fprr_val_ices=$oIfxA->f('fprr_val_ices');
                                 //total general
                                 $fprr_val_totl=$oIfxA->f('fprr_val_totl');
                                 $fprr_ruc_prov=$oIfxA->f('fprr_ruc_prov');
                                 $fprr_nom_prov=$oIfxA->f('fprr_nom_prov');
 
                                 $fprr_ruc_prov = mberegi_replace("[\n|\r|\n\r|\t|\0|\x0B]", "", $fprr_ruc_prov);
                                 $fprr_ruc_prov = str_replace('"', "", $fprr_ruc_prov);
                                 $fprr_ruc_prov = str_replace("'", "", $fprr_ruc_prov);
 
                                 $fprr_nom_prov = mberegi_replace("[\n|\r|\n\r|\t|\0|\x0B]", "", $fprr_nom_prov);
                                 $fprr_nom_prov = str_replace('"', "", $fprr_nom_prov);
                                 $fprr_nom_prov = str_replace("'", "", $fprr_nom_prov);
 
                                 $fprr_det_fprr = mberegi_replace("[\n|\r|\n\r|\t|\0|\x0B]", "", $fprr_det_fprr);
                                 $fprr_det_fprr = str_replace('"', "", $fprr_det_fprr);
                                 $fprr_det_fprr = str_replace("'", "", $fprr_det_fprr);
 
                                 $fminv_dge_valo=0;
                                 $fret_cta_ret='';
                                 $fdivRetencion = '';
                                 $ret_bas_imp=0;
                                 $ret_valor=0;
                                 $fdivAsto='';
                                 $tabla .= '{
                                     "col":"' . $d.'",
                                     "col_a":"' . $fprr_ruc_prov . '",
                                     "col_b":"' . $fprr_nom_prov . '",
                                     "col_c":"'. $fprr_fec_emis.'",
                                     "col_d":"' . $arrayTran[$fprr_cod_tran] . '",
                                     "col_e":"' . $fprr_serie . '",
                                     "col_f":"' . $fprr_num_fact . '",
                                     "col_g":"' . $fprr_det_fprr . '",
                                     "col_h":"' . number_format($fprr_con_iva,2,'.','') . '",
                                     "col_i":"' . number_format($fprr_sin_iva,2,'.','') . '",
                                     "col_j":"' . number_format($fprr_iva,2,'.','') . '",
                                     "col_jj":"' . number_format($fprr_val_ices,2,'.','') . '",
                                     "col_k":"' . number_format($fminv_dge_valo,2,'.','') . '",
                                     "col_l":"' . number_format($fprr_val_totl,2,'.','') . '",
                                     "col_m":"' . $fret_cta_ret . '",
                                     "col_n":"' . $fdivRetencion . '",
                                     "col_o":"' . number_format($ret_bas_imp,2,'.','') . '",
                                     "col_p":"' . number_format($ret_valor,2,'.','') . '",
                                     "col_q":"' . number_format($fprr_val_totl,2,'.','') . '",
                                     "col_r":"' . $fdivAsto . '"
                                   },';
                                   $k++;
                                 
 
                             }while ($oIfxA->SiguienteRegistro());
                         }
                     }
                     $oIfxA->Free();
                    $d++;

                    $i = 1;
                    if ($countTmp > 0) {
                        foreach ($arrayTmpRete as $val) {
                            if ($i > 1) {
                                $ret_cta_ret = $val[0];
                                $ret_num_ret = $val[1];
                                $ret_bas_imp = $val[2];
                                $ret_valor = $val[3];

                                $tabla .= '{
											"col":"' . $d . '",
											"col_a":"",
											"col_b":"",
											"col_c":"",
											"col_d":"",
											"col_e":"",
											"col_f":"",
											"col_g":"",
											"col_h":"' . $cero . '",
											"col_i":"' . $cero . '",
											"col_j":"' . $cero . '",
											"col_jj":"' . $cero . '",
											"col_k":"' . $cero . '",
											"col_l":"' . $cero . '",
											"col_m":"' . $ret_cta_ret . '",
											"col_n":"' . $divRetencion . '",
											"col_o":"' . number_format($ret_bas_imp,2,'.','') . '",
											"col_p":"' . number_format($ret_valor ,2,'.',''). '",
											"col_q":"' . number_format($valorReal,2,'.','') . '",
											"col_r":""
										},';
                                $d++;
                            }
                            $i++;
                        }
                    }
                    

                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////


    //LIQUIDACIONES

    if($opFactura == 1 || $opFactura == 6){
        //consulta facturas de inventario
        $sql = "select minv_fmov,
        minv_ser_docu as serie,
        minv_fac_prov,
        minv_tran_minv,
        minv_cod_tran,
        minv_dge_valo,
        minv_num_comp,
        minv_comp_cont,
        minv_cod_sucu,
        minv_cod_ejer,
        minv_num_prdo,
        minv_cod_clpv,
        minv_val_ice,
        clpv_nom_clpv,
        clpv_ruc_clpv,
        minv_cm1_minv as fprv_det_fprv,											
        round(( select sum(dmov_cto_dmov) as base_grava 
                    from  saedmov where 
                    dmov_cod_empr = $empresa and
                    dmov_cod_sucu = saeminv.minv_cod_sucu and
                    dmov_iva_porc = 12 and
                    dmov_num_comp = saeminv.minv_num_comp	 ),2) total_graba_12 ,
        round(( select COALESCE(sum(dmov_cto_dmov), '0') as base_nograva from saedmov where 
                    dmov_cod_empr = $empresa and
                    dmov_cod_sucu = saeminv.minv_cod_sucu and
                    dmov_iva_porc = 0 and
                    dmov_num_comp = saeminv.minv_num_comp ) ,2 ) as total_graba_0 ,

        round(COALESCE(minv_iva_valo,0),2) as valor_iva,
        round((minv_tot_minv -COALESCE(minv_dge_valo,0) + COALESCE(minv_otr_valo,0 ) + COALESCE(minv_fle_minv,0) + COALESCE(minv_iva_valo,0)),2)  as total,
        COALESCE(minv_val_noi, '0') as no_ojeto_iva,
        COALESCE(minv_val_exe, '0') as exento_iva,
        ret_num_ret,
        minv_cod_crtr as sustento
                from saeminv, saeclpv, saeret
                where rete_cod_asto = minv_tran_minv
                and asto_cod_empr = minv_cod_empr   
                and asto_cod_sucu = minv_cod_sucu   
                and asto_cod_ejer = minv_cod_ejer    
                and minv_cod_clpv = clpv_cod_clpv
                and minv_cod_empr = clpv_cod_empr
                and minv_cod_tran in ( select D.DEFI_COD_TRAN 
                                    from SAEDEFI D, SAETRAN T 
                                    WHERE T.TRAN_COD_TRAN = D.DEFI_COD_TRAN 
                                    AND D.DEFI_COD_MODU = 10 
                                    AND D.DEFI_COD_EMPR = $empresa 
                                    AND D.DEFI_TIP_DEFI = '0' 
                                    AND D.DEFI_TIP_COMP in ('03','02') 
                                    AND T.TRAN_COD_EMPR = $empresa )

                and minv_cod_empr  = $empresa
                and minv_fmov between '$fecha_inicio' and '$fecha_fin'
                $tmpSucursaliq
                and minv_est_minv = 'M'
                group by 1,2,3,4,5,6,7,8,9,10,11,12,13, 14,15,16,17,18,19,20,21,22,23,24
                order by 1,10,3 ";

        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                do {
                    $minv_num_comp = $oIfx->f('minv_num_comp');
                    $clpv_ruc_clpv = $oIfx->f('clpv_ruc_clpv');
                    $clpv_nom_clpv = $oIfx->f('clpv_nom_clpv');
                    $minv_fmov = ($oIfx->f('minv_fmov'));
                    //$array_fec= explode('/', $minv_fmov);
                    //$minv_fmov = $array_fec[1].'/'.$array_fec[0].'/'.$array_fec[2];

                    $minv_ser_docu = $oIfx->f('serie');
                    $minv_fac_prov = $oIfx->f('minv_fac_prov');
                    $minv_cm1_minv = $oIfx->f('fprv_det_fprv');

                    $minv_tot_minv = $oIfx->f('total');
                    $minv_con_iva = $oIfx->f('total_graba_12');
                    $minv_sin_iva = $oIfx->f('total_graba_0');
                    $minv_iva_valo = $oIfx->f('valor_iva');

                    $minv_dge_valo = $oIfx->f('minv_dge_valo');
                   
                    $minv_comp_cont = $oIfx->f('minv_comp_cont');
                    $minv_cod_sucu = $oIfx->f('minv_cod_sucu');
                    $minv_cod_ejer = $oIfx->f('minv_cod_ejer');
                    $minv_num_prdo = $oIfx->f('minv_num_prdo');
                    $minv_cod_clpv = $oIfx->f('minv_cod_clpv');
                    $minv_cod_tran = $oIfx->f('minv_cod_tran');
                    $totalMinv = $oIfx->f('total');
                    $minv_val_ice = $oIfx->f('minv_val_ice');

                    $clpv_ruc_clpv = mberegi_replace("[\n|\r|\n\r|\t|\0|\x0B]", "", $clpv_ruc_clpv);
                    $clpv_ruc_clpv = str_replace('"', "", $clpv_ruc_clpv);
                    $clpv_ruc_clpv = str_replace("'", "", $clpv_ruc_clpv);

                    $clpv_nom_clpv = mberegi_replace("[\n|\r|\n\r|\t|\0|\x0B]", "", $clpv_nom_clpv);
                    $clpv_nom_clpv = str_replace('"', "", $clpv_nom_clpv);
                    $clpv_nom_clpv = str_replace("'", "", $clpv_nom_clpv);

                    $minv_cm1_minv = mberegi_replace("[\n|\r|\n\r|\t|\0|\x0B]", "", $minv_cm1_minv);
                    $minv_cm1_minv = str_replace('"', "", $minv_cm1_minv);
                    $minv_cm1_minv = str_replace("'", "", $minv_cm1_minv);

                    $vacio = '';
                    $countTmp = 0;

                    unset($arrayTmpRete);
                    $arrayTmpRete = $arrayRete[$minv_cod_sucu][$minv_comp_cont][$minv_cod_ejer][$minv_num_prdo];
                    $countTmp = count($arrayTmpRete);

                    $i = 1;
                    $ret_cta_ret = '';
                    $ret_num_ret = '';
                    $ret_bas_imp = 0;
                    $ret_valor = 0;
                    if ($countTmp > 0) {
                        foreach ($arrayTmpRete as $val) {
                            if ($i == 1) {
                                $ret_cta_ret = $val[0];
                                $ret_num_ret = $val[1];
                                $ret_bas_imp = $val[2];
                                $ret_valor = $val[3];
                            }
                            $i++;
                        }
                    }

                    $valorReal = 0;
                    $valorReal = $totalMinv - $ret_valor;

                    $divAsto = '';
                    $divAsto = '<span style=\"cursor: pointer; font-weigth: bold;\" onclick=\"verDiarioContable(' . $empresa . ', ' . $minv_cod_sucu . ', ' . $minv_cod_ejer . ', ' . $minv_num_prdo . ', \'' . $minv_comp_cont . '\')\">' . $minv_comp_cont . '</span>';

                    $divRetencion = '';
                    $divRetencion = '<span style=\"cursor: pointer; font-weigth: bold;\" onclick=\"genera_documento(6, \''.$minv_num_comp.'\', \''.$minv_clav_sri.'\', \''.$minv_cod_clpv.'\', \''.$minv_fac_prov.'\', \''.$minv_cod_ejer.'\', \''.$minv_comp_cont.'\', \''.$minv_fmov.'\', '.$minv_cod_sucu.')\">' . $ret_num_ret . '</span>';

                    $tabla .= '{
								"col":"<font color=blue>' . $d . '</font>",
								"col_a":"<font color=blue>' . $clpv_ruc_clpv . '</font>",
								"col_b":"<font color=blue>' . $clpv_nom_clpv . '</font>",
								"col_c":"<font color=blue>'.$minv_fmov.'</font>",
								"col_d":"<font color=blue>' . $arrayTran[$minv_cod_tran] . '</font>",
								"col_e":"<font color=blue>' . $minv_ser_docu . '</font>",
								"col_f":"<font color=blue>' . $minv_fac_prov . '</font>",
								"col_g":"<font color=blue>' . $minv_cm1_minv . '</font>",
								"col_h":"'.$signo.'' . number_format($minv_con_iva,2,'.','') . '",
								"col_i":"'.$signo.'' . number_format($minv_sin_iva,2,'.','') . '",
								"col_j":"'.$signo.'' . number_format($minv_iva_valo,2,'.','') . '",
								"col_jj":"'.$signo.'' . number_format($minv_val_ice,2,'.','') . '",
								"col_k":"'.$signo.'' . number_format($minv_dge_valo,2,'.','') . '",
								"col_l":"'.$signo.'' . number_format($totalMinv,2,'.','') . '",
								"col_m":"<font color=blue>' . $ret_cta_ret . '</font>",
								"col_n":"<font color=blue>' . $divRetencion . '</font>",
								"col_o":"' . number_format($ret_bas_imp,2,'.','') . '",
								"col_p":"' . number_format($ret_valor,2,'.','') . '",
								"col_q":"' . number_format($valorReal,2,'.','') . '",
								"col_r":"<font color=blue>' . $divAsto . '</font>"
							  },';
                    $d++;

                    $i = 1;
                    if ($countTmp > 0) {
                        foreach ($arrayTmpRete as $val) {
                            if ($i > 1) {
                                $ret_cta_ret = $val[0];
                                $ret_num_ret = $val[1];
                                $ret_bas_imp = $val[2];
                                $ret_valor = $val[3];

                                $tabla .= '{
											"col":"' . $d . '",
											"col_a":"",
											"col_b":"",
											"col_c":"",
											"col_d":"",
											"col_e":"",
											"col_f":"",
											"col_g":"",
											"col_h":"' . $cero . '",
											"col_i":"' . $cero . '",
											"col_j":"' . $cero . '",
											"col_jj":"' . $cero . '",
											"col_k":"' . $cero . '",
											"col_l":"' . $cero . '",
											"col_m":"' . $ret_cta_ret . '",
											"col_n":"' . $divRetencion . '",
											"col_o":"' . number_format($ret_bas_imp,2,'.','') . '",
											"col_p":"' . number_format($ret_valor,2,'.','') . '",
											"col_q":"' . number_format($valorReal,2,'.','') . '",
											"col_r":""
										},';
                                $d++;
                            }
                            $i++;
                        }
                    }
                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();
    }


    if($opFactura == 1 || $opFactura == 4){
        // NOTA DE CREDITO DE INVENTARIO
        $sql = "select t.tloc_cod_crtr, c.clv_con_clpv, c.clpv_cod_clpv, nc.ncnd_num_docu,   
			    c.clpv_ruc_clpv, nc.ncnd_cod_tcmp, nc.ncnd_fec_emis, 
				c.clpv_par_rela, c.clpv_cod_tprov, c.clpv_nom_clpv,
				(substring(nc.ncnd_nsr_comp from 1 for 3)) AS estab,
				(substring(nc.ncnd_nsr_comp from 4 for 6)) AS ptoemi,
				nc.ncnd_nse_comp, t.tloc_cod_asto, t.tloc_mes_tloc, 
				t.tloc_ani_tloc, t.tloc_cod_sucu, DATE_PART('month',nc.ncnd_fec_emis) as mes,
				round((COALESCE(t.tloc_bim_ta0b,0)),2) as base_imponible,
				round((COALESCE(t.tloc_bim_tar0,0)),2) as base_imponible1,
				round((COALESCE(t.tloc_bas_imgr,0)),2) as baseimprgrav,
				round((COALESCE(t.tloc_val_mice,0)),2) as montoice,
				round((COALESCE(t.tloc_val_miva,0)),2) as montoiva,
				c.clpv_cod_tpago,
				nc.ncnd_cod_strs,
				(substring(nc.ncnd_num_srcm from 1 for 3)) AS estab_modi,
				(substring(nc.ncnd_num_srcm from 4 for 6)) AS ptoemi_modi,
				nc.ncnd_num_sccm as secu_modi,
				nc.ncnd_num_aucm as auto_modi,
				nc.ncnd_cod_tcmm
				from saencnd nc, saeclpv c, saetloc t where
				c.clpv_ruc_clpv = nc.ncnd_num_docu and
				nc.ncnd_nse_comp = t.tloc_nse_comp and
				c.clpv_ruc_clpv = t.tloc_num_docu and
				ncnd_fec_emis between '$fecha_inicio' and '$fecha_fin' and
				nc.ncnd_ruc_info = (select empr_ruc_empr from saeempr where
								   empr_cod_empr = $empresa) and
				c.clpv_cod_empr = $empresa and
				c.clpv_clopv_clpv = 'PV' and
				nc.ncnd_cod_tcmp in ('04','05') and
                nc.ncnd_cod_tcmp = t.tloc_cod_tcmp";
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas()) {
                do {
                    $cod_sustento = $oIfx->f('tloc_cod_crtr');
                    $tpIdProv = $oIfx->f('clv_con_clpv');
                    $clpv_ruc_clpv = $oIfx->f('clpv_ruc_clpv');
                    $clpv_nom_clpv = $oIfx->f('clpv_nom_clpv');
                    $ncnd_cod_tcmp = $oIfx->f('ncnd_cod_tcmp');
                    $minv_fmov = $oIfx->f('ncnd_fec_emis');
                    //$array_fec= explode('/', $minv_fmov);
                    //$minv_fmov = $array_fec[1].'/'.$array_fec[0].'/'.$array_fec[2];
                    $minv_ser_docu = $oIfx->f('estab').''.$oIfx->f('ptoemi');
                    $minv_fac_prov = $oIfx->f('ncnd_nse_comp');
                    $tloc_cod_asto = $oIfx->f('tloc_cod_asto');
                    $basenograiva = 0;
                    $baseimponible = $oIfx->f('base_imponible');
                    $baseimponible1 = $oIfx->f('base_imponible1');
                    $baseimpgrav = $oIfx->f('baseimprgrav');
                    $montoice = $oIfx->f('montoice');
                    $montoiva = $oIfx->f('montoiva');
                    $secu_modi = $oIfx->f('secu_modi');
                    $estab_modi = $oIfx->f('estab_modi');
                    $ptoemi_modi = $oIfx->f('ptoemi_modi');
                    $clpv_cod = $oIfx->f('clpv_cod_clpv');
                    $minv_num_prdo = $oIfx->f('mes');
                    $tloc_ani_tloc = $oIfx->f('tloc_ani_tloc');
                    $tloc_cod_sucu = $oIfx->f('tloc_cod_sucu');
                    $totalMinv = $baseimponible1 + $baseimpgrav + $montoiva;

                    //ejercicio
                    $sql = "select ejer_cod_ejer from saeejer where DATE_PART('year', ejer_fec_inil) = '$tloc_ani_tloc'";
                    $minv_cod_ejer = consulta_string_func($sql, 'ejer_cod_ejer', $oIfxA, 0);

                    if(empty($tloc_cod_sucu)){
                        $tloc_cod_sucu = $sucursal;
                    }

                    if(empty($tloc_cod_asto)){

                        // AUTORIZACION ES
                        $sql = "select dmcp_cod_asto, dmcp_cod_ejer 
								from saedmcp where 
								dmcp_num_fac like ('%$secu_modi%')  and 
								dmcp_cod_empr =  $empresa and 
								clpv_cod_clpv =   $clpv_cod and
								dmcp_cod_tran not in ('FAC', 'CAN')";
                        if($oIfxA->Query($sql)){
                            if($oIfxA->NumFilas() > 0){
                                do{
                                    $tloc_cod_asto = $oIfxA->f('dmcp_cod_asto');
                                    $minv_cod_ejer = $oIfxA->f('dmcp_cod_ejer');
                                } while ($oIfxA->SiguienteRegistro());
                            }
                        }
                        $oIfxA->Free();
                    }

                    $divAsto = '';
                    $divAsto = '<span style=\"cursor: pointer; font-weigth: bold;\" onclick=\"verDiarioContable(' . $empresa . ', ' . $tloc_cod_sucu . ', ' . $minv_cod_ejer . ', ' . $minv_num_prdo . ', \'' . $tloc_cod_asto . '\')\">' . $tloc_cod_asto . '</span>';


                    $tabla .= '{
								"col":"<font color=blue>' . $d . '</font>",
								"col_a":"<font color=blue>' . $clpv_ruc_clpv . '</font>",
								"col_b":"<font color=blue>' . $clpv_nom_clpv . '</font>",
								"col_c":"<font color=blue>'.$minv_fmov.'</font>",
								"col_d":"<font color=blue>' . $ncnd_cod_tcmp . '</font>",
								"col_e":"<font color=blue>' . $minv_ser_docu . '</font>",
								"col_f":"<font color=blue>' . $minv_fac_prov . '</font>",
								"col_g":"<font color=blue>APLICA A FACTURA: '.$estab_modi.'-'.$ptoemi_modi.' ' . $secu_modi . '</font>",
								"col_h":"-' . number_format($baseimpgrav,2,'.','') . '",
								"col_i":"-' . number_format($baseimponible1,2,'.','') . '",
								"col_j":"-' . number_format($montoiva,2,'.','') . '",
								"col_jj":"",
								"col_k":"' . number_format($vacio,2,'.','') . '",
								"col_l":"-' . number_format($totalMinv,2,'.','') . '",
								"col_m":"' . $vacio . '",
								"col_n":"' . $vacio . '",
								"col_o":"' . $vacio . '",
								"col_p":"' . $vacio . '",
								"col_q":"' . $vacio . '",
								"col_r":"<font color=blue>' . $divAsto . '</font>"
							  },';
                    $d++;

                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();
    }
    //eliminamos la coma que sobra
    $tabla = substr($tabla, 0, strlen($tabla) - 1);

    echo '{"data":[' . $tabla . ']}';
} catch (Exception $e) {
    echo $e->getMessage();
}
?>