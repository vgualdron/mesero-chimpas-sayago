<?php
session_start();
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

require_once("../conexion.php");
require_once("../encrypted.php");
require_once("../pedido/apiAlegra.php");
$conexion = new Conexion();
$apiAlegra = new ApiAlegra();

$frm = json_decode(file_get_contents('php://input'), true);

try {
  //Actualizar
  if ($_SERVER['REQUEST_METHOD'] == 'GET') {
      $date = date("Y-m-d H:i:s");
      $prefix = $_GET['prefix'];
      $type = $_GET['type'];

      $resolution = $apiAlegra->getResolution($prefix);

      if($resolution) {

        if ($type == 'POS') {
          $sql = $conexion->prepare("select 
          max(pedi.pedi_numerofactura) as nextNumber
          FROM pedido pedi
          WHERE pedi.pedi_prefijofactura = ? ;");
                                   
          $sql->bindValue(1, $prefix);
          $sql->execute();
          header("HTTP/1.1 200 OK");
          $result = $sql->fetch(PDO::FETCH_ASSOC);
          
          if ($result != false) {
            $resolution['nextInvoiceNumber'] = $result['nextNumber'];
          }
       }

        $input['data'] = $resolution;
        $input['mensaje'] = "consultado con éxito";
        header("HTTP/1.1 200 OK");
        echo json_encode($input);
        exit();
  	  } else {
        $input['data'] = $resolution;
        $input['mensaje'] = "Error consultando";
        header("HTTP/1.1 400 Bad Request");
        echo json_encode($input);
        exit();
  	  }
  }

} catch (Exception $e) {
    echo 'Excepción capturada: ', $e->getMessage(), "\n";
}

//En caso de que ninguna de las opciones anteriores se haya ejecutado
// header("HTTP/1.1 400 Bad Request");

?>
