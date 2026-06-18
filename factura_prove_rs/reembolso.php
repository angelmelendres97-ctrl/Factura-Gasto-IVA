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
	
	if (isset($_REQUEST['ccli']))
        $ccli = $_REQUEST['ccli'];
    else
        $ccli = 0;

    if (isset($_REQUEST['total']))
        $total = $_REQUEST['total'];
    else
        $total = 10;
	
	if (isset($_REQUEST['detalle']))
        $detalle = $_REQUEST['detalle'];
    else
        $detalle = '';
	
    //echo $total;
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
        xajax_genera_formulario_reembolso(  'nuevo', xajax.getFormValues("form1"), '<?=$total?>' , '<?=$detalle?>' );
    }

    function agregar_reembolso(){
        if (ProcesarFormulario()==true){
            xajax_agrega_modifica_grid( 0, xajax.getFormValues("form1"), 0, <?=$total?>);
        }
    }

    function totales_reemb(cont) {
            xajax_totales_reemb(xajax.getFormValues("form1"));
    }

    function totales1_reemb(cont) {
            xajax_totales_reemb(xajax.getFormValues("form1"));
    }

    function cuenta_gasto_reemb(op, event) {
            if (event.keyCode == 115 || event.keyCode == 13) { // F4 O ENTER
                var cod = document.getElementById('cta_gasto_reemb').value;
                var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=730, height=380, top=255, left=130";
                var pagina = '../factura_prove/cuentas_gasto_reemb.php?sesionId=<?= session_id() ?>&mOp=true&mVer=false&cuenta=' + cod + '&op=' + op;
                window.open(pagina, "", opciones);
            }
    }

    function cargar_ccosn(op, event) {
            if (event.keyCode == 115 || event.keyCode == 13) { // F4 O ENTER
                var cod = document.getElementById('ccosn_gasto_reemb').value;
                var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=730, height=380, top=255, left=130";
                var pagina = '../factura_prove/ccosn_reemb.php?sesionId=<?= session_id() ?>&mOp=true&mVer=false&cuenta=' + cod + '&op=' + op;
                window.open(pagina, "", opciones);
            }
    }


    function cargar_fact_reemb(op) {
            xajax_cargar_fact_reemb( op, xajax.getFormValues("form1"));
    }

    function limpiar(){
            document.getElementById('ruc_reemb').value          = '';
            document.getElementById('clpv_remb').value          = '';
            document.getElementById('factura_reemb').value      = '';
            document.getElementById('serie_reemb').value        = '';
            document.getElementById('estab_reemb').value        = '';
            document.getElementById('auto_reemb').value         = '';
            document.getElementById('detalle_reemb').value      = '';
            document.getElementById('cta_gasto_reemb').value    = '';
            document.getElementById('ccosn_gasto_reemb').value  = '';
            document.getElementById('valor_grab12b_reemb').value= 0;
            document.getElementById('valor_grab0b_reemb').value = 0;
            document.getElementById('iceb_reemb').value         = 0;
            document.getElementById('ivab_reemb').value         = 0;
            document.getElementById('valor_grab12s_reemb').value= 0;
            document.getElementById('valor_grab0s_reemb').value = 0;
            document.getElementById('ices_reemb').value         = 0;
            document.getElementById('ivas_reemb').value         = 0;
            document.getElementById('totals_reemb').value       = 0;
            document.getElementById('valor_grab12t_reemb').value= 0;
            document.getElementById('valor_grab0t_reemb').value = 0;
            document.getElementById('icet_reemb').value         = 0;
            document.getElementById('ivat_reemb').value         = 0;
            document.getElementById('valor_noObjIva_reemb').value   = 0;
            document.getElementById('valor_exentoIva_reemb').value  = 0;
            document.getElementById('cta_gasto_nreemb').value    = '';
            document.getElementById('ccosn_gasto_nreemb').value  = '';
    }

	
	function validar_cedula_len(){		
		// xajax_validar_cedula_len(xajax.getFormValues("form1"));

        var bandera_return = false;
        var variable_url = '<?= $url_javascrip_ori ?>';
        var control_pais = '<?= $pais_codigo_ext ?>';

        var numero = document.getElementById('ruc_reemb').value;
        var tipo_identificacion = $("#iden_reemb").val();
        // var tipo_identificacion =$("input[name='identificacion']:checked").val();
        if (tipo_identificacion != '' && numero != '') {
            if (tipo_identificacion == '' || tipo_identificacion == undefined || tipo_identificacion == null) {
                alertSwal('Debe elegir un tipo de identificaci&oacute;n', 'warning');
                $("#ruc_reemb").val('');
            } else if (tipo_identificacion == '01' && control_pais == '593' || tipo_identificacion == '02' && control_pais == '593') {
                if (numero.length != 13 && tipo_identificacion == '01') {
                    alertSwal('El RUC debe contener 13 caracteres', 'warning');
                    $("#ruc_reemb").val('');
                } else if (numero.length != 10 && tipo_identificacion == '02') {
                    alertSwal('La c&eacute;dula debe contener 10 caracteres', 'warning');
                    $("#ruc_reemb").val('');
                } else {
                    var validacion_js = validarDocumentoEcuReturnBolean(tipo_identificacion, numero);
                    if (!validacion_js) {
                        // alertSwal('Numero de Identificacion Incorrecta', 'warning');
                        // $("#ruc").val('');
                    }
                }
            }

        } else {
            alertSwal('Debe elegir un tipo de identificaci&oacute;n', 'warning');
            $("#ruc_reemb").val('');
        }
    }
	

   function validar_cedula() {
        tipo   = document.getElementById('iden_reemb').value;
        numero = document.getElementById('ruc_reemb').value;

        if (tipo != '03' && tipo != '07') {

            var suma = 0;
            var residuo = 0;
            var pri = false;
            var pub = false;
            var nat = false;
            var numeroProvincias = 24;
            var modulo = 11;
            var extranjero = 30;

            /* Verifico que el campo no contenga letras */
            var ok = 1;
            for (i = 0; i < numero.length && ok == 1; i++) {
                var n = parseInt(numero.charAt(i));
                if (isNaN(n))
                    ok = 0;
            }
            if (ok == 0) {
                alert("No puede ingresar caracteres en el numero");
                document.getElementById('ruc_reemb').value = '';
                return false;
            }

            if (numero.length < 10) {
                alert('El numero ingresado no es válido');
                document.getElementById('ruc_reemb').value = '';
                return false;
            }

            /* Los primeros dos digitos corresponden al codigo de la provincia */
            provincia = numero.substr(0, 2);
            if (provincia < 1 || provincia > numeroProvincias && provincia != extranjero) {
                alert('El codigo de la provincia (dos primeros digitos) es invalido');
                document.getElementById('ruc_reemb').value = '';
                return false;
            }

            /* Aqui almacenamos los digitos de la cedula en variables. */
            d1 = numero.substr(0, 1);
            d2 = numero.substr(1, 1);
            d3 = numero.substr(2, 1);
            d4 = numero.substr(3, 1);
            d5 = numero.substr(4, 1);
            d6 = numero.substr(5, 1);
            d7 = numero.substr(6, 1);
            d8 = numero.substr(7, 1);
            d9 = numero.substr(8, 1);
            d10 = numero.substr(9, 1);

            /* El tercer digito es: */
            /* 9 para sociedades privadas y extranjeros   */
            /* 6 para sociedades publicas */
            /* menor que 6 (0,1,2,3,4,5) para personas naturales */

            if (d3 == 7 || d3 == 8) {
                alert('El tercer digito ingresado es invalido');
                document.getElementById('ruc_reemb').value = '';
                return false;
            }

            /* Solo para personas naturales (modulo 10) */
            if (d3 < 6) {
                nat = true;

                if (provincia == 30) {
                    p1 = d1 * 3;
                } else {
                    p1 = d1 * 2;
                }

                if (p1 >= 10)
                    p1 -= 9;
                p2 = d2 * 1;
                if (p2 >= 10)
                    p2 -= 9;
                p3 = d3 * 2;
                if (p3 >= 10)
                    p3 -= 9;
                p4 = d4 * 1;
                if (p4 >= 10)
                    p4 -= 9;
                p5 = d5 * 2;
                if (p5 >= 10)
                    p5 -= 9;
                p6 = d6 * 1;
                if (p6 >= 10)
                    p6 -= 9;
                p7 = d7 * 2;
                if (p7 >= 10)
                    p7 -= 9;
                p8 = d8 * 1;
                if (p8 >= 10)
                    p8 -= 9;
                p9 = d9 * 2;
                if (p9 >= 10)
                    p9 -= 9;
                modulo = 10;
            }

            /* Solo para sociedades publicas (modulo 11) */
            /* Aqui el digito verficador esta en la posicion 9, en las otras 2 en la pos. 10 */
            else if (d3 == 6) {
                pub = true;
                p1 = d1 * 3;
                p2 = d2 * 2;
                p3 = d3 * 7;
                p4 = d4 * 6;
                p5 = d5 * 5;
                p6 = d6 * 4;
                p7 = d7 * 3;
                p8 = d8 * 2;
                p9 = 0;
            }

            /* Solo para entidades privadas (modulo 11) */
            else if (d3 == 9) {
                pri = true;
                p1 = d1 * 4;
                p2 = d2 * 3;
                p3 = d3 * 2;
                p4 = d4 * 7;
                p5 = d5 * 6;
                p6 = d6 * 5;
                p7 = d7 * 4;
                p8 = d8 * 3;
                p9 = d9 * 2;
            }

            suma = p1 + p2 + p3 + p4 + p5 + p6 + p7 + p8 + p9;
            residuo = suma % modulo;

            /* Si residuo=0, dig.ver.=0, caso contrario 10 - residuo*/
            digitoVerificador = residuo == 0 ? 0 : modulo - residuo;

            /* ahora comparamos el elemento de la posicion 10 con el dig. ver.*/
            if (pub == true) {
                if (digitoVerificador != d9) {
                    alert('El ruc de la empresa del sector publico es incorrecto...');
                    document.getElementById('ruc_reemb').value = '';
                    return false;
                }
                /* El ruc de las empresas del sector publico terminan con 0001*/
                if (numero.substr(9, 4) != '0001') {
                    alert('El ruc de la empresa del sector publico debe terminar con 0001');
                    document.getElementById('ruc_reemb').value = '';
                    return false;
                }
            }
            else if (pri == true) {
                if (digitoVerificador != d10) {
                    alert('El ruc de la empresa del sector privado es incorrecto...');
                    document.getElementById('ruc_reemb').value = '';
                    return false;
                }
                if (numero.substr(10, 3) != '001') {
                    alert('El ruc de la empresa del sector privado debe terminar con 001');
                    document.getElementById('ruc_reemb').value = '';
                    return false;
                }
            }

            else if (nat == true) {
                if (digitoVerificador != d10) {
                    alert('El numero de cedula de la persona natural es incorrecto...');
                    document.getElementById('ruc_reemb').value = '';
                    return false;
                }
                if (numero.length > 10 && numero.substr(10, 3) != '001') {
                    alert('El ruc de la persona natural debe terminar con 001');
                    document.getElementById('ruc_reemb').value = '';
                    return false;
                }
            }
        }
        return true;
    }


	
	
	//TIPO DE FACTURA
	function cargar_factura_reemb() {
		var op = document.getElementById("tipo_factura_reemb").value;
		if(op != ''){
			var clpv = document.getElementById("clpv_remb").value;
			if(clpv != ''){
				xajax_tipo_factura_reemb(xajax.getFormValues("form1"));
			}else{
				alert('Elija Proveedor para Continuar...');
				document.getElementById("tipo_factura_reemb").value = '';
				document.getElementById("clpv_remb").focus();
			}
		}else{
			document.getElementById("divFactura_reemb").innerHTML = '';
		}
	}
		
		
		
	function clave_acceso_sri_reemb() {
		var clave = document.getElementById("clave_acceso").value;
		if(clave.length == 49){
			xajax_clave_acceso_reemb(xajax.getFormValues("form1"));
		}else{
			alert('Debe ingresar los 49 digitos de la clave de acceso');
		}
		
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
                    <div id="divReembolsoDet" style="width:99%; width:900px;  overflow: scroll;"></div>
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