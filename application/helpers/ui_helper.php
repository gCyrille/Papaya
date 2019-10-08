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
 * Semantic UI Helpers
 */

// ------------------------------------------------------------------------

if ( ! function_exists('message'))
{
	/**
	 * Create dismissable message
	 *
	 *
	 * @param	mixed	string in ('error', 'success', 'info', 'warning')
	 					or array with all params
	 * @param	string
	 * @param	string
	 * @return	string	generated html
	 */
	function ui_message($type, $title='', $content='', $time=NULL)
	{
		if ($time == NULL)
		{
			$time = time();
		}
		if (is_array($type))
		{
			$msg = $type;	
		} 
		else
		{
			$msg = array(
				'type' 		=> $type,
				'title'		=> $title,
				'content'	=> $content,
				'time'		=> $time
			);
		}
		$CI =& get_instance();
		
		$CI->load->library('parser');
		if (in_array($msg['type'], array('error', 'success', 'info', 'warning')))
		{
			$msg_html = $CI->parser->parse('semantic-ui/message', $msg, TRUE);
			return $msg_html;
		} 
		else 
		{
			return NULL;
		}
	}
}

// ------------------------------------------------------------------------

/**
 * Papaya UI Helpers
 */

// ------------------------------------------------------------------------

if ( ! function_exists('delivery_steps'))
{
	/**
	 * Create dismissable message
	 *
	 *
	 * @param	mixed	string in ('error', 'success', 'info', 'warning')
	 					or array with all params
	 * @param	string
	 * @param	string
	 * @return	string	generated html
	 */
	function ui_delivery_steps(stdClass $delivery)
	{
		$CI =& get_instance();
		
		$CI->load->library('parser');
		$CI->load->helper('date');
		
		$date = new DateTime($delivery->delivery_date);
		switch ($delivery->status)
		{
			case 'collect':
				$content = array(
					'date' 			=> $date->format('d F Y'),
					'nb_orders'		=> $delivery->order_count,
					'url_details' 	=> base_url('deliveries/view/'.$delivery->id),
					'url_add_order'	=> base_url('deliveries/add_order/'.$delivery->id)
					);

				return $CI->parser->parse('deliveries/tpl_collect', $content, TRUE);
			case 'prepare':
				$content = array(
					'date' 			=> $date->format('d F Y'),
					'nb_orders'		=> $delivery->order_count,
					'sunset'		=> date_sunset($date->getTimestamp(), SUNFUNCS_RET_STRING, -13.42397, 32.09368, 96, +2),
					'url_details' 	=> base_url('deliveries/view/'.$delivery->id),
					'url_print_invoices'	=> base_url('invoices/print_all/'.$delivery->id)
					);

				return $CI->parser->parse('deliveries/tpl_prepare', $content, TRUE);
			case 'accounting':
				$content = array(
					'date' 				=> $date->format('d F Y'),
					'nb_payments'		=> $delivery->payment_count,
					'url_details' 		=> base_url('deliveries/view/'.$delivery->id),
					'url_add_payment'	=> base_url('accounting/register_all/'.$delivery->id)
					);
				return $CI->parser->parse('deliveries/tpl_accounting', $content, TRUE);
			case 'closed':
				$content = array(
					'date' 				=> $date->format('d F Y'),
					'total_paid'		=> format_kwacha($delivery->total_paid),
					'url_details' 		=> base_url('deliveries/view/'.$delivery->id),
					'accounting_url'	=> base_url('accounting/view/'.$delivery->id)
					);
				return $CI->parser->parse('deliveries/tpl_closed', $content, TRUE);
		}
	}
}
