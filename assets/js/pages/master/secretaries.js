// Admin Division Assignment Page JavaScript
(function () {
	function initSecretaries() {
		// Initialize DataTable
		var table = $("#kt_table_secretaries").DataTable({
			info: false,
			order: [],
			pageLength: 10,
			language: {
				search: "",
				searchPlaceholder: "Cari...",
				lengthMenu: "Tampilkan _MENU_ data",
				zeroRecords: "Data tidak ditemukan",
				paginate: {
					previous: "Sebelumnya",
					next: "Selanjutnya",
				},
			},
		});

		// Search functionality
		$('[data-kt-table-filter="search"]').on("keyup", function () {
			table.search($(this).val()).draw();
		});

		// Cascading checkbox logic
		$(document).on("change", ".position-checkbox", function () {
			var isChecked = $(this).is(":checked");
			var positionId = $(this).val();

			// Find all children checkboxes
			var $children = $('input.position-checkbox[data-parent-id="' + positionId + '"]');
			
			// Cascade to children
			$children.each(function() {
				$(this).prop("checked", isChecked);
				// Trigger change for nested children
				$(this).trigger("cascadeCheck");
			});
		});

		// Custom event for cascade without triggering infinite loop
		$(document).on("cascadeCheck", ".position-checkbox", function () {
			var isChecked = $(this).is(":checked");
			var positionId = $(this).val();

			var $children = $('input.position-checkbox[data-parent-id="' + positionId + '"]');
			$children.each(function() {
				$(this).prop("checked", isChecked);
				$(this).trigger("cascadeCheck");
			});
		});

		// Form submit for assigning positions
		$("#form_assign_admin").on("submit", function (e) {
			e.preventDefault();
			
			var adminId = $("#admin_select").val();
			if (!adminId) {
				Swal.fire({
					icon: "warning",
					title: "Perhatian",
					text: "Silakan pilih Admin terlebih dahulu",
				});
				return;
			}

			var positionIds = [];
			$(".position-checkbox:checked").each(function () {
				positionIds.push($(this).val());
			});

			$.ajax({
				url: appUrl + "master/secretaries/ajax",
				type: "POST",
				data: {
					admin_id: adminId,
					position_ids: positionIds,
				},
				success: function (response) {
					if (response.success) {
						Swal.fire({
							icon: "success",
							title: "Berhasil",
							text: response.message,
						}).then(function () {
							window.location.reload();
						});
					} else {
						Swal.fire({
							icon: "error",
							title: "Gagal",
							text: response.message,
						});
					}
				},
				error: function () {
					Swal.fire({
						icon: "error",
						title: "Error",
						text: "Terjadi kesalahan saat menyimpan data",
					});
				},
			});
		});

		// Edit assignment
		$(document).on("click", ".btn-edit-assignment", function () {
			var adminId = $(this).data("admin-id");
			var adminName = $(this).data("admin-name");

			// Set admin in select
			$("#admin_select").val(adminId).trigger("change");
			$("#edit_mode").val("1");
			$("#modal_assign_admin .modal-title").text("Edit Assignment: " + adminName);

			// Uncheck all first
			$(".position-checkbox").prop("checked", false);

			// Load current positions
			$.ajax({
				url: appUrl + "master/secretaries/ajax",
				type: "GET",
				data: { admin_id: adminId },
				success: function (response) {
					if (response.success) {
						response.data.forEach(function (posId) {
							$("#pos_" + posId).prop("checked", true);
						});
					}
					$("#modal_assign_admin").modal("show");
				},
				error: function () {
					Swal.fire({
						icon: "error",
						title: "Error",
						text: "Gagal memuat data posisi",
					});
				},
			});
		});

		// Delete assignment
		$(document).on("click", ".btn-delete-assignment", function () {
			var adminId = $(this).data("admin-id");
			var adminName = $(this).data("admin-name");

			Swal.fire({
				icon: "warning",
				title: "Hapus Assignment?",
				html: 'Anda yakin ingin menghapus semua assignment untuk <strong>' + adminName + '</strong>?',
				showCancelButton: true,
				confirmButtonText: "Ya, Hapus",
				cancelButtonText: "Batal",
				confirmButtonColor: "#dc3545",
			}).then(function (result) {
				if (result.isConfirmed) {
					$.ajax({
						url: appUrl + "master/secretaries/ajax",
						type: "DELETE",
						data: { admin_id: adminId },
						success: function (response) {
							if (response.success) {
								Swal.fire({
									icon: "success",
									title: "Berhasil",
									text: response.message,
								}).then(function () {
									window.location.reload();
								});
							} else {
								Swal.fire({
									icon: "error",
									title: "Gagal",
									text: response.message,
								});
							}
						},
						error: function () {
							Swal.fire({
								icon: "error",
								title: "Error",
								text: "Terjadi kesalahan saat menghapus data",
							});
						},
					});
				}
			});
		});

		// Reset modal when closed
		$("#modal_assign_admin").on("hidden.bs.modal", function () {
			$("#admin_select").val("").trigger("change");
			$(".position-checkbox").prop("checked", false);
			$("#edit_mode").val("0");
			$("#modal_assign_admin .modal-title").text("Assign Admin ke Divisi");
		});
	}

	// Check if jQuery is available
	if (typeof window.jQuery !== "undefined") {
		$(document).ready(function () {
			initSecretaries();
		});
	} else if (typeof window.onJQueryReady !== "undefined") {
		window.onJQueryReady.push(function () {
			$(document).ready(function () {
				initSecretaries();
			});
		});
	} else {
		var checkInterval = setInterval(function () {
			if (typeof window.jQuery !== "undefined") {
				clearInterval(checkInterval);
				$(document).ready(function () {
					initSecretaries();
				});
			}
		}, 50);
	}
})();
