<?php
$navItems = [
    ['href' => 'dashboard.php',       'icon' => 'bx bx-home',         'label' => 'Home'],
    ['href' => 'queue_manager.php', 'icon' => 'bx bx-skip-next',    'label' => 'Queue Handling', 'badge'],
    ['href' => 'registration.php',        'icon' => 'bx bx-user-plus',        'label' => 'Registration'],
    ['href' => 'search.php',   'icon' => 'bx bx-search',      'label' => 'Search', 'badge'],
    ['href' => 'picture_upload.php',      'icon' => 'bx bx-camera',  'label' => 'Picture Management'],
];

$current = basename($_SERVER['PHP_SELF']);
?>
<nav id="sidebar" class="sidebar bg-dark shadow">
    <div class="logo-details d-flex align-items-center px-3 py-3 border-bottom border-secondary">
        <i class="bx bxs-user text-white fs-3 me-2"></i>
        <span class="logo_name text-white fw-semibold">Staff Panel</span>
    </div>

    <ul class="nav flex-column nav-pills px-2 py-3">
        <?php foreach ($navItems as $item):
            $active = ($current === $item['href']) ? ' active' : '';
            $confirm = !empty($item['confirm']) ? ' onclick="return confirm(\'Are you sure you want to logout?\')"' : '';
        ?>
            <li class="nav-item mb-1">
                <a href="<?= htmlspecialchars($item['href']) ?>"
                   class="nav-link d-flex justify-content-between align-items-center<?= $active ?>"
                   data-bs-toggle="tooltip" data-bs-placement="right"
                   title="<?= htmlspecialchars($item['label']) ?>"<?= $confirm ?>>
                    <div class="d-flex align-items-center">
                        <i class="<?= htmlspecialchars($item['icon']) ?> me-3 fs-5"></i>
                        <span class="links_name"><?= htmlspecialchars($item['label']) ?></span>
                    </div>
                    <?php if (!empty($item['badge'])): ?>
                        <span class="badge bg-danger text-white fw-semibold small px-2 py-1 rounded-pill">
                            <?= $item['badge'] ?>
                        </span>
                    <?php endif; ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>

    <style>
        .sidebar {
            position: fixed;
            top: 0; bottom: 0; left: 0;
            width: 250px;
            background-color: #1f1f1f;
            overflow-y: auto;
            z-index: 1040;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.2);
        }

        .sidebar .nav-link {
            height: 48px;
            padding: 0.5rem 1rem;
            font-size: 0.95rem;
            border-radius: 8px;
            color: #cfd8dc;
            transition: background 0.3s, color 0.3s, box-shadow 0.3s;
        }

        .sidebar .nav-link:hover {
            background-color: #2c2c2c;
            color: #ffffff;
        }

        .sidebar .nav-link.active {
            background-color: #343a40;
            color: #ffffff;
            box-shadow: inset 2px 0 0 #00c853, 0 0 8px rgba(0, 200, 83, 0.4);
        }

        .sidebar .nav-link i {
            min-width: 30px;
            text-align: center;
        }

        .badge {
            font-size: 0.75rem;
            background-color: #dc3545; /* Bootstrap's danger color */
        }

        .tooltip-inner {
            background-color: #000;
            font-size: 0.75rem;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                new bootstrap.Tooltip(el);
            });
        });
    </script>
</nav>
