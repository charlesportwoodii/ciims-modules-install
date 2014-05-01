# CiiMS Installer Module

[![Latest Stable Version](https://poser.pugx.org/ciims-modules/install/v/stable.png)](https://packagist.org/packages/ciims-modules/install) [![Total Downloads](https://poser.pugx.org/ciims-modules/install/downloads.png)](https://packagist.org/packages/ciims-modules/install) [![Latest Unstable Version](https://poser.pugx.org/ciims-modules/install/v/unstable.png)](https://packagist.org/packages/ciims-modules/install) [![License](https://poser.pugx.org/ciims-modules/install/license.png)](https://packagist.org/packages/ciims-modules/install)

This is a generic Yii installation module that is used to bootstrap your application for installation. It was created to work with [CiiMS](https://github.com/charlesportwoodii/CiiMS), and is highly dependant upon the ```Cii``` core component that is part of CiiMS.

This package was built to pre-bootstrap CiiMS until it can be passed off to Yii Framework for CiiMS core installation and migration of the database. For the most part, the installer will take care of all the major issues in setting your site up for you.

## License Information & Copying

This module is licensed with [CiiMS](https://github.com/charlesportwoodii/CiiMS) under the MIT License. Please don't steal my work and claim it as your own.

## Hacking

This module uses npm, bower, and grunt for asset management, and is mostly managed through grunt. To make changes to the assets, run the following commands.

```
npm install
grunt
```

## Disclaimers

While this module is generic enough to be used in other Yii applications with some tweaking, it is dependent upon the ```Cii``` core component, and will produce errors until that is remedied. 
