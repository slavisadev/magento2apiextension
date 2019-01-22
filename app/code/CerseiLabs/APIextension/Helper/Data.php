<?php

namespace CerseiLabs\APIextension\Helper;

use Magento\Customer\Model\Address;
use Magento\Framework\Api\AttributeValue;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(\Magento\Framework\App\Helper\Context $context)
    {
        parent::__construct($context);
    }

    /**
     * Curl data and return body
     *
     * @param      $data
     * @param      $url
     * @param null $token
     *
     * @return \stdClass $output
     */
    public function proxy($data, $url, $token = null)
    {
        $output = new \stdClass();
        $ch = curl_init();
        $body = json_encode($data);

        if (!is_null($token)) {
            $url .= "?token=$token";
        }

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: application/json',
            'Content-Type: application/json',
            'Content-Length: ' . strlen($body)
        ));

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60); // 2 minutes to connect
        //curl_setopt($ch, CURLOPT_TIMEOUT, 60 * 4); // 8 minutes to fetch the response
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        // execute
        $response = curl_exec($ch);

        $output->status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // handle response
        $result = explode("\r\n\r\n", $response, 2);
        if (count($result) == 2) {
            $output->header = $result[0];
            $output->body = $result[1];
        } else {
            $output->body = 'Unexpected response';
        }

        return $output;
    }

    /**
     * @param $what
     */
    public function log($what)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->debug(json_encode($what));
    }

    /**
     * @param $baseUrl
     * @param $collectionFactory
     * @param $eventCode
     * @param $entity
     * @param $object
     * @param $subEntities
     */
    public function runWebHook($baseUrl, $collectionFactory, $eventCode, $entity, $object, $subEntities = null)
    {
        try {
            $collection = $collectionFactory->load();

            foreach ($collection as $webhook) {

                $webhookObject = $webhook->getData();
                if ($eventCode == $webhookObject['event_code']) {
                    $eventData = $this->transform($object, $entity);
                    $eventData['data'] = 'some data';
                    $eventData['webhook'] = 'some data';
                    $eventData['info'] = array(
                        'base_url' => $baseUrl,
                        'server_ip' => $_SERVER['SERVER_ADDR'],
                        'time' => time()
                    );
                    $this->proxy($eventData, $webhookObject['callback_url'], $webhookObject['token']);
                }
            }

        } catch (\Exception $e) {
            $this->log($e->getMessage());
        }
    }

    /**
     * @param $object
     * @param $entity
     *
     * @return array
     */
    public function transform($object, $entity)
    {
        switch ($entity) {
            case 'product' :
                $eventData = $this->transformProduct($object->getProduct());
                break;
            case 'category' :
                $eventData = $this->transformCategory($object->getCategory());
                break;
            case 'customer' :
                $eventData = $this->transformCustomer($object->getData('customer_data_object'));
                break;
            case 'order' :
                $eventData = $this->transformOrder($object->getOrder());
                break;
            default:
                $eventData = [];
                break;
        }

        return $eventData;
    }

    /**
     * @param $object
     *
     * @return mixed
     */
    public function transformOrder($object)
    {
        $data = $object->getData();

        return $data;
    }

    /**
     * @param $object
     *
     * @return mixed
     */
    public function transformCategory($object)
    {
        $data = $object->getData();
        $result = array();
        foreach ($data as $item => $value) {
            if (!is_array($value)) {
                $result[$item] = $value;
            } else {
                $result[$item] = implode(',', $value);
            }
        }
        $result['name'] = $object->getName();
        $result['description'] = $object->getDescription();
        $result['children_count'] = $object->getChildrenCount();

        return $result;
    }

    /**
     * @param $data
     *
     * @return array
     */
    public function transformCustomer($data)
    {
        $result = array();

        $result['id'] = $data->getId();
        $result['email'] = $data->getEmail();
        $result['firstname'] = $data->getFirstname();
        $result['lastname'] = $data->getLastname();
        $result['gender'] = $data->getGender();
        $result['middlename'] = $data->getMiddlename();
        $result['prefix'] = $data->getPrefix();
        $result['suffix'] = $data->getSuffix();
        $result['dob'] = $data->getDob();
        $result['taxvat'] = $data->getTaxvat();
        $result['store_id'] = $data->getStoreId();
        $result['website_id'] = $data->getWebsiteId();
        $result['group_id'] = $data->getGroupId();

        $result['addresses'] = array();

        $allAddresses = $data->getAddresses();

        foreach ($allAddresses as $address => $addressObject) {

            /** @var Address $addressObject */
            $result['addresses'][] = array(
                'city' => $addressObject->getCity(),
                'firstname' => $addressObject->getFirstname(),
                'lastname' => $addressObject->getLastname(),
                'region' => $addressObject->getRegion(),
                'postcode' => $addressObject->getPostcode(),
                'country_id' => $addressObject->getCountryId(),
                'telephone' => $addressObject->getTelephone(),
                'company' => $addressObject->getCompany(),
                'prefix' => $addressObject->getPrefix(),
                'middlename' => $addressObject->getMiddlename(),
                'fax' => $addressObject->getFax(),
                'street' => $addressObject->getStreet()
            );
        }

        return $result;
    }

    /**
     * @param $object
     *
     * @return array
     */
    public function transformProduct($object)
    {
        $result = array(
            'id' => $object->getId(),
            'entity_id' => $object->getId(),
            'attribute_set_id' => $object->getAttributeSetId(),
            'sku' => $object->getSku(),
            'name' => $object->getName(),
            'type_id' => $object->getTypeId(),
            'visibility' => $object->getVisibility(),
            'price' => $object->getPrice(),
            'status' => $object->getStatus(),
            'weight' => $object->getWeight(),
            'tax_class_id' => $object->getTaxClassId(),
            'description' => $object->getDescription(),
            'short_description' => $object->getShortDescription()
        );

        $media = $object->getMediaGalleryImages();

        if (!is_null($media)) {
            $result['media'] = array();

            foreach ($media->getItems() as $item) {
                $result['media'][] =
                    array(
                        'id' => $item->getId(),
                        'label' => $item->getLabel(),
                        'position' => $item->getPosition(),
                        'disabled' => $item->getIsDisabled(),
                        'url' => $item->getUrl(),
                        'types' => $item->getTypes(),
                    );
            }
        }

        $stockData = $object->getStockData();

        $result['stock_data'] = array(
            'is_in_stock' => (int)$stockData['is_in_stock'],
            'qty' => $stockData['qty'],
            'backorders' => $stockData['backorders'],
            'stock_id' => $stockData['stock_id'],
            'enable_qty_increments' => $stockData['enable_qty_increments'],
            'is_qty_decimal' => $stockData['is_qty_decimal'],
            'manage_stock' => $stockData['manage_stock'],
            'max_sale_qty' => $stockData['max_sale_qty'],
            'min_qty' => $stockData['min_qty'],
            'notify_stock_qty' => $stockData['notify_stock_qty'],
            'min_sale_qty' => $stockData['min_sale_qty'],
            'product_id' => $stockData['product_id'],
            'item_id' => $stockData['item_id']
        );

        $result['custom_attributes'] = array();

        foreach ($object->getCustomAttributes() as $custom_attribute => $attribute) {
            /** @var AttributeValue $attribute */
            if (!is_array($attribute->getValue()))
                $result['custom_attributes'][] = array(
                    'attribute_code' => $attribute->getAttributeCode(),
                    'value' => $attribute->getValue()
                );
        }

        return $result;
    }
}
