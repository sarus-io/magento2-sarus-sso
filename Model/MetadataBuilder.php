<?php
/**
 * Copyright © Sarus, LLC. All rights reserved.
 */

namespace Sarus\SsoIdp\Model;

class MetadataBuilder
{
    /**
     * @var \Sarus\SsoIdp\Model\Config\IdentityProvider
     */
    private $configIdp;

    /**
     * @var \Sarus\SsoIdp\Model\Metadata\IdpSsoBuilder
     */
    private $idpSsoBuilder;

    /**
     * @var \Sarus\SsoIdp\Model\Metadata\ContactPersonsBuilder
     */
    private $contactPersonsBuilder;

    /**
     * @var \Sarus\SsoIdp\Model\SignatureWriterFactory
     */
    private $signatureWriterFactory;

    /**
     * @param \Sarus\SsoIdp\Model\Config\IdentityProvider $configIdp
     * @param \Sarus\SsoIdp\Model\Metadata\IdpSsoBuilder $idpSsoBuilder
     * @param \Sarus\SsoIdp\Model\Metadata\ContactPersonsBuilder $contactPersonsBuilder
     * @param \Sarus\SsoIdp\Model\SignatureWriterFactory $signatureWriterFactory
     */
    public function __construct(
        \Sarus\SsoIdp\Model\Config\IdentityProvider $configIdp,
        \Sarus\SsoIdp\Model\Metadata\IdpSsoBuilder $idpSsoBuilder,
        \Sarus\SsoIdp\Model\Metadata\ContactPersonsBuilder $contactPersonsBuilder,
        \Sarus\SsoIdp\Model\SignatureWriterFactory $signatureWriterFactory
    ) {
        $this->configIdp = $configIdp;
        $this->idpSsoBuilder = $idpSsoBuilder;
        $this->contactPersonsBuilder = $contactPersonsBuilder;
        $this->signatureWriterFactory = $signatureWriterFactory;
    }

    /**
     * @return \LightSaml\Model\Metadata\EntityDescriptor
     */
    public function build()
    {
        $metadataDescriptor = new \LightSaml\Model\Metadata\EntityDescriptor();

        $metadataDescriptor->setEntityID($this->configIdp->getEntityId());

        $metadataDescriptor->addItem($this->idpSsoBuilder->build());

        foreach ($this->contactPersonsBuilder->build() as $contactPerson) {
            $metadataDescriptor->addContactPerson($contactPerson);
        }

        if ($this->configIdp->isMetadataSigned()) {
            $metadataDescriptor->setSignature($this->signatureWriterFactory->create());
        }

        return $metadataDescriptor;
    }
}
