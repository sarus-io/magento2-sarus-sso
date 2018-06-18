<?php
/**
 * Copyright © Sarus, LLC. All rights reserved.
 */

namespace Sarus\SsoIdp\Model\Config;

use Magento\Store\Model\ScopeInterface;

class IdentityProvider
{
    const XML_PATH_ENABLED = 'sarus_sso_idp/general/enabled';
    const XML_PATH_ENABLED_SLO = 'sarus_sso_idp/general/enabled_slo';

    const XML_PATH_ENTITY_ID = 'sarus_sso_idp/general/entity_id';

    const XML_PATH_METADATA_URL = 'sarus_sso_idp/general/metadata_url';
    const XML_PATH_SINGLE_SING_ON_URL = 'sarus_sso_idp/general/single_sing_on_url';
    const XML_PATH_SINGLE_SING_ON_BINDING = 'sarus_sso_idp/general/single_sing_on_binding';
    const XML_PATH_SINGLE_LOGOUT_URL = 'sarus_sso_idp/general/single_logout_url';
    const XML_PATH_SINGLE_LOGOUT_BINDING = 'sarus_sso_idp/general/single_logout_binding';

    const XML_PATH_SP_COUNT = 'sarus_sso_idp/general/sp_count';
    const XML_PATH_ALLOWED_SECONDS_SKEW = 'sarus_sso_idp/general/allowed_seconds_skew';


    const XML_PATH_WANT_METADATA_SIGNED = 'sarus_sso_idp/security/metadata_signed';
    const XML_PATH_WANT_AUTHN_SIGNED = 'sarus_sso_idp/security/want_authn_signed';
    const XML_PATH_WANT_LOGOUT_REQUEST_SIGNED = 'sarus_sso_idp/security/want_logout_request_signed';
    const XML_PATH_WANT_LOGOUT_RESPONSE_SIGNED = 'sarus_sso_idp/security/want_logout_response_signed';

    const XML_PATH_ASSERTION_ENCRYPTED = 'sarus_sso_idp/security/assertion_encrypted';
    const XML_PATH_ASSERTION_SIGNED = 'sarus_sso_idp/security/assertion_signed';
    const XML_PATH_MESSAGES_SIGNED = 'sarus_sso_idp/security/messages_signed';

    const XML_PATH_SIGNATURE_ALGORITHM = 'sarus_sso_idp/security/signature_algorithm';
    const XML_PATH_DIGEST_ALGORITHM = 'sarus_sso_idp/security/digest_algorithm';
    const XML_PATH_ENCRYPTED_METHOD_KEY = 'sarus_sso_idp/security/encrypted_method_key';
    const XML_PATH_ENCRYPTED_METHOD_DATA = 'sarus_sso_idp/security/encrypted_method_data';

    const XML_PATH_PRIVATE_KEY = 'sarus_sso_idp/credentials/private_key';
    const XML_PATH_CERT = 'sarus_sso_idp/credentials/cert';

    const XML_PATH_TECHNICAL_CONTACT_GIVEN_NAME = 'sarus_sso_idp/contact/technical_contact_given_name';
    const XML_PATH_TECHNICAL_CONTACT_EMAIL = 'sarus_sso_idp/contact/technical_contact_email';

    const XML_PATH_SUPPORT_CONTACT_GIVEN_NAME = 'sarus_sso_idp/contact/support_contact_given_name';
    const XML_PATH_SUPPORT_CONTACT_EMAIL = 'sarus_sso_idp/contact/support_contact_email';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\Url
     */
    private $urlBuilder;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\UrlFactory $urlBuilderFactory
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\UrlFactory $urlBuilderFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->urlBuilder = $urlBuilderFactory->create();
    }

    /**
     * @param int|string|null $storeId
     * @return bool
     */
    public function isEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int|string|null $storeId
     * @return bool
     */
    public function isEnabledSlo($storeId = null)
    {
        return $this->isEnabled($storeId)
            && $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED_SLO, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int|string|null $storeId
     * @return string
     */
    public function getEntityId($storeId = null)
    {
        return $this->getMetadataUrl($storeId);
    }

    /**
     * @param int|string|null $storeId
     * @return string
     */
    public function getMetadataUrl($storeId = null)
    {
        return $this->getUrl($storeId, $this->scopeConfig->getValue(self::XML_PATH_METADATA_URL));
    }

    /**
     * @param int|string|null $storeId
     * @return string
     */
    public function getSingleSingOnUrl($storeId = null)
    {
        return $this->getUrl($storeId, $this->scopeConfig->getValue(self::XML_PATH_SINGLE_SING_ON_URL));
    }

    /**
     * @param int|string|null $storeId
     * @return string
     */
    public function getSingleSingOnBinding($storeId = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_SINGLE_SING_ON_BINDING, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int|string|null $storeId
     * @return string
     */
    public function getLogoutUrl($storeId = null)
    {
        return $this->getUrl($storeId, $this->scopeConfig->getValue(self::XML_PATH_SINGLE_LOGOUT_URL));
    }

    /**
     * @param int|string|null $storeId
     * @return string
     */
    public function getLogoutBinding($storeId = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_SINGLE_LOGOUT_BINDING, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int|string|null $storeId
     * @param string $urlPath
     * @return string
     */
    private function getUrl($storeId, $urlPath)
    {
        return $this->urlBuilder->setScope($storeId)->getUrl($urlPath, ['_secure' => true, '_nosid' => true, '_query' => []]);
    }

    /**
     * @param int|string|null $storeId
     * @return int
     */
    public function getSpCount($storeId = null)
    {
        return $this->isEnabled($storeId)
            ? (int)$this->scopeConfig->getValue(self::XML_PATH_SP_COUNT, ScopeInterface::SCOPE_STORE, $storeId)
            : 0;
    }

    /**
     * @return int
     */
    public function getAllowedSecondsSkew()
    {
        return (int)$this->scopeConfig->getValue(self::XML_PATH_ALLOWED_SECONDS_SKEW);
    }

    /**
     * @param int|string|null $storeId
     * @return bool
     */
    public function isMetadataSigned($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_WANT_METADATA_SIGNED, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int|string|null $storeId
     * @return bool
     */
    public function isWantAuthnSigned($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_WANT_AUTHN_SIGNED, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int|string|null $storeId
     * @return bool
     */
    public function isWantLogoutRequestSigned($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_WANT_LOGOUT_REQUEST_SIGNED, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int|string|null $storeId
     * @return bool
     */
    public function isWantLogoutResponseSigned($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_WANT_LOGOUT_RESPONSE_SIGNED, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int|string|null $storeId
     * @return bool
     */
    public function isAssertionEncrypted($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ASSERTION_ENCRYPTED, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int|string|null $storeId
     * @return bool
     */
    public function isAssertionSigned($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ASSERTION_SIGNED, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int|string|null $storeId
     * @return bool
     */
    public function isMessagesSigned($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_MESSAGES_SIGNED, ScopeInterface::SCOPE_STORE, $storeId);
    }
    /**
     * @param int|string|null $storeId
     * @return string
     */
    public function getSignatureAlgorithm($storeId = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_SIGNATURE_ALGORITHM, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int|string|null $storeId
     * @return string
     */
    public function getDigestAlgorithm($storeId = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_DIGEST_ALGORITHM, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int|string|null $storeId
     * @return string
     */
    public function getEncryptedMethodKey($storeId = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ENCRYPTED_METHOD_KEY, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int|string|null $storeId
     * @return string
     */
    public function getEncryptedMethodData($storeId = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ENCRYPTED_METHOD_DATA, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int|string|null $storeId
     * @return string
     */
    public function getPrivateKey($storeId = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PRIVATE_KEY, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int|string|null $storeId
     * @return string
     */
    public function getCert($storeId = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_CERT, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int|string|null $storeId
     * @return string
     */
    public function getTechnicalContactGivenName($storeId = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_TECHNICAL_CONTACT_GIVEN_NAME, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int|string|null $storeId
     * @return string
     */
    public function getTechnicalContactEmail($storeId = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_TECHNICAL_CONTACT_EMAIL, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int|string|null $storeId
     * @return string
     */
    public function getSupportContactGivenName($storeId = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_SUPPORT_CONTACT_GIVEN_NAME, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int|string|null $storeId
     * @return string
     */
    public function getSupportContactEmail($storeId = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_SUPPORT_CONTACT_EMAIL, ScopeInterface::SCOPE_STORE, $storeId);
    }
}
