<?php
/**
 * Copyright © Sarus, LLC. All rights reserved.
 */

namespace Sarus\SsoIdp\Model\Assertion;

class ConditionsBuilder
{
    /**
     * @var \Magento\Framework\Intl\DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @param \Magento\Framework\Intl\DateTimeFactory $dateTimeFactory
     */
    public function __construct(
        \Magento\Framework\Intl\DateTimeFactory $dateTimeFactory
    ) {
        $this->dateTimeFactory = $dateTimeFactory;
    }

    /**
     * @param \LightSaml\Model\Protocol\AuthnRequest $authnRequest
     * @return \LightSaml\Model\Assertion\Conditions
     */
    public function build($authnRequest)
    {
        $assertionConditions = new \LightSaml\Model\Assertion\Conditions();
        $assertionConditions->setNotBefore($this->dateTimeFactory->create('now'));
        $assertionConditions->setNotOnOrAfter($this->dateTimeFactory->create('+1 MINUTE')->getTimestamp()); // TODO
        $assertionConditions->addItem(new \LightSaml\Model\Assertion\OneTimeUse());
        $assertionConditions->addItem(
            new \LightSaml\Model\Assertion\AudienceRestriction($authnRequest->getIssuer()->getValue())
        );
        return $assertionConditions;
    }
}
