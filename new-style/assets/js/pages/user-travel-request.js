"use strict";

var UserTravelRequest = (function () {
	var currentPage = 1;
	var perPage = 10;
	var totalPages = 1;
	var searchTimeout = null;

	function init() {
		initSelect2();
		initDateRangePicker();
		loadData();
		bindEvents();
	}

	function initSelect2() {
		$("#filter_status").select2({
			placeholder: "Status",
			allowClear: true,
			width: "auto",
			dropdownAutoWidth: true,
			minimumResultsForSearch: -1,
		});

		$("#filter_travel_type").select2({
			placeholder: "Jenis Perjalanan",
			allowClear: true,
			width: "auto",
			dropdownAutoWidth: true,
			minimumResultsForSearch: -1,
		});
	}

	function initDateRangePicker() {
		$("#filter_daterange").daterangepicker(
			{
				autoUpdateInput: false,
				locale: {
					format: "YYYY-MM-DD",
					separator: " - ",
					applyLabel: "Terapkan",
					cancelLabel: "Batal",
					fromLabel: "Dari",
					toLabel: "Sampai",
					customRangeLabel: "Pilih Sendiri",
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
				},
			},
			function (start, end, label) {
				$("#filter_daterange").val(
					start.format("YYYY-MM-DD") + " - " + end.format("YYYY-MM-DD")
				);
				$("#filter_start_date").val(start.format("YYYY-MM-DD"));
				$("#filter_end_date").val(end.format("YYYY-MM-DD"));
				$("#clear_date_filter").show();
				updateResetButtonVisibility();
			}
		);

		$("#filter_daterange").on("cancel.daterangepicker", function (ev, picker) {
			$(this).val("");
			$("#filter_start_date").val("");
			$("#filter_end_date").val("");
			$("#clear_date_filter").hide();
			updateResetButtonVisibility();
		});
	}

	function updateResetButtonVisibility() {
		var hasFilters =
			($("#filter_status").val() && $("#filter_status").val().length > 0) ||
			($("#filter_travel_type").val() &&
				$("#filter_travel_type").val().length > 0) ||
			$("#filter_start_date").val() ||
			$("#search_input").val();

		if (hasFilters) {
			$("#btn_reset_filter").show();
		} else {
			$("#btn_reset_filter").hide();
		}
	}

	function bindEvents() {
		// Search with debounce
		$("#search_input").on("keyup", function () {
			clearTimeout(searchTimeout);
			searchTimeout = setTimeout(function () {
				currentPage = 1;
				loadData();
				updateResetButtonVisibility();
			}, 500);
		});

		// Apply filter
		$("#btn_apply_filter").on("click", function () {
			currentPage = 1;
			loadData();
			updateResetButtonVisibility();
		});

		// Reset filter
		$("#btn_reset_filter").on("click", function () {
			$("#filter_status").val(null).trigger("change");
			$("#filter_travel_type").val(null).trigger("change");
			$("#filter_daterange").val("");
			$("#filter_start_date").val("");
			$("#filter_end_date").val("");
			$("#search_input").val("");
			$("#clear_date_filter").hide();
			$("#btn_reset_filter").hide();
			currentPage = 1;
			loadData();
		});

		// Clear date filter button
		$("#clear_date_filter").on("click", function () {
			$("#filter_daterange").val("");
			$("#filter_start_date").val("");
			$("#filter_end_date").val("");
			$(this).hide();
			updateResetButtonVisibility();
		});

		// Status filter change
		$("#filter_status").on("change", function () {
			updateResetButtonVisibility();
		});

		// Travel type filter change
		$("#filter_travel_type").on("change", function () {
			updateResetButtonVisibility();
		});

		// Pagination clicks
		$(document).on("click", ".page-link[data-page]", function (e) {
			e.preventDefault();
			var page = $(this).data("page");
			if (page && page !== currentPage) {
				currentPage = page;
				loadData();
			}
		});
	}

	function getFilters() {
		var statusVal = $("#filter_status").val();
		var travelTypeVal = $("#filter_travel_type").val();
		return {
			status: statusVal && statusVal.length > 0 ? statusVal : [],
			travel_type:
				travelTypeVal && travelTypeVal.length > 0 ? travelTypeVal : [],
			start_date: $("#filter_start_date").val() || "",
			end_date: $("#filter_end_date").val() || "",
			search: $("#search_input").val() || "",
			page: currentPage,
			per_page: perPage,
		};
	}

	function updateStatistics(statistics) {
		if (statistics) {
			// Handle both lowercase and uppercase keys from Oracle
			var total =
				statistics.total !== undefined
					? statistics.total
					: statistics.TOTAL || 0;
			var pending =
				statistics.pending !== undefined
					? statistics.pending
					: statistics.PENDING || 0;
			var approved =
				statistics.approved !== undefined
					? statistics.approved
					: statistics.APPROVED || 0;
			var rejected =
				statistics.rejected !== undefined
					? statistics.rejected
					: statistics.REJECTED || 0;
			var revision =
				statistics.revision !== undefined
					? statistics.revision
					: statistics.REVISION || 0;
			var canceled =
				statistics.canceled !== undefined
					? statistics.canceled
					: statistics.CANCELED || 0;
			var completed =
				statistics.completed !== undefined
					? statistics.completed
					: statistics.COMPLETED || 0;

			$("#stat_total").text(total);
			$("#stat_pending").text(pending);
			$("#stat_approved").text(approved);
			$("#stat_rejected").text(rejected);
			$("#stat_revision").text(revision);
			$("#stat_canceled").text(canceled);
			$("#stat_completed").text(completed);
		}
	}

	function loadData() {
		var filters = getFilters();
		$("#table_body").html(
			'<tr><td colspan="8" class="text-center py-10"><span class="spinner-border spinner-border-sm me-2"></span> Memuat data...</td></tr>'
		);

		$.ajax({
			url: BASE_URL + "api/travel_request",
			type: "GET",
			data: filters,
			success: function (response) {
				renderTable(response.data || []);
				renderPagination(response.pagination || {});
				updateStatistics(response.statistics || {});
			},
			error: function () {
				$("#table_body").html(
					'<tr><td colspan="8" class="text-center py-10 text-danger">Gagal memuat data</td></tr>'
				);
			},
		});
	}

	function renderTable(data) {
		if (data.length === 0) {
			$("#table_body").html(
				'<tr><td colspan="8" class="text-center py-10 text-muted">Tidak ada data</td></tr>'
			);
			return;
		}

		var html = "";
		data.forEach(function (item) {
			// Handle both lowercase and UPPERCASE field names from Oracle
			var documentNumber =
				item.document_number || item.DOCUMENT_NUMBER || "Draft";
			var travelType = item.travel_type || item.TRAVEL_TYPE || "-";
			var departureDate = item.departure_date || item.DEPARTURE_DATE || "-";
			var returnDate = item.return_date || item.RETURN_DATE || "-";
			var arrivalCityName =
				item.arrival_city_name || item.ARRIVAL_CITY_NAME || "";
			var destinationCount =
				item.destination_count || item.DESTINATION_COUNT || 0;
			var status = item.STATUS || item.status || "";
			var createdAt = item.created_at || item.CREATED_AT || "";
			var uuid = item.uuid || item.UUID || "";

			html += "<tr>";
			html += "<td>" + documentNumber + "</td>";
			html += "<td>" + formatTravelType(travelType) + "</td>";
			html += "<td>" + departureDate + "</td>";
			html += "<td>" + returnDate + "</td>";
			html +=
				"<td>" + formatDestination(arrivalCityName, destinationCount) + "</td>";
			html += "<td>" + formatStatus(status) + "</td>";
			html += '<td class="text-muted fs-7">' + formatDate(createdAt) + "</td>";
			html +=
				'<td><a href="' +
				BASE_URL +
				"user/travel_request/detail/" +
				uuid +
				'" class="btn btn-primary btn-sm"><i class="ki-outline ki-document fs-3"></i> Detail</a></td>';
			html += "</tr>";
		});

		$("#table_body").html(html);
	}

	function renderPagination(pagination) {
		var total = pagination.total || 0;
		var perPage = pagination.per_page || 10;
		var currentPage = pagination.current_page || 1;
		totalPages = pagination.total_pages || 1;

		var start = total > 0 ? (currentPage - 1) * perPage + 1 : 0;
		var end = Math.min(currentPage * perPage, total);
		$("#pagination_info").text(
			"Menampilkan " + start + " - " + end + " dari " + total + " data"
		);

		var paginationHtml = "";

		// Previous button
		if (currentPage > 1) {
			paginationHtml +=
				'<li class="page-item"><a class="page-link" href="#" data-page="' +
				(currentPage - 1) +
				'">&laquo;</a></li>';
		}

		// Page numbers
		for (var i = 1; i <= totalPages; i++) {
			if (i === currentPage) {
				paginationHtml +=
					'<li class="page-item active"><span class="page-link">' +
					i +
					"</span></li>";
			} else if (
				i <= 3 ||
				i > totalPages - 3 ||
				(i >= currentPage - 1 && i <= currentPage + 1)
			) {
				paginationHtml +=
					'<li class="page-item"><a class="page-link" href="#" data-page="' +
					i +
					'">' +
					i +
					"</a></li>";
			} else if (i === 4 || i === totalPages - 3) {
				paginationHtml +=
					'<li class="page-item disabled"><span class="page-link">...</span></li>';
			}
		}

		// Next button
		if (currentPage < totalPages) {
			paginationHtml +=
				'<li class="page-item"><a class="page-link" href="#" data-page="' +
				(currentPage + 1) +
				'">&raquo;</a></li>';
		}

		$("#pagination_nav").html(paginationHtml);
	}

	function formatDestination(cityName, count) {
		if (!cityName) return "-";
		if (count > 1) {
			return (
				'<span class="d-block">' + cityName + '</span>' +
				'<span class="text-gray-500 fs-7">dan ' + (count - 1) + ' Destinasi lain</span>'
			);
		}
		return '<span class="d-block">' + cityName + '</span>';
	}

	function formatTravelType(type) {
		var types = {
			dinas: '<span class="badge badge-light-primary">Dinas</span>',
			diklat: '<span class="badge badge-light-info">Diklat</span>',
			luar_negeri: '<span class="badge badge-light-warning">Luar Negeri</span>',
		};
		return (
			types[type] ||
			'<span class="badge badge-light-secondary">' + (type || "-") + "</span>"
		);
	}

	function formatStatus(status) {
		var badges = {
			PENDING:
				'<span class="badge badge-light-warning">Menunggu Persetujuan</span>',
			SUBMITTED: '<span class="badge badge-light-info">Diajukan</span>',
			PRE_APPROVED: '<span class="badge badge-light-success">Disetujui</span>',
			PRE_REJECTED: '<span class="badge badge-light-danger">Ditolak</span>',
			VERIFICATOR_APPROVED:
				'<span class="badge badge-light-info">Menunggu Pembayaran</span>',
			VERIFICATOR_REVISION:
				'<span class="badge badge-light-primary">Revisi Verifikator</span>',
			PAYMENT_APPROVER_REVISION:
				'<span class="badge badge-light-primary">Revisi Payment</span>',
			UNPAID: '<span class="badge badge-light-warning">Belum Dibayar</span>',
			PAID: '<span class="badge badge-light-success">Sudah Dibayar</span>',
			CANCELED: '<span class="badge badge-light-secondary">Dibatalkan</span>',
		};
		return (
			badges[status] ||
			'<span class="badge badge-light-secondary">' + (status || "-") + "</span>"
		);
	}

	function formatDate(dateStr) {
		if (!dateStr) return "-";
		// Handle format YYYY-MM-DD HH24:MI from Oracle
		if (dateStr.match(/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/)) {
			return dateStr + " WIB"; // Already in correct format, add WIB
		}
		var date = new Date(dateStr);
		if (isNaN(date.getTime())) return dateStr; // Return original if invalid
		var year = date.getFullYear();
		var month = String(date.getMonth() + 1).padStart(2, "0");
		var day = String(date.getDate()).padStart(2, "0");
		var hours = String(date.getHours()).padStart(2, "0");
		var minutes = String(date.getMinutes()).padStart(2, "0");
		return (
			year + "-" + month + "-" + day + " " + hours + ":" + minutes + " WIB"
		);
	}

	return {
		init: init,
	};
})();

$(document).ready(function () {
	UserTravelRequest.init();
});
