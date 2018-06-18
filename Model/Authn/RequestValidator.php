<?php
/**
 * Copyright © Sarus, LLC. All rights reserved.
 */

namespace Sarus\SsoIdp\Model\Authn;

class RequestValidator
{
    /**
     * @var \Sarus\SsoIdp\Model\Config\IdentityProvider
     */
    private $configIdp;

    /**
     * @var \Sarus\SsoIdp\Model\Config\ServiceProvider
     */
    private $configSp;

    /**
     * @var \Sarus\SsoIdp\Helper\SpCredentials
     */
    private $spCredentials;

    /**
     * @param \Sarus\SsoIdp\Model\Config\IdentityProvider $configIdp
     * @param \Sarus\SsoIdp\Model\Config\ServiceProvider $configSp
     * @param \Sarus\SsoIdp\Helper\SpCredentials $spCredentials
     */
    public function __construct(
        \Sarus\SsoIdp\Model\Config\IdentityProvider $configIdp,
        \Sarus\SsoIdp\Model\Config\ServiceProvider $configSp,
        \Sarus\SsoIdp\Helper\SpCredentials $spCredentials
    ) {
        $this->configIdp = $configIdp;
        $this->configSp = $configSp;
        $this->spCredentials = $spCredentials;
    }

    /**
     * @param \LightSaml\Model\Protocol\AuthnRequest $authnRequest
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function validate($authnRequest)
    {
        $this->validateIssuer($authnRequest);

        $this->validateSignature($authnRequest);

        $this->validateDestination($authnRequest);
        $this->validateNameIdFormat($authnRequest);

        $this->validateBinding($authnRequest);
        $this->validateAssertionConsumerUrl($authnRequest);

        return true;
    }

    /**
     * @param \LightSaml\Model\Protocol\AuthnRequest $authnRequest
     * @return bool
     * @throws \InvalidArgumentException
     */
    private function validateIssuer($authnRequest)
    {
        if (null == $authnRequest->getIssuer()) {
            throw new \InvalidArgumentException('Issuer is not specified.');
        }

        if ($authnRequest->getIssuer()->getValue() !== $this->configSp->getEntityId()) {
            throw new \InvalidArgumentException(sprintf('SP %s unknown issuer.', $authnRequest->getIssuer()->getValue()));
        }
        return true;
    }

    /**
     * @param \LightSaml\Model\Protocol\AuthnRequest $authnRequest
     * @return bool
     * @throws \InvalidArgumentException
     */
    private function validateSignature($authnRequest)
    {
        /** @var \LightSaml\Model\XmlDSig\SignatureXmlReader $signatureReader */
        $signatureReader = $authnRequest->getSignature();
        if ($this->configIdp->isWantAuthnSigned() && !$signatureReader) {
            throw new \InvalidArgumentException('No signature, but is required.');
        }

        if (!$signatureReader) {
            return true;
        }

        $isSignatureValid = $signatureReader->validate($this->spCredentials->getPublicKey());
        if (!$isSignatureValid) {
            throw new \InvalidArgumentException('Signature is not validated.');
        }
        return true;
    }

    /**
     * @param \LightSaml\Model\Protocol\AuthnRequest $authnRequest
     * @return bool
     * @throws \InvalidArgumentException
     */
    private function validateDestination($authnRequest)
    {
        if (!$authnRequest->getDestination()) {
            throw new \InvalidArgumentException('Destination url is not specified.');
        }

        if ($this->configIdp->getSingleSingOnUrl() != $authnRequest->getDestination()) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Destination url is %s, expected %s.',
                    $authnRequest->getDestination(),
                    $this->configIdp->getSingleSingOnUrl()
                )
            );
        }

        return true;
    }

    /**
     * @param \LightSaml\Model\Protocol\AuthnRequest $authnRequest
     * @return bool
     * @throws \InvalidArgumentException
     */
    private function validateNameIdFormat($authnRequest)
    {
        if (!$authnRequest->getNameIDPolicy() || !$authnRequest->getNameIDPolicy()->getFormat()) {
            throw new \InvalidArgumentException('Name ID format is not specified.');
        }

        $requestIDPolicyFormat = $authnRequest->getNameIDPolicy()->getFormat();
        if ($requestIDPolicyFormat !== $this->configSp->getNameIdFormat()) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Name ID format is %s, expected %s.',
                    $requestIDPolicyFormat,
                    $this->configSp->getNameIdFormat()
                )
            );
        }

        return true;
    }

    /**
     * @param \LightSaml\Model\Protocol\AuthnRequest $authnRequest
     * @return bool
     * @throws \InvalidArgumentException
     */
    private function validateBinding($authnRequest)
    {
        if (!$authnRequest->getProtocolBinding()) {
            throw new \InvalidArgumentException('Protocol binding is not specified.');
        }

        if ($this->configSp->getAssertionConsumerBinding() != $authnRequest->getProtocolBinding()) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Protocol binding is %s, expected %s.',
                    $authnRequest->getProtocolBinding(),
                    $this->configSp->getAssertionConsumerBinding()
                )
            );
        }

        return true;
    }

    /**
     * @param \LightSaml\Model\Protocol\AuthnRequest $authnRequest
     * @return bool
     * @throws \InvalidArgumentException
     */
    private function validateAssertionConsumerUrl($authnRequest)
    {
        if (!$authnRequest->getAssertionConsumerServiceURL()) {
            throw new \InvalidArgumentException('Assert consumer service url is not specified.');
        }

        if ($this->configSp->getAssertionConsumerUrl() != $authnRequest->getAssertionConsumerServiceURL()) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Assert consumer service url is %s, expected %s.',
                    $authnRequest->getAssertionConsumerServiceURL(),
                    $this->configSp->getAssertionConsumerUrl()
                )
            );
        }

        return true;
    }
}
