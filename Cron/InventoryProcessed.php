<?php
/**
 *
 * @category  InventoryProcessed
 * @package   SixtySeven_InventoryProcessed
 */
namespace SixtySeven\InventoryProcessed\Cron;

class InventoryProcessed
{
   /**
     * [$_logger description]
     * @var [type]
     */
    protected $_logger;
    /**
     * [$_dir description]
     * @var [type]
     */
    protected $_dir;
    /**
     * [$_storeManager description]
     * @var [type]
     */
    protected $_storeManager;

    /**
     * [$stockRegistry description]
     * @var [type]
     */
    protected $stockRegistry;

    private $productRepository;

    /**
     * @var \Magento\Indexer\Model\IndexerFactory
     */
    protected $_indexerFactory;
    /**
     * @var \Magento\Indexer\Model\Indexer\CollectionFactory
     */
    protected $_indexerCollectionFactory;

    protected $_productCollectionFactory;

    protected $importError;

    protected $importSuccess;

    public function __construct(\Magento\Indexer\Model\IndexerFactory $indexerFactory,
        \Magento\Indexer\Model\Indexer\CollectionFactory $indexerCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \SixtySeven\InventoryProcessed\Logger\Logger $imporLogger,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_logger                   = $logger;
        $this->stockRegistry             = $stockRegistry;
        $this->productRepository         = $productRepository;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_storeManager             = $storeManager;
        $this->_dir                      = $dir;
        $this->imporLogger               = $imporLogger;
        $this->_indexerFactory           = $indexerFactory;
        $this->_indexerCollectionFactory = $indexerCollectionFactory;
    }

    public function execute()
    {  
        
        try {
            foreach ($this->getInventoryData() as $data) {

                try {
                    $this->updateQty(trim($data['0']), trim($data['1']));
                } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {

                }

            }

            $success = true;

        } catch (\Exception $e) {

            $success = false;

        } finally {

            if ($success) {

                 $Csvfl=$this->_dir->getPath('var') . "/imagine/InventoryUpdateProcessed.csv";
                 $NewNm=$this->_dir->getPath('var') . "/imagine/archive/InventoryUpdateProcessed".preg_replace('-\W-','',date('m-d-Y H:i:s A')).".csv";

                rename($Csvfl, $NewNm);
            }

        }

    }


    public function updateQty($sku, $Qty)
    {
        $stockItem = $this->stockRegistry->getStockItemBySku($sku);

        $stockItem->setQty($Qty);
        if ($Qty > 0) {
            $stockItem->setIsInStock('1');
        }

        $this->stockRegistry->updateStockItemBySku($sku, $stockItem);
    }

    public function getInventoryData()
    {
        $filecsv   = $this->_dir->getPath('var') . "/imagine/InventoryUpdateProcessed.csv";
        $arrResult = array();
        $headers   = false;

        $handle = fopen($filecsv, "r");
        if (empty($handle) === false) {
            while (($data = fgetcsv($handle, ",")) !== false) {


                if (!$headers) {
                    $headers[] = $data;
                } else {
                    $arrResult[$data['0']] = $data;
                }
            }
            fclose($handle);
        }

        return $arrResult;

    }

}
