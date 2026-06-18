<? /* * ***************************************************************** */ ?>
<? /* NO MODIFICAR ESTA SECCION */ ?>
<? include_once('../_Modulo.inc.php'); ?>
<? include_once(HEADER_MODULO); ?>
<? if (isset($_REQUEST['codclpv'])) $codclpv = $_REQUEST['codclpv'];
else $codclpv = 0; ?>
<? if ($ejecuta) { ?>
    <? /*     * ***************************************************************** */ ?>

    <!--Bootstrap-->
    <link rel="stylesheet" type="text/css" href="<?= $_COOKIE["JIREH_INCLUDE"] ?>css/bootstrap-3.3.7-dist/css/bootstrap.min.css" media="screen" />
    <!-- Bootstrap 3.3.7 -->
    <link rel="stylesheet" href="<?= $_COOKIE["JIREH_COMPONENTES"] ?>bower_components/bootstrap/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?= $_COOKIE["JIREH_COMPONENTES"] ?>bower_components/font-awesome/css/font-awesome.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="<?= $_COOKIE["JIREH_COMPONENTES"] ?>bower_components/Ionicons/css/ionicons.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="<?= $_COOKIE["JIREH_COMPONENTES"] ?>dist/css/AdminLTE.min.css">
    <!-- AdminLTE Skins. Choose a skin from the css/skinsfolder instead of downloading all of them to reduce the load. -->
    <link rel="stylesheet" href="<?= $_COOKIE["JIREH_COMPONENTES"] ?>dist/css/skins/_all-skins.min.css">




    <!--Bootstrap-->
    <script type="text/javascript" language="JavaScript" src="<?= $_COOKIE["JIREH_INCLUDE"] ?>css/bootstrap-3.3.7-dist/js/bootstrap.min.js"></script>
    <!-- AdminLTE App -->
    <script src="<?= $_COOKIE["JIREH_COMPONENTES"] ?>dist/js/adminlte.min.js"></script>

    <script src="media/js/lenguajeusuario_.js"></script>


    <!--Javascript-->
    <script type="text/javascript" src="js/jquery.js"></script>
    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script src="media/js/jquery-1.10.2.js"></script>
    <script src="media/js/jquery.dataTables.min.js"></script>
    <script src="media/js/dataTables.bootstrap.min.js"></script>
    <script src="media/js/bootstrap.js"></script>
    <script type="text/javascript" language="javascript" src="<?= $_COOKIE["JIREH_INCLUDE"] ?>css/bootstrap-3.3.7-dist/js/bootstrap.min.js"></script>
    <script src="media/js/lenguajeusuario_producto.js"></script>




    <?php
    unset($_SESSION['GESTION_IMPORTACION']);
    unset($_SESSION['TIPO_FGASTO']);
    unset($_SESSION['claveAccesoExterno']);

    if (isset($_GET['importacion_modulos']) && isset($_GET['tipo_fgasto'])) {
        $importacion_modulos = $_GET['importacion_modulos'];
        $tipo_fgasto = $_GET['tipo_fgasto'];
        $_SESSION['GESTION_IMPORTACION'] = $importacion_modulos;
        $_SESSION['TIPO_FGASTO'] = $tipo_fgasto;
    }

    if (isset($_GET['clave_acceso'])) {
        $clave_acceso = $_GET['clave_acceso'];
        $_SESSION['claveAccesoExterno'] = $clave_acceso;
    }
    ?>



    <script>
        function refreshTablaIn() {
            parent.consultar();
            CloseAjaxWin();
        }

        function init_table(table) {

            var table = $('#' + table).DataTable({
                scrollY: '50vh',
                scrollX: true,
                scrollCollapse: true,
                paging: true,
                dom: 'Bfrtip',
                processing: "<i class='fa fa-spinner fa-spin' style='font-size:24px; color: #34495e;'></i>",
                "language": {
                    "search": "<i class='fa fa-search'></i>",
                    "searchPlaceholder": "Buscar",
                    'paginate': {
                        'previous': 'Anterior',
                        'next': 'Siguiente'
                    },
                    "zeroRecords": "No se encontro datos",
                    "info": "Mostrando _START_ a _END_ de  _TOTAL_ Total",
                    "infoEmpty": "",
                    "infoFiltered": "(Mostrando _MAX_ Registros Totales)",
                },
                "paging": false,
                "ordering": true,
                "info": true,
            });
            table.buttons().remove();
            table.search(search).draw();
        }

        function cheque() {


            var empr = 1;
            var sucu = document.getElementById('sucursal').value;
            var cliente = document.getElementById('cliente').value;
            var clpv = document.getElementById('cliente').value;


            empr = '1';
            sucu = '1';
            valor = '1';
            detalle = '1';
            moneda = '1';
            coti = '1';
            coti_ext = '1';
            fecha = '1';
            valor = '1';

            var array = [empr, sucu, clpv, valor, detalle, moneda, coti, coti_ext, cliente];
            AjaxWin('<?= $_COOKIE["JIREH_INCLUDE"] ?>', '../comprob_egreso/cheque.php?sesionId=<?= session_id() ?>&mOp=true&mVer=false&fecha=' + fecha + '&array=' + array, 'DetalleShow', 'iframe', 'CHEQUE', '800', '300', '10', '10', '1', '1');

        }


        function anadir_cuenta_aplicada() {
            xajax_asignar_cuenta();
        }

        function asignar_valor(val, id, eti) {
            xajax_asignar_valor_fact(val, id, eti, xajax.getFormValues("form1"));
        }

        function validar_val(id, val) {
            var valingresado = document.getElementById(id).value;
            if (valingresado > val) {

                alert('El valor ingresado es mayor al del total');
                document.getElementById(id).value = val;
            }
        }

        function abre_modal2() {
            $("#mostrarmodal2").modal("show");
        }

        function cargar_oc_det_gen(serial, empresa, sucursal) {
            $("#ModalOrdenDet").modal("show");
            xajax_orden_compra_reporte_det(serial, empresa, sucursal, xajax.getFormValues("form1"));
        }

        function orden_compra_consulta() {
            $("#ModalOrden").modal("show");
            xajax_orden_compra_reporte(xajax.getFormValues("form1"));
        }

        function genera_formulario() {
            document.getElementById('cod_oc').value = '';
            xajax_genera_formulario_pedido(<?php echo $codclpv ?>);
        }

        function autocompletar(empresa, event) {
            if (event.keyCode == 13 || event.keyCode == 115) { // F4
                var cliente_nom = document.getElementById('proveedor').value;
                var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=1100, height=500, top=255, left=130";
                var pagina = '../factura_prove_rs/buscar_cliente.php?sesionId=<?= session_id() ?>&mOp=true&mVer=false&cliente=' + cliente_nom;
                window.open(pagina, "", opciones);
            }
        }

        function autocompletarBtn() {
            var cliente_nom = '';
            var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=1100, height=500, top=255, left=130";
            var pagina = '../factura_prove_rs/buscar_cliente.php?sesionId=<?= session_id() ?>&mOp=true&mVer=false&cliente=' + cliente_nom;
            window.open(pagina, "", opciones);
        }


        function cargarDatosClpv(id, tipo) {
            xajax_cargarDatosClpv(xajax.getFormValues("form1"), id, tipo);

        }

        function cargar_ccos(id) {
            document.getElementById("ccosn").value = id;
            //xajax_cargarCcos(id);
        }

        function apareceOculta() {
            var clpv = document.getElementById("cliente").value;
            if (clpv != '') {
                document.getElementById("verInfoClpv").style.visibility = 'visible';
                $('#verInfoClpv').toggle();
            }
        }

        function cargarSustento(tipo, clv_con_clpv) {
            xajax_cargarSustento(xajax.getFormValues("form1"), tipo, clv_con_clpv);
        }

        function eliminar_lista_sustento() {
            var sel = document.getElementById("sust_trib");
            for (var i = (sel.length - 1); i >= 1; i--) {
                aBorrar = sel.options[i];
                aBorrar.parentNode.removeChild(aBorrar);
            }
        }

        function anadir_elemento_sustento(x, i, elemento) {
            var lista = document.form1.sust_trib;
            var option = new Option(elemento, i);
            lista.options[x] = option;
        }

        function cargarTipoComprobante(tipo) {
            xajax_cargarTipoComprobante(xajax.getFormValues("form1"), tipo);
        }

        function eliminar_lista_comprobante() {
            var sel = document.getElementById("comprobante");
            for (var i = (sel.length - 1); i >= 1; i--) {
                aBorrar = sel.options[i];
                aBorrar.parentNode.removeChild(aBorrar);
            }
        }

        function anadir_elemento_comprobante(x, i, elemento) {
            var lista = document.form1.comprobante;
            var option = new Option(elemento, i);
            lista.options[x] = option;
        }

        function cargar_secuencial() {
            var sucursal = document.getElementById("sucursal").value;
            xajax_genera_formulario_pedido(sucursal, 'nuevo', xajax.getFormValues("form1"));
        }

        function guardar_pedido() {
            if (ProcesarFormulario() == true) {
                var ctrl = document.getElementById("ctrl").value;
                var debitoLocal = document.getElementById("debitoLocal").value;
                var creditoLocal = document.getElementById("creditoLocal").value;
                var debitoExterior = document.getElementById("debitoExterior").value;
                var creditoExterior = document.getElementById("creditoExterior").value;

                if (debitoLocal < 1 && debitoLocal > -1 && creditoLocal < 1 && creditoLocal > -1 && debitoExterior < 1 && debitoExterior > -1 && creditoExterior < 1 && creditoExterior > -1) {
                    if (ctrl == 1) {
                        let tipo_factura = $("#tipo_factura").val();
                        let validFecha = true;
                        if (tipo_factura == 2) {
                            let fecha_validez = $("#fecha_validez").val();
                            let fecha_emision = $("#fecha_emision").val();

                            //alert(fecha_validez);
                            //alert(fecha_emision);
                            let f1 = new Date(fecha_emision);
                            let f2 = new Date(fecha_validez);
                            if (f2 < f1) {
                                validFecha = false;
                            }
                        }
                        if (validFecha) {
                            document.getElementById("ctrl").value = 2;
                            jsShowWindowLoad();
                            xajax_guarda_pedido(<?php echo $codclpv ?>, xajax.getFormValues("form1"));
                        } else {
                            Swal.fire({
                                type: 'error',
                                width: '40%',
                                title: 'Opps',
                                text: 'Fecha de Caducidad no puede ser inferior a la Fecha Actual'
                            })
                        }
                    } else {
                        var codigo = document.getElementById("fprv_cod_fprv").value;
                        var cont = codigo.length;
                        if (cont > 0) {
                            var mensaje = "Error La Factura - Gasto ya esta Ingresado";
                            var tipo = "info";
                            alerts(mensaje, tipo);

                        } else {
                            var mensaje = "Por favor espere Guardando Informacion";
                            var tipo = "info";
                            alerts(mensaje, tipo);

                        } // fin if
                    }
                } else {
                    var mensaje = "Error: El asiento esta descuadrado";
                    var tipo = "info";
                    alerts(mensaje, tipo);
                }


            }
        }

        function vista_previa() {
            var empresa = <?= $_SESSION['U_EMPRESA']; ?>;
            var sucursal = document.getElementById("sucursal").value;
            var cod_prove = document.getElementById("cliente").value;
            var asto_cod = document.getElementById("asto_cod").value;
            var ejer_cod = document.getElementById("ejer_cod").value;
            var prdo_cod = document.getElementById("prdo_cod").value;
            var tipo = document.getElementById("tipo_doc").value;

            //  alert(asto_cod);
            xajax_genera_pdf_doc(empresa, sucursal, asto_cod, ejer_cod, prdo_cod);
        }

        function liqCompras() {
            var empresa = <?= $_SESSION['U_EMPRESA']; ?>;
            var sucursal = document.getElementById("sucursal").value;
            var cod_prove = document.getElementById("cliente").value;
            var asto_cod = document.getElementById("asto_cod").value;
            var ejer_cod = document.getElementById("ejer_cod").value;
            var prdo_cod = document.getElementById("prdo_cod").value;
            var tipo = document.getElementById("tipo_doc").value;

            xajax_liqCompras(empresa, sucursal, asto_cod, ejer_cod, prdo_cod);
        }

        function cancelar_pedido() {
            confirmar = confirm("Deseas Guardar los cambios..?");
            if (confirmar) {
                guardar_pedido();
            } else {
                genera_formulario();
            }
        }


        function cancelar_actualizacion() {
            confirmar = confirm("Seguro Deseas Salir..?");
            if (confirmar) {
                genera_formulario();
            } else {
                cargar_datos_pedido();
            }
        }



        function cerrar_ventana() {
            CloseAjaxWin();
        }

        function cerrarModalord() {
            $("#miModal").html("");
            $("#miModal").modal("hide");
        }

        function focus_ruc() {
            var ruc = document.getElementById("ruc");
            ruc.focus();
            var value = ruc.value;
            ruc.value = "";
            ruc.value = value;
        }

        function tecla_ruc(event) {
            // F4 115
            // ENTER 13
            if (event.keyCode == 13 || event.keyCode == 115) {
                var sucursal = document.getElementById("sucursal").value;
                xajax_genera_formulario_pedido(sucursal, 'cargar_ruc', xajax.getFormValues("form1"));
            }
        }

        function cargar_tran() {
            xajax_cargar_tran(xajax.getFormValues("form1"));
        }

        function anadir_elemento_comun(x, i, elemento) {
            var lista = document.form1.placa;
            var option = new Option(elemento, i);
            lista.options[x] = option;
        }

        function limpiar_lista() {
            document.getElementById("placa").options.length = 0;
        }

        function autocompletar_producto(empresa, event, op) {
            if (event.keyCode == 115 || event.keyCode == 13) { // F4 O ENTER
                var prod_nom = document.getElementById('producto').value;
                var cod_nom = document.getElementById('codigo_producto').value;
                var sucu = document.getElementById('sucursal').value;
                var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=730, height=380, top=255, left=130";
                var pagina = '../factura_prove_rs/buscar_prod.php?sesionId=<?= session_id() ?>&mOp=true&mVer=false&producto=' + prod_nom + '&codigo=' + cod_nom + '&opcion=' + op + '&sucursal=' + sucu;
                window.open(pagina, "", opciones);
            }
        }


        function foco(idElemento) {
            document.getElementById(idElemento).focus();
        }

        function cod_ret_b(op, event, tipo) {
            if (event.keyCode == 115 || event.keyCode == 13) { // F4 O ENTER
                if (op == 0) {
                    var cod = document.getElementById('codigo_ret_fue_b').value;
                } else if (op == 1) {
                    var cod = document.getElementById('codigo_ret_fue_s').value;
                }

                xajax_cod_ret_b(op, cod, xajax.getFormValues("form1"), tipo);
            }
        }


        function ventana_cod_ret_b(op, tipo) {
            if (op == 0) {
                var cod = document.getElementById('codigo_ret_fue_b').value;
            } else if (op == 1) {
                var cod = document.getElementById('codigo_ret_fue_s').value;
            }

            var miInput = document.getElementById("tipo_retencion_ad");
            if (miInput !== null) {
                // El input existe
                var tipo_retencion_ad = document.getElementById('tipo_retencion_ad').value;
            } else {
                // El input NO existe
                console.log('El input NO existe');
                var tipo_retencion_ad = 'R';
            }

            var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=900, height=450, top=255, left=130";
            var pagina = '../factura_prove_rs/buscar_tret.php?sesionId=<?= session_id() ?>&mOp=true&mVer=false&cod=' + cod + '&op=' + op + '&tipo=' + tipo + '&tipo_retencion_ad=' + tipo_retencion_ad;
            window.open(pagina, "", opciones);
        }


        function cod_ret_iva(op, event, tipo) {
            if (event.keyCode == 115 || event.keyCode == 13) { // F4 O ENTER
                if (op == 0) {
                    var cod = document.getElementById('codigo_ret_iva_b').value;
                } else if (op == 1) {
                    var cod = document.getElementById('codigo_ret_iva_s').value;
                }

                xajax_cod_ret_iva(op, cod, xajax.getFormValues("form1"), tipo);
            }
        }



        function ventana_cod_ret_iva(op, tipo) {
            if (op == 0) {
                var cod = document.getElementById('codigo_ret_iva_b').value;
            } else if (op == 1) {
                var cod = document.getElementById('codigo_ret_iva_s').value;
            }

            var miInput = document.getElementById("tipo_retencion_ad");
            if (miInput !== null) {
                // El input existe
                var tipo_retencion_ad = document.getElementById('tipo_retencion_ad').value;
            } else {
                // El input NO existe
                console.log('El input NO existe');
                var tipo_retencion_ad = 'R';
            }

            var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=900, height=450, top=255, left=130";
            var pagina = '../factura_prove_rs/buscar_tret.php?sesionId=<?= session_id() ?>&mOp=true&mVer=false&cod=' + cod + '&op=' + op + '&tipo=' + tipo + '&tipo_retencion_ad=' + tipo_retencion_ad;
            window.open(pagina, "", opciones);
        }



        function cuenta_fisc(op, event) {
            if (event.keyCode == 115 || event.keyCode == 13) { // F4 O ENTER
                if (op == 0) {
                    var cod = document.getElementById('fisc_b').value;
                } else if (op == 1) {
                    var cod = document.getElementById('fisc_s').value;
                }
                xajax_cuenta_fisc(op, cod, xajax.getFormValues("form1"));
            }
        }

        function ventana_cuenta_fisc(op) {
            if (op == 0) {
                var cod = document.getElementById('fisc_b').value;
            } else if (op == 1) {
                var cod = document.getElementById('fisc_s').value;
            }
            var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=900, height=500, top=255, left=130";
            var pagina = '../factura_prove_rs/buscar_cuentas.php?sesionId=<?= session_id() ?>&mOp=true&mVer=false&cuenta=' + cod + '&op=' + op + '&tipo=' + 0;
            window.open(pagina, "", opciones);
        }



        function cuenta_gasto(op, event) {
            if (event.keyCode == 115 || event.keyCode == 13) { // F4 O ENTER
                if (op == 0) {
                    var cod = document.getElementById('gasto1').value;
                } else if (op == 1) {
                    var cod = document.getElementById('gasto2').value;
                }

                xajax_cuenta_gasto(op, cod, xajax.getFormValues("form1"));
            }
        }


        function ventana_cuenta_gasto(op) {
            if (op == 0) {
                var cod = document.getElementById('gasto1').value;
            } else if (op == 1) {
                var cod = document.getElementById('gasto2').value;
            }
            var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=900, height=500, top=255, left=130";
            var pagina = '../factura_prove_rs/buscar_cuentas.php?sesionId=<?= session_id() ?>&mOp=true&mVer=false&cuenta=' + cod + '&op=' + op + '&tipo=' + 1;
            window.open(pagina, "", opciones);
        }



        function totales(cont) {

            var a = cont.value;
            var ab = document.getElementById('fisc_b');
            var tipo_factura = document.getElementById('tipo_factura').value;

            // var op_ = document.getElementById('fisc_s');
            if (a > 0) {
                //ab.disabled = false;
            } else {
                // ab.disabled = true;
            }
            if (tipo_factura != 0) {
                var factura = document.getElementById('factura').value;
                //alert(factura);
                if (factura != '') {
                    xajax_totales(xajax.getFormValues("form1"));
                } else {
                    var mensaje = "Debe de ingresar un numero de factura";
                    var tipo = "info";
                    alerts(mensaje, tipo);

                }

            } else {
                var mensaje = "Debe de seleccionar un tipo de factura";
                var tipo = "info";
                alerts(mensaje, tipo);

            }

            //genera_comprobante();
        }

        function totales1(cont) {

            var a = cont.value;
            // var ab = document.getElementById('fisc_b');
            var op_ = document.getElementById('fisc_s');
            if (a > 0) {
                //op_.disabled = false;
            } else {
                //op_.disabled = true;
            }
            xajax_totales(xajax.getFormValues("form1"));
            //genera_comprobante();
        }

        function abrir_xml(xml) {
            var pagina = document.location = "descarga.php?xml=" + xml;
            location.href = pagina;
        }

        function num_digito(op) {
            xajax_num_digito(op, xajax.getFormValues("form1"));
        }

        function num_rete() {
            xajax_num_rete(xajax.getFormValues("form1"));
        }

        //FUNCIONES SRI 
        function firmar(nombre_archivo, clave_acceso, ruc, id_docu, correo, clpv, fact, ejer, asto, fec) {
            xajax_firmar(nombre_archivo, clave_acceso, ruc, id_docu, correo, clpv, fact, ejer, asto, fec);
        }

        function validaAutoriza(nombre_archivo, clave_acceso, id_docu, correo, clpv, fact, ejer, asto, fec) {
            //   alert("asdasdasdasd");
            xajax_validaAutoriza(nombre_archivo, clave_acceso, id_docu, correo, clpv, fact, ejer, asto, fec);
        }

        function update_comprobante(numeroAutorizacion, fechaAutorizacion, id_docu) {
            //alert("asdasdasdasdasdasd");
            xajax_update_comprobante(numeroAutorizacion, fechaAutorizacion, id_docu);
        }

        function autorizaComprobante(clave_acceso, id_docu, correo, clpv, fact, ejer, asto, fec) {
            //  alert("asdasdasdasd");
            xajax_autorizaComprobante(clave_acceso, id_docu, correo, clpv, fact, ejer, asto, fec);
        }


        //TIPO DE FACTURA
        function cargar_factura($tipo) {
            var op = document.getElementById("tipo_factura").value;
            if ($tipo == '') {
                document.getElementById("comprobante").value = $tipo;
            }
            var comprobante = document.getElementById("comprobante").value;
            //alert(comprobante);
            if (op != '') {
                var clpv = document.getElementById("cliente").value;
                if (clpv != '') {
                    if (comprobante != '0') {
                        xajax_tipo_factura(xajax.getFormValues("form1"));
                    } else {
                        var mensaje = "Debe de selecionar un comprobante";
                        var tipo = "info";
                        alerts(mensaje, tipo);
                        document.getElementById("tipo_factura").value = '0';
                    }

                } else {
                    var mensaje = "Elija Proveedor para Continuar";
                    var tipo = "info";
                    alerts(mensaje, tipo);

                    document.getElementById("tipo_factura").value = '';
                    document.getElementById("cliente_nombre").focus();
                }
            } else {
                document.getElementById("divFactura").innerHTML = '';
            }
        }

        function cargar_fecha_fact() {
            var fecha_emision = document.getElementById('fecha_emision').value
            //document.getElementById('fecha_contable').value = fecha_emision
            var plazo = document.getElementById('plazo').value;
            if (!plazo) {
                document.getElementById('plazo').value = 0;
            }
            calculaFechaVence();
            cargar_coti();
        }

        //TIPO DE FACTURA
        function validar_fact() {
            xajax_validar_factura(xajax.getFormValues("form1"));
        }

        function generar_pdf() {
            if (ProcesarFormulario() == true) {
                var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=730, height=.370, top=255, left=130";
                var pagina = '../../Include/documento_pdf3.php?sesionId=<?= session_id() ?>';
                //         var pagina = '../pedido/vista_previa.php?sesionId=<?= session_id() ?>&codigo='+codigo;
                //AjaxWin('<?= $_COOKIE["JIREH_INCLUDE"] ?>', '/documento_pdf.php?sesionId=<?= session_id() ?>&mOp=true&mVer=false, 'DetalleShow', 'iframe', 'Pedidos', '590', '200', '10', '10', '1', '1');
                window.open(pagina, "", opciones);
            }
        }

        function cargar_lista_correo() {
            xajax_cargar_lista_correo(xajax.getFormValues("form1"));
        }

        function eliminar_lista_correo() {
            // alert("asd");
            var sel = document.getElementById("proveedor_email");
            for (var i = (sel.length - 1); i >= 1; i--) {
                aBorrar = sel.options[i];
                aBorrar.parentNode.removeChild(aBorrar);
            }
        }

        function anadir_elemento_correo(x, i, elemento) {
            var lista = document.form1.proveedor_email;
            var option = new Option(elemento, i);
            lista.options[x] = option;
        }

        function cargarPlanillas() {
            xajax_cargarPlanillas(xajax.getFormValues("form1"));
        }

        function eliminar_lista_planilla() {
            var sel = document.getElementById("planilla");
            for (var i = (sel.length - 1); i >= 1; i--) {
                aBorrar = sel.options[i];
                aBorrar.parentNode.removeChild(aBorrar);
            }
        }

        function anadir_elemento_planilla(x, i, elemento) {
            var lista = document.form1.planilla;
            var option = new Option(elemento, i);
            lista.options[x] = option;
        }

        function cargarActividades() {
            xajax_cargarActividades(xajax.getFormValues("form1"));
        }

        function eliminar_lista_actividad() {
            var sel = document.getElementById("actividad");
            for (var i = (sel.length - 1); i >= 1; i--) {
                aBorrar = sel.options[i];
                aBorrar.parentNode.removeChild(aBorrar);
            }
        }

        function anadir_elemento_actividad(x, i, elemento) {
            var lista = document.form1.actividad;
            var option = new Option(elemento, i);
            lista.options[x] = option;
        }


        function validaAutorizacion(tipo, opcion) {
            var auto_prove = document.getElementById('auto_prove').value;
            var tamano = auto_prove.length;
            switch (tipo) {
                case 'electronica':
                    <?php
                    $u_pais_dig_autoe = '0';
                    if ($_SESSION['U_PAIS_DIG_AUTOE']) {
                        $u_pais_dig_autoe = $_SESSION['U_PAIS_DIG_AUTOE'];
                    }
                    ?>
                    var num_dig = <?php echo $u_pais_dig_autoe; ?>;
                    if (opcion == 'escribir' && (tamano > num_dig)) {
                        var mensaje = 'LA LONGITUD MAXIMA ES ' + num_dig + ' DIGITOS';
                        var tipo = "info";
                        alerts(mensaje, tipo);
                        document.getElementById('auto_prove').value = auto_prove.substring(0, num_dig);
                    } else if (opcion == 'enfoque' && tamano < num_dig) {
                        var mensaje = 'LA LONGITUD MAXIMA ES ' + num_dig + ' DIGITOS';
                        var tipo = "info";
                        alerts(mensaje, tipo);

                        document.getElementById('auto_prove').value = '';
                        document.getElementById('auto_prove').focus();
                    }
                    break;
                case 'impresa':
                    <?php
                    $u_pais_dig_autop = '0';
                    if ($_SESSION['U_PAIS_DIG_AUTOP']) {
                        $u_pais_dig_autop = $_SESSION['U_PAIS_DIG_AUTOP'];
                    }
                    ?>
                    var num_dig = <?php echo $u_pais_dig_autop; ?>;
                    if (opcion == 'escribir' && tamano > num_dig) {
                        var mensaje = 'LA LONGITUD MAXIMA ES ' + num_dig + ' DIGITOS';
                        var tipo = "info";
                        alerts(mensaje, tipo);
                        document.getElementById('auto_prove').value = auto_prove.substring(0, num_dig);
                    } else if (opcion == 'enfoque' && tamano < num_dig) {
                        var mensaje = 'LA LONGITUD MAXIMA ES ' + num_dig + ' DIGITOS';
                        var tipo = "info";
                        alerts(mensaje, tipo);

                        document.getElementById('auto_prove').value = '';
                        document.getElementById('auto_prove').focus();
                    }
                    break
            }

        }

        function validaSerie(opcion, tipo_fact) {
            var serie = document.getElementById('serie').value;
            var tamano = serie.length;

            if (tipo_fact == 1) {
                <?php
                $u_pais_dig_sere = '0';
                if ($_SESSION['U_PAIS_DIG_SERE']) {
                    $u_pais_dig_sere = $_SESSION['U_PAIS_DIG_SERE'];
                }
                ?>
                //electronico
                var num_dig = <?php echo $u_pais_dig_sere; ?>;
            } else {
                <?php
                $u_pais_dig_serp = '0';
                if ($_SESSION['U_PAIS_DIG_SERP']) {
                    $u_pais_dig_serp = $_SESSION['U_PAIS_DIG_SERP'];
                }
                ?>
                // preimpresa
                var num_dig = <?php echo $u_pais_dig_serp; ?>;
            }

            if (opcion == 'escribir' && tamano > num_dig) {
                var mensaje = 'LA LONGITUD MAXIMA ES ' + num_dig + ' DIGITOS';
                var tipo = "info";
                alerts(mensaje, tipo);

                document.getElementById('serie').value = serie.substring(0, num_dig);
                document.getElementById('serie').focus();
            } else if (opcion == 'enfoque' && tamano < num_dig) {
                var mensaje = 'LA LONGITUD MAXIMA ES ' + num_dig + ' DIGITOS';
                var tipo = "info";
                alerts(mensaje, tipo);

                document.getElementById('serie').value = '';
                document.getElementById('serie').focus();
            }
        }

        function validaFactura(tipo, opcion) {
            var factura = document.getElementById('factura').value;
            var tamano = factura.length;
            switch (tipo) {
                case 'electronica':
                    <?php
                    $u_pais_dig_face = '0';
                    if ($_SESSION['U_PAIS_DIG_FACE']) {
                        $u_pais_dig_face = $_SESSION['U_PAIS_DIG_FACE'];
                    }
                    ?>
                    var num_dig = <?php echo $u_pais_dig_face; ?>;
                    if (opcion == 'escribir' && tamano > num_dig) {
                        var mensaje = 'LA LONGITUD MAXIMA ES ' + num_dig + ' DIGITOS (ELECTRONICA)';
                        var tipo = "info";
                        alerts(mensaje, tipo);

                        document.getElementById('factura').value = factura.substring(0, num_dig);
                    } else if (opcion == 'enfoque' && tamano < num_dig)
                        num_digito();
                    break;
                case 'impresa':
                    <?php
                    $u_pais_dig_facp = '0';
                    if ($_SESSION['U_PAIS_DIG_FACP']) {
                        $u_pais_dig_facp = $_SESSION['U_PAIS_DIG_FACP'];
                    }
                    ?>
                    var num_dig = <?php echo $u_pais_dig_facp; ?>;
                    if (opcion == 'escribir' && tamano > num_dig) {
                        var mensaje = 'LA LONGITUD MAXIMA ES ' + num_dig + ' DIGITOS (IMPRESA)';
                        var tipo = "info";
                        alerts(mensaje, tipo);

                        document.getElementById('factura').value = factura.substring(0, num_dig);
                    } else if (opcion == 'enfoque' && tamano < num_dig) {
                        // Adrian
                        num_digito();
                        validar_fact();
                    }
                    break
            }
        }

        function reporte_retencionGasto() {
            //alert("Por favor primero guarde la factura");
            var fprv_cod_fprv = document.getElementById('fprv_cod_fprv').value;
            if (fprv_cod_fprv != '') {
                xajax_reporte(xajax.getFormValues("form1"));
            } else {
                var mensaje = "Por favor primero guarde la Factura";
                var tipo = "info";
                alerts(mensaje, tipo);

            }
        }

        //retencion preimpresa
        function reporteRetencion() {
            var cliente = document.getElementById("cliente").value;
            var factura = document.getElementById("factura").value;
            if (factura != '') {
                xajax_genera_documento(xajax.getFormValues("form1"));
            } else {
                var mensaje = "Por favor ingrese factura para generar vista previa";
                var tipo = "info";
                alerts(mensaje, tipo);

            }
        }

        function generar_pdf() {
            if (ProcesarFormulario() == true) {
                var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=730, height=.370, top=255, left=130";
                var pagina = '../../Include/documento_pdf3.php?sesionId=<?= session_id() ?>';
                window.open(pagina, "", opciones);
            }
        }

        function cargar_centroCostos3() {
            xajax_centroCostos3(xajax.getFormValues("form1"));
        }

        function cargar_centroCostos4() {
            xajax_centroCostos4(xajax.getFormValues("form1"));
        }

        function check_pago() {
            var op = document.getElementById('tipo_pago').value;
            if (op == 01) {
                document.getElementById("regimen_fiscal").checked = false;
                document.getElementById("doble_tributac").checked = false;
                document.getElementById("pago_sujeto_ret").checked = false;
                document.getElementById("regimen_fiscal").disabled = true;
                document.getElementById("doble_tributac").disabled = true;
                document.getElementById("pago_sujeto_ret").disabled = true;
            } else {
                document.getElementById("regimen_fiscal").disabled = false;
                document.getElementById("doble_tributac").disabled = false;
                document.getElementById("pago_sujeto_ret").disabled = false;
            }
        }

        function buscar_clave() {
            AjaxWin('<?= $_COOKIE["JIREH_INCLUDE"] ?>', '../factura_prove_rs/clave_acceso.php?sesionId=<?= session_id() ?>&mOp=true&mVer=false&cliente=', 'DetalleShow', 'iframe', 'Clave Acceso', '500', '120', '0', '0', '0', '0');
        }

        function redireccionar() {
            var url = "https://declaraciones.sri.gob.ec/tuportal-internet/";
            window.open(url, '_blank');
            //location.href=pagina
        }

        function clave_acceso_sri(tipo) {
            if (tipo == 1) {
                var clave = document.getElementById("clave_acceso_").value;

            } else {
                var clave = document.getElementById("clave_acceso").value;

            }
            if (clave.length == 49) {

                xajax_clave_acceso(xajax.getFormValues("form1"), tipo);

            } else {
                var mensaje = "Debe ingresar los 49 digitos de la clave de acceso";
                var tipo = "info";
                alerts(mensaje, tipo);

            }

        }


        function tot_compen() {
            var comp2 = parseFloat(document.getElementById("comp_2").value);
            var comp3 = parseFloat(document.getElementById("comp_3").value);
            var comp4 = parseFloat(document.getElementById("comp_4").value);
            var tot = 0;
            tot = comp2 + comp3 + comp4;
            document.getElementById("total_comp").value = tot;
        }

        function clave_acceso_buscar(empresa, event) {
            //alert(event.keyCode);
            if (event.keyCode == 13 || event.keyCode == 115) { // F4
                var clave = document.getElementById('clave_acceso').value;
                var ruc = document.getElementById('ruc').value;

                var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=730, height=380, top=255, left=130";
                var pagina = '../factura_prove_rs/clave_acceso_sri.php?sesionId=<?= session_id() ?>&mOp=true&mVer=false&clave=' + clave + '&ruc=' + ruc;
                window.open(pagina, "", opciones);
            }
        }



        function auto_ccosn(empresa, event, op, tipo) {
            if (event.keyCode == 13 || event.keyCode == 115) { // F4
                if (tipo == 1) {
                    var ccosn = document.getElementById('ccosn_3').value;
                    var cod_ccosn = document.getElementById('cod_ccosn_3').value;
                } else if (tipo == 2) {
                    var ccosn = document.getElementById('ccosn_4').value;
                    var cod_ccosn = document.getElementById('cod_ccosn_4').value;
                }

                var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=730, height=380, top=255, left=130";
                var pagina = '../factura_prove_rs/centro_costo.php?sesionId=<?= session_id() ?>&mOp=true&mVer=false&ccosn=' + ccosn + '&cod_ccosn=' + cod_ccosn + '&opcion=' + op + '&tipo=' + tipo;
                window.open(pagina, "", opciones);
            }
        }


        function cargar_reemb() {
            xajax_cargar_reemb(xajax.getFormValues("form1"));
        }


        function reembolso() {
            var total = document.getElementById("totals").value;
            var detalle = document.getElementById("detalle").value;

            if (total > 0) {
                AjaxWin('<?= $_COOKIE["JIREH_INCLUDE"] ?>', '../factura_prove_rs/reembolso.php?sesionId=<?= session_id() ?>&mOp=true&mVer=false&total=' + total + '&detalle=' + detalle, 'DetalleShow', 'iframe', 'Reembolso', '950', '500', '0', '0', '0', '0');
            } else {
                var mensaje = "Por favor Ingrese Datos del Proveedor";
                var tipo = "info";
                alerts(mensaje, tipo);

            }
        }

        function correo() {
            var cliente = document.getElementById("cliente").value;
            var sucursal = document.getElementById("sucursal").value;

            AjaxWin('<?= $_COOKIE["JIREH_INCLUDE"] ?>', '../factura_prove_rs/correo.php?sesionId=<?= session_id() ?>&mOp=true&mVer=false&cliente=' + cliente + '&sucursal=' + sucursal, 'DetalleShow', 'iframe', 'Correo', '450', '400', '0', '0', '0', '0');
        }


        function cargar_electronica() {
            xajax_cargar_electronica(xajax.getFormValues("form1"));
        }


        function automatico() {

            //document.getElementById("num_rete").disabled = true;
            document.form1.num_rete.disabled = true;
        }

        function manual() {
            document.getElementById("num_rete").disabled = false;
            document.getElementById("serie_rete").disabled = false;
            //document.getElementById("num_rete").focus(); 
        }


        function distribuir() {
            var sucursal = document.getElementById("sucursal").value;
            var valor = document.getElementById("val_gasto1").value;
            var cta = document.getElementById("gasto1").value;

            if (valor > 0) {
                AjaxWin('<?= $_COOKIE["JIREH_INCLUDE"] ?>', '../factura_prove_rs/distribucion.php?sesionId=<?= session_id() ?>&mOp=true&mVer=false&total=' + valor + '&sucursal=' + sucursal + '&cuenta=' + cta, 'DetalleShow', 'iframe', 'Distribucion', '800', '500', '0', '0', '0', '0');
            } else {
                var mensaje = "Por favor Ingrese Valores a la Factura";
                var tipo = "info";
                alerts(mensaje, tipo);

            }
        }


        function genera_comprobante() {
            //xajax_genera_comprobante( xajax.getFormValues("form1") );
            xajax_agrega_modifica_grid_dir_ori(0, xajax.getFormValues("form1"));
        }

        function agrega_modifica_grid_dir_ori() {
            xajax_agrega_modifica_grid_dir_ori(0, xajax.getFormValues("form1"));
        }

        function agregarArchivo() {
            xajax_agrega_modifica_gridAdj(0, xajax.getFormValues("form1"), '', '');
        }

        function cargarValoresPlanillaClpv() {
            xajax_cargarValoresPlanillaClpv(xajax.getFormValues("form1"));
        }

        function cargarPlanillaClpv() {
            var cliente = document.getElementById("cliente").value;
            if (cliente != '') {
                xajax_cargarPlanillaClpv(xajax.getFormValues("form1"));
            } else {
                var mensaje = "Seleccione Proveedor para continuar";
                var tipo = "info";
                alerts(mensaje, tipo);

            }
        }

        function cargar_stotal(id) {
            xajax_cargar_stotal(id, xajax.getFormValues("form1"));
        }

        function ordenCompra() {
            var id = document.getElementById("cliente").value;
            if (id != '') {
                document.getElementById("miModal").innerHTML = '';
                $("#miModal").modal("show");
                xajax_ordenCompra(xajax.getFormValues("form1"));
            } else {
                var mensaje = "Seleccione Proveedor para continuar";
                var tipo = "info";
                alerts(mensaje, tipo);

            }
        }

        function archivosAdjuntos() {
            var id = document.getElementById("cliente").value;
            if (id != '') {
                document.getElementById("miModal").innerHTML = '';
                $("#miModal").modal("show");
                xajax_archivosAdjuntos(xajax.getFormValues("form1"));
            } else {
                var mensaje = "Seleccione Proveedor para continuar";
                var tipo = "info";
                alerts(mensaje, tipo);

            }
        }

        function calculaFechaVence() {
            xajax_calculaFechaVence(xajax.getFormValues("form1"));
        }

        function selectCuentaContable(id, op, tipo) {
            xajax_selectCuentaContable(xajax.getFormValues("form1"), id, op, tipo);
            genera_comprobante();
        }

        function selectTret(id, op, tipo) {
            xajax_selectTret(xajax.getFormValues("form1"), id, op, tipo);
            if (id == 327) {

                document.getElementById("tr_divi1").style.display = '';
                document.getElementById("tr_divi2").style.display = '';
            } else {
                document.getElementById("tr_divi1").style.display = 'none';
                document.getElementById("tr_divi2").style.display = 'none';
            }
        }

        function muestra_divi() {
            document.getElementById("tr_divi1").style.display = '';
            document.getElementById("tr_divi2").style.display = '';
        }

        function forma_pago() {
            xajax_forma_pago_form(xajax.getFormValues("form1"));
        }

        function abre_modal() {
            $("#mostrarmodal").modal("show");
        }


        function cargar_fp() {
            xajax_agrega_modifica_gridFp(0, xajax.getFormValues("form1"));
            genera_comprobante();
        }

        function activo_fijo() {
            AjaxWin('<?= $_COOKIE["JIREH_INCLUDE"] ?>', '../activos_ficha/activo_fijo.php?sesionId=<?= session_id() ?>', 'DetalleShow', 'iframe', '  ACTIVO FIJO', '1300', '400', '10', '10', '1', '1');
        }

        function vista_previa_rete() {
            var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=730, height=380, top=255, left=130";
            var pagina = '../contabilidad_comprobante/vista_previa_rete.php?sesionId=<?= session_id() ?>';
            window.open(pagina, "", opciones);
        }


        /******** DIRECTORIO - RETENCION - DIARIO  */
        function anadir_ret() {
            xajax_agrega_modifica_grid_ret(0, xajax.getFormValues("form1"));
        }

        function cod_retencion(empresa, event) {
            if (event.keyCode == 13 || event.keyCode == 115) { // F4
                var codret = '';
                codret = document.getElementById('cod_ret').value;
                clpv_cod = document.getElementById('cliente').value;

                var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=730, height=380, top=255, left=130";
                var pagina = '../factura_prove_rs/buscar_codret.php?sesionId=<?= session_id() ?>&mOp=true&mVer=false&codret=' + codret + '&empresa=' + empresa + '&clpv_cod=' + clpv_cod;
                window.open(pagina, "", opciones);
            }
        }


        function cargar_coti() {
            // fecha en formato Y-m-d
            var fecha_contable = document.getElementById('fecha_contable').value;
            var mes = fecha_contable.substring(5, 7);

            var fecha_actual = new Date();
            var mes_actual = fecha_actual.getMonth() + 1;

            if (mes != mes_actual) {
                alert('ADVERTENCIA: La fecha contable no se esta ingresando en el mes actual. ');
            }

            xajax_cargar_coti(xajax.getFormValues("form1"));
        }



        function auto_dasi(empresa, event, op) {
            if (event.keyCode == 13 || event.keyCode == 115) { // F4
                if (op == 0) {
                    var nom = document.getElementById('nom_cta').value;
                } else {
                    var cod = document.getElementById('cod_cta').value;
                }
                var nom = document.getElementById('nom_cta').value;
                var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=730, height=380, top=255, left=130";
                var pagina = '../comprob_egreso/buscar_cuentas.php?sesionId=<?= session_id() ?>&mOp=true&mVer=false&cuenta=' + nom + '&empresa=' + empresa + '&op=' + op + '&codigo=' + cod;
                window.open(pagina, "", opciones);
            }
        }

        function anadir_dasi() {
            xajax_agrega_modifica_grid_dia(0, xajax.getFormValues("form1"));
        }

        function centro_costo_cuen(id) {
            //FACTURA SOLCITUD DE PAGOS

            var codpgs = <?php echo $codclpv ?>;

            //alert(codpgs);
            if (codpgs == '') {
                if (id == 'S') {
                    document.getElementById('ccosn').value = '';
                    document.getElementById('ccosn').disabled = false;
                } else if (id == 'N') {
                    document.getElementById('ccosn').value = '';
                    document.getElementById('ccosn').disabled = true;
                }

            }

        }


        function centro_actividad(id) {
            if (id == 'S') {
                document.getElementById('actividad').value = '';
                document.getElementById('actividad').disabled = false;
            } else if (id == 'N') {
                document.getElementById('actividad').value = '';
                document.getElementById('actividad').disabled = true;
            }
        }


        function modificar_valor(id, empresa, sucursal) {
            xajax_form_modificar_valor(id, empresa, sucursal, xajax.getFormValues("form1"));
        }

        function abre_modal_modi() {
            //alert('asa');
            $("#mostrarmodal5").modal("show");
        }

        function procesar(id, opcion) {
            xajax_modificar_valor(id, opcion, xajax.getFormValues("form1"));
            $("#mostrarmodal5").modal("hide");
        }



        function autocompletar_clpv_dir(empresa, event, op) {
            if (event.keyCode == 13 || event.keyCode == 115) { // F4
                var cliente_nom = document.getElementById('clpv_nom').value;
                var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=730, height=380, top=255, left=130";
                var pagina = '../factura_prove_rs/buscar_cliente_dir.php?sesionId=<?= session_id() ?>&mOp=true&mVer=false&cliente=' + cliente_nom + '&empresa=' + empresa + '&op=' + op;
                window.open(pagina, "", opciones);
            }
        }



        function cargar_lista_tran(op) {
            xajax_cargar_lista_tran(xajax.getFormValues("form1"), op);
        }


        function eliminar_lista_tran() {
            var sel = document.getElementById("tran");
            for (var i = (sel.length - 1); i >= 1; i--) {
                aBorrar = sel.options[i];
                aBorrar.parentNode.removeChild(aBorrar);
            }
        }

        function anadir_elemento_tran(x, i, elemento) {
            var lista = document.form1.tran;
            var option = new Option(elemento, i);
            lista.options[x] = option;
        }


        function cargar_lista_subcliente() {
            xajax_cargar_lista_subcliente(xajax.getFormValues("form1"));
        }


        function eliminar_lista_subcliente() {
            var sel = document.getElementById("ccli");
            for (var i = (sel.length - 1); i >= 1; i--) {
                aBorrar = sel.options[i];
                aBorrar.parentNode.removeChild(aBorrar);
            }
        }

        function anadir_elemento_subcliente(x, i, elemento) {
            var lista = document.form1.ccli;
            var option = new Option(elemento, i);
            lista.options[x] = option;
        }


        function anadir_dir() {
            var tran = document.getElementById('tran').value;
            if (tran.length > 0) {
                xajax_agrega_modifica_grid_dir_ori_add(0, xajax.getFormValues("form1"));
            } else {

                var mensaje = "Por favor seleccione Transaccion";
                var tipo = "info";
                alerts(mensaje, tipo);

            }
        }


        // DISTRIBUCION DE CUENTAS 
        function anadir_dasi_distri() {
            var cuen = document.getElementById('cod_cta').value;
            if (cuen.length > 0) {
                xajax_form_distri(xajax.getFormValues("form1"));
            } else {
                alert('!!!! Por favor Ingrese Cuenca Contable....');
            }
        }

        function abre_modal_distri() {
            //alert('asa');
            $("#mostrarmodal6").modal("show");
        }

        function procesar_distri(id, opcion) {
            xajax_procesar_distri(id, opcion, xajax.getFormValues("form1"));
            $("#mostrarmodal6").modal("hide");
        }


        function cargar_datos_ccosn() {
            var total = document.getElementById('val_cta').value;
            xajax_distribucion_ccosn(xajax.getFormValues("form1"), total);
        }


        function cargar_datos_ccosnV() {
            var total = document.getElementById('val_cta').value;
            xajax_distribucion_ccosnV(xajax.getFormValues("form1"), total);
        }

        function agregar_dist() {
            xajax_procesar_distri(xajax.getFormValues("form1"));
        }

        function fecha_final_rs(fec) {
            document.form1.fecha_vence.value = fec;
        }

        function total_diario() {
            xajax_total_diario(xajax.getFormValues("form1"));
        }

        function detalle_grid_() {
            var detalle = document.getElementById("detalle").value;
            var factura = document.getElementById("factura").value;

            document.getElementById("det_dir").value = detalle + '-' + factura;
            document.getElementById("detalla_diario").value = detalle + '-' + factura;
        }

        /// crear porveedor
        function crear_prove() {
            window.open('../ficha_proveedor/ficha_proveedor.php?sesionId=<?= session_id() ?>', '_blank');

        }
        //alertas
        function alerts(mensaje, tipo) {
            if (tipo == 'success') {
                Swal.fire({
                    type: tipo,
                    title: mensaje,
                    showCancelButton: false,
                    showConfirmButton: false,
                    timer: 2000,
                    width: '600',
                })
            } else {
                Swal.fire({
                    type: tipo,
                    title: mensaje,

                    showCancelButton: false,
                    showConfirmButton: true,
                    width: '600',

                })
            }

        }


        function totales_inciva(cont) {
            xajax_totales_inciva(xajax.getFormValues("form1"));

        }

        function recalculo() {
            xajax_totales(xajax.getFormValues("form1"));
        }


        function imp_gaso() {
            var pais_imp_comb = document.getElementById("pais_imp_comb").value;
            if (pais_imp_comb > 0) {
                document.getElementById("ga1").style.display = "block";
                document.getElementById("ga2").style.display = "block";
            } else {
                document.getElementById("ga1").style.display = "none";
                document.getElementById("ga2").style.display = "none";
            }

        }


        function cargarDatosOP(id) {
            xajax_cargarDatosOP(id, xajax.getFormValues("form1"));
        }

        // LIST DESPEGABLES
        function eliminar_lista(componente) {
            var sel = document.getElementById(componente);
            for (var i = (sel.length - 1); i >= 1; i--) {
                aBorrar = sel.options[i];
                aBorrar.parentNode.removeChild(aBorrar);
            }
        }

        function anadir_elemento(x, i, elemento, componente) {
            var lista = document.getElementById(componente);
            var option = new Option(elemento, i);
            lista.options[x] = option;
        }

        function listaBeneficiarios() {
            var id = document.getElementById("cliente").value;
            if (id != '') {
                $("#miModal").html("");
                $("#miModal").modal("show");
                xajax_listaBeneficiarios(xajax.getFormValues("form1"));
            } else {
                alert('Seleccione Proveedor para continuar..');
            }
        }

        function autocompletarBenf(event) {
            if (event.keyCode == 13 || event.keyCode == 115) { // F4
                var cliente_nom = document.getElementById('beneficiarioProy').value;
                var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=900, height=400, top=255, left=130";
                var pagina = '../factura_prove_rs/buscar_cliente_1.php?sesionId=<?= session_id() ?>&mOp=true&mVer=false&cliente=' + cliente_nom;
                window.open(pagina, "", opciones);
            }
        }

        function agregarArchivo_1() {
            xajax_agrega_modifica_gridAdj_1(0, xajax.getFormValues("form1"), '', '');
        }



        function actualizar_estado_gestion_importacion(id_movimiento_actualzar, idempresa, sucursal, idejer, idprdo, cliente, secu_minv, tran, descripcion_modulo, estado) {
            window.opener.actualizar_estado_gestion_importacion(id_movimiento_actualzar, idempresa, sucursal, idejer, idprdo, cliente, secu_minv, tran, descripcion_modulo, estado);
            window.close();
        }


        // -----------------------------------------------------------------------------------------
        // Cierre de anticipo en compras de inventario
        // -----------------------------------------------------------------------------------------

        function cerrar_anticipo_modulo() {
            var cliente = document.getElementById('cliente').value;

            if (cliente != '') {
                alertSwal('Cargando Diario', 'success');
                var pagina = '../comprobante_base/comprobante.php?sesionId=<?= session_id() ?>&num_clpv_fgasto=' + cliente;
                var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=730, height=380, top=255, left=130";
                window.open(pagina, "", opciones);

            } else {
                alert("Por favor primero seleccione un proveedor");
            }
        }

        // -----------------------------------------------------------------------------------------
        // FIN Cierre de anticipo en compras de inventario
        // -----------------------------------------------------------------------------------------


        // -----------------------------------------------------------------------------------------
        // Factura Afectar en factura gasto
        // -----------------------------------------------------------------------------------------

        function facturas(empresa, event) {
            if (event.keyCode == 13 || event.keyCode == 115) { // F4
                var factura = document.getElementById('factura_afectar').value;
                if (factura.length == 0) {
                    factura = '';
                }
                var sucu = document.getElementById('sucursal').value;
                var clpv = document.getElementById('cliente').value;
                var tran = document.getElementById('tran').value; // liquidacion en compras
                var det = document.getElementById('detalle').value;
                var coti = document.getElementById('cotizacion').value;
                var mone = document.getElementById('moneda').value;
                var coti_ext = document.getElementById('cotizacion_ext').value;

                var array = [factura, sucu, clpv, empresa, tran, det, coti, mone, coti_ext];
                AjaxWin('<?= $_COOKIE["JIREH_INCLUDE"] ?>', '../factura_prove_rs/buscar_factura.php?sesionId=<?= session_id() ?>&mOp=true&mVer=false&array=' + array, 'DetalleShow', 'iframe', 'FACTURAS', '800', '300', '10', '10', '1', '1');
            }
        }

        function habilitar_factura_afect() {
            document.getElementById('div_fact_afect1').style.display = 'block';
            document.getElementById('div_fact_afect2').style.display = 'block';
        }

        // -----------------------------------------------------------------------------------------
        // FIN Factura Afectar en factura gasto
        // -----------------------------------------------------------------------------------------

        function habilitar_retencion() {
            var tipo_retencion_ad = document.getElementById('tipo_retencion_ad').value;
            if (tipo_retencion_ad == 'R') {
                document.getElementById('div_rete_ad').style.display = 'block';
                document.getElementById('div_rete_ad').style.width = "200%";
            } else {
                document.getElementById('div_rete_ad').style.display = 'none';
            }
        }


        function mostrar_campos_no_domiliados(estado) {
            if (estado == 0) {
                document.getElementById('no_domi10').style.display = 'none';

                const input1 = document.querySelector('tip_renta');
                if (input1) {
                    document.getElementById('tip_renta').value = '';
                }

                const input2 = document.querySelector('conv_doble_tribu');
                if (input2) {
                    document.getElementById('conv_doble_tribu').value = '';
                }

                const input3 = document.querySelector('exonera_opera_domici');
                if (input3) {
                    document.getElementById('exonera_opera_domici').value = '';
                }

            } else {
                document.getElementById('no_domi10').style.display = 'block';
            }
        }
    </script>

    <!--DIBUJA FORMULARIO FILTRO-->


    <?php
    //Definiciones
    global $DSN_Ifx, $DSN;

    session_start();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $id_parcial = 0;
    $idEmpresa = $_SESSION['U_EMPRESA'];
    $idSucursal = $_SESSION['U_SUCURSAL'];
    $usuario_id = $_SESSION['U_ID'];
    $empr_cod_pais = $_SESSION['U_PAIS_COD'];

    // ETIQUETA RETENCION
    $sql = "select p.impuesto, p.etiqueta, p.porcentaje, porcentaje2 from comercial.pais_etiq_imp p where
    p.pais_cod_pais = $empr_cod_pais ";
    unset($array_imp);
    unset($array_imp2);
    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $impuesto      = $oIfx->f('impuesto');
                $etiqueta     = $oIfx->f('etiqueta');
                $porcentaje = $oIfx->f('porcentaje');
                $porcentaje2 = $oIfx->f('porcentaje2');
                $array_imp[$impuesto] = $etiqueta;
                if ($porcentaje2 > 0) {
                    $array_imp2[$impuesto] = $porcentaje2;
                }
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oIfx->Free();
    unset($_SESSION['U_ETIQUETA_RETE']);
    $_SESSION['U_ETIQUETA_RETE'] = $array_imp['RETENCION'];
    if (empty($_SESSION['U_ETIQUETA_RETE'])) {
        $_SESSION['U_ETIQUETA_RETE'] = 'RETENCION';
    }

    ?>


    <body>
        <div class="container-fluid">
            <form id="form1" name="form1" action="javascript:void(null);">
                <input type="hidden" id="contribuyente" name="clpv_etu_clpv" value="N">
                <input type="hidden" id="cod_oc" name="cod_oc" value="">
                <section class="main row">
                    <div id="divBotones" class="col-md-12"></div>
                    <div id="divFormularioCabecera" class="col-md-7"></div>
                    <div id="divFormularioFactura" class="col-md-5"></div>
                    <div id="divValoresFacturas" class="col-md-7"></div>
                    <div id="divValoresRetencion" class="col-md-5"></div>
                    <div id="divFormularioRetencion" class="col-md-12"></div>
                    <div id="divFormularioCuentas" class="col-md-12"></div>
                    <div id="divFormularioDetalle" class="col-md-12"></div>
                    <div id="divAbono" class="col-md-12"></div>
                    <div id="divReporte" class="col-md-12"></div>
                    <div id="divTotal" class="col-md-12"></div>
                    <div id="divComprobante" class="col-md-12"></div>
                    <div style="width: 100%;">
                        <div class="modal fade" id="miModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"></div>
                        <div id="miModalFP"></div>
                        <div id="miModalDistri"></div>
                        <div id="miModal_Diario" class="col-md-12"></div>
                    </div>

                    <div class="col-md-8" id="pestanas" style="float:left; width: 100%;">
                        <!-- Nav tabs -->
                        <ul class="nav nav-tabs" role="tablist">
                            <li role="presentation" class="active" class=""><a href="#divDirectorioMenu" aria-controls="divFormularioGenerales" role="tab" data-toggle="tab">DIRECTORIO</a></li>
                            <li role="presentation"><a href="#divRetencionMenu" aria-controls="divFormularioDatosSalario" role="tab" data-toggle="tab"><?= strtoupper($_SESSION['U_ETIQUETA_RETE']) ?></a></li>
                            <li role="presentation"><a href="#divDiarioMenu" aria-controls="divCag" role="tab" data-toggle="tab">DIARIO</a></li>
                        </ul>
                        <!-- Tab panes -->
                        <div class="tab-content" style="width: 100%;">
                            <div role="tabpanel" class="tab-pane active" id="divDirectorioMenu" style="width: 100%;">
                                <div id="divFormDir" class="table-responsive"></div>
                                <div id="divDir" class="table-responsive"></div>
                                <div id="divTotDir" class="table-responsive"></div>
                            </div>
                            <div role="tabpanel" class="tab-pane" id="divRetencionMenu">
                                <div id="divFormRet" class="table-responsive"></div>
                                <div id="divRet" class="table-responsive"></div>
                                <div id="divTotRet" class="table-responsive"></div>
                            </div>
                            <div role="tabpanel" class="tab-pane" id="divDiarioMenu">
                                <div id="divFormDiario" class="table-responsive"></div>
                                <div id="divDiario" class="table-responsive"></div>
                                <div id="divTotDiario" class="table-responsive"></div>
                            </div>
                        </div>
                    </div>

                </section>
                <div style="width: 100%;">
                    <div id="extra"></div>
                    <div id="extra2"></div>
                </div>

                <div class="modal fade" id="ModalOrden" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"></div>
                <div class="modal fade" id="ModalOrdenDet" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"></div>

            </form>
        </div>
    </body>
    <script>
        genera_formulario();
    </script>
    <script src="js/Webjs.js"></script>
    <? /*     * ***************************************************************** */ ?>
    <? /* NO MODIFICAR ESTA SECCION */ ?>
<? } ?>
<? include_once(FOOTER_MODULO); ?>
<? /* * ***************************************************************** */ ?>