<? /* * ***************************************************************** */ ?>
<? /* NO MODIFICAR ESTA SECCION */ ?>
<? include_once('../_Modulo.inc.php'); ?>
<? include_once(HEADER_MODULO); ?>
<? if ($ejecuta) { ?>
    <? /*     * ***************************************************************** */ ?>

    <!--CSS-->
    <link rel="stylesheet" type="text/css" href="<?= $_COOKIE["JIREH_INCLUDE"] ?>css/bootstrap-3.3.7-dist/css/bootstrap.min.css" media="screen" />
    
    <link rel="stylesheet" href="media/css/bootstrap.css">
    <link rel="stylesheet" href="media/css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="media/font-awesome/css/font-awesome.css">
    



      <!--Javascript--> 
      <script type="text/javascript" src="js/jquery.js"></script>
    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script src="media/js/jquery-1.10.2.js"></script>
    <script src="media/js/jquery.dataTables.min.js"></script>
    <script src="media/js/dataTables.bootstrap.min.js"></script>          
    <script src="media/js/bootstrap.js"></script>
    <script type="text/javascript" language="javascript" src="<?= $_COOKIE["JIREH_INCLUDE"] ?>css/bootstrap-3.3.7-dist/js/bootstrap.min.js"></script>    
   
    <script type="text/javascript" language="JavaScript" src="<?=$_COOKIE["JIREH_INCLUDE"]?>js/treeview/js/bootstrap-treeview.js"></script>
    <script type="text/javascript" language="javascript" src="<?= $_COOKIE["JIREH_INCLUDE"] ?>css/bootstrap-3.3.7-dist/js/bootstrap.min.js"></script>
    <script type="text/javascript" language="JavaScript" src="<?=$_COOKIE["JIREH_INCLUDE"]?>js/dataTables/jquery.dataTables.min.js"></script>
    <script type="text/javascript" language="JavaScript" src="<?=$_COOKIE["JIREH_INCLUDE"]?>js/dataTables/dataTables.bootstrap.min.js"></script>
    <script type="text/javascript" language="JavaScript" src="<?=$_COOKIE["JIREH_INCLUDE"]?>js/dataTables/dataTables.buttons.min.js"></script>
    <script type="text/javascript" language="JavaScript" src="<?=$_COOKIE["JIREH_INCLUDE"]?>js/dataTables/dataTables.buttons.flash.min.js"></script>
    <script type="text/javascript" language="JavaScript" src="<?=$_COOKIE["JIREH_INCLUDE"]?>js/dataTables/dataTables.jszip.min.js"></script>

    <script type="text/javascript" language="JavaScript" src="<?=$_COOKIE["JIREH_INCLUDE"]?>js/dataTables/dataTables.vfs_fonts.js"></script>
    <script type="text/javascript" language="JavaScript" src="<?=$_COOKIE["JIREH_INCLUDE"]?>js/dataTables/dataTables.buttons.html5.min.js"></script>
    <script type="text/javascript" language="JavaScript" src="<?=$_COOKIE["JIREH_INCLUDE"]?>js/dataTables/dataTables.buttons.print.min.js"></script>

    

    <script>
        function init(table) {
            var table = $('#'+table).DataTable({
                scrollY: '50vh',
                scrollX: true,
                dom: 'Bfrtip',
                buttons: [ {
                        extend: 'excelHtml5',
                        footer: true,
                        title: 'Reporte de Compras',
                        titleAttr: 'Click para descargar como Excel',
                        text: '<div class="contenedor_excel"><i class="fa fa-file-excel-o excel"></i><label class="labe"></label></div>',
                        exportOptions: {
                            format: {
                                body: function(data, row, column, node) {
                                    var retorno = "",
                                        tag, respuesta = "",
                                        reponer = [];

                                    tag = $(node).find('input');
                                    if (tag.length > 0) {
                                        retorno = retorno + ($(tag).map(function() {
                                            return $(this).val();
                                        }).get().join(','));
                                    }

                                    respuesta = (retorno != "") ? retorno : $.trim($(node).text());
                                    for (i = 0; i < reponer.length; i++) {
                                        $(node).append(reponer[i]);
                                    }

                                    return respuesta;
                                }
                            },
                        }
                    },
                    {
                        extend: 'csvHtml5',
                        footer: true,
                        title: 'Reporte de Compras',
                        titleAttr: 'Click para descargar como CSV',
                        text: '<div class="contenedor_csv"><i class="fa fa-file-text-o csv"></i><label class="labe"></label></div>',
                        exportOptions: {
                            format: {
                                body: function(data, row, column, node) {
                                    var retorno = "",
                                        tag, respuesta = "",
                                        reponer = [];

                                    tag = $(node).find('input');
                                    if (tag.length > 0) {
                                        retorno = retorno + ($(tag).map(function() {
                                            return $(this).val();
                                        }).get().join(','));
                                    }

                                    respuesta = (retorno != "") ? retorno : $.trim($(node).text());
                                    for (i = 0; i < reponer.length; i++) {
                                        $(node).append(reponer[i]);
                                    }

                                    return respuesta;
                                }
                            },
                        }
                    }
                    
                ],

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
                "paging": true,
                "ordering": true,
                "info": true,
                "pageLength": 1000
            });
            table.search().draw();
        }


        function generar_pdf() {
			var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=730, height=370, top=255, left=130";
			var pagina = '../../Include/documento_pdf2.php?sesionId=<?= session_id() ?>';
			window.open(pagina, "", opciones);
		}
        function genera_cabecera_formulario() {
            xajax_genera_cabecera_formulario('nuevo', xajax.getFormValues("form1"));
        }


        function genera_cabecera_filtro() {
            xajax_genera_cabecera_formulario('filtro', xajax.getFormValues("form1"));
        }

        function generar() {
            if ($('#form1')[0].checkValidity()) {
                jsShowWindowLoad();
                xajax_generar(xajax.getFormValues("form1"));
            }
            else{
                    alertSwal('Complete el formulario');
                    $("#form1")[0].reportValidity();
            }  
        }

        function f_filtro_sucursal(data) {
            xajax_f_filtro_sucursal(xajax.getFormValues("form1"), data);
        }

        function eliminar_lista_sucursal() {
            var sel = document.getElementById("sucursal");
            for (var i = (sel.length - 1); i >= 1; i--) {
                aBorrar = sel.options[i];
                aBorrar.parentNode.removeChild(aBorrar);
            }
        }

        function anadir_elemento_sucursal(x, i, elemento) {
            var lista = document.form1.sucursal;
            var option = new Option(elemento, i);
            lista.options[x] = option;
            document.form1.sucursal.value = i;
        }

        function f_filtro_ejercicio(data) {
            //alert(data);
            xajax_f_filtro_ejercicio(xajax.getFormValues("form1"), data);
        }

        function eliminar_lista_anio() {
            var sel = document.getElementById("ejercicio");
            for (var i = (sel.length - 1); i >= 1; i--) {
                aBorrar = sel.options[i];
                aBorrar.parentNode.removeChild(aBorrar);
            }
        }

        function anadir_elemento_anio(x, i, elemento) {
            //alert(x);
            var lista = document.form1.ejercicio;
            var option = new Option(elemento, i);
            lista.options[x] = option;
            document.form1.ejercicio.value = i;
        }


        function f_filtro_periodo(data) {
            xajax_f_filtro_periodo(xajax.getFormValues("form1"), data);
        }

        function eliminar_lista_periodo() {
            var sel = document.getElementById("periodo");
            for (var i = (sel.length - 1); i >= 1; i--) {
                aBorrar = sel.options[i];
                aBorrar.parentNode.removeChild(aBorrar);
            }
        }

        function anadir_elemento_periodo(x, i, elemento) {
            var lista = document.form1.periodo;
            var option = new Option(elemento, i);
            lista.options[x] = option;
            document.form1.periodo.value = i;
        }

        function f_filtro_mes_fin(data) {
            xajax_f_filtro_mes_fin(xajax.getFormValues("form1"), data);
        }

        function eliminar_lista_mes_fin() {
            var sel = document.getElementById("mes_fin");
            for (var i = (sel.length - 1); i >= 1; i--) {
                aBorrar = sel.options[i];
                aBorrar.parentNode.removeChild(aBorrar);
            }
        }

        function anadir_elemento_mes_fin(x, i, elemento) {
            var lista = document.form1.mes_fin;
            var option = new Option(elemento, i);
            lista.options[x] = option;
            document.form1.mes_fin.value = i;
        }

        function f_filtro_subgrupo(data) {
            xajax_f_filtro_subgrupo(xajax.getFormValues("form1"), data);
        }

        function eliminar_lista_subgrupo() {
            var sel = document.getElementById("cod_subgrupo");
            for (var i = (sel.length - 1); i >= 1; i--) {
                aBorrar = sel.options[i];
                aBorrar.parentNode.removeChild(aBorrar);
            }
        }

        function anadir_elemento_subgrupo(x, i, elemento) {
            var lista = document.form1.cod_subgrupo;
            var option = new Option(elemento, i);
            lista.options[x] = option;
            document.form1.cod_subgrupo.value = i;
        }


        function f_filtro_activos1(data) {
            xajax_f_filtro_activos1(xajax.getFormValues("form1"), data);
        }

        function eliminar_lista_activo1() {
            var sel = document.getElementById("cod_activo_hasta");
            for (var i = (sel.length - 1); i >= 1; i--) {
                aBorrar = sel.options[i];
                aBorrar.parentNode.removeChild(aBorrar);
            }
        }

        function anadir_elemento_activo1(x, i, elemento) {
            var lista = document.form1.cod_activo_hasta;
            var option = new Option(elemento, i);
            lista.options[x] = option;
            document.form1.cod_activo_hasta.value = i;
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

        function vista_previa_diario(sucursal, cod_prove, asto_cod, ejer_cod, prdo_cod) {
            var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=730, height=380, top=255, left=130";
            var pagina = '../contabilidad_comprobante/vista_previa.php?sesionId=<?= session_id() ?>&sucursal=' + sucursal + '&cod_prove=' + cod_prove + '&asto=' + asto_cod + '&ejer=' + ejer_cod + '&mes=' + prdo_cod;
            window.open(pagina, "", opciones);
        }

        function genera_documento(tipo_documento, id, clavAcce, clpv, num_fact, ejer, asto, fec_emis, sucu) {
            xajax_genera_documento(tipo_documento, id, clavAcce, clpv, num_fact, ejer, asto, fec_emis, sucu);
        }

        // FUNCION PARA EXPORTAR DATA A EXCEL	
        function f_exportar() {
            document.location = "excel.php";
        }

        function recargar_formulario(){
            $("#form1")[0].reset();
            location.reload();
        }
        function cargar_sucu(){
			xajax_genera_cabecera_formulario('nuevo', xajax.getFormValues("form1") );
        }
        function cambiar_filtro(){
            xajax_form_filtros(xajax.getFormValues("form1"));
        }
        function cargar_mes() {
            var op = document.getElementById('anio').value;
                xajax_cargar_mes(xajax.getFormValues("form1"), op);
        
        }

        function limpiar_lista() {
            document.getElementById("mes").options.length = 0;
        }

        function anadir_elemento_comun(x, i, elemento) {
            var lista = document.form1.mes;
            var option = new Option(elemento, i);
            lista.options[x] = option;
        }
    </script>
    <!--DIBUJA FORMULARIO FILTRO-->
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />

    <body>
        <form id="form1" name="form1" action="javascript:void(null);">
            <div class="col-md-12">
                            <div id="divFormularioReportesGrupos"></div>
            </div>
            <div class="col-md-12">
                            <div id="reporte"></div>
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
    </body>
    <script>
        genera_cabecera_formulario(); /*genera_detalle();genera_form_detalle();*/
    </script>
    <? /*     * ***************************************************************** */ ?>
    <? /* NO MODIFICAR ESTA SECCION */ ?>
<? } ?>
<? include_once(FOOTER_MODULO); ?>
<? /* * ***************************************************************** */ ?>