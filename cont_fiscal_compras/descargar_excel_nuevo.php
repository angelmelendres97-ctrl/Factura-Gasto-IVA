<?php
session_start();

$nombreArchivo = 'detalle_reporte_compras_iva_' . date('Ymd_His') . '.xls';
$contenido = isset($_SESSION['reporte_compras_nuevo_excel']) ? $_SESSION['reporte_compras_nuevo_excel'] : '';

if (empty($contenido)) {
    $contenido = '<table><tr><td>No existe un reporte nuevo generado para descargar.</td></tr></table>';
}

header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
header('Pragma: no-cache');
header('Expires: 0');

echo "\xEF\xBB\xBF";
echo '<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></head><body>';
echo $contenido;
echo '</body></html>';
?>
