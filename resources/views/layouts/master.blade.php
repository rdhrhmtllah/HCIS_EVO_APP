<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
    <meta name="csrf-token" content="{{ csrf_token() }}">

<head>
    @include('components.header')
    @stack('vueapp')
    <style>
        html,
        body {
            height: 100vh;
            margin: 0;
            display: flex;
            flex-direction: column;
        }

        #app {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        #main {
            flex: 1;
            padding: 1rem;

        }

        footer {
            background-color: #f8f9fa;
            padding: 1rem;
            text-align: center;
        }
        #sidebar.active .sidebar-wrapper {
            left: 0;
            z-index: 999;
        }
        .sidebar-backdrop{
            z-index: 998;
            transition: all 2s ease;
        }
    </style>

</head>

<body>

    <div id="app1">
        @include('components.sidebar')
        <div id="main">
            <header class="mb-3">
                <a href="#" class="burger-btn d-block d-xl-none">
                    <i class="bi bi-justify fs-3"></i>
                </a>
            </header>

            @yield('content')
            @include('sweetalert::alert')
        </div>
        @include('components.footer')
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>

    @stack('scripts')
</body>

</html>
