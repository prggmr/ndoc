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
 * Section object
 */
class Section extends Chapter {
    
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
        return true;
    }
    
}