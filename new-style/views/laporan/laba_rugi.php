<!--begin::Content wrapper-->
<div class="d-flex flex-column flex-column-fluid">
    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">Laporan Laba Rugi</h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="<?= base_url('dashboard') ?>" class="text-muted text-hover-primary">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-muted">Laporan</li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-muted">Laba Rugi</li>
                </ul>
            </div>
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <button type="button" class="btn btn-sm fw-bold btn-light-primary" onclick="window.print()">
                    <i class="ki-outline ki-printer fs-5"></i> Cetak
                </button>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->
    
    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">
            <!--begin::Filter-->
            <div class="card mb-5">
                <div class="card-body">
                    <form action="<?= base_url('laporan/laba_rugi') ?>" method="get" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">Periode Tahun</label>
                            <select name="tahun" class="form-select form-select-solid">
                                <?php foreach ($list_tahun as $th): ?>
                                <option value="<?= $th ?>" <?= $th == $tahun ? 'selected' : '' ?>><?= $th ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="ki-outline ki-magnifier fs-5"></i> Tampilkan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <!--end::Filter-->

            <div class="row g-5">
                <!--begin::Pendapatan-->
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header border-0 pt-6">
                            <h3 class="card-title">
                                <span class="card-label fw-bold text-gray-900">
                                    <i class="ki-outline ki-arrow-up-right text-success fs-2"></i> Pendapatan
                                </span>
                            </h3>
                        </div>
                        <div class="card-body pt-0">
                            <table class="table table-row-dashed fs-6 gy-4">
                                <tbody class="text-gray-600 fw-semibold">
                                    <?php foreach ($pendapatan as $row): ?>
                                    <tr>
                                        <td>
                                            <span class="badge badge-light-primary me-2"><?= $row->no_rek ?></span>
                                            <?= $row->nama_rek ?>
                                        </td>
                                        <td class="text-end"><?= format_rupiah($row->saldo, false) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($pendapatan)): ?>
                                    <tr><td colspan="2" class="text-center text-muted">Tidak ada data</td></tr>
                                    <?php endif; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="fw-bold fs-5 border-top border-2">
                                        <td class="text-end">Total Pendapatan:</td>
                                        <td class="text-end text-success"><?= format_rupiah($total_pendapatan, false) ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <!--end::Pendapatan-->

                <!--begin::Beban-->
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header border-0 pt-6">
                            <h3 class="card-title">
                                <span class="card-label fw-bold text-gray-900">
                                    <i class="ki-outline ki-arrow-down-left text-danger fs-2"></i> Beban
                                </span>
                            </h3>
                        </div>
                        <div class="card-body pt-0">
                            <table class="table table-row-dashed fs-6 gy-4">
                                <tbody class="text-gray-600 fw-semibold">
                                    <?php foreach ($beban as $row): ?>
                                    <tr>
                                        <td>
                                            <span class="badge badge-light-danger me-2"><?= $row->no_rek ?></span>
                                            <?= $row->nama_rek ?>
                                        </td>
                                        <td class="text-end"><?= format_rupiah($row->saldo, false) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($beban)): ?>
                                    <tr><td colspan="2" class="text-center text-muted">Tidak ada data</td></tr>
                                    <?php endif; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="fw-bold fs-5 border-top border-2">
                                        <td class="text-end">Total Beban:</td>
                                        <td class="text-end text-danger"><?= format_rupiah($total_beban, false) ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <!--end::Beban-->
            </div>

            <!--begin::Summary-->
            <div class="card mt-5">
                <div class="card-body text-center py-10">
                    <h2 class="text-gray-800 fw-bold mb-5">
                        <?= $laba_rugi >= 0 ? 'LABA BERSIH' : 'RUGI BERSIH' ?>
                    </h2>
                    <h1 class="<?= $laba_rugi >= 0 ? 'text-success' : 'text-danger' ?> fw-bolder" style="font-size: 3rem;">
                        <?= format_rupiah(abs($laba_rugi)) ?>
                    </h1>
                    <p class="text-gray-500 fs-6 mt-3">
                        Periode: Tahun <?= $tahun ?>
                    </p>
                </div>
            </div>
            <!--end::Summary-->
        </div>
    </div>
    <!--end::Content-->
</div>
<!--end::Content wrapper-->
