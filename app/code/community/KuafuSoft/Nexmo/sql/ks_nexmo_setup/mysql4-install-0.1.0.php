<?php
$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn(
    $installer->getTable('admin/user'),
    'phone',
    'varchar(255)'
);

$installer->endSetup();
