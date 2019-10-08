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
class Invoices extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('deliveries_model');
		$this->load->model('lists_model');
		$this->load->model('veget_model');
		$this->load->model('orders_model');
		$this->load->model('customers_model');
		$this->load->config('vegetables');
	}
	
	public function generate_all($d_id=-1)
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
		
		// Generation process
		// 	- For each order
		//		- Generate JSON for invoice
		//		- Insert JSON in db
		//		- Update status to not_printed
		//	- For delivery
		//		- Update status to prepare
		$delivery->vegetables = json_decode($delivery->vegetables);

		// Reindex vegetables array by ids
		$vegetables = array_column($delivery->vegetables, NULL, 'id');
		
		$orders = $this->orders_model->get_list($delivery->id, TRUE);
		foreach($orders as $order)
		{
			$invoice = $this->orders_model->generate_invoice($order, $vegetables);
			
			$ok = $this->orders_model->update($order->id, 
											  array(
											  	$this->orders_model::FIELD_INVOICE => json_encode($invoice),
											  	$this->orders_model::FIELD_STATUS  => 'not_printed')
											 );
		}
		
		$ok = $this->deliveries_model->update($d_id, array($this->deliveries_model::FIELD_STATUS => 'prepare'));
		
		if ($ok === FALSE)
		{
			$encoded_msg = $this->service_message->as_url_param(array (
				'type'		=> 'error',
				'title'		=> '<i class="attention icon"></i> Impossible to generate the invoices',
				'content'	=> '',
				'time'		=> time()
			));
			redirect('/deliveries/view/'.$d_id.'?msg='.$encoded_msg);
		}
		else
		{
			redirect('/invoices/print_all/'.$d_id);
		}
	}

	public function regenerate($o_id=-1)
	{
		$order = $this->orders_model->get($o_id);
		if ($order == NULL) {
			// Error, do not exists
			$msg = array (
				'type'		=> 'error',
				'title'		=> '<i class="attention icon"></i> Impossible to find the order',
				'content'	=> '',
				'time'		=> time()
			);
			$this->redirect->to('/lists/', NULL, $msg);
		}

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
		
		$msg = array (
			'type'		=> 'success',
			'title'		=> '<i class="check icon"></i> The invoice is up-to-date',
			'content'	=> '',
			'time'		=> time()
		);
		$this->redirect->to('invoices/view/'.$order->id, NULL, $msg);
	}
	
	public function print_all($d_id=-1)
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
		$list =  array_column($orders, 'id');
		redirect('invoices/view/'.$d_id.'/1/?in_list=true&order='.urlencode(json_encode($list)));
	}
	
	public function print($o_id=-1)
	{
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
		
		$invoice = json_decode($order->invoice);
		$this->load->library('PhpOffice');
		
		$tpl_config = $this->config->item('tpl_invoice');
		
//		To generate the invoice from Excel template (issue to generate PDF after)
		/*
		
		// Create new Spreadsheet object
		$spreadsheet = $this->phpoffice->load_file($tpl_config['filepath'].$tpl_config['filename']);
		$active_sheet = $spreadsheet->getActiveSheet();
		
		$active_sheet->setCellValue($tpl_config['cell_name'], $invoice->customer);
		$active_sheet->setCellValue($tpl_config['cell_date'], $invoice->date);
		$active_sheet->setCellValue($tpl_config['cell_num'], $invoice->number);
		
		$base_row_other = $tpl_config['base_row_other'];
		$base_row_veg = $tpl_config['base_row_vege'];
		
		$row = $base_row_veg;
		foreach ($invoice->vegets as $r => $data_row) 
		{
			$row++;
			
			$active_sheet->insertNewRowBefore($row, 1);

			$active_sheet
				->setCellValue($tpl_config['col_desc'].$row, $data_row->description)
				->setCellValue($tpl_config['col_unit'].$row, $data_row->unit)
				->setCellValue($tpl_config['col_qty'].$row, $data_row->qty)
				->setCellValue($tpl_config['col_price'].$row, $data_row->price)
				->setCellValue($tpl_config['col_amount'].$row, $data_row->amount);
		}
		$active_sheet->removeRow($base_row_veg, 1);
		
		$row = $base_row_other;
		foreach ($invoice->non_vegets as $r => $data_row) 
		{
			$row ++;
			
			$active_sheet->insertNewRowBefore($row, 1);

			$active_sheet
				->setCellValue($tpl_config['col_desc'].$row, $data_row->description)
				->setCellValue($tpl_config['col_unit'].$row, $data_row->unit)
				->setCellValue($tpl_config['col_qty'].$row, $data_row->qty)
				->setCellValue($tpl_config['col_price'].$row, $data_row->price)
				->setCellValue($tpl_config['col_amount'].$row, $data_row->amount);
		}
		$active_sheet->removeRow($base_row_other, 1);
		
		$shift = count($invoice->non_vegets) + count($invoice->vegets) - 2 ;// -2 base rows
		
		$row_total = $tpl_config['row_total'] + $shift; 
		
		$active_sheet->setCellValue($tpl_config['col_total'].$row_total, $invoice->total);
		
		
		$base_row_bal = $tpl_config['base_row_bal'];
		if (count($invoice->unpaids) > 0)
		{
			$row = $base_row_bal + $shift;
			
			foreach ($invoice->unpaids as $r => $data_row) 
			{
				$row ++;

				$active_sheet->insertNewRowBefore($row, 1);

				$active_sheet
					->setCellValue($tpl_config['col_date_bal'].$row, $data_row->date)
					->setCellValue($tpl_config['col_amount_bal'].$row, $data_row->total);
			}
			$active_sheet->removeRow($base_row_bal + $shift, 1);
			
			$shift += count($invoice->unpaids) - 1; // -1 base row
			$row_total = $tpl_config['row_big_total'] + $shift;
			
			$active_sheet->setCellValue($tpl_config['col_total'].$row_total, $invoice->total_balances);
			
			// Bug fix
			$active_sheet->getRowDimension($row_total-1)->setVisible(TRUE);
		}
		else
		{
			$active_sheet->removeRow($base_row_bal + $shift, 3);
		}
		
		// Download
		$this->phpoffice->download($spreadsheet, $invoice->date.'_'.$order->customer.'.xlsx');
		*/
		
		$this->load->library('parser');
		$this->load->helper('file');
		$this->load->helper('path');
		
		$invoice->header_url = APPPATH.'/../'.$tpl_config['header_img'];
		
		$html = $this->parser->parse('invoices/tpl_invoice', $invoice, TRUE);
		
		// Load and concat CSS for the invoice
		$css = read_file(APPPATH.'/views/invoices/table.css');
		$css .= read_file(APPPATH.'/views/invoices/image.css');
		$css .= read_file(APPPATH.'/views/invoices/site.css');

		$dompdf = $this->phpoffice->html_to_pdf($html, $css);
		
		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper('A5', 'portrait');
		$dompdf->set_option('fontHeightRatio', '0.6');

		// Render the HTML as PDF
		$dompdf->render();
		
		$filename = str_replace('/', '-', $invoice->date).'_'.$order->customer.'.pdf';
		$filepath = $this->phpoffice->getFilename($filename, 'pdf');
		
		write_file($filepath, $dompdf->output());
		
		if ($order->status === 'not_printed')
		{
//		 	pclose(popen("start /B ". set_realpath(APPPATH.'/PDFtoPrinter.exe').' "'.$filepath.'"', "r"));  
		
			passthru('start '.APPPATH.'/PDFtoPrinter.exe "'.$filepath.'"');
			
			// To allow to print again without issue, even in accounting, 
			// update status only if the order is in stat 'not_printed'
			$ok = $this->orders_model->update($order->id, 
												  array(
													$this->orders_model::FIELD_STATUS  => 'printed')
												 );
		}
		
		// Output the generated PDF to Browser
		$dompdf->stream($filename);
	}
	
	/**
	 * Display an invoice
	 * 
	 * @param int, the order id to show a unique invoice OR the delivery id to show the next/prev buttons to browse invoices
	 * @param int, index of the invoice if the first param is delivery id
	 *
	 * To show the invoice in 'browsing invoices' mode, the GET parameter 'in_list' must be equals to 'true'.
	 * And the GET parameter 'order' must be json encoded array of order ids.
	 *
	 * //TODO simplify these parameters... ('in_list' can be removed when $d_id is a delivery id)
	 */
	public function view($d_id=-1, $i_num=-1)
	{
		$in_list = $this->input->get('in_list') === 'true';
		
		$o_id = -1;
		if ($in_list)
		{
//			// If reached for print_all (print/d_id/o_id)
//			$delivery = $this->deliveries_model->get($d_id);
//			if ($delivery == NULL) {
//				// Error, do not exists
//				$encoded_msg = $this->service_message->as_url_param(array (
//					'type'		=> 'error',
//					'title'		=> '<i class="attention icon"></i> Impossible to find the delivery',
//					'content'	=> '',
//					'time'		=> time()
//				));
//				redirect('/lists/?msg='.$encoded_msg);
//			}
			
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
		
		$this->load->model('accounting_model');
		$payment = $this->accounting_model->get_where($order->d_id, $order->c_id);
		$data['payment_registred'] = ($payment !== NULL);
		
		$this->load->library('parser');
		$tpl_config = $this->config->item('tpl_invoice');
		
		$invoice = json_decode($order->invoice);
		$invoice->header_url = base_url($tpl_config['header_img']);
		
		$html = $this->parser->parse('invoices/tpl_invoice', $invoice, TRUE);
		
		$this->service_message->load();
		
		$data['delivery'] = $delivery;
		$data['title'] = 'Invoice for '.$order->customer;
		$data['order'] = $order;
		$data['invoice'] = $html;
		
		if ($in_list)
		{
			if ($i_num + 1 <= count($orders) )
			{
				$next_index = $i_num + 1;
				$data['next_url'] = base_url('invoices/view/'.$order->d_id.'/'.$next_index.'/?in_list=true&order='.urlencode($this->input->get('order')));
			}
			if ($i_num - 1 > 0)
			{
				$prev_index = $i_num - 1;
				$data['prev_url'] = base_url('invoices/view/'.$order->d_id.'/'.$prev_index.'/?in_list=true&order='.urlencode($this->input->get('order')));
			}
		}
		
		// Load templates
		$this->load->view('templates/header', $data);
		$this->load->view('invoices/view', $data);
		$this->load->view('templates/footer', $data);
	}
	
	
}
