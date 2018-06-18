<?php
/**
 * Copyright © Sarus, LLC. All rights reserved.
 */

namespace Sarus\SsoIdp\Model\Assertion;

use LightSaml\SamlConstants;

class SubjectBuilder
{
    /**
     * @var \Sarus\SsoIdp\Model\Assertion\NameIdBuilder
     */
    private $assertionNameIdBuilder;

    /**
     * @param \Sarus\SsoIdp\Model\Assertion\NameIdBuilder $assertionNameIdBuilder
     */
    public function __construct(
        \Sarus\SsoIdp\Model\Assertion\NameIdBuilder $assertionNameIdBuilder
    ) {
        $this->assertionNameIdBuilder = $assertionNameIdBuilder;
    }

    /**
     * @param \LightSaml\Model\Protocol\AuthnRequest $authnRequest
     * @return \LightSaml\Model\Assertion\Subject
     */
    public function build($authnRequest)
    {
        $subject = new \LightSaml\Model\Assertion\Subject();
        $subject->setNameID($this->assertionNameIdBuilder->build());
        $subject->addSubjectConfirmation($this->buildSubjectConfirmation($authnRequest));
        return $subject;
    }

    /**
     * @param \LightSaml\Model\Protocol\AuthnRequest $authnRequest
     * @return \LightSaml\Model\Assertion\SubjectConfirmation
     */
    private function buildSubjectConfirmation($authnRequest)
    {
        $subjectConfirmation = new \LightSaml\Model\Assertion\SubjectConfirmation();
        $subjectConfirmation->setMethod(SamlConstants::CONFIRMATION_METHOD_BEARER); // TODO
        $subjectConfirmation->setSubjectConfirmationData($this->buildSubjectConfirmationData($authnRequest));
        return $subjectConfirmation;
    }

    /**
     * @param \LightSaml\Model\Protocol\AuthnRequest $authnRequest
     * @return \LightSaml\Model\Assertion\SubjectConfirmationData
     */
    private function buildSubjectConfirmationData($authnRequest)
    {
        $subjectConfirmationData = new \LightSaml\Model\Assertion\SubjectConfirmationData();
        $subjectConfirmationData->setInResponseTo($authnRequest->getId());
        $subjectConfirmationData->setNotOnOrAfter(new \DateTime('+1 MINUTE')); // TODO
        $subjectConfirmationData->setRecipient($authnRequest->getAssertionConsumerServiceURL());
        return $subjectConfirmationData;
    }
}
