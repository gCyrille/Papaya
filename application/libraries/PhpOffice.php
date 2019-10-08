<?php
defined('BASEPATH') OR exit('No direct script access allowed');


use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\IWriter;
use PhpOffice\PhpSpreadsheet\Writer\Pdf;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class PhpOffice {

	protected $CI;
	
	public $PageSetup = PageSetup::class;
	public $Spreadsheet = Spreadsheet::class;

	// We'll use a constructor, as you can't directly call a function
	// from a property definition.
	public function __construct()
	{
		// Assign the CodeIgniter super-object
		$this->CI =& get_instance();
		
		require_once(APPPATH . '/libraries/third_party/dompdf/autoload.inc.php');
		
		spl_autoload_register(function ($name) {
			//var_dump($name);
			require_once APPPATH . '\libraries\third_party\\' . $name . '.php';

		});
	}
	
	/**
	 * Load a spreadsheet from a template file
	 *
	 * @param string $tpl_fullname
	 * @param string $reader_type
	 *
	 * @return Spreadsheet
	 */
	public function load_file($tpl_fullname, $reader_type = NULL)
	{
		if ($reader_type == NULL)
		{
			$reader_type = IOFactory::identify($tpl_fullname);	
		}
		$reader = IOFactory::createReader($reader_type);
		$reader->setLoadAllSheets();
		$spreadsheet = $reader->load($tpl_fullname);
		
		return $spreadsheet;
	}
	
	public function load_file_as_array($fullname, $reader_type = NULL)
	{
		if ($reader_type == NULL)
		{
			$reader_type = IOFactory::identify($fullname);	
		}
		$reader = IOFactory::createReader($reader_type);
		
		$spreadsheet = $reader->load($fullname);
		
		$sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
		return $sheetData;
	}
	
	/**
	 * Create a new Spreadsheet.
	 * @return object SpreadSheet
	 */
	public function new_spreadsheet()
	{
		$spreadsheet = new Spreadsheet();
		$spreadsheet->setActiveSheetIndex(0);
		return $spreadsheet;
	}
	
	/**
     * Write pdf document.
     *
     * @param Spreadsheet $spreadsheet
     * @param string $filename
     */
	public function write_pdf(Spreadsheet $spreadsheet, $filename=NULL)
	{
		$className = \PhpOffice\PhpSpreadsheet\Writer\Pdf\Dompdf::class;
		IOFactory::registerWriter('Pdf', $className);
		
		$spreadsheet->getActiveSheet()->setShowGridLines(false);
		$this->write($spreadsheet, $filename, ['Pdf']);
	}
	
	/**
	 * Copied from PhpOffice\PhpSpreadsheet\Helper\Sample
     * Write documents.
     *
     * @param Spreadsheet $spreadsheet
     * @param string $filename
     * @param string[] $writers
     */
    public function write(Spreadsheet $spreadsheet, $filename=NULL, array $writers = ['Xlsx', 'Xls'])
    {
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $spreadsheet->setActiveSheetIndex(0);

        // Write documents
        foreach ($writers as $writerType) {
			if ($filename == NULL)
			{
				$path = 'php://output';
			}
			else
			{
				$path = $this->getFilename($filename, mb_strtolower($writerType));
			}
            $writer = IOFactory::createWriter($spreadsheet, $writerType);
            if ($writer instanceof Pdf) {
                // PDF writer needs temporary directory
                $tempDir = $this->getTemporaryFolder();
                $writer->setTempDir($tempDir);
            }
            $writer->save($path);
        }
    }
	
    /**
	 * Copied from PhpOffice\PhpSpreadsheet\Helper\Sample
     * Returns the temporary directory and make sure it exists.
     *
     * @return string
     */
    public function getTemporaryFolder()
    {
        $tempFolder = sys_get_temp_dir() . '/papaya';
        if (!is_dir($tempFolder)) {
            if (!mkdir($tempFolder) && !is_dir($tempFolder)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $tempFolder));
            }
        }

        return $tempFolder;
    }

    /**
	 * Copied from PhpOffice\PhpSpreadsheet\Helper\Sample
     * Returns the filename that should be used for sample output.
     *
     * @param string $filename
     * @param string $extension
     *
     * @return string
     */
    public function getFilename($filename, $extension = 'xlsx')
    {
        $originalExtension = pathinfo($filename, PATHINFO_EXTENSION);

        return $this->getTemporaryFolder() . '/' . str_replace('.' . $originalExtension, '.' . $extension, basename($filename));
    }
	
	/**
	 * Download the xlsx file.
	 * Stop the script execution, call this method at the end.
	 *
     * @param Spreadsheet $spreadsheet
     * @param string $filename
	 */
	public function download(Spreadsheet $spreadsheet, $filename)
	{
		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		//$spreadsheet->setActiveSheetIndex(0);

		// Redirect output to a client’s web browser (Xlsx)
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="'.$filename.'"');
		header('Cache-Control: max-age=0');
		// If you're serving to IE 9, then the following may be needed
		header('Cache-Control: max-age=1');

		// If you're serving to IE over SSL, then the following may be needed
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
		header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header('Pragma: public'); // HTTP/1.0

		$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
		$writer->save('php://output');
		exit;
	}
	
	public function download_pdf(Spreadsheet $spreadsheet, $filename)
	{
		// Redirect output to a client’s web browser (PDF)
		header('Content-Type: application/pdf');
		header('Content-Disposition: attachment;filename="'.$filename.'.pdf"');
		header('Cache-Control: max-age=0');
		
		$this->write_pdf($spreadsheet);
		exit;
	}
	
	/**
	 *	Generate dompdf object from html and css
	 * 
	 * @param string
	 * @param string
	 *
	 * @return object Dompdf\Dompdf
	 */
	public function html_to_pdf($html, $css=NULL)
	{
		// instantiate and use the dompdf class
		$dompdf = new Dompdf\Dompdf();
		
		$dompdf->getOptions()->setTempDir($this->getTemporaryFolder());
		$dompdf->loadHtml($html);

		if ($css != NULL)
		{
			$stylesheet = new Dompdf\Css\Stylesheet($dompdf);
			$stylesheet->load_css($css);
			$dompdf->setCss($stylesheet);
		}
		
		return $dompdf;
	}
}
