<link rel="stylesheet" type="text/css" href="https://printjs-4de6.kxcdn.com/print.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jsbarcode/3.11.5/JsBarcode.all.min.js" integrity="sha512-QEAheCz+x/VkKtxeGoDq6nsGyzTx/0LMINTgQjqZ0h3+NjP+bCsPYz3hn0HnBkGmkIFSr7QcEZT+KyEM7lbLPQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://printjs-4de6.kxcdn.com/print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/he/1.2.0/he.js" integrity="sha512-o4gKX6jcK0rdciOZ9X8COYkV9gviTJAbYEVW8aC3OgIRuaKDmcT9/OFXBVzHSSOxiTjsTktqrUvCUrHkQHSn9Q==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="{{ asset('js/main.js') }}"></script>

<style>
    #contentDiv {
        width: 100% !important;
    }
</style>

<div class="printableBill" id="printableBill" style="font-family: 'Helvetica';display:none;">
    <script src="{{ asset('js/libs/qrcode.js') }}"></script>

    <table id="contentDiv" style="font-size:9pt; font-family: Helvetica;">
        <thead>
            <tr>
                <td colspan="6">
                    <table width="100%" style="font-size:9pt; font-family: Helvetica;">
                        <tr>
                            <td colspan="6" height="120px;"></td>
                        </tr>
                        <tr class="patientrowHeight">
                            <td width="13%"></td>
                            <td width="2%"></td>
                            <td width="40%"></td>
                            <td width="19%"></td>
                            <td width="2%"></td>
                            <td width="30%"><img src="data:image/png;base64,{{ \DNS1D::getBarcodePNG("SAMPLE", 'C39+', 1, 24) }}"></td>
                        </tr>
                        <tr class="patientrowHeight" style="z-index: 10;position: relative;" height="20px">
                            <td width="13%">Name</td>
                            <td width="2%">:</td>
                            <td width="40%"><b style="font-weight: bold;text-transform: uppercase;">Patien Name</b></td>
                            <td width="19%">Bill Number</td>
                            <td width="2%">:</td>
                            <td width="30%"><b>M000</b></td>
                        </tr>
                        <tr class="patientrowHeight" height="20px">
                            <td width="13%">Age/Gender</td>
                            <td width="2%">:</td>
                            <td width="40%"><b>24YEARS/GENDER</b></td>
                            <td width="19%">Bill Date</td>
                            <td width="2%">:</td>
                            <td width="30%">21-Apr-2024 11:30 AM</td>
                        </tr>
                        <tr class="patientrowHeight" height="20px">
                            <td width="13%">Sample Type</td>
                            <td width="2%">:</td>
                            <td width="40%"><b>Sample Type</b></td>
                            <td width="19%">Sample Collection</td>
                            <td width="2%">:</td>
                            <td width="30%">21-Apr-2024 11:35 AM</td>
                        </tr>
                        <tr class="patientrowHeight" height="20px">
                            <td width="13%">Reff By</td>
                            <td width="2%">:</td>
                            <td width="40%"><b>Reff By</b></td>
                            <td width="19%">Sample Received</td>
                            <td width="2%">:</td>
                            <td width="30%"><label id="patName" class="ReportingDate">21-Apr-2024 11:40 AM</label></td>
                        </tr>
                        <tr class="patientrowHeight" height="20px">
                            <td width="13%">TypedBy</td>
                            <td width="2%">:</td>
                            <td width="40%"><label id="patName" class="TypedBy">Typed By</label></td>
                            <td width="19%">Reporting Date</td>
                            <td width="2%">:</td>
                            <td width="30%"><label id="patName" class="ReportingDate">21-Apr-2024 01:50 PM</label></td>
                        </tr>
                        <tr class="patientrowHeight" style="border-bottom: 1px solid #000000;">
                            <td width="13%">&nbsp;</td>
                            <td width="2%"></td>
                            <td width="40%"></td>
                            <td width="19%"></td>
                            <td width="2%"></td>
                            <td width="30%"></td>
                        </tr>
                        <tr class="patientrowHeight" style="height: 15px;">
                            <td width="13%"></td>
                            <td width="2%"></td>
                            <td width="40%"></td>
                            <td width="19%"></td>
                            <td width="2%"></td>
                            <td width="30%"></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="6" style="text-align: center; padding-top: 10px; padding-bottom: 20px; font-size: 13px;">
                    <b><u>{{ $reportDetails->order_name }}</u></b>
                </td>
            </tr>

            @php
                $randomVarName = rand(1111111111, 9999999999);
                $variables = [];
                $variables["hideUnits_$randomVarName"] = true;
                $variables["hideRange_$randomVarName"] = true;
            @endphp

            @foreach($orderDetails as $reportItem)
                @php
                    if(!empty($reportItem['units'])) {
                        $variables["hideUnits_$randomVarName"] = false;
                    }
                    if (!empty($reportItem['order_details_range'])) {
                        $variables["hideRange_$randomVarName"] = false;
                    }
                @endphp
            @endforeach

            <tr class="resultRowHeight_{{ $randomVarName }}" style="font-size: 10pt;font-weight:bold;">
                <td style="width: 35%;"><u>INVESTIGATION</u></td>
                <td style="width: 5%;"></td>
                <td style="@if ($variables["hideUnits_$randomVarName"] || $variables["hideRange_$randomVarName"]) width: 30%; @endif"><u>RESULT</u></td>
                <td style="text-align: left; @if ($variables["hideUnits_$randomVarName"]) display: none; @endif"><u>UNITS</u></td>
                <td style="text-align: left; @if ($variables["hideRange_$randomVarName"]) display: none; @endif"><u>NORMAL RANGE</u></td>
                <td colspan="1"></td>
            </tr>

            <tr style="font-size: 9pt; height: 1mm;">
                <td colspan="6"></td>
            </tr>

            @foreach($orderDetails as $reportItem)
                @php
                    if(!empty($reportItem['units'])) {
                        $variables["hideUnits_$randomVarName"] = false;
                    }

                    if (!empty($reportItem['order_details_range'])) {
                        $variables["hideRange_$randomVarName"] = false;
                    }
                @endphp

                @if(!empty($reportItem['sub_heading']))
                    <tr class="resultRowHeight_{{ $randomVarName }}" style="font-size: 9pt;">
                        <td colspan="6">
                            <b><u>{{ $reportItem['sub_heading'] }}</u></b>
                        </td>
                    </tr>
                @endif

                <tr class="resultRowHeight_{{ $randomVarName }}" style="font-size: 9pt;position: relative;">
                    <td style="vertical-align: top; height: 8mm;">
                        {{ $reportItem['component_name'] }}
                        @if(!empty($reportItem['method']))
                            <span>
                                <span style="font-size:7pt; display: block">
                                    <span style="display: inline-block">(Method: {{ $reportItem['method'] }})</span>&nbsp;
                                </span>
                            </span>
                        @endif
                    </td>
                    <td></td>
                    <td style="vertical-align: top;">
                        <span style="position: absolute">    --</span>
                    </td>
                    <td style="vertical-align: top; @if ($variables["hideUnits_$randomVarName"]) display: none; @endif">{{ $reportItem['units'] }}</td>
                    <td style="vertical-align: top;white-space:pre; @if ($variables["hideRange_$randomVarName"]) display: none; @endif">{{ $reportItem['order_details_range'] }}</td>
                    <td colspan="1"></td>
                </tr>
            @endforeach

            @if(!empty($reportDetails->order_method))
                <tr>
                    <td colspan="6" id="OrderMethodPrint0" style="font-size: 7pt;">
                        <b>Method:</b>
                        <span class="OrderMethod" style="font-style: italic;">{{ $reportDetails->order_method }}</span>
                    </td>
                </tr>
            @endif

            @if(!empty($reportDetails->order_result_notes_1))
                <tr>
                    <td colspan="6" style="white-space: normal; text-align: left; border: 1px solid black; padding: 3px; font-size: 9pt;">{!! str_replace('<table>', '<table class="table table-bordered">', $reportDetails->order_result_notes_1) !!}</td>
                </tr>
            @endif

            @if(!empty($reportDetails->order_advice))
                <tr>
                    <td colspan="6" style="white-space: normal; text-align: left; padding: 3px; font-size: 9pt;">{{ $reportDetails->order_advice }}</td>
                </tr>
            @endif

            <tr>
                <td colspan="6" style="padding-top: 30px;padding-bottom:20px;" align="center">
                    <span style="font-size: 11px; text-align: center;">------------End of the Report------------</span>
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6"></td>
            </tr>
        </tfoot>
    </table>
</div>

<script>
    function printDiv() {
        var divElements = document.getElementById("printableBill").innerHTML;

        var printWindow = window.open('', '_blank');
        printWindow.document.write('<html><head><title></title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous"><link rel="stylesheet" href="{{ asset("css/print.css") }}"></head><body style="font-family: \'Microsoft JhengHei\', Arial;padding-left:15mm;padding-right:15mm;">');
        printWindow.document.write(divElements);
        printWindow.document.write('</body></html>');
        printWindow.document.close();

        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 500)
    }
</script>
