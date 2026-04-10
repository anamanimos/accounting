"use strict";

var KTEmployeeList = function () {
    var datatable;
    var modal;
    var form;

    var initTable = function () {
        var table = document.querySelector('#kt_table_employees');
        if (!table) {
            return;
        }

        datatable = $(table).DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "/api_employee/datatable",
                "type": "GET"
            },
            "columns": [
                { 
                    data: 'FULL_NAME',
                    render: function(data, type, row) {
                        var initial = data ? data.charAt(0).toUpperCase() : '?';
                        return `
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
                                    <a href="/employee/detail/${row.REF_ID}">
                                        <div class="symbol-label fs-3 bg-light-primary text-primary">
                                            ${initial}
                                        </div>
                                    </a>
                                </div>
                                <div class="d-flex flex-column">
                                    <a href="/employee/detail/${row.REF_ID}" class="text-gray-800 text-hover-primary mb-1 fw-bold">${data}</a>
                                    <span class="text-muted fs-7">${row.REF_ID || '-'}</span>
                                </div>
                            </div>
                        `;
                    }
                },
                { 
                    data: 'POSITION_NAME',
                    render: function(data, type, row) {
                         var position = data || 'Unknown';
                         var subgroup = '';
                         if (row.SUBGROUP_NAME) {
                            subgroup = `<div class="mt-1"><span class="badge badge-light-primary fw-bold text-wrap text-start">${row.GROUP_NAME} - ${row.SUBGROUP_NAME}</span></div>`;
                         } else {
                            subgroup = `<div class="mt-1"><span class="text-muted fs-7">Sub Kelompok belum diset</span></div>`;
                         }
                         return `
                            <div class="d-flex flex-column">
                                <span class="text-gray-800 fw-bold mb-1">${position}</span>
                                ${subgroup}
                            </div>
                         `;
                    }
                },
                {
                    data: 'TOTAL_REQUESTS',
                    className: 'text-center',
                    render: function(data, type, row) {
                        var count = data ? parseInt(data) : 0;
                        var badgeClass = count > 0 ? 'badge-light-success' : 'badge-light-secondary';
                        return `<span class="badge ${badgeClass} fs-7 fw-bold">${count}</span>`;
                    }
                },
                {
                    data: null,
                    className: 'text-end',
                    orderable: false,
                    render: function(data, type, row) {
                        return `
                        <a href="#" class="btn btn-sm btn-light btn-flex btn-center btn-active-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                            Aksi
                            <i class="ki-outline ki-down fs-5 ms-1"></i>
                        </a>
                        <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-200px py-4" data-kt-menu="true">
                            <div class="menu-item px-3">
                                <a href="/employee/detail/${row.REF_ID}" class="menu-link px-3">
                                    <i class="ki-outline ki-eye fs-5 me-2"></i> Lihat Detail
                                </a>
                            </div>
                            <div class="menu-item px-3">
                                <a href="#" class="menu-link px-3 btn-edit-subgroup" 
                                   data-employee-id="${row.REF_ID}"
                                   data-employee-name="${row.FULL_NAME}"
                                   data-subgroup-id="${row.SUBGROUP_ID || ''}">
                                    <i class="ki-outline ki-people fs-5 me-2"></i> Ubah Sub Kelompok
                                </a>
                            </div>
                        </div>
                        `;
                    }
                }
            ],
            "order": [[0, 'asc']],
            "pageLength": 10,
            "createdRow": function(row, data, dataIndex) {
                 // Re-initialize menu component for dynamically created elements
                 KTMenu.init();
            },
            "drawCallback": function(settings) {
                // Re-initialize menu after each draw
                KTMenu.init();
            }
        });

        // Search
        var filterSearch = document.querySelector('[data-kt-employee-table-filter="search"]');
        if (filterSearch) {
            filterSearch.addEventListener('keyup', function (e) {
                datatable.search(e.target.value).draw();
            });
        }
    }

    var initModal = function() {
        modal = new bootstrap.Modal(document.getElementById('modal_edit_subgroup'));
        form = document.getElementById('form_edit_subgroup');
        
        // Initialize Select2 for subgroup dropdown
        $('#edit_subgroup_id').select2({
            dropdownParent: $('#modal_edit_subgroup'),
            placeholder: 'Pilih Sub Kelompok',
            allowClear: true
        });
    }

    var initEditSubgroupButton = function() {
        // Handle click on "Ubah Sub Kelompok" menu item
        $(document).on('click', '.btn-edit-subgroup', function(e) {
            e.preventDefault();
            
            var $btn = $(this);
            var employeeId = $btn.data('employee-id');
            var employeeName = $btn.data('employee-name');
            var subgroupId = $btn.data('subgroup-id');
            
            // Populate modal
            $('#edit_employee_id').val(employeeId);
            $('#edit_employee_name').text(employeeName);
            $('#edit_employee_kopeg').text(employeeId);
            $('#edit_employee_initial').text(employeeName.charAt(0).toUpperCase());
            
            // Set subgroup value
            $('#edit_subgroup_id').val(subgroupId || '').trigger('change');
            
            // Show modal
            modal.show();
        });
    }

    var initFormSubmit = function() {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            var employeeId = $('#edit_employee_id').val();
            var subgroupId = $('#edit_subgroup_id').val();
            var $submitBtn = $('#btn_save_subgroup');
            
            // Show loading indicator
            $submitBtn.attr('data-kt-indicator', 'on');
            $submitBtn.prop('disabled', true);
            
            $.ajax({
                url: '/api_employee/update_subgroup',
                method: 'POST',
                data: {
                    employee_id: employeeId,
                    subgroup_id: subgroupId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message,
                            confirmButtonText: 'OK'
                        }).then(function() {
                            // Reload page to reflect changes
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Perhatian',
                            text: response.message
                        });
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Terjadi kesalahan saat memperbarui sub kelompok'
                    });
                },
                complete: function() {
                    $submitBtn.removeAttr('data-kt-indicator');
                    $submitBtn.prop('disabled', false);
                    modal.hide();
                }
            });
        });
    }

    return {
        init: function () {
            initTable();
            initModal();
            initEditSubgroupButton();
            initFormSubmit();
        }
    };
}();

KTUtil.onDOMContentLoaded(function () {
    KTEmployeeList.init();
});
