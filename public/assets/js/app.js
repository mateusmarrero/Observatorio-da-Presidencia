/**
 * OBSERVATÓRIO DA PRESIDÊNCIA — JAVASCRIPT PURO (VANILLA JS)
 * Zero frameworks pesados, 100% nativo e performático.
 */

document.addEventListener('DOMContentLoaded', () => {
    initTheme();
    initMobileNav();
    initAutoDismissAlerts();
});

// ==========================================
// 1. GESTÃO DE TEMA CLARO / ESCURO
// ==========================================
function initTheme() {
    const savedTheme = localStorage.getItem('op_theme') || 
        (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    
    document.documentElement.setAttribute('data-theme', savedTheme);
    updateThemeToggleBtn(savedTheme);

    const themeToggleBtn = document.getElementById('theme-toggle');
    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
            const nextTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            document.documentElement.setAttribute('data-theme', nextTheme);
            localStorage.setItem('op_theme', nextTheme);
            updateThemeToggleBtn(nextTheme);

            // Atualizar gráficos ativos se existirem
            if (window.Chart && Chart.instances) {
                Object.values(Chart.instances).forEach(chart => {
                    if (chart && typeof chart.update === 'function') {
                        chart.update();
                    }
                });
            }
        });
    }
}

function updateThemeToggleBtn(theme) {
    const btn = document.getElementById('theme-toggle');
    if (!btn) return;
    const label = btn.querySelector('.theme-label');
    const icon = btn.querySelector('.theme-icon');
    if (theme === 'dark') {
        if (label) label.textContent = 'Tema Claro';
        if (icon) icon.innerHTML = '☀️';
    } else {
        if (label) label.textContent = 'Tema Escuro';
        if (icon) icon.innerHTML = '🌙';
    }
}

// ==========================================
// 2. MENU MOBILE RESPONSIVO
// ==========================================
function initMobileNav() {
    const toggleBtn = document.getElementById('menu-toggle');
    const sidebar = document.getElementById('app-sidebar');
    
    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            sidebar.classList.toggle('open');
        });

        // Fechar ao clicar fora da sidebar em telas menores
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 992 && sidebar.classList.contains('open')) {
                if (!sidebar.contains(e.target) && e.target !== toggleBtn) {
                    sidebar.classList.remove('open');
                }
            }
        });
    }
}

// ==========================================
// 3. ALERTA AUTO-DISMISS
// ==========================================
function initAutoDismissAlerts() {
    document.querySelectorAll('.alert-auto-dismiss').forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.4s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 400);
        }, 5000);
    });
}

// ==========================================
// 4. CHART.JS HELPERS & CORES ADAPTATIVAS
// ==========================================
window.OPCharts = {
    getColor(name) {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        const colors = {
            textColor: isDark ? '#94a3b8' : '#64748b',
            gridColor: isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.06)',
            primary: '#2563eb',
            secondary: '#0ea5e9',
            warning: '#f59e0b',
            danger: '#ef4444',
            success: '#10b981',
            palette: ['#2563eb', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4', '#64748b', '#ef4444']
        };
        return colors[name] || colors.palette;
    },

    createBarChart(canvasId, labels, data, datasetLabel = 'Valores (R$)') {
        const ctx = document.getElementById(canvasId);
        if (!ctx || !window.Chart) return null;

        return new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: datasetLabel,
                    data: data,
                    backgroundColor: '#2563eb',
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => ' R$ ' + Number(ctx.raw).toLocaleString('pt-BR', { minimumFractionDigits: 2 })
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: () => OPCharts.getColor('textColor') }
                    },
                    y: {
                        grid: { color: () => OPCharts.getColor('gridColor') },
                        ticks: {
                            color: () => OPCharts.getColor('textColor'),
                            callback: (v) => 'R$ ' + (v >= 1e6 ? (v/1e6).toFixed(1) + 'M' : (v >= 1e3 ? (v/1e3).toFixed(0) + 'k' : v))
                        }
                    }
                }
            }
        });
    },

    createComparisonChart(canvasId, labels, nominalData, corrigidoData) {
        const ctx = document.getElementById(canvasId);
        if (!ctx || !window.Chart) return null;

        return new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Valor Nominal',
                        data: nominalData,
                        backgroundColor: '#64748b',
                        borderRadius: 4,
                    },
                    {
                        label: 'Corrigido pelo IPCA',
                        data: corrigidoData,
                        backgroundColor: '#2563eb',
                        borderRadius: 4,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { color: () => OPCharts.getColor('textColor') }
                    },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => ' ' + ctx.dataset.label + ': R$ ' + Number(ctx.raw).toLocaleString('pt-BR', { minimumFractionDigits: 2 })
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: () => OPCharts.getColor('textColor') }
                    },
                    y: {
                        grid: { color: () => OPCharts.getColor('gridColor') },
                        ticks: {
                            color: () => OPCharts.getColor('textColor'),
                            callback: (v) => 'R$ ' + (v >= 1e6 ? (v/1e6).toFixed(1) + 'M' : (v >= 1e3 ? (v/1e3).toFixed(0) + 'k' : v))
                        }
                    }
                }
            }
        });
    },

    createDoughnutChart(canvasId, labels, data, colors) {
        const ctx = document.getElementById(canvasId);
        if (!ctx || !window.Chart) return null;

        return new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors || OPCharts.getColor('palette'),
                    borderWidth: 2,
                    borderColor: 'transparent'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: () => OPCharts.getColor('textColor'),
                            boxWidth: 12,
                            padding: 12
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => ' ' + ctx.label + ': R$ ' + Number(ctx.raw).toLocaleString('pt-BR', { minimumFractionDigits: 2 })
                        }
                    }
                },
                cutout: '65%'
            }
        });
    }
};
