<?php
/**
 * Copyright © Sarus, LLC. All rights reserved.
 */

namespace Sarus\SsoIdp\Helper;

use LightSaml\Credential\KeyHelper;

class IdpCredentials
{
    /**
     * @var \Sarus\SsoIdp\Model\Config\IdentityProvider
     */
    private $configIdp;

    /**
     * @param \Sarus\SsoIdp\Model\Config\IdentityProvider $configIdp
     */
    public function __construct(
        \Sarus\SsoIdp\Model\Config\IdentityProvider $configIdp
    ) {
        $this->configIdp = $configIdp;
    }

    /**
     * @param string|int|null $storeId
     * @return \RobRichards\XMLSecLibs\XMLSecurityKey
     */
    public function getPublicKey($storeId = null)
    {
        return KeyHelper::createPublicKey($this->getCertificate($storeId));
    }

    /**
     * @param string|int|null $storeId
     * @return \LightSaml\Credential\X509Certificate
     */
    public function getCertificate($storeId = null)
    {
        $certificate = new \LightSaml\Credential\X509Certificate();
        return $certificate->loadPem($this->configIdp->getCert($storeId));
    }

    /**
     * @param string|int|null $storeId
     * @return \RobRichards\XMLSecLibs\XMLSecurityKey
     */
    public function getPrivateKey($storeId = null)
    {
        return KeyHelper::createPrivateKey(
            $this->configIdp->getPrivateKey($storeId),
            '',
            false,
            $this->configIdp->getSignatureAlgorithm($storeId)
        );
    }
}
