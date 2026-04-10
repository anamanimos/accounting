$(document).ready(function() {

    Inputmask("numeric", {
        // Mask untuk numeric/currency
        radixPoint: ",",      // Menggunakan koma sebagai pemisah desimal
        groupSeparator: ".",   // Menggunakan titik sebagai pemisah ribuan
        alias: "numeric",
        placeholder: "0",
        autoGroup: true,       // Secara otomatis mengelompokkan (ribuan)
        digits: 0,             // 2 angka di belakang koma (seperti ,00)
        digitsOptional: false, // Memastikan 2 angka desimal selalu ada
        positionCaretOnClick: "radixFocus",
        numericInput: true,
        allowMinus: false,
		autoUnmask: true,
    }).mask(".rupiah-input");

    // 1. Tambahkan event listener saat tombol 'Simpan' diklik
    $('#bulk-update').on('click', function(e) {
        e.preventDefault();
        
        const dataToSubmit = [];
        const $inputs = $('.daily-amount-input'); // Kelas harus unik
        const tripCategory = $(this).data('trip-category');

        // Cek semua input yang ada di tabel
        $('table tbody input[type="number"]').each(function() {
            const $input = $(this);
            const currentValue = parseInt($input.val().replace(/[^0-9]/g, '')) || 0; // Bersihkan nilai dan pastikan integer/0

            // Ambil ID yang diperlukan dari atribut data
            const provinceId = $input.closest('tr').data('province-id');
            const groupId = $input.data('group-id');
            const originalValue = $input.data('original-value');

            // Cek apakah nilai input benar-benar berubah dari nilai awal
            // Catatan: Jika Anda tidak tahu nilai awal (initial value), Anda harus mengirim semua data.
            // Untuk kesederhanaan, kita akan mengirim semua data yang ada di input.
            // Jika Anda hanya ingin mengirim yang diubah, simpan nilai awal di data-original-value saat render.
            
            if (provinceId && groupId && currentValue !== originalValue) {
                // Masukkan data ke dalam array relasional/transaksional
                dataToSubmit.push({
                    province_id: provinceId,
                    group_id: groupId,
                    daily_amount: currentValue
                });
            }
        });
        
        // Cek jika array tidak kosong
        if (dataToSubmit.length === 0) {
            Swal.fire({
                title: 'Perhatian',
                text: 'Tidak ada data perubahan yang perlu disimpan.',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            return;
        }

        const payload = {
            trip_category: tripCategory, // Nilai 'DINAS' atau 'DIKLAT'
            rates: dataToSubmit          // Array data tarif
        };

        $.ajax({
            url: appUrl + 'master/daily_rates/ajax/bulk_update',
            type: 'POST',
            dataType: 'json',
            contentType: 'application/json',
            data: JSON.stringify(payload),
            
            beforeSend: function() {
                // Tambahkan loading spinner
                Swal.fire({
                    title: 'Menyimpan data...',
                    text: 'Harap tunggu sebentar...',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: 'Berhasil',
                        text: response.message,
                        icon: 'success',
                        confirmButtonText: 'Tutup'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.reload();
                        }
                    });
                } else {
                        Swal.fire({
                        title: 'Gagal',
                        text: 'Gagal memperbarui data: ' + response.message,
                        icon: 'error',
                        confirmButtonText: 'Tutup'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", status, error, xhr.responseText);
                Swal.fire({
                    title: 'Gagal',
                    text: 'Terjadi kesalahan koneksi saat menyimpan data.',
                    icon: 'error',
                    confirmButtonText: 'Tutup'
                });
            },
            complete: function() {
                $('#bulk-update').attr('disabled', false).text('Simpan');
            }
        });
    });
});