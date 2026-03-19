# MyAdmin OpenVZ VPS Plugin

[![Tests](https://github.com/detain/myadmin-openvz-vps/actions/workflows/tests.yml/badge.svg)](https://github.com/detain/myadmin-openvz-vps/actions/workflows/tests.yml)
[![Latest Stable Version](https://poser.pugx.org/detain/myadmin-openvz-vps/version)](https://packagist.org/packages/detain/myadmin-openvz-vps)
[![Total Downloads](https://poser.pugx.org/detain/myadmin-openvz-vps/downloads)](https://packagist.org/packages/detain/myadmin-openvz-vps)
[![License](https://poser.pugx.org/detain/myadmin-openvz-vps/license)](https://packagist.org/packages/detain/myadmin-openvz-vps)

A [MyAdmin](https://github.com/detain/myadmin) plugin for provisioning and managing OpenVZ container-based virtual private servers. It integrates with the MyAdmin control panel through Symfony EventDispatcher hooks, providing VPS lifecycle management (create, delete, start, stop, restart, backup, restore), network configuration (IP assignment, hostname changes), resource allocation (slice-based CPU/memory/disk), and administrative settings for stock control and default server selection.

## Requirements

- PHP 8.2 or later
- ext-soap
- Symfony EventDispatcher 5.x, 6.x, or 7.x

## Installation

```sh
composer require detain/myadmin-openvz-vps
```

## Testing

```sh
composer install
vendor/bin/phpunit
```

## License

Licensed under the LGPL-2.1. See [LICENSE](https://www.gnu.org/licenses/old-licenses/lgpl-2.1.html) for details.
