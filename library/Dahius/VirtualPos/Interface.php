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

interface Dahius_VirtualPos_Interface
{
    /**
     * authenticate is method for 3d secure 
     * 
     * @param Dahius_VirtualPos_Request $request
     * @return Dahius_VirtualPos_Response
     */
    public function authenticate($request);

    /**
     * provision is method for reserve amount on the card limit
     * 
     * @param Dahius_VirtualPos_Request $request
     * @return Dahius_VirtualPos_Response
     */
    public function provision($request);

    /**
     * sale is method for payment sale amount on the card limit
     * 
     * @param Dahius_VirtualPos_Request $request
     * @return Dahius_VirtualPos_Response
     */
    public function sale($request);

    /**
     * reversal is method for provision request cancelation
     * 
     * @param Dahius_VirtualPos_Request $request
     * @return Dahius_VirtualPos_Response
     */
    public function reversal($request);

    /**
     * disposal is method for provision request status change via sale
     * 
     * @param Dahius_VirtualPos_Request $request
     * @return Dahius_VirtualPos_Response
     */
    public function disposal($request);

    /**
     * refusal is method for sale process update amount
     * 
     * @param Dahius_VirtualPos_Request $request
     * @return Dahius_VirtualPos_Response
     */
    public function refusal($request);
}
