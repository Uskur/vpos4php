<?php
/**
 * Joy Web Framework
 *
 * Copyright (c) 2008-2009 Netology Foundation (http://www.netology.org)
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
 * @package     Joy
 * @author      Hasan Ozgan <meddah@netology.org>
 * @copyright   2008-2009 Netology Foundation (http://www.netology.org)
 * @license     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @version     $Id$
 * @link        http://joy.netology.org
 * @since       0.5
 */
class Joy_Reflection
{
    protected $_reflection;
    protected $_class;

    public function __construct($class)
    {
        if (is_null($class)) {
            throw new Joy_Exception_NotFound_Class($class);
        }

        $this->_class = $class;
        $this->_reflection = new ReflectionClass($class);
    }

    public function isA($class)
    {
        if (in_array($class, $this->_reflection->getInterfaceNames())) {
            return true;
        }
        else if ($this->_reflection->isSubclassOf($class)) {
            return true;
        }

        return false;
    }

    public function newInstance($args=array()) 
    {
        return (empty($args)) 
            ? $this->_reflection->newInstance()
            : $this->_reflection->newInstanceArgs($args);
    }
}
