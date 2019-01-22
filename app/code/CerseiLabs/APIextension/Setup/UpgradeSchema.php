<?php
/**
 * Copyright © 2015 CerseiLabs. All rights reserved.
 */

namespace CerseiLabs\APIextension\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Class UpgradeSchema
 *
 * @package CerseiLabs\APIextension\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
//        $setup->startSetup();
//        $tableName = $setup->getTable('apiextension_webhook');
//
//        if ($setup->getConnection()->isTableExists($tableName) == true) {
//            $connection = $setup->getConnection();
//
//            $connection->changeColumn(
//                $tableName,
//                'callback url',
//                'callback_url',
//                ['type' => Table::TYPE_TEXT, 'nullable' => false, 'default' => ''],
//                'Callback URL'
//            );
//            // Changes here.
//        }
//
//        $setup->endSetup();
    }
}
