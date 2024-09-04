@php
    $sidebarMenuItems = DB::table('admin_menu_options')->orderBy('position', 'asc')->get();

    $userData = DB::table('users')
        ->join('admin_roles', 'admin_roles.id', '=', 'users.role')
        ->where('users.id', auth()->user()->id)
        ->select('admin_roles.access')
        ->first();
    $availableAccess = explode(',', $userData->access);

    $webUrl = url('/') . '/';

    $whichChild = "";
@endphp
<div class="webSidebar">
    @if(empty($userData->access))
        <ul>
            @php $whichChild = null; @endphp
            @foreach ($sidebarMenuItems as $menu)
                @if($menu->is_visible == "false")
                    @php continue; @endphp
                @endif

                @if(empty($menu->is_child))
                    <li class="{{ request()->is($menu->url) ? 'li_active' : '' }}">
                        <a href="{{ $webUrl . $menu->url }}"><i class='bx {{ $menu->icon }}'></i><span>{{ $menu->name }}</span></a>
                    </li>
                @else
                    @if($whichChild != $menu->is_child)
                        @php $whichChild = $menu->is_child; @endphp
                        @if ($loop->first)
                            <li class="subMenuOpenBtn" onclick="openCloseMenu('{{ $menu->is_child }}Menu', '{{ $menu->is_child }}ArrowIcon')">
                                <a><i id="{{ $menu->is_child }}ArrowIcon" class='bx bxs-down-arrow'></i><span>{{ $menu->is_child }}</span></a>
                            </li>
                            <ul class="submenu" id="{{ $menu->is_child }}Menu">
                        @else
                            </ul>
                            <li class="subMenuOpenBtn" onclick="openCloseMenu('{{ $menu->is_child }}Menu', '{{ $menu->is_child }}ArrowIcon')">
                                <a><i id="{{ $menu->is_child }}ArrowIcon" class='bx bxs-down-arrow'></i><span>{{ $menu->is_child }}</span></a>
                            </li>
                            <ul class="submenu" id="{{ $menu->is_child }}Menu">
                        @endif
                        <li class="{{ request()->is($menu->url) ? 'li_active' : '' }}">
                            <a href="{{ $webUrl . $menu->url }}"><span>{{ $menu->name }}</span></a>
                        </li>
                    @else
                        <li class="{{ request()->is($menu->url) ? 'li_active' : '' }}">
                            <a href="{{ $webUrl . $menu->url }}"><span>{{ $menu->name }}</span></a>
                        </li>
                    @endif
                @endif
            @endforeach
        </ul>
    @else
        <ul>
            @php $whichChild = null; @endphp
            @foreach ($sidebarMenuItems as $menu)
                @if(!in_array($menu->id, $availableAccess) || $menu->is_visible == "false")
                    @php continue; @endphp
                @endif

                @if(empty($menu->is_child))
                    <li class="{{ request()->is($menu->url) ? 'li_active' : '' }}">
                        <a href="{{ $webUrl . $menu->url }}"><i class='bx {{ $menu->icon }}'></i><span>{{ $menu->name }}</span></a>
                    </li>
                @else
                    @if($whichChild != $menu->is_child)
                        @php $whichChild = $menu->is_child; @endphp
                        @if ($loop->first)
                            <li class="subMenuOpenBtn" onclick="openCloseMenu('{{ $menu->is_child }}Menu', '{{ $menu->is_child }}ArrowIcon')">
                                <a><i id="{{ $menu->is_child }}ArrowIcon" class='bx bxs-down-arrow'></i><span>{{ $menu->is_child }}</span></a>
                            </li>
                            <ul class="submenu" id="{{ $menu->is_child }}Menu">
                        @else
                            </ul>
                            <li class="subMenuOpenBtn" onclick="openCloseMenu('{{ $menu->is_child }}Menu', '{{ $menu->is_child }}ArrowIcon')">
                                <a><i id="{{ $menu->is_child }}ArrowIcon" class='bx bxs-down-arrow'></i><span>{{ $menu->is_child }}</span></a>
                            </li>
                            <ul class="submenu" id="{{ $menu->is_child }}Menu">
                        @endif
                        <li class="{{ request()->is($menu->url) ? 'li_active' : '' }}">
                            <a href="{{ $webUrl . $menu->url }}"><span>{{ $menu->name }}</span></a>
                        </li>
                    @else
                        <li class="{{ request()->is($menu->url) ? 'li_active' : '' }}">
                            <a href="{{ $webUrl . $menu->url }}"><span>{{ $menu->name }}</span></a>
                        </li>
                    @endif
                @endif
            @endforeach
        </ul>
    @endif
</div>
<script>
    function openCloseMenu(id, arrowIconId) {
        var subMenu = document.getElementById(id);
        var arrowIcon = document.getElementById(arrowIconId);

        if (subMenu.style.display != "" && subMenu.style.display != "none") {
            subMenu.style.display = "none";
            arrowIcon.style.transform = "rotate(-90deg)";
        } else {
            subMenu.style.display = "block";
            arrowIcon.style.transform = "rotate(0deg)";
        }
    }
</script>
