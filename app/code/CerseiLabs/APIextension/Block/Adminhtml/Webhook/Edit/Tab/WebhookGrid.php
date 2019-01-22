<?php
namespace CerseiLabs\APIextension\Block\Adminhtml\Webhook\Edit\Tab;

class WebhookGrid extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        array $data = array()
    )
    {
        $this->_systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /* @var $model \Magento\Cms\Model\Page */
        $model = $this->_coreRegistry->registry('apiextension_webhook');

        $isElementDisabled = false;
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', array('legend' => __('Webhook Options')));

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', array('name' => 'id'));
        }

        $optionArray = array(
            0 => 'no',
            1 => 'yes'
        );

        $fieldset->addField(
            'event_code',
            'text',
            array(
                'name' => 'event_code',
                'label' => __('event code'),
                'title' => __('event code'),
                'required' => true,
            )
        );
        $fieldset->addField(
            'description',
            'textarea',
            array(
                'name' => 'description',
                'label' => __('Description'),
                'title' => __('Description'),
                /*'required' => true,*/
            )
        );
        $fieldset->addField(
            'token',
            'text',
            array(
                'name' => 'token',
                'label' => __('Token'),
                'title' => __('Token'),
                'required' => true,
            )
        );
        $fieldset->addField(
            'callback_url',
            'text',
            array(
                'name' => 'callback_url',
                'label' => __('Callback URL'),
                'title' => __('Callback URL'),
                'required' => true,
            )
        );
        $fieldset->addField(
            'data',
            'text',
            array(
                'name' => 'data',
                'label' => __('Data'),
                'title' => __('Data'),
                /*'required' => true,*/
            )
        );
        $fieldset->addField(
            'active',
            'select',
            array(
                'name' => 'active',
                'label' => __('Active'),
                'title' => __('Active'),
                'values' => $optionArray,
                'value' => $model->getData('active'),
                'required' => true,
            )
        );
        /*{{CedAddFormField}}*/

        if (!$model->getId()) {
            $model->setData('status', $isElementDisabled ? '2' : '1');
        }

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Webhook data');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Webhook data');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
