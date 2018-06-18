<?php
/**
 * Copyright © Sarus, LLC. All rights reserved.
 */

namespace Sarus\SsoIdp\Controller\Idp;

use Magento\Framework\App\State as AppState;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Customer\Model\Url as CustomerUrl;

class Signon extends \Magento\Framework\App\Action\Action
{
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
     * @var \Sarus\SsoIdp\Model\Authn\ResponseBuilder
     */
    private $authnResponseBuilder;

    /**
     * @var \Sarus\SsoIdp\Model\Authn\RequestValidator
     */
    private $authnRequestValidator;

    /**
     * @var \Sarus\SsoIdp\Model\MessageTransporter
     */
    private $messageTransporter;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    private $urlEncoder;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    private $dataFormKey;

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
     * @param \Sarus\SsoIdp\Model\Authn\ResponseBuilder $authnResponseBuilder
     * @param \Sarus\SsoIdp\Model\Authn\RequestValidator $authnRequestValidator
     * @param \Sarus\SsoIdp\Model\MessageTransporter $messageTransporter
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param \Magento\Framework\Data\Form\FormKey $dataFormKey
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\State $appState,
        \Sarus\SsoIdp\Model\Config\IdentityProvider $configIdp,
        \Sarus\SsoIdp\Model\Config\ServiceProvider $configSp,
        \Magento\Customer\Model\Session $customerSession,
        \Sarus\SsoIdp\Model\Authn\ResponseBuilder $authnResponseBuilder,
        \Sarus\SsoIdp\Model\Authn\RequestValidator $authnRequestValidator,
        \Sarus\SsoIdp\Model\MessageTransporter $messageTransporter,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Data\Form\FormKey $dataFormKey,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->appState = $appState;
        $this->configIdp = $configIdp;
        $this->configSp = $configSp;
        $this->customerSession = $customerSession;
        $this->authnResponseBuilder = $authnResponseBuilder;
        $this->authnRequestValidator = $authnRequestValidator;
        $this->messageTransporter = $messageTransporter;
        $this->urlEncoder = $urlEncoder;
        $this->dataFormKey = $dataFormKey;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->configIdp->isEnabled()
            || (!$this->customerSession->isLoggedIn() && empty($this->_request->getParam('SAMLRequest')))
            || (empty($this->getRequest()->getParam('authn_id')) && empty($this->_request->getParam('SAMLRequest')))
        ) {
            throw new NotFoundException(__('Page not found.'));
        }

        if (!$this->customerSession->authenticate()) {
            $authnRequest = $this->getAuthnRequest();
            $this->customerSession->setData($authnRequest->getID(), $authnRequest);

            $afterLoginUrl = $this->_url->getUrl(
                'sarus_sso_idp/idp/signon',
                ['authn_id' => $authnRequest->getID(), '_secure' => true, '_query' => []]
            );
            $referer = $this->urlEncoder->encode($afterLoginUrl);
            $loginUrl = $this->_url->getUrl(CustomerUrl::ROUTE_ACCOUNT_LOGIN, [CustomerUrl::REFERER_QUERY_PARAM_NAME => $referer]);
            $this->_response->setRedirect($loginUrl);

            $this->getActionFlag()->set('', 'no-dispatch', true);
        }

        return parent::dispatch($request);
    }

    /**
     * @param string|null $authnId
     * @return \LightSaml\Model\Protocol\AuthnRequest|null
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    private function getAuthnRequest($authnId = null)
    {
        $authnRequest = $authnId
            ? $this->customerSession->getData($authnId)
            : $this->messageTransporter->buildMessageContextFromRequest()->asAuthnRequest();

        if (!$authnRequest) {
            if ($this->appState->getMode() === AppState::MODE_DEVELOPER) {
                throw new \RuntimeException('No Authn Request.');
            }
            throw new NotFoundException(__('Page not found.'));
        }

        try {
            $this->authnRequestValidator->validate($authnRequest);
        } catch (\Exception $e) {
            $this->processFail($e);
        }

        return $authnRequest;
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\NotFoundException
     *
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    public function execute()
    {
        $authnId = $this->getRequest()->getParam('authn_id');
        $authnRequest = $this->getAuthnRequest($authnId);
        try {
            $authnResponse = $this->authnResponseBuilder->build($authnRequest);
            $this->messageTransporter->send($authnResponse, $this->configSp->getAssertionConsumerBinding());
            exit; // TODO
        } catch (\Exception $e) {
            $this->processFail($e);
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
