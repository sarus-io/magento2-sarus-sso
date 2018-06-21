<?php
/**
 * Copyright © Sarus, LLC. All rights reserved.
 */

namespace Sarus\SsoIdp\Controller\Adminhtml\Config;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;

class GenerateCertificate extends \Magento\Backend\App\Action
{
    const FULL_ACTION_NAME = 'sarus_sso_idp/config/generateCertificate';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Sarus\SsoIdp\Model\Config\CertificateGenerator
     */
    private $certificateGenerator;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Sarus\SsoIdp\Model\Config\CertificateGenerator $certificateGenerator
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Sarus\SsoIdp\Model\Config\CertificateGenerator $certificateGenerator,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->storeManager = $storeManager;
        $this->certificateGenerator = $certificateGenerator;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $website = $this->getRequest()->getParam('website', null);

        try {
            $websiteCode = $this->storeManager->getWebsite($website)->getCode();
            $certificates = $this->certificateGenerator->generate($websiteCode);

            $response = [
                'status' => 'success',
                'message' => __('Certificates are generated.'),
                'private_key' => $certificates['private_key'],
                'certificate' => $certificates['certificate']
            ];
        } catch (LocalizedException $e) {
            $response['status'] = 'fail';
            $response['message'] = $e->getMessage();
        } catch (\Exception $e) {
            $this->logger->critical($e);

            $response['status'] = 'fail';
            $response['message'] = __('Something went wrong, try again.');
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($response);
        return $resultJson;
    }
}
