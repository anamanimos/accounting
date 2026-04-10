<!--begin::Content wrapper-->
<div class="d-flex flex-column flex-column-fluid">
    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">Neraca Saldo</h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="<?= base_url('dashboard') ?>" class="text-muted text-hover-primary">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-muted">Laporan</li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-muted">Neraca Saldo</li>
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
                    <form action="<?= base_url('laporan/neraca_saldo') ?>" method="get" class="row g-3 align-items-end">
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

            <div class="card">
                <div class="card-header border-0 pt-6">
                    <h3 class="card-title">
                        <span class="card-label fw-bold text-gray-900">Neraca Saldo Periode <?= $tahun ?></span>
                    </h3>
                </div>
                <div class="card-body pt-0">
                    <table class="table align-middle table-row-dashed fs-6 gy-5">
                        <thead>
                            <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                                <th class="min-w-100px">No. Rekening</th>
                                <th class="min-w-200px">Nama Rekening</th>
                                <th class="min-w-120px text-end">Debet</th>
                                <th class="min-w-120px text-end">Kredit</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 fw-semibold">
                            <?php foreach ($rekening as $row): ?>
                            <tr>
                                <td><span class="fw-bold"><?= $row->no_rek ?></span></td>
                                <td><?= $row->nama_rek ?></td>
                                <td class="text-end"><?= $row->debet > 0 ? format_rupiah($row->debet, false) : '-' ?></td>
                                <td class="text-end"><?= $row->kredit > 0 ? format_rupiah($row->kredit, false) : '-' ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold fs-5 border-top border-2">
                                <td colspan="2" class="text-end">Total:</td>
                                <td class="text-end text-primary"><?= format_rupiah($total_debet, false) ?></td>
                                <td class="text-end text-success"><?= format_rupiah($total_kredit, false) ?></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-center">
                                    <?php if (abs($total_debet - $total_kredit) < 0.01): ?>
                                    <span class="badge badge-light-success fs-6">Balance ✓</span>
                                    <?php else: ?>
                                    <span class="badge badge-light-danger fs-6">Tidak Balance (Selisih: <?= format_rupiah(abs($total_debet - $total_kredit), false) ?>)</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!--end::Content-->
</div>
<!--end::Content wrapper-->
