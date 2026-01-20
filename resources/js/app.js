import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Initialize Alpine store for sidebar state
Alpine.store('sidebar', {
    collapsed: false
});

Alpine.start();
