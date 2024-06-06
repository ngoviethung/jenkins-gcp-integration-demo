@php(
    $user = backpack_user()
)

@if($user->hasRole('Admin'))
<!-- This file is used to store sidebar items, starting with Backpack\Base 0.9.0 -->
<li class='nav-item'><a class='nav-link' href="{{ backpack_url('dashboard') }}"><i class="fa fa-dashboard"></i>
        <span>{{ trans('backpack::base.dashboard') }}</span></a></li>
{{--<li class='nav-item'><a class='nav-link' href="{{ backpack_url('elfinder') }}"><i class="fa fa-files-o"></i>--}}
{{--        <span>{{ trans('backpack::crud.file_manager') }}</span></a></li>--}}

<li class='nav-item'><a class='nav-link' href='{{ backpack_url('style') }}'><i class='nav-icon fa fa-newspaper-o'></i> <span>Styles</span></a></li>


<li class="nav-item nav-dropdown">
    <a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon fa fa-newspaper-o"></i> Topics</a>
    <ul class="nav-dropdown-items">
        <li class='nav-item'><a class='nav-link' href='{{ backpack_url('topic') }}'><i class='fa fa-industry'></i> <span>Topics</span></a></li>
        <li class='nav-item'><a class='nav-link' href='{{ backpack_url('groupleveltopic') }}'><i class='fa fa-leaf'></i> GroupLevelTopics</a></li>
    </ul>
</li>

{{--<li class="nav-title">Items</li>--}}
<li class="nav-item nav-dropdown">
    <a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon fa fa-newspaper-o"></i> Items</a>
    <ul class="nav-dropdown-items">
        <li class='nav-item'><a class='nav-link' href='{{ backpack_url('item') }}'><i class='fa fa-industry'></i> <span>Normal</span></a></li>
        <li class='nav-item'><a class='nav-link' href='{{ backpack_url('item?type_code=hair') }}'><i class='fa fa-industry'></i> <span>Hair</span></a></li>
        <li class='nav-item'><a class='nav-link' href='{{ backpack_url('item?type_code=makeup') }}'><i class='fa fa-industry'></i> <span>Makeup</span></a></li>
        <li class='nav-item'><a class='nav-link' href='{{ backpack_url('grouplevelitem') }}'><i class='fa fa-leaf'></i> <span>Group Level Item</span></a></li>
    </ul>
</li>

{{--<li class="nav-title">First-Party Packages</li>--}}
{{--<li class="nav-item nav-dropdown">--}}
{{--<a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon fa fa-newspaper-o"></i> News</a>--}}
{{--<ul class="nav-dropdown-items">--}}
{{--<li class="nav-item"><a class="nav-link" href="{{ backpack_url('article') }}"><i class="nav-icon fa fa-newspaper-o"></i> <span>Articles</span></a></li>--}}
{{--<li class="nav-item"><a class="nav-link" href="{{ backpack_url('category') }}"><i class="nav-icon fa fa-list"></i> <span>Categories</span></a></li>--}}
{{--<li class="nav-item"><a class="nav-link" href="{{ backpack_url('tag') }}"><i class="nav-icon fa fa-tag"></i> <span>Tags</span></a></li>--}}
{{--</ul>--}}
{{--</li>--}}

<li class="nav-item nav-dropdown">
    <a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon fa fa-newspaper-o"></i> Types</a>
    <ul class="nav-dropdown-items">
        <li class='nav-item'><a class='nav-link' href='{{ backpack_url('type') }}'><i class='fa fa-tumblr'></i> <span>Types</span></a></li>
        <li class='nav-item'><a class='nav-link' href='{{ backpack_url('groupleveltype') }}'><i class='fa fa-leaf'></i> <span>Group Level Type</span></a></li>
    </ul>
</li>

{{--<li class="nav-item nav-dropdown">--}}
{{--    <a class='nav-link' href="#">--}}
{{--        <i class="fa fa-flickr"></i>--}}
{{--        <span>Tasks</span>--}}
{{--    </a>--}}
{{--    <ul class="nav-dropdown-items">--}}
{{--        <li class='nav-item'><a class='nav-link' href='{{ backpack_url('task') }}'><i class='fa fa-tasks'></i> <span>Tasks</span></a></li>--}}
{{--        <li class='nav-item'><a class='nav-link' href='{{ backpack_url('taskcategory') }}'><i class='fa fa-leaf'></i> <span>Task Categories</span></a></li>--}}
{{--        <li class='nav-item'><a class='nav-link' href='{{ backpack_url('groupleveltask') }}'><i class='fa fa-leaf'></i> <span>Group Level Task</span></a></li>--}}
{{--        <li class='nav-item'><a class='nav-link' href='{{ backpack_url('language') }}'><i class='fa fa-language'></i> <span>Languages</span></a></li>--}}
{{--    </ul>--}}
{{--</li>--}}

<li class="nav-item nav-dropdown">
    <a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon fa fa-newspaper-o"></i> Tasks</a>
    <ul class="nav-dropdown-items">
                <li class='nav-item'><a class='nav-link' href='{{ backpack_url('task') }}'><i class='fa fa-tasks'></i> <span>Tasks</span></a></li>
                <li class='nav-item'><a class='nav-link' href='{{ backpack_url('taskcategory') }}'><i class='fa fa-leaf'></i> <span>Task Categories</span></a></li>
                <li class='nav-item'><a class='nav-link' href='{{ backpack_url('groupleveltask') }}'><i class='fa fa-leaf'></i> <span>Group Level Task</span></a></li>
                <li class='nav-item'><a class='nav-link' href='{{ backpack_url('language') }}'><i class='fa fa-language'></i> <span>Languages</span></a></li>
    </ul>
</li>

<li class='nav-item'><a class='nav-link' href='{{ backpack_url('event') }}'><i class='nav-icon fa fa-question'></i> Events</a></li>
@endif


@if($user->hasRole('Tester|ItemEditor'))
    <li class="nav-item nav-dropdown">
        <a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon fa fa-newspaper-o"></i> Items</a>
        <ul class="nav-dropdown-items">
            <li class='nav-item'><a class='nav-link' href='{{ backpack_url('item') }}'><i class='fa fa-industry'></i> <span>Items</span></a></li>
        </ul>
    </li>
@endif
{{--@if($user->hasanyrole('Admin|ItemEditor'))--}}
{{--<li class="nav-item nav-dropdown">--}}
{{--    <a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon fa fa-newspaper-o"></i> Outfit</a>--}}
{{--    <ul class="nav-dropdown-items">--}}
{{--        <li class='nav-item'><a class='nav-link' href='{{ backpack_url('topic/outfit') }}'><i class='fa fa-industry'></i> Statistic</a></li>--}}
{{--        <li class='nav-item'><a class='nav-link' href='{{ backpack_url('outfit') }}'><i class='nav-icon fa fa-anchor'></i>Outfits</a></li>--}}
{{--    </ul>--}}
{{--</li>--}}
{{--@endhasanyrole--}}
@if($user->hasanyrole('Admin|Stylist|ItemEditor'))
    <li class='nav-item'><a class='nav-link' href='{{ backpack_url('template') }}'><i class='nav-icon fa fa-star'></i> Templates</a></li>
@endhasanyrole

@if($user->hasanyrole('Admin'))
    <li class='nav-item'><a class='nav-link' href='{{ backpack_url('model') }}'><i class='nav-icon fa fa-question'></i> Models</a></li>
@endhasanyrole
@if($user->hasRole('Admin'))
    <li class='nav-item'><a class='nav-link' href='{{ backpack_url('eloconvert') }}'><i class='nav-icon fa fa-question'></i> EloConverts</a></li>
    <li class='nav-item'><a class='nav-link' href='{{ backpack_url('download-export') }}'><i class='nav-icon fa fa-download'></i>Files Export</a></li>


@endif


@if($user->hasanyrole('ItemEditor'))
    <li class='nav-item'><a class='nav-link' href='{{ backpack_url('task') }}'><i class='fa fa-tasks'></i> <span>Tasks</span></a></li>
    <li class='nav-item'><a class='nav-link' href='{{ backpack_url('event') }}'><i class='nav-icon fa fa-question'></i> Events</a></li>
@endhasanyrole

<li class='nav-item'><a class='nav-link' href='{{ backpack_url('skin') }}'><i class='nav-icon fa fa-question'></i> Skins</a></li>
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('position') }}'><i class='nav-icon fa fa-question'></i> Positions</a></li>

@if($user->hasanyrole('Admin'))
    <li class="nav-item nav-dropdown">
        <a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon fa fa-newspaper-o"></i> Develop</a>
        <ul class="nav-dropdown-items">
            <li class='nav-item'><a class='nav-link' href='{{ backpack_url('challenges') }}'><i class='nav-icon fa fa-star'></i>Challenges</a></li>
            <li class='nav-item'><a class='nav-link' href='{{ backpack_url('members') }}'><i class='nav-icon fa fa-user'></i>Members</a></li>
            <li class='nav-item'><a class='nav-link' href='{{ backpack_url('iap') }}'><i class='nav-icon fa fa-user'></i>Iap</a></li>
            <li class='nav-item'><a class='nav-link' href='{{ backpack_url('level') }}'><i class='nav-icon fa fa-user'></i>Level</a></li>
            <li class='nav-item'><a class='nav-link' href='{{ backpack_url('config') }}'><i class='nav-icon fa fa-user'></i>Config</a></li>
            <li class='nav-item'><a class='nav-link' href='{{ backpack_url('shop') }}'><i class='nav-icon fa fa-user'></i>Shop</a></li>
            <li class='nav-item'><a class='nav-link' href='{{ backpack_url('voting-reward') }}'><i class='nav-icon fa fa-user'></i>Voting Reward</a></li>
        </ul>
    </li>

@endhasanyrole
