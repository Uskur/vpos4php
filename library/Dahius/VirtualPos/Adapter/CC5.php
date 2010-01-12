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

class Dahius_VirtualPos_Adapter_CC5 extends Dahius_VirtualPos_Adapter_Abstract
{
    protected function _getHash($request)
    {
        $hashstr =  sprintf("%s%s%s%s%s%s%s", 
                            $this->_parameters->getPath("client_id"),
                            $this->_formatOrderId($request->orderId, $request->isThreeDSecure),
                            $this->_formatAmount($request->amount),
                            $this->_parameters->getPath("host/callback"),
                            $this->_parameters->getPath("host/callback"),
                            $request->createdOn,
                            $this->_parameters->getPath("store_key"));
        
        return base64_encode(pack('H*',sha1($hashstr)));
    }

    protected function _getAuthenticate($request)
    {       
        $result = "pan={$request->cardNumber}".
                    "&Ecom_Payment_Card_ExpDate_Month={$request->expireMonth}".
                    "&Ecom_Payment_Card_ExpDate_Year={$request->expireYearShort}".
                    "&cv2={$request->cvc}".
                    "&cardType={$this->_formatCardType($request->cardType)}".
                    "&clientid={$this->_parameters->getPath("client_id")}".
                    "&amount={$this->_formatAmount($request->amount)}". 
                    "&oid={$this->_formatOrderId($request->orderId, $this->isThreeDSecure)}".
                    "&okUrl={$this->_getReturnUrl()}".
                    "&failUrl={$this->_getReturnUrl()}".
                    "&storetype={$this->_parameters->getPath("store_type")}".
                    "&rnd={$request->createdOn}".
                    "&xid={$this->_formatOrderId($request->orderId, $this->isThreeDSecure)}".
                    "&VirtualPosAdapterName={$this->_name}".
                    "&hash={$this->_getHash($request)}";

        return $result;
    }

    protected function _getComplete($request)
    {
        if (!in_array($request->threeDResponse["mdStatus"], $this->_parameters->getPath("valid_md_status"))) {
            $response = new Dahius_VirtualPos_Response();

            $response->createdOn = time();
            $response->createdBy = $this->_name;
            $response->code = -3;
            $response->message = "mdStatus({$request->threeDResponse["mdStatus"]}) Not Valid";

            return $response;
        }

        $transactionType = ($request->transactionType == "provision") ? "PreAuth" : "Auth";

        $xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-9\"?>
                <CC5Request>
                    <Name>{$this->_parameters->getPath("username")}</Name>
                    <Password>{$this->_parameters->getPath("password")}</Password>
                    <ClientId>{$this->_parameters->getPath("client_id")}</ClientId>
                    <IPAddress>{$request->remoteAddress}</IPAddress>
                    <Email>$request->email</Email>
                    <Mode>{$this->_parameters->getPath("working_mode")}</Mode>
                    <OrderId>{$this->_formatOrderId($request->orderId, $request->isThreeDSecure)}</OrderId>
                    <GroupId>{$request->id}</GroupId>
                    <TransId>{$request->id}</TransId>
                    <UserId>{$request->userId}</UserId>
                    <Type>{$transactionType}</Type>
                    <Number>{$request->cardNumber}</Number>
                    <Expires>{$this->_formatExpireDate($request->expireMonth, $request->expireYear)}</Expires>
                    <Cvv2Val>{$request->cvc}</Cvv2Val>
                    <Total>{$this->_formatAmount($request->amount)}</Total>
                    <Currency>{$this->_formatCurrency($request->currency)}</Currency>
                    <Taksit>{$this->_formatInstallment($request->installment)}</Taksit>
                    <PayerTxnId>{$request->threeDResponse["xid"]}</PayerTxnId>
                    <PayerSecurityLevel>{$request->threeDResponse["eci"]}</PayerSecurityLevel>
                    <PayerAuthenticationCode>{$request->threeDResponse["cavv"]}</PayerAuthenticationCode>   
                    <CardholderPresentCode>13</CardholderPresentCode>
                    <BillTo>
                        <Name>{$this->_toISO8859($request->cardHolder)}</Name> 
                        <Street1>{$this->_toISO8859($request->billTo->address)}</Street1>
                        <Street2></Street2>
                        <Street3></Street3>
                        <City>{$this->_toISO8859($request->billTo->city)}</City>
                        <StateProv></StateProv>
                        <PostalCode>{$this->_toISO8859($request->billTo->postalCode)}</PostalCode>
                        <Country>{$this->_toISO8859($request->billTo->country)}</Country>
                        <Company></Company>
                        <TelVoice></TelVoice>
                    </BillTo>
                    <ShipTo>
                        <Name>{$this->_toISO8859($request->cardHolder)}</Name> 
                        <Street1>{$this->_toISO8859($request->shipTo->address)}</Street1>
                        <Street2></Street2>
                        <Street3></Street3>
                        <City>{$this->_toISO8859($request->shipTo->city)}</City>
                        <StateProv></StateProv>
                        <PostalCode>{$this->_toISO8859($request->shipTo->postalCode)}</PostalCode>
                        <Country>{$this->_toISO8859($request->shipTo->country)}</Country>
                    </ShipTo>
                    <Extra></Extra>
                </CC5Request>
            "; 

        return "DATA=$xml";
    }

    protected function _getProvision($request)
    {
        $xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-9\"?>
                <CC5Request>
                    <Name>{$this->_parameters->getPath("username")}</Name>
                    <Password>{$this->_parameters->getPath("password")}</Password>
                    <ClientId>{$this->_parameters->getPath("client_id")}</ClientId>
                    <IPAddress>{$request->remoteAddress}</IPAddress>
                    <Email>$request->email</Email>
                    <Mode>{$this->_parameters->getPath("working_mode")}</Mode>
                    <OrderId>{$this->_formatOrderId($request->orderId, $request->isThreeDSecure)}</OrderId>
                    <GroupId>{$request->id}</GroupId>
                    <TransId>{$request->id}</TransId>
                    <UserId>{$request->userId}</UserId>
                    <Type>PreAuth</Type>
                    <Number>{$request->cardNumber}</Number>
                    <Expires>{$this->_formatExpireDate($request->expireMonth, $request->expireYear)}</Expires>
                    <Cvv2Val>{$request->cvc}</Cvv2Val>
                    <Total>{$this->_formatAmount($request->amount)}</Total>
                    <Currency>{$this->_formatCurrency($request->currency)}</Currency>
                    <Taksit>{$this->_formatInstallment($request->installment)}</Taksit>
                    <BillTo>
                        <Name>{$this->_toISO8859($request->cardHolder)}</Name> 
                        <Street1>{$this->_toISO8859($request->billTo->address)}</Street1>
                        <Street2></Street2>
                        <Street3></Street3>
                        <City>{$this->_toISO8859($request->billTo->city)}</City>
                        <StateProv></StateProv>
                        <PostalCode>{$this->_toISO8859($request->billTo->postalCode)}</PostalCode>
                        <Country>{$this->_toISO8859($request->billTo->country)}</Country>
                        <Company></Company>
                        <TelVoice></TelVoice>
                    </BillTo>
                    <ShipTo>
                        <Name>{$this->_toISO8859($request->cardHolder)}</Name> 
                        <Street1>{$this->_toISO8859($request->shipTo->address)}</Street1>
                        <Street2></Street2>
                        <Street3></Street3>
                        <City>{$this->_toISO8859($request->shipTo->city)}</City>
                        <StateProv></StateProv>
                        <PostalCode>{$this->_toISO8859($request->shipTo->postalCode)}</PostalCode>
                        <Country>{$this->_toISO8859($request->shipTo->country)}</Country>
                    </ShipTo>
                    <Extra></Extra>
                </CC5Request>
            ";        

        return "DATA=$xml";
    }

    protected function _getSale($request)
    {
        $xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-9\"?>
                <CC5Request>
                    <Name>{$this->_parameters->getPath("username")}</Name>
                    <Password>{$this->_parameters->getPath("password")}</Password>
                    <ClientId>{$this->_parameters->getPath("client_id")}</ClientId>
                    <IPAddress>{$request->remoteAddress}</IPAddress>
                    <Email>{$request->email}</Email>
                    <Mode>{$this->_parameters->getPath("working_mode")}</Mode>
                    <OrderId>{$this->_formatOrderId($request->orderId, $request->isThreeDSecure)}</OrderId>
                    <GroupId>{$request->id}</GroupId>
                    <TransId>{$request->transactionId}</TransId>
                    <UserId>{$request->userId}</UserId>
                    <Type>Auth</Type>
                    <Number>{$request->cardNumber}</Number>
                    <Expires>{$this->_formatExpireDate($request->expireMonth, $request->expireYear)}</Expires>
                    <Cvv2Val>{$request->cvc}</Cvv2Val>
                    <Total>{$this->_formatAmount($request->amount)}</Total>
                    <Currency>{$this->_formatCurrency($request->currency)}</Currency>
                    <Taksit>{$this->_formatInstallment($request->installment)}</Taksit>
                    <BillTo>
                        <Name>{$this->_toISO8859($request->cardHolder)}</Name> 
                        <Street1>{$this->_toISO8859($request->billTo->address)}</Street1>
                        <Street2></Street2>
                        <Street3></Street3>
                        <City>{$this->_toISO8859($request->billTo->city)}</City>
                        <StateProv></StateProv>
                        <PostalCode>{$this->_toISO8859($request->billTo->postalCode)}</PostalCode>
                        <Country>{$this->_toISO8859($request->billTo->country)}</Country>
                        <Company></Company>
                        <TelVoice></TelVoice>
                    </BillTo>
                    <ShipTo>
                        <Name>{$this->_toISO8859($request->cardHolder)}</Name> 
                        <Street1>{$this->_toISO8859($request->shipTo->address)}</Street1>
                        <Street2></Street2>
                        <Street3></Street3>
                        <City>{$this->_toISO8859($request->shipTo->city)}</City>
                        <StateProv></StateProv>
                        <PostalCode>{$this->_toISO8859($request->shipTo->postalCode)}</PostalCode>
                        <Country>{$this->_toISO8859($request->shipTo->country)}</Country>
                    </ShipTo>
                    <Extra></Extra>
                </CC5Request>
            ";        

        return "DATA=$xml";
    }

    protected function _getRefusal($request)
    {
        $xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-9\"?>
                <CC5Request>
                    <Name>{$this->_parameters->getPath("username")}</Name>
                    <Password>{$this->_parameters->getPath("password")}</Password>
                    <ClientId>{$this->_parameters->getPath("client_id")}</ClientId>
                    <IPAddress>{$request->remoteAddress}</IPAddress>
                    <Mode>{$this->_parameters->getPath("working_mode")}</Mode>
                    <OrderId>{$this->_formatOrderId($request->orderId, $request->isThreeDSecure)}</OrderId>
                    <TransId>{$request->transactionId}</TransId>
                    <UserId>{$request->userId}</UserId>
                    <Type>Credit</Type>
                    <Total>{$this->_formatAmount($request->amount)}</Total>
                    <Currency>{$this->_formatCurrency($request->currency)}</Currency>
                    <!-- Kredi Kartı Numaraları Gerçekten Bir İade Yapılacaksa Gerekli. -->
                    <Number>{$request->cardNumber}</Number>
                    <Expires>{$this->_formatExpireDate($request->expireMonth, $request->expireYear)}</Expires>
                    <Cvv2Val>{$request->cvc}</Cvv2Val>
                </CC5Request>
            ";        

        return "DATA=$xml";
    }

    protected function _getReversal($request)
    {
        $xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-9\"?>
                <CC5Request>
                    <Name>{$this->_parameters->getPath("username")}</Name>
                    <Password>{$this->_parameters->getPath("password")}</Password>
                    <ClientId>{$this->_parameters->getPath("client_id")}</ClientId>
                    <IPAddress>{$request->remoteAddress}</IPAddress>
                    <Mode>{$this->_parameters->getPath("working_mode")}</Mode>
                    <OrderId>{$this->_formatOrderId($request->orderId, $request->isThreeDSecure)}</OrderId>
                    <TransId>{$request->transactionId}</TransId>
                    <UserId>{$request->userId}</UserId>
                    <Type>Void</Type>
                    <Total>{$this->_formatAmount($request->amount)}</Total>
                    <Currency>{$this->_formatCurrency($request->currency)}</Currency>
                </CC5Request>
            ";

        return "DATA=$xml";
    }

    protected function _getDisposal($request)
    {
        $xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-9\"?>
                <CC5Request>
                    <Name>{$this->_parameters->getPath("username")}</Name>
                    <Password>{$this->_parameters->getPath("password")}</Password>
                    <ClientId>{$this->_parameters->getPath("client_id")}</ClientId>
                    <IPAddress>{$request->remoteAddress}</IPAddress>
                    <Mode>{$this->_parameters->getPath("working_mode")}</Mode>
                    <OrderId>{$this->_formatOrderId($request->orderId, $request->isThreeDSecure)}</OrderId>
                    <TransId>{$request->transactionId}</TransId>
                    <UserId>{$request->userId}</UserId>
                    <Type>PostAuth</Type>
                    <Total>{$this->_formatAmount($request->amount)}</Total>
                    <Currency>{$this->_formatCurrency($request->currency)}</Currency>
                </CC5Request>
            ";        

        return "DATA=$xml";
    }


    protected function _parseAuthenticate($answer)
    {
        $response = new Dahius_VirtualPos_Response();
        $response->createdOn = time();
        $response->createdBy = $this->_name;

        if ($answer["succeed"]) {
            $response->succeed = true;
            $response->message = $answer["response"];
        }
        else {
            $response->code = -1;
            $response->message = "CURL Error: {$answer["message"]}"; 
        }

        return $response;
    }

    protected function _parseSale($answer)
    {
        return $this->_parser($answer);
    }

    protected function _parseRefusal($answer)
    {
        return $this->_parser($answer);
    }

    protected function _parseProvision($answer)
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
        return number_format($amount, 2, '.', '');
    }

    private function _formatCurrency($currency)
    {
        $currencies = array("TRL"=>949, 
                            "YTL"=>949,
                            "USD"=>840,
                            "EUR"=>978);

        return ($currencies[$currency] > 0)
                    ? $currencies[$currency] 
                    : 949; 
    }

    private function _formatInstallment($installment)
    {
        return (is_numeric($installment) == false || intval($installment) <= 1) 
                    ? "" 
                    : $installment;
    }

    private function _formatCardType($type)
    {
        return (($type == "visa") ? "1" : "2");
    }

    private function _getReturnUrl()
    {
        return urlencode($this->_parameters->getPath("host/callback"));
    }

    private function _parser($answer)
    {
        $response = new Dahius_VirtualPos_Response();
        $response->createdOn = time();
        $response->createdBy = $this->_name;
  
        if ($answer["succeed"]) {
            $xmlData = substr($answer["response"], strpos($answer["response"], "<?xml"));
            try {
                $xmlObj = new SimpleXMLElement($xmlData);

                $response->succeed = ($xmlObj->Response == "Approved");
                $response->transactionId = $xmlObj->TransId;
                $response->provision = $xmlObj->AuthCode;
                $response->code = $xmlObj->ProcReturnCode;
                $response->message = $response->succeed
                                            ? "Succeed"
                                            : "ErrorMessage: {$xmlObj->ErrMsg} - ExtraHostMessage: {$xmlObj->Extra->HOSTMSG}";
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
}
