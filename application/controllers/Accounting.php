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
class Accounting extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('deliveries_model');
		$this->load->model('orders_model');
		$this->load->model('customers_model');
		$this->load->model('accounting_model');
		$this->load->config('vegetables');
	}
	
	/**
	 * (Automatic) step between delivery PREPARE and ACCOUNTING state
	 * Set all orders ready to register payments
	 */
	public function prepare($d_id=-1)
	{
		$delivery = $this->deliveries_model->get($d_id);
		if ($delivery == NULL) {
			// Error, customer do not exists
			$encoded_msg = $this->service_message->as_url_param(array (
				'type'		=> 'error',
				'title'		=> '<i class="attention icon"></i> Impossible to find the delivery',
				'content'	=> '',
				'time'		=> time()
			));
			redirect('/lists/?msg='.$encoded_msg);
		}
		
		$orders = $this->orders_model->get_list($delivery->id, TRUE);
		
		foreach($orders as $order)
		{
			$ok = $this->orders_model->update($order->id, 
								  array(
									$this->orders_model::FIELD_STATUS  => 'not_paid')
								 );
		}
		
		$ok = $this->deliveries_model->update($d_id, array($this->deliveries_model::FIELD_STATUS => 'accounting'));
		
		if ($ok === FALSE)
		{
			$msg = array (
				'type'		=> 'error',
				'title'		=> '<i class="attention icon"></i> Impossible to prepare the accounting',
				'content'	=> '',
				'time'		=> time()
			);
			$this->redirect->to('/deliveries/view/'.$d_id, NULL, $msg);
		}
		else
		{
			$this->redirect->to('/deliveries/view/'.$d_id, array('print_paysheet' => 'true'));
		}
	}
	
	/**
	 * Manual reverse from ACCOUNTING to PREPARE state
	 * Delete all payments and set orders to 'not_printed'
	 */
	public function undo_prepare($d_id=-1)
	{
		$delivery = $this->deliveries_model->get($d_id);
		if ($delivery == NULL) {
			// Error, customer do not exists
			$encoded_msg = $this->service_message->as_url_param(array (
				'type'		=> 'error',
				'title'		=> '<i class="attention icon"></i> Impossible to find the delivery',
				'content'	=> '',
				'time'		=> time()
			));
			redirect('/lists/?msg='.$encoded_msg);
		}
		
		$ok2 = TRUE;
		
		// Delete all payments
		$ok2 = $this->accounting_model->delete_all_for($d_id);
		
		$orders = $this->orders_model->get_list($delivery->id, TRUE);
		
		foreach($orders as $order)
		{
			$ok2 = $ok2 && $this->orders_model->update($order->id, 
								  array(
									$this->orders_model::FIELD_STATUS  => 'not_printed')
								 );
		}
		
		$ok = $this->deliveries_model->update($d_id, array($this->deliveries_model::FIELD_STATUS => 'prepare'));
		
		if ($ok === FALSE)
		{
			$msg = array (
				'type'		=> 'error',
				'title'		=> '<i class="attention icon"></i> Impossible to go back to the prepare state',
				'content'	=> '',
				'time'		=> time()
			);
			$this->redirect->to('/deliveries/view/'.$d_id, NULL, $msg);
		}
		else
		{
			$msg = NULL;
			
			if( ! $ok2)
			{
				$msg = array (
					'type'		=> 'warning',
					'title'		=> '<i class="attention icon"></i> An error occurs during the process. Some errors may appear on the payments or customers balances.',
					'content'	=> '',
					'time'		=> time()
				);
			}
			
			$this->redirect->to('/deliveries/view/'.$d_id, NULL, $msg);
		}
	}
	
	/**
	 * Manual reverse from CLOSED to ACCOUNTING state
	 *
	 */
	public function reopen_accounting($d_id=-1)
	{
		$delivery = $this->deliveries_model->get($d_id);
		if ($delivery == NULL) {
			// Error, customer do not exists
			$encoded_msg = $this->service_message->as_url_param(array (
				'type'		=> 'error',
				'title'		=> '<i class="attention icon"></i> Impossible to find the delivery',
				'content'	=> '',
				'time'		=> time()
			));
			redirect('/lists/?msg='.$encoded_msg);
		}
		
		$ok = $this->deliveries_model->update($d_id, array($this->deliveries_model::FIELD_STATUS => 'accounting'));
		
		if ($ok === FALSE)
		{
			$msg = array (
				'type'		=> 'error',
				'title'		=> '<i class="attention icon"></i> Impossible to reopen the accounting',
				'content'	=> '',
				'time'		=> time()
			);
			$this->redirect->to('/deliveries/view/'.$d_id, NULL, $msg);
		}
		else
		{
			$this->redirect->to('/deliveries/view/'.$d_id);
		}
	}
	
	
	/**
	 * (Automatic) step between delivery ACCOUNTING and CLOSED state
	 * Prepare accounting by parsing all paid orders and calculate total by accounting categories
	 */
	public function generate($d_id=-1)
	{
		$delivery = $this->deliveries_model->get($d_id);
		if ($delivery == NULL) {
			// Error, customer do not exists
			$msg = array (
				'type'		=> 'error',
				'title'		=> '<i class="attention icon"></i> Impossible to find the delivery',
				'content'	=> '',
				'time'		=> time()
			);
			$this->redirect->to('/lists/', NULL, $msg);
		}
		
		$this->load->model('veget_model');
		$accounting_cats = $this->config->item('accounting_cats');
		
		$ok = true;
		
		$delivery->vegetables = json_decode($delivery->vegetables);
		// Reindex vegetables array by ids
		$d_vegetables = array();
		array_walk(
			$delivery->vegetables,
			function ($item, $key) use (&$d_vegetables) {
				$d_vegetables[$item->id] = $item;
			}
		);

		$d_payments = $this->accounting_model->get_list($d_id);
		foreach($d_payments as $row)
		{
			if ($row->c_id == -1) // Exlude expenses row
			{
				continue;
			}
			
			$row->details = array_fill_keys(array_keys($accounting_cats), 0);
			
			$payments = json_decode($row->payments);
			//For each invoice paid by this payment
			// Get the total to pay for each accounting category
			foreach ($payments as $p)
			{
				if ($p->id < 0) // Exclude balances and extra row
				{
					continue;
				}
				
				$order = $this->orders_model->get($p->id);
				if ($order == NULL)
				{
					log_message('error', 'Found a payment for a inexistent order... Skip...');
					continue;
				}
				
				// Get vegetables list of the order (to use the prices)
				if ($order->d_id == $delivery->id)
				{
					$vegetables = $d_vegetables;
				}
				else
				{
					// Load vegetables for that delivery
					$d = $this->deliveries_model->get($order->d_id);
					if ($d == NULL)
					{
						//Fallback with current delivery
						$vegetables = $d_vegetables;
					}
					else
					{
						$d->vegetables = json_decode($d->vegetables);
						$vegetables = array();
						array_walk(
							$d->vegetables,
							function ($item, $key) use (&$vegetables) {
								$vegetables[$item->id] = $item;
							}
						);
					}
				}
				
				$details = array_fill_keys(array_keys($accounting_cats), 0); // Empty array for totals
				// Use the quantities of the order and calculate the total
				$o_veget = json_decode($order->vegetables);
				foreach($o_veget as $veg_id => $veg_qtt)
				{
					if (key_exists($veg_id, $vegetables))
					{
						// Veget selected for delivery, use this data
						if( ! key_exists($vegetables[$veg_id]->accounting_cat, $details)) // In case of removed category
						{
							$details[$vegetables[$veg_id]->accounting_cat] = 0;	
							$row->details[$vegetables[$veg_id]->accounting_cat] = 0;	
						}
						$details[$vegetables[$veg_id]->accounting_cat] += round_kwacha($veg_qtt * $vegetables[$veg_id]->price);
					}
					else
					{
						$veg = $this->veget_model->get($veg_id);
						if ($veg != NULL)
						{
							$veg->not_from_list = TRUE;
							$vegetables[] = $veg;
							if( ! key_exists($veg->accounting_cat, $details)) // In case of removed category
							{
								$details[$veg->accounting_cat] = 0;
								$row->details[$veg->accounting_cat] = 0;
							}
							$details[$veg->accounting_cat] += round_kwacha($veg->price * $veg_qtt);
						}
					}
				}
				
				// Adjust the amount according the total_paid (eg money received)
				$order->invoice = json_decode($order->invoice);
				if ($p->payment >= 0 && $order->invoice->total > 0)
				{
					$adj = $p->payment / $order->invoice->total;
				}
				array_walk(
					$details,
					function ($val, $cat) use (&$row, $adj) {
						$row->details[$cat] += round_kwacha($val * $adj);
					}
				);
				
			}
			
			// Include the balances automaticaly into the vegetables category
			$total = array_sum(array_values($row->details));
			if ($total < $row->total_paid) // Balances are into the total paid, but not into the orders payment list
			{
				$row->details['veg'] += ($row->total_paid - $total);
			}
			else if($total > $row->total_paid) // 'Extra' may have been used with negative value with not real cash
			{
				$diff = ($total - $row->total_paid);
				$ratio = $diff / $total;
				foreach ($row->details as $cat_name => $value)
				{
					$row->details[$cat_name] -= round_kwacha($value * $ratio); // Remove in proportion the negative "extra" paid
				}
			}
			else
			{
				// "LE COMPTE EST BON!"
			}
			
			$ok = $ok && $this->accounting_model->update($row->id, array(Accounting_model::FIELD_DETAILS => $row->details));
		}
		
		if ($ok === FALSE)
		{
			$msg = array (
				'type'		=> 'error',
				'title'		=> '<i class="attention icon"></i> Impossible to close the accounting',
				'content'	=> '',
				'time'		=> time()
			);
			$this->redirect->to('/deliveries/view/'.$d_id, NULL, $msg);
		}
		else
		{
			$this->redirect->to('/accounting/finalize/'.$d_id);
		}
	}
	
	/**
	 * (Manual) step after the 'generate' step, between delivery ACCOUNTING and CLOSED.
	 * Input the expenses and notes count.
	 */
	public function finalize($d_id=-1)
	{
		$delivery = $this->deliveries_model->get($d_id);
		if ($delivery == NULL) {
			// Error, customer do not exists
			$msg = array (
				'type'		=> 'error',
				'title'		=> '<i class="attention icon"></i> Impossible to find the delivery',
				'content'	=> '',
				'time'		=> time()
			);
			$this->redirect->to('/lists/', NULL, $msg);
		}
		
		$this->service_message->load();
		
		$this->load->helper('form');
		$this->load->library('form_validation');
		
		$payments = $this->accounting_model->get_list($d_id);
		$total_received = 0;
		foreach($payments as $p)
		{
			if ($p->c_id >= 0)
			{
				$total_received += $p->total_paid;
			}
		}
		
		$expenses_cats = $this->config->item('expenses_cats');

		$this->form_validation->set_rules('cash', 'Cash and cheques', 'required|numeric');
		$this->form_validation->set_rules('change', 'Change', 'required|numeric');
		
		foreach ($expenses_cats as $cat_code => $cat_title)
		{
			$this->form_validation->set_rules(
				'expenses['.$cat_code.']', 
				$cat_title, 
				'numeric');
		}

		if ($this->form_validation->run() === TRUE) // Save selection
		{
			$cash = floatval($this->input->post('cash'));
			$change = floatval($this->input->post('change'));
			$expenses = array();
			
			$total = $cash;
			foreach ($expenses_cats as $cat_code => $cat_title)
			{
				$expenses[$cat_code] = floatval($this->input->post('expenses['.$cat_code.']'));
				$total += $expenses[$cat_code];
			}
			
			$total -= $change;
			$diff = $total - $total_received;
			
			$expenses['cash'] = $cash;
			$expenses['change'] = $change;
			
			// Then add accounting row for this payment
			// Detect duplicated entry: already registered, try to update
			$entry = $this->accounting_model->get_where($d_id, -1);
			if ($entry != NULL)
			{
				$success = $this->accounting_model->update(
					$entry->id,
					array(
						$this->accounting_model::FIELD_TOTAL_DUE	=> $total_received, // Total paid from all payments
						$this->accounting_model::FIELD_TOTAL_PAID	=> $total, // Total counted money (= cash + expenses)
						$this->accounting_model::FIELD_DETAILS		=> $expenses
				));
			}
			else
			{
				$success = $this->accounting_model->create(
					array(
						$this->accounting_model::FIELD_DELIVERY_ID	=> $delivery->id,
						$this->accounting_model::FIELD_CUSTOMER_ID 	=> -1, // No customer
						$this->accounting_model::FIELD_TOTAL_DUE	=> $total_received, // Total paid from all payments
						$this->accounting_model::FIELD_TOTAL_PAID	=> $total, // Total counted money (= cash + expenses)
						$this->accounting_model::FIELD_DETAILS		=> $expenses
				));
			}
			
			if ($success === FALSE)
			{
				// Error
				$this->service_message->set(array (
					'type'		=> 'error',
					'title'		=> '<i class="exclamation triangle icon"></i> Sorry, we cannot finalize the accounting...',
					'content'	=> '<p></p><a href="'.base_url($this->redirect->build_url('deliveries/view/'.$d_id)).'" class="ui button">Skip <i class="right arrow icon"></i></a>',
					'time'		=> time()
				));
			}
			else
			{
				//Update delivery state to close
				$ok = $this->deliveries_model->update($d_id, array($this->deliveries_model::FIELD_STATUS => 'closed'));
				
				// And redirect to "view accounting"
				redirect('accounting/view/'.$d_id);
			}
		}
		
		$data['delivery'] = $delivery;
		$data['title'] = 'Finalize accounting';
		$data['form_url'] = base_url('accounting/finalize/'.$d_id);
		
		$data['total_received'] = format_kwacha($total_received);
		$data['expenses_cats'] = $expenses_cats;

		// Load templates
		$this->load->view('templates/header', $data);
		$this->load->view('accounting/finalize', $data);
		$this->load->view('templates/footer', $data);
		
		return;
	}
	
	public function view($d_id=-1)
	{
		$delivery = $this->deliveries_model->get($d_id);
		if ($delivery == NULL) {
			// Error, customer do not exists
			$msg = array (
				'type'		=> 'error',
				'title'		=> '<i class="attention icon"></i> Impossible to find the delivery',
				'content'	=> '',
				'time'		=> time()
			);
			$this->redirect->to('/lists/', NULL, $msg);
		}
		
		$this->service_message->load();
		
		
		$data['delivery'] = $delivery;
		$data['title'] = 'View accounting';
	
		$data['template'] = $this->accounting_sheet($delivery);
		
		// Load templates
		$this->load->view('templates/header', $data);
		$this->load->view('accounting/view', $data);
		$this->load->view('templates/footer', $data);
	}
	
	public function print_accounting_sheet($d_id=-1)
	{
		$delivery = $this->deliveries_model->get($d_id);
		if ($delivery == NULL) {
			// Error, customer do not exists
			$encoded_msg = $this->service_message->as_url_param(array (
				'type'		=> 'error',
				'title'		=> '<i class="attention icon"></i> Impossible to find the delivery',
				'content'	=> '',
				'time'		=> time()
			));
			redirect('/lists/?msg='.$encoded_msg);
		}
		
		$this->load->library('parser');
		$this->load->library('phpoffice');
		$this->load->helper('file');
		
		$html = $this->accounting_sheet($delivery);
		
		// Load and concat CSS for the invoice
		$css = read_file(APPPATH.'/views/invoices/table.css');
		$css .= read_file(APPPATH.'/views/invoices/site.css');
		
//		$html = '<style type="text/css">'.$css.'</style>'.$html;
//		echo $html;
//		return;

		$dompdf = $this->phpoffice->html_to_pdf($html, $css);
		
		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper('A4', 'portrait');
		$dompdf->set_option('fontHeightRatio', '0.6');
		$dompdf->set_option('defaultMediaType', 'print');

		// Render the HTML as PDF
		$dompdf->render();
		
		$filename = str_replace('/', '-', $delivery->delivery_date).'_accounting_sheet.pdf';
		$filepath = $this->phpoffice->getFilename($filename, 'pdf');
		
		write_file($filepath, $dompdf->output());
		exec(APPPATH.'/PDFtoPrinter.exe "'.$filepath.'"');
		
		// Output the generated PDF to Browser
		$dompdf->stream($filename);
	}
	
	public function print_payment_sheet($d_id=-1)
	{
		$delivery = $this->deliveries_model->get($d_id);
		if ($delivery == NULL) {
			// Error, customer do not exists
			$encoded_msg = $this->service_message->as_url_param(array (
				'type'		=> 'error',
				'title'		=> '<i class="attention icon"></i> Impossible to find the delivery',
				'content'	=> '',
				'time'		=> time()
			));
			redirect('/lists/?msg='.$encoded_msg);
		}
		
		$this->load->library('parser');
		$this->load->library('phpoffice');
		$this->load->helper('file');
		$this->load->model('orders_model');
		
		$data = array('date' => mysql_to_nice_date($delivery->delivery_date), 'orders' => array());
		
		$orders = $this->orders_model->get_list($delivery->id);
		foreach ($orders as $order)
		{
			$invoice = json_decode($order->invoice);
			
			$data['orders'][] = array(
				'customer' => $order->customer, 
				'total' => $invoice->total_balances);
		}
		
		$html = $this->parser->parse('accounting/tpl_payment_sheet', $data, TRUE);
		
		// Load and concat CSS for the invoice
		$css = read_file(APPPATH.'/views/invoices/table.css');
		$css .= read_file(APPPATH.'/views/invoices/image.css');
		$css .= read_file(APPPATH.'/views/invoices/site.css');
		
//		$html = '<style type="text/css">'.$css.'</style>'.$html;
//		echo $html;
//		return;

		$dompdf = $this->phpoffice->html_to_pdf($html, $css);
		
		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper('A5', 'portrait');
		$dompdf->set_option('fontHeightRatio', '0.6');

		// Render the HTML as PDF
		$dompdf->render();
		
		$filename = str_replace('/', '-', $delivery->delivery_date).'_payment_sheet.pdf';
		$filepath = $this->phpoffice->getFilename($filename, 'pdf');
		
		write_file($filepath, $dompdf->output());
		exec(APPPATH.'/PDFtoPrinter.exe "'.$filepath.'"');
		
		// Output the generated PDF to Browser
		$dompdf->stream($filename);
	}

	public function register_all($d_id=-1)
	{
		$delivery = $this->deliveries_model->get($d_id);
		if ($delivery == NULL) {
			// Error, do not exists
			$encoded_msg = $this->service_message->as_url_param(array (
				'type'		=> 'error',
				'title'		=> '<i class="attention icon"></i> Impossible to find the delivery',
				'content'	=> '',
				'time'		=> time()
			));
			redirect('/lists/?msg='.$encoded_msg);
		}
		
		$orders = $this->orders_model->get_list($d_id, TRUE);
//		$payments = $this->accounting_model->get_list($d_id);
//		$paid = array_column($payments, 'c_id');
//		
//		$list =  array();
//		foreach($orders as $order)
//		{
//			if ( ! in_array($order->c_id, $paid))
//			{
//				$list[] = $order->id;
//			}
//		}
		$list = array_column($orders, 'id');
		redirect('accounting/register/'.$d_id.'/1/?in_list=true&order='.urlencode(json_encode($list)));
	}
	
	/**
	 * Register payment for one or more orders
	 */
	public function register($d_id=-1, $i_num=-1)
	{
		$in_list = $this->input->get('in_list') === 'true';
		
		$o_id = -1;
		if ($in_list)
		{
			// get all orders, then keep the one of the index
			$orders = json_decode(urldecode($this->input->get('order')));
			//$orders = $this->orders_model->get_list($d_id, TRUE);
			if(is_numeric($i_num) && $i_num <= count($orders) && $i_num > 0)
			{
				$o_id = $orders[$i_num - 1];
			}
			else
			{
				// Error, wrong index cannot find related invoice
				log_message('error', 'Wrong index for invoice when printing [index='.$i_num.', d_id='.$d_id.']. Redirection...');
				$encoded_msg = $this->service_message->as_url_param(array (
					'type'		=> 'error',
					'title'		=> '<i class="attention icon"></i> Impossible to find the invoice',
					'content'	=> '',
					'time'		=> time()
				));
				redirect('/deliveries/view/'.$d_id.'/?msg='.$encoded_msg);
			}
		}
		else
		{
			// Else get directly the order (print/o_id)
			$o_id = $d_id;
		}
		
		$order = $this->orders_model->get($o_id);
		if ($order == NULL) {
			// Error, do not exists
			$encoded_msg = $this->service_message->as_url_param(array (
				'type'		=> 'error',
				'title'		=> '<i class="attention icon"></i> Impossible to find the order',
				'content'	=> '',
				'time'		=> time()
			));
			redirect('/lists/?msg='.$encoded_msg);
		}

		$delivery = $this->deliveries_model->get($order->d_id);
		if ($delivery == NULL) {
			// Maybe the delivery has been deleted, mock it to load the view
			$delivery = (object) array(
				'id'			=> -1,
				'l_id'			=> -1,
				'list_name'		=> 'Unknown',
				'delivery_date' => '2000/01/01'
			);
		}
		
		$modify = $this->input->get('modify') === 'true';
		
		if ($in_list)
		{
			$data['form_url'] = base_url('accounting/register/'.$order->d_id.'/'.$i_num.'/?in_list=true&order='.urlencode($this->input->get('order')));
			if ($modify)
			{
				$data['cancel_url'] = $data['form_url'];
				$data['form_url'] .= '&modify=true';
			}
			
			$data['modify_url'] = base_url('accounting/register/'.$order->d_id.'/'.$i_num.'/?modify=true&in_list=true&order='.urlencode($this->input->get('order')));
				
			if ($i_num + 1 <= count($orders) )
			{
				$next_index = $i_num + 1;
				$data['next_url'] = base_url('accounting/register/'.$order->d_id.'/'.$next_index.'/?in_list=true&order='.urlencode($this->input->get('order')));
			}
			if ($i_num - 1 > 0)
			{
				$prev_index = $i_num - 1;
				$data['prev_url'] = base_url('accounting/register/'.$order->d_id.'/'.$prev_index.'/?in_list=true&order='.urlencode($this->input->get('order')));
			}
		}
		else
		{
			$data['form_url'] = base_url('accounting/register/'.$order->id);
			if ($modify)
			{
				$data['cancel_url'] = $data['form_url'];
				$data['form_url'] .= '?modify=true';
			}
			
			$data['modify_url'] = base_url('accounting/register/'.$order->id.'/?modify=true');
		}
		
		$data['delivery'] = $delivery;
		$data['title'] = 'Register a payment for '.$order->customer;
		$data['order'] = $order;
		
		$this->service_message->load();
		
		$payment = $this->accounting_model->get_where($order->d_id, $order->c_id);
		
		
		if ($payment === NULL || $modify) 
		{
			/*
			If payment exist (ie $modify is TRUE), it's a modification of a previous payment.
			In that case, do not use current balance but registered previous balance.
			Be carefull of all these type of error.
			*/
			if ($payment === NULL)
			{
				$customer = $this->customers_model->get($order->c_id);	
				if ($customer != NULL)
				{
					$curr_balance = $customer->current_balance;	
				}
				else
				{
					$curr_balance = 0;
				}
				
				// Use actual unpaid orders
				$unpaids = $this->customers_model->get_unpaid_orders($order->c_id);
			}
			else
			{
				$paids = array_column(json_decode($payment->payments), NULL, 'id');
				
				// In case of modification, use previous balance instead of current one
				$curr_balance = $paids['-1']->payment;
				
				// Use actual unpaid order and add paid orders from payment
				$unpaids = $this->customers_model->get_unpaid_orders($order->c_id);
				$unpaids_ids = array_column($unpaids,'id');
				
				foreach ($paids as $paid)
				{
					if ($paid->id < 0)
					{
						continue;
					}
					elseif( ! in_array($paid->id, $unpaids_ids))
					{
						// If order is paid add it to the list of order to pay
						$o = $this->orders_model->get($paid->id);
						if ($o != NULL)
						{
							$unpaids[] = $o;
							$unpaids_ids = array_column($unpaids,'id');
						}
					}
				}
				// Adjusted payment value of orders to remive the received payment (reset to previous state)
				foreach($unpaids as $unpaid)
				{
					if (isset($paids[$unpaid->id]))
					{
						$unpaid->payment -= $paids[$unpaid->id]->payment;
					}
				}
				$order->payment -= $paids[$order->id]->payment;
			}

			$invoice = json_decode($order->invoice);

			$this->load->helper('form');
			$this->load->library('form_validation');
			
			$this->form_validation->set_rules('total_paid', 'Money received', 'required|numeric');
			$this->form_validation->set_rules(
				'pay[]', 
				'Money received', 
				'numeric');

			if ($this->form_validation->run() === TRUE) // Save selection
			{
				$success = TRUE;

				$include_balances = ($this->input->post('pay_all') === 'on') ? TRUE : FALSE;

				$new_bal = 0;
				$extra = 0;
				$update = array();
				$total_received = 0;

				if ($include_balances)
				{
					// Calcul 1: balancing the total paid on all the invoices
					$total_paid = floatval($this->input->post('total_paid'));
					$total_received = 0;//$total_paid;

					// Adjust total paid by adding balance (negative = overpayment from last time)
					if ($curr_balance < 0)
					{
						$sub_paid = $total_paid - $curr_balance;
					}
					else
					{
						$sub_paid = $total_paid;
					}

					// Pay first the current order
					{
						if ($sub_paid >= ($invoice->total - $order->payment))
						{
							$p = $invoice->total;
						}
						else
						{
							$p = $sub_paid;
						}
						$sub_paid -= $p;
						$total_received += $p;

						// Array used to update the db after or to show results
						$update[] = (object)array(
							'date' 		=> $invoice->date, 
							'payment'	=> format_kwacha($p),
							'total_paid'=> format_kwacha($order->payment + $p),
							'status'	=> (($order->payment + $p) >= $invoice->total) ? 'paid' : 'not_paid',
							'id' 		=> $order->id);
					}
					// Then pay each not_paid order
					foreach($unpaids as $unpaid)
					{
						if ($unpaid->id !== $order->id && ! empty($unpaid->invoice))
						{
							$inv = json_decode($unpaid->invoice);

							if ($sub_paid >= ($inv->total - $unpaid->payment)) // Do not forget to include previous payment
							{
								$p = ($inv->total - $unpaid->payment);
							} 
							else 
							{
								$p = $sub_paid;
							}
							$sub_paid -= $p;
							$total_received += $p;

							// Array used to update the db after or to show results
							$update[] = (object)array(
								'date' 		=> $inv->date, 
								'payment'	=> format_kwacha($p),
								'total_paid'=> format_kwacha($unpaid->payment + $p),
								'status'	=> (($unpaid->payment + $p) >= $inv->total) ? 'paid' : 'not_paid',
								'id' 		=> $unpaid->id);
						}
					}
					//At the end find the new balance
					if ($sub_paid > 0)
					{
						// Some money is remaining
						if ($curr_balance > 0)
						{
							// Use it to pay actual balance
							$new_bal = $curr_balance - $sub_paid;
							$extra = $sub_paid;
						}
						else
						{
							// Or set it as the new balance
							$new_bal = -$sub_paid;
							$extra = $sub_paid + $curr_balance;
						}
						
					}
					else 
					{
						// No money remains, keep current balance if it is due (positive amount)
						// Or set it to 0 if negative (because it has been used to the total paid)
						if ($curr_balance > 0)
						{
							$new_bal = $curr_balance;
							$extra = 0;
						}
						else
						{
							$new_bal = 0;
							$extra = $curr_balance;
						}
						//$new_bal = ($curr_balance < 0) ? 0 : $curr_balance;
					}
					$total_received += $extra;
				}
				else
				{
					// Calcul 2: use the manual indication for each balance	
					$paiments = $this->input->post('pay');
					$extra = floatval($this->input->post('paid_extra'));

					$over = 0; // Negative number for over paiment

					// First get paiment for each invoice
					foreach($unpaids as $unpaid)
					{
						if (key_exists($unpaid->id, $paiments) === TRUE &&  ! empty($unpaid->invoice))
						{
							$inv = json_decode($unpaid->invoice);
							$received = floatval($paiments[$unpaid->id]);

							$total_received += $received;

							// Array used to update the db after or to show results
							$update[] = (object)array(
								'date' 		=> $inv->date, 
								'payment'	=> format_kwacha($received),
								'total_paid' => format_kwacha($unpaid->payment + $received),
								'status'	=> (($unpaid->payment + $received) >= $inv->total) ? 'paid' : 'not_paid',
								'id' 		=> $unpaid->id);

							$diff = $inv->total - $received;
							if ($diff < 0)
							{
								// Over paiment
								$over += $diff;
							}
						}
					}
					// Then check extra and update the balance
					// If extra is positive number: decrease balance (money received from customer)
					// If extra is negative number: increase balance (invoices not fully paid)
					$new_bal = $curr_balance - $extra;

					// Include the extra in the total received
					$total_received += $extra;

					// Adjust with over paiment
					$new_bal += $over;
				}

				//Now update orders in database to save the payment
				$ok = TRUE;
				foreach ($update as $row)
				{
					$ok = $ok && $this->orders_model->update($row->id,
													  array(
														  $this->orders_model::FIELD_STATUS  => $row->status,
														  $this->orders_model::FIELD_PAYMENT  => $row->total_paid
													  )
													 );
				}
				
				//Small trick: add balance to payments to keep a trace of it
				$update[] = (object)array(
							'date' 		=> 'Old balance', 
							'payment'	=> format_kwacha($curr_balance),
							'status'	=> '',
							'id' 		=> -1);
				$update[] = (object)array(
							'date' 		=> 'New balance', 
							'payment'	=> format_kwacha($new_bal),
							'status'	=> '',
							'id' 		=> -2);
				$update[] = (object)array(
							'date' 		=> 'Extra paid', 
							'payment'	=> format_kwacha($extra),
							'status'	=> '',
							'id' 		=> -3);
				
				// Then add accounting row for this payment
				if ($payment === NULL)
				{
					$success = $ok && $this->accounting_model->create(
						array(
							$this->accounting_model::FIELD_DELIVERY_ID		=> $order->d_id,
							$this->accounting_model::FIELD_CUSTOMER_ID 		=> $order->c_id,
							$this->accounting_model::FIELD_TOTAL_DUE	 	=> $invoice->total_balances,
							$this->accounting_model::FIELD_TOTAL_PAID	 	=> $total_received,
							$this->accounting_model::FIELD_PAYMENTS		 	=> $update
					));
				}
				else
				{
					// In case of modification, update existing payment
					$success = $ok && $this->accounting_model->update(
						$payment->id,
						array(
							$this->accounting_model::FIELD_TOTAL_PAID	 	=> $total_received,
							$this->accounting_model::FIELD_PAYMENTS		 	=> $update
					));
				}

				// And update user balance
				$success = $success && $this->customers_model->update($order->c_id,
																	 array(
																		Customers_model::FIELD_BALANCE => $new_bal
																	 ));

				if ($success === TRUE)
				{
//					//Redirect, show msg success etc.
					$msg = array (
						'type'		=> 'success',
						'title'		=> '<i class="check icon"></i>Payment saved!',
						'content'	=> '',
						'time'		=> time()
					);
					if ($in_list)
					{
						$this->redirect->to('accounting/register/'.$order->d_id.'/'.$i_num, 
											array('in_list' => 'true', 
												  'order' => $this->input->get('order')),
											$msg);
					}
					else
					{
						$this->redirect->to('accounting/register/'.$order->id, 
											NULL,
											$msg);
					}
				}
				else
				{
					// Error
					$this->service_message->set(array (
						'type'		=> 'error',
						'title'		=> '<i class="exclamation triangle icon"></i> Cannot register the payment... Please try again',
						'content'	=> '',
						'time'		=> time()
					));
				}
			} 
			
			if ($payment != NULL)
			{
				$data['total_paid'] = $payment->total_paid;
				
				$paids = array_column(json_decode($payment->payments), NULL, 'id');
				
				// In case of modification, use payments to build the list of order to paid
				$data['to_pay'] = array();
				foreach($paids as $paid)
				{
					if ($paid->id !== $order->id)
					{
						$unpaid = $this->orders_model->get($paid->id);
						if ($unpaid != NULL)
						{
							$inv = json_decode($unpaid->invoice);
							$data['to_pay'][] = (object)array(
								'date' 	=> $inv->date, 
								'total' => format_kwacha($inv->total - $unpaid->payment + $paid->payment), 
								'id' 	=> $unpaid->id);
							$data['payments'][$paid->id] = $paid->payment;
						}
					} 
					else
					{
						$data['to_pay'][] = (object)array(
							'date' 	=> $invoice->date, 
							'total' => format_kwacha($invoice->total - $order->payment/* + $paid->payment*/),
							'id'	=> $order->id);
						$data['payments'][$order->id] = $paid->payment;
					}
				}
				// Browse unpaids to find those are missing in the previous list
				foreach($unpaids as $unpaid)
				{
					if ( ! array_key_exists($unpaid->id, $paids) && ! empty($unpaid->invoice))
					{
						$inv = json_decode($unpaid->invoice);
						$data['to_pay'][] = (object)array(
							'date' 	=> $inv->date, 
							'total' => format_kwacha($inv->total - $unpaid->payment), 
							'id' 	=> $unpaid->id);
						$data['payments'][$unpaid->id] = NULL;
					}
				}
				
				
				// Use previous balance en registered extra
				if (isset($paids['-3']))
				{
					if ($paids['-1']->payment < 0)
					{
						$data['payments']['extra'] = $paids['-2']->payment + $paids['-3']->payment;
						// if balance < 0, Extra show only the balance reduced by the extra paid
					}
					else
					{
						$data['payments']['extra'] = $paids['-3']->payment;
					}
				}
				else
				{
					$data['payments']['extra'] = NULL;	
				}
				//$data['payments']['extra'] = isset($paids['-3']) ? $paids['-3']->payment : NULL;
				$data['balance'] = $paids['-1']->payment;
			}
			else
			{
				$data['total_paid'] = NULL;
				
				$data['to_pay'] = array();
				$data['to_pay'][] = (object)array(
					'date' 	=> $invoice->date, 
					'total' => format_kwacha($invoice->total - $order->payment),
					'id'	=> $order->id);
					
				$data['payments'][$order->id] = NULL;
				
				foreach($unpaids as $unpaid)
				{
					if ($unpaid->id !== $order->id && ! empty($unpaid->invoice))
					{
						$inv = json_decode($unpaid->invoice);
						$data['to_pay'][] = (object)array(
							'date' 	=> $inv->date, 
							'total' => format_kwacha($inv->total - $unpaid->payment), 
							'id' 	=> $unpaid->id);
						$data['payments'][$unpaid->id] = NULL;
					}
				}
				
				$data['payments']['extra'] = NULL;
				$data['balance'] = format_kwacha($curr_balance);
			}

			$data['total_due'] = $invoice->total_balances;

			// Load templates
			$this->load->view('templates/header', $data);
			$this->load->view('accounting/payment_form', $data);
			$this->load->view('templates/footer', $data);
		}
		else
		{
			
			$data['total_due'] = $payment->total_due;
			$data['total_received'] = $payment->total_paid;
			if ( ! empty($payment->payments))
			{
				$data['paids'] = json_decode($payment->payments);
			}
			else
			{
				$data['paids'] = array();
			}

			// Load templates
			$this->load->view('templates/header', $data);
			$this->load->view('accounting/paid_view', $data);
			$this->load->view('templates/footer', $data);
		}
		
	}
	
	/**
	 * Close an invoice marking it as "paid" and adding a credit to it.
	 * @param integer $o_id order id
	 */
	public function close_invoice($o_id)
	{
		$order = $this->orders_model->get($o_id);
		if ($order == NULL || $order->invoice == NULL) {
			// Error, do not exists
			$encoded_msg = $this->service_message->as_url_param(array (
				'type'		=> 'error',
				'title'		=> '<i class="attention icon"></i> Impossible to find the invoice',
				'content'	=> '',
				'time'		=> time()
			));
			redirect('/lists/?msg='.$encoded_msg);
		}
		
		$invoice = json_decode($order->invoice);
		
		$invoice->unpaids[] = array('date' => 'Credit', 'total' => format_kwacha(-$invoice->total));
		$invoice->total_balances = format_kwacha($invoice->total_balances - $invoice->total);
		
		$success = $this->orders_model->update($order->id, 
										  array(
											$this->orders_model::FIELD_INVOICE => json_encode($invoice),
											$this->orders_model::FIELD_STATUS  => 'paid')
										 );
		if ($success === FALSE)
		{
			$msg = array (
				'type'		=> 'error',
				'title'		=> '<i class="exclamation triangle icon"></i> Sorry, we cannot close the invoice...',
				'content'	=> '',
				'time'		=> time()
			);
		}
		else
		{
			$msg = array (
				'type'		=> 'success',
				'title'		=> '<i class="check icon"></i> Invoice close!',
				'content'	=> '',
				'time'		=> time()
			);
		}
		$this->redirect->to('/customers/edit/'.$order->c_id, NULL, $msg);
		
	}
	
	public function delete($o_id=-1)
	{
		$order = $this->orders_model->get($o_id);
		
		if ($order === NULL) {
			// Error, do not exists
			$encoded_msg = $this->service_message->as_url_param(array (
				'type'		=> 'error',
				'title'		=> '<i class="attention icon"></i> Impossible to find the order',
				'content'	=> '',
				'time'		=> time()
			));
			redirect('/lists/?msg='.$encoded_msg);
		}

		$success = $this->accounting_model->delete($order->d_id, $order->c_id);
		
		if ($success === FALSE)
		{
			$msg = array (
				'type'		=> 'error',
				'title'		=> '<i class="exclamation triangle icon"></i> Sorry, we cannot delete the payment...',
				'content'	=> '',
				'time'		=> time()
			);
			$this->redirect->to('/deliveries/view/'.$order->d_id, NULL, $msg);
		}
		else
		{
			$msg = array (
				'type'		=> 'success',
				'title'		=> '<i class="check icon"></i> Payment deleted!',
				'content'	=> '',
				'time'		=> time()
			);
			$this->redirect->to('/deliveries/view/'.$order->d_id, NULL, $msg);
		}
	}
	
	/**
	 * Generate HTML for the accounting sheet based on tpl_accounting file.
	 *
	 * @return string html
	 */
	private function accounting_sheet($delivery)
	{
		$this->load->library('parser');
		
		$expenses_cats = $this->config->item('expenses_cats');
		$accounting_cats = $this->config->item('accounting_cats');
		//Create key/position array to have the payments in the right order
		$acc_cats_indexes = array_flip(array_keys($accounting_cats));
		
		$payments = $this->accounting_model->get_list($delivery->id);
		$totals = array_fill_keys(array_keys($accounting_cats), 0);
		$total_paid = 0;
		$total_due = 0;
		$expenses = NULL;
		foreach($payments as $key => $p)
		{
			if ($p->c_id != -1)// Exclude expenses row
			{
				$total_paid += $p->total_paid;
				$total_due += $p->total_due;
				$p->details = json_decode($p->details, TRUE);
				$p->details_row = array();
				if ($p->details != NULL)
				{
					// Sum accounting details to get total per category
					foreach($p->details as $key => $item)
					{
						if (key_exists($key, $totals))
						{
							// Sum for accounting totals
							$totals[$key] += $item;
							// Format data for the parser (use position from accounting_cats)
							$p->details_row[$acc_cats_indexes[$key]] = array('value' => $item == 0 ? '-' : format_kwacha($item));
						}
						else
						{
							// Skip that case (accounting cat doesn't exists in the config file)
							$totals[$key] = $item;
							$acc_cats_indexes[$key] = count($accounting_cats);
							$p->details_row[$acc_cats_indexes[$key]] = array('value' => $item == 0 ? '-' : format_kwacha($item));
							$accounting_cats[$key] = $key;
						}
					}
					// Sort the array by key
					ksort($p->details_row);
				}
				else
				{
					// Small bug fix if payment row has no registered payment
					$p->details_row = array_fill_keys(array_keys($accounting_cats), array('value' => '-'));
				}
				
				$customer = $this->customers_model->get($p->c_id);
				if ($customer == NULL)
				{
					$p->customer = 'Unknown'; // If the customer has been deleted
				}
				else
				{
					$p->customer = $customer->name;
				}
			}
			else
			{
				$expenses = $p;
				unset($payments[$key]);
			}
		}
		if ($expenses == NULL)
		{
			// Small fix in case the accounting sheet is displayed before the finalize step
			$expenses = (object) [
				'details' => json_encode(array('change' => 0, 'cash' => 0))
			];
			log_message('error', 'Impossible to find the expenses registrement for accounting... (d_id='.$delivery->id.')');
		}
		
		$date = new DateTime($delivery->delivery_date);
		$acc_data['date'] = $date->format('d/m/Y');
		$acc_data['payments'] = $payments;
		$acc_data['total_due'] = format_kwacha($total_due);
		$acc_data['total_paid'] = format_kwacha($total_paid);
		
		// Format totals for the parser and to display decimals
		foreach($totals as $key => $t)
		{
			if ($t == 0)
			{
				unset($totals[$key]);
				foreach($payments as $keyp => $p)
				{
					unset($p->details_row[$acc_cats_indexes[$key]]);
				}
				unset($accounting_cats[$key]);
			}
			else
			{
				$acc_data['totals'][$key]['value'] = format_kwacha($t);
			}
		}
		
		// Format accounting cat for the parser
		foreach ($accounting_cats as $cat_title)
		{
			$acc_data['accounting_cats'][] = array('title' => $cat_title);
		}
		
		// Calculate expenses
		$expenses->details = json_decode($expenses->details);
		$expenses_total = 0;
		$acc_data['expenses'] = array();
		foreach($expenses->details as $key => $val)
		{
			if ($key != 'change')
			{
				$expenses_total += $val;
			}
			
			// Format expenses for the parser
			if(key_exists($key, $expenses_cats))
			{
				$acc_data['expenses'][] = array( 'title' => $expenses_cats[$key],
											   	 'value' => format_kwacha($val));
			}
		}
		$expenses_total -= $expenses->details->change;
		
		$acc_data['expenses_cash'] = format_kwacha($expenses->details->cash);
		$acc_data['expenses_change'] = format_kwacha($expenses->details->change);
		$acc_data['expenses_total'] = format_kwacha($expenses_total);
		$acc_data['expenses_diff'] = format_kwacha($expenses_total - $total_paid);
		
		return $this->parser->parse('accounting/tpl_accounting', $acc_data, TRUE);
	}
	
	/**
	 * Generate Excel file that summaries the payment of a customer
	 * @param integer $c_id the customer id 
	 */
	public function download_payments($c_id)
	{
		
		$customer = $this->customers_model->get($c_id);
		if ($customer == NULL)
		{
			// Error, do not exists
			$encoded_msg = $this->service_message->as_url_param(array (
				'type'		=> 'error',
				'title'		=> '<i class="attention icon"></i> Impossible to find the customer.',
				'content'	=> '',
				'time'		=> time()
			));
			redirect('/customers/?msg='.$encoded_msg);
		}
		
		$orders = $this->customers_model->get_orders($c_id, NULL, NULL);
		if ($orders == NULL)
		{
			// Error, do not exists
			$encoded_msg = $this->service_message->as_url_param(array (
				'type'		=> 'error',
				'title'		=> '<i class="attention icon"></i> No order found',
				'content'	=> '',
				'time'		=> time()
			));
			redirect('/customers/edit/'.$c_id.'/?msg='.$encoded_msg);
		}
		
		$data = array();
		
		foreach($orders as $order)
		{
			if ($order->invoice != NULL)
			{
				$inv = json_decode($order->invoice);
				$row = array($inv->date, $inv->total, 0, 0);
				$data[$order->d_id] = $row;
			}
		}
		
		$initial_balance = -1;
		$payments = $this->accounting_model->get_customer($c_id);
		if ($payments != NULL)
		{
			foreach($payments as $payment)
			{
				if (isset($data[$payment->d_id]))
				{
					$data[$payment->d_id][2] = $payment->total_paid;
					if ($initial_balance == -1)
					{
						//Get initial balance from first payment
						$paids = array_column(json_decode($payment->payments), 'payment', 'id');
						$initial_balance = $paids[-1];
					}
				}
			}
		}
		if ($initial_balance == -1)
		{
			$initial_balance = 0;
		}
		
		ksort($data); // sort by delivery id == older to more recent
		
		$this->load->library('PhpOffice');
		// Create new Spreadsheet object
		$spreadsheet = $spreadsheet = $this->phpoffice->new_spreadsheet();
		
		$i = 1;
		$spreadsheet->getActiveSheet()
					->setCellValue('A'.$i, 'Date')
					->setCellValue('B'.$i, 'Invoice')
					->setCellValue('C'.$i, 'Payment')
					->setCellValue('D'.$i, 'Balance');
		$i++;
		
		foreach ($data as $row)
		{
			$spreadsheet->getActiveSheet()
					->setCellValue('A'.$i, $row[0])
					->setCellValue('B'.$i, $row[1])
					->setCellValue('C'.$i, $row[2]);
			if ($i == 2)
			{
				$spreadsheet->getActiveSheet()
					->setCellValue('D'.$i, '=B2-C2+'.$initial_balance);
			}
			else
			{
				$spreadsheet->getActiveSheet()
					->setCellValue('D'.$i, '=B'.$i.'-C'.$i.'+D'.($i-1));
			}
			$i++;
		}
		
//		$this->load->library('table');
//		echo $this->table->generate($data);
		
		// Download
		$this->phpoffice->download($spreadsheet, $customer->name.'-payments.xlsx');
	}
}
