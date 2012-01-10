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
 * @package  prggmrunit
 * @copyright  Copyright (c), 2010-12 Nickolas Whiting
 */

/**
 * Template definition class.
 *
 * This class soley defines the templates used when generating the docs.
 * 
 * The definitions DO NOT define the locations of the templates this is up to
 * the output generation object.
 */
class Templates {
    const BASE = 0xD0000;
    const CHAPTER = 0xD0001;
    const SECTION = 0xD0002;
    const PAGE = 0xD0003; 
}