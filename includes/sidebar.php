<?php
// includes/sidebar.php
$navItems = [
    [
        'href' => 'dashboard.php',
        'icon' => 'bx bx-home',
        'label' => 'Home'
    ],
    [
        'href' => 'user_management.php',
        'icon' => 'bx bx-user-plus',
        'label' => 'User Management'
    ],
    [
        'href' => 'students.php',
        'icon' => 'bx bx-group',
        'label' => 'Student Management'
    ],
    [
        'href' => 'queue_manager.php',
        'icon' => 'bx bx-list-ul',
        'label' => 'Queue Management'
    ],
    [
        'href' => 'ad_manager.php',
        'icon' => 'bx bx-play-circle',
        'label' => 'Ads Management'
    ],
    [
        'href' => 'reports.php',
        'icon' => 'bx bx-file',
        'label' => 'Reports & Logs'
    ],
];

$current = basename($_SERVER['PHP_SELF']);
?>
<nav id="sidebar" class="sidebar bg-dark">
    <div class="logo-details d-flex align-items-center px-3 py-2">
        <i class="bx bxl-angular text-white fs-3"></i>
        <span class="logo_name text-white ms-2">Admin Panel</span>
        <button class="btn btn-dark ms-auto p-0" id="sidebarToggle" aria-label="Toggle sidebar" aria-expanded="true">
            <i class="bx bx-menu text-white fs-4"></i>
        </button>
    </div>
    <ul class="nav flex-column nav-pills px-2 mb-0">
        <?php foreach ($navItems as $item):
            $active = ($current === $item['href']) ? ' active' : '';
            $confirm = !empty($item['confirm'])
                ? ' onclick="return confirm(\'Are you sure you want to logout?\')"'
                : '';
            ?>
            <li class="nav-item mb-1">
                <a href="<?= htmlspecialchars($item['href']) ?>"
                   class="nav-link text-white d-flex align-items-center<?= $active ?>"
                   data-bs-toggle="tooltip" data-bs-placement="right"
                   title="<?= htmlspecialchars($item['label']) ?>"
                    <?= $confirm ?>>
                    <i class="<?= htmlspecialchars($item['icon']) ?> me-2"></i>
                    <span class="links_name flex-grow-1"><?= htmlspecialchars($item['label']) ?></span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>

    <style>
        .sidebar {
            position: fixed;
            top: 0; bottom: 0; left: 0;
            width: 250px;
            background: #343a40;
            transition: width .3s;
            overflow-y: auto;
            z-index: 1000;
        }
        .sidebar.collapsed {
            width: 80px;
        }
        .sidebar .logo-details {
            border-bottom: 1px solid #495057;
        }
        .sidebar .logo-details .logo_name {
            transition: opacity .3s;
        }
        .sidebar.collapsed .logo_name {
            opacity: 0;
        }
        .sidebar .nav-link {
            height: 50px;
            padding: 0 .75rem;
            font-size: 1rem;
            transition: background .3s;
        }
        .sidebar .nav-link i {
            min-width: 30px;
            text-align: center;
        }
        .sidebar .links_name {
            white-space: nowrap;
            transition: opacity .3s;
        }
        .sidebar.collapsed .links_name {
            opacity: 0;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: #495057;
        }
        /* Tooltip inner for contrast */
        .tooltip-inner {
            background-color: #000;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // init Bootstrap tooltips
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                new bootstrap.Tooltip(el);
            });
            // toggle behavior
            const sidebar = document.getElementById('sidebar');
            const btn = document.getElementById('sidebarToggle');
            btn.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');
                const icon = btn.querySelector('i');
                icon.classList.toggle('bx-menu-alt-right');
                icon.classList.toggle('bx-menu');
                btn.setAttribute('aria-expanded', !sidebar.classList.contains('collapsed'));
            });
        });
        function confirmLogout() {
            return confirm('Are you sure you want to logout?');
        }
    </script>
</nav>
