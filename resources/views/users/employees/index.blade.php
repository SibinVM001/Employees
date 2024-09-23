@extends('layouts.app')

@section('content')
    <div class="p-5">
        <div class="row mb-2">
            <div class="col-6">
                <h4>Employees</h4>
            </div>
            <div class="col-6 d-flex justify-content-end">
                <a href="{{ route('employees.create') }}" class="btn btn-primary float-right">
                    Add New
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover" id="employeesTable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Contact No</th>
                        <th>Hobby</th>
                        <th>Category</th>
                        <th>Profile Pic</th>
                        <th>Edit</th>
                    </tr>
                </thead>
            </table>    
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            var employeesTable = $('#employeesTable').DataTable({
                paging: true,
                serverSide: true,
                processing: true,
                ordering: false,
                dom:"<'row mb-3'<'col-md-6'i><'col-md-6'f>>" + "t" + "<'row mt-3'<'col-md-6'l><'col-md-6'p>>",
                ajax: {
                    url: "{{ route('employees.index') }}",
                    type: 'GET',
                    data: function (d) {
                        d._token = $("input[name=_token]").val();
                    },
                    beforeSend: function () {
                        
                    },
                },
                columns: [
                    { "data": "name" },
                    { "data": "contact_no"},
                    { "data": "hobby"},
                    { "data": "category"},
                    {
                        data: null,
                        render: function(data, type, row) {
                            var imagePath = 'images/default-user.png'
                            
                            if (data.profile_pic) {
                                imagePath = 'storage/' + data.profile_pic;
                            }

                            return `
                                <img src="{{ asset('${imagePath}') }}" alt="" height="100%" width="100%" style="width: 80px !important;">
                            `;
                            
                        }
                    },
                    {
                        data: null,
                        render: function(data, type, row) {
                            var editUrl = "{{ route('employees.edit', ':id') }}";
                            editUrl = editUrl.replace(':id', data.id);

                            return `
                                <div class="d-inline-flex">
                                    <div style="align-content: center;">
                                        <a href="${editUrl}" class="btn btn-secondary">Edit</a>
                                    </div>
                                    <div style="align-content: center;">
                                        <form method="POST" action="/employees/${data.id}" class="delete-form mb-0" onsubmit="event.preventDefault(); deleteCategory(this);">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger delete-btn ms-3">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            `;
                        }
                    }
                ]
            });
        });
    </script>
@endpush