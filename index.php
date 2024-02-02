<!doctype html>
<html lang="en">

<head>
  
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">

    <title>PHP CRUD </title>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css" />
</head>

<body>

    <div class="modal fade" id="studentAddModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Add Employee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="saveStudent">
                    <div class="modal-body">

                        <div id="errorMessage" class="alert alert-warning d-none"></div>

                        <div class="mb-3">
                            <label for="">Email</label>
                            <input type="text" name="email" class="form-control"  required/>
                        </div>
                        <div class="mb-3">
                            <label for="">Firstname</label>
                            <input type="text" name="firstname" class="form-control" required />
                        </div>
                        <div class="mb-3">
                            <label for="">lastname</label>
                            <input type="text" name="lastname"  class="form-control" />
                        </div>
                        <div class="mb-3">
                            <label for="">Phone</label>
                            <input type="text" name="phone"  class="form-control" />
                        </div>
                        <div class="mb-3">
                            <label for="">Course</label>
                            <input type="text" name="course"  class="form-control" />
                        </div>
                     
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Employee</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

 
    <div class="modal fade" id="studentEditModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Edit Employee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="updateStudentForm" method="post">
                    <div class="modal-body">
                        <div id="errorMessageUpdate" class="alert alert-warning d-none"></div>
                        <input type="hidden" name="student_id" id="student_id">

                        <div class="mb-3">
                            <label for="">Email</label>
                            <input type="email" name="email" id="email"  class="form-control" />
                        </div>
                        <div class="mb-3">
                            <label for="">FirstName</label>
                            <input type="text" name="firstname" id="firstname" class="form-control" required />
                        </div>
                        <div class="mb-3">
                            <label for="">LastName</label>
                            <input type="text" name="lastname" id="lastname"  class="form-control" />
                        </div>
                  
                    <div class="mb-3">
                            <label for="">Phone</label>
                            <input type="text" name="phone" id="phone"  class="form-control" />
                        </div>
                        <div class="mb-3">
                            <label for="">Course</label>
                            <input type="text" name="course" id="course"  class="form-control" />
                        </div>
                        </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update Employee</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <button type="button" class="btn btn-primary float-left" data-bs-toggle="modal" data-bs-target="#studentAddModal">
                            Add Student
                        </button>
                    </div>
                    <div class="card-body">
                        <table id="myTable" class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Email</th>
                                    <th>FirstName</th>
                                    <th>LastName</th>
                                    <th>Phone</th>
                                    <th>Course</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.js">
    </script>
    <script src="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>

    <script>
$(document).ready(function() {
    $(document).on('submit', '#saveStudent', function(e) {
    e.preventDefault();

    var formData = new FormData(this);
    formData.append("save_student", true);
    $.ajax({
        type: "POST",
        url: "backend.php",
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            var res = jQuery.parseJSON(response);
            if (res.status == 422) {
                $('#errorMessage').removeClass('d-none');
                $('#errorMessage').text(res.message);
            } else if (res.status == 200) {
                var table = $('#myTable').DataTable();

                table.row.add([
                    res.data.id,
                    res.data.email,
                    res.data.firstname, 
                    res.data.lastname,
                    res.data.phone,
                    res.data.course,
                   
                    '<button type="button" value="' + res.data.id +
                    '" class="editStudentBtn btn btn-success btn-sm m-1">Edit</button>' +
                    '<button type="button" value="' + res.data.id +
                    '" class="deleteStudentBtn btn btn-danger btn-sm ">Delete</button>'
                ]).draw();

                $('#errorMessage').addClass('d-none');
                $('#studentAddModal').modal('hide');
                $('#saveStudent')[0].reset();

                alertify.set('notifier', 'position', 'top-right');
                alertify.success(res.message);
            } else if (res.status == 500) {
                alert(res.message);
            }
        }
    });
});
});



$(document).on('click', '.editStudentBtn', function() {
    var student_id = $(this).val();

    $.ajax({
        type: "GET",
        url: "backend.php",  // Adjust the URL according to your backend endpoint
        data: { student_id: student_id },  // Send student_id as a parameter in the GET request
        dataType: "json",
        success: function(res) {
            if (res.status == 404) {
                alert(res.message);
            } else if (res.status == 200) {
                $('#student_id').val(res.data.id);
                $('#email').val(res.data.email);
                $('#firstname').val(res.data.firstname);
                $('#lastname').val(res.data.lastname);
                $('#phone').val(res.data.phone);
                $('#course').val(res.data.course);
                $('#studentEditModal').modal('show');
            }
        },
        error: function() {
            console.log('Error fetching student data.');
        }
    });
});



$(document).on('submit', '#updateStudentForm', function (e) {
    e.preventDefault();
    var formData = new FormData(this);
    formData.append("update_student", true);

    $.ajax({
        type: "POST",
        url: "backend.php",
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            var res = jQuery.parseJSON(response);

            if (res.status == 422) {
                $('#errorMessageUpdate').removeClass('d-none');
                $('#errorMessageUpdate').text(res.message);
            } else if (res.status == 200) {
                $('#errorMessageUpdate').addClass('d-none');
                alertify.set('notifier', 'position', 'top-right');
                alertify.success(res.message);
                $('#studentEditModal').modal('hide');
                $('#updateStudentForm')[0].reset();
                var table = $('#myTable').DataTable();
                // Find the row to update based on student_id
                var rowToUpdate = table.row(function (idx, data, node) {
                    return data[0] == res.data.id;
                });
                if (rowToUpdate.length > 0) {
                    // Update the row data
                    rowToUpdate.data([
                        res.data.id,
                        res.data.email,
                        res.data.firstname,
                        res.data.lastname,
                        res.data.phone,
                        res.data.course,
                        res.data.buttonsHtml,
                    ]).draw();
                } else {
             
                    table.draw();
                    // table.ajax.reload();
                }
            } else if (res.status == 500) {
                alert(res.message);
            }
        }
    });
});


$(document).ready(function () {
    $('#myTable').DataTable({
        columnDefs: [{
                    "defaultContent": "-",
                    "targets": "_all"
                }],
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'ajax': {
            'url': 'fetch_data.php',
        },
        'columns': [
              {data:'id'},
              { data: 'email' },
              { data: 'firstname' },
              { data: 'lastname' },
              { data: 'phone' },
              { data: 'course' },
     {
        data: null,
        render: function (data, type, row) {
            return '<button class="editStudentBtn btn btn-success btn-sm m-1" value="' + row.id + '">Edit</button>' +
                '<button class="deleteStudentBtn btn btn-danger btn-sm" value="' + row.id + '">Delete</button>';
        }
    }
]
    });
});


        $(document).on('click', '.deleteStudentBtn', function(e) {
        e.preventDefault();

        if (confirm('Are you sure you want to delete this student?')) {
        var student_id = $(this).val();
        var rowToDelete = $(this).closest('tr');

        $.ajax({
            type: "POST",
            url: "backend.php",
            data: {
                'delete_student': true,
                'student_id': student_id
            },
            dataType: 'json',
            
            success: function(response) {
                var res = jQuery.parseJSON(response);
                if (res.status == 500) {
                    alert(res.message);
                } else {
                    alertify.set('notifier', 'position', 'top-right');
                    alertify.success(res.message);

                    var table = $('#myTable').DataTable();
                    var rowIndex = table.row(rowToDelete).index();
                    table.row(rowIndex).remove().draw();
                }
            }
        });
    }
});





    </script>

</body>

</html>