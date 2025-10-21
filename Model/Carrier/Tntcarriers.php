<?php

namespace Santi\Tntcarriers\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;

/**
 * Custom shipping model
 */
class Tntcarriers extends AbstractCarrier implements CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = 'tntcarriers';

    /**
     * @var bool
     */
    protected $_isFixed = true;

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    private $rateResultFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    private $rateMethodFactory;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        array $data = []
    ) {
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);

        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
    }

    /**
     * Custom Shipping Rates Collector
     *
     * @param RateRequest $request
     * @return \Magento\Shipping\Model\Rate\Result|bool
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->rateResultFactory->create();

        /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
        $method = $this->rateMethodFactory->create();

        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod($this->_code);
        $method->setMethodTitle($this->getConfigData('name'));

        $shippingCost = (float)$this->getConfigData('shipping_cost');

        $method->setPrice($shippingCost);
        $method->setCost($shippingCost);

        $result->append($method);

        return $result;
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('name')];
    }
    public function getTrackingInfo($trackingnumber)
    {
        /** @var \Magento\Shipping\Model\Tracking\Result\Status $tracking */

        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();

        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');

        $storeId =  $storeManager->getStore()->getStoreId();

        switch ($storeId) {
            case "2":
                $link = "https://www.tnt.com/express/es_es/site/herramientas-envio/seguimiento.html?searchType=ref&cons=" . $referencia; //enlace directo
                break;
            case "4":
                $trackingnumber = "GE" . $trackingnumber . "WW";
                $link = "https://www.tnt.it/tracking/getTrack.html?wt=1&consigNos=" . $trackingnumber; //enlace directo
                break;
            case "7":
                $link = "https://www.tnt.com/express/fr_fr/site/home/applications/tracking.html?searchType=ref&cons=" . $referencia; //enlace directo
                break;
            case "15":
                $link = "https://www.tnt.com/express/fr_fr/site/home/applications/tracking.html?searchType=ref&cons=" . $referencia; //enlace directo
                break;
            case "11":
                $link = "https://www.tnt.com/express/fr_fr/site/home/applications/tracking.html?searchType=ref&cons=" . $referencia; //enlace directo
                break;
            default:
                $link = "https://www.tnt.com/express/es_es/site/herramientas-envio/seguimiento.html?searchType=ref&cons=" . $referencia; //enlace directo
        }

        $tracking = $this->trackStatusFactory->create();

        $title = $this->getConfigData('title');

        $tracking->setCarrier($this->_code); //your carrier code
        $tracking->setCarrierTitle($title);
        $tracking->setTracking($trackingnumber);
        $tracking->setUrl($link);
        //you may want to add the events coming from your api
        $trackEventsData[] =
            [
                'deliverydate' => 'date',
                'deliverytime' => 'time',
                'deliverylocation' => 'location',
                'activity' => 'activity'
            ];
        $tracking->setStatus(isset($trackEventsData[0]) ? $trackEventsData[0]['activity'] : '');
        $tracking->setProgressdetail($trackEventsData);

        return $tracking;
    }
}
