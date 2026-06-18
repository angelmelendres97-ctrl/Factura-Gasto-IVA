<?

/********************************************************************/ ?>
<? /* NO MODIFICAR ESTA SECCION*/ ?>
<? include_once('../_Modulo.inc.php'); ?>
<? include_once(HEADER_MODULO); ?>
<? if ($ejecuta) { ?>
    <? /********************************************************************/ ?>

    <!-- Bootstrap 3.3.7 -->
    <link rel="stylesheet" href="<?= $_COOKIE["JIREH_COMPONENTES"] ?>bower_components/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="<?= $_COOKIE["JIREH_INCLUDE"] ?>css/dataTables/dataTables.buttons.min.css" media="screen">

    <!-- Select2 -->
    <link rel="stylesheet" href="<?= $_COOKIE["JIREH_COMPONENTES"] ?>bower_components/select2/dist/css/select2.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="<?= $_COOKIE["JIREH_COMPONENTES"] ?>dist/css/AdminLTE.min.css">
    <!-- AdminLTE Skins. Choose a skin from the css/skinsfolder instead of downloading all of them to reduce the load. -->
    <link rel="stylesheet" href="<?= $_COOKIE["JIREH_COMPONENTES"] ?>dist/css/skins/_all-skins.min.css">
    <link rel="stylesheet" type="text/css" href="<?= $_COOKIE["JIREH_INCLUDE"] ?>css/dataTables/dataTables.bootstrap.min.css" media="screen">

    <!--JavaScript-->
    <script type="text/javascript" language="JavaScript" src="<?= $_COOKIE["JIREH_INCLUDE"] ?>js/dataTables/jquery.dataTables.min.js"></script>
    <script type="text/javascript" language="JavaScript" src="<?= $_COOKIE["JIREH_INCLUDE"] ?>js/dataTables/dataTables.bootstrap.min.js"></script>

    <script type="text/javascript" language="JavaScript" src="<?= $_COOKIE["JIREH_INCLUDE"] ?>js/dataTables/dataTables.buttons.min.js"></script>
    <script type="text/javascript" language="JavaScript" src="<?= $_COOKIE["JIREH_INCLUDE"] ?>js/dataTables/dataTables.buttons.flash.min.js"></script>
    <script type="text/javascript" language="JavaScript" src="<?= $_COOKIE["JIREH_INCLUDE"] ?>js/dataTables/dataTables.jszip.min.js"></script>
    <script type="text/javascript" language="JavaScript" src="<?= $_COOKIE["JIREH_INCLUDE"] ?>js/dataTables/dataTables.pdfmake.min.js"></script>
    <script type="text/javascript" language="JavaScript" src="<?= $_COOKIE["JIREH_INCLUDE"] ?>js/dataTables/dataTables.vfs_fonts.js"></script>
    <script type="text/javascript" language="JavaScript" src="<?= $_COOKIE["JIREH_INCLUDE"] ?>js/dataTables/dataTables.buttons.html5.min.js"></script>
    <script type="text/javascript" language="JavaScript" src="<?= $_COOKIE["JIREH_INCLUDE"] ?>js/dataTables/dataTables.buttons.print.min.js"></script>

    <!-- Select2 -->
    <script src="<?= $_COOKIE["JIREH_COMPONENTES"] ?>bower_components/select2/dist/js/select2.full.min.js"></script>

    <!-- AdminLTE App -->
    <script src="<?= $_COOKIE["JIREH_COMPONENTES"] ?>dist/js/adminlte.min.js"></script>

    <!-- Bootstrap Toggle -->
    <link href="<?= $_COOKIE["JIREH_COMPONENTES"] ?>bower_components/bootstrap-toggle-master/css/bootstrap-toggle.min.css" rel="stylesheet">
    <script src="<?= $_COOKIE["JIREH_COMPONENTES"] ?>bower_components/bootstrap-toggle-master/js/bootstrap-toggle.min.js"></script>

    <!--CSS-->
    <link rel="stylesheet" type="text/css" href="<?= $_COOKIE["JIREH_INCLUDE"] ?>css/bootstrap-3.3.7-dist/css/bootstrap.css" media="screen">
    <link rel="stylesheet" type="text/css" href="<?= $_COOKIE["JIREH_INCLUDE"] ?>css/bootstrap-3.3.7-dist/css/bootstrap.min.css" media="screen">
    <link rel="stylesheet" type="text/css" href="<?= $_COOKIE["JIREH_INCLUDE"] ?>js/treeview/css/bootstrap-treeview.css" media="screen">

    <!-- JSON -->
    <script type="text/javascript" language="javascript" src="json/saepedf.js"></script>
    <script type="text/javascript" language="javascript" src="json/saeprod.js"></script>

    <style>
        .input-group-addon.primary {
            color: rgb(255, 255, 255);
            background-color: rgb(50, 118, 177);
            border-color: rgb(40, 94, 142);
        }
    </style>

    <script>
        function genera_formulario() {
            xajax_genera_formulario_reporte();
            generaSelect2();
        }

        function generaSelect2() {
            $('.select2').select2();
        }



        function anadir_elemento(x, i, elemento, form) {
            var lista = document.getElementById(form);
            var option = new Option(elemento, i);
            lista.options[x] = option;
        }

        function borrar_lista(form) {
            document.getElementById(form).options.length = 0;
        }

        function consultar() {
            xajax_cargar_txt(xajax.getFormValues("form1"));
        }

        // carga imagen a servidor
        function upload_image(id) { //Funcion encargada de enviar el archivo via AJAX
            $(".upload-msg").text('Cargando...');
            var inputFileImage = document.getElementById(id);
            var file = inputFileImage.files[0];
            var data = new FormData();
            data.append(id, file);

            $.ajax({
                url: "upload.php?id=" + id, // Url to which the request is send
                type: "POST", // Type of request to be send, called as method
                data: data, // Data sent to server, a set of key/value pairs (i.e. form fields and values)
                contentType: false, // The content type used when sending data to the server.
                cache: false, // To unable request pages to be cached
                processData: false, // To send DOMDocument or non processed data file it is set to false
                success: function(data) // A function to be called if request succeeds
                {
                    $(".upload-msg").html(data);
                    window.setTimeout(function() {
                        $(".alert-dismissible").fadeTo(500, 0).slideUp(500, function() {
                            $(this).remove();
                        });
                    }, 5000);
                }
            });

        }

        function cargar_sucu() {
            xajax_cargar_sucu(xajax.getFormValues("form1"));
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

        function elimina(id, sucu, empr) {
            let html = "";

            html = '<div class="form-group" align="left" style="margin-top: 20px;">' +
                '<h4 class="text-primary">Motivo:</h4>' +
                '<textarea class="form-control" id="observacionTarea" name="observacionTarea" rows="5" cols="40"></textarea>'
            '</div>';

            Swal.fire({
                title: 'Seguro Desea Anular Pedido ?',
                type: 'question',
                width: '50%',
                html: html,
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Continuar'
            }).then((result) => {
                if (result.value) {
                    let observacionTarea = $("#observacionTarea").val();
                    jsShowWindowLoad();
                    xajax_eliminar(id, sucu, empr, observacionTarea);
                }
            });
        }

        function factura(id, sucu, empr) {
            $('#ModalClpv').modal({
                backdrop: 'static',
                keyboard: false,
                show: false
            });
            $("#factura_det").empty();
            document.getElementById("pedf_cod_pedf").value = id;
            $("#ModalClpv").modal("show");
            //xajax_pedido_det( xajax.getFormValues("form1"), id, ruc, clpv, secu );
        }


        /*
                function crear_clpv(ruc, clpv) {
                    $('#ModalClpv').modal({
                        backdrop: 'static',
                        keyboard: false,
                        show: false
                    });
                    document.getElementById("ruc").value = ruc;
                    document.getElementById("clpv_nom").value = clpv;
                    document.getElementById("clpv_come").value = clpv;
                    document.getElementById("clpv_dir").value = '';
                    document.getElementById("clpv_tel").value = '';
                    document.getElementById("clpv_correo").value = '';
                    $("#ModalClpv").modal("show");

                }
        */

        function crear_clpv(ruc, clpv) {
            window.open('../ficha_proveedor/ficha_proveedor.php?sesionId=<?= session_id() ?>', '_blank');
        }

        function guardar_clpv() {
            var a = document.getElementById("ruc").value;
            var b = document.getElementById("clpv_nom").value;
            var c = document.getElementById("clpv_come").value;
            var d = document.getElementById("clpv_dir").value;
            var e = document.getElementById("clpv_tel").value;
            var f = document.getElementById("clpv_correo").value;

            if (a != 0 && b != 0 && c != 0 && d != 0 && e != 0 && f != 0) {
                Swal.fire({
                    title: 'Seguro Desea Guardar?',
                    type: 'question',
                    width: '50%',
                    html: '',
                    focusConfirm: false,
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Continuar'
                }).then((result) => {
                    if (result.value) {
                        jsShowWindowLoad();
                        xajax_guardar_clpv(xajax.getFormValues("form1"));
                    }
                });
            } else {
                Swal.fire({
                    title: '<h3>!!! Por favor estos Campos son Requeridos *</h3>',
                    width: 600,
                    type: 'error',
                    timer: 2000,
                    showConfirmButton: false
                })
                if (a == 0) {
                    foco('ruc');
                }
                if (b == 0) {
                    foco('clpv_nom');
                }
                if (c == 0) {
                    foco('clpv_come');
                }
                if (d == 0) {
                    foco('clpv_dir');
                }
                if (e == 0) {
                    foco('clpv_tel');
                }
                if (f == 0) {
                    foco('clpv_correo');
                }
            }
        }


        function generar_fact(tipo, fact, ruc, clpv, fech, fec_auto, emi, recep, clave, id, clpv_cod) {
            var tipo = document.getElementById('su.' + id).value;
            var plan = document.getElementById('pl.' + id).value;

            var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=750, height=500, top=180, left=290";


            if (tipo == 1) {
                // GASTO
                if (plan != '') {
                    $('#ModalGen').modal({
                        backdrop: 'static',
                        keyboard: false,
                        show: false
                    });
                    $("#ModalGen").modal("show");
                    jsShowWindowLoad();
                    xajax_generar(xajax.getFormValues("form1"), tipo, fact, ruc, clpv, fech, fec_auto, emi, recep, clave, id, clpv_cod);
                } else {



                    var pagina = '../factura_prove_rs/factura_prove.php?sesionId=<?= session_id() ?>&clave_acceso=' + clave;
                    //AjaxWin('<= $_COOKIE["JIREH_INCLUDE"] ?>', pagina, 'DetalleShow', 'iframe', 'Factura de Gasto', '1100', '500', '0', '0', '0', '0');
                    window.open(pagina, "Factura de Gasto", opciones);
                    // alerts('Por Favor Seleccione una Planilla...!!!', 'error');
                }

            } else if (tipo == 3) {
                // CAJA CHICA
                $('#ModalGen').modal({
                    backdrop: 'static',
                    keyboard: false,
                    show: false
                });
                $("#ModalGen").modal("show");
                jsShowWindowLoad();
                xajax_generar(xajax.getFormValues("form1"), tipo, fact, ruc, clpv, fech, fec_auto, emi, recep, clave, id, clpv_cod);
            } else if (tipo == 2) {
                // ALAMCEN
                if (plan != '') {
                    $('#ModalGen').modal({
                        backdrop: 'static',
                        keyboard: false,
                        show: false
                    });
                    // $("#ModalGen").modal("show");
                    // jsShowWindowLoad();
                    // xajax_generar_almacen( xajax.getFormValues("form1"), tipo, fact, ruc, clpv, fech, fec_auto, emi, recep, clave, id, clpv_cod );
                    // Nuevos metodos
                    // plan = 1 => Inventario Compras  ||  plan = 2 Compras sin retencion
                    if (plan == 1) {
                        var pagina = '../inventario_compra/inventario_compra.php?sesionId=<?= session_id() ?>&clave_acceso=' + clave;
                        window.open(pagina, "Inventario Compra", opciones);
                        //AjaxWin('<= $_COOKIE["JIREH_INCLUDE"] ?>', pagina, 'DetalleShow', 'iframe', 'Inventario Compra', '1100', '500', '0', '0', '0', '0');
                    } else if (plan == 2) {
                        var pagina = '../compra_sret/compra.php?sesionId=<?= session_id() ?>&clave_acceso=' + clave;
                        window.open(pagina, "Inventario Compra", opciones);
                        //AjaxWin('<= $_COOKIE["JIREH_INCLUDE"] ?>', pagina, 'DetalleShow', 'iframe', 'Compra Sin Retencion', '1100', '500', '0', '0', '0', '0');
                    }
                    // FIn Nuevos metodos


                } else {
                    alerts('Por Favor Seleccione una Planilla...!!!', 'error');
                }
            }
        }

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


        function guardar_gasto(tipo, fact, ruc, clpv, fech, fec_auto, emi, recep, clave, id, clpv_cod) {
            Swal.fire({
                title: 'Seguro Desea Guardar?',
                type: 'question',
                width: '50%',
                html: '',
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Continuar'
            }).then((result) => {
                if (result.value) {
                    var tipo = document.getElementById('su.' + id).value;
                    if (tipo == 1) {
                        // GASTO
                        jsShowWindowLoad();
                        xajax_guardar_gasto(xajax.getFormValues("form1"), tipo, fact, ruc, clpv, fech, fec_auto, emi, recep, clave, id, clpv_cod);
                    } else if (tipo == 3) {
                        // CAJA CHICA
                        var fprv_resp = document.getElementById('fprv_resp').value;
                        var fprv_caja = document.getElementById('fprv_caja').value;

                        if (fprv_resp != '' && fprv_caja != '') {
                            jsShowWindowLoad();
                            xajax_guardar_caja_chica(xajax.getFormValues("form1"), tipo, fact, ruc, clpv, fech, fec_auto, emi, recep, clave, id, clpv_cod);
                        } else {
                            alerts('Por Favor Seleccione una Responsable - N.- Caja.....!!!', 'error');
                        }
                    } else if (tipo == 2) {
                        // ALMACEN
                        var fprv_resp = document.getElementById('comprobante').value;
                        var fprv_caja = document.getElementById('bodega').value;
                        var tipo_ret = document.getElementById('tipo_retencion').value;

                        if (fprv_resp != '' && fprv_caja != '' && tipo_ret != '') {
                            jsShowWindowLoad();
                            xajax_guardar_almacen(xajax.getFormValues("form1"), tipo, fact, ruc, clpv, fech, fec_auto, emi, recep, clave, id, clpv_cod);
                        } else {
                            alerts('Por Favor Seleccione una Transaccion - Bodega - Tipo Retencion.....!!!', 'error');
                        }
                    }
                }
            });
        }


        function cargar_planilla(tipo, clpv_cod) {
            xajax_cargar_planilla(xajax.getFormValues("form1"), tipo, clpv_cod);
        }

        function buscar_producto(event, op, id) {
            if (event.keyCode == 115 || event.keyCode == 13) { // F4
                var bodega = document.getElementById('bodega').value;
                if (bodega != '') {
                    $('#ModalProd').modal({
                        backdrop: 'static',
                        keyboard: false,
                        show: false
                    });
                    $("#ModalProd").modal("show");
                    producto_bodega_normal(id);
                } else {
                    alerts('!!! Seleccione Bodega...!!!', 'error');
                }
            }
        }

        function cargar_prod(prod, nom, id) {
            document.getElementById('pr.' + id).value = prod;
            $("#ModalProd").modal("hide");
        }


        function abrirInventario(url, codigoCliente, idContrato) {
            window.open(url);
        }


        function retencion_auto(id) {
            xajax_retencion_auto(xajax.getFormValues("form1"), id);
        }

        function calculo_ret(id) {
            xajax_calculo_ret(xajax.getFormValues("form1"), id);
        }


        function asto_gasto(tipo, fact, ruc, clpv, fech, fec_auto, emi, recep, clave, id, clpv_cod) {
            $("#ModalAsto").modal("show");
            xajax_asiento_gasto(xajax.getFormValues("form1"), tipo, fact, ruc, clpv, fech, fec_auto, emi, recep, clave, id, clpv_cod);
        }

        function abrir_plantilla(idempresa, cod_prove, plan_ser) {
            var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=1250, height=500, top=180, left=290";
            var pagina = '../cxp_planilla_para/planilla.php?sesionId=<?= session_id() ?>&id_planilla=' + plan_ser;
            window.open(pagina, "Plantilla General", opciones);
        }

        function seleccionaItem(empr, sucu, ejer, mes, asto) {
            $("#miModal2").modal("show");
            $("#divInfo").html('');
            $("#divDirectorio").html('');
            $("#divRetencion").html('');
            $("#divDiario").html('');
            $("#divAdjuntos").html('');
            xajax_verDiarioContable(xajax.getFormValues("form1"), empr, sucu, ejer, mes, asto);
        }

        function vista_previa_diario(idempresa, sucursal, cod_prove, asto_cod, ejer_cod, prdo_cod) {
            xajax_genera_pdf_doc_compras(idempresa, sucursal, asto_cod, ejer_cod, prdo_cod);
        }

        function generar_pdf_compras() {
            var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=730, height=.370, top=255, left=130";
            var pagina = '../../Include/documento_pdf3.php?sesionId=<?= session_id() ?>';
            window.open(pagina, "", opciones);
        }

        function sumar_valor_otros() {
            var otros_valores_0 = document.getElementById('otros_valores_0').value;
            var total_ref_ad = document.getElementById('total_ref_ad').value;
            var total_suma = parseFloat(otros_valores_0) + parseFloat(total_ref_ad);
            document.getElementById('total').value = total_suma.toFixed(2);
        }
    </script>

    <body>
        <div class="row" id="Div_Principal">
            <form id="form1" class="form-horizontal" name="form1" action="javascript:void(null);">
                <div class="main-row col-md-12">
                    <div class="col-md-12">
                        <h4 class="text-primary">COMPRAS<small></small></h4>
                        <?
                        global $DSN_Ifx, $DSN;

                        if (session_status() !== PHP_SESSION_ACTIVE) {
                            session_start();
                        }

                        $idempresa  = $_SESSION['U_EMPRESA'];
                        $idsucursal = $_SESSION['U_SUCURSAL'];
                        $idPerfil   = $_SESSION['U_PERFIL'];

                        $oCon = new Dbo;
                        $oCon->DSN = $DSN;
                        $oCon->Conectar();

                        $oIfx = new Dbo;
                        $oIfx->DSN = $DSN_Ifx;
                        $oIfx->Conectar();

                        $fu = new Formulario;
                        $fu->DSN = $DSN;

                        // EMPRESA
                        $sql = "select empr_cod_empr, empr_nom_empr from saeempr ";
                        $lista_empr = lista_boostrap_func($oIfx, $sql, $idempresa, 'empr_cod_empr',  'empr_nom_empr');

                        $sqlSucu = "";
                        if ($idPerfil != 1 && $idPerfil != 2) {
                            $sqlSucu = " and sucu_cod_sucu = $idsucursal";
                        }

                        $sql = "select sucu_cod_sucu, sucu_nom_sucu
                                        from saesucu  where sucu_cod_empr = $idempresa
                                        $sqlSucu";
                        $lista_sucu = lista_boostrap_func($oIfx, $sql, $idsucursal, 'sucu_cod_sucu',  'sucu_nom_sucu');

                        $empr_cod_pais         = $_SESSION['U_PAIS_COD'];
                        // RUC 01
                        // CEDULA 02
                        // PASAPORTE 03
                        // CONSUMIDOR FINAL 07
                        // EXTRANJERIA 04

                        $sql = "SELECT t.id_iden_clpv, t.identificacion, t.tipo, c.identificacion AS iden, c.digitos
                                                FROM comercial.tipo_iden_clpv t , comercial.tipo_iden_clpv_pais c  WHERE
                                                t.id_iden_clpv = c.id_iden_clpv AND
                                                c.pais_cod_pais = '$empr_cod_pais' ";
                        unset($array_iden);
                        unset($array_iden_val);
                        if ($oCon->Query($sql)) {
                            if ($oCon->NumFilas() > 0) {
                                do {
                                    $array_iden[$oCon->f('tipo')] = $oCon->f('iden');
                                    $array_iden_val[$oCon->f('tipo')] = $oCon->f('digitos');
                                } while ($oCon->SiguienteRegistro());
                            } else {
                                //$oReturn->alert('Por favor Configure Pais - Etiquetas.....!!!!');
                            }
                        }
                        $oCon->Free();

                        // GRUPO
                        $sql = "SELECT GRPV_COD_GRPV, GRPV_NOM_GRPV FROM SAEGRPV WHERE
                                                GRPV_COD_EMPR = $idempresa AND
                                                GRPV_COD_MODU = 4";
                        $lista_grpv = lista_boostrap_func($oIfx, $sql, 0, 'grpv_cod_grpv',  'grpv_nom_grpv');


                        ?>
                    </div>
                    <div class="col-md-12">
                        <div class="btn-group">
                            <div class="btn btn-primary btn-sm" onclick="location.reload();">
                                <span class="glyphicon glyphicon-file"></span>
                                Nuevo
                            </div>
                            <button class="btn btn-primary btn-sm" value="Generar" onclick="document.location='excel_download.php?'">
                                Excel
                                <i class="glyphicon glyphicon-print"></i>
                            </button>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-row">
                            <div class="col-md-4" style="display: none">
                                <label for="empresa">* Empresa:</label>
                                <select id="empresa" name="empresa" class="form-control input-sm" onchange="cargar_sucu();">
                                    <option value="0">Seleccione una opcion..</option>
                                    <?= $lista_empr; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="sucursal">* Sucursal:</label>
                                <select id="sucursal" name="sucursal" class="form-control input-sm" onchange="cargar_bode();">
                                    <option value="0">Seleccione una opcion..</option>
                                    <?= $lista_sucu; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="sucursal">* Filtro:</label>
                                <select id="filtro_consulta" name="filtro_consulta" class="form-control input-sm">
                                    <option value="">Seleccione una opcion..</option>
                                    <option value="TO" selected>TODOS</option>
                                    <option value="PE">PENDIENTES</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="archivo">* Archivo:</label>
                                <input type="file" name="archivo" id="archivo" onchange="upload_image(id);" required>
                                <div class="upload-msg"></div>
                            </div>
                            <div class="col-md-3">
                                <div><label for="consultar">* Consultar:</label></div>
                                <div class="btn btn-primary btn-sm" onclick="consultar();" style="width: 100%">
                                    <span class="glyphicon glyphicon-search"></span>
                                    Consultar
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="col-md-12">
                    <div class="table-responsive"></div>
                </div>

                <br><br><br><br><br><br><br><br><br><br>


                <div class="col-md-12">
                    <div id="divFormularioDetalle" class="table-responsive"></div>
                </div>

                <div style="width: 100%;">
                    <div class="modal fade" id="ModalClpv" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg" style="width: 80%">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    <h4 class="modal-title">SUPLIDOR - PROVEEDOR</h4>
                                </div>
                                <div class="modal-body">

                                    <div class="col-md-12">
                                        <div class="form-row">
                                            <div class="col-md-4">
                                                <label for="grupo">* Grupo:</label>
                                                <select id="grupo" name="grupo" class="form-control input-sm">
                                                    <option value="">Seleccione una opcion..</option>
                                                    <?= $lista_grpv; ?>
                                                </select>
                                            </div>

                                            <div class="col-md-4">
                                                <label for="iden">* Tipo Identificacion:</label>
                                                <select id="iden" name="iden" class="form-control input-sm">
                                                    <option value="">Seleccione una opcion..</option>
                                                    <option value="01"><?= $array_iden['01']; ?></option>
                                                    <option value="02"><?= $array_iden['02']; ?></option>
                                                    <option value="03"><?= $array_iden['03']; ?></option>
                                                    <option value="04"><?= $array_iden['07']; ?></option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="ruc">* Identificacion:</label>
                                                <input class="form-control input-sm" type="text" id="ruc" name="ruc">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-row">
                                            <div class="col-md-12">
                                                <label for="clpv_nom">* Suplidor:</label>
                                                <input class="form-control input-sm" type="text" id="clpv_nom" name="clpv_nom">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-row">
                                            <div class="col-md-12">
                                                <label for="clpv_come">* Nombre Comercial:</label>
                                                <input class="form-control input-sm" type="text" id="clpv_come" name="clpv_come">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-row">
                                            <div class="col-md-12">
                                                <label for="clpv_dir">* Direccion:</label>
                                                <input class="form-control input-sm" type="text" id="clpv_dir" name="clpv_dir">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-row">
                                            <div class="col-md-6">
                                                <label for="clpv_tel">* Telefono:</label>
                                                <input class="form-control input-sm" type="text" id="clpv_tel" name="clpv_tel">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="clpv_correo">* Correo:</label>
                                                <input class="form-control input-sm" type="text" id="clpv_correo" name="clpv_correo">
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <br><br>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-primary" onclick="guardar_clpv()" style="width: 25%">Guardar</button>
                                    <button type="button" class="btn btn-danger" data-dismiss="modal" style="width: 25%">Cerrar</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="ModalGen" tabindex="-1" role="dialog" aria-labelledby="myModalLabel3" aria-hidden="true">
                        <div class="modal-dialog modal-lg" style="width: 90%">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    <h4 class="modal-title">FACTURA</h4>
                                </div>
                                <div class="modal-body">
                                    <div id="fac_dat"></div>
                                    <div class="col-md-12">
                                        <div id="fact_det" align="center"></div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="ModalProd" tabindex="-1" role="dialog" aria-labelledby="myModalLabel3" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    <h4 class="modal-title">PRODUCTOS</h4>
                                </div>
                                <div class="modal-body">
                                    <table id="prod_bode_table" class="table table-striped table-bordered table-hover table-condensed" style="width: 100%;" align="center">
                                        <thead>
                                            <tr>
                                                <td colspan="7" class="bg-primary">INVENTARIO</td>
                                            </tr>
                                            <tr class="info">
                                                <td>N.-</td>
                                                <td>Codigo</td>
                                                <td>Producto</td>
                                                <td>Stock</td>
                                                <td>Seleccionar</td>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="ModalAsto" tabindex="-1" role="dialog" aria-labelledby="myModalLabel3" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    <h4 class="modal-title">ASIENTO CONTABLE</h4>
                                </div>
                                <div class="modal-body">
                                    <div id="divasto"></div>
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal fade" id="miModal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                <h4 class="modal-title" id="myModalLabel">DIARIO CONTABLE <span id="divTituloAsto"></span></h4>
                            </div>
                            <div class="modal-body">
                                <div>
                                    <!-- Nav tabs -->
                                    <ul class="nav nav-tabs" role="tablist">
                                        <li role="presentation" class="active"><a href="#divInfo" aria-controls="divInfo" role="tab" data-toggle="tab">Informacion</a></li>
                                        <li role="presentation"><a href="#divDirectorio" aria-controls="divDirectorio" role="tab" data-toggle="tab">Directorio</a></li>
                                        <li role="presentation"><a href="#divRetencion" aria-controls="divRetencion" role="tab" data-toggle="tab">Retencion</a></li>
                                        <li role="presentation"><a href="#divDiario" aria-controls="divDiario" role="tab" data-toggle="tab">Diario</a></li>
                                        <li role="presentation"><a href="#divAdjuntos" aria-controls="divAdjuntos" role="tab" data-toggle="tab">Adjuntos</a></li>
                                    </ul>

                                    <!-- Tab panes -->
                                    <div class="tab-content">
                                        <div role="tabpanel" class="tab-pane active" id="divInfo">...</div>
                                        <div role="tabpanel" class="tab-pane" id="divDirectorio">...</div>
                                        <div role="tabpanel" class="tab-pane" id="divRetencion">...</div>
                                        <div role="tabpanel" class="tab-pane" id="divDiario">...</div>
                                        <div role="tabpanel" class="tab-pane" id="divAdjuntos">...</div>
                                    </div>

                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>

            </form>
        </div>
        <br><br><br>
    </body>



    <script>
        genera_formulario();
    </script>
    <? /********************************************************************/ ?>
    <? /* NO MODIFICAR ESTA SECCION*/ ?>
<? } ?>
<? include_once(FOOTER_MODULO); ?>
<? /********************************************************************/ ?>