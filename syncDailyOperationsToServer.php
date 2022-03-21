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
    // Converts it into a PHP object
    $columns = array();
    $cc = "";

    $columnsValues = array();
    $ccV = "";

    $co = array();
    $va = array();

    if ($json->data) {
        foreach ($json->data as $obj) {
            if ($obj->status == "newClient") {
                //fetch values
                foreach ($obj->values as $objValues => $newValue) {
                    $columnsValues[] = $newValue;
                    //get rowsTotal of request 
                    $rowsTotal++;
                }

                $sql = "SELECT column_name FROM information_schema.columns WHERE table_name = '$obj->table'";
                $stmt = $conn->prepare($sql);

                try {
                    if ($stmt->execute()) {
                        $raw_column_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($raw_column_data as $objColumn => $value) {
                            //fetch columns name from selected table
                            foreach ($value as $columnKey => $columnValue) {
                                $columns[] = $columnValue;
                                if ($columns) {
                                    //some operations to shift syntax of columns into => a,b,c,d,e
                                    $cc = implode(",", $columns);
                                    $cx = trim($cc, '"');
                                    //some operations to shift syntax of values into => (a,b,c,d,e) (a,b,c,d,e)
                                    $ccV = implode(",", $columnsValues);
                                    $ccV = trim($ccV, '"');
                                }
                            }
                        }
                        // start sync data with client by Insertion
                        $sql = "INSERT INTO $obj->table ($cx)";
                        $sql .= "VALUES";
                        $sql .= $ccV;
                        // echo $sql;
                        $stmt = $conn->prepare($sql);
                        if ($stmt->execute()) {
                            $id = $conn->lastInsertId();
                            //update sync_status after insertion
                            $sql = "UPDATE $obj->table set sync_status='editServer' WHERE id=$id ";

                            $stmt = $conn->prepare($sql);
                            if ($stmt->execute()) {
                                //fetch effcted rows 
                                $number_of_rows = $stmt->fetchColumn();
                                $number_of_rows += $number_of_rows;
                                header("rowsTotal:$rowsTotal");
                                header("rowsEffected:$number_of_rows");

                                echo "Sync data with server successfully";
                            }
                        }
                    }
                } catch (Exception $e) {
                    return $e->getMessage();
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
                                    $sql = "UPDATE $obj->table set $columns='$columnsValue' WHERE id=$values->id";
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
