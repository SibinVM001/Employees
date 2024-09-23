@extends('layouts.app')

@push('styles')
    <style>
        .error-text {
            color: red;
        }
    </style>
@endpush

@section('content')
    <div class="p-5 col-6 mx-auto">
        <div class="row mb-2">
            <div class="col-6">
                <h4>Add New Employee</h4>
            </div>
            <div class="col-6">
            </div>
        </div>
        <div>
            <form action="{{ route('employees.store') }}" method="POST" id="addEmployeeForm" enctype="multipart/form-data">
                @csrf

                <div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control madatory mb-2" id="name" aria-describedby="emailHelp" name="name">
                        <span class="error-text" id="nameError"></span>
                    </div>
                    <div class="mb-3">
                        <label for="contactNo" class="form-label">Contact No</label>
                        <input type="text" class="form-control madatory mb-2" id="contactNo" name="contact_no" maxlength="16">
                        <span class="error-text" id="contactNoError"></span>
                    </div>
                    <div class="mb-3">
                        <div>
                            <label for="hobby" class="form-label">Hobby</label>
                        </div>

                        <div class="d-inline-flex gap-3">
                            @foreach ($hobbies as $hobby)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="{{ $hobby->id }}" id="hobby_{{ $hobby->id }}" name="hobbies[]">
                                    <label class="form-check-label" for="hobby_{{ $hobby->id }}">
                                        {{ $hobby->title }}
                                    </label>    
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-select madatory mb-2" aria-label="Default select example" name="category" id="category">
                            <option selected value="">Select Category</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->title }}</option>
                            @endforeach
                        </select>
                        <span class="error-text" id="categoryError"></span>
                    </div>
                    <div class="mb-3">
                        <label for="profilePicFile" class="form-label">Profile Pic</label>

                        <div class="mb-3" id="imagePreview">
                            <img src="" alt="" srcset="" id="profilePic" width="100px">
                        </div>

                        <input class="form-control mt-3" type="file" id="profilePicFile" name="profile_pic">
                    </div>
                </div>
                <div class="d-flex justify-content-end">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">Save</button>
                        <a href="{{ route('employees.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#imagePreview').hide();

            $('.madatory').on('input', function() {
                var errorField = $(this).attr('name').split('_')
                                        .map(function(word, index) {
                                            return (index == 0) ? word : word.charAt(0).toUpperCase() + word.slice(1);
                                        }).join('');

                $(`#${errorField}Error`).text('');
            });

            $('#contactNo').on('input', function() {
                var elem = $(this);

                $(`#contactNoError`).text('');

                if (elem.data('lastKey') >= '0' && elem.data('lastKey') <= '9') {
                    if (elem.data('lastKey') !== 'Backspace') {
                        if (elem.val().length == 1) {
                            elem.val('+' + elem.val());
                        }

                        if (elem.val().length % 4 == 0) {
                            elem.val(elem.val() + ' ');
                        }
                    }
                } else {
                    if (elem.data('lastKey') !== 'Backspace') {
                        elem.val(elem.val().slice(0, -1));

                        $(`#contactNoError`).text('Please enter numeric values');
                    }
                }
            }).on('keydown', function(event) {
                elem = $(this);
                elem.data('lastKey', event.key);
            });

            $('#profilePicFile').on('change', function() {
                var files = $(this)[0].files;

                if (files.length > 0) {
                    var file = files[0];
                    var reader = new FileReader();

                    reader.onload = (function(file) {
                        return function(e) {
                            $('#profilePic').attr('src', e.target.result);
                        }
                    })(file);

                    reader.readAsDataURL(file);
                    $('#imagePreview').show();
                }
            });
        }); 

        $('#addEmployeeForm').on('submit', function(e) {
            e.preventDefault();

            var validatedCount = 0;
            var totalCount = 0;
            var form = $(this);
            var formData = new FormData(form[0]);

            $.each($('.madatory'), function(key, elem) {
                if ($(this).val() == '') {
                    var errorField = $(this).attr('name').split('_')
                                        .map(function(word, index) {
                                        return (index == 0) ? word : word.charAt(0).toUpperCase() + word.slice(1);
                                        }).join('');

                    var field = $(this).attr('name').split('_')
                                        .map(function(word, index) {
                                        return word.charAt(0).toUpperCase() + word.slice(1);
                                        }).join(' ');

                    $(`#${errorField}Error`).text(`${field} field is required`);
                } else {
                    validatedCount++;
                }

                totalCount++;
            });

            if (totalCount == validatedCount) {
                var contactNo = formData.get('contact_no').slice(1).split(' ').join('');
                formData.set('contact_no', contactNo);

                $.ajax({
                    url: "{{ route('employees.store') }}",
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.success);
                            
                            setTimeout(() => {
                                location.href = "{{ route('employees.index') }}";
                            }, 1000);
                        } else {
                            $.each(response.errors, function(key, value) {
                                var errorField = key.split('_')
                                                    .map(function(word, index) {
                                                        return (index == 0) ? word : word.charAt(0).toUpperCase() + word.slice(1);
                                                    }).join('');

                                var field = key.split('_')
                                                    .map(function(word, index) {
                                                    return word.charAt(0).toUpperCase() + word.slice(1);
                                                    }).join(' ');
                                
                                
                                $(`#${errorField}Error`).text(value.join(', '));
                            });
                        }
                    }
                });
            }
        });
    </script>
@endpush