<?

include_once('../../Include/config.inc.php');
include_once(path(DIR_INCLUDE) . 'conexiones/db_conexion.php');
include_once(path(DIR_INCLUDE) . 'comun.lib.php');
require_once 'html2pdf_v4.03/html2pdf.class.php';

if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

$oCnx = new Dbo ( );
$oCnx->DSN = $DSN;
$oCnx->Conectar();

$oIfx = new Dbo;
$oIfx->DSN = $DSN_Ifx;
$oIfx->Conectar();

$oIfx2 = new Dbo;
$oIfx2->DSN = $DSN_Ifx;
$oIfx2->Conectar();

$oIfxA = new Dbo;
$oIfxA->DSN = $DSN_Ifx;
$oIfxA->Conectar();

$oIfxB = new Dbo;
$oIfxB->DSN = $DSN_Ifx;
$oIfxB->Conectar();

$idEmpresa = $_SESSION['U_EMPRESA'];
$idSucursal = $_SESSION['U_SUCURSAL'];
$clpv = $_GET['cliente'];
$num_fact = $_GET['factura'];
$fec_emis = fecha_informix($_GET['fecha_emision']);
$asto = $_GET['asientoContable'];
$ejer_cod = $_GET['ejer_cod'];
$prdo_cod = $_GET['prdo_cod'];
//$idSucursal = $_GET['sucursal'];

$divContainerIni .= '<div style="width: 210mm; height: 280mm;">';

        $divContainerA .= '<div style="width: 210mm; height: 140mm;">';
        $divContainerA .= '<div style="width: 210mm; height: 20mm;"></div>';

         $sqlRet = "select * from saefprv  where
                            fprv_cod_empr = $idEmpresa and 
                            fprv_cod_sucu = $idSucursal  and 
                            fprv_cod_clpv = $clpv and 
                            fprv_num_fact = '$num_fact' and 
                            fprv_cod_ejer = '$ejer_cod' and  
                            fprv_cod_asto = '$asto' and  
                            fprv_fec_emis = '$fec_emis' ";

        if ($oIfx->Query($sqlRet)) {
                if ($oIfx->NumFilas() > 0) {
                    $divContainerA .= '<div style="width: 210mm; height: 20mm;">';
                    do {
                        $fprv_num_fact = $oIfx->f('fprv_num_fact');
                        $fprv_fec_emis = fecha_mysql_func($oIfx->f('fprv_fec_emis'));
                        $fprv_cod_rtf1 = $oIfx->f('fprv_cod_rtf1');
                        $fprv_cod_rtf2 = $oIfx->f('fprv_cod_rtf2');
                        $fprv_cod_riva1 = $oIfx->f('fprv_cod_riva1');
                        $fprv_cod_riva2 = $oIfx->f('fprv_cod_riva2');
                        $fprv_por_ret1 = $oIfx->f('fprv_por_ret1');
                        $fprv_por_ret2 = $oIfx->f('fprv_por_ret2');
                        $fprv_val_ret1 = $oIfx->f('fprv_val_ret1');
                        $fprv_val_ret2 = $oIfx->f('fprv_val_ret2');
                        $fprv_por_iva1 = $oIfx->f('fprv_por_iva1');
                        $fprv_por_iva2 = $oIfx->f('fprv_por_iva2');
                        $fprv_val_iva1 = $oIfx->f('fprv_val_iva1');
                        $fprv_val_iva2 = $oIfx->f('fprv_val_iva2');
                        $fprv_num_seri = $oIfx->f('fprv_num_seri');
                        $fprv_num_rete = $oIfx->f('fprv_num_rete');
                        $fprv_val_bas1 = $oIfx->f('fprv_val_bas1');
                        $fprv_val_bas2 = $oIfx->f('fprv_val_bas2');
                        $fprv_val_bas3 = $oIfx->f('fprv_val_bas3');
                        $fprv_val_bas4 = $oIfx->f('fprv_val_bas4');
                        $fprv_ruc_prov = $oIfx->f('fprv_ruc_prov');
                        $fprv_ser_rete = $oIfx->f('fprv_ser_rete');
                        $fprv_auto_sri = $oIfx->f('fprv_auto_sri');
                        $fprv_fech_sri = $oIfx->f('fprv_fech_sri');
                        $fprv_email_clpv = $oIfx->f('fprv_email_clpv');
                        $fprv_clav_sri = $oIfx->f('fprv_clav_sri');
                        $fprv_nom_clpv = $oIfx->f('fprv_nom_clpv');
                        $fprv_dir_clpv = $oIfx->f('fprv_dir_clpv');
                        $fprv_tel_clpv = $oIfx->f('fprv_tel_clpv');
                        $fprv_cod_clpv = $oIfx->f('fprv_cod_clpv');
                        $fprv_cod_tran = $oIfx->f('fprv_cod_tran');
                        $fprv_ani_fprv = $oIfx->f('fprv_ani_fprv');

                         //ruc si no existe
                        if (empty($fprv_ruc_prov)) {
                            $sql_ruc = "select clpv_ruc_clpv from saeclpv where clpv_cod_clpv = $clpv and clpv_cod_empr = $idEmpresa ";
                            $fprv_ruc_prov = consulta_string_func($sql_ruc, 'clpv_ruc_clpv', $oIfx2, '');
                        }

                        //nom si no existe
                        if (empty($fprv_nom_clpv)) {
                            $sql_nom = "select clpv_nom_clpv from saeclpv where clpv_cod_clpv = $clpv and clpv_cod_empr = $idEmpresa ";
                            $fprv_nom_clpv = consulta_string_func($sql_nom, 'clpv_nom_clpv', $oIfx2, '');
                        }

                        // direccion
                        if (empty($fprv_dir_clpv)) {
                            $sql = "select min( dire_dir_dire ) as dire  from saedire where
                                    dire_cod_empr = $idEmpresa and
                                    dire_cod_clpv = $clpv ";
                            $fprv_dir_clpv = consulta_string_func($sql, 'dire', $oIfx2, '');
                        }

                        // telefono
                        if (empty($fprv_tel_clpv)) {
                            $sql = "select min( tlcp_tlf_tlcp ) as telf  from saetlcp where
                                    tlcp_cod_empr = $idEmpresa and
                                    tlcp_cod_clpv = $clpv ";
                            $fprv_tel_clpv = consulta_string_func($sql, 'telf', $oIfx2, '');
                        }

                        // email
                        if (empty($fprv_email_clpv)) {
                            $sql = "select min( emai_ema_emai ) as ema  from saeemai where
                                    emai_cod_empr = $idEmpresa and
                                    emai_cod_clpv = $clpv";
                            $fprv_email_clpv = consulta_string_func($sql, 'ema', $oIfx2, '');
                        }

                        //TIPO DE COMPROBANTE
                        $sql_tran = "select tr.tran_cod_tran,
                                            t.tcmp_des_tcmp from
                                             saetcmp t, saetran tr
                                            where 
                                            tr.trans_tip_comp = t.tcmp_cod_tcmp and
                                            tr.tran_cod_tran = '$fprv_cod_tran' AND
                                            tr.tran_cod_empr = $idEmpresa and
                                            tr.tran_cod_sucu = $idSucursal and
                                            tr.tran_cod_modu = 4";
                        $tip_comp = consulta_string_func($sql_tran, 'tcmp_des_tcmp', $oIfx2, 0);
                        //$tip_comp = substr($tip_comp, 0, 50);

                        
                            $divContainerA .= '<table style="width: 185mm; height: 4mm; margin-top: 4mm;" align="center">      
                                                    <tr>
                                                      <td style="width: 135mm;"></td>
                                                      <td style="width: 93mm font-size: 10px;;">'.$asto.'</td>
                                                      <td style="width: 15mm;"></td>
                                                      <td style="width: 40mm; font-size: 11px;">'.$fprv_num_rete.'</td>
                                                    </tr>
                                                     
                                                </table>';
                            $divContainerA .= '<table style="width: 185mm; height: 22mm;" align="center">      
                                                    <tr>
                                                      <td style="width: 12mm;"></td>
                                                      <td style="width: 93mm; font-size: 11px;">'.substr($fprv_nom_clpv, 0, 48).'</td>
                                                      <td style="width: 35mm;"></td>
                                                      <td style="width: 40mm; font-size: 11px;">'.$fprv_fec_emis.'</td>
                                                    </tr>
                                                    <tr><td colspan="4"></td></tr>
                                                    <tr><td colspan="4"></td></tr>
                                                    <tr>
                                                      <td style="width: 12mm;"></td>
                                                      <td style="width: 93mm font-size: 11px;;">'.$fprv_ruc_prov.'</td>
                                                      <td style="width: 35mm;"></td>
                                                      <td style="width: 40mm; font-size: 11px;">'.$tip_comp.'</td>
                                                    </tr>   
                                                    <tr><td colspan="4"></td></tr>
                                                    <tr><td colspan="4"></td></tr> 
                                                    <tr>
                                                      <td style="width: 12mm;"></td>
                                                      <td style="width: 93mm; font-size: 11px;">'.substr($fprv_dir_clpv, 0, 51).'</td>
                                                      <td style="width: 35mm;"></td>
                                                      <td style="width: 40mm; font-size: 11px;">'.$fprv_num_seri.'-'.$fprv_num_fact.'</td>
                                                    </tr>  
                                                </table>';
                    } while ($oIfx->SiguienteRegistro());

                    $divContainerA .= '</div>';
        }
    }
    $oIfx->Free();


            $divContainerA .= '<div style="width: 210mm; height: 55mm; margin-top: 5mm;">';
            $divContainerA .= '<table style="width: 185mm; height: 55mm; font-size: 11px;" align="center">           
                                <tr>
                                  <td style="width: 25mm; height: 10mm;"></td>
                                  <td style="width: 30mm; height: 10mm;"></td>
                                  <td style="width: 30mm; height: 10mm;"></td>
                                  <td style="width: 30mm; height: 10mm;"></td>
                                  <td style="width: 30mm; height: 10mm;"></td>
                                  <td style="width: 40mm; height: 10mm;"></td>
                                </tr> ';
                        $iCont = 0;
                        $total = 0;
                        if ($fprv_val_ret1 > 0 || $fprv_cod_rtf1 != '') {
                            $deta .= ' <tr>';
                            $deta .= ' <td align=center">' . $fprv_ani_fprv . '</td>';
                            $deta .= ' <td align=center">' . number_format($fprv_val_bas1, 2, '.', '') . '</td>';
                            $deta .= ' <td align=center">RENTA</td>';
                            $deta .= ' <td align=center">' . $fprv_cod_rtf1 . '</td>';
                            $deta .= ' <td align=center">' . $fprv_por_ret1 . ' %' . '</td>';
                            $deta .= ' <td align=center">' . number_format($fprv_val_ret1, 2, '.', '') . '</td>';
                            $deta .= ' </tr>';
                            $total += $fprv_val_ret1;
                            $iCont++;
                        }

                        if ($fprv_val_ret2 > 0 || $fprv_cod_rtf2 != '') {
                            $deta .= ' <tr>';
                            $deta .= ' <td align=center">' .$fprv_ani_fprv . '</td>';
                            $deta .= ' <td align=center">' . number_format($fprv_val_bas2, 2, '.', '') . '</td>';
                            $deta .= ' <td align=center">RENTA</td>';
                            $deta .= ' <td align=center">' . $fprv_cod_rtf2 . '</td>';
                            $deta .= ' <td align=center">' . $fprv_por_ret2 . ' %' . '</td>';
                            $deta .= ' <td align=center">' . number_format($fprv_val_ret2, 2, '.', '') . '</td>';
                            $deta .= ' </tr>';
                            $total += $fprv_val_ret2;
                            $iCont++;
                        }

                        if ($fprv_val_iva1 > 0 || $fprv_cod_riva1 != '') {
                            $deta .= ' <tr>';
                            $deta .= ' <td align=center">' . $fprv_ani_fprv. '</td>';
                            $deta .= ' <td align=center">' . number_format($fprv_val_bas3, 2, '.', '') . '</td>';
                            $deta .= ' <td align=center">IVA</td>';
                            $deta .= ' <td align=center">' . $fprv_cod_riva1 . '</td>';
                            $deta .= ' <td align=center">' . $fprv_por_iva1 . ' %' . '</td>';
                            $deta .= ' <td align=center">' . number_format($fprv_val_iva1, 2, '.', '') . '</td>';
                            $deta .= ' </tr>';
                            $total += $fprv_val_iva1;
                            $iCont++;
                        }

                        if ($fprv_val_iva2 > 0 || $fprv_cod_riva2 != '') {
                            $deta .= ' <tr>';
                            $deta .= ' <td align=center">' .$fprv_ani_fprv . '</td>';
                            $deta .= ' <td align=center">' . number_format($fprv_val_bas4, 2, '.', '') . '</td>';
                            $deta .= ' <td align=center">IVA</td>';
                            $deta .= ' <td align=center">' . $fprv_cod_riva2 . '</td>';
                            $deta .= ' <td align=center">' . $fprv_por_iva2 . ' %' . '</td>';
                            $deta .= ' <td align=center">' . number_format($fprv_val_iva2, 2, '.', '') . '</td>';
                            $deta .= ' </tr>';
                            $total += $fprv_val_iva2;
                            $iCont++;
                        }

                        for($d = $iCont; $d < 4; $d++){
                             $deta .= ' <tr><td style="height:20px" colspan="8">&nbsp;</td></tr>';
                        }

                        $deta .= ' <tr><td style="height:20px" colspan="8"></td></tr>';
                        $deta .= ' <tr><td style="height:20px" colspan="8"></td></tr>';
                        $deta .= ' <tr>';
                        $deta .= ' <td colspan="5"></td>';
                        $deta .= ' <b><td align="center">' . number_format($total, 2, '.', '') . '</td></b>';
                        $deta .= ' </tr>';
                        $deta .= ' </table>';

                        $divContainerA .= $deta;

            $divContainerA .= '</div>';

                
    $divContainerA .= '</div>';


    /* ---> DIV CONTENEDOR B <--- */

    $divContainerB .= '<div style="width: 210mm; height: 140mm;">';
        $divContainerB .= '<div style="width: 210mm; height: 26mm; margin-top: 10mm;"></div>';

         $sqlRet = "select * from saefprv  where
                            fprv_cod_empr = $idEmpresa and 
                            fprv_cod_sucu = $idSucursal  and 
                            fprv_cod_clpv = $clpv and 
                            fprv_num_fact = '$num_fact' and 
                            fprv_cod_ejer = '$ejer_cod' and  
                            fprv_cod_asto = '$asto' and  
                            fprv_fec_emis = '$fec_emis' ";

        if ($oIfx->Query($sqlRet)) {
                if ($oIfx->NumFilas() > 0) {
                    $divContainerB .= '<div style="width: 210mm; height: 23mm;">';
                    do {
                        $fprv_num_fact = $oIfx->f('fprv_num_fact');
                        $fprv_fec_emis = fecha_mysql_func($oIfx->f('fprv_fec_emis'));
                        $fprv_cod_rtf1 = $oIfx->f('fprv_cod_rtf1');
                        $fprv_cod_rtf2 = $oIfx->f('fprv_cod_rtf2');
                        $fprv_cod_riva1 = $oIfx->f('fprv_cod_riva1');
                        $fprv_cod_riva2 = $oIfx->f('fprv_cod_riva2');
                        $fprv_por_ret1 = $oIfx->f('fprv_por_ret1');
                        $fprv_por_ret2 = $oIfx->f('fprv_por_ret2');
                        $fprv_val_ret1 = $oIfx->f('fprv_val_ret1');
                        $fprv_val_ret2 = $oIfx->f('fprv_val_ret2');
                        $fprv_por_iva1 = $oIfx->f('fprv_por_iva1');
                        $fprv_por_iva2 = $oIfx->f('fprv_por_iva2');
                        $fprv_val_iva1 = $oIfx->f('fprv_val_iva1');
                        $fprv_val_iva2 = $oIfx->f('fprv_val_iva2');
                        $fprv_num_seri = $oIfx->f('fprv_num_seri');
                        $fprv_num_rete = $oIfx->f('fprv_num_rete');
                        $fprv_val_bas1 = $oIfx->f('fprv_val_bas1');
                        $fprv_val_bas2 = $oIfx->f('fprv_val_bas2');
                        $fprv_val_bas3 = $oIfx->f('fprv_val_bas3');
                        $fprv_val_bas4 = $oIfx->f('fprv_val_bas4');
                        $fprv_ruc_prov = $oIfx->f('fprv_ruc_prov');
                        $fprv_ser_rete = $oIfx->f('fprv_ser_rete');
                        $fprv_auto_sri = $oIfx->f('fprv_auto_sri');
                        $fprv_fech_sri = $oIfx->f('fprv_fech_sri');
                        $fprv_email_clpv = $oIfx->f('fprv_email_clpv');
                        $fprv_clav_sri = $oIfx->f('fprv_clav_sri');
                        $fprv_nom_clpv = $oIfx->f('fprv_nom_clpv');
                        $fprv_dir_clpv = $oIfx->f('fprv_dir_clpv');
                        $fprv_tel_clpv = $oIfx->f('fprv_tel_clpv');
                        $fprv_cod_clpv = $oIfx->f('fprv_cod_clpv');
                        $fprv_cod_tran = $oIfx->f('fprv_cod_tran');
                        $fprv_ani_fprv = $oIfx->f('fprv_ani_fprv');

                         //ruc si no existe
                        if (empty($fprv_ruc_prov)) {
                            $sql_ruc = "select clpv_ruc_clpv from saeclpv where clpv_cod_clpv = $clpv and clpv_cod_empr = $idEmpresa ";
                            $fprv_ruc_prov = consulta_string_func($sql_ruc, 'clpv_ruc_clpv', $oIfx2, '');
                        }

                        //nom si no existe
                        if (empty($fprv_nom_clpv)) {
                            $sql_nom = "select clpv_nom_clpv from saeclpv where clpv_cod_clpv = $clpv and clpv_cod_empr = $idEmpresa ";
                            $fprv_nom_clpv = consulta_string_func($sql_nom, 'clpv_nom_clpv', $oIfx2, '');
                        }

                        // direccion
                        if (empty($fprv_dir_clpv)) {
                            $sql = "select min( dire_dir_dire ) as dire  from saedire where
                                    dire_cod_empr = $idEmpresa and
                                    dire_cod_clpv = $clpv ";
                            $fprv_dir_clpv = consulta_string_func($sql, 'dire', $oIfx2, '');
                        }

                        // telefono
                        if (empty($fprv_tel_clpv)) {
                            $sql = "select min( tlcp_tlf_tlcp ) as telf  from saetlcp where
                                    tlcp_cod_empr = $idEmpresa and
                                    tlcp_cod_clpv = $clpv ";
                            $fprv_tel_clpv = consulta_string_func($sql, 'telf', $oIfx2, '');
                        }

                        // email
                        if (empty($fprv_email_clpv)) {
                            $sql = "select min( emai_ema_emai ) as ema  from saeemai where
                                    emai_cod_empr = $idEmpresa and
                                    emai_cod_clpv = $clpv";
                            $fprv_email_clpv = consulta_string_func($sql, 'ema', $oIfx2, '');
                        }

                        //TIPO DE COMPROBANTE
                        $sql_tran = "select tr.tran_cod_tran,
                                            t.tcmp_des_tcmp from
                                             saetcmp t, saetran tr
                                            where 
                                            tr.trans_tip_comp = t.tcmp_cod_tcmp and
                                            tr.tran_cod_tran = '$fprv_cod_tran' AND
                                            tr.tran_cod_empr = $idEmpresa and
                                            tr.tran_cod_sucu = $idSucursal and
                                            tr.tran_cod_modu = 4";
                        $tip_comp = consulta_string_func($sql_tran, 'tcmp_des_tcmp', $oIfx2, 0);
                        //$tip_comp = substr($tip_comp, 0, 50);

                        
                            $divContainerB .= '<table style="width: 185mm; height: 4mm;" align="center">      
                                                    <tr>
                                                      <td style="width: 135mm;"></td>
                                                      <td style="width: 93mm font-size: 10px;;">'.$asto.'</td>
                                                      <td style="width: 15mm;"></td>
                                                      <td style="width: 40mm; font-size: 11px;">'.$fprv_num_rete.'</td>
                                                    </tr>
                                                     
                                                </table>';
                            $divContainerB .= '<table style="width: 185mm; height: 22mm;" align="center">      
                                                    <tr>
                                                      <td style="width: 12mm;"></td>
                                                      <td style="width: 93mm; font-size: 11px;">'.substr($fprv_nom_clpv, 0, 51).'</td>
                                                      <td style="width: 35mm;"></td>
                                                      <td style="width: 40mm; font-size: 11px;">'.$fprv_fec_emis.'</td>
                                                    </tr>
                                                    <tr><td colspan="4"></td></tr>
                                                    <tr><td colspan="4"></td></tr>
                                                    <tr>
                                                      <td style="width: 12mm;"></td>
                                                      <td style="width: 93mm font-size: 11px;;">'.$fprv_ruc_prov.'</td>
                                                      <td style="width: 35mm;"></td>
                                                      <td style="width: 40mm; font-size: 11px;">'.$tip_comp.'</td>
                                                    </tr>   
                                                    <tr><td colspan="4"></td></tr>
                                                    <tr><td colspan="4"></td></tr> 
                                                    <tr>
                                                      <td style="width: 12mm;"></td>
                                                      <td style="width: 93mm; font-size: 11px;">'.substr($fprv_dir_clpv, 0, 51).'</td>
                                                      <td style="width: 35mm;"></td>
                                                      <td style="width: 40mm; font-size: 11px;">'.$fprv_num_seri.'-'.$fprv_num_fact.'</td>
                                                    </tr>  
                                                </table>';
                    } while ($oIfx->SiguienteRegistro());

                    $divContainerB .= '</div>';
        }
    }
    $oIfx->Free();


            $divContainerB .= '<div style="width: 210mm; height: 55mm; margin-top: 5mm;">';
            $divContainerB .= '<table style="width: 185mm; height: 55mm; font-size: 11px;" align="center">           
                                <tr>
                                  <td style="width: 25mm; height: 10mm;"></td>
                                  <td style="width: 30mm; height: 10mm;"></td>
                                  <td style="width: 30mm; height: 10mm;"></td>
                                  <td style="width: 30mm; height: 10mm;"></td>
                                  <td style="width: 30mm; height: 10mm;"></td>
                                  <td style="width: 40mm; height: 10mm;"></td>
                                </tr> ';
                        $iCont = 0;
                        $total = 0;
                        $deta = '';
                        if ($fprv_val_ret1 > 0 || $fprv_cod_rtf1 != '') {
                            $deta .= ' <tr>';
                            $deta .= ' <td align=center">' . $fprv_ani_fprv . '</td>';
                            $deta .= ' <td align=center">' . number_format($fprv_val_bas1, 2, '.', '') . '</td>';
                            $deta .= ' <td align=center">RENTA</td>';
                            $deta .= ' <td align=center">' . $fprv_cod_rtf1 . '</td>';
                            $deta .= ' <td align=center">' . $fprv_por_ret1 . ' %' . '</td>';
                            $deta .= ' <td align=center">' . number_format($fprv_val_ret1, 2, '.', '') . '</td>';
                            $deta .= ' </tr>';
                            $total += $fprv_val_ret1;
                            $iCont++;
                        }

                        if ($fprv_val_ret2 > 0 || $fprv_cod_rtf2 != '') {
                            $deta .= ' <tr>';
                            $deta .= ' <td align=center">' .$fprv_ani_fprv . '</td>';
                            $deta .= ' <td align=center">' . number_format($fprv_val_bas2, 2, '.', '') . '</td>';
                            $deta .= ' <td align=center">RENTA</td>';
                            $deta .= ' <td align=center">' . $fprv_cod_rtf2 . '</td>';
                            $deta .= ' <td align=center">' . $fprv_por_ret2 . ' %' . '</td>';
                            $deta .= ' <td align=center">' . number_format($fprv_val_ret2, 2, '.', '') . '</td>';
                            $deta .= ' </tr>';
                            $total += $fprv_val_ret2;
                            $iCont++;
                        }

                        if ($fprv_val_iva1 > 0 || $fprv_cod_riva1 != '') {
                            $deta .= ' <tr>';
                            $deta .= ' <td align=center">' . $fprv_ani_fprv. '</td>';
                            $deta .= ' <td align=center">' . number_format($fprv_val_bas3, 2, '.', '') . '</td>';
                            $deta .= ' <td align=center">IVA</td>';
                            $deta .= ' <td align=center">' . $fprv_cod_riva1 . '</td>';
                            $deta .= ' <td align=center">' . $fprv_por_iva1 . ' %' . '</td>';
                            $deta .= ' <td align=center">' . number_format($fprv_val_iva1, 2, '.', '') . '</td>';
                            $deta .= ' </tr>';
                            $total += $fprv_val_iva1;
                            $iCont++;
                        }

                        if ($fprv_val_iva2 > 0 || $fprv_cod_riva2 != '') {
                            $deta .= ' <tr>';
                            $deta .= ' <td align=center">' .$fprv_ani_fprv . '</td>';
                            $deta .= ' <td align=center">' . number_format($fprv_val_bas4, 2, '.', '') . '</td>';
                            $deta .= ' <td align=center">IVA</td>';
                            $deta .= ' <td align=center">' . $fprv_cod_riva2 . '</td>';
                            $deta .= ' <td align=center">' . $fprv_por_iva2 . ' %' . '</td>';
                            $deta .= ' <td align=center">' . number_format($fprv_val_iva2, 2, '.', '') . '</td>';
                            $deta .= ' </tr>';
                            $total += $fprv_val_iva2;
                            $iCont++;
                        }

                        for($d = $iCont; $d < 4; $d++){
                             $deta .= ' <tr><td style="height:20px" colspan="8">&nbsp;</td></tr>';
                        }

                        $deta .= ' <tr><td style="height:20px" colspan="8"></td></tr>';
                        $deta .= ' <tr><td style="height:20px" colspan="8"></td></tr>';
                        $deta .= ' <tr>';
                        $deta .= ' <td colspan="5"></td>';
                        $deta .= ' <b><td align="center">' . number_format($total, 2, '.', '') . '</td></b>';
                        $deta .= ' </tr>';
                        $deta .= ' </table>';

                        $divContainerB .= $deta;

            $divContainerB .= '</div>';

                
    $divContainerB .= '</div>';
  

   
$divContainerFin .= '</div>';


    //$divEspacio = '<div style="height: 10mm; width: 210mm;"></div>';

    $tablePDF = $divContainerIni . $divContainerA . $divContainerB . $divContainerFin ;


    $html2pdf = new HTML2PDF('P', 'A4', 'es');
    $html2pdf->WriteHTML($tablePDF);
    $html2pdf->Output('recibo_template.pdf', '');


function fecha_mysql($fecha) {
    $fecha_array = explode('/', $fecha);
    $m = $fecha_array[0];
    $y = $fecha_array[2];
    $d = $fecha_array[1];

    return ( $d . '/' . $m . '/' . $y );
}

function fecha_informix($fecha) {
    $m = substr($fecha, 5, 2);
    $y = substr($fecha, 0, 4);
    $d = substr($fecha, 8, 2);

    return ( $m . '/' . $d . '/' . $y );
}

?>