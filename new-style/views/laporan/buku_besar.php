<!--begin::Content wrapper-->
<div class="d-flex flex-column flex-column-fluid">
    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">Buku Besar</h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="<?= base_url('dashboard') ?>" class="text-muted text-hover-primary">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-muted">Laporan</li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-muted">Buku Besar</li>
                </ul>
            </div>
            <?php if ($no_rek): ?>
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <button type="button" class="btn btn-sm fw-bold btn-light-primary" onclick="window.print()">
                    <i class="ki-outline ki-printer fs-5"></i> Cetak
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <!--end::Toolbar-->
    
    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">
            <!--begin::Filter-->
            <div class="card mb-5">
                <div class="card-body">
                    <form action="<?= base_url('laporan/buku_besar') ?>" method="get" class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label">Pilih Rekening</label>
                            <select name="no_rek" class="form-select form-select-solid" required>
                                <option value="">-- Pilih Rekening --</option>
                                <?php foreach ($rekening_list as $kode => $nama): ?>
                                <option value="<?= $kode ?>" <?= $kode == $no_rek ? 'selected' : '' ?>><?= $nama ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
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

            <?php if ($rekening): ?>
            <div class="card">
                <div class="card-header border-0 pt-6">
                    <h3 class="card-title">
                        <span class="card-label fw-bold text-gray-900">
                            Buku Besar: <?= $rekening->no_rek ?> - <?= $rekening->nama_rek ?>
                        </span>
                    </h3>
                    <div class="card-toolbar">
                        <span class="badge badge-light-info fs-6">Tahun <?= $tahun ?></span>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <table class="table align-middle table-row-dashed fs-6 gy-5">
                        <thead>
                            <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                                <th class="min-w-100px">Tanggal</th>
                                <th class="min-w-100px">No. Jurnal</th>
                                <th class="min-w-150px">Keterangan</th>
                                <th class="min-w-100px text-end">Debet</th>
                                <th class="min-w-100px text-end">Kredit</th>
                                <th class="min-w-100px text-end">Saldo</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 fw-semibold">
                            <tr class="bg-light">
                                <td colspan="5" class="fw-bold">Saldo Awal</td>
                                <td class="text-end fw-bold"><?= format_rupiah($saldo_awal, false) ?></td>
                            </tr>
                            <?php foreach ($jurnal as $row): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($row->tgl_jurnal)) ?></td>
                                <td>
                                    <a href="<?= base_url('jurnal_umum/view/' . $row->no_jurnal) ?>" class="text-primary">
                                        <?= $row->no_jurnal ?>
                                    </a>
                                </td>
                                <td><?= strlen($row->ket) > 30 ? substr($row->ket, 0, 30) . '...' : $row->ket ?></td>
                                <td class="text-end"><?= $row->debet > 0 ? format_rupiah($row->debet, false) : '-' ?></td>
                                <td class="text-end"><?= $row->kredit > 0 ? format_rupiah($row->kredit, false) : '-' ?></td>
                                <td class="text-end fw-bold <?= $row->saldo >= 0 ? 'text-primary' : 'text-danger' ?>">
                                    <?= format_rupiah($row->saldo, false) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($jurnal)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-5">Tidak ada transaksi pada periode ini</td></tr>
                            <?php endif; ?>
                        </tbody>
                        <?php if (!empty($jurnal)): ?>
                        <tfoot>
                            <tr class="fw-bold fs-5 border-top border-2 bg-light">
                                <td colspan="5" class="text-end">Saldo Akhir:</td>
                                <td class="text-end <?= end($jurnal)->saldo >= 0 ? 'text-primary' : 'text-danger' ?>">
                                    <?= format_rupiah(end($jurnal)->saldo, false) ?>
                                </td>
                            </tr>
                        </tfoot>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
            <?php else: ?>
            <div class="card">
                <div class="card-body py-15 text-center">
                    <i class="ki-outline ki-book-open fs-5tx text-gray-300 mb-5"></i>
                    <h3 class="text-gray-600">Pilih rekening untuk melihat buku besar</h3>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <!--end::Content-->
</div>
<!--end::Content wrapper-->
