"use strict";

$(document).ready(function () {
	// Manage Submission
	$("#form_group").on("submit", function (e) {
		e.preventDefault();

		// Validate at least one subgroup
		var subgroups = $('input[name="subgroup_name[]"]').filter(function () {
			return $(this).val().trim() !== "";
		});

		if (subgroups.length === 0) {
			Swal.fire({
				icon: "warning",
				title: "Perhatian",
				text: "Minimal harus ada 1 sub kelompok",
			});
			return;
		}

		// ajax
		$.ajax({
			url: appUrl + "master/groups/ajax",
			type: "POST",
			data: $(this).serialize(),
			success: function (response) {
				console.log(response);
				if (response.success) {
					// show success message
					Swal.fire({
						icon: "success",
						title: "Success",
						text: response.message,
					}).then(function () {
						// redirect to index
						window.location.href = appUrl + "master/groups";
					});
				}
			},
		});
	});
});
