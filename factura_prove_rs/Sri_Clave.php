<script>
    function crear_carpeta(){
            alert('hola');
            var fso = new ActiveXObject("Scripting.FileSystemObject");
            var a = fso.CreateFolder("C:\\SANTACRUZ");
    }
</script>
<?php

$clave_acceso = $_REQUEST['clave_acceso'];
$pos          = $_REQUEST['clave_acceso'];

//$clave_acceso = '1405201501099144986800120031010000002651234567811';
//$pos          = '1405201501099144986800120031010000002651234567811';
$id_empresa   = 1;

try{
        $clientOptions = array(
        "useMTOM" => FALSE,
        'trace' => 1,
        'stream_context' => stream_context_create(array('http' => array('protocol_version' => 1.0) ) )
        );   

//        $wsdlAutoComp[$clave_acceso] = new  SoapClient("https://celcer.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantes?wsdl",
        $wsdlAutoComp[$pos] = new  SoapClient("https://cel.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantes?wsdl", $clientOptions);

        //RECUPERA LA AUTORIZACION DEL COMPROBANTE
        $aClave = array("claveAccesoComprobante" => $clave_acceso);

        $autoComp[$pos] = new stdClass();
        $autoComp[$pos] = $wsdlAutoComp[$pos]->autorizacionComprobante($aClave);

        $RespuestaAutorizacionComprobante[$pos] = $autoComp[$pos]->RespuestaAutorizacionComprobante;
        $claveAccesoConsultada[$pos] = $RespuestaAutorizacionComprobante[$pos]->claveAccesoConsultada;
        $autorizaciones[$pos] = $RespuestaAutorizacionComprobante[$pos]->autorizaciones;
        $autorizacion[$pos] = $autorizaciones[$pos]->autorizacion;  

        if(count($autorizacion[$pos])>1){
            $estado[$pos] = $autorizacion[$pos][0]->estado;
            $numeroAutorizacion[$pos] = $autorizacion[$pos][0]->numeroAutorizacion;
            $fechaAutorizacion[$pos] = $autorizacion[$pos][0]->fechaAutorizacion;
            $ambiente[$pos] = $autorizacion[$pos][0]->ambiente;
            $comprobante[$pos] = $autorizacion[$pos][0]->comprobante;
            $mensajes[$pos] = $autorizacion[$pos][0]->mensajes;
            $mensaje[$pos] = $mensajes[$pos]->mensaje;
        }else{
            $estado[$pos] = $autorizacion[$pos]->estado;
            $numeroAutorizacion[$pos] = $autorizacion[$pos]->numeroAutorizacion;
            $fechaAutorizacion[$pos] = $autorizacion[$pos]->fechaAutorizacion;
            $ambiente[$pos] = $autorizacion[$pos]->ambiente;
            $comprobante[$pos] = $autorizacion[$pos]->comprobante;
            $mensajes[$pos] = $autorizacion[$pos]->mensajes;
            $mensaje[$pos] = $mensajes[$pos]->mensaje;
        }  

        $xml = '';        
        $xml .= '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<autorizacion>';
        $xml .="<estado>$estado[$pos]</estado>";
        $xml .="<numeroAutorizacion>$numeroAutorizacion[$pos]</numeroAutorizacion>";
        $xml .="<fechaAutorizacion>$fechaAutorizacion[$pos]</fechaAutorizacion>";
        $xml .="<ambiente>$ambiente[$pos]</ambiente>";
        $xml .="<comprobante><![CDATA[$comprobante[$pos]]]></comprobante>";
        $xml .='</autorizacion>';

                
        echo ('COMPROBANTE: '.$estado[$pos].'  '.$clave_acceso).'<br>';
        echo ('AUTORIZACION: '.$numeroAutorizacion[$pos]).'<br>';
        echo ('FECHA: '.$fechaAutorizacion[$pos]).'<br';
        echo ('AMBIENTE: '.$ambiente[$pos]).'<br><br><br><br>';
        echo ('COMPROBANTE: '.$xml);
        
        // GUARDAR EN XML
        // CREAR CARPETA ANEXO
        $serv = "C:/";
        $ruta = $serv."Doc Electronicos Web SRI";
        // CARPETA EMPRESA
        $ruta_empr = $ruta."/".$id_empresa;
        if(!file_exists($ruta)){
            mkdir ($ruta);
        }

        if(!file_exists($ruta_empr)){
            mkdir($ruta_empr);
        }
        
        $nombre = $clave_acceso.".xml";
        $archivo = fopen($nombre, "w+");
        fwrite($archivo, $xml);
        fclose($archivo);

        // ruta del xml
        $archivo_xml = fopen($ruta_empr.'/'.$nombre, "w+");
        fwrite($archivo_xml, $xml);
        fclose($archivo_xml);

        if($estado[$pos] == 'AUTORIZADO'){              

            $dia = substr($claveAccesoConsultada[$pos], 0, 2); 
            $mes = substr($claveAccesoConsultada[$pos], 2, 2); 
            $an = substr($claveAccesoConsultada[$pos], 4, 4);


        }else{  
            $informacionAdicional = (strtoupper($mensaje[$clave_acceso]->informacionAdicional));
            $informacionAdicional = preg_replace('([^A-Za-z0-9 ])', '',strtoupper($mensaje[$clave_acceso][0]->informacionAdicional));
            $informacionAdicional = htmlspecialchars_decode($informacionAdicional);
            echo 'Error...'.$informacionAdicional;
        }

    }catch (SoapFault $e) {
          echo $pos.' NO HUBO CONECCION AL SRI (AUTORIZAR)';
    }        

?>