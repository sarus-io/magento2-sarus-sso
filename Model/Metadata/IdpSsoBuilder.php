<?php
/**
 * Copyright © Sarus, LLC. All rights reserved.
 */

namespace Sarus\SsoIdp\Model\Metadata;

use LightSaml\Model\Metadata\KeyDescriptor;

class IdpSsoBuilder
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
     * @return \LightSaml\Model\Metadata\IdpSsoDescriptor
     */
    public function build()
    {
        $idpSsoDescriptor = new \LightSaml\Model\Metadata\IdpSsoDescriptor();

        $idpSsoDescriptor->setWantAuthnRequestsSigned($this->configIdp->isWantLogoutRequestSigned());

        if ($this->configIdp->isAssertionEncrypted()) {
            $idpSsoDescriptor->addKeyDescriptor(
                $this->createKeyDescriptor(KeyDescriptor::USE_ENCRYPTION, $this->idpCredentials->getCertificate())
            );
        }
        $idpSsoDescriptor->addKeyDescriptor(
            $this->createKeyDescriptor(KeyDescriptor::USE_SIGNING, $this->idpCredentials->getCertificate())
        );

        $idpSsoDescriptor->addNameIDFormat(\LightSaml\SamlConstants::NAME_ID_FORMAT_ENTITY);
        $idpSsoDescriptor->addNameIDFormat(\LightSaml\SamlConstants::NAME_ID_FORMAT_EMAIL);

        if ($this->configIdp->getSingleSingOnUrl()) {
            $idpSsoDescriptor->addSingleSignOnService($this->buildSingleSignOnService());
        }

        if ($this->configIdp->getLogoutUrl()) {
            $idpSsoDescriptor->addSingleLogoutService($this->buildSingleLogoutService());
        }

        return $idpSsoDescriptor;
    }

    /**
     * @param string $type
     * @param \LightSaml\Credential\X509Certificate $certificate
     * @return \LightSaml\Model\Metadata\KeyDescriptor
     */
    private function createKeyDescriptor($type, $certificate)
    {
        return new \LightSaml\Model\Metadata\KeyDescriptor($type, $certificate);
    }

    /**
     * @return \LightSaml\Model\Metadata\SingleSignOnService
     */
    private function buildSingleSignOnService()
    {
        $service = new \LightSaml\Model\Metadata\SingleSignOnService();
        $service->setLocation($this->configIdp->getSingleSingOnUrl());
        $service->setBinding($this->configIdp->getSingleSingOnBinding());
        return $service;
    }

    /**
     * @return \LightSaml\Model\Metadata\SingleLogoutService
     */
    private function buildSingleLogoutService()
    {
        $service = new \LightSaml\Model\Metadata\SingleLogoutService();
        $service->setLocation($this->configIdp->getLogoutUrl());
        $service->setBinding($this->configIdp->getLogoutBinding());
        return $service;
    }
}
