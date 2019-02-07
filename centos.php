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

// Redirect to upstream mirrorlist: required by NethServer 6
// In ns7 the the mirror list is returned by nethserver.php

// Global definition of latest, valult, and development releases:
require_once('config.php');
require_once('mirrorcache.php');


$release = $_GET['release'];
// Read nsrelease from URL path for ns6 (i.e. 6.9/centos?...)
$nsrelease = str_replace('/', '', $_SERVER['PATH_INFO']);
$arch = $_GET['arch'];
$repo = $_GET['repo'];

$valid_release = $release == '6';
$valid_nsrelease = in_array($nsrelease, array_merge($stable_releases, $development_releases, $vault_releases)) && ($nsrelease[0] == $release[0]);
$valid_arch = in_array($arch, array('x86_64'));
$valid_repo = in_array($repo, array('os', 'updates'));

if( ! $valid_release || ! $valid_arch || ! $valid_repo ) {
    header("HTTP/1.0 404 Not Found");
    exit(1);
}

if ( ! $valid_nsrelease ) {
    // Return the latest stable release:
    $nsrelease = $stable_releases[$release];
}

header('Content-type: text/plain; charset=UTF-8');

if(in_array($nsrelease, $vault_releases)) {
    $mirrors = array('http://vault.centos.org/');
} else {
    $mirrors = get_centos_mirrors($ce_mirror_countries, $release, $arch);
}

foreach($mirrors as $mirror) {
    echo trim($mirror)."/$nsrelease/$repo/$arch/\n";
}
