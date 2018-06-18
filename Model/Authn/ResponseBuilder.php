<?php
/**
 * Copyright © Sarus, LLC. All rights reserved.
 */

namespace Sarus\SsoIdp\Model\Authn;

use LightSaml\SamlConstants;

class ResponseBuilder
{
    /**
     * @var \Sarus\SsoIdp\Model\Config\IdentityProvider
     */
    private $configIdp;

    /**
     * @var \Sarus\SsoIdp\Model\Assertion\IssuerBuilder
     */
    private $issuerBuilder;

    /**
     * @var \Sarus\SsoIdp\Model\AssertionBuilder
     */
    private $assertionBuilder;

    /**
     * @var \Sarus\SsoIdp\Model\Assertion\EncryptorWriterBuilder
     */
    private $encryptorWriterBuilder;

    /**
     * @var \Sarus\SsoIdp\Model\SignatureWriterFactory
     */
    private $signatureWriterFactory;

    /**
     * @var \Magento\Framework\Intl\DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @param \Sarus\SsoIdp\Model\Config\IdentityProvider $configIdp
     * @param \Sarus\SsoIdp\Model\Assertion\IssuerBuilder $issuerBuilder
     * @param \Sarus\SsoIdp\Model\AssertionBuilder $assertionBuilder
     * @param \Sarus\SsoIdp\Model\Assertion\EncryptorWriterBuilder $encryptorWriterBuilder
     * @param \Sarus\SsoIdp\Model\SignatureWriterFactory $signatureWriterFactory
     * @param \Magento\Framework\Intl\DateTimeFactory $dateTimeFactory
     */
    public function __construct(
        \Sarus\SsoIdp\Model\Config\IdentityProvider $configIdp,
        \Sarus\SsoIdp\Model\Assertion\IssuerBuilder $issuerBuilder,
        \Sarus\SsoIdp\Model\AssertionBuilder $assertionBuilder,
        \Sarus\SsoIdp\Model\Assertion\EncryptorWriterBuilder $encryptorWriterBuilder,
        \Sarus\SsoIdp\Model\SignatureWriterFactory $signatureWriterFactory,
        \Magento\Framework\Intl\DateTimeFactory $dateTimeFactory
    ) {
        $this->configIdp = $configIdp;
        $this->issuerBuilder = $issuerBuilder;
        $this->assertionBuilder = $assertionBuilder;
        $this->encryptorWriterBuilder = $encryptorWriterBuilder;
        $this->signatureWriterFactory = $signatureWriterFactory;
        $this->dateTimeFactory = $dateTimeFactory;
    }

    /**
     * @param \LightSaml\Model\Protocol\AuthnRequest $authnRequest
     * @return \LightSaml\Model\Protocol\Response
     */
    public function build($authnRequest)
    {
        $authnResponse = new \LightSaml\Model\Protocol\Response();
        $authnResponse->setInResponseTo($authnRequest->getId());
        $authnResponse->setID(\LightSaml\Helper::generateID());
        $authnResponse->setIssueInstant($this->dateTimeFactory->create());
        $authnResponse->setDestination($authnRequest->getAssertionConsumerServiceURL());
        $authnResponse->setIssuer($this->issuerBuilder->build());
        $authnResponse->setStatus($this->createStatusStatus());

        $assertion = $this->assertionBuilder->build($authnRequest);
        if ($this->configIdp->isAssertionEncrypted()) {
            $authnResponse->addEncryptedAssertion($this->encryptorWriterBuilder->build($assertion));
        } else {
            $authnResponse->addAssertion($assertion);
        }

        if ($this->configIdp->isMessagesSigned()) {
            $authnResponse->setSignature($this->signatureWriterFactory->create());
        }

        if ($authnRequest->getRelayState()) {
            $authnResponse->setRelayState($authnRequest->getRelayState());
        }

        return $authnResponse;
    }

    /**
     * @return \LightSaml\Model\Protocol\Status
     */
    private function createStatusStatus()
    {
        return new \LightSaml\Model\Protocol\Status(
            new \LightSaml\Model\Protocol\StatusCode(SamlConstants::STATUS_SUCCESS)
        );
    }
}
