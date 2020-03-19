<?php

namespace HANDS\Brandedsms\Observer;

use Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Event\Observer       as Observer;
use \Magento\Framework\View\Element\Context as Context;
use \HANDS\Brandedsms\Helper\Data                 as Helper;

/**
 * Customer login observer
 */
class Registration implements ObserverInterface
{
    /**
     * Message manager
     *
     * @var \Magento\Framework\Message\ManagerInterface
     */
    const AJAX_PARAM_NAME = 'infscroll';
    /**
     *
     */
    const AJAX_HANDLE_NAME = 'infscroll_ajax_request';

    /**
     * Https request
     *
     * @var \Zend\Http\Request
     */
    private $request;

    /**
     * Layout Interface
     * @var \Magento\Framework\View\LayoutInterface
     */
    private $layout;

    /**
     * Cache
     * @var $_cache
     */
    private $cache;

    /**
     * Helper for BrandedsmsSMS Module
     * @var \HANDS\Brandedsms\Helper\Data
     */
    private $helper;

    /**
     * Message Manager
     * @var $messageManager
     */
    private $messageManager;

    /**
     * Username
     * @var $username
     */
    private $username;

    /**
     * Password
     * @var $password
     */
    private $password;

    /**
     * Sender ID
     * @var $senderId
     */
    private $senderId;

    /**
     * Destination
     * @var $destination
     */
    private $destination;

    /**
     * Message
     * @var $message
     */
    private $message;

    /**
     * Whether Enabled or not
     * @var $enabled
     */
    private $enabled;

    /**
     * Constructor
     * @param Context $context
     * @param Helper $helper _helper
     */
    public function __construct(
        Context $context,
        Helper $helper
    ) {
        $this->_helper  = $helper;
        $this->_request = $context->getRequest();
        $this->_layout  = $context->getLayout();
    }

    /**
     * The execute class
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /**
         * Getting Module Configuration from admin panel
         */

        //Getting Username
        $this->username = $this->_helper->getBrandedsmsUsername();

        //Getting Password
        $this->password = $this->_helper->getBrandedsmsPassword();
		
        //Getting Sender ID
        $this->senderId         = $this->_helper->getCustomerId();

        /**
         * Verification of API Account
         */

        //Verification of API
        $verificationResult = $this->_helper->verifyApi($this->username, $this->password);
        if ($verificationResult == true) {
            //Getting Order Details
            $event = $observer->getEvent();
            $customer = [
                'id'=>$event->getCustomer()->getId(),
                'createdAt'=>$event->getCustomer()->getCreatedAt(),
                'email'=>$event->getCustomer()->getEmail(),
                'firstName'=>$event->getCustomer()->getFirstname(),
                'lastName'=>$event->getCustomer()->getLastname()
            ];
            //Sending SMS to Admin
            if ($this->_helper->isAdminSmsIsEnabled() == 1) {
                $this->destination = $this->_helper->getAdminSenderId();
                $this->message = $this->_helper->getAdminSmsForRegister();
                $keywords = ['{customer_id}','{created_at}','{email}','{firstname}','{lastname}'];
                $this->message = str_replace($keywords, $customer, $this->message);
                $this->_helper->sendSms(
                    $this->username,
                    $this->password,
                    $this->senderId,
                    $this->destination,
                    $this->message
                );
            }
        }
    }
}
