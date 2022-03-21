<!-- all functions which download data from server to client -->

<?php
// required headers
include_once 'dbConnection.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8"); 



// start download data from server to client for the first time
$tables = array();
$sql="SHOW TABLES FROM $db_name";

$stmt = $conn->prepare($sql);

try {
    if ($stmt->execute())

        foreach ($row = $stmt->fetchAll(PDO::FETCH_ASSOC) as $outer_key => $array){
            foreach($array as $inner_key => $value){

                $sql="Select *  FROM $value";
                $stmt = $conn->prepare($sql);
                if($stmt->execute()){
                    $result=$stmt->fetchAll(PDO::FETCH_ASSOC);
                    $tables[$value]=$result;
                }
               
            }
    }
    
}catch (Exception $e){
    return $e->getMessage();
}
echo json_encode( $tables)

?>
<!-- function  -->