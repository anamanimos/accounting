# Modern Dashboard UI Design Guidelines

Whenever creating new pages, views, or controllers in this project, ALWAYS follow these UI guidelines:

1. **Use Modern Template Layout**:
   - In controllers, render pages using `$d['content'] = 'view_folder/index'; $this->load->view('templates/main', $d);`.
   - Never render new pages inside the legacy 2017 EasyUI (`home.php`) iframe/tab system.

2. **Dashboard Card & Page Structure**:
   - Use standard Metronic 8 / Bootstrap 5 dashboard layout container:
     ```html
     <div class="d-flex flex-column flex-column-fluid">
         <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
             <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                 <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                     <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">Page Title</h1>
                     <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                         <li class="breadcrumb-item text-muted"><a href="<?= base_url('home') ?>" class="text-muted text-hover-primary">Dashboard</a></li>
                         <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                         <li class="breadcrumb-item text-muted">Section</li>
                     </ul>
                 </div>
             </div>
         </div>

         <div id="kt_app_content" class="app-content flex-column-fluid">
             <div id="kt_app_content_container" class="app-container container-fluid">
                 <!-- Cards & Widgets -->
                 <div class="card shadow-sm mb-5">
                     <div class="card-header border-0 pt-6">
                         <h3 class="card-title fw-bold text-gray-900">Card Header</h3>
                     </div>
                     <div class="card-body py-4">
                         <!-- Content -->
                     </div>
                 </div>
             </div>
         </div>
     </div>
     ```

3. **Form Controls & Buttons**:
   - Use `.form-control.form-control-solid` or `.form-select.form-select-solid`.
   - Use `.btn.btn-primary`, `.btn.btn-light`, `.btn.btn-danger`, etc.
   - Use SweetAlert2 (`Swal.fire`) for user notifications and confirmation dialogs.

4. **Tables**:
   - Use `.table.align-middle.table-row-dashed.fs-6.gy-5` for datatables.
   - Format action buttons cleanly using `.btn.btn-icon.w-30px.h-30px`.

5. **Menu Registrations**:
   - Add new sidebar menu items to `json/menu.json` with appropriate `type`, `url`, `title`, `icon` (Keenthemes `ki-outline`), and `levels`.
