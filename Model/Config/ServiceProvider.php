<?php
/**
 * Copyright © Sarus, LLC. All rights reserved.
 */

namespace Sarus\SsoIdp\Model\Config;

use Magento\Store\Model\ScopeInterface;

class ServiceProvider
{
    const XML_PATH_ENTITY_ID = 'sarus_sso_idp/sp/entity_id';
    const XML_PATH_NAME_ID = 'sarus_sso_idp/sp/name_id';
    const XML_PATH_ASSERTION_CONSUMER_URL = 'sarus_sso_idp/sp/assertion_consumer_url';
    const XML_PATH_ASSERTION_CONSUMER_BINDING = 'sarus_sso_idp/sp/assertion_consumer_binding';
    const XML_PATH_SINGLE_LOGOUT_URL = 'sarus_sso_idp/sp/single_logout_url';
    const XML_PATH_SINGLE_LOGOUT_BINDING = 'sarus_sso_idp/sp/single_logout_binding';
    const XML_PATH_CERT = 'sarus_sso_idp/sp/cert';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Sarus\SsoIdp\Model\Config\Source\NameId
     */
    private $nameIdOptions;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Sarus\SsoIdp\Model\Config\Source\NameId $nameIdOptions
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Sarus\SsoIdp\Model\Config\Source\NameId $nameIdOptions
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->nameIdOptions = $nameIdOptions;
    }

    /**
     * @param int|string|null $storeId
     * @return string
     */
    public function getEntityId($storeId = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ENTITY_ID, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int|string|null $storeId
     * @return string
     */
    public function getNameId($storeId = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_NAME_ID, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int|string|null $storeId
     * @return string
     */
    public function getNameIdFormat($storeId = null)
    {
        return $this->nameIdOptions->getFormat($this->getNameId($storeId));
    }

    /**
     * @param int|string|null $storeId
     * @return string
     */
    public function getAssertionConsumerUrl($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ASSERTION_CONSUMER_URL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int|string|null $storeId
     * @return string
     */
    public function getAssertionConsumerBinding($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ASSERTION_CONSUMER_BINDING,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int|string|null $storeId
     * @return string
     */
    public function getSingleLogoutUrl($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SINGLE_LOGOUT_URL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int|string|null $storeId
     * @return string
     */
    public function getSingleLogoutBinding($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SINGLE_LOGOUT_BINDING,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }


    /**
     * @param int|string|null $storeId
     * @return string
     */
    public function getCert($storeId = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_CERT, ScopeInterface::SCOPE_STORE, $storeId);
    }
}
