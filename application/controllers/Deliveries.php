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
class Deliveries extends CI_Controller {

	
	//protected $date_regex = '/^(?P<date0>\d{2}(\/|-)\d{2}(\/|-)(\d{2}|\d{4}))$|^(?P<date1>\d{2}(\/|-)\d{2})$/';
	public const STATUS = array(
				'collect' 		=> 'Collecting vegetables',
				'prepare' 		=> 'Preparing delivery',
				'accounting'	=> 'Accounting',
				'closed'		=> 'Closed'
		);
	
	public function __construct()
	{
		parent::__construct();
		$this->load->model('deliveries_model');
		$this->load->model('lists_model');
	}
	
	public function list()
	{
		// Fill datas for the template pages
		$data['title'] = 'Deliveries';
		
		// Show service messages from other pages
		$this->service_message->load();
		
		// Load the templates in order with the data to print
		$this->load->view('templates/header', $data);
		$this->load->view('templates/footer', $data);
	}
	
	public function view($d_id=-1)
	{
		$this->load->config('vegetables');
		$this->load->model('orders_model');
		$this->load->model('accounting_model');
		$this->load->model('veget_model');
		
		$delivery = $this->deliveries_model->get($d_id);
		if ($delivery == NULL) {
			// Error, customer do not exists
			$encoded_msg = $this->service_message->as_url_param(array (
				'type'		=> 'error',
				'title'		=> '<i class="attention icon"></i> Impossible to find the delivery ',
				'content'	=> '',
				'time'		=> time()
			));
			redirect('/lists/?msg='.$encoded_msg);
		}
		
		// Show service messages from other pages
		$this->service_message->load();
		
		$delivery->vegetables = json_decode($delivery->vegetables);
		$this->veget_model->sort_vegetables_array($delivery->vegetables);
		
		$list = $this->lists_model->get($delivery->l_id);
		
		$orders = $this->orders_model->get_list($delivery->id);
		
		$prices = array_column($delivery->vegetables, 'price', 'id');
		$delivery->quantities = array_fill_keys(array_column($delivery->vegetables, 'id'), 0.00);
		$big_total = 0;
		
		// Loop to calculate in same time the total of each order and to find the quantity for each vegetable
		// If an order as a vegetable not in the list, we add it to the list
		foreach($orders as $order)
		{
			$order->vegetables = json_decode($order->vegetables, TRUE);
			$order->total = 0.00;
			foreach($order->vegetables as $veg_id => $veg_qtt)
			{
				if (key_exists($veg_id, $prices))
				{
					$order->total += $prices[$veg_id] * $veg_qtt;
					$delivery->quantities[$veg_id] += $veg_qtt;
				} 
				else 
				{
					$veg = $this->veget_model->get($veg_id);
					if ($veg != NULL)
					{
						//Okay, now add the price to the list and add the veg to the list
						$prices[$veg->id] = $veg->price;
						$delivery->quantities[$veg->id] = $veg_qtt; //insert new veg in the qtt list
						$veg->not_from_list = TRUE;
						$delivery->vegetables[] = $veg;
						
						// Obviously, compute total for the order
						$order->total += $veg->price * $veg_qtt;
					} 
					else 
					{
						unset($order->vegetables[$veg_id]);
					}
				}
			}
			$big_total += $order->total;
			
			$payment = $this->accounting_model->get_where($order->d_id, $order->c_id);
			$order->payment_registred = ($payment !== NULL);
		}
		
		// Load the templates in order with the data to print
		$data['title'] = mysql_to_nice_date($delivery->delivery_date);
		$data['delivery'] = $delivery;
		$data['units'] = $this->config->item('units');

		$data['vegetables'] = $delivery->vegetables;
		$data['orders'] = $orders;
		$data['big_total'] = $big_total;
		switch($delivery->status)
		{
			case 'collect':
				$data['nb_orders'] = $this->orders_model->get_count($delivery->id);
				$data['collect_step'] = 'active';
				$data['prepare_step'] = 'disabled';
				$data['accounting_step'] = 'disabled';
				break;
			case 'prepare':
				$data['collect_step'] = 'completed';
				$data['prepare_step'] = 'active';
				$data['accounting_step'] = 'disabled';
				break;
			case 'accounting':
				$data['nb_payments'] = $this->orders_model->get_count($delivery->id) - $this->accounting_model->get_count($delivery->id);
				$data['collect_step'] = 'completed';
				$data['prepare_step'] = 'completed';
				$data['accounting_step'] = 'active';
				break;
			case 'closed':
			default:
				$data['collect_step'] = 'completed';
				$data['prepare_step'] = 'completed';
				$data['accounting_step'] = 'completed';
				break;
		}
		
		// Use Javascript document.location to automaticaly export the vegetable list
		if ($this->input->get('export_list') == 'true')
		{
			$data['download_this'] = base_url('deliveries/export_list/'.$d_id);
		}
		elseif ($this->input->get('print_paysheet') == 'true')
		{
			$data['download_this'] = base_url('/accounting/print_payment_sheet/'.$d_id);
		}
		
		// Load templates
		$this->load->view('templates/header', $data);
		$this->load->view('deliveries/view', $data);
		$this->load->view('templates/footer', $data);
	}

	public function create($l_id)
	{
		$this->load->helper('form');
		$this->load->library('form_validation');
				
		$list = $this->lists_model->get($l_id);
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
		
		$delivery = array (
			'id' => -1,
			'l_id' => $l_id,
			'delivery_date' => date('d/m/Y', strtotime('next '.week_info()['days'][$list->day_of_week])), 
			'vegetables' => json_encode(array())
		);
		
		if ($this->_run_form_validation() === TRUE) 
		{
			// Save delivery into db
			$id = $this->deliveries_model->create($l_id);
			if ($id !== FALSE)
			{
				// Success, go back to list and show message
				$delivery = $this->deliveries_model->get($id);
				$encoded_msg = $this->service_message->as_url_param(array (
					'type'		=> 'success',
					'title'		=> '<i class="check circle icon"></i> '.$delivery->delivery_date.' successfully created!',
					'content'	=> 'Now please select vegetables for this delivery',
					'time'		=> time()
				));
				
				redirect('/deliveries/edit_vegetables/'.$delivery->id.'/new/?msg='.$encoded_msg);	
				
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
		$data['title'] = 'New delivery';
		$data['submit_btn'] = 'Next';
		$data['is_editing'] = FALSE;
		$data['back_link'] = base_url($this->redirect->build_url('/lists/view/'.$list->id));
		$data['form_url'] = 'deliveries/create/'.$list->id;
		$data['list'] = $list;
		$data['delivery'] = $delivery;
		
		$this->load->view('templates/header', $data);
		$this->load->view('deliveries/edit_form', $data);
		$this->load->view('templates/footer', $data);
	}
	
	/**
	 *	
	 * @param int delivery id
	 * @param boolean is the page open after creation of the list ?
  	 */
	public function edit_vegetables($d_id=-1, $new=FALSE)
	{
		$this->load->helper('form');
		$this->load->config('vegetables');
		$this->load->model('veget_model');
		
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
		
		$list_id = $delivery->l_id;
		$list = $this->lists_model->get($list_id);
		
		
		$selected_veget = $this->input->post('vegetables[]');
		
		if ($selected_veget !== NULL) // Save selection
		{
			$vegetables_all = $this->lists_model->get_vegetables($list_id);
			$vegetables_updated = array();
			foreach($vegetables_all as $veget)
			{
				// For each vegetables of the delivery list,
				// Use the new price if the vege is available for this delivery
				if (key_exists($veget->id, $selected_veget) && key_exists('available', $selected_veget[$veget->id]))
				{
					$veget->price = $selected_veget[$veget->id]['price'];
					$veget->unit = $selected_veget[$veget->id]['unit'];
					$vegetables_updated[] = $veget; // Store available vegets
				}
			}
			
			$this->deliveries_model->update_vegetables($d_id, $vegetables_updated);
			
			if ($new === FALSE)
			{
				$export_list = '';
			}
			else
			{
				$export_list = 'export_list=true&';
			}
			// Success, go back to list and show message
			$encoded_msg = $this->service_message->as_url_param(array (
				'type'		=> 'success',
				'title'		=> '<i class="lemon outline icon"></i> Vegetable list successfully saved!',
				'content'	=> '',
				'time'		=> time()
			));
			redirect('/deliveries/view/'.$d_id.'/?'.$export_list.'msg='.$encoded_msg.'#vegetables');
		}
		else // Show list
		{
			$this->service_message->load();
		
			// Load the templates in order with the data to print
			$data['title'] = 'Vegetables of '.mysql_to_nice_date($delivery->delivery_date);
			$data['delivery'] = $delivery;
			$data['list'] = $list;

			$vegetables = json_decode($delivery->vegetables);
			$vegetables_all = $this->lists_model->get_vegetables($list_id);
			$this->veget_model->sort_vegetables_array($vegetables_all);
			
			// Get vegetables list
			$data['vegetables_ids'] = array_column($vegetables, 'id');
			$data['vegetables_prices'] = array_replace(
				array_column($vegetables_all, 'price', 'id'),
				array_column($vegetables, 'price', 'id')
			);
			$data['vegetables_units'] = array_replace(
				array_column($vegetables_all, 'unit', 'id'),
				array_column($vegetables, 'unit', 'id')
			);
			$data['vegetables_all'] = $vegetables_all;
			$data['units'] = $this->config->item('units');
			
			if ($new === FALSE)
			{
				$data['form_url'] = 'deliveries/edit_vegetables/'.$d_id;
			}
			else
			{
				$data['form_url'] = 'deliveries/edit_vegetables/'.$d_id.'/new';
			}

			// Load templates
			$this->load->view('templates/header', $data);
			$this->load->view('deliveries/edit_veget', $data);
			$this->load->view('templates/footer', $data);
		}
	}

	public function export_list($d_id=-1)
	{
		$this->load->config('vegetables');
		$this->load->library('PhpOffice');
		
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
		
		$vegetables = json_decode($delivery->vegetables);
		
		$units = $this->config->item('units');
		$tpl_config = $this->config->item('tpl_export_veg_list');
		
		if ($tpl_config == NULL OR empty($tpl_config))
		{
			$tpl_config = array(
				'filepath'		=> NULL, // APPPATH.'/views/excel/',
				'filename'		=> NULL, //'Vegetable Liste.xlsx',
				'base_row_vege'	=> 2, // Row to use to insert vegetables
				'base_row_other'=> 1, // Row to use to insert other items
				'column_name'	=> 'A',
				'column_price'	=> 'B',
				'column_unit'	=> 'C',
				'column_order'	=> 'D',
				'column_total'	=> 'E'
			);
			
			$spreadsheet = $this->phpoffice->new_spreadsheet();
		}
		else
		{
			// Create new Spreadsheet object
			$spreadsheet = $this->phpoffice->load_file($tpl_config['filepath'].$tpl_config['filename']);	
		}
		/*
		 Columns: A = Description, B = price, C = unit, D = order, E = Kwacha
		 Rows	: 3 = non-vegetables, 4 = vegetables, 5 = total Kwacha
		 */
		$base_row_other = $tpl_config['base_row_other'];
		$base_row_veg = $tpl_config['base_row_vege'];
		
		$row_veg = $base_row_veg;
		$row_other = $base_row_other;
		foreach ($vegetables as $r => $data_row) {
			if ($data_row->accounting_cat !== NULL && $data_row->accounting_cat !== 'veg')
			{
				$row_veg++;
				$row = ++$row_other;
			} 
			else
			{
				$row = ++$row_veg;
			}
				$spreadsheet->getActiveSheet()->insertNewRowBefore($row, 1);

				$spreadsheet->getActiveSheet()
					->setCellValue($tpl_config['column_name'].$row, $data_row->name)
					->setCellValue($tpl_config['column_price'].$row, $data_row->price)
					->setCellValue($tpl_config['column_unit'].$row, element(strtolower($data_row->unit), $units))
	//				->setCellValue($tpl_config['column_order'].$row, $data_row['quantity'])
					->setCellValue($tpl_config['column_total'].$row, '='.$tpl_config['column_price'].$row.'*'.$tpl_config['column_order'].$row);
		}
		$spreadsheet->getActiveSheet()->removeRow($base_row_other, 1);
		$spreadsheet->getActiveSheet()->removeRow($row_other, 1);
		
		$row--;
		
		$spreadsheet->getActiveSheet()
					->setCellValue($tpl_config['column_total'].$row, '=SUM('.$tpl_config['column_total'].$tpl_config['base_row_other'].':'.$tpl_config['column_total'].($row - 1).')');

		// Download
		$this->phpoffice->download($spreadsheet, $delivery->delivery_date.'_vegetables_list.xlsx');
	}
	
	public function print_list($d_id=-1)
	{
		$this->load->config('vegetables');
		$this->load->library('PhpOffice');
		$this->load->model('veget_model');
		$this->load->model('orders_model');
		
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
		
		$units = $this->config->item('units');
		$tpl_config = $this->config->item('tpl_collect_list');
		
		// Create new Spreadsheet object
		$spreadsheet = $this->phpoffice->load_file($tpl_config['filepath'].$tpl_config['filename']);
		$active_sheet = $spreadsheet->getActiveSheet();
		
		$date = new DateTime($delivery->delivery_date);
		$active_sheet->setCellValue($tpl_config['cell_date'], $date->format('d/m/Y'));
		
		// First insert vegetables as rows in the template
		$delivery->vegetables = json_decode($delivery->vegetables);
		$vege_rows = array(); // id => row
		$other_rows = array(); //same for non vege
		
		// lambda function for increment array of rows
		$shift_rows_func = function(&$item, $key, $val) {
			$item += $val;
		};
		
		$base_row = $tpl_config['base_row'];
		
		$row_veg = $base_row;
		$row_other = $base_row;
		foreach ($delivery->vegetables as $r => $data_row) 
		{
			if ($data_row->accounting_cat !== NULL && $data_row->accounting_cat !== 'veg')
			{
				$row_veg++;
				$row = ++$row_other;
				$other_rows[$data_row->id] = $row;
				array_walk($vege_rows, $shift_rows_func, +1); // Shift down the rows
			} 
			else
			{
				$row = ++$row_veg;
				$vege_rows[$data_row->id] = $row;
			}
			$active_sheet->insertNewRowBefore($row, 1);

			$active_sheet
				->setCellValue($tpl_config['column_desc'].$row, $data_row->name)
				->setCellValue($tpl_config['column_unit'].$row, element(strtolower($data_row->unit), $units))
				->setCellValue($tpl_config['column_total'].$row, '=SUM('.$tpl_config['base_column'].$row.':'.$tpl_config['last_column'].$row.')');
			
		}
		$active_sheet->removeRow($base_row, 1);
		$row = $row_veg;
		array_walk($vege_rows, $shift_rows_func, -1); // Shift up the rows
		array_walk($other_rows, $shift_rows_func, -1); // Shift up the rows
		
		// Now load orders and insert each as a column in the template
		
		// If an order as a vegetable not in the list, we add it to the list
		$orders = $this->orders_model->get_list($delivery->id);
		$base_column = $tpl_config['base_column'];
		$column = $base_column;
		
		foreach($orders as $order)
		{
			$active_sheet->setCellValue($column.$tpl_config['row_customer'], $order->customer);
				
			$order->vegetables = json_decode($order->vegetables, TRUE);
			foreach($order->vegetables as $veg_id => $veg_qtt)
			{
				if (key_exists($veg_id, $vege_rows))
				{
					// Insert order
					$active_sheet->setCellValue($column.$vege_rows[$veg_id], $veg_qtt);
				}
				elseif (key_exists($veg_id, $other_rows))
				{
					// Insert order
					$active_sheet->setCellValue($column.$other_rows[$veg_id], $veg_qtt);
				}
				else
				{
					// If vege not already inserted
					$veg = $this->veget_model->get($veg_id);
					if ($veg != NULL)
					{
						//Okay, now add the price to the list and add the veg to the list
						$veg->not_from_list = TRUE;
						$delivery->vegetables[] = $veg;
						
						//Insert in template
						$active_sheet->insertNewRowBefore($row, 1);
						$active_sheet
							->setCellValue($tpl_config['column_desc'].$row, $veg->name)
							->setCellValue($tpl_config['column_unit'].$row, element(strtolower($veg->unit), $units))
							->setCellValue($tpl_config['column_total'].$row, '=SUM('.$tpl_config['base_column'].$row.':'.$tpl_config['last_column'].$row.')');

						$vege_rows[$veg->id] = $row;
						$row++;
						
						// Then insert order
						$active_sheet->setCellValue($column.$vege_rows[$veg_id], $veg_qtt);
					} 
					else 
					{
						unset($order->vegetables[$veg_id]);
						continue;
					}
				}
			}
			$column++;// Next column
		}
		
		// Resize columns
		$active_sheet
			->getRowDimension($tpl_config['row_customer'])
    		->setRowHeight(-1);

		// Disabled: column to wide
//		$active_sheet
//			->getColumnDimension($tpl_config['column_desc'])
//			->setAutoSize(TRUE);

		// Hide columns
		$short = $this->input->get('short');
		if ( ! empty($short) && $short === 'true')
		{
			$col_end = ++$tpl_config['last_column'];
			// Hide all
			for ($col = $tpl_config['base_column']; $col != $col_end; $col++)
			{
				$active_sheet
					->getColumnDimension($col)
					->setVisible(FALSE);
			}
		}
		else
		{
			$col_end = $tpl_config['last_column']; // Leave one gosh column to add an order by hand
			// Hide empties
			for ($col = $column; $col != $col_end; $col++)
			{
				$active_sheet
					->getColumnDimension($col)
					->setVisible(FALSE);
			}
		}
		// Hide row with 0.00 qtt
		foreach($active_sheet->getRowIterator() as $row_item) 
		{
			$i = $row_item->getRowIndex();
			if ($i >= $base_row 
				&& $active_sheet->getCell($tpl_config['column_total'].$i)->getCalculatedValue() == 0) 
			{
				$active_sheet->getRowDimension($i)->setVisible(FALSE);
			}
		}
		
		// Prepare for printing
		$active_sheet->getPageSetup()
			->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
		$active_sheet->getPageSetup()
			->setFitToPage(TRUE);
		$active_sheet->getPageSetup()
			->setPrintArea('1:'.$row);
		$active_sheet->setShowGridLines(false);

		// Download
		$filename = 'collect_list.xlsx';
		if (!empty($short) && $short === 'true')
		{
			$filename = 'short_'.$filename;
		}
		$this->phpoffice->download($spreadsheet, $delivery->delivery_date.'_'.$filename);
		//FIXME download_pdf produce an ugly pdf
	}
	
	public function back_to_collect($d_id=-1)
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
		
		$this->load->model('orders_model');
		$orders = $this->orders_model->get_list($delivery->id, TRUE);
		foreach($orders as $order)
		{
			$ok = $this->orders_model->update($order->id, 
											  array(
											  	$this->orders_model::FIELD_INVOICE => NULL,
											  	$this->orders_model::FIELD_STATUS  => 'collect')
											 );
		}
		
		$ok = $this->deliveries_model->update($d_id, array($this->deliveries_model::FIELD_STATUS => 'collect'));
		
		if ($ok === FALSE)
		{
			$msg = array (
				'type'		=> 'error',
				'title'		=> '<i class="attention icon"></i> Impossible to return to the collect',
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
	
	public function delete($d_id=-1)
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
		
		$this->load->model('orders_model');
		$this->load->model('accounting_model');
		
		$ok2 = TRUE;
		
		switch ($delivery->status)
		{
			case 'closed':
			case 'accounting':
				// Delete all payments
				$ok2 = $this->accounting_model->delete_all_for($d_id);
			case 'prepare':
			case 'collect':
				$ok2 = $ok2 && $this->orders_model->delete_all_for($d_id);
				break;
		}
		
		
		$ok = $this->deliveries_model->delete($d_id);
		
		if ($ok === FALSE)
		{
			$msg = array (
				'type'		=> 'error',
				'title'		=> '<i class="attention icon"></i> Impossible to delete to the delivery',
				'content'	=> '',
				'time'		=> time()
			);
			$this->redirect->to('/lists/view/'.$delivery->l_id, NULL, $msg);
		}
		else
		{
			if ( ! $ok2)
			{	
				$msg = array (
					'type'		=> 'error',
					'title'		=> '<i class="attention icon"></i> An error occurs during the deletion. Some orders or payments may remain...',
					'content'	=> '',
					'time'		=> time()
				);
				$this->redirect->to('/lists/view/'.$delivery->l_id, NULL, $msg);
			}
			else
			{
				$msg = array (
					'type'		=> 'success',
					'title'		=> '<i class="check circle icon"></i> Delivery successfully deleted!',
					'content'	=> '',
					'time'		=> time()
				);
				$this->redirect->to('/lists/view/'.$delivery->l_id, NULL, $msg);
			}
		}
	}
	
	private function _run_form_validation()
	{
		$this->form_validation->set_rules('delivery_date', 'Delivery date', 'trim|required|regex_match['.date_regex().']');
		$this->form_validation->set_message(
			'regex_match', 
			'The date must match one of the following format: 
			<ul>
				<li>dd/mm/yyyy</li>
				<li>dd-mm-yyyy</li>
				<li>dd/mm</li>
				<li>dd-mm</li>
			</ul>');
		
		return $this->form_validation->run();
	}
}
