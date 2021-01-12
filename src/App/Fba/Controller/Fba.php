<?php

namespace Be\Mf\App\Fba\Controller;


use Be\F\Plugin\Form\Item\FormItemSelect;
use Be\Mf\Be;
use Be\F\Cache;
use Be\F\Request;

/**
 * Class Fba
 * @package App\System\Controller
 *
 * @BeMenuGroup("FBA", icon="el-icon-fa fa-user", ordering="10")
 * @BePermissionGroup("FBA", ordering="10")
 */
class Fba
{

    private $fbaAccounts = null;
    private $apiKeyValues = null;

    public function __construct()
    {
        $this->fbaAccounts = $this->getFbaAccounts();
        $this->apiKeyValues = [

            // --------------------------------------------------------------------------------------------------------- 亚马逊物流销售报告
            'RequestReport_GET_FBA_FULFILLMENT_CUSTOMER_SHIPMENT_SALES_DATA_' => '请求报告 - 亚马逊物流买家货件销售报告（_GET_FBA_FULFILLMENT_CUSTOMER_SHIPMENT_SALES_DATA_）',


            // --------------------------------------------------------------------------------------------------------- 亚马逊物流库存报告
            'RequestReport_GET_AFN_INVENTORY_DATA_' => '1 亚马逊物流库存报告（_GET_AFN_INVENTORY_DATA_）',
            'RequestReport_GET_FBA_FULFILLMENT_CURRENT_INVENTORY_DATA_' => '2 亚马逊物流每日库存历史报告（_GET_FBA_FULFILLMENT_CURRENT_INVENTORY_DATA_）',
            'RequestReport_GET_FBA_FULFILLMENT_MONTHLY_INVENTORY_DATA_' => '3 亚马逊物流每月库存历史报告（_GET_FBA_FULFILLMENT_MONTHLY_INVENTORY_DATA_）',
            'RequestReport_GET_FBA_FULFILLMENT_INVENTORY_RECEIPTS_DATA_' => '4 亚马逊物流已收到库存报告（_GET_FBA_FULFILLMENT_INVENTORY_RECEIPTS_DATA_）',
            'RequestReport_GET_FBA_FULFILLMENT_INVENTORY_SUMMARY_DATA_' => '5 亚马逊物流库存事件详情报告（_GET_FBA_FULFILLMENT_INVENTORY_SUMMARY_DATA_）',
            'RequestReport_GET_FBA_FULFILLMENT_INVENTORY_ADJUSTMENTS_DATA_' => '6 亚马逊物流库存盘点报告（_GET_FBA_FULFILLMENT_INVENTORY_ADJUSTMENTS_DATA_）',
            'RequestReport_GET_FBA_FULFILLMENT_INVENTORY_HEALTH_DATA_' => '7 亚马逊物流库存状况报告（_GET_FBA_FULFILLMENT_INVENTORY_HEALTH_DATA_）',
            'RequestReport_GET_FBA_MYI_UNSUPPRESSED_INVENTORY_DATA_' => '8 亚马逊物流管理库存（_GET_FBA_MYI_UNSUPPRESSED_INVENTORY_DATA_）',
            'RequestReport_GET_FBA_MYI_ALL_INVENTORY_DATA_' => '9 亚马逊物流管理库存 - 存档（_GET_FBA_MYI_ALL_INVENTORY_DATA_）',
            'RequestReport_GET_FBA_FULFILLMENT_INBOUND_NONCOMPLIANCE_DATA_' => '10 亚马逊物流入库绩效报告（_GET_FBA_FULFILLMENT_INBOUND_NONCOMPLIANCE_DATA_）',
            'RequestReport_GET_FBA_HAZMAT_STATUS_CHANGE_DATA_' => '11 亚马逊物流危险品状态更改报告（_GET_FBA_HAZMAT_STATUS_CHANGE_DATA_）',

            'GetReportRequestList' => '报告请求列表',

            'GetReport' => '获取报告',

            'ListInboundShipments' => '入库',
            'ListInboundShipmentItems' => '入库明细',
        ];
    }

    /**
     * 测试
     *
     * @BeMenu("接口测试", icon="el-icon-fa fa-users", ordering="10")
     * @BePermission("接口测试", ordering="10")
     */
    public function index()
    {
        if (Request::isPost()) {
            $postData = Request::json();
            $response = $this->request($postData['formData']);
            print_r($response);
        } else {

            Be::getPlugin('Form')->setting([
                'form' => [
                    'ui' => [
                        'action' => beUrl('Fba.Fba.test'),
                        'target' => '_blank',
                    ],
                    'items' => [
                        [
                            'name' => 'accountKey',
                            'label' => '账号',
                            'driver' => FormItemSelect::class,
                            //'values' => array_slice(array_keys($this->fbaAccounts), 0, 20),
                            //'values' => array_rand($this->fbaAccounts, 100),
                            'values' => array_keys($this->fbaAccounts),
                            'value' => 'AnnabelleMaria[US]',
                            'ui' => [
                                'select' => ['style' => 'width: 800px;']
                            ],
                        ],
                        [
                            'name' => 'api',
                            'label' => '测试接口',
                            'driver' => FormItemSelect::class,
                            'keyValues' => $this->apiKeyValues,
                            'ui' => [
                                'select' => ['style' => 'width: 800px;']
                            ],
                        ],
                        [
                            'name' => 'reportId',
                            'label' => '报告ID',
                        ],
                    ],
                ]
            ])->execute();
        }

    }


    protected function request($formData)
    {

        $account = $formData['accountKey'];
        $api = $formData['api'];
        $reportId = $formData['reportId'];

        if (!isset($this->fbaAccounts[$account])) {
            return '账号不存在！';
        }

        if (!isset($this->apiKeyValues[$api])) {
            return 'API不存在！';
        }

        $account = $this->fbaAccounts[$account];


        $request = [
            'query' => [
                'AWSAccessKeyId' => $account['accessKeyId'],
                'MWSAuthToken' => $account['mwsAuthToken'] ?: $account['authToken'],
                'SellerId' => $account['merchantId'],
                'MarketplaceIdList.Id.1' => $account['marketplaceId'],
                'SignatureMethod' => 'HmacSHA256',
                'SignatureVersion' => '2',
                'Timestamp' => gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", time())
            ],
            'body' => [],
            'headers' => ['Content-Type' => 'text/xml;charset: iso-8859-1']
        ];

        $path = '';
        $action = '';
        switch ($api) {

            // --------------------------------------------------------------------------------------------------------- 亚马逊物流销售报告
            case 'RequestReport_GET_FBA_FULFILLMENT_CUSTOMER_SHIPMENT_SALES_DATA_': // 亚马逊物流买家货件销售报告
                $request['query']['Action'] = 'RequestReport';
                $request['query']['ReportType'] = '_GET_FBA_FULFILLMENT_CUSTOMER_SHIPMENT_SALES_DATA_';
                $request['query']['StartDate'] = gmdate("Y-m-d\T00:00:00", time() - 86400*2);
                $request['query']['EndDate'] = gmdate("Y-m-d\T00:00:00");
                break;



            // --------------------------------------------------------------------------------------------------------- 亚马逊物流库存报告
            case 'RequestReport_GET_AFN_INVENTORY_DATA_': // 1 亚马逊物流库存报告
                $request['query']['Action'] = 'RequestReport';
                $request['query']['ReportType'] = '_GET_AFN_INVENTORY_DATA_';
                break;

            case 'RequestReport_GET_FBA_FULFILLMENT_CURRENT_INVENTORY_DATA_': // 2 亚马逊物流每日库存历史报告
                $request['query']['Action'] = 'RequestReport';
                $request['query']['ReportType'] = '_GET_FBA_FULFILLMENT_CURRENT_INVENTORY_DATA_';

                //$t = time() - 86400 * 2;
                $t = strtotime(date('Y-m-d', strtotime(date('Y-m-d')) - 1)) - 1;
                $t = strtotime('2020-10-01 00:00:00')-3600;
                $startTime = date("y-m-d 00:00:00", $t);
                $endTime = date("y-m-d 23:59:59", $t);
                $request['query']['StartDate'] = gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", strtotime($startTime));
                $request['query']['EndDate'] = gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", strtotime($endTime));
                break;

            case 'RequestReport_GET_FBA_FULFILLMENT_MONTHLY_INVENTORY_DATA_': // 3 亚马逊物流每月库存历史报告
                $request['query']['Action'] = 'RequestReport';
                $request['query']['ReportType'] = '_GET_FBA_FULFILLMENT_MONTHLY_INVENTORY_DATA_';

                $t = strtotime(date('Y-m-01')) - 1;
                $request['query']['StartDate'] = gmdate('Y-m-01\T00:00:00.\\0\\0\\0\\Z', $t);
                $request['query']['EndDate'] = gmdate('Y-m-d\T23:59:59.\\0\\0\\0\\Z', $t);
                break;

            case 'RequestReport_GET_FBA_FULFILLMENT_INVENTORY_RECEIPTS_DATA_': // 4 亚马逊物流已收到库存报告
                $request['query']['Action'] = 'RequestReport';
                $request['query']['ReportType'] = '_GET_FBA_FULFILLMENT_INVENTORY_RECEIPTS_DATA_';
                $request['query']['StartDate'] = gmdate("Y-m-01\T00:00:00", strtotime(date('Y-m-01')) - 86400 * 30);
                $request['query']['EndDate'] = gmdate("Y-m-d\T23:59:59", strtotime(date('Y-m-01')) - 86400);
                break;

            case 'RequestReport_GET_FBA_FULFILLMENT_INVENTORY_SUMMARY_DATA_': // 5 亚马逊物流库存事件详情报告
                $request['query']['Action'] = 'RequestReport';
                $request['query']['ReportType'] = '_GET_FBA_FULFILLMENT_INVENTORY_SUMMARY_DATA_';
                $request['query']['StartDate'] = gmdate("Y-m-d\T00:00:00", time() - 86400*30);
                $request['query']['EndDate'] = gmdate("Y-m-d\T00:00:00");

                $t1 = strtotime('2020-10-10 00:00:00');
                $t2 = strtotime('2020-10-10 23:59:59');
                $request['query']['StartDate'] = gmdate("Y-m-d\T00:00:00", $t1);
                $request['query']['EndDate'] = gmdate("Y-m-d\T00:00:00", $t2);
                break;


            case 'RequestReport_GET_FBA_FULFILLMENT_INVENTORY_ADJUSTMENTS_DATA_': //6 亚马逊物流库存盘点报告
                $request['query']['Action'] = 'RequestReport';
                $request['query']['ReportType'] = '_GET_FBA_FULFILLMENT_INVENTORY_ADJUSTMENTS_DATA_';
                $request['query']['StartDate'] = gmdate("Y-m-d\T00:00:00", time() - 86400*360);
                $request['query']['EndDate'] = gmdate("Y-m-d\T00:00:00", time() - 86400*180);
                break;

            case 'RequestReport_GET_FBA_FULFILLMENT_INVENTORY_HEALTH_DATA_': // 7 亚马逊物流库存状况报告
                $request['query']['Action'] = 'RequestReport';
                $request['query']['ReportType'] = '_GET_FBA_FULFILLMENT_INVENTORY_HEALTH_DATA_';
                $request['query']['StartDate'] = gmdate("Y-m-d\T00:00:00", time() - 86400*180);
                $request['query']['EndDate'] = gmdate("Y-m-d\T00:00:00");
                break;

            case 'RequestReport_GET_FBA_MYI_UNSUPPRESSED_INVENTORY_DATA_': // 8 亚马逊物流管理库存
                $request['query']['Action'] = 'RequestReport';
                $request['query']['ReportType'] = '_GET_FBA_MYI_UNSUPPRESSED_INVENTORY_DATA_';
                $request['query']['StartDate'] = gmdate("Y-m-d\T00:00:00", time() - 86400*180);
                $request['query']['EndDate'] = gmdate("Y-m-d\T00:00:00");
                break;

            case 'RequestReport_GET_FBA_MYI_ALL_INVENTORY_DATA_': // 9 亚马逊物流管理库存 - 存档
                $request['query']['Action'] = 'RequestReport';
                $request['query']['ReportType'] = '_GET_FBA_MYI_ALL_INVENTORY_DATA_';
                $request['query']['StartDate'] = gmdate("Y-m-d\T00:00:00", time() - 86400*3);
                $request['query']['EndDate'] = gmdate("Y-m-d\T00:00:00");
                break;

            case 'RequestReport_GET_FBA_FULFILLMENT_INBOUND_NONCOMPLIANCE_DATA_': // 10 亚马逊物流入库绩效报告
                $request['query']['Action'] = 'RequestReport';
                $request['query']['ReportType'] = '_GET_FBA_FULFILLMENT_INBOUND_NONCOMPLIANCE_DATA_';
                $request['query']['StartDate'] = gmdate("Y-m-01\T00:00:00", strtotime(date('Y-m-01')) - 86400*360);
                $request['query']['EndDate'] = gmdate("Y-m-d\T23:59:59", strtotime(date('Y-m-01')) - 86400*180);
                break;

            case 'RequestReport_GET_FBA_HAZMAT_STATUS_CHANGE_DATA_': // 11 亚马逊物流危险品状态更改报告
                $request['query']['Action'] = 'RequestReport';
                $request['query']['ReportType'] = '_GET_FBA_HAZMAT_STATUS_CHANGE_DATA_';
                $request['query']['StartDate'] = gmdate("Y-m-01\T00:00:00", strtotime(date('Y-m-01')) - 86400*300);
                $request['query']['EndDate'] = gmdate("Y-m-d\T23:59:59", strtotime(date('Y-m-01')) - 86400);
                break;


            case 'GetReportRequestList':
                $request['query']['Action'] = 'GetReportRequestList';
                break;

            case 'GetReport':
                $request['query']['Action'] = 'GetReport';
                $request['query']['ReportId'] = $reportId;
                break;


            case 'ListInboundShipments':
                $path .= '/FulfillmentInboundShipment/2010-10-01';
                $request['query']['Action'] = 'ListInboundShipments';
                $request['query']['ShipmentStatusList.member.1'] = 'CLOSED';  // 货件已到达 亚马逊配送中心,且所有商品已标记为已收到
                $startTime = date('Y-m-d 23:59:59', strtotime('-20 day'));
                $endTime = date('Y-m-d 00:00:00');
                $request['query']['LastUpdatedAfter'] = gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", strtotime($startTime));
                $request['query']['LastUpdatedBefore'] = gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", strtotime($endTime));
                break;

            case 'ListInboundShipmentItems':
                $path .= '/FulfillmentInboundShipment/2010-10-01';
                $request['query']['Action'] = 'ListInboundShipmentItems';
                $request['query']['ShipmentId'] = $reportId;
                break;
        }

        $urls = [
            'US' => 'https://mws.amazonservices.com',     // 美国
            'CA' => 'https://mws.amazonservices.ca',      // 加拿大
            'MX' => 'https://mws.amazonservices.com.mx',  // 墨西哥
            'UK' => 'https://mws-eu.amazonservices.com',  // 英国
            'IT' => 'https://mws-eu.amazonservices.com',  // 意大利
            'FR' => 'https://mws-eu.amazonservices.com',  // 法国
            'DE' => 'https://mws-eu.amazonservices.com',  // 德国
            'ES' => 'https://mws-eu.amazonservices.com',  // 西班牙
            'JP' => 'https://mws.amazonservices.jp',      // 日本
            'BR' => 'https://mws.amazonservices.com',     // 巴西
            'IN' => 'https://mws.amazonservices.in',      // 印度
            'TR' => 'https://mws-eu.amazonservices.com',  // 土耳其
            'AU' => 'https://mws.amazonservices.com.au',  // 澳大利亚
            'CN' => 'https://mws.amazonservices.com.cn',  // 中国
            'AE' => 'https://mws.amazonservices.ae',      // 中东
            'SG' => 'https://mws-fe.amazonservices.com',  // 新加坡
            'NL' => 'https://mws-eu.amazonservices.com',  // 荷兰
            'GB' => 'https://mws.amazonservices.co.uk',   // 英国
        ];

        if (!isset($urls[$account['shorthandCode']])) {
            return '站点' . $account['shorthandCode'] . '不存在！';
        }

        $url = $urls[$account['shorthandCode']] . $path;

        $request['query']['Signature'] = $this->signature($url, $request['query'], $account['secretKey']); // 生成签名

        $client = new \GuzzleHttp\Client();

        /*
        $response = $client->request('POST', $url, array_merge([
            'timeout' => 60,
            'verify' => false
        ], $request));
        */

        $response = $client->request('POST', $url, array_merge([
            'timeout' => 60,
            'verify'  => false
        ], array_filter($request)));

        $code = $response->getStatusCode();

        if ($code != 200) {
            $response = $response->getReasonPhrase();
        } else {
            $responseHeaders = array_column(array_values($response->getHeaders()), 0);

            $response = (string)$response->getBody();

            if (in_array($request['query']['Action'], ['GetReport'])) {
                //解析相应头，获取报文字符集
                $charset = 'auto';
                foreach ($responseHeaders as $header) {
                    if (preg_match('/charset=(.*$)/', $header, $match) && isset($match[1])) {
                        $charset = $match[1];
                        break;
                    }
                }

                $response = @mb_convert_encoding($response, 'UTF-8', $charset);

                //报告返回结构非XML: 以换行符分割行, 制表符分割列单元
                $response = explode("\n", $response);

                foreach ($response as &$row) {
                    $row = explode("\t", $row);
                }

                $response = ['GetReportResult' => $response];
            } else {
                $response = json_decode(json_encode(simplexml_load_string($response)), true);
            }
        }

        return $response;
    }

    /**
     * 生成amazon请求链签名
     * @param $url
     * @param array $parameters
     * @param $secretAccessKey
     * @return string
     */
    private function signature($url, array $parameters = [], $secretAccessKey)
    {
        $parsedUrl = parse_url($url);
        $string = "POST\n";
        $string .= $parsedUrl['host'] . "\n";
        $parsedUrl += ['path' => '/'];
        $string .= implode('/', array_map('urlencode', explode('/', $parsedUrl['path'])));
        $string .= "\n";
        uksort($parameters, 'strcmp');
        $string .= $this->buildUrlQuery($parameters);
        return base64_encode(hash_hmac('sha256', $string, $secretAccessKey, true));
    }

    private function buildUrlQuery(array $parameters)
    {
        uksort($parameters, 'strcmp');
        $queryParameters = [];
        foreach ($parameters as $key => $value) {
            $queryParameters[] = $key . '=' . str_replace('%7E', '~', rawurlencode($value));
        }
        return implode('&', $queryParameters);
    }

    protected function getFbaAccounts()
    {
        /*
[
    {
        "accessKeyId": "AKIAI4DD7NTZUCNGCYVQ",
        "account": "Jullynice",
        "authToken": "A9BHTHUITKIXM",
        "authorizeState": 1,
        "expireState": 0,
        "marketplaceId": "ATVPDKIKX0DER",
        "merchantId": "A9BHTHUITKIXM",
        "mwsAuthToken": "amzn.mws.38b8abb8-2a0f-94ed-af38-f58702dc9b61",
        "secretKey": "E5YmmevVuKKaxWFRypJiAmlpT321tl+GH/VoCEWq",
        "shorthandCode": "US",
        "vatNo": ""
    }
]
         */

        $cacheKey = 'FbaAccounts';

        $accounts = Cache::get($cacheKey);
        if ($accounts) {
            return $accounts;
        }

        $url = 'http://newomsweb.kokoerp.com/api/account/token/getAccountToken';

        $client = new \GuzzleHttp\Client();

        $response = $client->request('POST', $url, [
            'body' => json_encode(['platformCode' => 'YA'])
        ]);

        $code = $response->getStatusCode();

        if ($code != 200) {
            return $response->getReasonPhrase();
        }

        $response = json_decode((string)$response->getBody(), true);

        $accounts = [];
        foreach ($response['data'] as $index => $item) {
            $site = $item['shorthandCode']?? '';
            $account = $item['account']?? '';
            $accounts[$account . '[' . $site . ']'] = $item;
        }

        Cache::set($cacheKey, $accounts);
        return $accounts;
    }


}
