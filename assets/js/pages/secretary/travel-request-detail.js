// Ensure jQuery is loaded before executing
if (typeof jQuery === "undefined") {
	console.error("jQuery is not loaded");
} else {
	var $ = jQuery;

	$(document).ready(function () {
		// Existing Reject Logic
		$("#btn-reject-submit").click(function () {
			const id = $("#travel_request_id").val();
			const employee_id = $("#employee_id").val();
			const notes = $("#reject_reason").val();
			const apiUrl = appUrl + "secretary/travel_request/" + id + "/cancel";

			if (notes == "") {
				Swal.fire({
					icon: "error",
					title: "Oops...",
					text: "Harap masukkan alasan pembatalan!",
				});
				return;
			}

			Swal.fire({
				title: "Apakah Anda yakin?",
				icon: "warning",
				showCancelButton: true,
				confirmButtonColor: "#3085d6",
				cancelButtonColor: "#d33",
				confirmButtonText: "Ya, Batalkan!",
			}).then((result) => {
				if (!result.isConfirmed) return;
				$.ajax({
					url: apiUrl,
					type: "POST",
					data: { id, employee_id, notes },
					success: function (response) {
						if (response && response.success) {
							Swal.fire({
								icon: "success",
								title: "Sukses",
								text: "Pengajuan berhasil dibatalkan",
							});
							setTimeout(function () {
								window.location.reload();
							}, 1500);
						} else {
							Swal.fire({
								icon: "error",
								title: "Oops...",
								text: (response && response.message) || "Terjadi kesalahan",
							});
						}
					},
					error: function (xhr, status, error) {
						Swal.fire({
							icon: "error",
							title: "Oops...",
							text: "Terjadi kesalahan pada server",
						});
					}
				});
			});
		});

		// --- FINANCE FEATURES ---

		// Handle "Unggah Bukti" button click
		$(document).on("click", ".btn-upload-proof", function () {
			let fileInput = $(this).siblings('input[type="file"]');
			fileInput.trigger('click');
		});

		// Handle file selection and upload
		$(document).on("change", 'input[type="file"]', function () {
			let fileInput = $(this);
			let file = fileInput[0].files[0];
			if (!file) return;

			let formData = new FormData();
			formData.append('file', file);

			// Show loading state
			let button = fileInput.siblings(".btn-upload-proof");
			let originalText = button.text();
			button.text("Uploading...").prop("disabled", true);

			$.ajax({
				url: appUrl + "secretary/travel_request/upload_documents",
				type: "POST",
				data: formData,
				processData: false,
				contentType: false,
				success: function (response) {
					button.prop("disabled", false);
					if (response.success) {
						// Store the file path in the hidden input
						let row = fileInput.closest("tr");
						row.find('input[name$="[proof_file]"]').val(response.data.stored_name);
						row.find('input[name$="[proof_file_original]"]').val(response.data.original_name);

						// Update View Button
						let viewBtn = row.find(".btn-view-proof");
						viewBtn.attr("href", response.data.file_url).removeClass("d-none");

						// Update Upload Button to "Replace" style
						button
							.html('<i class="ki-outline ki-file-up fs-4"></i>')
							.addClass("btn-light-primary btn-icon")
							.removeClass("btn-primary btn-upload-proof text-nowrap") // keep btn-upload-proof for click handler? yes.
                            .addClass("btn-upload-proof") // re-add class for handler
                            .attr("title", "Ganti File")
                            .tooltip("dispose") // dispose old tooltip
                            .tooltip(); // init new tooltip

						// Remove text from button if it was text-based, now it's icon based for consistency with PHP view
                        // Wait, PHP view has different structures for initial vs returned.
                        // PHP: If exists -> View Button + Replace Button (Icon).
                        // PHP: If empty -> Upload Button (Text).
                        // So I should transform the Text Button into the Icon Button.
                        
                        // Or just keep it simple: Change text to "Ganti"
						// User asked to "see and replace".
                        // Let's stick to the structure in PHP:
                        // <button ... btn-light-primary"><i ...></i></button>
                        
                        button
                            .removeClass("btn-primary")
                            .addClass("btn-light-primary")
                            .html('<i class="ki-outline ki-file-up fs-4"></i>')
                            .attr("title", "Ganti File")
                            .attr("data-bs-toggle", "tooltip");
                            
						// Re-init tooltip for this element
                        try {
						    $(button).tooltip();
                        } catch(e) {}

					} else {
						button.text("Gagal Upload").addClass("btn-danger").removeClass("btn-primary");
						Swal.fire("Error", response.message, "error");
						setTimeout(() => {
							button.text(originalText).removeClass("btn-danger").addClass("btn-primary");
						}, 3000);
					}
				},
				error: function (xhr) {
					button.text("Error").prop("disabled", false);
					Swal.fire("Error", "Gagal mengunggah file.", "error");
				}
			});
		});

		// Save Finance Data
		$("#btn-save-finance, #btn-submit-verification").click(function () {
			let isSubmit = $(this).attr("id") === "btn-submit-verification";
			let form = $("#form-finance");
			let formData = form.serializeArray(); // Serialize form data (handles repeater structure)
			
			// Add action flag
			if (isSubmit) {
				formData.push({ name: "action", value: "submit" });
			}

			// Add approvers manually if they are outside the form (they are inside in the view now)
			// But check if select2 values are collected by serializeArray. usually yes if they have name attribute.
			
			let travelId = $("#travel_request_id").val();

			if(isSubmit) {
				// Check Approver Status first
				let app1Status = $(this).data('approver1-status');
				let app2Status = $(this).data('approver2-status');

				if (app1Status !== 'APPROVED' || app2Status !== 'APPROVED') {
					Swal.fire({
						title: "Belum Disetujui",
						text: "Pengajuan belum disetujui oleh Approver 1 dan Approver 2. Harap tunggu persetujuan lengkap sebelum mengajukan verifikasi.",
						icon: "warning",
						confirmButtonText: "OK"
					});
					return;
				}

				// Validate approvers select inputs
				let verificator = $("#pre_approver").val();
				let paymentApprover = $("#post_approver").val();
				if(!verificator || !paymentApprover) {
					Swal.fire("Peringatan", "Harap pilih Verifikator dan Payment Approver!", "warning");
					return;
				}
				
				Swal.fire({
					title: "Ajukan Verifikasi?",
					text: "Data akan dikirim untuk verifikasi dan tidak dapat diubah lagi.",
					icon: "warning",
					showCancelButton: true,
					confirmButtonText: "Ya, Ajukan!",
					cancelButtonText: "Batal"
				}).then((result) => {
					if (result.isConfirmed) {
						submitFinanceData(travelId, formData);
					}
				});
			} else {
				submitFinanceData(travelId, formData);
			}
		});

		function submitFinanceData(id, data) {
			$.ajax({
				url: appUrl + "secretary/travel_request/save_finance/" + id,
				type: "POST",
				data: data,
				dataType: "json",
				beforeSend: function() {
					Swal.fire({
						title: "Menyimpan...",
						didOpen: () => {
							Swal.showLoading();
						}
					});
				},
				success: function (response) {
					if (response.success) {
						Swal.fire("Sukses", response.message, "success").then(() => {
							if (response.redirect) {
								window.location.href = response.redirect;
							} else {
								window.location.reload();
							}
						});
					} else {
						Swal.fire("Gagal", response.message, "error");
					}
				},
				error: function (xhr, status, error) {
					Swal.fire("Error", "Terjadi kesalahan server: " + error, "error");
				}
			});
		}
		// Handle Delete Item
		$(document).on("click", ".btn-delete-item", function () {
			let button = $(this);
			let id = button.data("id");

			Swal.fire({
				title: "Apakah Anda yakin?",
				text: "Item akan dihapus dari daftar pengeluaran.",
				icon: "warning",
				showCancelButton: true,
				confirmButtonColor: "#d33",
				cancelButtonColor: "#3085d6",
				confirmButtonText: "Ya, Hapus!",
				cancelButtonText: "Batal"
			}).then((result) => {
				if (result.isConfirmed) {
					$.ajax({
						url: appUrl + "secretary/travel_request/delete_item",
						type: "POST",
						data: { id: id },
						dataType: "json",
						beforeSend: function () {
							button.prop("disabled", true);
						},
						success: function (response) {
							if (response.success) {
								Swal.fire("Terhapus!", response.message, "success").then(() => {
									window.location.reload();
								});
							} else {
								Swal.fire("Gagal", response.message, "error");
								button.prop("disabled", false);
							}
						},
						error: function (xhr, status, error) {
							Swal.fire("Error", "Terjadi kesalahan server", "error");
							button.prop("disabled", false);
						}
					});
				}
			});
		});

		// --- Dynamic Total Calculation ---

		function formatRupiah(amount) {
			return new Intl.NumberFormat('id-ID', {
				style: 'currency',
				currency: 'IDR',
				minimumFractionDigits: 0,
				maximumFractionDigits: 0
			}).format(amount);
		}

		function parseRupiah(str) {
			if (!str) return 0;
			// Convert to string, remove dots (thousands separator), remove non-numeric
			// Assuming input comes as "1.000.000" or "1000000"
			let cleanStr = str.toString().replace(/\./g, '').replace(/[^0-9,-]+/g, "");
			return parseFloat(cleanStr) || 0;
		}

		function calculateGrandTotal() {
			let total = 0;
			$('tbody[data-repeater-list="kt_expense_repeater"] tr[data-repeater-item]').each(function () {
				// Check if row is visible
				if ($(this).css('display') !== 'none') {
					// Use a more flexible selector that matches both "qty" and "group[x][qty]"
					// We can select by partial match on name attribute ending with [qty] OR exact match "qty"
					// Or simply find input that contains "qty" in name, but safer to be specific about ending or exact.
					
					let qtyInput = $(this).find('input[name="qty"], input[name$="[qty]"]');
					let amountInput = $(this).find('input[name="amount"], input[name$="[amount]"]');
					
					let qtyStr = qtyInput.val();
					let amountStr = amountInput.val();
					
					let qty = parseFloat(qtyStr) || 0;
					let amount = parseRupiah(amountStr);
					
					let rowTotal = qty * amount;
					total += rowTotal;

					// Update Row Total
					$(this).find('span[data-total]').text(formatRupiah(rowTotal));
				}
			});

			// Update Grand Total in Footer
			// Target: tfoot > tr > td:eq(1) (since first td has colspan=6)
			$('#kt_expense_repeater table tfoot tr td').eq(1).text(formatRupiah(total));
		}

		// Bind events
		// Update selector here too
		$(document).on('keyup change paste', 'input[name="qty"], input[name$="[qty]"], input[name="amount"], input[name$="[amount]"]', function () {
			calculateGrandTotal();
		});

		// Also recalculate when a repeater item is deleted (if it triggers an event we can catch)
		// The repeater library usually triggers 'repeater-delete' or we can listen to the delete button click
		// But existing delete button logic does a hard delete via AJAX and reloads page, so init calculation covers it.
		// However, if we add "client-side only" delete for new rows later, we'll need this.
		// For now, let's just run it on load.

		// Initial calculation
		calculateGrandTotal();

	});
}
