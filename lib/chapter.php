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
 * Chapter object
 */
class Chapter extends Node {
    
    /**
     * Sections 
     * 
     * @param  object
     */
    protected $_sections = null;
    
    /**
     * Pages
     *
     * @param  object
     */
    protected $_pages = null;
    
    /**
     * Constructs the chapter object.
     * 
     * @return  void
     */
    public function __construct($source)
    {
        parent::__construct($source);
        // setup pages and section stacks
        $this->_pages = new \ArrayObject();
        $this->_sections = new \ArrayObject();
        
        // Index
        \ndoc::index($this, $source, \ndoc::SECTION);
    }
    
    /**
     * Returns if node is a chapter.
     *
     * @return  boolean
     */
    public function isChapter()
    {
        return true;
    }
    
    /**
     * Adds a new section
     *
     * @param  string  $source  Source to the section.
     *
     * @return  void
     */
    public function addSection(Section $source)
    {
        fire(Signals::NEW_SECTION, array($source, $this));
        $this->_sections->append($source);
    }

    
    /**
     * Adds a new page
     *
     * @param  string  $source  Source to the page.
     *
     * @return  void
     */
    public function addPage(Page $source)
    {
        fire(Signals::NEW_PAGE, array($source, $this));
        $this->_pages->append($source);
    }
    
    /**
     * Returns the sections.
     *
     * @return  object  ArrayObject
     */
    public function getSections()
    {
        return $this->_sections;
    }
    
    /**
     * Returns the pages.
     *
     * @return  object  ArrayObject
     */
    public function getPages()
    {
        return $this->_pages;
    }
}