<!--begin::Content wrapper-->
<div class="d-flex flex-column flex-column-fluid">
    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">Jurnal Umum</h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="<?= base_url('home') ?>" class="text-muted text-hover-primary">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-muted">Jurnal</li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-muted">Jurnal Umum</li>
                </ul>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->
    
    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">
            <div id="jurnal-umum-container">

<div id="view">
	<div class="d-flex justify-content-between align-items-center mb-5">
		<div>
			<a href="<?php echo base_url(); ?>jurnal_umum" class="btn btn-sm btn-light-primary ms-2">
				<i class="ki-outline ki-arrows-circle fs-3"></i> Refresh
			</a>
		</div>
		<div>
			<form id="form-search" class="d-flex align-items-center">
				<label class="me-2 fw-semibold text-muted">Cari No.Jurnal/Rek:</label>
				<input type="text" name="txt_cari" id="txt_cari" class="form-control form-control-sm form-control-solid w-200px me-2" placeholder="Pencarian..." />
				<button type="submit" name="cari" id="cari" class="btn btn-sm btn-icon btn-light-success">
                    <i class="ki-outline ki-magnifier fs-3"></i>
                </button>
			</form>
		</div>
	</div>
    
    <!-- Table Container will be populated via AJAX -->
	<div id="content-table">
        <?php $this->load->view('jurnal_umum/ajax_table'); ?>
    </div>
</div>

<!-- Modal Edit Jurnal Umum -->
<div class="modal fade" id="modal-edit-jurnal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header pb-0 border-0 justify-content-end">
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>
            
            <div class="modal-body scroll-y mx-5 mx-xl-15 my-7 pt-0">
                <form id="form-edit-jurnal" class="form">
                    <input type="hidden" name="old_no_rek" id="edit_old_no_rek" />
                    
                    <div class="text-center mb-10">
                        <h1 class="mb-3 text-gray-900 fw-bold">Edit Baris Jurnal Umum</h1>
                        <div class="text-muted fw-semibold fs-6">Perbarui rincian transaksi jurnal umum</div>
                    </div>

                    <div class="row mb-7">
                        <div class="col-md-6 mb-5 mb-md-0">
                            <label class="required fs-6 fw-semibold mb-2">No. Jurnal</label>
                            <input type="text" name="no_jurnal" id="edit_no_jurnal" class="form-control form-control-solid fw-bold" readonly />
                        </div>
                        <div class="col-md-6">
                            <label class="required fs-6 fw-semibold mb-2">Tanggal Jurnal</label>
                            <input type="date" name="tgl_jurnal" id="edit_tgl_jurnal" class="form-control form-control-solid" required />
                        </div>
                    </div>

                    <div class="row mb-7">
                        <div class="col-md-6 mb-5 mb-md-0">
                            <label class="fs-6 fw-semibold mb-2">No. Bukti</label>
                            <input type="text" name="no_bukti" id="edit_no_bukti" class="form-control form-control-solid" />
                        </div>
                        <div class="col-md-6">
                            <label class="required fs-6 fw-semibold mb-2">Rekening (No. Rek)</label>
                            <select name="no_rek" id="edit_no_rek" class="form-select form-select-solid" required>
                                <?php if (isset($list_rek) && $list_rek->num_rows() > 0): ?>
                                    <?php foreach ($list_rek->result() as $rk): ?>
                                        <option value="<?= $rk->no_rek ?>"><?= $rk->no_rek ?> - <?= htmlspecialchars($rk->nama_rek) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-7">
                        <label class="required fs-6 fw-semibold mb-2">Keterangan</label>
                        <textarea name="ket" id="edit_ket" class="form-control form-control-solid" rows="3" required></textarea>
                    </div>

                    <div class="row mb-7">
                        <div class="col-md-6 mb-5 mb-md-0">
                            <label class="fs-6 fw-semibold mb-2">Debet (Rp)</label>
                            <input type="number" name="debet" id="edit_debet" class="form-control form-control-solid" min="0" value="0" />
                        </div>
                        <div class="col-md-6">
                            <label class="fs-6 fw-semibold mb-2">Kredit (Rp)</label>
                            <input type="number" name="kredit" id="edit_kredit" class="form-control form-control-solid" min="0" value="0" />
                        </div>
                    </div>

                    <div class="form-check form-check-custom form-check-solid mb-8">
                        <input class="form-check-input" type="checkbox" name="apply_all_rows" value="1" id="edit_apply_all_rows" />
                        <label class="form-check-label fw-semibold text-gray-700 fs-7" for="edit_apply_all_rows">
                            Terapkan perubahan Tanggal, No. Bukti, dan Keterangan ke seluruh baris dengan No. Jurnal yang sama
                        </label>
                    </div>

                    <div class="text-center pt-5">
                        <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" id="btn-save-edit-jurnal" class="btn btn-primary">
                            <span class="indicator-label"><i class="ki-outline ki-check-circle fs-3 me-1"></i> Simpan Perubahan</span>
                            <span class="indicator-progress">Disimpan... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {

    var modalEditEl = document.getElementById('modal-edit-jurnal');
    var modalEdit = modalEditEl ? new bootstrap.Modal(modalEditEl) : null;

    // Function to load table via AJAX
    function loadTable(url, data = {}) {
        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            beforeSend: function() {
                $('#content-table').html('<div class="d-flex justify-content-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
            },
            success: function(response) {
                $('#content-table').html(response);
            },
            error: function() {
                Swal.fire('Error', 'Gagal memuat data tabel', 'error');
            }
        });
    }

    // Handle Search Form Submit via AJAX
    $('#form-search').on('submit', function(e) {
        e.preventDefault();
        var searchData = $(this).serialize();
        loadTable('<?php echo base_url(); ?>jurnal_umum/index/0', searchData);
    });

    // Handle Pagination Clicks via AJAX
    $(document).on('click', '.ajax-pagination a', function(e) {
        e.preventDefault();
        var pageUrl = $(this).attr('href');
        var searchVal = $('#txt_cari').val();
        loadTable(pageUrl, { txt_cari: searchVal });
    });

    // Handle Click Edit Jurnal Button
    $(document).on('click', '.btn-edit-jurnal', function(e) {
        e.preventDefault();
        var noJurnal = $(this).data('nojurnal');
        var noRek = $(this).data('norek');

        $.ajax({
            url: '<?php echo base_url(); ?>jurnal_umum/get_jurnal_row',
            type: 'POST',
            data: { no_jurnal: noJurnal, no_rek: noRek },
            dataType: 'json',
            beforeSend: function() {
                Swal.fire({
                    title: 'Memuat...',
                    text: 'Mengambil data jurnal',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });
            },
            success: function(res) {
                Swal.close();
                if (res.status === 'success' && res.data) {
                    var d = res.data;
                    $('#edit_no_jurnal').val(d.no_jurnal);
                    $('#edit_old_no_rek').val(d.no_rek);
                    $('#edit_no_rek').val(d.no_rek);
                    $('#edit_tgl_jurnal').val(d.tgl_jurnal);
                    $('#edit_no_bukti').val(d.no_bukti);
                    $('#edit_ket').val(d.ket);
                    $('#edit_debet').val(d.debet);
                    $('#edit_kredit').val(d.kredit);
                    $('#edit_apply_all_rows').prop('checked', false);

                    if (modalEdit) {
                        modalEdit.show();
                    }
                } else {
                    Swal.fire('Gagal', res.message || 'Data tidak ditemukan', 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Gagal terhubung ke server', 'error');
            }
        });
    });

    // Handle Form Submit Edit Jurnal
    $('#form-edit-jurnal').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        var btn = $('#btn-save-edit-jurnal');

        $.ajax({
            url: '<?php echo base_url(); ?>jurnal_umum/update_jurnal_row',
            type: 'POST',
            data: formData,
            dataType: 'json',
            beforeSend: function() {
                btn.prop('disabled', true);
            },
            success: function(res) {
                btn.prop('disabled', false);
                if (res.status === 'success') {
                    if (modalEdit) {
                        modalEdit.hide();
                    }
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: res.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    var searchVal = $('#txt_cari').val();
                    loadTable('<?php echo base_url(); ?>jurnal_umum/index/0', { txt_cari: searchVal });
                } else {
                    Swal.fire('Gagal', res.message || 'Gagal memperbarui data', 'error');
                }
            },
            error: function() {
                btn.prop('disabled', false);
                Swal.fire('Error', 'Terjadi kesalahan pada server', 'error');
            }
        });
    });

});
</script>
<div id="tampil_data"></div>
            </div>
        </div>
    </div>
    <!--end::Content-->
</div>