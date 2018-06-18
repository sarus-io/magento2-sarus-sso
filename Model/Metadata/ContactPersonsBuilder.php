<?php
/**
 * Copyright © Sarus, LLC. All rights reserved.
 */

namespace Sarus\SsoIdp\Model\Metadata;

use LightSaml\Model\Metadata\ContactPerson;

class ContactPersonsBuilder
{
    /**
     * @var \Sarus\SsoIdp\Model\Config\IdentityProvider
     */
    private $configIdp;

    /**
     * @param \Sarus\SsoIdp\Model\Config\IdentityProvider $configIdp
     */
    public function __construct(
        \Sarus\SsoIdp\Model\Config\IdentityProvider $configIdp
    ) {
        $this->configIdp = $configIdp;
    }

    /**
     * @return \LightSaml\Model\Metadata\ContactPerson[]
     */
    public function build()
    {
        $contactPerson = [];

        if ($this->configIdp->getTechnicalContactGivenName() || $this->configIdp->getTechnicalContactEmail()) {
            $contactPerson[] = $this->buildTechnicalContact();
        }

        if ($this->configIdp->getSupportContactGivenName() || $this->configIdp->getSupportContactEmail()) {
            $contactPerson[] = $this->buildSupportContact();
        }
        return $contactPerson;
    }

    /**
     * @return \LightSaml\Model\Metadata\ContactPerson
     */
    private function buildTechnicalContact()
    {
        $contactPerson = new \LightSaml\Model\Metadata\ContactPerson();
        $contactPerson->setContactType(ContactPerson::TYPE_TECHNICAL);

        if ($this->configIdp->getTechnicalContactGivenName()) {
            $contactPerson->setGivenName($this->configIdp->getTechnicalContactGivenName());
        }

        if ($this->configIdp->getTechnicalContactEmail()) {
            $contactPerson->setEmailAddress($this->configIdp->getTechnicalContactEmail());
        }

        return $contactPerson;
    }

    /**
     * @return \LightSaml\Model\Metadata\ContactPerson
     */
    private function buildSupportContact()
    {
        $contactPerson = new \LightSaml\Model\Metadata\ContactPerson();
        $contactPerson->setContactType(ContactPerson::TYPE_SUPPORT);

        if ($this->configIdp->getSupportContactGivenName()) {
            $contactPerson->setGivenName($this->configIdp->getSupportContactGivenName());
        }

        if ($this->configIdp->getSupportContactEmail()) {
            $contactPerson->setEmailAddress($this->configIdp->getSupportContactEmail());
        }

        return $contactPerson;
    }
}
