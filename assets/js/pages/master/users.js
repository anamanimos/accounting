$(document).ready(function() {
    // Initialize DataTable
    var table = $('#kt_table_users').DataTable({
        "lengthChange": false,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "dom": "lrtip"
    });

    // Custom search
    $('[data-kt-filter="search"]').on('keyup', function() {
        table.search(this.value).draw();
    });

    // Initialize menus
    KTMenu.init();

    // Change Role Modal
    $(document).on('click', '.btn-change-role', function(e) {
        e.preventDefault();
        var userId = $(this).data('user-id');
        var userName = $(this).data('user-name');
        var currentRole = $(this).data('current-role');

        $('#change_role_user_id').val(userId);
        $('#change_role_user_name').text(userName);
        $('#change_role_select').val(currentRole);

        var modal = new bootstrap.Modal(document.getElementById('modal_change_role'));
        modal.show();
    });

    // Reset Password Modal
    $(document).on('click', '.btn-reset-password', function(e) {
        e.preventDefault();
        var userId = $(this).data('user-id');
        var userName = $(this).data('user-name');

        $('#reset_password_user_id').val(userId);
        $('#reset_password_user_name').text(userName);
        $('#new_password').val('');
        $('#confirm_password').val('');

        var modal = new bootstrap.Modal(document.getElementById('modal_reset_password'));
        modal.show();
    });

    // Submit Change Role Form
    $('#form_change_role').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = form.find('[type="submit"]');

        btn.attr('data-kt-indicator', 'on');
        btn.prop('disabled', true);

        $.ajax({
            url: BASE_URL + 'master/users/update_role',
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                btn.removeAttr('data-kt-indicator');
                btn.prop('disabled', false);

                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message,
                        confirmButtonText: 'OK'
                    }).then(function() {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: response.message
                    });
                }
            },
            error: function() {
                btn.removeAttr('data-kt-indicator');
                btn.prop('disabled', false);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Terjadi kesalahan, silakan coba lagi.'
                });
            }
        });
    });

    // Submit Reset Password Form
    $('#form_reset_password').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = form.find('[type="submit"]');
        var newPassword = $('#new_password').val();
        var confirmPassword = $('#confirm_password').val();

        if (newPassword !== confirmPassword) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Password dan konfirmasi password tidak sama.'
            });
            return;
        }

        if (newPassword.length < 6) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Password minimal 6 karakter.'
            });
            return;
        }

        btn.attr('data-kt-indicator', 'on');
        btn.prop('disabled', true);

        $.ajax({
            url: BASE_URL + 'master/users/reset_password',
            type: 'POST',
            data: {
                user_id: $('#reset_password_user_id').val(),
                new_password: newPassword
            },
            dataType: 'json',
            success: function(response) {
                btn.removeAttr('data-kt-indicator');
                btn.prop('disabled', false);

                if (response.success) {
                    bootstrap.Modal.getInstance(document.getElementById('modal_reset_password')).hide();
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message,
                        confirmButtonText: 'OK'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: response.message
                    });
                }
            },
            error: function() {
                btn.removeAttr('data-kt-indicator');
                btn.prop('disabled', false);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Terjadi kesalahan, silakan coba lagi.'
                });
            }
        });
    });
});
