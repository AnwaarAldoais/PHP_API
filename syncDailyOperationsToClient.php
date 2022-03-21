<!-- all functions to sync daily operations between server and client -->

<?php
// required headers
include_once './dbConnection.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");


// $status1='newServerOnly';
// $status2='editServerOnly';
// $status3='delServerOnly';
// $pr=[
//     ":status1"=>$status1,
//     ":status2"=>$status2,
//     ":status3"=>$status3,
// ];
$sync_status = array('newServerOnly', 'editServerOnly', 'delServerOnly');
$param = str_repeat("?,", count($sync_status) - 1) . "?";

// start sync daily operations between server and client =:status1 OR sync_status=:status2 OR sync_status=:status3
$tables = array();
$data = array();
$sql = "SELECT table_name FROM information_schema.tables WHERE table_schema = '" .  $db_name . "'";

$stmt = $conn->prepare($sql);

try {
    if ($stmt->execute()) {

        foreach ($row = $stmt->fetchAll(PDO::FETCH_ASSOC) as $outer_key => $array) {
            foreach ($array as $inner_key => $value) {


                $sql = "Select *  FROM `$value` c WHERE  `sync_status` IN ($param)";
                $stmt = $conn->prepare($sql);

                if ($stmt->execute($sync_status)) {
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($result as $row => $rowValue) {
                        $data[$value] = $rowValue;
                        echo json_encode($data);
                    }
                }
            }
        }
    }
} catch (Exception $e) {
    return $e->getMessage();
}

?>
<!-- function  -->