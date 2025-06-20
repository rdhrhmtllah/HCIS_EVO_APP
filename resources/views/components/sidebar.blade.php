<div id="sidebar">
    <div class="sidebar-wrapper active">
        <div class="sidebar-header position-relative mt-4">
            <div class="d-flex justify-content-start align-items-center rounded mb-3">
                <img src="./assets/compiled/png/logo-lg.png" alt="Logo" width="20%" style="height: fit-content"><br>
                <span class="text-uppercase fw-bold px-2 py-0" style="font-size: 16px">EVO GROUP</span>

            </div>
            <div class="my-0 py-0 d-flex flex-column">
                <span class="  fw-bold px-2 py-0" style="font-size: 18px">👤 <span class="text-uppercase">{{
                        Auth::user()->Username
                        }}</span></span>
            <span style="font-size: 0.9rem; font-weight: 400;" class=" px-2 fw-0 my-0"> <span class=""></span> {{Auth::user()->divisionKaryawan->nama_divisi}}</span>
            </div>
        </div>

        {{-- <div class="theme-toggle d-flex gap-2 align-items-center mb-4 float-end px-4">
            <i class="bi bi-sun"></i>
            <div class="form-check form-switch fs-6">
                <input class="form-check-input me-0" type="checkbox" id="toggle-dark" value="dark" style="cursor: pointer">
                <label class="form-check-label"></label>
            </div>
            <i class="bi bi-moon"></i>
        </div> --}}

        <div class="sidebar-menu mx-0">
            <ul class="menu">
                <li class="sidebar-title">Menu</li>
            @can('open-kpi')

                <li class="sidebar-item {{ request()->is('dashboardSuperUser') ? 'active' : '' }}">
                    <a href="{{ route('superUser') }}" class="sidebar-link">
                        <i class="bi bi-house-door"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="sidebar-item {{ request()->is('parseResume') ? 'active' : '' }}">
                    <a href="{{ route('parseResume') }}" class="sidebar-link">
                        <i class="bi bi-magic"></i>
                        <span>Resume Parser</span>
                    </a>
                </li>
            @endcan

                <li class="sidebar-item has-sub">
                    <a href="#" class="sidebar-link">
                        <i class="bi bi-person-video2"></i>
                        <span>HCIS</span>
                    </a>
                <ul class="submenu px-0">
            @can('akses-spesial','OvertimeManagement')

                    <li class="submenu-item">
                        <a href="/overtime" class="submenu-link">
                            <i class="bi bi-stack-overflow"></i> Lembur
                        </a>
                    </li>
                @endcan
                @can('akses-spesial','ShiftManagement')
                <li class="submenu-item">
                    <a href="/userShift" class="submenu-link">
                        <i class="bi bi-shuffle"></i> Shift
                    </a>
                </li>
            @endcan
            @can('open-kpi')

                        <li class="submenu-item">
                            <a href="/uDash" class="submenu-link">
                                <i class="bi bi-person-badge"></i> uDash
                            </a>
                        </li>
            @endcan
                    </ul>
                </li>
            @can('can-kpi')

                <li class="sidebar-item has-sub">
                    <a href="#" class="sidebar-link">
                        <i class="bi bi-folder"></i>
                        <span>Master Data</span>
                    </a>
                    <ul class="submenu px-0">
                        <li class="submenu-item">
                            <a href="/divison" class="submenu-link">
                                <i class="bi bi-diagram-3"></i> Divisi
                            </a>
                        </li>
                        <li class="submenu-item">
                            <a href="/level" class="submenu-link">
                                <i class="bi bi-person-badge"></i> Jabatan
                            </a>
                        </li>
                        <li class="submenu-item">
                            <a href="/employee" class="submenu-link">
                                <i class="bi bi-people"></i> Karyawan
                            </a>
                        </li>
                        <li class="submenu-item">
                            <a href="/case" class="submenu-link">
                                <i class="bi bi-exclamation-triangle"></i> Case
                            </a>
                        </li>
                        <li class="submenu-item {{ request()->is('evaluation*') ? 'active' : '' }}">
                            <a href="/evaluation" class="submenu-link">
                                <i class="bi bi-hourglass"></i>
                                Evaluation
                            </a>
                        </li>
                        <li class="submenu-item {{ request()->is('project*') ? 'active' : '' }}">
                            <a href="{{ route('project.index') }}" class="submenu-link">
                                <i class="bi bi-kanban"></i>
                                Project
                            </a>
                        </li>
                        <li class="submenu-item {{ request()->is('task*') ? 'active' : '' }}">
                            <a href="/task" class="submenu-link">
                                <i class="bi bi-list-task"></i>
                                Task
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="sidebar-item has-sub">
                    <a href="#" class="sidebar-link">
                        <i class="bi bi-arrow-left-right"></i>
                        <span>Cross Review</span>
                    </a>
                    <ul class="submenu px-0">
                        <li class="submenu-item">
                            <a href="{{route('Cross.index')}}" class="submenu-link">
                                <i class="bi bi-person-bounding-box"></i> Create Cross Review
                            </a>
                        </li>
                        <li class="submenu-item">
                            <a href="/historyCrossReview" class="submenu-link">
                                <i class="bi bi-clock-history"></i> Review History
                            </a>
                        </li>
                        @if (auth()->user()->Level_Id == 2)

                        <li class="submenu-item">
                            <a href="/crossReviewPeriode" class="submenu-link">
                                <i class="bi bi-calendar2-plus"></i> Cross Review Period
                            </a>
                        </li>

                        @endif
                        <li class="submenu-item">
                            <a href="/crossFeedbackReview" class="submenu-link">
                                <i class="bi bi-mailbox-flag"></i> Cross Review Feedback
                            </a>
                        </li>
                        @if (auth()->user()->Level_Id == 2 and auth()->user()->Username == 'Welly')

                        <li class="submenu-item">
                            <a href="/crossReviewSummary" class="submenu-link">
                                <i class="bi bi-journals"></i> Summary Review
                            </a>
                        </li>


                        @endif


                    </ul>
                               
                </li>
                <li class="sidebar-item has-sub">
                    <a href="#" class="sidebar-link">
                        <i class="bi bi-ticket-detailed"></i>
                        <span>Ticket</span>
                    </a>
                    <ul class="submenu px-0">
                        <li class="submenu-item">
                            <a href="/displayTicket" class="submenu-link">
                                <i class="bi bi-card-list"></i> Display Ticket
                            </a>
                        </li>
                        <li class="submenu-item">
                            <a href="/takeTicket" class="submenu-link">
                                <i class="bi bi-reply"></i> Response Ticket
                            </a>
                        </li>
                        <li class="submenu-item">
                            <a href="/finishTicket" class="submenu-link">
                                <i class="bi bi-check-circle"></i> Finish Ticket
                            </a>
                        </li>
                    </ul>
                </li>
                @if (Auth::user()->Level_Id == 2)
                <li class="sidebar-item">
                    <a href="/globalReview" class="sidebar-link">
                        <i class="fa-regular fa-star-half-stroke"></i>
                        <span>Global Review</span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a href="/master-periode" class="sidebar-link">
                        <i class="fa-regular fa-star-half-stroke"></i>
                        <span>Master Periode</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="{{ route('summary.review') }}" class="sidebar-link">
                        <i class="fa-regular fa-star-half-stroke"></i>
                        <span>Summary Global Review</span>
                    </a>
                </li>
                @endif
            @endcan

                <hr>
            @can('open-kpi')

                <li class="sidebar-item">
                    <a href="/ticket" class="sidebar-link">
                        <i class="bi bi-plus-circle"></i>
                        <span>Create Ticket</span>
                    </a>
                </li>
            @endcan

                <li class="sidebar-item">
                    <a href="{{ route('password.reset') }}" class="sidebar-link">
                        <i class="bi bi-plus-circle"></i>
                        <span>Ubah Password</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="/logout" class="sidebar-link">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
