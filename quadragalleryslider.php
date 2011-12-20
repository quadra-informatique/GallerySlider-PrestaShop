<?php
class quadragalleryslider extends Module {
	
	protected $config = array();
	protected $images = array(); 
	protected $thumbs = array(); 
	protected $links = array(); 
	
	function __construct() {
		$this->name = 'quadragalleryslider';
		$this->tab = '';
		$this->version = '1.0';

		parent::__construct();

		$this->displayName = $this->l('Block Images Gallery Slider');
		$this->description = $this->l('Display thumbs of uploaded images in homepage, and show a slider of the images');
		
	}
	/**
	 * all images saved in database
	 * @return unknown_type
	 */
	function select_all(){
		
		$queries = array();
		$queries = Db::getInstance()->ExecuteS('SELECT * FROM ' . _DB_PREFIX_ . 'quadra_galleryslider');
		if(!isset($queries)){
	        return false;
		}
		return $queries;
	}
	/**
	 * create table galleryslider
	 * @return unknown_type
	 */
	function create_table(){
		$query = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'quadra_galleryslider (
				`id_image` int(10) NOT NULL AUTO_INCREMENT,
				`image` varchar(255) NOT NULL,
                `thumb` varchar(255) NOT NULL,
                `title` varchar(255) ,
                `link`  varchar(255) ,
				PRIMARY KEY  (`id_image`))
			ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3';

        if (!Db::getInstance()->Execute($query))
            return false;
        return true;    
	}
	/**
	 * delete table from database
	 * @return unknown_type
	 */
	function delete_table(){

		$query = 'DROP TABLE '. _DB_PREFIX_ .'quadra_galleryslider';
		
		if (!Db::getInstance()->Execute($query))
            return false;
        return true;
	}
	/**
	 * remove images from folder images,images_big,images_small
	 */
	function remove_images($image){
		
		$thumb_image = str_replace("big","small",$image);
		$tokens = explode('/',$image);
		$_image_original =_MODULE_DIR_."quadragalleryslider/images/".$tokens[5];
		if (!unlink($_SERVER['DOCUMENT_ROOT'].$image)){
			$errors[]=$this->l('Can\'t delete images').$image;
		}
		if (!unlink($_SERVER['DOCUMENT_ROOT'].$thumb_image)){
			$errors[]=$this->l('Can\'t delete images').$thumb_image;
		}
		if (!unlink($_SERVER['DOCUMENT_ROOT'].$_image_original)){
			$errors[]=$this->l('Can\'t delete images').$_image_original;
		}
	}
	/**
	 * delete a row in the database
	 * @return unknown_type
	 */
	function delete_row($image){
		
		$errors= array();
		$query = 'DELETE FROM `'. _DB_PREFIX_ . 'quadra_galleryslider` WHERE image = '.'"'.$image.'"';
				
		if (!Db::getInstance()->Execute($query))
			return false;

		return true;
	}
	/**
	 * insert an image in database
	 * @return unknown_type
	 */
	function insert_data($id){
		
		$query = "INSERT INTO " . _DB_PREFIX_ . "quadra_galleryslider (image,thumb,title,link)
		VALUES('".$_POST['img']."','".$_POST['thumb']."','".$_POST['title']."','".$_POST['link']."')";
		
		if (!Db::getInstance()->Execute($query))
			return false;
		return true;	
	}
	/**
	 * control the uploaded files
	 * @return unknown_type
	 */
	function controlUpload($_FILES){
		
 		$directory = '/images/';
        $file = basename($_FILES['img']['name']);
        $maxImageSize = 307200;
        $fileSize = filesize($_FILES['img']['tmp_name']);
        $extensions = array('.png', '.gif', '.jpg', '.jpeg');
        $extension = strrchr($_FILES['img']['name'], '.'); 
        $error="";
                        
        if(!in_array($extension, $extensions)) //check if correct extension
        {
        	$error = $this->l('Invalid file type').' upload files with extension png, gif, jpg, jpeg';
        }
        if($fileSize > $maxImageSize)//check if correct size 
        {
        	$error = 'File too big';
        }
        if($error == "") 
        {
	        $file = strtr($file,'ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ','AAAAAACEEEEIIIIOOOOOUUUUYaaaaaaceeeeiiiioooooouuuuyy');
	        //$file = preg_replace('/([^.a-z0-9]+)/i', '-', $file);
            $dest=dirname(__FILE__).$directory.$file ;                  
	        if(!move_uploaded_file($_FILES['img']['tmp_name'], $dest)) 
	        {
	        	$error= $this->l('An error occurred during the upload of the file');
	        }
        }
        return $dest;
	}
	/**
	 * resize the image in a big image and a small image for front display
	 * @param $_FILES
	 * @param $width
	 * @param $height
	 * @return unknown_type
	 */
	function resize_images($_FILES,$width,$height){
		
		$source= $this->controlUpload($_FILES);
		$errors = array();
		$dest_dir_big='/images_big/';
		$dest_dir_small='/images_small/';
		$file = basename($_FILES['img']['name']);
		$thumb_size= 20/100;
		if($source!= "" || $width!= ""|| $height != "" ){
			//big_image
			if (!imageResize($source,dirname(__FILE__).$dest_dir_big.$file,$width,$height)) {
				$errors[]=$this->l('An error occurred while generating the resize of big').' "'.$_FILES['img']['name'].'"';
			}
			//small_image
			if (!imageResize($source,dirname(__FILE__).$dest_dir_small.$file,$thumb_size*$width,$thumb_size*$height)) {
				$errors[]=$this->l('An error occurred while generating the resize of small').' "'.$_FILES['img']['name'].'"';
			}
		}
		if(isset($errors)){
			return $errors;
		}
	}
	/**
	 * resize the image in a big image and a small image for front display for image_generate
	 * @param $_FILES
	 * @param $width
	 * @param $height
	 * @return unknown_type
	 */
	function resize_generated_images($_image,$width,$height){

		$errors = array();
		$dest_dir_big='/images_big/';
		$dest_dir_small='/images_small/';
		$tokens = explode('/',$_image);
		$file = $tokens[9];
		$thumb_size= 20/100;
		if($file!= "" || $width!= ""|| $height != "" ){
			//big_image
			if (!imageResize($_image,dirname(__FILE__).$dest_dir_big.$file,$width,$height)) {
				$errors[]=$this->l('An error occurred while generating the resize of big').' "'.$file.'"';
			}
			//small_image
			if (!imageResize($_image,dirname(__FILE__).$dest_dir_small.$file,$thumb_size*$width,$thumb_size*$height)) {
				$errors[]=$this->l('An error occurred while generating the resize of small').' "'.$file.'"';
			}
		}
		if(isset($errors)){
			return $errors;
		}
	}
	
	function install() {
		if (!parent::install())
			return false;
			
		Configuration::updateValue('PS_QUADRA_SLIDER_HEIGHT', 200);
        Configuration::updateValue('PS_QUADRA_SLIDER_WIDTH', 500);
        Configuration::updateValue('PS_QUADRA_V_DISPLAY', 0);
        
		//creation de la table
		$this->create_table();
            			
		return $this->registerHook('home');
	}

	public function uninstall() {
		
		$sliders = $this->select_all();
		if(isset($sliders)){
			foreach($sliders as $slider){
				$this->delete_row($slider['image']);
				$this->remove_images($slider['image']);
			}
		}
		$this->delete_table();
		return parent::uninstall();
	}

	function getContent() {
		$this->_html = '<h2>'.$this->displayName.'</h2><p>'.$this->description.'</p>';

		if(Tools::isSubmit('submitConf'.$this->name))
			$this->_postProcess();
			
		if(Tools::isSubmit('gen_images')){
			Configuration::updateValue('PS_QUADRA_SLIDER_HEIGHT', $_POST['height']);
        	Configuration::updateValue('PS_QUADRA_SLIDER_WIDTH', $_POST['width']);
			$this->generate_images();
		}	
		$this->_displayForm();
		return $this->_html;
	}
	/**
	 * action of the button resize images
	 * @return unknown_type
	 */
	function generate_images(){
		
		foreach($this->select_all() as $row){
			$_POST['title'] = $row['title'];
			$_POST['link'] = $row['link'];
			$image_base = $row['image'];
		
			$tokens = explode('/',$image_base);
			$_image = dirname(__FILE__)."/images/".$tokens[5];
			$this->resize_generated_images($_image,$_POST['width'],$_POST['height']);
			$_POST['img']=_MODULE_DIR_."quadragalleryslider/images_big/".$tokens[5];
			$_POST['thumb']=_MODULE_DIR_."quadragalleryslider/images_small/".$tokens[5];
			//enlever l'ancienne ligne 
			$this->delete_row($image_base);
			//insertion des données dans la base
			foreach(array_keys($_POST) as $key){
				$this->insert_data($key);
				break;
			}
		}
	}
	
	protected function _postProcess() {
		$errors = array();
		// Vérification des valeurs
		if(isset($_POST['v_display'])){
			Configuration::updateValue('PS_QUADRA_V_DISPLAY', 1);
		}else{
			Configuration::updateValue('PS_QUADRA_V_DISPLAY', 0);
		}
		
		unset($_POST['submitConf'.$this->name]);
		if($_POST['title']!= "" || $_POST['link'] != ""){
			$this->resize_images($_FILES,$_POST['width'],$_POST['height']);//_MODULE_DIR_
			foreach(array_keys($_FILES) as $key) {
				$_POST['img']=_MODULE_DIR_."quadragalleryslider/images_big/".basename($_FILES['img']['name']);
				$_POST['thumb']=_MODULE_DIR_."quadragalleryslider/images_small/".basename($_FILES['img']['name']);
			}
			//insertion des données dans la base
			foreach(array_keys($_POST) as $key){
					$this->insert_data($key);
				break;
			}
		}
		//suppression dans des données
		if(isset($_POST['suppr']) && $_POST['suppr']) {
			foreach (array_keys($_POST['suppr']) as $id) {
				$this->delete_row($_POST['img'][$id]);
				$this->remove_images($_POST['img'][$id]);
			}
			unset($_POST['suppr']);
		}
	}

	protected function _displayForm() {
		// pour toujours avoir un champ pour ajouter
		$isThumb = is_file($_SERVER['DOCUMENT_ROOT'].$this->config['thumb']);
		$this->_html .= '
		<form method="post" action="'.$_SERVER['REQUEST_URI'].'" enctype="multipart/form-data">
			<fieldset>';
			$this->_html .= '
				<div style="float:left;">
					<label>'.$this->l('Image height').'</label>
					<div class="margin-form">
						<input type="text" name="height" size="64" value="' . Tools::getValue('height', Configuration::get('PS_QUADRA_SLIDER_HEIGHT')) . '" />
					</div>
					<label>'.$this->l('Image width').'</label>
					<div class="margin-form">
						<input type="text" name="width" size="64" value="'. Tools::getValue('width', Configuration::get('PS_QUADRA_SLIDER_WIDTH')) .'" />
					</div>
				</div>
				<div style="float:right;">
					<input type="submit" name="gen_images" value="'.$this->l('Generate images').'" class="button" />
				</div>
				<div style="float:right;">
				<label>'.$this->l('Vertical display').'</label>
					<div class="margin-form">';
			if(Configuration::get('PS_QUADRA_V_DISPLAY') == 1)
				$this->_html .= '<input type="checkbox" name="v_display" value="" checked=checked/>';
			else
				$this->_html .= '<input type="checkbox" name="v_display" value="" />';
					
			$this->_html .= '</div>
				</div>	
				<hr class="clear"/>';
			/**************/
				
				foreach ($this->select_all() as $i => $row) {
					$isFile2 = is_file($_SERVER['DOCUMENT_ROOT'].$row['image']);
					$isThumb2 = is_file($_SERVER['DOCUMENT_ROOT'].$row['thumb']);
					
					$this->_html .= '
					<div style="float:left;">
						'.($isThumb2 ? '<img src="'.$row['thumb'].'"name="thumb['.$i.']" alt="'.$row['title'].'" title="'.$row['title'].'"/>' : $this->l('Add') ).'
					</div><div style="float:left;">
						<label>'.$this->l('Image').'</label>
						<div class="margin-form">
							<input type="file" name="img'.$i.'" />
							'.($isFile2 ? '<input type="hidden" name="img['.$i.']" value="'.$row['image'].'"/>' : '' ).'
						</div>
						<label>'.$this->l('Image Title').'</label>
						<div class="margin-form">
							<input type="text" name="title['.$i.']" size="64" value="'.($row['title'] ? stripslashes(htmlspecialchars($row['title'])) : '').'" /></div>
						<label>'.$this->l('Link').'</label>
						<div class="margin-form">
							<input type="text" name="link['.$i.']" size="64" value="'.($row['link'] ? stripslashes(htmlspecialchars($row['link'])) : '').'" />
						</div>
						<label>'.$this->l('Delete').'</label>
						<div class="margin-form">
							<input type="checkbox" name="suppr['.$i.']" />
						</div>
					</div>
					<hr class="clear"/>';
				}
			/***************/
			
			$this->_html .= '
				<div style="float:left;">
					'.($isThumb ? '<img src="'.$this->config['thumb'].'" alt="'.$this->config['title'].'" title="'.$this->config['title'].'"/>' : $this->l('Add') ).'
				</div><div style="float:left;">
					<label>'.$this->l('Image').'</label>
					<div class="margin-form">
						<input type="file" name="img" />
					</div>
					<label>'.$this->l('Image Title').'</label>
					<div class="margin-form">
						<input type="text" name="title" size="64" value="'.($this->config['title'] ? stripslashes(($this->config['title'])) : '').'" /></div>
					<label>'.$this->l('Link').'</label>
					<div class="margin-form">
						<input type="text" name="link" size="64" value="'.($this->config['link'] ? stripslashes(($this->config['link'])) : '').'" />
					</div>
					<label>'.$this->l('Delete').'</label>
					<div class="margin-form">
						<input type="checkbox" name="suppr" />
					</div>
				</div>
				<hr class="clear"/>';
			
			$this->_html .= '
			<div class="margin-form clear"><input type="submit" name="submitConf'.$this->name.'" value="'.$this->l('Save').'" class="button" /></div>
			</fieldset>
		</form>';
	}

	
	function hookHome($params) {
		global $smarty;
		
		foreach($this->select_all() as $query){
			$images[]= $query['image'];
			$thumbs[]= $query['thumb'];
			$titles[]= $query['title'];
			$links[]= $query['link'];
		}
		
		$smarty->assign('imgs',$images);
		$smarty->assign('thumbs',$thumbs);
		$smarty->assign('titles',$titles);
		$smarty->assign('links',$links);
        $smarty->assign('jqZoomEnabled',Configuration::get('PS_DISPLAY_JQZOOM'));
        $smarty->assign('v_display',Configuration::get('PS_QUADRA_V_DISPLAY'));
        $smarty->assign('image_height',(Configuration::get('PS_QUADRA_SLIDER_HEIGHT')));
        $smarty->assign('v_height',(Configuration::get('PS_QUADRA_SLIDER_HEIGHT')*20/100)+20);
        $smarty->assign('box_height',((Configuration::get('PS_QUADRA_SLIDER_HEIGHT')*20/100)+20)*count($thumbs));
		return $this->display(__FILE__, 'quadragalleryslider.tpl');
	}
	/*function hookTop($params){
		if(!class_exists('IndexControllerCore',false))
			return;
		global $smarty;
		$smarty->assign('imgs',$this->config['img']);
		$smarty->assign('thumbs',$this->config['thumb']);
		$smarty->assign('titles',$this->config['title']);
		$smarty->assign('links',$this->config['link']);
        $smarty->assign('jqZoomEnabled',Configuration::get('PS_DISPLAY_JQZOOM'));
                  
		return $this->display(__FILE__, 'quadragalleryslider.tpl');
	}*/
	function hookFooter($params) {
		return $this->hookLeftColumn($params);
	}
}
