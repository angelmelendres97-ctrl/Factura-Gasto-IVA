<?php
/* ARCHIVO COMUN PARA LA EJECUCION DEL SERVIDOR AJAX DEL MODULO */

/***************************************************/
/* NO MODIFICAR */
include_once('../../Include/config.inc.php');
include_once(path(DIR_INCLUDE) . 'conexiones/db_conexion.php');
include_once(path(DIR_INCLUDE) . 'comun.lib.php');
include_once(path(DIR_INCLUDE) . 'Clases/Formulario/Formulario.class.php');
require_once(path(DIR_INCLUDE) . 'Clases/xajax/xajax_core/xajax.inc.php');
require_once(path(DIR_INCLUDE) . 'Clases/GeneraDetalleAsientoContable.class.php');
require_once(path(DIR_INCLUDE) . 'Clases/Logs.class.php');
require_once(path(DIR_INCLUDE) . 'Clases/GeneraDetalleInventario.class.php');;
/***************************************************/
/* INSTANCIA DEL SERVIDOR AJAX DEL MODULO*/
$xajax = new xajax('_Ajax.server.php');
$xajax->setCharEncoding('ISO-8859-1');
/***************************************************/
//	FUNCIONES PUBLICAS DEL SERVIDOR AJAX DEL MODULO 
//	Aqui registrar todas las funciones publicas del servidor ajax
//	Ejemplo,
//	$xajax->registerFunction("Nombre de la Funcion");
/***************************************************/
// envio muestra
$xajax->registerFunction("genera_formulario_reporte");
$xajax->registerFunction("guardar");
$xajax->registerFunction("reporte");
$xajax->registerFunction("cargar_sucu");
$xajax->registerFunction("cambioFiltroFecha");
$xajax->registerFunction("pedido_det");
$xajax->registerFunction("eliminar");
$xajax->registerFunction("cargar_txt");
$xajax->registerFunction("guardar_clpv");
$xajax->registerFunction("generar");
$xajax->registerFunction("clave_acceso");
$xajax->registerFunction("guardar_gasto");
$xajax->registerFunction("cargar_planilla");
$xajax->registerFunction("guardar_caja_chica");
$xajax->registerFunction("generar_almacen");
$xajax->registerFunction("guardar_almacen");
$xajax->registerFunction("retencion_auto");
$xajax->registerFunction("calculo_ret");
$xajax->registerFunction("asiento_gasto");
$xajax->registerFunction("verDiarioContable");
$xajax->registerFunction("genera_pdf_doc_compras");



/***************************************************/
