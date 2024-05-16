<nav class="pc-sidebar">
    <div class="navbar-wrapper">
      <div class="m-header">
        <a href="{{ route('dashboard') }}" class="b-brand text-primary">
          <!-- ========   Change your logo from here   ============ -->
          <img src="/assets/images/logo.png" width="100" alt="logo image" class="logo-lg" />
        </a>
      </div>
      <div class="navbar-content">
        <ul class="pc-navbar">
          <li class="pc-item pc-caption">
            <label>Navigation</label>
          </li>
          <li class="pc-item ">
            <a href="{{ route('dashboard') }}" class="pc-link">
              <span class="pc-micon">
                <i class="ph-duotone ph-gauge"></i>
              </span>
              <span class="pc-mtext">Dashboard</span>
            </a>
            
          </li>
        </ul>
       
      {{-- <div class="card pc-user-card">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
              <img src="../assets/images/user/avatar-1.jpg" alt="user-image" class="user-avtar wid-45 rounded-circle" />
            </div>
            <div class="flex-grow-1 ms-3 me-2">
              <h6 class="mb-0">{{ Auth::user()->name }}</h6>
              <small>Administrator</small>
            </div>
            <div class="dropdown">
              <a
                href="#"
                class="btn btn-icon btn-link-secondary avtar arrow-none dropdown-toggle"
                data-bs-toggle="dropdown"
                aria-expanded="false"
                data-bs-offset="0,20"
              >
                <i class="ph-duotone ph-windows-logo"></i>
              </a>
              <div class="dropdown-menu">
                <ul>
                  
                  <li
                    ><a href="{{ route('logout') }}" class="pc-user-links">
                      <i class="ph-duotone ph-power"></i>
                      <span>Logout</span>
                    </a>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div> --}}
    </div>
  </nav>