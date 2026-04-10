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
		
		// get form data - ambil semua input minimum_amount
		const accommodationData = [];
		const $minimumInputs = $('input[name^="minimum_amount_"]');
		
		$minimumInputs.each(function() {
			const $minInput = $(this);
			const inputName = $minInput.attr('name');
			// Extract group_id from name (e.g., "minimum_amount_123" -> "123")
			const groupId = inputName.replace('minimum_amount_', '');
			
			const $maxInput = $(`input[name='maximum_amount_${groupId}']`);
			
			const toNumber = (v) => {
				const n = Number(v);
				return Number.isFinite(n) ? n : 0;
			};
			
			accommodationData.push({
				group_id: groupId,
				id: $minInput.data("accommodation-id") ?? $maxInput.data("accommodation-id") ?? 0,
				old_minimum_amount: toNumber($minInput.data("old-value")),
				old_maximum_amount: toNumber($maxInput.data("old-value")),
				new_minimum_amount: toNumber($minInput.val()),
				new_maximum_amount: toNumber($maxInput.val()),
			});
		});

		// ajax
		$.ajax({
			url: appUrl + "master/accommodations/ajax/bulk_update",
			type: "POST",
			data: {
				accommodation_data: JSON.stringify(accommodationData),
			},
			success: function (response) {
				// success
				if (response.success) {
					// show success message
					Swal.fire({
						icon: "success",
						title: "Sukses",
						text: `${response.updated} data akomodasi diupdate`,
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
});
