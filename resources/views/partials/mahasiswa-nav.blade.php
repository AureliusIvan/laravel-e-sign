<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <li class="nav-item ">
            <div>
                <form action="{{ route('logout') }}" method="post">
                    @csrf
                    <input type="submit" value="Log Out" class="btn btn-link btn-sm custom-link">
                </form>
            </div>
        </li>
    </ul>
</nav>

<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="#" class="brand-link">
        <span class="brand-text font-weight-light">Skripsi</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        @if (auth()->user()->role === 'mahasiswa')
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="info">
                <a href="#" class="d-block">{{ auth()->user()->mahasiswaDetail->nama }}</a>
            </div>
        </div>
        <!-- SidebarSearch Form -->
        <div class="form-inline mt-3">
            <div class="input-group" data-widget="sidebar-search">
                <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
                <div class="input-group-append">
                    <button class="btn btn-sidebar">
                        <i class="fas fa-search fa-fw"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-item">
                    <a href="/mahasiswa/dashboard" class="nav-link {{ ($title === 'Dashboard') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>
                            Dashboard
                        </p>
                    </a>
                </li>

                <!-- Pembimbing Saya -->
                <li class="nav-item">
                    <a href="{{ route('pembimbing.saya') }}" class="nav-link {{ ($title === 'Pembimbing') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-user"></i>
                        <p>
                            Pembimbing Saya
                        </p>
                    </a>
                </li>

                <!-- Skripsi -->
                <li class="nav-item">
                    <a href="{{ route('proposal.skripsi.pengumpulan') }}"
                        class="nav-link {{ ($title === 'Skripsi') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-file"></i>
                        <p>
                            Skripsi
                        </p>
                    </a>
                </li>

                <!-- Hasil Skripsi -->
                <li class="nav-item">
                    <a href="{{ route('proposal.skripsi.hasil') }}"
                        class="nav-link {{ ($title === 'Hasil Skripsi') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-file"></i>
                        <p>
                            Hasil Skripsi
                        </p>
                    </a>
                </li>
            </ul>
        </nav>
        @endif
        <!-- /.sidebar-menu -->
    </div>
    <!--
 /.sideb
ar -->




</aside>