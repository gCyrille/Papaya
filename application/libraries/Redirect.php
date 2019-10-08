<?php
/**
 * Papaya
 *
 * An open source application to help in the managment of vegetable deliveries.
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2018 - 2019, Chikowa Youth Development Centre
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
 *
 * @author	Cyrille Gandon
 * @copyright	Copyright (c) 2018 - 2019, Chikowa Youth Development Centre
 * @license	http://opensource.org/licenses/MIT	MIT License
 */
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Service message class
 *
 * Provides a way to exchange messages between pages and 
 * to display it with Semantic UI message class.
 *
 * @subpackage	Libraries
 * @category	Libraries
 */
class Redirect {

	protected $CI;
	

	public function __construct()
	{
			// Assign the CodeIgniter super-object
			$this->CI =& get_instance();
	}
	
	/**
	 * Return hidden HTML field to store the "rdtfrom" parameter
	 *
	 * @return string
	 */
	public function from_field()
	{
		$this->CI->load->helper('form_helper');
		
		$from = $this->CI->input->post_get('rdtfrom');
		if($from != NULL && ! empty($from))
		{
			return form_hidden('rdtfrom', $from);
		}
		else
		{
			return '';
		}
	}
	
	/**
	 * Use CI redirect method but add url parameters and service message
	 * If rdtfrom exists, it will redirect to this page.
	 *
	 * @param string URI string
	 * @param mixed array() an associative or indexed array or a encoded url string
	 * @param array with service message properties
	 * @param bool forward from parameter to the new URI
	 *
	 */
	public function to($url, $params=NULL, array $service_msg=NULL, $fwd_from=FALSE)
	{
		redirect($this->build_url($url, $params, $service_msg, $fwd_from));
	}
	
	/**
	 * Return url builded with parameters and service message
	 * If rdtfrom exists, it will return this page.
	 *
	 * @param string URI string
	 * @param mixed array() an associative or indexed array or a encoded url string
	 * @param array with service message properties
	 * @param bool forward from parameter to the new URI
	 *
	 */
	public function build_url($url, $params=NULL, array $service_msg=NULL, $fwd_from=FALSE)
	{
		$query = '';
		$hash = FALSE;
		$from = $this->CI->input->post_get('rdtfrom');
		
		if ($params != NULL)
		{
			if (!is_array($params))
			{
				if (is_string($params))
				{
					$hash = substr($params, strrpos($params, '#') + 1);
					parse_str($params, $params);
				} else {
					$params = array();
				}
			}
		} 
		else
		{
			$params = array();
		}
		
		if ($service_msg != NULL)
		{
			$params['msg'] = $this->CI->service_message->as_url_param($service_msg);
		}
		
		if($from != NULL && ! empty($from) && $fwd_from === TRUE)
		{
			$params['rdtfrom']  = $from;
		}
		
		$url_parsed = parse_url($url);
		
		if(isset($url_parsed['query']))
		{
			$p = parse_str($url_parsed['query'], $q);
			$params = array_merge($params, $q);
		}
		
		$url_parsed['query'] = http_build_query($params);
		
		if ($hash !== FALSE)
		{
			$url_parsed['fragment'] = $hash;
		}
		
		if($from != NULL && ! empty($from) && $fwd_from === FALSE)
		{
			$url_parsed['path'] = $from;
		}
		
		return $this->glue_url($url_parsed);
	}
	
	/**
	 * Copied from example (@link php.net/manual/en/function.parse-url.html)
	 *
	 * @param array Associative array of parsed URI from parse_url method
	 *
	 * @return string URL
	 */
	private function glue_url($parsed) 
	{
		if (!is_array($parsed)) 
		{
			return false;
		}

		$uri = isset($parsed['scheme']) ? $parsed['scheme'].':'.((strtolower($parsed['scheme']) == 'mailto') ? '' : '//') : '';
		$uri .= isset($parsed['user']) ? $parsed['user'].(isset($parsed['pass']) ? ':'.$parsed['pass'] : '').'@' : '';
		$uri .= isset($parsed['host']) ? $parsed['host'] : '';
		$uri .= isset($parsed['port']) ? ':'.$parsed['port'] : '';

		if (isset($parsed['path']))
		{
			$uri .= (substr($parsed['path'], 0, 1) == '/') ? $parsed['path'] : ((!empty($uri) ? '/' : '' ) . $parsed['path']);
		}

		$uri .= isset($parsed['query']) ? '?'.$parsed['query'] : '';
		$uri .= isset($parsed['fragment']) ? '#'.$parsed['fragment'] : '';

		return $uri;
	}
}
