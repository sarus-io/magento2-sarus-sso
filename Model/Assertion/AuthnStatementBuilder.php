<?php
/**
 * Copyright © Sarus, LLC. All rights reserved.
 */

namespace Sarus\SsoIdp\Model\Assertion;

use LightSaml\SamlConstants;

class AuthnStatementBuilder
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Framework\Intl\DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Intl\DateTimeFactory $dateTimeFactory
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Intl\DateTimeFactory $dateTimeFactory
    ) {
        $this->customerSession = $customerSession;
        $this->dateTimeFactory = $dateTimeFactory;
    }

    /**
     * @return \LightSaml\Model\Assertion\AuthnStatement
     */
    public function build()
    {
        $authnStatement = new \LightSaml\Model\Assertion\AuthnStatement();
        $authnStatement->setAuthnInstant($this->dateTimeFactory->create('-10 MINUTE'));
        $authnStatement->setSessionIndex($this->customerSession->getSessionId());
        $authnStatement->setAuthnContext($this->buildAuthnContext());
        return $authnStatement;
    }

    /**
     * @return \LightSaml\Model\Assertion\AuthnContext
     */
    private function buildAuthnContext()
    {
        $authnContext = new \LightSaml\Model\Assertion\AuthnContext();
        $authnContext->setAuthnContextClassRef(SamlConstants::AUTHN_CONTEXT_PASSWORD_PROTECTED_TRANSPORT); // TODO
        return $authnContext;
    }
}
