<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') — Admin Panel | Zonely</title>
    <link rel="icon" href="/frontend/img/favicon.svg" type="image/svg+xml">
    <link rel="icon" href="/frontend/img/zonely_logo.png" type="image/png" sizes="192x192">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --sidebar-bg: #1e293b;
            --sidebar-hover: #334155;
            --primary-accent: #0ea5e9;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --text-light: #e2e8f0;
        }

        body {
            background-color: #f1f5f9;
            transition: background-color 0.3s;
        }

        body.dark-mode {
            background-color: #0f172a;
            color: #e2e8f0;
        }

        body.dark-mode .topbar {
            background-color: var(--sidebar-bg);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3);
        }

        body.dark-mode .kpi-card,
        body.dark-mode .section-card {
            background-color: var(--sidebar-bg);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3);
        }

        body.dark-mode .kpi-card p {
            color: #94a3b8;
        }

        body.dark-mode .card-header {
            background-color: var(--sidebar-hover) !important;
        }

        body.dark-mode .text-dark {
            color: #e2e8f0 !important;
        }

        body.dark-mode .dropdown-menu {
            background-color: var(--sidebar-bg);
            color: #e2e8f0;
        }

        body.dark-mode .dropdown-item {
            color: #e2e8f0;
        }

        body.dark-mode .dropdown-item:hover {
            background-color: var(--sidebar-hover);
        }

        body.dark-mode .list-group-item {
            background-color: var(--sidebar-hover);
            color: #e2e8f0;
            border-color: #334155;
        }

        body.dark-mode .border {
            border-color: #334155 !important;
        }

        .sidebar {
            background-color: var(--sidebar-bg);
            color: var(--text-light);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 280px;
            padding: 1.5rem;
            transition: all 0.3s;
            z-index: 1000;
            overflow-y: auto;
        }

        .sidebar.collapsed {
            width: 80px;
            padding: 1.5rem 0.8rem;
        }

        .sidebar.collapsed .nav-text,
        .sidebar.collapsed .logo-text {
            display: none;
        }

        .sidebar a {
            color: var(--text-light);
            text-decoration: none;
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            transition: all 0.2s;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background-color: var(--sidebar-hover);
            color: white;
        }

        .sidebar i {
            width: 36px;
            font-size: 1.2rem;
            text-align: center;
        }

        .main-content {
            margin-left: 280px;
            transition: all 0.3s;
            padding: 2rem;
        }

        .main-content.expanded {
            margin-left: 80px;
        }

        .topbar {
            background-color: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            padding: 1rem 2rem;
            position: fixed;
            top: 0;
            left: 280px;
            right: 0;
            z-index: 999;
            transition: all 0.3s;
        }

        .topbar.expanded {
            left: 80px;
        }

        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .kpi-card {
            background: white;
            padding: 1.5rem;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border-left: 4px solid var(--primary-accent);
            transition: transform 0.2s;
        }

        .kpi-card:hover {
            transform: translateY(-5px);
        }

        .kpi-card h3 {
            margin: 0;
            font-size: 2rem;
        }

        .kpi-card p {
            margin: 0.5rem 0 0;
            color: #64748b;
        }

        .section-card {
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            overflow: hidden;
            transition: transform 0.2s;
        }

        .section-card:hover {
            box-shadow: 0 8px 16px -2px rgba(0, 0, 0, 0.12);
        }

        .btn-toggle-sidebar {
            cursor: pointer;
            font-size: 1.5rem;
        }

        .hover-shadow:hover {
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            transform: translateY(-5px);
            transition: all 0.2s;
        }

        .role-badge {
            font-size: 0.75rem;
            padding: 0.3rem 0.6rem;
            border-radius: 0.5rem;
        }

        ul.dynamic-list li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.3rem;
        }

        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
                padding: 1.5rem 0.8rem;
            }

            .sidebar .nav-text,
            .sidebar .logo-text {
                display: none;
            }

            .main-content,
            .topbar {
                margin-left: 80px;
            }
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    @include('admin.partial._sidebar2')

    <!-- Topbar -->
    @include('admin.partial._navbar2')

    <!-- Main Content -->
    <main class="main-content" id="mainContent">

        {{-- Flash messages --}}
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mt-5 pt-3" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show mt-5 pt-3" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mt-5 pt-3" role="alert">
            <i class="fas fa-times-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mt-5 pt-3" role="alert">
            <i class="fas fa-times-circle me-2"></i>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        /* Sidebar Toggle */
        document.getElementById('toggleSidebar').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('collapsed');
            document.getElementById('mainContent').classList.toggle('expanded');
            document.getElementById('topbar').classList.toggle('expanded');
        });

        /* Theme Toggle */
        // document.getElementById('themeToggle').addEventListener('click', function() {
        //     document.body.classList.toggle('dark-mode');
        //     const icon = this.querySelector('i');
        //     icon.classList.toggle('fa-moon');
        //     icon.classList.toggle('fa-sun');
        // });
        // Apply the saved theme on page load
        document.addEventListener('DOMContentLoaded', () => {
            const isDarkMode = localStorage.getItem('darkMode') === 'true';
            if (isDarkMode) {
                document.body.classList.add('dark-mode');
                const icon = document.getElementById('themeToggle').querySelector('i');
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
            }
        });

        // Toggle theme on button click
        document.getElementById('themeToggle').addEventListener('click', function() {
            document.body.classList.toggle('dark-mode');

            const isDark = document.body.classList.contains('dark-mode');
            localStorage.setItem('darkMode', isDark); // Save preference

            const icon = this.querySelector('i');
            icon.classList.toggle('fa-moon');
            icon.classList.toggle('fa-sun');
        });

        // let roles = [{
        //         name: 'CEO Team – Full Control',
        //         level: 'Level 1',
        //         badge: 'bg-dark text-light',
        //         icon: 'crown'
        //     },
    </script>
    @yield('scripts')
</body>

</html>
