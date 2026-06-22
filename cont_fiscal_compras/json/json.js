function getReporteComprasFiltros(opFactura) {
    return {
        empresa: $("#empresa").val(),
        sucursal: $("#sucursal").val(),
        fecha_inicio: $("#fecha_inicio").val(),
        fecha_fin: $("#fecha_fin").val(),
        opFactura: opFactura
    };
}

function reiniciarTablaCompras(headers, footerTotalColspan, footerCells) {
    if ($.fn.DataTable.isDataTable('#data-table')) {
        $('#data-table').DataTable().clear().destroy();
    }

    var thead = '<tr>';
    for (var i = 0; i < headers.length; i++) {
        thead += '<td class="bg-info">' + headers[i] + '</td>';
    }
    thead += '</tr>';

    var tfoot = '<tr><td colspan="' + footerTotalColspan + '" align="right" class="fecha_letra bg-danger">Total: </td>';
    for (var j = 0; j < footerCells; j++) {
        tfoot += '<td class="fecha_letra bg-danger"></td>';
    }
    tfoot += '</tr>';

    $('#data-table').html('<thead>' + thead + '</thead><tfoot>' + tfoot + '</tfoot>');
}

function obtenerSumaNumericaCelda(valor) {
    var texto = String(valor).replace(/<[^>]*>/g, ' ').replace(/,/g, '');
    var coincidencias = texto.match(/-?\d+(\.\d+)?/g);
    var total = 0;

    if (coincidencias === null) {
        return total;
    }

    for (var i = 0; i < coincidencias.length; i++) {
        total += parseFloat(coincidencias[i]) || 0;
    }

    return total;
}

function sumarColumna(api, columnIndex) {
    var total = api.column(columnIndex).data().reduce(function (a, b) {
        var num = obtenerSumaNumericaCelda(a) + obtenerSumaNumericaCelda(b);
        return Math.round(num * 100) / 100;
    }, 0);
    $(api.column(columnIndex).footer()).html(total.toFixed(2));
}

function formatearCeldaExportacionCompras(data) {
    var texto = String(data);

    texto = texto.replace(/<\s*br\s*\/?\s*>/gi, '\n');
    texto = texto.replace(/<\s*\/\s*(div|p|li|tr)\s*>/gi, '\n');
    texto = texto.replace(/<[^>]*>/g, '');
    texto = texto.replace(/&nbsp;/gi, ' ');
    texto = texto.replace(/&amp;/gi, '&');
    texto = texto.replace(/&lt;/gi, '<');
    texto = texto.replace(/&gt;/gi, '>');
    texto = texto.replace(/&quot;/gi, '"');
    texto = texto.replace(/&#039;/gi, "'");
    texto = texto.replace(/\r\n/g, '\n');
    texto = texto.replace(/\n{3,}/g, '\n\n');

    var lineas = texto.split('\n');
    for (var i = 0; i < lineas.length; i++) {
        lineas[i] = lineas[i].replace(/^\s+|\s+$/g, '');
    }

    return lineas.join('\n').replace(/^\s+|\s+$/g, '');
}



function obtenerLineasExportacionCompras(valor) {
    var texto = formatearCeldaExportacionCompras(valor);

    if (texto === '') {
        return [''];
    }

    return texto.split('\n');
}

function expandirRetencionesExportacionCompras(data) {
    var codigoRetencionIndex = 13;
    var numeroRetencionIndex = 14;
    var baseRetencionIndex = 15;
    var valorRetenidoIndex = 16;
    var dataExpandida = [];

    for (var i = 0; i < data.body.length; i++) {
        var fila = data.body[i];
        var codigos = obtenerLineasExportacionCompras(fila[codigoRetencionIndex]);
        var numeros = obtenerLineasExportacionCompras(fila[numeroRetencionIndex]);
        var bases = obtenerLineasExportacionCompras(fila[baseRetencionIndex]);
        var valores = obtenerLineasExportacionCompras(fila[valorRetenidoIndex]);
        var maxLineas = Math.max(codigos.length, numeros.length, bases.length, valores.length);

        for (var linea = 0; linea < maxLineas; linea++) {
            var filaExportacion = fila.slice();

            if (linea > 0) {
                for (var columna = 0; columna < filaExportacion.length; columna++) {
                    filaExportacion[columna] = '';
                }
            }

            filaExportacion[codigoRetencionIndex] = codigos[linea] || '';
            filaExportacion[numeroRetencionIndex] = numeros[linea] || '';
            filaExportacion[baseRetencionIndex] = bases[linea] || '';
            filaExportacion[valorRetenidoIndex] = valores[linea] || '';
            dataExpandida.push(filaExportacion);
        }
    }

    data.body = dataExpandida;
}
function consultarJson(opFactura) {
    var filtros = getReporteComprasFiltros(opFactura);

    reiniciarTablaCompras([
        'No.', 'RUC', 'Razon Social', 'Fecha', 'Tipo', 'Serie', 'Secuencial', 'Detalle',
        'Valor Con Impuesto', 'Valor Sin Impuesto', 'Valor Impuesto', 'Valor  Exento',
        'Descuentos', 'Total Factura', 'C&oacute;digo Retenci&oacute;n', 'No. Retenci&oacute;n',
        'Valor Base Retenci&oacute;n', 'Valor Retenido', 'Total a Pagar', 'Asiento Contable'
    ], 8, 12);

    $('#data-table').DataTable({
        scrollY: '50vh',
        scrollX: true,
        dom: 'Bfrtip',
        "buttons": [
            {
                extend: 'excelHtml5',
                autoFilter: true,
                footer: true
            },
            'copy', 'csv', 'print', 'pdf'
        ],
        "searching": true,
        "pageLength": -1,
        "bDeferRender": true,
        "sPaginationType": "full_numbers",
        "ajax": {
            "url": "buscar.php?empresa=" + filtros.empresa + "&sucursal=" + filtros.sucursal + "&fecha_inicio=" + filtros.fecha_inicio + "&fecha_fin=" + filtros.fecha_fin + "&opFactura=" + filtros.opFactura,
            "type": "POST"
        },
        "columns": [
            {"data": "col"}, {"data": "col_a"}, {"data": "col_b"}, {"data": "col_c"},
            {"data": "col_d"}, {"data": "col_e"}, {"data": "col_f"}, {"data": "col_g"},
            {"data": "col_h"}, {"data": "col_i"}, {"data": "col_j"}, {"data": "col_jj"},
            {"data": "col_k"}, {"data": "col_l"}, {"data": "col_m"}, {"data": "col_n"},
            {"data": "col_o"}, {"data": "col_p"}, {"data": "col_q"}, {"data": "col_r"}
        ],
        "footerCallback": function () {
            var api = this.api();
            for (var j = 8; j <= 13; j++) {
                sumarColumna(api, j);
            }
            sumarColumna(api, 16);
            sumarColumna(api, 17);
        },
        "keys": {
            "columns": ":not(:first-child)",
            "editor": "editor"
        },
        "oLanguage": dataTableComprasIdioma()
    });
}

function consultarJsonNuevo(opFactura) {
    var filtros = getReporteComprasFiltros(opFactura);

    reiniciarTablaCompras([
        'RUC', 'Raz&oacute;n Social', 'Fecha', 'Tipo', 'Serie', 'Secuencial', 'Detalle',
        'VALOR 15%', 'VALOR 5%', 'VALOR 0%', 'IVA 15%', 'IVA 5%', 'Total Factura',
        'C&oacute;digo Retenci&oacute;n', 'No. Retenci&oacute;n', 'Valor Base Retenci&oacute;n',
        'Valor Retenido', 'Total a Pagar', 'Asiento Contable'
    ], 7, 12);

    $('#data-table').DataTable({
        scrollY: '50vh',
        scrollX: true,
        dom: 'Bfrtip',
        "buttons": [
            {
                extend: 'excelHtml5',
                autoFilter: true,
                footer: true,
                title: 'REPORTE FISCAL COMPRAS IVA MULTIPLE',
                exportOptions: {
                    format: {
                        body: formatearCeldaExportacionCompras,
                        footer: formatearCeldaExportacionCompras
                    },
                    customizeData: expandirRetencionesExportacionCompras
                }
            },
            'copy', 'csv', 'print',
            {
                extend: 'pdfHtml5',
                orientation: 'landscape',
                pageSize: 'A4',
                title: 'REPORTE FISCAL COMPRAS IVA MULTIPLE',
                footer: true,
                exportOptions: {
                    format: {
                        body: formatearCeldaExportacionCompras,
                        footer: formatearCeldaExportacionCompras
                    }
                }
            }
        ],
        "searching": true,
        "pageLength": -1,
        "bDeferRender": true,
        "sPaginationType": "full_numbers",
        "ajax": {
            "url": "buscar_nuevo.php?empresa=" + filtros.empresa + "&sucursal=" + filtros.sucursal + "&fecha_inicio=" + filtros.fecha_inicio + "&fecha_fin=" + filtros.fecha_fin + "&opFactura=" + filtros.opFactura,
            "type": "POST"
        },
        "columns": [
            {"data": "ruc"}, {"data": "razon_social"}, {"data": "fecha"}, {"data": "tipo"},
            {"data": "serie"}, {"data": "secuencial"}, {"data": "detalle"}, {"data": "valor_15"},
            {"data": "valor_5"}, {"data": "valor_0"}, {"data": "iva_15"}, {"data": "iva_5"},
            {"data": "total_factura"}, {"data": "codigo_retencion"}, {"data": "numero_retencion"},
            {"data": "valor_base_retencion"}, {"data": "valor_retenido"}, {"data": "total_pagar"},
            {"data": "asiento_contable"}
        ],
        "footerCallback": function () {
            var api = this.api();
            for (var j = 7; j <= 12; j++) {
                sumarColumna(api, j);
            }
            sumarColumna(api, 15);
            sumarColumna(api, 16);
            sumarColumna(api, 17);
        },
        "keys": {
            "columns": ":not(:first-child)",
            "editor": "editor"
        },
        "oLanguage": dataTableComprasIdioma()
    });
}

function dataTableComprasIdioma() {
    return {
        "sProcessing": "Procesando...",
        "sLengthMenu": 'Mostrar <select>' +
                '<option value="100">100</option>' +
                '<option value="300">300</option>' +
                '<option value="500">500</option>' +
                '<option value="800">800</option>' +
                '<option value="1000">1000</option>' +
                '<option value="-1">Todo</option>' +
                '</select> registros',
        "sZeroRecords": "No se encontraron resultados",
        "sEmptyTable": "Ningun dato disponible en esta tabla",
        "sInfo": "Mostrando del (_START_ al _END_) de un total de _TOTAL_ registros",
        "sInfoEmpty": "Mostrando del 0 al 0 de un total de 0 registros",
        "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
        "sInfoPostFix": "",
        "sSearch": "Filtrar:",
        "sUrl": "",
        "sInfoThousands": ",",
        "sLoadingRecords": "Por favor espere - cargando...",
        "oPaginate": {
            "sFirst": "Primero",
            "sLast": "Ultimo",
            "sNext": "Siguiente",
            "sPrevious": "Anterior"
        },
        "oAria": {
            "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
            "sSortDescending": ": Activar para ordenar la columna de manera descendente"
        }
    };
}
