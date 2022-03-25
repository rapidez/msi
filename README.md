# Rapidez MSI
This Rapidez Module will offer compatibility with the Magento MSI functionality.
This module uses a HTTP Middleware to have access to the ```stock_id ``` for that website. 

It also features 2 Global Scopes used by [Eventy Filters](https://docs.rapidez.io/0.x/package-development.html#eventy-filters) to enrich product Detail and Overview Page and The [Rapidez:Indexer](https://docs.rapidez.io/0.x/indexer.html#indexer) with Stock Status Information.

##Requirements
This modules requires the Magento MSI functionality to be active and fully functional on the Magento side.
Stock(s) and Source(s) should be created and assigned to the Website(s).

The [Message Queues](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/message-queues/message-queues.html) of Magento should be functional as MSI stock tables will be update through this mechanism. For example, the salable status of a product will be updated by process ` inventory.reservations.updateSalabilityStatus`.

##Installation
```composer require rapidez/msi```

To expose the `stock_qty` to the product detail and overview page you can follow the steps:
1. Publish the MSI configuration via `php artisan vendor:publish`
2. Set the values from `false` to `true` where you want to expose the stock information

Don't forget to reindex rapidez with `artisan rapidez:index` for `stock_qty` to be available in the Elasticsearch Indexes.

## Note
The MSI functionality in this module is currently supported for the following product types:
- Simple

##Changelog

##License
GNU General Public License v3. Please see [License File](LICENSE) File for more information.
