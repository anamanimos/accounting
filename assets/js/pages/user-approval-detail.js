$(document).ready(function () {
	const travelRequestUuid = $("#travel_request_uuid").val();
	const employeeId = $("#employee_id").val();
	const approvalRole = $("#approval_role").val();

	// Handle Approve button click
	$("#btnApprove").on("click", function () {
		Swal.fire({
			title: "Konfirmasi Persetujuan",
			text: "Apakah Anda yakin ingin menyetujui pengajuan perjalanan dinas ini?",
			icon: "question",
			showCancelButton: true,
			confirmButtonColor: "#3085d6",
			cancelButtonColor: "#6c757d",
			confirmButtonText: "Ya, Setujui",
			cancelButtonText: "Batal",
		}).then((result) => {
			if (result.isConfirmed) {
				submitApproval("APPROVED", null);
			}
		});
	});

	// Handle Reject button click
	$("#btnRejectConfirm").on("click", function () {
		const reason = $("#rejectReason").val().trim();

		if (!reason) {
			$("#rejectReason").addClass("is-invalid");
			$("#rejectReasonError").show();
			return;
		}

		$("#rejectReason").removeClass("is-invalid");
		$("#rejectReasonError").hide();

		submitApproval("REJECTED", reason);
	});

	// Clear validation on input
	$("#rejectReason").on("input", function () {
		$(this).removeClass("is-invalid");
		$("#rejectReasonError").hide();
	});

	/**
	 * Submit approval action to API
	 */
	function submitApproval(status, note) {
		// Show loading
		Swal.fire({
			title: "Memproses...",
			text: "Mohon tunggu sebentar",
			allowOutsideClick: false,
			allowEscapeKey: false,
			didOpen: () => {
				Swal.showLoading();
			},
		});

		const data = {
			id: travelRequestUuid,
			employee_id: employeeId,
			status: status,
		};

		if (note) {
			data.note = note;
		}

		$.ajax({
			url: appUrl + "api/travel_request/approval",
			type: "POST",
			data: data,
			dataType: "json",
			success: function (response) {
				if (response.success) {
					Swal.fire({
						icon: "success",
						title: "Berhasil",
						text:
							status === "APPROVED"
								? "Pengajuan berhasil disetujui"
								: "Pengajuan berhasil ditolak",
						confirmButtonText: "OK",
					}).then(() => {
						// Close modal if open
						$("#rejectModal").modal("hide");
						// Redirect back to approval list
						window.location.href = appUrl + "user/approval";
					});
				} else {
					Swal.fire({
						icon: "error",
						title: "Gagal",
						text:
							response.message || "Terjadi kesalahan saat memproses permintaan",
					});
				}
			},
			error: function (xhr, status, error) {
				let errorMessage = "Terjadi kesalahan saat memproses permintaan";
				if (xhr.responseJSON && xhr.responseJSON.message) {
					errorMessage = xhr.responseJSON.message;
				}
				Swal.fire({
					icon: "error",
					title: "Error",
					text: errorMessage,
				});
			},
		});
	}
});
