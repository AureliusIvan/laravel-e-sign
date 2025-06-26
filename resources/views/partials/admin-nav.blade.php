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
    @if (auth()->user()->role === 'admin')
    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
      <div class="info">
        <a href="#" class="d-block">{{ auth()->user()->adminDetail->nama }}</a>
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
        <!-- Dashboard -->
        <li class="nav-item">
          <a href="{{ route('dashboard.admin') }}" class="nav-link {{ ($title === 'Dashboard') ? 'active' : '' }}">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>
              Dashboard
            </p>
          </a>
        </li>

        <!-- Tahun Ajaran -->
        <li class="nav-item">
          <a href="{{ route('tahunajaran') }}" class="nav-link {{ ($title === 'Tahun Ajaran') ? 'active' : '' }}">
            <i class="nav-icon fas fa-calendar"></i>
            <p>
              Tahun Ajaran
            </p>
          </a>
        </li>

        <!-- Program Studi -->
        <li class="nav-item">
          <a href="{{ route('programstudi') }}" class="nav-link {{ ($title === 'Program Studi') ? 'active' : '' }}">
            <i class="nav-icon fas fa-user-graduate"></i>
            <p>
              Program Studi
            </p>
          </a>
        </li>

        <!-- Akun -->
        <li class="nav-item {{ $title === 'Akun' ? 'menu-open' : '' }}">
          <a href="#" class="nav-link {{ ($title === 'Akun') ? 'active' : '' }}">
            <i class="nav-icon fas fa-user"></i>
            <p>
              Akun
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="{{ route('mahasiswa') }}"
                class="nav-link {{ (str_contains($subtitle, 'Mahasiswa')) ? 'active' : '' }}">
                <i class="nav-icon far fa-circle"></i>
                <p>Mahasiswa</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('dosen') }}" class="nav-link {{ (str_contains($subtitle, 'Dosen')) ? 'active' : '' }}">
                <i class="nav-icon far fa-circle"></i>
                <p>Dosen</p>
              </a>
            </li>
          </ul>
        </li>

        <!-- Pembimbing -->
        <li class="nav-item">
          <a href="#" class="nav-link {{ ($title === 'Pembimbing dan Mahasiswa') ? 'active' : '' }}">
            <i class="nav-icon fas fa-users"></i>
            <p>Pembimbing</p>
          </a>
        </li>

        <!-- Kategori Nilai -->
        <li class="nav-item">
          <a href="#" class="nav-link  {{ ($title === 'Kategori Nilai') ? 'active' : '' }}">
            <i class="nav-icon fas fa-pen"></i>
            <p>Kategori Nilai</p>
          </a>
        </li>

        <!-- Verify Documents -->
        <li class="nav-item">
          <a href="{{ route('verify') }}" class="nav-link {{ ($title === 'Verify Dokumen') ? 'active' : '' }}">
            <i class="nav-icon fas fa-shield-alt"></i>
            <p>Verify Dokumen</p>
          </a>
        </li>

        <!-- Daftar Skripsi -->
        <li class="nav-item">
          <a href="{{ route('admin.daftar.skripsi') }}" class="nav-link {{ ($title === 'Daftar Skripsi') ? 'active' : '' }}">
            <i class="nav-icon fas fa-file-alt"></i>
            <p>Daftar Skripsi</p>
          </a>
        </li>

        <li class="nav-header">PENGATURAN</li>
        <!-- Pengaturan -->
        <li class="nav-item">
          <a href="{{ route('pengaturan') }}" class="nav-link  {{ ($title === 'Pengaturan') ? 'active' : '' }}">
            <i class="nav-icon fas fa-cog"></i>
            <p>Pengaturan</p>
          </a>
        </li>

        <li class="nav-header">FILE AKHIR</li>
        <!-- File -->
        <li class="nav-item">
          <a href="#" class="nav-link {{ ($title === 'File') ? 'active' : '' }}">
            <i class="nav-icon fas fa-file"></i>
            <p>
              File
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="#" class="nav-link {{ ($subtitle === 'Skripsi') ? 'active' : '' }}">
                <i class="nav-icon far fa-circle"></i>
                <p>Skripsi</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="#" class="nav-link {{ ($subtitle === 'Revisi Skripsi') ? 'active' : '' }}">
                <i class="nav-icon far fa-circle"></i>
                <p>Revisi Skripsi</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="#" class="nav-link {{ ($subtitle === 'Laporan Akhir') ? 'active' : '' }}">
                <i class="nav-icon far fa-circle"></i>
                <p>Laporan Akhir</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="#" class="nav-link {{ ($subtitle === 'Revisi Laporan Akhir') ? 'active' : '' }}">
                <i class="nav-icon far fa-circle"></i>
                <p>Revisi Laporan Akhir</p>
              </a>
            </li>
          </ul>
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
