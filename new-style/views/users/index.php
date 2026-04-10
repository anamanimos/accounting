<!--begin::Content wrapper-->
<div class="d-flex flex-column flex-column-fluid">
    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">Manajemen User</h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="<?= base_url('dashboard') ?>" class="text-muted text-hover-primary">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-muted">Users</li>
                </ul>
            </div>
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <a href="<?= base_url('users/create') ?>" class="btn btn-sm fw-bold btn-primary">
                    <i class="ki-outline ki-plus fs-5"></i> Tambah User
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

            <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger d-flex align-items-center p-5 mb-5">
                <i class="ki-outline ki-shield-cross fs-2hx text-danger me-4"></i>
                <div class="d-flex flex-column"><span><?= $this->session->flashdata('error') ?></span></div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body pt-5">
                    <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_users_table">
                        <thead>
                            <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                                <th class="w-50px">No</th>
                                <th class="min-w-125px">Username</th>
                                <th class="min-w-150px">Nama Lengkap</th>
                                <th class="min-w-125px">Email</th>
                                <th class="min-w-100px">Level</th>
                                <th class="min-w-80px">Status</th>
                                <th class="text-end min-w-100px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 fw-semibold">
                            <?php $no = 1; foreach ($users as $row): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><span class="fw-bold"><?= $row->username ?></span></td>
                                <td><?= $row->nama_lengkap ?></td>
                                <td><?= $row->email ?: '-' ?></td>
                                <td>
                                    <?php
                                    $badge_class = match($row->level) {
                                        'super admin' => 'badge-light-primary',
                                        'admin' => 'badge-light-success',
                                        default => 'badge-light-secondary'
                                    };
                                    ?>
                                    <span class="badge <?= $badge_class ?>"><?= ucfirst($row->level) ?></span>
                                </td>
                                <td>
                                    <?php if ($row->is_active): ?>
                                    <span class="badge badge-light-success">Aktif</span>
                                    <?php else: ?>
                                    <span class="badge badge-light-danger">Non-aktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="<?= base_url('users/edit/' . $row->id) ?>" class="btn btn-sm btn-icon btn-light-primary me-1" title="Edit">
                                        <i class="ki-outline ki-pencil fs-5"></i>
                                    </a>
                                    <?php if ($row->id != $user->id): ?>
                                    <button type="button" class="btn btn-sm btn-icon btn-light-danger btn-delete" data-url="<?= base_url('users/delete/' . $row->id) ?>" title="Hapus">
                                        <i class="ki-outline ki-trash fs-5"></i>
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
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
    $('#kt_users_table').DataTable({
        info: false,
        order: [],
        pageLength: 25,
        language: {
            search: "",
            searchPlaceholder: "Cari...",
            lengthMenu: "Tampilkan _MENU_",
            zeroRecords: "Tidak ada data"
        }
    });

    document.querySelectorAll('.btn-delete').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var url = this.dataset.url;
            Swal.fire({
                title: 'Hapus User?',
                text: 'Data akan dihapus permanen.',
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
});
</script>
