<?php
	
	include_once('../../Include/config.inc.php');
	include_once(path(DIR_INCLUDE).'conexiones/db_conexion.php');
	include_once(path(DIR_INCLUDE).'comun.lib.php');

	session_start();
    global $DSN_Ifx, $DSN;

	$oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();
	
	$oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    //varibales de sesion
    $idempresa = $_SESSION['U_EMPRESA'];
    $idsucursal = $_SESSION['U_SUCURSAL'];
    
    if (isset($_REQUEST['nomClpv']))
        $nomClpv = $_REQUEST['nomClpv'];
    else
        $nomClpv = null;
	
    $tabla = '';
	
	$sql = "select id, tipo from proy_tipo_contrato where id_empresa = $idempresa and estado = 'A'";
	if($oCon->Query($sql)){
		if($oCon->NumFilas() > 0){
			unset($arrayTipo);
			do{
				$arrayTipo[$oCon->f('id')] = $oCon->f('tipo');
			}while($oCon->SiguienteRegistro());
		}
	}
	$oCon->Free();

	$sql = "select id, id_clpv, codigo, nom_clpv, 
			ruc_clpv, observacion, id_tipo_proy
			from contrato_clpv
			where id_empresa = $idempresa and
			estado != 'RE' and
			id_tipo_proy in (2,3)";
    if($oCon->Query($sql)){
    	if($oCon->NumFilas() > 0){
    		$sHtmlEstado = '';
    		do{
    			$id = $oCon->f('id');
    			$id_clpv = $oCon->f('id_clpv');
                $codigo = $oCon->f('codigo');
                $nom_clpv = $oCon->f('nom_clpv');
				$ruc_clpv = $oCon->f('ruc_clpv');
				$observaciones = $oCon->f('observacion');
				$id_tipo_proy = $oCon->f('id_tipo_proy');
		
				$img = '<div class=\"center\"> <div class=\"btn btn-success btn-sm\" onclick=\"seleccionaItem(' . $id . ', '.$id_clpv.', \'' . $nom_clpv . '\')\"><span class=\"glyphicon glyphicon-ok\"><span></div></div>';

    			$tabla.='{
				  "codigo":"'.$arrayTipo[$id_tipo_proy].'",
				  "ruc":"'.$nom_clpv.'",
				  "cliente":"'.$ruc_clpv.'",
				  "contrato":"'.$codigo.'",
				  "detalle":"'.$observaciones.'",
				  "selecciona":"'.$img.'"
				},';

			}while($oCon->SiguienteRegistro());
    	}
	}
	$oCon->Free();

	//eliminamos la coma que sobra
	$tabla = substr($tabla,0, strlen($tabla) - 1);

	echo '{"data":['.$tabla.']}';
	
?>