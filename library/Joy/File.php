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
class Joy_File
{
    private $_path;
    private $_file;
    private $_directory;
    private $_extension;

    public function __construct($path) 
    {
        $this->parseFile($path);
    }

    private function parseFile($path)
    {
        $this->_path = rtrim($path, DIRECTORY_SEPARATOR);
        if (!is_file($this->_path)) { throw new Joy_Exception_NotFound_File("File not found ({$path})"); }

        $this->_directory = realpath(dirname($this->_path));
        $this->_file = array_shift(array_reverse(split(DIRECTORY_SEPARATOR, $this->_path)));
        $this->_file_extension = substr(strchr($this->_file, '.'), 1);
        $this->_extension = array_shift(array_reverse(split('\.', $this->_file_extension)));
    }

    public function __toString()
    {
        return $this->getPath();
    }

    public function getFile()
    {
        return $this->_file;
    }

    public function getPath()
    {
        return $this->_path;
    }

    public function getDirectory()
    {
        return $this->_directory;
    }

    public function getExtension()
    {
        return $this->_extension;
    }

    public function getType()
    {
        switch ($this->getExtension()) 
        {
            case Joy_File_Extension::MANIFEST:
            case Joy_File_Extension::CONFIG:
            case Joy_File_Extension::YAML:
                return Joy_File_Type::YAML;

            case Joy_File_Extension::INI:
            case Joy_File_Extension::LOCALE:
                return Joy_File_Type::INI;

            default: 
                return Joy_File_Type::UNKNOWN;
        }
    }

    public function dump()
    {
        $reader = $this->getReader();

        return $reader->toString();
    }

    public function getSize()
    {
        return filesize($this->getPath());
    }

    public function execute()
    {
        //TODO: Is execute file?!!?? Security etc...
    }

    public function getReader()
    {
        return Joy_File_Reader::factory($this->getPath());
    }
}
