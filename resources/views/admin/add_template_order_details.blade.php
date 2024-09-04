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
                <button type="button" onclick="goToRoute('template_order_details/{{ $orderDetails->report_id }}/{{ $templateId }}')" class="btn btn-primary">Cancel</button>
            </div>
            @if(isset($orderInputData))
                <form id="formBack" action="{{ url('update_template_order_details', ['order_id' => $orderDetails->report_id, 'template_id' => $templateId, 'update_id' => $orderInputData->id]) }}" method="post">
            @else
                <form id="formBack" action="{{ url('add_template_order_details', ['order_id' => $orderDetails->report_id, 'template_id' => $templateId]) }}" method="post">
            @endif
                @csrf
                @if(isset($orderInputData))
                    <h1 style="padding-bottom: 20px;font-size: 18px;color:var(--lightdarkgray);">Update Order Details For <u><b>{{ $orderDetails->order_name }}</b></u></h1>   
                @else
                    <h1 style="padding-bottom: 20px;font-size: 18px;color:var(--lightdarkgray);">Add Order Details For <u><b>{{ $orderDetails->order_name }}</b></u></h1>
                @endif
                <div class="inputBack">
                    <label>Sub Heading:</label>
                    @if(isset($orderInputData))
                        <input name="subHeading" type="text" placeholder="Sub Heading" value="{{ $orderInputData->sub_heading }}">
                    @else
                        <input name="subHeading" type="text" placeholder="Sub Heading">
                    @endif
                </div>
                <div class="inputBack">
                    <label>Component Name:</label>
                    @if(isset($orderInputData))
                        <input name="componentName" type="text" placeholder="Component Name" value="{{ $orderInputData->component_name }}" required>
                    @else
                        <input name="componentName" type="text" placeholder="Component Name" required>
                    @endif
                    <asterisk>*</asterisk>
                </div>
                <div class="inputBack">
                    <label>Machine Code:</label>
                    @if(isset($orderInputData))
                        <input name="machineCode" type="text" placeholder="Machine Code" value="{{ $orderInputData->machine_code }}">
                    @else
                        <input name="machineCode" type="text" placeholder="Machine Code"> 
                    @endif
                </div>
                <div class="inputBack">
                    <label>Specimen Code:</label>
                    @if(isset($orderInputData))
                        <input name="specimenCode" type="text" placeholder="Specimen Code" value="{{ $orderInputData->specimen_code }}">
                    @else
                        <input name="specimenCode" type="text" placeholder="Specimen Code">    
                    @endif
                </div>
                <div class="inputBack">
                    <label>Range:</label>
                    @if(isset($orderInputData))
                        <textarea name="range" placeholder="Range">{{ $orderInputData->order_details_range }}</textarea>
                    @else
                        <textarea name="range" placeholder="Range"></textarea>
                    @endif
                </div>
                <div class="inputBack">
                    <label>From Range:</label>
                    @if(isset($orderInputData))
                        <textarea name="fromRange" placeholder="From Range">{{ $orderInputData->from_range }}</textarea>
                    @else
                        <textarea name="fromRange" placeholder="From Range"></textarea>    
                    @endif
                </div>
                <div class="inputBack">
                    <label>To Range:</label>
                    @if(isset($orderInputData))
                        <textarea name="toRange" placeholder="To Range">{{ $orderInputData->to_range }}</textarea>
                    @else
                        <textarea name="toRange" placeholder="To Range"></textarea>   
                    @endif
                </div>
                <div class="inputBack">
                    <label>Units:</label>
                    @if(isset($orderInputData))
                        <input name="units" type="text" placeholder="Units" value="{{ $orderInputData->units }}">
                    @else
                        <input name="units" type="text" placeholder="Units">      
                    @endif
                </div>
                <div class="inputBack">
                    <label>Method:</label>
                    @if(isset($orderInputData))
                        <input name="method" type="text" placeholder="Method" value="{{ $orderInputData->method }}">
                    @else
                        <input name="method" type="text" placeholder="Method">  
                    @endif
                </div>
                <div class="inputBack">
                    <label>Default Value:</label>
                    @if(isset($orderInputData))
                        <textarea name="defaultValue" placeholder="Default Value">{{ $orderInputData->default_value }}</textarea>
                    @else
                        <textarea name="defaultValue" placeholder="Default Value"></textarea>       
                    @endif
                </div>
                <div class="inputBack">
                    <label>Calculations:</label>
                    @if(isset($orderInputData))
                        <textarea name="calculations" placeholder="Calculations">{{ $orderInputData->calculations }}</textarea>
                    @else
                        <textarea name="calculations" placeholder="Calculations"></textarea>
                    @endif
                </div>
                <div class="inputBack">
                    <label>Check to Inactive:</label>
                    @if(isset($orderInputData) && $orderInputData->status == "In Active")
                        <input name="checkToInactive" type="checkbox" checked>
                    @else
                        <input name="checkToInactive" type="checkbox">
                    @endif
                </div>
                <button id="saveOrderBtn" type="submit" class="btn btn-primary">Save Details</button>
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
    let table = new DataTable('#ordersTable');

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