<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ env('WEBSITE_NAME', '') }}</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/order_maintenance.css') }}">

    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
</head>
<style>
    .horizontal {
        display: flex;
        flex-direction: row;
        align-items: center;
        width: 100%;
        justify-content: space-between;
    }

    .vertical {
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .vertical2 {
        display: flex;
        flex-direction: column;
        justify-content: center;
        width: 100%;
    }

    .inputForm span {
        width: 100%;
    }

    .inputReadonly {
        background: var(--lightgray);
        border-radius: 5px;
        padding: 4px 10px;
        font-size: 14px;
        font-family: var(--font1);
        border: 1px solid var(--lightdarkgray);
    }

    .inputWriteable {
        border-radius: 5px;
        padding: 7.5px 10px;
        font-size: 14px;
        font-family: var(--font1);
        border: 1px solid var(--lightdarkgray);
    }

    #inputWriteableSaveBtn {
        border-bottom-left-radius: 0px;
        border-top-left-radius: 0px;
    }

    #phoneUmrInput {
        border-radius: 5px;
        border-bottom-right-radius: 0px;
        border-top-right-radius: 0px;
        padding: 6.5px 10px;
        font-size: 15px;
        font-family: var(--font1);
        border: 1px solid var(--lightdarkgray);
    }

    .inputField {
        border-radius: 5px;
        padding: 4px 10px;
        font-size: 14px;
        font-family: var(--font1);
        border: 1px solid var(--lightdarkgray);
    }

    .resultNoteButtons {
        display: flex;
        flex-direction: row;
        justify-content: center;
        align-items: center;
        margin-bottom: 20px;
    }

    #resultNotePage1Btn,
    #resultNotePage2Btn {
        background: var(--buttoncolor);
        padding: 8px 20px;
        font-size: 15px;
        border-radius: 10px;
        border: 0;
        font-family: var(--font1);
        cursor: pointer;
    }

    #resultNotePage2Btn {
        background: var(--lightgray);
        margin-left: 10px;
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
                <button type="button" class="btn btn-primary" onclick="window.history.back()">Back</button>
            </div>
            <div id="ordersTableBack">
                <div id="headerHorizonatl" class="horizontal">
                    <div class="vertical2">
                        <div class="horizontal inputForm">
                            <div class="vertical">
                                <span>Bill No:</span>
                                <input id="ageGenderInput" class="inputReadonly" value="{{ $orderDetails->bill_no }}" readonly>
                            </div>
                            <div class="vertical">
                                <span>Department:</span>
                                <input id="nameInput" class="inputReadonly" value="{{ $orderDetails['orderData']['order_department_name'] }}" readonly>
                            </div>
                            <div class="vertical">
                                <span>Order:</span>
                                <input id="nameInput" class="inputReadonly" value="{{ $orderDetails['orderData']['order_name'] }}" readonly>
                            </div>
                            <div class="vertical">
                                <span>Status:</span>
                                <input style="color:green;" id="phoneInput" class="inputReadonly" value="{{ $orderDetails->reportStatus }}" readonly>
                            </div>
                        </div>
                        <space style="height: 10px;"></space>
                        <div class="horizontal inputForm">
                            <div class="vertical">
                                <span>UMR(Card):</span>
                                <input id="nameInput" class="inputReadonly" value="{{ $orderDetails->umr_number }}" readonly>
                            </div>
                            <div class="vertical">
                                <span>Patient Name:</span>
                                <input id="ageGenderInput" class="inputReadonly" value="{{ $orderDetails->patient_title_name }} {{ $orderDetails->patient_name }}" readonly>
                            </div>
                            <div class="vertical">
                                <span>Phone Number:</span>
                                <input id="phoneInput" class="inputReadonly" value="{{ $orderDetails->patient_phone }}" readonly>
                            </div>
                            <div class="vertical">
                                <span>Gender:</span>
                                <input id="phoneInput" class="inputReadonly" value="{{ $orderDetails->patient_gender }}" readonly>
                            </div>
                        </div>
                        <space style="height: 10px;"></space>
                        <div class="horizontal inputForm">
                            <div class="vertical">
                                <span>Age:</span>
                                <input id="phoneInput" class="inputReadonly" value="{{ $orderDetails->patient_age }} {{ $orderDetails->patient_age_type }}" readonly>
                            </div>
                            <div class="vertical">
                                <span>Reff. Doctor:</span>
                                <input id="phoneInput" class="inputReadonly" value="{{ $orderDetails->doc_name }}" readonly>
                            </div>
                        </div>
                    </div>
                </div>
                <h4 style="color:red;text-align:center;margin-top:30px;">{{ $orderDetails['orderData']['order_name'] }}</h4>
                <hr>
                    <form style="display: flex;flex-direction: row;justify-content: space-evenly;align-items: center;" action="{{ url('orderbills/add_result_attachments') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="bill_no" value="{{ $orderDetails->bill_no }}">
                        <input type="hidden" name="report_id" value="{{ $orderDetails['orderData']['report_id'] }}">
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
                        @foreach($orderDetails['attachmentsList'] as $attachment)
                            <tr>
                                <input type="hidden" name="attachmentId" value="{{ $attachment['id'] }}">
                                <td>{{ $attachment['file_name'] }}</td>
                                <td>{{ $attachment['created_at'] }}</td>
                                <td><button type="submit" class="btn btn-primary" onclick="openNewTab(`{{ $attachment['file_path'] }}`)">View</button></td>
                                <td><button type="submit" class="btn btn-primary" onclick="goToRoute(`orderbills/delete_result_attachments/{{ $attachment['id'] }}`)">Delete</button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
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
</script>
</html>
