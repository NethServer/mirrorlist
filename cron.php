<?php

/*
 * Copyright (C) 2019 Nethesis S.r.l.
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

if(!defined('STDIN')) {
    error_log("[ERROR] Script must run from Bash or Cron");
    exit(1);
}

require_once("config.php");
require_once("mirrorcache.php");

$errors = 0;

$major_releases = array_unique(preg_replace('/^(\d).*/', '$1', $stable_releases));
foreach($major_releases as $release) {
    foreach($stable_arches as $arch) {
        $status = refresh_centos_mirrors_cache($ce_mirror_countries, $release, $arch);
        if( ! $status) {
            error_log("[ERROR] Failed to write $release $arch cache file");
            $errors++;
        }
    }
}

exit($errors ? 1 : 0);
