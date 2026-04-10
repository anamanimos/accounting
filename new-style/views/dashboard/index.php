<!--begin::Content wrapper-->
<div class="d-flex flex-column flex-column-fluid">
    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">Dashboard</h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="<?= base_url('dashboard') ?>" class="text-muted text-hover-primary">Home</a>
                    </li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-muted">Dashboard</li>
                </ul>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->
    
    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">
            <!--begin::Row-->
            <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
                <!--begin::Col-->
                <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3">
                    <!--begin::Card widget-->
                    <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-md-50 mb-5 mb-xl-10" style="background-color: #3E97FF;">
                        <div class="card-header pt-5">
                            <div class="card-title d-flex flex-column">
                                <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2"><?= number_format($total_rekening) ?></span>
                                <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Total Rekening</span>
                            </div>
                        </div>
                        <div class="card-body d-flex align-items-end pt-0">
                            <div class="d-flex align-items-center flex-column mt-3 w-100">
                                <a href="<?= base_url('rekening') ?>" class="btn btn-sm btn-light fw-bold">Lihat Detail</a>
                            </div>
                        </div>
                    </div>
                    <!--end::Card widget-->
                    
                    <!--begin::Card widget-->
                    <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-md-50 mb-5 mb-xl-10" style="background-color: #50CD89;">
                        <div class="card-header pt-5">
                            <div class="card-title d-flex flex-column">
                                <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2"><?= number_format($total_jurnal) ?></span>
                                <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Total Jurnal</span>
                            </div>
                        </div>
                        <div class="card-body d-flex align-items-end pt-0">
                            <div class="d-flex align-items-center flex-column mt-3 w-100">
                                <a href="<?= base_url('jurnal_umum') ?>" class="btn btn-sm btn-light fw-bold">Lihat Detail</a>
                            </div>
                        </div>
                    </div>
                    <!--end::Card widget-->
                </div>
                <!--end::Col-->
                
                <!--begin::Col-->
                <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3">
                    <!--begin::Card widget-->
                    <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-md-50 mb-5 mb-xl-10" style="background-color: #F1416C;">
                        <div class="card-header pt-5">
                            <div class="card-title d-flex flex-column">
                                <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2"><?= format_rupiah($total_debet, false) ?></span>
                                <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Total Debet (<?= date('F Y') ?>)</span>
                            </div>
                        </div>
                        <div class="card-body d-flex align-items-end pt-0">
                            <div class="d-flex align-items-center flex-column mt-3 w-100">
                                <span class="text-white opacity-75 fs-7">Bulan ini</span>
                            </div>
                        </div>
                    </div>
                    <!--end::Card widget-->
                    
                    <!--begin::Card widget-->
                    <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-md-50 mb-5 mb-xl-10" style="background-color: #7239EA;">
                        <div class="card-header pt-5">
                            <div class="card-title d-flex flex-column">
                                <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2"><?= format_rupiah($total_kredit, false) ?></span>
                                <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Total Kredit (<?= date('F Y') ?>)</span>
                            </div>
                        </div>
                        <div class="card-body d-flex align-items-end pt-0">
                            <div class="d-flex align-items-center flex-column mt-3 w-100">
                                <span class="text-white opacity-75 fs-7">Bulan ini</span>
                            </div>
                        </div>
                    </div>
                    <!--end::Card widget-->
                </div>
                <!--end::Col-->
                
                <!--begin::Col-->
                <div class="col-xxl-6">
                    <!--begin::Chart widget-->
                    <div class="card card-flush h-md-100">
                        <div class="card-header pt-5">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-gray-900">Grafik Jurnal <?= date('Y') ?></span>
                                <span class="text-gray-500 mt-1 fw-semibold fs-6">Debet vs Kredit per Bulan</span>
                            </h3>
                        </div>
                        <div class="card-body pt-5">
                            <div id="chart_jurnal" style="height: 300px;"></div>
                        </div>
                    </div>
                    <!--end::Chart widget-->
                </div>
                <!--end::Col-->
            </div>
            <!--end::Row-->
            
            <!--begin::Row-->
            <div class="row g-5 g-xl-10">
                <!--begin::Col-->
                <div class="col-xl-12">
                    <div class="card card-flush h-lg-100">
                        <div class="card-header pt-5">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-gray-900">Selamat Datang, <?= $user->nama_lengkap ?>!</span>
                                <span class="text-gray-500 mt-1 fw-semibold fs-6">Anda login sebagai <span class="badge badge-light-primary"><?= ucwords($user->level) ?></span></span>
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="d-flex flex-wrap gap-3">
                                <a href="<?= base_url('jurnal_umum/create') ?>" class="btn btn-primary">
                                    <i class="ki-outline ki-plus fs-2"></i> Input Jurnal Baru
                                </a>
                                <a href="<?= base_url('laporan/neraca_saldo') ?>" class="btn btn-light-primary">
                                    <i class="ki-outline ki-document fs-2"></i> Lihat Neraca Saldo
                                </a>
                                <a href="<?= base_url('laporan/laba_rugi') ?>" class="btn btn-light-success">
                                    <i class="ki-outline ki-chart fs-2"></i> Lihat Laba Rugi
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Col-->
            </div>
            <!--end::Row-->
        </div>
    </div>
    <!--end::Content-->
</div>
<!--end::Content wrapper-->

<script>
document.addEventListener('DOMContentLoaded', function() {
    var chartData = <?= $chart_data ?>;
    var months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    
    var options = {
        series: [{
            name: 'Debet',
            data: chartData.debet
        }, {
            name: 'Kredit',
            data: chartData.kredit
        }],
        chart: {
            type: 'bar',
            height: 300,
            toolbar: {
                show: false
            }
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '55%',
                borderRadius: 5
            },
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            show: true,
            width: 2,
            colors: ['transparent']
        },
        xaxis: {
            categories: months,
        },
        yaxis: {
            labels: {
                formatter: function (value) {
                    return new Intl.NumberFormat('id-ID').format(value);
                }
            }
        },
        fill: {
            opacity: 1
        },
        tooltip: {
            y: {
                formatter: function (val) {
                    return "Rp " + new Intl.NumberFormat('id-ID').format(val);
                }
            }
        },
        colors: ['#F1416C', '#7239EA']
    };

    var chart = new ApexCharts(document.querySelector("#chart_jurnal"), options);
    chart.render();
});
</script>
