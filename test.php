<?php

namespace core;

use core\sdk\Config;
use core\sdk\Profile\DefaultProfile;
use core\sdk\DefaultAcsClient;

// 加载区域结点配置
Config::load();
/**
 * Class SmsDemo
 *
 * Created on 17/10/17.
 * 短信服务API产品的DEMO程序,工程中包含了一个SmsDemo类，直接通过
 * 执行此文件即可体验语音服务产品API功能(只需要将AK替换成开通了云通信-短信服务产品功能的AK即可)
 * 备注:Demo工程编码采用UTF-8
 */
class test extends \yii\base\Component
{

    static $acsClient = null;
    public $config;

    public function init()
    {
        parent::init();
        if ( empty( $this->config ) )
        {
            throw new InvalidConfigException( 'SmsUtility::config 初始化失败！' );
        }
    }

    /**
     * 取得AcsClient
     *
     * @return DefaultAcsClient
     */
    public function getAcsClient() {
        //产品名称:云通信流量服务API产品,开发者无需替换
        $product = "Dysmsapi";
        //产品域名,开发者无需替换
        $domain = "dysmsapi.aliyuncs.com";

        // TODO 此处需要替换成开发者自己的AK (https://ak-console.aliyun.com/)
        $accessKeyId = $this->config['accessId']; // AccessKeyId

        $accessKeySecret = $this->config['accessKey']; // AccessKeySecret

        // 暂时不支持多Region
        $region = "cn-hangzhou";

        // 服务结点
        $endPointName = "cn-hangzhou";


        if(static::$acsClient == null) {

            //初始化acsClient,暂不支持region化
            $profile = DefaultProfile::getProfile($region, $accessKeyId, $accessKeySecret);

            // 增加服务结点
            DefaultProfile::addEndpoint($endPointName, $region, $product, $domain);

            // 初始化AcsClient用于发起请求
            static::$acsClient = new DefaultAcsClient($profile);
        }
        return static::$acsClient;
    }

    /**
     * 发送短信
     * @return stdClass
     */
    public function sendSms($mobile, $smsCode) {
        // 初始化SendSmsRequest实例用于设置发送短信的参数
        $request = new SendSmsRequest();

        // 必填，设置短信接收号码
        $request->setPhoneNumbers($mobile);

        // 必填，设置签名名称，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
        $request->setSignName($this->config['sign']);

        // 必填，设置模板CODE，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
        $request->setTemplateCode($this->config['tmpCode']);

        // 可选，设置模板参数, 假如模板中存在变量需要替换则为必填项
        $request->setTemplateParam(json_encode(array(  // 短信模板中字段的值
            "code"    => $smsCode,
            "product" => $this->config['paramKey']
        ), JSON_UNESCAPED_UNICODE));

        // 可选，设置流水号
//         $request->setOutId("yourOutId");

        // 选填，上行短信扩展码（扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段）
//         $request->setSmsUpExtendCode("1234567");

        $apiStart = microtime(true);
        
        try
        {
            // 发起访问请求
//             $res = $this->getAcsClient()->getAcsResponse($request);

            $apiEnd = microtime(true);

            $end = ($res['Message'] == 'OK');
            $end = true;

            $result = [
                'success'        => $end,
                'response'       => $res['RequestId'],
                'apiMsg'         => $end ? $res['Message'] : '发送失败，请稍后再试',
                'apiCode'        => $res['Code'],
                'apiTime'        => ($apiEnd - $apiStart) * 1000,
                'apiException'   => null,
                'responseStatus' => $end,
            ];
        }
        catch ( MnsException $e )
        {
            $apiEnd = microtime(true);
        
            $result = [
                'success' => false,
                'response' => strval( $e ),
                'apiMsg' => strval( $e ),
                'apiTime' => ( $apiEnd - $apiStart ) * 1000,
                'apiException' => strval( $e ),
                'responseStatus' => false,
            ];
        }

            return $result;
    }

    /**
     * 短信发送记录查询
     * @return stdClass
     */
    public static function querySendDetails() {

        // 初始化QuerySendDetailsRequest实例用于设置短信查询的参数
        $request = new QuerySendDetailsRequest();

        // 必填，短信接收号码
        $request->setPhoneNumber("12345678901");

        // 必填，短信发送日期，格式Ymd，支持近30天记录查询
        $request->setSendDate("20170718");

        // 必填，分页大小
        $request->setPageSize(10);

        // 必填，当前页码
        $request->setCurrentPage(1);

        // 选填，短信发送流水号
        $request->setBizId("yourBizId");

        // 发起访问请求
        $acsResponse = static::getAcsClient()->getAcsResponse($request);

        return $acsResponse;
    }

}