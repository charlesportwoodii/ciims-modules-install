<?php
/**
 * @author Charles R. Portwood II <charlesportwoodii@ethreal.net>
 * @package CiiMS https://www.github.com/charlesportwoodii/CiiMS
 * @license MIT License
 * @copyright 2011-2014 Charles R. Portwood II
 *
 * @notice  This file is part of CiiMS, and likely will not function without the necessary CiiMS classes
 */
?>
<!DOCTYPE html>
<html lang="<?php echo Yii::app()->getLanguage(); ?>">
    <head>
        <?php $asset = Yii::app()->assetManager->publish(YiiBase::getPathOfAlias('application.modules.install.assets'), true, -1, YII_DEBUG); ?>
        <?php Yii::app()->clientScript->registerCssFile($asset.'/dist/install.min.css'); ?>
        <?php Yii::app()->clientScript->registerScriptfile($asset.'/dist/install.min.js'); ?>
        <link href="//fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet" type="text/css">
        <link href="//fonts.googleapis.com/css?family=Oswald:400,700" rel="stylesheet" type="text/css">
        <title><?php echo Yii::t('Install.main', 'CiiMS Installer'); ?></title>
    </head>
    <body>
        <main>
            <?php echo $content; ?>
        </main>
    </body>
</html>
