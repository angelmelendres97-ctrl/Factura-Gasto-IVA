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
    
    if (isset($_REQUEST['nomClpv']))
        $nomClpv = $_REQUEST['nomClpv'];
    else
        $nomClpv = null;

    $tabla = '';

    $sql = "select clpv_cod_clpv, clpv_ruc_clpv, clpv_nom_clpv,
    		clpv_est_clpv, clv_con_clpv, clpv_etu_clpv
    		from saeclpv
    		where clpv_cod_empr = $idempresa and
    		clpv_clopv_clpv = 'PV' and
    		(clpv_nom_clpv like upper ('%$nomClpv%') OR clpv_ruc_clpv like ('%$nomClpv%') or clpv_cod_char='$nomClpv')
    		order by 1";
			//echo $sql;exit;
    if($oIfx->Query($sql)){
    	if($oIfx->NumFilas() > 0){
    		$sHtmlEstado = '';
    		do{

    			$clpv_cod_clpv = $oIfx->f('clpv_cod_clpv');
    			$clpv_ruc_clpv = $oIfx->f('clpv_ruc_clpv');
                $clpv_nom_clpv = utf8_encode(htmlentities($oIfx->f('clpv_nom_clpv')));
                $clpv_est_clpv = $oIfx->f('clpv_est_clpv');
				$clv_con_clpv = $oIfx->f('clv_con_clpv');
				$clpv_etu_clpv = $oIfx->f('clpv_etu_clpv');
				
				$clpv_nom_clpv = str_replace("'", " ", $clpv_nom_clpv);
				$clpv_nom_clpv = str_replace('"', " ", $clpv_nom_clpv);
                //$clpv_nom_clpv = "";

				$tipoIdentificacion = '';
				if($clv_con_clpv == '01'){
					$tipoIdentificacion = 'RUC';
				}elseif($clv_con_clpv == '02'){
					$tipoIdentificacion = 'CED';
				}elseif($clv_con_clpv == '03'){
					$tipoIdentificacion = 'PAS';
				}
		
                $estado = '';
                $color = '';
                if ($clpv_est_clpv == 'A') {
                    $estado = 'ACTIVO';
                    $color = 'primary';
                } elseif ($clpv_est_clpv == 'P') {
                    $estado = 'PENDIENTE';
                    $color = 'success';
                } elseif ($clpv_est_clpv == 'S') {
                    $estado = 'SUSPENDIDO';
                    $color = 'danger';
                } 
				
				$sHtmlEstado = '<div class=\"'.$color.'\">'.$estado.'</div>';

				$img = '';
				if($clpv_est_clpv != 'S'){
					$img = '<div class=\"center\"> <div class=\"btn btn-success btn-sm\" onclick=\"seleccionaItem(' . $clpv_cod_clpv . ', \'' . $clpv_etu_clpv . '\')\"><span class=\"glyphicon glyphicon-ok\"><span></div></div>';
				}

    			$tabla.='{
				  "clpv_cod_clpv":"'.$clpv_cod_clpv.'",
				  "clv_con_clpv":"'.$tipoIdentificacion.'",
				  "clpv_ruc_clpv":"'.$clpv_ruc_clpv.'",
				  "clpv_nom_clpv":"'.$clpv_nom_clpv.'",
				  "clpv_est_clpv":"'.$sHtmlEstado.'",
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