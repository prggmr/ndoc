<?php
namespace ndoc;
/**
 *  Copyright 2010-12 Nickolas Whiting
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 *
 *
 * @author  Nickolas Whiting  <prggmr@gmail.com>
 * @package  ndoc
 * @copyright  Copyright (c), 2010-12 Nickolas Whiting
 */
 
/**
 * A node is the object representing a chapter, section or page.
 */
abstract class Node {
    
    /**
     * Source location of the node.
     *
     * @var  string
     */
    protected $_source = null;
    
    /**
     * Constructs a new node.
     *
     * @param  source
     */
     public function __construct($source)
     {
         $this->_source = $source;
     }
    
    /**
     * Returns if node is a chapter.
     *
     * @return  boolean
     */
    public function isChapter()
    {
        return false;
    }
    
    /**
     * Returns if node is a section.
     *
     * @return  boolean
     */
    public function isSection()
    {
        return false;
    }
    
    /**
     * Returns if node is a page.
     *
     * @return  boolean
     */
    public function isPage()
    {
        return false;
    }
    
    /**
     * Returns the source of the node.
     *
     * @return  string
     */
    public function getSource()
    {
        return $this->_source;
    }
}