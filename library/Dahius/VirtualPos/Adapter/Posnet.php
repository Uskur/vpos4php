<?php
/**
 * Virtual Pos Library
 *
 * Copyright (c) 2008-2009 Dahius Corporation (http://www.dahius.com)
 * All rights reserved.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *  
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL.
 */

/**
 * @package     VirtualPos
 * @subpackage  Adapter
 * @author      Hasan Ozgan <hasan@dahius.com>
 * @copyright   2008-2009 Dahius Corporation (http://www.dahius.com)
 * @license     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @version     $Id$
 * @link        http://vpos4php.googlecode.com
 * @since       0.1
 */

class Dahius_VirtualPos_Adapter_Posnet extends Dahius_VirtualPos_Adapter_Abstract
{
    protected function _getAuthenticate($request)
    {
        $transactionType = ($request->transactionType == "provision") ? "Auth" : "Sale";

        $xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-9\"?>
                <posnetRequest>
                    <mid>{$this->_parameters->getPath("mid")}</mid>
                    <tid>{$this->_parameters->getPath("tid")}</tid>
                    <oosRequest>
                        <posnetid>{$this->_parameters->getPath("posnet_id")}</posnetid>
                        <ccno>{$request->cardNumber}</ccno>
                        <expDate>{$this->_formatExpireDate($request->expireMonth, $request->expireYearShort)}</expDate>
                        <cvc>{$request->cvc}</cvc>
                        <amount>{$this->_formatAmount($request->amount)}</amount>
                        <currencyCode>{$this->_formatCurrency($request->currency)}</currencyCode>
                        <installment>{$this->_formatInstallment($request->installment)}</installment>
                        <XID>{$this->_formatOrderId($request->orderId, $request->isThreeDSecure)}</XID>
                        <cardHolderName>{$this->_toISO8859($request->cardHolder)}</cardHolderName>
                        <tranType>{$this->_formatTransactionType($request->isThreeDSecure, $transactionType)}</tranType>
                    </oosRequest>
                </posnetRequest>";

        return "xmldata=$xml";
    }

    protected function _getComplete($request)
    {
        $response = new Dahius_VirtualPos_Response();
        $response->createdOn = time();
        $response->createdBy = $this->_name;

        // Check 3D Values
        $merchantPack = $request->threeDResponse["MerchantPacket"];
        $bankPack = $request->threeDResponse["BankPacket"];
        $sign  = $request->threeDResponse["Sign"];
        $hash = strtoupper(md5($merchantPack.$bankPack.$this->_parameters->getPath("merchant_key")));

        if (strcmp($hash, $sign) != 0) {
            $response->code = -4;
            $response->message = "Package Not Matched";
        
            return $response;
        }

        // Get MD Status...
        $block = mcrypt_get_block_size(MCRYPT_TripleDES, MCRYPT_MODE_CBC);
        $tdes = mcrypt_module_open(MCRYPT_TripleDES, '', MCRYPT_MODE_CBC, '');
        $key_size = mcrypt_enc_get_key_size($tdes);

        $merchant_info = $this->_deCrypt($merchantPack, $this->_parameters->getPath("merchant_key"), $block, $tdes, $key_size) ;
        mcrypt_generic_deinit($tdes);
        mcrypt_module_close($tdes);

        list($mid, $tid, $amount, $instant, $xid,
                $tp, $tpo, $webURL, $ip, $port, $txStatus,
                $mdStatus, $errMsg, $transactionTime, $currency) = explode(";", $merchant_info);

        if (!in_array($mdStatus, $this->_parameters->getPath("valid_md_status"))) {
            $response->code = -3;
            $response->message = "mdStatus({$request->threeDResponse["mdStatus"]}) Not Valid";

            return $response;
        }

        $xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-9\"?>
                <posnetRequest>
                    <mid>{$this->_parameters->getPath("mid")}</mid>
                    <tid>{$this->_parameters->getPath("tid")}</tid>
                    <username>{$this->_parameters->getPath("username")}</username>
                    <password>{$this->_parameters->getPath("password")}</password>
                    <oosTran>
                        <bank>{$bankPack}</bank>
                        <wpAmount>0</wpAmount>
                    </oosTran>
                </posnetRequest>";                        

        return "xmldata=$xml";
    }

    protected function _getProvision($request)
    {
        $xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-9\"?>
                <posnetRequest>
                    <mid>{$this->_parameters->getPath("mid")}</mid>
                    <tid>{$this->_parameters->getPath("tid")}</tid>
                    <{$this->_formatTransactionType($request->isThreeDSecure, "Auth")}>
                        <currencyCode>{$this->_formatCurrency()}</currencyCode>
                        <ccno>{$request->cardNumber}</ccno>
                        <expDate>{$this->_formatExpires($request->expireMonth, $request->expireYearShort)}</expDate>
                        <cvc>{$request->cvc}</cvc>
                        <amount>{$this->_formatAmount($request->amount)}</amount>
                        <installment>{$this->_formatInstallment($request->installment)}</installment>
                        <orderID>{$this->_formatOrderId($request->orderId)}</orderID>
                    </{$this->_formatTransactionType($request->isThreeDSecure, "Auth")}>
                </posnetRequest>";

       return "xmldata=$xml";
    }

    protected function _getSale($request)
    {
        $xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-9\"?>
                <posnetRequest>
                    <mid>{$this->client_id}</mid>
                    <tid>{$this->store_key}</tid>
                    <{$this->_formatTransactionType($request->isThreeDSecure, "Sale")}>
                        <currencyCode>{$this->_formatCurrency()}</currencyCode>
                        <ccno>{$request->cardNumber}</ccno>
                        <expDate>{$this->_formatExpires($request->expireMonth, $request->expireYearShort)}</expDate>
                        <cvc>{$request->cvc}</cvc>
                        <amount>{$this->_formatAmount($request->amount)}</amount>
                        <installment>{$this->_formatInstallment($request->installment)}</installment>
                        <orderID>{$this->_formatOrderId($request->orderId)}</orderID>
                    </{$this->_formatTransactionType($request->isThreeDSecure, "Sale")}>
              </posnetRequest>";

        return "xmldata=$xml";
    }

    protected function _getRefusal($request)
    {
        $xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-9\"?>
                <posnetRequest>
                    <mid>{$this->_parameters->getPath("mid")}</mid>
                    <tid>{$this->_parameters->getPath("tid")}</tid>
                    <{$this->_formatTransactionType($request->isThreeDSecure, "Return")}>
                        <currencyCode>{$this->_formatCurrency($request->currency)}</currencyCode>
                        <hostLogKey>{$request->transactionId}</hostLogKey>
                        <amount>{$this->_formatAmount($request->amount)}</amount>
                    </{$this->_formatTransactionType($request->isThreeDSecure, "Return")}>
                 </posnetRequest>
            ";

        return "xmldata=$xml";
    }

    protected function _getReversal($request)
    {
        if ($request->transactionType == "provision") {
            $transactionType = "auth";
        }
        else if ($request->transactionType == "sale") {
            $transactionType = "sale";
        }
        else if ($request->transactionType == "refusal") {
            $transactionType = "return";
        } 
        else if ($request->transactionType == "disposal") {
            $transactionType = "capt";
        } 
        else {
            throw new Exception("Please set \$request->transactionType value");
        }

        $xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-9\"?>
               <posnetRequest>
                    <mid>{$this->_parameters->getPath("mid")}</mid>
                    <tid>{$this->_parameters->getPath("tid")}</tid>

                    <{$this->_formatTransactionType($request->isThreeDSecure, "Reverse")}>
                        <transaction>{$transactionType}</transaction>
                        <hostLogKey>{$request->transactionId}</hostLogKey>
                        <authCode>000000</authCode>
                    </{$this->_formatTransactionType($request->isThreeDSecure, "Reverse")}>
                 </posnetRequest>
            "; 

        return "xmldata=$xml";
    }

    protected function _getDisposal($request)
    {
        $xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-9\"?>
                <posnetRequest>
                    <mid>{$this->_parameters->getPath("mid")}</mid>
                    <tid>{$this->_parameters->getPath("tid")}</tid>

                    <{$this->_formatTransactionType($request->isThreeDSecure, "Capt")}>
                        <currencyCode>{$this->_formatCurrency()}</currencyCode>
                        <amount>{$this->_formatAmount($request->amount)}</amount>
                        <installment>{$this->_formatInstallment($request->installment)}</installment>
                        <hostLogKey>{$request->transactionId}</hostLogKey>
                    </{$this->_formatTransactionType($request->isThreeDSecure, "Capt")}>
                 </posnetRequest>
            ";

        return "xmldata=$xml";
    }

    protected function _parseAuthenticate($answer)
    {
        $response = new Dahius_VirtualPos_Response();
        $response->createdOn = time();
        $response->createdBy = $this->_name;
 
        if ($answer["succeed"]) {
            $response->succeed = true;

            $xmlData = substr($answer["response"], strpos($answer["response"], "<?xml"));

            try {
                $xmlObj = new SimpleXMLElement($xmlData);

                if ($xmlObj->approved == 1) {
                    $request = "lang=tr".
                                 "&mid={$this->_parameters->getPath("mid")}".
                                 "&posnetID={$this->_parameters->getPath("posnet_id")}".
                                 "&posnetData={$xmlObj->oosRequestDataResponse->data1}".
                                 "&posnetData2={$xmlObj->oosRequestDataResponse->data2}".
                                 "&digest={$xmlObj->oosRequestDataResponse->sign}".
                                 "&url=".urlencode($this->_parameters->getPath("host/callback")).
                                 "&merchantReturnURL=".urlencode($this->_parameters->getPath("host/callback")).
                                 "&VirtualPosAdapterName={$this->_name}".
                                 "&openANewWindow=0";

                    $answer = $this->_post->($this->_parameters->getPath("host/authorization"), $request);

                    if ($answer["succeed"]) {
                        $form = $answer["response"];
                        $form = str_replace("var is_firefox","var is_firefox=false;//var is_firefox'", $form);
                        $form = str_replace('form.target =','//form.target =', $form);
                        $form = str_replace('document.getElementById("submit2")','', $form);
                        $form = str_replace("self.close()",'', $form);
                        $response->message = $form;
                        $response->succeed = true;
                    }
                    else {
                        $response->code = -1;
                        $response->message = "CURL Error: {$answer["message"]}"; 
                    }
                }
            }
            catch (Exception $ex) {
                $response->code = -2;
                $response->message = "XML Error: {$ex->getMessage()}"; 
            }
        }
        else {
            $response->code = -1;
            $response->message = "CURL Error: {$answer["message"]}"; 
        }

        return $response;
    }

    protected function _parseProvision($answer)
    {
        return $this->_parser($answer);
    }

    protected function _parseSale($answer)
    {
        return $this->_parser($answer);
    }

    protected function _parseRefusal($answer)
    {
        return $this->_parser($answer);
    }

    protected function _parseReversal($answer)
    {
        return $this->_parser($answer);
    }

    protected function _parseDisposal($answer)
    {
        return $this->_parser($answer);
    }

    protected function _parseComplete($answer)
    {
        return $this->_parser($answer);
    }

    private function _formatOrderId($orderID, $hasTreeDSecure=false)
    {    
        return substr(str_pad($orderID, 24, "0", STR_PAD_LEFT), 0, (($hasThreeDSecure) ? 20 : 24));
    }

    private function _formatExpireDate($month, $year)
    {
        return sprintf("%02s/%04s", $month, $year);
    }

    private function _formatAmount($amount)
    {
        return (number_format($amount, 2, '.', '') * 100);
    }

    private function _formatCurrency($currency)
    {
        $currencies = array("TRL"=>"TL", 
                            "YTL"=>"YT",
                            "USD"=>"US",
                            "EUR"=>"EU");

        return ($currencies[$currency] > 0)
                    ? $currencies[$currency] 
                    : "TL"; 
    }

    private function _formatInstallment($installment)
    {
        return (is_numeric($installment) == false || intval($installment) <= 1) 
                    ? "00" 
                    : $installment;
    }

    private function _formatTransactionType($isThreeDSecure, $type)
    {
        $type = strtolower($type);

        return (($isThreeDSecure) ? ucfirst($type) : $type);
    }

    private function _parser($answer)
    {
        $response = new Dahius_VirtualPos_Response();
        $response->createdOn = time();
        $response->createdBy = $this->_name;
 
        if ($answer["succeed"]) {
            $response->succeed = true;

            $xmlData = substr($answer["response"], strpos($answer["response"], "<?xml"));

            try {
                $xmlObj = new SimpleXMLElement($xmlData);

                $response->succeed = ($xmlObj->approved == 1);
                $response->transactionId = $xmlObj->hostlogkey;
                $response->provision = $xmlObj->authCode;
                $response->code = $xmlObj->respCode;
                $response->message = $response->succeed 
                                            ? "Succeed"
                                            : "ErrorMessage: {$xmlObj->respText}";
            }
            catch (Exception $ex) {
                $response->code = -2;
                $response->message = "XML Error: {$ex->getMessage()}"; 
            }
        }
        else {
            $response->code = -1;
            $response->message = "CURL Error: {$answer["message"]}"; 
        }

        return $response;

    }

    private function _deCrypt($data, $key, $block, $td, $ks)
    {
        if(strlen($data) < 16 + 8) return false;

        //Get IV
        $iv = pack("H*", substr($data, 0, 16));

        //Get Encrypted Data
        $encrypted_data = pack("H*", substr($data, 16, strlen($data)-16-8));

        //Get CRC
        $crc = substr($data, -8);

        //Check CRC
        if(!$this->_checkCrc(substr($data, 0, strlen($data)-8), $crc)) {
            throw new Dahius_VirtualPos_Exception("CRC is not valid ! (".$crc.")");
        }

        //Initialize
        mcrypt_generic_init($td, $this->_getKey($key, $ks), $iv);

        //Decrypt Data
        $decrypted_data = mdecrypt_generic($td, $encrypted_data);

        //Remove Padded Data
        return $this->_removeData($decrypted_data, $block);
    }

    private function _getKey($key, $ks)
    {
        return substr(strtoupper(md5($key)), 0, $ks);
    }


    private function _checkCrc($data, $crc)
    {
        $crc_calc = crc32($data);
        $hex_crc = sprintf("%08x", $crc_calc);
        $crc_calc = strtoupper($hex_crc);

        return (bool)(strcmp($crc_calc, $crc) == 0);
    }

    private function _removeData($data, $block)
    {
        $packing = ord($data { strlen($data) - 1 });

        if ($packing and ($packing < $block)) {
            for($P = strlen($data) - 1; $P >= strlen($data) - $packing; $P--) {
                if (ord($data { $P } ) != $packing) {
                    $packing = 0;
                }
            }
        }
        
        return substr($data, 0, strlen($data) - $packing);
    }
}
