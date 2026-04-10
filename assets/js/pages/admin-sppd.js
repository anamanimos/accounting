"use strict";

// SPPD List Module with Server-Side DataTable
var SPPDList = (function () {
	var dataTable = null;
	var currentFilter = {
		start_date: null,
		end_date: null,
		status: [],
		travel_type: [],
	};

	// API Base URL
	var apiUrl = window.location.origin + "/api/travel_request/superadmin";

	// Status definitions
	var statusConfig = {
		PENDING: {
			class: "badge-light-warning",
			text: "Menunggu Persetujuan 1/2",
			tooltip: "Menunggu Persetujuan Approver 1/2",
		},
		PRE_REJECTED: {
			class: "badge-light-danger",
			text: "Ditolak",
			tooltip: "Pengajuan Ditolak",
		},
		PRE_APPROVED: {
			class: "badge-light-info",
			text: "Disetujui",
			tooltip:
				"Pengajuan Disetujui, Menunggu Admin mengajukan dokumen keuangan",
		},
		SUBMITTED: {
			class: "badge-light-warning",
			text: "Menunggu Persetujuan Verifikator",
			tooltip: "Menunggu Persetujuan Verifikator",
		},
		VERIFICATOR_REVISION: {
			class: "badge-light-primary",
			text: "Perbaikan Verifikator",
			tooltip: "Dokumen Keuangan Perlu Perbaikan",
		},
		VERIFICATOR_APPROVED: {
			class: "badge-light-info",
			text: "Menunggu Persetujuan Payment Approver",
			tooltip: "Menunggu Persetujuan Payment Approver",
		},
		PAYMENT_APPROVER_REVISION: {
			class: "badge-light-primary",
			text: "Perbaikan Verifikator",
			tooltip: "Dokumen Keuangan Perlu Perbaikan",
		},
		UNPAID: {
			class: "badge-light-dark",
			text: "Menunggu Pembayaran",
			tooltip: "Dokumen Lengkap. Menunggu Pembayaran.",
		},
		PAID: { class: "badge-light-success", text: "Terbayar", tooltip: "" },
		CANCELED: {
			class: "badge-light-secondary",
			text: "Dibatalkan",
			tooltip: "Pengajuan Dibatalkan",
		},
	};

	var travelTypeConfig = {
		dinas: { class: "badge-light-primary", text: "Dinas" },
		diklat: { class: "badge-light-info", text: "Diklat" },
		luar_negeri: { class: "badge-light-warning", text: "Luar Negeri" },
	};

	// Initialize
	var init = function () {
		initDateRangePicker();
		initMultiSelects();
		initFilterButtons();
		initServerSideDataTable();
		fetchStatistics();
	};

	// Initialize Date Range Picker
	var initDateRangePicker = function () {
		var dateInput = document.getElementById("sppd_date_range");
		var clearBtn = document.getElementById("clear_date_filter");

		if (
			!dateInput ||
			typeof $ === "undefined" ||
			typeof $.fn.daterangepicker === "undefined"
		) {
			console.warn("DateRangePicker not available");
			return;
		}

		$(dateInput).daterangepicker({
			autoUpdateInput: false,
			opens: "left",
			locale: {
				format: "DD/MM/YYYY",
				separator: " - ",
				applyLabel: "Terapkan",
				cancelLabel: "Batal",
				fromLabel: "Dari",
				toLabel: "Sampai",
				customRangeLabel: "Kustom",
				weekLabel: "M",
				daysOfWeek: ["Min", "Sen", "Sel", "Rab", "Kam", "Jum", "Sab"],
				monthNames: [
					"Januari",
					"Februari",
					"Maret",
					"April",
					"Mei",
					"Juni",
					"Juli",
					"Agustus",
					"September",
					"Oktober",
					"November",
					"Desember",
				],
				firstDay: 1,
			},
			ranges: {
				"Hari Ini": [moment(), moment()],
				Kemarin: [moment().subtract(1, "days"), moment().subtract(1, "days")],
				"7 Hari Terakhir": [moment().subtract(6, "days"), moment()],
				"30 Hari Terakhir": [moment().subtract(29, "days"), moment()],
				"Bulan Ini": [moment().startOf("month"), moment().endOf("month")],
				"Bulan Lalu": [
					moment().subtract(1, "month").startOf("month"),
					moment().subtract(1, "month").endOf("month"),
				],
				"Tahun Ini": [moment().startOf("year"), moment().endOf("year")],
			},
		});

		$(dateInput).on("apply.daterangepicker", function (ev, picker) {
			var startDate = picker.startDate.format("YYYY-MM-DD");
			var endDate = picker.endDate.format("YYYY-MM-DD");

			$(this).val(
				picker.startDate.format("DD/MM/YYYY") +
					" - " +
					picker.endDate.format("DD/MM/YYYY")
			);

			currentFilter.start_date = startDate;
			currentFilter.end_date = endDate;

			if (clearBtn) clearBtn.style.display = "block";
		});

		$(dateInput).on("cancel.daterangepicker", function () {
			$(this).val("");
		});

		if (clearBtn) {
			clearBtn.addEventListener("click", function () {
				dateInput.value = "";
				currentFilter.start_date = null;
				currentFilter.end_date = null;
				clearBtn.style.display = "none";
			});
		}
	};

	// Initialize Multi Selects
	var initMultiSelects = function () {
		var travelTypeSelect = document.getElementById("filter_travel_type");
		if (
			travelTypeSelect &&
			typeof $ !== "undefined" &&
			typeof $.fn.select2 !== "undefined"
		) {
			$(travelTypeSelect)
				.select2({
					placeholder: "Jenis Perjalanan",
					allowClear: true,
					width: "150px",
				})
				.on("change", function () {
					currentFilter.travel_type = $(this).val() || [];
				});
		}

		var statusSelect = document.getElementById("filter_status");
		if (
			statusSelect &&
			typeof $ !== "undefined" &&
			typeof $.fn.select2 !== "undefined"
		) {
			$(statusSelect)
				.select2({
					placeholder: "Status",
					allowClear: true,
					width: "auto",
				})
				.on("change", function () {
					currentFilter.status = $(this).val() || [];
				});
		}
	};

	// Initialize Filter Buttons
	var initFilterButtons = function () {
		var applyBtn = document.getElementById("btn_apply_filter");
		var resetBtn = document.getElementById("btn_reset_filter");

		if (applyBtn) {
			applyBtn.addEventListener("click", function () {
				if (dataTable) {
					dataTable.ajax.reload();
					fetchStatistics();
				}
				if (resetBtn) resetBtn.style.display = "inline-block";
			});
		}

		if (resetBtn) {
			resetBtn.addEventListener("click", function () {
				currentFilter = {
					start_date: null,
					end_date: null,
					status: [],
					travel_type: [],
				};

				var dateInput = document.getElementById("sppd_date_range");
				if (dateInput) dateInput.value = "";

				var clearDateBtn = document.getElementById("clear_date_filter");
				if (clearDateBtn) clearDateBtn.style.display = "none";

				if (typeof $ !== "undefined") {
					$("#filter_travel_type").val(null).trigger("change");
					$("#filter_status").val(null).trigger("change");
				}

				resetBtn.style.display = "none";

				if (dataTable) {
					dataTable.ajax.reload();
					fetchStatistics();
				}
			});
		}
	};

	// Initialize Server-Side DataTable
	var initServerSideDataTable = function () {
		var table = document.getElementById("kt_table_sppd");
		if (
			!table ||
			typeof $ === "undefined" ||
			typeof $.fn.DataTable === "undefined"
		)
			return;

		dataTable = $(table).DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: apiUrl,
				type: "GET",
				data: function (d) {
					// Add custom filters
					if (currentFilter.start_date) d.start_date = currentFilter.start_date;
					if (currentFilter.end_date) d.end_date = currentFilter.end_date;
					if (currentFilter.status && currentFilter.status.length > 0)
						d.status = currentFilter.status;
					if (currentFilter.travel_type && currentFilter.travel_type.length > 0)
						d.travel_type = currentFilter.travel_type;
					return d;
				},
				dataSrc: function (json) {
					// Update statistics from response
					if (json.statistics) {
						renderStatistics(json.statistics);
					}
					if (json.current_month) {
						updateMonthDisplay(json.current_month);
					}
					return json.data || [];
				},
			},
			columns: [
				{
					data: "DOCUMENT_NUMBER",
					render: function (data) {
						return (
							'<span class="fw-bold">' + escapeHtml(data || "-") + "</span>"
						);
					},
				},
				{
					data: "EMPLOYEE_NAME",
					render: function (data, type, row) {
						var name = data || "-";
						var position = row.POSITION_NAME || "";
						var initial = name.charAt(0).toUpperCase();
						return `
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-circle symbol-40px overflow-hidden me-3">
                                    <div class="symbol-label fs-5 fw-semibold bg-light-primary text-primary">${initial}</div>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="text-gray-800 fw-bold">${escapeHtml(
																			name
																		)}</span>
                                    <span class="text-muted fs-7">${escapeHtml(
																			position
																		)}</span>
                                </div>
                            </div>
                        `;
					},
				},
				{
					data: "TRAVEL_TYPE",
					render: function (data) {
						var config = travelTypeConfig[data] || {
							class: "badge-light-primary",
							text: data || "Dinas",
						};
						return (
							'<span class="badge ' +
							config.class +
							'">' +
							config.text +
							"</span>"
						);
					},
				},
				{
					data: "STATUS",
					render: function (data) {
						var config = statusConfig[data] || {
							class: "badge-light-secondary",
							text: data || "-",
							tooltip: "",
						};
						var tooltipAttr = config.tooltip
							? 'data-bs-toggle="tooltip" data-bs-placement="top" title="' +
							  config.tooltip +
							  '"'
							: "";
						return (
							'<span class="badge ' +
							config.class +
							'" ' +
							tooltipAttr +
							">" +
							config.text +
							"</span>"
						);
					},
				},
				{
					data: "CREATED_AT_FORMATTED",
					render: function (data) {
						return data || "-";
					},
				},
				{
					data: "UUID",
					orderable: false,
					render: function (data, type, row) {
						var uuid = data || row.ID;
						return `
                            <div class="text-end">
                                <a href="#" class="btn btn-sm btn-light btn-active-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                    Aksi <i class="ki-duotone ki-down fs-5 ms-1"></i>
                                </a>
                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-175px py-4" data-kt-menu="true">
                                    <div class="menu-item px-3">
                                        <a href="${window.location.origin}/sppd/${uuid}/detail" class="menu-link px-3">
                                            <i class="ki-outline ki-eye me-2"></i> Lihat Detail
                                        </a>
                                    </div>
                                    <div class="menu-item px-3">
                                        <a href="${window.location.origin}/document/sppd/${uuid}" class="menu-link px-3">
                                            <i class="ki-outline ki-document me-2"></i> Download PDF
                                        </a>
                                    </div>
                                </div>
                            </div>
                        `;
					},
				},
			],
			order: [[4, "desc"]],
			pageLength: 10,
			language: {
				processing:
					'<span class="spinner-border spinner-border-sm me-2" role="status"></span> Memuat...',
				search: "",
				searchPlaceholder: "Cari...",
				lengthMenu: "Tampilkan _MENU_ data",
				info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
				infoEmpty: "Tidak ada data",
				infoFiltered: "(filter dari _MAX_ total data)",
				emptyTable: "Tidak ada data pengajuan",
				paginate: {
					first: "Pertama",
					last: "Terakhir",
					next: "Selanjutnya",
					previous: "Sebelumnya",
				},
			},
			drawCallback: function () {
				// Reinitialize tooltips after each draw
				var tooltipTriggerList = [].slice.call(
					document.querySelectorAll('[data-bs-toggle="tooltip"]')
				);
				tooltipTriggerList.forEach(function (el) {
					new bootstrap.Tooltip(el);
				});

				// Reinitialize KT menus
				if (typeof KTMenu !== "undefined") {
					KTMenu.init();
				}
			},
		});

		// Bind external search input
		var searchInput = document.querySelector('[data-kt-filter="search"]');
		if (searchInput) {
			searchInput.addEventListener("keyup", function (e) {
				dataTable.search(e.target.value).draw();
			});
		}
	};

	// Fetch statistics separately (for loading indicator)
	var fetchStatistics = function () {
		// Show loading for statistics
		[
			"stat_total",
			"stat_pending",
			"stat_approved",
			"stat_rejected",
			"stat_revision",
			"stat_canceled",
			"stat_completed",
		].forEach(function (id) {
			var el = document.getElementById(id);
			if (el) {
				el.innerHTML =
					'<span class="spinner-border spinner-border-sm" role="status"></span>';
			}
		});
	};

	// Render statistics cards
	var renderStatistics = function (stats) {
		document.getElementById("stat_total").textContent = stats.total || 0;
		document.getElementById("stat_pending").textContent = stats.pending || 0;
		document.getElementById("stat_approved").textContent = stats.approved || 0;
		document.getElementById("stat_rejected").textContent = stats.rejected || 0;
		document.getElementById("stat_revision").textContent = stats.revision || 0;
		document.getElementById("stat_canceled").textContent = stats.canceled || 0;
		document.getElementById("stat_completed").textContent =
			stats.completed || 0;
	};

	// Update month display
	var updateMonthDisplay = function (monthStr) {
		var el = document.getElementById("current_month_display");
		if (el) el.textContent = monthStr;
	};

	// Escape HTML
	var escapeHtml = function (text) {
		if (!text) return "";
		var div = document.createElement("div");
		div.appendChild(document.createTextNode(text));
		return div.innerHTML;
	};

	return {
		init: init,
	};
})();

// Initialize on DOM ready
document.addEventListener("DOMContentLoaded", function () {
	SPPDList.init();
});
