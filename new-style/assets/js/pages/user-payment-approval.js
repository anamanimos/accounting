$(document).ready(function () {
	// Initialize DataTable
	var table = $("#table_payment_approval_list").DataTable({
		info: false,
		order: [[1, "desc"]], // Sort by document number
		pageLength: 10,
		language: {
			search: "",
			searchPlaceholder: "Cari...",
			lengthMenu: "Tampilkan _MENU_ data",
			zeroRecords: "Tidak ada data yang ditemukan",
			paginate: {
				first: "Pertama",
				last: "Terakhir",
				next: "Selanjutnya",
				previous: "Sebelumnya",
			},
		},
	});

	// Custom search
	$('[data-kt-filter="search"]').on("keyup", function () {
		table.search(this.value).draw();
	});
});
