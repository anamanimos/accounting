(function () {
    var chart = null;
    var months_id = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                     'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    // Initialize chart
    function initChart(data) {
        var element = document.getElementById('kt_apexcharts_3');
        if (!element) return;

        var chartDataSource = data || (typeof window.chartData !== 'undefined' ? window.chartData : null);
        
        var travelRequestStatistics = {
            labels: [],
            values: []
        };

        if (chartDataSource) {
            travelRequestStatistics.labels = chartDataSource.days || chartDataSource.labels || [];
            travelRequestStatistics.values = chartDataSource.values || [];
        }

        var height = parseInt(KTUtil.css(element, 'height'));
        var labelColor = KTUtil.getCssVariableValue('--kt-gray-500') || '#A1A5B7';
        var borderColor = KTUtil.getCssVariableValue('--kt-gray-200') || '#EFF2F5';
        var baseColor = KTUtil.getCssVariableValue('--kt-info') || '#009EF7';
        var lightColor = KTUtil.getCssVariableValue('--kt-info-light') || '#F1FAFF';

        var options = {
            series: [{
                name: 'Pengajuan',
                data: travelRequestStatistics.values
            }],
            chart: {
                fontFamily: 'inherit',
                type: 'area',
                height: height,
                toolbar: { show: false }
            },
            legend: { show: false },
            dataLabels: { enabled: false },
            fill: { type: 'solid', opacity: 1 },
            stroke: {
                curve: 'smooth',
                show: true,
                width: 3,
                colors: [baseColor]
            },
            xaxis: {
                categories: travelRequestStatistics.labels,
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: {
                    style: { colors: labelColor, fontSize: '12px' }
                },
                crosshairs: {
                    position: 'front',
                    stroke: { color: baseColor, width: 1, dashArray: 3 }
                }
            },
            yaxis: {
                min: 0,
                forceNiceScale: true,
                labels: {
                    style: { colors: labelColor, fontSize: '12px' },
                    formatter: function(val) { return Math.floor(val); }
                }
            },
            tooltip: {
                style: { fontSize: '12px' },
                y: { formatter: function (val) { return val + ' Pengajuan'; } }
            },
            colors: [lightColor],
            grid: {
                borderColor: borderColor,
                strokeDashArray: 4,
                yaxis: { lines: { show: true } }
            },
            markers: { strokeColor: baseColor, strokeWidth: 3 }
        };

        if (chart) {
            chart.updateOptions(options);
        } else {
            chart = new ApexCharts(element, options);
            chart.render();
        }
    }

    // Update statistics cards
    function updateStatisticsCards(stats) {
        if (!stats) return;
        
        var cards = document.querySelectorAll('#statistics_cards .fs-2');
        if (cards.length >= 6) {
            cards[0].textContent = stats.total || 0;
            cards[1].textContent = stats.pending || 0;
            cards[2].textContent = stats.approved || 0;
            cards[3].textContent = stats.rejected || 0;
            cards[4].textContent = stats.revision || 0;
            cards[5].textContent = stats.completed || 0;
        }
    }

    // Update latest requests section
    function updateLatestRequests(requests) {
        var container = document.querySelector('.card-body.pt-2');
        if (!container) return;

        if (!requests || requests.length === 0) {
            container.innerHTML = '<div class="text-center text-muted py-10">' +
                '<i class="ki-duotone ki-document fs-3x text-gray-300 mb-3">' +
                '<span class="path1"></span><span class="path2"></span></i>' +
                '<div>Tidak ada pengajuan ditemukan</div></div>';
            return;
        }

        var html = '';
        for (var i = 0; i < requests.length; i++) {
            var req = requests[i];
            var statusClass = 'badge-light-secondary';
            var statusText = req.STATUS;
            
            switch (req.STATUS) {
                case 'PENDING': statusClass = 'badge-light-warning'; statusText = 'Menunggu'; break;
                case 'PRE_APPROVED': 
                case 'VERIFICATOR_APPROVED': statusClass = 'badge-light-success'; statusText = 'Disetujui'; break;
                case 'PRE_REJECTED': statusClass = 'badge-light-danger'; statusText = 'Ditolak'; break;
                case 'VERIFICATOR_REVISION': 
                case 'PAYMENT_APPROVER_REVISION': statusClass = 'badge-light-primary'; statusText = 'Revisi'; break;
                case 'PAID': 
                case 'UNPAID': statusClass = 'badge-light-dark'; statusText = 'Selesai'; break;
            }

            html += '<div class="d-flex align-items-center mb-7">' +
                '<div class="symbol symbol-50px me-5">' +
                '<div class="symbol-label bg-light-primary">' +
                '<i class="ki-duotone ki-airplane text-primary fs-2x">' +
                '<span class="path1"></span><span class="path2"></span></i></div></div>' +
                '<div class="flex-grow-1">' +
                '<a href="' + appUrl + 'secretary/travel_request/' + req.UUID + '/detail" class="text-gray-900 fw-bold text-hover-primary fs-6">' + (req.EMPLOYEE_NAME || '') + '</a>' +
                '<span class="text-muted d-block fw-semibold">' + (req.DESTINATION_CITY || 'Perjalanan Dinas') + '</span>' +
                '<div class="d-flex align-items-center mt-1">' +
                '<span class="badge ' + statusClass + ' fs-8">' + statusText + '</span>' +
                '<span class="text-muted fs-8 ms-2">' + (req.CREATED_AT_FORMATTED || '') + '</span>' +
                '</div></div></div>';
        }

        container.innerHTML = html;
    }

    // Apply filter function
    function applyFilter() {
        var status = $('#filter_status').val();
        var positions = $('#filter_position').val();
        var startDate = $('#filter_start_date').val();
        var endDate = $('#filter_end_date').val();

        // Show loading
        $('#btn_apply_filter').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Loading...');

        $.ajax({
            url: appUrl + 'secretary/dashboard_ajax',
            type: 'GET',
            data: {
                status: status ? status.join(',') : '',
                position_ids: positions ? positions.join(',') : '',
                start_date: startDate,
                end_date: endDate
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    updateStatisticsCards(response.statistics);
                    updateLatestRequests(response.latest_requests);
                    
                    // Update chart with new data
                    if (response.chart_data) {
                        initChart({
                            labels: response.chart_data.labels,
                            values: response.chart_data.values
                        });
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('Filter error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Gagal memuat data'
                });
            },
            complete: function() {
                $('#btn_apply_filter').prop('disabled', false).html('<i class="ki-outline ki-filter fs-6"></i> Filter');
            }
        });
    }

    // Reset filter function
    function resetFilter() {
        $('#filter_status').val(null).trigger('change');
        $('#filter_position').val(null).trigger('change');
        $('#filter_start_date').val('');
        $('#filter_end_date').val('');
        
        // Reload page to get original data
        window.location.reload();
    }

    // Initialize on document ready
    $(document).ready(function() {
        // Initialize Select2 for multiselect dropdowns
        if ($.fn.select2) {
            $('#filter_status').select2({
                placeholder: 'Semua Status',
                allowClear: true
            });
            $('#filter_position').select2({
                placeholder: 'Semua Posisi',
                allowClear: true
            });
        }

        // Initialize chart with server data
        initChart();

        // Bind filter buttons
        $('#btn_apply_filter').on('click', applyFilter);
        $('#btn_reset_filter').on('click', resetFilter);
    });
})();