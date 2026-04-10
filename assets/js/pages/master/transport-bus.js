"use strict";

$(document).ready(function () {

    Inputmask("numeric", {
        // Mask untuk numeric/currency
        radixPoint: ",",      // Menggunakan koma sebagai pemisah desimal
        groupSeparator: ".",   // Menggunakan titik sebagai pemisah ribuan
        alias: "numeric",
        placeholder: "0",
        autoGroup: true,       // Secara otomatis mengelompokkan (ribuan)
        digits: 0,             // 2 angka di belakang koma (seperti ,00)
        digitsOptional: false, // Memastikan 2 angka desimal selalu ada
        positionCaretOnClick: "radixFocus",
        numericInput: true,
        allowMinus: false,
		autoUnmask: true,
    }).mask(".rupiah-input");

	// on submit
	$("#form-bluk-update").on("click", function (e) {
		e.preventDefault();
		// get form data
		let groups = [];
		const $inputs = $('input[name="group_id[]"]');
		if ($inputs.length === 1) {
			const raw = $inputs.val();
			try {
				const parsed = JSON.parse(raw);
				groups = Array.isArray(parsed) ? parsed : [];
			} catch (err) {
				groups = (raw || "")
					.split(",")
					.map((v) => v.trim())
					.filter((v) => v.length > 0);
			}
		} else {
			groups = $inputs
				.map((_, el) => el.value)
				.get()
				.filter((v) => v && v.length > 0);
		}

		const transportData = groups.map((group) => {
			const $fixed = $(`input[name='fixed_amount_${group}']`);
			const $max = $(`input[name='maximum_amount_${group}']`);
			const toNumber = (v) => {
				const n = Number(v);
				return Number.isFinite(n) ? n : 0;
			};
			return {
				group_id: group,
				id: $fixed.data("transport-id") ?? $max.data("transport-id") ?? null,
				transport_type:
					$fixed.data("transport-type") ?? $max.data("transport-type") ?? null,
				old_fixed_amount: toNumber($fixed.data("old-value")),
				old_maximum_amount: toNumber($max.data("old-value")),
				new_fixed_amount: toNumber($fixed.val()),
				new_maximum_amount: toNumber($max.val()),
			};
		});

		// ajax
		$.ajax({
			url: appUrl + "master/transport/ajax/bulk_update",
			type: "POST",
			data: {
				transport_data: JSON.stringify(transportData),
			},
			success: function (response) {
				// success
				if (response.success) {
					// show success message
					Swal.fire({
						icon: "success",
						title: "Sukses",
						text: `${response.updated} data transportasi diupdate`,
					}).then(() => {
						// reload table
						window.location.reload();
					});
				} else {
					// show error message
					Swal.fire({
						icon: "error",
						title: "Gagal",
						text: "Tidak ada data yang diupdate",
					});
				}
				console.log(response);
			},
			error: function (response) {
				// error
				console.log(response);
			},
		});
	});

	// ========================================
	// SPECIAL RATE CRUD HANDLERS
	// ========================================
	
	var transportTypeId = $('#transport_type_id').val();
	var modal = $('#modalAddRate').length ? new bootstrap.Modal(document.getElementById('modalAddRate')) : null;
	var form = $('#formSpecialRate');

	// Init Select2 for province dropdowns
	$('#rate_departures, #rate_arrivals').select2({
		dropdownParent: $('#modalAddRate'),
		allowClear: true
	});

	// Money input formatter
	$('.money-input').on('keyup', function () {
		var value = $(this).val().replace(/\D/g, '');
		if (value) {
			$(this).val('Rp ' + parseInt(value).toLocaleString('id-ID'));
		}
	});

	function parseMoneyValue(value) {
		if (!value) return null;
		return parseInt(value.replace(/\D/g, '')) || null;
	}

	function formatMoney(value) {
		if (!value) return '';
		return 'Rp ' + parseInt(value).toLocaleString('id-ID');
	}

	function resetForm() {
		form[0].reset();
		$('#rate_id').val('');
		$('#rate_departures').val([]).trigger('change');
		$('#rate_arrivals').val([]).trigger('change');
		$('#modalTitle').text('Tambah Tarif Khusus');
	}

	// Handle form submit
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

	// Handle edit button
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

	// Handle delete button
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

	// Reset form on modal close
	$('#modalAddRate').on('hidden.bs.modal', function () {
		resetForm();
	});
});
