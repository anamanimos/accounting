<!--begin::Content wrapper-->
<div class="d-flex flex-column flex-column-fluid">
    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">Jurnal Penyesuaian</h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="<?= base_url('dashboard') ?>" class="text-muted text-hover-primary">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-muted">Jurnal Penyesuaian</li>
                </ul>
            </div>
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <a href="<?= base_url('jurnal_penyesuaian/create') ?>" class="btn btn-sm fw-bold btn-primary">
                    <i class="ki-outline ki-plus fs-5"></i> Input Jurnal Penyesuaian
                </a>
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

            <!--begin::Filter-->
            <div class="card mb-5">
                <div class="card-body">
                    <form action="<?= base_url('jurnal_penyesuaian') ?>" method="get" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">Dari Tanggal</label>
                            <input type="date" name="tgl_dari" class="form-control form-control-solid" value="<?= $tgl_dari ?>" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Sampai Tanggal</label>
                            <input type="date" name="tgl_sampai" class="form-control form-control-solid" value="<?= $tgl_sampai ?>" />
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="ki-outline ki-magnifier fs-5"></i> Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <!--end::Filter-->

            <div class="card">
                <div class="card-body pt-5">
                    <table class="table align-middle table-row-dashed fs-6 gy-5">
                        <thead>
                            <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                                <th class="min-w-100px">No. Jurnal</th>
                                <th class="min-w-100px">Tanggal</th>
                                <th class="min-w-80px">No. Rek</th>
                                <th class="min-w-150px">Keterangan</th>
                                <th class="min-w-100px text-end">Debet</th>
                                <th class="min-w-100px text-end">Kredit</th>
                                <th class="text-end min-w-80px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 fw-semibold">
                            <?php 
                            $current_no = '';
                            $total_debet = 0;
                            $total_kredit = 0;
                            foreach ($jurnal as $row): 
                                $total_debet += $row->debet;
                                $total_kredit += $row->kredit;
                            ?>
                            <tr>
                                <td>
                                    <?php if ($current_no !== $row->no_jurnal): ?>
                                    <span class="fw-bold text-primary"><?= $row->no_jurnal ?></span>
                                    <?php $current_no = $row->no_jurnal; else: ?>
                                    <span class="text-muted">↳</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d/m/Y', strtotime($row->tgl_jurnal)) ?></td>
                                <td><span class="badge badge-light-info"><?= $row->no_rek ?></span></td>
                                <td><?= strlen($row->ket) > 40 ? substr($row->ket, 0, 40) . '...' : $row->ket ?></td>
                                <td class="text-end"><?= $row->debet > 0 ? format_rupiah($row->debet, false) : '-' ?></td>
                                <td class="text-end"><?= $row->kredit > 0 ? format_rupiah($row->kredit, false) : '-' ?></td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-icon btn-light-danger btn-delete" data-url="<?= base_url('jurnal_penyesuaian/delete/' . $row->no_jurnal) ?>">
                                        <i class="ki-outline ki-trash fs-5"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($jurnal)): ?>
                            <tr><td colspan="7" class="text-center text-muted py-5">Tidak ada data</td></tr>
                            <?php endif; ?>
                        </tbody>
                        <?php if (!empty($jurnal)): ?>
                        <tfoot>
                            <tr class="fw-bold fs-5 border-top border-2">
                                <td colspan="4" class="text-end">Total:</td>
                                <td class="text-end text-primary"><?= format_rupiah($total_debet, false) ?></td>
                                <td class="text-end text-success"><?= format_rupiah($total_kredit, false) ?></td>
                                <td></td>
                            </tr>
                        </tfoot>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!--end::Content-->
</div>
<!--end::Content wrapper-->

<script>
document.querySelectorAll('.btn-delete').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var url = this.dataset.url;
        Swal.fire({
            title: 'Hapus Jurnal?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#F1416C'
        }).then(function(result) {
            if (result.isConfirmed) window.location.href = url;
        });
    });
});
</script>
