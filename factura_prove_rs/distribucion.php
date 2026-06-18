<? /********************************************************************/ ?>
<? /* NO MODIFICAR ESTA SECCION*/ ?>
<? include_once('../_Modulo.inc.php');?>
<? include_once(HEADER_MODULO);?>
<? if ($ejecuta) { ?>
    <? /********************************************************************/ ?>
<?
    if (isset($_REQUEST['id']))
        $id = $_REQUEST['id'];
    else
        $id = '';

    if (isset($_REQUEST['total']))
        $total = $_REQUEST['total'];
    else
        $total = 0;

    if (isset($_REQUEST['sucursal']))
        $sucursal = $_REQUEST['sucursal'];
    else
        $sucursal = 0;

	if (isset($_REQUEST['cuenta']))
        $cuenta = $_REQUEST['cuenta'];
    else
        $cuenta = 0;
    echo $cuenta;
?>

	<!--CSS--> 
    <link rel="stylesheet" type="text/css" href="<?=$_COOKIE["JIREH_INCLUDE"]?>css/bootstrap-3.3.7-dist/css/bootstrap.min.css" media="screen" />
    <link type="text/css" href="css/style.css" rel="stylesheet"/>
    <link type="text/css" href="datetime/jquery.datetimepicker.css" rel="stylesheet"/>
    <link rel="stylesheet" type="text/css" href="js/jquery/plugins/simpleTree/style.css" />
    <link rel="stylesheet" type="text/css" href="jsgantt.css"/>
    <link rel="stylesheet" href="media/css/bootstrap.css">
    <link rel="stylesheet" href="media/css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="media/font-awesome/css/font-awesome.css">

    <!--Javascript--> 
    <script type="text/javascript" src="js/jquery.js"></script>
    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="datetime/jquery.js"></script>
    <script type="text/javascript" src="datetime/jquery.datetimepicker.js"></script>    
    <script src="media/js/jquery-1.10.2.js"></script>
    <script src="media/js/jquery.dataTables.min.js"></script>
    <script src="media/js/dataTables.bootstrap.min.js"></script>          
    <script src="media/js/bootstrap.js"></script>
    <script type="text/javascript" language="javascript" src="<?=$_COOKIE["JIREH_INCLUDE"]?>css/bootstrap-3.3.7-dist/js/bootstrap.min.js"></script>
    <script type="text/javascript" language="javascript" src="jsgantt.js"></script>
	<script src="media/js/lenguajeusuario_.js"></script>   


<script>

    function genera_formulario(){
        xajax_genera_formulario_distribucion(  'nuevo', xajax.getFormValues("form1"), '<?=$total?>' );
    }

	
    function agregar_dist(){
        //if (ProcesarFormulario()==true){
		//	valor_dist = document.getElementById('valor_dist').value;
		//	if(valor_dist > 0){
				xajax_agrega_modifica_grid_dist( 0, xajax.getFormValues("form1"), 0, '<?=$total?>' , '<?=$cuenta?>' );
				parent.genera_comprobante();
				parent.cerrar_ventana();
		//	}else{
			//	alert('Ingrese un valor mayor a cero..!');
		//	}
        //}
    }
	
	function agregar_dist_tecla(event){
		if (event.keyCode == 13) { // F4 O ENTER
			if (ProcesarFormulario()==true){
				valor_dist = document.getElementById('valor_dist').value;
				if(valor_dist > 0){
					xajax_agrega_modifica_grid_dist( 0, xajax.getFormValues("form1"), 0, '<?=$total?>');
					parent.genera_comprobante();
				}else{
					alert('Ingrese un valor mayor a cero..!');
				}
			}
		}
	}

    function total_dist() {
            xajax_totales_dist( '<?=$total?>', xajax.getFormValues("form1"));
    }

    function cuenta_gasto_dist(op, event, tipo) {
            if (event.keyCode == 115) { // F4 O ENTER
                var cod = document.getElementById('cta_gasto_dist').value;
                var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=900, height=500, top=255, left=130";
                var pagina = '../factura_prove/buscar_cuentas_dist.php?sesionId=<?= session_id() ?>&mOp=true&mVer=false&cuenta=' + cod + '&op=' + op + '&tipo=' + tipo;
                window.open(pagina, "", opciones);
            }
    }

    function cargar_ccosn(op, event) {
            if (event.keyCode == 115 || event.keyCode == 13) { // F4 O ENTER
                var cod = document.getElementById('ccosn_gasto_dist').value;
                var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=730, height=380, top=255, left=130";
                var pagina = '../factura_prove/ccosn_dist.php?sesionId=<?= session_id() ?>&mOp=true&mVer=false&cuenta=' + cod + '&op=' + op;
                window.open(pagina, "", opciones);
            }
    }


    function limpiar(){
            document.getElementById('cta_gasto_dist').value    = '';
            document.getElementById('cta_gasto_ndist').value   = '';
            document.getElementById('ccosn_gasto_dist').value  = '';
            document.getElementById('ccosn_gasto_ndist').value = '';
			document.getElementById('detalleDistribucion').value ='';
            document.getElementById('valor_dist').value        = 0;
    }
	
	function selectCuentaContable(id, op, tipo){
		xajax_selectCuentaContable(xajax.getFormValues("form1"), id, op, tipo);
	}

	function cargar_datos_ccosn(){
		xajax_distribucion_ccosn( xajax.getFormValues("form1"),  '<?=$total?>');			
    }
	
	
	function cargar_datos_ccosnV(){
		xajax_distribucion_ccosnV( xajax.getFormValues("form1"),  '<?=$total?>');			
    }
	
	
</script>

<!--DIBUJA FORMULARIO FILTRO-->
<div align="center">
    <form id="form1" name="form1" action="javascript:void(null);">
        <table align="center" border="0" cellpadding="2" cellspacing="0" width="100%">
            <tr>
                <td valign="top" align="center">
                    <div id="divDistribucion"></div>
                </td>
            </tr>
             <tr>
                <td valign="top" align="center">
                    <div id="divDistribucionDet"></div>
                </td>
            </tr>
        </table>
    </form>
</div>
<div id="divGrid" ></div>
<script>genera_formulario();/*genera_detalle();genera_form_detalle();*/</script>
    <? /********************************************************************/ ?>
    <? /* NO MODIFICAR ESTA SECCION*/ ?>
<? } ?>
<? include_once(FOOTER_MODULO); ?>
<? /********************************************************************/ ?>