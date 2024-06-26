<li class="nav-item dropdown pr-4">
    <a class="nav-link" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
        <img class="img-avatar" src="https://media2.giphy.com/media/nR4L10XlJcSeQ/200.gif" alt="{{ backpack_auth()->user()->name }}" style="width: 50px; height: 45px;">
    </a>
    <div class="dropdown-menu {{ config('backpack.base.html_direction') == 'rtl' ? 'dropdown-menu-left' : 'dropdown-menu-right' }} mr-4 pb-1 pt-1">
        <a class="dropdown-item" href="{{ route('backpack.account.info') }}"><i class="fa fa-user"></i> {{ trans('backpack::base.my_account') }}</a>
        <div class="dropdown-divider"></div>
        <a class="dropdown-item" href="{{ backpack_url('logout') }}"><i class="fa fa-lock"></i> {{ trans('backpack::base.logout') }}</a>
    </div>
</li>
