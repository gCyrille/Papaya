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
class Lists extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('lists_model');
	}
	
	public function list()
	{
		$this->load->model('orders_model');
		$this->load->model('accounting_model');
		$this->load->helper('ui');
		
		// Fill datas for the template pages
		$data['title'] = 'Delivery lists';
		
		// Show service messages from other pages
		$this->service_message->load();
		
		$data['lists_items'] = array();
		$lists_count = $this->lists_model->get_customer_counts();
		$lists = $this->lists_model->get_list();
		foreach($lists as $list)
		{
			// Get deliveries
			$content = '';
			$deliveries = $this->lists_model->get_deliveries($list->id);
			$closed = NULL;
			foreach ($deliveries as $delivery)
			{
				if ($delivery->status != 'closed')
				{
					$delivery->order_count = $this->orders_model->get_count($delivery->id);
					$delivery->payment_count = $delivery->order_count - $this->accounting_model->get_count($delivery->id);
					$content .= ui_delivery_steps($delivery);
				}
				else
				{
					// Get the last closed delivery
					if ($closed == NULL)
					{
						$closed = $delivery;
					}
				}
			}
			
			$data['lists_items'][] = array (
				'id' 			=> $list->id,
				'name' 			=> $list->name,
				'day_of_week'	=> ucfirst(week_info()['days'][$list->day_of_week]),
				'count'			=> $lists_count[$list->id],
				'previous'		=> ($closed != NULL) ? mysql_to_nice_date($closed->delivery_date) : NULL,
				'previous_link' => ($closed != NULL) ? base_url('deliveries/view/'.$closed->id) : NULL,
				'deliveries'	=> $content
			);
		}
		
		$data['week_days'] = week_info()['days'];
		
		// Load the templates in order with the data to print
		$this->load->view('templates/header', $data);
		$this->load->view('lists/list', $data);
		$this->load->view('templates/footer', $data);
	}
	
	public function view($id=-1, $page=NULL)
	{
		$this->load->helper('form');
		$this->load->helper('ui');
		$this->load->library('pagination');
		$this->load->config('vegetables');
		$this->load->model('orders_model');
		$this->load->model('accounting_model');
		
		$list = $this->lists_model->get($id);
		if ($list == NULL) {
			// Error, customer do not exists
			$encoded_msg = $this->service_message->as_url_param(array (
				'type'		=> 'error',
				'title'		=> '<i class="attention icon"></i> Impossible to find the list',
				'content'	=> '',
				'time'		=> time()
			));
			redirect('/lists/?msg='.$encoded_msg);
		}
		
		$this->service_message->load();
		
		$list->day_of_week = ucfirst(week_info()['days'][$list->day_of_week]);
		
		$data['title'] = $list->name;
		$data['list'] = $list;
		$data['units'] = $this->config->item('units');
		
		$data['deliveries_open'] = '';
		$data['deliveries_closed'] = '';
		
		// Get deliveries
		$deliveries = $this->lists_model->get_open_deliveries($id);
		foreach ($deliveries as $delivery)
		{
			$delivery->order_count = $this->orders_model->get_count($delivery->id);
			$delivery->payment_count = $delivery->order_count - $this->accounting_model->get_count($delivery->id);
			$data['deliveries_open'] .= ui_delivery_steps($delivery);
		}
		
		// Get closed delivery with "show more"
		$show_more = $this->input->get('more');
		if ($show_more === NULL)
		{
			$show_more = 5;
		}
		
		$deliveries = $this->lists_model->get_closed_deliveries($id, $show_more, 0);
		foreach ($deliveries as $delivery)
		{
			$p = $this->accounting_model->get_where($delivery->id, -1);
			$delivery->total_paid = $p->total_paid;
			$data['deliveries_closed'] .= ui_delivery_steps($delivery);
		}
		
		if (count($deliveries) < $show_more)
		{
			$data['show_more'] = -1;
		}
		else
		{
			$data['show_more'] = $show_more + 5;
		}
		
				
		// Get customer list pagginged
		$c_config['base_url'] = base_url('/lists/view/'.$id);
		//$c_config['reuse_query_string'] = TRUE;
		$c_config['prefix'] = 'cpage';
		$c_config['suffix'] = '#customers';
		$c_config['total_rows'] = $this->lists_model->get_customers_count($id);
		$c_config['first_url'] = base_url('/lists/view/'.$id.'#customers');
		$this->pagination->initialize($c_config);
		$data['c_pagination'] = $this->pagination->create_links();
		
		$customers = $this->lists_model->get_customers($id, $this->pagination->per_page, $this->pagination->per_page * max($this->pagination->cur_page - 1, 0));
		
		foreach ($customers as $c)
		{
			$c->total_unpaid = $this->customers_model->get_total_unpaid($c->id);
		}
		$data['customers'] = $customers;
		
		// Get vegetables list pagginged
		$v_config['base_url'] = base_url('/lists/view/'.$id);
		//$v_config['reuse_query_string'] = TRUE;
		$v_config['prefix'] = 'vpage';
		$v_config['suffix'] = '#vegetables';
		$v_config['total_rows'] = $this->lists_model->get_vegetables_count($id);
		$v_config['first_url'] = base_url('/lists/view/'.$id.'#vegetables');
		$this->pagination->cur_page = NULL;
		$this->pagination->initialize($v_config);
		$data['v_pagination'] = $this->pagination->create_links();
		$data['vegetables'] = $this->lists_model->get_vegetables($id, $this->pagination->per_page, $this->pagination->per_page * max($this->pagination->cur_page - 1, 0));

		// Create list with only unsuscribed customer for add action
		$all_customers = $this->customers_model->get_list();
		$data['add_customers_list'] = array_diff_assoc(
			array_column($all_customers, 'name', 'id'), 
			array_column($data['customers'], 'name', 'id')
		);
		
		// Load templates
		$this->load->view('templates/header', $data);
		$this->load->view('lists/view', $data);
		$this->load->view('templates/footer', $data);
	}

	public function create()
	{
		$this->load->helper('form');
		$this->load->library('form_validation');
		$this->load->model('customers_model');
		
		$this->service_message->load();
		
		$list = array ('id' => -1, 'name' => NULL, 'day_of_week' => -1);
		
		if ($this->_run_form_validation() === TRUE) 
		{
			// Save vegetable into db
			$id = $this->lists_model->create();
			if ($id !== FALSE)
			{
				// Success, go back to list and show message
				$list = $this->lists_model->get($id);
				$encoded_msg = $this->service_message->as_url_param(array (
					'type'		=> 'success',
					'title'		=> '<i class="check circle icon"></i> '.$list->name.' successfully created!',
					'content'	=> ''/*'<br /><a class="ui basic button" href="'.base_url('/lists/view/'.$list->id).'">View details</a>'*/,
					'time'		=> time()
				));

				redirect('/lists/edit_vegetables/'.$list->id.'/?msg='.$encoded_msg);
				
			}
			else
			{
				// Warning message: not save, retry
				$this->service_message->set(array (
					'type'		=> 'warning',
					'title'		=> '<i class="exclamation triangle icon"></i> Impossible to save!',
					'content'	=> 'Due to an internal error, the list is not saved.<br/> Please try again.'
				));	
			}
		}
		
		// Load the templates in order with the data to print
		$data['title'] = 'New delivery list';
		$data['submit_btn'] = 'Next';
		$data['is_editing'] = FALSE;
		$data['back_link'] = base_url('/lists/');
		$data['form_url'] = 'lists/create';
		$data['list'] = $list;
		$data['customers'] = array();
		
		// Dropdown lists
		$data['days'] = array_map("ucfirst", week_info()['days']);
		$data['customers_list'] = array_column($this->customers_model->get_list(), 'name', 'id');
		
		$this->load->view('templates/header', $data);
		$this->load->view('lists/edit_form', $data);
		$this->load->view('templates/footer', $data);
	}
	
	public function edit($id=-1)
	{
		$this->load->helper('form');
		$this->load->library('form_validation');
		$this->load->model('customers_model');
		
		$list = $this->lists_model->get($id, TRUE);
		if ($list == NULL) {
			// Error, do not exists
			$encoded_msg = $this->service_message->as_url_param(array (
				'type'		=> 'error',
				'title'		=> '<i class="attention icon"></i> Impossible to find the delivery list',
				'content'	=> '',
				'time'		=> time()
			));
			redirect('/lists/?msg='.$encoded_msg);
		}
		
		if ($this->_run_form_validation() === TRUE) 
		{
			if ($this->lists_model->update($id) === TRUE)
			{
				// Success, go back to list and show message
				$list = $this->lists_model->get($id);
				$encoded_msg = $this->service_message->as_url_param(array (
					'type'		=> 'success',
					'title'		=> '<i class="check circle icon"></i> '.$list->name.' successfully saved!',
					'content'	=> '',
					'time'		=> time()
				));
				redirect('/lists/view/'.$id.'?msg='.$encoded_msg);
			}
			else
			{
				// Warning message: not save, retry
				$this->service_message->set(array (
					'type'		=> 'warning',
					'title'		=> '<i class="exclamation triangle icon"></i> Impossible to save!',
					'content'	=> 'Due to an internal error, the delivery list is not saved.<br/> Please try again.'
				));	
			}
		}
		
		// Load the templates in order with the data to print
		$data['title'] = 'Editing '.$list['name'];
		$data['submit_btn'] = 'Save';
		$data['is_editing'] = TRUE;
		$data['back_link'] = base_url('/lists/view/'.$list['id']);
		$data['form_url'] = 'lists/edit/'.$list['id'];
		$data['list'] = $list;
		$data['customers'] = $this->lists_model->get_customer_ids($id);
		
		// Dropdown lists
		$data['days'] = array_map("ucfirst", week_info()['days']);
		$data['customers_list'] = array_column($this->customers_model->get_list(), 'name', 'id');
		
		$this->load->view('templates/header', $data);
		$this->load->view('lists/edit_form', $data);
		$this->load->view('templates/footer', $data);
	}
	
	public function remove_customer($list_id=-1, $customer_id=-1)
	{
		$list = $this->lists_model->get($list_id);
		if ($list == NULL)
		{
			// Error, list do not exists
			$encoded_msg = $this->service_message->as_url_param(array (
				'type'		=> 'error',
				'title'		=> '<i class="attention icon"></i> Impossible to find the list',
				'content'	=> '',
				'time'		=> time()
			));
			redirect('/lists/?msg='.$encoded_msg);
		}
		
		$this->load->model('customers_model');
		
		$customer = $this->customers_model->get($customer_id);
		if ($customer == NULL)
		{
			$encoded_msg = $this->service_message->as_url_param(array (
				'type'		=> 'error',
				'title'		=> '<i class="attention icon"></i> Impossible to find the user',
				'content'	=> '',
				'time'		=> time()
			));
			redirect('/lists/view/'.$list_id.'/?msg='.$encoded_msg.'#customers');
		}
		
		if ($this->lists_model->remove_customer($list_id, $customer_id) === TRUE)
		{
			// Success, go back to list and show message
			$encoded_msg = $this->service_message->as_url_param(array (
				'type'		=> 'success',
				'title'		=> '<i class="user times icon"></i> '.$customer->name.' successfully removed!',
				'content'	=> '',
				'time'		=> time()
			));
			redirect('/lists/view/'.$list_id.'/?msg='.$encoded_msg.'#customers');
		}
		else
		{
			$encoded_msg = $this->service_message->as_url_param(array (
						'type'		=> 'error',
						'title'		=> '<i class="attention icon"></i> Impossible to remove '.$customer->name,
						'content'	=> '',
						'time'		=> time()
					));
			redirect('/lists/view/'.$list_id.'/?msg='.$encoded_msg.'#customers');
		}
	}
	
	public function add_customers($list_id, $ids_encoded)
	{
		$list = $this->lists_model->get($list_id);
		if ($list == NULL)
		{
			// Error, list do not exists
			$encoded_msg = $this->service_message->as_url_param(array (
				'type'		=> 'error',
				'title'		=> '<i class="attention icon"></i> Impossible to find the list',
				'content'	=> '',
				'time'		=> time()
			));
			redirect('/lists/?msg='.$encoded_msg);
		}
		
		$ids = json_decode(rawurldecode($ids_encoded));
		$this->load->model('customers_model');
		$customer_names = '';

		foreach($ids as $index => $customer_id)
		{
			$customer = $this->customers_model->get($customer_id);
			if ($customer !== NULL)
			{
				if ($this->lists_model->add_customer($list_id, $customer_id) === TRUE)
				{
					if ($index == 0)
					{
						$customer_names .=	$customer->name;
					}
					else
					{
					 	$customer_names .= ', '.$customer->name;
					}
				}
			}
		}
		
		if ( ! empty($customer_names))
		{
			// Success, go back to list and show message
			$encoded_msg = $this->service_message->as_url_param(array (
				'type'		=> 'success',
				'title'		=> '<i class="user plus icon"></i> '.$customer_names.' successfully added!',
				'content'	=> '',
				'time'		=> time()
			));
			redirect('/lists/view/'.$list_id.'/?msg='.$encoded_msg.'#customers');
		}
		else
		{
			$encoded_msg = $this->service_message->as_url_param(array (
						'type'		=> 'error',
						'title'		=> '<i class="attention icon"></i> Impossible to add customers',
						'content'	=> '',
						'time'		=> time()
					));
			redirect('/lists/view/'.$list_id.'/?msg='.$encoded_msg.'#customers');
		}
		
		return;
	}
	
	public function edit_vegetables($list_id=-1)
	{
		$this->load->helper('form');
		$this->load->config('vegetables');
		$this->load->model('veget_model');
		
		$list = $this->lists_model->get($list_id);
		if ($list == NULL)
		{
			// Error, list do not exists
			$encoded_msg = $this->service_message->as_url_param(array (
				'type'		=> 'error',
				'title'		=> '<i class="attention icon"></i> Impossible to find the list',
				'content'	=> '',
				'time'		=> time()
			));
			redirect('/lists/?msg='.$encoded_msg);
		}
		
		$selected_veget = $this->input->post('vegetables[]');
		
		if ($selected_veget !== NULL) // Save selection
		{
			$this->lists_model->update_vegetables($list_id);
			
			// Success, go back to list and show message
			$encoded_msg = $this->service_message->as_url_param(array (
				'type'		=> 'success',
				'title'		=> '<i class="lemon outline icon"></i> Vegetable list successfully saved!',
				'content'	=> '',
				'time'		=> time()
			));
			redirect('/lists/view/'.$list_id.'/?msg='.$encoded_msg.'#vegetables');
		}
		else // Show list
		{
			$this->service_message->load();

			// Load the templates in order with the data to print
			$data['title'] = 'Vegetables for '.$list->name;
			$data['list'] = $list;

			$vegetables_all = $this->veget_model->get_list();
			$this->veget_model->sort_vegetables_array($vegetables_all);
			// Get vegetables list
			$data['vegetables_ids'] = array_column($this->lists_model->get_vegetables($list_id), 'id');
			$data['vegetables_all'] = $vegetables_all;
			$data['units'] = $this->config->item('units');
			$data['form_url'] = 'lists/edit_vegetables/'.$list_id;


			// Load templates
			$this->load->view('templates/header', $data);
			$this->load->view('lists/edit_veget', $data);
			$this->load->view('templates/footer', $data);
		}
	}
	
	public function remove_vegetable($list_id=-1, $veget_id=-1)
	{
		$list = $this->lists_model->get($list_id);
		if ($list == NULL)
		{
			// Error, list do not exists
			$encoded_msg = $this->service_message->as_url_param(array (
				'type'		=> 'error',
				'title'		=> '<i class="attention icon"></i> Impossible to find the list',
				'content'	=> '',
				'time'		=> time()
			));
			redirect('/lists/?msg='.$encoded_msg);
		}
		
		$this->load->model('veget_model');
		
		$veget = $this->veget_model->get($veget_id);
		if ($veget == NULL)
		{
			$encoded_msg = $this->service_message->as_url_param(array (
				'type'		=> 'error',
				'title'		=> '<i class="attention icon"></i> Impossible to find the vegetable',
				'content'	=> '',
				'time'		=> time()
			));
			redirect('/lists/view/'.$list_id.'/?msg='.$encoded_msg.'#vegetables');
		}
		
		if ($this->lists_model->remove_vegetable($list_id, $veget_id) === TRUE)
		{
			// Success, go back to list and show message
			$encoded_msg = $this->service_message->as_url_param(array (
				'type'		=> 'success',
				'title'		=> '<i class="ban icon"></i> '.$veget->name.' successfully removed!',
				'content'	=> '',
				'time'		=> time()
			));
			redirect('/lists/view/'.$list_id.'/?msg='.$encoded_msg.'#vegetables');
		}
		else
		{
			$encoded_msg = $this->service_message->as_url_param(array (
						'type'		=> 'error',
						'title'		=> '<i class="attention icon"></i> Impossible to remove '.$veget->name,
						'content'	=> '',
						'time'		=> time()
					));
			redirect('/lists/view/'.$list_id.'/?msg='.$encoded_msg.'#vegetables');
		}
	}
	
	public function delete($l_id=-1)
	{
		$list = $this->lists_model->get($l_id);
		if ($list == NULL)
		{
			// Error, list do not exists
			$encoded_msg = $this->service_message->as_url_param(array (
				'type'		=> 'error',
				'title'		=> '<i class="attention icon"></i> Impossible to find the list',
				'content'	=> '',
				'time'		=> time()
			));
			redirect('/lists/?msg='.$encoded_msg);
		}
		
		$ok = $this->lists_model->delete($l_id);
		
		if ($ok === FALSE)
		{
			$msg = array (
				'type'		=> 'error',
				'title'		=> '<i class="attention icon"></i> Impossible to delete to the list',
				'content'	=> '',
				'time'		=> time()
			);
			$this->redirect->to('/lists/', NULL, $msg);
		}
		else
		{
			$msg = array (
				'type'		=> 'success',
				'title'		=> '<i class="check circle icon"></i> List successfully deleted!',
				'content'	=> '',
				'time'		=> time()
			);
			$this->redirect->to('/lists/', NULL, $msg);
		}
	}
	
	private function _run_form_validation()
	{
		$this->form_validation->set_rules('name', 'Name', 'required|alpha_numeric_spaces|min_length[5]');
		$this->form_validation->set_rules(
			'day_of_week', 
			'Day of week', 
			'required|in_list['.implode(',', array_keys(week_info()['days'])).']');

		return $this->form_validation->run();
	}
}
