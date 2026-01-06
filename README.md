# Rapidez MSI

This Rapidez package will offer compatibility with the Magento MSI functionality.

## Requirements

This package requires the Magento MSI functionality to be active and configured in Magento. Also the [Message Queues](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/message-queues/message-queues.html) of Magento should be functional as MSI stock tables will be update through this mechanism. For example, the salable status of a product will be updated by process `inventory.reservations.updateSalabilityStatus`

## Installation

```
composer require rapidez/msi
```

And run a reindex with `php artisan rapidez:index`

## Note

Currently this does not work with grouped products!

## License

GNU General Public License v3. Please see [License File](LICENSE) File for more information.
