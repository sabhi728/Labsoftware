@php
    $webUrl = url('/') . '/';
@endphp

<div class="webSidebar">
    <ul>
        @if ($user->show_dashboard == "true")
            <li class="{{ request()->is('referralpanel/home') ? 'li_active' : '' }}">
                <a href="{{ $webUrl . 'referralpanel/home' }}"><i class='bx bxs-dashboard'></i><span>Dashboard</span></a>
            </li>
        @endif
        <li class="{{ request()->is('referralpanel/orderentry/index') ? 'li_active' : '' }}">
            <a href="{{ $webUrl . 'referralpanel/orderentry/index' }}"><i class='bx bx bxs-message-square-add'></i><span>Order Entry</span></a>
        </li>
        <li class="{{ request()->is('referralpanel/sample-status') ? 'li_active' : '' }}">
            <a href="{{ $webUrl . 'referralpanel/sample-status' }}"><i class='bx bx bxs-package'></i><span>Sample Status</span></a>
        </li>
        <li class="{{ request()->is('referralpanel/submitted-sample') ? 'li_active' : '' }}">
            <a href="{{ $webUrl . 'referralpanel/submitted-sample' }}"><i class='bx bx bxs-package'></i><span>Submitted Samples</span></a>
        </li>
        @if ($user->show_bill_reports == "true")
            <li class="{{ request()->is('referralpanel/reports/bill-reports/index') ? 'li_active' : '' }}">
                <a href="{{ $webUrl . 'referralpanel/reports/bill-reports/index' }}"><i class='bx bxs-report'></i><span>Bill Reports</span></a>
            </li>
        @endif
        <li class="{{ request()->is('referralpanel/contact-us') ? 'li_active' : '' }}">
            <a href="{{ $webUrl . 'referralpanel/contact-us' }}"><i class='bx bxs-phone'></i><span>Contact Us</span></a>
        </li>
    </ul>
</div>
