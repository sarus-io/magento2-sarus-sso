<?php
/**
 * Copyright © Sarus, LLC. All rights reserved.
 */

namespace Sarus\SsoIdp\Plugin\Customer\Controller\Account;

class Login
{
    /**
     * @var \Sarus\SsoIdp\Model\Config\IdentityProvider
     */
    private $configIdp;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @param \Sarus\SsoIdp\Model\Config\IdentityProvider $configIdp
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Sarus\SsoIdp\Model\Config\IdentityProvider $configIdp,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->configIdp = $configIdp;
        $this->customerSession = $customerSession;
    }

    /**
     * @param \Magento\Customer\Controller\Account\Login $subject
     * @param \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\View\Result\Page $result
     * @return \Magento\Framework\Controller\ResultInterface
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute($subject, $result)
    {
        if ($result instanceof \Magento\Framework\Controller\Result\Redirect
            || !$this->configIdp->isEnabled()
            || strpos((string)$this->customerSession->getBeforeAuthUrl(), $this->configIdp->getSingleSingOnUrl()) !== 0
        ) {
            return $result;
        }

        $result->addHandle('sarus_sso_idp');

        return $result;
    }
}
