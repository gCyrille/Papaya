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
class Vegetables extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('veget_model');
		$this->load->config('vegetables');
	}
	
	public function list($use_page=NULL,$page=NULL)
	{
		$this->load->library('pagination');
		$this->load->model('lists_model');

		// Fill datas for the template pages
		$data['title'] = 'Vegetable list';
		
		// Show service messages from other pages
		$this->service_message->load();
		
		if ($page == NULL)
		{
			$page = 0;
		}

		if ($use_page == NULL OR $use_page === 'all')
		{
			$results = $this->veget_model->get_list();
		}
		else
		{
			$results = $this->veget_model->get_list(15, $page);
		
			$config['base_url'] = base_url('/vegetables/list/page/');
			$config['per_page'] = 15;
			$config['total_rows'] = $this->veget_model->get_count();
			$this->pagination->initialize($config);
		}
		
		$avaibilities = array(); // 'veget_id' => array('list_id')
		foreach($results as $veget)
		{
			$avaibilities[$veget->id] = array_column($this->veget_model->get_delivery_lists($veget->id), 'id');
		}
		
		$data['veget_items'] = $results;
		$data['avaibilities'] = $avaibilities;
		$data['lists_count'] = $this->lists_model->get_count();
		$data['units'] = $this->config->item('units');
		$data['delivery_lists'] = array_column($this->lists_model->get_list(), NULL, 'id');
		
		// Load the templates in order with the data to print
		$this->load->view('templates/header', $data);
		$this->load->view('vegetables/list', $data);
		$this->load->view('templates/footer', $data);
	}
	
	/**
	 * REST API
	 * Search end point for Semantic UI API
	 *
	 * Syntax: 
	 *     vegetables/search/?q={query}
	 */
	public function search()
	{
		$vegetables = $this->veget_model->search($this->veget_model::FIELD_NAME);
		
		$units = $this->config->item('units');
		$accounting_cats = $this->config->item('accounting_cats');
		
		$answer = array(
			'results' => array (
			),
			'action' => array(
				'url' => base_url('vegetables/new'),
				'text' => 'Add a new vegetable'
			)
		);
		
		foreach ($vegetables as $veget)
		{
			$answer['results'][] = array(
				'title' 		=> $veget->name,
				'description' 	=> element(strtolower($veget->unit), $units),
				'price' 		=> $veget->price,
				'url' 			=> base_url('/vegetables/edit/'.$veget->id),
				'id'			=> $veget->id,
				'accounting_cat'=> $veget->accounting_cat, //$accounting_cats[$veget->accounting_cat],
				'unit'			=> element(strtolower($veget->unit), $units),
				'lists'			=> json_encode(array_column($this->veget_model->get_delivery_lists($veget->id), 'id'))
			);
		}
		
		echo json_encode($answer);
	}
	
	/**
	 * REST API
	 * End point for UI API to change vegetable availability
	 *
	 * Syntax:
	 *		vegetables/update_availibility/{vegetable_id}?lists={lists_ids}
	 */
	public function update_availibility($id)
	{
		$lists = json_decode($this->input->get('lists'));
		
		// Remove 'false' from lists to keep only the ids
		foreach($lists as $key => $item)
		{
			if ( is_bool($item))
			{
				unset($lists[$key]);
			}
		}
		
		$success = $this->veget_model->update($id, FALSE, $lists);
		
		$new_lists = array_column($this->veget_model->get_delivery_lists($id), 'id');
		
		$response = array(
			'success' 	=> $success === TRUE ? 'true' : 'false',
			'message' 	=> 'Vegetable updated!',
			'data'		=> array( 'id' => $id, 'lists' => json_encode($new_lists))
		);
		
		echo json_encode($response);
	}
	
	public function create()
	{
		$this->load->helper('form');
		$this->load->library('form_validation');
		$this->load->model('lists_model');
		
		$this->service_message->load();
		
		$veget = array ('id' => -1, 'name' => NULL, 'price' => 0.00, 'unit' => NULL, 'accounting_cat' => NULL);
		
		if ($this->_run_form_validation() === TRUE) 
		{
			// Save vegetable into db
			$id = $this->veget_model->create();
			if ($id !== FALSE)
			{
				// Success, go back to list and show message
				$veget = $this->veget_model->get($id);
				$msg = array (
					'type'		=> 'success',
					'title'		=> '<i class="check circle icon"></i> '.$veget->name.' successfully created!',
					'content'	=> '<br /><a class="ui basic button" href="'.base_url('/vegetables/edit/'.$veget->id).'">Go to edit</a>',
					'time'		=> time()
				);
				if ($this->input->post('new_after'))
				{
					$this->redirect->to('/vegetables/new', NULL, $msg);	
				}
				else
				{
					$this->redirect->to('/vegetables/list', NULL, $msg);	
				}
				
			}
			else
			{
				// Warning message: not save, retry
				$this->service_message->set(array (
					'type'		=> 'warning',
					'title'		=> '<i class="exclamation triangle icon"></i> Impossible to save!',
					'content'	=> 'Due to an internal error, the vegetable is not saved.<br/> Please try again.'
				));	
			}
		}
		
		// Load the templates in order with the data to print
		$data['title'] = 'New vegetable';
		$data['submit_btn'] = 'Create';
		$data['is_editing'] = FALSE;
		$data['back_link'] = base_url($this->redirect->build_url('/vegetables/list'));
		$data['form_url'] = 'vegetables/new';
		$data['veget'] = $veget;
		
		// Dropdown lists
		$units = array_merge(array( '0' => ''), $this->config->item('units'));
		$data['units'] = $units;
		$data['accounting_cats'] = $this->config->item('accounting_cats');
		$data['lists'] = array();
		$data['delivery_lists'] = array_column($this->lists_model->get_list(), 'name', 'id');
		
		$this->load->view('templates/header', $data);
		$this->load->view('vegetables/edit_form', $data);
		$this->load->view('templates/footer', $data);
	}
	
	public function edit($id=-1)
	{
		$this->load->helper('form');
		$this->load->library('form_validation');
		$this->load->model('lists_model');
		
		$veget = $this->veget_model->get($id, TRUE);
		if ($veget == NULL) {
			// Error, vegetable do not exists
			$msg = array (
				'type'		=> 'error',
				'title'		=> '<i class="attention icon"></i> Impossible to find the vegetable',
				'content'	=> '',
				'time'		=> time()
			);
			$this->redirect->to('/vegetables/list', NULL, $msg);
		}
		
		if ($this->_run_form_validation() === TRUE) 
		{
			if ($this->veget_model->update($id) === TRUE)
			{
				// Success, go back to list and show message
				$veget = $this->veget_model->get($id);
				$msg = array (
					'type'		=> 'success',
					'title'		=> '<i class="check circle icon"></i> '.$veget->name.' successfully saved!',
					'content'	=> '',
					'time'		=> time()
				);
				$this->redirect->to('/vegetables/list', NULL, $msg);
			}
			else
			{
				// Warning message: not save, retry
				$this->service_message->set(array (
					'type'		=> 'warning',
					'title'		=> '<i class="exclamation triangle icon"></i> Impossible to save!',
					'content'	=> 'Due to an internal error, the vegetable is not saved.<br/> Please try again.'
				));	
			}
		}
		
		// Load the templates in order with the data to print
		$data['title'] = 'Editing '.$veget['name'];
		$data['submit_btn'] = 'Save';
		$data['is_editing'] = TRUE;
		$data['back_link'] = base_url($this->redirect->build_url('/vegetables/list'));
		$data['form_url'] = 'vegetables/edit/'.$veget['id'];
		$data['veget'] = $veget;
		// Dropdown lists
		$units = array_merge(array( '0' => ''), $this->config->item('units'));
		$data['units'] = $units;
		$data['accounting_cats'] = $this->config->item('accounting_cats');
		$data['lists'] = array_column($this->veget_model->get_delivery_lists($id), 'id');
		$data['delivery_lists'] = array_column($this->lists_model->get_list(), 'name', 'id');
		
		$this->load->view('templates/header', $data);
		$this->load->view('vegetables/edit_form', $data);
		$this->load->view('templates/footer', $data);
	}
	
	public function delete($id=-1)
	{		
		$veget = $this->veget_model->get($id);
		if ($veget == NULL) {
			// Error, vegetable do not exists
			$msg = array (
						'type'		=> 'error',
						'title'		=> '<i class="attention icon"></i> Impossible to find the vegetable',
						'content'	=> '',
						'time'		=> time()
					);
			$this->redirect->to('/vegetables/list', NULL, $msg);
		}
		
		if ($this->veget_model->delete($id) === TRUE)
		{
			// Success, go back to list and show message
			$msg = array (
				'type'		=> 'success',
				'title'		=> '<i class="trash alternate icon"></i> '.$veget->name.' successfully deleted!',
				'content'	=> '',
				'time'		=> time()
			);
			$this->redirect->to('/vegetables/list', NULL, $msg);
		}
		else
		{
			$msg = array (
						'type'		=> 'error',
						'title'		=> '<i class="attention icon"></i> Impossible to delete '.$veget->name,
						'content'	=> '',
						'time'		=> time()
					);
			$this->redirect->to('/vegetables/list?msg='.$encoded_msg);
		}
	}
	
	private function _run_form_validation()
	{
		$this->form_validation->set_rules('name', 'Name', 'required|regex_match[/^[\\w -_()]+$/]|min_length[5]');
		$this->form_validation->set_rules('price', 'Price', 'required|numeric');
		$this->form_validation->set_rules(
			'unit', 
			'Unit', 
			'required|in_list['.implode(',', array_keys($this->config->item('units'))).']');
		$this->form_validation->set_rules(
			'accounting_cat', 
			'Accounting category', 
			'required|in_list['.implode(',', array_keys($this->config->item('accounting_cats'))).']');
		
		return $this->form_validation->run();
	}

}
