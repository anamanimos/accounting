<!--begin::Content wrapper-->
<div class="d-flex flex-column flex-column-fluid">
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">Data Customer</h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted"><a href="<?= base_url('dashboard') ?>" class="text-muted text-hover-primary">Dashboard</a></li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-muted">Customer</li>
                </ul>
            </div>
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <a href="<?= base_url('customers/create') ?>" class="btn btn-sm fw-bold btn-primary">
                    <i class="ki-outline ki-plus fs-5"></i> Tambah Customer
                </a>
            </div>
        </div>
    </div>
    
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">
            <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success d-flex align-items-center p-5 mb-5">
                <i class="ki-outline ki-shield-tick fs-2hx text-success me-4"></i>
                <div class="d-flex flex-column"><span><?= $this->session->flashdata('success') ?></span></div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body pt-5">
                    <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table">
                        <thead>
                            <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                                <th>Kode</th>
                                <th>Nama</th>
                                <th>Telepon</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 fw-semibold">
                            <?php foreach ($customers as $row): ?>
                            <tr>
                                <td><span class="badge badge-light-info"><?= $row->kode ?></span></td>
                                <td class="fw-bold"><?= $row->nama ?></td>
                                <td><?= $row->telepon ?: '-' ?></td>
                                <td><?= $row->email ?: '-' ?></td>
                                <td>
                                    <?php if ($row->is_active): ?>
                                    <span class="badge badge-light-success">Aktif</span>
                                    <?php else: ?>
                                    <span class="badge badge-light-danger">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="<?= base_url('customers/edit/' . $row->id) ?>" class="btn btn-sm btn-icon btn-light-primary me-1"><i class="ki-outline ki-pencil fs-5"></i></a>
                                    <button type="button" class="btn btn-sm btn-icon btn-light-danger btn-delete" data-url="<?= base_url('customers/delete/' . $row->id) ?>"><i class="ki-outline ki-trash fs-5"></i></button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#kt_table').DataTable({ info: false, order: [], pageLength: 25 });
    $('.btn-delete').click(function() {
        var url = $(this).data('url');
        Swal.fire({ title: 'Hapus Customer?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Hapus!', cancelButtonText: 'Batal', confirmButtonColor: '#F1416C' }).then(function(r) { if (r.isConfirmed) window.location.href = url; });
    });
});
</script>
