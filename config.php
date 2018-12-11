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

// Map an upstream major version number to the latest stable NethServer release
$stable_releases = array(
    '6' => '6.10',
    '7' => '7.5.1804',
);

// Map an upstream major version number to the latest stable CentOS release.
// Update as soon as CentOS releases a new stable version.
$centos_releases = array(
    '6' => '6.10',
    '7' => '7.6.1810',
);

// Upstream versions served by vault.centos.org. It implies the same NethServer
// releases aren't served by NethServer mirrors any more.
$vault_releases = array(
);

// Other valid release numbers for upcoming NethServer releases. If not listed
// here, an unknown nsrelease number is mapped to the stable release (i.e. the
// system is upgraded to latest stable). Always served by packages.nethserver.org.
$development_releases = array(
    '7.6.1810',
);

// Repositories that are always served by packages.nethserver.org
$development_repos = array(
    'testing',
    'nethforge-testing',
);


// Alternative architectures:
// - stable goes to mirror,
// - development goes to packages.nethserver.org
$stable_arches = array(
    'x86_64',
);

$development_arches = array(
    'armhfp',
    'aarch64',
);
