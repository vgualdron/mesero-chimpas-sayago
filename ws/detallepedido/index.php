<?php
session_start();
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

require_once("../conexion.php");
require_once("../encrypted.php");
$conexion = new Conexion();

require_once("../ticket/detallepedido.php");

$frm = json_decode(file_get_contents('php://input'), true);

$unwanted_array = array('Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A',                                     'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
    'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
    'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
    'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
    'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );

try {
  
  //  listar todos los posts o solo uno
  if ($_SERVER['REQUEST_METHOD'] == 'GET') {
      $registradopor = openCypher('decrypt', $_GET['token']);
      if (isset($_GET['id'])) {
        $sql = $conexion->prepare(" select 
                                    depe.depe_id as id,
                                    depe.depe_descripcion as descripcion,
                                    depe.pedi_id as idpedido,
                                    tipr.tipr_id as idtipoproducto,
                                    tipr.tipr_descripcion as descripciontipoproducto,
                                    depe.prod_id as idproducto,
                                    prod.prod_descripcion as descripcionproducto,
                                    depe.prod_costo as costoproducto,
                                    depe.prod_cantidad as cantidadproducto,
                                    depe.prod_precio as precioproducto
                                    FROM pinchetas_restaurante.detallepedido depe
                                    inner join pinchetas_restaurante.pedido pedi on (pedi.pedi_id = depe.pedi_id)
                                    inner join pinchetas_restaurante.producto prod on (prod.prod_id = depe.prod_id)
                                    inner join pinchetas_restaurante.tipoproducto tipr on (tipr.tipr_id = prod.tipr_id)
                                    WHERE depe.depe_id = ? 
                                    ORDER BY depe_fechacambio;");
                    							
        $sql->bindValue(1, $_GET['id']);
        $sql->execute();
        header("HTTP/1.1 200 OK");
        $result = $sql->fetch(PDO::FETCH_ASSOC);
        if ($result == false) {
          $data = (object) array();
          $data->mensaje = "No se encontró el registro.";
          header("HTTP/1.1 400 Bad Request");
          echo json_encode( $data );
          exit();
        } else {
          echo json_encode($result);
          exit();
        }
  	  } else if (isset($_GET['idpedido'])) {
        $date = date("Y-m-d");
        $sql = $conexion->prepare(" SELECT 
        depe.depe_id as id,
        depe.depe_descripcion as descripcion,
        depe.pedi_id as idpedido,
        tipr.tipr_id as idtipoproducto,
        tipr.tipr_descripcion as descripciontipoproducto,
        tipr2.tipr_id as idtipoproducto2,
        tipr2.tipr_descripcion as descripciontipoproducto2,
        depe.prod_id as idproducto,
        depe.prod_id2 as idproducto2,
        CONCAT(IF(ISNULL(depe.prod_id2), CONCAT(prod.prod_descripcion), CONCAT(IF(ISNULL(depe.prod_id2), ' ', '* 1/2'), ' ', prod.prod_descripcion)), ' ', IF(ISNULL(depe.prod_id2), '', CONCAT(IF(ISNULL(depe.prod_id2), '', '<br>* 1/2 '), ' ', prod2.prod_descripcion))) AS descripcionprod,
        prod.prod_descripcion as descripcionproducto,
        prod2.prod_descripcion as descripcionproducto2,
        depe.prod_costo as costoproducto,
        depe.prod_cantidad as cantidadproducto,
        depe.prod_precio as precioproducto
        FROM pinchetas_restaurante.detallepedido depe
        inner join pinchetas_restaurante.pedido pedi on (pedi.pedi_id = depe.pedi_id)
        inner join pinchetas_restaurante.producto prod on (prod.prod_id = depe.prod_id)
        inner join pinchetas_restaurante.tipoproducto tipr on (tipr.tipr_id = prod.tipr_id)
        left join pinchetas_restaurante.producto prod2 on (prod2.prod_id = depe.prod_id2)
        left join pinchetas_restaurante.tipoproducto tipr2 on (tipr2.tipr_id = prod2.tipr_id)
        WHERE depe.pedi_id = ?
        ORDER BY depe_fechacambio;");
        $sql->bindValue(1, $_GET['idpedido']);
        $sql->execute();
        $sql->setFetchMode(PDO::FETCH_ASSOC);
        header("HTTP/1.1 200 OK");
        echo json_encode( $sql->fetchAll() );
        exit();
  	  } else {
        $date = date("Y-m-d");
        $sql = $conexion->prepare(" depe.depe_id as id,
                                    depe.depe_descripcion as descripcion,
                                    depe.pedi_id as idpedido,
                                    tipr.tipr_id as idtipoproducto,
                                    tipr.tipr_descripcion as descripciontipoproducto,
                                    depe.prod_id as idproducto,
                                    prod.prod_descripcion as descripcionproducto,
                                    depe.prod_costo as costoproducto,
                                    depe.prod_cantidad as cantidadproducto,
                                    depe.prod_precio as precioproducto
                                    FROM pinchetas_restaurante.detallepedido depe
                                    inner join pinchetas_restaurante.pedido pedi on (pedi.pedi_id = depe.pedi_id)
                                    inner join pinchetas_restaurante.producto prod on (prod.prod_id = depe.prod_id)
                                    inner join pinchetas_restaurante.tipoproducto tipr on (tipr.tipr_id = prod.tipr_id)
                                    ORDER BY depe_fechacambio;");
        $sql->execute();
        $sql->setFetchMode(PDO::FETCH_ASSOC);
        header("HTTP/1.1 200 OK");
        echo json_encode( $sql->fetchAll() );
        exit();
  	  }
  }
  // Crear un nuevo post
  else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      $input = $_POST;

      $idestadopedido = 0;
      $descripcion = $frm['descripcion'];
      $pedido = $frm['pedido'];
      $idpedido = $frm['idpedido'];
      $idproducto = $frm['idproducto'];
      $idproducto2 = $frm['idproducto2'];
      $cantidadproducto = $frm['cantidadproducto'];
      $registradopor = openCypher('decrypt', $frm['token']);
      $date = date("Y-m-d H:i:s");

      if ($pedido) {
        $idestadopedido = $pedido['idestado'];
      }
      
      $sql = "INSERT INTO 
        pinchetas_restaurante.detallepedido(depe_descripcion, pedi_id, prod_id, prod_id2, prod_costo, prod_cantidad, prod_precio, depe_registradopor, depe_fechacambio)
        VALUES (
        ?,
        ?,
        ?,
        ?,
        (select MAX(prod_costo) from pinchetas_restaurante.producto where prod_id = ? OR prod_id = ?),
        ?,
        (select MAX(prod_precio) from pinchetas_restaurante.producto where prod_id = ? OR prod_id = ?),
        ?,
        ?); ";
            
      $sql = $conexion->prepare($sql);
      $sql->bindValue(1, $descripcion);
      $sql->bindValue(2, $idpedido);
      $sql->bindValue(3, $idproducto);
      $sql->bindValue(4, $idproducto2);
      $sql->bindValue(5, $idproducto);
      $sql->bindValue(6, $idproducto2);
      $sql->bindValue(7, $cantidadproducto);
      $sql->bindValue(8, $idproducto);
      $sql->bindValue(9, $idproducto2);
      $sql->bindValue(10, $registradopor);
      $sql->bindValue(11, $date);
      $sql->execute();
      $postId = $conexion->lastInsertId();
      
      $frm['id'] = $postId;
 
      if ($idestadopedido > 1) {
        printCommand($frm, 'ADD');
      }

    $input['id'] = $postId;
    $input['mensaje'] = "Registrado con éxito";
    header("HTTP/1.1 200 OK");
    echo json_encode($input);
    exit();
  	  
  }
  //Actualizar
  else if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
      $input = $_GET;
      
      $id = $frm['id'];
      $descripcion = $frm['descripcion'];
      $idpedido = $frm['idpedido'];
      $idproducto = $frm['idproducto'];
      $idproducto2 = $frm['idproducto2'];
      $cantidadproducto = $frm['cantidadproducto'];
      $registradopor = openCypher('decrypt', $frm['token']);
      $date = date("Y-m-d H:i:s");

      $idestadopedido = 0;
      $pedido = $frm['pedido'];

      if ($pedido) {
        $idestadopedido = $pedido['idestado'];
      }
      if ($idestadopedido > 1) {
        printCommand($frm, 'EDIT');
      }
      
      $sql = "UPDATE pinchetas_restaurante.detallepedido 
              SET depe_descripcion = ?,
              pedi_id = ?,
              prod_id = ?,
              prod_id2 = ?,
              prod_costo = (select MAX(prod_costo) from pinchetas_restaurante.producto where prod_id = ? OR prod_id = ?),
              prod_cantidad = ?,
              prod_precio = (select MAX(prod_precio) from pinchetas_restaurante.producto where prod_id = ? OR prod_id = ?),
              depe_registradopor = ?,
              depe_fechacambio = ?
              WHERE depe_id = ?; ";
            
      $sql = $conexion->prepare($sql);
      $sql->bindValue(1, $descripcion);
      $sql->bindValue(2, $idpedido);
      $sql->bindValue(3, $idproducto);
      $sql->bindValue(4, $idproducto2);
      $sql->bindValue(5, $idproducto);
      $sql->bindValue(6, $idproducto2);
      $sql->bindValue(7, $cantidadproducto);
      $sql->bindValue(8, $idproducto);
      $sql->bindValue(9, $idproducto2);

      $sql->bindValue(10, $registradopor);
      $sql->bindValue(11, $date);
      $sql->bindValue(12, $id);
      $result = $sql->execute();
      
      if($result) {
        $input['id'] = $result;
        $input['mensaje'] = "Actualizado con éxito";
        header("HTTP/1.1 200 OK");
        echo json_encode($input);
        exit();
  	  } else {
        $input['id'] = $result;
        $input['mensaje'] = "Error actualizando";
        header("HTTP/1.1 400 Bad Request");
        echo json_encode($input);
        exit();
  	  }
  	  
  }
  // Eliminar
  else if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
      $input = $_GET;
      $id = $input['id'];
      $registradopor = openCypher('decrypt', $input['token']);

      $idestadopedido = 0;
      $pedido = $frm['pedido'];

      if ($pedido) {
        $idestadopedido = $pedido['idestado'];
      }
      if ($idestadopedido > 1) {
        printCommand($frm, 'CANCEL');
      }

      $date = date("Y-m-d H:i:s");
      
      $sql = "CALL procedimiento_eliminar_detallepedido(?, ?); ";
            
      $sql = $conexion->prepare($sql);
      $sql->bindValue(1, $id);
      $sql->bindValue(2, $registradopor);
      $result = $sql->execute();
      if($result) {
        $output['id'] = $postId;
        $output['mensaje'] = "Eliminado con éxito";
        header("HTTP/1.1 200 OK");
        echo json_encode($output);
        exit();
  	  } else {
        $output['id'] = $postId;
        $output['mensaje'] = "Error eliminando";
        header("HTTP/1.1 400 Bad Request");
        echo json_encode($output);
        exit();
  	  }
  }

} catch (Exception $e) {
    echo 'Excepción capturada: ', $e->getMessage(), "\n";
}

//En caso de que ninguna de las opciones anteriores se haya ejecutado
// header("HTTP/1.1 400 Bad Request");

?>