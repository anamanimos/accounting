$(document).ready(function () {
	// Handle generate claims button
	$(document).on("click", "#btn-generate-claims", function (e) {
		e.preventDefault();

		var travelRequestId = $(this).data("travel-request-id");
		var btn = $(this);

		// Show loading state
		btn.prop("disabled", true);
		btn.html(
			'<span class="spinner-border spinner-border-sm me-2"></span>Loading...'
		);

		// Hit endpoint to generate claims
		$.ajax({
			url: "/secretary/generate_claims/" + travelRequestId,
			type: "GET",
			dataType: "json",
			success: function (response) {
				if (response.success) {
					Swal.fire({
						icon: "success",
						title: "Sukses",
						text: response.message + " (" + response.generated_count + " item)",
						buttonsStyling: false,
						confirmButtonText: "OK",
						customClass: {
							confirmButton: "btn btn-primary",
						},
					}).then(() => {
						location.reload();
					});
				} else {
					Swal.fire({
						icon: "error",
						title: "Error",
						text: response.message,
						buttonsStyling: false,
						confirmButtonText: "OK",
						customClass: {
							confirmButton: "btn btn-primary",
						},
					});
					btn.prop("disabled", false);
					btn.html(
						'<i class="ki-outline ki-plus-square fs-2"></i> Input Rincian Keuangan'
					);
				}
			},
			error: function (xhr, status, error) {
				var errorMsg = "Terjadi kesalahan";
				if (xhr.responseJSON && xhr.responseJSON.message) {
					errorMsg = xhr.responseJSON.message;
				}

				Swal.fire({
					icon: "error",
					title: "Error",
					text: errorMsg,
					buttonsStyling: false,
					confirmButtonText: "OK",
					customClass: {
						confirmButton: "btn btn-primary",
					},
				});
				btn.prop("disabled", false);
				btn.html(
					'<i class="ki-outline ki-plus-square fs-2"></i> Input Rincian Keuangan'
				);
			},
		});
	});

	// Initialize repeater for expense items
	$("#kt_expense_repeater").repeater({
		initEmpty: false,

		show: function () {
//			var $item = $(this);
			$(this).slideDown();

            // Enable inputs in the cloned item
            $(this).find('input, select, textarea, button').prop('disabled', false);

            // Clear values (in case it cloned a filled row)
            $(this).find('input[type="text"]').val('');
            $(this).find('input[type="number"]').val('');
            $(this).find('input[type="file"]').val('');
            $(this).find('input[name$="[id]"]').val('');
            $(this).find('input[name$="[proof_file]"]').val('');
            $(this).find('input[name$="[proof_file_original]"]').val('');

            // Reset Select2 and set default to OTHER if possible, or just re-init
            var select = $(this).find('[data-kt-repeater="select2"]');
            select.val('OTHER'); // Set default category
            select.select2();

            // Reset Total
            $(this).find('[data-total]').text('Rp. -');

            // Hide Upload/View buttons state (reset to initial)
             var uploadBtn = $(this).find('.btn-upload-proof');
             var viewBtn = $(this).find('.btn-view-proof');
             
             // Reset upload button to "Unggah Bukti"
             uploadBtn.removeClass('btn-light-primary btn-icon')
                      .addClass('btn-primary text-nowrap')
                      .html('Unggah Bukti')
                      .removeAttr('title')
                      .removeAttr('data-bs-toggle')
                      .removeAttr('data-bs-original-title'); // Bootstrap tooltip attr
             
             // Hide view button
             viewBtn.addClass('d-none').attr('href', '#');
             
            // Re-init tooltips
			$('[data-bs-toggle="tooltip"]').tooltip();
		},

		hide: function (deleteElement) {
			Swal.fire({
				text: "Apakah Anda yakin ingin menghapus item ini?",
				icon: "warning",
				buttonsStyling: false,
				showCancelButton: true,
				confirmButtonText: "Hapus",
				cancelButtonText: "Batal",
				customClass: {
					confirmButton: "btn btn-danger",
					cancelButton: "btn btn-secondary",
				},
			}).then((result) => {
				if (result.isConfirmed) {
					$(this).slideUp(deleteElement);
				}
			});
		},

		ready: function () {
			// Init tooltips
			$('[data-bs-toggle="tooltip"]').tooltip();
		},

		isFirstItemUndeletable: false,
	});

	// Format currency & Calculate Total on amount or qty change
	$(document).on("change keyup", 'input[name*="[amount]"], input[name*="[qty]"]', function () {
		var row = $(this).closest("tr");
		var amountInput = row.find('input[name*="[amount]"]');
		var qtyInput = row.find('input[name*="[qty]"]');
		
		var amountStr = amountInput.val().replace(/\./g, "").replace(/,/g, ".");
		var amount = parseFloat(amountStr) || 0;
		var qty = parseFloat(qtyInput.val()) || 0;
		
		var total = amount * qty;
		
		if (total > 0) {
			var formatted = new Intl.NumberFormat("id-ID", {
				style: "currency",
				currency: "IDR",
				minimumFractionDigits: 0,
			}).format(total);
			row.find("[data-total]").text(formatted);
		} else {
             row.find("[data-total]").text("Rp 0");
        }
        
        // Also re-format the amount input to show dots if it was the trigger
        // But be careful not to mess up cursor position if it's keyup on amount
        // ideally masked input handles this. implementing simple dot for now if change event.
        if (event.type === 'change' && $(this).is(amountInput)) {
             // Re-format amount input for display
             // logic mostly handled by mask or simple formatter
        }
	});
});
