# FancyPhoto for Codeigniter

透過Fancy Box實現-後台圖片上傳與刪除工具


### Step 1 - 新增SQL

````SQL
DROP TABLE IF EXISTS `n_fancy_photo`;

CREATE TABLE `n_fancy_photo` (
  `fp_id` int(11) UNSIGNED NOT NULL,
  `fp_controller` varchar(20) NOT NULL COMMENT 'controler名稱',
  `fp_controller_id` smallint(8) NOT NULL COMMENT '物件id',
  `fp_filename` varchar(128) NOT NULL COMMENT '圖片位置',
  `fp_data` longblob NOT NULL COMMENT '資料內容',
  `fp_mime` varchar(32) NOT NULL COMMENT 'MIME type',
  `fp_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '新增時間',
  `fp_del` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT '是否刪除'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='資料圖庫';

ALTER TABLE `n_fancy_photo`
  ADD PRIMARY KEY (`fp_id`),
  ADD UNIQUE KEY `fp_id` (`fp_id`);

ALTER TABLE `n_fancy_photo` ADD KEY `idx_controller_id_del` (`fp_controller`,`fp_controller_id`,`fp_del`);

ALTER TABLE `n_fancy_photo` ADD KEY `idx_name_del` (`fp_filename`,`fp_del`);

ALTER TABLE `n_fancy_photo` CHANGE `fp_id` `fp_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT;
````



### Setp 2 - 修改config/router.php
新增下面程式碼
````PHP
$route['fancy_photo/upload_image'] = 'fancy_photo/upload_image';
$route['fancy_photo/get_image'] = 'fancy_photo/get_image';
$route['fancy_photo/remove_image'] = 'fancy_photo/remove_image';
$route['fancyphoto/photos/(:any)'] = 'fancy_photo/read_photos/$1';
````


### Step 3 - 載入fancy_photo套件
````
composer requires mikecai\fancyphoto
````

### Step 4 - 新增Controller - Fancy_photo.php
````PHP
<?php
use mikecai\fancyphoto\Fancyphoto;

class Fancy_photo extends CI_Controller {

    var $Fancyphoto; //載入Fancyphoto 擴充物件
    public function __construct() {
        parent::__construct();

        if (!$this->Fancyphoto) {
            $this->Fancyphoto = new Fancyphoto(
                $this->db->hostname,
                $this->db->username,
                $this->db->password,
                $this->db->database,
                "fancyphoto/photos"     //圖片儲存位置
            );
        }

        //建立cache
        if ($this->config->item("cache_time") > 0) {
            $this->output->cache($this->config->item("cache_time"));
        }
    }

    /*
     * 讀取檔案
     */
    public function read_photos($filename = false){

        // 從Fancyphoto讀取資料庫的圖片
        $data["upload_file"] = $this->Fancyphoto->read_fancy_photo($filename);
        $data["upload_file"]->u_mime = $data["upload_file"]->fp_mime;
        $data["upload_file"]->u_data = $data["upload_file"]->fp_data;
        $this->load->view("fancy_photo", $data);
    }

    public function upload_image(){

        // 透過Fancyphoto儲存圖片
        $upload_path = "fancyphoto/photos/";        
        $this->Fancyphoto->save_image($upload_path);
    }

    public function get_image(){

        $images = $this->Fancyphoto->get_image();
        $html_tag = "";

        // 透過得到的圖片輸出圖片樣式
        foreach ($images as $key => $value) {
            $image_url = $this->config->item("server_base_url").$value->fp_filename;
            $html_tag .= "<div class='fancybox_div'>";
            $html_tag .="<button type='button' data-id='$value->fp_id' class='btn btn-danger fancybox_remove_btn'>X</button>";
            $html_tag .= "<a href='$image_url' class='fancybox_a' data-fancybox='images'>";
            $html_tag .="<img width='200px' src='$image_url' alt='' class='fancybox_img''>";
            $html_tag .= "</a>";
            $html_tag .= "</div>";
        }
        exit($html_tag);
    }

    public function remove_image(){
        $this->Fancyphoto->remove_image();
    }


}
````

### Step 5 - 新增View - fancy_photo.php
````PHP
<?php

if ($upload_file) {
    header("Content-Type:".$upload_file->u_mime);
    echo ($upload_file->u_data);
    exit;
}
````


### Step 6 - 在原先需要加入FancyPhoto的功能-Controller 上加入
````PHP
<?php
use mikecai\fancyphoto\Fancyphoto;

...略過....

$Fancyphoto = new Fancyphoto(
    $this->db->hostname,
    $this->db->username,
    $this->db->password,
    $this->db->database,
    "fancyphoto/photos"     //圖片儲存位置
);

$data["Fancyphoto"] = $Fancyphoto;
````


### Step 7 - 在原先需要加入FancyPhoto的功能-View 上加入
````HTML
<header>
    <!-- Add fancyphoto -->
    <?=$Fancyphoto->render_generator("css")?>
</header>


<body>

<!-- 要顯示圖片的地方 -->
<!-- Add fancyphoto -->
<?=$Fancyphoto->render_generator("body")?>


<!-- Script 區域-->
<!-- Add fancyPhoto  -->
<?=$Fancyphoto->render_generator("footer_js")?>


<!-- Jquery 區域-->
<?=$Fancyphoto->render_generator("ajax",
array(
    "contorller_name" =>"cherry_tree",
    "contorller_id" =>$cherry_tree_data->ct_id,
    "ajax_upload_image_api" => $this->config->item('server_base_url')."fancy_photo/upload_image",
    "ajax_get_image_api" => $this->config->item('server_base_url')."fancy_photo/get_image",
    "ajax_remove_image_api" => $this->config->item('server_base_url')."fancy_photo/remove_image",
))?>

</body>
````
