<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ env('WEBSITE_NAME', '') }}</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/add_order.css') }}">

    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
</head>
<body>
    <div class="header">
        @include('include.header')
    </div>
    <div class="main_container">
        <div class="sidebar">
            @include('include.sidebar')
        </div>
        <div class="main">
            <div class="actions">
                <button type="button" id="clearButton" class="btn btn-primary">Clear</button>
                <button type="button" onclick="window.history.back()" class="btn btn-primary">Cancel</button>
            </div>
            @if(isset($users))
                <form id="formBack" action="{{ url('labuser/update', ['id' => $users->id]) }}" method="post">
            @else
                <form id="formBack" action="{{ url('labuser/add') }}" method="post">
            @endif
                @csrf
                <div class="inputBack">
                    <label>First Name:</label>
                    @if(isset($users))
                        <input name="firstName" type="text" value="{{ $users->first_name }}" required>
                    @else
                        <input name="firstName" type="text" required>
                    @endif
                    <asterisk>*</asterisk>
                </div>
                <div class="inputBack">
                    <label>Last Name:</label>
                    @if(isset($users))
                        <input name="lastName" type="text" value="{{ $users->last_name }}" required>
                    @else
                        <input name="lastName" type="text" required>
                    @endif
                    <asterisk>*</asterisk>
                </div>
                <div class="inputBack">
                    <label>Username:</label>
                    @if(isset($users))
                        <input name="username" type="text" value="{{ $users->username }}" required>
                    @else
                        <input name="username" type="text" required>
                    @endif
                    <asterisk>*</asterisk>
                </div>
                <div class="inputBack">
                    <label>Email:</label>
                    @if(isset($users))
                        <input name="email" type="text" value="{{ $users->email }}" required>
                    @else
                        <input name="email" type="text" required>
                    @endif
                    <asterisk>*</asterisk>
                </div>
                @if(!isset($users))
                    <div class="inputBack">
                        <label>Password:</label>
                        <input name="password" type="text" required>
                        <asterisk>*</asterisk>
                    </div>
                @endif
                <div class="inputBack">
                    <label>Role:</label>
                    @if(isset($users))
                        <select name="role" autocomplete="off" required>
                            <option value="">NONE</option>
                            @foreach($labRoles as $labRole)
                                @if($users->role == $labRole->id)
                                    <option value="{{ $labRole->id }}" selected>{{ $labRole->name }}</option>
                                @else
                                    <option value="{{ $labRole->id }}">{{ $labRole->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    @else
                        <select name="role" required>
                            <option value="">None</option>
                            @foreach($labRoles as $labRole)
                                <option value="{{ $labRole->id }}">{{ $labRole->name }}</option>
                            @endforeach
                        </select>
                    @endif
                    <asterisk>*</asterisk>
                </div>
                <div class="inputBack">
                    <label>Location:</label>
                    @if(isset($users))
                        <select name="labLocation" autocomplete="off">
                            <option value="">NONE</option>
                            @foreach($labLocations as $labLocation)
                                @if($users->lab_location == $labLocation->id)
                                    <option value="{{ $labLocation->id }}" selected>{{ $labLocation->location_name }}</option>
                                @else
                                    <option value="{{ $labLocation->id }}">{{ $labLocation->location_name }}</option>
                                @endif
                            @endforeach
                        </select>
                    @else
                        <select name="labLocation">
                            <option value="">None</option>
                            @foreach($labLocations as $labLocation)
                                <option value="{{ $labLocation->id }}">{{ $labLocation->location_name }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
                <div class="inputBack">
                    <label>Department:<br><p style="font-size: 12px;">(By setting NONE user will have access to all department reports)</p></label>
                    @if(isset($users))
                        <select name="departmentAccess" autocomplete="off">
                            <option value="">NONE</option>
                            @foreach($departments as $department)
                                @if($users->department_access == $department->depart_id)
                                    <option value="{{ $department->depart_id }}" selected>{{ $department->department_name }}</option>
                                @else
                                    <option value="{{ $department->depart_id }}">{{ $department->department_name }}</option>
                                @endif
                            @endforeach
                        </select>
                    @else
                        <select name="departmentAccess">
                            <option value="">None</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->depart_id }}">{{ $department->department_name }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
                <div class="inputBack">
                    <label>Status:</label>
                    @if(isset($users))
                        <select name="status" required>
                            @if($users->status == "active")
                                <option value="active" selected>Active</option>
                            @else
                                <option value="active">Active</option>
                            @endif
                            @if($users->status == "in-active")
                                <option value="in-active" selected>In-Active</option>
                            @else
                                <option value="in-active">In-Active</option>
                            @endif
                        </select>
                    @else
                        <select name="status" required>
                            <option value="active">Active</option>
                            <option value="in-active">In-Active</option>
                        </select>
                    @endif
                    <asterisk>*</asterisk>
                </div>
                <div class="action_top">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
    <!-- <div class="footer">
        @include('include.footer')
    </div> -->

    <script src="{{ asset('js/main.js') }}"></script>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>
</body>
<script>
    const clearButton = document.getElementById('clearButton');
    const inputElements = document.querySelectorAll('#formBack input, #formBack select, #formBack textarea');

    clearButton.addEventListener('click', function() {
        inputElements.forEach(element => {
            if (element.type === 'text' || element.type === 'textarea') {
                element.value = '';
            } else if (element.type === 'checkbox') {
                element.checked = false;
            } else if (element.tagName === 'SELECT') {
                element.selectedIndex = 0;
            }
        });
    });
</script>
</html>
