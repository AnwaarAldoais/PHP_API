<!-- all functions to sync daily operations between server and client -->

<?php
// required headers
include_once './dbConnection.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");


$sync_status = array('newServer', 'editServer', 'deleServer');
$param = str_repeat("?,", count($sync_status) - 1) . "?";

// start sync daily operations between server and client =:status1 OR sync_status=:status2 OR sync_status=:status3
$tables = array();
$data = array();
$sql = "SELECT table_name FROM information_schema.tables WHERE table_schema = '" .  $db_name . "'";

$stmt = $conn->prepare($sql);

try {
    if ($stmt->execute()) {

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $outer_key => $array) {
            foreach ($array as $inner_key => $value) {

                $sql = "Select *  FROM `$value`  WHERE  `sync_status` IN ($param)";
                $stmt = $conn->prepare($sql);
                if ($stmt->execute($sync_status)) {
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $data[$value] = $result;   
                }                   
            }
        }             
    }
} catch (Exception $e) {
    return $e->getMessage();
}
echo json_encode($data);
?>
<!-- function  -->
