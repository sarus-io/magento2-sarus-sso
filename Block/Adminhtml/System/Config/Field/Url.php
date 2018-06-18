<?php
/**
 * Copyright © Sarus, LLC. All rights reserved.
 */

namespace Sarus\SsoIdp\Block\Adminhtml\System\Config\Field;

use Magento\Framework\Data\Form\Element\AbstractElement;

class Url extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Magento\Framework\Url
     */
    private $urlBuilder;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Url $urlBuilder
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Url $urlBuilder,
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $data);
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $this->urlBuilder->setScope($this->getCurrentScopeStore());
        return $this->urlBuilder->getUrl((string)$element->getValue(), ['_secure' => true, '_nosid' => true, '_query' => []]);
    }

    /**
     * @return \Magento\Store\Model\Store
     */
    private function getCurrentScopeStore()
    {
        $websiteCode = $this->_request->getParam('website');
        return $websiteCode
            ? $this->_storeManager->getWebsite($websiteCode)->getDefaultStore()
            : $this->_storeManager->getDefaultStoreView();
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsCanUseWebsiteValue();
        $element->unsCanUseDefaultValue();
        return parent::render($element);
    }
}
