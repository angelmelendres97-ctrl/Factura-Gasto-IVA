<?php

require("_Ajax.comun.php"); // No modificar esta linea
/* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
  // S E R V I D O R   A J A X //
  :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */

/* * ******************************************* */
/* FCA01 :: GENERA INGRESO TABLA PRESUPUESTO  */
/* * ******************************************* */

function genera_cabecera_formulario($sAccion = 'nuevo', $aForm = '')
{
    //Definiciones
    global $DSN_Ifx, $DSN;

    session_start();

    $oIfx = new Dbo();
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfx1 = new Dbo();
    $oIfx1->DSN = $DSN_Ifx;
    $oIfx1->Conectar();

    $oCon = new Dbo();
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $fu = new Formulario;
    $fu->DSN = $DSN;

    $ifu = new Formulario;
    $ifu->DSN = $DSN_Ifx;

    $oReturn = new xajaxResponse();

    //variables de sesion
    $idempresa = $_SESSION['U_EMPRESA'];
    $idsucursal = $_SESSION['U_SUCURSAL'];

    //variables del formulario
    $empresa = $aForm['empresa'];

    if (empty($empresa)) {
        $empresa = $idempresa;
    }

    //VALIDACION REPORTE SEMESTRAL EMPRESAS RIMPE
    $sql="select empr_rimp_sn from saeempr where empr_cod_empr= $empresa";
    $rimpe=consulta_string($sql,'empr_rimp_sn',$oCon,'');
    if($rimpe=='S'){
        $periodo='<div class="form-group col-xs-12 col-sm-12 col-md-4 col-lg-4">                   
                    <label class="control-label" for="filtro">* Periodo:</label><br>
                    Mes <input type="radio" class="custom-control-input"   onclick="cambiar_filtro();" style="width:20px;height:20px" name="filtro" id="filtro" value="M"  checked>
                    
                    1er Semestre <input type="radio" class="custom-control-input" onclick="cambiar_filtro();" style="width:20px;height:20px" name="filtro" id="filtro" value="1S" >
                    2do Semestre <input type="radio" class="custom-control-input" onclick="cambiar_filtro();" style="width:20px;height:20px" name="filtro" id="filtro" value="2S"  >
                </div>';
    }
    else{
        $periodo='<div class="form-group col-xs-12 col-sm-12 col-md-4 col-lg-4">                   
                    <label class="control-label" for="filtro">* Periodo:</label><br>
                    Mes <input type="radio" class="custom-control-input"   onclick="cambiar_filtro();" style="width:20px;height:20px" name="filtro" id="filtro" value="M"  checked>
                </div>';
    }




    $sucursal = $aForm['sucursal'];

    if (empty($sucursal)) {
        $sucursal = $idsucursal;
    }

    $id_anio = $aForm['anio'];
    if(empty($id_anio)){
        $id_anio='NULL';
    }
    $id_mes = $aForm['mes'];

    $table_op = '';


         //Empresa
	  $optionEmpresa = '';
      $sql_empresa = "SELECT EMPR_COD_EMPR, EMPR_NOM_EMPR FROM SAEEMPR ORDER BY EMPR_NOM_EMPR";
	  if ($oCon->Query($sql_empresa)) {
		  if ($oCon->NumFilas() > 0) {
			  do {
				  $optionEmpresa .= '<option value="' . $oCon->f('empr_cod_empr') . '">' . $oCon->f('empr_nom_empr') . '</option>';
			  } while ($oCon->SiguienteRegistro());
		  }
	  }
	  $oCon->Free();


       //Sucursal
       $optionSucursal = '';
       $sql = "select sucu_cod_sucu, sucu_nom_sucu from saesucu where sucu_cod_empr = $empresa ";
       if ($oIfx->Query($sql)) {
           if ($oIfx->NumFilas() > 0) {
               do {
                   $optionSucursal .= '<option value="' . $oIfx->f('sucu_cod_sucu') . '">' . $oIfx->f('sucu_nom_sucu') . '</option>';
               } while ($oIfx->SiguienteRegistro());
           }
       }
       $oIfx->Free();

      //Empresa
	  $optionEmpresa = '';
      $sql_empresa = "SELECT EMPR_COD_EMPR, EMPR_NOM_EMPR FROM SAEEMPR ORDER BY EMPR_NOM_EMPR";
	  if ($oCon->Query($sql_empresa)) {
		  if ($oCon->NumFilas() > 0) {
			  do {
				  $optionEmpresa .= '<option value="' . $oCon->f('empr_cod_empr') . '">' . $oCon->f('empr_nom_empr') . '</option>';
			  } while ($oCon->SiguienteRegistro());
		  }
	  }
	  $oCon->Free();

       //Ejercicio
	  $optionEjercicio = '';
      $sql_ejer = "select ejer_cod_ejer,  DATE_PART('year', ejer_fec_inil) as anio from saeejer where
      ejer_cod_empr = $empresa order by 2 desc ";
	  if ($oCon->Query($sql_ejer)) {
		  if ($oCon->NumFilas() > 0) {
			  do {
				  $optionEjercicio .= '<option value="' . $oCon->f('ejer_cod_ejer') . '">' . $oCon->f('anio') . '</option>';
			  } while ($oCon->SiguienteRegistro());
		  }
	  }
	  $oCon->Free();


      //Mes
	  $optionMes = '';
      $sql_mes = "select  prdo_num_prdo,   prdo_nom_prdo  from saeprdo where
      prdo_cod_empr = $empresa and
      prdo_cod_ejer = $id_anio  ";
	  if ($oCon->Query($sql_mes)) {
		  if ($oCon->NumFilas() > 0) {
			  do {
				  $optionMes .= '<option value="' . $oCon->f('prdo_num_prdo') . '">' . $oCon->f('prdo_nom_prdo') . '</option>';
			  } while ($oCon->SiguienteRegistro());
		  }
	  }
	  $oCon->Free();


    switch ($sAccion) {
        case 'nuevo':
            $ifu->AgregarCampoListaSQL('empresa', 'Empresa|left', "select empr_cod_empr, empr_nom_empr
															from saeempr
                                                            WHERE empr_cod_empr = $idempresa
															order by empr_nom_empr", true, 150, 150, true);
            $ifu->AgregarComandoAlCambiarValor('empresa', 'f_filtro_sucursal(); f_filtro_ejercicio();');
            $ifu->AgregarCampoListaSQL('sucursal', 'Sucursal|left', '', false, 150, 150, true);
            $ifu->AgregarCampoListaSQL('ejercicio', 'Ejercicio|left', '', true, 150, 150, true);
            $ifu->AgregarComandoAlCambiarValor('ejercicio', 'f_filtro_periodo()');
            $ifu->AgregarCampoListaSQL('periodo', 'Per&iacuteodo|left', '', true, 150, 150, true);
            $ifu->AgregarCampoLista('tipo', 'Tipo|left', true, '', 150, 150, true);
            $ifu->AgregarOpcionCampoLista('tipo', 'RENTA', R);
            $ifu->AgregarOpcionCampoLista('tipo', 'IVA', I);
            $ifu->AgregarCampoTexto('proveedor', 'Proveedor|left', false, '', 150, 150, true);
            $ifu->AgregarCampoTexto('factura', 'Factura|left', false, '', 150, 150, true);
            $ifu->AgregarCampoFecha('fecha_desde', 'Fecha Desde|left', false, '', 150, 150, true);
            $ifu->AgregarCampoFecha('fecha_hasta', 'Fecha Hasta|left', false, '', 150, 150, true);
    }
    /*<tr>
						<td colspan = "8">    
							<div class="btn-group">
								<div class="btn btn-primary btn-sm" onclick="f_exportar();" id = "exportar">
										<span class="glyphicon glyphicon-cog"></span>
										Excel
								</div>
							</div>
						</td>                   
					</tr>*/
    $table_op .= '<table class="table table-striped table-condensed" style="width: 80%; margin-bottom: 0px;" >
					<tr> 
						<td colspan="8" align="center" class="bg-primary">REPORTE DE COMPRAS</td>
					</tr>
					
					<tr class="msgFrm">
						<td colspan="8" align="center">Los campos con * son de ingreso obligatorio</td>
					</tr>
                    
					<tr>
						<td>' . $ifu->ObjetoHtmlLBL('empresa') . '</td>
						<td>' . $ifu->ObjetoHtml('empresa') . '</td>
						<td>' . $ifu->ObjetoHtmlLBL('sucursal') . '</td>
						<td>' . $ifu->ObjetoHtml('sucursal') . '</td>
						<td colspan="2"><div class="form-group col-xs-12 col-sm-12 col-md-4 col-lg-4">                   
                <label class="control-label" for="filtro">* Periodo:</label><br>
                Mes <input type="radio"    onclick="cambiar_filtro();" style="width:20px;height:20px" name="filtro" id="filtro" value="M"  checked>
                
                1er Semestre <input type="radio"  onclick="cambiar_filtro();" style="width:20px;height:20px" name="filtro" id="filtro" value="1S" >
                2do Semestre <input type="radio"  onclick="cambiar_filtro();" style="width:20px;height:20px" name="filtro" id="filtro" value="2S"  >
                </div></td>
					</tr>
					 <tr>  
					 	<td colspan="2"><div id="div_filtro" class="row form-group col-xs-12 col-sm-12 col-md-12 col-lg-12"></div></td>
						<td><input type="checkbox" id="detallado" name="detallado" value="S"> Detallado </td> 
					</tr>
					<tr>
						<td colspan = "8" align="center">    
							<div class="btn-group">
								<div class="btn btn-primary btn-sm" onclick="generar();" id = "generar">
										<span class="glyphicon glyphicon-search"></span>
										Consultar
								</div>
							</div>
						</td>                   
					</tr>

                </table>				
				<br>
				<div id = "reporte"> </div>';
    $table_op .= '</fieldset>';


    $sHtml = '<table class="table table-striped table-condensed" align="center" cellpadding="0" cellspacing="2" width="100%" border="0">
            <tr align="center"><td align="center" class="bg-primary">REPORTE DE COMPRAS</td></tr>
            <tr class="msgFrm">
						<td align="center">Los campos con * son de ingreso obligatorio</td>
					</tr>
           </table>
           
           <div class="row">
      <div class="form-group col-xs-12 col-sm-12 col-md-12 col-lg-12">
          <div class="btn-group">
          <br>
              <div class="btn btn-primary btn-sm" onclick="recargar_formulario();">
                  <span class="glyphicon glyphicon-file"></span>
                  Nuevo
              </div>
              
          </div>
      </div> 
      
      </div>';
//FILTRO FECHA VALORAR DESARROLLO
//Fecha <input type="radio" class="custom-control-input" onclick="cambiar_filtro();" style="width:20px;height:20px" name="filtro" id="filtro" value="F"  >
    $sHtml .= '
    
    <div class="row form-group col-xs-12 col-sm-12 col-md-12 col-lg-12">

            <div class="form-group col-xs-12 col-sm-12 col-md-4 col-lg-4">                   
                    <label class="control-label" for="empresa">* Empresa:</label>
                        <select id="empresa" name="empresa" class="form-control select2" style="width: 100%;" onchange="cargar_sucu();" required>
                        <option value="">..Seleccione una Opcion..</option>
                            ' . $optionEmpresa . '
                        </select>
                </div>

                 <div class="form-group col-xs-12 col-sm-12 col-md-4 col-lg-4">                   
                    <label class="control-label" for="sucursal"> Sucursal:</label>
                        <select id="sucursal" name="sucursal" class="form-control select2" style="width: 100%;"  >
                        <option value="">..Seleccione una Opcion..</option>
                            ' . $optionSucursal . '
                        </select>
                </div>

                <div class="form-group col-xs-12 col-sm-12 col-md-4 col-lg-4">                   
                    <label class="control-label" for="sucursal">Detallado:</label>
                    <br><input type="checkbox" class="custom-control-input" id="detallado" name="detallado" value="S">
                </div>

            </div>';

         $sHtml .= '
         
         <div class="row form-group col-xs-12 col-sm-12 col-md-12 col-lg-12">
                '.$periodo.'
                 <div id="div_filtro" class=" form-group col-xs-12 col-sm-12 col-md-8 col-lg-8"></div>     
        </div>

         <div class="row form-group col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div  class=" form-group col-xs-12 col-sm-12 col-md-4 col-lg-4"></div>
            
                <div  class="form-group  col-xs-3 col-sm-3 col-md-3  col-lg-3">
                
                        <div class="btn btn-primary btn-sm" onclick="generar();" style="margin-left:20px;">
                            <span class="glyphicon glyphicon-search"></span>
                            Consultar
                        </div>
                </div>
         </div>';




    $oReturn->assign("divFormularioReportesGrupos", "innerHTML", $sHtml);

    $oReturn->assign("empresa","value",$empresa);
    $oReturn->assign("sucursal","value",$sucursal);
    $oReturn->script('cambiar_filtro();');

    return $oReturn;
}
function form_filtros($aForm = ''){

    //Definiciones
    global $DSN_Ifx, $DSN;
    if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oCon = new Dbo ( );
	$oCon->DSN = $DSN;
	$oCon->Conectar ();

    $oReturn = new xajaxResponse();

    //variables del formulario
    $empresa = $aForm['empresa'];
    if(empty($empresa)){
        $empresa =  $_SESSION['U_EMPRESA'];    
    }

    $id_anio = $aForm['anio'];
    if(empty($id_anio)){
        $id_anio='NULL';
    }

    $tipo=$aForm['filtro'];



     //Ejercicio
	  $optionEjercicio = '';
      $sql_ejer = "select ejer_cod_ejer,  DATE_PART('year', ejer_fec_inil) as anio from saeejer where
      ejer_cod_empr = $empresa order by 2 desc ";
	  if ($oCon->Query($sql_ejer)) {
		  if ($oCon->NumFilas() > 0) {
			  do {
				  $optionEjercicio .= '<option value="' . $oCon->f('ejer_cod_ejer') . '">' . $oCon->f('anio') . '</option>';
			  } while ($oCon->SiguienteRegistro());
		  }
	  }
	  $oCon->Free();

      //Periodo

      $sql_mes="select  prdo_num_prdo,   prdo_nom_prdo  from saeprdo where
      prdo_cod_empr = $empresa and
      prdo_cod_ejer = $id_anio";

      $optionMes='';
      if ($oCon->Query($sql_mes)) {
        if ($oCon->NumFilas() > 0) {
            do {
                $optionMes .= '<option value="' . $oCon->f('prdo_num_prdo') . '">' . $oCon->f('prdo_nom_prdo') . '</option>';
            } while ($oCon->SiguienteRegistro());
        }
    }
    $oCon->Free();



      if($tipo=='M'){

        $sHtml=' <div class="form-group col-xs-12 col-sm-12 col-md-3 col-lg-3">                   
            <label class="control-label" for="anio">* Ejercicio:</label>
                <select id="anio" name="anio" class="form-control select2" style="width: 100%;" onchange="cargar_mes();" required>
                    <option value="">..Seleccione una Opcion..</option>
                        ' . $optionEjercicio . '
                </select>
                </div>';

                $sHtml.=' <div class="form-group col-xs-12 col-sm-12 col-md-2 col-lg-2">                   
                <label class="control-label" for="mes">* Mes:</label>
                    <select id="mes" name="mes" class="form-control select2" style="width: 100%;"  required>
                        <option value="">..Seleccione una Opcion..</option>
                            ' . $optionMes . '
                    </select>
                    </div>';

      }

      elseif($tipo=='F'){
      

        $sHtml=' <div class="form-group col-xs-12 col-sm-12 col-md-3 col-lg-3">                   
        <label class="control-label" for="fechaini">* Fecha Inicial:</label>
        <input class="form-control" type="date" step="1" value="'.date('Y-m-01').'"  id="fechaini" name="fechaini"   />  

            </div>
            
            <div class="form-group col-xs-12 col-sm-12 col-md-3 col-lg-3">                   
        <label class="control-label" for="fechafin">* Fecha Final:</label>
        <input class="form-control" type="date" step="1" value="'.date('Y-m-d').'"  id="fechafin" name="fechafin"   />  

            </div>';
    
    }
    else{
        $sHtml=' <div class="form-group col-xs-12 col-sm-12 col-md-3 col-lg-3">                   
            <label class="control-label" for="anio">* Ejercicio:</label>
                <select id="anio" name="anio" class="form-control select2" style="width: 100%;" onchange="cargar_mes();" required>
                    <option value="">..Seleccione una Opcion..</option>
                        ' . $optionEjercicio . '
                </select>
                </div>';

    }

   



    $oReturn->assign("div_filtro", "innerHTML", $sHtml);

    return $oReturn;


}

function cargar_mes($aForm='',$id_anio='')
{
    if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}
    global $DSN_Ifx, $DSN;

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oReturn = new xajaxResponse();

    $empresa = $aForm['empresa'];
    if(empty($empresa)){
        $empresa =  $_SESSION['U_EMPRESA'];    
    }
   

    //  LECTURA SUCIA
    
    $oReturn->script('limpiar_lista();');

    if(empty($id_anio)){

        $id_anio='NULL';
    }

    $sql = "select  prdo_num_prdo,   prdo_nom_prdo  from saeprdo where
    prdo_cod_empr = $empresa and
    prdo_cod_ejer = $id_anio";

    $i = 0;
    $msn = "-- Seleccione una Opcion --";

    if ($oIfx->Query($sql)) {
        if ($oIfx->NumFilas() > 0) {
            do {
                $id = $oIfx->f('prdo_num_prdo');
                $mes = $oIfx->f('prdo_nom_prdo');
                $oReturn->script(('anadir_elemento_comun(' . $i . ',\'' . $id . '\', \'' . $mes . '\' )'));
                $i++;
            } while ($oIfx->SiguienteRegistro());
            $oReturn->script(('anadir_elemento_comun(' . $i . ',"", \'' . $msn . '\' )'));
        } else {
            $oReturn->script('limpiar_lista();');
            $oReturn->script(('anadir_elemento_comun(' . $i . ',"", \'' . $msn . '\' )'));
        }
    }
    $oIfx->Free();



    return $oReturn;
}

function f_filtro_sucursal($aForm, $data)
{
    //Definiciones
    global $DSN, $DSN_Ifx;
    session_start();

    $oCon = new Dbo();
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oIfx = new Dbo();
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oReturn = new xajaxResponse();

    //variables formulario
    $empresa = $aForm['empresa'];

    // DATOS EMPRESA
    $sql = "select sucu_cod_sucu, sucu_nom_sucu
			from saesucu
			where sucu_cod_empr = '$empresa'			
			order by sucu_nom_sucu";
    //echo $sql; exit;
    $i = 1;
    if ($oIfx->Query($sql)) {
        $oReturn->script('eliminar_lista_sucursal();');
        if ($oIfx->NumFilas() > 0) {
            do {
                $oReturn->script(('anadir_elemento_sucursal(' . $i++ . ',\'' . $oIfx->f('sucu_cod_sucu') . '\', \'' . $oIfx->f('sucu_nom_sucu') . '\' )'));
            } while ($oIfx->SiguienteRegistro());
        }
    }
    $oReturn->assign('sucursal', 'value', $data);
    return $oReturn;
}

function f_filtro_ejercicio($aForm, $data)
{
    //Definiciones
    global $DSN, $DSN_Ifx;
    session_start();

    $oCon = new Dbo();
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oIfx = new Dbo();
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oReturn = new xajaxResponse();
    $idempresa = $_SESSION['U_EMPRESA'];
    //variables formulario
    $empresa = $aForm['empresa'];
    if (empty($empresa)) {
        $empresa = $idempresa;
    }
    // DATOS EMPRESA
    $sql = "select ejer_cod_ejer, DATE_PART('year', ejer_fec_inil) as anio
			from saeejer 
			where ejer_cod_empr = $empresa
			order by anio desc";
    //echo $sql; exit;
    $i = 1;
    if ($oIfx->Query($sql)) {
        $oReturn->script('eliminar_lista_anio();');
        if ($oIfx->NumFilas() > 0) {
            // $i = $oIfx->NumFilas();
            do {
                $oReturn->script(('anadir_elemento_anio(' . $i++ . ',\'' . $oIfx->f('ejer_cod_ejer') . '\',\'' . $oIfx->f('anio') . '\')'));
            } while ($oIfx->SiguienteRegistro());
        }
    }
    // AÑO ACTUAL
    $sql_ejer = "select ejer_cod_ejer from saeejer where ejer_cod_empr = $empresa and DATE_PART('year', ejer_fec_inil) = DATE_PART('year', CURRENT_DATE)";
    $data = consulta_string($sql_ejer, 'ejer_cod_ejer', $oIfx, 0);
    $oReturn->assign('ejercicio', 'value', $data);
    $oReturn->script("f_filtro_periodo();");
    return $oReturn;
}

function f_filtro_periodo($aForm, $data)
{
    //Definiciones
    global $DSN, $DSN_Ifx;
    session_start();

    $oCon = new Dbo();
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oIfx = new Dbo();
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oReturn = new xajaxResponse();


    // variables de session
    $idempresa = $_SESSION['U_EMPRESA'];
    $idsucursal = $_SESSION['U_SUCURSAL'];

    //variables formulario
    $ejercicio = $aForm['ejercicio'];
    $empresa = $aForm['empresa'];
    $sucursal = $aForm['sucursal'];

    if (empty($empresa)) {
        $empresa = $idempresa;
    }
    if (empty($sucursal)) {
        $sucursal = $idsucursal;
    }


    // DATOS DEL PERIODO
    $sql = "select prdo_num_prdo, prdo_nom_prdo
			from saeprdo
			where prdo_cod_empr = '$empresa'
			and prdo_cod_ejer = '$ejercicio'			
			order by prdo_num_prdo";
    //echo $sql; exit;
    $i = 1;
    if ($oIfx->Query($sql)) {
        $oReturn->script('eliminar_lista_periodo();');
        if ($oIfx->NumFilas() > 0) {
            do {
                $oReturn->script(('anadir_elemento_periodo(' . $i++ . ',\'' . $oIfx->f('prdo_num_prdo') . '\', \'' . $oIfx->f('prdo_nom_prdo') . '\' )'));
            } while ($oIfx->SiguienteRegistro());
        }
    }
    // BUSCAR MES ACTUAL
    $sql_periodo = "select prdo_num_prdo from saeprdo where prdo_cod_empr = $empresa and prdo_cod_ejer = $ejercicio and DATE_PART('month',prdo_fec_ini) = DATE_PART('month',CURRENT_DATE)";
    $data = consulta_string($sql_periodo, 'prdo_num_prdo', $oIfx, 0);
    //echo $data; exit;
    $oReturn->assign('periodo', 'value', $data);
    return $oReturn;
}
function genera_documento($tipo_documento = 0, $id = '', $clavAcce = 'no_autorizado', $clpv = 0, $num_fact = '', $ejer = 0, $asto = '', $fec_emis = '', $sucu = 0)
{
    session_start();
    global $DSN_Ifx;

    $oReturn = new xajaxResponse();

    switch ($tipo_documento) {
        case 1:
            $_SESSION['pdf'] = reporte_factura($id, $clavAcce, $sucu);
            break;
        case 2:
            $_SESSION['pdf'] = reporte_notaDebito($id, $clavAcce);
            break;
        case 3:
            $_SESSION['pdf'] = reporte_notaCredito($id, $clavAcce, $sucu);
            break;
        case 4:
            $_SESSION['pdf'] = reporte_guiaRemision($id, $clavAcce, $sucu);
            break;
        case 5:
            $id = $_SESSION['sqlId'][$id];
            $_SESSION['pdf'] = reporte_retencionGasto($id, $clavAcce, $rutapdf, $clpv, $num_fact, $ejer, $asto, $fec_emis, $sucu);
            break;
        case 6:
            $id = $_SESSION['sqlId'][$id];
            $_SESSION['pdf'] = reporte_retencionInve($id, $clavAcce, $rutapdf, $clpv, $num_fact, $ejer, $asto, $fec_emis, $sucu);
            break;
        case 7:
            $_SESSION['pdf'] = reporte_factura_export($id, $clavAcce);
            break;
        case 8:
            $_SESSION['pdf'] = reporte_factura_flor($id, $clavAcce);
            break;
        case 9:
            $_SESSION['pdf'] = reporte_factura_flor_export($id, $clavAcce);
            break;
        case 10:
            $_SESSION['pdf'] = reporte_guiaRemisionFlor($id, $clavAcce);
            break;
    }

    $oReturn->script('generar_pdf()');

    return $oReturn;
}

function eliminar_acentos($cadena)
{

    //Reemplazamos la A y a
    $cadena = str_replace(
        array('Á', 'À', 'Â', 'Ä', 'á', 'à', 'ä', 'â', 'ª'),
        array('A', 'A', 'A', 'A', 'a', 'a', 'a', 'a', 'a'),
        $cadena
    );

    //Reemplazamos la E y e
    $cadena = str_replace(
        array('É', 'È', 'Ê', 'Ë', 'é', 'è', 'ë', 'ê'),
        array('E', 'E', 'E', 'E', 'e', 'e', 'e', 'e'),
        $cadena
    );

    //Reemplazamos la I y i
    $cadena = str_replace(
        array('Í', 'Ì', 'Ï', 'Î', 'í', 'ì', 'ï', 'î'),
        array('I', 'I', 'I', 'I', 'i', 'i', 'i', 'i'),
        $cadena
    );

    //Reemplazamos la O y o
    $cadena = str_replace(
        array('Ó', 'Ò', 'Ö', 'Ô', 'ó', 'ò', 'ö', 'ô'),
        array('O', 'O', 'O', 'O', 'o', 'o', 'o', 'o'),
        $cadena
    );

    //Reemplazamos la U y u
    $cadena = str_replace(
        array('Ú', 'Ù', 'Û', 'Ü', 'ú', 'ù', 'ü', 'û'),
        array('U', 'U', 'U', 'U', 'u', 'u', 'u', 'u'),
        $cadena
    );


    return $cadena;
}

function generar($aForm = '')
{
    //Definiciones
    global $DSN, $DSN_Ifx;

    session_start();

    $oCon = new Dbo();
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oIfx = new Dbo();
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oIfxA = new Dbo();
    $oIfxA->DSN = $DSN_Ifx;
    $oIfxA->Conectar();

    $oIfxB = new Dbo();
    $oIfxB->DSN = $DSN_Ifx;
    $oIfxB->Conectar();


    $oReturn = new xajaxResponse();

    //variables de sesion
    $array = ($_SESSION['ARRAY_PINTA']);
    $usuario_web = $_SESSION['U_ID'];
    $idempresa = $_SESSION['U_EMPRESA'];
    $idsucursal = $_SESSION['U_SUCURSAL'];

    $empresa = $aForm['empresa'];
    $sucursal = $aForm['sucursal'];

    //CONTROL REGISTROS
    $ctrlreg = 0;

    if (empty($empresa)) {
        $empresa = $idempresa;
    }
    if (!empty($sucursal)) {
        $idsucursal = $sucursal;
        $filtro = "and saeret.asto_cod_sucu = " . $sucursal;
        $filtro_gasto = "fprv_cod_sucu = " . $sucursal . " and";
        $filtro_compras = "and minv_cod_sucu = " . $sucursal;
        $filtro_ncre = "and ncre_cod_sucu = " . $sucursal;
    }

   
    $ejercicio 	= $aForm['anio'];
    if(empty($ejercicio)){
        $ejercicio='NULL';
    }
    $periodo 	= $aForm['mes'];

    $filtroPeriodo= $aForm['filtro'];
    if($filtroPeriodo=='1S'){
        $filmes="in (1,2,3,4,5,6)";
        $filnc="in ('01','02','03','04','05','06')";
    }
    elseif($filtroPeriodo=='2S'){
        $filmes="in (7,8,9,10,11,12)";
        $filnc="in ('07','08','09','10','11','12')";
    }
    else{
        $filmes="in($periodo)";
        if ($periodo <= 9) {
            $periodo = '0' . $periodo;
        }
        $filnc="in('$periodo')";
    }


    $detallado = $aForm['detallado'];

    $sql_anio = "select DATE_PART('year',ejer_fec_inil) as anio from saeejer where ejer_cod_empr = $empresa and ejer_cod_ejer=$ejercicio";
    $anio = consulta_string($sql_anio, 'anio', $oIfx, 0);

    $sql_impuesto_empr = "SELECT empr_iva_empr from saeempr where empr_cod_empr = $empresa";
    $impuesto_empresa = consulta_string($sql_impuesto_empr, 'empr_iva_empr', $oIfx, 0);

    /*if ($periodo <= 9) {
        $mes = '0' . $periodo;
    }*/

    try {
        $oIfx->QueryT('BEGIN');
        // TIPOS DE RETENCIONES
        $sql = " select distinct ret_cta_ret,  tret_ban_retf 
					from saeret, saetret 
					where tret_cod = ret_cta_ret
					and tret_cod_empr = asto_cod_empr
					and asto_cod_empr = $empresa
					and asto_cod_ejer = $ejercicio
					$filtro
					order by 2";
        //echo $sql; exit;
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                unset($arrayTipoReteciones);
                do {
                    $arrayTipoReteciones[] = array($oIfx->f('ret_cta_ret'), $oIfx->f('tret_ban_retf'));
                } while ($oIfx->SiguienteRegistro());
            }
        }

        $html = '<table id="tbcompras" class="table table-striped table-bordered table-hover table-condensed" style=" width: 100%;" >';
        $html .= '<thead>';



        //var_dump ($arrayTipoReteciones); exit;
        $oIfx->Free($arrayTipoReteciones);
        if (count($arrayTipoReteciones) > 0) {
            unset($arrayA);
            $ll_row = count($arrayTipoReteciones);
            for ($k = 0; $k < ($ll_row); $k++) {
                if ($k < ($ll_row - 1)) {
                    if ($arrayTipoReteciones[$k][1] != $arrayTipoReteciones[$k + 1][1]) {
                        $arrayA[] = array($k + 1, $arrayTipoReteciones[$k][1]);
                    }
                } else {
                    $arrayA[] = array($k + 1, $arrayTipoReteciones[$k][1]);
                }
            }

            unset($_SESSION['ACT_REPORTE']);
            $html .= '<tr>';
            if ($detallado == 'S') {
                $html .= '<td class="bg-primary" align = "center" colspan="19">Compras</td>';
                foreach ($arrayA as $arrayB) {
                    if ($arrayB[1] == 'IR') {
                        $impuesto = 'RETENCION RENTA';
                    } else {
                        $impuesto = 'RETENCION IVA';
                    }
                    $html .= '<td class="bg-primary" align = "center" colspan="' . $arrayB[0] . '">' . $impuesto . '</td>';
                }
            } else {
                $html .= '<td class="bg-primary" align = "center" colspan="19">Compras</td>';
            }
            $html .= '</tr>';
        }


        /// nombre transacciones
        $sql = "SELECT tran_cod_tran, tran_des_tran, trans_tip_comp from saetran where tran_cod_empr='$empresa' and trim(tran_des_tran) <> 'FACTURA CLIENTES'";
        //var_dump($sql);exit;

        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                unset($arrayTipoTransacion);
                do {
                    $arrayTipoTransacion[$oIfx->f('tran_cod_tran')] =  array($oIfx->f('tran_des_tran'), $oIfx->f('trans_tip_comp'));
                } while ($oIfx->SiguienteRegistro());
            }
        }
        /// nombre crtr
        $sql = "SELECT crtr_cod_crtr, crtr_des_crtr from saecrtr ";
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                unset($arrayTipoCrtr);
                do {
                    $arrayTipoCrtr[$oIfx->f('crtr_cod_crtr')] =  $oIfx->f('crtr_des_crtr');
                } while ($oIfx->SiguienteRegistro());
            }
        }
        // REPORTE

        //CAMBIO CON_IVA SIN_IVA PARA QUE TOEM DIRTECTPO DE LA COLUMNAS MIN_CON_IVA Y MINV_SIN_IVA
        /* 
         round( COALESCE ( minv_con_iva, 0 ), 2 ) as con_iva ,
                        round( COALESCE ( minv_sin_iva, 0 ), 2 ) as total_graba_0 ,
        */
        /* $sql = "
			select fprv_fec_emis, fprv_cod_sucu, 
						fprv_num_seri,
						fprv_num_fact,
						fprv_cod_asto,
						fprv_cod_tran,
						clpv_nom_clpv,
                        clpv_ruc_clpv,
                        fprv_cod_clpv,
						fprv_det_fprv,	
						round(COALESCE(fprv_val_grab, 0) + COALESCE(fprv_val_grbs, 0), 2) as total_graba_12,
						round(COALESCE(fprv_val_gra0, 0) + COALESCE(fprv_val_gr0s, 0), 2) as total_graba_0,
						round(COALESCE(fprv_val_viva, 0), 2) as valor_iva,
						round(COALESCE(fprv_val_grab, 0) + COALESCE(fprv_val_grbs, 0) + COALESCE(fprv_val_gra0, 0) + COALESCE(fprv_val_gr0s, 0) + COALESCE(fprv_val_viva, 0), 2) as total,
						COALESCE(fprv_val_noi,0) as no_ojeto_iva,
						COALESCE(fprv_val_exe,0) as exento_iva,
						ret_num_ret,
						fprv_cre_fisc as sustento,
                        fprv_num_auto as autorizacion,
                        ret_aut_ret,
                        ret_ser_ret,
                        ret_num_ret
					FROM saeasto, saefprv, saeclpv, saeret 
					WHERE ( saeret.rete_cod_asto = saeasto.asto_cod_asto ) and  
						( saeret.asto_cod_empr = saeasto.asto_cod_empr ) and  
						( saeret.asto_cod_sucu = saeasto.asto_cod_sucu ) and  
						( saeret.asto_cod_ejer = saeasto.asto_cod_ejer ) and  
						( saefprv.fprv_cod_asto = saeasto.asto_cod_asto ) and  
						( saefprv.fprv_cod_empr = saeasto.asto_cod_empr ) and  
						( saefprv.fprv_cod_sucu = saeasto.asto_cod_sucu ) and  
						( saefprv.fprv_cod_ejer = saeasto.asto_cod_ejer ) and
						( saefprv.fprv_cod_clpv = saeclpv.clpv_cod_clpv ) and
						( saefprv.fprv_cod_empr = saeclpv.clpv_cod_empr ) and 
						fprv_cod_empr = $empresa and 
						$filtro_gasto
						fprv_cod_ejer = $ejercicio and
						 DATE_PART('month',fprv_fec_emis) = $periodo and 
						saeret.asto_num_prdo = $periodo and
						asto_est_asto not in ('AN', 'PE')
					UNION 
					select minv_fmov,minv_cod_sucu,
						minv_ser_docu as serie,
						minv_fac_prov,
						minv_tran_minv,
						minv_cod_tran,
						clpv_nom_clpv,
                        clpv_ruc_clpv,
                        minv_cod_clpv,
						minv_cm1_minv as fprv_det_fprv,	
                        round( ( ( COALESCE ( minv_iva_valo, 0 ) * 100 ) / 12 ), 2 ) AS con_iva ,

                        round(
                            (
                                (
                                    minv_tot_minv - COALESCE ( minv_dge_valo, 0 ) + COALESCE ( minv_otr_valo, 0 ) + COALESCE ( minv_fle_minv, 0 ) 
                                ) - round( ( ( COALESCE ( minv_iva_valo, 0 ) * 100 ) / 12 ), 2 ) 
                            ),
                            2 
                        ) as total_graba_0 ,
                        
						round(COALESCE(minv_iva_valo,0),2) as valor_iva,
						round((minv_tot_minv -COALESCE(minv_dge_valo,0) + COALESCE(minv_otr_valo,0 ) + COALESCE(minv_fle_minv,0) + COALESCE(minv_iva_valo,0)),2)  as total,
						COALESCE(minv_val_noi, '0') as no_ojeto_iva,
						COALESCE(minv_val_exe, '0') as exento_iva,
						ret_num_ret,
						minv_cod_crtr as sustento,
						minv_aut_usua as autorizacion,
                        ret_aut_ret,
                        ret_ser_ret,
                        ret_num_ret
                        
					from saeminv, saeclpv, saeret
					where rete_cod_asto = minv_tran_minv
					and asto_cod_empr = minv_cod_empr   
					and asto_cod_sucu = minv_cod_sucu   
					and asto_cod_ejer = minv_cod_ejer    
					and minv_cod_clpv = clpv_cod_clpv
					and minv_cod_empr = clpv_cod_empr
					and minv_cod_tran in ( select D.DEFI_COD_TRAN 
										   from SAEDEFI D, SAETRAN T 
										   WHERE T.TRAN_COD_TRAN = D.DEFI_COD_TRAN 
										   AND D.DEFI_COD_MODU = 10 
										   AND D.DEFI_COD_EMPR = $empresa 
										   AND D.DEFI_TIP_DEFI = '0' 
										   AND D.DEFI_TIP_COMP in ( '01'  , '03', '02') 
										   AND T.TRAN_COD_EMPR = $empresa )

					and minv_cod_empr  = $empresa
					$filtro_compras
					and minv_cod_ejer = $ejercicio
					and DATE_PART('month',minv_fmov) = $periodo
					and saeret.asto_num_prdo = $periodo
					and minv_est_minv = 'M'
					group by 1,2,3,4,5,6,7,8,9,10,11,12,13, 14,15,16,17,18, 19, 20, 21
					order by 1,10,3 
			";*/





        $sql = "SELECT
             fprv_fec_emis, fprv_cod_sucu, 0 as minv_num_comp,
						fprv_num_seri,
						fprv_num_fact,
						fprv_cod_asto,
						fprv_cod_tran,
						clpv_nom_clpv,
                        clpv_ruc_clpv,
                        fprv_cod_clpv,
						fprv_det_fprv,	
						round(COALESCE(fprv_val_grab, 0) + COALESCE(fprv_val_grbs, 0), 2) as total_graba_12,
						round(COALESCE(fprv_val_gra0, 0) + COALESCE(fprv_val_gr0s, 0), 2) as total_graba_0,
						round(COALESCE(fprv_val_viva, 0), 2) as valor_iva,
						round(COALESCE(fprv_val_grab, 0) + COALESCE(fprv_val_grbs, 0) + COALESCE(fprv_val_gra0, 0) + COALESCE(fprv_val_gr0s, 0) + COALESCE(fprv_val_viva, 0), 2) as total,
						COALESCE(fprv_val_noi,0) as no_ojeto_iva,
						COALESCE(fprv_val_exe,0) as exento_iva,
						ret_num_ret,
                        fprv_cre_fisc as sustento,
                        fprv_num_auto as autorizacion,
                        ret_aut_ret,
                        ret_ser_ret,
                        ret_num_ret
					FROM saeasto

                    left join saeret
                        on saeret.rete_cod_asto = saeasto.asto_cod_asto 
                            AND saeret.asto_cod_empr = saeasto.asto_cod_empr 
                            AND saeret.asto_cod_sucu = saeasto.asto_cod_sucu 
                            AND saeret.asto_cod_ejer = saeasto.asto_cod_ejer

                    inner join saefprv
                            on saefprv.fprv_cod_asto = saeasto.asto_cod_asto
                            and saefprv.fprv_cod_empr = saeasto.asto_cod_empr
                            AND saefprv.fprv_cod_sucu = saeasto.asto_cod_sucu
                            AND saefprv.fprv_cod_ejer = saeasto.asto_cod_ejer

                    inner join saeclpv
                        on saefprv.fprv_cod_clpv = saeclpv.clpv_cod_clpv 
                        AND  saefprv.fprv_cod_empr = saeclpv.clpv_cod_empr 
					WHERE 
						fprv_cod_empr = $empresa and 
						$filtro_gasto
						fprv_cod_ejer = $ejercicio and
						 DATE_PART('month',fprv_fec_emis) $filmes and 
						(saeret.asto_num_prdo $filmes or saeret.asto_num_prdo is null)
                        and asto_est_asto not in ('AN', 'PE')
                        and fprv_cod_tran not in (select tran_cod_tran from saetran where tran_cod_empr = $empresa and (trim(tran_cod_tran) like 'NDC%' OR trim(tran_cod_tran) like 'NCR%' ) )
					UNION 
					select minv_fmov,minv_cod_sucu, minv_num_comp,
						minv_ser_docu as serie,
						minv_fac_prov,
						minv_tran_minv,
						minv_cod_tran,
						clpv_nom_clpv,
                        clpv_ruc_clpv,
                        minv_cod_clpv,
						minv_cm1_minv as fprv_det_fprv,	
                        round(((COALESCE(minv_iva_valo,0) * 100 ) / 15 ),2) as con_iva,
				        round(((minv_tot_minv - COALESCE(minv_dge_valo, 0) + COALESCE(minv_otr_valo, 0) + COALESCE(minv_fle_minv, 0)) -  round((( COALESCE(minv_iva_valo, 0) * 100 ) / 15 ),2)),2) as total_graba_0,
						round(COALESCE(minv_iva_valo,0),2) as valor_iva,
						round((minv_tot_minv -COALESCE(minv_dge_valo,0) + COALESCE(minv_otr_valo,0 ) + COALESCE(minv_fle_minv,0) + COALESCE(minv_iva_valo,0)),2)  as total,
						COALESCE(minv_val_noi, '0') as no_ojeto_iva,
						COALESCE(minv_val_exe, '0') as exento_iva,
						ret_num_ret,
						minv_cod_crtr as sustento,
						minv_aut_usua as autorizacion,
                        ret_aut_ret,
                        ret_ser_ret,
                        ret_num_ret
					from saeminv
                    
                    left join saeclpv
                            on minv_cod_clpv = clpv_cod_clpv
                            and minv_cod_empr = clpv_cod_empr
                    left join saeret
                            on rete_cod_asto = minv_tran_minv
                            and asto_cod_empr = minv_cod_empr   
                            and asto_cod_sucu = minv_cod_sucu   
                            and asto_cod_ejer = minv_cod_ejer 
                     
					where
                            minv_cod_tran in ( select D.DEFI_COD_TRAN 
										   from SAEDEFI D, SAETRAN T 
										   WHERE T.TRAN_COD_TRAN = D.DEFI_COD_TRAN 
										   AND D.DEFI_COD_MODU = 10 
										   AND D.DEFI_COD_EMPR = $empresa 
										   AND D.DEFI_TIP_DEFI = '0' 
										   AND D.DEFI_TIP_COMP in ( '01'  , '03', '02') 
										   AND T.TRAN_COD_EMPR = $empresa )

					and minv_cod_empr  = $empresa
					$filtro_compras
					and minv_cod_ejer = $ejercicio
					and DATE_PART('month',minv_fmov) $filmes
					and (saeret.asto_num_prdo $filmes or saeret.asto_num_prdo is null) 
					and minv_est_minv = 'M'
                    and minv_cod_tran not in (select tran_cod_tran from saetran where tran_cod_empr = $empresa and (trim(tran_cod_tran) like 'NDC%' OR trim(tran_cod_tran) like 'NCR%' ) )
					group by 1,2,3,4,5,6,7,8,9,10,11,12,13, 14,15,16,17,18, 19, 20, 21, 22
					order by 1,10,4 
			";
        //echo $sql; exit;
        //$oReturn->alert($sql);
        //exit;
        if ($oIfx->Query($sql)) {
            if ($oIfx->NumFilas() > 0) {
                $ctrlreg++;
                unset($arrayCompras);
                do {
                    //$base_imp_renta = 0;
                    //$base_imp_renta = $oIfx->f('total_graba_12');
                    $fecha = '<font color="blue">' . $oIfx->f('fprv_fec_emis') . '</font>';

                    $fprv_num_fact = $oIfx->f('fprv_num_fact');
                    $fprv_cod_clpv = $oIfx->f('fprv_cod_clpv');
                    if(empty($fprv_cod_clpv)){
                        $fprv_cod_clpv ='NULL';
                    }
                    $fprv_cod_sucu = $oIfx->f('fprv_cod_sucu');

                    //FACTURAS PEQUEÑAS
                    $sqlf = "select fprr_cod_tran,fprr_num_fact,fprr_num_seri,fprr_fec_regc,fprr_det_fprr, fprr_fec_emis,fprr_num_esta,fprr_val_noi,fprr_val_exe,
                    fprr_val_tots,fprr_val_gras,fprr_val_grab,fprr_val_gra0, fprr_val_grs0,fprr_val_vivs,fprr_val_vivb, fprr_val_ices,fprr_val_totl,fprr_ruc_prov, fprr_nom_prov
                     from saefprr where  fprr_fac_fprv='$fprv_num_fact' and fprr_cod_ejer=$ejercicio and fprr_cod_sucu=$fprv_cod_sucu and fprr_clpv_fprv=$fprv_cod_clpv order by fprr_cod_fprr";

                    $clpv_nom_clpv = '<font color="blue">' . $oIfx->f('clpv_nom_clpv') . '</font>';
                    $clpv_ruc_clpv = '<font color="blue">' . $oIfx->f('clpv_ruc_clpv') . '</font>';


                    $tran = $arrayTipoTransacion[$oIfx->f('fprv_cod_tran')][0];
                    $tran = '<font color="blue">' . $tran . '</font>';
                    $tran_cod = $arrayTipoTransacion[$oIfx->f('fprv_cod_tran')][1];

                    $cod_sustento = $oIfx->f('sustento');
                    if (empty($cod_sustento)) {
                        $cod_tran = $oIfx->f('fprv_cod_tran');
                        $sql = "select defi_cod_crtr from saedefi where defi_cod_tran='$cod_tran'";
                        $cod_sus = consulta_string($sql, 'defi_cod_crtr', $oCon, '');
                        $crtr = $cod_sus . '-' . substr($arrayTipoCrtr[$cod_sus], 0, 38);
                    } else {
                        $crtr = $oIfx->f('sustento') . '-' . substr($arrayTipoCrtr[$oIfx->f('sustento')], 0, 38);
                    }

                    $crtr = '<font color="blue">' . $crtr . '</font>';
                    $fprv_num_seri = '<font color="blue">' . $oIfx->f('fprv_num_seri') . '</font>';
                    $fprv_num_fact = '<font color="blue">' . $oIfx->f('fprv_num_fact') . '</font>';
                    $fprv_det_fprv = '<font color="blue">' . $oIfx->f('fprv_det_fprv') . '</font>';

                    $autorizacion_ad = $oIfx->f('autorizacion');
                    $ret_aut_ret_ad = $oIfx->f('ret_aut_ret');
                    $ret_ser_ret_ad = $oIfx->f('ret_ser_ret');
                    $ret_num_ret_ad = $oIfx->f('ret_num_ret');
                    $campos_adicionales = "$autorizacion_ad/$ret_aut_ret_ad/$ret_ser_ret_ad/$ret_num_ret_ad/$fprv_cod_sucu";

                    /////// Agregado por B@nch ////////
                    $minv_num_comp = $oIfx->f('minv_num_comp');
                    if ($minv_num_comp == 0)
                    {
                        $total_graba_iva = $oIfx->f('total_graba_12');
                        $total_graba_iva5 = 0;
                        $total_graba_0 = $oIfx->f('total_graba_0');
                        $total_porc15 = $total_graba_iva * 0.15;
                        $total_porc5 = 0;
        }
                    else
                    {
                        $sqlg = "select sum(case when dmov_iva_porc=0 then dmov_cto_dmov else 0 end) as iva0, sum(case when dmov_iva_porc=5 then dmov_cto_dmov else 0 end) as iva5, 
                        sum(case when dmov_iva_porc=15 then dmov_cto_dmov else 0 end) as iva15, sum(case when dmov_iva_porc=15 then dmov_cto_dmov else 0 end) * 0.15 as porciva15, 
                        sum(case when dmov_iva_porc=5 then dmov_cto_dmov else 0 end) * 0.05 as porciva5 
                        from saedmov where dmov_cod_empr=$empresa and dmov_cod_ejer=$ejercicio and dmov_num_prdo=$periodo and dmov_num_comp=$minv_num_comp";
                        if ($oIfxB->Query($sqlg)) 
                        {
                            if ($oIfxB->NumFilas() > 0) 
                            {
                                do 
                                {
                                    $total_graba_iva = $oIfxB->f('iva15');
                                    $total_graba_iva5 = $oIfxB->f('iva5');
                                    $total_graba_0 = $oIfxB->f('iva0');
                                    $total_porc15 = $oIfxB->f('porciva15');
                                    $total_porc5 = $oIfxB->f('porciva5');
                                }  while ($oIfxB->SiguienteRegistro());
                            }
                        }
                    }
                        //$total_graba_0 = $oIfx->f('total_graba_0');
                    //////// Hasta Aqui Agregado por B@nch /////////

                    if ($total_graba_0 < 0) {
                        $total_graba_0 = 0;
                    }

                    $arrayCompras[] = array(
                        $fecha,    $fprv_num_seri, $fprv_num_fact, $oIfx->f('fprv_cod_asto'),  $tran, $clpv_nom_clpv,      $fprv_det_fprv, $total_graba_iva,  $total_graba_iva5,  $total_graba_0, $total_porc15,   $total_porc5,      $oIfx->f('total'), $oIfx->f('no_ojeto_iva'),         $oIfx->f('exento_iva'), $oIfx->f('ret_num_ret'),      $total_graba_iva + $total_graba_iva5, $total_porc15 + $total_porc5, $crtr, $tran_cod, $clpv_ruc_clpv, $campos_adicionales
                        //$fecha,    $fprv_num_seri, $fprv_num_fact, $oIfx->f('fprv_cod_asto'),  $tran, $clpv_nom_clpv,      $fprv_det_fprv, $oIfx->f('total_graba_12'),    $total_graba_0, $oIfx->f('valor_iva'),         $oIfx->f('total'), $oIfx->f('no_ojeto_iva'),         $oIfx->f('exento_iva'), $oIfx->f('ret_num_ret'),      $oIfx->f('total_graba_12'), $oIfx->f('valor_iva'), $crtr, $tran_cod, $clpv_ruc_clpv, $campos_adicionales
                    );

                    //fcaturas pequeñas
                    if ($oIfxA->Query($sqlf)) {
                        if ($oIfxA->NumFilas() > 0) {

                            do {
                                $fprr_cod_tran = $oIfxA->f('fprr_cod_tran');
                                $fprr_num_fact = $oIfxA->f('fprr_num_fact');
                                $fprr_num_seri = $oIfxA->f('fprr_num_seri');
                                $fprr_num_esta = $oIfxA->f('fprr_num_esta');

                                $fprr_serie = $fprr_num_seri . $fprr_num_esta;
                                $fprr_fec_emis = $oIfxA->f('fprr_fec_emis');
                                $fprr_det_fprr = $oIfxA->f('fprr_det_fprr');
                                $fprr_val_tots = $oIfxA->f('fprr_val_tots');

                                $fprr_val_gras = $oIfxA->f('fprr_val_gras');
                                $fprr_val_grab = $oIfxA->f('fprr_val_grab');

                                $fprr_con_iva = $fprr_val_gras + $fprr_val_grab;

                                $fprr_val_gra0 = $oIfxA->f('fprr_val_gra0');
                                $fprr_val_grs0 = $oIfxA->f('fprr_val_grs0');

                                $fprr_sin_iva = $fprr_val_gra0 + $fprr_val_grs0;

                                $fprr_con_iva = $fprr_con_iva + $fprr_sin_iva;

                                $fprr_val_noi = $oIfxA->f('fprr_val_noi');
                                $fprr_val_exe = $oIfxA->f('fprr_val_exe');


                                $fprr_val_vivs = $oIfxA->f('fprr_val_vivs');
                                $fprr_val_vivb = $oIfxA->f('fprr_val_vivb');

                                // $fprr_iva = $fprr_val_vivs + $fprr_val_vivb;


                                $fprr_val_ices = $oIfxA->f('fprr_val_ices');
                                //total general
                                $fprr_val_totl = $oIfxA->f('fprr_val_totl');
                                $fprr_ruc_prov = $oIfxA->f('fprr_ruc_prov');
                                $fprr_nom_prov = $oIfxA->f('fprr_nom_prov');

                                $fprr_ruc_prov = mberegi_replace("[\n|\r|\n\r|\t|\0|\x0B]", "", $fprr_ruc_prov);
                                $fprr_ruc_prov = str_replace('"', "", $fprr_ruc_prov);
                                $fprr_ruc_prov = str_replace("'", "", $fprr_ruc_prov);

                                $fprr_nom_prov = mberegi_replace("[\n|\r|\n\r|\t|\0|\x0B]", "", $fprr_nom_prov);
                                $fprr_nom_prov = str_replace('"', "", $fprr_nom_prov);
                                $fprr_nom_prov = str_replace("'", "", $fprr_nom_prov);

                                $fprr_det_fprr = mberegi_replace("[\n|\r|\n\r|\t|\0|\x0B]", "", $fprr_det_fprr);
                                $fprr_det_fprr = str_replace('"', "", $fprr_det_fprr);
                                $fprr_det_fprr = str_replace("'", "", $fprr_det_fprr);

                                $fminv_dge_valo = 0;
                                $fret_cta_ret = '';
                                $fdivRetencion = '';
                                $ret_bas_imp = 0;
                                $ret_valor = 0;
                                $fdivAsto = '';

                                $tran = $arrayTipoTransacion[$fprr_cod_tran][0];
                                $tran_cod = $arrayTipoTransacion[$fprr_cod_tran][1];
                                $crtr = '';

                                $autorizacion_ad = '';
                                $ret_aut_ret_ad = '';
                                $ret_ser_ret_ad = '';
                                $ret_num_ret_ad = '';



                                $campos_adicionales = "$autorizacion_ad/$ret_aut_ret_ad/$ret_ser_ret_ad/$ret_num_ret_ad/$fprv_cod_sucu";
                                $f = 1;
                                foreach ($arrayTipoReteciones as $arrayCuetas) {
                                    if ($f == count($arrayTipoReteciones)) {
                                        $campos_adicionales .= '0.00';
                                    } else {
                                        $campos_adicionales .= '0.00/';
                                    }
                                    $f++;
                                }
                                $arrayCompras[] = array(
                                    $fprr_fec_emis,    $fprr_serie, $fprr_num_fact, '',  $tran, $fprr_nom_prov,       $fprr_det_fprr, $fprr_con_iva, 0,   $fprr_sin_iva, $fprr_iva,    0,      $fprr_val_totl, $fprr_val_noi,         $fprr_val_exe, 0,      $fprr_con_iva, $fprr_iva, $crtr, $tran_cod, $fprr_ruc_prov, $campos_adicionales
                                );
                            } while ($oIfxA->SiguienteRegistro());
                        }
                    }
                    $oIfxA->Free();
                } while ($oIfx->SiguienteRegistro());
            }
        }

//        foreach ($arrayCompras as $compra) {
//            echo $compra[0] . " - " . $compra[20] . "<br>";
//        }
//        exit;
        


        $oIfx->Free($arrayCompras);
        unset($arrayTotales);
        //echo count($arrayCompras); exit; 
        if (count($arrayCompras) > 0) {
            for ($i = 0; $i < count($arrayCompras); $i++) 
            {
                $asiento = $arrayCompras[$i][3];
                $numRete = $arrayCompras[$i][15];
                $sql = "select ret_cta_ret, ret_valor												
						from saeasto, saeret
						where ( saeret.rete_cod_asto = saeasto.asto_cod_asto )
						and ( saeret.asto_cod_empr = saeasto.asto_cod_empr ) 
						and ( saeret.asto_cod_sucu = saeasto.asto_cod_sucu ) 
						and ( saeret.asto_cod_ejer = saeasto.asto_cod_ejer ) 
						and saeasto.asto_cod_asto = '$asiento'
						and saeret.ret_num_ret = '$numRete'
						and saeasto.asto_cod_ejer = $ejercicio
						and saeasto.asto_cod_empr = $empresa
						group by 1,2
						order by 1";

                //$oReturn->alert($sql);
                if ($oIfx->Query($sql)) {
                    if ($oIfx->NumFilas() > 0) {
                        unset($arrayRetenciones);
                        do {
                            $arrayRetenciones[$oIfx->f('ret_cta_ret')] = array($oIfx->f('ret_valor'));
                        } while ($oIfx->SiguienteRegistro());
                        $oIfx->Free($arrayRetenciones);
                        //var_dump($arrayRetenciones); exit;
                        if (count($arrayTipoReteciones > 0)) {
                            $k = 22;
                            for ($j = 0; $j < count($arrayTipoReteciones); $j++) {
                                //$k = count($arrayTipoReteciones);
                                $r =  $k + $j;
                                $indice = $arrayTipoReteciones[$j][0];
                                //echo $indice; exit;
                                $valor = $arrayRetenciones[$indice][0];

                                $arrayCompras[$i][$r] = $valor;
                                $arrayTotales[$r] = $arrayTotales[$r] + $valor;
                            }
                        }
                    }
                }
            }
        }


        // ORDENAMINETO DEL ARREGLO POR EL INDICE
        // ksort($arrayTotales);
        //var_dump($arrayCompras); exit;
        //$oReturn->alert($sql);

        // GENERA TABLA DE COMPRAS - RETENCIONES
        if (count($arrayCompras) > 0) {
            $html .= '<tr>	
                            <td class="bg-primary" align = "center"> Nro </td>						
							<td class="bg-primary" align = "center"> Fecha </td>
							<td class="bg-primary" align = "center"> Serie </td>
							<td class="bg-primary" align = "center"> Documento </td>
							<td class="bg-primary" align = "center"> Comprobante</td>
                            <td class="bg-primary" align = "center"> Autorizacion</td>
							<td class="bg-primary" align = "center"> Doc. Tibutario </td>
							<td class="bg-primary" align = "center"> Sus. Tibutario </td>
							<td class="bg-primary" align = "center"> Ruc</td>
                            <td class="bg-primary" align = "center"> Proveedor	</td>
							<td class="bg-primary" align = "center"> Detalle </td>
							<td class="bg-primary" align = "center"> Con impuesto 15%</td>
                            <td class="bg-primary" align = "center"> Con impuesto 5%</td>
							<td class="bg-primary" align = "center"> Sin impuesto</td>
							<td class="bg-primary" align = "center"> Impuesto 15%</td>
                            <td class="bg-primary" align = "center"> Impuesto 5%</td>
							<td class="bg-primary" align = "center"> Total </td>
							<td class="bg-primary" align = "center"> No Objeto Impuesto </td>
							<td class="bg-primary" align = "center"> Exento de Impuesto </td>';
            if ($detallado == 'S') {
                $html .= '<td class="bg-primary" align = "center"> Reteci&oacuten </td>
							<td class="bg-primary" align = "center"> Base Imponible Renta </td>
							<td class="bg-primary" align = "center"> Base Imponible Impuesto </td>';
                foreach ($arrayTipoReteciones as $arrayCuetas) {
                    $html .= '<td class="bg-primary" align = "center">' . $arrayCuetas[0] . ' </td>';
                }

                $html .= '<td class="bg-primary" align = "center"> Autorizacion </td>
                <td class="bg-primary" align = "center"> Establecimiento </td>
                <td class="bg-primary" align = "center"> Punto Emision </td>
                <td class="bg-primary" align = "center"> Secuencial </td>
                ';
            }
            $html .= '</tr> ';
            $html .= '</thead>';
            $html .= '<tbody>';

            // INICIALIZAR TOTALES
            $sumaIva12     = 0;
            $sumaIva5     = 0;
            $sumaIva0      = 0;
            $sumaIva       = 0;
            $sumaIvaPorc5  = 0;
            $sumaTotal     = 0;
            $sumaBaseRenta = 0;
            $sumaBaseIva   = 0;
            $numero_compras_foreach = 0;
            $n = 1;
            foreach ($arrayCompras as $compras) {
                //var_dump($compras);exit;
                $sumaIva12     = $sumaIva12     + $compras[7];
                $sumaIva5     = $sumaIva5     + $compras[8];
                $sumaIva0      = $sumaIva0      + $compras[9];
                $sumaIva       = $sumaIva       + $compras[10];
                $sumaIvaPorc5       = $sumaIvaPorc5   + $compras[11];
                $sumaTotal        = $sumaTotal     + $compras[12];
                $sumaNoOjeto   = $sumaNoOjeto   + $compras[13];
                $sumaExento    = $sumaExento    + $compras[14];
                $sumaBaseRenta = $sumaBaseRenta + $compras[16];
                $sumaBaseIva   = $sumaBaseIva   + $compras[17];

                $sustento      = limpiar_string(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $compras[18]));
                //  $sustento      = utf8_decode/limpiar_string($compras[16]);
                $tansaccion    = $compras[19];
                $ruc_clpv    = $compras[20];
                $asto_cod_asto_ad    = $compras[3];
                $campos_adicionales_array = explode("/", $compras[21]);
                $autorizacion    = $campos_adicionales_array[0];
                $ret_aut_ret_ad_    = $campos_adicionales_array[1];
                $ret_ser_ret_ad    = $campos_adicionales_array[2];
                $ret_num_ret_ad    = $campos_adicionales_array[3];
                $idsucursal    = $campos_adicionales_array[4];
                $ret_fprv = $campos_adicionales_array[5];

                $sql_auto_rete = "SELECT fprv_auto_sri from saefprv 
                                    where fprv_num_mayo = '$asto_cod_asto_ad'
                                    and fprv_cod_ejer = $ejercicio
                                    and fprv_cod_empr = $empresa
                                    and fprv_cod_sucu = $idsucursal
                                    ";
                $ret_aut_ret_ad = consulta_string_func($sql_auto_rete, 'fprv_auto_sri', $oIfx, '');
                if (empty($ret_aut_ret_ad)) {

                    $sql_auto_rete = "SELECT minv_auto_sri from saeminv 
                                    where minv_tran_minv = '$asto_cod_asto_ad'
                                    and minv_cod_ejer = $ejercicio
                                    and minv_cod_empr = $empresa
                                    and minv_cod_sucu = $idsucursal
                                    ";
                    $ret_aut_ret_ad = consulta_string_func($sql_auto_rete, 'minv_auto_sri', $oIfx, '');
                }

                if (empty($ret_aut_ret_ad)) {
                    $ret_aut_ret_ad =  $ret_aut_ret_ad_;
                }


                $sumaConSIn  = $compras[7]   + $compras[8];
                $class = '';
                if ($tansaccion == '04') {
                    $class = "bg-success";
                }
                if ($tansaccion == '03') {
                    $class = "bg-warning";
                }
                $contador = count($compras);
                // $contador=$contador-2;
                if ($detallado == 'S') {
                    for ($i = 0; $i < $contador; $i++) {
                        if ($i == 0) {
                            $html .= '<tr class="' . $class . '">';
                            $html .= '<td  align = "center">' . $n . '</td>';
                        }
                        $color = 'black';
                        if ($i > 15) {
                            if ($compras[$i] == null) {
                                $compras[$i] = '0.00';
                                $color = 'black';
                            } else {
                                if ($i == 15 || $i == 16) {
                                    $color = 'black';
                                } else {
                                    $color = 'blue';
                                }
                            }

                            if ($i == 18 || $i == 19 || $i == 20 || $i == 21) {
                            } elseif ($i == 16) {
                                $html .= '<td  style = "color:' . $color . '; mso-number-format:\@;" align = "right">' . $sumaConSIn . '</td>';
                            } else {
                                $html .= '<td style = "color:' . $color . '; mso-number-format:\@;" align = "right">' . $compras[$i] . ' </td>';
                            }
                        } else {
                            if ($i == 4) {
                                $html .= '<td style = "color:black; mso-number-format:\@;">NA: ' . $autorizacion . '</td>';
                            }
                            if ($i == 5) {

                                $html .= '<td style = "color:' . $color . '; mso-number-format:\@;">' . $sustento . '</td>';
                                $html .= '<td style = "color:black; mso-number-format:\@;" >' . $ruc_clpv . '</td>';
                            }
                            if ($i > 6) {
                                $html .= '<td style = "color:' . $color . '; mso-number-format:\@;" align = "right">' . $compras[$i] . ' </td>';
                            } else {
                                if ($i == 3) {
                                    $html .= '<td style = "mso-number-format:\@;"> <a href="#" onclick="seleccionaItem(' . $empresa . ', ' . $idsucursal . ', ' . $ejercicio . ', ' . $periodo . ', \'' . $compras[$i] . '\');">' . $compras[$i] . '</a></td>';
                                } else {
                                    $html .= '<td style = "color:' . $color . '">' . $compras[$i] . ' </td>';
                                }
                            }
                        }

                        if ($i == $contador) {
                            $html .= '</tr>';
                        }
                    }

                    if (!empty($ret_fprv) || $contador == 22) {
                        foreach ($arrayTipoReteciones as $arrayCuetas) {
                            $html .= '<td align="left">0.00</td>';
                        }
                    }
                    $html .= '<td style = "color:' . $color . '; mso-number-format:\@;">' . $ret_aut_ret_ad . ' </td>';
                    $html .= '<td style = "color:' . $color . '; mso-number-format:\@;">' . substr($ret_ser_ret_ad, 0, 3) . ' </td>';
                    $html .= '<td style = "color:' . $color . '; mso-number-format:\@;">' . substr($ret_ser_ret_ad, 3, 6) . ' </td>';
                    $html .= '<td style = "color:' . $color . '; mso-number-format:\@;">' . $ret_num_ret_ad . ' </td>';
                } else {
                    for ($i = 0; $i < $contador; $i++) {
                        if ($i == 0) {
                            $html .= '<tr class="' . $class . '">';
                            $html .= '<td  align = "center">' . $n . '</td>';
                        }
                        $color = 'black';
                        if ($i < 15) {
                            if ($i == 5) {
                                $html .= '<td style = "color:' . $color . '; mso-number-format:\@;" >' . $sustento . '</td>';
                                $html .= '<td style = "color:black; mso-number-format:\@;">' . $ruc_clpv . '</td>';
                            }
                            if ($i == 4) {
                                $html .= '<td style = "color:black; mso-number-format:\@;">NA: ' . $autorizacion . '</td>';
                            }
                            if ($i > 6) {
                                $html .= '<td style = "color:' . $color . '; mso-number-format:\@;" align = "right">' . number_format($compras[$i], 2, '.', '') . ' </td>';
                            } else {
                                if ($i == 3) {
                                    $html .= '<td style="mso-number-format:\@;"> <a href="#" onclick="seleccionaItem(' . $empresa . ', ' . $idsucursal . ', ' . $ejercicio . ', ' . $periodo . ', \'' . $compras[$i] . '\');">' . $compras[$i] . '</a></td>';
                                } else {
                                    $html .= '<td style = "color:' . $color . '; mso-number-format:\@;">' . $compras[$i] . ' </td>';
                                }
                            }
                            if ($i == $contador) {
                                $html .= '</tr>';
                            }
                        }
                    }
                }
                $numero_compras_foreach++;
                $n++;
            }

            // NOTA DE CREDITO
            $sql = "SELECT 
                        t.tloc_cod_crtr, c.clv_con_clpv, c.clpv_cod_clpv, nc.ncnd_num_docu, nc.ncnd_num_auto,  
                        c.clpv_ruc_clpv, nc.ncnd_cod_tcmp, nc.ncnd_fec_emis, 
                        c.clpv_par_rela, c.clpv_cod_tprov, c.clpv_nom_clpv,
                        (substring(nc.ncnd_nsr_comp from 1 for 3)) AS estab,
                        (substring(nc.ncnd_nsr_comp from 4 for 6)) AS ptoemi,
                        nc.ncnd_nse_comp, t.tloc_cod_asto, t.tloc_mes_tloc, 
                        t.tloc_ani_tloc, t.tloc_cod_sucu, DATE_PART('month',nc.ncnd_fec_emis) as mes,
                        round((COALESCE(t.tloc_bim_ta0b,0)),2) as base_imponible,
                        round((COALESCE(t.tloc_bim_tar0,0)),2) as base_imponible1,
                        round((COALESCE(t.tloc_bas_imgr,0)),2) as baseimprgrav,
                        round((COALESCE(t.tloc_val_mice,0)),2) as montoice,
                        round((COALESCE(t.tloc_val_miva,0)),2) as montoiva,
                        c.clpv_cod_tpago,
                        nc.ncnd_cod_strs,
                        (substring(nc.ncnd_num_srcm from 1 for 3)) AS estab_modi,
                        (substring(nc.ncnd_num_srcm from 4 for 6)) AS ptoemi_modi,
                        nc.ncnd_num_sccm as secu_modi,
                        nc.ncnd_num_aucm as auto_modi,
                        nc.ncnd_cod_tcmm
                    from 
                        saencnd nc, saeclpv c, saetloc t 
                    where
                        c.clpv_ruc_clpv = nc.ncnd_num_docu and
                        nc.ncnd_nse_comp = t.tloc_nse_comp and
                        c.clpv_ruc_clpv = t.tloc_num_docu and
                        DATE_PART('year',ncnd_fec_emis) = '$anio' and 
                        DATE_PART('month',ncnd_fec_emis) $filmes  and
                        nc.ncnd_ruc_info = (select empr_ruc_empr from saeempr where
                                        empr_cod_empr = $empresa) and
                        c.clpv_cod_empr = $empresa and
                        c.clpv_clopv_clpv = 'PV' and
                        nc.ncnd_cod_tcmp in ('04','05') and
                        nc.ncnd_cod_tcmp = t.tloc_cod_tcmp
                        order by nc.ncnd_fec_emis";


            if ($oIfx->Query($sql)) {
                if ($oIfx->NumFilas()) {
                    do {
                        $ncre_fech_fact         = $oIfx->f('ncnd_fec_emis');
                        $ncre_nse_ncre             = $oIfx->f('estab') . '' . $oIfx->f('ptoemi');
                        $ncre_num_preimp         = $oIfx->f('ncnd_nse_comp');
                        $ncre_cod_asto             = $oIfx->f('tloc_cod_asto');

                        $ncnd_cod_tcmp          = $oIfx->f('ncnd_cod_tcmp');

                        if ($ncnd_cod_tcmp == '05') {
                            $tipo_documento         = 'NOTA DE DEBITO';
                        } else {
                            $tipo_documento         = 'NOTA DE CREDITO';
                        }

                        $ncre_doc_tran             = $arrayTipoTransacion[012][0];
                        $ncre_ruc_clie             = $oIfx->f('clpv_ruc_clpv');
                        $ncre_nom_cliente         = $oIfx->f('clpv_nom_clpv');
                        $ncre_cm_ncre             = 'APLICA A FACTURA: ' . $oIfx->f('estab_modi') . '-' . $oIfx->f('ptoemi_modi') . ' ' . $oIfx->f('secu_modi');
                        $ncre_cod_ncre         = $oIfx->f('ncre_cod_ncre');
                        $ncre_est_fact         = $oIfx->f('ncre_est_fact');
                        $ncre_cod_fact         = $oIfx->f('ncre_cod_fact');
                        $ncnd_num_auto         = $oIfx->f('ncnd_num_auto');


                        $ncre_con_miva         = number_format(($oIfx->f('baseimprgrav') * -1), 2, '.', '');
                        $ncre_sin_miva         = number_format(($oIfx->f('base_imponible1') * -1), 2, '.', '');
                        $ncre_iva             = number_format(($oIfx->f('montoiva') * -1), 2, '.', '');
                        $total                 = number_format((($oIfx->f('base_imponible1') + $oIfx->f('baseimprgrav') + $oIfx->f('montoiva')) * -1), 2, '.', '');

                        if ($ncnd_cod_tcmp == '05') {
                            $ncre_con_miva         = number_format(($oIfx->f('baseimprgrav')), 2, '.', '');
                            $ncre_sin_miva         = number_format(($oIfx->f('base_imponible1')), 2, '.', '');
                            $ncre_iva             = number_format(($oIfx->f('montoiva')), 2, '.', '');
                            $total      = number_format((($oIfx->f('base_imponible1') + $oIfx->f('baseimprgrav') + $oIfx->f('montoiva'))), 2, '.', '');
                        }

                        $sumaIva12     = $sumaIva12     + $ncre_con_miva;
                        $sumaIva0      = $sumaIva0      + $ncre_sin_miva;
                        $sumaIva       = $sumaIva       + $ncre_iva;
                        $sumaTotal        = $sumaTotal     + $total;
                        $sumaNoOjeto   = $sumaNoOjeto   + 0;
                        $sumaExento    = $sumaExento    + 0;


                        $html .= '<tr height="20">';
                        $html .= '<td  align = "center">' . $n . '</td>';
                        $html .= '<td style = "color:blue" align="right">' . $ncre_fech_fact . '</td>';
                        $html .= '<td style = "color:blue" align="left">' . $ncre_nse_ncre . '</td>';
                        $html .= '<td style = "color:blue;  mso-number-format:\@;" align="left">' . $ncre_num_preimp . '</td>';
                        $html .= '<td  style=" mso-number-format:\@;"> <a href="#" onclick="seleccionaItem(' . $empresa . ', ' . $idsucursal . ', ' . $ejercicio . ', ' . $periodo . ', \'' . $ncre_cod_asto . '\');">' . $ncre_cod_asto . '</a></td>';
                        $html .= '<td style = "color:blue;mso-number-format:\@;" align="left">NA: ' . $ncnd_num_auto . '</td>';
                        $html .= '<td style = "color:blue;  mso-number-format:\@;" align="left">' . $tipo_documento . '</td>';
                        $html .= '<td style = "color:blue;  mso-number-format:\@;" align="left" align="left">' . $ncre_doc_tran . '</td>';
                        $html .= '<td style = "color:blue;  mso-number-format:\@;" align="left" align="left">' . $ncre_ruc_clie . '</td>';
                        $html .= '<td style = "color:blue; mso-number-format:\@;" align="left" align="left">' . $ncre_nom_cliente . '</td>';
                        $html .= '<td style = "color:blue; mso-number-format:\@;" align="left" align="left">' . $ncre_cm_ncre . '</td>';
                        $html .= '<td align="right">' . $ncre_con_miva . '</td>';
                        /////esto
                        $html .= '<td align="right">0.00</td>';
                        $html .= '<td align="right">' . $ncre_sin_miva . '</td>';
                        $html .= '<td align="right">' . $ncre_iva . '</td>';
                        /////esto
                        $html .= '<td align="right">0.00</td>';
                        $html .= '<td align="right">' . $total . '</td>';
                        $html .= '<td align="right">0.00</td>';
                        $html .= '<td align="right">0.00</td>';

                        if ($detallado == 'S') {
                            $html .= '<td align="right"></td>';
                            $html .= '<td align="right">0.00</td>';
                            $html .= '<td align="right">0.00</td>';
                            foreach ($arrayTipoReteciones as $arrayCuetas) {
                                $html .= '<td align="right">0.00</td>';
                            }
                            $html .= '<td align="right"></td>';
                            $html .= '<td align="right"></td>';
                            $html .= '<td align="right"></td>';
                            $html .= '<td align="right"></td>';
                        }

                        $html .= '</tr>';
                        //echo $html;exit;
                        $i++;
                        $n++;
                    } while ($oIfx->SiguienteRegistro());
                } else {
                    //$oReturn->alert('No existen datos de esta sucursal para este ejercicio ni periodo');
                }
            }

            // TOTALES RETENCIONES	number_format($compras[$i],2,'.',',')
            $html .= '</tbody>';
            $html .= '<tr>';

            $html .= '<tfoot><tr>';
            $html .= '<td align = "right" bgcolor = "#CCCCCC" style = "color:red"></td>';
            $html .= '<td align = "right" bgcolor = "#CCCCCC" style = "color:red"></td>';
            $html .= '<td align = "right" bgcolor = "#CCCCCC" style = "color:red"></td>';
            $html .= '<td align = "right" bgcolor = "#CCCCCC" style = "color:red"></td>';
            $html .= '<td align = "right" bgcolor = "#CCCCCC" style = "color:red"></td>';
            $html .= '<td align = "right" bgcolor = "#CCCCCC" style = "color:red"></td>';
            $html .= '<td align = "right" bgcolor = "#CCCCCC" style = "color:red"></td>';
            $html .= '<td align = "right" bgcolor = "#CCCCCC" style = "color:red"></td>';
            $html .= '<td align = "right" bgcolor = "#CCCCCC" style = "color:red"></td>';
            $html .= '<td align = "right" bgcolor = "#CCCCCC" style = "color:red"></td>';
            $html .= '<td align = "right"  style = "color:red" bgcolor = "#CCCCCC"> TOTALES: </td>';
            $html .= '<td align = "right" bgcolor = "#CCCCCC" style = "color:red; mso-number-format:\@;">' . number_format($sumaIva12, 2, '.', ',') . ' </td>';
            $html .= '<td align = "right" bgcolor = "#CCCCCC" style = "color:red; mso-number-format:\@;">' . number_format($sumaIva5, 2, '.', ',') . ' </td>';
            $html .= '<td align = "right" bgcolor = "#CCCCCC" style = "color:red; mso-number-format:\@;">' . number_format($sumaIva0, 2, '.', ',') . ' </td>';
            $html .= '<td align = "right" bgcolor = "#CCCCCC" style = "color:red; mso-number-format:\@;">' . number_format($sumaIva, 2, '.', ',') . ' </td>';
            $html .= '<td align = "right" bgcolor = "#CCCCCC" style = "color:red; mso-number-format:\@;">' . number_format($sumaIvaPorc5, 2, '.', ',') . ' </td>';
            $html .= '<td align = "right" bgcolor = "#CCCCCC" style = "color:red; mso-number-format:\@;">' . number_format($sumaTotal, 2, '.', ',') . ' </td>';
            $html .= '<td align = "right" bgcolor = "#CCCCCC" style = "color:red; mso-number-format:\@;">' . number_format($sumaNoOjeto, 2, '.', ',') . ' </td>';
            $html .= '<td align = "right" bgcolor = "#CCCCCC" style = "color:red; mso-number-format:\@;">' . number_format($sumaExento, 2, '.', ',') . ' </td>';
            if ($detallado == 'S') {
                $html .= '<td align = "right" bgcolor = "#CCCCCC" style = "color:red; mso-number-format:\@;"> </td>';
                $html .= '<td align = "right" bgcolor = "#CCCCCC" style = "color:red; mso-number-format:\@;">' . number_format($sumaBaseRenta, 2, '.', ',') . ' </td>';
                $html .= '<td align = "right" bgcolor = "#CCCCCC" style = "color:red; mso-number-format:\@;">' . number_format($sumaBaseIva, 2, '.', ',') . ' </td>';
                for ($f = 22; $f < count($arrayTotales) + 22; $f++) {

                    $html .= '<td style = "color:red"; bgcolor = "#CCCCCC"; mso-number-format:\@;">' . number_format($arrayTotales[$f], 2, '.', ',') . '</td>';
                }
                $html .= '<td></td><td></td><td></td><td></td>';
            }
            $html .= '</tr></tfoot>';
            $html .= '</table>';




            // NOTA DE CREDITO DE INVENTARIO
            $sql = "select  t.tloc_cod_crtr, c.clv_con_clpv,   c.clpv_cod_clpv, nc.ncnd_num_docu,   c.clpv_ruc_clpv,
				c.clpv_cod_tprov, c.clpv_par_rela, nc.ncnd_cod_tcmp,   nc.ncnd_fec_emis,
				( substring(  nc.ncnd_nsr_comp   from 1 for 3 ) ) AS estab,
				( substring(  nc.ncnd_nsr_comp   from 4 for 6 ) ) AS ptoemi,
				nc.ncnd_nse_comp ,  t.tloc_nau_comp,
				round((coalesce(t.tloc_bim_ta0b,0)),2)  as base_imponible,
				round((coalesce(t.tloc_bim_tar0,0)),2) as base_imponible1,
				round((coalesce(t.tloc_bas_imgr,0)),2)  as  baseimprgrav,
				round((coalesce(t.tloc_val_mice,0)),2) as montoice,
				round((coalesce(t.tloc_val_miva,0)),2) as montoiva,
				c.clpv_cod_tpago,
				nc.ncnd_cod_strs,
				( substring(  nc.ncnd_num_srcm   from 1 for 3 ) ) AS estab_modi,
				( substring(  nc.ncnd_num_srcm   from 4 for 6 ) ) AS ptoemi_modi,
				nc.ncnd_num_sccm  as secu_modi,
				nc.ncnd_num_aucm   as auto_modi,
				nc.ncnd_cod_tcmm, t.tloc_nom_raso,
				t.tloc_cod_asto
				from saencnd  nc , saeclpv c , saetloc t where
				c.clpv_ruc_clpv = nc.ncnd_num_docu and
				nc.ncnd_nse_comp = t.tloc_nse_comp and
				c.clpv_ruc_clpv = t.tloc_num_docu and

				SUBSTR(cast (nc.ncnd_fec_emis as text), 0, 5) = '$anio' and
				SUBSTR(cast (nc.ncnd_fec_emis as text), 6, 2) $filnc and
				nc.ncnd_ruc_info = (    select empr_ruc_empr from saeempr where
										empr_cod_empr = $empresa ) and
				c.clpv_cod_empr = $empresa and
				c.clpv_clopv_clpv = 'PV' and
				nc.ncnd_cod_tcmp in ( '04' , '05' ) 
				order by nc.ncnd_fec_emis";
            //echo $sql; exit;
            //$oReturn->alert($sql);


            if ($oIfx->Query($sql)) {
                if ($oIfx->NumFilas()) {
                    do {
                        $ctrlreg++;
                        $cod_sustento         = $oIfx->f('tloc_cod_crtr');
                        $tpIdProv             = $oIfx->f('clv_con_clpv');
                        $IdProv             = $oIfx->f('clpv_ruc_clpv');
                        $tipo_comprobante     = $oIfx->f('ncnd_cod_tcmp');
                        $fecha_registro     = $oIfx->f('ncnd_fec_emis');
                        $estab                 = $oIfx->f('estab');
                        $ptoemi             = $oIfx->f('ptoemi');
                        $secuencial         = $oIfx->f('ncnd_nse_comp');
                        $fecha_emision         = $oIfx->f('ncnd_fec_emis');
                        $autorizacion         = $oIfx->f('tloc_nau_comp');
                        $basenograiva         = number_format(0.00, 2, '.', '');
                        $baseimponible         = number_format($oIfx->f('base_imponible'), 2, '.', '');
                        $baseimponible1     = number_format($oIfx->f('base_imponible1'), 2, '.', '');
                        $baseimpgrav         = number_format($oIfx->f('baseimprgrav'), 2, '.', '');
                        $montoice             = number_format($oIfx->f('montoice'), 2, '.', '');
                        $montoiva             = number_format($oIfx->f('montoiva'), 2, '.', '');
                        $valRetBien10         = number_format(0.00, 2, '.', '');
                        $valRetServ20         = number_format(0.00, 2, '.', '');
                        $valoretbienes         = number_format(0.00, 2, '.', '');
                        $valoretservicios     = number_format(0.00, 2, '.', '');
                        $valoretserv100     = number_format(0.00, 2, '.', '');
                        $cod_tpago             = $oIfx->f('clpv_cod_tpago');
                        $estab_modi         = $oIfx->f('estab_modi');
                        $ptoemi_modi         = $oIfx->f('ptoemi_modi');
                        $secu_modi             = $oIfx->f('secu_modi');
                        $auto_modi             = $oIfx->f('auto_modi');
                        $doc_modi             = $oIfx->f('ncnd_cod_tcmm');
                        $tloc_nom_raso         = $oIfx->f('tloc_nom_raso');
                        $clpv_cod_tprov     = $oIfx->f('clpv_cod_tprov');
                        $clpv_par_rela         = $oIfx->f('clpv_par_rela');
                        $tloc_cod_asto         = $oIfx->f('tloc_cod_asto');
                        $arreglo_secu_modi = explode("-", $secu_modi);
                        if ($arreglo_secu_modi[1] != '') {
                            $estab_modi = substr($arreglo_secu_modi[0], 0, 3);
                            $ptoemi_modi = substr($arreglo_secu_modi[0], 3, 6);
                            $secu_modi = $arreglo_secu_modi[1];
                        }

                        if ($clpv_par_rela == 'S') {
                            $clpv_par_rela = 'SI';
                        } else {
                            $clpv_par_rela = 'NO';
                        }

                        if ($sClass == 'off')
                            $sClass = 'on';
                        else
                            $sClass = 'off';

                        $tmp_nombre = '';
                        if ($tipo_comprobante == '04') {
                            // $oReturn->alert($tipo_comprobante);
                            $tmp_nombre = 'NOTA CREDITO';
                            // totales
                            $basenograiva_tot -= $basenograiva;
                            $baseimponible_tot -= $baseimponible1;
                            $baseimpgrav_tot -= $baseimpgrav;
                            $montoice_tot -= $montoice;
                            $montoiva_tot -= $montoiva;

                            // totales
                            $valRetBien10_tot -= $valRetBien10;
                            $valRetServ20_tot -= $valRetServ20;
                            $valoretbienes_tot -= $valoretbienes;
                            $valoretservicios_tot -= $valoretservicios;
                            $valoretserv100_tot -= $valoretserv100;
                        } elseif ($tipo_comprobante == '05') {
                            $tmp_nombre = 'NOTA DEBITO';
                            $basenograiva_tot += $basenograiva;
                            $baseimponible_tot +=  $baseimponible1;
                            $baseimpgrav_tot +=  $baseimpgrav;
                            $montoice_tot +=  $montoice;
                            $montoiva_tot +=  $montoiva;

                            // totales
                            $valRetBien10_tot +=  $valRetBien10;
                            $valRetServ20_tot +=  $valRetServ20;
                            $valoretbienes_tot +=  $valoretbienes;
                            $valoretservicios_tot +=  $valoretservicios;
                            $valoretserv100_tot += $valoretserv100;
                        }

                        $reporte_xml .= '<tr height="20" class="' . $sClass . '"
                                                onMouseOver="javascript:this.className=\'link\';"
                                                onMouseOut="javascript:this.className=\'' . $sClass . '\';">';
                        $reporte_xml .= '<td align="right">' . $i . '</td>';
                        $reporte_xml .= '<td align="left">' . $tmp_nombre . '</td>';
                        $reporte_xml .= '<td align="left"></td>';
                        $reporte_xml .= '<td align="right">' . $cod_sustento . '</td>';
                        $reporte_xml .= '<td align="right">' . $tpIdProv . '</td>';
                        $reporte_xml .= '<td align="left">' . $IdProv . '</td>';
                        $reporte_xml .= '<td align="left">' . $tloc_nom_raso . '</td>';
                        $reporte_xml .= '<td align="right">' . $tipo_comprobante . '</td>';
                        $reporte_xml .= '<td align="right">' . $clpv_cod_tprov . '</td>';
                        $reporte_xml .= '<td align="right">' . $clpv_par_rela . '</td>';
                        $reporte_xml .= '<td align="right">' . $fecha_registro . '</td>';
                        $reporte_xml .= '<td align="right">' . $estab . '</td>';
                        $reporte_xml .= '<td>' . $ptoemi . '</td>';
                        $reporte_xml .= '<td align="right">' . $secuencial . '</td>';
                        $reporte_xml .= '<td align="right">' . $fecha_emision . '</td>';
                        $reporte_xml .= '<td align="right">NA: ' . $autorizacion . '</td>';
                        $reporte_xml .= '<td align="right">' . $tloc_cod_asto . '</td>';
                        $reporte_xml .= '<td align="right">' . $basenograiva . '</td>';
                        $reporte_xml .= '<td align="right">' . $baseimponible1 . '</td>';
                        $reporte_xml .= '<td align="right">' . $baseimpgrav . '</td>';
                        $reporte_xml .= '<td align="right">' . $montoice . '</td>';
                        $reporte_xml .= '<td align="right">' . $montoiva . '</td>';
                        $reporte_xml .= '<td align="right">' . $valRetBien10 . '</td>';
                        $reporte_xml .= '<td align="right">' . $valRetServ20 . '</td>';
                        $reporte_xml .= '<td align="right">' . $valoretbienes . '</td>';
                        $reporte_xml .= '<td align="right">' . $valoretservicios . '</td>';
                        $reporte_xml .= '<td align="right">' . $valoretserv100 . '</td>';
                        $reporte_xml .= '<td align="right">' . $cod_tpago . '</td>';
                        $reporte_xml .= '<td align="right">NA</td>';
                        $reporte_xml .= '<td align="right">NA</td>';
                        $reporte_xml .= '<td align="right">NA</td>';
                        $reporte_xml .= '<td align="right"></td>';
                        $reporte_xml .= '<td align="right"></td>';
                        $reporte_xml .= '<td align="right"></td>';
                        $reporte_xml .= '<td align="right"></td>';
                        $reporte_xml .= '<td align="right"></td>';
                        $reporte_xml .= '<td align="right"></td>';
                        $reporte_xml .= '<td align="right"></td>';
                        $reporte_xml .= '<td align="right"></td>';
                        $reporte_xml .= '<td align="right"></td>';
                        $reporte_xml .= '<td align="right">' . $doc_modi . '</td>';
                        $reporte_xml .= '<td align="right">' . $estab_modi . '</td>';
                        $reporte_xml .= '<td align="right">' . $ptoemi_modi . '</td>';
                        $reporte_xml .= '<td align="right">' . $secu_modi . '</td>';
                        $reporte_xml .= '<td align="right">' . $auto_modi . '</td>';
                        $reporte_xml .= '</tr>';
                        $i++;
                    } while ($oIfx->SiguienteRegistro());
                    //$oReturn->alert('Buscando ...');
                } else {
                    //$oReturn->alert('No existen datos de esta sucursal para este ejercicio ni periodo');
                }
            }


            // $html=$reporte_xml;
            // $html=$reporte_xml;

            $_SESSION['ACT_REPORTE'] = $html;
        }

        if ($ctrlreg == 0) {
            $html = '<span>!!..SIN DATOS PARA MOSTRAR...!!</span>';
        }

        $oReturn->assign("reporte", "innerHTML", $html);
        $oReturn->script("jsRemoveWindowLoad();");

        if ($ctrlreg != 0) {
            $oReturn->script("init('tbcompras')");
        }



        //$oReturn->script("recarga();");
        $oIfx->QueryT('COMMIT WORK;');
    } catch (Exception $e) {
        $oCon->QueryT('ROLLBACK');
        $oReturn->script("jsRemoveWindowLoad();");
        $oReturn->alert($e->getMessage());
    }
    return $oReturn;
}

function verDiarioContable($aForm = '', $empr = 0, $sucu = 0, $ejer = 0, $mes = 0, $asto = '')
{

    session_start();
    global $DSN_Ifx, $DSN;

    $oIfx = new Dbo;
    $oIfx->DSN = $DSN_Ifx;
    $oIfx->Conectar();

    $oCon = new Dbo;
    $oCon->DSN = $DSN;
    $oCon->Conectar();

    $oReturn = new xajaxResponse();

    //variables del formulario
    $empresa = $aForm['empresa'];
    $anio = $aForm['anio'];
    $mes_1 = $aForm['mes_1'];
    $mes_2 = $aForm['mes_2'];
    $nivel = $aForm['nivel'];
    $campo = 0;

    $class = new GeneraDetalleAsientoContable();

    $arrayAsto = $class->informacionAsientoContable($oIfx, $empr, $sucu, $ejer, $mes, $asto);

    $arrayDiario = $class->diarioAsientoContable($oIfx, $empr, $sucu, $ejer, $mes, $asto);

    $arrayDirectorio = $class->directorioAsientoContable($oIfx, $empr, $sucu, $ejer, $mes, $asto);

    $arrayRetencion = $class->retencionAsientoContable($oIfx, $empr, $sucu, $ejer, $mes, $asto);

    $arrayAdjuntos = $class->adjuntosAsientoContable($oCon, $empr, $sucu, $ejer, $mes, $asto);

    try {

        //LECTURA SUCIA1
        // $oIfx->QueryT('set isolation to dirty read;');


        //sucursal
        $sql = "select sucu_nom_sucu from saesucu where sucu_cod_sucu = $sucu";
        $sucu_nom_sucu = consulta_string_func($sql, 'sucu_nom_sucu', $oIfx, '');


        $oReturn->assign("divTituloAsto", "innerHTML", $asto . ' - ' . $sucu_nom_sucu);

        if (count($arrayAsto) > 0) {

            $table .= '<table class="table table-striped table-condensed" align="center" width="98%">';
            $table .= '<tr>';
            $table .= '<td colspan="4" class="bg-primary">DIARIO CONTABLE</td>';
            $table .= '</tr>';

            foreach ($arrayAsto as $val) {
                $asto_cod_asto = $val[0];
                $asto_vat_asto = $val[1];
                $asto_ben_asto = $val[2];
                $asto_fec_asto = $val[3];
                $asto_det_asto = $val[4];
                $asto_cod_modu = $val[5];
                $asto_usu_asto = $val[6];
                $asto_user_web = $val[7];
                $asto_fec_serv = $val[8];
                $asto_cod_tidu = $val[9];

                //modulo
                $sql = "select modu_des_modu from saemodu where modu_cod_modu = $asto_cod_modu";
                $modu_des_modu = consulta_string_func($sql, 'modu_des_modu', $oIfx, '');

                //tipo documento
                $sql = "select tidu_des_tidu from saetidu where tidu_cod_tidu = '$asto_cod_tidu'";
                $tidu_des_tidu = consulta_string_func($sql, 'tidu_des_tidu', $oIfx, '');

                $table .= '<tr>';
                $table .= '<td>Diario:</td>';
                $table .= '<td>' . $asto_cod_asto . '</td>';
                $table .= '<td>Fecha:</td>';
                $table .= '<td>' . $asto_fec_asto . '</td>';
                $table .= '</tr>';

                $table .= '<tr>';
                $table .= '<td>Beneficiario:</td>';
                $table .= '<td colspan="3">' . $asto_ben_asto . '</td>';
                $table .= '</tr>';

                $table .= '<tr>';
                $table .= '<td>Modulo:</td>';
                $table .= '<td>' . $modu_des_modu . '</td>';
                $table .= '<td>Documento:</td>';
                $table .= '<td>' . $asto_cod_tidu . ' - ' . $tidu_des_tidu . '</td>';
                $table .= '</tr>';

                $table .= '<tr>';
                $table .= '<td>Detalle:</td>';
                $table .= '<td colspan="3">' . $asto_det_asto . '</td>';
                $table .= '</tr>';
                //sucursal, cod_prove, asto_cod, ejer_cod, prdo_cod
                $table .= '<tr>';
                $table .= '<td>Formato:</td>';
                $table .= '<td align="left">
							<div class="btn btn-primary btn-sm" onclick="vista_previa_diario(' . $sucu . ', 0, \'' . $asto . '\', ' . $ejer . ', ' . $mes . ');">
								<span class="glyphicon glyphicon-print"></span>
							</div>
						</td>';
                $table .= '<td>Valor:</td>';
                $table .= '<td class="bg-danger fecha_letra" align="left">' . number_format($asto_vat_asto, 2, '.', ',') . '</td>';
                $table .= '</tr>';
            } //fin foreach

            $table .= '</table>';

            $oReturn->assign("divInfo", "innerHTML", $table);
        }

        //directorio
        if (count($arrayDiario) > 0) {

            $tableDia .= '<table class="table table-striped table-condensed table-bordered table-hover" align="center" width="98%">';
            $tableDia .= '<tr>';
            $tableDia .= '<td colspan="5" class="bg-primary">DIARIO</td>
						<td align="center">
							<div class="btn btn-primary btn-sm" onclick="vista_previa_diario(' . $sucu . ', 0, \'' . $asto . '\', ' . $ejer . ', ' . $mes . ');">
								<span class="glyphicon glyphicon-print"></span>
							</div>
						</td>';
            $tableDia .= '</tr>';
            $tableDia .= '<tr>';
            $tableDia .= '<td>Cuenta Contable</td>';
            $tableDia .= '<td>Centro Costos</td>';
            $tableDia .= '<td>Centro Actividad</td>';
            $tableDia .= '<td>Documento</td>';
            $tableDia .= '<td>Debito</td>';
            $tableDia .= '<td>Credito</td>';
            $tableDia .= '</tr>';
            $totalDeb = 0;
            $totalCre = 0;
            foreach ($arrayDiario as $val) {
                $dasi_cod_cuen = $val[0];
                $dasi_cod_cact = $val[1];
                $ccos_cod_ccos = $val[2];
                $dasi_dml_dasi = $val[3];
                $dasi_cml_dasi = $val[4];
                $dasi_det_asi = $val[5];
                $dasi_num_depo = $val[6];

                //clpv
                $cuen_nom_cuen = '';
                if (!empty($dasi_cod_cuen)) {
                    $sql = "select cuen_nom_cuen from saecuen where cuen_cod_cuen = '$dasi_cod_cuen' and cuen_cod_empr = $empr";
                    $cuen_nom_cuen = consulta_string_func($sql, 'cuen_nom_cuen', $oIfx, '');
                }

                $ccosn_nom_ccosn = '';
                if (!empty($ccos_cod_ccos)) {
                    $sql = "select ccosn_nom_ccosn from saeccosn where ccosn_cod_ccosn = '$ccos_cod_ccos' and ccosn_cod_empr = $empr";
                    $ccosn_nom_ccosn = consulta_string_func($sql, 'ccosn_nom_ccosn', $oIfx, '');
                }

                $cact_nom_cact = '';
                if (!empty($dasi_cod_cact)) {
                    $sql = "select cact_nom_cact from saecact where cact_cod_cact = '$dasi_cod_cact' and cact_cod_empr = $empr";
                    $cact_nom_cact = consulta_string_func($sql, 'cact_nom_cact', $oIfx, '');
                }

                $tableDia .= '<tr>';
                $tableDia .= '<td>' . $dasi_cod_cuen . ' - ' . $cuen_nom_cuen . '</td>';
                $tableDia .= '<td>' . $ccos_cod_ccos . ' - ' . $ccosn_nom_ccosn . '</td>';
                $tableDia .= '<td>' . $dasi_cod_cact . ' - ' . $cact_nom_cact . '</td>';
                $tableDia .= '<td>' . $dasi_num_depo . '</td>';
                $tableDia .= '<td align="right">' . number_format($dasi_dml_dasi, 2, '.', ',') . '</td>';
                $tableDia .= '<td align="right">' . number_format($dasi_cml_dasi, 2, '.', ',') . '</td>';
                $tableDia .= '</tr>';

                $totalDeb += $dasi_dml_dasi;
                $totalCre += $dasi_cml_dasi;
            } //fin foreach
            $tableDia .= '<tr>';
            $tableDia .= '<td align="right" class="bg-danger fecha_letra" colspan="4">TOTAL:</td>';
            $tableDia .= '<td align="right" class="bg-danger fecha_letra">' . number_format($totalDeb, 2, '.', ',') . '</td>';
            $tableDia .= '<td align="right" class="bg-danger fecha_letra">' . number_format($totalCre, 2, '.', ',') . '</td>';
            $tableDia .= '</tr>';
            $tableDia .= '</table>';

            $oReturn->assign("divDiario", "innerHTML", $tableDia);
        }

        //directorio
        if (count($arrayDirectorio) > 0) {

            $tableDir .= '<table class="table table-striped table-condensed table-bordered table-hover" align="center" width="98%">';
            $tableDir .= '<tr>';
            $tableDir .= '<td colspan="6" class="bg-primary">DIRECTORIO</td>';
            $tableDir .= '</tr>';
            $tableDir .= '<tr>';
            $tableDir .= '<td>No.</td>';
            $tableDir .= '<td>Cliente/Proveedor</td>';
            $tableDir .= '<td>Transaccion</td>';
            $tableDir .= '<td>Factura</td>';
            $tableDir .= '<td>Credito</td>';
            $tableDir .= '<td>Debito</td>';
            $tableDir .= '</tr>';
            $totalDeb = 0;
            $totalCre = 0;
            foreach ($arrayDirectorio as $val) {
                $dir_cod_dir = $val[0];
                $dir_cod_cli = $val[1];
                $tran_cod_modu = $val[2];
                $dir_cod_tran = $val[3];
                $dir_num_fact = $val[4];
                $dir_detalle = $val[5];
                $dir_fec_venc = $val[6];
                $dir_deb_ml = $val[7];
                $dir_cre_ml = $val[8];

                //clpv
                $clpv_nom_clpv = '';
                if (!empty($dir_cod_cli)) {
                    $sql = "select clpv_nom_clpv from saeclpv where clpv_cod_clpv = $dir_cod_cli";
                    $clpv_nom_clpv = consulta_string_func($sql, 'clpv_nom_clpv', $oIfx, '');
                }

                $tableDir .= '<tr>';
                $tableDir .= '<td>' . $dir_cod_dir . '</td>';
                $tableDir .= '<td>' . $clpv_nom_clpv . '</td>';
                $tableDir .= '<td>' . $dir_cod_tran . '</td>';
                $tableDir .= '<td>' . $dir_num_fact . '</td>';
                $tableDir .= '<td align="right">' . number_format($dir_cre_ml, 2, '.', ',') . '</td>';
                $tableDir .= '<td align="right">' . number_format($dir_deb_ml, 2, '.', ',') . '</td>';
                $tableDir .= '</tr>';

                $totalCre += $dir_cre_ml;
                $totalDeb += $dir_deb_ml;
            } //fin foreach
            $tableDir .= '<tr>';
            $tableDir .= '<td align="right" class="bg-danger fecha_letra" colspan="4">TOTAL:</td>';
            $tableDir .= '<td align="right" class="bg-danger fecha_letra">' . number_format($totalCre, 2, '.', ',') . '</td>';
            $tableDir .= '<td align="right" class="bg-danger fecha_letra">' . number_format($totalDeb, 2, '.', ',') . '</td>';
            $tableDir .= '</tr>';
            $tableDir .= '</table>';

            $oReturn->assign("divDirectorio", "innerHTML", $tableDir);
        }

        //retencion
        if (count($arrayRetencion) > 0) {

            $tableRet .= '<table class="table table-striped table-condensed table-bordered table-hover" align="center" width="98%">';
            $tableRet .= '<tr>';
            $tableRet .= '<td colspan="8" class="bg-primary">RETENCION</td>';
            $tableRet .= '</tr>';
            $tableRet .= '<tr>';
            $tableRet .= '<td>Cliente/Proveedor</td>';
            $tableRet .= '<td>Factura</td>';
            $tableRet .= '<td>Retencion</td>';
            $tableRet .= '<td>Codigo</td>';
            $tableRet .= '<td>Porcentaje</td>';
            $tableRet .= '<td>Base Imp.</td>';
            $tableRet .= '<td>Valor</td>';
            $tableRet .= '<td>Print</td>';
            $tableRet .= '</tr>';
            foreach ($arrayRetencion as $val) {
                $ret_cta_ret = $val[0];
                $ret_porc_ret = $val[1];
                $ret_bas_imp = $val[2];
                $ret_valor = $val[3];
                $ret_num_ret = $val[4];
                $ret_detalle = $val[5];
                $ret_num_fact = $val[6];
                $ret_ser_ret = $val[7];
                $ret_cod_clpv = $val[8];
                $ret_fec_ret = $val[9];

                //clpv
                $clpv_nom_clpv = '';
                if (!empty($ret_cod_clpv)) {
                    $sql = "select clpv_nom_clpv from saeclpv where clpv_cod_clpv = $ret_cod_clpv";
                    $clpv_nom_clpv = consulta_string_func($sql, 'clpv_nom_clpv', $oIfx, '');
                }

                //fprv
                $printRet = '';
                if ($asto_cod_modu == 4 || $asto_cod_modu == 6) {

                    //fecha fprv o minv
                    if ($asto_cod_modu == 4) {
                        $sql = "select fprv_fec_emis 
								from saefprv
								where fprv_cod_clpv = $ret_cod_clpv and
								fprv_num_fact = '$ret_num_fact' and
								fprv_cod_asto = '$asto' and
								fprv_cod_ejer = $ejer and
								fprv_cod_empr = $empr and
								fprv_cod_sucu = $sucu";
                        $fechaEmis = consulta_string_func($sql, 'fprv_fec_emis', $oIfx, '');
                    } elseif ($asto_cod_modu == 6) {
                        $sql = "select minv_fmov 
								from saeminv
								where minv_cod_clpv = $ret_cod_clpv and
								minv_fac_prov = '$ret_num_fact' and
								minv_comp_cont = '$asto' and
								minv_cod_ejer = $ejer and
								minv_cod_empr = $empr and
								minv_cod_sucu = $sucu";
                        $fechaEmis = consulta_string_func($sql, 'minv_fmov', $oIfx, '');
                    }

                    $printRet = '<div class="btn btn-primary btn-sm" onclick="genera_documento(5, \'' . $campo . '\',\'' . $fprv_clav_sri . '\' ,
																				 \'' . $ret_cod_clpv . '\'  , \'' . $ret_num_fact . '\', \'' . $ejer . '\',
																				 \'' . $asto . '\',  \'' . $fechaEmis . '\', ' . $sucu . ');">
									<span class="glyphicon glyphicon-print"></span>
								</div>';
                }

                $tableRet .= '<tr>';
                $tableRet .= '<td>' . $clpv_nom_clpv . '</td>';
                $tableRet .= '<td>' . $ret_num_fact . '</td>';
                $tableRet .= '<td>' . $ret_ser_ret . ' - ' . $ret_num_ret . '</td>';
                $tableRet .= '<td>' . $ret_cta_ret . '</td>';
                $tableRet .= '<td align="right">' . $ret_porc_ret . '</td>';
                $tableRet .= '<td align="right">' . number_format($ret_bas_imp, 2, '.', ',') . '</td>';
                $tableRet .= '<td align="right">' . number_format($ret_valor, 2, '.', ',') . '</td>';
                $tableRet .= '<td align="center">' . $printRet . '</td>';
                $tableRet .= '</tr>';
            } //fin foreach

            $tableRet .= '</table>';

            $oReturn->assign("divRetencion", "innerHTML", $tableRet);
        }

        //adjuntos
        if (count($arrayAdjuntos) > 0) {

            $tableAdj .= '<table class="table table-striped table-condensed table-bordered table-hover" align="center" width="98%">';
            $tableAdj .= '<tr>';
            $tableAdj .= '<td colspan="2" class="bg-primary">ARCHIVOS ADJUNTOS</td>';
            $tableAdj .= '</tr>';
            $tableAdj .= '<tr>';
            $tableAdj .= '<td>Titulo</td>';
            $tableAdj .= '<td>Ruta</td>';
            $tableAdj .= '</tr>';
            foreach ($arrayAdjuntos as $val) {
                $titulo = $val[0];
                $ruta = $val[1];

                $tableAdj .= '<tr>';
                $tableAdj .= '<td>' . $titulo . '</td>';
                $tableAdj .= '<td><a href="#" onclick="dowloand(\'' . $ruta . '\')">' . $ruta . '</a></td>';
                $tableAdj .= '</tr>';
            } //fin foreach

            $tableAdj .= '</table>';

            $oReturn->assign("divAdjuntos", "innerHTML", $tableAdj);
        }
    } catch (Exception $e) {
        $oReturn->alert($e->getMessage());
    }

    return $oReturn;
}


/* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
/* PROCESO DE REQUEST DE LAS FUNCIONES MEDIANTE AJAX NO MODIFICAR */
$xajax->processRequest();
/* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
