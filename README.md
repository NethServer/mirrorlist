# mirrorlist

This is the list of official NethServer mirrors. 

If you think some mirror should be added/removed feel free to [edit the mirrors
file](https://github.com/NethServer/mirrorlist/edit/master/mirrors) and open a
pull request.

The `mirrors` file format follows these rules:

1. each line represents a mirror record
2. the line begins with the two letters country code where the mirror is 
   located, followed by a single space separator (e.g. `us `)
3. the line continues with the base mirror URL (e.g. `http://mirror.nethserver.org/nethserver/`)
4. the mirror URL must end with a slash "/"

## Mirror status

A `mirmon` report is available at http://mirror-status.nethserver.org/

## Donate a NethServer mirror

If you want to host a NethServer mirror 

* Read the community [HowTo](https://community.nethserver.org/t/how-to-create-your-own-nethserver-mirror/344)
* Edit [mirrors](https://github.com/NethServer/mirrorlist/edit/master/mirrors) and open a pull request

## Installation

* Clone this repository in the "mirrorlist" web site root directory
* Copy `mirrorlist.cron` in /etc/cron.d/ and adjust the script path and run frequency

## Configuration

All the configuration is set by ``config.php``. Other scripts should not be
modified, unless a business rule change is wanted.

The mirrors list is generated by ``nethserver.php`` for both ns6 and ns7. The
``centos.php`` script is specific to ns6: it generates the mirrors list for
CentOS repositories.

Use cases and configuration editing rules:

- Edit ``$stable_releases`` only when NethServer releases a new version.
  The old stable number must go to ``$vault_releases`` otherwise it becomes 404

- When a CentOS stable release goes to vault, list that release number under
  ``$vault_releases``. It is allowed to list the same release number under both
  ``$stable_releases`` and ``$vault_releases``: the latter takes precedence and
  the release is served by `mirror.nethserver.org` and `vault.centos.org`

- Before editing the `$stable_release` variable wait until the old stable CentOS
  is served by `vault` and the new one is available from CentOS mirrors



