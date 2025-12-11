// ===================================
// Navigation
// ===================================
const navbar = document.getElementById('navbar');
const hamburger = document.getElementById('hamburger');
const navMenu = document.getElementById('navMenu');

// Navbar scroll effect
window.addEventListener('scroll', () => {
    if (window.scrollY > 50) {
        navbar.classList.add('scrolled');
    } else {
        navbar.classList.remove('scrolled');
    }
});

// Mobile menu toggle
hamburger.addEventListener('click', () => {
    navMenu.classList.toggle('active');
});

// Close menu on link click
document.querySelectorAll('.nav-menu a').forEach(link => {
    link.addEventListener('click', () => {
        navMenu.classList.remove('active');
    });
});

// ===================================
// Smooth Scroll
// ===================================
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// ===================================
// Stats Counter Animation
// ===================================
const animateCounter = (element, target, duration = 2000) => {
    let start = 0;
    const increment = target / (duration / 16);
    
    const timer = setInterval(() => {
        start += increment;
        if (start >= target) {
            element.textContent = target.toFixed(1);
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(start);
        }
    }, 16);
};

const observeStats = () => {
    const stats = document.querySelectorAll('.stat-number');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const target = parseFloat(entry.target.getAttribute('data-target'));
                animateCounter(entry.target, target);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    stats.forEach(stat => observer.observe(stat));
};

observeStats();

// ===================================
// Hero Chart
// ===================================
const initHeroChart = () => {
    const ctx = document.getElementById('heroChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],
            datasets: [{
                label: 'Downtime (jam)',
                data: [2, 1.5, 3, 2.5, 1.8, 2.2],
                borderColor: 'rgb(99, 102, 241)',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        display: false
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
};

// ===================================
// Demo Tabs
// ===================================
const initDemoTabs = () => {
    const tabs = document.querySelectorAll('.demo-tab');
    const panels = document.querySelectorAll('.demo-panel');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const targetTab = tab.getAttribute('data-tab');

            // Remove active class from all tabs and panels
            tabs.forEach(t => t.classList.remove('active'));
            panels.forEach(p => p.classList.remove('active'));

            // Add active class to clicked tab and corresponding panel
            tab.classList.add('active');
            document.getElementById(`demo-${targetTab}`).classList.add('active');

            // Initialize charts for active panel
            if (targetTab === 'dashboard') {
                // Delay to ensure panel is visible before initializing charts
                setTimeout(() => {
                    initDemoCharts();
                }, 100);
            } else if (targetTab === 'reports') {
                setTimeout(() => {
                    initReportCharts();
                }, 100);
            }
        });
    });
};

// ===================================
// Demo Charts
// ===================================
let demoChart1Instance = null;
let demoChart2Instance = null;

const initDemoCharts = () => {
    // Destroy existing charts if they exist
    if (demoChart1Instance) {
        demoChart1Instance.destroy();
        demoChart1Instance = null;
    }
    if (demoChart2Instance) {
        demoChart2Instance.destroy();
        demoChart2Instance = null;
    }

    // Chart 1: Downtime Trend
    const ctx1 = document.getElementById('demoChart1');
    if (ctx1) {
        // Clear canvas
        const ctx = ctx1.getContext('2d');
        ctx.clearRect(0, 0, ctx1.width, ctx1.height);

        demoChart1Instance = new Chart(ctx1, {
            type: 'line',
            data: {
                labels: ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4'],
                datasets: [{
                    label: 'Downtime (jam)',
                    data: [8, 12, 6, 10],
                    borderColor: 'rgb(99, 102, 241)',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: 'rgb(99, 102, 241)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        callbacks: {
                            label: function(context) {
                                return 'Downtime: ' + context.parsed.y + ' jam';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 2,
                            callback: function(value) {
                                return value + ' jam';
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    // Chart 2: Top 5 Machines (Pie Chart)
    const ctx2 = document.getElementById('demoChart2');
    if (ctx2) {
        // Clear canvas
        const ctx = ctx2.getContext('2d');
        ctx.clearRect(0, 0, ctx2.width, ctx2.height);

        demoChart2Instance = new Chart(ctx2, {
            type: 'pie',
            data: {
                labels: ['HPA-001', 'CBS-002', 'PKG-003', 'WLD-004', 'ASM-005'],
                datasets: [{
                    data: [30, 25, 20, 15, 10],
                    backgroundColor: [
                        'rgb(99, 102, 241)',
                        'rgb(16, 185, 129)',
                        'rgb(251, 191, 36)',
                        'rgb(239, 68, 68)',
                        'rgb(139, 92, 246)'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            font: {
                                size: 12,
                                weight: '500'
                            },
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return label + ': ' + value + ' jam (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }
};

// ===================================
// Report Charts
// ===================================
let reportChart1Instance = null;
let reportChart2Instance = null;

const initReportCharts = () => {
    // Destroy existing charts if they exist
    if (reportChart1Instance) {
        reportChart1Instance.destroy();
        reportChart1Instance = null;
    }
    if (reportChart2Instance) {
        reportChart2Instance.destroy();
        reportChart2Instance = null;
    }

    const ctx1 = document.getElementById('reportChart1');
    if (ctx1) {
        // Clear canvas
        const ctx = ctx1.getContext('2d');
        ctx.clearRect(0, 0, ctx1.width, ctx1.height);

        reportChart1Instance = new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
                datasets: [{
                    label: 'MTTR (jam)',
                    data: [3.2, 2.8, 2.5, 2.3, 2.1, 2.0],
                    backgroundColor: 'rgba(99, 102, 241, 0.8)',
                    borderRadius: 8,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                return 'MTTR: ' + context.parsed.y + ' jam';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value + ' jam';
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    const ctx2 = document.getElementById('reportChart2');
    if (ctx2) {
        // Clear canvas
        const ctx = ctx2.getContext('2d');
        ctx.clearRect(0, 0, ctx2.width, ctx2.height);

        reportChart2Instance = new Chart(ctx2, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
                datasets: [{
                    label: 'Uptime (%)',
                    data: [96, 97, 97.5, 98, 98.5, 99],
                    borderColor: 'rgb(16, 185, 129)',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: 'rgb(16, 185, 129)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                return 'Uptime: ' + context.parsed.y + '%';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        min: 95,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }
};

// ===================================
// PM Filter
// ===================================
const initPMFilter = () => {
    const filter = document.getElementById('pmFilter');
    const cards = document.querySelectorAll('.demo-pm-card');

    if (filter) {
        filter.addEventListener('change', (e) => {
            const status = e.target.value.toLowerCase().replace(' ', '-');
            
            cards.forEach(card => {
                if (status === 'semua-status' || card.getAttribute('data-status') === status) {
                    card.style.display = 'block';
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'scale(1)';
                    }, 10);
                } else {
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.9)';
                    setTimeout(() => {
                        card.style.display = 'none';
                    }, 300);
                }
            });
        });
    }
};

// ===================================
// Report Tabs
// ===================================
const initReportTabs = () => {
    const tabs = document.querySelectorAll('.report-tab');
    const panels = document.querySelectorAll('.report-panel');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const targetReport = tab.getAttribute('data-report');

            tabs.forEach(t => t.classList.remove('active'));
            panels.forEach(p => p.classList.remove('active'));

            tab.classList.add('active');
            document.getElementById(`report-${targetReport}`).classList.add('active');
        });
    });
};

// ===================================
// Feature Cards Animation
// ===================================
const initFeatureCards = () => {
    const cards = document.querySelectorAll('.feature-card');
    
    cards.forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.style.transform = 'translateY(-10px) scale(1.02)';
        });

        card.addEventListener('mouseleave', () => {
            card.style.transform = 'translateY(0) scale(1)';
        });
    });
};

// ===================================
// Form Submission
// ===================================
const initContactForm = () => {
    const form = document.getElementById('contactForm');
    
    if (form) {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            
            // Simulate form submission
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim...';
            submitBtn.disabled = true;
            
            setTimeout(() => {
                submitBtn.innerHTML = '<i class="fas fa-check"></i> Terkirim!';
                submitBtn.style.background = 'var(--success-color)';
                
                setTimeout(() => {
                    form.reset();
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    submitBtn.style.background = '';
                    alert('Terima kasih! Tim kami akan menghubungi Anda segera.');
                }, 2000);
            }, 1500);
        });
    }
};

// ===================================
// Scroll Animations
// ===================================
const initScrollAnimations = () => {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    document.querySelectorAll('.feature-card, .benefit-item, .pricing-card').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'all 0.6s ease';
        observer.observe(el);
    });
};

// ===================================
// PM Progress Animation
// ===================================
const animatePMProgress = () => {
    const progressBars = document.querySelectorAll('.pm-progress-bar');
    
    progressBars.forEach(bar => {
        const width = bar.style.width;
        bar.style.width = '0%';
        
        setTimeout(() => {
            bar.style.width = width;
        }, 500);
    });
};

// ===================================
// Initialize on DOM Load
// ===================================
document.addEventListener('DOMContentLoaded', () => {
    initHeroChart();
    initDemoTabs();
    
    // Initialize dashboard charts when page loads (if dashboard tab is active)
    const dashboardPanel = document.getElementById('demo-dashboard');
    if (dashboardPanel && dashboardPanel.classList.contains('active')) {
        setTimeout(() => {
            initDemoCharts();
        }, 300);
    }
    
    initPMFilter();
    initReportTabs();
    initFeatureCards();
    initContactForm();
    initScrollAnimations();
    
    // Animate PM progress when PM tab is active
    const pmTab = document.querySelector('[data-tab="pm"]');
    if (pmTab) {
        pmTab.addEventListener('click', () => {
            setTimeout(animatePMProgress, 300);
        });
    }
});

// ===================================
// Parallax Effect
// ===================================
window.addEventListener('scroll', () => {
    const scrolled = window.pageYOffset;
    const parallaxElements = document.querySelectorAll('.hero-background');
    
    parallaxElements.forEach(element => {
        const speed = 0.5;
        element.style.transform = `translateY(${scrolled * speed}px)`;
    });
});

