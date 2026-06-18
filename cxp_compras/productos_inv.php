<?php
	include_once('../../Include/config.inc.php');
	include_once(path(DIR_INCLUDE).'conexiones/db_conexion.php');
	include_once(path(DIR_INCLUDE).'comun.lib.php');

	if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}
    global $DSN_Ifx;

	$oIfx = new Dbo;
	$oIfx->DSN = $DSN_Ifx;
	$oIfx->Conectar();

    //varibales de sesion											
    if(isset($_REQUEST['producto'])){    	$producto=$_REQUEST['producto'];    }else{	    $producto=null;    }
	if(isset($_REQUEST['bodega']))		    $bodega = $_REQUEST['bodega'];	    else		$bodega = 0;
	if(isset($_REQUEST['empresa']))		    $idempresa = $_REQUEST['empresa'];	    else		$idempresa = 0;
	if(isset($_REQUEST['id']))		    $id = $_REQUEST['id'];	    else		$id = 0;

	$sql = "select subo_cod_sucu from saesubo where subo_cod_empr = $idempresa and subo_cod_bode = $bodega ";
	$idsucursal = consulta_string_func($sql, 'subo_cod_sucu', $oIfx, 0);	

	$sql_tmp = '';
    if(!empty($producto)){
        $sql_tmp = " and (  prod_nom_prod like '%$producto%' or prod_cod_prod like '%$producto%' ) ";
    }
	
    //lectura sucia
	//////////////
	unset($array);
	unset($array2);
    $tabla = '';

    $sql = "select pr.prbo_cod_prod, p.prod_nom_prod, COALESCE(pr.prbo_dis_prod,0) as prbo_dis_prod, pr.prbo_cta_inv, pr.prbo_cta_ideb
                        from saeprbo pr, saeprod p where
                        p.prod_cod_prod     = pr.prbo_cod_prod and
                        p.prod_cod_empr     = $idempresa and
                        p.prod_cod_sucu     = $idsucursal and
                        pr.prbo_cod_empr    = $idempresa and
                        pr.prbo_cod_bode    = '$bodega'
                        $sql_tmp  order by  2 limit 100 ";
	//echo $sql;
	$i=1;
    if($oIfx->Query($sql)){
    	if($oIfx->NumFilas() > 0){
    		$sHtmlEstado = '';
    		do{

    			$prbo_cod_prod = $oIfx->f('prbo_cod_prod');
    			$prod_nom_prod = $oIfx->f('prod_nom_prod');
    			$prbo_dis_prod = $oIfx->f('prbo_dis_prod');
				
				$prod_nom_prod = str_replace("'", " ", $prod_nom_prod);
			    $prod_nom_prod = str_replace('"', " ", $prod_nom_prod);
                							    
				$img = '<div align=\"center\"> <div class=\"btn btn-warning btn-sm\" onclick=\"cargar_prod( \''.$prbo_cod_prod.'\',  \''.$prod_nom_prod.'\',  \''.$id.'\')\"><span class=\"glyphicon glyphicon-ok\"><span></div> </div>';
				
			    $tabla.='{
					  "i":"'.$i.'",
					  "codigo":"'.$prbo_cod_prod.'",
					  "producto":"'.$prod_nom_prod.'",
					  "stock":"'.$prbo_dis_prod.'",
					  "selecciona":"'.$img.'"
				},';
			 	
				$i++;
			}while($oIfx->SiguienteRegistro());
	    }
    }
	$oIfx->Free();

	//eliminamos la coma que sobra
	$tabla = substr($tabla,0, strlen($tabla) - 1);

	echo '{"data":['.$tabla.']}';
?>