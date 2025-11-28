@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Bagan Struktur Organisasi (STO)</h1>
            <div class="flex items-center gap-3">
                <a href="{{ route('users.organizational-structure.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 text-sm font-medium">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>List View
                </a>
                <a href="{{ route('users.index') }}" class="text-gray-600 hover:text-gray-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 relative">
            <!-- Zoom Controls -->
            <div class="absolute top-4 right-4 z-10 flex flex-col gap-2 bg-white rounded-lg shadow-lg p-2 border">
                <button id="zoom-in" class="p-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition" title="Zoom In">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7" />
                    </svg>
                </button>
                <button id="zoom-out" class="p-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition" title="Zoom Out">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7" />
                    </svg>
                </button>
                <button id="zoom-reset" class="p-2 bg-gray-600 text-white rounded hover:bg-gray-700 transition" title="Reset Zoom">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </button>
                <div class="text-xs text-center text-gray-600 mt-1" id="zoom-level">100%</div>
            </div>

            <div id="org-chart-container" class="overflow-auto" style="min-height: 600px; position: relative;">
                <div id="org-chart-wrapper" style="transform-origin: top left; transition: transform 0.3s ease;">
                    <div id="org-chart" class="org-chart">
                        <!-- Chart will be rendered here by JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.org-chart {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 40px 20px;
    min-width: 100%;
    width: max-content;
    position: relative;
}

.org-level {
    display: flex;
    justify-content: center;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 20px;
    margin-bottom: 40px;
    width: 100%;
}

.org-node {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    min-width: 200px;
    margin-bottom: 0;
}

.org-node-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    padding: 16px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    cursor: pointer;
    position: relative;
    z-index: 2;
    min-width: 200px;
    max-width: 250px;
    margin-bottom: 0;
}

.org-node-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
}

.org-node-card.level-7 {
    background: linear-gradient(135deg, #9333ea 0%, #7e22ce 100%);
}

.org-node-card.level-6 {
    background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
}

.org-node-card.level-5 {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
}

.org-node-card.level-4 {
    background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
}

.org-node-card.level-3 {
    background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
}

.org-node-card.level-2 {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.org-node-card.level-1 {
    background: linear-gradient(135deg, #84cc16 0%, #65a30d 100%);
}

.org-node-photo {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid white;
    margin: 0 auto 10px;
    display: block;
}

.org-node-photo-placeholder {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.3);
    border: 3px solid white;
    margin: 0 auto 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.org-node-photo-placeholder svg {
    width: 30px;
    height: 30px;
}

.org-node-name {
    color: white;
    font-weight: bold;
    font-size: 14px;
    text-align: center;
    margin-bottom: 4px;
}

.org-node-role {
    color: rgba(255, 255, 255, 0.9);
    font-size: 11px;
    text-align: center;
    margin-bottom: 2px;
}

.org-node-nik {
    color: rgba(255, 255, 255, 0.8);
    font-size: 10px;
    text-align: center;
    margin-top: 4px;
}

.org-node-link {
    text-decoration: none;
    color: inherit;
}

.org-node-link:hover {
    text-decoration: none;
}

.org-children {
    display: flex;
    justify-content: center;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 20px;
    margin-top: 40px;
    position: relative;
    padding-top: 0;
    min-width: 100%;
}

/* SVG container for connection lines */
.org-connection-svg {
    position: absolute;
    pointer-events: none;
    z-index: 1;
    width: 100%;
    left: 0;
    top: 0;
}

.org-connection-svg line {
    stroke: #64748b;
    stroke-width: 3;
    stroke-linecap: round;
}

/* Ensure container has relative positioning */
#org-chart {
    position: relative;
}

/* Ensure org-chart-container also has relative positioning */
#org-chart-container {
    position: relative;
}


@media (max-width: 768px) {
    .org-node {
        min-width: 150px;
    }

    .org-node-card {
        min-width: 150px;
        max-width: 180px;
        padding: 12px;
    }

    .org-node-photo,
    .org-node-photo-placeholder {
        width: 50px;
        height: 50px;
    }

    .org-node-name {
        font-size: 12px;
    }

    .org-node-role {
        font-size: 10px;
    }
}
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const treeData = @json($treeData);
    const container = document.getElementById('org-chart');
    const wrapper = document.getElementById('org-chart-wrapper');
    const containerDiv = document.getElementById('org-chart-container');

    // Zoom functionality
    let currentZoom = 1;
    const minZoom = 0.3;
    const maxZoom = 3;
    const zoomStep = 0.1;

    function updateZoom(scale) {
        currentZoom = Math.max(minZoom, Math.min(maxZoom, scale));
        wrapper.style.transform = `scale(${currentZoom})`;
        document.getElementById('zoom-level').textContent = Math.round(currentZoom * 100) + '%';

        // Redraw lines after zoom - wait for transform to complete
        requestAnimationFrame(() => {
            setTimeout(() => {
                drawConnectionLines();
            }, 50);
        });
    }

    function zoomIn() {
        updateZoom(currentZoom + zoomStep);
    }

    function zoomOut() {
        updateZoom(currentZoom - zoomStep);
    }

    function resetZoom() {
        autoFitZoom();
    }

    function autoFitZoom() {
        setTimeout(() => {
            const chart = container;
            if (!chart) return;

            const chartWidth = chart.scrollWidth;
            const chartHeight = chart.scrollHeight;
            const containerWidth = containerDiv.clientWidth;
            const containerHeight = containerDiv.clientHeight;

            const scaleX = containerWidth / chartWidth;
            const scaleY = containerHeight / chartHeight;
            const autoScale = Math.min(scaleX, scaleY, 1) * 0.9;

            updateZoom(autoScale);
        }, 100);
    }

    // Zoom button events
    document.getElementById('zoom-in').addEventListener('click', zoomIn);
    document.getElementById('zoom-out').addEventListener('click', zoomOut);
    document.getElementById('zoom-reset').addEventListener('click', resetZoom);

    // Mouse wheel zoom
    containerDiv.addEventListener('wheel', function(e) {
        if (e.ctrlKey || e.metaKey) {
            e.preventDefault();
            const delta = e.deltaY > 0 ? -zoomStep : zoomStep;
            updateZoom(currentZoom + delta);
        }
    }, { passive: false });

    // Helper function to get role level for styling
    function getRoleLevel(role) {
        const hierarchy = {
            'general_manager': 7,
            'manager': 6,
            'ast_manager': 5,
            'coordinator': 4,
            'group_leader': 3,
            'team_leader': 2,
            'mekanik': 1,
        };
        return hierarchy[role] || 1;
    }

    // Render node function
    function renderNode(node, level = 1) {
        const cardLevel = getRoleLevel(node.role);
        const hasChildren = node.children && node.children.length > 0;
        const nodeClass = hasChildren ? 'org-node has-children' : 'org-node';

        let html = `
            <div class="${nodeClass}" data-node-id="${node.id}">
                <a href="{{ route('users.edit', '') }}/${node.id}" class="org-node-link">
                    <div class="org-node-card level-${cardLevel}">
        `;

        // Photo
        if (node.photo) {
            html += `<img src="{{ asset('storage') }}/${node.photo}" alt="${node.name}" class="org-node-photo">`;
        } else {
            html += `
                <div class="org-node-photo-placeholder">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
            `;
        }

        html += `
                        <div class="org-node-name">${node.name}</div>
                        <div class="org-node-role">${node.role_display || ''}</div>
                        <div class="org-node-nik">NIK: ${node.nik || '-'}</div>
                    </div>
                </a>
        `;

        // Children
        if (hasChildren) {
            html += '<div class="org-children">';
            node.children.forEach(child => {
                html += renderNode(child, level + 1);
            });
            html += '</div>';
        }

        html += '</div>';
        return html;
    }

    // Draw connection lines function - recalculates after zoom
    function drawConnectionLines() {
        // Remove existing SVG
        const existingSvg = container.querySelector('.org-connection-svg');
        if (existingSvg) {
            existingSvg.remove();
        }

        const allNodes = container.querySelectorAll('.org-node.has-children');
        if (allNodes.length === 0) return;

        // Get container position - getBoundingClientRect accounts for zoom transform
        const containerRect = container.getBoundingClientRect();

        // Calculate SVG dimensions - use actual container dimensions
        const svgWidth = container.scrollWidth || container.offsetWidth;
        const svgHeight = container.scrollHeight || container.offsetHeight;

        // Create SVG positioned relative to container
        // SVG is inside the scaled wrapper, so coordinates need to be in unscaled space
        const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        svg.setAttribute('class', 'org-connection-svg');
        svg.setAttribute('width', svgWidth);
        svg.setAttribute('height', svgHeight);
        svg.setAttribute('viewBox', `0 0 ${svgWidth} ${svgHeight}`);
        svg.style.position = 'absolute';
        svg.style.top = '0';
        svg.style.left = '0';
        svg.style.pointerEvents = 'none';
        svg.style.zIndex = '1';
        svg.style.overflow = 'visible';

        // Draw lines for each parent-child relationship (1 level only)
        allNodes.forEach(parentNode => {
            const parentCard = parentNode.querySelector('.org-node-card');
            const childrenContainer = parentNode.querySelector('.org-children');

            if (!parentCard || !childrenContainer) return;

            // Only get DIRECT children
            const children = Array.from(childrenContainer.children).filter(child =>
                child.classList.contains('org-node')
            );

            if (children.length === 0) return;

            // Get parent position relative to container
            // Use getBoundingClientRect which accounts for transforms, then convert to container coordinates
            const parentCardRect = parentCard.getBoundingClientRect();
            const wrapperRect = wrapper.getBoundingClientRect();

            // Calculate position in container's coordinate system (unscaled)
            // getBoundingClientRect gives viewport coordinates (after scale transform)
            // Convert to container's original coordinate system by dividing by currentZoom
            const parentX = (parentCardRect.left - wrapperRect.left) / currentZoom + (parentCardRect.width / 2) / currentZoom;
            const parentY = (parentCardRect.bottom - wrapperRect.top) / currentZoom;

            // Draw line to each child
            children.forEach(childNode => {
                const childCard = childNode.querySelector('.org-node-card');
                if (!childCard) return;

                const childCardRect = childCard.getBoundingClientRect();
                const childX = (childCardRect.left - wrapperRect.left) / currentZoom + (childCardRect.width / 2) / currentZoom;
                const childY = (childCardRect.top - wrapperRect.top) / currentZoom;

                // Draw line from parent bottom center to child top center
                const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
                line.setAttribute('x1', parentX);
                line.setAttribute('y1', parentY);
                line.setAttribute('x2', childX);
                line.setAttribute('y2', childY);
                line.setAttribute('stroke', '#64748b');
                line.setAttribute('stroke-width', '3');
                line.setAttribute('stroke-linecap', 'round');
                svg.appendChild(line);
            });
        });

        container.appendChild(svg);
    }

    // Render chart
    if (treeData && treeData.length > 0) {
        treeData.forEach(node => {
            container.innerHTML += renderNode(node);
        });

        // Draw lines after rendering
        setTimeout(() => {
            drawConnectionLines();
            autoFitZoom();
        }, 200);

        // Redraw on resize
        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                drawConnectionLines();
            }, 250);
        });
    } else {
        container.innerHTML = `
            <div class="text-center py-12">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <p class="text-gray-500 text-lg">Belum ada data struktur organisasi.</p>
                <p class="text-gray-400 text-sm mt-2">Silakan tambahkan user terlebih dahulu di menu Users.</p>
            </div>
        `;
    }
});
</script>
@endpush
@endsection
