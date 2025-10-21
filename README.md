# Santi_Tntcarriers

Módulo de transportista TNT para Magento 2.4.8 instalable vía Composer desde GitHub.

## Instalación (VCS)
En el `composer.json` del proyecto Magento:

```json
{
  "repositories": [
    {"type": "vcs", "url": "https://github.com/santimolto/module-tntcarriers"}
  ],
  "require": {
    "santi/module-tntcarriers": "^1.0"
  }
}
```

Luego:

```bash
composer require santi/module-tntcarriers:^1.0
bin/magento setup:upgrade
```
