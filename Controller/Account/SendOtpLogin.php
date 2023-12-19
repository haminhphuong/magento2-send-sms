<?php
namespace Ecommage\Sms\Controller\Account;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\ResultFactory;
use Ecommage\Sms\Helper\Data;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;

class SendOtpLogin extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Data
     */
    public $helper;
    /**
     * @var PageFactory
     */
    public $pageFactory;
    /**
     * @var JsonFactory
     */
    public $jsonFactory;

	/**
	 * @var ManagerInterface
	 */
	protected $messageManager;

    public function __construct(
        Context $context,
        Data $helper,
        PageFactory $pageFactory,
        JsonFactory $jsonFactory,
        ManagerInterface $messageManager
    ){
		$this->messageManager = $messageManager;
        $this->helper = $helper;
        $this->pageFactory = $pageFactory;
        $this->jsonFactory = $jsonFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\Result\Json&\Magento\Framework\Controller\ResultInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $mobileNumber = $this->getRequest()->getParam('login')['username'];
        if(substr($mobileNumber,0,1) != 0){
            $mobileNumber = '0'.$mobileNumber;
        }
        $result = $this->helper->sendLoginOTPCode($mobileNumber);
        $preUrl = $this->_request->getServer('HTTP_REFERER');
        $pos = strpos($preUrl, 'customer/account/login');
        if ($pos !== false) {
            $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $redirect->setUrl($this->helper->getBaseUrl().'sms/account/verifyotplogin?mobileNumber='.$mobileNumber);
			if (empty($result))
			{
				$url = $this->_redirect->getRefererUrl();
				$this->messageManager->addErrorMessage(__('Mobile number does not exist.'));
				$redirect->setUrl($url);
			}
            return $redirect;
        } else {
            $resultJson = $this->jsonFactory->create();
            $otpLoginHtml = $this->pageFactory->create()->getLayout()
                ->createBlock
                ('Ecommage\Sms\Block\VerifyOtpLogin',
                    'Ecommage_Sms::verify_otp_login',
                    [
                        'data' => [
                            'mobileNumber' => $mobileNumber
                        ]
                    ]
                )
                ->setTemplate('Ecommage_Sms::verify-otp-login.phtml')
                ->toHtml();
            $resultJson->setData(['success'=>"$result", 'mobileNumber'=>$mobileNumber,'otplogin'=>$otpLoginHtml]);
            return $resultJson;
        }
    }
}
