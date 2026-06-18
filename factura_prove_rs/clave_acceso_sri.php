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
<title>LISTA DE DESCRIPCION</title>
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
	function datos( a ){
			window.opener.document.form1.clave_acceso.value = a;
			close();
	}
</script>
</head>

<body>

<?
        if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

        $oIfx = new Dbo;
		$oIfx -> DSN = $DSN;
		$oIfx -> Conectar();

		$idempresa =  $_SESSION['U_EMPRESA'];
        $clave     = $_GET['clave'];
		$ruc       = $_GET['ruc'];

		$sql = "select c.id_clave, c.clave_acceso,  SUBSTRING(c.clave_acceso,11,13) as ruc,
						SUBSTRING(c.clave_acceso,25,6) as serie ,
						SUBSTRING(c.clave_acceso,31,9) as factura
						from clave_acceso c where
						c.empr_cod_empr = $idempresa and 
						SUBSTRING(c.clave_acceso,11,13) = '$ruc' and
						c.id_documento  = 1 ";
//        echo $sql;
?>
</body>
<div id="contenido">
<?
	$cont=1;
	echo '<table align="center" border="0" cellpadding="2" cellspacing="1" width="98%" style="border:#999999 1px solid">';
	echo '<tr><th colspan="7" align="center" class="titulopedido">CLAVE DE ACCESO</th></tr>';
	echo '<tr>
			<th align="left" bgcolor="#EBF0FA" class="titulopedido">ID</th>
			<th align="left" bgcolor="#EBF0FA" class="titulopedido">RUC</th>
			<th align="left" bgcolor="#EBF0FA" class="titulopedido">SERIE</th>
			<th align="left" bgcolor="#EBF0FA" class="titulopedido">FACTURA</th>
			<th align="left" bgcolor="#EBF0FA" class="titulopedido">CLAVE DE ACCESO</th>			
		  </tr>';

    if ($oIfx->Query($sql)){
        if( $oIfx->NumFilas() > 0 ){
		do {
						$codigo = ($oIfx->f('id_clave'));
                        $clave  = $oIfx->f('clave_acceso');
						$ruc    = $oIfx->f('ruc');
						$serie  = $oIfx->f('serie');
						$factura= $oIfx->f('factura');

                        if ($sClass=='off') $sClass='on'; else $sClass='off';
						echo '<tr height="20" class="'.$sClass.'"
                                        onMouseOver="javascript:this.className=\'link\';"
                                        onMouseOut="javascript:this.className=\''.$sClass.'\';">';
				echo '<td>'.$cont.'</td>';
				echo '<td>'.$ruc.'</td>';
				echo '<td>'.$serie.'</td>';
				echo '<td>'.$factura.'</td>';
				echo '<td width="100">';
	?>
    				<a href="#" onclick="datos('<? echo $clave;?>')">
					             <? echo $clave;?></a>
    <?
				echo '</td>';
				
	?>
    				
    <?
    ?>
                                
						     <? ?>
    <?
                                                               
    ?>
    <?
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
?>
</div>
</html>
