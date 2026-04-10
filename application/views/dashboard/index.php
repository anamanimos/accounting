<!--begin::Content wrapper-->
<div class="d-flex flex-column flex-column-fluid">
    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">Dashboard</h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="<?= base_url('home') ?>" class="text-muted text-hover-primary">Home</a>
                    </li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-muted">Dashboard</li>
                </ul>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->
    
    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">
            
            <!--begin::Welcome Message-->
            <div class="card bg-light-primary border-primary border border-dashed mb-5 mb-xl-10">
                <div class="card-body py-4">
                    <div class="d-flex align-items-center">
                        <i class="ki-outline ki-profile-user fs-1 text-primary me-3"></i>
                        <span class="fs-4 fw-semibold text-gray-800">
                            Hai, Selamat datang <b><?= $user->nama_lengkap ?? $this->session->userdata('nama_lengkap') ?></b> di Manajemen <b><?= $nama_program ?? 'Sistem Akuntansi Standar' ?></b>
                        </span>
                    </div>
                </div>
            </div>
            <!--end::Welcome Message-->

            <div class="mb-5">
                <h3 class="fw-bold text-gray-900 fs-2 mb-5">CONTROL PANEL</h3>
            </div>

            <!--begin::Row-->
            <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
                
                <!--begin::Col-->
                <div class="col-md-4 col-xl-2 col-6">
                    <a href="<?= base_url('rekening') ?>" class="card card-flush hover-elevate-up shadow-sm parent-hover">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center py-8">
                            <i class="ki-outline ki-bank fs-3x text-primary mb-3"></i>
                            <span class="text-gray-900 fw-bold fs-6 text-center">Rekening / COA</span>
                        </div>
                    </a>
                </div>
                <!--end::Col-->

                <!--begin::Col-->
                <div class="col-md-4 col-xl-2 col-6">
                    <a href="<?= base_url('saldo_awal') ?>" class="card card-flush hover-elevate-up shadow-sm parent-hover">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center py-8">
                            <i class="ki-outline ki-wallet fs-3x text-success mb-3"></i>
                            <span class="text-gray-900 fw-bold fs-6 text-center">Saldo Awal</span>
                        </div>
                    </a>
                </div>
                <!--end::Col-->

                <!--begin::Col-->
                <div class="col-md-4 col-xl-2 col-6">
                    <a href="<?= base_url('jurnal_umum') ?>" class="card card-flush hover-elevate-up shadow-sm parent-hover">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center py-8">
                            <i class="ki-outline ki-book fs-3x text-info mb-3"></i>
                            <span class="text-gray-900 fw-bold fs-6 text-center">Jurnal Umum</span>
                        </div>
                    </a>
                </div>
                <!--end::Col-->

                <!--begin::Col-->
                <div class="col-md-4 col-xl-2 col-6">
                    <a href="<?= base_url('jurnal_penyesuaian') ?>" class="card card-flush hover-elevate-up shadow-sm parent-hover">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center py-8">
                            <i class="ki-outline ki-setting-2 fs-3x text-warning mb-3"></i>
                            <span class="text-gray-900 fw-bold fs-6 text-center">Jurnal Penyesuaian</span>
                        </div>
                    </a>
                </div>
                <!--end::Col-->

                <!--begin::Col-->
                <div class="col-md-4 col-xl-2 col-6">
                    <a href="<?= base_url('buku_besar') ?>" class="card card-flush hover-elevate-up shadow-sm parent-hover">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center py-8">
                            <i class="ki-outline ki-document fs-3x text-danger mb-3"></i>
                            <span class="text-gray-900 fw-bold fs-6 text-center">Buku Besar</span>
                        </div>
                    </a>
                </div>
                <!--end::Col-->

            </div>
            <!--end::Row-->
            
            <!--begin::Notes-->
            <div class="card card-flush shadow-sm">
                <div class="card-header pt-5">
                    <h3 class="card-title">
                        <span class="card-icon"><i class="ki-outline ki-information-5 text-primary fs-2"></i></span>
                        <span class="card-label fw-bold text-gray-800">Catatan</span>
                    </h3>
                </div>
                <div class="card-body">
                    <ol class="fs-6 text-gray-700">
                        <li class="mb-2">Kasus yang ditangani pada akuntansi standard ini adalah Perusahaan Jasa</li>
                        <li class="mb-2">Pastikan jurnal Anda diisi dengan benar</li>
                        <li>Kalo ada perubahan No.Rek (COA) pada Prive Pemilik Modal. Ubah/edit pada kode program lap_neraca/view_data</li>
                    </ol>
                </div>
            </div>
            <!--end::Notes-->

        </div>
    </div>
    <!--end::Content-->
</div>
<!--end::Content wrapper-->
