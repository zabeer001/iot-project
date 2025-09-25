@php
    $credential = \App\Helpers\Credentials::getCredentials();
@endphp

<nav class="sidebar">
    <div class="sidebar-header">
        <a href="#" class="sidebar-brand">
            AppnGO
        </a>
        <div class="sidebar-toggler not-active">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>
    <div class="sidebar-body">
        <ul class="nav">
            <li class="nav-item {{ active_class(['companies']) }}">
                <a href="{{ url('/companies') }}" class="nav-link">
                    <i class="link-icon" data-feather="box"></i>
                    <span class="link-title">Companies</span>
                </a>
            </li>


            @if ($credential && ($credential->url || $credential->accessKey || $credential->secretKey))
                <li class="nav-item {{ active_class(['zabeer-device']) }}">
                    <a href="{{ route('zabeer.device') }}" class="nav-link">
                        <i class="link-icon" data-feather="cpu"></i>
                        <span class="link-title"> Device</span>
                    </a>
                </li>
            @endif

            {{-- <li class="nav-item {{ active_class(['zabeer-device']) }}">
                <a href="{{ route('zabeer.polling.device') }}" class="nav-link">
                    <i class="link-icon" data-feather="cpu"></i>
                    <span class="link-title"> Device</span>
                </a>
            </li> --}}

            {{-- <li class="nav-item {{ active_class(['setup/*']) }}"> --}}

            <li class="nav-item {{ active_class(['setup/*']) }}">
                <a class="nav-link" data-bs-toggle="collapse" href="#sliders" role="button"
                    aria-expanded="{{ is_active_route(['setup/*']) }}" aria-controls="sliders">
                    <i class="link-icon" data-feather="sliders"></i>
                    <span class="link-title">Setup</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="feather feather-chevron-down link-arrow">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </a>
                <div class="collapse {{ show_class(['setup/*']) }}" id="sliders">
                    <ul class="nav sub-menu">
                        <li class="nav-item">
                            <a href="{{ route('setup.areas') }}"
                                class="nav-link {{ active_class(['setup/areas']) }}">Area</a>
                        </li>
                    </ul>
                </div>
            </li>

            @if (!empty($company))
                <li class="nav-item {{ active_class(['companies/section/*']) }}">
                    <a class="nav-link" data-bs-toggle="collapse" href="#trello" role="button"
                        aria-expanded="{{ is_active_route(['companies/section/*']) }}" aria-controls="trello">
                        <i class="link-icon" data-feather="trello"></i>
                        <span class="link-title">{{ $company->name }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="feather feather-chevron-down link-arrow">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </a>
                    <div class="collapse {{ show_class(['companies/section/*']) }}" id="trello">
                        <ul class="nav sub-menu">
                            <li class="nav-item">
                                <a href="{{ route('companies.section.profile', $company->id) }}"
                                    class="nav-link {{ active_class(['companies/section/profile/*']) }}">Profile</a>
                            </li>
                        </ul>
                    </div>
                </li>
            @else
                <li class="nav-item">
                    <a class="nav-link cursor-pointer">
                        <i class="link-icon" data-feather="lock"></i>
                        <span class="link-title">Select Company First</span>
                    </a>
                </li>
            @endif

            <li class="nav-item {{ active_class(['companies.section.profile']) }}">
                <a href="{{ route('companies.section.profile') }}" class="nav-link">
                    <i class="link-icon" data-feather="cpu"></i>
                    <span class="link-title"> Test Profile for all </span>
                </a>
            </li>
        </ul>
    </div>
</nav>
