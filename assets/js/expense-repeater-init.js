"use strict";

// Class definition
var KTExpenseRepeater = (function () {
	// Private variables
	var form;
	var repeater;
	var submitButton;
	var validator;

	// Private functions
	var initRepeater = function () {
		repeater = document.querySelector("#kt_expense_repeater");

		if (!repeater) {
			console.error("Repeater container tidak ditemukan");
			return;
		}

		// Check if Repeater library exists
		if (typeof Repeater === "undefined") {
			console.error(
				"Repeater library tidak ter-load. Pastikan formrepeater.bundle.js sudah diload"
			);
			return;
		}

		var repeaterInstance = new Repeater(repeater, {
			initEmpty: false,

			show: function () {
				this.item.classList.remove("d-none"); // show hidden row
				// Reinitialize select2 for new row
				initSelect2();
				// Reinitialize tooltips
				if (typeof KTUtil !== "undefined") {
					KTUtil.makeResponsive();
				}
			},

			hide: function (e) {
				e.preventDefault(); // prevent hide
				if (typeof Swal !== "undefined") {
					Swal.fire({
						text: "Apakah Anda yakin ingin menghapus item ini?",
						icon: "warning",
						buttonsStyling: false,
						confirmButtonText: "Hapus",
						cancelButtonText: "Batal",
						customClass: {
							confirmButton: "btn btn-primary",
							cancelButton: "btn btn-secondary",
						},
						didOpen: (result) => {
							if (typeof KTUtil !== "undefined") {
								KTUtil.markElement(
									result.querySelector("button.btn-primary"),
									"btn-active"
								);
							}
						},
					}).then((result) => {
						if (result.value) {
							this.remove();
						}
					});
				} else {
					if (confirm("Apakah Anda yakin ingin menghapus item ini?")) {
						this.remove();
					}
				}
			},

			insideContainer: false,
		});
	};

	var initSelect2 = function () {
		// Init select2 elements in repeater
		var select2Items = repeater.querySelectorAll(
			'[data-kt-repeater="select2"]'
		);
		select2Items.forEach((element) => {
			if ($(element).hasClass("select2-hidden-accessible")) {
				$(element).select2("destroy");
			}

			$(element).select2({
				dir: $("html").attr("dir") == "rtl" ? "rtl" : "ltr",
				dropdownParent: $(element).parent(),
			});
		});
	};

	var initValidation = function () {
		// Fetch form element
		form = document.querySelector("#kt_expense_form");

		if (!form) {
			return;
		}

		// Init form validation rules. For more info check the FormValidation plugin's official documentation: https://formvalidation.io/
		validator = FormValidation.formValidation(form, {
			fields: {
				"kt_expense_repeater[0][description]": {
					validators: {
						notEmpty: {
							message: "Deskripsi harus diisi",
						},
					},
				},
				"kt_expense_repeater[0][category]": {
					validators: {
						notEmpty: {
							message: "Kategori harus dipilih",
						},
					},
				},
				"kt_expense_repeater[0][amount]": {
					validators: {
						notEmpty: {
							message: "Nilai harus diisi",
						},
						numeric: {
							message: "Nilai harus berupa angka",
						},
					},
				},
			},

			plugins: {
				trigger: new FormValidation.plugins.Trigger(),
				bootstrap: new FormValidation.plugins.Bootstrap5({
					rowSelector: ".fv-row",
					eleInvalidClass: "",
					eleValidClass: "",
				}),
			},
		});
	};

	var handleSubmit = function () {
		submitButton = document.querySelector("[data-kt-expense-form-submit]");

		if (!submitButton) {
			return;
		}

		submitButton.addEventListener("click", function (e) {
			e.preventDefault();

			if (validator) {
				validator.validate().then(function (status) {
					if (status == "Valid") {
						submitButton.setAttribute("data-kt-indicator", "on");
						submitButton.disabled = true;

						setTimeout(function () {
							submitButton.removeAttribute("data-kt-indicator");
							submitButton.disabled = false;

							Swal.fire({
								text: "Data berhasil disimpan!",
								icon: "success",
								buttonsStyling: false,
								confirmButtonText: "Ok, paham!",
								customClass: {
									confirmButton: "btn btn-primary",
								},
							});
						}, 2000);
					}
				});
			}
		});
	};

	return {
		// Public functions
		init: function () {
			// Delay initialization untuk memastikan semua library sudah siap
			if (document.readyState === "loading") {
				document.addEventListener("DOMContentLoaded", () => {
					setTimeout(() => {
						initRepeater();
						initSelect2();
						initValidation();
						handleSubmit();
						addStyles();
					}, 100);
				});
			} else {
				setTimeout(() => {
					initRepeater();
					initSelect2();
					initValidation();
					handleSubmit();
					addStyles();
				}, 100);
			}
		},
	};

	// Add styles function
	var addStyles = function () {
		var style = document.createElement("style");
		style.textContent = `
            .bg-repeater {
                transition: background-color 500ms ease;
            }

            .bg-repeater:hover {
                background-color: #f7faff;
            }

            [data-repeater-item] {
                animation: slideIn 0.3s ease-in-out;
            }

                @keyframes slideIn {
                    from {
                        opacity: 0;
                        transform: translateY(-10px);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }
            `;
		document.head.appendChild(style);
	};
})();

// On document ready
if (typeof KTUtil !== "undefined" && KTUtil.onDOMContentLoaded) {
	KTUtil.onDOMContentLoaded(function () {
		KTExpenseRepeater.init();
	});
} else {
	document.addEventListener("DOMContentLoaded", function () {
		KTExpenseRepeater.init();
	});
}
