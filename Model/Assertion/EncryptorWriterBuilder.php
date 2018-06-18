<?php
/**
 * Copyright © Sarus, LLC. All rights reserved.
 */

namespace Sarus\SsoIdp\Model\Assertion;

class EncryptorWriterBuilder
{
    /**
     * @var \Sarus\SsoIdp\Model\Config\IdentityProvider
     */
    private $configIdp;

    /**
     * @var \Sarus\SsoIdp\Helper\SpCredentials
     */
    private $spCredentials;

    /**
     * @param \Sarus\SsoIdp\Model\Config\IdentityProvider $configIdp
     * @param \Sarus\SsoIdp\Helper\SpCredentials $spCredentials
     */
    public function __construct(
        \Sarus\SsoIdp\Model\Config\IdentityProvider $configIdp,
        \Sarus\SsoIdp\Helper\SpCredentials $spCredentials
    ) {
        $this->configIdp = $configIdp;
        $this->spCredentials = $spCredentials;
    }

    /**
     * @param \LightSaml\Model\Assertion\Assertion $assertion $assertion
     * @return \LightSaml\Model\Assertion\EncryptedAssertionWriter
     */
    public function build($assertion)
    {
        $encryptedAssertionWriter = new \LightSaml\Model\Assertion\EncryptedAssertionWriter(
            $this->configIdp->getEncryptedMethodData(),
            $this->configIdp->getEncryptedMethodKey()
        );

        $encryptedAssertionWriter->encrypt($assertion, $this->spCredentials->getPublicKey());
        return $encryptedAssertionWriter;
    }
}
