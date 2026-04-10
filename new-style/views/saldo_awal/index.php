<!--begin::Content wrapper-->
<div class="d-flex flex-column flex-column-fluid">
    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">Saldo Awal</h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="<?= base_url('dashboard') ?>" class="text-muted text-hover-primary">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-muted">Saldo Awal</li>
                </ul>
            </div>
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <a href="<?= base_url('saldo_awal/create') ?>" class="btn btn-sm fw-bold btn-primary">
                    <i class="ki-outline ki-plus fs-5"></i> Tambah Saldo Awal
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
                    <form action="<?= base_url('saldo_awal') ?>" method="get" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">Periode Tahun</label>
                            <select name="periode" class="form-select form-select-solid">
                                <?php foreach ($list_periode as $p): ?>
                                <option value="<?= $p ?>" <?= $p == $periode ? 'selected' : '' ?>><?= $p ?></option>
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
                        <span class="card-label fw-bold text-gray-900">Saldo Awal Periode <?= $periode ?></span>
                    </h3>
                </div>
                <div class="card-body pt-0">
                    <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_saldo_table">
                        <thead>
                            <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                                <th class="min-w-100px">No. Rekening</th>
                                <th class="min-w-150px">Nama Rekening</th>
                                <th class="min-w-100px text-end">Debet</th>
                                <th class="min-w-100px text-end">Kredit</th>
                                <th class="text-end min-w-80px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 fw-semibold">
                            <?php 
                            $total_debet = 0;
                            $total_kredit = 0;
                            foreach ($saldo_awal as $row): 
                                $total_debet += $row->debet;
                                $total_kredit += $row->kredit;
                            ?>
                            <tr>
                                <td><span class="fw-bold"><?= $row->no_rek ?></span></td>
                                <td><?= $row->nama_rek ?></td>
                                <td class="text-end"><?= $row->debet > 0 ? format_rupiah($row->debet, false) : '-' ?></td>
                                <td class="text-end"><?= $row->kredit > 0 ? format_rupiah($row->kredit, false) : '-' ?></td>
                                <td class="text-end">
                                    <a href="<?= base_url('saldo_awal/edit/' . $row->id) ?>" class="btn btn-sm btn-icon btn-light-primary me-1" title="Edit">
                                        <i class="ki-outline ki-pencil fs-5"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-icon btn-light-danger btn-delete" data-url="<?= base_url('saldo_awal/delete/' . $row->id) ?>" title="Hapus">
                                        <i class="ki-outline ki-trash fs-5"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($saldo_awal)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-5">Belum ada data saldo awal untuk periode ini</td></tr>
                            <?php endif; ?>
                        </tbody>
                        <?php if (!empty($saldo_awal)): ?>
                        <tfoot>
                            <tr class="fw-bold fs-5 border-top border-2">
                                <td colspan="2" class="text-end">Total:</td>
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
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-delete').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var url = this.dataset.url;
            Swal.fire({
                title: 'Hapus Saldo Awal?',
                text: 'Data akan dihapus permanen.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#F1416C'
            }).then(function(result) {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        });
    });
});
</script>
