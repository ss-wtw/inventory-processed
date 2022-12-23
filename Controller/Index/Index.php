<?php
/**
 * 
 * @category  InventoryProcessed
 * @package   SixtySeven_InventoryProcessed
 */
namespace SixtySeven\InventoryProcessed\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;

	protected $_InventoryProcessed;

	protected $order;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\SixtySeven\InventoryProcessed\Cron\InventoryProcessed $InventoryProcessed,
		\Magento\Framework\View\Result\PageFactory $pageFactory)
	{
		$this->_pageFactory = $pageFactory;
		$this->_InventoryProcessed= $InventoryProcessed; 
		return parent::__construct($context);
	}

	public function execute()
	{

        $this->_InventoryProcessed->execute();
		die('check-inventory-controller-controller');
		//return $this->_pageFactory->create();
	}
}