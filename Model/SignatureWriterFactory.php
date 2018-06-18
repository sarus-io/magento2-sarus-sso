<?php
/**
 * Copyright © Sarus, LLC. All rights reserved.
 */

namespace Sarus\SsoIdp\Model;

class SignatureWriterFactory
{
    /**
     * @var \Sarus\SsoIdp\Model\Config\IdentityProvider
     */
    private $configIdp;

    /**
     * @var \Sarus\SsoIdp\Helper\IdpCredentials
     */
    private $idpCredentials;

    /**
     * @param \Sarus\SsoIdp\Model\Config\IdentityProvider $configIdp
     * @param \Sarus\SsoIdp\Helper\IdpCredentials $idpCredentials
     */
    public function __construct(
        \Sarus\SsoIdp\Model\Config\IdentityProvider $configIdp,
        \Sarus\SsoIdp\Helper\IdpCredentials $idpCredentials
    ) {
        $this->configIdp = $configIdp;
        $this->idpCredentials = $idpCredentials;
    }

    /**
     * @param string|int|null $storeId
     * @return \LightSaml\Model\XmlDSig\SignatureWriter
     */
    public function create($storeId = null)
    {
        return new \LightSaml\Model\XmlDSig\SignatureWriter(
            $this->idpCredentials->getCertificate($storeId),
            $this->idpCredentials->getPrivateKey($storeId),
            $this->configIdp->getDigestAlgorithm($storeId)
        );
    }
}
