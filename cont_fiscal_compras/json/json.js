function consultarJson(opFactura) {
    var empresa = $("#empresa").val();
    var sucursal = $("#sucursal").val();
    var fecha_inicio = $("#fecha_inicio").val();
    var fecha_fin = $("#fecha_fin").val();

    $('#data-table').DataTable({
        scrollY:        '50vh',
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
            "url": "buscar.php?empresa=" + empresa + "&sucursal=" + sucursal + "&fecha_inicio=" + fecha_inicio + "&fecha_fin=" + fecha_fin + "&opFactura=" + opFactura,
            "type": "POST"
        },
        "columns": [
            {"data": "col"},
            {"data": "col_a"},
            {"data": "col_b"},
            {"data": "col_c"},
            {"data": "col_d"},
            {"data": "col_e"},
            {"data": "col_f"},
            {"data": "col_g"},
            {"data": "col_h"},
            {"data": "col_i"},
            {"data": "col_j"},
			{"data": "col_jj"},
            {"data": "col_k"},
            {"data": "col_l"},
            {"data": "col_m"},
            {"data": "col_n"},
            {"data": "col_o"},
            {"data": "col_p"},
            {"data": "col_q"},
            {"data": "col_r"}
        ],
		"footerCallback": function (row, data, start, end, display) {
			var j = 8;
			for(var i = 1; i <= 6; i++){
				total = this.api().column(j).data().reduce(function (a, b) {
					var num = parseFloat(a) + parseFloat(b);
					return Math.round(num * 100) / 100;
				}, 0);
				$(this.api().column(j).footer()).html(total);
				j++;
			}
			
			var j = 15;
			for(var i = 1; i <= 2; i++){
				total = this.api().column(j).data().reduce(function (a, b) {
					var num = parseFloat(a) + parseFloat(b);
					return Math.round(num * 100) / 100;
				}, 0);
				$(this.api().column(j).footer()).html(total);
				j++;
			}
		},
        "keys": {
            "columns": ":not(:first-child)",
            "editor": "editor"
        },
        "oLanguage": {
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
        }
    });
}
function consultarJsonNuevo() {
    var empresa = $("#empresa").val();
    var sucursal = $("#sucursal").val();
    var fecha_inicio = $("#fecha_inicio").val();
    var fecha_fin = $("#fecha_fin").val();

    $('#data-table').DataTable({
        scrollY: '50vh',
        scrollX: true,
        dom: 'Bfrtip',
        "buttons": [
            {
                extend: 'excelHtml5',
                title: 'DETALLE DE REPORTE COMPRAS IVA',
                autoFilter: true,
                footer: true
            },
            'copy', 'csv', 'print',
            {
                extend: 'pdfHtml5',
                title: 'DETALLE DE REPORTE COMPRAS IVA',
                orientation: 'landscape',
                pageSize: 'A4',
                footer: true
            }
        ],
        "searching": true,
        "pageLength": -1,
        "bDeferRender": true,
        "sPaginationType": "full_numbers",
        "ajax": {
            "url": "buscar_nuevo.php?empresa=" + empresa + "&sucursal=" + sucursal + "&fecha_inicio=" + fecha_inicio + "&fecha_fin=" + fecha_fin,
            "type": "POST"
        },
        "columns": [
            {"data": "col"}, {"data": "col_a"}, {"data": "col_b"}, {"data": "col_c"},
            {"data": "col_d"}, {"data": "col_e"}, {"data": "col_f"}, {"data": "col_g"},
            {"data": "col_h"}, {"data": "col_i"}, {"data": "col_j"}, {"data": "col_k"},
            {"data": "col_l"}, {"data": "col_m"}, {"data": "col_n"}, {"data": "col_o"},
            {"data": "col_p"}, {"data": "col_q"}, {"data": "col_r"}, {"data": "col_s"}
        ],
        "footerCallback": function () {
            var columnasTotal = [8, 9, 10, 11, 12, 13, 16, 17, 18];
            var api = this.api();
            columnasTotal.forEach(function (columna) {
                var total = api.column(columna).data().reduce(function (a, b) {
                    var aNum = parseFloat(String(a).replace(/<[^>]*>/g, '').replace(',', '')) || 0;
                    var bNum = parseFloat(String(b).replace(/<[^>]*>/g, '').replace(',', '')) || 0;
                    return Math.round((aNum + bNum) * 100) / 100;
                }, 0);
                $(api.column(columna).footer()).html(total.toFixed(2));
            });
        },
        "oLanguage": {
            "sProcessing": "Procesando...",
            "sLengthMenu": "Mostrar registros",
            "sZeroRecords": "No se encontraron resultados",
            "sEmptyTable": "Ningun dato disponible en esta tabla",
            "sInfo": "Mostrando del (_START_ al _END_) de un total de _TOTAL_ registros",
            "sInfoEmpty": "Mostrando del 0 al 0 de un total de 0 registros",
            "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
            "sSearch": "Filtrar:",
            "sLoadingRecords": "Por favor espere - cargando...",
            "oPaginate": {"sFirst": "Primero", "sLast": "Ultimo", "sNext": "Siguiente", "sPrevious": "Anterior"}
        }
    });
}
