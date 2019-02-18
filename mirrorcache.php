<?php

/*
 * Copyright (C) 2018 Nethesis S.r.l.
 * http://www.nethesis.it - nethserver@nethesis.it
 *
 * This script is part of NethServer.
 *
 * NethServer is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License,
 * or any later version.
 *
 * NethServer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NethServer.  If not, see COPYING.
 */


function _get_cache_file_name($release, $arch) {
    return "/var/cache/httpd/mirrorlist-centos-${release}.${arch}.lst";
}

/**
 * Run this function in a cronjob to refresh the mirrors cache
 */ 
function refresh_centos_mirrors_cache($cc_map, $release, $arch)
{
    $filter_url = function ($url) {
        if(filter_var($url, FILTER_VALIDATE_URL)) {
            $url = preg_replace('/\/[0-9]\.[0-9].*$/', '', $url, 1);
            return trim($url);
        }
        return FALSE;
    };
    
    $mirrors = array();

    foreach($cc_map as $cc => $nb) {
        $rh = curl_init();
        curl_setopt($rh, CURLOPT_URL, "http://mirrorlist.centos.org/?release=${release}&arch=${arch}&repo=updates&cc=${cc}");
        curl_setopt($rh, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($rh, CURLOPT_HEADER, 0);
        curl_setopt($rh, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($rh, CURLOPT_MAXREDIRS, 5);
        // Extract $nb items from the response, trimming the release number suffix:
        $cc_mirrors = array_slice(array_filter(array_map($filter_url, explode("\n", curl_exec($rh)))), 0, $nb);
        curl_close($rh);

        if(empty($cc_mirrors)) {
            error_log("[ERROR] $cc mirror list is empty!");
            continue;
        } else {
            $mirrors = array_merge($mirrors, $cc_mirrors);
        }
    }

    return file_put_contents(_get_cache_file_name($release, $arch), implode("\n", $mirrors));
}


/**
 * Retrieve the CentOS mirror list from cache
 */
function get_centos_mirrors($release, $arch)
{
    $cache_file = _get_cache_file_name($release, $arch);

    if(file_exists($cache_file)) {
        // Valid cache file
        $mirrors = array_map('trim', file($cache_file));
    } else {
        // Invalid cache file
        $mirrors = array();
    }

    // fallback to default mirrorlist or return valid entries
    return empty($mirrors) ? array('http://mirror.centos.org/centos') : $mirrors;
}
