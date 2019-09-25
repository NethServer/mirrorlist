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

// Version numbers served by mirrors
$stable_releases = array(
    '6.10',
    '7.6.1810',
);

// Other valid version numbers served by mirror.nethserver.org
$development_releases = array(
    '7.7.1908',
);

$ce_mirror_countries = array(
    'au' => 1,
    'br' => 1,
    'cn' => 1,
    'gb' => 2,
    'it' => 2,
    'us' => 2,
    'za' => 1,
);

// Upstream versions served by vault.centos.org. It implies the same NethServer
// releases are not served by NethServer mirrors any more.
$vault_releases = array(
    '7.5.1804',
    '7.6.1810',
);

// Repositories that are always served by packages.nethserver.org
$development_repos = array(
    'testing',
    'nethforge-testing',
);


// Architectures:
//
// - "stable" goes to the official NS mirror infrastructure
//
// - "development" goes to mirror.nethserver.org
//
$stable_arches = array(
    'x86_64',
);

$development_arches = array(
    'armhfp',
    'aarch64',
);
