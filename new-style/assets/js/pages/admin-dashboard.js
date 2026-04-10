"use strict";

// Dashboard Statistics Module
var DashboardStatistics = (function () {
    var chart = null;
    var chartData = null;
    var currentFilter = {
        start_date: null,
        end_date: null
    };

    // API Base URL
    var apiUrl = window.location.origin + '/api/statistic';

    // Initialize
    var init = function () {
        initDateRangePicker();
        fetchDashboardData();
    };

    // Initialize Date Range Picker
    var initDateRangePicker = function () {
        var dateInput = document.getElementById('dashboard_date_range');
        var clearBtn = document.getElementById('clear_date_filter');

        if (!dateInput || typeof $ === 'undefined' || typeof $.fn.daterangepicker === 'undefined') {
            console.warn('DateRangePicker not available');
            return;
        }

        $(dateInput).daterangepicker({
            autoUpdateInput: false,
            opens: 'left',
            locale: {
                format: 'DD/MM/YYYY',
                separator: ' - ',
                applyLabel: 'Terapkan',
                cancelLabel: 'Batal',
                fromLabel: 'Dari',
                toLabel: 'Sampai',
                customRangeLabel: 'Kustom',
                weekLabel: 'M',
                daysOfWeek: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
                monthNames: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                             'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
                firstDay: 1
            },
            ranges: {
                'Hari Ini': [moment(), moment()],
                'Kemarin': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                '7 Hari Terakhir': [moment().subtract(6, 'days'), moment()],
                '30 Hari Terakhir': [moment().subtract(29, 'days'), moment()],
                'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
                'Bulan Lalu': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                'Tahun Ini': [moment().startOf('year'), moment().endOf('year')]
            }
        });

        $(dateInput).on('apply.daterangepicker', function(ev, picker) {
            var startDate = picker.startDate.format('YYYY-MM-DD');
            var endDate = picker.endDate.format('YYYY-MM-DD');
            
            $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
            
            // Update filter and fetch data
            currentFilter.start_date = startDate;
            currentFilter.end_date = endDate;
            
            // Show clear button
            if (clearBtn) {
                clearBtn.style.display = 'block';
            }
            
            showLoading();
            fetchDashboardData();
        });

        $(dateInput).on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });

        // Clear filter button
        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                dateInput.value = '';
                currentFilter.start_date = null;
                currentFilter.end_date = null;
                clearBtn.style.display = 'none';
                
                showLoading();
                fetchDashboardData();
            });
        }
    };

    // Show loading state
    var showLoading = function () {
        ['stat_total', 'stat_pending', 'stat_approved', 'stat_rejected', 'stat_revision', 'stat_canceled', 'stat_completed'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) {
                el.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';
            }
        });

        var loadingEl = document.getElementById('latest_requests_loading');
        var contentEl = document.getElementById('latest_requests_content');
        if (loadingEl) loadingEl.style.display = 'block';
        if (contentEl) contentEl.style.display = 'none';
    };

    // Fetch dashboard data from API
    var fetchDashboardData = function () {
        var url = apiUrl;
        var params = [];

        if (currentFilter.start_date) {
            params.push('start_date=' + encodeURIComponent(currentFilter.start_date));
        }
        if (currentFilter.end_date) {
            params.push('end_date=' + encodeURIComponent(currentFilter.end_date));
        }

        if (params.length > 0) {
            url += '?' + params.join('&');
        }

        fetch(url, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(function(response) {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(function(result) {
            if (result.success) {
                renderStatistics(result.data.statistics);
                renderLatestRequests(result.data.latest_requests);
                renderChart(result.data.chart_data);
                updateMonthDisplay(result.data.current_month);
            } else {
                console.error('API Error:', result.message);
                showError('Gagal memuat data dashboard');
            }
        })
        .catch(function(error) {
            console.error('Fetch Error:', error);
            showError('Terjadi kesalahan saat memuat data');
        });
    };

    // Render statistics cards
    var renderStatistics = function (stats) {
        document.getElementById('stat_total').textContent = stats.total || 0;
        document.getElementById('stat_pending').textContent = stats.pending || 0;
        document.getElementById('stat_approved').textContent = stats.approved || 0;
        document.getElementById('stat_rejected').textContent = stats.rejected || 0;
        document.getElementById('stat_revision').textContent = stats.revision || 0;
        document.getElementById('stat_canceled').textContent = stats.canceled || 0;
        document.getElementById('stat_completed').textContent = stats.completed || 0;
    };

    // Render latest requests
    var renderLatestRequests = function (requests) {
        var loadingEl = document.getElementById('latest_requests_loading');
        var contentEl = document.getElementById('latest_requests_content');

        if (!requests || requests.length === 0) {
            loadingEl.style.display = 'none';
            contentEl.innerHTML = `
                <div class="text-center text-muted py-10">
                    <i class="ki-duotone ki-document fs-3x text-gray-300 mb-3">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    <div>Belum ada pengajuan</div>
                </div>
            `;
            contentEl.style.display = 'block';
            return;
        }

        var html = '';
        requests.forEach(function (req) {
            var statusInfo = getStatusInfo(req.STATUS);
            var employeeName = req.EMPLOYEE_NAME || 'N/A';
            var destinationCity = req.DESTINATION_CITY || 'Perjalanan Dinas';
            var createdAt = req.CREATED_AT_FORMATTED || '';
            var uuid = req.UUID || req.ID;

            html += `
                <div class="d-flex align-items-center mb-7">
                    <div class="symbol symbol-50px me-5">
                        <div class="symbol-label bg-light-primary">
                            <i class="ki-duotone ki-airplane text-primary fs-2x">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <a href="${window.location.origin}/secretary/travel_request/${uuid}/detail" class="text-gray-900 fw-bold text-hover-primary fs-6">${escapeHtml(employeeName)}</a>
                        <span class="text-muted d-block fw-semibold">${escapeHtml(destinationCity)}</span>
                        <div class="d-flex align-items-center mt-1">
                            <span class="badge ${statusInfo.class} fs-8">${statusInfo.text}</span>
                            <span class="text-muted fs-8 ms-2">${createdAt}</span>
                        </div>
                    </div>
                </div>
            `;
        });

        loadingEl.style.display = 'none';
        contentEl.innerHTML = html;
        contentEl.style.display = 'block';
    };

    // Get status badge info
    var getStatusInfo = function (status) {
        var statusClass = 'badge-light-secondary';
        var statusText = status;

        switch (status) {
            case 'PENDING':
            case 'SUBMITTED':
                statusClass = 'badge-light-warning';
                statusText = 'Menunggu';
                break;
            case 'PRE_APPROVED':
            case 'VERIFICATOR_APPROVED':
                statusClass = 'badge-light-success';
                statusText = 'Disetujui';
                break;
            case 'PRE_REJECTED':
                statusClass = 'badge-light-danger';
                statusText = 'Ditolak';
                break;
            case 'VERIFICATOR_REVISION':
            case 'PAYMENT_APPROVER_REVISION':
                statusClass = 'badge-light-primary';
                statusText = 'Revisi';
                break;
            case 'PAID':
            case 'UNPAID':
                statusClass = 'badge-light-dark';
                statusText = 'Selesai';
                break;
            case 'CANCELED':
                statusClass = 'badge-light-secondary';
                statusText = 'Dibatalkan';
                break;
        }

        return { class: statusClass, text: statusText };
    };

    // Render chart
    var renderChart = function (data) {
        var element = document.getElementById('kt_apexcharts_3');

        if (!element) {
            return;
        }

        chartData = data;

        var height = parseInt(KTUtil.css(element, 'height'));
        var labelColor = KTUtil.getCssVariableValue('--kt-gray-500') || '#A1A5B7';
        var borderColor = KTUtil.getCssVariableValue('--kt-gray-200') || '#EFF2F5';
        var baseColor = KTUtil.getCssVariableValue('--kt-info') || '#009EF7';
        var lightColor = KTUtil.getCssVariableValue('--kt-info-light') || '#F1FAFF';

        // Use labels if available, otherwise use days
        var categories = data.labels || data.days || [];

        var options = {
            series: [{
                name: '',
                data: data.values || []
            }],
            chart: {
                fontFamily: 'inherit',
                type: 'area',
                height: height,
                toolbar: {
                    show: false
                }
            },
            plotOptions: {},
            legend: {
                show: false
            },
            dataLabels: {
                enabled: false
            },
            fill: {
                type: 'solid',
                opacity: 1
            },
            stroke: {
                curve: 'smooth',
                show: true,
                width: 3,
                colors: [baseColor]
            },
            xaxis: {
                categories: categories,
                axisBorder: {
                    show: false,
                },
                axisTicks: {
                    show: false
                },
                labels: {
                    style: {
                        colors: labelColor,
                        fontSize: '12px'
                    },
                    rotate: -45,
                    rotateAlways: categories.length > 15
                },
                crosshairs: {
                    position: 'front',
                    stroke: {
                        color: baseColor,
                        width: 1,
                        dashArray: 3
                    }
                },
                tooltip: {
                    enabled: true,
                    formatter: undefined,
                    offsetY: 0,
                    style: {
                        fontSize: '12px'
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: labelColor,
                        fontSize: '12px'
                    }
                }
            },
            states: {
                normal: {
                    filter: {
                        type: 'none',
                        value: 0
                    }
                },
                hover: {
                    filter: {
                        type: 'none',
                        value: 0
                    }
                },
                active: {
                    allowMultipleDataPointsSelection: false,
                    filter: {
                        type: 'none',
                        value: 0
                    }
                }
            },
            tooltip: {
                style: {
                    fontSize: '12px'
                },
                x: {
                    formatter: function (val, opts) {
                        var label = categories[opts.dataPointIndex] || val;
                        if (data.month_name && data.year) {
                            return label + ' ' + (data.month_name || '') + ' ' + (data.year || '');
                        }
                        return label;
                    }
                },
                y: {
                    formatter: function (val) {
                        return val + ' Pengajuan';
                    }
                }
            },
            colors: [lightColor],
            grid: {
                borderColor: borderColor,
                strokeDashArray: 4,
                yaxis: {
                    lines: {
                        show: true
                    }
                }
            },
            markers: {
                strokeColor: baseColor,
                strokeWidth: 3
            }
        };

        // Destroy existing chart if any
        if (chart) {
            chart.destroy();
        }

        chart = new ApexCharts(element, options);
        chart.render();
    };

    // Update month display
    var updateMonthDisplay = function (monthStr) {
        var currentMonthEl = document.getElementById('current_month_display');
        var chartMonthEl = document.getElementById('chart_month_display');

        if (currentMonthEl) {
            currentMonthEl.textContent = monthStr;
        }
        if (chartMonthEl) {
            chartMonthEl.textContent = monthStr;
        }
    };

    // Escape HTML to prevent XSS
    var escapeHtml = function (text) {
        if (!text) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    };

    // Show error message
    var showError = function (message) {
        // Update stats with error indicators
        ['stat_total', 'stat_pending', 'stat_approved', 'stat_rejected', 'stat_revision', 'stat_canceled', 'stat_completed'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) {
                el.innerHTML = '<i class="ki-duotone ki-information-5 text-danger"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>';
            }
        });

        // Update latest requests with error
        var loadingEl = document.getElementById('latest_requests_loading');
        var contentEl = document.getElementById('latest_requests_content');
        if (loadingEl) loadingEl.style.display = 'none';
        if (contentEl) {
            contentEl.innerHTML = `
                <div class="text-center text-danger py-10">
                    <i class="ki-duotone ki-information-5 fs-3x text-danger mb-3">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                    </i>
                    <div>${message}</div>
                </div>
            `;
            contentEl.style.display = 'block';
        }
    };

    return {
        init: init
    };
})();

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function () {
    DashboardStatistics.init();
});