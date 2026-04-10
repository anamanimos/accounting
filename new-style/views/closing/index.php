<!--begin::Content wrapper-->
<div class="d-flex flex-column flex-column-fluid">
    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">Tutup Buku</h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="<?= base_url('dashboard') ?>" class="text-muted text-hover-primary">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-muted">Tutup Buku</li>
                </ul>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->
    
    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">
            <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success d-flex align-items-center p-5 mb-5">
                <i class="ki-outline ki-shield-tick fs-2hx text-success me-4"></i>
                <div class="d-flex flex-column"><span><?= $this->session->flashdata('success') ?></span></div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger d-flex align-items-center p-5 mb-5">
                <i class="ki-outline ki-shield-cross fs-2hx text-danger me-4"></i>
                <div class="d-flex flex-column"><span><?= $this->session->flashdata('error') ?></span></div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <div class="row g-5">
                <!--begin::Tutup Bulan-->
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ki-outline ki-calendar fs-2 text-primary me-2"></i>
                                Tutup Bulan
                            </h3>
                        </div>
                        <div class="card-body">
                            <p class="text-gray-600 mb-5">
                                Tutup buku bulanan akan mengunci transaksi pada bulan yang dipilih.
                            </p>
                            <form action="<?= base_url('closing/tutup_bulan') ?>" method="post" id="form_bulan">
                                <div class="mb-5">
                                    <label class="form-label">Bulan</label>
                                    <select name="bulan" class="form-select form-select-solid" required>
                                        <option value="">-- Pilih Bulan --</option>
                                        <?php 
                                        $bulan_list = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                                        for ($i = 1; $i <= 12; $i++): ?>
                                        <option value="<?= $i ?>" <?= $i == date('n') ? 'selected' : '' ?>><?= $bulan_list[$i-1] ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="mb-5">
                                    <label class="form-label">Tahun</label>
                                    <select name="tahun" class="form-select form-select-solid" required>
                                        <?php foreach ($list_tahun as $th): ?>
                                        <option value="<?= $th ?>" <?= $th == date('Y') ? 'selected' : '' ?>><?= $th ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary btn-tutup-bulan">
                                    <i class="ki-outline ki-lock fs-5"></i> Tutup Bulan
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <!--end::Tutup Bulan-->

                <!--begin::Tutup Tahun-->
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ki-outline ki-calendar-tick fs-2 text-success me-2"></i>
                                Tutup Tahun
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed mb-5 p-5">
                                <i class="ki-outline ki-information-5 fs-2tx text-warning me-4"></i>
                                <div class="d-flex flex-column">
                                    <h4 class="mb-1 text-warning">Perhatian!</h4>
                                    <span class="text-gray-700">Tutup tahun akan membuat saldo awal untuk periode berikutnya berdasarkan neraca saldo tahun yang dipilih.</span>
                                </div>
                            </div>
                            <form action="<?= base_url('closing/tutup_tahun') ?>" method="post" id="form_tahun">
                                <div class="mb-5">
                                    <label class="form-label">Tahun</label>
                                    <select name="tahun" class="form-select form-select-solid" required>
                                        <?php foreach ($list_tahun as $th): ?>
                                        <option value="<?= $th ?>"><?= $th ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-success btn-tutup-tahun">
                                    <i class="ki-outline ki-lock fs-5"></i> Tutup Tahun
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <!--end::Tutup Tahun-->
            </div>
        </div>
    </div>
    <!--end::Content-->
</div>
<!--end::Content wrapper-->

<script>
document.querySelector('.btn-tutup-bulan').addEventListener('click', function(e) {
    e.preventDefault();
    Swal.fire({
        title: 'Tutup Bulan?',
        text: 'Proses ini akan mengunci transaksi pada bulan yang dipilih.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Tutup Bulan!',
        cancelButtonText: 'Batal'
    }).then(function(result) {
        if (result.isConfirmed) {
            let form = document.getElementById('form_bulan');
            let formData = new FormData(form);
            
            // Tampilkan loading state
            Swal.fire({
                title: 'Memproses...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            axios.post(form.action, formData, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(function (response) {
                if (response.data.status === 'success') {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: response.data.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(function() {
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Gagal', response.data.message, 'error');
                }
            })
            .catch(function (error) {
                Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
            });
        }
    });
});

document.querySelector('.btn-tutup-tahun').addEventListener('click', function(e) {
    e.preventDefault();
    Swal.fire({
        title: 'Tutup Tahun?',
        text: 'Proses ini akan membuat saldo awal untuk periode berikutnya.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Tutup Tahun!',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#50CD89'
    }).then(function(result) {
        if (result.isConfirmed) {
            let form = document.getElementById('form_tahun');
            let formData = new FormData(form);
            
            Swal.fire({
                title: 'Memproses...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            axios.post(form.action, formData, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(function (response) {
                if (response.data.status === 'success') {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: response.data.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(function() {
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Gagal', response.data.message, 'error');
                }
            })
            .catch(function (error) {
                Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
            });
        }
    });
});
</script>
