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
class Customers_model extends CI_Model {
	
	const TABLE_NAME 				= 'customers';
	const TABLE_LISTS_CUSTOMERS 	= 'lists_customers';
	const FIELD_ID					= 'id';
	const FIELD_NAME 				= 'name';
	const FIELD_CONTACT				= 'contact_name';
	const FIELD_EMAIL 				= 'email';
	const FIELD_EMAIL_2				= 'email_2';
	const FIELD_DELIVERY_PLACE 		= 'delivery_place';
	const FIELD_DELIVERY_PLACE_2	= 'delivery_place_2';
	const FIELD_BALANCE				= 'current_balance';

	public function __construct()
	{
		$this->load->database();
	}
	
	public function get_list($value=NULL, $offset=NULL)
	{
		$this->db->from(self::TABLE_NAME)
			->order_by(self::FIELD_NAME, 'ASC')
			->order_by(self::FIELD_CONTACT, 'ASC');
		if ($value !== NULL && $offset !== NULL)
		{
			$this->db->limit($value, $offset);
		}
		$query = $this->db->get();
		
		return $query->result();
	}
	
	public function get_count()
	{
		return $this->db->count_all(self::TABLE_NAME);
	}
	
	public function get($id, $as_array=FALSE)
	{
		$query = $this->db->where(self::FIELD_ID, $id)
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
	
	/**
	 * Return the list of suscribed deliveries
	 *
	 * @param user id
	 *
	 * @return array of list objects
	 */
	public function get_delivery_lists($user_id)
	{
		$this->load->model('lists_model');
		
		$query = $this->db->from(self::TABLE_LISTS_CUSTOMERS)
			->from(Lists_model::TABLE_NAME)
			->select(Lists_model::TABLE_NAME.'.*')
			->where(Lists_model::TABLE_NAME.'.id = '.self::TABLE_LISTS_CUSTOMERS.'.l_id')
			->where(self::TABLE_LISTS_CUSTOMERS.'.c_id = '.$user_id)
			->get();
		return $query->result();
	}
	
	public function get_orders($u_id, $value=NULL, $offset=NULL, $start_date=NULL)
	{
		$this->load->model('orders_model');
		
		$this->db->from(Orders_model::VUE_DETAILED_NAME)
			//->order_by(Orders_model::FIELD_STATUS, 'DESC')
			->order_by('delivery_date', 'DESC')
			->where(Orders_model::FIELD_CUSTOMER_ID, $u_id);
		
		if ($start_date != NULL)
		{
			$this->db->where('delivery_date >= \''.$start_date.'\'');
		}
		
		if ($value !== NULL && $offset !== NULL)
		{
			$this->db->limit($value, $offset);
		}
		$query = $this->db->get();
		
		return $query->result();
	}
		
	public function get_orders_date_range($u_id, $start_date, $end_date)
	{		
		$this->load->model('orders_model');
		
		$this->db->from(Orders_model::VUE_DETAILED_NAME)
			//->order_by(Orders_model::FIELD_STATUS, 'DESC')
			->order_by('delivery_date', 'DESC');
		if ($u_id !== '*')
		{
			$this->db->where(Orders_model::FIELD_CUSTOMER_ID, $u_id);
		}
		$this->db->where('delivery_date >= \''.$start_date.'\'')
			->where('delivery_date <= \''.$end_date.'\'');

		$query = $this->db->get();
		
		return $query->result();
	}
	
	public function get_unpaid_orders($u_id)
	{
		$this->load->model('orders_model');
		
		$this->db->from(Orders_model::VUE_DETAILED_NAME)
			->where(Orders_model::FIELD_CUSTOMER_ID, $u_id)
			->where(Orders_model::FIELD_STATUS, 'not_paid')
			->order_by('delivery_date', 'ASC');
		
		$query = $this->db->get();
		return $query->result();
	}
	
	public function get_total_unpaid($u_id, $date=NULL)
	{
		$this->load->model('orders_model');
		
		$this->db->from(Orders_model::VUE_DETAILED_NAME)
			->where(Orders_model::FIELD_CUSTOMER_ID, $u_id)
			->where(Orders_model::FIELD_STATUS, 'not_paid');
		
		if ($date != NULL)
		{
			$this->db->where('delivery_date <= \''.$date.'\'')	;
		}
		
		$this->db->order_by('delivery_date', 'ASC');
		
		$query = $this->db->get();
		$unpaids = $query->result();
		
		$total = 0;
		foreach($unpaids as $unpaid)
		{
			if ( ! empty($unpaid->invoice))
			{
				$unpaid->invoice = json_decode($unpaid->invoice);
			}
			else
			{
				continue;
			}
			$total += ($unpaid->invoice->total - $unpaid->payment);	
		}
		
		return $total;
	}
	
	public function search_field($field, $query=NULL)
	{
		if ($this->db->field_exists($field, self::TABLE_NAME))
		{
			if ($query == NULL && $this->input->get('q'))
			{
				$query = $this->input->get('q');
			}
			
			$result = $this->db->like($field, $query)
				->from(self::TABLE_NAME)
				->get();
			return $result->result();
		}
		
		return NULL;
	}
	
	public function search($query=NULL)
	{
		if ($query == NULL && $this->input->get('q'))
		{
			$query = $this->input->get('q');
		}

		$result = $this->db->like(self::FIELD_NAME, $query)
			->or_like(self::FIELD_EMAIL, $query)
			->or_like(self::FIELD_EMAIL_2, $query)
			->from(self::TABLE_NAME)
			->get();
		return $result->result();
	}
	
	public function update($id, array $customer=NULL, array $input_lists=NULL)
	{
		if ($input_lists == NULL)
		{
			$input_lists = $this->input->post('lists[]');
			if ($input_lists == NULL)
			{
				//$input_lists = array();
				// Nope, if input_list is NULL that means we don't want to update the subscribtion
			}
		}
		
		if ($customer == NULL)
		{
			// Update user first
			$this->db->from(self::TABLE_NAME)
				->where(self::FIELD_ID, $id)
				->set(self::FIELD_NAME, $this->input->post('name'))
				->set(self::FIELD_CONTACT, $this->input->post('contact_name'))
				->set(self::FIELD_EMAIL, $this->input->post('email'))
				->set(self::FIELD_EMAIL_2, $this->input->post('email_2'))
				->set(self::FIELD_DELIVERY_PLACE, $this->input->post('delivery_place'))
				->set(self::FIELD_DELIVERY_PLACE_2, $this->input->post('delivery_place_2'));

			if ($this->input->post('edit_balance'))
			{
				$this->db->set(self::FIELD_BALANCE, $this->input->post('current_balance'));
			}
			$success = $this->db->update();		
		}
		else
		{
			$success = $this->db->from(self::TABLE_NAME)
				->where(self::FIELD_ID, $id)
				->set($customer)
				->update();
		}
		
		//And if ok update the lists subscribtion 
		if ($success === TRUE && $input_lists != NULL)
		{
			$bdd_lists = array_column($this->get_delivery_lists($id), 'id');

			// Suscribe to new lists (i.e. who are int the input list but not yet in bdd)
			foreach(array_diff($input_lists, $bdd_lists) as $list)
			{
				$this->db->from(self::TABLE_LISTS_CUSTOMERS)
					->set('c_id', $id)
					->set('l_id', $list)
					->insert();
			}

			// Remove suscribtion (i.e. who are in bdd but not in the input list)
			foreach(array_diff($bdd_lists, $input_lists) as $list)
			{
				$this->db->from(self::TABLE_LISTS_CUSTOMERS)
					->where('c_id', $id)
					->where('l_id', $list)
					->delete();
			}
		}
		
		return $success;
		
	}
	
	public function create(array $customer=NULL)
	{
		$input_lists = $this->input->post('lists[]');
		if ($input_lists == NULL)
		{
			$input_lists = array();
		}
		
		if($customer != NULL)
		{
			$name = $customer['name'];
			$contact = $customer['contact_name'];
			$email = $customer['email'];
			$email_2 = $customer['email_2'];
			$place = $customer['delivery_place'];
			$place_2 = $customer['delivery_place_2'];
		}
		else
		{
			$name = $this->input->post('name');
			$contact = $this->input->post('contact_name');
			$email = $this->input->post('email');
			$email_2 = $this->input->post('email_2');
			$place = $this->input->post('delivery_place');
			$place_2 = $this->input->post('delivery_place_2');
		}
		
		$success = $this->db
			->set(self::FIELD_NAME, $name)
			->set(self::FIELD_CONTACT, $contact)
			->set(self::FIELD_EMAIL, $email)
			->set(self::FIELD_EMAIL_2, $email_2)
			->set(self::FIELD_DELIVERY_PLACE, $place)
			->set(self::FIELD_DELIVERY_PLACE_2, $place_2)
			->insert(self::TABLE_NAME);
		
		if ($success === TRUE)
		{
			$id = $this->db->insert_id();
			
			// Suscribe to lists
			foreach($input_lists as $list)
			{
				$this->db->from(self::TABLE_LISTS_CUSTOMERS)
					->set('c_id', $id)
					->set('l_id', $list)
					->insert();
			}
			return $id;
		}
		
		return FALSE;
	}
	
	public function delete($id)
	{
		// Unsuscribe from lists
		$this->db->from(self::TABLE_LISTS_CUSTOMERS)
			->where('c_id', $id)
			->delete();
		
		// Delete user
		return $this->db->from(self::TABLE_NAME)
			->where(self::FIELD_ID, $id)
			->delete();
	}
	
}
