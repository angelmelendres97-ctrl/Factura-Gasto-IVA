<?php

include_once('../../Include/config.inc.php');
include_once(path(DIR_INCLUDE) . 'conexiones/db_conexion.php');
include_once(path(DIR_INCLUDE) . 'comun.lib.php');

function limpiar_reporte_compras_nuevo($valor) {
    $valor = mberegi_replace("[\n|\r|\n\r|\t|\0|\x0B]", "", $valor);
    $valor = str_replace('"', '', $valor);
    $valor = str_replace("'", '', $valor);
    return $valor;
}

function numero_reporte_compras_nuevo($valor) {
    return number_format((float)$valor, 2, '.', '');
}

function json_reporte_compras_nuevo($valor) {
    return str_replace(array('\\', '"'), array('\\\\', '\\"'), $valor);
}

function existe_tabla_iva_multiple_reporte_compras($oIfx) {
    $sql = "select count(*) as contador from information_schema.tables where lower(table_name) = 'saefprv_iva_det'";
    $contador = consulta_string_func($sql, 'contador', $oIfx, 0);
    if ($contador > 0) {
        return true;
    }

    $sql = "select count(*) as contador from systables where lower(tabname) = 'saefprv_iva_det'";
    return (consulta_string_func($sql, 'contador', $oIfx, 0) > 0);
}

function agregar_fila_reporte_compras_nuevo(&$tabla, $fila) {
    $tabla .= '{';
    $i = 0;
    foreach ($fila as $campo => $valor) {
        if ($i > 0) {
            $tabla .= ',';
        }
        $tabla .= '"' . $campo . '":"' . json_reporte_compras_nuevo($valor) . '"';
        $i++;
    }
    $tabla .= '},';
}

try {
    session_start();
    global $DSN_Ifx;

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $empresa = isset($_REQUEST['empresa']) ? $_REQUEST['empresa'] : $_SESSION['U_EMPRESA'];
    $sucursal = isset($_REQUEST['sucursal']) ? $_REQUEST['sucursal'] : $_SESSION['U_SUCURSAL'];
    $fecha_inicio = isset($_REQUEST['fecha_inicio']) ? fecha_informix_func($_REQUEST['fecha_inicio']) : date('Y-m-d');
    $fecha_fin = isset($_REQUEST['fecha_fin']) ? fecha_informix_func($_REQUEST['fecha_fin']) : date('Y-m-d');
    $opFactura = isset($_REQUEST['opFactura']) ? $_REQUEST['opFactura'] : 0;

    $tabla = '';
    $tmpSucursalFprv = '';
    $tmpSucursalRete = '';
    if (!empty($sucursal)) {
        $tmpSucursalFprv = " and f.fprv_cod_sucu = $sucursal";
        $tmpSucursalRete = " and r.asto_cod_sucu = $sucursal";
    }

    $arrayTran = array();
    $sql = "select tran_cod_tran, tran_des_tran from saetran where tran_cod_empr = $empresa";
    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $arrayTran[$oIfx->f('tran_cod_tran')] = limpiar_reporte_compras_nuevo($oIfx->f('tran_des_tran'));
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();

    $arrayRete = array();
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
            do {
                $arrayRete[$oIfx->f('asto_cod_sucu')][$oIfx->f('rete_cod_asto')][$oIfx->f('asto_cod_ejer')][$oIfx->f('asto_num_prdo')][] = array(
                    limpiar_reporte_compras_nuevo($oIfx->f('ret_cta_ret')),
                    limpiar_reporte_compras_nuevo($oIfx->f('ret_num_ret')),
                    (float)$oIfx->f('ret_bas_imp'),
                    (float)$oIfx->f('ret_valor')
                );
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();

    if ($opFactura == 1 || $opFactura == 3 || $opFactura == 5 || $opFactura == 6) {
        $filtroTransaccion = "f.fprv_cod_tran in ('FAC', 'NDB', 'NVE', 'LQC', 'TIC')";
        if ($opFactura == 5) {
            $filtroTransaccion = "f.fprv_cod_tran like '%CRM%'";
        } elseif ($opFactura == 6) {
            $filtroTransaccion = "f.fprv_cod_tran = 'LQC'";
        }

        $joinIvaMultiple = "left join (
                    select 0 as fpid_cod_empr, 0 as fpid_cod_sucu, '' as fpid_num_fact, '' as fpid_num_seri, 0 as fpid_cod_clpv,
                           0 as valor_15, 0 as valor_5, 0 as valor_0, 0 as iva_15, 0 as iva_5
                    from systables
                    where tabid = 1 and 1 = 0
                ) iva on 1 = 0";
        if (existe_tabla_iva_multiple_reporte_compras($oIfx)) {
            $joinIvaMultiple = "left join (
                    select fpid_cod_empr, fpid_cod_sucu, fpid_num_fact, fpid_num_seri, fpid_cod_clpv,
                           sum(case when round(fpid_por_iva, 2) = 15 then fpid_base_imp else 0 end) as valor_15,
                           sum(case when round(fpid_por_iva, 2) = 5 then fpid_base_imp else 0 end) as valor_5,
                           sum(case when round(fpid_por_iva, 2) = 0 then fpid_base_imp else 0 end) as valor_0,
                           sum(case when round(fpid_por_iva, 2) = 15 then fpid_val_iva else 0 end) as iva_15,
                           sum(case when round(fpid_por_iva, 2) = 5 then fpid_val_iva else 0 end) as iva_5
                    from saefprv_iva_det
                    where fpid_est_reg = '1'
                    group by fpid_cod_empr, fpid_cod_sucu, fpid_num_fact, fpid_num_seri, fpid_cod_clpv
                ) iva on iva.fpid_cod_empr = f.fprv_cod_empr and
                         iva.fpid_cod_sucu = f.fprv_cod_sucu and
                         iva.fpid_num_fact = f.fprv_num_fact and
                         iva.fpid_num_seri = f.fprv_num_seri and
                         iva.fpid_cod_clpv = f.fprv_cod_clpv";
        }

        $sql = "select c.clpv_ruc_clpv, c.clpv_nom_clpv, f.fprv_fec_emis,
                       f.fprv_num_fact, f.fprv_num_seri, f.fprv_det_fprv,
                       f.fprv_cod_sucu, f.fprv_cod_ejer, f.fprv_mes_fprv,
                       f.fprv_cod_asto, f.fprv_cod_clpv, f.fprv_cod_tran,
                       COALESCE(iva.valor_15, COALESCE(f.fprv_val_grab, 0) + COALESCE(f.fprv_val_grbs, 0)) as valor_15,
                       COALESCE(iva.valor_5, 0) as valor_5,
                       COALESCE(iva.valor_0, COALESCE(f.fprv_val_gra0, 0) + COALESCE(f.fprv_val_gr0s, 0)) as valor_0,
                       COALESCE(iva.iva_15, COALESCE(f.fprv_val_viva, 0)) as iva_15,
                       COALESCE(iva.iva_5, 0) as iva_5,
                       (COALESCE(iva.valor_15, COALESCE(f.fprv_val_grab, 0) + COALESCE(f.fprv_val_grbs, 0)) +
                        COALESCE(iva.valor_5, 0) +
                        COALESCE(iva.valor_0, COALESCE(f.fprv_val_gra0, 0) + COALESCE(f.fprv_val_gr0s, 0)) +
                        COALESCE(iva.iva_15, COALESCE(f.fprv_val_viva, 0)) +
                        COALESCE(iva.iva_5, 0) + COALESCE(f.fprv_val_vice, 0)) as total_factura
                from saefprv f
                inner join saeclpv c on f.fprv_cod_empr = c.clpv_cod_empr and f.fprv_cod_clpv = c.clpv_cod_clpv
                inner join saeasto a on a.asto_cod_empr = f.fprv_cod_empr and
                                        a.asto_cod_sucu = f.fprv_cod_sucu and
                                        a.asto_cod_asto = f.fprv_cod_asto and
                                        a.asto_cod_ejer = f.fprv_cod_ejer and
                                        cast(a.asto_num_prdo as text) = f.fprv_mes_fprv
                $joinIvaMultiple
                where a.asto_est_asto != 'AN' and
                      f.fprv_cod_empr = $empresa and
                      $filtroTransaccion and
                      f.fprv_fec_emis between '$fecha_inicio' and '$fecha_fin'
                      $tmpSucursalFprv
                order by f.fprv_fec_emis, f.fprv_num_seri, f.fprv_num_fact";

        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                do {
                    $codSucu = $oIfx->f('fprv_cod_sucu');
                    $codAsto = $oIfx->f('fprv_cod_asto');
                    $codEjer = $oIfx->f('fprv_cod_ejer');
                    $numPrdo = $oIfx->f('fprv_mes_fprv');
                    $codTran = $oIfx->f('fprv_cod_tran');
                    $codClpv = $oIfx->f('fprv_cod_clpv');
                    $numFact = limpiar_reporte_compras_nuevo($oIfx->f('fprv_num_fact'));
                    $fecha = $oIfx->f('fprv_fec_emis');
                    $totalFactura = (float)$oIfx->f('total_factura');
                    $retenciones = isset($arrayRete[$codSucu][$codAsto][$codEjer][$numPrdo]) ? $arrayRete[$codSucu][$codAsto][$codEjer][$numPrdo] : array();
                    $totalRetenido = 0;
                    foreach ($retenciones as $retencionTmp) {
                        $totalRetenido += (float)$retencionTmp[3];
                    }
                    if (count($retenciones) == 0) {
                        $retenciones[] = array('', '', 0, 0);
                    }

                    $primera = true;
                    foreach ($retenciones as $retencion) {
                        $divAsto = '';
                        if ($primera) {
                            $divAsto = '<span style=\"cursor: pointer; font-weigth: bold;\" onclick=\"verDiarioContable(' . $empresa . ', ' . $codSucu . ', ' . $codEjer . ', ' . $numPrdo . ', \'' . $codAsto . '\')\">' . $codAsto . '</span>';
                        }

                        $divRetencion = '';
                        if (!empty($retencion[1])) {
                            $divRetencion = '<span style=\"cursor: pointer; font-weigth: bold;\" onclick=\"genera_documento(5, \'\',\'no_autorizado\' , \'' . $codClpv . '\'  , \'' . $numFact . '\', \'' . $codEjer . '\', \'' . $codAsto . '\',  \'' . $fecha . '\', ' . $codSucu . ')\">' . $retencion[1] . '</span>';
                        }

                        agregar_fila_reporte_compras_nuevo($tabla, array(
                            'ruc' => $primera ? limpiar_reporte_compras_nuevo($oIfx->f('clpv_ruc_clpv')) : '',
                            'razon_social' => $primera ? limpiar_reporte_compras_nuevo($oIfx->f('clpv_nom_clpv')) : '',
                            'fecha' => $primera ? $fecha : '',
                            'tipo' => $primera ? (isset($arrayTran[$codTran]) ? $arrayTran[$codTran] : $codTran) : '',
                            'serie' => $primera ? limpiar_reporte_compras_nuevo($oIfx->f('fprv_num_seri')) : '',
                            'secuencial' => $primera ? $numFact : '',
                            'detalle' => $primera ? limpiar_reporte_compras_nuevo($oIfx->f('fprv_det_fprv')) : '',
                            'valor_15' => $primera ? numero_reporte_compras_nuevo($oIfx->f('valor_15')) : '0.00',
                            'valor_5' => $primera ? numero_reporte_compras_nuevo($oIfx->f('valor_5')) : '0.00',
                            'valor_0' => $primera ? numero_reporte_compras_nuevo($oIfx->f('valor_0')) : '0.00',
                            'iva_15' => $primera ? numero_reporte_compras_nuevo($oIfx->f('iva_15')) : '0.00',
                            'iva_5' => $primera ? numero_reporte_compras_nuevo($oIfx->f('iva_5')) : '0.00',
                            'total_factura' => $primera ? numero_reporte_compras_nuevo($totalFactura) : '0.00',
                            'codigo_retencion' => $retencion[0],
                            'numero_retencion' => $divRetencion,
                            'valor_base_retencion' => numero_reporte_compras_nuevo($retencion[2]),
                            'valor_retenido' => numero_reporte_compras_nuevo($retencion[3]),
                            'total_pagar' => $primera ? numero_reporte_compras_nuevo($totalFactura - $totalRetenido) : '',
                            'asiento_contable' => $divAsto
                        ));
                        $primera = false;
                    }
                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();
    }

    $tabla = substr($tabla, 0, -1);
    echo '{"data":[' . $tabla . ']}';
} catch (Exception $e) {
    echo '{"data":[],"error":"' . json_reporte_compras_nuevo($e->getMessage()) . '"}';
}
?>
