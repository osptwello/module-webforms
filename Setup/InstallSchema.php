<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        /**
         * Create table 'webforms'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('webforms')
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Form ID'
        )->addColumn(
            'name',
            Table::TYPE_TEXT,
            255,
            [],
            'Form Name'
        )->addColumn(
            'code',
            Table::TYPE_TEXT,
            255,
            [],
            'Form Code'
        )->addColumn(
            'redirect_url',
            Table::TYPE_TEXT,
            null,
            [],
            'Redirect URL'
        )->addColumn(
            'description',
            Table::TYPE_TEXT,
            null,
            [],
            'Description'
        )->addColumn(
            'success_text',
            Table::TYPE_TEXT,
            null,
            [],
            'Success Text'
        )->addColumn(
            'send_email',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Send admin notification'
        )->addColumn(
            'add_header',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Add header to the e-mail'
        )->addColumn(
            'duplicate_email',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Send customer notification'
        )->addColumn(
            'email',
            Table::TYPE_TEXT,
            255,
            [],
            'Admin notification e-mail address'
        )->addColumn(
            'email_reply_to',
            Table::TYPE_TEXT,
            null,
            [],
            'Reply-to e-mail address for customer'
        )->addColumn(
            'email_template_id',
            Table::TYPE_INTEGER,
            11,
            [],
            'Admin notification template'
        )->addColumn(
            'email_customer_template_id',
            Table::TYPE_INTEGER,
            11,
            [],
            'Customer notification template'
        )->addColumn(
            'email_reply_template_id',
            Table::TYPE_INTEGER,
            11,
            [],
            'Reply template'
        )->addColumn(
            'email_result_approval',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Enable result approval'
        )->addColumn(
            'email_result_approved_template_id',
            Table::TYPE_INTEGER,
            11,
            [],
            'Approved result notification template'
        )->addColumn(
            'email_result_completed_template_id',
            Table::TYPE_INTEGER,
            11,
            [],
            'Completed result notification template'
        )->addColumn(
            'email_result_notapproved_template_id',
            Table::TYPE_INTEGER,
            11,
            [],
            'Not approved result notification template'
        )->addColumn(
            'email_attachments_admin',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Attach files to admin notifications'
        )->addColumn(
            'email_attachments_customer',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Attach files to customer notifications'
        )->addColumn(
            'survey',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Survey mode'
        )->addColumn(
            'approve',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Enable results approval'
        )->addColumn(
            'captcha_mode',
            Table::TYPE_TEXT,
            40,
            [],
            'Captcha mode'
        )->addColumn(
            'files_upload_limit',
            Table::TYPE_INTEGER,
            11,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Files upload limit'
        )->addColumn(
            'images_upload_limit',
            Table::TYPE_INTEGER,
            11,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Images upload limit'
        )->addColumn(
            'created_time',
            Table::TYPE_DATETIME,
            null,
            [],
            'Created time'
        )->addColumn(
            'update_time',
            Table::TYPE_TIMESTAMP,
            null,
            [],
            'Last update time'
        )->addColumn(
            'is_active',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Active'
        )->addColumn(
            'menu',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Add form link to menu'
        )->addColumn(
            'submit_button_text',
            Table::TYPE_TEXT,
            255,
            [],
            'Submit button text'
        )->addColumn(
            'print_template_id',
            Table::TYPE_INTEGER,
            11,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Print template'
        )->addColumn(
            'print_attach_to_email',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Attach printed result to notification'
        )->addColumn(
            'access_enable',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Enable access controls'
        )->addColumn(
            'access_groups_serialized',
            Table::TYPE_TEXT,
            null,
            [],
            'Access groups'
        )->addColumn(
            'dashboard_enable',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Enable customer dashboard controls'
        )->addColumn(
            'dashboard_groups_serialized',
            Table::TYPE_TEXT,
            null,
            [],
            'Dashboard groups'
        )->addColumn(
            'customer_print_template_id',
            Table::TYPE_INTEGER,
            11,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Customer print template'
        )->addColumn(
            'customer_print_attach_to_email',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Attach printed result to customer notification'
        )->addColumn(
            'approved_print_template_id',
            Table::TYPE_INTEGER,
            11,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Approved result print template'
        )->addColumn(
            'approved_print_attach_to_email',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Attach approved printed result to customer notification'
        )->addColumn(
            'completed_print_template_id',
            Table::TYPE_INTEGER,
            11,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Completed result print template'
        )->addColumn(
            'completed_print_attach_to_email',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Attach completed printed result to customer notification'
        )->setComment(
            'WebForms Forms'
        );
        $installer->getConnection()->createTable($table);


        /**
         * Create table 'webforms_fieldsets'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('webforms_fieldsets')
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Fieldset ID'
        )->addColumn(
            'webform_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Form ID'
        )->addColumn(
            'name',
            Table::TYPE_TEXT,
            255,
            [],
            'Fieldset Name'
        )->addColumn(
            'css_class',
            Table::TYPE_TEXT,
            null,
            [],
            'CSS class'
        )->addColumn(
            'result_display',
            Table::TYPE_TEXT,
            10,
            [],
            'Display in notification'
        )->addColumn(
            'position',
            Table::TYPE_INTEGER,
            11,
            [],
            'Position'
        )->addColumn(
            'created_time',
            Table::TYPE_DATETIME,
            null,
            [],
            'Created time'
        )->addColumn(
            'update_time',
            Table::TYPE_TIMESTAMP,
            null,
            [],
            'Last update time'
        )->addColumn(
            'is_active',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Active'
        )->addForeignKey(
            $installer->getFkName('webforms_fields', 'webform_id', 'webforms', 'id'),
            'webform_id',
            $installer->getTable('webforms'),
            'id',
            Table::ACTION_CASCADE
        )->setComment(
            'WebForms Fieldsets'
        );
        $installer->getConnection()->createTable($table);


        /**
         * Create table 'webforms_fields'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('webforms_fields')
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Field ID'
        )->addColumn(
            'webform_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Form ID'
        )->addColumn(
            'fieldset_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Fieldset ID'
        )->addColumn(
            'name',
            Table::TYPE_TEXT,
            255,
            [],
            'Field Name'
        )->addColumn(
            'code',
            Table::TYPE_TEXT,
            255,
            [],
            'Field Code'
        )->addColumn(
            'comment',
            Table::TYPE_TEXT,
            null,
            [],
            'Comment'
        )->addColumn(
            'result_label',
            Table::TYPE_TEXT,
            null,
            [],
            'Result label'
        )->addColumn(
            'result_display',
            Table::TYPE_TEXT,
            255,
            [],
            'Result display'
        )->addColumn(
            'type',
            Table::TYPE_TEXT,
            100,
            [],
            'Field type'
        )->addColumn(
            'size',
            Table::TYPE_TEXT,
            20,
            [],
            'Field size'
        )->addColumn(
            'value',
            Table::TYPE_TEXT,
            null,
            [],
            'Field value'
        )->addColumn(
            'email_subject',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Use field as email subject'
        )->addColumn(
            'css_class',
            Table::TYPE_TEXT,
            null,
            [],
            'CSS class for input'
        )->addColumn(
            'css_class_container',
            Table::TYPE_TEXT,
            null,
            [],
            'CSS class for container'
        )->addColumn(
            'css_style',
            Table::TYPE_TEXT,
            null,
            [],
            'CSS style for input'
        )->addColumn(
            'validate_message',
            Table::TYPE_TEXT,
            null,
            [],
            'Validation error message'
        )->addColumn(
            'validate_regex',
            Table::TYPE_TEXT,
            null,
            [],
            'Validation RegExp'
        )->addColumn(
            'validate_length_max',
            Table::TYPE_INTEGER,
            11,
            [],
            'Maximum length'
        )->addColumn(
            'validate_length_min',
            Table::TYPE_INTEGER,
            11,
            [],
            'Minimum length'
        )->addColumn(
            'position',
            Table::TYPE_INTEGER,
            11,
            [],
            'Position'
        )->addColumn(
            'required',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Required'
        )->addColumn(
            'validation_advice',
            Table::TYPE_TEXT,
            null,
            [],
            'Validation advice'
        )->addColumn(
            'created_time',
            Table::TYPE_DATETIME,
            null,
            [],
            'Created time'
        )->addColumn(
            'update_time',
            Table::TYPE_TIMESTAMP,
            null,
            [],
            'Last update time'
        )->addColumn(
            'is_active',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Active'
        )->addColumn(
            'hint',
            Table::TYPE_TEXT,
            null,
            [],
            'Hint'
        )->addForeignKey(
            $installer->getFkName('webforms_fieldsets', 'webform_id', 'webforms', 'id'),
            'webform_id',
            $installer->getTable('webforms'),
            'id',
            Table::ACTION_CASCADE
        )->setComment(
            'WebForms Fields'
        );
        $installer->getConnection()->createTable($table);


        /**
         * Create table 'webforms_logic'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('webforms_logic')
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Logic ID'
        )->addColumn(
            'field_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Field ID'
        )->addColumn(
            'logic_condition',
            Table::TYPE_TEXT,
            20,
            ['default' => 'equal'],
            'Condition'
        )->addColumn(
            'action',
            Table::TYPE_TEXT,
            20,
            ['default' => 'show'],
            'Action'
        )->addColumn(
            'aggregation',
            Table::TYPE_TEXT,
            20,
            ['default' => 'any'],
            'Aggregation'
        )->addColumn(
            'value_serialized',
            Table::TYPE_TEXT,
            null,
            [],
            'Value'
        )->addColumn(
            'target_serialized',
            Table::TYPE_TEXT,
            null,
            [],
            'Target'
        )->addColumn(
            'created_time',
            Table::TYPE_DATETIME,
            null,
            [],
            'Created time'
        )->addColumn(
            'update_time',
            Table::TYPE_TIMESTAMP,
            null,
            [],
            'Last update time'
        )->addColumn(
            'is_active',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Active'
        )->addForeignKey(
            $installer->getFkName('webforms_logic', 'field_id', 'webforms_fields', 'id'),
            'field_id',
            $installer->getTable('webforms_fields'),
            'id',
            Table::ACTION_CASCADE
        )->setComment(
            'WebForms Logic'
        );
        $installer->getConnection()->createTable($table);


        /**
         * Create table 'webforms_results'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('webforms_results')
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Result ID'
        )->addColumn(
            'webform_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Form ID'
        )->addColumn(
            'store_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Store ID'
        )->addColumn(
            'customer_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Customer ID'
        )->addColumn(
            'customer_ip',
            Table::TYPE_BIGINT,
            20,
            ['unsigned' => true, 'nullable' => false],
            'Customer IP'
        )->addColumn(
            'approved',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Approved status'
        )->addColumn(
            'created_time',
            Table::TYPE_DATETIME,
            null,
            [],
            'Created time'
        )->addColumn(
            'update_time',
            Table::TYPE_TIMESTAMP,
            null,
            [],
            'Last update time'
        )->addForeignKey(
            $installer->getFkName('webforms_results', 'webform_id', 'webforms', 'id'),
            'webform_id',
            $installer->getTable('webforms'),
            'id',
            Table::ACTION_CASCADE
        )->setComment(
            'WebForms Results'
        );
        $installer->getConnection()->createTable($table);


        /**
         * Create table 'webforms_results_values'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('webforms_results_values')
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Value ID'
        )->addColumn(
            'result_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Result ID'
        )->addColumn(
            'field_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Field ID'
        )->addColumn(
            'value',
            Table::TYPE_TEXT,
            null,
            [],
            'Value'
        )->addColumn(
            'key',
            Table::TYPE_TEXT,
            10,
            [],
            'Key'
        )->addForeignKey(
            $installer->getFkName('webforms_results_values', 'result_id', 'webforms_results', 'id'),
            'result_id',
            $installer->getTable('webforms_results'),
            'id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('webforms_results_values', 'field_id', 'webforms_fields', 'id'),
            'field_id',
            $installer->getTable('webforms_fields'),
            'id',
            Table::ACTION_CASCADE
        )->setComment(
            'WebForms Results Values'
        );
        $installer->getConnection()->createTable($table);


        /**
         * Create table 'webforms_message'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('webforms_message')
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Message ID'
        )->addColumn(
            'result_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Result ID'
        )->addColumn(
            'user_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'User ID'
        )->addColumn(
            'message',
            Table::TYPE_TEXT,
            null,
            [],
            'Message'
        )->addColumn(
            'author',
            Table::TYPE_TEXT,
            100,
            [],
            'Author'
        )->addColumn(
            'is_customer_emailed',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Customer e-mailed'
        )->addColumn(
            'created_time',
            Table::TYPE_DATETIME,
            null,
            [],
            'Created time'
        )->addForeignKey(
            $installer->getFkName('webforms_message', 'result_id', 'webforms_results', 'id'),
            'result_id',
            $installer->getTable('webforms_results'),
            'id',
            Table::ACTION_CASCADE
        )->setComment(
            'WebForms Message'
        );
        $installer->getConnection()->createTable($table);


        /**
         * Create table 'webforms_store'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('webforms_store')
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'ID'
        )->addColumn(
            'store_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Store ID'
        )->addColumn(
            'entity_type',
            Table::TYPE_TEXT,
            10,
            [],
            'Entity type'
        )->addColumn(
            'entity_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Entity ID'
        )->addColumn(
            'store_data',
            Table::TYPE_TEXT,
            null,
            [],
            'data'
        )->addIndex(
            $installer->getIdxName(
                'webforms_store',
                ['store_id', 'entity_type', 'entity_id'],
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['store_id', 'entity_type', 'entity_id'],
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        )->setComment(
            'WebForms Store Data'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'webforms_quickresponse'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('webforms_quickresponse')
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'ID'
        )->addColumn(
            'title',
            Table::TYPE_TEXT,
            null,
            [],
            'Title'
        )->addColumn(
            'message',
            Table::TYPE_TEXT,
            null,
            [],
            'Message'
        )->addColumn(
            'created_time',
            Table::TYPE_DATETIME,
            null,
            [],
            'Created time'
        )->addColumn(
            'update_time',
            Table::TYPE_TIMESTAMP,
            null,
            [],
            'Last update time'
        );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();

    }
}
