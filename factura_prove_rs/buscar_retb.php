<?
include_once('../../Include/config.inc.php');
include_once(path(DIR_INCLUDE).'conexiones/db_conexion.php');
include_once(path(DIR_INCLUDE).'comun.lib.php');

if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<link rel="stylesheet" type = "text/css" href="<?=$_COOKIE["JIREH_INCLUDE"]?>css/general.css">
    <link href="<?=$_COOKIE["JIREH_INCLUDE"]?>Clases/Formulario/Css/Formulario.css" rel="stylesheet" type="text/css"/>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>RETENCIONES</title>
<style type="text/css">
<!--
.Estilo1 {
	font-size: 12px;
	font-family: Georgia, "Times New Roman", Times, serif;
	color: #000000;
}
-->
</style>

<script>
	function datos( cod, det, porc, op ){
                if(op==0){
                    window.opener.document.form1.codigo_ret_fue_b.value = cod;
                    window.opener.document.form1.porc_ret_fue_b.value = porc;
                    var base = window.opener.document.form1.base_fue_b.value;
                    var resp = ((base * porc)/100).toFixed(2);
                    window.opener.document.form1.val_fue_b.value = resp;
                }else if(op==1){
                    window.opener.document.form1.codigo_ret_fue_s.value = cod;
                    window.opener.document.form1.porc_ret_fue_s.value = porc;
                    var base = window.opener.document.form1.base_fue_s.value;
                    var resp = ((base * porc)/100).toFixed(2);
                    window.opener.document.form1.val_fue_s.value = resp;
                }
		        window.opener.genera_comprobante();
                close();
	}
</script>
</head>

<body>

<?
        if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}
        
    $oIfx = new Dbo;
	$oIfx -> DSN = $DSN_Ifx;
	$oIfx -> Conectar();

    $oIfxA = new Dbo;
	$oIfxA -> DSN = $DSN_Ifx;
	$oIfxA -> Conectar();
	
	$idempresa =  $_SESSION['U_EMPRESA'];
    $cod = $_GET['cod'];
    $op = $_GET['op'];

    if(empty($cod)){
        $sql_tmp = '';
    }elseif(!empty($cod)){
        $sql_tmp = " and tret_cod like '$cod%' ";
    }

//	$codigo_busca = strtr(strtoupper($codigo), "àáâãäåæçèéêëìíîïðñòóôõöøùüú", "ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÜÚ");
	$sql = "select tret_cod, tret_det_ret, tret_porct,tret_cta_cre
                        from saetret where
                        tret_cod_empr = $idempresa and
                        tret_ban_retf = 'IR'  and
                        tret_ban_crdb = 'CR'
                        $sql_tmp
                         order by 1 ";
//      echo $sql;
?> 
</body>
<div id="contenido">
<?	
	$cont=1;
	echo '<table align="center" border="0" cellpadding="2" cellspacing="1" width="98%" style="border:#999999 1px solid">';
	echo '<tr><th colspan="7" align="center" class="titulopedido">RETENCIONES</th></tr>';
	echo '<tr>
    			<th align="left" bgcolor="#EBF0FA" class="titulopedido">ID</th>
    			<th align="left" bgcolor="#EBF0FA" class="titulopedido">CODIGO</th>
    			<th align="left" bgcolor="#EBF0FA" class="titulopedido">DETALLE</th>
                <th align="left" bgcolor="#EBF0FA" class="titulopedido">PORCENTAJE</th>
                <th align="left" bgcolor="#EBF0FA" class="titulopedido">CUENTA</th>
		  </tr>';

    if ($oIfx->Query($sql)){
        if( $oIfx->NumFilas() > 0 ){
		do {
			$codigo  = ($oIfx->f('tret_cod'));
			$detalle = htmlentities($oIfx->f('tret_det_ret'));
            $porc    = ($oIfx->f('tret_porct'));
            $cuen_cod= ($oIfx->f('tret_cta_cre'));
                        
                        if ($sClass=='off') $sClass='on'; else $sClass='off';
			echo '<tr height="20" class="'.$sClass.'"
                                        onMouseOver="javascript:this.className=\'link\';"
                                        onMouseOut="javascript:this.className=\''.$sClass.'\';">';
				echo '<td>'.$cont.'</td>';
				echo '<td width="100">';		
	?>
    				<a href="#" onclick="datos('<? echo $codigo;?>','<? echo $detalle;?>', '<? echo $porc ?>', '<? echo $op ?>' )">
					             <? echo $codigo;?></a>
    <?
				echo '</td>';
				echo '<td>'
	?>
    				<a href="#" onclick="datos('<? echo $codigo;?>','<? echo $detalle;?>', '<? echo $porc ?>', '<? echo $op ?>' )">
						     <? echo $detalle;?></a>
    <?
				echo '</td>';
                echo '<td>';
    ?>
                <a href="#" onclick="datos('<? echo $codigo;?>', '<? echo $detalle;?>', 
                                           '<? echo $porc ?>',   '<? echo $op ?>' )">
				<? echo $porc;?></a>
    <?
                echo '</td>';                               
                echo '<td>';
    ?>               
                <a href="#" onclick="datos('<? echo $codigo;?>', '<? echo $detalle;?>', 
                                           '<? echo $porc ?>',   '<? echo $op ?>' )">
                <? echo $cuen_cod;?></a>                
    <?			
                echo '</td>';
    			echo '</tr>';
    			echo '<tr>'; echo '</tr>'; 		echo '<tr>'; echo '</tr>'; 	
    			echo '<tr>'; echo '</tr>'; 		echo '<tr>'; echo '</tr>'; 	
		$cont++;
		}while($oIfx->SiguienteRegistro());
            }else{
                echo '<span class="fecha_letra">Sin Datos....</span>';
            }
	}
	$oIfx->Free();
	echo '<tr><td colspan="3">Se mostraron '.($cont-1).' Registros</td></tr>';
	echo '</table>';
	//echo $cod_producto;

        function fecha_mysql_func2($fecha){
        $fecha_array = explode('/',$fecha);
        $m = $fecha_array[0];
	$y = $fecha_array[2];
	$d = $fecha_array[1];

	return ( $y.'/'.$m.'/'.$d );
}
?>    
</div>
</html>

