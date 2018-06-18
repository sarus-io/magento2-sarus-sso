<?php
/**
 * Copyright © Sarus, LLC. All rights reserved.
 */

namespace Sarus\SsoIdp\Helper;

use LightSaml\Credential\KeyHelper;

class SpCredentials
{
    /**
     * @var \Sarus\SsoIdp\Model\Config\ServiceProvider
     */
    private $configSp;

    /**
     * @param \Sarus\SsoIdp\Model\Config\ServiceProvider $configSp
     */
    public function __construct(
        \Sarus\SsoIdp\Model\Config\ServiceProvider $configSp
    ) {
        $this->configSp = $configSp;
    }

    /**
     * @param string|int|null $storeId
     * @return \LightSaml\Credential\X509Certificate
     */
    public function getCertificate($storeId = null)
    {
        $certificate = new \LightSaml\Credential\X509Certificate();
        return $certificate->loadPem($this->configSp->getCert($storeId));
    }

    /**
     * @param string|int|null $storeId
     * @return \RobRichards\XMLSecLibs\XMLSecurityKey
     */
    public function getPublicKey($storeId = null)
    {
        return KeyHelper::createPublicKey($this->getCertificate($storeId));
    }
}
