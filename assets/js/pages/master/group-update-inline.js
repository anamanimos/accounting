// All logic for group update page
// Uses callback mechanism to ensure jQuery is loaded before executing
(function () {
	function initGroupUpdate() {
		// Update group name
		$("#form_group_update").on("submit", function (e) {
			e.preventDefault();
			$.ajax({
				url: appUrl + "master/groups/ajax",
				type: "PUT",
				data: $(this).serialize(),
				success: function (response) {
					if (response.success) {
						Swal.fire({
							icon: "success",
							title: "Berhasil",
							text: response.message,
						});
					}
				},
				error: function (xhr, status, error) {
					Swal.fire({
						icon: "error",
						title: "Gagal",
						text: "Terjadi kesalahan saat mengupdate data",
					});
				},
			});
		});

		// Add subgroup
		$("#form_add_subgroup").on("submit", function (e) {
			e.preventDefault();
			$.ajax({
				url: appUrl + "master/groups/subgroups_ajax",
				type: "POST",
				data: $(this).serialize(),
				success: function (response) {
					if (response.success) {
						Swal.fire({
							icon: "success",
							title: "Berhasil",
							text: response.message,
						}).then(function () {
							window.location.reload();
						});
					}
				},
				error: function (xhr, status, error) {
					Swal.fire({
						icon: "error",
						title: "Gagal",
						text: "Terjadi kesalahan saat menambahkan sub kelompok",
					});
				},
			});
		});

		// Edit subgroup - open modal
		$(document).on("click", ".btn-edit-subgroup", function () {
			var id = $(this).data("id");
			var name = $(this).data("name");
			var description = $(this).data("description");

			$("#edit_subgroup_id").val(id);
			$("#edit_subgroup_name").val(name);
			$("#edit_subgroup_description").val(description);

			$("#modal_edit_subgroup").modal("show");
            
            // Fallback for Bootstrap 5 if jQuery method fails or not present
            if (!$('#modal_edit_subgroup').hasClass('show')) {
                const modalEl = document.getElementById('modal_edit_subgroup');
                if (modalEl && typeof bootstrap !== 'undefined') {
                    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    modal.show();
                }
            }
		});

		// Edit subgroup - submit
		$("#form_edit_subgroup").on("submit", function (e) {
			e.preventDefault();
			$.ajax({
				url: appUrl + "master/groups/subgroups_ajax",
				type: "PUT",
				data: $(this).serialize(),
				success: function (response) {
					if (response.success) {
						Swal.fire({
							icon: "success",
							title: "Berhasil",
							text: response.message,
						}).then(function () {
							window.location.reload();
						});
					}
				},
				error: function (xhr, status, error) {
					Swal.fire({
						icon: "error",
						title: "Gagal",
						text: "Terjadi kesalahan saat mengupdate sub kelompok",
					});
				},
			});
		});

		// Delete subgroup
		$(document).on("click", ".btn-delete-subgroup", function () {
			var id = $(this).data("id");
			var name = $(this).data("name");

			Swal.fire({
				icon: "warning",
				title: "Hapus Sub Kelompok?",
				html:
					"Anda yakin ingin menghapus sub kelompok <strong>" +
					name +
					'</strong>?<br><small class="text-danger">Pegawai yang tergabung akan dilepas dari sub kelompok ini.</small>',
				showCancelButton: true,
				confirmButtonText: "Ya, Hapus",
				cancelButtonText: "Batal",
				confirmButtonColor: "#dc3545",
			}).then(function (result) {
				if (result.isConfirmed) {
					$.ajax({
						url: appUrl + "master/groups/subgroups_ajax",
						type: "DELETE",
						data: {
							id: id,
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
							}
						},
						error: function (xhr, status, error) {
							Swal.fire({
								icon: "error",
								title: "Gagal",
								text: "Terjadi kesalahan saat menghapus sub kelompok",
							});
						},
					});
				}
			});
		});
	}

	// Check if jQuery is available
	if (typeof window.jQuery !== "undefined") {
		// jQuery is already available, use document.ready
		$(document).ready(function () {
			initGroupUpdate();
		});
	} else if (typeof window.onJQueryReady !== "undefined") {
		// jQuery not yet loaded, add to callback queue
		window.onJQueryReady.push(function () {
			$(document).ready(function () {
				initGroupUpdate();
			});
		});
	} else {
		// Fallback: wait for jQuery with interval check
		var checkInterval = setInterval(function () {
			if (typeof window.jQuery !== "undefined") {
				clearInterval(checkInterval);
				$(document).ready(function () {
					initGroupUpdate();
				});
			}
		}, 50);
	}
})();
