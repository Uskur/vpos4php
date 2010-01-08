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
    protected $_parameters;

    public function __construct($params)
    {
        $this->_parameters = new Joy_Array($params);
    }

    /**
     * authentication is method for 3d secure 
     * 
     * @param Dahius_VirtualPos_Request $request
     * @return Dahius_VirtualPos_Response
     */
    public function authenticate($request)
    {
        $response = $request->post($this->_parameters->getPath("host/authentication"), 
                                   $this->getAuthenticateData($request));

        return new Dahius_VirtualPos_Response();
    }

    /**
     * provision is method for reserve amount on the card limit
     * 
     * @param Dahius_VirtualPos_Request $request
     * @return Dahius_VirtualPos_Response
     */
    public function provision($request)
    {
        $response = $request->post($this->_parameters->getPath("host/bank"), 
                                   $this->getProvisionData($request));
    }

    /**
     * sale is method for payment sale amount on the card limit
     * 
     * @param Dahius_VirtualPos_Request $request
     * @return Dahius_VirtualPos_Response
     */
    public function sale($request)
    {
        $response = $request->post($this->_parameters->getPath("host/bank"), 
                                   $this->getSaleData($request));
    }

    /**
     * reversal is method for provision request cancelation
     * 
     * @param Dahius_VirtualPos_Request $request
     * @return Dahius_VirtualPos_Response
     */
    public function reversal($request)
    {
        $response = $request->post($this->_parameters->getPath("host/bank"), 
                                   $this->getReversalData($request));
    }

    /**
     * disposal is method for provision request status change via sale
     * 
     * @param Dahius_VirtualPos_Request $request
     * @return Dahius_VirtualPos_Response
     */
    public function disposal($request)
    {
        $response = $request->post($this->_parameters->getPath("host/bank"), 
                                   $this->getDisposalData($request));
    }

    /**
     * refusal is method for sale process update amount
     * 
     * @param Dahius_VirtualPos_Request $request
     * @return Dahius_VirtualPos_Response
     */
    public function refusal($request)
    {
        $response = $request->post($this->_parameters->getPath("host/bank"), 
                                   $this->getRefusalData($request));
    }

    protected abstract function getAuthenticateData($request);
    protected abstract function getProvisionData($request);
    protected abstract function getSaleData($request);
    protected abstract function getRefusalData($request);
    protected abstract function getReversalData($request);
    protected abstract function getDisposalData($request);
}
