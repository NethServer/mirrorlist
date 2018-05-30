<?php

$release = $_GET['release'];
$nsrelease = $_GET['nsrelease'];
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

$latest_releases = array(
    '6' => '6.9',
    '7' => '7.5.1804',
);

$valid_release = in_array($release, array_keys($latest_releases));
$valid_nsrelease = in_array($nsrelease, array('6.8','6.9','7.3.1611','7.4.1708','7.5.1804'));
$valid_arch = in_array($arch, array('x86_64'));
$valid_repo = in_array($repo, array_merge($ns_repos,array_keys($ce_repos)));

# nsrelease overrides release
if ( $valid_nsrelease ) {
    $release = $nsrelease;
} else {
    $release = $latest_releases[$release];
}

if( ! $valid_release || ! $valid_arch || ! $valid_repo ) {
    header("HTTP/1.0 404 Not Found");
    exit(1);
}

header('Content-type: text/plain; charset=UTF-8');

if($repo === 'testing' || $repo === 'nethforge-testing') {
    $mirrors = array('http://packages.nethserver.org/nethserver');
} else if (in_array($repo, array_keys($ce_repos))) {
    // CentOS versions served by mirror infrastructure,
    // otherwise fallback to vault.centos.org:
    if(in_array($release, array('6.9', '7.5.1804'))) {
        $mirrors = file("ce-mirrors");
    } else {
        $mirrors = array('http://vault.centos.org');
    }
    $repo = $ce_repos[$repo]; # map to real repository name
} else {
    $mirrors = file("mirrors");
}

foreach($mirrors as $mirror) {
    echo trim($mirror)."/$release/$repo/$arch/\n";
}

