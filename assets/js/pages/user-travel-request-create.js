"use strict";

// Class definition
var KTUserTravelRequestCreate = (function () {
	// Private variables
	var form;
	var submitButton;
	var repeater;

	// Private functions
	var initRepeater = function () {
		repeater = $("#kt_docs_repeater_advanced").repeater({
			initEmpty: false,

			defaultValues: {
				departure_city_code: "",
				arrival_city_code: "",
				transport_type_id: "",
				departure_date: "",
				return_date: "",
			},

			show: function () {
				$(this).slideDown();

				// Re-init select2 for new row
				$(this)
					.find('[data-kt-repeater="select2"]')
					.each(function () {
						$(this).select2({
							placeholder: $(this).data("placeholder"),
						});
					});

				// Re-init datepicker for new row
				$(this)
					.find('[data-kt-repeater="datepicker"]')
					.each(function () {
						$(this).flatpickr({
							dateFormat: "Y-m-d",
						});
					});

				// Init file preview for new row
				$(this)
					.find(".file-upload-input")
					.each(function () {
						initFilePreview(this);
					});
			},

			hide: function (deleteElement) {
				$(this).slideUp(deleteElement);
			},

			ready: function (setIndexes) {
				// Init select2 for existing rows
				$('[data-kt-repeater="select2"]').each(function () {
					$(this).select2({
						placeholder: $(this).data("placeholder"),
					});
				});

				// Init datepicker for existing rows
				$('[data-kt-repeater="datepicker"]').each(function () {
					$(this).flatpickr({
						dateFormat: "Y-m-d",
					});
				});

				// Init file preview for existing rows
				$(".file-upload-input").each(function () {
					initFilePreview(this);
				});
			},
		});
	};

	// File preview function
	var initFilePreview = function (input) {
		$(input).on("change", function () {
			var previewContainer = $(this).siblings(".file-preview");
			previewContainer.empty();

			if (this.files && this.files.length > 0) {
				var fileList = $('<div class="d-flex flex-wrap gap-2"></div>');

				for (var i = 0; i < this.files.length; i++) {
					var file = this.files[i];
					var fileSize = (file.size / 1024).toFixed(1) + " KB";
					if (file.size > 1024 * 1024) {
						fileSize = (file.size / (1024 * 1024)).toFixed(2) + " MB";
					}

					var badge = $(
						'<span class="badge badge-light-primary fs-7 p-2">' +
							'<i class="ki-duotone ki-file fs-6 me-1"><span class="path1"></span><span class="path2"></span></i>' +
							file.name +
							" (" +
							fileSize +
							")" +
							"</span>"
					);
					fileList.append(badge);
				}

				previewContainer.append(fileList);
				previewContainer.append(
					'<small class="text-success d-block mt-1">' +
						this.files.length +
						" file dipilih</small>"
				);
			}
		});
	};

	var handleSubmit = function () {
		submitButton = document.querySelector(".btn-submit");

		if (!submitButton) return;

		submitButton.addEventListener("click", function (e) {
			e.preventDefault();

			// Validate form
			var travelType = document.querySelector('[name="travel_type"]').value;
			var preApprover = document.querySelector('[name="pre_approver"]').value;
			var postApprover = document.querySelector('[name="post_approver"]').value;

			if (!travelType) {
				Swal.fire({
					icon: "warning",
					title: "Perhatian",
					text: "Pilih jenis perjalanan terlebih dahulu",
				});
				return;
			}

			if (!preApprover || !postApprover) {
				Swal.fire({
					icon: "warning",
					title: "Perhatian",
					text: "Pilih pemberi persetujuan terlebih dahulu",
				});
				return;
			}

			// Get repeater data
			var repeaterData = repeater.repeaterVal();
			var destinations = repeaterData.kt_docs_repeater_advanced || [];

			if (destinations.length === 0) {
				Swal.fire({
					icon: "warning",
					title: "Perhatian",
					text: "Tambahkan minimal satu destinasi perjalanan",
				});
				return;
			}

			// Validate each destination
			var isValid = true;
			destinations.forEach(function (dest, index) {
				if (
					!dest.departure_city_code ||
					!dest.arrival_city_code ||
					!dest.transport_type_id ||
					!dest.departure_date ||
					!dest.return_date
				) {
					isValid = false;
				}
			});

			if (!isValid) {
				Swal.fire({
					icon: "warning",
					title: "Perhatian",
					text: "Lengkapi semua data destinasi perjalanan",
				});
				return;
			}

			// Prepare form data
			var formData = new FormData();
			formData.append("travel_type", travelType);
			formData.append(
				"employee_id",
				document.querySelector('[name="employee_id"]').value
			);
			formData.append(
				"created_by",
				document.querySelector('[name="created_by"]').value
			);
			formData.append("approver1_id", preApprover);
			formData.append("approver2_id", postApprover);
			formData.append(
				"note",
				document.querySelector('[name="note"]').value || ""
			);

			// Add destinations as array format expected by API
			destinations.forEach(function (dest, index) {
				formData.append(
					"destination[" + index + "][departure_city_code]",
					dest.departure_city_code
				);
				formData.append(
					"destination[" + index + "][arrival_city_code]",
					dest.arrival_city_code
				);
				formData.append(
					"destination[" + index + "][transport_type_id]",
					dest.transport_type_id
				);
				formData.append(
					"destination[" + index + "][departure_date]",
					dest.departure_date
				);
				formData.append(
					"destination[" + index + "][return_date]",
					dest.return_date
				);
			});

			// Handle file uploads - format: file[destination_index][]
			var repeaterItems = document.querySelectorAll("[data-repeater-item]");
			repeaterItems.forEach(function (item, index) {
				// Find file input within repeater item
				var fileInput = item.querySelector('input[type="file"]');

				if (fileInput && fileInput.files && fileInput.files.length > 0) {
					for (var i = 0; i < fileInput.files.length; i++) {
						formData.append("file[" + index + "][]", fileInput.files[i]);
					}
				}
			});

			// Show loading
			submitButton.setAttribute("data-kt-indicator", "on");
			submitButton.disabled = true;

			// Submit via AJAX
			$.ajax({
				url: BASE_URL + "api/travel_request/create",
				type: "POST",
				data: formData,
				processData: false,
				contentType: false,
				success: function (response) {
					submitButton.removeAttribute("data-kt-indicator");
					submitButton.disabled = false;

					if (response.success) {
						Swal.fire({
							icon: "success",
							title: "Berhasil",
							text:
								response.message ||
								"Pengajuan perjalanan dinas berhasil dibuat",
							confirmButtonText: "OK",
						}).then(function () {
							window.location.href = BASE_URL + "user/travel_request";
						});
					} else {
						Swal.fire({
							icon: "error",
							title: "Gagal",
							text: response.message || "Terjadi kesalahan saat menyimpan data",
						});
					}
				},
				error: function (xhr) {
					submitButton.removeAttribute("data-kt-indicator");
					submitButton.disabled = false;

					var message = "Terjadi kesalahan pada server";
					if (xhr.responseJSON && xhr.responseJSON.message) {
						message = xhr.responseJSON.message;
					}

					Swal.fire({
						icon: "error",
						title: "Error",
						text: message,
					});
				},
			});
		});
	};

	// Public methods
	return {
		init: function () {
			form = document.querySelector("form");

			if (!form) {
				return;
			}

			initRepeater();
			handleSubmit();
		},
	};
})();

// On document ready
KTUtil.onDOMContentLoaded(function () {
	KTUserTravelRequestCreate.init();
});
