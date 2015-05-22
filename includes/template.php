<?php
// $Id$

/**
 * This class provides template support for backend.
 * 
 * Simple PHP templates are used to provide a custom user interface
 * and text. Templates are written in pure PHP, and can make use of 
 * many PHP facilities. But only a small subset of available variables
 * are passed into the template.
 * 
 * When writing templates, you are encouraged to use the absolute
 * minimum amount of PHP code possible. Mainly, you should be printing
 * variables.
 * 
 * Use templates as a way of generating a user interface without having
 * to edit significant amounts of code.
 * @file
 * @package TemplateLib
 */

/**
 * This is the main class responsible for handling templates.
 * 
 * It provides tools for rendering templates, including one
 * template inside of another, and controlling the output of templates.
 *
 */
class Template {

    private $_available_languages = array();
    private $_template_path = './templates';
    private $_request_language_attr = 'language';
  
    /**
     * Create a new Template object.
     * 
     * Assing a new default path to this Template object. Templates will be 
     * loaded from this directory unless the template passed to the renders is
     * an absolute path.
     *
     * @param string $template_path         Path to template directory.
     * @param array  $available_languages   Language Templates available
     * @param string $request_language_attr Request attribute for language override
     */
    public function __construct($template_path = null, $available_languages = null, $request_language_attr = null) {
        if (!is_null($template_path)) {
            $this->_template_path = $template_path;
        }
        if (!is_null($available_languages)) {
            $this->_available_languages = $available_languages;
        }
        if (!is_null($request_language_attr)) {
            $this->_request_language_attr = $request_language_attr;
        }
    }
  
    /**
     * Render content into a template and return the string.
     * 
     * Given a template, render variables into the template. This will return the
     * rendered template as a string. Some other process will be responsible for
     * sending this back to the client.
     * 
     * NOTE: This is based on the template rendering system in Caryatid 
     * ({@see http://aleph-null.tv}).
     * 
     * Example:
     * <code>
     * $t = new Template();
     * $args = array(
     *   'myVar' => 'Hello World', // Available in the template as $myVar
     *   'myOtherVar' => 'Another string.', // Available in the template as $myOtherVar
     * );
     * $contents = $t->render($myTemplate, $args);
     * print $contents;
     * </code>
     * 
     * @param string $template Path to the template to render. If the path is relative,
     *   the default template path will be used.
     * @param array $args Associative array of arguments that will be imported into 
     * the template's namespace. 
     * @return String containing the rendered template contents.
     */
    public function render($template, $args = array()) {
        // This is done primarily for backward compatibility.
        // Headers might get sent here.
        // deactivated because of various problems with blank pages.
        //@ob_end_flush(); 
    
        ob_start();
        $template = $this->getRealPath($template);
        if ($template !== false) {
            // This use of extract() comes from Drupal 6's code:
            if(count($args) > 0) {
                extract($args, EXTR_SKIP);
            }
            include($template);
        }
        $r = ob_get_contents();
        ob_end_clean();
        return $r;
    }
  
    /**
     * Include a template from within another template.
     * 
     * This does not buffer output. Instead, it sends it straight to any 
     * existing output buffer. For that reason, it should be used 
     * primarily from within another template.
     *
     * @param string $template Path to template file. If the path does not have a slash
     *   in it, it is assumed to be relative to the default template path.
     * @param array $args Zero or more variables that should be added to this template.
     *
     * @see Template::render($template)
     */
    public function includeTemplate($template, $args = array()) {
        $template = $this->getRealPath($template);
        if ($template !== false) {
            if (count($args) > 0) {
                extract($args, EXTR_SKIP);
            }
            include($template);
        }
    }

    /**
     * Determine which language out of an available set the user prefers most
     *
     * @param array  $available_languages   language-tag-strings (must be lowercase) that are available
     * @param string $http_accept_language  a HTTP_ACCEPT_LANGUAGE string (default: $_SERVER['HTTP_ACCEPT_LANGUAGE'])
     * @param string $request_language_attr override http language by this request attribute if present
     *
     * @return string best language match
     */
    public static function preferred_language($available_languages, $http_accept_language="auto", $request_language_attr='language') {

        // if $http_accept_language was left out, read it from the HTTP-Header
        if ($http_accept_language == "auto") {
            $http_accept_language = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '';
        }

        // use $request_language_attr if we can
        if (!empty($request_language_attr) && isset($_REQUEST[$request_language_attr])) {
            $http_accept_language = $_REQUEST[$request_language_attr] . ', ' . $http_accept_language;
        }

        // standard  for HTTP_ACCEPT_LANGUAGE is defined under
        // http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.4
        // pattern to find is therefore something like this:
        //    1#( language-range [ ";" "q" "=" qvalue ] )
        // where:
        //    language-range  = ( ( 1*8ALPHA *( "-" 1*8ALPHA ) ) | "*" )
        //    qvalue         = ( "0" [ "." 0*3DIGIT ] )
        //            | ( "1" [ "." 0*3("0") ] )
        preg_match_all(
            "/([[:alpha:]]{1,8})(-([[:alpha:]|-]{1,8}))?" .
            "(\s*;\s*q\s*=\s*(1\.0{0,3}|0\.\d{0,3}))?\s*(,|$)/i",
            $http_accept_language, $hits, PREG_SET_ORDER
        );

        // default language (in case of no hits) is the first in the array
        $bestlang = $available_languages[0];
        $bestqval = 0;

        foreach ($hits as $arr) {
            // read data from the array of this hit
            $langprefix = strtolower ($arr[1]);
            if (!empty($arr[3])) {
                $langrange = strtolower ($arr[3]);
                $language = $langprefix . "-" . $langrange;
            } else {
                $language = $langprefix;
            }
            $qvalue = 1.0;
            if (!empty($arr[5])) {
                $qvalue = floatval($arr[5]);
            }
     
            // find q-maximal language 
            if (in_array($language,$available_languages) && ($qvalue > $bestqval)) {
                $bestlang = $language;
                $bestqval = $qvalue;
            } else if (in_array($langprefix,$available_languages) && (($qvalue*0.9) > $bestqval)) {
                // if no direct hit, try the prefix only but decrease q-value by 10% (as http_negotiate_language does)
                $bestlang = $langprefix;
                $bestqval = $qvalue*0.9;
            }
        }
        return $bestlang;
    } 
  
    /**
     * Given a template, determine its absolute path on the file system.
     *
     * @param string $template the template to use
     *
     * @return string The full path to the template.
     */
    private function getRealPath($template) {
    
        // Chop of leading dot
        if (strpos($template, '../') === 0) {
            $template = substr($template, 1);
        }
    
        if(strpos($template, '/') === 0) {
            // path is absolute
            $final_template = $template;
        } else {
            // Path is relative

            // check if we have several languages available...
            if (!empty($this->_available_languages)) {

                // find all available templates
                $template_languages = array();
                foreach ($this->_available_languages as $lang) {
                    $template_test = $this->_template_path . '/' . $lang . '/' . $template;
                    if (file_exists($template_test)) {
                        $template_languages[] = $lang;
                    }
                }

                // get best templates
                if (empty($template_languages)) {
                    // no language options found, try template path directly
                    $final_template = $this->_template_path . '/' . $template;
                } else {
                    // use language accept header
                    $lang = self::preferred_language($template_languages, 'auto', $this->_request_language_attr);
                    $final_template = $this->_template_path . '/' . $lang . '/' . $template;
                }
            }
        }

        if (!file_exists($final_template)) {
            print "<h1>Template Not Found</h1><p>Content cannot be displayed.</p>";
            return false;
        } else {
            return $final_template;
        }
    }
}
