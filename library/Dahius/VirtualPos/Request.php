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
 * @author      Hasan Ozgan <hasan@dahius.com>
 * @copyright   2008-2009 Dahius Corporation (http://www.dahius.com)
 * @license     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @version     $Id$
 * @link        http://vpos4php.googlecode.com
 * @since       0.1
 */

class Dahius_VirtualPos_Request
{
    protected $_id;
    protected $_createdOn;
    protected $_createdBy;
    protected $_remoteAddress;
    protected $_adapter;        // adapterName
    protected $_threeDResponse; 

    protected $_cardNumber;     // 4325567898568882 | 4324-4567-7895-3456 | 5432 2345 6789 8543
    protected $_cardHolder;     
    protected $_cvc;            // 435
    protected $_expireMonth;    // 1 | "01"
    protected $_expireYear;     // 2010

    protected $_orderId;  
    protected $_transactionId;  // Bank Response Value
    protected $_installment;    // 1 | "01"
    protected $_amount;         // 9.34
    protected $_currency;       // "YTL", "USD", "EUR", "TRL"

    protected $_email;
    protected $_userId;
    protected $_billTo;
    protected $_shipTo;

    protected $_isThreeDSecure;
    protected $_transactionType;

    public function __construct()
    {
        $this->_id = md5(uniqid(rand(), true));
        $this->_remoteAddress = $_SERVER["REMOTE_ADDR"];
        $this->_isThreeDSecure = false;
        $this->_billTo = clone $this->_shipTo = (object) array("address"=>"N/A",
                                                               "postalCode"=>"N/A",
                                                               "city"=>"N/A",
                                                               "country"=>"N/A");
    }

    public function __set($key, $value)
    {
        switch ($key)
        {
            case "expireMonth": 
                $value = sprintf("%02s", $value);
                break;
            case "expireYear": 
                $value = sprintf("%04s", $value);
                break;
            case "cardNumber": 
                  $value = str_replace("-", "", trim($value));
                  $value = str_replace(" ", "", $value);
  
                  if (preg_match("/[^0-9]+/", $value, $matches)) {
                      throw new Exception("Card number must be numeric");
                  }
                break;
            case "installment":
                $value = (int) ($installment < 2) ? 1 : $value;
                break;
            case "cvc":
                $value = sprintf("%03s", $value);
                break;
            case "amount":
                $value = (double) str_replace(",", ".", $value);
                break;
        }

        $property = "_$key";
        if (!property_exists($this, $property)) throw new Exception("Property($key) not found");
    
        $this->{$property} = $value;
    }

    public function __get($key)
    {
        switch ($key) 
        {
            case "expireYearShort":
                return sprintf("%02s", substr($this->_expireYear, -2));
            case "secureNumber":
                return  substr($this->_cardNumber, 0, 4)." ".
                        substr($this->_cardNumber, 4, 2)."** **** ".
                        substr($this->_cardNumber, -4);
            case "binNumber":
                return substr($this->_cardNumber, 0, 6);
            case "cardType":
                return $this->_getCardType();
            default:
                $property = "_$key";
                if (!property_exists($this, $property)) throw new Exception("Property($key) not found");

                return $this->{$property};
        }
    }

    private function _getCardType()
    {
        $type = substr($this->_cardNumber, 0, 1);

        switch ($type) 
        {
            case 3: return "amex";
            case 4: return "visa";
            case 5: return "master";
        }

        return null;
    }
}
