<?php
/**
 * Copyright © Sarus, LLC. All rights reserved.
 */

namespace Sarus\SsoIdp\Model\Config;

use Magento\Store\Model\ScopeInterface;

class CertificateGenerator
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param string|null $websiteCode
     * @return array
     */
    public function generate($websiteCode = null)
    {
        $privateKey = openssl_pkey_new($this->getPrivateKeyConfiguration());
        $csr = openssl_csr_new($this->getStoreInformation($websiteCode), $privateKey);
        $x509 = openssl_csr_sign($csr, null, $privateKey, 3650, ['digest_alg' => 'sha512']);

        $cert = '';
        openssl_x509_export($x509, $cert);

        $key = '';
        openssl_pkey_export($privateKey, $key, null);

        return [
            'private_key' => $key,
            'certificate' => $cert
        ];
    }

    /**
     * @return array
     */
    private function getPrivateKeyConfiguration()
    {
        return [
            'digest_alg' => 'sha512',
            'private_key_bits' => 4096,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];
    }

    /**
     * @param string|null $websiteCode
     * @return array
     */
    private function getStoreInformation($websiteCode = null)
    {
        $data['countryName'] = $this->getConfigValue('general/store_information/country_id', $websiteCode) ?: 'US';

        $state = $this->getConfigValue('general/store_information/region_id', $websiteCode);
        if ($state) {
            $data['stateOrProvinceName'] = $state;
        }

        $organizationName = $this->getConfigValue('general/store_information/name', $websiteCode);
        if ($organizationName) {
            $data['organizationName'] = $organizationName;
        }

        $emailAddress = $this->getConfigValue('trans_email/ident_support/email', $websiteCode);
        if ($emailAddress) {
            $data['organizationName'] = $emailAddress;
        }

        return $data;
    }

    /**
     * @param string $path
     * @param string|null $websiteCode
     * @return string
     */
    private function getConfigValue($path, $websiteCode = null)
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_WEBSITE, $websiteCode);
    }
}
