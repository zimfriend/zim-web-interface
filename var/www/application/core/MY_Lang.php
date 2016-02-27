<?php

(defined('BASEPATH')) OR exit('No direct script access allowed');

/*
 * http://www.maximechaillou.com/creer-un-site-multilingue-avec-codeigniter/
 */

/**
 * Language Identifier
 *
 * Adds a language identifier prefix to all site_url links
 *
 * @copyright        Copyright (c) 2011 Wiredesignz
 * @version          0.30
 * Modified by       Maxime CHAILLOU
 * @add              Language's browser detection
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
class MY_Lang extends CI_Lang {

    function __construct() {

        global $URI, $CFG, $IN;

        $config = & $CFG->config;

        //$index_page    = $config['index_page'];
        $index_page = '';
        $lang_hide = $config['lang_hide'];
        $default_abbr = $config['language_abbr'];
        $lang_uri_abbr = $config['lang_uri_abbr'];

        /* get the language abbreviation from uri */
        $uri_abbr = $URI->segment(1);

        /* adjust the uri string leading slash */
        $URI->uri_string = preg_replace("|^\/?|", '/', $URI->uri_string);

        if ($lang_hide) {

            if (isset($lang_uri_abbr[$uri_abbr])) {

                /* set the language_abbreviation cookie */
                $IN->set_cookie('user_lang', $uri_abbr, $config['sess_expiration']);
            } else {

                /* get the language_abbreviation from cookie */
                $lang_abbr = $IN->cookie($config['cookie_prefix'] . 'user_lang');
            }

            if (strlen($uri_abbr) == 2) {

                /* reset the uri identifier */
                $index_page .= empty($index_page) ? '' : '/';

                /* remove the invalid abbreviation */
                $URI->uri_string = preg_replace("|^\/?$uri_abbr\/?|", '', $URI->uri_string);

                /* redirect */
                header('Location: ' . $config['base_url'] . $index_page . $URI->uri_string);
                exit;
            }
        } else {

            /* set the language abbreviation */
            $lang_abbr = $uri_abbr;
        }
      

        /* check validity against config array */
        if (isset($lang_uri_abbr[$lang_abbr])) {

            /* reset uri segments and uri string */
            $URI->_reindex_segments(array_shift($URI->segments));
            $URI->uri_string = preg_replace("|^\/?$lang_abbr|", '', $URI->uri_string);

            /* set config language values to match the user language */
            $config['language'] = $lang_uri_abbr[$lang_abbr];
            $config['language_abbr'] = $lang_abbr;

            /* if abbreviation is not ignored */
            if (!$lang_hide) {

                /* check and set the uri identifier */
                $index_page .= empty($index_page) ? $lang_abbr : "/$lang_abbr";

                /* reset the index_page value */
                $config['index_page'] = $index_page;
            }

            /* set the language_abbreviation cookie */
            $IN->set_cookie('user_lang', $lang_abbr, $config['sess_expiration']);
        } else {

        	/* if abbreviation is not ignored */
             if (!$lang_hide) {

                /* Check the browser's language */
                $lang_browser = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
                if (isset($lang_uri_abbr[$lang_browser])) {
                    $index_page .= empty($index_page) ? $lang_browser : "/$lang_browser";
                } else {
                    /* check and set the uri identifier to the default value */
                    $index_page .= empty($index_page) ? $default_abbr : "/$default_abbr";
                }

                if (strlen($lang_abbr) == 2) {

                    /* remove invalid abbreviation */
                    $URI->uri_string = preg_replace("|^\/?$lang_abbr|", '', $URI->uri_string);
                }

                /* redirect */
                header('Location: ' . $config['base_url'] . $index_page . $URI->uri_string);
                exit;
             }

             /* Check the browser's language */
             // use default language if we do not receive language setting
             $lang_browser = (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) ? substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : $default_abbr;
             if (isset($lang_uri_abbr[$lang_browser])) {
             	$lang_abbr = $lang_browser;
             } else {
             	$lang_abbr = $default_abbr;
             }
             	
            /* set the language_abbreviation cookie */
			$IN->set_cookie('user_lang', $lang_abbr, $config['sess_expiration']);

            /* set config language values to match the user language */
            $config['language'] = $lang_uri_abbr[$lang_abbr];
            $config['language_abbr'] = $lang_abbr;
        }

        log_message('debug', "Language_Identifier Class Initialized");
    }

}

/* translate helper */

function t($line, $arg = NULL) {
    global $LANG;
    
//     $t = $LANG->line($line);
//     return $t ? vsprintf($t, $arg) : $line;
    
     return ($t = $LANG->line($line)) ? vsprintf($t, $arg) : $line;
}

/* message translate helper */

function message($arr) {
    if (!array_key_exists("Context", $arr) or !array_key_exists("Id", $arr)) {
        // Malformed message
        return implode("-", $arr);
    } else {
        global $CFG;

        $json = @file_get_contents($CFG->config['hardconf'] . $CFG->config['language_abbr'] . "/" . $arr["Context"] . ".json");
        if ($json === false) {
            // Can't find message file
            if ($CFG->config['language_abbr'] == "en") {
                // The english version can't be found
                return implode("-", $arr);
            } else {
                // Try english version
                $json = @file_get_contents($CFG->config['hardconf'] . 'en\\' . $arr["Context"] . ".json");
                if ($json === false) {
                    // The english version can't be found
                    return implode("-", $arr);
                }
            }
        }
        // Json decoding
        $messages = json_decode($json, true);
        if ($messages === null or !array_key_exists($arr["Id"], $messages)) {
            // Json decoding error
            return implode("-", $arr);
        } else {
            return $messages[$arr["Id"]];
        }
    }
}