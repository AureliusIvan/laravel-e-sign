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
        @if (auth()->user()->role === 'dosen')
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="info">
                <a href="#" class="d-block">{{ auth()->user()->dosenDetail->nama }}</a>
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
                    <a href="/dosen/dashboard" class="nav-link {{ ($title === 'Dashboard') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>
                            Dashboard
                        </p>
                    </a>
                </li>
                
                <!-- Topik Penelitian Saya -->
                <li class="nav-item">
                    <a href="{{ route('topik.penelitian.saya') }}"
                        class="nav-link  {{ ($title === 'Topik Penelitian Saya') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-book"></i>
                        <p>Topik Penelitian Saya</p>
                    </a>
                </li>

                <!-- Mahasiswa -->
                <li class="nav-item {{ $title === 'Mahasiswa' ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ ($title === 'Mahasiswa') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-users"></i>
                        <p>
                            Mahasiswa
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('permintaan.mahasiswa') }}"
                                class="nav-link {{ (str_contains($subtitle, 'Permintaan Menjadi Pembimbing')) ? 'active' : '' }}">
                                <i class="nav-icon far fa-circle"></i>
                                <p>Permintaan Menjadi Pembimbing</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('list.mahasiswa.bimbingan') }}"
                                class="nav-link {{ (str_contains($subtitle, 'List Mahasiswa Bimbingan')) ? 'active' : '' }}">
                                <i class="nav-icon far fa-circle"></i>
                                <p>List Mahasiswa Bimbingan</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('proposal.rti.periksa') }}"
                                class="nav-link {{ (str_contains($subtitle, 'Approve Proposal RTI')) ? 'active' : '' }}">
                                <i class="nav-icon far fa-circle"></i>
                                <p>Approve Proposal RTI</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Periksa Proposal -->
                <li class="nav-item">
                    <a href="{{ route('proposal.skripsi.periksa') }}"
                        class="nav-link {{ ($title === 'Periksa Proposal') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-file"></i>
                        <p>
                            Periksa Proposal
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