<?php
/**
 * Copyright © Sarus, LLC. All rights reserved.
 */

namespace Sarus\SsoIdp\Controller\Idp;

use Magento\Framework\App\State as AppState;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NotFoundException;

class Logout extends \Magento\Framework\App\Action\Action
{
    const LOGOUT_REQUEST_ID = 'logout_request_id';

    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    /**
     * @var \Sarus\SsoIdp\Model\Config\IdentityProvider
     */
    private $configIdp;

    /**
     * @var \Sarus\SsoIdp\Model\Config\ServiceProvider
     */
    private $configSp;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private $cookieMetadataManager;

    /**
     * @var \Sarus\SsoIdp\Model\Logout\RequestValidator
     */
    private $logoutRequestValidator;

    /**
     * @var \Sarus\SsoIdp\Model\Logout\ResponseValidator
     */
    private $logoutResponseValidator;

    /**
     * @var \Sarus\SsoIdp\Model\Logout\ResponseBuilder
     */
    private $logoutResponseBuilder;

    /**
     * @var \Sarus\SsoIdp\Model\MessageTransporter
     */
    private $messageTransporter;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\State $appState
     * @param \Sarus\SsoIdp\Model\Config\IdentityProvider $configIdp
     * @param \Sarus\SsoIdp\Model\Config\ServiceProvider $configSp
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\Stdlib\Cookie\PhpCookieManager $cookieMetadataManager
     * @param \Sarus\SsoIdp\Model\Logout\RequestValidator $logoutRequestValidator
     * @param \Sarus\SsoIdp\Model\Logout\ResponseValidator $logoutResponseValidator
     * @param \Sarus\SsoIdp\Model\Logout\ResponseBuilder $logoutResponseBuilder
     * @param \Sarus\SsoIdp\Model\MessageTransporter $messageTransporter
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\State $appState,
        \Sarus\SsoIdp\Model\Config\IdentityProvider $configIdp,
        \Sarus\SsoIdp\Model\Config\ServiceProvider $configSp,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\Stdlib\Cookie\PhpCookieManager $cookieMetadataManager,
        \Sarus\SsoIdp\Model\Logout\RequestValidator $logoutRequestValidator,
        \Sarus\SsoIdp\Model\Logout\ResponseValidator $logoutResponseValidator,
        \Sarus\SsoIdp\Model\Logout\ResponseBuilder $logoutResponseBuilder,
        \Sarus\SsoIdp\Model\MessageTransporter $messageTransporter,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->appState = $appState;
        $this->configIdp = $configIdp;
        $this->configSp = $configSp;
        $this->customerSession = $customerSession;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->cookieMetadataManager = $cookieMetadataManager;
        $this->logoutRequestValidator = $logoutRequestValidator;
        $this->logoutResponseValidator = $logoutResponseValidator;
        $this->logoutResponseBuilder = $logoutResponseBuilder;
        $this->messageTransporter = $messageTransporter;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        if (!$this->configIdp->isEnabledSlo()) {
            throw new NotFoundException(__('Page not found.'));
        }

        try {
            $messageContext = $this->messageTransporter->buildMessageContextFromRequest();
        } catch (\Exception $e) {
            $this->processFail($e);
        }

        if ($messageContext->asLogoutResponse()) {
            return $this->processLogoutResponse($messageContext->asLogoutResponse());
        }

        if (!$messageContext->asLogoutRequest()) {
            if ($this->appState->getMode() === AppState::MODE_DEVELOPER) {
                throw new \InvalidArgumentException('Missing SAMLRequest or SAMLResponse parameter.');
            }
            throw new NotFoundException(__('Page not found.'));
        }

        $this->processLogoutRequest($messageContext->asLogoutRequest());
    }

    /**
     * @param \LightSaml\Model\Protocol\LogoutResponse $logoutResponse
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \Exception
     */
    private function processLogoutResponse($logoutResponse)
    {
        try {
            $requestId = $this->customerSession->getData(self::LOGOUT_REQUEST_ID);
            $this->logoutResponseValidator->validate($logoutResponse, $requestId);
        } catch (\Exception $e) {
            $this->processFail($e);
        }

        $this->logoutCustomer();

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('customer/account/logoutSuccess');
    }

    /**
     * @param $logoutRequest
     * @return void
     * @throws \Exception
     *
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    private function processLogoutRequest($logoutRequest)
    {
        try {
            $customer = $this->customerSession->isLoggedIn() ? $this->customerSession->getCustomer() : null;
            $this->logoutRequestValidator->validate($logoutRequest, $customer);
        } catch (\Exception $e) {
            $this->processFail($e);
        }

        $this->logoutCustomer();

        $logoutResponse = $this->logoutResponseBuilder->build($logoutRequest);
        $this->messageTransporter->send($logoutResponse, $this->configSp->getSingleLogoutBinding());
        exit; // TODO
    }

    /**
     * @return void
     */
    private function logoutCustomer()
    {
        $this->customerSession->logout();
        if ($this->cookieMetadataManager->getCookie('mage-cache-sessid')) {
            $metadata = $this->cookieMetadataFactory->createCookieMetadata();
            $metadata->setPath('/');
            $this->cookieMetadataManager->deleteCookie('mage-cache-sessid', $metadata);
        }
    }

    /**
     * @param \Exception $exception
     * @return void
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    private function processFail($exception)
    {
        if ($this->appState->getMode() === AppState::MODE_DEVELOPER) {
            throw $exception;
        }

        $this->logger->critical($exception);

        throw new NotFoundException(__('Page not found.'));
    }
}
