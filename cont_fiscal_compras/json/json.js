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

function sumarColumna(api, columnIndex) {
    var total = api.column(columnIndex).data().reduce(function (a, b) {
        var valorA = parseFloat(String(a).replace(/<[^>]*>/g, '').replace(/,/g, '')) || 0;
        var valorB = parseFloat(String(b).replace(/<[^>]*>/g, '').replace(/,/g, '')) || 0;
        var num = valorA + valorB;
        return Math.round(num * 100) / 100;
    }, 0);
    $(api.column(columnIndex).footer()).html(total.toFixed(2));
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
                title: 'REPORTE FISCAL COMPRAS IVA MULTIPLE'
            },
            'copy', 'csv', 'print',
            {
                extend: 'pdfHtml5',
                orientation: 'landscape',
                pageSize: 'A4',
                title: 'REPORTE FISCAL COMPRAS IVA MULTIPLE',
                footer: true
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
