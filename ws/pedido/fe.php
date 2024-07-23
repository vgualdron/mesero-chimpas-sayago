<?php
session_start();
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

require_once("../conexion.php");
require_once("../encrypted.php");
require_once("./apiAlegra.php");
require_once("../../../phpqrcode/qrlib.php");
$conexion = new Conexion();
$apiAlegra = new ApiAlegra();

$frm = json_decode(file_get_contents('php://input'), true);

try {
  //Actualizar
  if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
      $input = $_GET;
      
      $id = $frm['id'];
      $idestado = $frm['idestado'];
      $registradopor = openCypher('decrypt', $frm['token']);
      $date = date("Y-m-d H:i:s");
      
      $nombreCliente = $frm['nombrecliente'];
      $direccionCliente = $frm['direccioncliente'];
      $telefonoCliente = $frm['telefonocliente'];
      $tipoPago = $frm['tipopago'];
      $facturar = $frm['facturar'];

      $prefijofactura = $frm['prefijofactura'];
      $numerofactura = $frm['numerofactura'];
      $idmesa = $frm['idmesa'];
      $clienteFE = $frm['clienteFE'];
      
      $date = date("Y-m-d");
      $bandera = true;

      $dataPayment = array(
        "idPedido" => $id,
        "idAccount" => 1, // caja general
        "date" => $date
      );

      $dataResolution = $apiAlegra->getResolution('FER');
	  
      $itemsInvoice = $apiAlegra->makeItemsInvoice($id);
      $paymentsInvoice = $apiAlegra->makePaymentsInvoice($dataPayment);
      $clientInvoice = $apiAlegra->makeClientInvoice($clienteFE);
      $warehouseInvoice = $apiAlegra->makeWarehouseInvoice($id);

      $dataInvoice = array(
        "date" => $date,
        "items" => $itemsInvoice,
        "client" => $clientInvoice,
        "warehouse" => $warehouseInvoice,
        "payments" => $paymentsInvoice,
        "paymentForm" => "CASH",
        "paymentMethod" => $tipoPago,
        "numberTemplate" => $dataResolution
      );
	 

      $invoice = $apiAlegra->makeStampInvoice($dataInvoice);
      $result = $apiAlegra->stampInvoice($invoice);
	  
	    // print_r($result);
      
      $cliente = $result["client"];
      $stamp = $result["stamp"];
      $numberTemplate = $result["numberTemplate"];
      $idInvoice = $result["id"];

      $nombreCliente = $cliente["name"];
      $direccionCliente =  $cliente["address"]["address"];

      $prefijofactura = $numberTemplate["prefix"];
      $numerofactura = $numberTemplate["number"];
      
      $qr = $stamp["barCodeContent"];
      $cufe = $stamp["cufe"];

      QRcode::png($qr, "../ticket/qr/$id.png", 'L', 4, 2);

      $sql = "UPDATE pinchetas_restaurante.pedido 
        SET espe_id = ?, pedi_registradopor = ?, pedi_fechacambio = ?,
        pedi_nombrecliente = ?,
        pedi_direccioncliente = ?,
        pedi_telefonocliente = ?,
        pedi_tipopago = ?,
        pedi_bandera = ?,
        pedi_numerofactura = ?,
        pedi_prefijofactura	= ?,
        mesa_id = ?,
        pedi_cufe = ?,
        pedi_qr = ?,
        pedi_idAlegra = ?
        WHERE pedi_id = ?; ";
          
      $sql = $conexion->prepare($sql);
      $sql->bindValue(1, $idestado);
      $sql->bindValue(2, $registradopor);
      $sql->bindValue(3, $date);
      $sql->bindValue(4, $nombreCliente);
      $sql->bindValue(5, $direccionCliente);
      $sql->bindValue(6, $telefonoCliente);
      $sql->bindValue(7, $tipoPago);
      $sql->bindValue(8, $bandera);
      $sql->bindValue(9, $numerofactura);
      $sql->bindValue(10, $prefijofactura);
      $sql->bindValue(11, $idmesa);
      $sql->bindValue(12, $cufe);
      $sql->bindValue(13, $qr);
      $sql->bindValue(14, $idInvoice);
      $sql->bindValue(15, $id);

      $resultBd = $sql->execute();
      
      if($resultBd) {
        header("HTTP/1.1 200 OK");
        echo json_encode($result);
        exit();
  	  } else {
        $input['data'] = json_encode($result);
        $input['mensaje'] = "Error actualizando base de datos";
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