<?php
	
	include_once('../../Include/config.inc.php');
	include_once(path(DIR_INCLUDE).'conexiones/db_conexion.php');
	include_once(path(DIR_INCLUDE).'comun.lib.php');

	if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}
    global $DSN_Ifx, $DSN;

	$oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    //varibales de sesion
    $idempresa = $_SESSION['U_EMPRESA'];
    $idsucursal = $_SESSION['U_SUCURSAL'];
    
    if (isset($_REQUEST['cuenta']))
        $cuenta = $_REQUEST['cuenta'];
    else
        $cuenta = null;
		
	if (isset($_REQUEST['op']))
        $op = $_REQUEST['op'];
    else
        $op = null;
	
	if (isset($_REQUEST['tipo']))
        $tipo = $_REQUEST['tipo'];
    else
        $tipo = null;

	if (isset($_REQUEST['tipo_retencion_ad']))
        $tipo_retencion_ad = $_REQUEST['tipo_retencion_ad'];
    else
        $tipo_retencion_ad = 'R';


	if($tipo_retencion_ad == 'R'){
		$sql_adicional = "and (tret_ret_det = '$tipo_retencion_ad' OR tret_ret_det is null OR tret_ret_det = '') ";
	}else{
		$sql_adicional = "and tret_ret_det = '$tipo_retencion_ad' ";
	}

    $tabla = '';
	
	$sqlTmp = "";
	if($tipo == 0){
		$sqlTmp = " and tret_ban_retf = 'IR'";
	}elseif($tipo){
		$sqlTmp = " and tret_ban_retf = 'RI'";
	}

    $sql = "select tret_cod, tret_det_ret, tret_porct, tret_cta_cre
			from saetret where
			tret_cod_empr = $idempresa and
			tret_ban_crdb = 'CR' and
			tret_cod like '$cuenta%'
			$sql_adicional
			$sqlTmp
			order by 1 ";

    if($oIfx->Query($sql)){
    	if($oIfx->NumFilas() > 0){
    		do{
    			$tret_cod = $oIfx->f('tret_cod');
    			$tret_det_ret = utf8_encode(htmlentities($oIfx->f('tret_det_ret')));
				$tret_porct = $oIfx->f('tret_porct');
				$tret_cta_cre = $oIfx->f('tret_cta_cre');
				
				$img = '';
				if(!empty($tret_cta_cre)){
					$img = '<div align=\"center\"> <div class=\"btn btn-success btn-sm\" onclick=\"seleccionaItem(\'' . $tret_cod . '\')\"><span class=\"glyphicon glyphicon-ok\"><span></div></div>';
				}
				
    			$tabla.='{
				  "tret_cod":"'.$tret_cod.'",
				  "tret_det_ret":"'.$tret_det_ret.'",
				  "tret_porct":"'.$tret_porct.'",
				  "tret_cta_cre":"'.$tret_cta_cre.'",
				  "selecciona":"'.$img.'"
				},';

			}while($oIfx->SiguienteRegistro());
    	}
	}
	$oIfx->Free();

	//eliminamos la coma que sobra
	$tabla = substr($tabla,0, strlen($tabla) - 1);

	echo '{"data":['.$tabla.']}';
	
?>