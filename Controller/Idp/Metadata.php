<?php
/**
 * Copyright © Sarus, LLC. All rights reserved.
 */

namespace Sarus\SsoIdp\Controller\Idp;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;

class Metadata extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Sarus\SsoIdp\Model\Config\IdentityProvider
     */
    private $configIdp;

    /**
     * @var \Sarus\SsoIdp\Model\MetadataBuilder
     */
    private $metadataBuilder;

    /**
     * @var \Sarus\SsoIdp\Model\Serializer
     */
    private $serializer;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Sarus\SsoIdp\Model\Config\IdentityProvider $configIdp
     * @param \Sarus\SsoIdp\Model\MetadataBuilder $metadataBuilder
     * @param \Sarus\SsoIdp\Model\Serializer $serializer
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Sarus\SsoIdp\Model\Config\IdentityProvider $configIdp,
        \Sarus\SsoIdp\Model\MetadataBuilder $metadataBuilder,
        \Sarus\SsoIdp\Model\Serializer $serializer
    ) {
        $this->configIdp = $configIdp;
        $this->metadataBuilder = $metadataBuilder;
        $this->serializer = $serializer;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        if (!$this->configIdp->isEnabled()) {
            /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
            $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
            return $resultForward->forward('noroute');
        }

        $metadataDescriptor = $this->metadataBuilder->build();
        $metadataXml = $this->serializer->toXml($metadataDescriptor);

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $resultRaw->setHeader('Content-Type', 'application/xml');
        return $resultRaw->setContents($metadataXml);
    }
}
