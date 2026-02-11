<?php

return [
    'menu_groups' => [
        [
            'name' => 'Dashboard',
            'icon' => 'home',
            'type' => 'group',
            'menu_key' => 'dashboard',
            'children' => [
                ['name' => 'Dashboard Normal', 'route' => 'dashboard', 'icon' => 'home', 'menu_key' => 'dashboard'],
                ['name' => 'Dashboard Large', 'route' => 'dashboard.large', 'icon' => 'tv', 'menu_key' => 'dashboard-large'],
                ['name' => 'Dashboard Portrait', 'route' => 'dashboard.portrait', 'icon' => 'mobile', 'menu_key' => 'dashboard-portrait'],
                ['name' => 'Dashboard Settings', 'route' => 'dashboard-settings.index', 'icon' => 'cog', 'menu_key' => 'dashboard-settings', 'admin_only' => true],
            ]
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
                ['name' => 'Plants', 'route' => 'plants.index', 'icon' => 'leaf', 'menu_key' => 'plants'],
                ['name' => 'Processes', 'route' => 'processes.index', 'icon' => 'cog', 'menu_key' => 'processes'],
                ['name' => 'Lines', 'route' => 'lines.index', 'icon' => 'bars', 'menu_key' => 'lines'],
                ['name' => 'Room ERP', 'route' => 'room-erp.index', 'icon' => 'server', 'menu_key' => 'room-erp'],
            ]
        ],
        [
            'name' => 'Machinary',
            'icon' => 'server',
            'type' => 'group',
            'menu_key' => 'machinary',
            'children' => [
                ['name' => 'Systems', 'route' => 'systems.index', 'icon' => 'cog', 'menu_key' => 'systems'],
                ['name' => 'Groups', 'route' => 'groups.index', 'icon' => 'users', 'menu_key' => 'groups'],
                ['name' => 'Machine Types', 'route' => 'machine-types.index', 'icon' => 'chip', 'menu_key' => 'machine-types'],
                ['name' => 'Brands', 'route' => 'brands.index', 'icon' => 'tag', 'menu_key' => 'brands'],
                ['name' => 'Models', 'route' => 'models.index', 'icon' => 'cube', 'menu_key' => 'models'],
                ['name' => 'Machine ERP', 'route' => 'machine-erp.index', 'icon' => 'server', 'menu_key' => 'machine-erp'],
                ['name' => 'Mutasi', 'route' => 'mutasi.index', 'icon' => 'exchange', 'menu_key' => 'mutasi'],
            ]
        ],
        [
            'name' => 'Downtime',
            'icon' => 'clock',
            'type' => 'group',
            'menu_key' => 'downtime',
            'children' => [
                ['name' => 'Problems', 'route' => 'problems.index', 'icon' => 'exclamation', 'menu_key' => 'problems'],
                ['name' => 'Reasons', 'route' => 'reasons.index', 'icon' => 'question', 'menu_key' => 'reasons'],
                ['name' => 'Actions', 'route' => 'actions.index', 'icon' => 'bolt', 'menu_key' => 'actions'],
                ['name' => 'Downtime ERP', 'route' => 'downtime_erp.index', 'icon' => 'server', 'menu_key' => 'downtime-erp'],
                ['name' => 'Downtime ERP2', 'route' => 'downtime-erp2.index', 'icon' => 'server', 'menu_key' => 'downtime-erp2'],
                ['name' => 'Work Orders', 'route' => 'work-orders.index', 'icon' => 'clipboard-list', 'menu_key' => 'work-orders'],
            ]
        ],
        [
            'name' => 'Production',
            'icon' => 'chart-bar',
            'type' => 'group',
            'menu_key' => 'production',
            'children' => [
                ['name' => 'Hasil Produksi Per Jam', 'route' => 'production-hourly.index', 'icon' => 'clock', 'menu_key' => 'production-hourly'],
                ['name' => 'Hasil Produksi Perhari', 'route' => 'production-daily.index', 'icon' => 'calendar', 'menu_key' => 'production-daily'],
            ]
        ],
        [
            'name' => 'Users',
            'icon' => 'user',
            'type' => 'group',
            'menu_key' => 'users',
            'children' => [
                ['name' => 'Users', 'route' => 'users.index', 'icon' => 'user', 'menu_key' => 'users-list'],
                ['name' => 'Struktur Organisasi', 'route' => 'users.organizational-structure.index', 'icon' => 'sitemap', 'menu_key' => 'organizational-structure'],
                ['name' => 'Bagan STO', 'route' => 'users.organizational-structure.chart', 'icon' => 'sitemap', 'menu_key' => 'organizational-structure'],
                ['name' => 'Activity', 'route' => 'activities.index', 'icon' => 'clock', 'menu_key' => 'activities'],
                // Role Permissions hanya untuk admin, akan ditambahkan dinamis di navigation.blade.php
            ]
        ],
        [
            'name' => 'Preventive Maintenance',
            'icon' => 'wrench',
            'type' => 'group',
            'menu_key' => 'preventive-maintenance',
            'children' => [
                ['name' => 'Scheduling', 'route' => 'preventive-maintenance.scheduling.index', 'icon' => 'calendar', 'menu_key' => 'preventive-scheduling'],
                ['name' => 'Controlling', 'route' => 'preventive-maintenance.ctrl.index', 'icon' => 'cog', 'menu_key' => 'preventive-controlling'],
                ['name' => 'Monitoring', 'route' => 'preventive-maintenance.monitoring.index', 'icon' => 'chart', 'menu_key' => 'preventive-monitoring'],
                ['name' => 'Updating', 'route' => 'preventive-maintenance.updating.index', 'icon' => 'edit', 'menu_key' => 'preventive-updating'],
                ['name' => 'Reporting', 'route' => 'preventive-maintenance.reporting.index', 'icon' => 'document', 'menu_key' => 'preventive-reporting'],
            ]
        ],
        [
            'name' => 'Predictive Maintenance',
            'icon' => 'chart-line',
            'type' => 'group',
            'menu_key' => 'predictive-maintenance',
            'children' => [
                ['name' => 'Standards', 'route' => 'standards.index', 'icon' => 'clipboard-check', 'menu_key' => 'standards'],
                ['name' => 'Scheduling PdM', 'route' => 'predictive-maintenance.scheduling.index', 'icon' => 'calendar', 'menu_key' => 'predictive-scheduling'],
                ['name' => 'Controlling PdM', 'route' => 'predictive-maintenance.controlling.index', 'icon' => 'cog', 'menu_key' => 'predictive-controlling'],
                ['name' => 'Monitoring PdM', 'route' => 'predictive-maintenance.monitoring.index', 'icon' => 'chart', 'menu_key' => 'predictive-monitoring'],
                ['name' => 'Updating PdM', 'route' => 'predictive-maintenance.updating.index', 'icon' => 'edit', 'menu_key' => 'predictive-updating'],
                ['name' => 'Reporting PdM', 'route' => 'predictive-maintenance.reporting.index', 'icon' => 'document', 'menu_key' => 'predictive-reporting'],
            ]
        ],
        [
            'name' => 'Inspections',
            'icon' => 'clipboard-check',
            'type' => 'group',
            'menu_key' => 'inspections',
            'children' => [
                ['name' => 'Templates', 'route' => 'inspection-templates.index', 'icon' => 'file-text', 'menu_key' => 'inspection-templates'],
                ['name' => 'Scheduling', 'route' => 'inspections.scheduling.index', 'icon' => 'calendar', 'menu_key' => 'inspection-scheduling'],
                ['name' => 'Updating', 'route' => 'inspections.updating.index', 'icon' => 'edit', 'menu_key' => 'inspection-updating'],
                ['name' => 'Reporting', 'route' => 'inspections.reporting.index', 'icon' => 'document', 'menu_key' => 'inspection-reporting'],
                ['name' => 'Inspeksi List', 'route' => 'inspections.index', 'icon' => 'list', 'menu_key' => 'inspections-list'],
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
                    'route' => 'mttr_mtbf.index',
                    'icon' => 'chart-line',
                    'type' => 'single',
                    'menu_key' => 'mttr-mtbf'
                ],
                [
                    'name' => 'Pareto Mesin',
                    'route' => 'pareto-machine.index',
                    'icon' => 'chart-bar',
                    'type' => 'single',
                    'menu_key' => 'pareto-machine'
                ],
                [
                    'name' => 'Summary Downtime',
                    'route' => 'summary_downtime.index',
                    'icon' => 'chart',
                    'type' => 'single',
                    'menu_key' => 'summary-downtime'
                ],
                [
                    'name' => 'Kinerja Mekanik',
                    'route' => 'mechanic_performance.index',
                    'icon' => 'users',
                    'type' => 'single',
                    'menu_key' => 'mechanic-performance'
                ],
                [
                    'name' => 'Root Cause Analysis',
                    'route' => 'root-cause-analysis.index',
                    'icon' => 'search',
                    'type' => 'single',
                    'menu_key' => 'root-cause-analysis'
                ],
                [
                    'name' => 'OEE',
                    'route' => 'oee.index',
                    'icon' => 'chart-line',
                    'type' => 'single',
                    'menu_key' => 'oee'
                ],
                [
                    'name' => 'Detail Penggunaan Part',
                    'route' => 'part-erp.stock-movement-report',
                    'icon' => 'document',
                    'type' => 'single',
                    'menu_key' => 'part-erp'
                ],
            ]
        ],
    ],
];

