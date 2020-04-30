<?php
namespace mikecai\fancyphoto;

use Exception;
use  Nette\Database\Connection;
use  Nette\Database\Context;
use  Nette\Database\Structure;
use  Nette\Caching\Storages\FileStorage;
use  Nette\Http\Request;
use  Nette\Http\UrlScript;
use  Nette\Utils\Image;


/**
 * 
 */
class Fancyphoto
{
	
	public $db;
	public $db_context;
	public $db_structure;
	public $storage;
	public $fancyphoto_table;

	function __construct($yourDbHost, $yourDbUser,$yourDbPassword,$yourDbName,$upload_folder="")
	{
		if (!$this->db) {
			$dsn = "mysql:host=$yourDbHost;dbname=$yourDbName";
			$this->db =new Connection($dsn, $yourDbUser, $yourDbPassword);
			$this->storage = new FileStorage($this->setting_storage_path($upload_folder));
			$this->db_structure =new Structure($this->db,$this->storage);
			$this->db_context =new Context($this->db,$this->db_structure);
			$this->fancyphoto_table = $this->db_context->table('n_fancy_photo');
		}
	}

	// 設定檔案儲存資料夾
	public function setting_storage_path($upload_folder){
		$storage_path = FCPATH . $upload_folder;
		if (DIRECTORY_SEPARATOR == '\\') {
		    $storage_path = str_replace('/', '\\', $storage_path);
		}
		if (!file_exists($storage_path)) {
		    exec("mkdir " . $storage_path);
		}
		return $storage_path;
	}

	public function render_generator($render_key,$args=array()){
		switch ($render_key) {
			case 'css':
				render_css();
				break;
			case 'footer_js':
				render_footer_js();
				break;
			case 'body':
				renader_body();
				break;
			case 'ajax':
				if (isset($args["contorller_name"]) && isset($args["contorller_id"])) {

				    if (isset($args['ajax_upload_image_api']) && isset($args['ajax_get_image_api']) && isset($args['ajax_remove_image_api'])){
                        render_ajax($args["contorller_name"],$args["contorller_id"],$args['ajax_upload_image_api'],$args['ajax_get_image_api'],$args['ajax_remove_image_api']);
                    }else{
                        render_ajax($args["contorller_name"],$args["contorller_id"]);
                    }

				}else{
					$error = '$arg need contorller_name & contorller_id';
					throw new Exception($error);
				}
				break;
		}
	}

	public function read_fancy_photo($path){
		$photo = new \stdClass;
		$rows = $this->fancyphoto_table->where("fp_filename","fancyphoto/photos/".$path);

		if ($rows) {
			foreach ($rows as $key => $value) {
				$photo->fp_id = $value->fp_id;
				$photo->fp_mime = $value->fp_mime;
				$photo->fp_data = $value->fp_data;
			}
		}
		
		return $photo;
	}

	public function save_image($upload_path){
		$url_Script = new UrlScript($_SERVER["REQUEST_URI"]);
		$httpRequest = new Request($url_Script,$_POST);

		//傳入值接收
		$input_array = array(
		    "controller_name" => $httpRequest->getPost("controller_name"),
		    "controller_id" => $httpRequest->getPost("controller_id")
		);

		$image = Image::fromFile($_FILES["file"]["tmp_name"]);
		$new_filename = substr(md5(uniqid(rand())), 3, 12);
		$new_filename = $upload_path.$new_filename.".png";
		$image->save($new_filename,8,Image::PNG);


		//上傳DB

		$this->fancyphoto_table->insert(
			[
				'fp_controller' => $input_array["controller_name"],
                'fp_controller_id' => $input_array["controller_id"],
                'fp_filename' => $new_filename,
                'fp_data' => (string)$image,
                'fp_mime' => "image/png",
                'fp_timestamp' => date("Y-m-d H:i:s")
			]
		);
	}

	public function get_image(){
		$photo = new \stdClass;
		$photos = array();
		$url_Script = new UrlScript($_SERVER["REQUEST_URI"]);
		$httpRequest = new Request($url_Script,$_POST);

		//傳入值接收
		$input_array = array(
		    "controller_name" => $httpRequest->getPost("controller_name"),
		    "controller_id" => $httpRequest->getPost("controller_id")
		);

		$rows = $this->fancyphoto_table->where("fp_controller", $input_array["controller_name"])
									   ->where("fp_controller_id", $input_array["controller_id"])
									   ->where("fp_del", "N");

		if ($rows) {
			foreach ($rows as $key => $value) {
				$photo = new \stdClass;
				$photo->fp_id = $value->fp_id;
				$photo->fp_filename = $value->fp_filename;
				$photos[] =$photo;
			}
		}

		return $photos;

	}

	public function remove_image(){
		$url_Script = new UrlScript($_SERVER["REQUEST_URI"]);
		$httpRequest = new Request($url_Script,$_POST);
		//傳入值接收
		$input_array = array(
		    "id" => $httpRequest->getPost("id"),
		);

		$this->fancyphoto_table->where("fp_id", $input_array["id"])
							   ->update(["fp_del" => "Y"]);
	}

}