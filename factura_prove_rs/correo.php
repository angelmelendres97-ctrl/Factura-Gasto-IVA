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
	
	if (isset($_REQUEST['cliente']))
        $cliente = $_REQUEST['cliente'];
    else
        $cliente = 0;

    if (isset($_REQUEST['sucursal']))
        $sucursal = $_REQUEST['sucursal'];
    else
        $sucursal = 10;

?>

<script type="text/javascript" src="dist/jquery-1.11.1.min.js"></script>
<script type="text/javascript" src="alerts/jquery.alerts.js"></script>

<link rel="stylesheet" type="text/css" href="alerts/jquery.alerts.css"/>

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
    <script type="text/javascript" src="media/js/jquery-1.10.2.js"></script>
    <script type="text/javascript" src="media/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="media/js/dataTables.bootstrap.min.js"></script>          
    <script type="text/javascript" src="media/js/bootstrap.js"></script>
    <script type="text/javascript" language="javascript" src="<?=$_COOKIE["JIREH_INCLUDE"]?>css/bootstrap-3.3.7-dist/js/bootstrap.min.js"></script>
    <script type="text/javascript" language="javascript" src="jsgantt.js"></script>
	<script type="text/javascript" src="media/js/lenguajeusuario_.js"></script>   


<script>

    function genera_formulario(){
        xajax_genera_formulario_correo(  'nuevo', xajax.getFormValues("form1"), '<?=$sucursal?>' );
    }
    

    function guardar() {
            xajax_guardar_correo(xajax.getFormValues("form1"), '<?=$cliente?>', '<?=$sucursal?>' );
            parent.cargar_lista_correo();
            parent.cerrar_ventana();
    }

    
    function cerrar_ventana() {
            CloseAjaxWin();
    }

</script>

<!--DIBUJA FORMULARIO FILTRO-->
<div align="center">
    <form id="form1" name="form1" action="javascript:void(null);">
        <table align="center" border="0" cellpadding="2" cellspacing="0" width="100%">
            <tr>
                <td valign="top" align="center">
                    <div id="divReembolso"></div>
                </td>
            </tr>
             <tr>
                <td valign="top" align="center">
                    <div id="divReembolsoDet"></div>
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