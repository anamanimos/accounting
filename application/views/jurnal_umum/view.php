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

<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {

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

});
</script>
<div id="tampil_data"></div>
            </div>
        </div>
    </div>
    <!--end::Content-->
</div>