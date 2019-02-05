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

function _build_cache($release, $arch, $cc_list, $cache_file)
{
    error_log("[NOTICE] Rebuilding mirror cache $cache_file");

    $requests = array();
    
    $filter_url = function ($url) {
        if(filter_var($url, FILTER_VALIDATE_URL)) {
            $url = preg_replace('/\/[0-9]\.[0-9].*$/', '', $url, 1);
            return trim($url);
        }
        return FALSE;
    };
    
    foreach($cc_list as $cc) {
        $rh = curl_init();
        curl_setopt($rh, CURLOPT_URL, "http://mirrorlist.centos.org/?release=${release}&arch=${arch}&repo=updates&cc=${cc}");
        curl_setopt($rh, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($rh, CURLOPT_HEADER, 0);
        $response[$cc] = array_slice(array_filter(array_map($filter_url, explode("\n", curl_exec($rh)))), 0, 10);
        curl_close($rh);
    }

    file_put_contents($cache_file, json_encode($response));
}

function _get_cached_mirror_list($cache_file, $weights)
{
    $mirrors = array();
    $cache_contents = json_decode(file_get_contents($cache_file), TRUE);
    if( $cache_contents === FALSE ) {
        error_log("[WARNING] the cache file cannot be parsed. Removing $cache_file");
        unlink($cache_file);
    }

    // pick $qty mirrors for each $cc (country code)
    foreach($weights as $cc => $qty) {
        for($i = 0; $i < $qty; $i++) {
            if(isset($cache_contents[$cc][$i])) {
                $mirrors[] = $cache_contents[$cc][$i];
            } else {
                error_log("[WARNING] the cache contents are inconsistent. Removing $cache_file");
                unlink($cache_file);
            }
        }
    }
    
    // append the master centos mirror to ensure a non empty list always exists:
    if(empty($mirrors)) {
        error_log("[WARNING] mirror cache is empty. Returning the master centos mirror.");
        $mirrors = array('http://mirror.centos.org/centos');
    }

    return array_slice($mirrors, 0, array_sum($weights));
}

function get_centos_mirrors($weights, $release, $arch)
{
    $cache_file = "/var/cache/mirrorlist/centos-mirrors.${release}.${arch}.ini";
    $max_timestamp = time() - 3600 * 4;
    if(! file_exists($cache_file) || filemtime($cache_file) < $max_timestamp) {
        _build_cache($release, $arch, array_keys($weights), $cache_file);
    }
    return _get_cached_mirror_list($cache_file, $weights);
}
