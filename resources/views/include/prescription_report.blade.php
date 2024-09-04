<link rel="stylesheet" href="{{ asset('css/print_prescription.css') }}">

@php
    $systemSettings = DB::select('SELECT * FROM system_settings WHERE 1');
    $systemSettings = reset($systemSettings)
@endphp

<div class="printableBill" id="printableBill">
    @if (isset($user->settings['prescription_background_image']))
        <img class="backgroundImage" src="{{ asset($user->settings['prescription_background_image']) }}">
    @endif

    <div style="font-family: sans-serif; position: relative;font-size: 9pt;padding-left: 10mm;padding-right: 10mm;">
        <table id="contentDiv" style="font-size:inherit; font-family: inherit;">
            <thead>
                <tr>
                    <td colspan="6" style="height: 160px !important;"></td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="6">
                        <table width="100%" style="font-size:inherit; font-family: inherit;border-collapse: collapse;">
                            <tr class="patientrowHeight" style="z-index: 10;position: relative;">
                                <td width="19%"><b>Patient Name</b></td>
                                <td width="2%"><b>:</b></td>
                                <td width="34%" style="text-transform: uppercase;">{{ $orderDetails->patient_title_name }} {{ $orderDetails->patient_name }}</td>
                                <td width="19%"><b>UMR Number</b></td>
                                <td width="2%"><b>:</b></td>
                                <td width="30%">{{ $orderDetails->umr_number }}</td>
                            </tr>
                            <tr class="patientrowHeight">
                                <td width="19%"><b>Age/Gender</b></td>
                                <td width="2%"><b>:</b></td>
                                <td width="34%">{{ str_replace(' ', '', strtoupper($orderDetails->patient_age . $orderDetails->patient_age_type . '/' . $orderDetails->patient_gender)) }}</td>
                                <td width="19%"><b>Bill Number</b></td>
                                <td width="2%"><b>:</b></td>
                                <td width="30%">{{ $orderDetails->bill_no }}</td>
                            </tr>
                            <tr class="patientrowHeight">
                                <td width="19%"><b>Address</b></td>
                                <td width="2%"><b>:</b></td>
                                <td width="34%">{!! $orderDetails->patient_address !!}</td>
                                <td width="19%"><b>Reg Date</b></td>
                                <td width="2%"><b>:</b></td>
                                <td width="30%">{{ Str::substr($orderDetails->bill_formatted_date, 0, 11) }}</td>
                            </tr>
                            <tr class="patientrowHeight">
                                <td width="19%"><b>Consultant</b></td>
                                <td width="2%"><b>:</b></td>
                                <td width="34%">{{ $orderDetails->orderData['order_name'] }}</td>
                                <td width="19%"><b>Reg Time</b></td>
                                <td width="2%"><b>:</b></td>
                                <td width="30%">{{ Str::substr($orderDetails->bill_formatted_date, 11) }}</td>
                            </tr>
                            <tr class="patientrowHeight">
                                <td width="19%"><b>Mobile Number</b></td>
                                <td width="2%"><b>:</b></td>
                                <td width="34%">{{ $orderDetails->patient_phone }}</td>
                                <td width="19%"><b>Bill Generator</b></td>
                                <td width="2%"><b>:</b></td>
                                <td width="30%">{{ $orderDetails->user_first_name }} {{ $orderDetails->user_last_name }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr style="height: 10px;"><td colspan="6"></td></tr>

                <tr>
                    <td width="20%"><b>Weight :</b><span id="prescriptionWeightTxt"></span></td>
                    <td width="16%"><b>Height :</b><span id="prescriptionHeightTxt"></span></td>
                    <td width="16%"><b>BP :</b><span id="prescriptionBPTxt"></span></td>
                    <td width="16%"><b>Temp :</b><span id="prescriptionTempTxt"></span></td>
                    <td width="16%"><b>Spo2 :</b><span id="prescriptionSpo2Txt"></span></td>
                    <td width="16%"><b>PR :</b><span id="prescriptionPRTxt"></span></td>
                </tr>

                <tr style="height: 10px;"><td style="border-bottom: 1px solid black;" colspan="6"></td></tr>

                <tr>
                    <td colspan="6" style="padding-top: 10px">
                        <span><b><u>Patient Past History :</u></b></span>
                        <div id="patientPastHistoryDiv" style="min-height: 120px;width=100%;margin-top:10px;"></div>
                    </td>
                </tr>

                <tr>
                    <td colspan="6">
                        <span><b><u>Clinical Notes / Investigations / Treatment :</u></b></span>
                        <div id="clinicalNotesDiv" style="width=100%;margin-top:10px;"></div>
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6" style="height: 220px !important;">
                    </td>
                </tr>
            </tfoot>
        </table>

        <div class="footer-item" style="display: flex;flex-direction: column;justify-content: center;align-items: center;width: calc(100% - 10mm);">
            <span style="font-size: 12px; text-align: center;padding-bottom:6px;"><b>This OP is valid for only 5 days</b></span>
            <img src="data:image/png;base64,{{ \DNS1D::getBarcodePNG($orderDetails->bill_no, 'C39+', 1.6, 25) }}">
        </div>
    </div>
</div>

<script>
    function printPrescriptionReport() {
        var divElements = document.getElementById("printableBill").outerHTML;

        var printWindow = window.open('', '_blank');
        printWindow.document.write('<html><head><title></title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous"><link rel="stylesheet" href="{{ asset("css/print_prescription.css") }}"></head><body>');
        printWindow.document.write(divElements);
        printWindow.document.write('</body></html>');
        printWindow.document.close();

        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 1000);
    }
</script>
