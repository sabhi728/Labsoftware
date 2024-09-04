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

@php
    $systemSettings = DB::select('SELECT * FROM system_settings WHERE 1');
    $systemSettings = reset($systemSettings)
@endphp

<div class="printableBill" id="printableBill" style="font-family: 'Helvetica';">
    <script src="{{ asset('js/libs/qrcode.js') }}"></script>

    <style>
        .page-break {
            page-break-before: always;
        }
    </style>

    @if(!empty($user->settings['result_header']))
        <img id="headerImage" class="headerImage" style="margin-bottom: 15px; display: none;" height="101px" width="100%" src="{{ asset($user->settings['result_header']) }}">
    @endif

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
                            <td width="30%"><img src="data:image/png;base64,{{ \DNS1D::getBarcodePNG($orderDetails->bill_no, 'C39+', 1, 24) }}"></td>
                        </tr>
                        <tr class="patientrowHeight" style="z-index: 10;position: relative;" height="20px">
                            <td width="13%">Name</td>
                            <td width="2%">:</td>
                            <td width="40%"><b style="font-weight: bold;text-transform: uppercase;">{{ $orderDetails->patient_title_name }} {{ $orderDetails->patient_name }}</b></td>
                            <td width="19%">Bill Number</td>
                            <td width="2%">:</td>
                            <td width="30%"><b> {{ $orderDetails->bill_no }}</b></td>
                        </tr>
                        <tr class="patientrowHeight" height="20px">
                            <td width="13%">Age/Gender</td>
                            <td width="2%">:</td>
                            <td width="40%"><b>{{ str_replace(' ', '', strtoupper($orderDetails->patient_age . $orderDetails->patient_age_type . '/' . $orderDetails->patient_gender)) }}</b></td>
                            <td width="19%">Bill Date</td>
                            <td width="2%">:</td>
                            <td width="30%">{{ $orderDetails->bill_formatted_date }}</td>
                        </tr>
                        @if (!empty($orderDetails->sample_type))
                            <tr class="patientrowHeight" height="20px">
                                <td width="13%">Sample Type</td>
                                <td width="2%">:</td>
                                <td width="40%"><b>{!! $orderDetails->sample_type !!}</b></td>
                                <td width="19%">Sample Collection</td>
                                <td width="2%">:</td>
                                <td width="30%">{{ $orderDetails->sample_collection_date }}</td>
                            </tr>
                        @endif
                        <tr class="patientrowHeight" height="20px">
                            <td width="13%">Reff By</td>
                            <td width="2%">:</td>
                            <td width="40%"><b>{{ $orderDetails->doc_name }}</b></td>
                            @if($systemSettings->sample_time_in_reports == "true" && !empty($orderDetails->sample_type))
                                <td width="19%">Sample Received</td>
                                <td width="2%">:</td>
                                <td width="30%"><label id="patName" class="ReportingDate">{{ $orderDetails->sample_received_date }}</label></td>
                            @else
                                <td width="19%">Reporting Date</td>
                                <td width="2%">:</td>
                                <td width="30%"><label id="patName" class="ReportingDate">{{ $orderDetails->reporting_date }}</label></td>
                            @endif
                        </tr>
                        <tr class="patientrowHeight" height="20px">
                            <td width="13%">TypedBy</td>
                            <td width="2%">:</td>
                            <td width="40%"><label id="patName" class="TypedBy">{{ $orderDetails->typed_by }}</label></td>
                            @if($systemSettings->sample_time_in_reports == "true" && !empty($orderDetails->sample_type))
                                <td width="19%">Reporting Date</td>
                                <td width="2%">:</td>
                                <td width="30%"><label id="patName" class="ReportingDate">{{ $orderDetails->reporting_date }}</label></td>
                            @endif
                        </tr>
                        <tr class="patientrowHeight" style="border-bottom: 1px solid #000000;">
                            <td width="13%">&nbsp;</td>
                            <td width="2%"></td>
                            <td width="40%"></td>
                            <td width="19%"></td>
                            <td width="2%"></td>
                            <td width="30%"></td>
                        </tr>
                        <tr class="patientrowHeight" style="height: 6px;">
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
            @if(empty($orderDetails->result_page_1) && empty($orderDetails->result_page_2) && empty($orderDetails->result_page_3))
                @if (!empty($orderDetails->order_department_name))
                    <tr>
                        <td colspan="6" style="background: #cccccc; padding: 6px; text-align: center; font-size: 13px;font-weight:bold;border: 1px solid black;">
                            <b>{{ $orderDetails->order_department_name }}</b>
                        </td>
                    </tr>

                    <tr>
                        <td colspan="6" style="text-align: center; padding-top: 10px; padding-bottom: 20px; font-size: 13px;">
                            <b><u>
                                @if (empty($orderDetails->order_display_name))
                                    {{ $orderDetails->order_name }}
                                @else
                                    {{ $orderDetails->order_display_name }}
                                @endif
                            </u></b>
                        </td>
                    </tr>
                @else
                    <tr>
                        <td colspan="6" style="background: #cccccc; padding: 6px; text-align: center; font-size: 13px;font-weight:bold;border: 1px solid black;">
                            <b>
                                @if (empty($orderDetails->order_display_name))
                                    {{ $orderDetails->order_name }}
                                @else
                                    {{ $orderDetails->order_display_name }}
                                @endif
                            </b>
                        </td>
                    </tr>

                    <tr>
                        <td colspan="6" style="padding-bottom: 20px;"></td>
                    </tr>
                @endif

                @php
                    $randomVarName = rand(1111111111, 9999999999);
                    $variables = [];
                    $variables["hideUnits_$randomVarName"] = true;
                    $variables["hideRange_$randomVarName"] = true;
                @endphp

                @foreach($orderDetails->report_items_data as $reportItem)
                    @if(!empty($reportItem['results']))
                        @php
                            if(!empty($reportItem['units'])) {
                                $variables["hideUnits_$randomVarName"] = false;
                            }
                            if (!empty($reportItem['order_details_range'])) {
                                $variables["hideRange_$randomVarName"] = false;
                            }
                        @endphp
                    @endif
                @endforeach

                <tr class="resultRowHeight_{{ $randomVarName }}" style="font-size: 10pt;font-weight:bold;">
                    <td style="width: 35%;"><u>INVESTIGATION</u></td>
                    <td style="width: 5%;"></td>
                    <td style="@if ($variables["hideUnits_$randomVarName"] || $variables["hideRange_$randomVarName"]) width: 30%; @endif"><u>RESULT</u></td>
                    <td style="width: 5%;"></td>
                    <td style="text-align: left; @if ($variables["hideUnits_$randomVarName"]) display: none; @endif"><u>UNITS</u></td>
                    <td style="text-align: left; @if ($variables["hideRange_$randomVarName"]) display: none; @endif"><u>NORMAL RANGE</u></td>
                    <td colspan="1"></td>
                </tr>

                <tr style="font-size: 9pt; height: 1mm;">
                    <td colspan="7"></td>
                </tr>

                @foreach($orderDetails->report_items_data as $reportItem)
                    @if(!empty($reportItem['results']))
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
                                        
                                <span style="position: absolute;@if($reportItem['abnormal'] == "on")font-weight:bold;@endif" id="1">{{ $reportItem['results'] }}</span>
                            </td>
                            <td></td>
                            <td style="vertical-align: top; @if ($variables["hideUnits_$randomVarName"]) display: none; @endif">{{ $reportItem['units'] }}</td>
                            <td style="vertical-align: top;white-space:pre; @if ($variables["hideRange_$randomVarName"]) display: none; @endif">{{ $reportItem['order_details_range'] }}</td>
                            <td colspan="1"></td>
                        </tr>
                    @endif
                @endforeach
            @else
                <tr>
                    <td colspan="6" style="display: flex; flex-direction: column;">
                        @php
                            $resultPage1 = str_replace('<table>', '<table class="table table-bordered">', $orderDetails->result_page_1);
                            $resultPage2 = str_replace('<table>', '<table class="table table-bordered">', $orderDetails->result_page_2);
                            $resultPage3 = str_replace('<table>', '<table class="table table-bordered">', $orderDetails->result_page_3);
                        @endphp

                        @if (!empty(strip_tags($resultPage1)))
                            <div>{!! $resultPage1 !!}</div>
                        @endif

                        @if (!empty(strip_tags($resultPage2)))
                            <div class="page-break">{!! $resultPage2 !!}</div>
                        @endif

                        @if (!empty(strip_tags($resultPage3)))
                            <div class="page-break">{!! $resultPage3 !!}</div>
                        @endif
                    </td>
                </tr>
            @endif

            @if(!empty($orderDetails->reporting_method))
                <tr>
                    <td colspan="6" id="OrderMethodPrint0" style="font-size: 7pt;">
                        <b>Method:</b>
                        <span class="OrderMethod" style="font-style: italic;">{{ $orderDetails->reporting_method }}</span>
                    </td>
                </tr>
            @endif

            @if(!empty($orderDetails->reporting_notes))
                <tr>
                    <td colspan="6">
                        <div style="white-space: normal; text-align: left; border: 1px solid black; padding: 3px; font-size: 9pt;">{!! str_replace('<table>', '<table class="table table-bordered">', $orderDetails->reporting_notes) !!}</div>
                    </td>
                </tr>
            @endif

            <tr>
                <td colspan="6" style="white-space: normal; text-align: left; padding: 3px; font-size: 9pt;font-weight:bold;">
                    @if(!empty($orderDetails->reporting_advice))
                        {{ $orderDetails->reporting_advice }}
                    @else
                        Sugessted Clinical Correlation If necesarry Kindly Discuss.
                    @endif
                </td>
            </tr>

            <tr>
                <td colspan="6" style="padding-top: 20px;" align="center">
                    <span style="font-size: 11px; text-align: center;font-weight:bold;">------------End of the Report------------</span>
                </td>
            </tr>

            <tr>
                <td colspan="6">
                    <table width="100%">
                        <tr>
                            <td width="33.33%" align="center" style="vertical-align: bottom;">
                                @if(!empty($orderDetails->left_signature_image))
                                    <img style="height: 40px;" src="{{ asset($orderDetails->left_signature_image) }}" >
                                @else
                                    <div style="height: 40px;"></div>
                                @endif
                                <br>
                                <b style="font-size:13px;">{!! nl2br(e($orderDetails->left_signature_label)) !!}</b>
                            </td>
                            <td width="33.33%" align="center">
                                @php
                                    $orderNoFinal = isset($orderNo) ? $orderNo : $orderDetails->report_id;
                                    $qrCodeUrl = url('/') . '/viewbill/' . $orderDetails->bill_no . '?orders=' . $orderNoFinal;
                                @endphp
                                <div>{{ \QrCode::size(60)->generate("$qrCodeUrl") }}</div>
                            </td>
                            <td width="33.33%" align="center" style="vertical-align: bottom;">
                                @if(!empty($orderDetails->signature_image))
                                    <img style="height: 40px;" src="{{ asset($orderDetails->signature_image) }}" >
                                @else
                                    <div style="height: 40px;"></div>
                                @endif
                                <br>
                                <b style="font-size:13px;">{!! nl2br(e($orderDetails->signature_label)) !!}</b>
                            </td>
                        </tr>
                    </table>
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
