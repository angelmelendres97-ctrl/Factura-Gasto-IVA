<?php	
	include_once('../../Include/config.inc.php');
	include_once(path(DIR_INCLUDE).'conexiones/db_conexion.php');
	include_once(path(DIR_INCLUDE).'comun.lib.php');

	if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}
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

	if (isset($_REQUEST['sucu']))		$idsucursal = $_REQUEST['sucu'];	else		$idsucursal = $_SESSION['U_SUCURSAL'];
	if (isset($_REQUEST['tipo']))		$tipo       = $_REQUEST['tipo'];	else		$tipo       = 0;
	if (isset($_REQUEST['fec_ini']))	$fec_ini    = fecha_informix_func($_REQUEST['fec_ini']);	else		$fec_ini    = '';
	if (isset($_REQUEST['fec_fin']))	$fec_fin    = fecha_informix_func($_REQUEST['fec_fin']);	else		$fec_fin    = '';
	if (isset($_REQUEST['anio']))		$anio	    = $_REQUEST['anio'];	else		$anio	    = '';
	if (isset($_REQUEST['mes']))		$mes		= $_REQUEST['mes'];		else		$mes        = '';
	if (isset($_REQUEST['estado']))		$estado		= $_REQUEST['estado'];	else		$estado     = '';

	$estado_tmp = explode(",", $estado);
	$sql_estado = '';
	if(count($estado_tmp)>0){
		$estado = "";
		$estado = "'".$estado_tmp[0]."','".$estado_tmp[1]."','".$estado_tmp[2]."'";
		$sql_estado .= " and pedf_est_fact in (  $estado ) ";
	}

    //varibales de sesion
	$idempresa  = $_SESSION['U_EMPRESA'];
	
	$sql_tmp = "";
	if(tipo=='f'){
		$sql_tmp = " and pedf_fech_fact between '$fec_ini' and '$fec_fin' ";
	}elseif(tipo=='m'){
		$sql_tmp = " and pedf_cod_ejer = '$anio' and pedf_num_prdo =  '$mes' ";
	}elseif(tipo=='a'){
		$sql_tmp = " and pedf_cod_ejer = '$anio' ";
	}

    //lectura sucia
    //////////////
	
	$sql = "select sucu_cod_sucu, sucu_nom_sucu from saesucu where sucu_cod_empr = $idempresa ";
	unset($array_sucu);     
	$array_sucu  = array_dato($oIfx, $sql, 'sucu_cod_sucu', 'sucu_nom_sucu');

	// bdega
	$sql = "SELECT bode_cod_bode, bode_nom_bode FROM saebode  WHERE 
					bode_cod_empr = $idempresa Order by 2";
	unset($array_bode);   
	$array_bode = array_dato($oIfx, $sql, 'bode_cod_bode', 'bode_nom_bode');

    $tabla = '';
	$sql = "select pedf_cod_pedf, pedf_cod_clpv, 
					pedf_fech_fact, pedf_num_preimp, 
					pedf_nom_cliente, pedf_tlf_cliente,
					pedf_est_fact, pedf_ruc_clie, pedf_dir_clie,
					pedf_cm1_pedf, pedf_hor_fin, 
					pedf_iva, pedf_dsg_valo, 
					pedf_fle_fact, pedf_otr_fact, 
					pedf_tot_fact
					from saepedf where
					pedf_cod_empr = $idempresa and
					pedf_cod_sucu = $idsucursal 
					$sql_estado
					$sql_tmp
					order by pedf_fech_fact, pedf_hor_fin, pedf_nom_cliente  ";
	$i = 1;
    if($oIfx->Query($sql)){
    	if($oIfx->NumFilas() > 0){
    		$sHtmlEstado = '';
    		do{
				$pedf_cod_pedf   = $oIfx->f('pedf_cod_pedf');
				$pedf_cod_clpv   = $oIfx->f('pedf_cod_clpv');
				$pedf_fech_fact  = fecha_mysql_func($oIfx->f('pedf_fech_fact'));
				$pedf_num_preimp = $oIfx->f('pedf_num_preimp');
				$pedf_nom_cliente= $oIfx->f('pedf_nom_cliente');
				$pedf_tlf_cliente= $oIfx->f('pedf_tlf_cliente');
				$pedf_est_fact   = $oIfx->f('pedf_est_fact');
				$pedf_ruc_clie   = $oIfx->f('pedf_ruc_clie');
				$pedf_dir_clie   = $oIfx->f('pedf_dir_clie');
				$pedf_cm1_pedf   = $oIfx->f('pedf_cm1_pedf');
				$pedf_hor_fin    = $oIfx->f('pedf_hor_fin');
				$pedf_tot_fact   = $oIfx->f('pedf_iva') - $oIfx->f('pedf_dsg_valo') + $oIfx->f('pedf_fle_fact') + $oIfx->f('pedf_otr_fact') + $oIfx->f('pedf_tot_fact');

				$det = ''; $anu = ''; $fact = ''; $mapa = '';
				if($pedf_est_fact=='PE'){
					$det   = '<div align=\"center\"> <div title=\"Detalle Pedido\"   class=\"btn btn-warning btn-sm\" onclick=\"seleccionaItem(\'' . $pedf_cod_pedf . '\', \'' . $pedf_ruc_clie . '\' , \'' . $pedf_nom_cliente . '\'  , \'' . $pedf_num_preimp . '\'  )\"><span class=\"glyphicon glyphicon-list\" ><span></div> </div>';
					$anu   = '<div align=\"center\"> <div title=\"Anula Pedido\"     class=\"btn btn-danger btn-sm\" onclick=\"elimina(\'' . $pedf_cod_pedf . '\', \'' . $idsucursal . '\' , \'' . $idempresa . '\'  )\"><span class=\"glyphicon glyphicon-remove\"  ><span></div> </div>';
					$fact  = '<div align=\"center\"> <div title=\"Factura Pedido\"   class=\"btn btn-success btn-sm\" onclick=\"factura(\'' . $pedf_cod_pedf . '\', \'' . $idsucursal . '\' , \'' . $idempresa . '\' )\"><span class=\"glyphicon glyphicon-check\"   ><span></div> </div>';
					$mapa  = '<div align=\"center\"> <div title=\"Ubicacion Pedido\" class=\"btn btn-primary btn-sm\" onclick=\"mapa(\'' . $pedf_cod_pedf . '\', \'' . $idsucursal . '\' , \'' . $idempresa . '\' )\"><span class=\"glyphicon glyphicon-map-marker\" ><span></div> </div>';
				}elseif($pedf_est_fact=='GR'){
					$det   = '<div align=\"center\"> <div title=\"Detalle Pedido\"   class=\"btn btn-warning btn-sm\" onclick=\"seleccionaItem(\'' . $pedf_cod_pedf . '\', \'' . $pedf_ruc_clie . '\' , \'' . $pedf_nom_cliente . '\'  , \'' . $pedf_num_preimp . '\'  )\"><span class=\"glyphicon glyphicon-list\" ><span></div> </div>';					
					$mapa  = '<div align=\"center\"> <div title=\"Ubicacion Pedido\" class=\"btn btn-primary btn-sm\" onclick=\"mapa(\'' . $pedf_cod_pedf . '\', \'' . $idsucursal . '\' , \'' . $idempresa . '\' )\"><span class=\"glyphicon glyphicon-map-marker\" ><span></div> </div>';
				}elseif($pedf_est_fact=='AN'){
					$det   = '<div align=\"center\"> <div title=\"Detalle Pedido\"   class=\"btn btn-warning btn-sm\" onclick=\"seleccionaItem(\'' . $pedf_cod_pedf . '\', \'' . $pedf_ruc_clie . '\' , \'' . $pedf_nom_cliente . '\'  , \'' . $pedf_num_preimp . '\'  )\"><span class=\"glyphicon glyphicon-list\" ><span></div> </div>';					
					$mapa  = '<div align=\"center\"> <div title=\"Ubicacion Pedido\" class=\"btn btn-primary btn-sm\" onclick=\"mapa(\'' . $pedf_cod_pedf . '\', \'' . $idsucursal . '\' , \'' . $idempresa . '\' )\"><span class=\"glyphicon glyphicon-map-marker\" ><span></div> </div>';
				}
				
				// \'' . $fpag_cod_fpag . '\', \'' . $fpag_cod_sucu . '\', \'' . $fpag_cod_cuen . '\', \'' . $fpag_sig_fpag . '\', 	\'' . $fpag_des_fpag . '\', \'' . $fpag_det_fpag . '\',  \'' . $fpag_cot_fpag . '\'
    			$tabla.='{
						  "i":"'.$i.'",
						  "fecha":"'.$pedf_fech_fact.' '.$pedf_hor_fin.'",
						  "pedido":"'.$pedf_num_preimp.'",	
						  "estado":"'.$pedf_est_fact.'",
						  "clpv":"'.$pedf_nom_cliente.'",							  
						  "ruc":"'.$pedf_ruc_clie.'",		
						  "dir":"'.$pedf_dir_clie.'",	
						  "tel":"'.$pedf_tlf_cliente.'",	
						  "msn":"'.$pedf_cm1_pedf.'",
						  "total":"'.$pedf_tot_fact.'",
						  "det":"'.$det.'",
						  "anu":"'.$anu.'",
						  "fact":"'.$fact.'",
						  "mapa":"'.$mapa.'"
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