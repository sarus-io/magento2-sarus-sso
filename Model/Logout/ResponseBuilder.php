<?php
/**
 * Copyright © Sarus, LLC. All rights reserved.
 */

namespace Sarus\SsoIdp\Model\Logout;

use LightSaml\SamlConstants;

class ResponseBuilder
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
     * @var \Sarus\SsoIdp\Model\Assertion\IssuerBuilder
     */
    private $issuerBuilder;

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
     * @param \Sarus\SsoIdp\Model\Config\ServiceProvider $configSp
     * @param \Sarus\SsoIdp\Model\Assertion\IssuerBuilder $issuerBuilder
     * @param \Sarus\SsoIdp\Model\SignatureWriterFactory $signatureWriterFactory
     * @param \Magento\Framework\Intl\DateTimeFactory $dateTimeFactory
     */
    public function __construct(
        \Sarus\SsoIdp\Model\Config\IdentityProvider $configIdp,
        \Sarus\SsoIdp\Model\Config\ServiceProvider $configSp,
        \Sarus\SsoIdp\Model\Assertion\IssuerBuilder $issuerBuilder,
        \Sarus\SsoIdp\Model\SignatureWriterFactory $signatureWriterFactory,
        \Magento\Framework\Intl\DateTimeFactory $dateTimeFactory
    ) {
        $this->configIdp = $configIdp;
        $this->configSp = $configSp;
        $this->issuerBuilder = $issuerBuilder;
        $this->signatureWriterFactory = $signatureWriterFactory;
        $this->dateTimeFactory = $dateTimeFactory;
    }

    /**
     * @param \LightSaml\Model\Protocol\LogoutRequest $logoutRequest
     * @return \LightSaml\Model\Protocol\LogoutResponse
     */
    public function build($logoutRequest)
    {
        $logoutResponse = new \LightSaml\Model\Protocol\LogoutResponse();
        $logoutResponse->setID(\LightSaml\Helper::generateID());
        $logoutResponse->setIssueInstant($this->dateTimeFactory->create());
        $logoutResponse->setIssuer($this->issuerBuilder->build());
        $logoutResponse->setInResponseTo($logoutRequest->getId());
        $logoutResponse->setDestination($this->configSp->getSingleLogoutUrl());
        $logoutResponse->setStatus($this->createStatusStatus());

        if ($this->configIdp->isMessagesSigned()) {
            $logoutResponse->setSignature($this->signatureWriterFactory->create());
        }

        if ($logoutRequest->getRelayState()) {
            $logoutResponse->setRelayState($logoutRequest->getRelayState());
        }

        return $logoutResponse;
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
