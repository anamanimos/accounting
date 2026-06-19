<!--begin::Content wrapper-->
<div class="d-flex flex-column flex-column-fluid">
    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">Master Harga Jual</h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="<?= base_url('home') ?>" class="text-muted text-hover-primary">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-muted">Control Panel</li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-muted">Master Harga Jual</li>
                </ul>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->
    
    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">
            <div class="card">
                <div class="card-header border-0 pt-6">
                    <div class="card-title">
                        <div class="d-flex align-items-center position-relative my-1">
                            <i class="ki-outline ki-magnifier fs-3 position-absolute ms-5"></i>
                            <input type="text" id="search_table" class="form-control form-control-solid w-250px ps-13" placeholder="Cari Data..." />
                        </div>
                    </div>
                    <div class="card-toolbar">
                        <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
                            <button type="button" class="btn btn-primary" onclick="tambahData()">
                                <i class="ki-outline ki-plus fs-2"></i> Tambah Data
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body py-4">
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed fs-6 gy-5" id="table_master_harga">
                            <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                    <th class="w-10px pe-2">No</th>
                                    <th class="min-w-125px">Deskripsi</th>
                                    <th class="min-w-125px">Harga per CM (Rp)</th>
                                    <th class="text-end min-w-100px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 fw-semibold">
                                <?php 
                                $no = 1;
                                foreach($data->result() as $row): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= $row->deskripsi ?></td>
                                    <td><?= number_format($row->harga_jual, 0, ',', '.') ?></td>
                                    <td class="text-end">
                                        <button class="btn btn-icon btn-active-light-primary w-30px h-30px me-3" onclick="editData(<?= $row->id ?>)">
                                            <i class="ki-outline ki-pencil fs-3"></i>
                                        </button>
                                        <button class="btn btn-icon btn-active-light-danger w-30px h-30px" onclick="hapusData(<?= $row->id ?>)">
                                            <i class="ki-outline ki-trash fs-3"></i>
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

<!-- Modal -->
<div class="modal fade" id="modal_form" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold" id="modal_title">Tambah Data</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>
            <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                <form id="form_data" class="form" action="#">
                    <input type="hidden" name="id" id="id">
                    <div class="d-flex flex-column mb-7 fv-row">
                        <label class="d-flex align-items-center fs-6 fw-semibold form-label mb-2">
                            <span class="required">Deskripsi</span>
                        </label>
                        <input type="text" class="form-control form-control-solid" placeholder="Masukkan deskripsi (cth: DTF)" name="deskripsi" id="deskripsi" />
                    </div>
                    <div class="d-flex flex-column mb-7 fv-row">
                        <label class="d-flex align-items-center fs-6 fw-semibold form-label mb-2">
                            <span class="required">Harga per CM</span>
                        </label>
                        <input type="number" class="form-control form-control-solid" placeholder="Masukkan harga per cm" name="harga_jual" id="harga_jual" />
                    </div>
                    <div class="text-center pt-15">
                        <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-primary" id="btn_simpan" onclick="simpanData()">
                            <span class="indicator-label">Simpan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $("#search_table").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#table_master_harga tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
});

function tambahData() {
    $('#form_data')[0].reset();
    $('#id').val('');
    $('#modal_title').text('Tambah Data');
    $('#modal_form').modal('show');
}

function editData(id) {
    $.ajax({
        url: baseUrl + 'master_harga/edit',
        type: 'POST',
        data: {id: id},
        dataType: 'json',
        success: function(res) {
            if(res.status == 'success') {
                $('#id').val(res.data.id);
                $('#deskripsi').val(res.data.deskripsi);
                $('#harga_jual').val(res.data.harga_jual);
                $('#modal_title').text('Edit Data');
                $('#modal_form').modal('show');
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        }
    });
}

function simpanData() {
    var data = $('#form_data').serialize();
    $.ajax({
        url: baseUrl + 'master_harga/simpan',
        type: 'POST',
        data: data,
        dataType: 'json',
        success: function(res) {
            if(res.status == 'success') {
                $('#modal_form').modal('hide');
                Swal.fire('Berhasil', res.message, 'success').then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        }
    });
}

function hapusData(id) {
    Swal.fire({
        title: 'Yakin hapus data ini?',
        text: "Data yang dihapus tidak dapat dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: baseUrl + 'master_harga/hapus',
                type: 'POST',
                data: {id: id},
                dataType: 'json',
                success: function(res) {
                    if(res.status == 'success') {
                        Swal.fire('Terhapus!', res.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                }
            });
        }
    });
}
</script>
