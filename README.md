# CiiMS Installer Module

[![Latest Version](https://img.shields.io/packagist/v/ciims-modules/install.svg?style=flat)]()
[![Downloads](https://img.shields.io/packagist/dt/ciims-modules/install.svg?style=flat)]()
[![Gittip](https://img.shields.io/gittip/charlesportwoodii.svg?style=flat "Gittip")](https://www.gittip.com/charlesportwoodii/)
[![License](https://img.shields.io/badge/license-MIT-orange.svg?style=flat "License")](https://github.com/charlesportwoodii/ciims-modules-install/blob/master/LICENSE.md)

This is a generic Yii installation module that is used to bootstrap your application for installation. It was created to work with [CiiMS](https://github.com/charlesportwoodii/CiiMS), and is highly dependant upon the ```Cii``` core component that is part of CiiMS.

This package was built to pre-bootstrap CiiMS until it can be passed off to Yii Framework for CiiMS core installation and migration of the database. For the most part, the installer will take care of all the major issues in setting your site up for you.

## Hacking

This module uses npm, bower, and grunt for asset management, and is mostly managed through grunt. To make changes to the assets, run the following commands.

```
npm install
bower install
grunt
```
