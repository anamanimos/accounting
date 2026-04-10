<!DOCTYPE html>
<html lang="en">
<!--begin::Head-->

<head>
   <title><?php if (isset($title)) {
               echo $title . ' | E-SPPD - Perum Jasa Tirta 1';
            } else {
               echo 'E-SPPD - Perum Jasa Tirta 1';
            } ?></title>
   <meta charset="utf-8" />
   <link rel="shortcut icon" href="<?= base_url('assets/icon.png') ?>" />
   <!--begin::Fonts(mandatory for all pages)-->
   <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
   <!--end::Fonts-->
   <!--begin::Vendor Stylesheets(used for this page only)-->
   <?php
   if (isset($css)) {
      foreach ($css as $file) {
         echo '<link href="' . $file . '" rel="stylesheet" type="text/css"/>';
      }
   }
   ?>

   <!--end::Vendor Stylesheets-->
   <!--begin::Global Stylesheets Bundle(mandatory for all pages)-->
   <link href="<?= base_url('assets/plugins/global/plugins.bundle.css') ?>" rel="stylesheet" type="text/css" />
   <link href="<?= base_url('assets/css/style.bundle.css') ?>" rel="stylesheet" type="text/css" />
   <!--end::Global Stylesheets Bundle-->
   <script>
      // Frame-busting to prevent site from being loaded within a frame without permission (click-jacking)
      if (window.top != window.self) {
         window.top.location.replace(window.self.location.href);
      }
   </script>
</head>
<!--end::Head-->
<!--begin::Body-->

<body id="kt_app_body" data-kt-app-layout="light-sidebar" data-kt-app-header-fixed="true" data-kt-app-sidebar-enabled="true" data-kt-app-sidebar-fixed="true" data-kt-app-sidebar-hoverable="true" data-kt-app-sidebar-push-header="true" data-kt-app-sidebar-push-toolbar="true" data-kt-app-sidebar-push-footer="true" data-kt-app-toolbar-enabled="true" class="app-default">
   <!--begin::Theme mode setup on page load-->
   <script>
      var defaultThemeMode = "light";
      var themeMode;

      if (document.documentElement) {
         if (document.documentElement.hasAttribute("data-bs-theme-mode")) {
            themeMode = document.documentElement.getAttribute("data-bs-theme-mode");
         } else {
            if (localStorage.getItem("data-bs-theme") !== null) {
               themeMode = localStorage.getItem("data-bs-theme");
            } else {
               themeMode = defaultThemeMode;
            }
         }

         if (themeMode === "system") {
            themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
         }

         document.documentElement.setAttribute("data-bs-theme", themeMode);
      }
   </script>
   <!--end::Theme mode setup on page load-->
   <!--begin::Global JS Variables-->
   <script>
      var baseUrl = "<?= base_url() ?>";
   </script>
   <!--end::Global JS Variables-->
   <!--begin::App-->
   <div class="d-flex flex-column flex-root app-root" id="kt_app_root">
      <!--begin::Page-->
      <div class="app-page  flex-column flex-column-fluid " id="kt_app_page">
         <!--begin::Header-->
         <div id="kt_app_header" class="app-header "
            data-kt-sticky="true" data-kt-sticky-activate="{default: true, lg: true}" data-kt-sticky-name="app-header-minimize" data-kt-sticky-offset="{default: '200px', lg: '0'}" data-kt-sticky-animation="false">
            <!--begin::Header container-->
            <div class="app-container  container-fluid d-flex align-items-stretch justify-content-between " id="kt_app_header_container">
               <!--begin::Sidebar mobile toggle-->
               <div class="d-flex align-items-center d-lg-none ms-n3 me-1 me-md-2" title="Show sidebar menu">
                  <div class="btn btn-icon btn-active-color-primary w-35px h-35px" id="kt_app_sidebar_mobile_toggle">
                     <i class="ki-outline ki-abstract-14 fs-2 fs-md-1"></i>
                  </div>
               </div>
               <!--end::Sidebar mobile toggle-->
               <!--begin::Mobile logo-->
               <div class="d-flex align-items-center flex-grow-1 flex-lg-grow-0">
                  <a href="/metronic8/demo1/index.html" class="d-lg-none">
                     <img alt="Logo" src="<?= base_url() ?>assets/icon.png" class="h-30px" />
                  </a>
               </div>
               <!--end::Mobile logo-->
               <!--begin::Header wrapper-->
               <div class="d-flex align-items-stretch justify-content-between flex-lg-grow-1" id="kt_app_header_wrapper">
                  <!--begin::Menu wrapper-->
                  <div
                     class="
                        app-header-menu 
                        app-header-mobile-drawer 
                        align-items-stretch
                        "
                     data-kt-drawer="true"
                     data-kt-drawer-name="app-header-menu"
                     data-kt-drawer-activate="{default: true, lg: false}"
                     data-kt-drawer-overlay="true"
                     data-kt-drawer-width="250px"
                     data-kt-drawer-direction="end"
                     data-kt-drawer-toggle="#kt_app_header_menu_toggle"
                     data-kt-swapper="true"
                     data-kt-swapper-mode="{default: 'append', lg: 'prepend'}"
                     data-kt-swapper-parent="{default: '#kt_app_body', lg: '#kt_app_header_wrapper'}">
                  </div>
                  <!--end::Menu wrapper-->
                  <!--begin::Navbar-->
                  <div class="app-navbar flex-shrink-0">
                     <!--begin::Theme mode-->
                     <div class="app-navbar-item ms-1 ms-md-4">
                        <!--begin::Menu toggle-->
                        <a href="#" class="btn btn-icon btn-custom btn-icon-muted btn-active-light btn-active-color-primary w-35px h-35px" data-kt-menu-trigger="{default:'click', lg: 'hover'}" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
                           <i class="ki-outline ki-night-day theme-light-show fs-1"></i> <i class="ki-outline ki-moon theme-dark-show fs-1"></i></a>
                        <!--begin::Menu toggle-->
                        <!--begin::Menu-->
                        <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-title-gray-700 menu-icon-gray-500 menu-active-bg menu-state-color fw-semibold py-4 fs-base w-150px" data-kt-menu="true" data-kt-element="theme-mode-menu">
                           <!--begin::Menu item-->
                           <div class="menu-item px-3 my-0">
                              <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="light">
                                 <span class="menu-icon" data-kt-element="icon">
                                    <i class="ki-outline ki-night-day fs-2"></i> </span>
                                 <span class="menu-title">
                                    Light
                                 </span>
                              </a>
                           </div>
                           <!--end::Menu item-->
                           <!--begin::Menu item-->
                           <div class="menu-item px-3 my-0">
                              <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="dark">
                                 <span class="menu-icon" data-kt-element="icon">
                                    <i class="ki-outline ki-moon fs-2"></i> </span>
                                 <span class="menu-title">
                                    Dark
                                 </span>
                              </a>
                           </div>
                           <!--end::Menu item-->
                           <!--begin::Menu item-->
                           <div class="menu-item px-3 my-0">
                              <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="system">
                                 <span class="menu-icon" data-kt-element="icon">
                                    <i class="ki-outline ki-screen fs-2"></i> </span>
                                 <span class="menu-title">
                                    System
                                 </span>
                              </a>
                           </div>
                           <!--end::Menu item-->
                        </div>
                        <!--end::Menu-->
                     </div>
                     <!--end::Theme mode-->
                     <!--begin::User menu-->
                     <div class="app-navbar-item ms-1 ms-md-4" id="kt_header_user_menu_toggle">
                        <!--begin::Menu wrapper-->
                        <div class="cursor-pointer symbol symbol-35px"
                           data-kt-menu-trigger="{default: 'click', lg: 'hover'}"
                           data-kt-menu-attach="parent"
                           data-kt-menu-placement="bottom-end">
                           <div class="symbol-label fs-5 fw-semibold bg-primary text-inverse-primary"><?= generate_initials($user->nama_lengkap ?? 'User') ?></div>
                        </div>

                        <!--begin::User account menu-->
                        <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-color fw-semibold py-4 fs-6 w-275px" data-kt-menu="true">
                           <!--begin::Menu item-->
                           <div class="menu-item px-3">
                              <div class="menu-content d-flex align-items-center px-3">
                                 <!--begin::Avatar-->
                                 <div class="symbol symbol-50px me-5">
                                    <div class="symbol-label fs-2 fw-semibold bg-primary text-inverse-primary"><?= generate_initials($user->nama_lengkap ?? 'User') ?></div>
                                 </div>
                                 <!--end::Avatar-->
                                 <!--begin::Username-->
                                 <div class="d-flex flex-column">
                                    <div class="fw-bold d-flex align-items-center fs-5">
                                       <?= $user->nama_lengkap ?? 'User' ?>
                                    </div>
                                    <a href="javascript:void(0);" class="fw-semibold text-muted text-hover-primary fs-7">
                                       <?= $user->email ?? '' ?>
                                    </a>
                                 </div>
                                 <!--end::Username-->
                              </div>
                           </div>
                           <!--end::Menu item-->
                           <!--begin::Menu separator-->
                           <div class="separator my-2"></div>
                           <!--end::Menu separator-->
                           <!--begin::Menu item-->
                           <div class="menu-item px-5 my-1">
                              <a href="javascript:void(0);" class="menu-link px-5">
                                 Pengaturan Akun
                              </a>
                           </div>
                           <!--end::Menu item-->
                           <!--begin::Menu item-->
                           <div class="menu-item px-5">
                              <a href="<?= base_url('auth/logout') ?>" class="menu-link px-5 js-logout">
                                 Keluar
                              </a>
                           </div>
                           <!--end::Menu item-->
                        </div>
                        <!--end::User account menu-->
                        <!--end::Menu wrapper-->
                     </div>
                     <!--end::User menu-->
                     <!--begin::Aside toggle-->
                  </div>
                  <!--end::Navbar-->
               </div>
               <!--end::Header wrapper-->
            </div>
            <!--end::Header container-->
         </div>
         <!--end::Header-->
         <!--begin::Wrapper-->
         <div class="app-wrapper  flex-column flex-row-fluid " id="kt_app_wrapper">
            <!--begin::Sidebar-->
            <div id="kt_app_sidebar" class="app-sidebar  flex-column "
               data-kt-drawer="true" data-kt-drawer-name="app-sidebar" data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="225px" data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">
               <!--begin::Logo-->
               <div class="app-sidebar-logo px-6" id="kt_app_sidebar_logo">
                  <!--begin::Logo image-->
                  <a href="<?= base_url() ?>">
                     <img alt="Logo" src="<?= base_url() ?>assets/logo-web.png" class="h-25px app-sidebar-logo-default theme-light-show" />
                     <img alt="Logo" src="<?= base_url() ?>assets/media/logos/esppd-logo-dark.svg" class="h-25px app-sidebar-logo-default theme-dark-show" />
                     <img alt="Logo" src="<?= base_url() ?>assets/icon.png" class="h-20px app-sidebar-logo-minimize" />
                  </a>
                  <!--end::Logo image-->
                  <!--begin::Sidebar toggle-->
                  <!--begin::Minimized sidebar setup:
                        if (isset($_COOKIE["sidebar_minimize_state"]) && $_COOKIE["sidebar_minimize_state"] === "on") { 
                            1. "src/js/layout/sidebar.js" adds "sidebar_minimize_state" cookie value to save the sidebar minimize state.
                            2. Set data-kt-app-sidebar-minimize="on" attribute for body tag.
                            3. Set data-kt-toggle-state="active" attribute to the toggle element with "kt_app_sidebar_toggle" id.
                            4. Add "active" class to to sidebar toggle element with "kt_app_sidebar_toggle" id.
                        }
                        -->
                  <div
                     id="kt_app_sidebar_toggle"
                     class="app-sidebar-toggle btn btn-icon btn-shadow btn-sm btn-color-muted btn-active-color-primary h-30px w-30px position-absolute top-50 start-100 translate-middle rotate "
                     data-kt-toggle="true"
                     data-kt-toggle-state="active"
                     data-kt-toggle-target="body"
                     data-kt-toggle-name="app-sidebar-minimize">
                     <i class="ki-outline ki-black-left-line fs-3 rotate-180"></i>
                  </div>
                  <!--end::Sidebar toggle-->
               </div>
               <!--end::Logo-->
               <!--begin::sidebar menu-->
               <div class="app-sidebar-menu overflow-hidden flex-column-fluid">
                  <!--begin::Menu wrapper-->
                  <div id="kt_app_sidebar_menu_wrapper" class="app-sidebar-wrapper">
                     <!--begin::Scroll wrapper-->
                     <div
                        id="kt_app_sidebar_menu_scroll"
                        class="scroll-y my-5 mx-3"
                        data-kt-scroll="true"
                        data-kt-scroll-activate="true"
                        data-kt-scroll-height="auto"
                        data-kt-scroll-dependencies="#kt_app_sidebar_logo, #kt_app_sidebar_footer"
                        data-kt-scroll-wrappers="#kt_app_sidebar_menu"
                        data-kt-scroll-offset="5px"
                        data-kt-scroll-save-state="true">
                        <!--begin::Menu-->
                        <div
                           class="menu menu-column menu-rounded menu-sub-indention fw-semibold fs-6"
                           id="#kt_app_sidebar_menu"
                           data-kt-menu="true"
                           data-kt-menu-expand="false">
                           <?php
                           if (empty($this->uri->segment(2))) {
                              $url = str_replace('/', '', $this->uri->segment(1));
                           } else {
                              $url = $this->uri->segment(1) . '/' . $this->uri->segment(2);
                           }

                           // Get user role_id from session (1=superadmin, 2=admin, 3=user)
                           $session_user = $this->session->userdata('user');
                           $user_role_id = isset($session_user['role_id']) ? (int)$session_user['role_id'] : 3;

                           $menu_file = 'json/menu.json';
                           if (file_exists($menu_file)) {
                              $menu_json = file_get_contents($menu_file);
                              $menu_items = json_decode($menu_json, true);

                              foreach ($menu_items as $menu_item) {
                                 // Role-based filtering using role_ids array
                                 if (isset($menu_item['role_ids']) && !in_array($user_role_id, $menu_item['role_ids'])) {
                                    continue; // Skip this menu item if user role_id is not allowed
                                 }

                                 // Inject Badge for 'user/approval'
                                 if (isset($menu_item['url']) && $menu_item['url'] == 'user/approval' && isset($user)) {
                                     $CI =& get_instance();
                                     $CI->load->model('Travel_request_model');
                                     $pending_count = $CI->Travel_request_model->count_pending_approvals($user->REF_ID);
                                     if ($pending_count > 0) {
                                         $menu_item['badge'] = [
                                             'class' => 'badge-danger',
                                             'text' => $pending_count
                                         ];
                                     }
                                 }

                                 // Inject Badge for 'secretary/travel_request'
                                 if (isset($menu_item['url']) && $menu_item['url'] == 'secretary/travel_request' && isset($user)) {
                                     $CI =& get_instance();
                                     $CI->load->model('Travel_request_model');
                                     $action_count = $CI->Travel_request_model->count_secretary_actions_needed($user->REF_ID);
                                     if ($action_count > 0) {
                                         $menu_item['badge'] = [
                                             'class' => 'badge-danger',
                                             'text' => $action_count
                                         ];
                                     }
                                 }

                                 if ($menu_item['type'] == 'heading') {
                                    echo '<div class="menu-item pt-5"><div class="menu-content"><span class="menu-heading fw-bold text-uppercase fs-7">' . $menu_item['title'] . '</span></div></div>';
                                 }

                                 if ($menu_item['type'] == 'link') {
                                    if (isset($menu_item['badge'])) {
                                       $badge = '<span class="badge badge-circle ' . $menu_item['badge']['class'] . '">' . $menu_item['badge']['text'] . '</span>';
                                    } else {
                                       $badge = '';
                                    }
                           ?>
                                    <!--begin:Menu item-->
                                    <div class="menu-item">
                                       <!--begin:Menu link-->
                                       <a class="menu-link <?= $url == $menu_item['url'] ? 'active' : '' ?>" href="<?= base_url($menu_item['url']) ?>"><span class="menu-icon">
                                             <i class="ki-outline <?= $menu_item['icon'] ?> fs-2"></i>
                                          </span><span class="menu-title"><?= $menu_item['title'] ?></span><?= $badge ?>
                                       </a>
                                       <!--end:Menu link-->
                                    </div>
                                    <!--end:Menu item-->
                                 <?php
                                 }

                                 if ($menu_item['type'] == 'accordion') {
                                 ?>
                                    <!--begin:Menu item-->
                                    <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                                       <!--begin:Menu link-->
                                       <span class="menu-link">
                                          <span class="menu-icon">
                                             <i class="ki-outline <?= $menu_item['icon'] ?> fs-2"></i>
                                          </span>
                                          <span class="menu-title"><?= $menu_item['title'] ?>&nbsp;&nbsp;<?= $badge ?></span>
                                          <span class="menu-arrow"></span>
                                       </span>
                                       <!--end:Menu link-->
                                       <?php
                                       if (isset($menu_item['submenu'])) {
                                       ?>
                                          <!--begin:Menu sub-->
                                          <div class="menu-sub menu-sub-accordion">
                                             <?php
                                             foreach ($menu_item['submenu'] as $submenu) {
                                                if (isset($submenu['badge'])) {
                                                   $badge = '<span class="badge badge-circle ' . $submenu['badge']['class'] . '">' . $submenu['badge']['text'] . '</span>';
                                                } else {
                                                   $badge = '';
                                                }
                                             ?>
                                                <!--begin:Menu item-->
                                                <div class="menu-item">
                                                   <!--begin:Menu link-->
                                                   <a class="menu-link <?= $url == $submenu['url'] ? 'active' : '' ?>" href="<?= base_url($submenu['url']) ?>">
                                                      <span class="menu-icon">
                                                         <i class="ki-outline <?= $submenu['icon'] ?> fs-2"></i>
                                                      </span>
                                                      <span class="menu-title"><?= $submenu['title'] ?></span><?= $badge ?>
                                                   </a>
                                                   <!--end:Menu link-->
                                                </div>
                                                <!--end:Menu item-->
                                             <?php
                                             }
                                             ?>
                                          </div>
                                          <!--end:Menu sub-->
                                       <?php
                                       }
                                       ?>
                                    </div>
                                    <!--end:Menu sub-->
                           <?php
                                 }
                              }
                           }
                           ?>
                        </div>
                        <!--end::Menu-->
                     </div>
                     <!--end::Scroll wrapper-->
                  </div>
                  <!--end::Menu wrapper-->
               </div>
               <!--end::sidebar menu-->
            </div>
            <!--end::Sidebar-->
            <!--begin::Main-->
            <div class="app-main flex-column flex-row-fluid " id="kt_app_main">
               <!--begin::Content wrapper-->
               <?php isset($content) ? $this->load->view($content, get_defined_vars()) : $this->load->view('templates/default-content') ?>
               <!--end::Content wrapper-->
               <!--begin::Footer-->
               <div id="kt_app_footer" class="app-footer ">
                  <!--begin::Footer container-->
                  <div class="app-container  container-fluid d-flex flex-column flex-md-row flex-center flex-md-stack py-3 ">
                     <!--begin::Copyright-->
                     <div class="text-gray-900 order-2 order-md-1">
                        <span class="text-muted fw-semibold me-1">2025 &copy;</span>
                        <a href="#" target="_blank" class="text-gray-800 text-hover-primary">Perum Jasa Tirta 1</a>
                     </div>
                     <!--end::Copyright-->
                     <!--begin::Menu-->
                     <ul class="menu menu-gray-600 menu-hover-primary fw-semibold order-1">
                     </ul>
                     <!--end::Menu-->
                  </div>
                  <!--end::Footer container-->
               </div>
               <!--end::Footer-->
            </div>
            <!--end:::Main-->
         </div>
         <!--end::Wrapper-->
      </div>
      <!--end::Page-->
   </div>
   <!--end::App-->

   <!--begin::Scrolltop-->
   <div id="kt_scrolltop" class="scrolltop" data-kt-scrolltop="true">
      <i class="ki-outline ki-arrow-up"></i>
   </div>
   <!--end::Scrolltop-->

   <!--begin::Modals-->
   <!--end::Modals-->

   <!--begin::Javascript-->
   <script>
      var appUrl = "<?= base_url() ?>";
      var hostUrl = "<?= base_url() ?>assets/";
      var BASE_URL = "<?= base_url() ?>";
   </script>
   <!--begin::Global Javascript Bundle(mandatory for all pages)-->
   <script src="<?= base_url() ?>assets/plugins/global/plugins.bundle.js"></script>
   <script src="<?= base_url() ?>assets/js/scripts.bundle.js"></script>
   <!--end::Global Javascript Bundle-->
   <!--begin::Vendors Javascript(used for this page only)-->
   <?php
   if (isset($js_vendors)) {
      foreach ($js_vendors as $file) {
         echo '<script src="' . $file . '"></script>';
      }
   }
   ?>
   <!--end::Vendors Javascript-->

   <!--begin::Custom Javascript(used for this page only)-->
   <?php
   if (isset($js_custom)) {
      foreach ($js_custom as $file) {
         echo '<script src="' . $file . '"></script>';
      }
   }
   ?>

   <!--end::Custom Javascript-->

   <script>
      (function() {
         var logoutLink = document.querySelector('.js-logout');
         if (!logoutLink) return;
         logoutLink.addEventListener('click', function(e) {
            e.preventDefault();
            var href = this.getAttribute('href');
            if (typeof Swal === 'undefined') {
               window.location.href = href;
               return;
            }
            Swal.fire({
               title: 'Keluar dari aplikasi?',
               text: 'Anda akan keluar dari sesi saat ini.',
               icon: 'warning',
               showCancelButton: true,
               confirmButtonText: 'Ya, keluar',
               cancelButtonText: 'Batal'
            }).then(function(result) {
               if (result.isConfirmed) {
                  window.location.href = href;
               }
            });
         });
      })();
   </script>

   <!--end::Javascript-->
</body>
<!--end::Body-->

</html>