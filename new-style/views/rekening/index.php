<!--begin::Content wrapper-->
<div class="d-flex flex-column flex-column-fluid">
    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">Chart of Accounts</h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="<?= base_url('dashboard') ?>" class="text-muted text-hover-primary">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-muted">Rekening</li>
                </ul>
            </div>
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <a href="<?= base_url('rekening/create') ?>" class="btn btn-sm fw-bold btn-primary">
                    <i class="ki-outline ki-plus fs-5"></i> Tambah Rekening
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
                <div class="card-header border-0 pt-6">
                    <div class="card-title">
                        <div class="d-flex align-items-center position-relative my-1">
                            <i class="ki-outline ki-magnifier fs-3 position-absolute ms-5"></i>
                            <input type="text" id="searchInput" class="form-control form-control-solid w-250px ps-13" placeholder="Cari rekening..." />
                        </div>
                    </div>
                    <div class="card-toolbar gap-2">
                        <button type="button" class="btn btn-sm btn-light-primary" id="btn_expand_all">
                            <i class="ki-outline ki-arrow-down fs-5"></i> Expand All
                        </button>
                        <button type="button" class="btn btn-sm btn-light" id="btn_collapse_all">
                            <i class="ki-outline ki-arrow-up fs-5"></i> Collapse All
                        </button>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed fs-6 gy-4" id="kt_rekening_tree">
                            <thead>
                                <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                                    <th class="min-w-300px">Rekening</th>
                                    <th class="min-w-100px">Tipe</th>
                                    <th class="min-w-80px">Status</th>
                                    <th class="text-end min-w-100px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 fw-semibold">
                                <?php 
                                // Group by parent level
                                $grouped = [];
                                foreach ($rekening as $row) {
                                    $parts = explode('.', $row->no_rek);
                                    $row->_depth = count($parts) - 1;
                                    $grouped[] = $row;
                                }
                                
                                $tipe_badges = [
                                    'aset' => 'badge-light-primary',
                                    'kewajiban' => 'badge-light-danger',
                                    'modal' => 'badge-light-success',
                                    'pendapatan' => 'badge-light-info',
                                    'beban' => 'badge-light-warning'
                                ];
                                
                                foreach ($grouped as $row):
                                    $depth = $row->_depth;
                                    $indent = $depth * 25;
                                    $is_parent = false;
                                    
                                    // Check if has children
                                    foreach ($rekening as $check) {
                                        if (strpos($check->no_rek, $row->no_rek . '.') === 0) {
                                            $is_parent = true;
                                            break;
                                        }
                                    }
                                    
                                    $badge = $tipe_badges[$row->tipe] ?? 'badge-light';
                                    $parent_id = $depth > 0 ? implode('.', array_slice(explode('.', $row->no_rek), 0, -1)) : '';
                                ?>
                                <tr class="rekening-row <?= $depth > 0 ? 'child-row' : 'parent-row' ?>" 
                                    data-no-rek="<?= $row->no_rek ?>" 
                                    data-parent="<?= $parent_id ?>"
                                    data-depth="<?= $depth ?>"
                                    style="<?= $depth > 1 ? 'display:none;' : '' ?>">
                                    <td>
                                        <div style="padding-left: <?= $indent ?>px;" class="d-flex align-items-center">
                                            <?php if ($is_parent): ?>
                                            <span class="toggle-btn cursor-pointer me-2 text-primary" data-no-rek="<?= $row->no_rek ?>">
                                                <i class="ki-outline ki-plus-square fs-4 toggle-icon"></i>
                                            </span>
                                            <?php else: ?>
                                            <span class="me-2 text-gray-400" style="width:20px;">
                                                <?php if ($depth > 0): ?><i class="ki-outline ki-minus fs-5"></i><?php endif; ?>
                                            </span>
                                            <?php endif; ?>
                                            <span class="<?= $depth == 0 ? 'fw-bolder text-gray-900' : ($depth == 1 ? 'fw-bold' : '') ?>">
                                                <span class="text-primary me-2"><?= $row->no_rek ?></span>
                                                <?= $row->nama_rek ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($row->tipe): ?>
                                        <span class="badge <?= $badge ?>"><?= ucfirst($row->tipe) ?></span>
                                        <?php else: ?>
                                        <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($row->is_active): ?>
                                        <span class="badge badge-light-success">Aktif</span>
                                        <?php else: ?>
                                        <span class="badge badge-light-danger">Nonaktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= base_url('rekening/edit/' . $row->no_rek) ?>" class="btn btn-sm btn-icon btn-light-primary me-1" title="Edit">
                                            <i class="ki-outline ki-pencil fs-5"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-icon btn-light-danger btn-delete" data-url="<?= base_url('rekening/delete/' . $row->no_rek) ?>" title="Hapus">
                                            <i class="ki-outline ki-trash fs-5"></i>
                                        </button>
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
    <!--end::Content-->
</div>
<!--end::Content wrapper-->

<style>
.toggle-btn { transition: transform 0.2s; }
.toggle-btn.expanded .toggle-icon { transform: rotate(45deg); }
.toggle-btn:hover { opacity: 0.8; }
.child-row { background-color: rgba(0,0,0,0.01); }
.cursor-pointer { cursor: pointer; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle children
    document.querySelectorAll('.toggle-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var noRek = this.dataset.noRek;
            var isExpanded = this.classList.contains('expanded');
            
            if (isExpanded) {
                // Collapse: hide all descendants
                this.classList.remove('expanded');
                document.querySelectorAll('.rekening-row').forEach(function(row) {
                    if (row.dataset.noRek.indexOf(noRek + '.') === 0) {
                        row.style.display = 'none';
                        // Also collapse nested toggles
                        var toggle = row.querySelector('.toggle-btn');
                        if (toggle) toggle.classList.remove('expanded');
                    }
                });
            } else {
                // Expand: show direct children only
                this.classList.add('expanded');
                document.querySelectorAll('.rekening-row').forEach(function(row) {
                    var parent = row.dataset.parent;
                    if (parent === noRek) {
                        row.style.display = '';
                    }
                });
            }
        });
    });

    // Expand All
    document.getElementById('btn_expand_all').addEventListener('click', function() {
        document.querySelectorAll('.rekening-row').forEach(function(row) {
            row.style.display = '';
        });
        document.querySelectorAll('.toggle-btn').forEach(function(btn) {
            btn.classList.add('expanded');
        });
    });

    // Collapse All
    document.getElementById('btn_collapse_all').addEventListener('click', function() {
        document.querySelectorAll('.rekening-row').forEach(function(row) {
            if (parseInt(row.dataset.depth) > 0) {
                row.style.display = 'none';
            }
        });
        document.querySelectorAll('.toggle-btn').forEach(function(btn) {
            btn.classList.remove('expanded');
        });
    });

    // Search
    document.getElementById('searchInput').addEventListener('keyup', function(e) {
        var value = e.target.value.toLowerCase();
        document.querySelectorAll('.rekening-row').forEach(function(row) {
            var text = row.textContent.toLowerCase();
            if (value === '' && parseInt(row.dataset.depth) > 1) {
                row.style.display = 'none';
            } else if (value === '' && parseInt(row.dataset.depth) <= 1) {
                row.style.display = '';
            } else {
                row.style.display = text.indexOf(value) > -1 ? '' : 'none';
            }
        });
    });

    // Delete confirmation
    document.querySelectorAll('.btn-delete').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var url = this.dataset.url;
            Swal.fire({
                title: 'Hapus Rekening?',
                text: 'Data rekening akan dihapus permanen.',
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
