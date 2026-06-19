<?php
include_once('../../Include/config.inc.php');
include_once(path(DIR_INCLUDE) . 'conexiones/db_conexion.php');
include_once(path(DIR_INCLUDE) . 'comun.lib.php');

function limpiar_reporte_compras($valor) {
    $valor = mberegi_replace("[\n|\r|\n\r|\t|\0|\x0B]", " ", $valor);
    $valor = str_replace(array('"', "'"), '', $valor);
    return trim($valor);
}

function numero_reporte_compras($valor) {
    return number_format((float)$valor, 2, '.', '');
}

try {
    session_start();
    global $DSN_Ifx;

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $empresa = isset($_REQUEST['empresa']) ? (int)$_REQUEST['empresa'] : (int)$_SESSION['U_EMPRESA'];
    $sucursal = isset($_REQUEST['sucursal']) ? (int)$_REQUEST['sucursal'] : (int)$_SESSION['U_SUCURSAL'];
    $fecha_inicio = isset($_REQUEST['fecha_inicio']) ? fecha_informix_func($_REQUEST['fecha_inicio']) : date('Y-m-d');
    $fecha_fin = isset($_REQUEST['fecha_fin']) ? fecha_informix_func($_REQUEST['fecha_fin']) : date('Y-m-d');

    $tmpSucursalFprv = '';
    if (!empty($sucursal)) {
        $tmpSucursalFprv = " and f.fprv_cod_sucu = $sucursal";
    }

    $arrayTran = array();
    $sql = "select tran_cod_tran, tran_des_tran from saetran where tran_cod_empr = $empresa";
    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $arrayTran[$oIfx->f('tran_cod_tran')] = $oIfx->f('tran_des_tran');
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();

    $arrayRete = array();
    $sql = "select r.rete_cod_asto, r.asto_cod_sucu, r.asto_cod_ejer, r.asto_num_prdo,
                   r.ret_cta_ret, r.ret_num_ret, r.ret_bas_imp, r.ret_valor
            from saeret r, saeasto a
            where a.asto_cod_empr = r.asto_cod_empr
              and a.asto_cod_sucu = r.asto_cod_sucu
              and a.asto_cod_asto = r.rete_cod_asto
              and a.asto_cod_ejer = r.asto_cod_ejer
              and a.asto_num_prdo = r.asto_num_prdo
              and r.asto_cod_empr = $empresa
              and a.asto_fec_asto between '$fecha_inicio' and '$fecha_fin'
              and a.asto_est_asto != 'AN'" . (!empty($sucursal) ? " and r.asto_cod_sucu = $sucursal" : '');
    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $arrayRete[$oIfx->f('asto_cod_sucu')][$oIfx->f('rete_cod_asto')][$oIfx->f('asto_cod_ejer')][$oIfx->f('asto_num_prdo')][] = array(
                    $oIfx->f('ret_cta_ret'),
                    $oIfx->f('ret_num_ret'),
                    $oIfx->f('ret_bas_imp'),
                    $oIfx->f('ret_valor')
                );
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();

    $data = array();
    $d = 1;
    $sql = "select c.clpv_ruc_clpv, c.clpv_nom_clpv, f.fprv_fec_emis,
                   f.fprv_num_fact, f.fprv_num_seri, f.fprv_det_fprv, f.fprv_val_totl,
                   f.fprv_cod_sucu, f.fprv_cod_ejer, f.fprv_mes_fprv, f.fprv_cod_asto,
                   f.fprv_cod_clpv, f.fprv_cod_tran,
                   COALESCE(sum(case when d.fpid_por_iva = 15 then d.fpid_base_imp else 0 end), 0) as base_15_det,
                   COALESCE(sum(case when d.fpid_por_iva = 5 then d.fpid_base_imp else 0 end), 0) as base_5_det,
                   COALESCE(sum(case when d.fpid_por_iva = 0 then d.fpid_base_imp else 0 end), 0) as base_0_det,
                   COALESCE(sum(case when d.fpid_por_iva = 15 then d.fpid_val_iva else 0 end), 0) as iva_15_det,
                   COALESCE(sum(case when d.fpid_por_iva = 5 then d.fpid_val_iva else 0 end), 0) as iva_5_det,
                   count(d.fpid_num_fact) as cant_det,
                   (COALESCE(f.fprv_val_grab, 0) + COALESCE(f.fprv_val_grbs, 0)) as base_gravada_historica,
                   (COALESCE(f.fprv_val_gra0, 0) + COALESCE(f.fprv_val_gr0s, 0)) as base_0_historica,
                   COALESCE(f.fprv_val_viva, 0) as iva_historico
            from saefprv f
            inner join saeclpv c on f.fprv_cod_empr = c.clpv_cod_empr and f.fprv_cod_clpv = c.clpv_cod_clpv
            inner join saeasto a on a.asto_cod_empr = f.fprv_cod_empr and a.asto_cod_sucu = f.fprv_cod_sucu
                 and a.asto_cod_asto = f.fprv_cod_asto and a.asto_cod_ejer = f.fprv_cod_ejer
                 and cast(a.asto_num_prdo as text) = f.fprv_mes_fprv
            left join saefprv_iva_det d on d.fpid_cod_empr = f.fprv_cod_empr and d.fpid_cod_sucu = f.fprv_cod_sucu
                 and d.fpid_num_fact = f.fprv_num_fact and d.fpid_num_seri = f.fprv_num_seri
                 and d.fpid_cod_clpv = f.fprv_cod_clpv and d.fpid_est_reg = '1'
            where a.asto_est_asto != 'AN'
              and f.fprv_cod_empr = $empresa
              and f.fprv_cod_tran in ('FAC', 'NDB', 'NVE', 'LQC', 'TIC')
              and f.fprv_fec_emis between '$fecha_inicio' and '$fecha_fin'
              $tmpSucursalFprv
            group by 1,2,3,4,5,6,7,8,9,10,11,12,13,20,21,22
            order by 3,5,4";

    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $codSucu = $oIfx->f('fprv_cod_sucu');
                $codAsto = $oIfx->f('fprv_cod_asto');
                $codEjer = $oIfx->f('fprv_cod_ejer');
                $numPrdo = $oIfx->f('fprv_mes_fprv');
                $retenciones = isset($arrayRete[$codSucu][$codAsto][$codEjer][$numPrdo]) ? $arrayRete[$codSucu][$codAsto][$codEjer][$numPrdo] : array();
                $retencion = count($retenciones) > 0 ? $retenciones[0] : array('', '', 0, 0);

                $usaDetalle = ((int)$oIfx->f('cant_det')) > 0;
                $base15 = $usaDetalle ? $oIfx->f('base_15_det') : $oIfx->f('base_gravada_historica');
                $base5 = $usaDetalle ? $oIfx->f('base_5_det') : 0;
                $base0 = $usaDetalle ? $oIfx->f('base_0_det') : $oIfx->f('base_0_historica');
                $iva15 = $usaDetalle ? $oIfx->f('iva_15_det') : $oIfx->f('iva_historico');
                $iva5 = $usaDetalle ? $oIfx->f('iva_5_det') : 0;
                $totalFactura = $usaDetalle ? ((float)$base15 + (float)$base5 + (float)$base0 + (float)$iva15 + (float)$iva5) : $oIfx->f('fprv_val_totl');
                $valorRetenido = (float)$retencion[3];
                $totalPagar = (float)$totalFactura - $valorRetenido;
                $divAsto = "<span style=\"cursor: pointer; font-weigth: bold;\" onclick=\"verDiarioContable(" . $empresa . ", " . $codSucu . ", " . $codEjer . ", " . $numPrdo . ", '" . trim($codAsto) . "')\">" . $codAsto . "</span>";

                $data[] = array(
                    'col' => '<font color=blue>' . $d . '</font>',
                    'col_a' => '<font color=blue>' . limpiar_reporte_compras($oIfx->f('clpv_ruc_clpv')) . '</font>',
                    'col_b' => '<font color=blue>' . limpiar_reporte_compras($oIfx->f('clpv_nom_clpv')) . '</font>',
                    'col_c' => '<font color=blue>' . $oIfx->f('fprv_fec_emis') . '</font>',
                    'col_d' => '<font color=blue>' . (isset($arrayTran[$oIfx->f('fprv_cod_tran')]) ? $arrayTran[$oIfx->f('fprv_cod_tran')] : $oIfx->f('fprv_cod_tran')) . '</font>',
                    'col_e' => '<font color=blue>' . $oIfx->f('fprv_num_seri') . '</font>',
                    'col_f' => '<font color=blue>' . $oIfx->f('fprv_num_fact') . '</font>',
                    'col_g' => '<font color=blue>' . limpiar_reporte_compras($oIfx->f('fprv_det_fprv')) . '</font>',
                    'col_h' => numero_reporte_compras($base15),
                    'col_i' => numero_reporte_compras($base5),
                    'col_j' => numero_reporte_compras($base0),
                    'col_k' => numero_reporte_compras($iva15),
                    'col_l' => numero_reporte_compras($iva5),
                    'col_m' => numero_reporte_compras($totalFactura),
                    'col_n' => $retencion[0],
                    'col_o' => $retencion[1],
                    'col_p' => numero_reporte_compras($retencion[2]),
                    'col_q' => numero_reporte_compras($valorRetenido),
                    'col_r' => numero_reporte_compras($totalPagar),
                    'col_s' => '<font color=blue>' . $divAsto . '</font>'
                );
                $d++;

                for ($i = 1; $i < count($retenciones); $i++) {
                    $data[] = array('col' => $d, 'col_a' => '', 'col_b' => '', 'col_c' => '', 'col_d' => '', 'col_e' => '', 'col_f' => '', 'col_g' => '',
                        'col_h' => '0.00', 'col_i' => '0.00', 'col_j' => '0.00', 'col_k' => '0.00', 'col_l' => '0.00', 'col_m' => '0.00',
                        'col_n' => $retenciones[$i][0], 'col_o' => $retenciones[$i][1], 'col_p' => numero_reporte_compras($retenciones[$i][2]),
                        'col_q' => numero_reporte_compras($retenciones[$i][3]), 'col_r' => '', 'col_s' => '');
                    $d++;
                }
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();

    echo json_encode(array('data' => $data));
} catch (Exception $e) {
    echo json_encode(array('data' => array(), 'error' => $e->getMessage()));
}
?>
