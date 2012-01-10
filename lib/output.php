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
 * Output Generation Class
 *
 */
 class Output implements Output_Generator {

     /**
      * Output generator
      *
      * @var  object
      */
     protected $_generator = null;

     /**
      * Default generator used.
      *
      * @var string
      */
     public static $default_format = 'html';
     
     /**
      * List of avaliable output types and their associated generators.
      *
      * @var  array
      */
     public static $output_formats = array(
        'html' => 'php'
     );

     /**
      * Initalizes output.
      *
      * @param  string  $format  Output generation format object
      *
      * @return   void
      */
     public function __construct($format = null)
     {
         if (null === $format) {
             $format = static::$default_format;
         }
         if (is_string($format)) {
             $format = static::$output_formats[$format];
             // first startup
             $file = sprintf(
                 '%s/output/%s.php',
                 dirname(realpath(__FILE__)),
                 $format
             );
             // attempt to load
             if (file_exists($file)) {
                 require_once $file;
             } else {
                 throw new \Exception(
                     'Could not load default output format, output generation failed'
                 );
             }
             $this->_generator = new \ndoc\Output\PHP();
         } else {
             $this->_generator = $format;
         }
     }
     
    /**
     * Outputs a generated file.
     *
     * @param  string  $template  Template file.
     * @param  string  $source  Source file to send output.
     * @param  array  $vars  Array of variables to pass to template.
     */
    public function generate($template, $source, $vars)
    {
        // Signal the parsing of the document
        fire(\ndoc\Signals::DOC_PARSE, array(
            &$template, $vars
        ));
        // Fire the generation of the document
        fire(\ndoc\Signals::DOC_GENERATE, array(
            $template, $source
        ));
    }
}

 /**
  * Output Generator
  */
 interface Output_Generator {

     /**
      * Outputs a generated file.
      *
      * @param  string  $template  Template file.
      * @param  string  $source  Source file to send output.
      * @param  array  $vars  Array of variables to pass to template.
      */
     public function generate($template, $source, $vars);
 }
