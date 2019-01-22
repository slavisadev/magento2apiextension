<?php
namespace CerseiLabs\APIextension\Block\Adminhtml\Webhook\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected function _construct()
    {

        parent::_construct();
        $this->setId('checkmodule_webhook_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Webhook Information'));
    }
}
