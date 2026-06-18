<?php
require("_Ajax.comun.php"); // No modificar esta linea
include_once './mayorizacion.inc.php';
include_once './Transliterator.php';
/*:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
// S E R V I D O R   A J A X //
::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::*/
/**
Herramientas de apoyo 
 */
// cargar pedido excel DE PRATI
function cargar_txt($aForm = '')
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $ifu = new Formulario;
    $ifu->DSN = $DSN_Ifx;

    $oReturn = new xajaxResponse();

    $idempresa  = $aForm['empresa'];
    $idsucursal = $aForm['sucursal'];
    $filtro_consulta = $aForm['filtro_consulta'];
    $idbodega   = $aForm['bode_cod_bode'];

    unset($_SESSION['U_COMPRAS_SRI']);

    //////////////

    try {
        // PROVEEDOR 
        $sql = "select clpv_cod_clpv, clpv_ruc_clpv , clpv_nom_clpv from saeclpv where clpv_cod_empr = $idempresa and clpv_clopv_clpv = 'PV' ";
        unset($array_clpv);
        unset($array_clpv_cod);
        $array_clpv     = array_dato($oIfx, $sql, 'clpv_ruc_clpv', 'clpv_cod_clpv');
        //$array_clpv_cod = array_dato($oIfx, $sql, 'clpv_ruc_clpv', 'clpv_cod_clpv'); 

        // PLANILLA
        $sql = "SELECT pafa_cod_pafa, concact(pafa_nom_pafa) from saepafa where
                        pafa_cod_empr = $idempresa  order by 2 ";
        $sql = "SELECT c.id, CONCAT(c.codigo, '-', c.nombre) AS plantilla_nom_compl FROM comercial.plantilla_clpv c WHERE
                            c.id_empresa = $idempresa 
                            --AND c.id_clpv = 0
                            ORDER BY 2; ";
        $lista_plan = lista_boostrap_func($oCon, $sql, '', 'id',  'plantilla_nom_compl');

        $archivo = $aForm['archivo'];

        // archivo txt
        $archivo_real = substr($archivo, 12);
        $nombre_archivo = "upload/" . $archivo_real;

        $file       = fopen($nombre_archivo, "r");
        $datos      = file($nombre_archivo);
        $NumFilas   = count($datos);


        $table_cab  = '<br><br>';
        $table_cab_excel  = '<br><br>';
        $table_cab .= '<table class="table table-bordered table-striped table-condensed" style="width: 100%; margin-bottom: 0px;">';
        $table_cab_excel .= '<table class="table table-bordered table-striped table-condensed" style="width: 100%; margin-bottom: 0px;">';
        $table_cab .= '<tr>
		                            <td class="success" style="width: 1.5%;">N.-</td>
		                            <td class="success" style="width: 1.5%;">COMPROBANTE</td>
		                            <td class="success" style="width: 4.5%;">FACTURA</td>
		                            <td class="success" style="width: 3.5%;">IDENTIFICACION</td>
		                            <td class="success" style="width: 4.5%;">SUPLIDOR - PROVEEDOR</td>
		                            <td class="success" style="width: 4.5%;">FECHA EMISION</td>
		                            <td class="success" style="width: 7.5%;">MODULO</td>
                                    <td class="success" style="width: 4.5%;">RETENCION</td>
                                    <td class="success" style="width: 4.5%;">TIPO</td>
                                    <td class="success" style="width: 9.5%;">PLANILLA</td>
                                    <td class="success" style="width: 4.5%;">ACEPTAR</td>
		                      	</tr>';

        $table_cab_excel .= '<tr>
                                  <td class="success" style="width: 4.5%;">N.-</td>
                                  <td class="success" style="width: 4.5%;">COMPROBANTE</td>
                                  <td class="success" style="width: 4.5%;">FACTURA</td>
                                  <td class="success" style="width: 4.5%;">IDENTIFICACION</td>
                                  <td class="success" style="width: 4.5%;">SUPLIDOR - PROVEEDOR</td>
                                  <td class="success" style="width: 4.5%;">FECHA EMISION</td>
                                </tr>';
        $x = 1;
        $y = 1;
        $oReturn->alert('Buscando ...');
        unset($array_prod);

        $datos = array_reverse($datos);
        $numero_de_registros = count($datos);
        foreach ($datos as $val) {
            /*COMPROBANTE	        SERIE_COMPROBANTE	        RUC_EMISOR	                RAZON_SOCIAL_EMISOR	        FECHA_EMISION	
                  FECHA_AUTORIZACION	TIPO_EMISION	            IDENTIFICACION_RECEPTOR	    CLAVE_ACCESO	
                  NUMERO_AUTORIZACION   IMPORTE_TOTAL
				*/

            /*
            list(
                $tipo_compr,  $factura,         $ruc_emisor,    $clpv_emisor,   $fecha_emi, $fecha_auto, $tipo_emision,
                $ab,          $ruc_recep,       $clave_acceso,  $autorizacion,  $importe
            ) = explode("	", $val);
            */

            list(
                $ruc_emisor,
                $clpv_emisor,
                $tipo_compr,
                $factura,
                $clave_acceso,
                $fecha_auto,
                $fecha_emi,
                $ruc_recep,
                $valor_sn_impuesto,
                $impuesto,
                $importe,

            ) = explode("	", $val);
            $clpv_emisor = $clpv_emisor;

            if ($x > 0 && !empty($tipo_compr) && !empty($factura) && $x < $numero_de_registros) {

                $tipo_compr = limpiar_string_encode($tipo_compr);
                $factura = limpiar_string_encode($factura);
                $clpv_emisor = limpiar_string_encode($clpv_emisor);
                $fecha_emi = limpiar_string_encode($fecha_emi);
                $fecha_auto = limpiar_string_encode($fecha_auto);
                $tipo_emision = limpiar_string_encode($tipo_emision);
                $ab = limpiar_string_encode($ab);
                $ruc_recep = limpiar_string_encode($ruc_recep);
                $clave_acceso = limpiar_string_encode($clave_acceso);
                $autorizacion = limpiar_string_encode($autorizacion);
                $importe = limpiar_string_encode($importe);


                $ruc_emisor = trim($ruc_emisor);
                $ruc_recep  = trim($ruc_recep);

                // PROVEEDOR
                $sql = "select clpv_cod_clpv from saeclpv where clpv_cod_empr = $idempresa and clpv_clopv_clpv = 'PV' and clpv_ruc_clpv = '$ruc_emisor' ";
                $cont_clpv = $array_clpv[$ruc_emisor];



                if (empty($cont_clpv)) {
                    $cont_clpv = 0;
                }


                // METODO PARA VERIFICSR SI LA FACTURA YA FUE INGRESADA

                $serie1 = substr($factura, 0, 3);   // Serie uno 001
                $serie2 = substr($factura, 4, 3);   // Serie dos 002
                $serie = $serie1 . '' . $serie2;        // La serie en el archivo es 001-002 por eso se realiza en dos partes
                $secuencial = substr($factura, 8, 9);  // Factura

                if (empty($idsucursal) || $idsucursal == 0) {
                    $idsucursal = '';
                }

                // 2024-11-12
                // formato fecha emi = 16/11/2024
                $anio             = substr($fecha_emi, 6, 4);
                $idprdo         = (substr($fecha_emi, 3, 2)) * 1;
                $fecha_ejer     = $anio . '-12-31';
                $sql             = "SELECT ejer_cod_ejer from saeejer where ejer_fec_finl = '$fecha_ejer' and ejer_cod_empr = $idempresa ";
                $idejer         = consulta_string($sql, 'ejer_cod_ejer', $oIfx, 1);

                $sql_control = "SELECT fprv_cod_asto, fprv_cod_ejer, fprv_fec_regc from saefprv where 
                            fprv_cod_empr = $idempresa and
							-- CAST(fprv_cod_sucu AS TEXT) like '%$idsucursal%' and
                            fprv_num_seri = '$serie' and  
                            fprv_cod_clpv = $cont_clpv and	
							fprv_num_fact = '$secuencial' and
                            fprv_cod_ejer = $idejer
                            ";
                $contador_gastos = '';
                $cod_asto = '';
                $cod_ejer = '';
                $cod_prdo = '';
                if ($oIfx->Query($sql_control)) {
                    if ($oIfx->NumFilas() > 0) {
                        do {
                            $contador_gastos = $oIfx->f('fprv_cod_asto');
                            $cod_asto = $oIfx->f('fprv_cod_asto');
                            $cod_ejer = $oIfx->f('fprv_cod_ejer');
                            $cod_prdo = date('m', strtotime($oIfx->f('fprv_fec_regc')));
                        } while ($oIfx->SiguienteRegistro());
                    }
                }
                $oIfx->Free();


                $sql_control = "SELECT count(*) as contador from saeminv where 
                            minv_cod_empr = $idempresa and
							-- CAST(minv_cod_sucu AS TEXT) like '%$idsucursal%' and
                            minv_ser_docu = '$serie' and  
                            minv_cod_clpv = $cont_clpv and	
                            minv_est_minv <> '0' and 
							minv_fac_prov = '$secuencial' and
                            minv_cod_ejer = $idejer
                            ";
                $contador_compras = consulta_string($sql_control, 'contador', $oIfx, '');

                if (!empty($contador_gastos)) {
                    $color = 'background-color: #9AE36D';
                    $mensaje = ' - Factura ya Ingresada (GASTOS)';
                    $mostrar_acciones = 'display: none';
                    //$mostrar_asiento='display: block';
                } else if ($contador_compras > 0) {
                    $color = 'background-color: #9AE36D';
                    $mensaje = ' - Factura ya Ingresada (COMPRAS)';
                    $mostrar_acciones = 'display: none';
                    //  $mostrar_acciones = 'display: none';
                    //$mostrar_asiento='display: block';

                } else {
                    $color = '';
                    $mensaje = '';
                    $mostrar_acciones = '';
                    $mostrar_asiento = 'display: none';
                }

                // FIN METODO PARA VERIFICSR SI LA FACTURA YA FUE INGRESADA
                $mostar_recurso = '';
                if ($filtro_consulta == 'PE') {
                    $mostar_recurso = $mostrar_acciones;
                }

                $asiento_contable = '<a href="#" onclick="seleccionaItem(' . $idempresa . ', ' . $idsucursal . ', ' . $cod_ejer . ', ' . $cod_prdo . ', \'' . $cod_asto . '\');">' . $cod_asto . '</a>';


                if ($sClass == 'off') $sClass = 'on';
                else $sClass = 'off';
                $table_cab .= ' <tr style="' . $mostar_recurso . '; ' . $color . '; " >';
                $table_cab_excel .= ' <tr style="' . $mostar_recurso . '">';
                $table_cab .= '<td>' . ($y) . '</td>
                                       <td>' . $tipo_compr . '</td>
                                       <td style="' . $color . '">' . $factura . '' . $mensaje . ' ' . $asiento_contable . '</td>';
                $table_cab_excel .= '<td style="mso-number-format:\@;" align= "left">' . ($y) . '</td>
                                       <td>' . \LukeMadhanga\Transliterator::convert($tipo_compr) . '</td>
                                       <td style="' . $color . '">' . $factura . '' . $mensaje . '</td>';

                if ($cont_clpv == 0) {
                    $table_cab .= '<td class="warning" title="CREAR SUPLIDOR" onclick="crear_clpv(\'' . $ruc_emisor . '\', \'' . $clpv_emisor . '\')"><a>' . $ruc_emisor . '</a> <br> <code> El proveedor no existe en el sistema</code></td>
                                           <td class="warning" title="CREAR SUPLIDOR" onclick="crear_clpv(\'' . $ruc_emisor . '\', \'' . $clpv_emisor . '\')"><a>' . $clpv_emisor . '</a> <br> <code> El proveedor no existe en el sistema</code></td>';
                    $table_cab_excel .= '<td style="background:yellow; mso-number-format:\@;" align= "left" title="CREAR SUPLIDOR" onclick="crear_clpv(\'' . $ruc_emisor . '\', \'' . $clpv_emisor . '\')">' .  $ruc_emisor . '</td>
                                           <td style="background:yellow; mso-number-format:\@;" align= "left" title="CREAR SUPLIDOR" onclick="crear_clpv(\'' . $ruc_emisor . '\', \'' . $clpv_emisor . '\')">' . \LukeMadhanga\Transliterator::convert($clpv_emisor) . '</td>';
                } else {
                    $table_cab .= '<td>' . $ruc_emisor . '</td>
                                           <td>' . $clpv_emisor . '</td>';
                    $table_cab_excel .= '<td style="mso-number-format:\@;" align= "left">' . $ruc_emisor . '</td>
                                           <td style="mso-number-format:\@;" align= "left">' . \LukeMadhanga\Transliterator::convert($clpv_emisor) . '</td>';
                }

                $sql = "SELECT c.id_compra_elec FROM comercial.cxp_compra c WHERE 
                                    c.empr_cod_empr = $idempresa AND 
                                    c.clave_acceso  = '$clave_acceso' AND
                                    c.clpv_cod_clpv = '$cont_clpv' ";
                $id_compra_elec = consulta_string_func($sql, 'id_compra_elec', $oCon, '0');


                $tipo_ser = 'su.' . $y;
                $rete_ser = 're.' . $y;
                $trib_ser = 'ti.' . $y;
                $plan_ser = 'pl.' . $y;

                $table_cab .= '<td>' . $fecha_emi . '</td>';
                $table_cab_excel .= '<td style="mso-number-format:\@;" align= "left">' . $fecha_emi . '</td>';
                if ($id_compra_elec > 0) {
                    $table_cab .= '<td class="alert ">
                                                    <select   id="' . $tipo_ser . '" name="' . $tipo_ser . '" class="form-control input-sm"
                                                    onchange="cargar_planilla(\'' . $y . '\', \'' . $cont_clpv . '\' );" >
                                                        <option value="">Seleccione una opcion..</option>
                                                        <option value="1" selected>Gasto</option>
                                                        <option value="2">Almacen</option>
                                                        <option value="3">Caja Chica</option>                                                
                                                    </select>
                                            </td>
                                            <td class="alert ">
                                                    <select id="' . $rete_ser . '" name="' . $rete_ser . '" class="form-control input-sm">
                                                        <option value="">Seleccione una opcion..</option>
                                                        <option value="1" selected>Si</option>
                                                        <option value="2">No</option>                                                
                                                    </select>
                                            </td>
                                            <td class="alert ">
                                                    <select  id="' . $trib_ser . '" name="' . $trib_ser . '" class="form-control input-sm" 
                                                        onchange="cargar_planilla(\'' . $y . '\', \'' . $cont_clpv . '\' );">
                                                        <option value="">Seleccione una opcion..</option>
                                                        <option value="1" selected>General</option>
                                                        <option value="2">Personalizado</option>                                                
                                                    </select>
                                            </td>
                                            <td class="alert ">
                                                    <select  id="' . $plan_ser . '" name="' . $plan_ser . '" class="form-control input-sm">
                                                        <option value="">Seleccione una opcion..</option>
                                                        ' . $lista_plan . '                                             
                                                    </select>
                                            </td>';

                    if ($cont_clpv == 0) {
                        $table_cab .= '
                                            <td>
                                                <label style="color: red">Debe ingresar el proveedor antes de continuar</label>
                                            </td>
                                            ';
                    } else {
                        $table_cab .= '
                                            <td class="alert " style="' . $color . ';">
                                                        <div align="center" style="' . $mostrar_acciones . ';"> 
                                                                <div title="Generar" class="btn btn-success btn-sm" 
                                                                    onclick="generar_fact(\'' . $tipo_compr . '\', \'' . $factura . '\' , \'' . $ruc_emisor . '\' , \'' . $clpv_emisor . '\', 
                                                                                        \'' . $fecha_emi . '\' ,   \'' . $fecha_auto . '\', \'' . $tipo_emision . '\',  \'' . $ruc_recep . '\', 
                                                                                        \'' . $clave_acceso . '\', \'' . $y . '\', \'' . $cont_clpv . '\' )" >
                                                                    <span class="glyphicon glyphicon-check"   ><span>
                                                                </div> 
                                                        </div>
                                            </td>';
                    }
                } else {
                    $table_cab .= '<td>
                                                    <select   id="' . $tipo_ser . '" name="' . $tipo_ser . '" class="form-control input-sm"
                                                    onchange="cargar_planilla(\'' . $y . '\', \'' . $cont_clpv . '\' );" >
                                                        <option value="">Seleccione una opcion..</option>
                                                        <option value="1" selected>Gasto</option>
                                                        <option value="2">Almacen</option>
                                                        <option value="3">Caja Chica</option>                                                
                                                    </select>
                                            </td>
                                            <td>
                                                    <select   id="' . $rete_ser . '" name="' . $rete_ser . '" class="form-control input-sm">
                                                        <option value="">Seleccione una opcion..</option>
                                                        <option value="1" selected>Si</option>
                                                        <option value="2">No</option>                                                
                                                    </select>
                                            </td>
                                            <td>
                                                    <select   id="' . $trib_ser . '" name="' . $trib_ser . '" class="form-control input-sm" 
                                                        onchange="cargar_planilla(\'' . $y . '\', \'' . $cont_clpv . '\' );">
                                                        <option value="">Seleccione una opcion..</option>
                                                        <option value="1" selected>General</option>
                                                        <option value="2">Personalizado</option>                                                
                                                    </select>
                                            </td>
                                            <td>
                                                    <select  id="' . $plan_ser . '" name="' . $plan_ser . '" class="form-control input-sm">
                                                        <option value="">Seleccione una opcion..</option>
                                                        ' . $lista_plan . '                                             
                                                    </select>
                                            </td>';

                    if ($cont_clpv == 0) {
                        $table_cab .= '
                                            <td>
                                                <label style="color: red">Debe ingresar el proveedor antes de continuar</label>
                                            </td>
                                            ';
                    } else {
                        $table_cab .= '
                                            <td style="' . $color . ';">
                                                        <div align="center" style="' . $mostrar_acciones . ';"> 
                                                                <div title="Generar" class="btn btn-success btn-sm" 
                                                                    onclick="generar_fact(\'' . $tipo_compr . '\', \'' . $factura . '\' , \'' . $ruc_emisor . '\' , \'' . $clpv_emisor . '\', 
                                                                                        \'' . $fecha_emi . '\' ,   \'' . $fecha_auto . '\', \'' . $tipo_emision . '\',  \'' . $ruc_recep . '\', 
                                                                                        \'' . $clave_acceso . '\', \'' . $y . '\', \'' . $cont_clpv . '\' )" >
                                                                    <span class="glyphicon glyphicon-check"   ><span>
                                                                </div> 
                                                        </div>

                                                      
                                            </td>';
                    }
                }
                $table_cab .= '</tr>';
                $table_cab_excel .= '</tr>';

                $array_prod[] = array(
                    $tipo_compr,
                    $factura,
                    $ruc_emisor,
                    $clpv_emisor,
                    $fecha_emi,
                    $fecha_auto,
                    $tipo_emision,
                    $ruc_recep,
                    $clave_acceso,
                    $y,
                    $cont_clpv
                );
                $y++;
            }
            $x++;
        }

        $_SESSION['U_COMPRAS_SRI'] = $array_prod;

        $table_cab .= "</table>";
        $table_cab_excel .= "</table>";



        $_SESSION['Html_rep_excel'] = 'SIN DATOS';
        unset($_SESSION['Html_rep_excel']);
        $_SESSION['Html_rep_excel'] = $table_cab_excel;


        $oReturn->assign("divFormularioDetalle", "innerHTML", $table_cab);
    } catch (Exception $ex) {
        $oReturn->alert($ex->getMessage());
    }

    $oReturn->script("jsRemoveWindowLoad();");
    return $oReturn;
}

function verDiarioContable($aForm = '', $empr = 0, $sucu = 0, $ejer = 0, $mes = 0, $asto = '')
{

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    global $DSN_Ifx, $DSN;

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oReturn = new xajaxResponse();

    //variables del formulario
    $empresa = $aForm['empresa'];
    $anio = $aForm['anio'];
    $mes_1 = $aForm['mes_1'];
    $mes_2 = $aForm['mes_2'];
    $nivel = $aForm['nivel'];
    $campo = 0;

    $class = new GeneraDetalleAsientoContable();

    $arrayAsto = $class->informacionAsientoContable($oIfx, $empr, $sucu, $ejer, $mes, $asto);

    $arrayDiario = $class->diarioAsientoContable($oIfx, $empr, $sucu, $ejer, $mes, $asto);

    $arrayDirectorio = $class->directorioAsientoContable($oIfx, $empr, $sucu, $ejer, $mes, $asto);

    $arrayRetencion = $class->retencionAsientoContable($oIfx, $empr, $sucu, $ejer, $mes, $asto);

    $arrayAdjuntos = $class->adjuntosAsientoContable($oCon, $empr, $sucu, $ejer, $mes, $asto);

    try {

        //LECTURA SUCIA1
        // 


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
							<div class="btn btn-primary btn-sm" onclick="vista_previa_diario(' . $empresa . ',' . $sucu . ', 0, \'' . $asto . '\', ' . $ejer . ', ' . $mes . ');">
								<span class="glyphicon glyphicon-print"></span>
							</div>
						</td>';
                $table .= '<td>Valor:</td>';
                $table .= '<td class="bg-danger fecha_letra" align="left">' . number_format($asto_vat_asto, 2, '.', ',') . '</td>';
                $table .= '</tr>';
            } //fin foreach

            $table .= '</table>';

            $oReturn->assign("divInfo", "innerHTML", $table);
        }

        //directorio
        if (count($arrayDiario) > 0) {

            $tableDia .= '<table class="table table-striped table-condensed table-bordered table-hover" align="center" width="98%">';
            $tableDia .= '<tr>';
            $tableDia .= '<td colspan="5" class="bg-primary">DIARIO</td>
						<td align="center">
							<div class="btn btn-primary btn-sm" onclick="vista_previa_diario(' . $empresa . ',' . $sucu . ', 0, \'' . $asto . '\', ' . $ejer . ', ' . $mes . ');">
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
            } //fin foreach
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
            } //fin foreach
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
                if ($asto_cod_modu == 4 || $asto_cod_modu == 6) {

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
                    } elseif ($asto_cod_modu == 6) {
                        $sql = "select minv_fmov 
								from saeminv
								where minv_cod_clpv = $ret_cod_clpv and
								minv_fac_prov = '$ret_num_fact' and
								minv_comp_cont = '$asto' and
								minv_cod_ejer = $ejer and
								minv_cod_empr = $empr and
								minv_cod_sucu = $sucu";
                        $fechaEmis = consulta_string_func($sql, 'minv_fmov', $oIfx, '');
                    }

                    $printRet = '<div class="btn btn-primary btn-sm" onclick="vista_previa_diario(' . $empresa . ',' . $sucu . ', 0, \'' . $asto . '\', ' . $ejer . ', ' . $mes . ');">
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
            } //fin foreach

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
            } //fin foreach

            $tableAdj .= '</table>';

            $oReturn->assign("divAdjuntos", "innerHTML", $tableAdj);
        }
    } catch (Exception $e) {
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}


function genera_pdf_doc_compras($idempresa, $idsucursal, $asto_cod, $ejer_cod, $prdo_cod)
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    global $DSN_Ifx;

    $oIfxA = new Dbo();
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();
    unset($_SESSION['pdf']);
    $oReturn = new xajaxResponse();

    $tipo     = $aForm['documento'];
    $usuario = $_SESSION['U_NOMBRECOMPLETO'];

    $diario = generar_diarios_ingresos_pdf($idempresa, $idsucursal, $asto_cod, $ejer_cod, $prdo_cod);
    $_SESSION['pdf'] = $diario;

    $oReturn->script('generar_pdf_compras()');
    return $oReturn;
}

function eliminar_acentos($cadena)
{

    //Reemplazamos la A y a
    $cadena = str_replace(
        array('Á', 'À', 'Â', 'Ä', 'á', 'à', 'ä', 'â', 'ª'),
        array('A', 'A', 'A', 'A', 'a', 'a', 'a', 'a', 'a'),
        $cadena
    );

    //Reemplazamos la E y e
    $cadena = str_replace(
        array('É', 'È', 'Ê', 'Ë', 'é', 'è', 'ë', 'ê'),
        array('E', 'E', 'E', 'E', 'e', 'e', 'e', 'e'),
        $cadena
    );

    //Reemplazamos la I y i
    $cadena = str_replace(
        array('Í', 'Ì', 'Ï', 'Î', 'í', 'ì', 'ï', 'î'),
        array('I', 'I', 'I', 'I', 'i', 'i', 'i', 'i'),
        $cadena
    );

    //Reemplazamos la O y o
    $cadena = str_replace(
        array('Ó', 'Ò', 'Ö', 'Ô', 'ó', 'ò', 'ö', 'ô'),
        array('O', 'O', 'O', 'O', 'o', 'o', 'o', 'o'),
        $cadena
    );

    //Reemplazamos la U y u
    $cadena = str_replace(
        array('Ú', 'Ù', 'Û', 'Ü', 'ú', 'ù', 'ü', 'û'),
        array('U', 'U', 'U', 'U', 'u', 'u', 'u', 'u'),
        $cadena
    );

    //Reemplazamos la Ñ y ñ
    $cadena = str_replace(
        array('Ñ', 'ñ'),
        array('N', 'n'),
        $cadena
    );


    return $cadena;
}

function cargar_sucu($aForm = '')
{
    //Definiciones
    global $DSN_Ifx;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oReturn = new xajaxResponse();

    $idempresa  = $aForm['empresa'];
    $sucu       = "sucursal";

    //lectura sucia
    //////////////              

    // SUCURSAL
    $sql = "select  sucu_cod_sucu, sucu_nom_sucu from saesucu where
                        sucu_cod_empr = $idempresa  ";
    if ($oIfx->Query($sql)) {
        $oReturn->script('eliminar_lista(\'' . $sucu . '\');');
        if ($oIfx->NumFilas() > 0) {
            $i = 1;
            do {
                $detalle = $oIfx->f('sucu_nom_sucu');
                $oReturn->script(('anadir_elemento(' . $i++ . ',\'' . $oIfx->f('sucu_cod_sucu') . '\', \'' . $detalle . '\' ,\'' . $sucu . '\' )'));
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();

    return $oReturn;
}

function cargar_planilla($aForm = '', $id, $clpv_cod)
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    global $DSN_Ifx, $DSN;

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oReturn   = new xajaxResponse();
    $idempresa = $aForm['empresa'];
    $idsucursal = $aForm['sucursal'];
    $tipo      = $aForm['ti.' . $id];
    $modulo    = $aForm['su.' . $id];
    $plan_ser  = 'pl.' . $id;

    // PLANILLA
    if ($tipo == 1) {
        // GENERAL
        if ($modulo == 2) { // almacen
            $sql = "select id,  nombre from comercial.plantilla_clpv where id_clpv in ('0') and id_empresa = $idempresa and modulo = 10 order by 2 ";
            class PlantillaCompras
            {
                public $id;
                public $nombre;
            }

            $opcion1 = new PlantillaCompras();
            $opcion1->id = '1';
            $opcion1->nombre = 'INVENTARIO COMPRA';
            $opcion2 = new PlantillaCompras();
            $opcion2->id = '2';
            $opcion2->nombre = 'COMPRA SIN RETENCION';
            $opciones_compras = array($opcion1, $opcion2);
            // var_dump($opciones_compras);
            // exit;
            $opciones_compras_json = json_decode(json_encode($opciones_compras), true);
            $i = 0;
            $msn = "...Seleccione una Opcion...";
            $txt = $plan_ser;
            $oReturn->script('borrar_lista( \'' . $txt . '\' )');
            foreach ($opciones_compras_json as $key => $value) {
                $id   = $value['id'];
                $nom  = $value['nombre'];
                $oReturn->script(('anadir_elemento(' . $i . ',' . $id . ', \'' . $nom . '\', \'' . $txt . '\' )'));
                $i++;
            }
        } else {              // gasto . caja chica
            $sql = "select id,  nombre from comercial.plantilla_clpv where id_clpv in ('0') and id_empresa = $idempresa and modulo = 4 order by 2 ";

            $i = 0;
            $msn = "...Seleccione una Opcion...";
            $txt = $plan_ser;
            $oReturn->script('borrar_lista( \'' . $txt . '\' )');
            if ($oCon->Query($sql)) {
                if ($oCon->NumFilas() > 0) {
                    do {
                        $id   = $oCon->f('id');
                        $nom  = $oCon->f('nombre');
                        $oReturn->script(('anadir_elemento(' . $i . ',' . $id . ', \'' . $nom . '\', \'' . $txt . '\' )'));
                        $i++;
                    } while ($oCon->SiguienteRegistro());
                    $oReturn->script(('anadir_elemento(' . $i . ',"", \'' . $msn . '\', \'' . $txt . '\' )'));
                }
            }
            $oCon->Free();
        }
    } else {
        // CLPV
        $sql = "select id,  nombre from comercial.plantilla_clpv where id_clpv in ('$clpv_cod') and id_empresa = $idempresa order by 2 ";
        $i = 0;
        $msn = "...Seleccione una Opcion...";
        $txt = $plan_ser;
        $oReturn->script('borrar_lista( \'' . $txt . '\' )');
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                do {
                    $id   = $oCon->f('id');
                    $nom  = $oCon->f('nombre');
                    $oReturn->script(('anadir_elemento(' . $i . ',' . $id . ', \'' . $nom . '\', \'' . $txt . '\' )'));
                    $i++;
                } while ($oCon->SiguienteRegistro());
                $oReturn->script(('anadir_elemento(' . $i . ',"", \'' . $msn . '\', \'' . $txt . '\' )'));
            }
        }
        $oCon->Free();
    }


    return $oReturn;
}


// PROVEEDOR
function guardar_clpv($aForm = '')
{
    //Definiciones
    global $DSN, $DSN_Ifx;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oReturn = new xajaxResponse();

    //      VARIABLES
    $idempresa   = $aForm['empresa'];
    $idsucursal  = $aForm['sucursal'];
    $user_web    = $_SESSION['U_ID'];
    $grupo       = $aForm['grupo'];
    $iden        = $aForm['iden'];
    $ruc         = $aForm['ruc'];
    $clpv_nom    = strtoupper($aForm['clpv_nom']);
    $clpv_come   = strtoupper($aForm['clpv_come']);
    $clpv_dir    = strtoupper($aForm['clpv_dir']);
    $clpv_tel    = $aForm['clpv_tel'];
    $clpv_correo = strtoupper($aForm['clpv_correo']);

    try {
        // commit
        $oIfx->QueryT('BEGIN WORK;');
        $oCon->QueryT('BEGIN');

        $sql = "select pcon_mon_base from saepcon where pcon_cod_empr = $idempresa  ";
        $clpv_cod_mone = consulta_string_func($sql, 'pcon_mon_base', $oIfx, '1');

        $sql = "SELECT GRPV_CTA_GRPV FROM SAEGRPV WHERE
						GRPV_COD_EMPR = $idempresa AND 
						GRPV_COD_MODU = 4 AND
						GRPV_COD_GRPV = '$grupo' ";
        $cuenta = consulta_string_func($sql, 'grpv_cta_grpv', $oIfx, '');

        $fecha = date("Y-m-d");
        // cliente nuevo
        $sql = "insert into saeclpv (clpv_cod_sucu,         clpv_cod_empr,      clpv_cod_cuen,
                                     clv_con_clpv,          clpv_cod_char,      clpv_clopv_clpv, 
                                     clpv_nom_clpv,         clpv_ruc_clpv,      clpv_est_clpv, 
                                    clpv_fec_des,           clpv_fec_has,       clpv_fec_reno,
                                    clpv_nom_come,          clpv_cal_clpv,      clpv_est_mon,  
                                    grpv_cod_grpv,          clpv_dsc_clpv,      clpv_dsc_prpg,
                                    clpv_ret_sn,            clpv_cod_mone )
                          VALUES  ($idsucursal, 	        $idempresa, 	    '$cuenta',
                                  '$iden',                  '$ruc', 		    'PV', 		
                                  '$clpv_nom',              '$ruc', 		    'A',
                                  '$fecha', 		        '$fecha', 	        '$fecha',
                                  '$clpv_come',             'A',                'N',           
                                  '$grupo', 		        0,                  0,
                                  'N' ,                     $clpv_cod_mone)";
        $oIfx->QueryT($sql);

        // serial de cliente
        $sql = "select clpv_cod_clpv from saeclpv where
											clpv_cod_empr = $idempresa and
											clpv_ruc_clpv = '$ruc' and
											clpv_clopv_clpv = 'PV' and
											clv_con_clpv = '$iden' and
											clpv_fec_des = '$fecha'";
        $clpv_cod = consulta_string($sql, 'clpv_cod_clpv', $oIfx, 0);

        // DIRECCION
        $sqlDire = "insert into saedire(dire_cod_empr,          dire_cod_sucu,          dire_cod_clpv,      dire_dir_dire,
                                        dire_cod_tipo )
                                values( $idempresa,             $idsucursal,            $clpv_cod,          '$clpv_dir',
                                        1 )";
        $oIfx->QueryT($sqlDire);

        // TELEFONO
        $sqlTelf = "insert into saetlcp(tlcp_cod_empr,  tlcp_cod_sucu,  tlcp_cod_clpv,  tlcp_tip_ticp,  tlcp_tlf_tlcp )
                                 values($idempresa,     $idsucursal,    $clpv_cod,      'C',            '$clpv_tel' )";
        $oIfx->QueryT($sqlTelf);

        // CORREO
        $sqlEmai = "insert into saeemai(emai_cod_empr,  emai_cod_sucu,  emai_cod_clpv,  emai_ema_emai,      emai_cod_tiem )
                                 values($idempresa,     $idsucursal,    $clpv_cod,      '$clpv_correo',     '3' )";
        $oIfx->QueryT($sqlEmai);

        $oIfx->QueryT('COMMIT WORK;');
        $oCon->QueryT('COMMIT');

        $oReturn->script('habilitar_boton();');
        $oReturn->alert('Ingresado correctamente...');

        $oReturn->script('consultar();');
    } catch (Exception $e) {
        // rollback
        $oIfx->QueryT('ROLLBACK WORK;');
        $oCon->QueryT('ROLLBACK');
        $oReturn->alert($e->getMessage());
    }

    $oReturn->script("jsRemoveWindowLoad();");
    return $oReturn;
}

// GENERAR
function generar(
    $aForm = '',
    $tipo_compr = '',
    $factura = '',
    $ruc_emisor = '',
    $clpv_emisor = '',
    $fecha_emi = '',
    $fecha_auto = '',
    $tipo_emision = '',
    $ruc_recep = '',
    $clave_acceso = '',
    $y = '',
    $cont_clpv = ''
) {
    global $DSN_Ifx, $DSN;
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo();
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oCon = new Dbo();
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oReturn = new xajaxResponse();
    //////////////

    $idempresa  = $aForm['empresa'];
    $idsucursal = $aForm['sucursal'];
    $idempresa  = $aForm['empresa'];

    $tipo_fact  = $aForm['su.' . $y];
    $rete_ser   = $aForm['re.' . $y];
    $tipo_ser   = $aForm['ti.' . $y];
    $plan_ser   = $aForm['pl.' . $y];

    $pos        = $clave_acceso;
    $ambiente_sri = "S";

    $sHtml_clpv = '';


    if ($tipo_fact == 1 || $tipo_fact == 3) {
        // GASTO
        if (empty($cont_clpv)) {
            $cont_clpv = 0;
        }
        $sql = "select clv_con_clpv from saeclpv where clpv_cod_empr = $idempresa and clpv_cod_clpv = '$cont_clpv' ";
        $tipoRuc = consulta_string_func($sql, 'tlcp_tlf_tlcp', $oIfx, '');

        $strigTipoSustento = '';
        //query sustentos
        $sql = "select sustento from comercial.matriz_sri 
                where identificacion ='$tipoRuc' and
                estado = 'S' group by 1";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                $strigTipoSustento = ' where crtr_cod_crtr in(';
                do {
                    $strigTipoSustento_ .= $oCon->f('sustento') . ',';
                } while ($oCon->SiguienteRegistro());
                $strigTipoSustento_ .= substr($strigTipoSustento_, 0, strlen($strigTipoSustento_) - 1);
                $strigTipoSustento .= $strigTipoSustento_;
                $strigTipoSustento .= ' )';
            }
        }
        $oCon->Free();

        $sql = "select crtr_cod_crtr, crtr_des_crtr  from saecrtr
                    $strigTipoSustento";
        $lista_sust = '';
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                do {
                    $crtr_cod_crtr = $oIfx->f('crtr_cod_crtr');
                    $crtr_des_crtr = $crtr_cod_crtr . '  ' . htmlentities($oIfx->f('crtr_des_crtr'));

                    $selectedEmpr = '';
                    if ($crtr_cod_crtr == '02') {
                        $selectedEmpr = 'selected';
                    }

                    $lista_sust .= '<option value="' . $crtr_cod_crtr . '" ' . $selectedEmpr . '>' . $crtr_des_crtr . '</option>';
                } while ($oIfx->SiguienteRegistro());
            }
        }
        $oIfx->Free();

        // CONG PACCPP
        $sql = "select pccp_cod_facp from saepccp where pccp_cod_empr = $idempresa ";
        $tran = consulta_string_func($sql, 'pccp_cod_facp', $oIfx, 'FAC');

        // TRAN
        $sql = "SELECT tran_cod_tran, trans_tip_comp, tran_des_tran
                        FROM saetran WHERE
                        (trans_tip_comp is not null ) AND
                        tran_cod_empr = $idempresa AND
                        tran_cod_sucu = $idsucursal and
                        tran_cod_modu = 4 order by 2";
        $lista_tran = lista_boostrap_func($oIfx, $sql, $tran, 'tran_cod_tran',  'tran_des_tran');

        // TIPO DOCU
        $sql = "select tidu_cod_tidu, tidu_des_tidu
					from saetidu where
					tidu_cod_empr = $idempresa and
					tidu_cod_modu = 4 ";
        $lista_tidu = lista_boostrap_func($oIfx, $sql, '021', 'tidu_cod_tidu',  'tidu_des_tidu');

        // RESPONABLE CAJA CHICA
        $sql = "SELECT empl_cod_empl,  empl_nom_empl,    empl_ape_empl, empl_ape_nomb
                         FROM saeempl, SAEPCCH WHERE
                        SAEPCCH.pcch_cod_empl = saeempl.empl_cod_empl AND 
                        empl_cod_empr = $idempresa  order by 2 ";
        $lista_resp = lista_boostrap_func($oIfx, $sql, '', 'empl_cod_empl',  'empl_ape_nomb');


        unset($array);
        $array = clave_acceso($clave_acceso, $ambiente_sri, 2, $idempresa, $cont_clpv);
        $resp = '';
        $fecauto = '';
        $num_auto = '';
        $serie = '';
        $factura = '';
        $valor_grab12b = 0;
        $valor_grab0s = 0;
        $totalbien = 0;
        $totalserv = 0;
        $total_excento = 0;
        $total = 0;
        $imp = 0;

        if (count($array) > 0) {
            foreach ($array as $val) {
                $resp    = $val[0];
                $num_auto = $val[1];
                $serie   = $val[2];
                $factura = $val[3];
                $valor_grab12b = vacios($val[4], 0);
                $valor_grab0s  = vacios($val[5], 0);
                $imp           = $val[8] * 1;
                $total_excento  = $val[9];
                $total         = $valor_grab12b + $valor_grab0s + $imp + $total_excento;
            }
        }

        if ($resp == 'ok' || $resp == 'warning') {
            if ($resp == 'warning') {
                $resp_msn = "Factura Ingresada " . $serie . ' - ' . $factura;
                $oReturn->script('alerts("' . $resp_msn . '", "error");');
            }

            $sHtml_clpv  = '<br>';


            // Convertir la fecha con DateTime::createFromFormat
            $fecha_obj = DateTime::createFromFormat('d/m/Y', $fecha_emi);
            // Formatear la fecha al formato YYYY-MM-DD
            $fecha_emi = $fecha_obj->format('Y-m-d');




            $sHtml_clpv .= '<div class="col-md-12">
                                <div class="form-row">
                                    <div class="col-md-6">
                                            <label for="tipo_retencion">* Tipo Retencion:</label>
                                            <select id="tipo_retencion" name="tipo_retencion" 
                                                class="form-control input-sm" style="width:100%" onchange="retencion_auto( \'' . $ruc_emisor . '\' );" >
                                                <option value="">Seleccione una opcion..</option>
                                                <option value="1">Retencion Electronica</option>
                                                <option value="2">Retencion Preimpresa</option>
                                                <option value="3">Retencion Manual</option>
                                            </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label>Secuencial Retencion:</label>
                                        <div id="ret_auto"> </div>
                                    </div>
                                    
                                     <div class="col-md-1">
                                     </div>
                                     
                                     <div class="col-md-2">
                                        <label style="color: red">Fecha Retencion:</label>
                                        <div id=""> 
                                            <input type="date" class="form-control input-sm" id="fecha_retencion_fact" name="fecha_retencion_fact" style="width: 100%" value="' . date('Y-m-d') . '" />
                                        </div>
                                    </div>
                                    

                                    <div class="col-md-3">
                                        <label>Fecha Registro Contable:</label>
                                        <div id=""> 
                                            <input type="date" class="form-control input-sm" id="fecha_contable_fact" name="fecha_contable_fact" style="width: 100%" value="' . $fecha_emi . '" />
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <label>Fecha Emision Factura:</label>
                                        <div id=""> 
                                            <input type="date" class="form-control input-sm" id="fecha_emision_fact" name="fecha_emision_fact" style="width: 100%" value="' . $fecha_emi . '" />
                                        </div>
                                    </div>

                                </div>
                            </div>';
            $sHtml_clpv .= '<div class="col-md-12">
                                <div class="form-row">
                                    <div class="col-md-6">
                                        <label for="serie">* Serie:</label>
                                        <input type="text" class="form-control input-sm" id="serie" name="serie" value="' . $serie . '" style="text-align:right" readonly />
                                    </div>

                                    <div class="col-md-6">
                                        <label for="factura">* Factura:</label>
                                        <input type="text" class="form-control input-sm" id="factura" name="factura" value="' . $factura . '" style="text-align:right" readonly />
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-row">
                                    <div class="col-md-2">
                                        <label for="base12">Valor Base Imponible:</label>
                                        <input type="text" class="form-control input-sm" id="base12" name="base12" value="' . $valor_grab12b . '" style="text-align:right" readonly />
                                    </div>

                                    <div class="col-md-3">
                                        <label for="base0">Valor 0%:</label>
                                        <input type="text" class="form-control input-sm" id="base0" name="base0" value="' . $valor_grab0s . '" style="text-align:right" readonly />
                                    </div>

                                    <div class="col-md-2">
                                        <label for="iva">Impuesto%:</label>
                                        <input type="text" class="form-control input-sm" id="iva" name="iva" value="' . $imp . '" style="text-align:right" readonly />
                                    </div>

                                    <div class="col-md-2">
                                        <label for="total">Excento Impuesto:</label>
                                        <input type="text" class="form-control input-sm" id="excento_impuesto_ad" name="excento_impuesto_ad" value="' . $total_excento . '" style="text-align:right" readonly /> 
                                    </div>

                                    <div class="col-md-3">
                                        <label for="total">Total:</label>
                                        <input type="text" class="form-control input-sm" id="total" name="total" value="' . $total . '" style="text-align:right" readonly /> 
                                        <input type="text" class="form-control input-sm" id="total_ref_ad" name="total_ref_ad" value="' . $total . '" style="text-align:right; display: none" /> 
                                    </div>
                                </div>
                            </div>';

            if ($rete_ser == 1) {
                if ($tipo_ser == 1) {
                    // GENERAL
                    $sql = "SELECT p.cuenta_aplicada, p.credito_bienes, p.credito_servicios,
                                    p.rete_fuente_bienes, p.rete_fuente_servicios, 
                                    p.rete_iva_bienes, p.rete_iva_servicios, p.detalle
                                    from comercial.plantilla_clpv p WHERE 
                                    --id_clpv      = 0 AND 
                                    id_empresa   = $idempresa AND
                                    id           = '$plan_ser' order by 2 ";
                    if ($oCon->Query($sql)) {
                        if ($oCon->NumFilas() > 0) {
                            $pafa_rete_fuen    = $oCon->f('rete_fuente_bienes');
                            $pafa_rete_fuenser = $oCon->f('rete_fuente_servicios');
                            $pafa_rete_iva     = $oCon->f('rete_iva_bienes');
                            $pafa_rete_ivaser  = $oCon->f('rete_iva_servicios');
                            $detalle  = $oCon->f('detalle');
                        }
                    }
                    $oCon->Free();
                } else {
                    // CLIENTE
                    $sql = "SELECT p.cuenta_aplicada, p.credito_bienes, p.credito_servicios,
                                    p.rete_fuente_bienes, p.rete_fuente_servicios, 
                                    p.rete_iva_bienes, p.rete_iva_servicios
                                    from comercial.plantilla_clpv p WHERE 
                                    id_clpv      in ('$cont_clpv') AND 
                                    id_empresa   = $idempresa AND
                                    id           = '$plan_ser' order by 2 ";
                    if ($oCon->Query($sql)) {
                        if ($oCon->NumFilas() > 0) {
                            $pafa_rete_fuen     = $oCon->f('rete_fuente_bienes');
                            $pafa_rete_fuenser  = $oCon->f('rete_fuente_servicios');
                            $pafa_rete_iva      = $oCon->f('rete_iva_bienes');
                            $pafa_rete_ivaser   = $oCon->f('rete_iva_servicios');
                        }
                    }
                    $oCon->Free();
                }


                $sHtml_clpv .= '<div class="col-md-12">';
                if (!empty($pafa_rete_fuen)) {
                    $sql = "select tret_porct from saetret where tret_cod_empr = $idempresa and tret_cod  = '$pafa_rete_fuen' ";
                    $tret_porct = consulta_string_func($sql, 'tret_porct', $oIfx, '0');
                    $base_ret_f = $valor_grab12b + $valor_grab0s;
                    $rete_fuente = round((($base_ret_f * $tret_porct) / 100), 2);

                    $sHtml_clpv .= '<div class="col-md-3">
                                        <label for="retcodif">Codigo Retencion:</label>
                                        <input type="text" class="form-control input-sm" id="retcodif" name="retcodif" value="' . $pafa_rete_fuen . '" style="text-align:right" readonly />
                                    </div>';
                    $sHtml_clpv .= '<div class="col-md-3">
                                        <label for="retfuente">Valor Retencion Fuente:</label>
                                        <input type="text" class="form-control input-sm" id="retfuente" name="retfuente" value="' . $rete_fuente . '" style="text-align:right"  />
                                    </div>';
                }

                if (!empty($pafa_rete_fuenser)) {
                    $sql = "select tret_porct from saetret where tret_cod_empr = $idempresa and tret_cod  = '$pafa_rete_fuenser' ";
                    $tret_porct = consulta_string_func($sql, 'tret_porct', $oIfx, '0');
                    $base_ret_f = $valor_grab12b + $valor_grab0s;
                    $rete_fuente = round((($base_ret_f * $tret_porct) / 100), 2);

                    $sHtml_clpv .= '<div class="col-md-3">
                                        <label for="retcodifser">Codigo Retencion:</label>
                                        <input type="text" class="form-control input-sm" id="retcodifser" name="retcodifser" value="' . $pafa_rete_fuenser . '" style="text-align:right" readonly />
                                    </div>';
                    $sHtml_clpv .= '<div class="col-md-3">
                                        <label for="retfuenteser">Valor Retencion Fuente:</label>
                                        <input type="text" class="form-control input-sm" id="retfuenteser" name="retfuenteser" value="' . $rete_fuente . '" style="text-align:right"  />
                                    </div>';
                }


                if (!empty($pafa_rete_iva)) {
                    $sql = "select tret_porct from saetret where tret_cod_empr = $idempresa and tret_cod  = '$pafa_rete_iva' ";
                    $tret_porct = consulta_string_func($sql, 'tret_porct', $oIfx, '0');
                    $base_ret_i = $imp;
                    $rete_iva   = round((($base_ret_i * $tret_porct) / 100), 2);

                    $sHtml_clpv .= '<div class="col-md-3">
                                        <label for="retcodii">Codigo Retencion:</label>
                                        <input type="text" class="form-control input-sm" id="retcodii" name="retcodii" value="' . $pafa_rete_iva . '" style="text-align:right" readonly />
                                    </div>';
                    $sHtml_clpv .= '<div class="col-md-3">
                                        <label for="retiva">Valor Retencion Impuesto:</label>
                                        <input type="text" class="form-control input-sm" id="retiva" name="retiva" value="' . $rete_iva . '" style="text-align:right"  />
                                    </div>';
                }

                if (!empty($pafa_rete_ivaser)) {
                    $sql = "select tret_porct from saetret where tret_cod_empr = $idempresa and tret_cod  = '$pafa_rete_ivaser' ";
                    $tret_porct = consulta_string_func($sql, 'tret_porct', $oIfx, '0');
                    $base_ret_i = $imp;
                    $rete_iva   = round((($base_ret_i * $tret_porct) / 100), 2);

                    $sHtml_clpv .= '<div class="col-md-3">
                                        <label for="retcodiiser">Codigo Retencion:</label>
                                        <input type="text" class="form-control input-sm" id="retcodiiser" name="retcodiiser" value="' . $pafa_rete_ivaser . '" style="text-align:right" readonly />
                                    </div>';
                    $sHtml_clpv .= '<div class="col-md-3">
                                        <label for="retivaser">Valor Retencion Impuesto:</label>
                                        <input type="text" class="form-control input-sm" id="retivaser" name="retivaser" value="' . $rete_iva . '" style="text-align:right"  />
                                    </div>';
                }


                $sHtml_clpv .= '<div class="col-md-3">
                                        <label for="retivaser">Otros Valores Valor 0%:</label>
                                        <input type="number" class="form-control input-sm" id="otros_valores_0" name="otros_valores_0" value="" style="text-align:right" onchange="sumar_valor_otros()"  />
                                    </div>';


                $sHtml_clpv .= '</div>';
            }

            if ($tipo_fact == 3) {
                // CAAJA CHICA
                $sHtml_clpv .= '<div class="col-md-12">
                                    <div class="form-row">
                                        <div class="col-md-6">
                                            <label for="fprv_resp">* Responsable:</label>
                                            <select id="fprv_resp" name="fprv_resp" class="form-control input-sm" style="width:100%">
                                                <option value="">Seleccione una opcion..</option>
                                                ' . $lista_resp . '
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="fprv_caja">* N.- Caja:</label>
                                            <input type="text" class="form-control input-sm" id="fprv_caja" name="fprv_caja" value="" style="text-align:right" />
                                        </div>
                                    </div>
                                </div>';
            }

            $sHtml_clpv .= '<div class="col-md-12">
                                <div class="form-row">
                                    <div class="col-md-12">
                                        <label for="sustento">* Sustento Tributario:</label>
                                        <select id="sustento" name="sustento" class="form-control input-sm">
                                            <option value="">Seleccione una opcion..</option>
                                            ' . $lista_sust . '
                                        </select>
                                    </div>
                                </div>
                            </div>';
            $sHtml_clpv .= '<div class="col-md-12">
                                <div class="form-row">
                                    <div class="col-md-12">
                                        <label for="comprobante">* Comprobante:</label>
                                        <select id="comprobante" name="comprobante" class="form-control input-sm" style="width:100%">
                                            <option value="">Seleccione una opcion..</option>
                                            ' . $lista_tran . '
                                        </select>
                                    </div>
                                </div>
                            </div>';
            $sHtml_clpv .= '<div class="col-md-12">
                                <div class="form-row">
                                    <div class="col-md-12">
                                        <label for="tidu">* Tipo Documento:</label>
                                            <select id="tidu" name="tidu" class="form-control input-sm" style="width:100%">
                                                <option value="">Seleccione una opcion..</option>
                                                ' . $lista_tidu . '
                                            </select>
                                    </div>
                                </div>
                            </div>';
            $sHtml_clpv .= '<div class="col-md-12">
                            <div class="form-row">
                                <div class="col-md-12">
                                    <label for="tidu" style="color: red">* Detalle: (Completar Detalle de Compra):</label>
                                        <input type="text" name="detalle_asiento" id="detalle_asiento" value="' . $detalle . '"class="form-control">
                                </div>
                            </div>
                        </div>';

            if ($resp == 'ok') {
                $sHtml_clpv .= '<br><br><br><br><br><br><div class="col-md-12">
                                    <div class="form-row">
                                        <div class="col-md-12">
                                                <div id ="imagen1" class="btn btn-primary btn-sm" 
                                                        onclick="guardar_gasto(\'' . $tipo_compr . '\', \'' . $factura . '\' , \'' . $ruc_emisor . '\' , \'' . $clpv_emisor . '\', 
                                                        \'' . $fecha_emi . '\' ,   \'' . $fecha_auto . '\', \'' . $tipo_emision . '\',  \'' . $ruc_recep . '\', 
                                                        \'' . $clave_acceso . '\', \'' . $y . '\', \'' . $cont_clpv . '\');" style="width:100%">
                                                        <span class="glyphicon glyphicon-floppy-disk"></span>
                                                        GUARDAR
                                                </div>
                                        </div>
                                    </div>
                                </div>';
            } else {
                $sHtml_clpv .= '<div class="col-md-12">
                                    <div class="form-row">
                                        <div class="col-md-12">
                                        </div>
                                    </div>
                                </div>';
            }
        } else {
            $oReturn->script('alerts("' . $num_auto . '", "error");');
        }
    }


    $sHtml .= '<div class="table-responsive" style="width: 100%; height: 80%; overflow-y: scroll;">';
    $sHtml .= $sHtml_clpv;
    $sHtml .= '</div>';

    $boton  = '<div class="btn btn-warning btn-sm" 
                    onclick="asto_gasto(\'' . $tipo_compr . '\', \'' . $factura . '\' , \'' . $ruc_emisor . '\' , \'' . $clpv_emisor . '\', 
                    \'' . $fecha_emi . '\' ,   \'' . $fecha_auto . '\', \'' . $tipo_emision . '\',  \'' . $ruc_recep . '\', 
                    \'' . $clave_acceso . '\', \'' . $y . '\', \'' . $cont_clpv . '\');" >
                    <span class="glyphicon glyphicon-list"></span>
                    ASIENTO
                </div> ';
    $oReturn->assign("fact_det", "innerHTML", $sHtml);
    $oReturn->assign("fac_dat", "innerHTML", htmlentities($clpv_emisor) . ' ' . $boton);
    $oReturn->script("jsRemoveWindowLoad();");

    return $oReturn;
}

// GENERA CLAVE
function clave_acceso($clave_acceso = '', $ambiente_sri = '', $tipo = '', $idempresa = '', $clpv_cod = '')
{
    global $DSN_Ifx;
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo();
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oReturn = new xajaxResponse();

    $pos        = $clave_acceso;

    unset($array);
    // GENERAR DATOS SRI
    try {


        $headers = array(
            "Content-Type:application/json",
            "Token-Api:9c0ab4af-30dc-4b85-93e2-f0cd28dd7e51"
        );
        $data = array(
            "clave_acceso" => $clave_acceso
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, URL_JIREH_WS . "/api/facturacion/electronica/autorizacion/comprobante");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $respuesta = curl_exec($ch);
        $autoComp[$pos] = (object) json_decode($respuesta, true);
        $data = $autoComp[$pos];
        // $autorizado = $autoComp[$pos]->estado;


        // var_dump($autoComp[$pos]);
        // exit;
        // Adrian47



        //$clientOptions = array(
        //"useMTOM" => FALSE,
        //'trace' => 1,
        //'stream_context' => stream_context_create(array('http' => array('protocol_version' => 1.0) ) )
        //);   

        //if($ambiente_sri == 'S'){
        //	$wsdlAutoComp[$pos] = new  SoapClient("https://cel.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl", $clientOptions);
        //}else{
        //	$wsdlAutoComp[$pos] = new  SoapClient("https://celcer.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl", $clientOptions);
        //}     

        ////RECUPERA LA AUTORIZACION DEL COMPROBANTE
        //$aClave = array("claveAccesoComprobante" => $clave_acceso);

        //$autoComp[$pos] = new stdClass();
        //$autoComp[$pos] = $wsdlAutoComp[$pos]->autorizacionComprobante($aClave);

        $RespuestaAutorizacionComprobante[$pos]    = $data;
        $claveAccesoConsultada[$pos]             = $RespuestaAutorizacionComprobante[$pos]->claveAccesoConsultada;
        // $autorizaciones[$pos] 					= $RespuestaAutorizacionComprobante[$pos]->autorizaciones;
        // $autorizacion[$pos]						= $autorizaciones[$pos]->autorizacion; 
        $autorizacion[$pos]                        = 1;

        // var_dump($autorizaciones[$pos]);
        // exit;

        if (count($autorizacion[$pos]) > 1) {
            $estado[$pos]                 = $autorizacion[$pos][0]->estado;
            $numeroAutorizacion[$pos]     = $autorizacion[$pos][0]->numeroAutorizacion;
            $fechaAutorizacion[$pos]     = $autorizacion[$pos][0]->fechaAutorizacion;
            $ambiente[$pos]             = $autorizacion[$pos][0]->ambiente;
            $comprobante[$pos]             = $autorizacion[$pos][0]->comprobante;
            $mensajes[$pos]             = $autorizacion[$pos][0]->mensajes;
            $mensaje[$pos]                 = $mensajes[$pos]->mensaje;
        } else {
            $estado[$pos]                 = $RespuestaAutorizacionComprobante[$pos]->estado;
            $numeroAutorizacion[$pos]     = $RespuestaAutorizacionComprobante[$pos]->numeroAutorizacion;
            $fechaAutorizacion[$pos]     = $RespuestaAutorizacionComprobante[$pos]->fechaAutorizacion;
            $ambiente[$pos]             = $RespuestaAutorizacionComprobante[$pos]->ambiente;
            $comprobante[$pos]             = $RespuestaAutorizacionComprobante[$pos]->comprobante;
            $mensajes[$pos]             = $RespuestaAutorizacionComprobante[$pos]->mensajes;
            $mensaje[$pos]                 = $mensajes[$pos]->mensaje;
        }

        $xml    =    '';

        $xml     .=    "$comprobante[$pos]";
        //$xml 	.=	'</autorizacion>';


        // var_dump($RespuestaAutorizacionComprobante[$pos]);
        // exit;


        if ($estado[$pos] == 'AUTORIZADO') {
            // GUARDAR EN XML
            // CREAR CARPETA ANEXO
            //$serv = "C:";
            $ruta = "Doc_Electronicos_Web_SRI";
            // CARPETA EMPRESA
            $ruta_empr = $ruta . "/" . $idempresa;
            if (!file_exists($ruta)) {
                mkdir($ruta);
            }

            if (!file_exists($ruta_empr)) {
                mkdir($ruta_empr);
            }

            // ruta del xml
            $nombre = $clave_acceso . ".xml";
            $archivo = fopen($nombre, "w+");
            fwrite($archivo, $xml);
            fclose($archivo);

            // ruta del xml
            $archivo_xml = fopen($ruta_empr . '/' . $nombre, "w+");
            $ruta_xml    = $ruta_empr . '/' . $nombre;
            fwrite($archivo_xml, $xml);
            fclose($archivo_xml);
            $xmlParse             = simplexml_load_file($ruta_xml);

            $estab             = $xmlParse->infoTributaria->estab;
            $ptoEmi         = $xmlParse->infoTributaria->ptoEmi;
            $secuencial     = $xmlParse->infoTributaria->secuencial;
            $codDoc     = $xmlParse->infoTributaria->codDoc;
            $identificacionComprador = $xmlParse->infoFactura->identificacionComprador;
            $fechaEmision = strval($xmlParse->infoFactura->fechaEmision);
            $identificacionProveedor = $xmlParse->infoTributaria->ruc;
            $totalImpuesto     = $xmlParse->infoFactura->totalConImpuestos->totalImpuesto;
            $detalles = $xmlParse->detalles->detalle;
            //$detalle=$detalles[0];
            foreach ($detalles as $arreglo1) {
                $deta = $arreglo1->descripcion;
            }

            $totalbien = 0;
            $totalserv = 0;
            $totalexcento = 0;
            $valor_grab12b = 0;
            $valor_grab0s = 0;
            $valor = 0;
            $base_15 = 0;
            $base_5 = 0;
            $base_0 = 0;
            $base_exenta = 0;
            $iva_15 = 0;
            $iva_5 = 0;
            $iva_total = 0;
            foreach ($totalImpuesto as $bases) {
                $codigoPorcentaje = isset($bases->codigoPorcentaje) ? trim((string) $bases->codigoPorcentaje) : '';
                $baseImponible = floatval($bases->baseImponible);
                $valorImp = floatval($bases->valor);
                $tarifa = isset($bases->tarifa) ? round(floatval($bases->tarifa), 2) : null;

                $iva_total += $valorImp;

                if ($tarifa === 15.00) {
                    $base_15 += $baseImponible;
                    $iva_15 += $valorImp;
                } elseif ($tarifa === 5.00) {
                    $base_5 += $baseImponible;
                    $iva_5 += $valorImp;
                } elseif ($tarifa === 0.00 || $codigoPorcentaje === '0') {
                    $base_0 += $baseImponible;
                } elseif ($codigoPorcentaje === '7') {
                    $base_exenta += $baseImponible;
                } else {
                    // Mantener compatibilidad para codigos no contemplados explicitamente.
                    $base_15 += $baseImponible;
                    $iva_15 += $valorImp;
                }
            }

            $valor_grab12b = $base_15 + $base_5;
            $valor_grab0s = $base_0;
            $totalexcento = $base_exenta;
            $valor = $iva_total;
            $totalbien = $valor_grab12b;
            $totalserv = $valor_grab0s;

            $serie      = $estab . $ptoEmi;
            $secuencial = $secuencial . '';

            $sql_control = "select count(*) as contador from saeminv where 
                                minv_cod_empr = $idempresa and
                                minv_ser_docu = '$serie' and  
                                minv_cod_clpv = '$clpv_cod' and	
                                minv_est_minv <> '0' and 
                                minv_fac_prov = '$secuencial'";
            $contador_ = consulta_string_func($sql_control, 'contador', $oIfx, '0');

            $sql_control_ = "select count(*) as contador from saefprv  where 
                                fprv_cod_empr     = $idempresa
                                and fprv_cod_clpv = '$clpv_cod'
                                and fprv_num_fact = '$secuencial'
                                and fprv_num_seri = '$serie' ";
            $contador_1 = consulta_string_func($sql_control_, 'contador', $oIfx, '0');

            if ($tipo == '2') {
                // GASTO
                // if($ruc == $identificacionComprador.''){
                if ($contador_ > 0 || $contador_1 > 0) {
                    $mensaje  = "Factura Numero: " . $secuencial . " Ya esta Ingresada.....";
                    //$array [] = array('warning', $mensaje);
                    $array[] = array(
                        'warning',
                        $numeroAutorizacion[$pos],
                        $serie,
                        $secuencial,
                        $valor_grab12b,
                        $valor_grab0s,
                        $totalbien,
                        $totalserv,
                        $valor,
                        $totalexcento,
                        $base_15,
                        $base_5,
                        $base_0,
                        $base_exenta,
                        $iva_15,
                        $iva_5,
                        $iva_total
                    );
                } else {
                    $array[] = array(
                        'ok',
                        $numeroAutorizacion[$pos],
                        $serie,
                        $secuencial,
                        $valor_grab12b,
                        $valor_grab0s,
                        $totalbien,
                        $totalserv,
                        $valor,
                        $totalexcento,
                        $base_15,
                        $base_5,
                        $base_0,
                        $base_exenta,
                        $iva_15,
                        $iva_5,
                        $iva_total
                    );
                }

                /*}else{
					$mensaje='El numero de identificacion del Proveedor: '. $ruc . ' no coincide con la identificacion del archivo xml: '.$identificacionComprador;
					$tipo_mesaje='info';
					$oReturn->script('alerts("'.$mensaje.'", "'.$tipo_mesaje.'");');					
				}*/
            } elseif ($tipo == 1) {
                // ALMACEN
                if ($contador_ > 0 || $contador_1 > 0) {
                    $mensaje = "Factura Numero: " . $secuencial . " Ya esta Ingresada.....";
                    // $array [] = array('warning', $mensaje);   

                    foreach ($xmlParse->detalles->detalle as $arreglo) {
                        $cantidad   = floatval($arreglo->cantidad);
                        $costo      = floatval($arreglo->precioUnitario);
                        $descuento  = floatval($arreglo->descuento);
                        $iva        = floatval($arreglo->impuestos->impuesto->tarifa);
                        $prod_cod   = $arreglo->codigoPrincipal;
                        $prod_nom   = $arreglo->descripcion;
                        if ($costo > 0  && $cantidad > 0) {
                            $desc_porc  = ($descuento * 100) / ($costo * $cantidad);
                        } else {
                            $desc_porc  = 0;
                        }


                        $array[] = array(
                            'warning',
                            $numeroAutorizacion[$pos],
                            $serie,
                            $secuencial,
                            $valor_grab12b,
                            $valor_grab0s,
                            $totalbien,
                            $totalserv,
                            $valor,
                            $prod_cod,
                            $prod_nom,
                            $cantidad,
                            $costo,
                            $descuento,
                            $desc_porc,
                            $iva,
                            $totalexcento
                        );
                    } // fin forach 

                } else {
                    foreach ($xmlParse->detalles->detalle as $arreglo) {
                        $cantidad   = floatval($arreglo->cantidad);
                        $costo      = floatval($arreglo->precioUnitario);
                        $descuento  = floatval($arreglo->descuento);
                        $iva        = floatval($arreglo->impuestos->impuesto->tarifa);
                        $prod_cod   = $arreglo->codigoPrincipal;
                        $prod_nom   = $arreglo->descripcion;
                        if ($costo > 0  && $cantidad > 0) {
                            $desc_porc  = ($descuento * 100) / ($costo * $cantidad);
                        } else {
                            $desc_porc  = 0;
                        }


                        $array[] = array(
                            'ok',
                            $numeroAutorizacion[$pos],
                            $serie,
                            $secuencial,
                            $valor_grab12b,
                            $valor_grab0s,
                            $totalbien,
                            $totalserv,
                            $valor,
                            $prod_cod,
                            $prod_nom,
                            $cantidad,
                            $costo,
                            $descuento,
                            $desc_porc,
                            $iva,
                            $totalexcento
                        );
                    } // fin forach                        
                }
            }
        } else {
            $informacionAdicional = (strtoupper($mensaje[$clave_acceso]->informacionAdicional));
            //$informacionAdicional = preg_replace('([^A-Za-z0-9 ])', '',strtoupper($mensaje[$clave_acceso][0]->informacionAdicional));
            $informacionAdicional = htmlspecialchars_decode($informacionAdicional);
            //$oReturn->alert('Error...'.$informacionAdicional);
            $array[] = array('error', 'SIN CONEXION SRI');
        }
    } catch (SoapFault $e) {
        //$oReturn->alert($pos.' NO HUBO CONECCION AL SRI (AUTORIZAR)');
        $array[] = array('error', 'NO HUBO CONECCION AL SRI (AUTORIZAR)');
    }


    return $array;
}


// GUARDAR GASTO
function guardar_gasto(
    $aForm = '',
    $tipo_compr = '',
    $factura = '',
    $ruc = '',
    $cliente_nombre = '',
    $fecha_emi = '',
    $fecha_auto = '',
    $tipo_emision = '',
    $ruc_recep = '',
    $clave_acceso = '',
    $y = '',
    $cod_prove = ''
) {
    //Definiciones
    global $DSN, $DSN_Ifx;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oReturn = new xajaxResponse();

    //      VARIABLES
    $idempresa   = $aForm['empresa'];
    $idsucursal  = $aForm['sucursal'];
    $user_web    = $_SESSION['U_ID'];
    $serie       = $aForm['serie'];
    $factura     = $aForm['factura'];
    $base12      = $aForm['base12'];
    $base0       = $aForm['base0'];
    $excento_impuesto_ad = $aForm['excento_impuesto_ad'];
    $iva         = $aForm['iva'];
    $total       = $aForm['total'];
    $sust_trib   = $aForm['sustento'];
    $comprobante = $aForm['comprobante'];
    $otros_valores_0 = $aForm['otros_valores_0'];
    if ($otros_valores_0 > 0) {
        $base0 = $base0 + $otros_valores_0;
    }

    $fecha_contable_fact = $aForm['fecha_contable_fact'];
    $fecha_emision_fact = $aForm['fecha_emision_fact'];
    $fecha_retencion_fact = $aForm['fecha_retencion_fact'];


    $detalle = $aForm['detalle_asiento'];

    $tipo_doc    = $aForm['tidu'];
    $usuario_informix = $_SESSION['U_USER_INFORMIX'];
    $fecha_server   = date("Y-m-d");
    $hora           = date("Y-m-d H:i:s");
    $fechaServer    = date("Y-m-d H:i:s");

    $tipo_fact  = $aForm['su.' . $y];
    $rete_ser   = $aForm['re.' . $y];
    $tipo_ser   = $aForm['ti.' . $y];
    $plan_ser   = $aForm['pl.' . $y];
    $trib_ser   = 'S';

    $tipo_retencion  = $aForm['tipo_retencion'];
    $rete_manual     = $aForm['rete_manual'];


    $rete_manual     = $aForm['rete_manual'];
    $asto_cuadre = $aForm['asto_cuadre'];
    //echo $asto_cuadre;exit;





    try {
        // commit
        $oIfx->QueryT('BEGIN WORK;');
        $oCon->QueryT('BEGIN');

        if (empty($asto_cuadre)) {
            throw new Exception("Primero habra la vista previa del asiento contable");
        } elseif ($asto_cuadre == 'D') {
            throw new Exception("Asiento Contable Descuadrado");
        }
        $sql = "select pcon_mon_base , pcon_seg_mone from saepcon where pcon_cod_empr = $idempresa  ";
        $moneda = consulta_string_func($sql, 'pcon_mon_base', $oIfx, '1');
        $mone_extr = consulta_string_func($sql, 'pcon_seg_mone', $oIfx, '');

        $sql = "select tcam_valc_tcam from saetcam where
				mone_cod_empr = $idempresa and
				tcam_cod_mone = $mone_extr and
				tcam_fec_tcam in (select max(tcam_fec_tcam)  from saetcam where
											mone_cod_empr = $idempresa and
											tcam_cod_mone = $mone_extr)";
        $coti = consulta_string_func($sql, 'tcam_valc_tcam', $oIfx, 0);

        if ($tipo_ser == 1) {
            // PLANILLA GENERAL
            $sql = "SELECT p.cuenta_aplicada, p.credito_bienes, p.credito_servicios,  detalle
                            from comercial.plantilla_clpv p WHERE 
                            id_clpv      in ('0') AND 
                            id_empresa   = $idempresa AND
                            id           = '$plan_ser' order by 2 ";
            if ($oCon->Query($sql)) {
                if ($oCon->NumFilas() > 0) {
                    // $detalle = $oCon->f('detalle') . ' FC: ' . $serie . '-' . $factura;
                    $gasto1  = $oCon->f('cuenta_aplicada');
                    $fisc_b  = $oCon->f('credito_bienes');
                    if (empty($fisc_b)) {
                        $fisc_b  = $oCon->f('credito_servicios');
                    }
                }
            }
            $oCon->Free();

            $sql = "SELECT c.cuenta, c.centro_costos, c.porcentaje
                            FROM comercial.plantilla_ccosn c WHERE
                            c.id_empresa    = $idempresa AND
                            c.id_clpv       = 0 AND
                            c.id_plantilla  = '$plan_ser' ";
            unset($array_cuen);
            if ($oCon->Query($sql)) {
                if ($oCon->NumFilas() > 0) {
                    do {
                        $cuenta         = $oCon->f('cuenta');
                        $centro_costos  = $oCon->f('centro_costos');
                        $porcentaje     = $oCon->f('porcentaje');
                        $array_cuen[]  = array($cuenta, $centro_costos, $porcentaje);
                    } while ($oCon->SiguienteRegistro());
                }
            }
            $oCon->Free();
        } else {
            // PLANILLA CLIENTE
            $sql = "SELECT p.cuenta_aplicada, p.credito_bienes, p.credito_servicios,  detalle
                            from comercial.plantilla_clpv p WHERE 
                            id_clpv      in ('$cod_prove') AND 
                            id_empresa   = $idempresa AND
                            id           = '$plan_ser' order by 2 ";
            if ($oCon->Query($sql)) {
                if ($oCon->NumFilas() > 0) {
                    // $detalle = $oCon->f('detalle') . ' FC: ' . $serie . '-' . $factura;
                    $gasto1  = $oCon->f('cuenta_aplicada');
                    $fisc_b  = $oCon->f('credito_bienes');
                    if (empty($fisc_b)) {
                        $fisc_b  = $oCon->f('credito_servicios');
                    }
                }
            }
            $oCon->Free();

            $sql = "SELECT c.cuenta, c.centro_costos, c.porcentaje
                            FROM comercial.plantilla_ccosn c WHERE
                            c.id_empresa    = $idempresa AND
                            c.id_clpv       = $cod_prove AND
                            c.id_plantilla  = '$plan_ser' ";
            unset($array_cuen);
            if ($oCon->Query($sql)) {
                if ($oCon->NumFilas() > 0) {
                    do {
                        $cuenta         = $oCon->f('cuenta');
                        $centro_costos  = $oCon->f('centro_costos');
                        $porcentaje     = $oCon->f('porcentaje');
                        $array_cuen[]  = array($cuenta, $centro_costos, $porcentaje);
                    } while ($oCon->SiguienteRegistro());
                }
            }
            $oCon->Free();
        }

        if (empty($cuenta)) {
            throw new Exception("No existe cuenta, parametrizar en la planilla general");
        }


        // direccion
        $sql = "select min( dire_dir_dire ) as dire  from saedire where
                    dire_cod_empr = $idempresa and
                    dire_cod_clpv = $cod_prove ";
        $direccion = consulta_string_func($sql, 'dire', $oIfx, '');

        // email
        $sql = "select min( emai_ema_emai ) as ema  from saeemai where
                emai_cod_empr = $idempresa and
                emai_cod_clpv = $cod_prove";
        $fprv_email_clpv = consulta_string_func($sql, 'ema', $oIfx, '');

        $sql = "select empr_iva_empr from saeempr where empr_cod_empr = $idempresa ";
        $empr_iva_empr = consulta_string_func($sql, 'empr_iva_empr', $oIfx, '0');

        $rise       = 'N';
        $doble_trib = 'N';
        $suje_rete  = 'N';

        // BIENES
        $valor_grab12b  = $base12;
        $valor_grab0b   = $base0;
        $iceb           = 0;
        $ivab           = $iva;
        $ivabp          = $empr_iva_empr;
        $val_gasto1     = $base12 + $base0;
        $val_gasto2     = 0;

        // SERVICIOS
        $valor_grab12s  = 0;
        $valor_grab0s   = 0;
        $ices           = 0;
        $ivas           = 0;
        $totals         = 0;

        // TOTAL
        $valor_grab12t  = $base12;
        $valor_grab0t   = $base0;
        $icet           = 0;
        $ivat           = $iva;

        //no objeto y excento de iva
        $valor_noObjIva = 0;
        $valor_exentoIva = $excento_impuesto_ad;
        if (empty($valor_exentoIva)) {
            $valor_exentoIva = 0;
        }
        if (empty($excento_impuesto_ad)) {
            $excento_impuesto_ad = 0;
        }
        $no_obj_iva     = '';

        $sql = "select ftrn_cod_ftrn, ftrn_des_ftrn 
                    from saeftrn where
                    ftrn_cod_empr = $idempresa and
                    ftrn_cod_modu = 4 and
                    ftrn_tip_movi = 'DI' ";
        $formato = consulta_string_func($sql, 'ftrn_cod_ftrn', $oIfx, '0');

        $fprv_aprob_sri = 'N';
        $fprv_auto_sri  = '';
        $fprv_fech_sri  = '';
        $ret_elec_sn    = 'S';
        $serie_rete  = '';
        $auto_rete      = '';
        $cad_rete      = '';
        $electronica = 'N';
        $num_rete    = '';
        unset($array_rete);
        $val_fue_b = 0;
        $val_iva_b = 0;
        $val_fue_bser = 0;
        $val_iva_bser = 0;
        if ($rete_ser == 1) {
            // RETENCION FUENTE            
            $codigo_ret_fue_b    = $aForm['retcodif'];
            $base_fue_b          = $valor_grab12t + $valor_grab0t;
            $sql = "select tret_porct , tret_cta_cre from saetret where tret_cod_empr = $idempresa and tret_cod  = '$codigo_ret_fue_b' ";
            $porc_ret_fue_b      = consulta_string_func($sql, 'tret_porct', $oIfx, '0');
            $tret_cta_creb       = consulta_string_func($sql, 'tret_cta_cre', $oIfx, '0');
            $val_fue_b           = vacios($aForm['retfuente'], 0);

            $codigo_ret_iva_b    = $aForm['retcodii'];
            $base_iva_b         =  $iva;
            $sql = "select tret_porct, tret_cta_cre from saetret where tret_cod_empr = $idempresa and tret_cod  = '$codigo_ret_iva_b' ";
            $porc_ret_iva_b      = consulta_string_func($sql, 'tret_porct', $oIfx, '0');
            $tret_cta_crei       = consulta_string_func($sql, 'tret_cta_cre', $oIfx, '0');
            $val_iva_b           = vacios($aForm['retiva'], 0);

            // RETENCION FUENTE  SERVICIOS
            $codigo_ret_fue_bser    = $aForm['retcodifser'];
            $base_fue_bser          = $valor_grab12t + $valor_grab0t;
            $sql = "select tret_porct , tret_cta_cre from saetret where tret_cod_empr = $idempresa and tret_cod  = '$codigo_ret_fue_bser' ";
            $porc_ret_fue_bser      = consulta_string_func($sql, 'tret_porct', $oIfx, '0');
            $tret_cta_crebser       = consulta_string_func($sql, 'tret_cta_cre', $oIfx, '0');
            $val_fue_bser           = vacios($aForm['retfuenteser'], 0);

            $codigo_ret_iva_bser    = $aForm['retcodiiser'];
            $base_iva_bser         =  $iva;
            $sql = "select tret_porct, tret_cta_cre from saetret where tret_cod_empr = $idempresa and tret_cod  = '$codigo_ret_iva_bser' ";
            $porc_ret_iva_bser      = consulta_string_func($sql, 'tret_porct', $oIfx, '0');
            $tret_cta_creiser       = consulta_string_func($sql, 'tret_cta_cre', $oIfx, '0');
            $val_iva_bser           = vacios($aForm['retivaser'], 0);

            if (!empty($codigo_ret_fue_b) || !empty($codigo_ret_iva_b)) {
                // BIENES
                $valor_grab12b  = $base12;
                $valor_grab0b   = $base0;
                $val_fue_b = $val_fue_b;
                $porc_ret_iva_b = $porc_ret_iva_b;
                $val_iva_b = $val_iva_b;
                $base_iva_b = $base_iva_b;
                $base_fue_b = $base_fue_b;


                $valor_grab12s  = 0;
                $valor_grab0s   = 0;
                $val_fue_s = 0;
                $porc_ret_iva_s = 0;
                $val_iva_s = 0;
                $base_iva_s = 0;
                $base_fue_s =  0;
            }

            if (!empty($codigo_ret_fue_bser) || !empty($codigo_ret_iva_bser)) {
                // SERVICIOS
                $valor_grab12s  = $base12;
                $valor_grab0s   = $base0;
                $val_fue_s = $val_fue_b;
                $porc_ret_iva_s = $porc_ret_iva_b;
                // $val_iva_s = $val_iva_bser;
                $base_iva_s = $base_iva_bser;
                $base_fue_s =  $base_fue_bser;

                $valor_grab12b  = 0;
                $valor_grab0b   = 0;
                $val_fue_b = 0;
                $porc_ret_iva_b = 0;
                $val_iva_b = 0;
                $base_iva_b = 0;
                $base_fue_b = 0;
            }



            if (empty($val_iva_s)) {
                $val_iva_s = 0;
            }

            if (empty($base_iva_s)) {
                $base_iva_s = 0;
            }

            if (empty($base_fue_s)) {
                $base_fue_s = 0;
            }

            if (empty($val_iva_s)) {
                $val_iva_s = 0;
            }

            if (empty($val_iva_b)) {
                $val_iva_b = 0;
            }

            if (empty($base_iva_b)) {
                $base_iva_b = 0;
            }

            if (empty($base_fue_b)) {
                $base_fue_b = 0;
            }

            // ------------------------------------------------------------------
            // Script retencion control de saetran y tipo para la retencion unicamente 332 Adrian47
            // ------------------------------------------------------------------
            $comprobante = $aForm['comprobante'];
            $tipo_retencion = $aForm['tipo_retencion'];

            $sql_saetran = "SELECT trans_tip_comp from saetran where tran_cod_tran = '$comprobante' and trans_tip_comp is not null;";
            $trans_tip_comp = consulta_string_func($sql_saetran, 'trans_tip_comp', $oIfx, '');

            $contador = 0;
            $variable_llena = "";


            if (!empty($codigo_ret_fue_b)) {
                $contador++;
                $variable_llena = "codigo_ret_fue_b";
            }

            if (!empty($codigo_ret_iva_b)) {
                $contador++;
                $variable_llena = "codigo_ret_iva_b";
            }

            if (!empty($codigo_ret_fue_bser)) {
                $contador++;
                $variable_llena = "codigo_ret_fue_bser";
            }

            if (!empty($codigo_ret_iva_bser)) {
                $contador++;
                $variable_llena = "codigo_ret_iva_bser";
            }

            $actualizar_secuencial = 'S';
            if ($trans_tip_comp == '01' && $tipo_retencion == 1 && $contador == 1) {
                if ($$variable_llena == '332') {
                    $actualizar_secuencial = 'N';
                }
            }
            // ------------------------------------------------------------------
            // FIN Script retencion control de saetran y tipo para la retencion unicamente 332
            // ------------------------------------------------------------------




            if ($tipo_retencion == 1) { // ELECTRONICO
                $sql = "select retp_sec_retp , retp_num_seri from saeretp where 
                            retp_cod_empr = $idempresa and
                            retp_cod_sucu = $idsucursal and
                            retp_act_retp = '1' and
                            retp_elec_sn  = 'S' ";
                $num_rete     = consulta_string_func($sql, 'retp_sec_retp', $oIfx, '');
                $serie_rete   = consulta_string_func($sql, 'retp_num_seri', $oIfx, '');
                $num_rete     = secuencial(2, '', $num_rete, 9);

                if ($actualizar_secuencial == 'S') {
                    $num_rete_sec = $num_rete * 1;
                    $sql = "update saeretp set retp_sec_retp = ($num_rete_sec ) 
                                    where retp_cod_empr = $idempresa and
                                    retp_cod_sucu = $idsucursal and
                                    retp_act_retp = 1 and
                                    retp_elec_sn = 'S' ";
                    $oIfx->QueryT($sql);
                }

                $ret_elec_sn    = 'S';
            } elseif ($tipo_retencion == 2) {  // PREIMPRESA
                $sql = "select retp_sec_retp , retp_num_seri from saeretp where 
                            retp_cod_empr = $idempresa and
                            retp_cod_sucu = $idsucursal and
                            retp_act_retp = '1' and
                            retp_elec_sn  = 'N' ";
                $num_rete     = consulta_string_func($sql, 'retp_sec_retp', $oIfx, '');
                $serie_rete   = consulta_string_func($sql, 'retp_num_seri', $oIfx, '');
                $num_rete     = secuencial(2, '', $num_rete, 9);

                if ($actualizar_secuencial == 'S') {
                    $num_rete_sec = $num_rete * 1;
                    $sql = "update saeretp set retp_sec_retp = ($num_rete_sec ) 
                                where retp_cod_empr = $idempresa and
                                retp_cod_sucu = $idsucursal and
                                retp_act_retp = 1 and
                                retp_elec_sn = 'N' ";
                    $oIfx->QueryT($sql);
                }
                $fprv_aprob_sri = 'S';
                $ret_elec_sn    = 'N';
            } elseif ($tipo_retencion == 3) { // MANUAL
                // RETENCION MANUAL
                $sql = "SELECT c.id_rete_elec, c.retencion_serie, c.retencion_num, c.retencion_auto, c.clave_acceso
                                FROM cxp_retencion c WHERE
                                c.empr_cod_empr = $idempresa AND
                                c.id_rete_elec  = $rete_manual ";
                if ($oCon->Query($sql)) {
                    if ($oCon->NumFilas() > 0) {
                        $serie_rete     = $oCon->f('retencion_serie');
                        $num_rete       = $oCon->f('retencion_num');
                        $reten_fauto    = $oCon->f('retencion_auto');
                        $reten_auto     = $oCon->f('clave_acceso');
                    }
                }
                $oCon->Free();
                $fprv_aprob_sri = 'S';
                $fprv_auto_sri  = $reten_auto;
                $fprv_fech_sri  = $reten_fauto;
                $ret_elec_sn    = 'S';

                $sql = "update cxp_retencion set aprobado = 'S' where empr_cod_empr = $idempresa AND id_rete_elec  = $rete_manual ";
                $oCon->QueryT($sql);
            }


            $array_rete[0] = array($codigo_ret_fue_b, $base_fue_b, $porc_ret_fue_b, $val_fue_b, $num_rete, $serie_rete, $tret_cta_creb, $ret_elec_sn);
            $array_rete[1] = array($codigo_ret_iva_b, $base_iva_b, $porc_ret_iva_b, $val_iva_b, $num_rete, $serie_rete, $tret_cta_crei, $ret_elec_sn);

            $array_rete[2] = array($codigo_ret_fue_bser, $base_fue_bser, $porc_ret_fue_bser, $val_fue_bser, $num_rete, $serie_rete, $tret_cta_crebser, $ret_elec_sn);
            $array_rete[3] = array($codigo_ret_iva_bser, $base_iva_bser, $porc_ret_iva_bser, $val_iva_bser, $num_rete, $serie_rete, $tret_cta_creiser, $ret_elec_sn);
        }

        // COMPENSACION DEL IVA
        $comp2iva  = 0;
        $comp3iva   = 0;
        $comp4iva  = 0;
        $comp_tot_iva = 0;

        // C O D I G O     D E L     E M P L E A D O     I N F O R M I X
        $sql = "SELECT usua_cod_empl, usua_nom_usua FROM SAEUSUA WHERE USUA_COD_USUA = $usuario_informix ";
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                $empleado       = $oIfx->f('usua_cod_empl');
                $usua_nom_usua  = $oIfx->f('usua_nom_usua');
            }
        }
        $oIfx->Free();

        //  EJERCICIO SAEFPRV
        list($anio_fac, $m1, $d1) = explode('-', $fecha_contable_fact);
        $fecha_ejer = $anio_fac . '-12-31';
        $sql = "select ejer_cod_ejer from saeejer where ejer_fec_finl = '$fecha_ejer' and ejer_cod_empr = $idempresa ";
        $idejer_fact = consulta_string_func($sql, 'ejer_cod_ejer', $oIfx, 1);

        //$fecha_emis     = $m1 . '/' . $d1 . '/' . $anio_fac;
        $fecha_emis    = $fecha_emision_fact;
        $fecha_contable = $fecha_contable_fact;
        $fecha_vence    = $fecha_contable_fact;
        $fecha_mysql    = $fecha_emision_fact;

        $idprdo_cont = $m1;
        $idejer_cont = $idejer_fact;
        $tipo_factura = '1';
        $plazo       = 0;
        $fecha_validez = $fecha_contable_fact;

        // PROVEEDOR
        $sql = "select  clpv_cod_tprov, clv_con_clpv, clpv_cod_paisp, clpv_cod_cuen
                    from saeclpv where
                    clpv_cod_clpv = '$cod_prove' and
                    clpv_cod_empr = $idempresa and
                    clpv_clopv_clpv = 'PV' ";
        $prove_tprov = '';
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                $prove_tprov   = $oIfx->f('clpv_cod_tprov');
                $clv_con_clpv  = $oIfx->f('clv_con_clpv');
                $clpv_cod_pais = $oIfx->f('clpv_cod_paisp');
                $clpv_cod_cuen = $oIfx->f('clpv_cod_cuen');
            }
        }
        $oIfx->Free();

        //registra Log de transaccion
        $Logs = new Logs(4);

        $class = new mayorizacion_class();
        unset($array);
        $array = $class->secu_asto($oIfx, $idempresa, $idsucursal, 4, $fecha_contable, $usuario_informix, $tipo_doc);
        foreach ($array as $val) {
            $secu_asto  = $val[0];
            $secu_dia   = $val[1];
            $asto_cod   = $val[0];
            $comp_cod   = $val[1];
            $tidu       = $val[2];
            $idejer     = $val[3];
            $idprdo     = $val[4];
            $moneda     = $val[5];
            $tcambio    = $val[6];
            $empleado   = $val[7];
            $usua_nom   = $val[8];
        } // fin foreach   




        $Logs->crearLog($factura, $secu_asto, "Asto: $secu_asto, Tidu: $tidu, Comprobante: $comp_cod");


        $fprv_clav_sri = '';
        // SAEFPRV





        if (empty($porc_ret_iva_s)) {
            $porc_ret_iva_s = 0;
        }
        if (empty($porc_ret_iva_b)) {
            $porc_ret_iva_b = 0;
        }
        if (empty($porc_ret_fue_b)) {
            $porc_ret_fue_b = 0;
        }

        if (empty($porc_ret_fue_bser)) {
            $porc_ret_fue_bser = 0;
        }

        if (empty($val_fue_b)) {
            $val_fue_b = 0;
        }
        if (empty($val_fue_s)) {
            $val_fue_s = 0;
        }

        if (empty($fprv_val_bas1)) {
            $fprv_val_bas1 = 0;
        }
        if (empty($fprv_val_bas2)) {
            $fprv_val_bas2 = 0;
        }
        if (empty($fprv_val_bas3)) {
            $fprv_val_bas3 = 0;
        }
        if (empty($fprv_val_bas4)) {
            $fprv_val_bas4 = 0;
        }
        if (empty($fprv_val_gas1)) {
            $fprv_val_gas1 = 0;
        }
        if (empty($fprv_val_gas2)) {
            $fprv_val_gas2 = 0;
        }


        if (empty($base_fue_s)) {
            $base_fue_s = 0;
        }
        if (empty($base_iva_b)) {
            $base_iva_b = 0;
        }
        if (empty($base_fue_b)) {
            $base_fue_b = 0;
        }
        if (empty($base_iva_s)) {
            $base_iva_s = 0;
        }
        if (empty($cad_rete)) {
            $cad_rete = 'NULL';
        } else {
            $cad_rete = "'" . $cad_rete . "'";
        }
        if (empty($proyecto)) {
            $proyecto = 0;
        }
        if (empty($actividad)) {
            $actividad = 0;
        }


        if (empty($porc_ret_iva_bser)) {
            $porc_ret_iva_bser = 0;
        }

        if (empty($porc_ret_iva_b)) {
            $porc_ret_iva_b = 0;
        }

        if ($actualizar_secuencial == 'N') {
            $fprv_aprob_sri = 'S';
            $auto_rete = '999999999';
        }

        $sql = "insert into saefprv (fprv_cod_empr, fprv_cod_sucu, fprv_cod_ejer,  fprv_cod_tran,
                                    fprv_cod_clpv, fprv_num_fact, fprv_fec_emis,  fprv_num_dias,
                                    fprv_cod_rtf1 ,fprv_cod_rtf2, fprv_cod_riva1, fprv_cod_riva2,
                                    fprv_val_grab ,fprv_val_gra0, fprv_val_piva,  fprv_val_viva,
                                    fprv_val_vice, fprv_val_totl, fprv_por_ret1,  fprv_por_ret2,
                                    fprv_val_ret1, fprv_val_ret2, fprv_por_iva1,  fprv_por_iva2,
                                    fprv_val_iva1, fprv_val_iva2, fprv_num_auto,  fprv_num_seri,
                                    fprv_fec_vali, fprv_cre_fisc, fprv_cta_cfis,  fprv_cta_gast,
                                    fprv_num_rete, fprv_det_fprv, 
                                    fprv_val_grbs,
                                    fprv_val_gr0s ,fprv_val_ices ,fprv_cta_fiss , fprv_cta_gst1 ,
                                    fprv_val_bas1 ,fprv_val_bas2 , fprv_val_bas3 ,
                                    fprv_val_bas4 ,fprv_val_gas1 ,fprv_val_gas2 , fprv_cod_pafa ,
                                    fprv_cod_tidu ,fprv_mnt_noi , fprv_est_rise ,
                                    fprv_emp_soli ,fprv_emp_aut , fprv_cod_mone , fprv_cot_fprv ,
                                    fprv_cod_libro ,fprv_ruc_prov ,fprv_cod_pcch ,fprv_sec_caja ,
                                    fprv_ser_rete ,fprv_aut_rete ,fprv_fec_rete , fprv_fec_regc ,
                                    fprv_cod_fpagop ,	fprv_cod_tprov ,
                                    fprv_cod_tpago ,   fprv_cod_paisp ,    fprv_ani_fprv ,  fprv_mes_fprv ,
                                    fprv_apl_conv ,    fprv_pag_exte,      fprv_email_clpv,
                                    fprv_nom_clpv,     fprv_dir_clpv,      fprv_tel_clpv,
                                    fprv_cod_ccs1,     fprv_cod_ccs2,      fprv_val_noi,
                                    fprv_val_exe,      fprv_reg_fis ,      fprv_cod_asto, fprv_num_mayo,
                                    fprv_comp2_iva ,   fprv_comp3_iva ,    fprv_comp4_iva ,
                                    fprv_tot_compiva , fprv_cod_ftrn ,		fprv_aprob_sri,
                                    fprv_auto_sri,		fprv_cod_proy, 		fprv_cod_actv,
                                    fprv_clav_sri,      fprv_fech_sri,      fprv_rete_fec )
                                values($idempresa,              $idsucursal,            $idejer_fact,           '$comprobante', 
                                        $cod_prove,             '$factura',             '$fecha_emis',          $plazo,
                                        '$codigo_ret_fue_b',    '$codigo_ret_fue_bser', '$codigo_ret_iva_b',    '$codigo_ret_iva_bser',
                                        $valor_grab12b,          $valor_grab0b,         $ivabp,                 $ivat,
                                        $iceb,                   $totals,               $porc_ret_fue_b,      $porc_ret_fue_bser,
                                        $val_fue_b,              $val_fue_bser,          $porc_ret_iva_b,      $porc_ret_iva_bser,
                                        $val_iva_b,              $val_iva_s,            '$clave_acceso',          '$serie',
                                        '$fecha_validez',       '$sust_trib',           '$fisc_b',              '$gasto1',
                                        '$num_rete',            '$detalle' ,  
                                        '$valor_grab12s',
                                        '$valor_grab0s',        '$ices',                '$fisc_s',              '$gasto2' ,
                                        '$base_fue_b',          '$base_fue_s',          '$base_iva_b' ,
                                        '$base_iva_s',          '$val_gasto1',          '$val_gasto2' ,         '$plan_ser' ,
                                        '$tipo_doc',            '$no_obj_iva',          '$rise',
                                        '$empl_soli',           '$empl_auto' ,          $moneda,                $coti,
                                        '0',                     '$ruc',                 '',                     '' ,
                                        '$serie_rete',          '$auto_rete',           $cad_rete,            '$fecha_contable_fact',
                                        '$forma_pago',          '$prove_tprov',
                                        '$tipo_pago',           '$clpv_cod_pais',       $anio_fac,              $m1,
                                        '$doble_tributac',      '$pago_sujeto_ret',    '$fprv_email_clpv',
                                        '$cliente_nombre',      '$direccion',           '$telefono',
                                        '$ccosn_3',             '$ccosn_4',             '$valor_noObjIva',
                                        '$valor_exentoIva',      '$regimen_fiscal',     '$secu_asto',           '$comp_cod',
                                        '$comp2iva',             '$comp3iva',           '$comp4iva',
                                        '$comp_tot_iva',        '$formato' ,	        '$fprv_aprob_sri',
                                        '$fprv_auto_sri',	    '$proyecto',            '$actividad',
                                        '$fprv_clav_sri'  ,     '$fprv_fech_sri',       '$fecha_retencion_fact' ); ";
        $oIfx->QueryT($sql);

        $Logs->crearLog($factura, $secu_asto, $sql);

        $sql = "select trans_tip_comp from saetran where
                    tran_cod_empr = $idempresa  and
                    tran_cod_modu = 4 and
                    tran_cod_tran =  '$comprobante'  ";
        $tipo_comprob = consulta_string_func($sql, 'trans_tip_comp', $oIfx, '');
        $serie_tmp = substr($serie, 0, 3);
        $estab_tmp = substr($serie, 3, 6);

        $basenograiva = 0;
        $baseimponible = $valor_grab0t;

        $basenograiva     = number_format($basenograiva, 2, '.', '');
        $baseimponible     = number_format($baseimponible, 2, '.', '');
        $baseimpgrav     = number_format($valor_grab12t, 2, '.', '');
        $montoice         = number_format($icet, 2, '.', '');
        $montoiva         = number_format($ivat, 2, '.', '');

        $serie_rete_tmp = substr($serie_rete, 0, 3);
        $estab_rete_tmp = substr($serie_rete, 3, 6);

        // SAEASTO
        $sql = $class->saeasto(
            $oIfx,
            $secu_asto,
            $idempresa,
            $idsucursal,
            $idejer,
            $idprdo,
            $moneda,
            $usuario_informix,
            $comprobante,
            $cliente_nombre,
            $total,
            $fecha_contable,
            $detalle,
            $secu_dia,
            $fecha_contable,
            $tidu,
            $usua_nom_usua,
            $user_web,
            4,
            $formato
        );

        $Logs->crearLog($factura, $secu_asto, $sql);

        $val_fue_b = vacios($val_fue_b, 0);
        $val_fue_s = vacios($val_fue_s, 0);
        $val_iva_b = vacios($val_iva_b, 0);
        $val_iva_s = vacios($val_iva_s, 0);

        // DIRECTORIO
        $cod_dir = 1;
        $debito     = 0;
        $credito        = $total - $val_fue_b - $val_fue_s - $val_iva_b - $val_iva_s - $val_fue_bser - $val_iva_bser;
        $debito_ext = 0;
        $credito_ext    = round(($credito / $coti), 2);
        $fact_tmp = $serie . '-' . $factura . '-001';
        $sql = $class->saedir(
            $oIfx,
            $idempresa,
            $idsucursal,
            $idprdo,
            $idejer,
            $secu_asto,
            $cod_prove,
            4,
            $comprobante,
            $fact_tmp,
            $fecha_vence,
            $detalle,
            $debito,
            $credito,
            $debito_ext,
            $credito_ext,
            'DB',
            '',
            '',
            '',
            '',
            '',
            '',
            $user_web,
            $cod_dir,
            $coti,
            $cliente_nombre,
            $ccli_cod
        );
        $Logs->crearLog($factura, $secu_asto, $sql);

        // CTA PROVEEDOR DASI

        $sql_cuenta_ad = "SELECT cuen_nom_cuen from saecuen where cuen_cod_empr = $idempresa and cuen_cod_cuen = '$clpv_cod_cuen' ";
        $cuen_nom_ad = consulta_string_func($sql_cuenta_ad, 'cuen_nom_cuen', $oIfx, '');
        if (empty($cuen_nom_ad)) {
            throw new Exception("No existe cuenta contable de proveedor $clpv_cod_cuen");
        }

        $sql = $class->saedasi(
            $oIfx,
            $idempresa,
            $idsucursal,
            $clpv_cod_cuen,
            $idprdo,
            $idejer,
            $ccosn_cod,
            $debito,
            $credito,
            $debito_ext,
            $credito_ext,
            $coti,
            $detalle,
            '',
            '',
            $user_web,
            $secu_asto,
            '',
            $cod_dir,
            '',
            $opBand,
            $opBacn,
            $opFlch,
            '',
            $act_cod
        );
        $Logs->crearLog($factura, $secu_asto, $sql);

        // RETENCION
        if (count($array_rete) > 0) {
            $ret_secu = 1;
            foreach ($array_rete as $val) {
                $codigo_ret = $val[0];
                $ret_base   = $val[1];
                $ret_porc   = $val[2];
                $ret_val    = $val[3];
                $num_rete   = $val[4];
                $serie_rete = $val[5];
                $cta_ret    = $val[6];
                $ret_elec_sn = $val[7];
                $debito     = 0;
                $credito    = $ret_val;
                $debito_ext = 0;
                $credito_ext = round(($ret_val / $coti), 2);

                if (!empty($codigo_ret)) {
                    $ret_det    = 'RETENCION ' . $serie_rete . ' - ' . $num_rete . ' FC: ' . $serie . '-' . $factura;

                    if ($actualizar_secuencial == 'N') {
                        $num_rete = 000000000;
                    }

                    $sql2 = $class->saeret(
                        $oIfx,
                        $idempresa,
                        $idsucursal,
                        $idprdo,
                        $idejer,
                        $secu_asto,
                        $cod_prove,
                        $cliente_nombre,
                        $direccion,
                        '',
                        $ruc,
                        $ret_secu,
                        $codigo_ret,
                        $ret_porc,
                        $ret_base,
                        $ret_val,
                        $num_rete,
                        $ret_det,
                        $debito,
                        $credito,
                        $debito_ext,
                        $credito_ext,
                        $factura,
                        $serie_rete,
                        '',
                        '',
                        $fprv_email_clpv,
                        $ret_elec_sn,
                        $coti
                    );
                    $Logs->crearLog($factura, $secu_asto, $sql2);


                    $sql_cuenta_ad = "SELECT cuen_nom_cuen from saecuen where cuen_cod_empr = $idempresa and cuen_cod_cuen = '$cta_ret' ";
                    $cuen_nom_ad = consulta_string_func($sql_cuenta_ad, 'cuen_nom_cuen', $oIfx, '');
                    if (empty($cuen_nom_ad)) {
                        throw new Exception("No existe cuenta contable de retencion $cta_ret");
                    }

                    $sql = $class->saedasi(
                        $oIfx,
                        $idempresa,
                        $idsucursal,
                        $cta_ret,
                        $idprdo,
                        $idejer,
                        $ccosn_cod,
                        $debito,
                        $credito,
                        $debito_ext,
                        $credito_ext,
                        $coti,
                        $detalle,
                        '',
                        '',
                        $user_web,
                        $secu_asto,
                        '',
                        $cod_dir,
                        '',
                        $opBand,
                        $opBacn,
                        $opFlch,
                        '',
                        $act_cod
                    );
                    $Logs->crearLog($factura, $secu_asto, $sql);

                    $ret_secu++;
                }
            }
        }


        // CTA GASTO DASI
        if (count($array_cuen) > 0) {
            // DISTRIBUIR
            $valor_distr  = $val_gasto1 + $excento_impuesto_ad;
            $tot_dist = 0;
            foreach ($array_cuen as $val) {
                $porcentaje     = $val[2];
                $valor_tmp      = 0;
                $valor_tmp      = (($valor_distr * $porcentaje) / 100);
                $tot_dist       += $valor_tmp;
            }
            $dif = 0;
            $dif = round(($valor_distr - round($tot_dist, 2)), 2);
            $rd  = 1;
            foreach ($array_cuen as $val) {
                $cuenta         = $val[0];
                $centro_costos  = $val[1];
                $porcentaje     = $val[2];
                $valor_tmp      = 0;
                if ($rd == 1) {
                    $valor_tmp      = round((($valor_distr * $porcentaje) / 100), 2) + $dif;
                } else {
                    $valor_tmp      = round((($valor_distr * $porcentaje) / 100), 2);
                }

                $credito = 0;
                $credito_ext = 0;
                $debito  = $valor_tmp;
                $debito_ext    = round(($debito / $coti), 2);


                $sql_cuenta_ad = "SELECT cuen_nom_cuen from saecuen where cuen_cod_empr = $idempresa and cuen_cod_cuen = '$cuenta' ";
                $cuen_nom_ad = consulta_string_func($sql_cuenta_ad, 'cuen_nom_cuen', $oIfx, '');
                if (empty($cuen_nom_ad)) {
                    throw new Exception("No existe cuenta contable de gasto $cuenta");
                }

                $sql = $class->saedasi(
                    $oIfx,
                    $idempresa,
                    $idsucursal,
                    $cuenta,
                    $idprdo,
                    $idejer,
                    $centro_costos,
                    $debito,
                    $credito,
                    $debito_ext,
                    $credito_ext,
                    $coti,
                    $detalle,
                    '',
                    '',
                    $user_web,
                    $secu_asto,
                    '',
                    0,
                    '',
                    $opBand,
                    $opBacn,
                    $opFlch,
                    '',
                    $act_cod
                );
                $Logs->crearLog($factura, $secu_asto, $sql);

                $rd++;
            }
        } else {
            // UNA SOLA CUENTA
            $credito = 0;
            $credito_ext = 0;
            $debito  = $val_gasto1 + $excento_impuesto_ad;
            $debito_ext    = round(($debito / $coti), 2);

            $sql_cuenta_ad = "SELECT cuen_nom_cuen from saecuen where cuen_cod_empr = $idempresa and cuen_cod_cuen = '$gasto1' ";
            $cuen_nom_ad = consulta_string_func($sql_cuenta_ad, 'cuen_nom_cuen', $oIfx, '');
            if (empty($cuen_nom_ad)) {
                throw new Exception("No existe cuenta contable de gasto $gasto1");
            }

            $sql = $class->saedasi(
                $oIfx,
                $idempresa,
                $idsucursal,
                $gasto1,
                $idprdo,
                $idejer,
                $ccosn_cod,
                $debito,
                $credito,
                $debito_ext,
                $credito_ext,
                $coti,
                $detalle,
                '',
                '',
                $user_web,
                $secu_asto,
                '',
                0,
                '',
                $opBand,
                $opBacn,
                $opFlch,
                '',
                $act_cod
            );
            $Logs->crearLog($factura, $secu_asto, $sql);
        }

        // CTA IMPUESTO DASI
        $credito = 0;
        $credito_ext = 0;
        $debito  = $ivat;
        $debito_ext    = round(($debito / $coti), 2);


        // VALIDAMOS QUE LA CUENTA DE IVA NO ESTE INGRESADA, QUE LE DEBITO DE ESE VALOR SEA 0 Y EL CREDITO SEA 0
        if (empty($fisc_b) && floatval($debito) == 0  && floatval($credito) == 0) {
        } else {
            $sql_cuenta_ad = "SELECT cuen_nom_cuen from saecuen where cuen_cod_empr = $idempresa and cuen_cod_cuen = '$fisc_b' ";
            $cuen_nom_ad = consulta_string_func($sql_cuenta_ad, 'cuen_nom_cuen', $oIfx, '');
            if (empty($cuen_nom_ad)) {
                throw new Exception("No existe cuenta contable de impuesto $fisc_b");
            }

            $sql = $class->saedasi(
                $oIfx,
                $idempresa,
                $idsucursal,
                $fisc_b,
                $idprdo,
                $idejer,
                $ccosn_cod,
                $debito,
                $credito,
                $debito_ext,
                $credito_ext,
                $coti,
                $detalle,
                '',
                '',
                $user_web,
                $secu_asto,
                '',
                0,
                '',
                $opBand,
                $opBacn,
                $opFlch,
                '',
                $act_cod
            );
            $Logs->crearLog($factura, $secu_asto, $sql);
        }

        // ACTUALIZACION SAEASTO
        $sql = "update saeasto set asto_est_asto = 'MY', 
                    asto_vat_asto = $total  where
                    asto_cod_empr = $idempresa  and
                    asto_cod_sucu = $idsucursal and
                    asto_cod_asto = '$secu_asto' and
                    asto_cod_ejer = $idejer and
                    asto_num_prdo = $idprdo and
                    asto_cod_empr = $idempresa and     
                    asto_cod_sucu = $idsucursal and
                    asto_user_web = $user_web ";
        $oIfx->QueryT($sql);
        $Logs->crearLog($factura, $secu_asto, $sql);

        $sql = "insert into comercial.cxp_compra ( empr_cod_empr,     sucu_cod_sucu,      factura_serie,      factura_num,
                                         clave_acceso,      factura_fecha,      asto_cod_asto,      asto_cod_ejer,
                                         asto_num_prdo,     usuario_id,         fecha_server,       clpv_cod_clpv,
                                         clpv_nom_clpv,     clpv_ruc_clpv,      factura_tributario
                                        ) 
                                 values( $idempresa,        $idsucursal,       '$serie',            '$factura',
                                         '$clave_acceso',   '$fecha_mysql',    '$secu_asto',         $idejer,
                                         $idprdo,           $user_web,       now(),               $cod_prove,
                                         '$cliente_nombre', '$ruc' ,           '$trib_ser'
                                       )";
        $oCon->QueryT($sql);


        if ($actualizar_secuencial == 'N') {
            $num_rete = 000000000;
        }

        $msj = "Factura: $factura, Retencion: $num_rete, Diario: $secu_asto";

        $oReturn->script("Swal.fire({
                            type: 'success',
                            width: '40%',
                            title: 'Factura - Gasto Ingresada Correctamente ',
                            text: '$msj'
                        })");

        $oIfx->QueryT('COMMIT WORK;');
        $oCon->QueryT('COMMIT');

        $oReturn->script("$('#ModalGen').modal('hide');");
        $oReturn->assign("asto_cuadre", "value", '');
        $oReturn->script('consultar();');
    } catch (Exception $e) {
        // rollback
        $oIfx->QueryT('ROLLBACK WORK;');
        $oCon->QueryT('ROLLBACK');
        $oReturn->alert($e->getMessage());
    }

    $oReturn->script("jsRemoveWindowLoad();");
    return $oReturn;
}


// GUARDAR CAJA CHICA
function guardar_caja_chica(
    $aForm = '',
    $tipo_compr = '',
    $factura = '',
    $ruc = '',
    $cliente_nombre = '',
    $fecha_emi = '',
    $fecha_auto = '',
    $tipo_emision = '',
    $ruc_recep = '',
    $clave_acceso = '',
    $y = '',
    $cod_prove = ''
) {
    //Definiciones
    global $DSN, $DSN_Ifx;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oReturn = new xajaxResponse();

    //      VARIABLES
    $idempresa   = $aForm['empresa'];
    $idsucursal  = $aForm['sucursal'];
    $user_web    = $_SESSION['U_ID'];
    $serie       = $aForm['serie'];
    $factura     = $aForm['factura'];
    $base12      = $aForm['base12'];
    $base0       = $aForm['base0'];
    $iva         = $aForm['iva'];
    $total       = $aForm['total'];
    $sust_trib   = $aForm['sustento'];
    $comprobante = $aForm['comprobante'];
    $tipo_doc    = $aForm['tidu'];
    $usuario_informix = $_SESSION['U_USER_INFORMIX'];
    $fecha_server   = date("Y-m-d");
    $hora           = date("Y-m-d H:i:s");
    $fechaServer    = date("Y-m-d H:i:s");

    $tipo_fact  = $aForm['su.' . $y];
    $rete_ser   = $aForm['re.' . $y];
    $tipo_ser   = $aForm['ti.' . $y];
    $plan_ser   = $aForm['pl.' . $y];
    $trib_ser   = 'S';



    try {
        // commit
        $oIfx->QueryT('BEGIN WORK;');
        $oCon->QueryT('BEGIN');

        $fprv_resp  = $aForm['fprv_resp'];
        $fprv_caja  = $aForm['fprv_caja'];
        $sql = "select empl_ape_nomb from saeempl where empl_cod_empr = $idempresa and empl_cod_empl = '$fprv_resp'  ";
        $fprv_nom = consulta_string_func($sql, 'empl_ape_nomb', $oIfx, '');

        $sql = "select pcon_mon_base , pcon_seg_mone from saepcon where pcon_cod_empr = $idempresa  ";
        $moneda = consulta_string_func($sql, 'pcon_mon_base', $oIfx, '1');
        $mone_extr = consulta_string_func($sql, 'pcon_seg_mone', $oIfx, '');

        $sql = "select tcam_valc_tcam from saetcam where
				mone_cod_empr = $idempresa and
				tcam_cod_mone = $mone_extr and
				tcam_fec_tcam in (select max(tcam_fec_tcam)  from saetcam where
											mone_cod_empr = $idempresa and
											tcam_cod_mone = $mone_extr)";
        $coti = consulta_string_func($sql, 'tcam_valc_tcam', $oIfx, 0);

        if ($tipo_ser == 1) {
            // PLANILLA GENERAL
            $sql = "SELECT p.cuenta_aplicada, p.credito_bienes, p.credito_servicios,  detalle
                            from comercial.plantilla_clpv p WHERE 
                            id_clpv      in ('0') AND 
                            id_empresa   = $idempresa AND
                            id           = '$plan_ser' order by 2 ";
            if ($oCon->Query($sql)) {
                if ($oCon->NumFilas() > 0) {
                    $detalle = $oCon->f('detalle') . ' FC: ' . $serie . '-' . $factura;
                    $gasto1  = $oCon->f('cuenta_aplicada');
                    $fisc_b  = $oCon->f('credito_bienes');
                    if (empty($fisc_b)) {
                        $fisc_b  = $oCon->f('credito_servicios');
                    }
                }
            }
            $oCon->Free();

            $sql = "SELECT c.cuenta, c.centro_costos, c.porcentaje
                            FROM comercial.plantilla_ccosn c WHERE
                            c.id_empresa    = $idempresa AND
                            c.id_clpv       = 0 AND
                            c.id_plantilla  = '$plan_ser' ";
            unset($array_cuen);
            if ($oCon->Query($sql)) {
                if ($oCon->NumFilas() > 0) {
                    do {
                        $cuenta         = $oCon->f('cuenta');
                        $centro_costos  = $oCon->f('centro_costos');
                        $porcentaje     = $oCon->f('porcentaje');
                        $array_cuen[]  = array($cuenta, $centro_costos, $porcentaje);
                    } while ($oCon->SiguienteRegistro());
                }
            }
            $oCon->Free();
        } else {
            // PLANILLA CLIENTE
            $sql = "SELECT p.cuenta_aplicada, p.credito_bienes, p.credito_servicios,  detalle
                            from comercial.plantilla_clpv p WHERE 
                            id_clpv      in ('$cod_prove') AND 
                            id_empresa   = $idempresa AND
                            id           = '$plan_ser' order by 2 ";
            if ($oCon->Query($sql)) {
                if ($oCon->NumFilas() > 0) {
                    $detalle = $oCon->f('detalle') . ' FC: ' . $serie . '-' . $factura;
                    $gasto1  = $oCon->f('cuenta_aplicada');
                    $fisc_b  = $oCon->f('credito_bienes');
                    if (empty($fisc_b)) {
                        $fisc_b  = $oCon->f('credito_servicios');
                    }
                }
            }
            $oCon->Free();

            $sql = "SELECT c.cuenta, c.centro_costos, c.porcentaje
                            FROM comercial.plantilla_ccosn c WHERE
                            c.id_empresa    = $idempresa AND
                            c.id_clpv       = $cod_prove AND
                            c.id_plantilla  = '$plan_ser' ";
            unset($array_cuen);
            if ($oCon->Query($sql)) {
                if ($oCon->NumFilas() > 0) {
                    do {
                        $cuenta         = $oCon->f('cuenta');
                        $centro_costos  = $oCon->f('centro_costos');
                        $porcentaje     = $oCon->f('porcentaje');
                        $array_cuen[]  = array($cuenta, $centro_costos, $porcentaje);
                    } while ($oCon->SiguienteRegistro());
                }
            }
            $oCon->Free();
        }


        // direccion
        $sql = "select min( dire_dir_dire ) as dire  from saedire where
                    dire_cod_empr = $idempresa and
                    dire_cod_clpv = $cod_prove ";
        $direccion = consulta_string_func($sql, 'dire', $oIfx, '');

        // email
        $sql = "select min( emai_ema_emai ) as ema  from saeemai where
                emai_cod_empr = $idempresa and
                emai_cod_clpv = $cod_prove";
        $fprv_email_clpv = consulta_string_func($sql, 'ema', $oIfx, '');

        $sql = "select empr_iva_empr from saeempr where empr_cod_empr = $idempresa ";
        $empr_iva_empr = consulta_string_func($sql, 'empr_iva_empr', $oIfx, '0');

        $rise       = 'N';
        $doble_trib = 'N';
        $suje_rete  = 'N';

        // BIENES
        $valor_grab12b  = $base12;
        $valor_grab0b   = $base0;
        $iceb           = 0;
        $ivab           = $iva;
        $ivabp          = $empr_iva_empr;
        $val_gasto1     = $base12 + $base0;
        $val_gasto2     = 0;

        // SERVICIOS
        $valor_grab12s  = 0;
        $valor_grab0s   = 0;
        $ices           = 0;
        $ivas           = 0;
        $totals         = 0;

        // TOTAL
        $valor_grab12t  = $base12;
        $valor_grab0t   = $base0;
        $icet           = 0;
        $ivat           = $iva;

        //no objeto y excento de iva
        $valor_noObjIva = 0;
        $valor_exentoIva = 0;
        $no_obj_iva     = '';

        $sql = "select ftrn_cod_ftrn, ftrn_des_ftrn 
                    from saeftrn where
                    ftrn_cod_empr = $idempresa and
                    ftrn_cod_modu = 4 and
                    ftrn_tip_movi = 'DI' ";
        $formato = consulta_string_func($sql, 'ftrn_cod_ftrn', $oIfx, '0');

        $serie_rete  = '';
        $auto_rete      = '';
        $cad_rete      = '';
        $electronica = 'N';
        $num_rete    = '';
        unset($array_rete);
        $val_fue_b = 0;
        $val_iva_b = 0;
        if ($rete_ser == 1) {
            // RETENCION FUENTE            
            $codigo_ret_fue_b    = $aForm['retcodif'];
            $base_fue_b          = $valor_grab12t + $valor_grab0t;
            $sql = "select tret_porct , tret_cta_cre from saetret where tret_cod_empr = $idempresa and tret_cod  = '$codigo_ret_fue_b' ";
            $porc_ret_fue_b      = consulta_string_func($sql, 'tret_porct', $oIfx, '0');
            $tret_cta_creb       = consulta_string_func($sql, 'tret_cta_cre', $oIfx, '0');
            $val_fue_b           = $aForm['retfuente'];

            $codigo_ret_iva_b    = $aForm['retcodii'];
            $base_iva_b         =  $iva;
            $sql = "select tret_porct, tret_cta_cre from saetret where tret_cod_empr = $idempresa and tret_cod  = '$codigo_ret_iva_b' ";
            $porc_ret_iva_b      = consulta_string_func($sql, 'tret_porct', $oIfx, '0');
            $tret_cta_crei       = consulta_string_func($sql, 'tret_cta_cre', $oIfx, '0');
            $val_iva_b           = $aForm['retiva'];

            $sql = "select retp_sec_retp , retp_num_seri from saeretp where 
                        retp_cod_empr = $idempresa and
                        retp_cod_sucu = $idsucursal and
                        retp_act_retp = '1' and
                        retp_elec_sn = 'S'";
            $num_rete     = consulta_string_func($sql, 'retp_sec_retp', $oIfx, '');
            $serie_rete   = consulta_string_func($sql, 'retp_num_seri', $oIfx, '');
            $num_rete     = secuencial(2, '', $num_rete, 9);

            $array_rete[0] = array($codigo_ret_fue_b, $base_fue_b, $porc_ret_fue_b, $val_fue_b, $num_rete, $serie_rete, $tret_cta_creb);
            $array_rete[1] = array($codigo_ret_iva_b, $base_iva_b, $porc_ret_iva_b, $val_iva_b, $num_rete, $serie_rete, $tret_cta_crei);

            $num_rete = $num_rete * 1;
            $sql = "update saeretp set retp_sec_retp = ($num_rete ) 
                            where retp_cod_empr = $idempresa and
                            retp_cod_sucu = $idsucursal and
                            retp_act_retp = 1 and
                            retp_elec_sn = 'S' ";
            $oIfx->QueryT($sql);
        }

        // COMPENSACION DEL IVA
        $comp2iva  = 0;
        $comp3iva   = 0;
        $comp4iva  = 0;
        $comp_tot_iva = 0;

        // C O D I G O     D E L     E M P L E A D O     I N F O R M I X
        $sql = "SELECT usua_cod_empl, usua_nom_usua FROM SAEUSUA WHERE USUA_COD_USUA = $usuario_informix ";
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                $empleado       = $oIfx->f('usua_cod_empl');
                $usua_nom_usua  = $oIfx->f('usua_nom_usua');
            }
        }
        $oIfx->Free();

        //  EJERCICIO SAEFPRV
        list($d1, $m1, $anio_fac) = explode('/', $fecha_emi);
        $fecha_ejer = $anio_fac . '-12-31';
        $sql = "select ejer_cod_ejer from saeejer where ejer_fec_finl = '$fecha_ejer' and ejer_cod_empr = $idempresa ";
        $idejer_fact = consulta_string_func($sql, 'ejer_cod_ejer', $oIfx, 1);

        $fecha_emis     = $m1 . '/' . $d1 . '/' . $anio_fac;
        $fecha_contable = $fecha_emis;
        $fecha_vence    = $fecha_emis;
        $fecha_mysql    = $anio_fac . '-' . $m1 . '-' . $d1;

        $idprdo_cont = $m1;
        $idejer_cont = $idejer_fact;
        $tipo_factura = '1';
        $plazo       = 0;
        $fecha_validez = $fecha_emis;

        // PROVEEDOR
        $sql = "select  clpv_cod_tprov, clv_con_clpv, clpv_cod_paisp, clpv_cod_cuen
                    from saeclpv where
                    clpv_cod_clpv = '$cod_prove' and
                    clpv_cod_empr = $idempresa and
                    clpv_clopv_clpv = 'PV' ";
        $prove_tprov = '';
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                $prove_tprov   = $oIfx->f('clpv_cod_tprov');
                $clv_con_clpv  = $oIfx->f('clv_con_clpv');
                $clpv_cod_pais = $oIfx->f('clpv_cod_paisp');
                $clpv_cod_cuen = $oIfx->f('clpv_cod_cuen');
            }
        }
        $oIfx->Free();

        //registra Log de transaccion
        $Logs = new Logs(4);

        $fprv_aprob_sri = 'N';
        $fprv_auto_sri = $clave_acceso;

        $codigo_ret_fue_s = '';
        $val_fue_s = 0;
        $porc_ret_iva_s = 0;
        $val_iva_s = 0;
        $gasto2 = 0;
        $base_iva_s = 0;
        $codigo_ret_iva_s = '';
        $porc_ret_fue_s = 0;
        $base_fue_s = 0;
        $fprv_clav_sri = '';
        // SAEFPRV
        $sql = "insert into saefprv (fprv_cod_empr, fprv_cod_sucu, fprv_cod_ejer,  fprv_cod_tran,
                                    fprv_cod_clpv, fprv_num_fact, fprv_fec_emis,  fprv_num_dias,
                                    fprv_cod_rtf1 ,fprv_cod_rtf2, fprv_cod_riva1, fprv_cod_riva2,
                                    fprv_val_grab ,fprv_val_gra0, fprv_val_piva,  fprv_val_viva,
                                    fprv_val_vice, fprv_val_totl, fprv_por_ret1,  fprv_por_ret2,
                                    fprv_val_ret1, fprv_val_ret2, fprv_por_iva1,  fprv_por_iva2,
                                    fprv_val_iva1, fprv_val_iva2, fprv_num_auto,  fprv_num_seri,
                                    fprv_fec_vali, fprv_cre_fisc, fprv_cta_cfis,  fprv_cta_gast,
                                    fprv_num_rete, fprv_det_fprv, 
                                    fprv_val_grbs,
                                    fprv_val_gr0s ,fprv_val_ices ,fprv_cta_fiss , fprv_cta_gst1 ,
                                    fprv_val_bas1 ,fprv_val_bas2 , fprv_val_bas3 ,
                                    fprv_val_bas4 ,fprv_val_gas1 ,fprv_val_gas2 , fprv_cod_pafa ,
                                    fprv_cod_tidu ,fprv_mnt_noi , fprv_est_rise ,
                                    fprv_emp_soli ,fprv_emp_aut , fprv_cod_mone , fprv_cot_fprv ,
                                    fprv_cod_libro ,fprv_ruc_prov ,fprv_cod_pcch ,fprv_sec_caja ,
                                    fprv_ser_rete ,fprv_aut_rete ,fprv_fec_rete , fprv_fec_regc ,
                                    fprv_cod_fpagop ,	fprv_cod_tprov ,
                                    fprv_cod_tpago ,   fprv_cod_paisp ,    fprv_ani_fprv ,  fprv_mes_fprv ,
                                    fprv_apl_conv ,    fprv_pag_exte,      fprv_email_clpv,
                                    fprv_nom_clpv,     fprv_dir_clpv,      fprv_tel_clpv,
                                    fprv_cod_ccs1,     fprv_cod_ccs2,      fprv_val_noi,
                                    fprv_val_exe,      fprv_reg_fis ,      fprv_cod_asto, fprv_num_mayo,
                                    fprv_comp2_iva ,   fprv_comp3_iva ,    fprv_comp4_iva ,
                                    fprv_tot_compiva , fprv_cod_ftrn ,		fprv_aprob_sri,
                                    fprv_auto_sri,		fprv_cod_proy, 		fprv_cod_actv,
                                    fprv_clav_sri,      fprv_caja_nom  )
                                values($idempresa,              $idsucursal,            $idejer_fact,           '$comprobante', 
                                        $cod_prove,             '$factura',             '$fecha_emis',          $plazo,
                                        '$codigo_ret_fue_b',    '$codigo_ret_fue_s',    '$codigo_ret_iva_b',    '$codigo_ret_iva_s',
                                        $valor_grab12b,          $valor_grab0b,         $ivabp,                 $ivat,
                                        $iceb,                   $totals,               '$porc_ret_fue_b',      '$porc_ret_fue_s',
                                        $val_fue_b,              $val_fue_s,            '$porc_ret_iva_b',      '$porc_ret_iva_s',
                                        $val_iva_b,              $val_iva_s,            '$auto_prove',          '$serie',
                                        '$fecha_validez',       '$sust_trib',           '$fisc_b',              '$gasto1',
                                        '$num_rete',            '$detalle' ,  
                                        '$valor_grab12s',
                                        '$valor_grab0s',        '$ices',                '$fisc_s',              '$gasto2' ,
                                        '$base_fue_b',          '$base_fue_s',          '$base_iva_b' ,
                                        '$base_iva_s',          '$val_gasto1',          '$val_gasto2' ,         '$plan_ser' ,
                                        '$tipo_doc',            '$no_obj_iva',          '$rise',
                                        '$empl_soli',           '$empl_auto' ,          $moneda,                $coti,
                                        '',                     '$ruc',                 '$fprv_resp',           '$fprv_caja' ,
                                        '$serie_rete',          '$auto_rete',           '$cad_rete',            '$fecha_server' ,
                                        '$forma_pago',          '$prove_tprov',
                                        '$tipo_pago',           '$clpv_cod_pais',       $anio_fac,              $m1,
                                        '$doble_tributac',      '$pago_sujeto_ret',    '$fprv_email_clpv',
                                        '$cliente_nombre',      '$direccion',           '$telefono',
                                        '$ccosn_3',             '$ccosn_4',             '$valor_noObjIva',
                                        '$valor_exentoIva',      '$regimen_fiscal',     '$secu_asto',           '$comp_cod',
                                        '$comp2iva',             '$comp3iva',           '$comp4iva',
                                        '$comp_tot_iva',        '$formato' ,	        '$fprv_aprob_sri',
                                        '$fprv_auto_sri',	    '$proyecto',            '$actividad',
                                        '$fprv_clav_sri' ,      '$fprv_nom' ); ";
        $oIfx->QueryT($sql);

        $Logs->crearLog($factura, $secu_asto, $sql);
        $basenograiva = 0;
        $baseimponible = $valor_grab0t;

        $basenograiva     = number_format($basenograiva, 2, '.', '');
        $baseimponible     = number_format($baseimponible, 2, '.', '');

        $sql = "insert into comercial.cxp_compra ( empr_cod_empr,     sucu_cod_sucu,      factura_serie,      factura_num,
                                         clave_acceso,      factura_fecha,      asto_cod_asto,      asto_cod_ejer,
                                         asto_num_prdo,     usuario_id,         fecha_server,       clpv_cod_clpv,
                                         clpv_nom_clpv,     clpv_ruc_clpv,      factura_tributario
                                        ) 
                                 values( $idempresa,        $idsucursal,       '$serie',            '$factura',
                                         '$clave_acceso',   '$fecha_mysql',    '$secu_asto',         $idejer_fact,
                                         $m1,                $user_web,       now(),               $cod_prove,
                                         '$cliente_nombre', '$ruc' ,           '$trib_ser'
                                       )";
        $oCon->QueryT($sql);

        $msj = "Factura: $factura";

        $oReturn->script("Swal.fire({
                            type: 'success',
                            width: '40%',
                            title: 'Factura - Gasto Ingresada Correctamente ',
                            text: '$msj'
                        })");

        $oIfx->QueryT('COMMIT WORK;');
        $oCon->QueryT('COMMIT');

        $oReturn->script("$('#ModalGen').modal('hide');");
        $oReturn->script('consultar();');
    } catch (Exception $e) {
        // rollback
        $oIfx->QueryT('ROLLBACK WORK;');
        $oCon->QueryT('ROLLBACK');
        $oReturn->alert($e->getMessage());
    }

    $oReturn->script("jsRemoveWindowLoad();");
    return $oReturn;
}

// GENERAR ALMACEN
function generar_almacen(
    $aForm = '',
    $tipo_compr = '',
    $factura = '',
    $ruc_emisor = '',
    $clpv_emisor = '',
    $fecha_emi = '',
    $fecha_auto = '',
    $tipo_emision = '',
    $ruc_recep = '',
    $clave_acceso = '',
    $y = '',
    $cont_clpv = ''
) {

    global $DSN_Ifx, $DSN;
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo();
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oCon = new Dbo();
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oReturn = new xajaxResponse();
    //////////////

    $idempresa  = $aForm['empresa'];
    $idsucursal = $aForm['sucursal'];
    $idempresa  = $aForm['empresa'];

    $tipo_fact  = $aForm['su.' . $y];
    $rete_ser   = $aForm['re.' . $y];
    $tipo_ser   = $aForm['ti.' . $y];
    $plan_ser   = $aForm['pl.' . $y];
    unset($_SESSION['U_COMPRAS_INV']);

    $pos        = $clave_acceso;
    $ambiente_sri = "S";

    $sHtml_clpv = '';
    if ($tipo_fact == 2) {
        // ALMACEN
        // TRAN
        $sql = "select t.tran_cod_tran, t.tran_des_tran  from saetran t, saedefi d  where
                        t.tran_cod_tran = d.defi_cod_tran and
                        t.tran_cod_empr = $idempresa and
                        t.tran_cod_sucu = $idsucursal and
                        t.tran_cod_modu = 10 and
                        d.defi_cod_empr = $idempresa and
                        d.defi_tip_defi = '0' and
                        d.defi_cod_modu = 10 order by 2 ";
        $lista_tran = lista_boostrap_func($oIfx, $sql, '002', 'tran_cod_tran',  'tran_des_tran');

        // BODEGA
        $sql = "select  b.bode_cod_bode, b.bode_nom_bode from saebode b, saesubo s where
                    b.bode_cod_bode = s.subo_cod_bode and
                    b.bode_cod_empr = $idempresa and
                    s.subo_cod_empr = $idempresa and
                    s.subo_cod_sucu = $idsucursal";
        $lista_bode = lista_boostrap_func($oIfx, $sql, '1', 'bode_cod_bode',  'bode_nom_bode');

        unset($array);
        $array = clave_acceso($clave_acceso, $ambiente_sri, 1, $idempresa, $cont_clpv);
        $resp  = '';
        $fecauto = '';
        $num_auto = '';
        $serie = '';
        $factura = '';
        $valor_grab12b = 0;
        $valor_grab0s = 0;
        $totalbien = 0;
        $totalserv = 0;
        $total = 0;
        $imp = 0;

        $html_prod = ' <table class="table table-striped table-condensed table-bordered table-hover" style="width: 100%; margin-top: 20px;" align="center">
                            <tr>
                                <td class="alert alert-success">No-</td>
                                <td class="alert alert-success" align="center">Codigo</td>
                                <td class="alert alert-success" align="center">Producto</td>
                                <td class="alert alert-success" align="center">Producto</td>             
                                <td class="alert alert-success" align="center">Cantidad</td>
                                <td class="alert alert-success" align="center">Costo</td>
                                <td class="alert alert-success" align="center">Impuesto</td>
                                <td class="alert alert-success" align="center">Desc1</td>
                                <td class="alert alert-success" align="center">Desc General</td>
                                <td class="alert alert-success" align="center">Total</td>
                                <td class="alert alert-success" align="center">Total con Imp.</td>
                            </tr>';
        unset($array_dato);
        if (count($array) > 0) {
            $i = 1;
            foreach ($array as $val) {
                $resp    = $val[0];
                $num_auto = $val[1];
                $serie   = $val[2];
                $factura = $val[3];
                $valor_grab12b = vacios($val[4], 0);
                $valor_grab0s  = vacios($val[5], 0);
                $imp           = $val[8] * 1;
                $total         = $valor_grab12b + $valor_grab0s + $imp;

                $prod_cod   = trim($val[9]);
                $prod_nom   = trim($val[10]);
                $cantidad   = trim($val[11]);
                $costo      = trim($val[12]);
                $desc_val   = trim($val[13]);
                $descuento  = trim($val[14]);
                $iva        = trim($val[15]);
                $descuento_2 = 0;
                $descuento_general = 0;

                // TOTAL
                $total_fac  = 0;
                $total_con_iva = 0;
                $total_fac  = calculo_dmov($costo, $cantidad, $descuento, $descuento_2, $descuento_general);

                // total con iva
                if ($iva > 0) {
                    $total_con_iva = round((($total_fac * $iva) / 100), 2) + $total_fac;
                } else {
                    $total_con_iva = $total_fac;
                }

                $ser_prod = 'pr.' . $i;

                $array_dato[] = array($ser_prod, $i, $prod_cod, $cantidad, $costo, $iva, $descuento, $descuento_general, $total_fac, $total_con_iva);


                $html_prod .= '<tr height="20">';
                $html_prod .= '<td class="alert alert-warning">' . $i . '</td>';
                $html_prod .= '<td class="alert alert-warning">' . $prod_cod . '</td>';
                $html_prod .= '<td class="alert alert-warning" onclick="">' . $prod_nom . '</td>';
                $html_prod .= '<td class="alert alert-warning" >
                                        <input type="text" class="form-control input-sm" id="' . $ser_prod . '" name="' . $ser_prod . '" 
                                        onkeyup="buscar_producto( event, 1, \'' . $i . '\' ); "
                                        placeholder="Enter o F4" />
                               </td>';
                $html_prod .= '<td class="alert alert-warning" align="right">' . $cantidad . '</td>';
                $html_prod .= '<td class="alert alert-warning" align="right">' . $costo . '</td>';
                $html_prod .= '<td class="alert alert-warning" align="right">' . $iva . '</td>';
                $html_prod .= '<td class="alert alert-warning" align="right">' . $descuento . '</td>';
                $html_prod .= '<td class="alert alert-warning" align="right">' . $descuento_general . '</td>';
                $html_prod .= '<td class="alert alert-warning" align="right">' . $total_fac . '</td>';
                $html_prod .= '<td class="alert alert-warning" align="right">' . $total_con_iva . '</td>';
                $html_prod .= '</tr>';
                $i++;
            }
        }

        $html_prod .= '</table';

        if ($resp == 'ok' || $resp == 'warning') {
            if ($resp == 'warning') {
                $resp_msn = "Factura Ingresada " . $serie . ' - ' . $factura;
                $oReturn->script('alerts("' . $resp_msn . '", "error");');
            }

            $sHtml_clpv  = '<br>';
            $sHtml_clpv .= '<div class="col-md-12">
                                <div class="form-row">
                                    <div class="col-md-6">
                                            <label for="tipo_retencion">* Tipo Retencion:</label>
                                            <select id="tipo_retencion" name="tipo_retencion" 
                                                class="form-control input-sm" style="width:100%" onchange="retencion_auto( \'' . $ruc_emisor . '\' );" >
                                                <option value="">Seleccione una opcion..</option>
                                                <option value="1">Retencion Electronica</option>
                                                <option value="2">Retencion Preimpresa</option>
                                                <option value="3">Retencion Manual</option>
                                            </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label>Secuencial Retencion:</label>
                                        <div id="ret_auto"> </div>
                                    </div>
                                     <div class="col-md-3">
                                        <label>Fecha Retencion:</label>
                                        <div id=""> 
                                            <input type="date" class="form-control input-sm" id="fecha_retencion_fact" name="fecha_retencion_fact" style="width: 100%" value="' . date('Y-m-d') . '" />
                                        </div>
                                    </div>
                                </div>
                            </div>';

            $ruta = 'crear_prod_ter_inv/producto.php';
            $cero = 0;
            $script = 'abrirInventario(\'../' . $ruta . '?sesionId=' . session_id() . '\', ' . $cero . ', ' . $cero . ')';
            $sHtml_clpv .= '<div class="col-md-12">
                                <div class="form-row">
                                    <div class="col-md-4">
                                        <label for="comprobante">* Transaccion:</label>
                                        <select id="comprobante" name="comprobante" class="form-control select2" style="width:100%" >
                                            <option value="">Seleccione una opcion..</option>
                                            ' . $lista_tran . '
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="bodega">* Bodega:</label>
                                        <select id="bodega" name="bodega" class="form-control select2" style="width:100%" >
                                            <option value="">Seleccione una opcion..</option>
                                            ' . $lista_bode . '
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label >.</label>
                                        <div class="btn btn-primary btn-sm" 
                                                    onclick="' . $script . '" style="width:100%">
                                                    <span class="glyphicon glyphicon-list"></span>
                                                    CREAR PRODUCTO
                                        </div>
                                    </div>
                                </div>
                            </div>';

            $sHtml_clpv .= '<h4 class="text-primary">FACTURA<small></small></h4>
                            <div class="col-md-12">
                                <div class="form-row">
                                    <div class="col-md-6">
                                        <label for="serie">* Serie:</label>
                                        <input type="text" class="form-control input-sm" id="serie" name="serie" value="' . $serie . '" style="text-align:right" readonly />
                                    </div>

                                    <div class="col-md-6">
                                        <label for="factura">* Factura:</label>
                                        <input type="text" class="form-control input-sm" id="factura" name="factura" value="' . $factura . '" style="text-align:right" readonly />
                                    </div>
                                </div>
                            </div>';
            $sHtml_clpv .= '<div class="col-md-12">
                                <div class="form-row">
                                    <div class="col-md-12">
                                        ' . $html_prod . '
                                    </div>
                                </div>
                            </div>';
            $sHtml_clpv .= '<h4 class="text-primary">RETENCION<small></small></h4>
                            <div class="col-md-12">
                                <div class="form-row">
                                    <div class="col-md-3">
                                        <label for="base12">Valor Base Imponible:</label>
                                        <input type="text" class="form-control input-sm" id="base12" name="base12" value="' . $valor_grab12b . '" style="text-align:right" readonly />
                                    </div>

                                    <div class="col-md-3">
                                        <label for="base0">Valor 0%:</label>
                                        <input type="text" class="form-control input-sm" id="base0" name="base0" value="' . $valor_grab0s . '" style="text-align:right" readonly />
                                    </div>

                                    <div class="col-md-3">
                                        <label for="iva">Impuesto%:</label>
                                        <input type="text" class="form-control input-sm" id="iva" name="iva" value="' . $imp . '" style="text-align:right" readonly />
                                    </div>

                                    <div class="col-md-3">
                                        <label for="total">Total:</label>
                                        <input type="text" class="form-control input-sm" id="total" name="total" value="' . $total . '" style="text-align:right" readonly /> 
                                    </div>
                                </div>
                            </div>';

            if ($rete_ser == 1) {
                if ($tipo_ser == 1) {
                    // GENERAL
                    $sql = "SELECT p.cuenta_aplicada, p.credito_bienes, p.credito_servicios,
                                    p.rete_fuente_bienes, p.rete_fuente_servicios, 
                                    p.rete_iva_bienes, p.rete_iva_servicios
                                    from comercial.plantilla_clpv p WHERE 
                                    id_clpv      in ('0') AND 
                                    id_empresa   = $idempresa AND
                                    id           = '$plan_ser' order by 2 ";
                    if ($oCon->Query($sql)) {
                        if ($oCon->NumFilas() > 0) {
                            $pafa_rete_fuen     = $oCon->f('rete_fuente_bienes');
                            $pafa_rete_fuenser  = $oCon->f('rete_fuente_servicios');

                            $pafa_rete_iva      = $oCon->f('rete_iva_bienes');
                            $pafa_rete_ivaser  = $oCon->f('rete_iva_servicios');
                        }
                    }
                    $oCon->Free();
                } else {
                    // CLIENTE
                    $sql = "SELECT p.cuenta_aplicada, p.credito_bienes, p.credito_servicios,
                                    p.rete_fuente_bienes, p.rete_fuente_servicios, 
                                    p.rete_iva_bienes, p.rete_iva_servicios
                                    from comercial.plantilla_clpv p WHERE 
                                    id_clpv      in ('$cont_clpv') AND 
                                    id_empresa   = $idempresa AND
                                    id           = '$plan_ser' order by 2 ";
                    if ($oCon->Query($sql)) {
                        if ($oCon->NumFilas() > 0) {
                            $pafa_rete_fuen     = $oCon->f('rete_fuente_bienes');
                            $pafa_rete_fuenser  = $oCon->f('rete_fuente_servicios');

                            $pafa_rete_iva      = $oCon->f('rete_iva_bienes');
                            $pafa_rete_ivaser   = $oCon->f('rete_iva_servicios');
                        }
                    }
                    $oCon->Free();
                }


                $sHtml_clpv .= '<div class="col-md-12">';
                if (!empty($pafa_rete_fuen)) {
                    $sql        = "select tret_porct from saetret where tret_cod_empr = $idempresa and tret_cod  = '$pafa_rete_fuen' ";
                    $tret_porct = consulta_string_func($sql, 'tret_porct', $oIfx, '0');
                    $base_ret_f = $valor_grab12b + $valor_grab0s;
                    $rete_fuente = round((($base_ret_f * $tret_porct) / 100), 2);

                    // RETENCION FUENTE
                    $sql = "select tret_cod, tret_det_ret, tret_porct, tret_cta_cre			
                                    from saetret where			    
                                    tret_cod_empr = $idempresa and			
                                    tret_ban_crdb = 'CR' and
                                    tret_ban_retf = 'IR' and
                                    COALESCE( tret_cta_cre, 'ru') <> 'ru'  and
                                    tret_cta_cre <> '' order by 1 ";
                    $lista_retf = '';
                    if ($oIfx->Query($sql)) {
                        if ($oIfx->NumFilas() > 0) {
                            do {
                                $tret_cod = $oIfx->f('tret_cod');
                                $tret_porct = $tret_cod . ' - ' . htmlentities($oIfx->f('tret_porct')) . '%';

                                $selectedEmpr = '';
                                if ($tret_cod == $pafa_rete_fuen) {
                                    $selectedEmpr = 'selected';
                                }

                                $lista_retf .= '<option value="' . $tret_cod . '" ' . $selectedEmpr . '>' . $tret_porct . '</option>';
                            } while ($oIfx->SiguienteRegistro());
                        }
                    }
                    $oIfx->Free();

                    $sHtml_clpv .= '<div class="col-md-3">
                                        <label for="retcodif">Codigo Retencion:</label>
                                        <select id="retcodif" name="retcodif" class="form-control" style="width:100%" onchange="calculo_ret(1);">
                                            <option value="">Seleccione una opcion..</option>
                                            ' . $lista_retf . '
                                        </select>
                                    </div>';
                    $sHtml_clpv .= '<div class="col-md-3">
                                        <label for="retfuente">Valor Retencion Fuente:</label>
                                        <input type="text" class="form-control input-sm" id="retfuente" name="retfuente" value="' . $rete_fuente . '" style="text-align:right" />
                                    </div>';
                }


                if (!empty($pafa_rete_fuenser)) {
                    $sql = "select tret_porct from saetret where tret_cod_empr = $idempresa and tret_cod  = '$pafa_rete_fuenser' ";
                    $tret_porct = consulta_string_func($sql, 'tret_porct', $oIfx, '0');
                    $base_ret_f = $valor_grab12b + $valor_grab0s;
                    $rete_fuente = round((($base_ret_f * $tret_porct) / 100), 2);

                    // RETENCION FUENTE
                    $sql = "select tret_cod, tret_det_ret, tret_porct, tret_cta_cre			
                                    from saetret where			    
                                    tret_cod_empr = $idempresa and			
                                    tret_ban_crdb = 'CR' and
                                    tret_ban_retf = 'IR' and
                                    COALESCE( tret_cta_cre, 'ru') <> 'ru'  and
                                    tret_cta_cre <> '' order by 1 ";
                    $lista_retf = '';
                    if ($oIfx->Query($sql)) {
                        if ($oIfx->NumFilas() > 0) {
                            do {
                                $tret_cod = $oIfx->f('tret_cod');
                                $tret_porct = $tret_cod . ' - ' . htmlentities($oIfx->f('tret_porct')) . '%';

                                $selectedEmpr = '';
                                if ($tret_cod == $pafa_rete_fuenser) {
                                    $selectedEmpr = 'selected';
                                }

                                $lista_retf .= '<option value="' . $tret_cod . '" ' . $selectedEmpr . '>' . $tret_porct . '</option>';
                            } while ($oIfx->SiguienteRegistro());
                        }
                    }
                    $oIfx->Free();

                    $sHtml_clpv .= '<div class="col-md-3">
                                        <label for="retcodifser">Codigo Retencion:</label>
                                        <select id="retcodifser" name="retcodifser" class="form-control" style="width:100%" onchange="calculo_ret(3);">
                                            <option value="">Seleccione una opcion..</option>
                                            ' . $lista_retf . '
                                        </select>
                                    </div>';
                    $sHtml_clpv .= '<div class="col-md-3">
                                        <label for="retfuenteser">Valor Retencion Fuente:</label>
                                        <input type="text" class="form-control input-sm" id="retfuenteser" name="retfuenteser" value="' . $rete_fuente . '" style="text-align:right"  />
                                    </div>';
                }
                $sHtml_clpv .= '</div>';

                $sHtml_clpv .= '<div class="col-md-12">';
                if (!empty($pafa_rete_iva)) {
                    $sql = "select tret_porct from saetret where tret_cod_empr = $idempresa and tret_cod  = '$pafa_rete_iva' ";
                    $tret_porct = consulta_string_func($sql, 'tret_porct', $oIfx, '0');
                    $base_ret_i = $imp;
                    $rete_iva   = round((($base_ret_i * $tret_porct) / 100), 2);

                    // RETENCION FUENTE
                    $sql = "select tret_cod, tret_det_ret, tret_porct, tret_cta_cre			
                                    from saetret where			    
                                    tret_cod_empr = $idempresa and			
                                    tret_ban_crdb = 'CR' and
                                    tret_ban_retf = 'RI' and
                                    COALESCE( tret_cta_cre, 'ru') <> 'ru'  and
                                    tret_cta_cre <> '' order by 1 ";
                    $lista_reti = '';
                    if ($oIfx->Query($sql)) {
                        if ($oIfx->NumFilas() > 0) {
                            do {
                                $tret_cod = $oIfx->f('tret_cod');
                                $tret_porct = $tret_cod . ' - ' . htmlentities($oIfx->f('tret_porct')) . '%';

                                $selectedEmpr = '';
                                if ($tret_cod == $pafa_rete_iva) {
                                    $selectedEmpr = 'selected';
                                }

                                $lista_reti .= '<option value="' . $tret_cod . '" ' . $selectedEmpr . '>' . $tret_porct . '</option>';
                            } while ($oIfx->SiguienteRegistro());
                        }
                    }
                    $oIfx->Free();

                    $sHtml_clpv .= '<div class="col-md-3">
                                        <label for="retcodii">Codigo Retencion:</label>
                                        <select id="retcodii" name="retcodii" class="form-control" style="width:100%" onchange="calculo_ret(2);">
                                            <option value="">Seleccione una opcion..</option>
                                            ' . $lista_reti . '
                                        </select>
                                    </div>';
                    $sHtml_clpv .= '<div class="col-md-3">
                                        <label for="retiva">Valor Retencion Impuesto:</label>
                                        <input type="text" class="form-control input-sm" id="retiva" name="retiva" value="' . $rete_iva . '" style="text-align:right" />
                                    </div>';
                }


                if (!empty($pafa_rete_ivaser)) {
                    $sql = "select tret_porct from saetret where tret_cod_empr = $idempresa and tret_cod  = '$pafa_rete_ivaser' ";
                    $tret_porct = consulta_string_func($sql, 'tret_porct', $oIfx, '0');
                    $base_ret_i = $imp;
                    $rete_iva   = round((($base_ret_i * $tret_porct) / 100), 2);

                    // RETENCION FUENTE
                    $sql = "select tret_cod, tret_det_ret, tret_porct, tret_cta_cre			
                                    from saetret where			    
                                    tret_cod_empr = $idempresa and			
                                    tret_ban_crdb = 'CR' and
                                    tret_ban_retf = 'RI' and
                                    COALESCE( tret_cta_cre, 'ru') <> 'ru'  and
                                    tret_cta_cre <> '' order by 1 ";
                    $lista_reti = '';
                    if ($oIfx->Query($sql)) {
                        if ($oIfx->NumFilas() > 0) {
                            do {
                                $tret_cod = $oIfx->f('tret_cod');
                                $tret_porct = $tret_cod . ' - ' . htmlentities($oIfx->f('tret_porct')) . '%';

                                $selectedEmpr = '';
                                if ($tret_cod == $pafa_rete_ivaser) {
                                    $selectedEmpr = 'selected';
                                }

                                $lista_reti .= '<option value="' . $tret_cod . '" ' . $selectedEmpr . '>' . $tret_porct . '</option>';
                            } while ($oIfx->SiguienteRegistro());
                        }
                    }
                    $oIfx->Free();

                    $sHtml_clpv .= '<div class="col-md-3">
                                        <label for="retcodiiser">Codigo Retencion:</label>
                                        <select id="retcodiiser" name="retcodiiser" class="form-control" style="width:100%" onchange="calculo_ret(4);">
                                            <option value="">Seleccione una opcion..</option>
                                            ' . $lista_reti . '
                                        </select>
                                    </div>';
                    $sHtml_clpv .= '<div class="col-md-3">
                                        <label for="retivaser">Valor Retencion Impuesto:</label>
                                        <input type="text" class="form-control input-sm" id="retivaser" name="retivaser" value="' . $rete_iva . '" style="text-align:right"  />
                                    </div>';
                }

                $sHtml_clpv .= '</div>';
            }

            if ($resp == 'ok') {
                $sHtml_clpv .= '<div class="col-md-12">
                                <div class="form-row">
                                    <div class="col-md-12">
                                            <div id ="imagen1" class="btn btn-primary btn-sm" 
                                                    onclick="guardar_gasto(\'' . $tipo_compr . '\', \'' . $factura . '\' , \'' . $ruc_emisor . '\' , \'' . $clpv_emisor . '\', 
                                                    \'' . $fecha_emi . '\' ,   \'' . $fecha_auto . '\', \'' . $tipo_emision . '\',  \'' . $ruc_recep . '\', 
                                                    \'' . $clave_acceso . '\', \'' . $y . '\', \'' . $cont_clpv . '\');" style="width:100%">
                                                    <span class="glyphicon glyphicon-floppy-disk"></span>
                                                    GUARDAR
                                            </div>
                                    </div>
                                </div>
                            </div>';
            } else {
                $sHtml_clpv .= '<div class="col-md-12">
                                <div class="form-row">
                                    <div class="col-md-12"></div>
                                </div>
                            </div>';
            }
        } else {
            $oReturn->script('alerts("' . $num_auto . '", "error");');
        }

        $_SESSION['U_COMPRAS_INV'] = $array_dato;
    }



    $sHtml .= '<div class="table-responsive" style="width: 100%; height: 80%; overflow-y: scroll;">';
    $sHtml .= $sHtml_clpv;
    $sHtml .= '</div>';

    $oReturn->assign("fact_det", "innerHTML", $sHtml);
    $oReturn->assign("fac_dat", "innerHTML", htmlentities($clpv_emisor));
    $oReturn->script("jsRemoveWindowLoad();");

    $oReturn->script("$('.select2').select2({
                                            dropdownParent: $('#ModalGen')
                                        });");

    return $oReturn;
}


function calculo_dmov($costo = 0, $cantidad = 0, $descuento = 0, $descuento_2 = 0, $descuento_general = 0)
{
    $total_fac  = 0;
    $dsc1       = ($costo * $cantidad * $descuento) / 100;
    $dsc2       = ((($costo * $cantidad) - $dsc1) * $descuento_2) / 100;
    if ($descuento_general > 0) {
        // descto general
        $dsc3   = ((($costo * $cantidad) - $dsc1 - $dsc2) * $descuento_general) / 100;
        $total_fact_tmp = ((($costo * $cantidad) - ($dsc1 + $dsc2 + $dsc3)));
        $tmp    = ((($costo * $cantidad) - ($dsc1 + $dsc2)));
    } else {
        // sin descuento general
        $total_fact_tmp = ((($costo * $cantidad) - ($dsc1 + $dsc2)));
        $tmp = $total_fact_tmp;
    }

    $total_fac = round($total_fact_tmp, 2);

    return $total_fac;
}


// GUARDAR GASTO
function guardar_almacen(
    $aForm = '',
    $tipo_compr = '',
    $factura = '',
    $ruc = '',
    $cliente_nombre = '',
    $fecha_emi = '',
    $fecha_auto = '',
    $tipo_emision = '',
    $ruc_recep = '',
    $clave_acceso = '',
    $y = '',
    $cod_prove = ''
) {
    //Definiciones
    global $DSN, $DSN_Ifx;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oReturn = new xajaxResponse();

    //      VARIABLES
    $idempresa   = $aForm['empresa'];
    $idsucursal  = $aForm['sucursal'];
    $user_web    = $_SESSION['U_ID'];
    $serie       = $aForm['serie'];
    $factura     = $aForm['factura'];
    $base12      = $aForm['base12'];
    $base0       = $aForm['base0'];
    $iva         = $aForm['iva'];
    $total       = $aForm['total'];
    $tran        = $aForm['comprobante'];
    $bode_cod    = $aForm['bodega'];
    $usuario_informix = $_SESSION['U_USER_INFORMIX'];
    $fecha_server   = date("Y-m-d");
    $hora           = date("Y-m-d H:i:s");
    $fechaServer    = date("Y-m-d H:i:s");

    $tipo_fact  = $aForm['su.' . $y];
    $rete_ser   = $aForm['re.' . $y];
    $tipo_ser   = $aForm['ti.' . $y];
    $plan_ser   = $aForm['pl.' . $y];
    $trib_ser   = 'S';

    $array_prod = $_SESSION['U_COMPRAS_INV'];

    $tipo_retencion  = $aForm['tipo_retencion'];
    $rete_manual     = $aForm['rete_manual'];

    try {
        // commit
        $oIfx->QueryT('BEGIN WORK;');
        $oCon->QueryT('BEGIN');

        $sql = "select pcon_mon_base , pcon_seg_mone from saepcon where pcon_cod_empr = $idempresa  ";
        $moneda = consulta_string_func($sql, 'pcon_mon_base', $oIfx, '1');
        $mone_extr = consulta_string_func($sql, 'pcon_seg_mone', $oIfx, '');

        $sql = "select tcam_valc_tcam from saetcam where
				mone_cod_empr = $idempresa and
				tcam_cod_mone = $mone_extr and
				tcam_fec_tcam in (select max(tcam_fec_tcam)  from saetcam where
											mone_cod_empr = $idempresa and
											tcam_cod_mone = $mone_extr)";
        $coti = consulta_string_func($sql, 'tcam_valc_tcam', $oIfx, 0);

        if ($tipo_ser == 1) {
            // PLANILLA GENERAL
            $sql = "SELECT p.cuenta_aplicada, p.credito_bienes, p.credito_servicios,  detalle
                            from comercial.plantilla_clpv p WHERE 
                            id_clpv      in ('0') AND 
                            id_empresa   = $idempresa AND
                            id           = '$plan_ser' order by 2 ";
            if ($oCon->Query($sql)) {
                if ($oCon->NumFilas() > 0) {
                    $detalle = $oCon->f('detalle') . ' FC: ' . $serie . '-' . $factura;
                    $gasto1  = $oCon->f('cuenta_aplicada');
                    $fisc_b  = $oCon->f('credito_bienes');
                    if (empty($fisc_b)) {
                        $fisc_b  = $oCon->f('credito_servicios');
                    }
                }
            }
            $oCon->Free();

            $sql = "SELECT c.cuenta, c.centro_costos, c.porcentaje
                            FROM comercial.plantilla_ccosn c WHERE
                            c.id_empresa    = $idempresa AND
                            c.id_clpv       = 0 AND
                            c.id_plantilla  = '$plan_ser' ";
            unset($array_cuen);
            if ($oCon->Query($sql)) {
                if ($oCon->NumFilas() > 0) {
                    do {
                        $cuenta         = $oCon->f('cuenta');
                        $centro_costos  = $oCon->f('centro_costos');
                        $porcentaje     = $oCon->f('porcentaje');
                        $array_cuen[]  = array($cuenta, $centro_costos, $porcentaje);
                    } while ($oCon->SiguienteRegistro());
                }
            }
            $oCon->Free();
        } else {
            // PLANILLA CLIENTE
            $sql = "SELECT p.cuenta_aplicada, p.credito_bienes, p.credito_servicios,  detalle
                            from comercial.plantilla_clpv p WHERE 
                            id_clpv      in ('$cod_prove') AND 
                            id_empresa   = $idempresa AND
                            id           = '$plan_ser' order by 2 ";
            if ($oCon->Query($sql)) {
                if ($oCon->NumFilas() > 0) {
                    $detalle = $oCon->f('detalle') . ' FC: ' . $serie . '-' . $factura;
                    $gasto1  = $oCon->f('cuenta_aplicada');
                    $fisc_b  = $oCon->f('credito_bienes');
                    if (empty($fisc_b)) {
                        $fisc_b  = $oCon->f('credito_servicios');
                    }
                }
            }
            $oCon->Free();

            $sql = "SELECT c.cuenta, c.centro_costos, c.porcentaje
                            FROM comercial.plantilla_ccosn c WHERE
                            c.id_empresa    = $idempresa AND
                            c.id_clpv       = $cod_prove AND
                            c.id_plantilla  = '$plan_ser' ";
            unset($array_cuen);
            if ($oCon->Query($sql)) {
                if ($oCon->NumFilas() > 0) {
                    do {
                        $cuenta         = $oCon->f('cuenta');
                        $centro_costos  = $oCon->f('centro_costos');
                        $porcentaje     = $oCon->f('porcentaje');
                        $array_cuen[]  = array($cuenta, $centro_costos, $porcentaje);
                    } while ($oCon->SiguienteRegistro());
                }
            }
            $oCon->Free();
        }


        // direccion
        $sql = "select min( dire_dir_dire ) as dire  from saedire where
                    dire_cod_empr = $idempresa and
                    dire_cod_clpv = $cod_prove ";
        $direccion = consulta_string_func($sql, 'dire', $oIfx, '');

        // email
        $sql = "select min( emai_ema_emai ) as ema  from saeemai where
                emai_cod_empr = $idempresa and
                emai_cod_clpv = $cod_prove";
        $fprv_email_clpv = consulta_string_func($sql, 'ema', $oIfx, '');

        $sql = "select empr_iva_empr from saeempr where empr_cod_empr = $idempresa ";
        $empr_iva_empr = consulta_string_func($sql, 'empr_iva_empr', $oIfx, '0');

        $minv_aprob_sri = 'N';
        $minv_auto_sri = '';
        $minv_fech_sri = '';
        $rise       = 'N';
        $serie_rete  = '';
        $auto_rete      = '';
        $cad_rete      = '';
        $electronica = 'N';
        $num_rete    = '';
        unset($array_rete);
        $val_fue_b = 0;
        $val_iva_b = 0;
        if ($rete_ser == 1) {
            // RETENCION FUENTE            
            $codigo_ret_fue_b    = $aForm['retcodif'];
            $base_fue_b          = $valor_grab12t + $valor_grab0t;
            $sql = "select tret_porct , tret_cta_cre from saetret where tret_cod_empr = $idempresa and tret_cod  = '$codigo_ret_fue_b' ";
            $porc_ret_fue_b      = consulta_string_func($sql, 'tret_porct', $oIfx, '0');
            $tret_cta_creb       = consulta_string_func($sql, 'tret_cta_cre', $oIfx, '0');
            $val_fue_b           = $aForm['retfuente'];

            $codigo_ret_iva_b    = $aForm['retcodii'];
            $base_iva_b         =  $iva;
            $sql = "select tret_porct, tret_cta_cre from saetret where tret_cod_empr = $idempresa and tret_cod  = '$codigo_ret_iva_b' ";
            $porc_ret_iva_b      = consulta_string_func($sql, 'tret_porct', $oIfx, '0');
            $tret_cta_crei       = consulta_string_func($sql, 'tret_cta_cre', $oIfx, '0');
            $val_iva_b           = $aForm['retiva'];


            // RETENCION FUENTE  SERVICIOS
            $codigo_ret_fue_bser    = $aForm['retcodifser'];
            $base_fue_bser          = $valor_grab12t + $valor_grab0t;
            $sql = "select tret_porct , tret_cta_cre from saetret where tret_cod_empr = $idempresa and tret_cod  = '$codigo_ret_fue_bser' ";
            $porc_ret_fue_bser      = consulta_string_func($sql, 'tret_porct', $oIfx, '0');
            $tret_cta_crebser       = consulta_string_func($sql, 'tret_cta_cre', $oIfx, '0');
            $val_fue_bser           = vacios($aForm['retfuenteser'], 0);

            $codigo_ret_iva_bser    = $aForm['retcodiiser'];
            $base_iva_bser         =  $iva;
            $sql = "select tret_porct, tret_cta_cre from saetret where tret_cod_empr = $idempresa and tret_cod  = '$codigo_ret_iva_bser' ";
            $porc_ret_iva_bser      = consulta_string_func($sql, 'tret_porct', $oIfx, '0');
            $tret_cta_creiser       = consulta_string_func($sql, 'tret_cta_cre', $oIfx, '0');
            $val_iva_bser           = vacios($aForm['retivaser'], 0);


            if ($tipo_retencion == 1) { // ELECTRONICO
                $sql = "select retp_sec_retp , retp_num_seri from saeretp where 
                            retp_cod_empr = $idempresa and
                            retp_cod_sucu = $idsucursal and
                            retp_act_retp = '1' and
                            retp_elec_sn  = 'S' ";
                $num_rete     = consulta_string_func($sql, 'retp_sec_retp', $oIfx, '');
                $serie_rete   = consulta_string_func($sql, 'retp_num_seri', $oIfx, '');
                $num_rete     = secuencial(2, '', $num_rete, 9);

                $num_rete_sec = $num_rete * 1;
                $sql = "update saeretp set retp_sec_retp = ($num_rete_sec ) 
                                where retp_cod_empr = $idempresa and
                                retp_cod_sucu = $idsucursal and
                                retp_act_retp = 1 and
                                retp_elec_sn = 'S' ";
                $oIfx->QueryT($sql);
                $ret_elec_sn    = 'S';
            } elseif ($tipo_retencion == 2) {  // PREIMPRESA
                $sql = "select retp_sec_retp , retp_num_seri from saeretp where 
                            retp_cod_empr = $idempresa and
                            retp_cod_sucu = $idsucursal and
                            retp_act_retp = '1' and
                            retp_elec_sn  = 'N' ";
                $num_rete     = consulta_string_func($sql, 'retp_sec_retp', $oIfx, '');
                $serie_rete   = consulta_string_func($sql, 'retp_num_seri', $oIfx, '');
                $num_rete     = secuencial(2, '', $num_rete, 9);

                $num_rete_sec = $num_rete * 1;
                $sql = "update saeretp set retp_sec_retp = ($num_rete_sec ) 
                                where retp_cod_empr = $idempresa and
                                retp_cod_sucu = $idsucursal and
                                retp_act_retp = 1 and
                                retp_elec_sn = 'N' ";
                $oIfx->QueryT($sql);

                $minv_aprob_sri = 'S';
                $minv_auto_sri = '';
                $minv_fech_sri = '';
                $ret_elec_sn    = 'N';
            } elseif ($tipo_retencion == 3) { // MANUAL
                $sql = "SELECT c.id_rete_elec, c.retencion_serie, c.retencion_num, c.retencion_auto, c.clave_acceso
                                FROM cxp_retencion c WHERE
                                c.empr_cod_empr = $idempresa AND
                                c.id_rete_elec  = $rete_manual ";
                if ($oCon->Query($sql)) {
                    if ($oCon->NumFilas() > 0) {
                        $serie_rete     = $oCon->f('retencion_serie');
                        $num_rete       = $oCon->f('retencion_num');
                        $reten_fauto    = $oCon->f('retencion_auto');
                        $reten_auto     = $oCon->f('clave_acceso');
                    }
                }
                $oCon->Free();
                $minv_aprob_sri = 'S';
                $minv_auto_sri  = $reten_auto;
                $minv_fech_sri  = $reten_fauto;
                $ret_elec_sn    = 'S';

                $sql = "update cxp_retencion set aprobado = 'S' where empr_cod_empr = $idempresa AND id_rete_elec  = $rete_manual ";
                $oCon->QueryT($sql);
            }


            $array_rete[0] = array($codigo_ret_fue_b, $base_fue_b, $porc_ret_fue_b, $val_fue_b, $num_rete, $serie_rete, $tret_cta_creb, $ret_elec_sn);
            $array_rete[1] = array($codigo_ret_iva_b, $base_iva_b, $porc_ret_iva_b, $val_iva_b, $num_rete, $serie_rete, $tret_cta_crei, $ret_elec_sn);

            $array_rete[2] = array($codigo_ret_fue_bser, $base_fue_bser, $porc_ret_fue_bser, $val_fue_bser, $num_rete, $serie_rete, $tret_cta_crebser, $ret_elec_sn);
            $array_rete[3] = array($codigo_ret_iva_bser, $base_iva_bser, $porc_ret_iva_bser, $val_iva_bser, $num_rete, $serie_rete, $tret_cta_creiser, $ret_elec_sn);
        }

        // C O D I G O     D E L     E M P L E A D O     I N F O R M I X
        $sql = "SELECT usua_cod_empl, usua_nom_usua FROM SAEUSUA WHERE USUA_COD_USUA = $usuario_informix ";
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                $empleado       = $oIfx->f('usua_cod_empl');
                $usua_nom_usua  = $oIfx->f('usua_nom_usua');
            }
        }
        $oIfx->Free();

        //  EJERCICIO SAEFPRV
        list($d1, $m1, $anio_fac) = explode('/', $fecha_emi);
        $fecha_ejer = $anio_fac . '-12-31';
        $sql = "select ejer_cod_ejer from saeejer where ejer_fec_finl = '$fecha_ejer' and ejer_cod_empr = $idempresa ";
        $idejer_fact = consulta_string_func($sql, 'ejer_cod_ejer', $oIfx, 1);

        //$fecha_emis     = $m1 . '/' . $d1 . '/' . $anio_fac;
        $fecha_emis    = $anio_fac . '-' . $m1 . '-' . $d1;
        $fecha_contable = $fecha_emis;
        $fecha_vence    = $fecha_emis;
        $fecha_mysql    = $anio_fac . '-' . $m1 . '-' . $d1;

        $idprdo_cont = $m1;
        $idejer_cont = $idejer_fact;
        $tipo_factura = '1';
        $plazo       = 0;
        $fecha_validez = $fecha_emis;

        // PROVEEDOR
        $sql = "select  clpv_cod_tprov, clv_con_clpv, clpv_cod_paisp, clpv_cod_cuen
                    from saeclpv where
                    clpv_cod_clpv = '$cod_prove' and
                    clpv_cod_empr = $idempresa and
                    clpv_clopv_clpv = 'PV' ";
        $prove_tprov = '';
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                $prove_tprov   = $oIfx->f('clpv_cod_tprov');
                $clv_con_clpv  = $oIfx->f('clv_con_clpv');
                $clpv_cod_pais = $oIfx->f('clpv_cod_paisp');
                $clpv_cod_cuen = $oIfx->f('clpv_cod_cuen');
            }
        }
        $oIfx->Free();

        //registra Log de transaccion
        $Logs = new Logs(4);

        // ASIENTO CONTABLE
        // TIDU
        $sql = "select  defi_cod_tidu  from saedefi where
                        defi_cod_empr = $idempresa and
                        defi_cod_sucu = $idsucursal and
                        defi_cod_tran = '$tran' ";
        $tidu = consulta_string_func($sql, 'defi_cod_tidu', $oIfx, '');
        $total_compra = $total;
        $iva_total    = $iva;
        $desc_valor   = 0;
        $total_otros  = 0;

        $class = new mayorizacion_class();
        unset($array);
        $array = $class->secu_asto($oIfx, $idempresa, $idsucursal, 10, $fecha_contable, $usuario_informix, $tidu);
        foreach ($array as $val) {
            $secu_asto  = $val[0];
            $secu_dia   = $val[1];
            $asto_cod   = $val[0];
            $comp_cod   = $val[1];
            $tidu       = $val[2];
            $idejer     = $val[3];
            $idprdo     = $val[4];
            $moneda     = $val[5];
            $tcambio    = $val[6];
            $empleado   = $val[7];
            $usua_nom   = $val[8];
        } // fin foreach   

        $fprv_aprob_sri = 'N';
        $fprv_auto_sri = $clave_acceso;

        $Logs->crearLog($factura, $secu_asto, "Asto: $secu_asto, Tidu: $tidu, Comprobante: $comp_cod");

        // SAEMINV
        // SECUENCIAL MINV INGRESO COMPRA
        $sql_defi = "SELECT DEFI_COD_MODU, DEFI_TRS_DEFI  , DEFI_TIP_DEFI, DEFI_FOR_DEFI FROM SAEDEFI WHERE
                            DEFI_COD_EMPR = $idempresa AND
                            DEFI_COD_SUCU = $idsucursal and
                            defi_cod_modu = 10 and
                            defi_tip_defi = 0 and
                            defi_cod_tran = '$tran' ";
        $secu_minv = '';
        $formato = 0;
        if ($oIfx->Query($sql_defi)) {
            if ($oIfx->NumFilas() > 0) {
                $secu_minv = $oIfx->f('defi_trs_defi');
                $formato = $oIfx->f('defi_for_defi');
            }
        }
        $oIfx->Free();
        $secu_minv = secuencial(2, '0', $secu_minv, 8);
        $Logs->crearLog($factura, $secu_asto, $sql_defi);

        $fecha_pedido = $fecha_emis;
        $fecha_entrega = $fecha_emis;
        $cliente = $cod_prove;
        $fact_tot     = $base0 + $base12;
        $auto_prove    = $clave_acceso;
        $minv_email_clpv = $fprv_email_clpv;
        $serie_prove  = $serie;
        $usuario_web   = $user_web;
        $ret_electronica = 'S';
        $sql_minv = "insert into saeminv(	minv_num_plaz,  	minv_num_sec,       	minv_cod_tcam,              minv_cod_mone,  	
                                            minv_cod_empr,      minv_cod_sucu,          minv_cod_tran,  	        minv_cod_modu,      	
                                            minv_cod_empl,      minv_cod_ftrn,  	    minv_fmov,          	    minv_dege_minv,
                                            minv_cod_usua,  	minv_num_prdo,      	minv_cod_ejer,              minv_fac_prov,  	
                                            minv_fec_entr,      minv_fec_ser,           minv_est_minv,  	        minv_tot_minv,      	
                                            minv_con_iva,       minv_sin_iva,   	    minv_dge_valo,      	    minv_iva_valo,
                                            minv_otr_valo,  	minv_fle_minv,      	minv_aut_usua,              minv_aut_impr,  	
                                            minv_fac_inic,      minv_fac_fina,          minv_ser_docu,  	        minv_fec_valo,      	
                                            minv_sucu_clpv,     minv_sno_esta,  	    minv_usu_minv ,     	    minv_cm1_minv,
                                            minv_fec_regc,  	minv_cod_fpagop,    	minv_cod_tpago,             minv_ani_minv,  	
                                            minv_mes_minv,      minv_user_web,          minv_comp_cont, 	        minv_tran_minv,     	
                                            minv_cod_clpv,      minv_email_clpv, 	    minv_elec_sn,			    minv_num_dgi ,
                                            minv_val_tcam ,     minv_cm6_minv,          minv_nom_clpv,              minv_ruc_clpv,
                                            minv_aprob_sri,     minv_auto_sri,          minv_fech_sri 	)
                                    values( 1,             	    '$secu_minv',       	$tcambio,                   $moneda,        	
                                            $idempresa,         $idsucursal,             '$tran',        	        10, 
                                            '$empleado',       '$formato',     	        '$fecha_pedido',    	    0,
                                            $usuario_informix,  $idprdo,         		$idejer,                    '$factura',     	
                                            '$fecha_entrega',   CURRENT_DATE,                'M',             	        $fact_tot,         		
                                            $base12,            $base0,            	    $desc_valor,        	    $iva_total,
                                            $total_otros,   	0,                 		'$auto_prove',              '',             	
                                            '$fac_ini',         '$fac_fin',             '$serie_prove', 	        '$fecha_emis',      	
                                            $idsucursal,         0,              	    '$usua_nom_usua',   	    '$detalle',
                                            CURRENT_DATE,        	'$fpago_prove',     	'$tipo_pago',                $anio_fac,           	
                                            $idprdo,             $usuario_web,          '$secu_asto' ,  	        '$secu_asto',        	
                                            $cliente,           '$minv_email_clpv',     '$ret_electronica',         '$dgui' ,
                                            $coti,              '$msn_reco' ,			'$cliente_nombre',           '$ruc'	,
                                            '$minv_aprob_sri',  '$minv_auto_sri',       '$minv_fech_sri' ) ";
        $oIfx->QueryT($sql_minv);
        $Logs->crearLog($factura, $secu_asto, $sql);

        //UPDATE AL SECUENCIAL SAEDEFI
        $sql_update = "UPDATE SAEDEFI SET DEFI_TRS_DEFI = '$secu_minv' WHERE
                                DEFI_COD_EMPR = $idempresa AND
                                DEFI_COD_SUCU = $idsucursal and
                                defi_cod_modu = 10 and
                                defi_tip_defi = 0 and
                                defi_cod_tran = '$tran' ";
        $oIfx->QueryT($sql_update);
        $Logs->crearLog($factura, $secu_asto, $sql_update);

        //SERIAL DEL SAEDMIV
        $serial_minv = 0;
        $sql_serial = "select minv_num_comp from saeminv where
                                minv_num_sec = '$secu_minv' and
                                minv_cod_empr = $idempresa and
                                minv_cod_sucu = $idsucursal and
                                minv_cod_clpv = $cliente and
                                minv_cod_tran = '$tran' ";
        $serial_minv = consulta_string_func($sql_serial, 'minv_num_comp', $oIfx, 0);
        $Logs->crearLog($factura, $secu_asto, $sql_serial);

        // FORMA DE PAGO
        $sql = "select fpag_cod_fpag from saefpag where
                        fpag_cod_empr = $idempresa and
                        fpag_cod_sucu = $idsucursal and
                        fpag_cod_modu = 10 ";
        $fpag_cod_fpag = consulta_string_func($sql, 'fpag_cod_fpag', $oIfx, 0);
        if ($fpag_cod_fpag > 0) {
            $sql_d = "insert into saemxfp(
                            mxfp_cod_mxfp,   mxfp_num_comp,      mxfp_cod_sucu,         mxfp_cod_empr,   mxfp_cod_fpag,      
                            mxfp_num_prdo,   mxfp_cod_ejer,      mxfp_num_dias,         mxfp_poc_mxfp,   mxfp_val_mxfp,      
                            mxfp_fec_mxfp,   mxfp_fec_fin )
                    values( 1,               $serial_minv,       $idsucursal,           $idempresa,      $fpag_cod_fpag,    
                            $idprdo,         $idejer,           0,                      100,            $total,
                            '$fecha_emis'  ,  '$fecha_emis'    ) ";
            $oIfx->QueryT($sql_d);
            $Logs->crearLog($factura, $secu_asto, $sql_d);
        }

        $comprobante = $tran;
        // SAEASTO
        $sql = $class->saeasto(
            $oIfx,
            $secu_asto,
            $idempresa,
            $idsucursal,
            $idejer,
            $idprdo,
            $moneda,
            $usuario_informix,
            $comprobante,
            $cliente_nombre,
            $total,
            $fecha_contable,
            $detalle,
            $secu_dia,
            $fecha_contable,
            $tidu,
            $usua_nom_usua,
            $user_web,
            10,
            $formato
        );

        $Logs->crearLog($factura, $secu_asto, $sql);

        // DIRECTORIO
        $cod_dir = 1;
        $debito     = 0;
        $credito        = $total;
        $debito_ext = 0;
        $credito_ext    = round(($credito / $coti), 2);
        $fact_tmp = $serie . '-' . $factura . '-001';
        $sql = $class->saedir(
            $oIfx,
            $idempresa,
            $idsucursal,
            $idprdo,
            $idejer,
            $secu_asto,
            $cod_prove,
            10,
            $comprobante,
            $fact_tmp,
            $fecha_vence,
            $detalle,
            $debito,
            $credito,
            $debito_ext,
            $credito_ext,
            'CR',
            '',
            '',
            '',
            '',
            '',
            '',
            $user_web,
            $cod_dir,
            $coti,
            $cliente_nombre,
            $ccli_cod
        );
        $Logs->crearLog($factura, $secu_asto, $sql);

        // CTA PROVEEDOR DASI
        $sql = $class->saedasi(
            $oIfx,
            $idempresa,
            $idsucursal,
            $clpv_cod_cuen,
            $idprdo,
            $idejer,
            $ccosn_cod,
            $debito,
            $credito,
            $debito_ext,
            $credito_ext,
            $coti,
            $detalle,
            '',
            '',
            $user_web,
            $secu_asto,
            '',
            $cod_dir,
            '',
            $opBand,
            $opBacn,
            $opFlch,
            '',
            $act_cod
        );
        $Logs->crearLog($factura, $secu_asto, $sql);

        // RETENCION
        if (count($array_rete) > 0) {
            $ret_secu  = 1;
            $total_ret = 0;
            foreach ($array_rete as $val) {
                $codigo_ret = $val[0];
                $ret_base   = $val[1];
                $ret_porc   = $val[2];
                $ret_val    = $val[3];
                $num_rete   = $val[4];
                $serie_rete = $val[5];
                $cta_ret    = $val[6];
                $ret_elec_sn = $val[7];
                $debito     = 0;
                $credito    = $ret_val;
                $debito_ext = 0;
                $credito_ext = round(($ret_val / $coti), 2);

                if (!empty($codigo_ret)) {
                    $ret_det    = 'RETENCION ' . $serie_rete . ' - ' . $num_rete . ' FC: ' . $serie . '-' . $factura;
                    $sql2 = $class->saeret(
                        $oIfx,
                        $idempresa,
                        $idsucursal,
                        $idprdo,
                        $idejer,
                        $secu_asto,
                        $cod_prove,
                        $cliente_nombre,
                        $direccion,
                        '',
                        $ruc,
                        $ret_secu,
                        $codigo_ret,
                        $ret_porc,
                        $ret_base,
                        $ret_val,
                        $num_rete,
                        $ret_det,
                        $debito,
                        $credito,
                        $debito_ext,
                        $credito_ext,
                        $factura,
                        $serie_rete,
                        '',
                        '',
                        $fprv_email_clpv,
                        $ret_elec_sn,
                        $coti
                    );
                    $Logs->crearLog($factura, $secu_asto, $sql2);

                    $sql = $class->saedasi(
                        $oIfx,
                        $idempresa,
                        $idsucursal,
                        $cta_ret,
                        $idprdo,
                        $idejer,
                        $ccosn_cod,
                        $debito,
                        $credito,
                        $debito_ext,
                        $credito_ext,
                        $coti,
                        $detalle,
                        '',
                        '',
                        $user_web,
                        $secu_asto,
                        '',
                        2,
                        '',
                        $opBand,
                        $opBacn,
                        $opFlch,
                        '',
                        $act_cod
                    );
                    $Logs->crearLog($factura, $secu_asto, $sql);

                    $ret_secu++;
                    $total_ret += $credito;
                }
            }

            // DASI RETENCION CRUCE
            $sql = "select tran_cod_tran  from saetran where
                                tran_cod_empr = $idempresa and
                                tran_cod_sucu = $idsucursal and
                                tran_cod_tran like 'RET%' ";
            $tran_ret = consulta_string($sql, 'tran_cod_tran', $oIfx, 'RET');

            $debito     = $total_ret;
            $credito    = 0;
            $debito_ext = round(($total_ret / $coti), 2);
            $credito_ext = 0;

            $sql = $class->saedasi(
                $oIfx,
                $idempresa,
                $idsucursal,
                $clpv_cod_cuen,
                $idprdo,
                $idejer,
                $ccosn_cod,
                $debito,
                $credito,
                $debito_ext,
                $credito_ext,
                $coti,
                $detalle,
                '',
                '',
                $user_web,
                $secu_asto,
                '',
                $cod_dir,
                '',
                $opBand,
                $opBacn,
                $opFlch,
                '',
                $act_cod
            );
            $Logs->crearLog($factura, $secu_asto, $sql);

            $sql = $class->saedir(
                $oIfx,
                $idempresa,
                $idsucursal,
                $idprdo,
                $idejer,
                $secu_asto,
                $cod_prove,
                10,
                $tran_ret,
                $fact_tmp,
                $fecha_vence,
                $detalle,
                $debito,
                $credito,
                $debito_ext,
                $credito_ext,
                'DB',
                '',
                '',
                '',
                '',
                '',
                '',
                $user_web,
                2,
                $coti,
                $cliente_nombre,
                $ccli_cod
            );
            $Logs->crearLog($factura, $secu_asto, $sql);
        }

        // MOVIMIENTO INVENTARIO
        if (count($array_prod) > 0) {
            $x = 1;
            unset($array_dmov);
            $tot_cuen_inv = 0;
            foreach ($array_prod as $val) {
                $ser_prod = $val[0];
                $pos      = $val[1];
                $prod_cod = $aForm[$ser_prod]; //$val[2]; 
                $cantidad = $val[3];
                $costo    = $val[4];
                $iva      = $val[5];
                $descuento = $val[6];
                $descuento_general  = $val[7];
                $total_fac          = $val[8];
                $total_con_iva      = $val[9];
                $costo_real         = round(($total_fac / $cantidad), 6);

                $resp_dmov = saedmov_insert(
                    $oIfx,
                    $x,
                    $prod_cod,
                    $idsucursal,
                    $idempresa,
                    $bode_cod,
                    $idejer,
                    $serial_minv,
                    $idprdo,
                    $cantidad,
                    $costo,
                    $total_fac,
                    $costo_real,
                    $descuento,
                    $descuento_general,
                    $iva,
                    $tran,
                    $factura,
                    $cod_prove,
                    $fecha_emis
                );
                $x++;

                // ARRAY DE CUENTA PROD Y IVA
                $sql = "select prbo_cta_inv from saeprbo where
                                prbo_cod_empr = $idempresa and
                                prbo_cod_sucu = $idsucursal and
                                prbo_cod_bode = $bode_cod and
                                prbo_cod_prod = '$prod_cod' ";
                $cuenta_prod  = consulta_string_func($sql, 'prbo_cta_inv', $oIfx, '');
                $array_dmov[$cuenta_prod] += $total_fac;
                $tot_cuen_inv += $total_fac;
            }
        }

        // AJUSTE ASIENTO
        $dif = 0;
        $dif = round(($total - $tot_cuen_inv - $iva_total), 2);
        // CTA INV DASI
        if (count($array_dmov) > 0) {
            $suma = 0;
            $rd   = 1;
            foreach (array_keys($array_dmov) as $key) {
                if ($rd == 1) {
                    $suma = $array_dmov[$key] + $dif;
                } else {
                    $suma = $array_dmov[$key];
                }

                $credito = 0;
                $credito_ext = 0;
                $debito  = $suma;
                $debito_ext    = round(($debito / $coti), 2);

                $sql = $class->saedasi(
                    $oIfx,
                    $idempresa,
                    $idsucursal,
                    $key,
                    $idprdo,
                    $idejer,
                    $centro_costos,
                    $debito,
                    $credito,
                    $debito_ext,
                    $credito_ext,
                    $coti,
                    $detalle,
                    '',
                    '',
                    $user_web,
                    $secu_asto,
                    '',
                    0,
                    '',
                    $opBand,
                    $opBacn,
                    $opFlch,
                    '',
                    $act_cod
                );
                $Logs->crearLog($factura, $secu_asto, $sql);

                $rd++;
            }
        }

        // CTA IMPUESTO DASI
        $sql = "select  bode_cta_ideb from saebode where bode_cod_empr = $idempresa and bode_cod_bode = $bode_cod ";
        $fisc_b = consulta_string_func($sql, 'bode_cta_ideb', $oIfx, '');

        $credito = 0;
        $credito_ext = 0;
        $debito  = $iva_total;
        $debito_ext    = round(($debito / $coti), 2);
        $sql = $class->saedasi(
            $oIfx,
            $idempresa,
            $idsucursal,
            $fisc_b,
            $idprdo,
            $idejer,
            $ccosn_cod,
            $debito,
            $credito,
            $debito_ext,
            $credito_ext,
            $coti,
            $detalle,
            '',
            '',
            $user_web,
            $secu_asto,
            '',
            0,
            '',
            $opBand,
            $opBacn,
            $opFlch,
            '',
            $act_cod
        );
        $Logs->crearLog($factura, $secu_asto, $sql);

        // ACTUALIZACION SAEASTO
        $sql = "update saeasto set asto_est_asto = 'MY', 
                    asto_vat_asto = $total  where
                    asto_cod_empr = $idempresa  and
                    asto_cod_sucu = $idsucursal and
                    asto_cod_asto = '$secu_asto' and
                    asto_cod_ejer = $idejer and
                    asto_num_prdo = $idprdo and
                    asto_cod_empr = $idempresa and     
                    asto_cod_sucu = $idsucursal and
                    asto_user_web = $user_web ";
        $oIfx->QueryT($sql);
        $Logs->crearLog($factura, $secu_asto, $sql);

        $sql = "insert into comercial.cxp_compra ( empr_cod_empr,     sucu_cod_sucu,      factura_serie,      factura_num,
                                         clave_acceso,      factura_fecha,      asto_cod_asto,      asto_cod_ejer,
                                         asto_num_prdo,     usuario_id,         fecha_server,       clpv_cod_clpv,
                                         clpv_nom_clpv,     clpv_ruc_clpv,      factura_tributario
                                        ) 
                                 values( $idempresa,        $idsucursal,       '$serie',            '$factura',
                                         '$clave_acceso',   '$fecha_mysql',    '$secu_asto',         $idejer,
                                         $idprdo,           $user_web,       now(),               $cod_prove,
                                         '$cliente_nombre', '$ruc' ,           '$trib_ser'
                                       )";
        $oCon->QueryT($sql);

        $msj = "Factura: $factura, Retencion: $num_rete, Diario: $secu_asto";

        $oReturn->script("Swal.fire({
                            type: 'success',
                            width: '40%',
                            title: 'Factura - Inventario Ingresada Correctamente ',
                            text: '$msj'
                        })");

        $oIfx->QueryT('COMMIT WORK;');
        $oCon->QueryT('COMMIT');

        $oReturn->script("$('#ModalGen').modal('hide');");
        $oReturn->script('consultar();');
    } catch (Exception $e) {
        // rollback
        $oIfx->QueryT('ROLLBACK WORK;');
        $oCon->QueryT('ROLLBACK');
        $oReturn->alert($e->getMessage());
    }

    $oReturn->script("jsRemoveWindowLoad();");
    return $oReturn;
}


function saedmov_insert(
    $oIfx,
    $x,
    $prod_cod,
    $idsucursal,
    $idempresa,
    $idbodega,
    $idejer,
    $minv_cod,
    $idprdo,
    $cantidad,
    $costo,
    $total,
    $costo_real,
    $descuento,
    $descuento_general,
    $iva,
    $tran,
    $factura,
    $clpv_cod,
    $fecha
) {
    // sctok bodega
    $sql = "select prbo_dis_prod, prbo_uco_prod, prbo_cod_unid from saeprbo where
                        prbo_cod_empr = $idempresa and
                        prbo_cod_sucu = $idsucursal and
                        prbo_cod_bode = $idbodega and
                        prbo_cod_prod = '$prod_cod' ";
    $unid_cod      = consulta_string_func($sql, 'prbo_cod_unid', $oIfx, 1);
    $costo_ult_tmp = consulta_string_func($sql, 'prbo_uco_prod', $oIfx, 0);
    $cant_ult_tmp  = consulta_string_func($sql, 'prbo_dis_prod', $oIfx, 0);
    $stock         = 0;
    $stock         = $cant_ult_tmp;
    $hora = date("Y-m-d H:i:s");

    $sql_d = "insert into saedmov(
                    dmov_cod_dmov,   dmov_cod_prod,     dmov_cod_sucu,
                    dmov_cod_empr,   dmov_cod_bode,     dmov_cod_unid,
                    dmov_cod_ejer,   dmov_num_comp,     dmov_num_prdo,
                    dmov_can_dmov,   dmov_can_entr,     dmov_cun_dmov,
                    dmov_cto_dmov,   dmov_pun_dmov,     dmov_pto_dmov,
                    dmov_ds1_dmov,   dmov_ds2_dmov,     dmov_ds3_dmov,
                    dmov_ds4_dmov,   dmov_des_tota,     dmov_imp_dmov,
                    dmov_est_dmov,   dmov_iva_dmov,     dmov_iva_porc,
                    dmov_dis_dmov,   dmov_ice_dmov,     dmov_hor_crea,
                    dmov_cod_tran,   dmov_fac_prov,     dmov_cod_clpv,
                    dmov_fmov,       dmov_pto1_dmov,    dmov_cod_lote	)
            values( $x,             '$prod_cod',        $idsucursal,    
                    $idempresa,      $idbodega,         $unid_cod,
                    $idejer,         $minv_cod,         $idprdo, 
                    $cantidad,       $cantidad,         $costo,
                    $total,          $costo_real,       ($cantidad*$costo_real),
                    $descuento,      0,                 0,
                    0,               $descuento_general, 0,
                    '1',             0,                 $iva,
                    'N',             0,                 '$hora',
                    '$tran',        '$factura',         $clpv_cod,
                    '$fecha',       0,                  '' ) ";
    $oIfx->QueryT($sql_d);

    // COSTO
    // COSTO PROMEDIO
    $costo_real_tot = ($costo_real * $cantidad) + ($costo_ult_tmp * $cant_ult_tmp);
    $cant_real      = $cant_ult_tmp + $cantidad;
    if ($cant_real > 0) {
        $cost_act   = round(($costo_real_tot / $cant_real), 6);
    } else {
        $cost_act   = 0;
    }

    if ($cost_act < 0) {
        $cost_act   = $costo_real;
        $cant_real  = $cantidad;
    }

    // actualiza stock en bodega                                                
    $sql = "update saeprbo set prbo_dis_prod = ($stock+$cantidad), prbo_uco_prod = $costo_real where
                    prbo_cod_empr = $idempresa and
                    prbo_cod_sucu = $idsucursal and
                    prbo_cod_bode = $idbodega and
                    prbo_cod_prod = '$prod_cod' ";
    $oIfx->QueryT($sql);

    // saecost
    // ID DEL SAECOST
    $sql_id_cost = "select max(cost_cod_cost) as maximo from saecost where
                            cost_cod_prod = '$prod_cod' and
                            cost_cod_empr = $idempresa ";
    $cost_cod_cost = consulta_string($sql_id_cost, 'maximo', $oIfx, 0);
    // INGRESO SAECOST
    $sql_cost = "insert into saecost(cost_cod_cost,       cost_cod_prod,      cost_num_comp,
                                    cost_cod_dmov,        cost_cod_bode,      cost_cod_sucu,
                                    cost_cod_empr,        cost_num_prdo,      cost_cod_ejer,
                                    cost_fec_cost,        cost_can_cost,      cost_val_unit,
                                    cost_est_cost,        cost_tip_cost )
                            values(($cost_cod_cost+1),    '$prod_cod',        $minv_cod,
                                    ($x),                 $idbodega,          $idsucursal,
                                    $idempresa,           $idprdo,            $idejer,
                                    '$fecha',            ($cant_real),        $cost_act,
                                    1,                   'I' ) ";
    $oIfx->QueryT($sql_cost);

    return 'OK';
}


function retencion_auto($aForm = '', $ruc = '')
{
    //Definiciones
    global $DSN, $DSN_Ifx;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oReturn = new xajaxResponse();

    //      VARIABLES
    $idempresa   = $aForm['empresa'];
    $idsucursal  = $aForm['sucursal'];
    $tipo_rete   = $aForm['tipo_retencion'];

    if ($tipo_rete == '1') {
        // ELECTRONICA
        $sql = "select retp_sec_retp , retp_num_seri from saeretp where 
                    retp_cod_empr = $idempresa and
                    retp_cod_sucu = $idsucursal and
                    retp_act_retp = '1' and
                    retp_elec_sn = 'S'";
        $num_rete     = consulta_string_func($sql, 'retp_sec_retp', $oIfx, '');
        $serie_rete   = consulta_string_func($sql, 'retp_num_seri', $oIfx, '');
        $num_rete     = secuencial(2, '', $num_rete, 9);
    } elseif ($tipo_rete == '2') {
        // PREIMPRESA
        $sql = "select retp_sec_retp , retp_num_seri, retp_fech_cadu from saeretp where 
                    retp_cod_empr = $idempresa and
                    retp_cod_sucu = $idsucursal and
                    retp_act_retp = '1' and
                    retp_elec_sn = 'N' ";
        $num_rete     = consulta_string_func($sql, 'retp_sec_retp', $oIfx, '');
        $serie_rete   = consulta_string_func($sql, 'retp_num_seri', $oIfx, '');
        $cadu_rete    = 'Caducidad: ' . fecha_mysql_func(consulta_string_func($sql, 'retp_fech_cadu', $oIfx, ''));
        $num_rete     = secuencial(2, '', $num_rete, 9);
    } else {
        $serie_rete = '';
        $num_rete = '';
        // MANUAL
        $sql = "SELECT c.id_rete_elec, c.retencion_serie, c.retencion_num, c.retencion_auto
                    FROM cxp_retencion c WHERE
                    c.empr_cod_empr = $idempresa AND
                    c.clpv_ruc_clpv = '$ruc' AND
                    c.aprobado      = 'N' order by c.retencion_auto ";
        $lista_retf = '';
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                do {
                    $tret_cod = $oCon->f('id_rete_elec');
                    $tret_num = $oCon->f('retencion_serie') . ' - ' . $oCon->f('retencion_num') . ' - ' . $oCon->f('retencion_auto');

                    $lista_retf .= '<option value="' . $tret_cod . '" >' . $tret_num . '</option>';
                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();

        $serie_rete = '';
        $serie_rete = '<select id="rete_manual" name="rete_manual" class="form-control select2" style="width:100%" >
                            <option value="">Seleccione una opcion..</option>
                            ' . $lista_retf . '
                      </select>';
    }



    $oReturn->assign("ret_auto", "innerHTML", $serie_rete . ' - ' . $num_rete . ' ' . $cadu_rete);

    return $oReturn;
}

function calculo_ret($aForm = '', $tipo = '')
{
    //Definiciones
    global $DSN, $DSN_Ifx;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oReturn = new xajaxResponse();

    //      VARIABLES
    $idempresa   = $aForm['empresa'];
    $retcodii    = $aForm['retcodii'];
    $retcodif    = $aForm['retcodif'];
    $importe     = $aForm['base12'] + $aForm['base0'];
    $imp         = $aForm['iva'];

    $retcodiiser    = $aForm['retcodii'];
    $retcodifser    = $aForm['retcodifser'];

    if ($tipo == 1) {
        //RETENCION FUENTE 1
        $sql = "select tret_porct from saetret where tret_cod_empr = $idempresa and tret_cod  = '$retcodif' ";
        $tret_porct = consulta_string_func($sql, 'tret_porct', $oIfx, '0');
        $rete_fue   = round((($importe * $tret_porct) / 100), 2);
        $oReturn->assign("retfuente", "value", $rete_fue);
    } elseif ($tipo == 2) {
        // IVA 1
        $sql = "select tret_porct from saetret where tret_cod_empr = $idempresa and tret_cod  = '$retcodii' ";
        $tret_porct = consulta_string_func($sql, 'tret_porct', $oIfx, '0');
        $rete_iva   = round((($imp * $tret_porct) / 100), 2);
        $oReturn->assign("retiva", "value", $rete_iva);
    } elseif ($tipo == 3) {
        //RETENCION FUENTE 2
        $sql = "select tret_porct from saetret where tret_cod_empr = $idempresa and tret_cod  = '$retcodifser' ";
        $tret_porct = consulta_string_func($sql, 'tret_porct', $oIfx, '0');
        $rete_fue   = round((($importe * $tret_porct) / 100), 2);
        $oReturn->assign("retfuenteser", "value", $rete_fue);
    } elseif ($tipo == 4) {
        // IVA 2
        $sql = "select tret_porct from saetret where tret_cod_empr = $idempresa and tret_cod  = '$retcodiiser' ";
        $tret_porct = consulta_string_func($sql, 'tret_porct', $oIfx, '0');
        $rete_iva   = round((($imp * $tret_porct) / 100), 2);
        $oReturn->assign("retivaser", "value", $rete_iva);
    }

    return $oReturn;
}


// ASIENTO GASTO
function asiento_gasto(
    $aForm = '',
    $tipo_compr = '',
    $factura = '',
    $ruc = '',
    $cliente_nombre = '',
    $fecha_emi = '',
    $fecha_auto = '',
    $tipo_emision = '',
    $ruc_recep = '',
    $clave_acceso = '',
    $y = '',
    $cod_prove = ''
) {

    // echo "sj";exit;
    //Definiciones
    global $DSN, $DSN_Ifx;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oReturn = new xajaxResponse();

    //      VARIABLES
    $idempresa   = $aForm['empresa'];
    $serie       = $aForm['serie'];
    $factura     = $aForm['factura'];
    $base12      = $aForm['base12'];
    $base0       = $aForm['base0'];

    $otros_valores_0 = $aForm['otros_valores_0'];
    if ($otros_valores_0 > 0) {
        $base0 = $base0 + $otros_valores_0;
    }

    $excento_impuesto_ad = $aForm['excento_impuesto_ad'];
    if (empty($excento_impuesto_ad)) {
        $excento_impuesto_ad = 0;
    }
    $iva         = $aForm['iva'];
    $total       = $aForm['total'];


    $rete_ser   = $aForm['re.' . $y];
    $tipo_ser   = $aForm['ti.' . $y];
    $plan_ser   = $aForm['pl.' . $y];


    $sql = "select pcon_mon_base , pcon_seg_mone from saepcon where pcon_cod_empr = $idempresa  ";
    $moneda = consulta_string_func($sql, 'pcon_mon_base', $oIfx, '1');
    $mone_extr = consulta_string_func($sql, 'pcon_seg_mone', $oIfx, '');

    $sql = "select tcam_valc_tcam from saetcam where
            mone_cod_empr = $idempresa and
            tcam_cod_mone = $mone_extr and
            tcam_fec_tcam in (select max(tcam_fec_tcam)  from saetcam where
                                        mone_cod_empr = $idempresa and
                                        tcam_cod_mone = $mone_extr)";
    $coti = consulta_string_func($sql, 'tcam_valc_tcam', $oIfx, 0);

    if ($tipo_ser == 1) {
        // PLANILLA GENERAL
        $sql = "SELECT p.cuenta_aplicada, p.credito_bienes, p.credito_servicios,  detalle
                        from comercial.plantilla_clpv p WHERE 
                        id_clpv      in ('0') AND 
                        id_empresa   = $idempresa AND
                        id           = '$plan_ser' order by 2 ";

        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                $detalle = $oCon->f('detalle') . ' FC: ' . $serie . '-' . $factura;
                $gasto1  = $oCon->f('cuenta_aplicada');
                $fisc_b  = $oCon->f('credito_bienes');
                if (empty($fisc_b)) {
                    $fisc_b  = $oCon->f('credito_servicios');
                }
            }
        }
        $oCon->Free();

        $sql = "SELECT c.cuenta, c.centro_costos, c.porcentaje
                        FROM comercial.plantilla_ccosn c WHERE
                        c.id_empresa    = $idempresa AND
                        c.id_clpv       = 0 AND
                        c.id_plantilla  = '$plan_ser' ";
        unset($array_cuen);
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                do {
                    $cuenta         = $oCon->f('cuenta');
                    $centro_costos  = $oCon->f('centro_costos');
                    $porcentaje     = $oCon->f('porcentaje');
                    $array_cuen[]  = array($cuenta, $centro_costos, $porcentaje);
                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();
    } else {
        // PLANILLA CLIENTE
        $sql = "SELECT p.cuenta_aplicada, p.credito_bienes, p.credito_servicios,  detalle
                        from comercial.plantilla_clpv p WHERE 
                        id_clpv      in ('$cod_prove') AND 
                        id_empresa   = $idempresa AND
                        id           = '$plan_ser' order by 2 ";
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                $detalle = $oCon->f('detalle') . ' FC: ' . $serie . '-' . $factura;
                $gasto1  = $oCon->f('cuenta_aplicada');
                $fisc_b  = $oCon->f('credito_bienes');
                if (empty($fisc_b)) {
                    $fisc_b  = $oCon->f('credito_servicios');
                }
            }
        }
        $oCon->Free();

        $sql = "SELECT c.cuenta, c.centro_costos, c.porcentaje
                        FROM comercial.plantilla_ccosn c WHERE
                        c.id_empresa    = $idempresa AND
                        c.id_clpv       = $cod_prove AND
                        c.id_plantilla  = '$plan_ser' ";
        unset($array_cuen);
        if ($oCon->Query($sql)) {
            if ($oCon->NumFilas() > 0) {
                do {
                    $cuenta         = $oCon->f('cuenta');
                    $centro_costos  = $oCon->f('centro_costos');
                    $porcentaje     = $oCon->f('porcentaje');
                    $array_cuen[]  = array($cuenta, $centro_costos, $porcentaje);
                } while ($oCon->SiguienteRegistro());
            }
        }
        $oCon->Free();
    }



    // BIENES
    $val_gasto1     = $base12 + $base0;

    // TOTAL
    $valor_grab12t  = $base12;
    $valor_grab0t   = $base0;
    $ivat           = $iva;

    $ret_elec_sn    = 'S';
    $serie_rete  = '';
    $num_rete    = '';
    unset($array_rete);
    $val_fue_b = 0;
    $val_iva_b = 0;
    $val_fue_bser = 0;
    $val_iva_bser = 0;
    if ($rete_ser == 1) {
        // RETENCION FUENTE  BIENES
        $codigo_ret_fue_b    = $aForm['retcodif'];
        $base_fue_b          = $valor_grab12t + $valor_grab0t;
        $sql = "select tret_porct , tret_cta_cre from saetret where tret_cod_empr = $idempresa and tret_cod  = '$codigo_ret_fue_b' ";
        $porc_ret_fue_b      = consulta_string_func($sql, 'tret_porct', $oIfx, '0');
        $tret_cta_creb       = consulta_string_func($sql, 'tret_cta_cre', $oIfx, '0');
        $val_fue_b           = vacios($aForm['retfuente'], 0);

        $codigo_ret_iva_b    = $aForm['retcodii'];
        $base_iva_b         =  $iva;
        $sql = "select tret_porct, tret_cta_cre from saetret where tret_cod_empr = $idempresa and tret_cod  = '$codigo_ret_iva_b' ";
        $porc_ret_iva_b      = consulta_string_func($sql, 'tret_porct', $oIfx, '0');
        $tret_cta_crei       = consulta_string_func($sql, 'tret_cta_cre', $oIfx, '0');
        $val_iva_b           = vacios($aForm['retiva'], 0);


        // RETENCION FUENTE  SERVICIOS
        $codigo_ret_fue_bser    = $aForm['retcodifser'];
        $base_fue_bser          = $valor_grab12t + $valor_grab0t;
        $sql = "select tret_porct , tret_cta_cre from saetret where tret_cod_empr = $idempresa and tret_cod  = '$codigo_ret_fue_bser' ";
        $porc_ret_fue_bser      = consulta_string_func($sql, 'tret_porct', $oIfx, '0');
        $tret_cta_crebser       = consulta_string_func($sql, 'tret_cta_cre', $oIfx, '0');
        $val_fue_bser           = vacios($aForm['retfuenteser'], 0);

        $codigo_ret_iva_bser    = $aForm['retcodiiser'];
        $base_iva_bser         =  $iva;
        $sql = "select tret_porct, tret_cta_cre from saetret where tret_cod_empr = $idempresa and tret_cod  = '$codigo_ret_iva_bser' ";
        $porc_ret_iva_bser      = consulta_string_func($sql, 'tret_porct', $oIfx, '0');
        $tret_cta_creiser       = consulta_string_func($sql, 'tret_cta_cre', $oIfx, '0');
        $val_iva_bser           = vacios($aForm['retivaser'], 0);

        $array_rete[0] = array($codigo_ret_fue_b, $base_fue_b, $porc_ret_fue_b, $val_fue_b, $num_rete, $serie_rete, $tret_cta_creb, $ret_elec_sn);
        $array_rete[1] = array($codigo_ret_iva_b, $base_iva_b, $porc_ret_iva_b, $val_iva_b, $num_rete, $serie_rete, $tret_cta_crei, $ret_elec_sn);

        $array_rete[2] = array($codigo_ret_fue_bser, $base_fue_bser, $porc_ret_fue_bser, $val_fue_bser, $num_rete, $serie_rete, $tret_cta_crebser, $ret_elec_sn);
        $array_rete[3] = array($codigo_ret_iva_bser, $base_iva_bser, $porc_ret_iva_bser, $val_iva_bser, $num_rete, $serie_rete, $tret_cta_creiser, $ret_elec_sn);
    }


    // PROVEEDOR
    $sql = "select  clpv_cod_tprov, clv_con_clpv, clpv_cod_paisp, clpv_cod_cuen
                from saeclpv where
                clpv_cod_clpv = '$cod_prove' and
                clpv_cod_empr = $idempresa and
                clpv_clopv_clpv = 'PV' ";
    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            $clpv_cod_cuen = $oIfx->f('clpv_cod_cuen');
        }
    }
    $oIfx->Free();


    // ASIENTO CONTABLE
    $i = 1;
    $mostrar_plantilla_clpv = 'N';

    $html_asto = '  <div style="text-align:center" id="estado_asiento" name="estado_asiento"></div>';

    $html_asto .= ' <table class="table table-striped table-condensed table-bordered table-hover" style="width: 100%; margin-top: 20px;" align="center">
                        <tr>
                            <td class="fecha_letra">No-</td>
                            <td class="fecha_letra" align="center">Codigo</td>
                            <td class="fecha_letra" align="center">Cuenta</td>
                            <td class="fecha_letra" align="center">Debito</td>             
                            <td class="fecha_letra" align="center">Credito</td>
                            <td class="fecha_letra" align="center">Centro Costo</td>
                        </tr>';
    $tot_deb = 0;
    $tot_cre = 0;
    // DIRECTORIO
    $val_fue_s  = 0;
    $val_iva_s      = 0;
    $debito     = 0;
    $credito        = $total - $val_fue_b - $val_fue_s - $val_iva_b - $val_iva_s - $val_fue_bser - $val_iva_bser;
    $debito_ext = 0;
    $credito_ext    = round(($credito / $coti), 2);

    // CTA PROVEEDOR DASI
    $sql = "select cuen_nom_cuen from saecuen where cuen_cod_empr = $idempresa and cuen_cod_cuen = '$clpv_cod_cuen' ";
    $cuen_nom   = consulta_string_func($sql, 'cuen_nom_cuen', $oIfx, '');

    $txt_cuenta_clpv = '';
    $txt_color_clpv = '';
    if (empty($cuen_nom)) {
        $txt_cuenta_clpv = '(Cuenta contable de proveedor no existe) ';
        $txt_color_clpv = 'yellow';
        $mostrar_plantilla_clpv = 'S';
    }

    $html_asto .= '<tr height="20" style="background-color: ' . $txt_color_clpv . '">';
    $html_asto .= '<td >' . $i . '</td>';
    $html_asto .= '<td >' . $clpv_cod_cuen . '</td>';
    $html_asto .= '<td >' . $txt_cuenta_clpv . $cuen_nom . '</td>';
    $html_asto .= '<td  align="right">' . $debito . '</td>';
    $html_asto .= '<td  align="right">' . $credito . '</td>';
    $html_asto .= '<td >' . $ccosn_cod . '</td>';
    $html_asto .= '</tr>';

    $tot_deb += $debito;
    $tot_cre += $credito;

    // RETENCION
    $i = 2;


    if (count($array_rete) > 0) {
        $ret_secu = 1;
        foreach ($array_rete as $val) {
            $ret_val    = $val[3];
            $num_rete   = $val[4];
            $serie_rete = $val[5];
            $cta_ret    = $val[6];
            $ret_elec_sn = $val[7];
            $debito     = 0;
            $credito    = $ret_val;
            $debito_ext = 0;
            $credito_ext = round(($ret_val / $coti), 2);

            if ($ret_val >= 0 && ($cta_ret != 0 && !empty($cta_ret))) {
                $sql = "select cuen_nom_cuen from saecuen where cuen_cod_empr = $idempresa and cuen_cod_cuen = '$cta_ret' ";
                $cuen_nom   = consulta_string_func($sql, 'cuen_nom_cuen', $oIfx, '');

                $txt_cuenta_rete = '';
                $txt_color_rete = '';
                if (empty($cuen_nom)) {
                    $txt_cuenta_rete = '(Cuenta contable de retencion no existe) ';
                    $txt_color_rete = 'yellow';
                    $mostrar_plantilla_clpv = 'S';
                }

                $html_asto .= '<tr height="20" style="background-color: ' . $txt_color_rete . '">';
                $html_asto .= '<td >' . $i . '</td>';
                $html_asto .= '<td >' . $cta_ret . '</td>';
                $html_asto .= '<td >' . $txt_cuenta_rete  . $cuen_nom . '</td>';
                $html_asto .= '<td  align="right">' . $debito . '</td>';
                $html_asto .= '<td  align="right">' . $credito . '</td>';
                $html_asto .= '<td >' . $ccosn_cod . '</td>';
                $html_asto .= '</tr>';
                $i++;
                $tot_deb += $debito;
                $tot_cre += $credito;
            }
        }
    }


    // CTA GASTO DASI
    if (count($array_cuen) > 0) {
        // DISTRIBUIR
        $valor_distr  = $val_gasto1 + $excento_impuesto_ad;
        $tot_dist = 0;
        foreach ($array_cuen as $val) {
            $porcentaje     = $val[2];
            $valor_tmp      = 0;
            $valor_tmp      = (($valor_distr * $porcentaje) / 100);
            $tot_dist       += $valor_tmp;
        }
        $dif = 0;
        $dif = round(($valor_distr - round($tot_dist, 2)), 2);
        $rd  = 1;
        foreach ($array_cuen as $val) {
            $cuenta         = $val[0];
            $centro_costos  = $val[1];
            $porcentaje     = $val[2];
            $valor_tmp      = 0;
            if ($rd == 1) {
                $valor_tmp      = round((($valor_distr * $porcentaje) / 100), 2) + $dif;
            } else {
                $valor_tmp      = round((($valor_distr * $porcentaje) / 100), 2);
            }

            $credito = 0;
            $credito_ext = 0;
            $debito  = $valor_tmp;
            $debito_ext    = round(($debito / $coti), 2);

            $sql = "select cuen_nom_cuen from saecuen where cuen_cod_empr = $idempresa and cuen_cod_cuen = '$cuenta' ";
            $cuen_nom   = consulta_string_func($sql, 'cuen_nom_cuen', $oIfx, '');

            $txt_cuenta_gasto = '';
            $txt_color_gasto = '';
            if (empty($cuen_nom)) {
                $txt_cuenta_gasto = '(Cuenta contable de gasto no existe) ';
                $txt_color_gasto = 'yellow';
                $mostrar_plantilla_clpv = 'S';
            }

            $html_asto .= '<tr height="20" style="background-color: ' . $txt_color_gasto . '">';
            $html_asto .= '<td >' . $i . '</td>';
            $html_asto .= '<td >' . $cuenta . '</td>';
            $html_asto .= '<td >' . $txt_cuenta_gasto . $cuen_nom . '</td>';
            $html_asto .= '<td  align="right">' . $debito . '</td>';
            $html_asto .= '<td  align="right">' . $credito . '</td>';
            $html_asto .= '<td >' . $centro_costos . '</td>';
            $html_asto .= '</tr>';

            $tot_deb += $debito;
            $tot_cre += $credito;

            $i++;
        }
    } else {
        // UNA SOLA CUENTA
        $credito = 0;
        $credito_ext = 0;
        $debito  = $val_gasto1 + $excento_impuesto_ad;
        $debito_ext    = round(($debito / $coti), 2);

        $sql = "select cuen_nom_cuen from saecuen where cuen_cod_empr = $idempresa and cuen_cod_cuen = '$gasto1' ";
        $cuen_nom   = consulta_string_func($sql, 'cuen_nom_cuen', $oIfx, '');

        $txt_cuenta_unacuenta = '';
        $txt_color_unacuenta = '';
        if (empty($cuen_nom)) {
            $txt_cuenta_unacuenta = '(Cuenta contable no existe) ';
            $txt_color_unacuenta = 'yellow';
            $mostrar_plantilla_clpv = 'S';
        }

        $html_asto .= '<tr height="20" style="background-color: ' . $txt_color_unacuenta . '">';
        $html_asto .= '<td >' . $i . '</td>';
        $html_asto .= '<td >' . $gasto1 . '</td>';
        $html_asto .= '<td >' . $txt_cuenta_unacuenta . $cuen_nom . '</td>';
        $html_asto .= '<td  align="right">' . $debito . '</td>';
        $html_asto .= '<td  align="right">' . $credito . '</td>';
        $html_asto .= '<td >' . $ccosn_cod . '</td>';
        $html_asto .= '</tr>';
        $i++;
        $tot_deb += $debito;
        $tot_cre += $credito;
    }

    // CTA IMPUESTO DASI
    $credito = 0;
    $credito_ext = 0;
    $debito  = $ivat;
    $debito_ext    = round(($debito / $coti), 2);



    // VALIDAMOS QUE LA CUENTA DE IVA NO ESTE INGRESADA, QUE LE DEBITO DE ESE VALOR SEA 0 Y EL CREDITO SEA 0
    if (empty($fisc_b) && floatval($debito) == 0  && floatval($credito) == 0) {
    } else {

        $sql = "select cuen_nom_cuen from saecuen where cuen_cod_empr = $idempresa and cuen_cod_cuen = '$fisc_b' ";
        $cuen_nom   = consulta_string_func($sql, 'cuen_nom_cuen', $oIfx, '');

        $txt_cuenta_imp = '';
        $txt_color_imp = '';
        if (empty($cuen_nom)) {
            $txt_cuenta_imp = '(Cuenta contable de impuesto no existe) ';
            $txt_color_imp = 'yellow';
            $mostrar_plantilla_clpv = 'S';
        }


        $total_deb_ad = $tot_deb + $debito;
        $total_cre_ad = $tot_cre + $credito;

        $total_descuadre = round($total_deb_ad - $total_cre_ad, 2);
        if ($total_descuadre > 0 && abs($total_descuadre) == '0.01') {
            $debito -= $total_descuadre;
            $oReturn->assign("iva", "value", $debito);
        }


        $html_asto .= '<tr height="20" style="background-color: ' . $txt_color_imp . '">';
        $html_asto .= '<td >' . $i . '</td>';
        $html_asto .= '<td >' . $fisc_b . '</td>';
        $html_asto .= '<td >' . $txt_cuenta_imp . $cuen_nom . '</td>';
        $html_asto .= '<td  align="right">' . $debito . '</td>';
        $html_asto .= '<td  align="right">' . $credito . '</td>';
        $html_asto .= '<td >' . $ccosn_cod . '</td>';
        $html_asto .= '</tr>';
    }




    $tot_deb += $debito;
    $tot_cre += $credito;
    //$tot_cre = 0;


    $html_asto .= '<tr height="20">';
    $html_asto .= '<td ></td>';
    $html_asto .= '<td ></td>';
    $html_asto .= '<td class="fecha_letra">TOTAL:</td>';
    $html_asto .= '<td class="fecha_letra" align="right">' . $tot_deb . '</td>';
    $html_asto .= '<td class="fecha_letra" align="right">' . $tot_cre . '</td>';
    $html_asto .= '<td ></td>';
    $html_asto .= '</tr>';

    /*
    c.id_empresa    = $idempresa AND
                        c.id_clpv       = $cod_prove AND
                        c.id_plantilla  = '$plan_ser' ";
                        */

    if ($mostrar_plantilla_clpv == 'S') {
        $html_asto .= '
                        <tr>
                            <td colspan="3" style="color: red">Existen cuentas contables erroneas. Por favor revisa la plantilla </td>
                            <td colspan="3">
                                <div id ="imagen1" class="btn btn-primary btn-sm" onclick="abrir_plantilla(\'' . $idempresa . '\', \'' . $cod_prove . '\', \'' . $plan_ser . '\');">
									<span class="glyphicon glyphicon-floppy-disk"></span>
									Abrir Plantilla
								</div>
                            </td>
                        </tr>
                    ';
    }

    $html_asto .= '</table>';




    if (round($tot_deb, 2) === round($tot_cre, 2)) {
        $asiento_cuadrado = '<h5 class="text-success"> ASIENTO CONTABLE CUADRADO</h5>
        <input type="hidden" id="asto_cuadre" value="C" name="asto_cuadre">
        ';
    } else {
        $asiento_cuadrado = '<h5 class="text-danger"> ASIENTO CONTABLE DESCUADRADO</h5>
        <input type="hidden" id="asto_cuadre" value="D" name="asto_cuadre">';
        // $oReturn->assign("asto_cuadre", "value", 1);
    }

    $oReturn->assign("divasto", "innerHTML", $html_asto);
    $oReturn->assign("estado_asiento", "innerHTML", $asiento_cuadrado);


    return $oReturn;
}


/*::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::*/
/* PROCESO DE REQUEST DE LAS FUNCIONES MEDIANTE AJAX NO MODIFICAR */
$xajax->processRequest();
/*::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::*/
