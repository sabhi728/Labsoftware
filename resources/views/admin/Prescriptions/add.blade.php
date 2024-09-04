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
<style>
    .inputWriteable {
        border-radius: 5px;
        padding: 7.5px 10px;
        font-size: 14px;
        font-family: var(--font1);
        border: 1px solid var(--lightdarkgray);
    }
</style>
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
            @if(isset($prescription))
                <form id="formBack" action="{{ url('prescriptions/update', ['id' => $prescription->id]) }}" method="post">
            @else
                <form id="formBack" action="{{ url('prescriptions/add') }}" method="post">
            @endif
                @csrf
                <div class="inputBack">
                    <label>Name:</label>
                    @if(isset($prescription))
                        <input name="name" type="text" value="{{ $prescription->name }}" required>
                    @else
                        <input name="name" type="text" required>
                    @endif
                </div>
                <div class="inputBack">
                    <label>Patient Age:</label>
                    @if(isset($prescription))
                        <input name="age" type="text" value="{{ $prescription->age }}" required>
                    @else
                        <input name="age" type="text" required>
                    @endif
                </div>
                <div class="inputBack">
                    <label>Content:</label>
                    @if(isset($prescription))
                        <textarea id="content" name="content">{{ $prescription->content }}</textarea>
                    @else
                        <textarea id="content" name="content"></textarea>
                    @endif
                </div>
                <div class="inputBack">
                    <label>Patient Name:</label>
                    @if(isset($prescription))
                        <input name="patientName" type="text" value="{{ $prescription->patient_name }}" required>
                    @else
                        <input name="patientName" type="text" required>
                    @endif
                </div>
                <div class="inputBack">
                    <label>Patient Phone:</label>
                    @if(isset($prescription))
                        <input name="patientPhone" type="text" value="{{ $prescription->patient_phone }}">
                    @else
                        <input name="patientPhone" type="text">
                    @endif
                </div>
                <div class="action_top" style="top:5px;">
                    <button id="saveOrderBtn" type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
            @if (isset($prescriptionAttachments))
                <div id="formBack">
                    <form style="display: flex;flex-direction: row;justify-content: space-evenly;align-items: center;" action="{{ url('prescriptions/add_result_attachments', ['prescription_id' => $prescription->id]) }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="attachment" class="inputWriteable" required>
                        <input type="text" name="fileName" class="inputWriteable" placeholder="File name" required>
                        <button type="submit" class="btn btn-primary">Add</button>
                    </form>
                    <hr>
                    <table class="table" id="ordersTable">
                        <thead>
                            <tr>
                            <th scope="col">File Name</th>
                            <th scope="col">Date</th>
                            <th scope="col"></th>
                            <th scope="col"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($prescriptionAttachments as $attachment)
                                <tr>
                                    <input type="hidden" name="attachmentId" value="{{ $attachment['id'] }}">
                                    <td>{{ $attachment['file_name'] }}</td>
                                    <td>{{ $attachment['created_at'] }}</td>
                                    <td><button type="submit" class="btn btn-primary" onclick="openNewTab(`{{ $attachment['file_path'] }}`)">View</button></td>
                                    <td><button type="submit" class="btn btn-primary" onclick="goToRoute(`prescriptions/delete_result_attachments/{{ $attachment['id'] }}`)">Delete</button></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
    <!-- <div class="footer">
        @include('include.footer')
    </div> -->

    <script src="{{ asset('js/main.js') }}"></script>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.css" integrity="sha512-ZbehZMIlGA8CTIOtdE+M81uj3mrcgyrh6ZFeG33A4FHECakGrOsTPlPQ8ijjLkxgImrdmSVUHn1j+ApjodYZow==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.js" integrity="sha512-lVkQNgKabKsM1DA/qbhJRFQU8TuwkLF2vSN3iU/c7+iayKs08Y8GXqfFxxTZr1IcpMovXnf2N/ZZoMgmZep1YQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
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

    $(document).ready(function() {
        $('#content').summernote(commonSummernoteOptions);
    });
</script>
</html>
