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
class Lists_model extends CI_Model {
	
	const TABLE_NAME 			= 'lists';
	const TABLE_LISTS_CUSTOMERS = 'lists_customers';
	const TABLE_LISTS_VEGETABLES = 'lists_vegetables';
	const FIELD_ID				= 'id';
	const FIELD_NAME 			= 'name';
	const FIELD_DAY_OF_WEEK		= 'day_of_week';
	const FIELD_VEGETABLES		= 'vegetables';

	public function __construct()
	{
		$this->load->database();
	}
	
	public function get_list()
	{
		$this->db->from(self::TABLE_NAME)
			->order_by(self::FIELD_NAME, 'ASC')
			->order_by(self::FIELD_DAY_OF_WEEK, 'ASC');
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
	 * Update a list object, including the list of customers
	 *
	 * @param the list id
	 *
	 * @return boolean
	 */
	public function update($id)
	{
		$input_customers = $this->input->post('customers[]');
		if ($input_customers == NULL)
		{
			$input_customers = array();
		}
		
		$bdd_customers = $this->get_customer_ids($id);
		
		// Add new customers (i.e. who are int the input list but not yet in bdd)
		foreach(array_diff($input_customers, $bdd_customers) as $customer)
		{
			$this->db->from(self::TABLE_LISTS_CUSTOMERS)
				->set('l_id', $id)
				->set('c_id', $customer)
				->insert();
		}
		
		// Remove customers (i.e. who are in bdd but not in the input list)
		foreach(array_diff($bdd_customers, $input_customers) as $customer)
		{
			$this->db->from(self::TABLE_LISTS_CUSTOMERS)
				->where('l_id', $id)
				->where('c_id', $customer)
				->delete();
		}
		
		return $this->db->from(self::TABLE_NAME)
			->where(self::FIELD_ID, $id)
			->set(self::FIELD_NAME, $this->input->post('name'))
			->set(self::FIELD_DAY_OF_WEEK, $this->input->post('day_of_week'))
			->update();
	}
	
	public function create()
	{
		$input_customers = $this->input->post('customers[]');
		if ($input_customers == NULL)
		{
			$input_customers = array();
		}
		
		$success = $this->db
			->set(self::FIELD_NAME, $this->input->post('name'))
			->set(self::FIELD_DAY_OF_WEEK, $this->input->post('day_of_week'))
			->insert(self::TABLE_NAME);
		
		if ($success === TRUE)
		{
			$id = $this->db->insert_id();
			// Add new customers
			foreach($input_customers as $customer)
			{
				$this->db->from(self::TABLE_LISTS_CUSTOMERS)
					->set('l_id', $id)
					->set('c_id', $customer)
					->insert();
			}
			
			return $id;
		}
		
		return FALSE;
	}
	
	public function delete($list_id)
	{
		//Remove customer suscribtion
		$this->db->from(self::TABLE_LISTS_CUSTOMERS)
			->where('l_id', $list_id)
			->delete();
		
		//Delete list
		return $this->db->from(self::TABLE_NAME)
			->where(self::FIELD_ID, $list_id)
			->delete();
	}
	
	/**
	 * Returns the number of customers for each list
	 * 
	 * @return      array('list_id' => count)
	 */
	public function get_customer_counts()
	{
		// Get count of customers, build an array( 'list_id' => count)
		$query = $this->db->from(self::TABLE_LISTS_CUSTOMERS)
			->select(self::TABLE_LISTS_CUSTOMERS.'.l_id, COUNT('.self::TABLE_LISTS_CUSTOMERS.'.c_id) AS count')
			->group_by(self::TABLE_LISTS_CUSTOMERS.'.l_id')
			->get();
		$result_count = array_column($query->result_array(), 'count', 'l_id');
		
		// Get lists, build an array ('list_id' => 0)
		$query = $this->db->from(self::TABLE_NAME)
			->select('id')
			->get();
		$ids = array_fill_keys(array_column($query->result_array(), 'id'), 0);

		// Replace the value with the right count
		$count = array_replace($ids, $result_count);
		return $count;
	}
	
	/**
	 * Return an array of user ids for the list
	 *
	 * @param 	id of the list
	 * 
	 * @return 	array()
	 */
	public function get_customer_ids($list_id)
	{
		$query = $this->db->from(self::TABLE_LISTS_CUSTOMERS)
			->select(self::TABLE_LISTS_CUSTOMERS.'.c_id AS customer_id')
			->where(self::TABLE_LISTS_CUSTOMERS.'.l_id = '.$list_id)
			->get();
		return array_column($query->result_array(), 'customer_id');
	}
	
	/**
	 * Return an array of Customer objects that suscribe to the list
	 *
	 * @param	id of the list
	 *
	 * @return array(Object)
	 */
	public function get_customers($list_id, $value=NULL, $offset=NULL)
	{
		$this->load->model('customers_model');
		
		$this->db->from(self::TABLE_LISTS_CUSTOMERS)
			->from(Customers_model::TABLE_NAME)
			->select(Customers_model::TABLE_NAME.'.*')
			->where(Customers_model::TABLE_NAME.'.id = '.self::TABLE_LISTS_CUSTOMERS.'.c_id')
			->where(self::TABLE_LISTS_CUSTOMERS.'.l_id = '.$list_id)
			->order_by(Customers_model::FIELD_NAME, 'ASC');
		
		if ($value !== NULL && $offset !== NULL)
		{
			$this->db->limit($value, $offset);
		}
			$query = $this->db->get();
		return $query->result();
	}
	
	/**
	 * Return number of customers for one list
	 *
	 * @param	id of the list
	 *
	 * @return int
	 */
	public function get_customers_count($list_id)
	{
		return $this->db->from(self::TABLE_LISTS_CUSTOMERS)
			->where(self::TABLE_LISTS_CUSTOMERS.'.l_id = '.$list_id)
			->count_all_results();
	}
	
	public function remove_customer($list_id, $customer_id)
	{
		return $this->db->from(self::TABLE_LISTS_CUSTOMERS)
			->where('l_id', $list_id)
			->where('c_id', $customer_id)
			->delete();
	}
	
	public function add_customer($list_id, $customer_id)
	{
		return $this->db->from(self::TABLE_LISTS_CUSTOMERS)
			->set('l_id', $list_id)
			->set('c_id', $customer_id)
			->insert();
	}
	
	/**
	 * Return an array of Vegetables objects that are available for the list
	 *
	 * @param	id of the list
	 *
	 * @return array(Object)
	 */
	public function get_vegetables($list_id, $value=NULL, $offset=NULL)
	{
		$this->load->model('veget_model');
		
		$this->db->from(self::TABLE_LISTS_VEGETABLES)
			->from(Veget_model::TABLE_NAME)
			->select(Veget_model::TABLE_NAME.'.*')
			->where(Veget_model::TABLE_NAME.'.id = '.self::TABLE_LISTS_VEGETABLES.'.v_id')
			->where(self::TABLE_LISTS_VEGETABLES.'.l_id = '.$list_id)
			->order_by(Veget_model::FIELD_NAME, 'ASC');
		
		if ($value !== NULL && $offset !== NULL)
		{
			$this->db->limit($value, $offset);
		}
		$query = $this->db->get();
		
		return $query->result();
	}
	
	/**
	 * Return number of vegetables for one list
	 *
	 * @param	id of the list
	 *
	 * @return int
	 */
	public function get_vegetables_count($list_id)
	{
		return $this->db->from(self::TABLE_LISTS_VEGETABLES)
			->where(self::TABLE_LISTS_VEGETABLES.'.l_id = '.$list_id)
			->count_all_results();
	}
	
	/**
	 * Update the vegetable list of the delivery list
	 * 
	 * @param the list id
	 *
	 * @return boolean
	 */
	public function update_vegetables($list_id)
	{
		$input_veget = $this->input->post('vegetables[]');
		if ($input_veget == NULL)
		{
			$input_veget = array();
		}
		
		$bdd_veget = array_column($this->get_vegetables($list_id), 'id');
		
		// Add new veget (i.e. who are int the input list but not yet in bdd)
		foreach(array_diff($input_veget, $bdd_veget) as $veget)
		{
			$this->db->from(self::TABLE_LISTS_VEGETABLES)
				->set('l_id', $list_id)
				->set('v_id', $veget)
				->insert();
		}
		
		// Remove customers (i.e. who are in bdd but not in the input list)
		foreach(array_diff($bdd_veget, $input_veget) as $veget)
		{
			$this->db->from(self::TABLE_LISTS_VEGETABLES)
				->where('l_id', $list_id)
				->where('v_id', $veget)
				->delete();
		}
	}
	
	public function remove_vegetable($list_id, $veget_id)
	{
		return $this->db->from(self::TABLE_LISTS_VEGETABLES)
			->where('l_id', $list_id)
			->where('v_id', $veget_id)
			->delete();
	}
	
	public function get_deliveries($list_id)
	{
		$this->load->model('deliveries_model');
		
		$this->db->from(Deliveries_model::TABLE_NAME)
			->select(Deliveries_model::TABLE_NAME.'.*')
			->where(Deliveries_model::FIELD_LIST_ID.' = '.$list_id)
			->order_by(Deliveries_model::FIELD_DELIVERY_DATE, 'DESC');

		$query = $this->db->get();
		
		return $query->result();
	}
	
	public function get_open_deliveries($list_id)
	{
		$this->load->model('deliveries_model');
		
		$this->db->from(Deliveries_model::TABLE_NAME)
			->select(Deliveries_model::TABLE_NAME.'.*')
			->where(Deliveries_model::FIELD_LIST_ID.' = '.$list_id)
			->where(Deliveries_model::FIELD_STATUS.' != "closed"')
			->order_by(Deliveries_model::FIELD_DELIVERY_DATE, 'DESC');

		$query = $this->db->get();
		
		return $query->result();
	}
	
	public function get_closed_deliveries($list_id, $value=NULL, $offset=NULL)
	{
		$this->load->model('deliveries_model');
		
		$this->db->from(Deliveries_model::TABLE_NAME)
			->select(Deliveries_model::TABLE_NAME.'.*')
			->where(Deliveries_model::FIELD_LIST_ID.' = '.$list_id)
			->where(Deliveries_model::FIELD_STATUS.' = "closed"')
			->order_by(Deliveries_model::FIELD_DELIVERY_DATE, 'DESC');
		
		if ($value !== NULL && $offset !== NULL)
		{
			$this->db->limit($value, $offset);
		}
		$query = $this->db->get();
		
		return $query->result();
	}
}
