# PcComponentes Coding Standard

PcComponentes Coding Standard for [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) provides sniffs that fall into three categories.

## Usage
Use composer to require the library:

```bash
composer require --dev pccomponentes/coding-standard
```

Create the `phpcs.xml.dist` configuration file and use the standard:

```xml
<?xml version="1.0" ?>
<ruleset name="Project rules">
    <rule ref="vendor/pccomponentes/coding-standard/src/ruleset.xml" />
</ruleset>
```
