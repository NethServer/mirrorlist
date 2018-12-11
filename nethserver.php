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

// Global definition of latest, valult, and development releases:
include 'config.php';

$release = $_GET['release'];

// Read nsrelease from query string, or fall back to URL path for ns6 (i.e. 6.9/nethserver?...)
$nsrelease = $_GET['nsrelease'] ?: str_replace('/', '', $_SERVER['PATH_INFO']);
$arch = $_GET['arch'];
$repo = $_GET['repo'];

$ns_repos = array(
    'base',
    'updates',
    'testing',
    'nethforge',
    'nethforge-testing'
);

$ce_repos = array(
    'ce-base' => 'os',
    'ce-updates' => 'updates',
    'ce-extras' => 'extras',
);

$valid_release = in_array($release, array_keys($stable_releases));
$valid_nsrelease = in_array($nsrelease, array_merge($stable_releases, $development_releases, $vault_releases)) && ($nsrelease[0] == $release[0]);
$valid_arch = in_array($arch, array_merge($stable_arches, $development_arches));
$valid_repo = in_array($repo, array_merge($ns_repos,array_keys($ce_repos)));

if( ! $valid_release || ! $valid_arch || ! $valid_repo ) {
    header("HTTP/1.0 404 Not Found");
    exit(1);
}

if ( ! $valid_nsrelease ) {
    // Return the latest stable release:
    $nsrelease = $stable_releases[$release];
}

header('Content-type: text/plain; charset=UTF-8');

$served_by_nethserver_mirrors = in_array($repo, $ns_repos)
  && ! (in_array($nsrelease, $vault_releases)
        || in_array($repo, $development_repos)
        || in_array($nsrelease, $development_releases)
        || in_array($arch, $development_arches))
;

if($served_by_nethserver_mirrors) {
    $mirrors = file("mirrors");
} elseif (in_array($repo, array_keys($ce_repos))) {
    // map to real repository name:
    $repo = $ce_repos[$repo];
    if(in_array($nsrelease, $vault_releases)) {
        $mirrors = array('http://vault.centos.org');
    } else {
        // CentOS versions served by upstream mirror infrastructure
        $mirrors = file("ce-mirrors");
    }
} else {
    // Serverd only by the NethServer master mirror
    $mirrors = array('http://packages.nethserver.org/nethserver');
}

foreach($mirrors as $mirror) {
    echo trim($mirror)."/$nsrelease/$repo/$arch/\n";
}
