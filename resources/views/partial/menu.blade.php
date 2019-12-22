<!-- Add icons to the links using the .nav-icon class
                         with font-awesome or any other icon font library -->
<li class="nav-item">
    <router-link to="/home" class="nav-link">
        <i class="nav-icon fas fa-tachometer-alt"></i>
        <p>
            Dashboard
            <span class="right badge badge-danger">New</span>
        </p>
    </router-link>
</li>

@can('isAdmin')
    <li class="nav-item has-treeview">
        <a href="#" class="nav-link">
            <i class="nav-icon fas fa-cog"></i>
            <p>
                Management
                <i class="right fas fa-angle-left"></i>
            </p>
        </a>

        <ul class="nav nav-treeview">

            <li class="nav-item">
                <router-link to="/users" class="nav-link">
                    <i class="fas fa-users nav-icon"></i>
                    <p>Users </p>
                </router-link>
            </li>
            <li class="nav-item">
                <router-link to="/courses" class="nav-link">
                    <i class="fas fa-book-open nav-icon"></i>
                    <p>Courses </p>
                </router-link>
            </li>
            <li class="nav-item">
                <router-link to="/tests" class="nav-link">
                    <i class="fas fa-question nav-icon"></i>
                    <p>Tests </p>
                </router-link>
            </li>
        </ul>
    </li>
@endcan

<li class="nav-item">
    <router-link to="/profile" class="nav-link">
        <i class="nav-icon fas fa-user"></i>
        <p>
            Profile
        </p>
    </router-link>
</li>

<li class="nav-item">
    <a class="nav-link" href="{{ route('logout') }}"
       onclick="event.preventDefault();
                           document.getElementById('logout-form').submit();">
        <i class="nav-icon fas fa-power-off"></i>
        <p>
            Logout
        </p>
    </a>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>
</li>
