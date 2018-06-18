<?php
/**
 * Copyright © Sarus, LLC. All rights reserved.
 */

namespace Sarus\SsoIdp\Block\Adminhtml\System\Config\Field;

use Magento\Framework\Data\Form\Element\AbstractElement;

class Url extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\Framework\Url
     */
    private $urlBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Url $urlBuilder
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Url $urlBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
        $this->storeManager = $storeManager;
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
        $websiteCode = $this->request->getParam('website');
        return $websiteCode
            ? $this->storeManager->getWebsite($websiteCode)->getDefaultStore()
            : $this->storeManager->getDefaultStoreView();
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
