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
    $ch = fopen($cache_file, 'c');
    $wouldblock = NULL;
    flock($ch, LOCK_EX | LOCK_NB, $wouldblock);
    if($wouldblock) {
        flock($ch, LOCK_UN);
        fclose($ch);
        error_log("[NOTICE] Skip the cache building because the file is locked by another process: $cache_file");
        return;
    }

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

    ftruncate($ch, 0);
    fwrite($ch, json_encode($response));
    flock($ch, LOCK_UN);
    fclose($ch);
}

function _get_cached_mirror_list($cache_file, $weights)
{
    $mirrors = array();
    $ch = fopen($cache_file, 'r');
    flock($ch, LOCK_SH);
    $cache_contents = json_decode(stream_get_contents($ch), TRUE);
    flock($ch, LOCK_UN);
    fclose($ch);

    if( $cache_contents === FALSE ) {
        error_log("[WARNING] the cache file cannot be parsed: $cache_file");
    } else {
        // pick $qty mirrors for each $cc (country code)
        foreach($weights as $cc => $qty) {
            for($i = 0; $i < $qty; $i++) {
                if(isset($cache_contents[$cc][$i])) {
                    $mirrors[] = $cache_contents[$cc][$i];
                }
            }
        }
    }

    return array_slice($mirrors, 0, array_sum($weights));
}

function get_centos_mirrors($weights, $release, $arch)
{
    $cache_file = "/var/cache/mirrorlist/centos-mirrors.${release}.${arch}.ini";
    $max_timestamp = time() - 3600 * 4;

    if(file_exists($cache_file) && filemtime($cache_file) >= $max_timestamp) {
        // Valid cache file
        $mirrors = _get_cached_mirror_list($cache_file, $weights);
    } else {
        // Invalid cache file
        $mirrors = array();
    }

    // Invalid cache file or invalid cache contents: trigger cache rebuild
    if(empty($mirrors)) {
        _build_cache($release, $arch, array_keys($weights), $cache_file);
        $mirrors = _get_cached_mirror_list($cache_file, $weights);
    }

    // fallback to default mirrorlist or return valid entries
    return empty($mirrors) ? array('http://mirror.centos.org/centos') : $mirrors;
}
