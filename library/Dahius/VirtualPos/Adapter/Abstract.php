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

abstract class Dahius_VirtualPos_Adapter_Abstract implements Dahius_VirtualPos_Interface
{
    /**
     * var Joy_Array $_parameters is pos parameters
     */
    protected $_parameters;

    /**
     * var string $_name is  adapter name
     */

    /**
     * __construct method
     *
     * @param string $name is adapter-name
     * @param array $params is parameter values
     */
    public function __construct($name, $params)
    {
        $this->_name = $name;
        $this->_parameters = new Joy_Array($params);
    }

    /**
     * __toString method
     *
     * @return string adapter name
     */
    public function __toString()
    {
        return $this->_name;
    }

    /**
     * authenticate is method for 3d secure 
     * 
     * @param Dahius_VirtualPos_Request $request
     * @return Dahius_VirtualPos_Response
     */
    public function authenticate($request)
    {
        $this->_stamp(&$request);

        $response = $this->_post($this->_parameters->getPath("host/authenticate"),
                                 $this->_getAuthenticate($request));
        
        return $this->_parseAuthenticate($response);
    }

    /**
     * complete is method for 3d secure 
     * 
     * @param array $request
     * @return Dahius_VirtualPos_Response
     */
    public function complete($request)
    {
        $complete = $this->_getComplete($request);

        if ($complete instanceof Dahius_VirtualPos_Response) {
            return $complete;
        }

        $response = $this->_post($this->_parameters->getPath("host/bank"), 
                                 $complete);

        return $this->_parseComplete($response);
    }

    /**
     * provision is method for reserve amount on the card limit
     * 
     * @param Dahius_VirtualPos_Request $request
     * @return Dahius_VirtualPos_Response
     */
    public function provision($request)
    {
        $this->_stamp(&$request, "provision");

        if ($request->isThreeDSecure) { return $this->authenticate($request); }

        $response = $this->_post($this->_parameters->getPath("host/bank"),
                                 $this->_getProvision($request));

        return $this->_parseProvision($response);
    }

    /**
     * sale is method for payment sale amount on the card limit
     * 
     * @param Dahius_VirtualPos_Request $request
     * @return Dahius_VirtualPos_Response
     */
    public function sale($request)
    {
        $this->_stamp(&$request, "sale");

        if ($request->isThreeDSecure) { return $this->authenticate($request); }

        $response = $this->_post($this->_parameters->getPath("host/bank"), 
                                 $this->_getSale($request));

        return $this->_parseSale($response);
    }

    /**
     * reversal is method for provision request cancelation
     * 
     * @param Dahius_VirtualPos_Request $request
     * @return Dahius_VirtualPos_Response
     */
    public function reversal($request)
    {
        $this->_stamp(&$request);

        $response = $this->_post($this->_parameters->getPath("host/bank"), 
                                 $this->_getReversal($request));

        return $this->_parseReversal($response);
    }

    /**
     * disposal is method for provision request status change via sale
     * 
     * @param Dahius_VirtualPos_Request $request
     * @return Dahius_VirtualPos_Response
     */
    public function disposal($request)
    {
        $this->_stamp(&$request);

        $response = $this->_post($this->_parameters->getPath("host/bank"),
                                 $this->_getDisposal($request));

        return $this->_parseDisposal($response);
    }

    /**
     * refusal is method for sale process update amount
     * 
     * @param Dahius_VirtualPos_Request $request
     * @return Dahius_VirtualPos_Response
     */
    public function refusal($request)
    {
        $this->_stamp(&$request);

        $response = $this->_post($this->_parameters->getPath("host/bank"), 
                                   $this->_getRefusal($request));

        return $this->_parseRefusal($response);
    }

    /**
     * _stamp method is request marker
     * 
     * @param Dahius_VirtualPos_Request $request
     * @param string $transactionType 
     * @return void
     */
    protected function _stamp($request, $transactionType=null)
    {
        $request->createdBy = $this->_name;
        $request->createdOn = time();

        if ($transactionType) {
            $request->transactionType = $transactionType;
        }
    }

    /**
     * _post method is using CURL Library
     *
     * @param string $url
     * @param array $fields
     * @return array
     */
    protected function _post($url, $fields)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_MUTE, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 100);
        $response = curl_exec($ch);

        if (curl_errno($ch)) { 
            $message = curl_error($ch); 
        }
        else { 
            curl_close($ch); 
            $message = "";
        }

        return array("succeed"=>(bool)!$message, 
                     "message"=>$message, 
                     "response"=>$response);
    }

    /**
     * _toISO8859 method is charset converter
     *
     * @param string $text
     * @return string 
     */
    protected function _toISO8859($text)
    {
        return iconv("UTF-8", "ISO-8859-9", $text);  
    }

    /**
     * _getXXX methods are preparing xml data
     */
    protected abstract function _getAuthenticate($request);
    protected abstract function _getProvision($request);
    protected abstract function _getSale($request);
    protected abstract function _getRefusal($request);
    protected abstract function _getReversal($request);
    protected abstract function _getDisposal($request);
    protected abstract function _getComplete($request);

    /**
     * _parseXXX methods are getting Dahius_VirtualPos_Response from post answer array.
     */
    protected abstract function _parseAuthenticate($request);
    protected abstract function _parseProvision($request);
    protected abstract function _parseSale($request);
    protected abstract function _parseRefusal($request);
    protected abstract function _parseReversal($request);
    protected abstract function _parseDisposal($request);
    protected abstract function _parseComplete($request);
}
