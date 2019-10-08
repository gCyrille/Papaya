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
class Service_message {

	protected $CI;
	
	// array compatible with Semantic Helper
	protected $service_msg = NULL;

	// We'll use a constructor, as you can't directly call a function
	// from a property definition.
	public function __construct()
	{
			// Assign the CodeIgniter super-object
			$this->CI =& get_instance();
	}
	
	public function load()
	{
		$input = $this->CI->input->get('msg');
		if ($input != NULL) 
		{
			$msg_base64 = rawurldecode($input);
			$msg_decoded = base64_decode($msg_base64);
			
			if ($msg_decoded !== FALSE)
			{
				$msg = unserialize($msg_decoded);
				if ($msg !== FALSE && array_key_exists('title', $msg))
				{
					// If there not time or the delta < 20s, display msg
					if ( ! array_key_exists('time', $msg) OR time() < ($msg['time'] + 20))
					{
						$this->service_msg = $msg;
						return $msg;
					}
				}
			}
		}
		return NULL;
	}
	
	public function set($array)
	{
		$this->service_msg = $array;
	}
	
	public function as_url_param($array)
	{
		return rawurlencode(base64_encode(serialize($array)));
	}
	
	public function to_html($opening_tag='', $closing_tag='')
	{
		if ($this->service_msg != NULL)
		{
			$this->CI->load->helper('ui');
			return $opening_tag.ui_message($this->service_msg).$closing_tag;
		} else {
			return NULL;
		}
	}
}
