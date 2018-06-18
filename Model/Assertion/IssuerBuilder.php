<?php
/**
 * Copyright © Sarus, LLC. All rights reserved.
 */

namespace Sarus\SsoIdp\Model\Assertion;

class IssuerBuilder
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
     * @return \LightSaml\Model\Assertion\Issuer
     */
    public function build()
    {
        $issuer = new \LightSaml\Model\Assertion\Issuer();
        $issuer->setValue($this->configIdp->getEntityId());
        return $issuer;
    }
}
