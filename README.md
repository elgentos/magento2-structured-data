# Magento 2 Structured Data



## Installation

This package can be installed using [Composer](https://getcomposer.com).

```bash
composer require elgentos/magento2-structured-data
bin/magento module:enable Elgentos_StructuredData
bin/magento setup:upgrade
```

## Usage
To use the extension, you need to enable it in the configuration of Magento. This will display the structured data
in your pages just before the end of the `<body>` tag.

Also make sure you remove all `itemscope`, `itemtype` and `itemprop` attributes. These are normally added to the product
page and will also add structured data to the product pages. This needs to be done in your theme.

## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License
MIT