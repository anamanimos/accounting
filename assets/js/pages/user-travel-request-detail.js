"use strict";

// Class definition
var KTUserTravelRequestDetail = (function () {
	// Shared variables
	var table;
	var datatable;

	// Private functions
	var initDatatable = function () {
		var tableEl = document.querySelector("#table_expenses");
		if (!tableEl) return;

		datatable = $(tableEl).DataTable({
			info: false,
			order: [],
			pageLength: 10,
			columnDefs: [{ orderable: false, targets: -1 }],
		});
	};

	// Public methods
	return {
		init: function () {
			initDatatable();
		},
	};
})();

// On document ready
KTUtil.onDOMContentLoaded(function () {
	KTUserTravelRequestDetail.init();
});
