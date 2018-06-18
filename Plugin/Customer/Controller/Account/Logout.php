<?php
/**
 * Copyright © Sarus, LLC. All rights reserved.
 */

namespace Sarus\SsoIdp\Plugin\Customer\Controller\Account;

use Sarus\SsoIdp\Controller\Idp\Logout as SsoIdpControllerLogout;

class Logout
{
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
     * @var \Sarus\SsoIdp\Model\Logout\RequestBuilder
     */
    private $logoutRequestBuilder;

    /**
     * @var \Sarus\SsoIdp\Model\MessageTransporter
     */
    private $messageTransporter;

    /**
     * @param \Sarus\SsoIdp\Model\Config\IdentityProvider $configIdp
     * @param \Sarus\SsoIdp\Model\Config\ServiceProvider $configSp
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Sarus\SsoIdp\Model\Logout\RequestBuilder $logoutRequestBuilder
     * @param \Sarus\SsoIdp\Model\MessageTransporter $messageTransporter
     */
    public function __construct(
        \Sarus\SsoIdp\Model\Config\IdentityProvider $configIdp,
        \Sarus\SsoIdp\Model\Config\ServiceProvider $configSp,
        \Magento\Customer\Model\Session $customerSession,
        \Sarus\SsoIdp\Model\Logout\RequestBuilder $logoutRequestBuilder,
        \Sarus\SsoIdp\Model\MessageTransporter $messageTransporter
    ) {
        $this->configIdp = $configIdp;
        $this->configSp = $configSp;
        $this->customerSession = $customerSession;
        $this->logoutRequestBuilder = $logoutRequestBuilder;
        $this->messageTransporter = $messageTransporter;
    }

    /**
     * @param \Magento\Customer\Controller\Account\Logout $subject
     * @param \Closure $proceed
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    public function aroundExecute($subject, \Closure $proceed)
    {
        if (!$this->configIdp->isEnabledSlo()) {
            return $proceed();
        }

        $logoutResponse = $this->logoutRequestBuilder->build();
        $this->customerSession->setData(SsoIdpControllerLogout::LOGOUT_REQUEST_ID, $logoutResponse->getID());
        $this->messageTransporter->send($logoutResponse, $this->configSp->getSingleLogoutBinding());
        exit;
    }
}
