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
class Joy_Array implements IteratorAggregate, Iterator, ArrayAccess, Countable
{
    private $_position = 0;
    private $_array;

    public function __construct($array=array()) 
    {
        if (!is_array($array)) {
            throw new Joy_Exception("Joy_Array parameter must be array value");
        }

        $this->_position = 0;
        $this->_array = $array;
    }

    public static function merge_recursive_distinct ( array &$array1, array &$array2 )
    {
        $merged = $array1;

        foreach ( $array2 as $key => &$value )
        {
            if ( is_array ( $value ) && isset ( $merged [$key] ) && is_array ( $merged [$key] ) )
            {
                $merged [$key] = self::merge_recursive_distinct ( $merged [$key], $value );
            }
            else
            {
                $merged [$key] = $value;
            }
        }

        return $merged;
    }

    public function merge($_array=array(), $root="")
    {
        $this->_array = self::merge_recursive_distinct($this->_array, $_array); 
    }

    public function setPath($path, $value)
    {
        $items = split(DIRECTORY_SEPARATOR, $path);
        $this->_array = self::merge_recursive_distinct($this->_array, $this->setElement($items, $value));
    }

    public function getPath($path)
    {
        $items = split(DIRECTORY_SEPARATOR, $path);
        return $this->getElement($items, $this->_array);
    }

    private function getElement(&$items, $data)
    {
        return ($key = array_shift($items)) ? $this->getElement($items, $data[$key]) : $data;
    }

    private function setElement(&$items, $value)
    {
        return ($key = array_pop($items)) ? $this->setElement($items, array($key=>$value)) : $value;
    }

    // Iterator Implement
    public function rewind() 
    {
        $this->_position = 0;
    }

    public function current() 
    {
        return $this->_array[$this->_position];
    }

    public function key() 
    {
        return $this->_position;
    }

    public function next() 
    {
        ++$this->_position;
    }

    public function valid() 
    {
        return isset($this->_array[$this->_position]);
    }

    // ArrayAccess Implements
    public function offsetSet($offset, $value) 
    {
        $this->_array[$offset] = $value;
    }

    public function offsetExists($offset) 
    {
        return isset($this->_array[$offset]);
    }

    public function offsetUnset($offset) 
    {
        unset($this->_array[$offset]);
    }

    public function offsetGet($offset) 
    {
        return isset($this->_array[$offset]) ? $this->_array[$offset] : null;
    }

    // Countable Implements
    public function count()
    {
        return count($this->_array);
    }

    // IteratorAggregate
    public function getIterator() 
    {
        return new ArrayIterator((object)$this->_array);
    }
}


