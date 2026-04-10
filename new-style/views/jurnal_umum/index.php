<!--begin::Content wrapper-->
<div class="d-flex flex-column flex-column-fluid">
    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">Jurnal Umum</h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="<?= base_url('dashboard') ?>" class="text-muted text-hover-primary">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-muted">Jurnal Umum</li>
                </ul>
            </div>
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <a href="<?= base_url('jurnal_umum/create') ?>" class="btn btn-sm fw-bold btn-primary">
                    <i class="ki-outline ki-plus fs-5"></i> Input Jurnal
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
                <div class="d-flex flex-column">
                    <span><?= $this->session->flashdata('success') ?></span>
                </div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger d-flex align-items-center p-5 mb-5">
                <i class="ki-outline ki-shield-cross fs-2hx text-danger me-4"></i>
                <div class="d-flex flex-column">
                    <span><?= $this->session->flashdata('error') ?></span>
                </div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!--begin::Card-->
            <div class="card">
                <!--begin::Card header-->
                <div class="card-header border-0 pt-6">
                    <div class="card-title w-100 d-flex justify-content-between align-items-center">
                        
                        <!-- Left side filters -->
                        <div class="d-flex align-items-center gap-2">
                            <!-- Filter trigger -->
                            <button type="button" class="btn btn-sm btn-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-start">
                                <i class="ki-outline ki-plus fs-2"></i> Tambah Filter
                            </button>
                            
                            <button type="button" id="btn_reset" class="btn btn-sm btn-light">
                                <i class="ki-outline ki-arrows-circle fs-2"></i> Reset
                            </button>

                            <!-- Filter Menu -->
                            <div class="menu menu-sub menu-sub-dropdown w-300px w-md-325px" data-kt-menu="true">
                                <div class="px-7 py-5">
                                    <div class="fs-5 text-dark fw-bold">Opsi Filter</div>
                                </div>
                                <div class="separator border-gray-200"></div>
                                <div class="px-7 py-5" data-kt-user-table-filter="form">
                                    <!-- Date range filter -->
                                    <div class="mb-5">
                                        <label class="form-label fs-6 fw-semibold">Rentang Tanggal</label>
                                        <input class="form-control form-control-solid" placeholder="Pilih tanggal" id="filter_date_range"/>
                                    </div>
                                    <input type="hidden" id="tgl_dari" />
                                    <input type="hidden" id="tgl_sampai" />

                                    <!-- Rekening filter -->
                                    <div class="mb-5">
                                        <label class="form-label fs-6 fw-semibold">Rekening</label>
                                        <select id="filter_rekening" class="form-select form-select-solid" data-kt-select2="true" data-placeholder="Pilih opsi" data-allow-clear="true" data-hide-search="false">
                                            <option></option>
                                            <?php foreach ($rekening_list as $no => $nama): ?>
                                            <option value="<?= $no ?>"><?= $no ?> - <?= $nama ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="d-flex justify-content-end">
                                        <button type="button" class="btn btn-primary fw-semibold px-6" data-kt-menu-dismiss="true" id="btn_apply_filter">Terapkan</button>
                                    </div>
                                </div>
                            </div>
                            <!-- End Filter Menu -->
                        </div>

                        <!-- Right side search -->
                        <div class="d-flex align-items-center position-relative my-1">
                            <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                            <input type="text" id="search_jurnal" class="form-control form-control-solid form-control-sm w-200px ps-12" placeholder="Cari data..." />
                        </div>

                    </div>
                </div>
                <!--end::Card header-->
                
                <div class="card-body pt-0">
                    <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_jurnal_table">
                        <thead>
                            <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                                <th class="min-w-100px">No. Jurnal</th>
                                <th class="min-w-100px">Tanggal</th>
                                <th class="min-w-100px">No. Bukti</th>
                                <th class="min-w-80px">No. Rek</th>
                                <th class="min-w-150px">Keterangan</th>
                                <th class="min-w-100px text-end">Debet</th>
                                <th class="min-w-100px text-end">Kredit</th>
                                <th class="text-end min-w-80px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 fw-semibold">
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
    var table = $('#kt_jurnal_table').DataTable({
        processing: true,
        serverSide: true,
        order: [],
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        ajax: {
            url: "<?= base_url('jurnal_umum/ajax_list') ?>",
            type: "POST",
            data: function ( data ) {
                data.tgl_dari = $('#tgl_dari').val();
                data.tgl_sampai = $('#tgl_sampai').val();
                data.no_rek_filter = $('#filter_rekening').val();
                data.search.value = $('#search_jurnal').val();
            }
        },
        columnDefs: [
            { className: "text-end", targets: [5, 6, 7] },
            { orderable: false, targets: [0, 7] }
        ],
        dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        language: {
            lengthMenu: "Tampilkan _MENU_ data",
            zeroRecords: "Tidak ada data ditemukan",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Selanjutnya",
                previous: "Sebelumnya"
            }
        }
    });

    // Init flatpickr for date range
    $("#filter_date_range").flatpickr({
        mode: "range",
        dateFormat: "Y-m-d",
        onChange: function(selectedDates, dateStr, instance) {
            if (selectedDates.length === 2) {
                let range = dateStr.split(" to ");
                $('#tgl_dari').val(range[0]);
                $('#tgl_sampai').val(range[1]);
            } else {
                $('#tgl_dari').val('');
                $('#tgl_sampai').val('');
            }
        }
    });

    // Custom Filters Event Listeners
    $('#search_jurnal').on('keyup', function() {
        table.draw();
    });

    $('#btn_apply_filter').on('click', function() {
        table.draw();
    });

    $('#btn_reset').on('click', function() {
        $('#search_jurnal').val('');
        $('#filter_date_range').val('');
        if ($('#filter_date_range')[0]._flatpickr) {
            $('#filter_date_range')[0]._flatpickr.clear();
        }
        $('#tgl_dari').val('');
        $('#tgl_sampai').val('');
        $('#filter_rekening').val(null).trigger('change');
        table.draw();
    });
});
</script>
