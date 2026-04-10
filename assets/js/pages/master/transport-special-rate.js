"use strict";

var KTSpecialRatesCRUD = function () {
    var table;
    var modal;
    var form;
    var transportTypeId;

    var initTable = function () {
        table = $('#kt_special_rates_table');
        
        // Init datatable
        if (table.length && table.find('tbody tr').length > 1) {
            table.DataTable({
                info: false,
                order: [],
                pageLength: 10,
                language: {
                    search: "",
                    searchPlaceholder: "Cari...",
                    lengthMenu: "Tampilkan _MENU_ data",
                    zeroRecords: "Tidak ada data yang ditemukan",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Selanjutnya",
                        previous: "Sebelumnya"
                    }
                }
            });
        }
    };

    var initSelect2 = function () {
        $('#rate_departures, #rate_arrivals').select2({
            dropdownParent: $('#modalAddRate'),
            allowClear: true
        });
    };

    var initMoneyInput = function () {
        $('.money-input').on('keyup', function () {
            var value = $(this).val().replace(/\D/g, '');
            if (value) {
                $(this).val('Rp ' + parseInt(value).toLocaleString('id-ID'));
            }
        });
    };

    var parseMoneyValue = function (value) {
        if (!value) return null;
        return parseInt(value.replace(/\D/g, '')) || null;
    };

    var formatMoney = function (value) {
        if (!value) return '';
        return 'Rp ' + parseInt(value).toLocaleString('id-ID');
    };

    var resetForm = function () {
        form[0].reset();
        $('#rate_id').val('');
        $('#rate_departures').val([]).trigger('change');
        $('#rate_arrivals').val([]).trigger('change');
        $('#modalTitle').text('Tambah Tarif Khusus');
    };

    var handleFormSubmit = function () {
        form.on('submit', function (e) {
            e.preventDefault();

            var btn = $('#btnSaveRate');
            btn.attr('data-kt-indicator', 'on');
            btn.prop('disabled', true);

            var departures = [];
            $('#rate_departures').val().forEach(function (id) {
                departures.push({ location_type: 'PROVINCE', location_id: id });
            });

            var arrivals = [];
            $('#rate_arrivals').val().forEach(function (id) {
                arrivals.push({ location_type: 'PROVINCE', location_id: id });
            });

            var data = {
                id: $('#rate_id').val(),
                transport_type_id: transportTypeId,
                group_id: $('#rate_group_id').val(),
                name: $('#rate_name').val(),
                description: $('#rate_description').val(),
                fixed_amount: parseMoneyValue($('#rate_fixed_amount').val()),
                maximum_amount: parseMoneyValue($('#rate_maximum_amount').val()),
                departures: JSON.stringify(departures),
                arrivals: JSON.stringify(arrivals)
            };

            var url = data.id ? BASE_URL + 'master_transport/ajax_update_special_rate' : BASE_URL + 'master_transport/ajax_create_special_rate';

            $.ajax({
                url: url,
                type: 'POST',
                data: data,
                dataType: 'json',
                success: function (response) {
                    btn.removeAttr('data-kt-indicator');
                    btn.prop('disabled', false);

                    if (response.success) {
                        Swal.fire({
                            text: data.id ? "Tarif khusus berhasil diperbarui!" : "Tarif khusus berhasil ditambahkan!",
                            icon: "success",
                            confirmButtonText: "Ok"
                        }).then(function () {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            text: response.message || "Terjadi kesalahan",
                            icon: "error",
                            confirmButtonText: "Ok"
                        });
                    }
                },
                error: function () {
                    btn.removeAttr('data-kt-indicator');
                    btn.prop('disabled', false);
                    Swal.fire({
                        text: "Terjadi kesalahan pada server",
                        icon: "error",
                        confirmButtonText: "Ok"
                    });
                }
            });
        });
    };

    var handleEdit = function () {
        $(document).on('click', '.btn-edit', function (e) {
            e.preventDefault();
            var id = $(this).data('id');

            $.ajax({
                url: BASE_URL + 'master_transport/ajax_get_special_rate/' + id,
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    if (response.success && response.data) {
                        var data = response.data;
                        
                        $('#rate_id').val(data.ID);
                        $('#rate_name').val(data.NAME);
                        $('#rate_description').val(data.DESCRIPTION);
                        $('#rate_group_id').val(data.GROUP_ID);
                        $('#rate_fixed_amount').val(formatMoney(data.FIXED_AMOUNT));
                        $('#rate_maximum_amount').val(formatMoney(data.MAXIMUM_AMOUNT));
                        
                        // Set departures
                        var depIds = data.departures.map(function (d) { return d.LOCATION_ID; });
                        $('#rate_departures').val(depIds).trigger('change');
                        
                        // Set arrivals
                        var arrIds = data.arrivals.map(function (a) { return a.LOCATION_ID; });
                        $('#rate_arrivals').val(arrIds).trigger('change');
                        
                        $('#modalTitle').text('Edit Tarif Khusus');
                        modal.show();
                    } else {
                        Swal.fire({
                            text: "Gagal memuat data tarif",
                            icon: "error",
                            confirmButtonText: "Ok"
                        });
                    }
                }
            });
        });
    };

    var handleDelete = function () {
        $(document).on('click', '.btn-delete', function (e) {
            e.preventDefault();
            var id = $(this).data('id');

            Swal.fire({
                text: "Apakah Anda yakin ingin menghapus tarif ini?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Ya, Hapus!",
                cancelButtonText: "Batal"
            }).then(function (result) {
                if (result.isConfirmed) {
                    $.ajax({
                        url: BASE_URL + 'master_transport/ajax_delete_special_rate',
                        type: 'POST',
                        data: { id: id },
                        dataType: 'json',
                        success: function (response) {
                            if (response.success) {
                                Swal.fire({
                                    text: "Tarif khusus berhasil dihapus!",
                                    icon: "success",
                                    confirmButtonText: "Ok"
                                }).then(function () {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    text: response.message || "Gagal menghapus tarif",
                                    icon: "error",
                                    confirmButtonText: "Ok"
                                });
                            }
                        }
                    });
                }
            });
        });
    };

    var handleModalReset = function () {
        $('#modalAddRate').on('hidden.bs.modal', function () {
            resetForm();
        });
    };

    return {
        init: function () {
            transportTypeId = $('#transport_type_id').val();
            modal = new bootstrap.Modal(document.getElementById('modalAddRate'));
            form = $('#formSpecialRate');

            initTable();
            initSelect2();
            initMoneyInput();
            handleFormSubmit();
            handleEdit();
            handleDelete();
            handleModalReset();
        }
    };
}();

KTUtil.onDOMContentLoaded(function () {
    KTSpecialRatesCRUD.init();
});
