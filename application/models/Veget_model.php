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
class Veget_model extends CI_Model {
	
	const TABLE_NAME 				= 'vegetables';
	const TABLE_LISTS_VEGETABLES	= 'lists_vegetables';
	const FIELD_ID					= 'id';
	const FIELD_NAME 				= 'name';
	const FIELD_UNIT 				= 'unit';
	const FIELD_PRICE 				= 'price';
	const FIELD_ACCOUNTING_CAT 		= 'accounting_cat';

	public function __construct()
	{
		$this->load->database();
	}
	
	public function get_list($value=NULL, $offset=NULL)
	{
		$this->db->from(self::TABLE_NAME)
			->order_by(self::FIELD_NAME, 'ASC')
			->order_by(self::FIELD_UNIT, 'ASC');
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
	 * Return the list of avaibility per delivery lists
	 *
	 * @param vegetable id
	 *
	 * @return array of list objects
	 */
	public function get_delivery_lists($veget_id)
	{
		$this->load->model('lists_model');
		
		$query = $this->db->from(self::TABLE_LISTS_VEGETABLES)
			->from(Lists_model::TABLE_NAME)
			->select(Lists_model::TABLE_NAME.'.*')
			->where(Lists_model::TABLE_NAME.'.id = '.self::TABLE_LISTS_VEGETABLES.'.l_id')
			->where(self::TABLE_LISTS_VEGETABLES.'.v_id = '.$veget_id)
			->get();
		return $query->result();
	}
	
	public function search( $field, $query=NULL)
	{
		if ($this->db->field_exists($field, self::TABLE_NAME))
		{
			if ($query == NULL && $this->input->get('q'))
			{
				$query = $this->input->get('q');
			}
			
			$result = $this->db->like($field, $query)
				->order_by($field, 'ASC')
				->from(self::TABLE_NAME)
				->get();
			return $result->result();
		}
		
		return NULL;
	}
	
	/**
	 * Update vegetables
	 *
	 * @param int vegetable id
	 * @param mixed NULL to use GET parameters, array with proporties to update, FALSE to not update only the lists
	 * @param array the available lists
	 * @return TRUE in success
	 */
	public function update($id, $vege=NULL, array $input_lists=NULL)
	{
		if ($input_lists === NULL)
		{
			// Try to get lists[] in case of $_POST update
			$input_lists = $this->input->post('lists[]');
		}
		
		$success = TRUE;
		if ($vege === NULL)
		{
			$success = $this->db->from(self::TABLE_NAME)
				->where(self::FIELD_ID, $id)
				->set(self::FIELD_NAME, $this->input->post('name'))
				->set(self::FIELD_UNIT, $this->input->post('unit'))
				->set(self::FIELD_PRICE, $this->input->post('price'))
				->set(self::FIELD_ACCOUNTING_CAT, $this->input->post('accounting_cat'))
				->update();		
			// $_POST update means lists[] may be sent too
			if ($input_lists === NULL)
			{
				$input_lists = array();
			}
		}
		elseif (is_array($vege))
		{
			$success = $this->db->from(self::TABLE_NAME)
				->where(self::FIELD_ID, $id)
				->set($vege)
				->update();
		}
		
		//And if ok update the lists subscribtion 
		if ($success === TRUE && $input_lists !== NULL)
		{
			$bdd_lists = array_column($this->get_delivery_lists($id), 'id');

			// Suscribe to new lists (i.e. who are int the input list but not yet in bdd)
			foreach(array_diff($input_lists, $bdd_lists) as $list)
			{
				$this->db->from(self::TABLE_LISTS_VEGETABLES)
					->set('v_id', $id)
					->set('l_id', $list)
					->insert();
			}

			// Remove suscribtion (i.e. who are in bdd but not in the input list)
			foreach(array_diff($bdd_lists, $input_lists) as $list)
			{
				$this->db->from(self::TABLE_LISTS_VEGETABLES)
					->where('v_id', $id)
					->where('l_id', $list)
					->delete();
			}
		}
		
		return $success;
	}
	
	public function create(array $veg=NULL)
	{
		$input_lists = $this->input->post('lists[]');
		if ($input_lists == NULL)
		{
			$input_lists = array();
		}
		
		if ($veg != NULL)
		{
			$name = $veg['name'];
			$unit = $veg['unit'];
			$price = $veg['price'];
			$accounting_cat = $veg['accounting_cat'];
		}
		else
		{
			$name = $this->input->post('name');
			$unit = $this->input->post('unit');
			$price = $this->input->post('price');
			$accounting_cat = $this->input->post('accounting_cat');
		}
		
		$success = $this->db
			->set(self::FIELD_NAME, $name)
			->set(self::FIELD_UNIT, $unit)
			->set(self::FIELD_PRICE, $price)
			->set(self::FIELD_ACCOUNTING_CAT, $accounting_cat)
			->insert(self::TABLE_NAME);
		
		if ($success === TRUE)
		{
			$id = $this->db->insert_id();
			
			// Suscribe to lists
			foreach($input_lists as $list)
			{
				$this->db->from(self::TABLE_LISTS_VEGETABLES)
					->set('v_id', $id)
					->set('l_id', $list)
					->insert();
			}

			return $id;
		}
		
		return FALSE;
	}
	
	public function delete($id)
	{
		// Remove avaibility
		$this->db->from(self::TABLE_LISTS_VEGETABLES)
			->where('v_id', $id)
			->delete();
		
		return $this->db->from(self::TABLE_NAME)
			->where(self::FIELD_ID, $id)
			->delete();
	}
	
	
	public function sort_vegetables_array(&$array)
	{
		usort($array, function($a, $b) 
			{
				if ($a->accounting_cat != 'veg' && $b->accounting_cat != 'veg')
				{
				  return strcmp($a->name, $b->name);
				}
				elseif ($a->accounting_cat != 'veg')
				{
				  return -1;
				}
				elseif ($b->accounting_cat != 'veg')
				{
				  return 1;
				}
				else
				{
				   return strcmp($a->name, $b->name);
				}
			});
	}
}
