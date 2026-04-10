"use strict";

// Class definition
var KTUserVerificatorDetail = function () {
    var travelRequestId;

    var handleVerificationActions = function () {
        // Approve Button
        $('#btn-approve').on('click', function (e) {
            e.preventDefault();

            Swal.fire({
                text: "Apakah Anda yakin ingin menyetujui pengajuan ini?",
                icon: "warning",
                showCancelButton: true,
                buttonsStyling: false,
                confirmButtonText: "Ya, Setujui!",
                cancelButtonText: "Batal",
                customClass: {
                    confirmButton: "btn btn-success",
                    cancelButton: "btn btn-active-light"
                }
            }).then(function (result) {
                if (result.value) {
                    $.ajax({
                        url: BASE_URL + 'user/verify_approve',
                        type: 'POST',
                        data: {
                            id: travelRequestId
                        },
                        dataType: 'json',
                        success: function (response) {
                            if (response.status) {
                                Swal.fire({
                                    text: response.message,
                                    icon: "success",
                                    buttonsStyling: false,
                                    confirmButtonText: "Ok, mengerti!",
                                    customClass: {
                                        confirmButton: "btn btn-primary"
                                    }
                                }).then(function () {
                                    // Reload page
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    text: response.message,
                                    icon: "error",
                                    buttonsStyling: false,
                                    confirmButtonText: "Ok, mengerti!",
                                    customClass: {
                                        confirmButton: "btn btn-primary"
                                    }
                                });
                            }
                        },
                        error: function (xhr, status, error) {
                            Swal.fire({
                                text: "Terjadi kesalahan pada server.",
                                icon: "error",
                                buttonsStyling: false,
                                confirmButtonText: "Ok, mengerti!",
                                customClass: {
                                    confirmButton: "btn btn-primary"
                                }
                            });
                        }
                    });
                }
            });
        });

        // Revision Submit Button
        $('#btn-submit-revision').on('click', function (e) {
            e.preventDefault();
            var note = $('#revision_note').val();
            var btn = $(this);

            if (!note) {
                Swal.fire({
                    text: "Mohon isi catatan revisi.",
                    icon: "warning",
                    buttonsStyling: false,
                    confirmButtonText: "Ok",
                    customClass: {
                        confirmButton: "btn btn-primary"
                    }
                });
                return;
            }

            btn.attr('data-kt-indicator', 'on');
            btn.prop('disabled', true);

            $.ajax({
                url: BASE_URL + 'user/verify_revision',
                type: 'POST',
                data: {
                    id: travelRequestId,
                    note: note
                },
                dataType: 'json',
                success: function (response) {
                    btn.removeAttr('data-kt-indicator');
                    btn.prop('disabled', false);

                    if (response.status) {
                        $('#revisionModal').modal('hide');
                        Swal.fire({
                            text: response.message,
                            icon: "success",
                            buttonsStyling: false,
                            confirmButtonText: "Ok, mengerti!",
                            customClass: {
                                confirmButton: "btn btn-primary"
                            }
                        }).then(function () {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            text: response.message,
                            icon: "error",
                            buttonsStyling: false,
                            confirmButtonText: "Ok, mengerti!",
                            customClass: {
                                confirmButton: "btn btn-primary"
                            }
                        });
                    }
                },
                error: function (xhr, status, error) {
                    btn.removeAttr('data-kt-indicator');
                    btn.prop('disabled', false);
                    
                    Swal.fire({
                        text: "Terjadi kesalahan pada server.",
                        icon: "error",
                        buttonsStyling: false,
                        confirmButtonText: "Ok, mengerti!",
                        customClass: {
                            confirmButton: "btn btn-primary"
                        }
                    });
                }
            });
        });
    }

    return {
        // Public functions
        init: function () {
            travelRequestId = $('#travel_request_uuid').val();
            handleVerificationActions();
        }
    };
}();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTUserVerificatorDetail.init();
});
