function consultarPara() {
    var table = $('#saefpag').DataTable();
    table.destroy();

    var tipo    = $('input[name="fecha"]:checked').val();
    var fec_ini = document.getElementById("fecha_inicio").value;
    var fec_fin = document.getElementById("fecha_fin").value;
    var anio    = document.getElementById("anio").value;
    var mes     = document.getElementById("mes").value;
    var sucu    = document.getElementById("sucursal").value;
    var estado  = $('#estado').val(); //document.getElementById("estado").value;
    
    //alert(estado);

    var nomClpv = '';
    $('#saefpag').DataTable({
        dom: 'Bfrtip',
        "buttons": [{
                extend: 'excelHtml5',
                autoFilter: true,
                footer: true
            },
            'copy', 'csv', 'print', 'pdf'
        ],
        "searching": true,
        "pageLength": 30,
        "bDeferRender": true,
        "sPaginationType": "full_numbers",
        "ajax": {
            "url": "saepedf.php?tipo=" + tipo+"&fec_ini="+fec_ini+"&fec_fin="+fec_fin+"&anio="+anio+"&mes="+mes+"&sucu="+sucu+"&estado="+estado,
            "type": "POST"
        },
        "columns": [
            { "data": "i" },
            { "data": "fecha" },
            { "data": "pedido" },
            { "data": "estado" },
            { "data": "clpv" },
            { "data": "ruc" },
            { "data": "dir" },
            { "data": "tel" },
            { "data": "msn" },
            { "data": "total" },
            { "data": "det" },
            { "data": "anu" },
            { "data": "fact" },
            { "data": "mapa" }
        ],
        "columnDefs": [
            {className: "text-right", "targets": [0,9]}
        ],
        "footerCallback": function(row, data, start, end, display) {
            var j = 9;
            for (var i = 1; i <= 5; i++) {
                total = this.api().column(j).data().reduce(function(a, b) {
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
                '<option value="30">30</option>' +
                '<option value="60">60</option>' +
                '<option value="90">90</option>' +
                '<option value="120">120</option>' +
                '<option value="150">150</option>' +
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