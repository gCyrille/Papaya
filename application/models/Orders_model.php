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
class Orders_model extends CI_Model {
	
	const TABLE_NAME 				= 'orders';
	const VUE_DETAILED_NAME			= 'detailed_orders';
	const FIELD_ID					= 'id';
	const FIELD_DELIVERY_ID			= 'd_id';
	const FIELD_CUSTOMER_ID			= 'c_id';
	const FIELD_VEGETABLES			= 'vegetables';
	const FIELD_STATUS				= 'status';
	const FIELD_PAYMENT				= 'payment';
	const FIELD_INVOICE				= 'invoice';
	const FIELD_COMMENTS			= 'comments';

	public function __construct()
	{
		$this->load->database();
	}
	
	public function get_list($d_id, $detailed=FALSE)
	{
		$this->load->model('customers_model');
	
		if ( ! $detailed)
		{
			$this->db->from(self::TABLE_NAME)
				->select(self::TABLE_NAME.'.*,'.Customers_model::TABLE_NAME.'.'.Customers_model::FIELD_NAME.' AS customer')
				->order_by(self::FIELD_STATUS, 'ASC')
				->order_by(Customers_model::FIELD_NAME, 'ASC')
				->where(self::FIELD_DELIVERY_ID, $d_id)
				->join(
					Customers_model::TABLE_NAME, 
					Customers_model::TABLE_NAME.'.'.Customers_model::FIELD_ID.'='.self::TABLE_NAME.'.'.self::FIELD_CUSTOMER_ID);
			$query = $this->db->get();
		}
		else
		{
			$this->db->from(self::VUE_DETAILED_NAME)
				->order_by(self::FIELD_STATUS, 'ASC')
				->order_by('customer', 'ASC')
				->where(self::FIELD_DELIVERY_ID, $d_id);
			$query = $this->db->get();
		}
		return $query->result();
	}
	
	public function get_count($d_id)
	{
		$this->db->from(self::TABLE_NAME)
			->where(self::FIELD_DELIVERY_ID, $d_id);
		return $this->db->count_all_results();
	}
	
	public function get_paid_count($d_id)
	{
		$this->db->from(self::TABLE_NAME)
			->select('COUNT('.self::FIELD_STATUS.') AS \'not_paid\'')
			->where(self::FIELD_DELIVERY_ID, $d_id)
			->where(self::FIELD_STATUS.'!= \'paid\'');
		$not_paid = $this->db->get()->row_array();
		
		$this->db->from(self::TABLE_NAME)
			->select('COUNT('.self::FIELD_STATUS.') AS \'paid\'')
			->where(self::FIELD_DELIVERY_ID, $d_id)
			->where(self::FIELD_STATUS.'= \'paid\'');
		$paid = $this->db->get()->row_array();
		
		return array_merge($not_paid, $paid);
			
	}
	
	public function get($o_id, $as_array=FALSE)
	{
		$query = $this->db->where(self::FIELD_ID, $o_id)
			->from(self::VUE_DETAILED_NAME)
			->get();
		if ($as_array)
		{
			return $query->row_array();
		}
		else
		{
			return $query->row();
		}
	}

	/**
	 * update the list of vegetable of o_id order
	 * 
	 * @param int id of the order
	 * @param array ('vegetable id' => quantity)
	 */
	public function update_vegetables($o_id, $vegetables)
	{
		if( ! is_array($vegetables))
		{
			return FALSE;
		}
		return $this->db->from(self::TABLE_NAME)
			->where(self::FIELD_ID, $o_id)
			->set(self::FIELD_VEGETABLES, json_encode($vegetables))
			->update();
	}
	
	public function create($d_id)
	{
		$selected_veget = $this->input->post('vegetables[]');
		
		// Clean array and format qtt as number	
		foreach($selected_veget as $id => $qtt)
		{
			if (empty($qtt))
			{
				unset($selected_veget[$id]);// = 0;
			}
			else
			{
				$selected_veget[$id] = floatval($qtt);
			}
		}

		$success = $this->db
			->set(self::FIELD_DELIVERY_ID, $d_id)
			->set(self::FIELD_CUSTOMER_ID, $this->input->post('customer'))
			->set(self::FIELD_VEGETABLES, json_encode($selected_veget))
			->set(self::FIELD_STATUS, 'collect')
			->set(self::FIELD_COMMENTS, $this->input->post('comments'))
			->insert(self::TABLE_NAME);
		
		if ($success === TRUE)
		{
			$id = $this->db->insert_id();
			return $id;
		}
		
		return FALSE;
	}
	
	public function update($o_id, array $order=NULL)
	{
		if ($order == NULL)
		{
			$selected_veget = $this->input->post('vegetables[]');

			// Clean array and format qtt as number	
			foreach($selected_veget as $id => $qtt)
			{
				if (empty($qtt))
				{
					unset($selected_veget[$id]);// = 0;
				}
				else
				{
					$selected_veget[$id] = floatval($qtt);
				}
			}

			$success = $this->db->from(self::TABLE_NAME)
				->where(self::FIELD_ID, $o_id)
				->set(self::FIELD_CUSTOMER_ID, $this->input->post('customer'))
				->set(self::FIELD_VEGETABLES, json_encode($selected_veget))
				->set(self::FIELD_COMMENTS, $this->input->post('comments'))
				->update();

			return $success;
		}
		else
		{
			if (isset($order[self::FIELD_VEGETABLES]) && is_array($order[self::FIELD_VEGETABLES]))
			{
				$order[self::FIELD_VEGETABLES] = json_encode($order[self::FIELD_VEGETABLES]);
			}

			$success = $this->db->from(self::TABLE_NAME)
				->where(self::FIELD_ID, $o_id)
				->set($order)
				->update();

			return $success;
		}
	}
	
	public function delete($o_id)
	{
		// Delete delivery
		return $this->db->from(self::TABLE_NAME)
			->where(self::FIELD_ID, $o_id)
			->delete();
	}
	
	/**
	 * Delete all order entry for given delivery
	 *
	 * @param int delivery id
	 */
	public function delete_all_for($d_id)
	{
		return $this->db->from(self::TABLE_NAME)
			->where(self::FIELD_DELIVERY_ID, $d_id)
			->delete();
	}
	
	/**
	 * Build and return invoice object for a given order
	 *
	 * @param stdClass order
	 * @param &array list of vegetables for the delivery (prices, units, etc.)
	 *
	 * @return stdClass
	 *
	 * // Invoice per order (per customer)
	 * // 	- Customer name
	 * // 	- Delivery nice date
	 * // 	- Invoice number
	 * // 	- Lines for non-vegetables {Description, Unit, Qty, Price, Amount}
	 * // 	- Lines for vegetables {Description, Unit, Qty, Price, Amount}
	 * // 	- Total
	 * // 	- Lines for previous unpaid invoices {Date, Total}
	 * // 	- Total of balances
	 */
	public function generate_invoice($order, &$vegetables)
	{
		$this->load->model('customers_model');
		
		$invoice = new stdClass();
		$invoice->customer = $order->customer;
		$date = new DateTime($order->delivery_date);
		$invoice->date = $date->format('d/m/Y');
		$invoice->number = sprintf('%d/%05d', $order->d_id, $order->id);
		$invoice->vegets = array();
		$invoice->non_vegets = array();
		$invoice->unpaids = array();
		$invoice->total = 0.00;

		$units_code = $this->config->item('units');
		
		// Build vegetables list
		$order->vegetables = json_decode($order->vegetables, TRUE);
		foreach($order->vegetables as $veg_id => $veg_qtt)
		{
			if (key_exists($veg_id, $vegetables))
			{
				$sub_total =  $vegetables[$veg_id]->price * $veg_qtt;
				$invoice->total += $sub_total;
				// Insert line in order_array
				if ($vegetables[$veg_id]->accounting_cat == NULL OR $vegetables[$veg_id]->accounting_cat == 'veg')
				{
					$row = 'vegets';
				}
				else
				{
					$row = 'non_vegets';
				}
				$invoice->$row[] = array(
					'description' 	=> $vegetables[$veg_id]->name,
					'unit'			=> element(strtolower($vegetables[$veg_id]->unit), $units_code), 
					'qty'			=> $veg_qtt,
					'price'			=> $vegetables[$veg_id]->price,
					'amount'		=> format_kwacha($sub_total)
				);
			} 
			else 
			{
				$veg = $this->veget_model->get($veg_id);
				if ($veg != NULL)
				{
					//Okay, now add the price to the list and add the veg to the list
					$veg->not_from_list = TRUE;
					$vegetables[$veg_id] = $veg;

					// Obviously, compute total for the order
					$sub_total =  $veg->price * $veg_qtt;
					$invoice->total += $sub_total;
					// Insert line in order_array
					if ($vegetables[$veg_id]->accounting_cat == NULL OR $vegetables[$veg_id]->accounting_cat == 'veg')
					{
						$row = 'vegets';
					}
					else
					{
						$row = 'non_vegets';
					}
					$invoice->$row[] = array(
						'description' 	=> $vegetables[$veg_id]->name,
						'unit'			=> element(strtolower($vegetables[$veg_id]->unit), $units_code), 
						'qty'			=> $veg_qtt,
						'price'			=> $vegetables[$veg_id]->price,
						'amount'		=> format_kwacha($sub_total)
					);
				} 
				else 
				{
					unset($order->vegetables[$veg_id]);
					log_message('error', 'Vegetable does not exit [o_id='.$order->id.',veg_id='.$veg_id.']');
				}
			}
		}

		// Add balances
		$invoice->total_balances = $invoice->total;
		$unpaids = $this->customers_model->get_unpaid_orders($order->c_id);
		foreach($unpaids as $unpaid)
		{
			// Only add to balance the unpaid invoices from deliveries that are closed
			if ($unpaid->delivery_status == 'closed' && $unpaid->id != $order->id)
			{
				if ( ! empty($unpaid->invoice))
				{
					$unpaid->invoice = json_decode($unpaid->invoice);
					$rest = $unpaid->invoice->total - $unpaid->payment;
					$invoice->unpaids[] = array(
						'date' 	=> mysql_to_nice_date($unpaid->delivery_date),
						'total' => format_kwacha($rest));
					$invoice->total_balances += $rest;
				}
				else
				{
					// Error, invoice not available for not_paid status...
					log_message('error', 'Order id='.$unpaid->id.': Invoice not available for not_paid status...');
					continue;
				}
			}
		}

		$balance = $this->customers_model->get($order->c_id)->current_balance;
		if ($balance < 0)
		{
			$invoice->unpaids[] = array(
				'date'	=> 'Credit',
				'total'	=> format_kwacha(-$balance));
			$invoice->total_balances += $balance;
		}
		else
		{
			$invoice->unpaids[] = array(
				'date'	=> 'Balance',
				'total'	=> $balance);
			$invoice->total_balances += $balance;
		}

		$invoice->total_balances = format_kwacha($invoice->total_balances);
		$invoice->total = format_kwacha($invoice->total);
		
		return $invoice;
	}
}
