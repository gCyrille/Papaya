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
class Customers extends CI_Controller {

	// array compatible with Semantic Helper
	private $service_msg = NULL;
	
	public function __construct()
	{
		parent::__construct();
		$this->load->model('customers_model');
	}
	
	public function list($page=NULL)
	{
		$this->load->library('pagination');
		
		if($page == NULL)
		{
			$page = 0;
		}
		
		// Fill datas for the template pages
		$data['title'] = 'Customer list';

//		$config['base_url'] = base_url('/customers/list/');
//		$config['per_page'] = 15;
//		$config['total_rows'] = $this->customers_model->get_count();
//		$this->pagination->initialize($config);
		
		$customers = $this->customers_model->get_list(/*15, $page*/);
		
		foreach ($customers as $c)
		{
			$c->total_unpaid = $this->customers_model->get_total_unpaid($c->id) + $c->current_balance;
		}
		
		$data['customers'] = $customers;
		
		// Show service messages from other pages
		$this->service_message->load();
				
		// Load the templates in order with the data to print
		$this->load->view('templates/header', $data);
		$this->load->view('customers/list', $data);
		$this->load->view('templates/footer', $data);
	}
	
	public function search()
	{
		$customers = $this->customers_model->search();
		
		$answer = array(
			'results' => array (
			),
			'action' => array(
				'url' => base_url('customers/new'),
				'text' => 'Add a customer'
			)
		);
		
		foreach ($customers as $customer)
		{
			$answer['results'][] = array(
				'title' => $customer->name,
				'description' => $customer->email,
				'url' => base_url('/customers/edit/'.$customer->id)
			);
		}
		
		echo json_encode($answer);
	}
	
	public function delete($id=-1)
	{		
		$cus = $this->customers_model->get($id);
		if ($cus == NULL) {
			// Error, customer do not exists
			$encoded_msg = $this->service_message->as_url_param(array (
						'type'		=> 'error',
						'title'		=> '<i class="attention icon"></i> Impossible to find the customer',
						'content'	=> '',
						'time'		=> time()
					));
			redirect('/customers/list?msg='.$encoded_msg);
		}
		
		if ($this->customers_model->delete($id) === TRUE)
		{
			// Success, go back to list and show message
			$encoded_msg = $this->service_message->as_url_param(array (
				'type'		=> 'success',
				'title'		=> '<i class="trash alternate icon"></i> '.$cus->name.' successfully deleted!',
				'content'	=> '',
				'time'		=> time()
			));
			redirect('/customers/list?msg='.$encoded_msg);
		}
		else
		{
			$encoded_msg = $this->service_message->as_url_param(array (
						'type'		=> 'error',
						'title'		=> '<i class="attention icon"></i> Impossible to delete '.$cus->name,
						'content'	=> '',
						'time'		=> time()
					));
			redirect('/customers/list?msg='.$encoded_msg);
		}
	}
	
	public function create()
	{
		$this->load->helper('form');
		$this->load->library('form_validation');
		$this->load->model('lists_model');
		
		$this->service_message->load();
		
		$customer = array (
			'id' => -1,
			'name' => NULL,
			'contact_name' => NULL,
			'email' => NULL,
			'email_2' => NULL,
			'delivery_place' => NULL,
			'delivery_place_2' => NULL,
			'current_balance' => 0.00,
		);
		
		if ($this->_run_form_validation(FALSE) === TRUE) 
		{
			// Save vegetable into db
			$id = $this->customers_model->create();
			if ($id !== FALSE)
			{
				// Success, go back to list and show message
				$customer = $this->customers_model->get($id);
				$encoded_msg = $this->service_message->as_url_param(array (
					'type'		=> 'success',
					'title'		=> '<i class="check circle icon"></i> Customer "'.$customer->name.'" successfully created!',
					'content'	=> '<br /><a class="ui basic button" href="'.base_url('/customers/edit/'.$customer->id).'">Edit profil</a>',
					'time'		=> time()
				));
				if ($this->input->post('new_after'))
				{
					redirect('/customers/new?msg='.$encoded_msg);	
				}
				else
				{
					redirect('/customers/list?msg='.$encoded_msg);	
				}
				
			}
			else
			{
				// Warning message: not save, retry
				$this->service_message->set(array (
					'type'		=> 'warning',
					'title'		=> '<i class="exclamation triangle icon"></i> Impossible to save!',
					'content'	=> 'Due to an internal error, the customer profil is not saved.<br/> Please try again.'
				));	
			}
		}
		
		// Load the templates in order with the data to print
		$data['title'] = 'New customer';
		$data['submit_btn'] = 'Create';
		$data['is_editing'] = FALSE;
		$data['back_link'] = base_url('/customers/list');
		$data['form_url'] = 'customers/new';
		$data['customer'] = $customer;
		$data['lists'] = array();
		$data['delivery_lists'] = array_column($this->lists_model->get_list(), 'name', 'id');
				
		$this->load->view('templates/header', $data);
		$this->load->view('customers/create', $data);
		$this->load->view('templates/footer', $data);
	}
	
	public function edit($id=-1)
	{
		$this->load->helper('form');
		$this->load->library('form_validation');
		$this->load->model('lists_model');
		
		$this->service_message->load();
		
		$customer = $this->customers_model->get($id, TRUE);
		if ($customer == NULL) {
			// Error, customer do not exists
			$encoded_msg = $this->service_message->as_url_param(array (
				'type'		=> 'error',
				'title'		=> '<i class="attention icon"></i> Impossible to find the customer',
				'content'	=> '',
				'time'		=> time()
			));
			redirect('/customers/list?msg='.$encoded_msg);
		}
		
		if ($this->_run_form_validation(TRUE) === TRUE) 
		{
			if ($this->customers_model->update($id) === TRUE)
			{
				// Success, go back to list and show message
				$customer = $this->customers_model->get($id);
				$msg = array (
					'type'		=> 'success',
					'title'		=> '<i class="check circle icon"></i> '.$customer->name.' successfully saved!',
					'content'	=> '',
					'time'		=> time()
				);
				$this->redirect->to('/customers/edit/'.$customer->id, NULL, $msg);
			}
			else
			{
				// Warning message: not save, retry
				$this->service_message->set(array (
					'type'		=> 'warning',
					'title'		=> '<i class="exclamation triangle icon"></i> Impossible to save!',
					'content'	=> 'Due to an internal error, the customer profil is not saved.<br/> Please try again.'
				));	
			}
		}
		
		$show_more = $this->input->get('more');
		if ($show_more === NULL)
		{
			$show_more = 5;
		}
		
		$delivery_names = array();
		$orders = $this->customers_model->get_orders($id, $show_more, 0);
		foreach($orders as $order)
		{
			if ( ! key_exists($order->l_id, $delivery_names))
			{
				$l = $this->lists_model->get($order->l_id);
				if ($l !== NULL)
				{
					$delivery_names[$l->id] = $l->name;
				}
				else
				{
					$delivery_names[$order->l_id] = 'Deleted list';
				}
			}
			
			$order->delivery_name = $delivery_names[$order->l_id];
			
			if ( ! empty($order->invoice))
			{
				$invoice = json_decode($order->invoice);
				$order->total = $invoice->total;
			} 
			else
			{
				$order->total = 0.00;
			}
			if ( ! empty($order->vegetables))
			{
				$order->vegetables = json_decode($order->vegetables, TRUE);
			}
			
			//Check if payment has been registered for this order
			if ($order->status === 'not_paid')
			{
				$this->load->model('accounting_model');
				$payment = $this->accounting_model->get_where($order->d_id, $order->c_id);
				$order->payment_registered = ($payment !== NULL);
			}
		}
		
		if (count($orders) < $show_more)
		{
			$data['show_more'] = -1;
		}
		else
		{
			$data['show_more'] = $show_more + 5;
		}
		
		
		$this->load->config('vegetables');
		$data['edit_password'] = $this->config->item('edit_password');
		
		// Load the templates in order with the data to print
		$data['title'] = $customer['name'];
		$data['submit_btn'] = 'Save';
		$data['is_editing'] = TRUE;
		$data['back_link'] = base_url($this->redirect->build_url('/customers/list'));
		$data['form_url'] = 'customers/edit/'.$customer['id'];
		$data['customer'] = $customer;
		$data['total_unpaid'] = $this->customers_model->get_total_unpaid($customer['id']);
		$data['orders'] = $orders;
		$data['lists'] = array_column($this->customers_model->get_delivery_lists($id), 'id');
		$data['delivery_lists'] = array_column($this->lists_model->get_list(), 'name', 'id');
		
		$this->load->view('templates/header', $data);
		$this->load->view('customers/profil', $data);
		$this->load->view('templates/footer', $data);
	}
	
	private function _run_form_validation($is_edition)
	{
		
		if ($is_edition === TRUE)
		{
			$this->form_validation->set_rules(
				'name', 
				'Name', 
				'trim|required|regex_match[/^[\\w -_()]+$/]'
			);
			$this->form_validation->set_rules(
				'email', 
				'Email', 
				'trim|valid_email'
			);
		}
		else
		{
			$this->form_validation->set_rules(
				'name', 
				'Name', 
				'trim|required|alpha_numeric_spaces|is_unique['.$this->customers_model::TABLE_NAME.'.'.$this->customers_model::FIELD_NAME.']'
			);
			$this->form_validation->set_rules(
				'email', 
				'Email', 
				'trim|valid_email'
			);
		}
		
		$this->form_validation->set_rules(
			'contact_name', 
			'Contact name', 
			'trim|alpha_numeric_spaces'
		);
		$this->form_validation->set_rules(
			'email_2', 
			'Email 2', 
			'trim|valid_email'
		);
		$this->form_validation->set_rules(
			'current_balance', 
			'Balance', 
			'numeric'
		);
		$this->form_validation->set_message(
			'is_unique', 
			'This {field} is already used for another customer.'
		);
		
		return $this->form_validation->run();
	}
}
