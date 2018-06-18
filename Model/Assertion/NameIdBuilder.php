<?php
/**
 * Copyright © Sarus, LLC. All rights reserved.
 */

namespace Sarus\SsoIdp\Model\Assertion;

class NameIdBuilder
{
    /**
     * @var \Sarus\SsoIdp\Model\Config\ServiceProvider
     */
    private $configSp;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @param \Sarus\SsoIdp\Model\Config\ServiceProvider $configSp
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Sarus\SsoIdp\Model\Config\ServiceProvider $configSp,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->configSp = $configSp;
        $this->customerSession = $customerSession;
    }

    /**
     * @return \LightSaml\Model\Assertion\NameID
     */
    public function build()
    {
        $nameId = new \LightSaml\Model\Assertion\NameID();
        $nameId->setValue($this->getNameIdValue());
        $nameId->setFormat($this->configSp->getNameIdFormat());
        return $nameId;
    }

    /**
     * @return string|int
     */
    private function getNameIdValue()
    {
        $customer = $this->customerSession->getCustomer();
        return $customer->getDataUsingMethod($this->configSp->getNameId());
    }
}
