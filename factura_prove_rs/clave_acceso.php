<? /* * ***************************************************************** */ ?>
<? /* NO MODIFICAR ESTA SECCION */ ?>
<? include_once('../_Modulo.inc.php'); ?>
<? include_once(HEADER_MODULO); ?>
<? if ($ejecuta) { ?>
    <? /*     * ***************************************************************** */ ?>

    <script>

        function genera_formulario_clave() {
            xajax_genera_formulario_clave();
        }
		
		function clave_acceso_sri() {
			xajax_clave_acceso(xajax.getFormValues("form1"));
		}
		
    </script>

    <!--DIBUJA FORMULARIO FILTRO-->
    <div align="center">
        <form id="form1" name="form1" action="javascript:void(null);">
            <table align="center" border="0" cellpadding="2" cellspacing="0" width="100%">
                <tr>
                    <td valign="top" align="center">
                        <div id="divReporteClave"></div>
                    </td>
                </tr>		
            </table>
        </form>
    </div>
    <div id="divGrid" ></div>
    <script>genera_formulario_clave();/*genera_detalle();genera_form_detalle();*/</script>
    <? /*     * ***************************************************************** */ ?>
    <? /* NO MODIFICAR ESTA SECCION */ ?>
<? } ?>
<? include_once(FOOTER_MODULO); ?>
<? /* * ***************************************************************** */ ?>