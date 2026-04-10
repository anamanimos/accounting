<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('format_rupiah')) {
    function format_rupiah($angka, $prefix = true)
    {
        if ($prefix) {
            return "Rp " . number_format($angka, 0, ',', '.');
        }
        return number_format($angka, 0, ',', '.');
    }
}

if (!function_exists('generate_initials')) {
    function generate_initials($name)
    {
        if (empty($name)) return 'U';
        $words = explode(" ", $name);
        $initials = "";
        foreach ($words as $w) {
            if(!empty($w)) {
                $initials .= $w[0];
            }
        }
        return strtoupper(substr($initials, 0, 2));
    }
}
