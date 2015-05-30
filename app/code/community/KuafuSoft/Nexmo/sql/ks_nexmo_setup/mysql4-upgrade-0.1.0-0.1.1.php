<?php
$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn(
    $installer->getTable('admin/user'),
    'nexmo_id',
    'varchar(64)'
);

$installer->getConnection()->addColumn(
    $installer->getTable('sales/quote'),
    'nexmo_id',
    'varchar(64)'
);

$installer->endSetup();
