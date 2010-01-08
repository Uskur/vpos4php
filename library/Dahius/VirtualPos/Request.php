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
    protected $_mode; // 3d, 3dpay, pay
    protected $_type; // sale, provision, refusal, reversal, disposal

    protected $_cardNumber;
    protected $_cardHolder;
    protected $_cvc;
    protected $_expireMonth;
    protected $_expireYear;

    protected $_orderId;
    protected $_installment;
    protected $_amount;

    public function __construct()
    {
        $this->_id = md5(uniqid(rand(), true));
        $this->_createdOn = time();
        $this->_mode = "3dpay";
    }

    public function setType($type)
    {
        $this->_type = $type;
    }

    public function setMode($mode)
    {
        $this->_mode = $mode;
    }

    public function setCardNumber($number)
    {
        $this->_cardNumber = $number;
    }

    public function setCardHolder($name)
    {
        $this->_cardHolder = $name;
    }

    public function setCvc($cvc)
    {
        $this->_cvc = $cvc;
    }

    public function setExpireDate($month, $year)
    {
        $this->_expireMonth = $month;
        $this->_expireYear = $year;
    }

    public function setExpireMonth($month)
    {
        $this->_expireMonth = $month;
    }

    public function setExpireYear($year)
    {
        $this->_expireYear = $year;
    }

    public function setOrderId($orderId) 
    {
        $this->_orderId = $orderId;
    }

    public function setInstallment($installment) 
    {
        $this->_installment = ($installment < 2) ? 1 : $installment;
    }

    public function setAmount($amount) 
    {
        $this->_amount = (double) str_replace(",", ".", $amount);
    }


    public function getType()
    {
        return $this->_type;
    }

    public function getMode()
    {
        return $this->_mode;
    }

    public function getCardNumber()
    {
        return $this->_cardNumber;
    }

    public function getCardHolder()
    {
        return $this->_cardHolder;
    }

    public function getCvc()
    {
        return $this->_cvc;
    }

    public function getExpireMonth()
    {
        return $this->_expireMonth;
    }

    public function getExpireYear()
    {
        return $this->_expireYear;
    }

    public function getOrderId() 
    {
        return $this->_orderId;
    }

    public function getInstallment() 
    {
        return $this->_installment;
    }

    public function getAmount() 
    {
        return $this->_amount;
    }

    public function post($url, $fields)
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

        return array("success"=>(bool)$message, 
                     "message"=>$message, 
                     "response"=>$response);
    }
}
