"use strict";

// Class definition
var KTSecretaryTravelRequestCreate = (function () {
	// Private variables
	var form;
	var submitButton;
	var destinationTemplate;
	var destinationIndex = 0;

	// Helper to init file preview
	var initFilePreview = function(item) {
		var input = item.querySelector('.file-upload-input');
		if (!input) return;
		
		$(input).on("change", function () {
			var previewContainer = item.querySelector(".file-preview");
			$(previewContainer).empty();

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

				$(previewContainer).append(fileList);
				$(previewContainer).append(
					'<small class="text-success d-block mt-1">' +
						this.files.length +
						" file dipilih</small>"
				);
			}
		});
	};

	// Helper to init plugins on a specific item
	var initPlugins = function(item) {
		// Init Select2
		$(item).find('select').each(function() {
			$(this).select2({
				placeholder: $(this).find('option:first').text(),
				allowClear: true,
				width: '100%' // Ensure full width
			});
		});

		// Init Flatpickr
		$(item).find('input[placeholder="Pilih Tanggal"]').flatpickr({
			dateFormat: "Y-m-d",
		});
		
		// Init File Preview
		initFilePreview(item);
	};

	// Helper to destroy plugins on a specific item (for clean cloning)
	var destroyPlugins = function(item) {
		// Destroy Select2
		$(item).find('select').each(function() {
			if ($(this).hasClass("select2-hidden-accessible")) {
				$(this).select2('destroy');
			}
		});
	};

	// Initialize destination repeater manually
	var initDestinations = function () {
		var container = document.getElementById('destinations_container');
		var addButton = document.getElementById('btn_add_destination');
		
		// Get the first item
		var firstItem = container.querySelector('.destination-item');
		
		// Create a clean template from the first item BEFORE initializing plugins
		destinationTemplate = firstItem.cloneNode(true);
		
		// Now initialize plugins on the existing first item
		initPlugins(firstItem);
		
		// Add click handler for add button
		addButton.addEventListener('click', function() {
			addDestination();
		});
		
		// Delegate click handler for remove buttons
		container.addEventListener('click', function(e) {
			// Handle remove button click (including icon click)
			var target = e.target;
			var removeBtn = target.closest('.btn-remove-destination');
			
			if (removeBtn) {
				removeDestination(removeBtn.closest('.destination-item'));
			}
		});
		
		
		// Update remove button visibility
		updateRemoveButtons();
	};
	
	// Add new destination
	var addDestination = function() {
		destinationIndex++;
		var newItem = destinationTemplate.cloneNode(true);
		newItem.setAttribute('data-index', destinationIndex);
		
		// Clear all values
		newItem.querySelectorAll('select').forEach(function(select) {
			select.selectedIndex = 0;
		});
		newItem.querySelectorAll('input').forEach(function(input) {
			input.value = '';
		});
		newItem.querySelectorAll('.file-preview').forEach(function(div) {
			div.innerHTML = '';
		});
		
		// Append FIRST, then initialize
		newItem.style.display = 'none';
		document.getElementById('destinations_container').appendChild(newItem);
		
		// Initialize plugins on the new item
		initPlugins(newItem);
		
		$(newItem).slideDown(300);
		
		updateRemoveButtons();
	};
	
	// Remove destination
	var removeDestination = function(item) {
		var items = document.querySelectorAll('.destination-item');
		if (items.length > 1) {
			$(item).slideUp(300, function() {
				// Destroy plugins before removing
				destroyPlugins(item);
				item.remove();
				updateRemoveButtons();
			});
		} else {
			Swal.fire({
				icon: 'warning',
				title: 'Perhatian',
				text: 'Minimal harus ada satu destinasi perjalanan'
			});
		}
	};
	
	// Update remove button visibility
	var updateRemoveButtons = function() {
		var items = document.querySelectorAll('.destination-item');
		items.forEach(function(item) {
			var removeBtn = item.querySelector('.btn-remove-destination');
			if (items.length <= 1) {
				removeBtn.style.display = 'none';
			} else {
				removeBtn.style.display = '';
			}
		});
	};
	
	// Get all destinations data
	var getDestinations = function() {
		var destinations = [];
		var items = document.querySelectorAll('.destination-item');
		
		items.forEach(function(item) {
			destinations.push({
				departure_city_code: item.querySelector('.dest-departure-city').value,
				departure_date: item.querySelector('.dest-departure-date').value,
				transport_type_id: item.querySelector('.dest-transport').value,
				arrival_city_code: item.querySelector('.dest-arrival-city').value,
				return_date: item.querySelector('.dest-return-date').value
			});
		});
		
		return destinations;
	};

	var handleSubmit = function () {
		submitButton = document.querySelector(".btn-submit");

		if (!submitButton) return;

		submitButton.addEventListener("click", function (e) {
			e.preventDefault();

			// Validate requestor
			var requestorId = document.querySelector('[name="requestor_id"]').value;
			if (!requestorId) {
				Swal.fire({
					icon: "warning",
					title: "Perhatian",
					text: "Pilih pemohon terlebih dahulu",
				});
				return;
			}

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

			// Get destinations
			var destinations = getDestinations();

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
			formData.append("employee_id", requestorId);
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

			// Add destinations
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
			
			// Handle file uploads
			var items = document.querySelectorAll('.destination-item');
			items.forEach(function(item, index) {
				var fileInput = item.querySelector('.file-upload-input');
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
							window.location.href = BASE_URL + "secretary/travel_request";
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
			form = document.getElementById("travel_request_form");

			if (!form) {
				return;
			}

			initDestinations();
			handleSubmit();
		},
	};
})();

// On document ready
KTUtil.onDOMContentLoaded(function () {
	KTSecretaryTravelRequestCreate.init();
});
