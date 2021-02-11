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
require_once('config.php');
require_once('mirrorcache.php');

$release = $_GET['release'];

// Read nsrelease from query string, or fall back to URL path for ns6 (i.e. 6.9/nethserver?...)
$nsrelease = $_GET['nsrelease'] ?: str_replace('/', '', $_SERVER['PATH_INFO']);
$arch = $_GET['arch'];
$repo = $_GET['repo'];
$repo_suffix = "";

$ns_repos = array(
    'base',
    'updates',
    'testing',
    'nethforge',
    'nethforge-testing',
    'ns6upgrade',
);

// Map our repository name to CentOS name
// Warning! The "-" character is treated specially (because of SCLo),
// to set $repo_suffix.
$ce_repos = array(
    'ce-base' => 'os',
    'ce-updates' => 'updates',
    'ce-extras' => 'extras',
    'ce-sclo-sclo' => 'sclo-sclo',
    'ce-sclo-rh' => 'sclo-rh',
);

// Remap machine architecture to repo architecture:
if($arch == 'armv7hl') {
    $arch = 'armhfp';
}

// Assign to $nsrelease a default full stable release number. This is required
// by legacy/unlocked clients and plain CentOS installations that do not send
// $nsrelease properly:
if( ! preg_match("/^${release}\./", $nsrelease)) {
    $nsrelease = array_shift(preg_grep("/^${release}\./", $stable_releases));
}

$known_releases = array_unique(array_merge($stable_releases, $vault_releases, $development_releases));
$major_releases = array_unique(preg_replace('/^(\d).*/', '$1', $known_releases));
$valid_release = in_array($release, $major_releases);
$valid_nsrelease = in_array($nsrelease, $known_releases) && ($nsrelease[0] == $release[0]);
$valid_arch = in_array($arch, array_merge($stable_arches, $development_arches));
$valid_repo = in_array($repo, array_merge($ns_repos,array_keys($ce_repos)));

header('Content-type: text/plain; charset=UTF-8');

if( ! $valid_release || ! $valid_arch || ! $valid_repo || ! $valid_nsrelease ) {
    header("HTTP/1.0 404 Not Found");
    echo "Invalid release/repo/arch\n";
    exit(1);
}


$served_by_nethserver_mirrors = in_array($repo, $ns_repos)
  && ! (in_array($nsrelease, array_merge($vault_releases, $development_releases))
        || in_array($repo, $development_repos)
        || in_array($arch, $development_arches))
;

if($served_by_nethserver_mirrors) {
    // trim spaces, leading CC-prefix and trailing slash:
    $mirrors = array_filter(preg_replace('/(^\w\w +|\/$)/', '', array_map('trim', file("mirrors"))));
} elseif (in_array($repo, array_keys($ce_repos))) {
    // map to real repository name, extracting the $repo_suffix (required by SCLo):
    list($repo, $repo_suffix) = array_merge(explode('-', $ce_repos[$repo], 2), array(''));

    if($repo == 'sclo' && $arch == 'armhfp') {
        $repo = 'empty';
        $repo_suffix = '';
        $mirrors = array('http://mirror.nethserver.org/nethserver');
    } elseif(in_array($nsrelease, $vault_releases)) {
        // CentOS versions served by vault.centos.org
        if($arch == 'x86_64') {
            $mirrors = array('http://vault.centos.org/centos');
        } else {
            $mirrors = array('http://vault.centos.org/altarch');
        }
    } else {
        // CentOS versions served by upstream mirror infrastructure
        $mirrors = get_centos_mirrors($release, $arch);
        if(empty($mirrors)) {
            if($arch == 'x86_64') {
                $mirrors = array('http://mirror.centos.org/centos');
            } else {
                $mirrors = array('http://mirror.centos.org/altarch');
            }
        }
    }
} else {
    // Serverd only by the NethServer master mirror
    $mirrors = array('http://mirror.nethserver.org/nethserver');
}

foreach($mirrors as $mirror) {
    if($repo_suffix) {
        echo "$mirror/$nsrelease/$repo/$arch/$repo_suffix/\n";
    } else {
        echo "$mirror/$nsrelease/$repo/$arch/\n";
    }
}
