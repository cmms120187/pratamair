<?php

return [
    'menu_groups' => [
        [
            'name' => 'Dashboard',
            'route' => 'dashboard',
            'icon' => 'home',
            'type' => 'single',
            'menu_key' => 'dashboard'
        ],
        [
            'name' => 'Part ERP',
            'route' => 'part-erp.index',
            'icon' => 'server',
            'type' => 'single',
            'menu_key' => 'part-erp'
        ],
        [
            'name' => 'Location',
            'icon' => 'building',
            'type' => 'group',
            'menu_key' => 'location',
            'children' => [
                ['name' => 'Plants', 'route' => '/plants', 'icon' => 'leaf', 'menu_key' => 'plants'],
                ['name' => 'Processes', 'route' => '/processes', 'icon' => 'cog', 'menu_key' => 'processes'],
                ['name' => 'Lines', 'route' => '/lines', 'icon' => 'bars', 'menu_key' => 'lines'],
                ['name' => 'Room ERP', 'route' => '/room-erp', 'icon' => 'server', 'menu_key' => 'room-erp'],
            ]
        ],
        [
            'name' => 'Machinary',
            'icon' => 'server',
            'type' => 'group',
            'menu_key' => 'machinary',
            'children' => [
                ['name' => 'Systems', 'route' => '/systems', 'icon' => 'cog', 'menu_key' => 'systems'],
                ['name' => 'Groups', 'route' => '/groups', 'icon' => 'users', 'menu_key' => 'groups'],
                ['name' => 'Machine Types', 'route' => '/machine-types', 'icon' => 'chip', 'menu_key' => 'machine-types'],
                ['name' => 'Brands', 'route' => '/brands', 'icon' => 'tag', 'menu_key' => 'brands'],
                ['name' => 'Models', 'route' => '/models', 'icon' => 'cube', 'menu_key' => 'models'],
                ['name' => 'Machine ERP', 'route' => '/machine-erp', 'icon' => 'server', 'menu_key' => 'machine-erp'],
                ['name' => 'Mutasi', 'route' => '/mutasi', 'icon' => 'exchange', 'menu_key' => 'mutasi'],
            ]
        ],
        [
            'name' => 'Downtime',
            'icon' => 'clock',
            'type' => 'group',
            'menu_key' => 'downtime',
            'children' => [
                ['name' => 'Problems', 'route' => '/problems', 'icon' => 'exclamation', 'menu_key' => 'problems'],
                ['name' => 'Reasons', 'route' => '/reasons', 'icon' => 'question', 'menu_key' => 'reasons'],
                ['name' => 'Actions', 'route' => '/actions', 'icon' => 'bolt', 'menu_key' => 'actions'],
                ['name' => 'Downtime ERP2', 'route' => '/downtime-erp2', 'icon' => 'server', 'menu_key' => 'downtime-erp2'],
                ['name' => 'Work Orders', 'route' => '/work-orders', 'icon' => 'clipboard-list', 'menu_key' => 'work-orders'],
            ]
        ],
        [
            'name' => 'Production',
            'icon' => 'chart-bar',
            'type' => 'group',
            'menu_key' => 'production',
            'children' => [
                ['name' => 'Hasil Produksi Per Jam', 'route' => '/production-hourly', 'icon' => 'clock', 'menu_key' => 'production-hourly'],
                ['name' => 'Hasil Produksi Perhari', 'route' => '/production-daily', 'icon' => 'calendar', 'menu_key' => 'production-daily'],
            ]
        ],
        [
            'name' => 'Users',
            'icon' => 'user',
            'type' => 'group',
            'menu_key' => 'users',
            'children' => [
                ['name' => 'Users', 'route' => '/users', 'icon' => 'user', 'menu_key' => 'users-list'],
                ['name' => 'Struktur Organisasi', 'route' => '/users/organizational-structure', 'icon' => 'sitemap', 'menu_key' => 'organizational-structure'],
                ['name' => 'Bagan STO', 'route' => '/users/organizational-structure/chart', 'icon' => 'sitemap', 'menu_key' => 'organizational-structure'],
                ['name' => 'Activity', 'route' => '/activities', 'icon' => 'clock', 'menu_key' => 'activities'],
                // Role Permissions hanya untuk admin, akan ditambahkan dinamis di navigation.blade.php
            ]
        ],
        [
            'name' => 'Preventive Maintenance',
            'icon' => 'wrench',
            'type' => 'group',
            'menu_key' => 'preventive-maintenance',
            'children' => [
                ['name' => 'Scheduling', 'route' => '/preventive-maintenance/scheduling', 'icon' => 'calendar', 'menu_key' => 'preventive-scheduling'],
                ['name' => 'Controlling', 'route' => '/preventive-maintenance/controlling', 'icon' => 'cog', 'menu_key' => 'preventive-controlling'],
                ['name' => 'Monitoring', 'route' => '/preventive-maintenance/monitoring', 'icon' => 'chart', 'menu_key' => 'preventive-monitoring'],
                ['name' => 'Updating', 'route' => '/preventive-maintenance/updating', 'icon' => 'edit', 'menu_key' => 'preventive-updating'],
                ['name' => 'Reporting', 'route' => '/preventive-maintenance/reporting', 'icon' => 'document', 'menu_key' => 'preventive-reporting'],
            ]
        ],
        [
            'name' => 'Predictive Maintenance',
            'icon' => 'chart-line',
            'type' => 'group',
            'menu_key' => 'predictive-maintenance',
            'children' => [
                ['name' => 'Standards', 'route' => '/standards', 'icon' => 'clipboard-check', 'menu_key' => 'standards'],
                ['name' => 'Scheduling PdM', 'route' => '/predictive-maintenance/scheduling', 'icon' => 'calendar', 'menu_key' => 'predictive-scheduling'],
                ['name' => 'Controlling PdM', 'route' => '/predictive-maintenance/controlling', 'icon' => 'cog', 'menu_key' => 'predictive-controlling'],
                ['name' => 'Monitoring PdM', 'route' => '/predictive-maintenance/monitoring', 'icon' => 'chart', 'menu_key' => 'predictive-monitoring'],
                ['name' => 'Updating PdM', 'route' => '/predictive-maintenance/updating', 'icon' => 'edit', 'menu_key' => 'predictive-updating'],
                ['name' => 'Reporting PdM', 'route' => '/predictive-maintenance/reporting', 'icon' => 'document', 'menu_key' => 'predictive-reporting'],
            ]
        ],
        [
            'name' => 'Report and Analytics',
            'icon' => 'chart-bar',
            'type' => 'group',
            'menu_key' => 'reports',
            'children' => [
                [
                    'name' => 'MTTR & MTBF',
                    'route' => '/mttr-mtbf',
                    'icon' => 'chart-line',
                    'type' => 'single',
                    'menu_key' => 'mttr-mtbf'
                ],
                [
                    'name' => 'Pareto Mesin',
                    'route' => '/pareto-machine',
                    'icon' => 'chart-bar',
                    'type' => 'single',
                    'menu_key' => 'pareto-machine'
                ],
                [
                    'name' => 'Summary Downtime',
                    'route' => '/summary-downtime',
                    'icon' => 'chart',
                    'type' => 'single',
                    'menu_key' => 'summary-downtime'
                ],
                [
                    'name' => 'Kinerja Mekanik',
                    'route' => '/mechanic-performance',
                    'icon' => 'users',
                    'type' => 'single',
                    'menu_key' => 'mechanic-performance'
                ],
                [
                    'name' => 'Root Cause Analysis',
                    'route' => '/root-cause-analysis',
                    'icon' => 'search',
                    'type' => 'single',
                    'menu_key' => 'root-cause-analysis'
                ],
            ]
        ],
    ],
];

