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
class Orders extends CI_Controller {

	
	public function __construct()
	{
		parent::__construct();
		$this->load->model('deliveries_model');
		$this->load->model('orders_model');
	}
	
	public function view($o_id=-1)
	{
		$this->load->config('vegetables');
		$this->load->model('veget_model');
		
		$order = $this->orders_model->get($o_id);
		if ($order == NULL) {
			// Error, customer do not exists
			$msg = array (
				'type'		=> 'error',
				'title'		=> '<i class="attention icon"></i> Impossible to find the order',
				'content'	=> '',
				'time'		=> time()
			);
			$this->redirect->to('/lists/', NULL, $msg);
		}
//		$order->vegetables = json_decode($order->vegetables);
//		
//		//$delivery = $this->deliveries_model->get($order->d_id);
//		
//		// Load the templates in order with the data to print
//		$data['title'] = $order->customer.'\'s order for the '.mysql_to_nice_date($order->delivery_date);
//		
//		// Load templates
//		$this->load->view('templates/header', $data);
//		//$this->load->view('deliveries/view', $data);
//		print_r($order);
//		$this->load->view('templates/footer', $data);
		$this->load->model('accounting_model');
		
		$payment = $this->accounting_model->get_where($order->d_id, $order->c_id);
		
		if ($payment === NULL)
		{
			$msg = array (
				'type'		=> 'info',
				'title'		=> '<i class="info icon"></i> Comments for this order:',
				'content'	=> '<p>'.(empty($order->comments)? 'No comment' : $order->comments).'</p>',
	//				'time'		=> time()
			);
		}
		else
		{
			$details = '';
			$payment->payments = json_decode($payment->payments);
			foreach($payment->payments as $paid)
			{
				$details .= '<li><strong>'.$paid->date.'</strong> <i class="long arrow alternate right icon"></i>'.$paid->payment.'</li>';
			}
			$msg = array (
				'type'		=> 'success',
				'title'		=> '<i class="handshake icon"></i> Payment registered',
				'content'	=> '<ul class="list">
									<li>Total due: '.$payment->total_due.'</li>
									<li>Total received: '.$payment->total_paid.'</li>
									<li>Paiment details:
										<ul>
											'.$details.'
										</ul>
									</li>
								</ul>',
	//				'time'		=> time()
			);
			
			
		}
		
		if(empty($order->invoice))
		{
			$this->redirect->to('orders/edit/'.$order->id, NULL, $msg, TRUE);
		}
		else
		{
			$this->redirect->to('invoices/view/'.$order->id, NULL, $msg, TRUE);
		}
	}
	
	public function add($d_id=-1)
	{
		$this->load->helper('form');
		$this->load->library('form_validation');
		$this->load->config('vegetables');
		$this->load->model('veget_model');
		$this->load->model('lists_model');
		
		$delivery = $this->deliveries_model->get($d_id);
		if ($delivery == NULL) {
			// Error, delivery do not exists
			$encoded_msg = $this->service_message->as_url_param(array (
				'type'		=> 'error',
				'title'		=> '<i class="attention icon"></i> Impossible to find the delivery',
				'content'	=> '',
				'time'		=> time()
			));
			redirect('/lists/?msg='.$encoded_msg);
		}
		
		if ($this->_run_form_validation() === TRUE) // Save selection
		{
			//TODO
			$o_id = $this->orders_model->create($d_id);
			
			if ($o_id !== FALSE)
			{
				$order = $this->orders_model->get($o_id);
				
				$customer_name = $order->customer;
				
				// If the order is added after the collect (eg in accounting): generate the invoice
				if ($delivery->status !== 'collect')
				{
					$delivery->vegetables = json_decode($delivery->vegetables);

					// Reindex vegetables array by ids
					$vegetables = array();
					array_walk(
						$delivery->vegetables,
						function ($item, $key) use (&$vegetables) {
							$vegetables[$item->id] = $item;
						}
					);
					$invoice = $this->orders_model->generate_invoice($order, $vegetables);
					$ok = $this->orders_model->update($order->id,
													 array(
														 $this->orders_model::FIELD_INVOICE => json_encode($invoice)
													 ));
				}

				// Success, go back to delivery and show message
				$encoded_msg = $this->service_message->as_url_param(array (
					'type'		=> 'success',
					'title'		=> '<i class="cart plus icon"></i> Order for '.$customer_name.' successfully added!',
					'content'	=> '',
					'time'		=> time()
				));
				redirect('/deliveries/view/'.$d_id.'?msg='.$encoded_msg.'#orders');
			}
			else
			{
				// Error
				$this->service_message->set(array (
					'type'		=> 'error',
					'title'		=> '<i class="exclamation triangle icon"></i> Cannot save the order... Please retry',
					'content'	=> '',
					'time'		=> time()
				));
			}
				
		}
		
		$delivery->vegetables = json_decode($delivery->vegetables);
		$this->veget_model->sort_vegetables_array($delivery->vegetables);
		$customers_all = array_column($this->lists_model->get_customers($delivery->l_id), 'name', 'id');
		$customers_with_order = array_column($this->orders_model->get_list($delivery->id), 'customer', 'c_id');
		
		// Check imported vegetables, add it if not in the list of the delivery
		$imported_veget = json_decode(base64_decode(rawurldecode($this->input->get('vegets'))), TRUE);
		$veget_ids = array_column($delivery->vegetables, 'id');
		
		if (is_array($imported_veget))
		{
			foreach($imported_veget as $veg_id => $veg_qtt)
			{
				if ( ! in_array($veg_id, $veget_ids))
				{
					// Veget not available : add it to the list
					$veg = $this->veget_model->get($veg_id);
					if ($veg != NULL)
					{
						//Okay, now add the veg to the list
						$veg->not_from_list = TRUE;
						$delivery->vegetables[] = $veg;
					} 
					else 
					{
						unset($imported_veget[$veg_id]);
					}
				}
			}
		}

		$this->service_message->load();

		// Load the templates in order with the data to print
		$data['title'] = 'Add order for the '.mysql_to_nice_date($delivery->delivery_date);
		$data['breadcrumb_title'] = 'Add order';
		$data['delivery'] = $delivery;
		$data['customers_list'] = array_diff_assoc($customers_all, $customers_with_order);
		$data['customer_id'] = $this->input->get('customer');
		$data['customer_name'] = '';
		$data['comments'] = $this->input->get('comments');
		$data['order_id'] = -1;
		// Empty array of qtt for default values
		$data['vegetables'] = array_fill_keys(array_column($delivery->vegetables, 'id'), 0.00);
		if (is_array($imported_veget))
		{
			$data['vegetables'] = array_replace($data['vegetables'], $imported_veget);
		}

		// Get vegetables list
		$data['vegetables_all'] = $delivery->vegetables;
		$data['units'] = $this->config->item('units');

		$data['is_editing'] = FALSE;
		$data['form_url'] = 'orders/add/'.$d_id;

		// Load templates
		$this->load->view('templates/header', $data);
		$this->load->view('orders/edit_form', $data);
		$this->load->view('templates/footer', $data);
	}
		
	public function edit($o_id=-1)
	{
		$this->load->helper('form');
		$this->load->library('form_validation');
		$this->load->config('vegetables');
		$this->load->model('veget_model');
		$this->load->model('lists_model');
		
		$order = $this->orders_model->get($o_id);
		if ($order == NULL) {
			// Error, order do not exists
			$msg = array (
				'type'		=> 'error',
				'title'		=> '<i class="attention icon"></i> Impossible to find the order',
				'content'	=> '',
				'time'		=> time()
			);
			$this->redirect->to('/lists/', NULL, $msg);
		}
		
		if ($this->_run_form_validation() === TRUE) // Save selection
		{
			//TODO
			$success = $this->orders_model->update($o_id);
			
			
			if ($success === TRUE)
			{
				$order = $this->orders_model->get($o_id);
				// If the invoice already exists (except in collect)
				if ($order->delivery_status !== 'collect' && ! empty($order->invoice))
				{
					$delivery = $this->deliveries_model->get($order->d_id);
					$delivery->vegetables = json_decode($delivery->vegetables);

					// Reindex vegetables array by ids
					$vegetables = array();
					array_walk(
						$delivery->vegetables,
						function ($item, $key) use (&$vegetables) {
							$vegetables[$item->id] = $item;
						}
					);
					$invoice = $this->orders_model->generate_invoice($order, $vegetables);
					$ok = $this->orders_model->update($order->id,
													 array(
														 $this->orders_model::FIELD_INVOICE => json_encode($invoice)
													 ));
				}
				
				$customer_name = $order->customer;

				// Success, go back to delivery and show message
				$msg = array (
					'type'		=> 'success',
					'title'		=> '<i class="cart plus icon"></i> Order for '.$customer_name.' successfully saved!',
					'content'	=> '',
					'time'		=> time()
				);
				$this->redirect->to('/deliveries/view/'.$order->d_id, '#orders', $msg);
			}
			else
			{
				// Error
				$this->service_message->set(array (
					'type'		=> 'error',
					'title'		=> '<i class="exclamation triangle icon"></i> Cannot save the order... Please retry',
					'content'	=> '',
					'time'		=> time()
				));
			}
				
		}
		$delivery = $this->deliveries_model->get($order->d_id);
		
		$delivery->vegetables = json_decode($delivery->vegetables);
		$this->veget_model->sort_vegetables_array($delivery->vegetables);
		$customers_all = array_column($this->lists_model->get_customers($delivery->l_id), 'name', 'id');
		$customers_with_order = array_column($this->orders_model->get_list($delivery->id), 'customer', 'c_id');

		//Import vegetables list
		$order->vegetables = json_decode($order->vegetables, TRUE);
		$imported_veget = json_decode(base64_decode(rawurldecode($this->input->get('vegets'))), TRUE);
		$replace = $this->input->get('replace_list');
		
		if (is_array($imported_veget) AND count($imported_veget) > 0)
		{
			if ( ! empty($replace) && $replace === 'true')
			{
				$order->vegetables = $imported_veget;
			}
			else
			{
				$order->vegetables = array_replace($order->vegetables, $imported_veget);
			}
		}

		// Check if veget are in the delivery list
		$veget_ids = array_column($delivery->vegetables, 'id');
		
		foreach($order->vegetables as $veg_id => $veg_qtt)
		{
			if ( ! in_array($veg_id, $veget_ids))
			{
				// Veget not available : add it to the list
				$veg = $this->veget_model->get($veg_id);
				if ($veg != NULL)
				{
					//Okay, now add the veg to the list
					$veg->not_from_list = TRUE;
					$delivery->vegetables[] = $veg;
				} 
				else 
				{
					unset($order->vegetables[$veg_id]);
				}
			}
		}
		
		//Keep our current customer in the list of all user
		unset($customers_with_order[$order->c_id]);
		$data['customer_id'] = $order->c_id; // Select the user in the list
		$data['customer_name'] = $order->customer;
		$data['comments'] = $order->comments;
		$data['order_id'] = $order->id;
		$data['vegetables'] = array_replace( // Create list of qtt for all vegetables
			array_fill_keys(array_column($delivery->vegetables, 'id'), 0.00),
			$order->vegetables);

		$this->service_message->load();

		// Load the templates in order with the data to print
		$data['title'] = 'Edit '.$order->customer.'\'s order for the '.mysql_to_nice_date($delivery->delivery_date);
		$data['breadcrumb_title'] = 'Edit order';
		$data['delivery'] = $delivery;
		$data['customers_list'] = array_diff_assoc($customers_all, $customers_with_order);

		// Get vegetables list
		$data['vegetables_all'] = $delivery->vegetables;
		$data['units'] = $this->config->item('units');
		
		$data['is_editing'] = TRUE;
		$data['form_url'] = 'orders/edit/'.$order->id;

		// Load templates
		$this->load->view('templates/header', $data);
		$this->load->view('orders/edit_form', $data);
		$this->load->view('templates/footer', $data);
	}
	
	public function delete($o_id=-1)
	{
		$order = $this->orders_model->get($o_id);
		if ($order == NULL) {
			// Error, order do not exists
			$encoded_msg = $this->service_message->as_url_param(array (
				'type'		=> 'error',
				'title'		=> '<i class="attention icon"></i> Impossible to find the order',
				'content'	=> '',
				'time'		=> time()
			));
			redirect('/lists/?msg='.$encoded_msg);
		}
				
		if ($this->orders_model->delete($o_id) === TRUE)
		{
			// Success, go back to list and show message
			$encoded_msg = $this->service_message->as_url_param(array (
				'type'		=> 'success',
				'title'		=> '<i class="trash alternate icon"></i> '.$order->customer.'\'s order successfully deleted!',
				'content'	=> '',
				'time'		=> time()
			));
			redirect('/deliveries/view/'.$order->d_id.'/?msg='.$encoded_msg);
		}
		else
		{
			$encoded_msg = $this->service_message->as_url_param(array (
						'type'		=> 'error',
						'title'		=> '<i class="attention icon"></i> Impossible to delete '.$order->customer.'\'s order',
						'content'	=> '',
						'time'		=> time()
					));
			redirect('/deliveries/view/'.$order->d_id.'/?msg='.$encoded_msg);
		}
	}
	
	public function upload_list()
	{
		$this->load->config('vegetables');
		$this->load->library('PhpOffice');
		$this->load->model('veget_model');
		
		$o_id = $this->input->post('order_id');
		$d_id = $this->input->post('delivery_id');
		$replace = $this->input->post('replace');
		if (empty($o_id) || empty($d_id))
		{
			redirect($this->input->server('HTTP_REFERER'));
		}
		
		$config['upload_path']          = $this->phpoffice->getTemporaryFolder();
		$config['allowed_types']        = 'xls|xlsx';

		$this->load->library('upload', $config);
		
		if ( ! $this->upload->do_upload('import_list'))
		{
			$encoded_msg = $this->service_message->as_url_param(array (
				'type'		=> 'error',
				'title'		=> '<i class="attention icon"></i> '.$this->upload->display_errors(''),
				'content'	=> '',
				'time'		=> time()
			));
			if ($o_id != -1)
			{
				redirect('/orders/edit/'.$o_id.'/?msg='.$encoded_msg);
			}
			else
			{
				redirect('/deliveries/add_order/'.$d_id.'/?msg='.$encoded_msg);
			}
		}
		else
		{
			// Upload file with CI
			$data = $this->upload->data();
			// Then use PhpOffice to load the Excel spreadsheet
			$spreadsheet = $this->phpoffice->load_file($data['full_path']);
			
			$tpl_config = $this->config->item('tpl_import_veg_list');
			if ($tpl_config == NULL OR empty($tpl_config))
			{
				$tpl_config = array(
					'base_row_vege'	=> 2, // Row to use to insert vegetables
					'base_row_other'=> 1, // Row to use to insert other items
					'column_name'	=> 'A',
					'column_order'	=> 'D'
				);
			}
			
			$base_row_other = $tpl_config['base_row_other'];
			$base_row_veg = $tpl_config['base_row_vege'];
			$base_row = min($base_row_veg, $base_row_other);
			
			$veg_orders = array(); // Array for vegetables to add to the order
			
			for($row = $base_row, $reach_end = 0; $reach_end <= 10; $row++)
			{
				$name = $spreadsheet->getActiveSheet()->getCell($tpl_config['column_name'].$row)->getCalculatedValue();
				$qtt = $spreadsheet->getActiveSheet()->getCell($tpl_config['column_order'].$row)->getCalculatedValue();
				
				if ($qtt === 'Total')
				{
					break;
				}
				
				if ($qtt != NULL && $name != NULL)
				{
					$results = $this->veget_model->search(Veget_model::FIELD_NAME, $name);
					if (count($results) > 0) // found at least one
					{
						if (count($results) > 1)
						{
							// If more than one, get the exact match
							foreach ($results as $r)
							{
								if ($r->name == $name)
								{
									$veg = $r;
									break;
								}
							}
						}
						else
						{
							$veg = $results[0];
						}
					
						//echo '{'.$veg->name.' : '.$qtt.' x '.$veg->price.' = '.($qtt*$veg->price).'}';
						$veg_orders[$veg->id] = $qtt;
					}
				}
				if ($name === NULL)
				{
					$reach_end++;
				}
			}
			
			//Remove temp uploaded file
			
			if (sizeof($veg_orders) > 0)
			{
				$encoded_veget = rawurlencode(base64_encode(json_encode($veg_orders)));

				// Success, go back to delivery and show message
				$encoded_msg = $this->service_message->as_url_param(array (
					'type'		=> 'success',
					'title'		=> '<i class="lemon outline icon"></i> Vegetables successfully imported!',
					'content'	=> '',
					'time'		=> time()
				));
			}
			else
			{
				$encoded_veget = NULL;
				
				// Success, go back to delivery and show message
				$encoded_msg = $this->service_message->as_url_param(array (
					'type'		=> 'warning',
					'title'		=> '<i class="exclamation triangle icon"></i> No vegetable has been imported!',
					'content'	=> '<p>Please check your Excel file, maybe there is a problem with the formating.</p>',
					'time'		=> time()
				));
			}
			
			if( ! empty($replace))
			{
				$replace = '&replace_list=true';
			}
			
			if ($o_id != -1)
			{
				redirect('/orders/edit/'.$o_id.'/?msg='.$encoded_msg.'&vegets='.$encoded_veget.$replace);
			}
			else
			{
				$param = '&'.http_build_query(array(
					'customer' => $this->input->post('customer'), 
					'comments' => $this->input->post('comments')));
				redirect('/deliveries/add_order/'.$d_id.'/?msg='.$encoded_msg.'&vegets='.$encoded_veget.$param.$replace);
			}
		}
	}
	
	private function _run_form_validation()
	{
		$this->form_validation->set_rules('customer', 'Customer', 'required');
		$this->form_validation->set_rules(
			'vegetables[]', 
			'Quantities', 
			'numeric');

		return $this->form_validation->run();
	}

}
