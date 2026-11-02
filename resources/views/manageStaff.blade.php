<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>HSS | Manage Staff Account</title>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link href="{{ asset('import/assets/css/styles.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.4.0/fullcalendar.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.4.0/fullcalendar.min.js"></script>

    <link href="{{ asset('import/assets/vendors/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('import/assets/vendors/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('import/assets/vendors/boxicons/css/boxicons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('import/assets/vendors/quill/quill.snow.css') }}" rel="stylesheet">
    <link href="{{ asset('import/assets/vendors/quill/quill.bubble.css') }}" rel="stylesheet">
    <link href="{{ asset('import/assets/vendors/remixicon/remixicon.css') }}" rel="stylesheet">
    <link href="{{ asset('import/assets/vendors/simple-datatables/style.css') }}" rel="stylesheet">

    <style>
      .status-button {
          padding: 5px 10px;
          border-radius: 5px;
          font-weight: bold;
      }
      .edit {
          background-color: rgb(20, 133, 240);
          color: rgb(255, 255, 255);
      }
      .delete {
          background-color: rgb(197, 0, 0);
          color: white;
      }
      .status-pending {
          background-color: rgb(212, 209, 37);
          color: rgb(0, 0, 0);
      }
  </style>
</head>
<body>
    
    <x-app-layout>
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <i class="bi bi-list toggle-sidebar-btn"></i> &nbsp;
                {{ __('Admin | Manage Staff Account') }}
            </h2>
        </x-slot>

        <!-- ======= Sidebar ======= -->
        <aside id="sidebar" class="sidebar">

            <ul class="sidebar-nav" id="sidebar-nav">
  
            <li class="nav-item">
                <a class="nav-link collapsed" href="{{ route('staff-form') }}" :active="request()->routeIs('calendar.depschedule')">
                <i class="bi bi-person-plus-fill"></i>
                <span>Add New Account</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link " href="{{ route('addStaff') }}">
                <i class="bi bi-kanban-fill"></i>
                <span>Manage Staff Account</span>
                </a>
            </li>
            
        </aside><!-- End Sidebar-->

        <!-- Modal -->
        <div class="modal fade" id="leaveReviewModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">Edit Staff Details</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="{{ route('updateStaff') }}">
                            @csrf
                            <div class="form-group">
                                <label for="title">Staff Name:</label><br>
                                <input class="form-control" id="title" name="title">
                            </div>
                            <div class="form-group">
                                <label for="user_id">User ID:</label><br>
                                <input class="form-control" id="user_id" name="user_id" readonly>
                            </div>
                            <div class="form-group">
                                <label for="department">Department:</label><br>
                                <input class="form-control" id="department" name="department">
                            </div>
                            <div class="form-group">
                                <label for="role">Role:</label><br>
                                <input class="form-control" id="role" name="role">
                            </div>
                            <div class="form-group">
                                <label for="email">Email:</label><br>
                                <input class="form-control" id="email" name="email" rows="3">
                            </div> 
                        </div>
                        <div class="modal-footer">
                            <button id='closeModal' class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                            <button id="confirmBtn" class="btn btn-primary">Save</button>
                        </div>
                        </form>
                </div>
            </div>
        </div>   

        <!-- Modal -->
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="rejectionReasonModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="rejectionReasonModalLabel">Warning</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure to delete this account?</p>
                      </div>
                    <div class="modal-footer">
                      <button id='closeModal' class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                      <button id="confirmBtn" class="btn btn-primary">Confirm</button>
                    </div>
                </div>
            </div>
          </div>

          <div class="modal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Modal title</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <p>Modal body text goes here.</p>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-primary">Save changes</button>
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
              </div>
            </div>
          </div>
               
        <main id="main" class="main">
        
            <section class="section">
              <div class="row">
                <div class="col-lg-12">
        
                  <div class="card">
                    <div class="card-body">
                     
                    <!-- Table with stripped rows -->
                      <table class="table datatable">
                        <thead>
                          <tr>
                            <th scope="col">No</th>
                            <th scope="col">Name</th>
                            <th scope="col">ID</th>
                            <th scope="col">Department</th>
                            <th scope="col">Role</th>
                            <th scope="col">Email</th>
                            <th scope="col">Action</th>
                          </tr>
                        </thead>
                        <tbody>
                          @foreach ($staffs as $staff)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $staff->name }}</td>
                                <td>{{ $staff->id }}</td>
                                <td>{{ $staff->department?->name }}</td>
                                <td>{{ $staff->role }}</td>
                                <td>{{ $staff->email }}</td>
                                <td><button class="btn btn-danger btn-sm delete" onclick="deleteStaff('{{ $staff->id }}')">Delete</button>&nbsp;<button class="btn btn-primary btn-sm edit" data-bs-toggle="modal" data-bs-target="#leaveReviewModal" onclick="getStaff('{{ $staff->id }}')">Edit</button></td>
                            </tr>
                          @endforeach
                        </tbody>
                      </table>
                      <!-- End Table with stripped rows -->
                    </div>
                  </div>
        
                </div>
              </div>
            </section>
        
          </main><!-- End #main -->
        
          <!-- ======= Footer ======= -->
          <footer id="footer" class="footer">
            <div class="copyright">
              &copy; Copyright <strong><span>Hospital Scheduling System</span></strong>. All Rights Reserved
            </div>
          </footer><!-- End Footer -->
        
          <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
          
          <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.js"></script> 
          <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js" integrity="sha384-QJHtvGhmr9XOIpI6YVutG+2QOK9T+ZnN4kzFN1RtK3zEFEIsxhlmWl5/YESvpZ13" crossorigin="anonymous"></script>
          <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
          <script>
            $(document).ready(function() {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
            });
          </script>
          <script>
            function deleteStaff(id) {
                    $('#deleteModal').modal('show');
                    // Handle rejection reason form submission
                    $(document).on('click', '#confirmBtn', function () {
                      $('#deleteModal').modal('hide');
                      url = "{{ route('deleteStaff', ['id' => ':id']) }}";
                      url = url.replace(':id', id);
                      $.ajax({
                          url: url,
                          type: "DELETE",
                          dataType: 'json',
                          success: function (response) {
                              Swal.fire("Success", "Successfully deleted the account!", "success");
                              setTimeout(function() {
                                location.reload();
                            }, 2000);
                          },
                          error: function (xhr, status, error) {
                            Swal.fire('Error', 'Error encountered.', 'error');
                              if(error.responseJSON.errors) {
                                  $('#titleError').html(error.responseJSON.errors.title);
                              }
                              return;
                          }
                      });
                    });
                };
          </script>
          <script>
            function getStaff(id) {
                $.ajax({
                    url: '/getStaff/' + id,
                    method: 'GET',
                    success: function(response) {
                        // Handle the response here
                        console.log(response);
                        // Update the modal with the retrieved livestream data
                        $('#title').val(response.name);
                        $('#user_id').val(response.id);
                        $('#department').val(response.department);
                        $('#role').val(response.role);
                        $('#email').val(response.email);
                        
                        // Get the selected class ID
                        var id = response.user_id;
                    },
                    error: function(xhr, status, error) {
                    // Handle the error here
                    console.log(error);
                    }
                });
            }
          </script>
          <script src="{{ asset('import/assets/vendors/apexcharts/apexcharts.min.js') }}"></script>
          <script src="{{ asset('import/assets/vendors/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
          <script src="{{ asset('import/assets/vendors/chart.js/chart.umd.js') }}"></script>
          <script src="{{ asset('import/assets/vendors/echarts/echarts.min.js') }}"></script>
          <script src="{{ asset('import/assets/vendors/quill/quill.min.js') }}"></script>
          <script src="{{ asset('import/assets/vendors/simple-datatables/simple-datatables.js') }}"></script>
          <script src="{{ asset('import/assets/vendors/tinymce/tinymce.min.js') }}"></script>
          <script src="{{ asset('import/assets/vendors/php-email-form/validate.js') }}"></script>
          <script src="{{ asset('import/assets/js/mains.js') }}"></script>
    </x-app-layout>

</body>
</html>