<style>
#dataTableDetail tbody tr:nth-child(even) { background-color: var(--bs-light); }
#dataTableDetail tbody tr:hover { background-color: var(--bs-light-primary); }
</style>
<script type="text/javascript">
function hapusData(id){
	var no_jurnal = $("#no_jurnal").val();
	var string = "no_jurnal="+no_jurnal+"&no_rek="+id;
	
    Swal.fire({
        title: 'Konfirmasi',
        text: 'Data yang akan dihapus no rek = ' + id + '?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type	: 'POST',
                url		: "<?php echo site_url(); ?>/jurnal_umum/hapusDetail",
                data	: string,
                cache	: false,
                success	: function(data){
                    $("#tampil_data").html(data);
                }
            });
        }
    });
}
</script>

<div class="table-responsive mt-5" style="max-height: 400px; overflow-y: auto; overflow-x: auto;">
    <table id="dataTableDetail" class="table table-row-dashed table-bordered table-row-gray-300 align-middle gs-0 gy-3 fs-7 text-nowrap">
        <thead class="position-sticky top-0 z-index-1">
            <tr class="fw-bold text-muted bg-light text-nowrap">
                <th class="ps-4 rounded-start text-center">No</th>
                <th class="text-center">#Rek</th>
                <th>Nama Rek</th>
                <th class="text-end">Debet</th>
                <th class="text-end">Kredit</th>
                <th class="pe-4 rounded-end text-center">Hapus</th>
            </tr>
        </thead>
        <tbody>
        <?php 
        if($data->num_rows() > 0){
            $t_dr =0;
            $t_kr =0;
            $no=1;
            foreach($data->result() as $t){
                $nama_rek = $this->app_model->CariNamaRek($t->no_rek);
        ?>
            <tr>
                <td width="30" class="text-center"><?php echo $no;?></td>
                <td width="100" class="text-center"><span class="badge badge-light-primary fw-bold"><?php echo $t->no_rek;?></span></td>
                <td><?php echo $nama_rek;?></td>
                <td class="text-end fw-bold"><?php echo number_format($t->debet);?></td>
                <td class="text-end fw-bold"><?php echo number_format($t->kredit);?></td>
                <td class="text-center" width="60">
                    <?php echo "<a href='javascript:hapusData(\"{$t->no_rek}\")' class='btn btn-icon btn-sm btn-light-danger h-30px w-30px'>";?>
                    <i class="ki-outline ki-trash fs-5"></i>
                    </a>
                </td>
            </tr>        
        <?php	
                $t_dr =$t_dr+$t->debet;
                $t_kr =$t_kr+$t->kredit;
                $no++;
            }
        ?>

        <?php
        }else{
            $t_dr =0;
            $t_kr =0;
        ?>
        <tr>
            <td colspan="6" class="text-center text-muted">Tidak ada data</td>
        </tr>
        <?php 
        }
        ?>
        </tbody>
        <tfoot class="position-sticky bottom-0 z-index-1 bg-light">
            <tr class="fw-bold border-top border-gray-300">
                <td colspan="3" class="text-end pe-4">SALDO</td>
                <td class="text-end text-success fs-6"><?php echo number_format($t_dr);?></td>
                <td class="text-end text-success fs-6"><?php echo number_format($t_kr);?></td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>    