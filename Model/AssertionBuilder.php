<?php
/**
 * Copyright © Sarus, LLC. All rights reserved.
 */

namespace Sarus\SsoIdp\Model;

class AssertionBuilder
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
     * @var \Sarus\SsoIdp\Model\Assertion\SubjectBuilder
     */
    private $subjectBuilder;

    /**
     * @var \Sarus\SsoIdp\Model\Assertion\ConditionsBuilder
     */
    private $conditionsBuilder;

    /**
     * @var \Sarus\SsoIdp\Model\Assertion\AuthnStatementBuilder
     */
    private $authnStatementBuilder;

    /**
     * @var \Sarus\SsoIdp\Model\Assertion\AttributeStatementBuilder
     */
    private $attributeStatementBuilder;

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
     * @param \Sarus\SsoIdp\Model\Assertion\SubjectBuilder $subjectBuilder
     * @param \Sarus\SsoIdp\Model\Assertion\ConditionsBuilder $conditionsBuilder
     * @param \Sarus\SsoIdp\Model\Assertion\AuthnStatementBuilder $authnStatementBuilder
     * @param \Sarus\SsoIdp\Model\Assertion\AttributeStatementBuilder $attributeStatementBuilder
     * @param \Sarus\SsoIdp\Model\SignatureWriterFactory $signatureWriterFactory
     * @param \Magento\Framework\Intl\DateTimeFactory $dateTimeFactory
     */
    public function __construct(
        \Sarus\SsoIdp\Model\Config\IdentityProvider $configIdp,
        \Sarus\SsoIdp\Model\Assertion\IssuerBuilder $issuerBuilder,
        \Sarus\SsoIdp\Model\Assertion\SubjectBuilder $subjectBuilder,
        \Sarus\SsoIdp\Model\Assertion\ConditionsBuilder $conditionsBuilder,
        \Sarus\SsoIdp\Model\Assertion\AuthnStatementBuilder $authnStatementBuilder,
        \Sarus\SsoIdp\Model\Assertion\AttributeStatementBuilder $attributeStatementBuilder,
        \Sarus\SsoIdp\Model\SignatureWriterFactory $signatureWriterFactory,
        \Magento\Framework\Intl\DateTimeFactory $dateTimeFactory
    ) {
        $this->configIdp = $configIdp;
        $this->issuerBuilder = $issuerBuilder;
        $this->subjectBuilder = $subjectBuilder;
        $this->conditionsBuilder = $conditionsBuilder;
        $this->authnStatementBuilder = $authnStatementBuilder;
        $this->attributeStatementBuilder = $attributeStatementBuilder;
        $this->signatureWriterFactory = $signatureWriterFactory;
        $this->dateTimeFactory = $dateTimeFactory;
    }

    /**
     * @param \LightSaml\Model\Protocol\AuthnRequest $authnRequest
     * @return \LightSaml\Model\Assertion\Assertion
     */
    public function build($authnRequest)
    {
        $assertion = new \LightSaml\Model\Assertion\Assertion();
        $assertion->setId(\LightSaml\Helper::generateID());
        $assertion->setIssueInstant($this->dateTimeFactory->create());
        $assertion->setIssuer($this->issuerBuilder->build());
        $assertion->setSubject($this->subjectBuilder->build($authnRequest));
        $assertion->setConditions($this->conditionsBuilder->build($authnRequest));
        $assertion->addItem($this->attributeStatementBuilder->build());
        $assertion->addItem($this->authnStatementBuilder->build());

        if ($this->configIdp->isAssertionSigned()) {
            $assertion->setSignature($this->signatureWriterFactory->create());
        }
        return $assertion;
    }
}
