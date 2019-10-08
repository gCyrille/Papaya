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
class Accounting_model extends CI_Model {
	
	const TABLE_NAME 				= 'accounting';
	const FIELD_ID					= 'id';
	const FIELD_DELIVERY_ID			= 'd_id';
	const FIELD_CUSTOMER_ID			= 'c_id';
	const FIELD_TOTAL_DUE			= 'total_due';
	const FIELD_TOTAL_PAID			= 'total_paid';
	const FIELD_DETAILS				= 'details';
	const FIELD_PAYMENTS			= 'payments';
	const FIELD_TIMESTAMP			= 'timestamp';

	public function __construct()
	{
		$this->load->database();
	}
	
	public function get_list($d_id)
	{
		$this->load->model('customers_model');
		
		$this->db->from(self::TABLE_NAME)
//			->select(self::TABLE_NAME.'.*,'.Customers_model::TABLE_NAME.'.'.Customers_model::FIELD_NAME.' AS customer')
//			->order_by(Customers_model::FIELD_NAME, 'ASC')
			->order_by(self::FIELD_TIMESTAMP, 'ASC')
			->where(self::FIELD_DELIVERY_ID, $d_id);
//			->join(
//				Customers_model::TABLE_NAME, 
//				Customers_model::TABLE_NAME.'.'.Customers_model::FIELD_ID.'='.self::TABLE_NAME.'.'.self::FIELD_CUSTOMER_ID);
		
		$query = $this->db->get();
		
		return $query->result();
	}
	
	public function get_customer($c_id)
	{
		$this->load->model('customers_model');
		
		$this->db->from(self::TABLE_NAME)
			->where(self::FIELD_CUSTOMER_ID, $c_id);
		$query = $this->db->get();
		
		return $query->result();
	}
	
	public function get_count($d_id)
	{
		$this->db->from(self::TABLE_NAME)
			->where(self::FIELD_DELIVERY_ID, $d_id)
			->where(self::FIELD_CUSTOMER_ID.' !=', -1);
		return $this->db->count_all_results();
	}
	
	public function get($a_id, $as_array=FALSE)
	{
		$query = $this->db->where(self::FIELD_ID, $a_id)
			->from(self::TABLE_NAME)
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
	
	public function get_where($d_id, $c_id)
	{
		$query = $this->db
			->where(self::FIELD_DELIVERY_ID, $d_id)
			->where(self::FIELD_CUSTOMER_ID, $c_id)
			->from(self::TABLE_NAME)
			->get();

		return $query->row();
	}
	
	public function create(array $input)
	{
		if (isset($input[self::FIELD_DETAILS]) && is_array($input[self::FIELD_DETAILS]))
		{
			$input[self::FIELD_DETAILS] = json_encode($input[self::FIELD_DETAILS]);
		}
		
		if (isset($input[self::FIELD_PAYMENTS]) && is_array($input[self::FIELD_PAYMENTS]))
		{
			$input[self::FIELD_PAYMENTS] = json_encode($input[self::FIELD_PAYMENTS]);
		}
		
		$success = $this->db
			->set($input)
			->insert(self::TABLE_NAME);
		
		if ($success === TRUE)
		{
			$id = $this->db->insert_id();
			return $id;
		}
		
		return FALSE;
	}
	
	public function update($a_id, array $input)
	{

		if (isset($input[self::FIELD_DETAILS]) && is_array($input[self::FIELD_DETAILS]))
		{
			$input[self::FIELD_DETAILS] = json_encode($input[self::FIELD_DETAILS]);
		}
		
		if (isset($input[self::FIELD_PAYMENTS]) && is_array($input[self::FIELD_PAYMENTS]))
		{
			$input[self::FIELD_PAYMENTS] = json_encode($input[self::FIELD_PAYMENTS]);
		}

		$success = $this->db->from(self::TABLE_NAME)
			->where(self::FIELD_ID, $a_id)
			->set($input)
			->update();

		return $success;
	}
	
	public function delete($a_id, $c_id=-1)
	{
		if ($c_id == -1)
		{
			// Delete
			return $this->db->from(self::TABLE_NAME)
				->where(self::FIELD_ID, $a_id)
				->delete();
		}
		else
		{
			$p = $this->get_where($a_id, $c_id);
			if ($p !== NULL)
			{
				$this->_cancel_payment($p);
			}
			return $this->db->from(self::TABLE_NAME)
				->where(self::FIELD_DELIVERY_ID, $a_id)
				->where(self::FIELD_CUSTOMER_ID, $c_id)
				->delete();
		}
	}
	
	/**
	 * Delete all accounting entry for given delivery
	 *
	 * @param int delivery id
	 */
	public function delete_all_for($d_id)
	{
		// First adjust balances for orders (extra paid)
		$payments = $this->get_list($d_id);
		
		$success = TRUE;
		
		// For each payment, cancel the payment to the order and cancel the possible change on the user balance
		foreach ($payments as $p)
		{
			if ($p->c_id < 0) // Exlude expenses row
			{
				continue;
			}

			$success = $success && $this->_cancel_payment($p);
		}
		
		// And then remove all payments
		return $success && $this->db->from(self::TABLE_NAME)
			->where(self::FIELD_DELIVERY_ID, $d_id)
			->delete();
	}
	
	private function _cancel_payment($p)
	{
		$success = TRUE; 
		
		$paids = array_column(json_decode($p->payments), NULL, 'id');

		foreach($paids as $paid)
		{
			if ($paid->id < 0) // Skip extra, balances...
			{ 
				continue;
			}

			if ($paid->payment > 0 OR $paid->status === 'paid') // Remove the payment for this order
			{
				$order = $this->orders_model->get($paid->id);
				if ($order !== NULL)
				{
					$val = $order->payment - $paid->payment;
					$success = $this->orders_model->update($order->id, 
							  array(
								$this->orders_model::FIELD_PAYMENT  => $val,
							  	$this->orders_model::FIELD_STATUS	=> 'not_paid')
							 );
				}
				else
				{
					log_message('error', 'Found a payment for an inexistent order... Skip...');
				}
			}
		}

		$extra = $paids['-3']->payment;

		if ($extra !== 0)
		{
			// Update user canceling the extra paid from is balance
			// (positive extra = money has reduced balance, negative extra = money has increased the balance)
			$customer = $this->customers_model->get($p->c_id);
			if ($customer !== NULL)
			{
				$bal = $customer->current_balance + $extra;
				$success = $success && $this->customers_model->update($p->c_id,
																	 array(
																		Customers_model::FIELD_BALANCE => $bal
																	 ));
			} 
			else
			{
				log_message('error', 'Found a payment for an inexistent customer... Skip...');
			}
		}
		
		return $success;
	}
	
}
