<?php
/**
 * Copyright © Sarus, LLC. All rights reserved.
 */

namespace Sarus\SsoIdp\Model\Logout;

class RequestBuilder
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
     * @var \Sarus\SsoIdp\Model\Assertion\NameIdBuilder
     */
    private $nameIdBuilder;

    /**
     * @var \Sarus\SsoIdp\Model\SignatureWriterFactory
     */
    private $signatureWriterFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Framework\Intl\DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @param \Sarus\SsoIdp\Model\Config\IdentityProvider $configIdp
     * @param \Sarus\SsoIdp\Model\Config\ServiceProvider $configSp
     * @param \Sarus\SsoIdp\Model\Assertion\IssuerBuilder $issuerBuilder
     * @param \Sarus\SsoIdp\Model\Assertion\NameIdBuilder $nameIdBuilder
     * @param \Sarus\SsoIdp\Model\SignatureWriterFactory $signatureWriterFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Intl\DateTimeFactory $dateTimeFactory
     */
    public function __construct(
        \Sarus\SsoIdp\Model\Config\IdentityProvider $configIdp,
        \Sarus\SsoIdp\Model\Config\ServiceProvider $configSp,
        \Sarus\SsoIdp\Model\Assertion\IssuerBuilder $issuerBuilder,
        \Sarus\SsoIdp\Model\Assertion\NameIdBuilder $nameIdBuilder,
        \Sarus\SsoIdp\Model\SignatureWriterFactory $signatureWriterFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Intl\DateTimeFactory $dateTimeFactory
    ) {
        $this->configIdp = $configIdp;
        $this->configSp = $configSp;
        $this->issuerBuilder = $issuerBuilder;
        $this->nameIdBuilder = $nameIdBuilder;
        $this->signatureWriterFactory = $signatureWriterFactory;
        $this->customerSession = $customerSession;
        $this->dateTimeFactory = $dateTimeFactory;
    }

    /**
     * @return \LightSaml\Model\Protocol\LogoutRequest
     */
    public function build()
    {
        $logoutResponse = new \LightSaml\Model\Protocol\LogoutRequest();
        $logoutResponse->setIssuer($this->issuerBuilder->build());
        $logoutResponse->setID(\LightSaml\Helper::generateID());
        $logoutResponse->setIssueInstant($this->dateTimeFactory->create());
        $logoutResponse->setNameID($this->nameIdBuilder->build());
        $logoutResponse->setDestination($this->configSp->getSingleLogoutUrl());
        $logoutResponse->setSessionIndex($this->customerSession->getSessionId());

        if ($this->configIdp->isMessagesSigned()) {
            $logoutResponse->setSignature($this->signatureWriterFactory->create());
        }
        return $logoutResponse;
    }
}
