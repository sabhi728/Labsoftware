<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ env('WEBSITE_NAME', '') }}</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/order_details.css') }}">

    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
</head>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Merriweather&display=swap');
.label{
    width:100px;
    margin-top: 10x;
}
.merriweather-regular {
  font-family: "Merriweather", serif;
  font-weight: 100;
  font-size:14px;
  font-style: normal;
}
table td{
    font-size:14px;
}
table td span{
    font-size:8px;
}
table th{
    font-size:14px;
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
            <div class="card" style="padding:25px;border-bottom: 30px solid red;">
                <div class="container">
                    <div class="row">
                        <div class="col-6">
                            <img src="https://www.google.com/url?sa=i&url=https%3A%2F%2Fwww.vecteezy.com%2Fvector-art%2F8930155-letter-m-vector-logo-design-with-star-shape&psig=AOvVaw3LuxY7jlZ2U0zWAUwkjh55&ust=1709314007984000&source=images&cd=vfe&opi=89978449&ved=0CBMQjRxqFwoTCJiHpYSJ0YQDFQAAAAAdAAAAABAE">
                        </div>
                        <div class="col-6">
                            <img src="https://www.google.com/url?sa=i&url=https%3A%2F%2Fwww.vecteezy.com%2Fvector-art%2F8930155-letter-m-vector-logo-design-with-star-shape&psig=AOvVaw3LuxY7jlZ2U0zWAUwkjh55&ust=1709314007984000&source=images&cd=vfe&opi=89978449&ved=0CBMQjRxqFwoTCJiHpYSJ0YQDFQAAAAAdAAAAABAE">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                           <div><label class="label">Name</label><strong>: Aman Raj</strong></div>
                           <div><label class="label">Age</label><strong>: Aman Raj</strong></div>
                           <div><label class="label">Refered BY</label><strong>: Aman Raj</strong></div>
                        </div>
                        <div class="col-6">
                            <div><label class="label">Name</label><strong>: Aman Raj</strong></div>
                            <div><label class="label">Age</label><strong>: Aman Raj</strong></div>
                            <div><label class="label">Refered BY</label><strong>: Aman Raj</strong></div>
                        </div>
                    </div>
                    <hr style="border-bottom:1px solid;">
                    <div class="row merriweather-regular" style="margin-bottom:20px;">
                        <div class="text-center" style="font-size:16px;"><b>HEMATOLOGY</b></div>
                        <div class="text-center" style="font-size:14px;"><b>COMPLETE BLOOD PICTURE ( CBP )</b></div>
                    </div>
                    <div class="row merriweather-regular">
                        <div class="col-12">
                            <table style="width:100%">
                                <thead>
                                  <tr>
                                    <th>Test Description</th>
                                    <th>RESULT</th>
                                    <th>UNITS</th>
                                    <th>Reference Range</th>
                                  </tr>
                                </thead>
                                <tbody>
                                  <tr>
                                    <td>HAEMOGLOBIN<br><span>(Method : Cell Counter)</span></td>
                                    <td>4.5</td>
                                    <td>Millions/cu.mm</td>
                                    <td>4.5 - 6.</td>
                                  </tr>
                                  <tr>
                                    <td>HAEMOGLOBIN<br><span>(Method : Cell Counter)</span></td>
                                    <td>4.5</td>
                                    <td>Millions/cu.mm</td>
                                    <td>4.5 - 6.</td>
                                  </tr>
                                  <tr>
                                    <td>HAEMOGLOBIN<br><span>(Method : Cell Counter)</span></td>
                                    <td>4.5</td>
                                    <td>Millions/cu.mm</td>
                                    <td>4.5 - 6.</td>
                                  </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="row merriweather-regular">
                        <div style="font-weight:bold;">ERIPHERAL SMEAR</div>
                        <div class="col-4">
                            <div>RBCs</div>
                            <div>WBCs</div>
                        </div>
                        <div class="col-8">
                            <div>NORMOCYTIC / NORMOCHROMIC</div>
                            <div>WITHIN NORMAL LIMITS</div>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="text-center" style="font-weight:bold;">M.STAR MEDICAL DIAGNOSTICS PVT.LTD.</div>
                        <div class="text-center">A/181, Ram Nagar Rd, opp. Community Hall, P C Colony, Kankarbagh, Patna, Bihar 800020</div>
                        <div class="text-center">M.STAR MEDICAL DIAGNOSTICS PVT.LTD.</div>
                    </div>
                </div>
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
</body>
</html>
