
<?php 
include 'database.php';

$draw = $_POST['draw'];
$row = $_POST['start'];
$rowperpage = $_POST['length']; // Rows display per page
$columnIndex = $_POST['order'][0]['column']; // Column index
$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
$searchValue = mysqli_real_escape_string($con, $_POST['search']['value']); // Search value

$searchQuery = "";
if (!empty($searchValue)) {
    $searchValue = mysqli_real_escape_string($con, $searchValue);
    $searchQuery = " HAVING (
        id LIKE '%$searchValue%' OR 
        email LIKE '%$searchValue%' OR 
        firstname LIKE '%$searchValue%' OR
        lastname LIKE '%$searchValue%' OR
        phone LIKE '%$searchValue%' OR
        course LIKE '%$searchValue%'
    )";
}

// Total number of records without filtering
$totalRecordsQuery = "SELECT COUNT(*) AS allcount FROM studentinfo";
$totalRecordsResult = mysqli_query($con, $totalRecordsQuery);
$records = mysqli_fetch_assoc($totalRecordsResult);
$totalRecords = $records['allcount'];

// Total number of records with filtering
$totalRecordWithFilterQuery = "SELECT COUNT(*) AS allcount FROM studentinfo WHERE 1 $searchQuery";
$totalRecordWithFilterResult = mysqli_query($con, $totalRecordWithFilterQuery);
$records = mysqli_fetch_assoc($totalRecordWithFilterResult);
$totalRecordwithFilter = $records['allcount'];


$empQuery = "SELECT 
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
             WHERE 1
             GROUP BY
                studentinfo.id, studentinfo.email
             $searchQuery
             ORDER BY
                $columnName $columnSortOrder
             LIMIT $row, $rowperpage";

$empRecords = mysqli_query($con, $empQuery);
$data = array();

while ($row = mysqli_fetch_assoc($empRecords)) {
    $data[] = array( 
        "id" => $row['id'],
        "email" => $row['email'],
        "firstname" => $row['firstname'],
        "lastname" => $row['lastname'],
        "phone" => $row['phone'],
        "course" => $row['course']
    );
}

              $response = array(
                "draw" => intval($draw),
                "iTotalRecords" => $totalRecords,
                "iTotalDisplayRecords" => $totalRecordwithFilter,
                "aaData" => $data
              );
              
              echo json_encode($response);
            

?>

