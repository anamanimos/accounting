$(document).ready(function () {
	const travelRequestUuid = $("#travel_request_uuid").val();
	const employeeId = $("#employee_id").val();

	// Handle Approve Payment button click
	$("#btnApprovePayment").on("click", function () {
		Swal.fire({
			title: "Konfirmasi Persetujuan Pembayaran",
			html: `
                <div class="text-start">
                    <p>Dengan menyetujui pembayaran ini, Anda menyatakan bahwa:</p>
                    <ul class="text-muted">
                        <li>Semua dokumen telah diverifikasi dengan benar</li>
                        <li>Rincian keuangan sudah sesuai</li>
                        <li>Pembayaran dapat diproses</li>
                    </ul>
                    <p class="text-danger fw-bold">Tindakan ini tidak dapat dibatalkan.</p>
                </div>
            `,
			icon: "warning",
			showCancelButton: true,
			confirmButtonColor: "#50cd89",
			cancelButtonColor: "#6c757d",
			confirmButtonText: "Ya, Setujui Pembayaran",
			cancelButtonText: "Batal",
		}).then((result) => {
			if (result.isConfirmed) {
				submitPaymentApproval("APPROVED", null);
			}
		});
	});

	// Handle Revision button click
	$("#btnRevisionConfirm").on("click", function () {
		const notes = $("#revisionNotes").val().trim();

		if (!notes) {
			$("#revisionNotes").addClass("is-invalid");
			$("#revisionNotesError").show();
			return;
		}

		$("#revisionNotes").removeClass("is-invalid");
		$("#revisionNotesError").hide();

		submitPaymentApproval("REVISION", notes);
	});

	// Clear validation on input
	$("#revisionNotes").on("input", function () {
		$(this).removeClass("is-invalid");
		$("#revisionNotesError").hide();
	});

	/**
	 * Submit payment approval action to API
	 */
	function submitPaymentApproval(status, notes) {
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

		if (notes) {
			data.notes = notes;
		}

		$.ajax({
			url: appUrl + "api/travel_request/update_payment_approver_status",
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
								? "Pembayaran berhasil disetujui"
								: "Permintaan revisi berhasil dikirim",
						confirmButtonText: "OK",
					}).then(() => {
						// Close modal if open
						$("#revisionModal").modal("hide");
						// Reload page
						location.reload();
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
