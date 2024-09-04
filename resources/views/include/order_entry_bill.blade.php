<link rel="stylesheet" type="text/css" href="https://printjs-4de6.kxcdn.com/print.min.css">
<div id="printableBill" style="width:1000px;">
    <div style="width: auto;border: 1px solid black;background: white;font-family: 'Times New Roman';margin: 20px;">
        <div class="billHeader" style="display: flex;flex-direction: column;justify-content: center;align-items: center;padding: 6px;">
            @if ($isOrderOnlyConsulting && !empty($user->settings['header_consulting_bill']))
                <img style="margin-bottom: 15px" height="101px" width="100%" src="{{ asset($user->settings['header_consulting_bill']) }}">
            @else
                @if(!empty($user->settings['bill_header']))
                    <img style="margin-bottom: 15px" height="101px" width="100%" src="{{ asset($user->settings['bill_header']) }}">
                @endif
            @endif
            <span style="font-weight: bold;text-align: center;">{{ !empty($user->settings['lab_name_on_bill']) ? $user->settings['lab_name_on_bill'] : $user->settings['lab_name'] }}</span>
            <span style="font-size: 9pt;margin-top: 10px;width: 80%;text-align: center;">{{ !empty($user->settings['address']) ? $user->settings['address'] : "" }}</span>
        </div>
        <div class="billDetails" style="display: flex;flex-direction: column;align-items: center;justify-content: center;border-top: 1px solid black;border-bottom: 1px solid black;padding: 6px;">
            <span style="font-weight: bold;font-size: 13pt;text-align: center;">BILL / RECEIPT</span>
            <table width="100%" align="center" style="margin-top: 1px; padding-bottom: 5px; font-size: 9pt;">
                <tbody>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td><svg id="barcode"></svg></td>
                    </tr>
                    <tr>
                        <td width="5%"></td>
                        <td width="10%">Patient Name</td>
                        <td width="2%">:</td>
                        <td width="37%" style="text-transform: uppercase;">{{ $orderDetails->patient_title_name }} {{ $orderDetails->patient_name }}</td>
                        <td width="12%">Bill Number</td>
                        <td width="2%">:</td>
                        <td width="27%">{{ $orderDetails->bill_no }}</td>
                    </tr>
                    <tr>
                        <td width="5%"></td>
                        <td width="10%">Age/Gender</td>
                        <td width="2%">:</td>
                        <td width="37%">{{ $orderDetails->patient_age }} {{ $orderDetails->patient_age_type }} / {{ $orderDetails->patient_gender }}</td>
                        <td width="12%">Bill Date</td>
                        <td width="2%">:</td>
                        <td width="27%">{{ $createdAtFormatted }}</td>
                    </tr>
                    <tr>
                        <td width="5%"></td>
                        <td width="10%">Referred By</td>
                        <td width="2%">:</td>
                        <td width="37%">{{ $orderDetails->referred_by }}</td>
                        <td width="12%">UMR</td>
                        <td width="2%">:</td>
                        <td width="27%">{{ $orderDetails->umr_number }}</td>
                    </tr>
                    <tr>
                        <td width="5%"></td>
                        <td width="10%">Doctor</td>
                        <td width="2%">:</td>
                        <td width="37%">{{ $orderDetails->doc_name }}</td>
                        <td width="12%">Cell</td>
                        <td width="2%">:</td>
                        <td width="27%">{{ $orderDetails->patient_phone }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="billItems">
            <table width="100%" style="font-size: 9pt;">
                <tbody>
                    <tr style="border-bottom: 1px solid black;">
                        <td width="5.8%"></td>
                        <td width="10%"><b>S.No</b></td>
                        <td style="width: 40%;"><b>Out Patient</b></td>
                        <td><b>Sample Type</b></td>
                        <td><b>Amount</b></td>
                    </tr>

                    @php $count = 0; @endphp
                    @foreach($orderDetails->orderData as $orderItems)
                        @php $count++; @endphp
                        <tr>
                            <td></td>
                            <td>{{ $count }}</td>
                            <td>{{ $orderItems['order_name'] }}</td>
                            <td>{!! $orderItems['sample_type'] !!}</td>
                            <td>{{ $orderItems['custom_order_amount'] }}</td>
                        </tr>
                    @endforeach
                    <tr style="border-top: 1px solid black;">
                        <td></td>
                        <td></td>
                        <td></td>
                        <td style="display: block;text-align:end;margin-right:20px;">Total Bill:</td>
                        <td>{{ str_replace('.00', '', $orderDetails->total_bill) }}</td>
                    </tr>
                    @if ($orderDetails->discount != '0')
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td style="display: block;text-align:end;margin-right:20px;">Discount:</td>
                            <td>{{ $orderDetails->discount }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td style="display: block;text-align:end;margin-right:20px;">Paid Amount:</td>
                        <td>{{ $orderDetails->paid_amount }}</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td style="display: block;text-align:end;margin-right:20px;">Balance:</td>
                        <td>{{ $orderDetails->balance }}</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td style="display: block;text-align:end;margin-right:20px;">Pay mode:</td>
                        <td>{{ $payMode }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="billFooter" style="padding: 10px;display: flex;flex-direction: row;justify-content: space-between;margin-top: 15px;font-size: 9pt;position: relative;">
            <div style="display: flex;flex-direction: column;">
                <span id="ResultVerifiedDoctor" style="white-space: pre;">{{ !empty($user->settings['bill_footer']) ? $user->settings['bill_footer'] : "" }}</span>
                @if (!empty($user->name) && (empty($user->first_name)))
                    <span>Print By: {{ $user->name }}</span>
                @else
                    <span>Print By: {{ $user->first_name }} {{ $user->last_name }}</span>
                @endif
            </div>
            <img  id="billStampImage" style="position: absolute;left: 50%;bottom: 0;transform: translate(-50%,-20%);visibility: hidden;" height="100px" src="{{ asset($user->settings['bill_stamp']) }}">
            <div style="display: flex;flex-direction: column;">
                <span>For M STAR MEDICAL DIAGNOSTICS PVT.LTD</span>
                <img id="billSignatureImage" style="display: inline-block;align-self: end;visibility: hidden;" height="35px" width="70px" src="{{ asset($user->settings['bill_signature']) }}">
                <span style="display: inline-block;text-align: end;padding-right: 10px;">Cashier</span>
            </div>
        </div>
    </div>
</div>

@php
    $patientTitleName = strtolower($orderDetails->patient_title_name);
    $patientName = strtolower($orderDetails->patient_name);
    $billNo = $orderDetails->bill_no;

    $fileName = "{$patientTitleName}_{$patientName}_{$billNo}";
    $fileName = str_replace(' ', '_', $fileName) . '.pdf';
@endphp

<script src="https://cdnjs.cloudflare.com/ajax/libs/jsbarcode/3.11.5/JsBarcode.all.min.js" integrity="sha512-QEAheCz+x/VkKtxeGoDq6nsGyzTx/0LMINTgQjqZ0h3+NjP+bCsPYz3hn0HnBkGmkIFSr7QcEZT+KyEM7lbLPQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://printjs-4de6.kxcdn.com/print.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    JsBarcode("#barcode", "{{ $orderDetails->bill_no }}", {
        width: 1.4,
        height: 20,
        displayValue: false
    });
    document.getElementById('barcode').style.marginLeft = "-8px";

    function sendBillOnWhatsapp() {
        var billStampImage = document.getElementById('billStampImage');
        var billSignatureImage = document.getElementById('billSignatureImage');

        billStampImage.style.visibility = "visible";
        billSignatureImage.style.visibility = "visible";

        var divElements = document.getElementById('printableBill').innerHTML;
        divElements = divElements.trim().replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '');

        var html =
            "<html lang=\"en\">" +
            "<head>" +
                "<meta charset=\"UTF-8\">" +
                "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">" +
                "<title></title>" +
            "</head>" +
            "<body>" +
                divElements +
            "</body>" +
            "</html>";

        billStampImage.style.visibility = "hidden";
        billSignatureImage.style.visibility = "hidden";

        var options = {
            margin: 10,
            filename: "{{ $fileName }}",
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2 },
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
        };

        html2pdf().from(html).set(options).outputPdf('arraybuffer').then(function (pdf) {
            var blob = new Blob([pdf], { type: 'application/pdf' });

            var formData = new FormData();
            formData.append('pdf', blob, "{{ $fileName }}");

            var csrfToken = "{{ csrf_token() }}";
            formData.append("_token", csrfToken);
            formData.append("phone", "{{ $orderDetails->patient_phone }}");

            var xhr = new XMLHttpRequest();
            xhr.open('POST', webUrl + 'orderbills/send_bill_whatsapp', true);
            xhr.onload = function () {
                if (xhr.status === 200) {
                    // var response = JSON.parse(xhr.responseText);
                    // window.open(response.fileUrl, '_blank');

                    Swal.fire({
                        title: `Result sent on Whatsapp successfully`
                    });
                } else {
                    Swal.showValidationMessage('Failed to send results on whatsapp');
                }
            };
            xhr.send(formData);
        });
    }

    function printDiv() {
        var divElements = document.getElementById("printableBill").innerHTML;
        var oldPage = document.body.innerHTML;
        document.body.innerHTML =
          "<html><head><title></title></head><body>" +
          divElements + "</body>";

        var styleElement = document.createElement("style");
        // styleElement.innerHTML = "body {display: flex;height: 100%;justify-content: center;}";
        document.head.appendChild(styleElement);

        window.print();
        document.body.innerHTML = oldPage;

        // let confirmation = confirm("Do you want to print again?");
        // if (!confirmation)
        goToRoute('orderentry/index');
    }
</script>
