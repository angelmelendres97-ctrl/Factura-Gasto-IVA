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
<title>Cuentas Contables</title>
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
	function datos(op, a, b, c){
		if(c == 1){
			window.opener.document.form1.cta_gasto_dist.value = a;
			window.opener.document.form1.cta_gasto_ndist.value = b;
			window.opener.document.form1.ccosn_gasto_dist.focus();
			close();
		}else{
			alert('La Cuenta Contable debe ser de movimiento..!');
		}
	}

</script>
</head>

<body>

<?
	$oIfx = new Dbo;
	$oIfx -> DSN = $DSN_Ifx;
	$oIfx -> Conectar();
	
	$empresa = $_SESSION['U_EMPRESA'];
        $opcion = $_GET['op'];
        $cod    = $_GET['cuenta'];
	$fecha = date("m-d-Y");

        if(empty($cod)){
            $sql_tmp = '';
        }elseif(!empty($cod)){
            $numero = str_replace('.', '', $cod);
            $is_numeric = is_numeric($numero);
            if($is_numeric==true){
                $sql_tmp = " and cuen_cod_cuen like '$cod%' ";
            }else{
                $sql_tmp = " and cuen_nom_cuen like UPPER('$cod%') ";
            }  
        }

        // cuenta inicial
        $sql = "select cuen_cod_cuen, cuen_nom_cuen, cuen_nom_ingl, cuen_mov_cuen 
				from saecuen where
				cuen_cod_empr = $empresa
				$sql_tmp
				order by cuen_cod_cuen ";
	
?> 
</body>
<div id="contenido">
<?	
	$cont=1;
	echo '<table align="center" border="0" cellpadding="2" cellspacing="1" width="98%" style="border:#999999 1px solid">';
	echo '<tr><th colspan="7" align="center" class="diagrama">Cuentas Contables</th></tr>';
	echo '<tr>
			<th align="center" class="diagrama">Cuenta</th>
			<th align="center" class="diagrama">Nombre - Espa&ntilde;ol</th>
			<th align="center" class="diagrama">Nombre - Ingles</th>
              </tr>';

    if ($oIfx->Query($sql)){
        if( $oIfx->NumFilas() > 0 ){
		do {
			$cod_cuenta = trim($oIfx->f('cuen_cod_cuen'));
			$nom_cuenta = htmlentities($oIfx->f('cuen_nom_cuen'));
			$nom_cuenta_ingles = htmlentities($oIfx->f('cuen_nom_ingl'));
			$cuen_mov_cuen = $oIfx->f('cuen_mov_cuen');

                        if ($sClass=='off') $sClass='on'; else $sClass='off';
                        echo '<tr height="20" class="'.$sClass.'"
                                    onMouseOver="javascript:this.className=\'link\';"
                                    onMouseOut="javascript:this.className=\''.$sClass.'\';">';
				echo '<td width="100">';		
	?>
    				<a href="#" onclick="datos(<? echo $opcion; ?>, '<? echo $cod_cuenta;?>', '<? echo $nom_cuenta;?>', '<? echo $cuen_mov_cuen;?>') ">
					             <? echo $cod_cuenta;?></a>
    <?
				echo '</td>';
				echo '<td>'
	?>
    				<a href="#" onclick="datos(<? echo $opcion; ?>, '<? echo $cod_cuenta;?>', '<? echo $nom_cuenta;?>', '<? echo $cuen_mov_cuen;?>') ">
						     <? echo $nom_cuenta;?></a>
    <?
				echo '</td>';
				echo '<td>';
	
	?>
                                <a href="#" onclick="datos(<? echo $opcion; ?>, '<? echo $cod_cuenta;?>', '<? echo $nom_cuenta;?>', '<? echo $cuen_mov_cuen;?>') ">
                                                    <? echo $nom_cuenta_ingles;?></a>
	
	<?			echo '</td>';
				
				
        ?>
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
?>    
</div>
</html>

