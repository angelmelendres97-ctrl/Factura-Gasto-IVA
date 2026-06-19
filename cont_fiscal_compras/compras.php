<? /* * ***************************************************************** */ ?>
<? /* NO MODIFICAR ESTA SECCION */ ?>
<? include_once('../_Modulo.inc.php'); ?>
<? include_once(HEADER_MODULO); ?>
<? if ($ejecuta) { ?>

    <!-- Bootstrap 3.3.7 -->
    <link rel="stylesheet" href="../../Include/Componentes/bower_components/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="../../Include/css/dataTables/dataTables.buttons.min.css" media="screen">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="../../Include/Componentes/bower_components/font-awesome/css/font-awesome.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="../../Include/Componentes/bower_components/Ionicons/css/ionicons.min.css">
    <!-- Select2 -->
    <link rel="stylesheet" href="../../Include/Componentes/bower_components/select2/dist/css/select2.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="../../Include/Componentes/dist/css/AdminLTE.min.css">
    <!-- AdminLTE Skins. Choose a skin from the css/skinsfolder instead of downloading all of them to reduce the load. -->
    <link rel="stylesheet" href="../../Include/Componentes/dist/css/skins/_all-skins.min.css">
    <link rel="stylesheet" type="text/css" href="../../Include/css/dataTables/dataTables.bootstrap.min.css" media="screen">

    <!--JavaScript--> 
    <script type="text/javascript" language="JavaScript" src="../../Include/js/dataTables/jquery.dataTables.min.js"></script>
    <script type="text/javascript" language="JavaScript" src="../../Include/js/dataTables/dataTables.bootstrap.min.js"></script>

    <script type="text/javascript" language="JavaScript" src="../../Include/js/dataTables/dataTables.buttons.min.js"></script>
    <script type="text/javascript" language="JavaScript" src="../../Include/js/dataTables/dataTables.buttons.flash.min.js"></script>
    <script type="text/javascript" language="JavaScript" src="../../Include/js/dataTables/dataTables.jszip.min.js"></script>
    <script type="text/javascript" language="JavaScript" src="../../Include/js/dataTables/dataTables.pdfmake.min.js"></script>
    <script type="text/javascript" language="JavaScript" src="../../Include/js/dataTables/dataTables.vfs_fonts.js"></script>
    <script type="text/javascript" language="JavaScript" src="../../Include/js/dataTables/dataTables.buttons.html5.min.js"></script>
    <script type="text/javascript" language="JavaScript" src="../../Include/js/dataTables/dataTables.buttons.print.min.js"></script>
    <script type="text/javascript" language="JavaScript" src="json/json.js"></script>

    <!-- Select2 -->
    <script src="../../Include/Componentes/bower_components/select2/dist/js/select2.full.min.js"></script>

    <!-- AdminLTE App -->
    <script src="../../Include/Componentes/dist/js/adminlte.min.js"></script>


    <script>

        function cargar_lista() {
            xajax_cargar_lista(xajax.getFormValues("form1"));
        }

        function eliminar_lista() {
            var sel = document.getElementById("sucursal");
            for (var i = (sel.length - 1); i >= 1; i--) {
                aBorrar = sel.options[i];
                aBorrar.parentNode.removeChild(aBorrar);
            }
        }

        function anadir_elemento(x, i, elemento) {
            var lista = document.form1.sucursal;
            var option = new Option(elemento, i);
            lista.options[x] = option;
        }

        function consultar() {
            if (ProcesarFormulario() == true) {
                var opFactura = getRadioButtonSelectedValue(document.form1.opFactura);
                consultarJson(opFactura);
            }
        }

        function consultarNuevo() {
            if (ProcesarFormulario() == true) {
                var opFactura = getRadioButtonSelectedValue(document.form1.opFactura);
                consultarJsonNuevo(opFactura);
            }
        }

        function verDiarioContable(empr, sucu, ejer, mes, asto) {
            $("#miModalDiarioContable").modal("show");
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

        function dowloand(ruta) {
            document.location = "../oportunidades/dowloand.php?ruta=" + ruta;
        }

        function genera_documento(tipo_documento, id, clavAcce, clpv, num_fact, ejer, asto, fec_emis, sucu) {
            xajax_genera_documento(tipo_documento, id, clavAcce, clpv, num_fact, ejer, asto, fec_emis, sucu);
        }

        function generar_pdf() {
            var opciones = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, width=730, height=370, top=255, left=130";
            var pagina = '../../Include/documento_pdf.php?sesionId=<?= session_id() ?>';
            window.open(pagina, "", opciones);
        }
		
		function getRadioButtonSelectedValue(ctrl){
            for(i=0;i<ctrl.length;i++)
                if(ctrl[i].checked)
            return ctrl[i].value;
        }

    </script>

    <!--DIBUJA FORMULARIO FILTRO-->
    <body>
        <div class="container-fluid">
            <form id="form1" class="form-horizontal" name="form1" action="javascript:void(null);">
                <div class="col-md-12">
                    <h4 class="text-primary"> Reporte de Compras <small> </small> </h4>
                    <?
                    global $DSN_Ifx, $DSN;

                    session_start();

                    //variables de session
                    $idempresa = $_SESSION['U_EMPRESA'];
                    $idsucursal = $_SESSION['U_SUCURSAL'];

                    //variables del formulario
                    $hoy = date("Y-m-d");

                    $oIfx = new Dbo;
                    $oIfx->DSN = $DSN_Ifx;
                    $oIfx->Conectar();

                    $sql = "select empr_cod_empr, empr_nom_empr
                            from saeempr
                            where empr_cod_empr = $idempresa";
                    if ($oIfx->Query($sql)) {
                        if ($oIfx->NumFilas() > 0) {
                            unset($arrayEmpresa);
                            do {
                                $arrayEmpresa[] = array($oIfx->f('empr_cod_empr'), $oIfx->f('empr_nom_empr'));
                            } while ($oIfx->SiguienteRegistro());
                        }
                    }
                    $oIfx->Free();
                    ?>

                    <div class="form-group form-group-sm">
                        <label for="empresa" class="col-md-1">Empresa:</label>
                        <div class="col-md-2">
                            <select id="empresa" name="empresa" class="form-control">
                                <option value="0">Seleccione una opcion..</option>
                                <?
                                if (count($arrayEmpresa) > 0) {
                                    foreach ($arrayEmpresa as $val) {
                                        $id = $val[0];
                                        $nom = $val[1];

                                        $default = "";
                                        if ($id == $idempresa) {
                                            $default = "selected";
                                        }

                                        echo '<option value="' . $id . '" ' . $default . ' onclick="cargar_lista()">' . $nom . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <label for="sucursal" class="col-md-1">Sucursal:</label>
                        <div class="col-md-2">
                            <select id="sucursal" name="sucursal" class="form-control">
                                <option value="0">Seleccione una opcion..</option>
                            </select>
                        </div>

                        <label for="fecha_inicio" class="col-md-1">Inicio:</label>
                        <div class="col-md-2">
                            <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control input-sm" value="<?= $hoy ?>"/>
                        </div>

                        <label for="fecha_fin" class="col-md-1">Fin:</label>
                        <div class="col-md-2">
                            <input type="date" id="fecha_fin" name="fecha_fin" class="form-control input-sm" value="<?= $hoy ?>"/>
                        </div>
                    </div>

                    <div class="form-group form-group-sm">
						<label for="opFacturaT" class="col-md-1">Global:</label>
                        <div class="col-md-1">
                            <input type="radio" id="opFacturaT" name="opFactura" value="1" checked/>
                        </div>
						<label for="opFacturaI" class="col-md-1">Inventario:</label>
                        <div class="col-md-1">
                            <input type="radio" id="opFacturaI" name="opFactura" value="2"/>
                        </div>
						<label for="opFacturaG" class="col-md-1">Gasto:</label>
                        <div class="col-md-1">
                            <input type="radio" id="opFacturaG" name="opFactura" value="3"/>
                        </div>
						<label for="opFacturaN" class="col-md-1">Nota Credito:</label>
                        <div class="col-md-1">
                            <input type="radio" id="opFacturaN" name="opFactura" value="4"/>
                        </div>
                        <label for="opReembolso" class="col-md-1">Reembolsos:</label>
                        <div class="col-md-1">
                            <input type="radio" id="opFacturaR" name="opFactura" value="5"/>
                        </div>
                        <label for="opReembolso" class="col-md-1">Liquidaciones:</label>
                        <div class="col-md-1">
                            <input type="radio" id="opFacturaL" name="opFactura" value="6"/>
                        </div>
                    </div>

                    <div class="form-group form-group-sm">
                        <div class="col-md-2 col-md-offset-5">
                            <button type="button" class="btn btn-primary" onclick="consultar();">
                                <span class="glyphicon glyphicon-search"></span>
                                Consultar
                            </button>
                            <button type="button" class="btn btn-success" onclick="consultarNuevo();">
                                <span class="glyphicon glyphicon-list-alt"></span>
                                Consulta Nuevo
                            </button>
                        </div>	
                    </div>
                    <div class="table-responsive" style="width: 100%;">
                        <table id="data-table" class="table table-striped table-bordered table-hover table-condensed" style="width: 100%;" align="center">
                            <thead>
								<tr>
									<td class="bg-info">No.</td>    
									<td class="bg-info">RUC</td>
									<td class="bg-info">Razon Social</td>
									<td class="bg-info">Fecha</td>
									<td class="bg-info">Tipo</td>
									<td class="bg-info">Serie</td>
									<td class="bg-info">Secuencial</td>
									<td class="bg-info">Detalle</td>
									<td class="bg-info">Valor Con Impuesto</td>
									<td class="bg-info">Valor Sin Impuesto</td>
									<td class="bg-info">Valor Impuesto</td>
									<td class="bg-info">Valor  Exento</td>
									<td class="bg-info">Descuentos</td>
									<td class="bg-info">Total Factura</td>
									<td class="bg-info">C&oacute;digo Retenci&oacute;n</td>
									<td class="bg-info">No. Retenci&oacute;n</td>
									<td class="bg-info">Valor Base Retenci&oacute;n</td>
									<td class="bg-info">Valor Retenido</td>
									<td class="bg-info">Total a Pagar</td>
									<td class="bg-info">Asiento Contable</td>
								</tr>
                            </thead>
							<tfoot>
								<tr>
									<td colspan="8" align="right" class="fecha_letra bg-danger">Total: </td>
									<td class="fecha_letra bg-danger"></th>
									<td class="fecha_letra bg-danger"></td>
									<td class="fecha_letra bg-danger"></td>
									<td class="fecha_letra bg-danger"></td>
									<td class="fecha_letra bg-danger"></td>
									<td class="fecha_letra bg-danger"></td>
									<td class="fecha_letra bg-danger"></td>
									<td class="fecha_letra bg-danger"></td>
									<td class="fecha_letra bg-danger"></td>
									<td class="fecha_letra bg-danger"></td>
									<td class="fecha_letra bg-danger"></td>
									<td class="fecha_letra bg-danger"></td>
								</tr>
							</tfoot>
                        </table>
                    </div>
                </div>
                <div style="width: 100%;">
                    <div class="modal fade" id="miModalDiarioContable" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div style="width: 100%;">
                    <div class="modal fade" id="miModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"></div>
                </div>
            </form>
        </div>
    </body>
    <script>cargar_lista();</script>

    <? /*     * ***************************************************************** */ ?>
    <? /* NO MODIFICAR ESTA SECCION */ ?>
<? } ?>
<? include_once(FOOTER_MODULO); ?>
<? /* * ***************************************************************** */ ?>

