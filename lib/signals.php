<?php
namespace ndoc;
/**
 *  Copyright 2010-11 Nickolas Whiting
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
 * Events signals.
 */
class Signals {
    const START = 0xD001;
    const END   = 0xD002;
    const INDEX = 0XD003;
    const NEW_CHAPTER = 0xD004;
    const NEW_SECTION = 0xD005;
    const NEW_PAGE = 0xD006;
    const INDEX_GENERATION = 0xD007;
}