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
class Administration extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('admin_model');
	}

	/**
	 * Landing page for administration panel
	 */
	public function index()
	{	
		// Title page
		$data['title'] = 'Administration page';

		$data['db_ready'] = $this->admin_model->is_db_ready();
		$data['db_name'] = $this->admin_model->db_config('database');

		// Load the templates in order with the data to print
		$this->load->view('admin/header', $data);
		$this->load->view('admin/index', $data);
		//$this->load->view('templates/footer', $data);
	}
		
	/**
	 * Display the about page
	 */
	public function about()
	{
		$data['title'] = "About Papaya";

		$this->load->view('admin/header', $data);
		$this->load->view('admin/about', $data);
		//$this->load->view('templates/footer', $data);	
	}
	
	/**
	 * Page for setup processs
	 * @param string actual step of the installation
	 */
	public function setup($step='')
	{
		$this->load->helper('form');
		// Title page
		$data['title'] = 'Administration page';
		
		$override_db = (uri_string() == 'install') || ($this->input->post_get('override_db') === 'true');
		
		$data['override_db'] = $override_db && $this->admin_model->is_db_ready() === TRUE;
		
		if ($this->admin_model->is_db_ready() === TRUE && ! $override_db)
		{ 
			redirect('/admin');
		}
		
		if ($step === 'step1' && $this->admin_model->db_exists() && ! $override_db)
		{
			$step = 'step2';
		}
		
		switch($step)
		{
			case 'step1':
				$config_file = APPPATH.'config\database.php';
				
				$tpl = 'setup1';
				$data['db_name'] = $this->admin_model->db_config('database');
				$data['config_file'] = $config_file;
				break;
				
			case 'step2':
				//Check if db name is in POST
				$dbname = $this->input->post('dbname');
				
				
				if ($dbname !== NULL)
				{
					// Use this db name and change the config file
					$data['error'] = FALSE;
					$config_path 	= 'application/config/database.php';

					// Open the file
					$database_file = file_get_contents($config_path);

//					'database' => 'papaya',
					$replacement = sprintf("'database' => '%s'", $dbname);
					$new = preg_replace("/'database' => '(\w+)'/", $replacement, $database_file);
					// Write the new database.php file
					$handle = fopen($config_path,'w+');

					// Chmod the file, in case the user forgot
					@chmod($config_path, 0777);

					// Verify file permissions
					if(is_writable($config_path))
					{
						// Write the file
						if(fwrite($handle, $new)) 
						{
							$data['error'] = TRUE;
							$data['error'] = $this->admin_model->force_reload_config();
						} 
						else 
						{
							$data['error'] = FALSE;
						}
					} 
					else 
					{
						$data['error'] = FALSE;
					}
				}
				// else use default db name
				
				// Check db exists or create it
				$data['error'] = $this->admin_model->create_db();
				
				$data['db_name'] = $this->admin_model->db_config('database');
				
				$tpl = 'setup2';
				
				break;
			case 'step3':
				// use migration system to create the database
                if ($this->migration->current() === FALSE)
                {
					$data['error'] = show_error($this->migration->error_string());
                }
				else
				{
				}
				
				$tpl = 'setup3';
				break;
			default:
				$tpl = 'setup0';
		}
		
		$this->load->view('admin/header', $data);
		$this->load->view('admin/'.$tpl, $data);
		//$this->load->view('templates/footer', $data);
	}
	
	/**
	 * Page to start a migration of the database
	 * @param integer [$ver=-1] targeted migration version. -1 for the latest.
	 */
	public function migrate($ver=-1)
	{
		if ($ver == -1)
		{
			if ($this->migration->current() === FALSE)
			{
				echo show_error($this->migration->error_string());
			}
			else
			{
				redirect('/admin');
			}
		}
		else
		{
			if ($this->migration->version($ver) === FALSE)
			{
				echo show_error($this->migration->error_string());
			} 
			else
			{
				redirect('/admin');
			}
		}
	}
	
	/**
	 * Page to import a list of vegetables or of customers into database.
	 */
	public function import_file()
	{
		$this->load->helper(array('form', 'url'));
		
		$this->service_message->load();
		
		$data['title'] = 'Import a file into database';
		
		// Load the templates in order with the data to print
		$this->load->view('admin/header', $data);
		$this->load->view('admin/import_form', $data);
		//$this->load->view('templates/footer', $data);
	}
	
	/**
	 * Target page of the upload form for uploading vegetable list.
	 */
	public function do_upload_vege()
	{
		$this->load->config('vegetables');
		$this->load->library('PhpOffice');
		$this->load->model('veget_model');
		
		$config['upload_path']          = $this->phpoffice->getTemporaryFolder();
		$config['allowed_types']        = 'xls|xlsx|xlsm';

		$this->load->library('upload', $config);

		if ( ! $this->upload->do_upload('userfile'))
		{
			$encoded_msg = $this->service_message->as_url_param(array (
				'type'		=> 'error',
				'title'		=> '<i class="attention icon"></i> '.$this->upload->display_errors(''),
				'content'	=> '',
				'time'		=> time()
			));
			redirect('/administration/import_file/?msg='.$encoded_msg);
		}
		else
		{
			// Upload file with CI
			$data_file = $this->upload->data();
			// Then use PhpOffice to load the Excel spreadsheet
			$spreadsheet = $this->phpoffice->load_file($data_file['full_path']);
			
			$base_row = $this->input->post('first_row');
			$end_row = $this->input->post('last_row');
			$name_col = strtoupper($this->input->post('name_col'));
			$price_col = strtoupper($this->input->post('price_col'));
			$unit_col = strtoupper($this->input->post('unit_col'));
			$categ_col = strtoupper($this->input->post('accounting_col'));
			
			$units = $this->config->item('units');
			$accounting_cats = $this->config->item('accounting_cats');
			$vegets = array();
			$nb_imported = 0;
			
			for($row = $base_row; $row <= $end_row; $row++)
			{
				$name = $spreadsheet->getActiveSheet()->getCell($name_col.$row)->getCalculatedValue();
				$price = $spreadsheet->getActiveSheet()->getCell($price_col.$row)->getCalculatedValue();
				$unit = $spreadsheet->getActiveSheet()->getCell($unit_col.$row)->getCalculatedValue();
				$categ = $spreadsheet->getActiveSheet()->getCell($categ_col.$row)->getCalculatedValue();
				
				$veg = array(
					'name'			=> $name,
					'price'			=> $price,
					'unit'			=> $unit,
					'accounting_cat'=> 'veg',
					'imported'		=> 'false',
					'issue'		=> NULL);
				
				if ($unit != NULL && $name != NULL && $price != NULL)
				{
					if ($categ != NULL && key_exists(strtolower($categ), $accounting_cats))
					{
						$veg['accounting_cat'] = str_replace(' ', '_', strtolower($categ));
					}
					
					if (key_exists(strtolower($unit), $units))
					{
						$veg['unit'] = strtolower($unit);
						$veg['price'] = floatval($price);
						
						if ($this->veget_model->create($veg) !== FALSE)
						{
							$veg['imported'] = 'true';
							$nb_imported++;
						}
						else
						{
							switch($this->db->error()['code'])
							{
								case 1062:
									$veg['issue'] =  'Duplicate vegetable (already exists in database)';
									$veg['imported'] = 'unknown';
									break;
								default:
									$veg['issue'] =  $this->db->error()['message'];
							}
						}
					}
					else
					{
						$veg['issue'] = 'Unknown unit ('.$unit.')';
					}
				}
				else
				{
					$veg['issue'] = 'Wrong format';
				}
				$vegets[] = $veg;
			}
			
			$data['results'] = $vegets;
			$data['table_header'] = array('Description', 'Price', 'Unit', 'Categ', 'Imported', 'Issue');
			$data['nb_imported'] = $nb_imported;
			$data['title'] = 'Import a list of vegetables into database';
			
			// Load the templates in order with the data to print
			$this->load->view('admin/header', $data);
			$this->load->view('admin/import_results', $data);
			//$this->load->view('templates/footer', $data);
		}
	}
	
	/**
	 * Target page of the upload form for uploading customer list.
	 */
	public function do_upload_customers()
	{
		$this->load->config('vegetables');
		$this->load->library('PhpOffice');
		$this->load->model('customers_model');
		
		$config['upload_path']          = $this->phpoffice->getTemporaryFolder();
		$config['allowed_types']        = 'xls|xlsx|xlsm';

		$this->load->library('upload', $config);

		if ( ! $this->upload->do_upload('userfile'))
		{
			$encoded_msg = $this->service_message->as_url_param(array (
				'type'		=> 'error',
				'title'		=> '<i class="attention icon"></i> '.$this->upload->display_errors(''),
				'content'	=> '',
				'time'		=> time()
			));
			redirect('/administration/import_file/?msg='.$encoded_msg);
		}
		else
		{
			// Upload file with CI
			$data_file = $this->upload->data();
			// Then use PhpOffice to load the Excel spreadsheet
			$spreadsheet = $this->phpoffice->load_file($data_file['full_path']);
			
			$base_row = $this->input->post('first_row');
			$end_row = $this->input->post('last_row');
			$name_col = strtoupper($this->input->post('name_col'));
			$contact_col = strtoupper($this->input->post('contact_col'));
			$email_col = strtoupper($this->input->post('email_col'));
			$place_col = strtoupper($this->input->post('place_col'));
			
			$customers = array();
			$nb_imported = 0;
			
			for($row = $base_row; $row <= $end_row; $row++)
			{
				$name = $spreadsheet->getActiveSheet()->getCell($name_col.$row)->getCalculatedValue();
				$contact = $spreadsheet->getActiveSheet()->getCell($contact_col.$row)->getCalculatedValue();
				$email = $spreadsheet->getActiveSheet()->getCell($email_col.$row)->getCalculatedValue();
				$place = $spreadsheet->getActiveSheet()->getCell($place_col.$row)->getCalculatedValue();
				
				$customer = array(
					'name'				=> $name,
					'contact_name'		=> $contact,
					'email'				=> $email,
					'email_2'			=> NULL,
					'delivery_place'	=> $place,
					'delivery_place_2'	=> NULL,
					'imported'			=> 'false',
					'issue'				=> NULL);
				
				if ($name != NULL)
				{
					if ($this->customers_model->create($customer) !== FALSE)
					{
						$customer['imported'] = 'true';
						$nb_imported++;
					}
					else
					{
						switch($this->db->error()['code'])
						{
							case 1062:
								$customer['issue'] =  $this->db->error()['message'];
								$customer['imported'] = 'unknown';
								break;
							default:
								$customer['issue'] =  $this->db->error()['message'];
						}
					}
				}
				else
				{
					$customer['issue'] = 'Wrong format';
				}
				
				// Unset for display only filled column
				unset($customer['email_2']);
				unset($customer['delivery_place_2']);
				
				$customers[] = $customer;
			}
			
			$data['results'] = $customers;
			$data['table_header'] = array('Name', 'Contact', 'Email', 'Place', 'Imported', 'Issue');
			$data['nb_imported'] = $nb_imported;
			$data['title'] = 'Import a list of customers into database';
			
			// Load the templates in order with the data to print
			$this->load->view('admin/header', $data);
			$this->load->view('admin/import_results', $data);
			//$this->load->view('templates/footer', $data);
		}
	}
	
	/**
	 * Page to manage database backup
	 * @param string [$action=NULL] action can be : upload or download. NULL will just display the page.
	 */
	public function backup_db($action=NULL)
	{
		$this->load->helper('form');
		
		// Title page
		$data['title'] = 'Database backup';
		$data['error'] = NULL;
		
		if ($action == NULL)
		{
			// Load the templates in order with the data to print
			$this->load->view('admin/header', $data);
			$this->load->view('admin/backup_db', $data);
			//$this->load->view('templates/footer', $data);	
		}
		else
		{
			switch($action)
			{
				case 'download':
					$backup_name = time().'_veget_database_backup_';
					$backup_type = 'full';
					
					// Load the DB utility class
					$this->load->dbutil();

					$prefs = array(
						/// ignore VIEW. keep migrations table that tell which version of db structure it is
						'ignore'        => array('detailed_orders'),// List of tables to omit from the backup
						'format'        => 'zip',						// gzip, zip, txt
						'add_drop'      => TRUE,						// Whether to add DROP TABLE statements to backup file
						'add_insert'    => TRUE,						// Whether to add INSERT data to backup file
					);
					
					if (stripos($this->input->get('what'), 'customers') !== FALSE)
					{
						$prefs['tables'][] = 'customers';
						$backup_type = 'customers';
					}
					if (stripos($this->input->get('what'), 'vegetables') !== FALSE)
					{
						$prefs['tables'][] = 'vegetables';
						if ($backup_type !== 'full')
						{
							$backup_type .= '-vegetables';
						}
						else
						{
							$backup_type = 'vegetables';
						}
					}
					
					$backup_name .= $backup_type;
					$prefs['filename'] = $backup_name.'.sql'; // File name - NEEDED ONLY WITH ZIP FILES
					
					// Backup your entire database and assign it to a variable
					$backup = $this->dbutil->backup($prefs);

					// Load the file helper and write the file to your server
					$this->load->helper('file');
					write_file('./backups/'.$backup_name.'.zip', $backup);

					// Load the download helper and send the file to your desktop
					$this->load->helper('download');
					force_download($backup_name.'.zip', $backup);
					break;
					
				case 'upload':
					$this->load->library('PhpOffice');
					$config['upload_path']		= $this->phpoffice->getTemporaryFolder();
					$config['allowed_types']	= array('zip', 'sql');//'sql|text|zip|gzip';

					$this->load->library('upload', $config);

					if ( ! $this->upload->do_upload('upload_backup'))
					{
						print_r($this->upload->data());
						$data['error'] = $this->upload->display_errors();
					}
					else
					{
						// Get uploaded file with CI
						$file_data = $this->upload->data();
						
						$sql = NULL;
						
						// First, is it a zip or sql ?
						if ($file_data['file_type'] == 'application/zip')
						{
							$zip = new ZipArchive;
							$res = $zip->open($file_data['full_path']);
							if ($res === TRUE) 
							{
								if ($zip->numFiles != 1)
								{
									$data['error'] = 'There more than one file in the archive.';
								}
								else
								{
									$sql = $zip->getFromIndex(0);
								}
								
								$zip->close();
								
							} else {
								$data['error'] = 'Cannot extract the zip file. (code: ' . $res . ')';
							}
						}
						elseif ($file_data['file_type'] == 'text/plain')
						{
							$sql = file_get_contents($file_data['full_path']);
						}
						else
						{
							$data['error'] = 'Unknown file type';
						}
						
						// Then process the SQL
						if ($sql !== NULL)
						{
							$lines = preg_split("/[\n\r]/", $sql);
									
							$query = '';
							$create_line = FALSE;

							$this->load->database();
							$this->db->trans_start(); // Query will be rolled back
							foreach($lines as $line)
							{
								// Skip comments
								if (strrpos($line, '#') !== FALSE)
								{
									continue;
								}

								// CREATE query is on multiple lines
								if (stripos($line, 'CREATE') !== FALSE)
								{
									$query = $line;
									$create_line = TRUE;
								}
								elseif (empty($line) && $create_line)
								{
									// End of create line, run query
									$r = $this->db->query($query);
									$query = '';
									$create_line = FALSE;
								}
								elseif ( ! empty($line))
								{
									// Add to CREATE query or run
									if ($create_line)
									{
										$query .= $line;
									}
									else
									{
										$r = $this->db->query($line);
									}
								}
							}
							$this->db->trans_complete();

							if ($this->db->trans_status() === FALSE)
							{
								log_message('error', 'Error during restoring database. [file='.$file_data['orig_name'].']');
								$data['error'] = 'Cannot restore database: There is one or more error in the SQL file (message='.$this->db->error()['message'].')';
							}
							else
							{
								$data['success'] = 'Data recovered from the backup. <br /><br />'
									.'<a class="ui button" href="'.base_url().'">'
									.'<i class="left arrow icon"></i>'
									.'Back to Papaya</a>';
							}
						}
					}
					$this->load->view('admin/header', $data);
					$this->load->view('admin/backup_db', $data);
					//$this->load->view('templates/footer', $data);	
					break;
			}
			
		}
		
	}
	
	/**
	 * Generic controler to show pages to edit the configuration file.
	 * @param string [$what=NULL] the item of the config to edit. NULL shows 404 error.
	 */
	public function config_file($what=NULL)
	{
		$config_cats['units'] = array(
			'title'		=> 'vegetables units',
			'name'		=> 'unit',
			'preg'		=> "/[$]config\['units'\] = array\(\s*('[a-z0-9_-]+'\s*=>\s*'[ A-Za-z0-9_-]+'[,]?\s*.*\s*)+\s*\);/",
			'php_line'	=> "\$config['units'] = array(\n"
			
		);
		
		$config_cats['accounting_cats'] = array(
			'title'		=> 'accouting categories',
			'name'		=> 'accounting category',
			'preg'		=> "/[$]config\['accounting_cats'\] = array\(\s*('[a-z0-9_-]+'\s*=>\s*'[ A-Za-z0-9_-]+'[,]?\s*.*\s*)+\s*\);/",
			'php_line'	=> "\$config['accounting_cats'] = array(\n"
			
		);
		$config_cats['expenses_cats'] = array(
			'title'		=> 'cash expenses categories',
			'name'		=> 'expenses category',
			'preg'		=> "/[$]config\['expenses_cats'\] = array\(\s*('[a-z0-9_-]+'\s*=>\s*'[ A-Za-z0-9_-]+'[,]?\s*.*\s*)+\s*\);/",
			'php_line'	=> "\$config['expenses_cats'] = array(\n"
		);
		
		if ($what === NULL ||  ! key_exists($what, $config_cats))
		{
			show_404();
		}
		
		$this->load->helper('form');
		$this->load->config('vegetables');
		$this->load->library('form_validation');
		
		$this->service_message->load();
		
		// Title page
		$data['title'] = 'Edit '.$config_cats[$what]['title'];
		$data['form_url'] = 'administration/config_file/'.$what;
		$data['what'] = $config_cats[$what]['name'];
		
		$input = $this->input->post('units');
//		var_dump($input);
		
		$units = $this->config->item($what);

		if ($input != NULL)
		{
			foreach($input as $i => $row)
			{
				$this->form_validation->set_rules(
					'units['.$i.'][code]', 
					'Unit '.($i+1), 
					'trim|required|alpha_dash',
					array(
						'required' 		=> 'Please enter a code',
						'alpha_dash'	=> 'The internal code can contain only numbers, small letters, dashs and underscores')
				);
				$this->form_validation->set_rules(
					'units['.$i.'][name]', 
					'Name '.($i+1), 
					'trim|required|regex_match[/^[ ,A-Za-z0-9_-]+$/]',
					array(
						'required'		=> 'Please enter a name',
						'regex_match'	=> 'The display name can contain only numbers, small letters, spaces, dashs and underscores')
				);
			}
		}
		
		if ($input !== NULL && count($units) < count($input))
		{
			// More new lines than old
			$size_u = count($units);
			$size_i = count($input);
			for ($i = $size_u; $i < $size_i; $i++)
			{
				$units['Code '.($i+1)] = 'Name '.($i+1); // Fake unit, will be replace by old field if needed
			}
		}
		
		
		if ($this->form_validation->run() == FALSE)
		{
			$data['units'] = $units;
		}
		else
		{
			// Save into vegetables config file
			$config_path 	= 'application/config/vegetables.php';

			

			//Default accouting category check//
			if ($what == 'accounting_cats')
			{
				if ( ! in_array('veg', array_column($input, 'code')))
				{
						$input[] = array('code' => 'veg', 'name' => 'Vegetable');
				}
			}
			//End for accounting cat//
			
			/* Produce array as code string
					$config['units'] = array(
						'kg'	=> 'Kg',
						'100gr'	=> '100g',
						...
					);*/
			$php_code = $config_cats[$what]['php_line'];
			foreach($input as $unit)
			{
				$code = $unit['code'];
				$name = $unit['name'];
				$php_code .= "\t\t'$code'\t\t=> '$name',\n";	
			}
			$php_code .= "\t);";
			
			// Open the file
			$config_file = file_get_contents($config_path);

			$new = preg_replace($config_cats[$what]['preg'],
								$php_code, 
								$config_file);
			
			// Write the new vegetables.php file
			$handle = fopen($config_path,'w+');

			// Chmod the file, in case the user forgot
			@chmod($config_path, 0777);

			// Verify file permissions
			if(is_writable($config_path))
			{
				// Write the file
				if(fwrite($handle, $new)) 
				{
					$this->service_message->set(array (
						'type'		=> 'success',
						'title'		=> '<i class="thumbs up icon"></i> Config file successfully saved!',
						'content'	=> '',
						'time'		=> time()
					));
					$key = array_search(APPPATH.'config/vegetables.php', $this->config->is_loaded);
					if ($key !== FALSE)
					{
						unset($this->config->is_loaded[$key]);
					}
					$this->load->config('vegetables');
					$units = $this->config->item($what);
				} 
				else 
				{
					// Error
					$this->service_message->set(array (
						'type'		=> 'error',
						'title'		=> '<i class="exclamation triangle icon"></i> Cannot save the file... Please check the configuration and retry',
						'content'	=> '',
						'time'		=> time()
					));
				}
			} 
			else 
			{
				// Error
				$this->service_message->set(array (
					'type'		=> 'error',
					'title'		=> '<i class="exclamation triangle icon"></i> Cannot save the file... Please check the configuration and retry',
					'content'	=> '',
					'time'		=> time()
				));
			}
			
			$data['units'] = $units;
		}
		
		// Load the templates in order with the data to print
		$this->load->view('admin/header', $data);
		$this->load->view('admin/config_file', $data);
		//$this->load->view('templates/footer', $data);
	}
	
	/**
	 * Page to edit the password.
	 */
	public function change_password()
	{
		$this->load->helper('form');
		$this->load->config('vegetables');
		$this->load->library('form_validation');
		
		$this->service_message->load();
		
		// Title page
		$data['title'] = 'Change the password';
		
		$this->form_validation->set_rules(
			'passold',
			'Old Password',
			array( //Rules
				'required',
				array(
					'password_check_callable',
					function($value)
					{
						if ($value != $this->config->item('edit_password'))
						{
							return FALSE;
						}
						else
						{
							return TRUE;
						}
					}
				)
			),
			array( //Messages
				'password_check_callable' =>  '{field} doesn\'t match the actual password.'
			)
		);
		$this->form_validation->set_rules('password', 'New password', 'required|min_length[4]|alpha_numeric');
		$this->form_validation->set_rules('passconf', 'Password Confirmation', 'required|matches[password]');
		
		if ($this->form_validation->run() == FALSE)
		{
			//Load default page
		}
		else
		{
			//Check if old is ok!
			$new_pwd = $this->input->post('password');
			
			// Save into vegetables config file
			$config_path 	= 'application/config/vegetables.php';
			
			/* Produce array as code string
				$config['edit_password'] = '****';
			*/
			$replacement = sprintf("\$config['edit_password'] = '%s';", $new_pwd);
			
			// Open the file
			$config_file = file_get_contents($config_path);

			$new = preg_replace("/[$]config\['edit_password'\] = '(\w+)';/",
								$replacement, 
								$config_file);
			
			// Write the new vegetables.php file
			$handle = fopen($config_path,'w+');

			// Chmod the file, in case the user forgot
			@chmod($config_path, 0777);

			// Verify file permissions
			if(is_writable($config_path))
			{
				// Write the file
				if(fwrite($handle, $new)) 
				{
					$this->service_message->set(array (
						'type'		=> 'success',
						'title'		=> '<i class="thumbs up icon"></i> Password successfully changed!',
						'content'	=> '',
						'time'		=> time()
					));
					
					// Reset form
					$this->form_validation->reset_validation();
					$_POST['passold'] = NULL;
					$_POST['password'] = NULL;
					$_POST['passconf'] = NULL;
				} 
				else 
				{
					// Error
					$this->service_message->set(array (
						'type'		=> 'error',
						'title'		=> '<i class="exclamation triangle icon"></i> Cannot change the password... Please check the configuration and retry',
						'content'	=> '',
						'time'		=> time()
					));
				}
			} 
			else 
			{
				// Error
				$this->service_message->set(array (
					'type'		=> 'error',
					'title'		=> '<i class="exclamation triangle icon"></i> Cannot change the password... Please check the configuration and retry',
					'content'	=> '',
					'time'		=> time()
				));
			}
		}
		
		// Load the templates in order with the data to print
		$this->load->view('admin/header', $data);
		$this->load->view('admin/change_pwd', $data);
		//$this->load->view('templates/footer', $data);
	}
	
	/**
	 * Page to edit the templates used to export or import vegetable lists.
	 * @param string [$action=NULL] used to send the form in POST ('export' or 'import').
	 */
	public function tpl_veget_lists($action=NULL)
	{
		$this->load->helper(array('form', 'url'));
		
		$this->service_message->load();
		
		$data['title'] = 'Manage Excel templates';
		
		if ($action !== NULL && in_array($action, array('export', 'import')) && $this->input->method() == 'post')
		{	
			$config_cats['export'] = array(
				'preg'		=> "/^[$]config\['tpl_export_veg_list'\] = (NULL|array\(\s*('[a-z0-9_-]+'\s*=>\s*.*\s*)+\s*\));/m"
			);
			$config_cats['import'] = array(
				'preg'		=> "/^[$]config\['tpl_import_veg_list'\] = ([$]config\['tpl_export_veg_list'\]|array\(\s*('[a-z0-9_-]+'\s*=>\s*.*\s*)+\s*\));/m"
			);

			if ($action == 'export')
			{
				$this->load->library('PhpOffice');

				$doupload = $this->input->post('use_file');
				
				if ($doupload == 'TRUE')
				{
					$config['upload_path']          = $this->phpoffice->getTemporaryFolder();
					$config['allowed_types']        = 'xls|xlsx|xlsm';

					$this->load->library('upload', $config);

					if ( ! $this->upload->do_upload('userfile'))
					{
						// Error even if no file, save the config with old template file
						$tpl_export_veg_list = $this->config->item('tpl_export_veg_list');
						$new_tpl_file = $tpl_export_veg_list['filename'];
						$generate_config = TRUE;
					}
					else
					{
						// Upload file with CI
						$data_file = $this->upload->data();

						$copied = copy($data_file['full_path'], APPPATH.'/views/excel/'.$data_file['file_name']);
						if ( ! $copied)
						{
							// Error
							$this->service_message->set(array (
								'type'		=> 'error',
								'title'		=> '<i class="attention icon"></i> An error occurred. Please try again.',
								'content'	=> '',
								'time'		=> time()
							));
							
							$generate_config = FALSE;
						}
						else
						{
							//The new file name is this one:
							$new_tpl_file = $data_file['file_name'];
							$generate_config = TRUE;
						}
					}
					if ($generate_config)
					{
						$name_col = $this->input->post('name_col');
						$price_col = $this->input->post('price_col');
						$unit_col = $this->input->post('unit_col');
						$qtt_col = $this->input->post('qtt_col');
						$total_col = $this->input->post('total_col');
						$base_row_vege = $this->input->post('base_row_vege');
						$base_row_other = $this->input->post('base_row_other');

						/* Produce array as code string
						$config['tpl_export_veg_list'] = array(
							'filepath'		=> APPPATH.'/views/excel/',
							'filename'		=> 'Vegetable Liste.xlsx',
							'base_row_vege'	=> 15, // Row to use to insert vegetables
							'base_row_other'=> 14, // Row to use to insert other items
							'column_name'	=> 'B',
							'column_price'	=> 'C',
							'column_unit'	=> 'D',
							'column_order'	=> 'E',
							'column_total'	=> 'F'
						);*/
						$php_code = "\$config['tpl_export_veg_list'] = array(\n";
						$php_code .= "\t\t'filepath'\t\t=> APPPATH.'/views/excel/',\n";	
						$php_code .= "\t\t'filename'\t\t=> '$new_tpl_file',\n";	
						$php_code .= "\t\t'base_row_vege'\t\t=> $base_row_vege,\n";	
						$php_code .= "\t\t'base_row_other'\t\t=> $base_row_other,\n";	
						$php_code .= "\t\t'column_name'\t\t=> '$name_col',\n";	
						$php_code .= "\t\t'column_price'\t\t=> '$price_col',\n";	
						$php_code .= "\t\t'column_unit'\t\t=> '$unit_col',\n";	
						$php_code .= "\t\t'column_order'\t\t=> '$qtt_col',\n";	
						$php_code .= "\t\t'column_total'\t\t=> '$total_col',\n";	
						$php_code .= "\t);";
					}
				}
				else
				{
					$php_code = "\$config['tpl_export_veg_list'] = NULL;";
				}
			}
			elseif ($action == 'import')
			{
				$use_export = $this->input->post('use_export');
				
				if ($use_export == 'TRUE')
				{
					$php_code = "\$config['tpl_import_veg_list'] = \$config['tpl_export_veg_list'];";
				}
				else
				{
					$name_col = $this->input->post('name_col');
					$qtt_col = $this->input->post('qtt_col');
					$base_row_vege = $this->input->post('base_row_vege');
					$base_row_other = $this->input->post('base_row_other');
					
					/* Produce array as code string
					$config['tpl_import_veg_list'] = array(
						'base_row_vege'	=> 2,
						'base_row_other'=> 1,
						'column_name'	=> 'A',
						'column_order'	=> 'D'
					);*/
					$php_code = "\$config['tpl_import_veg_list'] = array(\n";
					$php_code .= "\t\t'base_row_vege'\t\t=> $base_row_vege,\n";	
					$php_code .= "\t\t'base_row_other'\t\t=> $base_row_other,\n";	
					$php_code .= "\t\t'column_name'\t\t=> '$name_col',\n";	
					$php_code .= "\t\t'column_order'\t\t=> '$qtt_col',\n";	
					$php_code .= "\t);";
				}
			}
			
			if (isset($php_code)) // Means write the new config file
			{
				// Save into vegetables config file
				$config_path 	= 'application/config/vegetables.php';

				// Open the file
				$config_file = file_get_contents($config_path);

				$new = preg_replace($config_cats[$action]['preg'],
									$php_code, 
									$config_file);
				
				print_r($new);

				// Write the new vegetables.php file
				$handle = fopen($config_path,'w+');

				// Chmod the file, in case the user forgot
				@chmod($config_path, 0777);

				// Verify file permissions
				if(is_writable($config_path))
				{
					// Write the file
					if(fwrite($handle, $new)) 
					{
						$this->service_message->set(array (
							'type'		=> 'success',
							'title'		=> '<i class="thumbs up icon"></i> Config file successfully saved!',
							'content'	=> '',
							'time'		=> time()
						));
						$key = array_search(APPPATH.'config/vegetables.php', $this->config->is_loaded);
						if ($key !== FALSE)
						{
							unset($this->config->is_loaded[$key]);
						}
						$this->load->config('vegetables');
	//					$tpl_import_veg_list = $this->config->item('tpl_import_veg_list');
	//					$tpl_export_veg_list = $this->config->item('tpl_export_veg_list');
					} 
					else 
					{
						// Error
						$this->service_message->set(array (
							'type'		=> 'error',
							'title'		=> '<i class="exclamation triangle icon"></i> Cannot save the file... Please check the configuration and retry',
							'content'	=> '',
							'time'		=> time()
						));
					}
				} 
				else 
				{
					// Error
					$this->service_message->set(array (
						'type'		=> 'error',
						'title'		=> '<i class="exclamation triangle icon"></i> Cannot save the file... Please check the configuration and retry',
						'content'	=> '',
						'time'		=> time()
					));
				}
			}
		}
		
		$letters = array();
		$numbers = array();
		
		for ($l = 'A', $n = 1; $l <= 'Y'; $l++, $n++)
		{
			$letters[$l] = $l;
			$numbers[$n] = $n;
		}
		
		$data['letters'] = $letters;
		$data['numbers'] = $numbers;
		
		$tpl_import_veg_list = $this->config->item('tpl_import_veg_list');
		$tpl_export_veg_list = $this->config->item('tpl_export_veg_list');
		
		if ($tpl_export_veg_list == NULL OR empty($tpl_export_veg_list))
		{
			$tpl_export_veg_list = array(
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
			$data['use_tpl'] = FALSE;
		}
		else
		{
			$data['use_tpl'] = TRUE;
		}
		
		if ($tpl_import_veg_list == NULL OR empty($tpl_import_veg_list))
		{
			if ($data['use_tpl'] === FALSE)
			{
				$data['use_export'] = TRUE;
			}
			else
			{
				$data['use_export'] = FALSE;
				$tpl_import_veg_list = array(
					'base_row_vege'	=> 2, // Row to use to insert vegetables
					'base_row_other'=> 1, // Row to use to insert other items
					'column_name'	=> 'A',
					'column_order'	=> 'D'
				);
			}
		}
		else
		{
			//Read config file
			$config_path 	= 'application/config/vegetables.php';
			$config_file = file_get_contents($config_path);

			$preg = "/^[$]config\['tpl_import_veg_list'\] = ([$]config\['tpl_export_veg_list'\]|array\(\s*('[a-z0-9_-]+'\s*=>\s*.*\s*)+\s*\));/m";
			
			$ret = preg_match($preg,
							  $config_file,
							  $matches);

			if ($ret === 1 && $matches[1] == "\$config['tpl_export_veg_list']")
			{
				$data['use_export'] = TRUE;
			}
			else
			{
				$data['use_export'] = FALSE;
			}
		}
		
		$data['tpl_import_veg_list'] = $tpl_import_veg_list;
		$data['tpl_export_veg_list'] = $tpl_export_veg_list;
		
		// Load the templates in order with the data to print
		$this->load->view('admin/header', $data);
		$this->load->view('admin/tpl_veget_lists', $data);
		//$this->load->view('templates/footer', $data);
	}
	
	/**
	 * Page to edit the template used for the collect list
	 * @param string [$action=NULL] used to send the form (save)
	 */
	public function tpl_collect_list($action=NULL)
	{
		$this->load->helper(array('form', 'url'));
		
		$this->service_message->load();
		
		$data['title'] = 'Manage Excel templates';
		
		if ($action == 'save' && $this->input->method() == 'post')
		{	
			$this->load->library('PhpOffice');

			$doupload = $this->input->post('use_file');

			$config['upload_path']          = $this->phpoffice->getTemporaryFolder();
			$config['allowed_types']        = 'xls|xlsx|xlsm';

			$this->load->library('upload', $config);

			if ( ! $this->upload->do_upload('userfile'))
			{
				// Error, do nothing, just save the file
				//Keep old file name and save new values
				$tpl_collect_list = $this->config->item('tpl_collect_list');
				$new_tpl_file = $tpl_collect_list['filename'];
				$save_new_config = TRUE;
			}
			else
			{
				// Upload file with CI
				$data_file = $this->upload->data();

				$copied = copy($data_file['full_path'], APPPATH.'/views/excel/'.$data_file['file_name']);
				if ( ! $copied)
				{
					// Error
					$this->service_message->set(array (
						'type'		=> 'error',
						'title'		=> '<i class="attention icon"></i> An error occurred. Please try again.',
						'content'	=> '',
						'time'		=> time()
					));
					$save_new_config = FALSE;
				}
				else
				{
					//The new file name is this one:
					$new_tpl_file = $data_file['file_name'];
					$save_new_config = TRUE;
				}
			}
			if ($save_new_config)
			{
				$date_col = $this->input->post('date_col');
				$date_row = $this->input->post('date_row');
				$row_customer = $this->input->post('row_customer');
				$base_column = $this->input->post('base_column');
				$last_column = $this->input->post('last_column');
				$column_desc = $this->input->post('column_desc');
				$unit_col = $this->input->post('unit_col');
				$total_col = $this->input->post('total_col');
				$base_row = $this->input->post('base_row');

				/* Produce array as code string
				$config['tpl_collect_list'] = array(
					'filepath'		=> APPPATH.'/views/excel/',
					'filename'		=> 'collect_list.xlsx',
					'cell_date'		=> 'A1',
					'base_row'		=> 3, // From which row insert vegetables
					'base_column'	=> 'B', // From which column to insert customers
					'last_column'	=> 'AE',// Until which column to inset customers
					'row_customer'	=> 1, // On which row to insert customers
					'column_desc'	=> 'A', // Name/Description of vegetable
					'column_unit'	=> 'AG', // Unit of vegetables
					'column_total'	=> 'AF' // Total for the row
				);*/
				$php_code = "\$config['tpl_collect_list'] = array(\n";
				$php_code .= "\t\t'filepath'\t\t=> APPPATH.'/views/excel/',\n";	
				$php_code .= "\t\t'filename'\t\t=> '$new_tpl_file',\n";	
				$php_code .= "\t\t'cell_date'\t\t=> '$date_col$date_row',\n";	
				$php_code .= "\t\t'base_row'\t\t=> $base_row,\n";	
				$php_code .= "\t\t'base_column'\t\t=> '$base_column',\n";	
				$php_code .= "\t\t'last_column'\t\t=> '$last_column',\n";	
				$php_code .= "\t\t'row_customer'\t\t=> $row_customer,\n";	
				$php_code .= "\t\t'column_desc'\t\t=> '$column_desc',\n";	
				$php_code .= "\t\t'column_unit'\t\t=> '$unit_col',\n";	
				$php_code .= "\t\t'column_total'\t\t=> '$total_col',\n";	
				$php_code .= "\t);";

				// Save into vegetables config file
				$config_path 	= 'application/config/vegetables.php';

				// Open the file
				$config_file = file_get_contents($config_path);

				$preg = "/^[$]config\['tpl_collect_list'\] = array\(\s*('[a-z0-9_-]+'\s*=>\s*.*\s*)+\s*\);/m";
				$new = preg_replace($preg,
									$php_code, 
									$config_file);

				// Write the new vegetables.php file
				$handle = fopen($config_path,'w+');

				// Chmod the file, in case the user forgot
				@chmod($config_path, 0777);

				// Verify file permissions
				if(is_writable($config_path))
				{
					// Write the file
					if(fwrite($handle, $new)) 
					{
						$this->service_message->set(array (
							'type'		=> 'success',
							'title'		=> '<i class="thumbs up icon"></i> Config file successfully saved!',
							'content'	=> '',
							'time'		=> time()
						));
						$key = array_search(APPPATH.'config/vegetables.php', $this->config->is_loaded);
						if ($key !== FALSE)
						{
							unset($this->config->is_loaded[$key]);
						}
						$this->load->config('vegetables');
	//					$tpl_import_veg_list = $this->config->item('tpl_import_veg_list');
	//					$tpl_export_veg_list = $this->config->item('tpl_export_veg_list');
					} 
					else 
					{
						// Error
						$this->service_message->set(array (
							'type'		=> 'error',
							'title'		=> '<i class="exclamation triangle icon"></i> Cannot save the file... Please check the configuration and retry',
							'content'	=> '',
							'time'		=> time()
						));
					}
				} 
				else 
				{
					// Error
					$this->service_message->set(array (
						'type'		=> 'error',
						'title'		=> '<i class="exclamation triangle icon"></i> Cannot save the file... Please check the configuration and retry',
						'content'	=> '',
						'time'		=> time()
					));
				}
			}
		}
		
		$letters = array();
		$numbers = array();
		
		for ($l = 'A', $n = 1; $l < 'ZZ'; $l++, $n++)
		{
			$letters[$l] = $l;
			$numbers[$n] = $n;
		}
		
		$data['letters'] = $letters;
		$data['numbers'] = $numbers;
		
		$tpl_collect_list = $this->config->item('tpl_collect_list');
		
		preg_match("/^([A-Z]+)([0-9]+)/m", $tpl_collect_list['cell_date'], $matches);
		$tpl_collect_list['col_date'] = $matches[1];
		$tpl_collect_list['row_date'] = $matches[2];
		
		$data['tpl_collect_list'] = $tpl_collect_list;
		
		// Load the templates in order with the data to print
		$this->load->view('admin/header', $data);
		$this->load->view('admin/tpl_collect_list', $data);
		//$this->load->view('templates/footer', $data);
	}
	/**
	 * Page to manage the backup of the config file.
	 * @param string [$action=NULL] action can be: upload or download. NULL will just show the page.
	 */
	public function backup_config($action=NULL)
	{
		$this->load->helper('form');
		
		// Title page
		$data['title'] = 'Configuration backup';
		$data['error'] = NULL;
		
		if ($action == NULL)
		{
			// Load the templates in order with the data to print
			$this->load->view('admin/header', $data);
			$this->load->view('admin/backup_config', $data);
			//$this->load->view('templates/footer', $data);	
		}
		else
		{
			switch($action)
			{
				case 'download':
					
					$this->load->library('PhpOffice');
					$temp_path	= $this->phpoffice->getTemporaryFolder().'/';
					
					$config_path = APPPATH.'config/'; 
					$backup_name = time().'_papaya_configuration_backup.zip';
					
					$zip = new ZipArchive;
					
					if ($zip->open($temp_path.$backup_name,  ZipArchive::CREATE ) === TRUE) {
						$zip->addFile($config_path.'vegetables.php', 'vegetables.php');
						// $zip->addFile($config_path.'database.php', 'database.php');
						$zip->close();
					
						// Load the download helper and send the file to your desktop
						$this->load->helper('download');
						// force_download($backup_name, file_get_contents($config_file));
						force_download($temp_path.$backup_name, NULL);
					}
					break;
					
				case 'upload':
					$this->load->library('PhpOffice');
					$config['upload_path']		= $this->phpoffice->getTemporaryFolder();
					$config['allowed_types']	= array('zip', 'php');//'sql|text|zip|gzip';

					$this->load->library('upload', $config);

					if ( ! $this->upload->do_upload('upload_backup'))
					{
						$data['error'] = $this->upload->display_errors();
					}
					else
					{
						// Get uploaded file with CI
						$file_data = $this->upload->data();
						
						$file_content = NULL;
						
						// First, is it a zip or php ?
						if ($file_data['file_type'] == 'application/zip')
						{
							$zip = new ZipArchive;
							$res = $zip->open($file_data['full_path']);
							if ($res === TRUE) 
							{
								for ($i=0; $i < $zip->numFiles; $i++)
								{
									if ($zip->getNameIndex($i) == 'vegetables.php')
									{
										$file_content = $zip->getFromIndex($i);
									}
								}
								
								if ($file_content === NULL)
								{
									$data['error'] = 'There more than one file in the archive.';
								}
								
								$zip->close();
							} 
							else 
							{
								$data['error'] = 'Cannot extract the zip file. (code: ' . $res . ')';
							}
						}
						elseif ($file_data['file_type'] == 'text/plain')
						{
							$file_content = file_get_contents($file_data['full_path']);
						}
						else
						{
							$data['error'] = 'Unknown file type';
	
						}
						
						if ($file_content !== NULL)
						{
							//TODO check if the version is the same !
							
							$ret = preg_match("/[$]config\['papaya_version'\] = '([0-9]+.[0-9]+)';/", $file_content, $matches);
							
							if ($ret && $matches[1] == APP_VERSION)
							{
								$config_path = APPPATH.'config/'; 
								$backup_file = $config_path.'vegetables.bak.php';
								$config_file = $config_path.'vegetables.php';
								
								if (file_exists($backup_file) === TRUE)
								{
									unlink($backup_file);
								}
								rename($config_file, $backup_file);
								
								$this->load->helper('file');
								if ( ! write_file($config_file, $file_content))
								{
									log_message('error', 'Error during restoring config file. [file='.$file_data['orig_name'].']');
									$data['error'] = 'Cannot restore the config file.';
									
									rename($backup_file, $config_file);
								}
								else
								{
									$data['success'] = 'Config file replaced by the new one. <br /><br />'
										.'<a class="ui button" href="'.base_url().'">'
										.'<i class="left arrow icon"></i>'
										.'Back to Papaya</a>';
								}
							}
							else
							{
								log_message('error', 'Error during restoring config file: version doesn\'t match [file='.$file_data['orig_name'].']');
								$data['error'] = 'The uploaded config file is not compatible with the current version of Papaya.';
							}
							
						}
						
					}
					
					$this->load->view('admin/header', $data);
					$this->load->view('admin/backup_config', $data);
					//$this->load->view('templates/footer', $data);	
					break;
			}
		}
	}
	
	/**
	 * Download a zip file that contains the entire Papaya folder from htdocs
	 */
	public function download_app()
	{
		
		$this->load->library('PhpOffice');
		$temp_path	= $this->phpoffice->getTemporaryFolder().'/';

		$papaya_path = realpath(APPPATH.'/../'); 
		$zip_name = 'Papaya.'.APP_VERSION.'.zip';

		$zip = new ZipArchive;

		if ($zip->open($temp_path.$zip_name,  ZipArchive::CREATE ) === TRUE) {
//			$zip->addFile($papaya_path);
			$this->_ZipArchive_add_dir($papaya_path, $zip, realpath(APPPATH.'/../../')); 
			
			$zip->close();

			// Load the download helper and send the file to your desktop
			$this->load->helper('download');
			force_download($temp_path.$zip_name, NULL);
		}
	}
	
	/**
	 * Method to recursivly add a folder into a zip archive
	 * @private
	 * @param string $path      path of the folder to add.
	 * @param object $zip       ZipArchive file to which add the folder.
	 * @param string $base_path root folder path to have relative path into the archive.
	 */
	private function _ZipArchive_add_dir($path, $zip, $base_path)
	{
		$dir = substr($path, strpos($path, $base_path)+strlen($base_path)+1);
		$zip->addEmptyDir($dir);
		
		$nodes = scandir($path);
		foreach ($nodes as $noden) 
		{
			if (in_array($noden, array('.', '..', '.svn')))
			{
				continue;
			}
			$node = realpath($path.'/'.$noden);
			if (is_dir($node)) 
			{ 
				$this->_ZipArchive_add_dir($node, $zip, $base_path); 
			} 
			else if (is_file($node))  
			{ 
				$zip->addFile($node, substr($node, strpos($node, $base_path)+strlen($base_path)+1)); 
			} 
		} 
	}
}
