$(document).ready(function() {
    // Initialize DataTable
    var table = $('#table_verificator_list').DataTable({
        "lengthChange": false,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "dom": "lrtip"
    });

    // Custom search
    $('[data-kt-filter="search"]').on('keyup', function() {
        table.search(this.value).draw();
    });
});
