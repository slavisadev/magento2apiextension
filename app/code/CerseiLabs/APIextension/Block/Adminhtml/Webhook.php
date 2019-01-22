<?php
namespace CerseiLabs\APIextension\Block\Adminhtml;
class Webhook extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {

        $this->_controller = 'adminhtml_webhook';/*block grid.php directory*/
        $this->_blockGroup = 'CerseiLabs_APIextension';
        $this->_headerText = __('Webhook');
        $this->_addButtonLabel = __('Add New Entry');
        parent::_construct();

    }
}
