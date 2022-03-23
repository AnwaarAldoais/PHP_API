<?php

include_once 'dbConnection.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Takes raw data from the request
    $json = json_decode(file_get_contents("php://input"));

    $rowsTotal = 0;
    $number_of_rows = 0;
    // Converts it into a PHP object
    $columns = array();
    $cc = "";
    $cx = "";
    $columnsValues = array();
    $ccV = "";
    $ccT = "";
    $status = "";
    $tables = array();

    if ($json->data) {
        foreach ($json->data as $obj) {
            if ($obj->status == "newClient") {

                //fetch tables
                $tables[] = $obj->table;

                //fetch values
                foreach ($obj->values as $objValues => $newValue) {
                    $columnsValues[] = $newValue;
                    //get rowsTotal of request 
                    $rowsTotal++;
                }

                $sql = "SELECT column_name FROM information_schema.columns WHERE table_name = '$obj->table'";
                $stmt = $conn->prepare($sql);
                if ($stmt->execute()) {
                    $raw_column_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    // echo json_encode($raw_column_data);

                    foreach ($raw_column_data as $objColumn => $value) {
                        //fetch columns name from selected table
                        foreach ($value as $columnKey => $columnValue) {
                            $columns[] = $columnValue;
                        }
                    }
                }
                if ($columns) {
                    $columns[] .= "\n";
                    $columnsValues[] .= "\n";
                    $tables[] .= "\n";
                    //some operations to shift syntax of columns[] into => "a,b,c,d,e"
                    $cc = implode(",", $columns);
                    $cx = trim($cc, '"');

                    //some operations to shift syntax of values[] into => (a,b,c,d,e) (a,b,c,d,e)
                    $ccV = implode(",", $columnsValues);
                    //some operations to shift syntax of values into => (a,b,c,d,e) (a,b,c,d,e)
                    $ccT = implode("", $tables);
                }
            } else if ($obj->status == "deleClient") {
                foreach ($obj->values as $deleTables) {
                    $rowsTotal++;

                    //delete from table
                    $sql = "DELETE FROM $obj->table WHERE id=$deleTables";
                    $stmt = $conn->prepare($sql);

                    try {
                        if ($stmt->execute()) {
                            //fetch effcted rows 
                            $number_of_rows = $stmt->rowCount();

                            header("rowsTotal:$rowsTotal");
                            header("rowsEffected:$number_of_rows");

                            echo "Sync data with server successfully";
                        }
                    } catch (Exception $e) {
                        return $e->getMessage();
                    }
                }
            } else {

                foreach ($obj->values as $values) {
                    $rowsTotal++;
                    $sql = "SELECT * FROM $obj->table WHERE id=$values->id AND sync_status='editServer' ";
                    $stmt = $conn->prepare($sql);

                    try {
                        if ($stmt->execute()) {
                            $raw_column_data = $stmt->fetch(PDO::FETCH_ASSOC);
                            foreach ($values as $columns => $columnsValue) {
                                if (strtotime($values->updated_at) > $raw_column_data["updated_at"]) {
                                    $sql = "UPDATE $obj->table set $columns='$columnsValue',sync_status='sync' WHERE id=$values->id";
                                    $stmt = $conn->prepare($sql);
                                    if ($stmt->execute()) {
                                        header("rowsTotal:$rowsTotal");
                                        header("rowsEffected:$number_of_rows");

                                        echo "Sync data with server successfully";
                                    }
                                }
                            }
                        }
                    } catch (Exception $e) {
                        return $e->getMessage();
                    }
                }
            }
        }
    }
}
foreach ($json->data as $obj) {
    if ($obj->status == "newClient") {
        $status = "addnew";
    }
}
if ($status == "addnew") {
    $arr = explode("\n", $cc);

    $arr2 = explode("\n", $ccV);


    $arr3 = explode("\n", $ccT);


    for ($i = 0; $i < count($arr) - 1; $i++) {

        // start sync data with client by Insertion
        $sql = "INSERT INTO ";
        $sql .= trim($arr3[$i], ', ');
        $sql .= "(";
        $sql .=  trim($arr[$i], ', ');
        $sql .= ")";
        $sql .= "VALUES";
        $sql .= trim($arr2[$i], ', ');
        $sql .= ";";
        $stmt = $conn->prepare($sql);

        if ($stmt->execute()) {

            $number_of_rows = $stmt->rowCount();

            header("rowsTotal:$rowsTotal");
            header("rowsEffected:$number_of_rows");

            echo "Sync data with server successfully";
        }
    }
}
