"use strict";

var UserApproval = (function () {
	var currentPageNeedApproval = 1;
	var currentPageHistory = 1;
	var perPage = 10;
	var searchTimeout = null;
	var currentTab = "need_approval";

	function init() {
		loadData("need_approval");
		loadData("history");
		bindEvents();
	}

	function bindEvents() {
		// Search with debounce
		$("#search_input").on("keyup", function () {
			clearTimeout(searchTimeout);
			searchTimeout = setTimeout(function () {
				currentPageNeedApproval = 1;
				currentPageHistory = 1;
				loadData("need_approval");
				loadData("history");
			}, 500);
		});

		// Tab change
		$('a[data-bs-toggle="tab"]').on("shown.bs.tab", function (e) {
			var target = $(e.target).attr("href");
			if (target === "#tab_need_approval") {
				currentTab = "need_approval";
			} else if (target === "#tab_history") {
				currentTab = "history";
			}
		});

		// Pagination clicks for need_approval
		$(document).on(
			"click",
			"#pagination_nav_need_approval .page-link[data-page]",
			function (e) {
				e.preventDefault();
				var page = $(this).data("page");
				if (page && page !== currentPageNeedApproval) {
					currentPageNeedApproval = page;
					loadData("need_approval");
				}
			}
		);

		// Pagination clicks for history
		$(document).on(
			"click",
			"#pagination_nav_history .page-link[data-page]",
			function (e) {
				e.preventDefault();
				var page = $(this).data("page");
				if (page && page !== currentPageHistory) {
					currentPageHistory = page;
					loadData("history");
				}
			}
		);
	}

	function getFilters(tab) {
		return {
			tab: tab,
			search: $("#search_input").val() || "",
			page:
				tab === "need_approval" ? currentPageNeedApproval : currentPageHistory,
			per_page: perPage,
		};
	}

	function updateStatistics(statistics) {
		if (statistics) {
			$("#stat_total").text(statistics.total || 0);
			$("#stat_pending").text(statistics.pending || 0);
			$("#stat_approved").text(statistics.approved || 0);
			$("#stat_rejected").text(statistics.rejected || 0);
			$("#badge_need_approval").text(statistics.need_approval_count || 0);
			$("#badge_history").text(statistics.history_count || 0);
		}
	}

	function loadData(tab) {
		var filters = getFilters(tab);
		var tableBodyId =
			tab === "need_approval"
				? "#table_body_need_approval"
				: "#table_body_history";

		$(tableBodyId).html(
			'<tr><td colspan="8" class="text-center py-10"><span class="spinner-border spinner-border-sm me-2"></span> Memuat data...</td></tr>'
		);

		$.ajax({
			url: BASE_URL + "api/travel_request/approval_list",
			type: "GET",
			data: filters,
			success: function (response) {
				renderTable(tab, response.data || []);
				renderPagination(tab, response.pagination || {});
				if (tab === "need_approval") {
					updateStatistics(response.statistics || {});
				}
			},
			error: function () {
				$(tableBodyId).html(
					'<tr><td colspan="8" class="text-center py-10 text-danger">Gagal memuat data</td></tr>'
				);
			},
		});
	}

	function renderTable(tab, data) {
		var tableBodyId =
			tab === "need_approval"
				? "#table_body_need_approval"
				: "#table_body_history";
		var currentPage =
			tab === "need_approval" ? currentPageNeedApproval : currentPageHistory;

		if (data.length === 0) {
			$(tableBodyId).html(
				'<tr><td colspan="8" class="text-center py-10 text-muted"><i class="ki-duotone ki-document fs-3x text-gray-300 mb-5"><span class="path1"></span><span class="path2"></span></i><p class="fs-6">Tidak ada data</p></td></tr>'
			);
			return;
		}

		var html = "";
		var startIndex = (currentPage - 1) * perPage;

		data.forEach(function (item, index) {
			var employeeName = item.EMPLOYEE_FULL_NAME || "-";
			var positionName = item.POSITION_NAME || "N/A";
			var departureDate = item.DEPARTURE_DATE || "-";
			var returnDate = item.RETURN_DATE || "-";
			var arrivalCityName = item.ARRIVAL_CITY_NAME || "N/A";
			var destinationCount = item.DESTINATION_COUNT || 0;
			var userRole = item.USER_ROLE || "";
            var userRoles = item.USER_ROLES || userRole; // Use array if available
			var uuid = item.UUID || "";
			var createdAt = item.CREATED_AT || "";

			// Role badge
			var roleBadge = formatRole(userRoles);

			// Status badge
			var statusBadge = formatApproverStatus(item, userRole);

			// Detail URL
			var detailUrl = getDetailUrl(userRole, uuid);

			// Format date
			var formattedDate = formatDate(createdAt);

			html += "<tr>";
			html += "<td>" + (startIndex + index + 1) + "</td>";
			html +=
				'<td><span class="d-block fw-bold fs-6">' +
				employeeName +
				'</span><span class="d-block text-gray-500 fs-7">' +
				positionName +
				"</span></td>";
			html +=
				'<td><span class="d-block">' +
				departureDate +
				'</span><span class="text-gray-500 fs-7">s/d ' +
				returnDate +
				"</span></td>";
			html += '<td class="fw-bold">' + arrivalCityName;
			if (destinationCount > 1) {
				html +=
					'<span class="text-gray-500 fs-7 d-block">dan ' +
					(destinationCount - 1) +
					" destinasi lainnya</span>";
			}
			html += "</td>";
			html += "<td>" + roleBadge + "</td>";
			html += "<td>" + statusBadge + "</td>";
			html += '<td class="text-muted fs-7">' + formattedDate + "</td>";
			html +=
				'<td class="text-end"><a href="' +
				detailUrl +
				'" class="btn btn-primary btn-sm"><i class="ki-outline ki-document fs-3"></i> Detail</a></td>';
			html += "</tr>";
		});

		$(tableBodyId).html(html);
	}

	function renderPagination(tab, pagination) {
		var total = pagination.total || 0;
		var perPage = pagination.per_page || 10;
		var currentPage = pagination.current_page || 1;
		var totalPages = pagination.total_pages || 1;

		var paginationInfoId =
			tab === "need_approval"
				? "#pagination_info_need_approval"
				: "#pagination_info_history";
		var paginationNavId =
			tab === "need_approval"
				? "#pagination_nav_need_approval"
				: "#pagination_nav_history";

		var start = total > 0 ? (currentPage - 1) * perPage + 1 : 0;
		var end = Math.min(currentPage * perPage, total);
		$(paginationInfoId).text(
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
			if (
				i === 1 ||
				i === totalPages ||
				(i >= currentPage - 2 && i <= currentPage + 2)
			) {
				if (i === currentPage) {
					paginationHtml +=
						'<li class="page-item active"><span class="page-link">' +
						i +
						"</span></li>";
				} else {
					paginationHtml +=
						'<li class="page-item"><a class="page-link" href="#" data-page="' +
						i +
						'">' +
						i +
						"</a></li>";
				}
			} else if (i === 2 || i === totalPages - 1) {
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

		$(paginationNavId).html(paginationHtml);
	}

	function formatRole(role) {
		var roleLabels = {
			approver1: {
				label: "Pemberi Persetujuan 1",
				class: "badge-light-primary",
			},
			approver2: { label: "Pemberi Persetujuan 2", class: "badge-light-info" },
			verificator: { label: "Verifikator", class: "badge-light-success" },
			payment_approver: {
				label: "Payment Approver",
				class: "badge-light-warning",
			},
		};

        // Handle array of roles
        if (Array.isArray(role)) {
            var badges = role.map(function(r) {
                var roleInfo = roleLabels[r] || {
                    label: "-",
                    class: "badge-light-secondary",
                };
                return '<span class="badge ' + roleInfo.class + ' me-1">' + roleInfo.label + "</span>";
            });
            return badges.join(" ");
        }

        // Handle single role string
		var roleInfo = roleLabels[role] || {
			label: "-",
			class: "badge-light-secondary",
		};
		return (
			'<span class="badge ' + roleInfo.class + '">' + roleInfo.label + "</span>"
		);
	}

	function formatApproverStatus(item, userRole) {
		var approverStatus = "PENDING";

		if (userRole === "approver1") approverStatus = item.APPROVER1_STATUS;
		else if (userRole === "approver2") approverStatus = item.APPROVER2_STATUS;
		else if (userRole === "verificator")
			approverStatus = item.VERIFICATOR_STATUS;
		else if (userRole === "payment_approver")
			approverStatus = item.PAYMENT_APPROVER_STATUS;

		if (approverStatus === "PENDING") {
			return '<span class="badge badge-light-warning">Menunggu Persetujuan Anda</span>';
		} else if (approverStatus === "APPROVED") {
			return '<span class="badge badge-light-success">Sudah Anda Setujui</span>';
		} else if (approverStatus === "REJECTED") {
			return '<span class="badge badge-light-danger">Sudah Anda Tolak</span>';
		} else if (approverStatus === "REVISION") {
			return '<span class="badge badge-light-warning">Meminta Revisi</span>';
		} else {
			return (
				'<span class="badge badge-light-secondary">' +
				(item.STATUS || "-") +
				"</span>"
			);
		}
	}

	function getDetailUrl(userRole, uuid) {
		if (userRole === "verificator") {
			return BASE_URL + "user/approval/" + uuid + "/verificator";
		} else if (userRole === "payment_approver") {
			return BASE_URL + "user/approval/" + uuid + "/payment_approval";
		} else {
			return BASE_URL + "user/approval/" + uuid + "/approver";
		}
	}

	function formatDate(dateStr) {
		if (!dateStr) return "-";
		try {
			var date = new Date(dateStr);
			if (isNaN(date.getTime())) return dateStr;
			var day = String(date.getDate()).padStart(2, "0");
			var month = String(date.getMonth() + 1).padStart(2, "0");
			var year = date.getFullYear();
			var hours = String(date.getHours()).padStart(2, "0");
			var minutes = String(date.getMinutes()).padStart(2, "0");
			return (
				day +
				"/" +
				month +
				"/" +
				year +
				"<br />" +
				hours +
				":" +
				minutes +
				" WIB"
			);
		} catch (e) {
			return dateStr;
		}
	}

	return {
		init: init,
	};
})();

$(document).ready(function () {
	UserApproval.init();
});
