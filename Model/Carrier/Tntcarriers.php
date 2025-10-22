<?php
declare(strict_types=1);

namespace Santi\Tntcarriers\Model\Carrier;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Shipping\Model\Tracking\Result\StatusFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class Tntcarriers extends AbstractCarrier implements CarrierInterface
{
    protected $_code = 'tntcarriers';
    protected $_isFixed = true;

    private ResultFactory $rateResultFactory;
    private MethodFactory $rateMethodFactory;
    private StatusFactory $trackStatusFactory; // prop mantiene el nombre “trackStatusFactory”
    private StoreManagerInterface $storeManager;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        StatusFactory $statusFactory,             // <-- renombrado
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
        $this->rateResultFactory  = $rateResultFactory;
        $this->rateMethodFactory  = $rateMethodFactory;
        $this->trackStatusFactory = $statusFactory; // <-- asignación
        $this->storeManager       = $storeManager;
    }

    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $result = $this->rateResultFactory->create();
        $method = $this->rateMethodFactory->create();

        $method->setCarrier($this->_code);
        $method->setCarrierTitle((string)$this->getConfigData('title'));
        $method->setMethod($this->_code);
        $method->setMethodTitle((string)$this->getConfigData('name'));

        $shippingCost = (float)$this->getConfigData('shipping_cost');
        $method->setPrice($shippingCost);
        $method->setCost($shippingCost);

        $result->append($method);
        return $result;
    }

    public function getAllowedMethods()
    {
        return [$this->_code => (string)$this->getConfigData('name')];
    }

    public function isTrackingAvailable(): bool
    {
        return true;
    }

    public function getTrackingInfo($trackingNumber)
    {
        $storeId = (string)$this->storeManager->getStore()->getId();

        switch ($storeId) {
            case '4': // IT
                $cons = 'GE' . $trackingNumber . 'WW';
                $link = 'https://www.tnt.it/tracking/getTrack.html?wt=1&consigNos=' . $cons;
                break;
            case '7': // FR
            case '11': // FR
            case '15': // FR
                $link = 'https://www.tnt.com/express/fr_fr/site/home/applications/tracking.html?searchType=ref&cons=' . $trackingNumber;
                break;
            case '2': // ES
            default:
                $link = 'https://www.tnt.com/express/es_es/site/herramientas-envio/seguimiento.html?searchType=ref&cons=' . $trackingNumber;
                break;
        }

        $status = $this->trackStatusFactory->create();
        $status->setCarrier($this->_code);
        $status->setCarrierTitle((string)$this->getConfigData('title'));
        $status->setTracking($trackingNumber);
        $status->setUrl($link);

        $status->setStatus(__('Ver seguimiento en TNT'));
        $status->setProgressdetail([[
            'deliverydate'     => '',
            'deliverytime'     => '',
            'deliverylocation' => '',
            'activity'         => __('Ver seguimiento en TNT')
        ]]);

        return $status;
    }
}
