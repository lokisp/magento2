<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Account;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\View\Result\PageFactory;

class ResetPasswordPost extends \Magento\Customer\Controller\Account
{
    /** @var AccountManagementInterface */
    protected $accountManagement;

    /** @var CustomerRepositoryInterface */
    protected $customerRepository;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param RedirectFactory $resultRedirectFactory
     * @param PageFactory $resultPageFactory
     * @param AccountManagementInterface $accountManagement
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        RedirectFactory $resultRedirectFactory,
        PageFactory $resultPageFactory,
        AccountManagementInterface $accountManagement,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->accountManagement = $accountManagement;
        $this->customerRepository = $customerRepository;
        parent::__construct($context, $customerSession, $resultRedirectFactory, $resultPageFactory);
    }

    /**
     * Reset forgotten password
     *
     * Used to handle data received from reset forgotten password form
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resetPasswordToken = (string)$this->getRequest()->getQuery('token');
        $customerId = (int)$this->getRequest()->getQuery('id');
        $password = (string)$this->getRequest()->getPost('password');
        $passwordConfirmation = (string)$this->getRequest()->getPost('password_confirmation');

        if ($password !== $passwordConfirmation) {
            $this->messageManager->addError(__("New Password and Confirm New Password values didn't match."));
            $resultRedirect->setPath('*/*/createPassword', ['id' => $customerId, 'token' => $resetPasswordToken]);
            return $resultRedirect;
        }
        if (iconv_strlen($password) <= 0) {
            $this->messageManager->addError(__('New password field cannot be empty.'));
            $resultRedirect->setPath('*/*/createPassword', ['id' => $customerId, 'token' => $resetPasswordToken]);
            return $resultRedirect;
        }

        try {
            $customerEmail = $this->customerRepository->getById($customerId)->getEmail();
            $this->accountManagement->resetPassword($customerEmail, $resetPasswordToken, $password);
            $this->messageManager->addSuccess(__('Your password has been updated.'));
            $resultRedirect->setPath('*/*/login');
            return $resultRedirect;
        } catch (\Exception $exception) {
            $this->messageManager->addError(__('There was an error saving the new password.'));
            $resultRedirect->setPath('*/*/createPassword', ['id' => $customerId, 'token' => $resetPasswordToken]);
            return $resultRedirect;
        }
    }
}
