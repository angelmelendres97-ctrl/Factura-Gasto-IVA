<?
    if (isset($_REQUEST['cliente']))
        $cliente = $_REQUEST['cliente'];
    else
        $cliente = '';
?>

<html lang="es">

    <head>
        <meta charset="UTF-8">
        <title>Proveedores</title>
        <!--CSS-->    
        <link rel="stylesheet" href="media/css/bootstrap.css">
        <link rel="stylesheet" href="media/css/dataTables.bootstrap.min.css">
        <link rel="stylesheet" href="media/css/style.css">
        <link rel="stylesheet" href="media/font-awesome/css/font-awesome.css">
		<link rel="stylesheet" href="media/css/dataTables.select.min.css">
		<link rel="stylesheet" href="media/css/dataTables.buttons.min.css">
		
        <!--Javascript-->    
        <script type="text/javascript" src="media/js/jquery-1.10.2.js"></script>
        <script type="text/javascript" src="media/js/jquery.dataTables.min.js"></script>
        <script type="text/javascript" src="media/js/dataTables.keyTable.min.js"></script>
		<script type="text/javascript" src="media/js/dataTables.select.min.js"></script>
		<script type="text/javascript" src="media/js/dataTables.buttons.min.js"></script>
		<script type="text/javascript" src="media/js/dataTables.buttons.flash.min.js"></script>
		<script type="text/javascript" src="media/js/dataTables.jszip.min.js"></script>
		<script type="text/javascript" src="media/js/dataTables.pdfmake.min.js"></script>
		<script type="text/javascript" src="media/js/dataTables.vfs_fonts.js"></script>
		<script type="text/javascript" src="media/js/dataTables.buttons.html5.min.js"></script>
		<script type="text/javascript" src="media/js/dataTables.buttons.print.min.js"></script>
        <script type="text/javascript" src="media/js/dataTables.bootstrap.min.js"></script>          
        <script type="text/javascript" src="media/js/bootstrap.js"></script>
        <script type="text/javascript" src="media/js/lenguajeusuario_.js"></script>   
        <script type="text/javascript" src="js/teclaEvent.js" type="text/javascript"></script>  
        <script>

            shortcut.add("Esc", function() {
                close();
            });

            shortcut.add("F8", function() {
                var inputs = document.getElementsByTagName('input');

                for(var i=0; i<inputs.length; i++){
                    if(inputs[i].getAttribute('type')=='search'){
                        inputs[i].focus();
                    }
                }
            });

            $(document).ready(function(){
                 var inputs = document.getElementsByTagName('input');

                for(var i=0; i<inputs.length; i++){
                    if(inputs[i].getAttribute('type')=='search'){
                        inputs[i].focus();
                    }
                }
            });

            function seleccionaItem(a, contr){
				if(!isNaN(a)){
					window.opener.document.form1.sust_trib.focus();
					window.opener.cargarDatosClpv(a);
                    window.opener.cargarDatosOP(a);
					window.close();
				}
            }
        </script>   
    </head>

    <body style="background: white; width: 100%;">
        <div class="container-fluid">
            <div class="alert-danger" role="alert" style="margin-bottom: 10px; text-align: right;">
                <span class="glyphicon glyphicon-text-size" aria-hidden="true"></span>
                Atajos del Teclado
                <span class="glyphicon glyphicon-option-vertical" aria-hidden="true"></span>
                ESC (Salir)
                <span class="glyphicon glyphicon-option-vertical" aria-hidden="true"></span>
                F8 (Filtrar)
            </div>
            <div class="table-responsive"> 
                <input type="hidden" name="nomClpv" id="nomClpv" value="<?=$cliente?>">
                <table id="datatable-keytable" class="table table-striped table-bordered table-condensed" cellspacing="0" width="100%">
                    <thead>
                        <tr class="info">
                            <th>Codigo</th>
							<th>Tipo</th>
                            <th>RUC</th>
                            <th>Proveedor</th>
                            <th>Estado</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                        <tr class="info">
                            <th>Codigo</th>
							<th>Tipo</th>
                            <th>RUC</th>
                            <th>Proveedor</th>
                            <th>Estado</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>        
            </div>
        </div>
    </body>
</html>
