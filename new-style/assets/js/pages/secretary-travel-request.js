$(document).ready(function() {
    // Status Configuration (loaded from JSON)
    var statusConfig = {};

    function loadStatusConfig() {
        $.getJSON(appUrl.replace('secretary/travel_request/', '') + 'json/status.json', function(data) {
            statusConfig = data;
            // Redraw table if data is already loaded to apply new status text
            // With server-side processing, we might just need to reload the table
            if ($.fn.DataTable.isDataTable('#table_travel_request')) {
                 table.ajax.reload(null, false); // Reloads data without resetting paging
            }
        }).fail(function() {
            console.error("Failed to load status.json");
        });
    }

    loadStatusConfig();

    // Helper function to format destination
    function formatDestination(arrivalCityName, destinationCount) {
        var destination = arrivalCityName || '';
        if (destinationCount > 1) {
            destination += ' +' + (destinationCount - 1) + ' lainnya';
        }
        return destination;
    }

    // Helper function to format status badge
    function formatStatus(row) {
        var statusClass = 'badge-light-secondary';
        var statusText = row.status || 'Unknown';
        var statusTooltip = '';

        if (statusConfig[row.status]) {
            statusClass = statusConfig[row.status].class;
            statusText = statusConfig[row.status].text;
            statusTooltip = statusConfig[row.status].tooltip;
        } else {
             // Fallback for unknown statuses or if JSON not loaded yet
             switch (row.status) {
                case 'PENDING': statusClass = 'badge-light-warning'; statusText = 'Menunggu Persetujuan'; break;
                case 'SUBMITTED': statusClass = 'badge-light-info'; statusText = 'Diajukan'; break;
                case 'PRE_APPROVED': statusClass = 'badge-light-success'; statusText = 'Disetujui'; break;
                case 'PRE_REJECTED': statusClass = 'badge-light-danger'; statusText = 'Ditolak'; break;
                case 'APPROVED': statusClass = 'badge-light-info'; statusText = 'Disetujui'; break;
                case 'REJECTED': statusClass = 'badge-light-danger'; statusText = 'Ditolak'; break;
                case 'PARTIALLY_APPROVED': statusClass = 'badge-light-info'; statusText = 'Sebagian Disetujui'; break;
                case 'VERIFICATOR_APPROVED': statusClass = 'badge-light-info'; statusText = 'Menunggu Pembayaran'; break;
                case 'VERIFICATOR_REVISION': statusClass = 'badge-light-primary'; statusText = 'Revisi Verifikator'; break;
                case 'PAYMENT_APPROVER_APPROVED': statusClass = 'badge-light-success'; statusText = 'Disetujui Pembayaran'; break;
                case 'PAYMENT_APPROVER_REVISION': statusClass = 'badge-light-primary'; statusText = 'Revisi Payment'; break;
                case 'UNPAID': statusClass = 'badge-light-warning'; statusText = 'Belum Dibayar'; break; 
                case 'PAID': statusClass = 'badge-light-success'; statusText = 'Sudah Dibayar'; break;
                case 'CANCELED': statusClass = 'badge-light-secondary'; statusText = 'Dibatalkan'; break;
             }
        }
        return '<span class="badge ' + statusClass + '" title="' + (statusTooltip || '') + '">' + statusText + '</span>';
    }

    var currentTab = 'action'; // Default tab

    // Initialize DataTable
    var table = $('#table_travel_request').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": appUrl.replace('secretary/travel_request/', '') + 'secretary/travel_request/list_ajax',
            "type": "GET",
            "data": function(d) {
                d.tab = currentTab;
                d.status = $('#filter_status').val();
                d.position_ids = $('#filter_position').val();
                d.start_date = $('#filter_start_date').val(); 
                d.end_date = $('#filter_end_date').val(); 
            },
            "dataSrc": function(json) {
                // Update stats if available
                if (json.statistics) {
                    updateStatisticsCards(json.statistics);
                }
                // Update Badge Action Needed
                if (json.action_needed_count !== undefined) {
                     $('#badge_action_needed').text(json.action_needed_count);
                }
                return json.data;
            }
        },
        "columns": [
            { 
                "data": "document_number",
                "render": function(data, type, row) {
                    return data || 'Draft';
                }
            },
            { 
                "data": "employee_full_name",
                "render": function(data, type, row) {
                    return '<span class="d-block fw-bold fs-6 text-nowrap">' + data + '</span>' +
                           '<span class="d-block text-gray-500 fs-7">' + (row.position_name || '') + '</span>';
                }
            },
            { 
                "data": "departure_date",
                "render": function(data) { return data || '-'; }
            },
            { 
                "data": "return_date",
                "render": function(data) { return data || '-'; }
            },
            {
                "data": "arrival_city_name", // We need to check if this is coming from DB or formatted
                "render": function(data, type, row) {
                    return formatDestination(data, row.destination_count);
                }
            },
            { 
                "data": "status", // STATUS raw
                "render": function(data, type, row) {
                    return formatStatus(row);
                }
            },
            { 
                "data": "created_at",
                "render": function(data) { return '<span class="text-muted fs-7">' + (data || '') + '</span>'; }
            },
            {
                "data": null,
                "orderable": false,
                "className": "text-end",
                "render": function(data, type, row) {
                    var uuid = row.uuid || row.id;
                    var detailUrl = appUrl + 'secretary/travel_request/' + uuid + '/detail'; // Corrected appUrl usage
                    var financeUrl = appUrl + 'secretary/travel_request/' + uuid + '/finance'; // Corrected appUrl usage
                    var downloadUrl = appUrl.replace('secretary/travel_request/', '') + 'document/sppd/' + uuid;

                    var downloadLink = '#';
                    var downloadClass = 'btn-download-check';
                    var downloadAttr = 'data-document-ready="false"';
                    
                    if (row.document_number) {
                        downloadLink = downloadUrl;
                        downloadClass = ''; // Remove check class if ready, or keep it but set data-document-ready="true"
                        // Actually better to keep class and handle both cases or just remove class if we don't need to intercept
                        // If we want to intercept only failures:
                        downloadAttr = 'data-document-ready="true" target="_blank"';
                    }

                    // We will use a common class 'btn-action-download' to verify
                    
                    if (!row.document_number) {
                         // If not ready
                         downloadLink = '#';
                         downloadAttr = 'data-document-ready="false"';
                    } else {
                         downloadAttr = 'data-document-ready="true" target="_blank"';
                    }

                    return `
                        <a href="#" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">Actions 
                        <i class="ki-duotone ki-down fs-5 ms-1"></i></a>
                        <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                            <div class="menu-item px-3">
                                <a href="${detailUrl}" class="menu-link px-3">Detail</a>
                            </div>
                            <div class="menu-item px-3">
                                <a href="${financeUrl}" class="menu-link px-3">Keuangan</a>
                            </div>
                            <div class="menu-item px-3">
                                <a href="${downloadLink}" class="menu-link px-3 btn-action-download" ${downloadAttr}>Download Dokumen</a>
                            </div>
                        </div>
                    `;
                }
            }
        ],
        "drawCallback": function(settings) {
            KTMenu.createInstances(); 
            $('[data-bs-toggle="tooltip"]').tooltip();
        },
        "lengthChange": false,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "dom": "lrtip"
    });

    // Status Configuration (loaded from JSON)
    var statusConfig = {};

    function loadStatusConfig() {
        $.getJSON(appUrl.replace('secretary/travel_request/', '') + 'json/status.json', function(data) {
            statusConfig = data;
            // Draw table to apply status config
            table.draw();
        }).fail(function() {
            console.error("Failed to load status.json");
        });
    }
    loadStatusConfig();

    // Tab Change Listener
    $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        var target = $(e.target).attr('href');
        if (target === '#tab_action') {
            currentTab = 'action';
        } else {
            currentTab = 'all'; 
        }
        table.draw();
    });

    // Custom search
    $('[data-kt-filter="search"]').on('keyup', function() {
        table.search(this.value).draw();
    });

    // Initialize Select2
    if ($.fn.select2) {
        $('#filter_status').select2({ placeholder: 'Semua Status', allowClear: true });
        $('#filter_position').select2({ placeholder: 'Semua Posisi', allowClear: true });
    }

    // Apply Filter
    $('#filter_apply').on('click', function() {
        table.draw();
    });

    // Reset Filter
    $('#filter_reset').on('click', function() {
        $('#filter_status').val(null).trigger('change');
        $('#filter_position').val(null).trigger('change');
        $('input[name="start_date"]').val('');
        $('input[name="end_date"]').val('');
        table.draw();
    });

    // Helper: Format Destination
    function formatDestination(arrivalCity, count) {
        if (!arrivalCity) return '-';
        if (count && count > 1) {
            return arrivalCity + '<br><span class="text-muted fs-7">dan ' + (count - 1) + ' Destinasi lain</span>';
        }
        return arrivalCity;
    }

    // Helper: Format Status
    function formatStatus(row) {
        // IMPORTANT: The backend returns key 'STATUS' uppercase, but DataTables maps lower case 'status' 
        // We set 'data': 'status' (DB: STATUS) in columns config. 
        // Row object passed here is the full data row.
        
        var statusKey = row.status || row.STATUS; 
        
        var statusClass = 'badge-light-secondary';
        var statusText = statusKey || 'Unknown';
        var statusTooltip = '';

        if (statusConfig[statusKey]) {
            statusClass = statusConfig[statusKey].class;
            statusText = statusConfig[statusKey].text;
            statusTooltip = statusConfig[statusKey].tooltip;
        } else {
             switch (statusKey) {
                case 'PENDING': statusClass = 'badge-light-warning'; statusText = 'Menunggu Persetujuan'; break;
                case 'SUBMITTED': statusClass = 'badge-light-info'; statusText = 'Diajukan'; break;
                case 'PRE_APPROVED': statusClass = 'badge-light-success'; statusText = 'Disetujui'; break;
                case 'PRE_REJECTED': statusClass = 'badge-light-danger'; statusText = 'Ditolak'; break;
                case 'APPROVED': statusClass = 'badge-light-info'; statusText = 'Disetujui'; break;
                case 'REJECTED': statusClass = 'badge-light-danger'; statusText = 'Ditolak'; break;
                case 'PARTIALLY_APPROVED': statusClass = 'badge-light-info'; statusText = 'Sebagian Disetujui'; break;
                case 'VERIFICATOR_APPROVED': statusClass = 'badge-light-info'; statusText = 'Menunggu Pembayaran'; break;
                case 'VERIFICATOR_REVISION': statusClass = 'badge-light-primary'; statusText = 'Revisi Verifikator'; break;
                case 'PAYMENT_APPROVER_APPROVED': statusClass = 'badge-light-success'; statusText = 'Disetujui Pembayaran'; break;
                case 'PAYMENT_APPROVER_REVISION': statusClass = 'badge-light-primary'; statusText = 'Revisi Payment'; break;
                case 'UNPAID': statusClass = 'badge-light-warning'; statusText = 'Belum Dibayar'; break; 
                case 'PAID': statusClass = 'badge-light-success'; statusText = 'Sudah Dibayar'; break;
                case 'CANCELED': statusClass = 'badge-light-secondary'; statusText = 'Dibatalkan'; break;
             }
        }
        return '<span class="badge ' + statusClass + '" title="' + (statusTooltip || '') + '">' + statusText + '</span>';
    }

    // Update statistics cards
    function updateStatisticsCards(stats) {
        if (!stats) return;
        // Logic to update cards if IDs are present in View...
        // Assuming cards have data-kt-countup attributes or similar or specific IDs
        // For now, if user hasn't asked to fix stats update specifically with server side, 
        // we keep the function structure.
    }
    
    // Custom search
    $('[data-kt-filter="search"]').on('keyup', function() {
        table.search(this.value).draw();
    });

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

    // updateTable function is no longer needed with serverSide processing
    // function updateTable(travel_requests) { ... }

    // Apply filter function
    function applyFilter() {
        var positions = $('#filter_position').val();
        var startDate = $('#filter_start_date').val();
        var endDate = $('#filter_end_date').val();

        // Show loading
        $('#btn_apply_filter').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Loading...');

        $.ajax({
            url: appUrl + 'secretary/travel_request/list_ajax',
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
                    updateTable(response.travel_requests);
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

    // Bind filter buttons
    $('#btn_apply_filter').on('click', applyFilter);
    $('#btn_reset_filter').on('click', resetFilter);

    // Mark as Paid handler
    $(document).on('click', '.btn-mark-paid', function(e) {
        e.preventDefault();
        var uuid = $(this).data('uuid');
        
        Swal.fire({
            title: 'Konfirmasi Pembayaran',
            text: 'Apakah Anda yakin ingin menandai pengajuan ini sebagai sudah dibayar?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#50cd89',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Tandai Sudah Dibayar',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                $.ajax({
                    url: appUrl + 'api/travel_request/mark_as_paid',
                    type: 'POST',
                    data: { id: uuid },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message,
                                confirmButtonText: 'OK'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: response.message || 'Terjadi kesalahan saat memproses permintaan'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        var errorMessage = 'Terjadi kesalahan saat memproses permintaan';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMessage
                        });
                    }
                });
            }
        });
    });


    // Handle download click validation
    $(document).on('click', '.btn-action-download', function(e) {
        var isReady = $(this).data('document-ready');
        // Check strict equality to false or string 'false' depending on how jQuery parses it
        if (isReady === false || isReady === 'false') {
            e.preventDefault();
            Swal.fire({
                icon: "error",
                title: "Oops...",
                text: "Dokumen belum siap!",
            });
        }
    });
});