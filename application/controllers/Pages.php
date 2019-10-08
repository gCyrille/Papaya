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
class Pages extends CI_Controller {

	public function view($page = 'home')
	{
		if( ! file_exists(APPPATH.'views/pages/'.$page.'.php'))
		{
			// Cannot fint the page. Use default CodeIgniter 404 page!
			show_404();
		}

		$this->load->helper('url');

		// Otherwise display the page.

		// Fill datas for the template pages
		$data['title'] = ucfirst($page); // Capitalize first letter

		// Load the templates in order with the data to print
		$this->load->view('templates/header', $data);
		$this->load->view('pages/'.$page, $data);
		$this->load->view('templates/footer', $data);
	}
	
	/**
	 * Display the about page
	 */
	public function about()
	{
		$data['title'] = "About Papaya";

		$this->load->view('templates/header', $data);
		$this->load->view('admin/about', $data);
		$this->load->view('templates/footer', $data);	
	}
	
	public function home()
	{
		$this->load->model('lists_model');
		$this->load->helper('ui');
		$this->load->model('orders_model');
		$this->load->model('accounting_model');
		
		// Fill datas for the template pages
		$data['title'] = 'Papaya - Deliveries management';
		
		// Show service messages from other pages
		$this->service_message->load();
		
		$data['lists_items'] = array();
		$lists = $this->lists_model->get_list();
		foreach($lists as $list)
		{
			// Get deliveries
			$content = '';
			$deliveries = $this->lists_model->get_deliveries($list->id);
			if (count($deliveries) > 0)
			{
				foreach ($deliveries as $delivery)
				{
					if ($delivery->status != 'closed')
					{
						$delivery->order_count = $this->orders_model->get_count($delivery->id);
						$delivery->payment_count = $delivery->order_count - $this->accounting_model->get_count($delivery->id);
						$content .= ui_delivery_steps($delivery);
					}
				}
				
				
			}
			
			if (empty($content))
			{
				$content .= '<div class="ui secondary segment"><div class="ui large ribbon grey label">No delivery</div></div>';
			}
			
			$data['lists_items'][] = array (
				'id' 			=> $list->id,
				'name' 			=> $list->name,
				'deliveries'	=> $content
			);
		}
		
		$data['week_days'] = week_info()['days'];
		
		// Load the templates in order with the data to print
		$this->load->view('templates/header', $data);
		$this->load->view('pages/home', $data);
		$this->load->view('templates/footer', $data);
	}
	
}
