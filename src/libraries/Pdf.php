<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * CodeIgniter PDF Library
 *
 * Generate PDF's in your CodeIgniter applications.
 *
 * @package         CodeIgniter
 * @subpackage      Libraries
 * @category        Libraries
 * @author          Chris Harvey
 * @license         MIT License
 * @link            https://github.com/chrisnharvey/CodeIgniter-PDF-Generator-Library
 */

require_once(dirname(__FILE__) . '/dompdf/vendor/autoload.php');

class Pdf
{
	protected $CI;
	public function __construct() {
		$this->CI = & get_instance();
	}

    public function load_view($view, $data = array())
    {
		$dompdf = new Dompdf\Dompdf();
        $html = $this->CI->load->view($view, $data, TRUE);
		$dompdf->loadHtml($html, 'UTF-8');
		$dompdf->setPaper('A4', "portrait");
		$dompdf->render();
		$dompdf->stream("", array("Attachment" => false)); // show in browser
    }

	public function download_view($view, $data = array(), $filename = null)
	{
		$dompdf = new Dompdf\Dompdf();
		$html = $this->CI->load->view($view, $data, TRUE);
		$dompdf->loadHtml($html, 'UTF-8');
		$dompdf->setPaper('A4', "portrait");
		$dompdf->render();
		$dompdf->stream( $filename == null ? "invoice-0.pdf" : $filename); // download
	}
}
