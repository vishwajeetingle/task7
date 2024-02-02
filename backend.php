<?php

    require 'database.php';

    if (isset($_POST['save_student'])) {
        $id= mysqli_real_escape_string($con, $_POST['id']);
        $email = mysqli_real_escape_string($con, $_POST['email']);
        $firstname = mysqli_real_escape_string($con, $_POST['firstname']);
        $lastname = mysqli_real_escape_string($con, $_POST['lastname']);
        $phone = mysqli_real_escape_string($con, $_POST['phone']);
        $course = mysqli_real_escape_string($con, $_POST['course']);
    
        $query_studentinfo = "INSERT INTO studentinfo (email) VALUES ('$email')";
        $query_run_studentinfo = mysqli_query($con, $query_studentinfo);
    
        if ($query_run_studentinfo) {
            $lastInsertedId = mysqli_insert_id($con);
    
            foreach ($_POST as $key => $value) {
                if ($key === 'save_student') {
                    continue;
                }
                if ($key !== 'email' && !empty($value)) {
                    $metakey = mysqli_real_escape_string($con, $key);
                    $metavalue = mysqli_real_escape_string($con, $value);
    
                    $query_studentmeta = "INSERT INTO studentmeta (student_id, metakey, metavalue) VALUES ('$lastInsertedId', '$metakey', '$metavalue')";
                    $query_run_studentmeta = mysqli_query($con, $query_studentmeta);
    
                    if (!$query_run_studentmeta) {
                        // Rollback the insertion in studentinfo table if studentmeta insertion fails
                        mysqli_query($con, "DELETE FROM studentinfo WHERE id = '$lastInsertedId'");
                        $res = [
                            'status' => 500,
                            'message' => 'Student Not Created'
                        ];
                        echo json_encode($res);
                        return;
                    }
                }
            }
        
            $zapierWebhookUrl = 'https://hooks.zapier.com/hooks/catch/17806201/3qehgrm/';
    
            $zapierData = array(
                'id'=>$lastInsertedId,
                'email' => $email,
                'firstname' => $firstname,
                'lastname' => $lastname,
                'phone' => $phone,
                'course' => $course,
            );

            $zapierDataJson = json_encode($zapierData);
    

            $ch = curl_init($zapierWebhookUrl);
    
            // Set cURL options
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $zapierDataJson);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($zapierDataJson),
            ));
    
         
            $response = curl_exec($ch);
  
            if (curl_errno($ch)) {
                echo 'Curl error: ' . curl_error($ch);
            } else {
                echo 'Data sent to Zapier successfully!';
            }
    
            // Close cURL session
            curl_close($ch);
    
            $res = [
                'status' => 200,
                'message' => 'Student Created Successfully',
                'data' => [
                    'id' => $lastInsertedId,
                    'email' => $email,
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'phone' => $phone,
                    'course' => $course,
                    'buttons' => [
                        'edit' => '<button type="button" value="' . $lastInsertedId . '" class="editStudentBtn btn btn-success btn-sm m-2">Edit</button>',
                        'delete' => '<button type="button" value="' . $lastInsertedId . '" class="deleteStudentBtn btn btn-danger btn-sm m-2">Delete</button>'
                    ]
                ]
            ];
            echo json_encode($res);
            return;
        }
    
        $res = [
            'status' => 500,
            'message' => 'Student Not Created'
        ];
        echo json_encode($res);
        return;
    }
    



    if (isset($_POST['update_student'])) {
        $student_id = mysqli_real_escape_string($con, $_POST['student_id']);
        $email = mysqli_real_escape_string($con, $_POST['email']);
        $firstname = mysqli_real_escape_string($con, $_POST['firstname']);
        $lastname = mysqli_real_escape_string($con, $_POST['lastname']);
        $phone = mysqli_real_escape_string($con, $_POST['phone']);
        $course = mysqli_real_escape_string($con, $_POST['course']);
    
        $query_studentinfo = "UPDATE studentinfo SET email=? WHERE id=?";
        $stmt_studentinfo = mysqli_prepare($con, $query_studentinfo);
        mysqli_stmt_bind_param($stmt_studentinfo, "si", $email, $student_id);
    
        // Update studentmeta table for other fields
        $query_firstname = "UPDATE studentmeta SET metavalue=? WHERE student_id=? AND metakey='firstname'";
        $stmt_firstname = mysqli_prepare($con, $query_firstname);
        mysqli_stmt_bind_param($stmt_firstname, "si", $firstname, $student_id);
    
        $query_lastname = "UPDATE studentmeta SET metavalue=? WHERE student_id=? AND metakey='lastname'";
        $stmt_lastname = mysqli_prepare($con, $query_lastname);
        mysqli_stmt_bind_param($stmt_lastname, "si", $lastname, $student_id);
    
        $query_phone = "UPDATE studentmeta SET metavalue=? WHERE student_id=? AND metakey='phone'";
        $stmt_phone = mysqli_prepare($con, $query_phone);
        mysqli_stmt_bind_param($stmt_phone, "si", $phone, $student_id);
    
        $query_course = "UPDATE studentmeta SET metavalue=? WHERE student_id=? AND metakey='course'";
        $stmt_course = mysqli_prepare($con, $query_course);
        mysqli_stmt_bind_param($stmt_course, "si", $course, $student_id);
    
        mysqli_begin_transaction($con);
    
        // Check for successful query executions
        $query_run_studentinfo = mysqli_stmt_execute($stmt_studentinfo);
        $query_run_firstname = mysqli_stmt_execute($stmt_firstname);
        $query_run_lastname = mysqli_stmt_execute($stmt_lastname);
        $query_run_phone = mysqli_stmt_execute($stmt_phone);
        $query_run_course = mysqli_stmt_execute($stmt_course);
    
        if ($query_run_studentinfo && $query_run_firstname && $query_run_lastname && $query_run_phone && $query_run_course) {
            // Send data to Zapier using cURL
            $zapierWebhookUrl = 'https://hooks.zapier.com/hooks/catch/17806201/3qehgrm/' . $student_id;

            
            $zapierData = array(
                'email' => $email,
                'firstname' => $firstname,
                'lastname' => $lastname,
                'phone' => $phone,
                'course' => $course,
                'student_id' => $student_id,
        
            );
            $zapierDataJson = json_encode($zapierData);
    
     
            $ch = curl_init($zapierWebhookUrl);
    
            // Set cURL options
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $zapierDataJson);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($zapierDataJson),
            ));
    
            // Execute cURL session and get the response
            $response = curl_exec($ch);
    
            // Check for cURL errors
            if (curl_errno($ch)) {
                echo 'Curl error: ' . curl_error($ch);
            } else {
                echo 'Data sent to Zapier successfully!';
            }
    
      
            curl_close($ch);
    
            $buttonsHtml = '<button type="button" value="' . $student_id . '" class="editStudentBtn btn btn-success btn-sm m-1">Edit</button>' .
            '<button type="button" value="' . $student_id . '" class="deleteStudentBtn btn btn-danger btn-sm ">Delete</button>';
            $res = [
                'status' => 200,
                'message' => 'Student Updated Successfully',
                'data' => [
                    'id' => $student_id,
                    'email' => $email,
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'phone' => $phone,
                    'course' => $course,
                    'buttonsHtml' => $buttonsHtml,
                ]
            ];
            echo json_encode($res);
            mysqli_commit($con);
       
    
        } else {
            mysqli_rollback($con);
            $res = [
                'status' => 500,
                'message' => 'Student Not Updated'
            ];
            echo json_encode($res);
        }
        mysqli_stmt_close($stmt_studentinfo);
        mysqli_stmt_close($stmt_firstname);
       
    }
    






if (isset($_GET['student_id'])) {
    $student_id = $_GET['student_id'];

    $query = 
        "SELECT 
            studentinfo.id,
            studentinfo.email,
            MAX(CASE WHEN studentmeta.metakey = 'firstname' THEN studentmeta.metavalue END) as firstname,
            MAX(CASE WHEN studentmeta.metakey = 'lastname' THEN studentmeta.metavalue END) as lastname,
            MAX(CASE WHEN studentmeta.metakey = 'phone' THEN studentmeta.metavalue END) as phone,
            MAX(CASE WHEN studentmeta.metakey = 'course' THEN studentmeta.metavalue END) as course
        FROM 
            studentinfo
        LEFT JOIN 
            studentmeta ON studentinfo.id = studentmeta.student_id
        WHERE
            studentinfo.id = ?
        GROUP BY
            studentinfo.id, studentinfo.email
        ORDER BY
            studentinfo.id;";

    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "i", $student_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($result) == 1) {
        $studentData = mysqli_fetch_assoc($result);
        $res = [
            'status' => 200,
            'message' => 'Student Fetch Successfully by id',
            'data' => $studentData
        ];
        echo json_encode($res);
    } else {
        $res = [
            'status' => 404,
            'message' => 'Student Id Not Found'
        ];
        echo json_encode($res);
    }
    mysqli_stmt_close($stmt);
}



if (isset($_POST['delete_student'])) {
    $student_id = mysqli_real_escape_string($con, $_POST['student_id']);

    mysqli_begin_transaction($con);

    $query_studentmeta = "DELETE FROM studentmeta WHERE student_id=?";
    $stmt_studentmeta = mysqli_prepare($con, $query_studentmeta);

    if ($stmt_studentmeta) {
        mysqli_stmt_bind_param($stmt_studentmeta, "i", $student_id);
        $query_run_studentmeta = mysqli_stmt_execute($stmt_studentmeta);

        if ($query_run_studentmeta) {
            $query_studentinfo = "DELETE FROM studentinfo WHERE id=?";
            $stmt_studentinfo = mysqli_prepare($con, $query_studentinfo);

            if ($stmt_studentinfo) {
                mysqli_stmt_bind_param($stmt_studentinfo, "i", $student_id);
                $query_run_studentinfo = mysqli_stmt_execute($stmt_studentinfo);

                if ($query_run_studentinfo) {
                    $zapierWebhookUrl = 'https://hooks.zapier.com/hooks/catch/17806201/3qehgrm/'.$student_id;

                    $zapierData = array(
                        'action' => 'delete',
                        'student_id' => $student_id,
                        
                    );

                    $zapierDataJson = json_encode($zapierData);

                    // Initialize cURL session
                    $ch = curl_init($zapierWebhookUrl);

                    // Set cURL options
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $zapierDataJson);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        'Content-Type: application/json',
                        'Content-Length: ' . strlen($zapierDataJson),
                    ));

                    // Execute cURL session and get the response
                    $response = curl_exec($ch);

                    // Check for cURL errors
                    if (curl_errno($ch)) {
                        echo 'Curl error: ' . curl_error($ch);
                        mysqli_rollback($con);
                        $res = [
                            'status' => 500,
                            'message' => 'Error sending data to Zapier'
                        ];
                        echo json_encode($res);
                        exit;  // Exit the script after encountering an error
                    } else {
                        echo 'Data sent to Zapier successfully!';
                    }

                    // Close cURL session
                    curl_close($ch);

                    // Commit the transaction if both deletions and Zapier data send are successful
                    mysqli_commit($con);
                    $res = [
                        'status' => 200,
                        'message' => 'Student Deleted Successfully'
                    ];
                    echo json_encode($res);
                } else {
                    mysqli_rollback($con);

                    $res = [
                        'status' => 500,
                        'message' => 'Student Not Deleted (studentinfo)'
                    ];
                    echo json_encode($res);
                }
                mysqli_stmt_close($stmt_studentinfo);
            } else {
                mysqli_rollback($con);
                $res = [
                    'status' => 500,
                    'message' => 'Prepared statement for studentinfo failed'
                ];
                echo json_encode($res);
            }
        } else {
            mysqli_rollback($con);
            $res = [
                'status' => 500,
                'message' => 'Student Not Deleted (studentmeta)'
            ];
            echo json_encode($res);
        }
        mysqli_stmt_close($stmt_studentmeta);
    } else {
        mysqli_rollback($con);
        $res = [
            'status' => 500,
            'message' => 'Prepared statement for studentmeta failed'
        ];
        echo json_encode($res);
    }
    mysqli_close($con);
}





?>

