<?php

require("_Ajax.comun.php"); // No modificar esta linea

/* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
  // S E R V I D O R   A J A X //
  :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */

/* * **************************************************************** */
/* DF01 :: G E N E R A    F O R M U L A R I O    P E D I D O       */
/* * **************************************************************** */

function cargar_lista($aForm = '') {
    //Definiciones
    global $DSN_Ifx;

    session_start();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oReturn = new xajaxResponse();

    //variables de session
    $idsucursal = $_SESSION['U_SUCURSAL'];

    //variables del formulario
    $empresa = $aForm['empresa'];

    //////////////$oIfx->QueryT('set isolation to dirty read;');

    $sql = "select sucu_cod_sucu, sucu_nom_sucu from saesucu where sucu_cod_empr = $empresa";
    $i = 1;
    if ($oIfx->Query($sql)) {
        $oReturn->script('eliminar_lista();');
        if ($oIfx->NumFilas() > 0) {
            do {
                $oReturn->script('anadir_elemento(' . $i++ . ',\'' . $oIfx->f('sucu_cod_sucu') . '\', \'' . $oIfx->f('sucu_nom_sucu') . '\' )');
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();

    $oReturn->assign('sucursal', 'value', $idsucursal);

    return $oReturn;
}

function consultar($aForm = '') {
    //Definiciones
    global $DSN_Ifx, $DSN;

    session_start();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oConA = new Dbo;
    $oConA->DSN = $DSN;
    $oConA->Conectar();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oReturn = new xajaxResponse();

    //variables de session
    $idempresa = $_SESSION['U_EMPRESA'];
    $idsucursal = $_SESSION['U_SUCURSAL'];

    //variables del formulario
    $empresa = $aForm['empresa'];
    $sucursal = $aForm['sucursal'];
    $fecha_inicio = fecha_informix_func($aForm['fecha_inicio']);
    $fecha_fin = fecha_informix_func($aForm['fecha_fin']);

    try {

        //LECTURA SUCIA
        //////////////$oIfx->QueryT('set isolation to dirty read;');

        $table .= '<table class="table table-striped table-bordered table-hover table-condensed">';
        $table .= '<tr class="info">
                        <td class="bg-info">RUC</td>
                        <td class="bg-info">Razon Social</td>
                        <td class="bg-info">Fecha</td>
                        <td class="bg-info">Tipo</td>
                        <td class="bg-info">Serie</td>
                        <td class="bg-info">Secuencial</td>
                        <td class="bg-info">Detalle</td>
                        <td class="bg-info">Valor Impuesto 15%</td>
                        <td class="bg-info">Valor Impuesto 0%</td>
                        <td class="bg-info">Valor  Impuesto</td>
                        <td class="bg-info">Descuentos</td>
                        <td class="bg-info">Total Factura</td>
                        <td class="bg-info">C&oacute;digo Retenci&oacute;n</td>
                        <td class="bg-info">No. Retenci&oacute;n</td>
                        <td class="bg-info">Valor Base Retenci&oacute;n</td>
                        <td class="bg-info">Valor Retenido</td>
                        <td class="bg-info">Total a Pagar</td>
                        <td class="bg-info">Asiento Contable</td>
                    </tr>';

        $tmpSucursal = "";
        if (!empty($sucursal)) {
            $tmpSucursal = " and minv_cod_sucu = $sucursal";
        }

        $sql = "select m.minv_num_comp, c.clpv_ruc_clpv, c.clpv_nom_clpv, m.minv_fmov, 
                m.minv_m.minv_fac_prov, minv_cod_ejer, minv_num_prdo, minv_cod_sucu
                from saeminv m, saeclpv c
                where
                m.minv_cod_empr = c.clpv_cod_empr and
                m.minv_cod_clpv = m.minv_cod_clpv and
                m.minv_cod_empr = $empresa and
                c.clpv_clopv_clpv = 'PV' and
                m.minv_cod_tran = '002' and
                m.minv_est_minv <> '0' and
                m.minv_fmov between '$fecha_inicio' and '$fecha_fin'
                order by 2";
        //$oReturn->alert($sql);		   
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                $i = 1;
                $totalCant = 0;
                $totalCosto = 0;
                $granTotal = 0;
                unset($arrayCtrl);
                do {
                    $minv_num_comp = $oIfx->f('minv_num_comp');
                    $minv_fmov = $oIfx->f('minv_fmov');
                    $dmov_cod_prod = $oIfx->f('dmov_cod_prod');
                    $prod_nom_prod = $oIfx->f('prod_nom_prod');
                    $cantidad = $oIfx->f('cantidad');
                    $costo = $oIfx->f('costo');
                    $clpv_ruc_clpv = $oIfx->f('clpv_ruc_clpv');
                    $clpv_nom_clpv = $oIfx->f('clpv_nom_clpv');
                    $minv_fac_prov = $oIfx->f('minv_fac_prov');
                    $minv_fac_prov = $oIfx->f('minv_fac_prov');
                    $minv_fac_prov = $oIfx->f('minv_fac_prov');
                    $minv_fac_prov = $oIfx->f('minv_fac_prov');

                    $total = $cantidad * $costo;

                    $arrayCtrl[$i] = $minv_fac_prov;

                    if ($i > 0) {
                        if ($arrayCtrl[$i] == $arrayCtrl[$i - 1]) {
                            $minv_fac_prov = '';
                            $clpv_nom_clpv = '';
                        }
                    }

                    $table .= '<tr>
								   <td align="center">' . $i . '</td>
								   <td align="left">' . $clpv_nom_clpv . '</td>
								   <td align="left">' . $minv_fmov . '</td>
								   <td align="left">' . $minv_fac_prov . '</td>
								   <td align="left">' . $dmov_cod_prod . '</td>
								   <td align="left">' . $prod_nom_prod . '</td>
								   <td align="right">' . $cantidad . '</td>
								   <td align="right">' . $costo . '</td>
								   <td align="right">' . $total . '</td>
							   </tr>';
                    $i++;
                    $totalCant += $cantidad;
                    $totalCosto += $costo;
                    $granTotal += $total;
                } while ($oIfx->SiguienteRegistro());
                $table .= '<tr class="danger">
							   <td align="right" colspan="6">TOTAL:</td>
							   <td align="right">' . $totalCant . '</td>
							   <td align="right">' . $totalCosto . '</td>
							   <td align="right">' . $granTotal . '</td>
						   </tr>';
            }
        }
        $oIfx->Free();

        $table .= '</table>';

        //Armado Cabecera Excel
        unset($_SESSION['sHtml_cab']);
        unset($_SESSION['sHtml_det']);
        $sHtml_exe_p .= '<table align="center" border="0" cellpadding="2" cellspacing="1" width="100%">
								<tr>
										<th colspan = "15">REPORTE COMPRAS</th>
								</tr>
								<tr></tr><tr></tr>
										<th colspan="2">Fecha Reporte:</th>
										<td align="left">' . date("d-m-Y") . '</td>
										<td></td>
								</tr>
								<tr></tr><tr></tr>
							</table>';

        $_SESSION['sHtml_cab'] = $sHtml_exe_p;
        $_SESSION['sHtml_det'] = $table;

        $oReturn->assign("divFormularioDetalle", "innerHTML", $table);
    } catch (Exception $e) {
        $oReturn->alert($e->getMessage());
    }//fin if try

    return $oReturn;
}

function verDiarioContable($aForm = '', $empr = 0, $sucu = 0, $ejer = 0, $mes = 0, $asto = '') {

    session_start();
    global $DSN_Ifx, $DSN;

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oReturn = new xajaxResponse();

    //variables del formulario
    $campo = 0;

    $class = new GeneraDetalleAsientoContable();

    $arrayAsto = $class->informacionAsientoContable($oIfx, $empr, $sucu, $ejer, $mes, $asto);

    $arrayDiario = $class->diarioAsientoContable($oIfx, $empr, $sucu, $ejer, $mes, $asto);

    $arrayDirectorio = $class->directorioAsientoContable($oIfx, $empr, $sucu, $ejer, $mes, $asto);

    $arrayRetencion = $class->retencionAsientoContable($oIfx, $empr, $sucu, $ejer, $mes, $asto);

    $arrayAdjuntos = $class->adjuntosAsientoContable($oCon, $empr, $sucu, $ejer, $mes, $asto);

    try {

        //LECTURA SUCIA1
        //////////////$oIfx->QueryT('set isolation to dirty read;');


        //sucursal
        $sql = "select sucu_nom_sucu from saesucu where sucu_cod_sucu = $sucu";
        $sucu_nom_sucu = consulta_string_func($sql, 'sucu_nom_sucu', $oIfx, '');


        $oReturn->assign("divTituloAsto", "innerHTML", $asto . ' - ' . $sucu_nom_sucu);

        if (count($arrayAsto) > 0) {

            $table .= '<table class="table table-striped table-condensed" align="center" width="98%">';
            $table .= '<tr>';
            $table .= '<td colspan="4" class="bg-primary">DIARIO CONTABLE</td>';
            $table .= '</tr>';

            foreach ($arrayAsto as $val) {
                $asto_cod_asto = $val[0];
                $asto_vat_asto = $val[1];
                $asto_ben_asto = $val[2];
                $asto_fec_asto = $val[3];
                $asto_det_asto = $val[4];
                $asto_cod_modu = $val[5];
                $asto_usu_asto = $val[6];
                $asto_user_web = $val[7];
                $asto_fec_serv = $val[8];
                $asto_cod_tidu = $val[9];

                //modulo
                $sql = "select modu_des_modu from saemodu where modu_cod_modu = $asto_cod_modu";
                $modu_des_modu = consulta_string_func($sql, 'modu_des_modu', $oIfx, '');

                //tipo documento
                $sql = "select tidu_des_tidu from saetidu where tidu_cod_tidu = '$asto_cod_tidu'";
                $tidu_des_tidu = consulta_string_func($sql, 'tidu_des_tidu', $oIfx, '');

                $table .= '<tr>';
                $table .= '<td>Diario:</td>';
                $table .= '<td>' . $asto_cod_asto . '</td>';
                $table .= '<td>Fecha:</td>';
                $table .= '<td>' . $asto_fec_asto . '</td>';
                $table .= '</tr>';

                $table .= '<tr>';
                $table .= '<td>Beneficiario:</td>';
                $table .= '<td colspan="3">' . $asto_ben_asto . '</td>';
                $table .= '</tr>';

                $table .= '<tr>';
                $table .= '<td>Modulo:</td>';
                $table .= '<td>' . $modu_des_modu . '</td>';
                $table .= '<td>Documento:</td>';
                $table .= '<td>' . $asto_cod_tidu . ' - ' . $tidu_des_tidu . '</td>';
                $table .= '</tr>';

                $table .= '<tr>';
                $table .= '<td>Detalle:</td>';
                $table .= '<td colspan="3">' . $asto_det_asto . '</td>';
                $table .= '</tr>';
                //sucursal, cod_prove, asto_cod, ejer_cod, prdo_cod
                $table .= '<tr>';
                $table .= '<td>Formato:</td>';
                $table .= '<td align="left">
							<div class="btn btn-primary btn-sm" onclick="vista_previa_diario(' . $sucu . ', 0, \'' . $asto . '\', ' . $ejer . ', ' . $mes . ');">
								<span class="glyphicon glyphicon-print"></span>
							</div>
						</td>';
                $table .= '<td>Valor:</td>';
                $table .= '<td class="bg-danger fecha_letra" align="left">' . number_format($asto_vat_asto, 2, '.', ',') . '</td>';
                $table .= '</tr>';
            }//fin foreach

            $table .= '</table>';

            $oReturn->assign("divInfo", "innerHTML", $table);
        }

        //directorio
        if (count($arrayDiario) > 0) {

            $tableDia .= '<table class="table table-striped table-condensed table-bordered table-hover" align="center" width="98%">';
            $tableDia .= '<tr>';
            $tableDia .= '<td colspan="5" class="bg-primary">DIARIO</td>
						<td align="center">
							<div class="btn btn-primary btn-sm" onclick="vista_previa_diario(' . $sucu . ', 0, \'' . $asto . '\', ' . $ejer . ', ' . $mes . ');">
								<span class="glyphicon glyphicon-print"></span>
							</div>
						</td>';
            $tableDia .= '</tr>';
            $tableDia .= '<tr>';
            $tableDia .= '<td>Cuenta Contable</td>';
            $tableDia .= '<td>Centro Costos</td>';
            $tableDia .= '<td>Centro Actividad</td>';
            $tableDia .= '<td>Documento</td>';
            $tableDia .= '<td>Debito</td>';
            $tableDia .= '<td>Credito</td>';
            $tableDia .= '</tr>';
            $totalDeb = 0;
            $totalCre = 0;
            foreach ($arrayDiario as $val) {
                $dasi_cod_cuen = $val[0];
                $dasi_cod_cact = $val[1];
                $ccos_cod_ccos = $val[2];
                $dasi_dml_dasi = $val[3];
                $dasi_cml_dasi = $val[4];
                $dasi_det_asi = $val[5];
                $dasi_num_depo = $val[6];

                //clpv
                $cuen_nom_cuen = '';
                if (!empty($dasi_cod_cuen)) {
                    $sql = "select cuen_nom_cuen from saecuen where cuen_cod_cuen = '$dasi_cod_cuen' and cuen_cod_empr = $empr";
                    $cuen_nom_cuen = consulta_string_func($sql, 'cuen_nom_cuen', $oIfx, '');
                }

                $ccosn_nom_ccosn = '';
                if (!empty($ccos_cod_ccos)) {
                    $sql = "select ccosn_nom_ccosn from saeccosn where ccosn_cod_ccosn = '$ccos_cod_ccos' and ccosn_cod_empr = $empr";
                    $ccosn_nom_ccosn = consulta_string_func($sql, 'ccosn_nom_ccosn', $oIfx, '');
                }

                $cact_nom_cact = '';
                if (!empty($dasi_cod_cact)) {
                    $sql = "select cact_nom_cact from saecact where cact_cod_cact = '$dasi_cod_cact' and cact_cod_empr = $empr";
                    $cact_nom_cact = consulta_string_func($sql, 'cact_nom_cact', $oIfx, '');
                }

                $tableDia .= '<tr>';
                $tableDia .= '<td>' . $dasi_cod_cuen . ' - ' . $cuen_nom_cuen . '</td>';
                $tableDia .= '<td>' . $ccos_cod_ccos . ' - ' . $ccosn_nom_ccosn . '</td>';
                $tableDia .= '<td>' . $dasi_cod_cact . ' - ' . $cact_nom_cact . '</td>';
                $tableDia .= '<td>' . $dasi_num_depo . '</td>';
                $tableDia .= '<td align="right">' . number_format($dasi_dml_dasi, 2, '.', ',') . '</td>';
                $tableDia .= '<td align="right">' . number_format($dasi_cml_dasi, 2, '.', ',') . '</td>';
                $tableDia .= '</tr>';

                $totalDeb += $dasi_dml_dasi;
                $totalCre += $dasi_cml_dasi;
            }//fin foreach
            $tableDia .= '<tr>';
            $tableDia .= '<td align="right" class="bg-danger fecha_letra" colspan="4">TOTAL:</td>';
            $tableDia .= '<td align="right" class="bg-danger fecha_letra">' . number_format($totalDeb, 2, '.', ',') . '</td>';
            $tableDia .= '<td align="right" class="bg-danger fecha_letra">' . number_format($totalCre, 2, '.', ',') . '</td>';
            $tableDia .= '</tr>';
            $tableDia .= '</table>';

            $oReturn->assign("divDiario", "innerHTML", $tableDia);
        }

        //directorio
        if (count($arrayDirectorio) > 0) {

            $tableDir .= '<table class="table table-striped table-condensed table-bordered table-hover" align="center" width="98%">';
            $tableDir .= '<tr>';
            $tableDir .= '<td colspan="6" class="bg-primary">DIRECTORIO</td>';
            $tableDir .= '</tr>';
            $tableDir .= '<tr>';
            $tableDir .= '<td>No.</td>';
            $tableDir .= '<td>Cliente/Proveedor</td>';
            $tableDir .= '<td>Transaccion</td>';
            $tableDir .= '<td>Factura</td>';
            $tableDir .= '<td>Credito</td>';
            $tableDir .= '<td>Debito</td>';
            $tableDir .= '</tr>';
            $totalDeb = 0;
            $totalCre = 0;
            foreach ($arrayDirectorio as $val) {
                $dir_cod_dir = $val[0];
                $dir_cod_cli = $val[1];
                $tran_cod_modu = $val[2];
                $dir_cod_tran = $val[3];
                $dir_num_fact = $val[4];
                $dir_detalle = $val[5];
                $dir_fec_venc = $val[6];
                $dir_deb_ml = $val[7];
                $dir_cre_ml = $val[8];

                //clpv
                $clpv_nom_clpv = '';
                if (!empty($dir_cod_cli)) {
                    $sql = "select clpv_nom_clpv from saeclpv where clpv_cod_clpv = $dir_cod_cli";
                    $clpv_nom_clpv = consulta_string_func($sql, 'clpv_nom_clpv', $oIfx, '');
                }

                $tableDir .= '<tr>';
                $tableDir .= '<td>' . $dir_cod_dir . '</td>';
                $tableDir .= '<td>' . $clpv_nom_clpv . '</td>';
                $tableDir .= '<td>' . $dir_cod_tran . '</td>';
                $tableDir .= '<td>' . $dir_num_fact . '</td>';
                $tableDir .= '<td align="right">' . number_format($dir_cre_ml, 2, '.', ',') . '</td>';
                $tableDir .= '<td align="right">' . number_format($dir_deb_ml, 2, '.', ',') . '</td>';
                $tableDir .= '</tr>';

                $totalCre += $dir_cre_ml;
                $totalDeb += $dir_deb_ml;
            }//fin foreach
            $tableDir .= '<tr>';
            $tableDir .= '<td align="right" class="bg-danger fecha_letra" colspan="4">TOTAL:</td>';
            $tableDir .= '<td align="right" class="bg-danger fecha_letra">' . number_format($totalCre, 2, '.', ',') . '</td>';
            $tableDir .= '<td align="right" class="bg-danger fecha_letra">' . number_format($totalDeb, 2, '.', ',') . '</td>';
            $tableDir .= '</tr>';
            $tableDir .= '</table>';

            $oReturn->assign("divDirectorio", "innerHTML", $tableDir);
        }

        //retencion
        if (count($arrayRetencion) > 0) {

            $tableRet .= '<table class="table table-striped table-condensed table-bordered table-hover" align="center" width="98%">';
            $tableRet .= '<tr>';
            $tableRet .= '<td colspan="8" class="bg-primary">RETENCION</td>';
            $tableRet .= '</tr>';
            $tableRet .= '<tr>';
            $tableRet .= '<td>Cliente/Proveedor</td>';
            $tableRet .= '<td>Factura</td>';
            $tableRet .= '<td>Retencion</td>';
            $tableRet .= '<td>Codigo</td>';
            $tableRet .= '<td>Porcentaje</td>';
            $tableRet .= '<td>Base Imp.</td>';
            $tableRet .= '<td>Valor</td>';
            $tableRet .= '<td>Print</td>';
            $tableRet .= '</tr>';
            foreach ($arrayRetencion as $val) {
                $ret_cta_ret = $val[0];
                $ret_porc_ret = $val[1];
                $ret_bas_imp = $val[2];
                $ret_valor = $val[3];
                $ret_num_ret = $val[4];
                $ret_detalle = $val[5];
                $ret_num_fact = $val[6];
                $ret_ser_ret = $val[7];
                $ret_cod_clpv = $val[8];
                $ret_fec_ret = $val[9];

                //clpv
                $clpv_nom_clpv = '';
                if (!empty($ret_cod_clpv)) {
                    $sql = "select clpv_nom_clpv from saeclpv where clpv_cod_clpv = $ret_cod_clpv";
                    $clpv_nom_clpv = consulta_string_func($sql, 'clpv_nom_clpv', $oIfx, '');
                }

                //fprv
                $printRet = '';
                $tipoDoc = 0;
                if ($asto_cod_modu == 4 || $asto_cod_modu == 10) {

                    //fecha fprv o minv
                    if ($asto_cod_modu == 4) {
                        $sql = "select fprv_fec_emis 
                                from saefprv
                                where fprv_cod_clpv = $ret_cod_clpv and
                                fprv_num_fact = '$ret_num_fact' and
                                fprv_cod_asto = '$asto' and
                                fprv_cod_ejer = $ejer and
                                fprv_cod_empr = $empr and
                                fprv_cod_sucu = $sucu";
                        $fechaEmis = consulta_string_func($sql, 'fprv_fec_emis', $oIfx, '');
                        $tipoDoc = 5;
                    } elseif ($asto_cod_modu == 10) {
                        $sql = "select minv_fmov 
                                from saeminv
                                where minv_cod_clpv = $ret_cod_clpv and
                                minv_fac_prov = '$ret_num_fact' and
                                minv_comp_cont = '$asto' and
                                minv_cod_ejer = $ejer and
                                minv_cod_empr = $empr and
                                minv_cod_sucu = $sucu";
                        $fechaEmis = consulta_string_func($sql, 'minv_fmov', $oIfx, '');
                        $tipoDoc = 6;
                    }

                    $printRet = '<div class="btn btn-primary btn-sm" onclick="genera_documento(' . $tipoDoc . ', \'' . $campo . '\',\'' . $fprv_clav_sri . '\' ,
                                                                                            \'' . $ret_cod_clpv . '\'  , \'' . $ret_num_fact . '\', \'' . $ejer . '\',
                                                                                            \'' . $asto . '\',  \'' . $fechaEmis . '\', ' . $sucu . ');">
                                    <span class="glyphicon glyphicon-print"></span>
                                </div>';
                }

                $tableRet .= '<tr>';
                $tableRet .= '<td>' . $clpv_nom_clpv . '</td>';
                $tableRet .= '<td>' . $ret_num_fact . '</td>';
                $tableRet .= '<td>' . $ret_ser_ret . ' - ' . $ret_num_ret . '</td>';
                $tableRet .= '<td>' . $ret_cta_ret . '</td>';
                $tableRet .= '<td align="right">' . $ret_porc_ret . '</td>';
                $tableRet .= '<td align="right">' . number_format($ret_bas_imp, 2, '.', ',') . '</td>';
                $tableRet .= '<td align="right">' . number_format($ret_valor, 2, '.', ',') . '</td>';
                $tableRet .= '<td align="center">' . $printRet . '</td>';
                $tableRet .= '</tr>';
            }//fin foreach

            $tableRet .= '</table>';

            $oReturn->assign("divRetencion", "innerHTML", $tableRet);
        }

        //adjuntos
        if (count($arrayAdjuntos) > 0) {

            $tableAdj .= '<table class="table table-striped table-condensed table-bordered table-hover" align="center" width="98%">';
            $tableAdj .= '<tr>';
            $tableAdj .= '<td colspan="2" class="bg-primary">ARCHIVOS ADJUNTOS</td>';
            $tableAdj .= '</tr>';
            $tableAdj .= '<tr>';
            $tableAdj .= '<td>Titulo</td>';
            $tableAdj .= '<td>Ruta</td>';
            $tableAdj .= '</tr>';
            foreach ($arrayAdjuntos as $val) {
                $titulo = $val[0];
                $ruta = $val[1];

                $tableAdj .= '<tr>';
                $tableAdj .= '<td>' . $titulo . '</td>';
                $tableAdj .= '<td><a href="#" onclick="dowloand(\'' . $ruta . '\')">' . $ruta . '</a></td>';
                $tableAdj .= '</tr>';
            }//fin foreach

            $tableAdj .= '</table>';

            $oReturn->assign("divAdjuntos", "innerHTML", $tableAdj);
        }
    } catch (Exception $e) {
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}

function genera_documento($tipo_documento = 0, $id = '', $clavAcce = 'no_autorizado', $clpv = 0, $num_fact = '', $ejer = 0, $asto = '', $fec_emis = '', $sucu = 0) {
    session_start();
    global $DSN_Ifx;

    $oReturn = new xajaxResponse();

    try {
        switch ($tipo_documento) {
            case 1:
                $_SESSION['pdf'] = reporte_factura($id, $clavAcce, $sucu);
                break;
            case 2:
                $_SESSION['pdf'] = reporte_notaDebito($id, $clavAcce);
                break;
            case 3:
                $_SESSION['pdf'] = reporte_notaCredito($id, $clavAcce, $sucu);
                break;
            case 4:
                $_SESSION['pdf'] = reporte_guiaRemision($id, $clavAcce, $sucu);
                break;
            case 5:
                $id = $_SESSION['sqlId'][$id];
                $_SESSION['pdf'] = reporte_retencionGasto($id, $clavAcce, $rutapdf, $clpv, $num_fact, $ejer, $asto, $fec_emis, $sucu);
                break;
            case 6:
                $id = $_SESSION['sqlId'][$id];
                $_SESSION['pdf'] = reporte_retencionInve($id, $clavAcce, $rutapdf, $clpv, $num_fact, $ejer, $asto, $fec_emis, $sucu);
                break;
            case 7:
                $_SESSION['pdf'] = reporte_factura_export($id, $clavAcce);
                break;
            case 8:
                $_SESSION['pdf'] = reporte_factura_flor($id, $clavAcce);
                break;
            case 9:
                $_SESSION['pdf'] = reporte_factura_flor_export($id, $clavAcce);
                break;
            case 10:
                $_SESSION['pdf'] = reporte_guiaRemisionFlor($id, $clavAcce);
                break;
        }
        
        $oReturn->script('generar_pdf()');
    } catch (Exception $e) {
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}

/* PROCESO DE REQUEST DE LAS FUNCIONES MEDIANTE AJAX NO MODIFICAR */
$xajax->processRequest();
/* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
?>

