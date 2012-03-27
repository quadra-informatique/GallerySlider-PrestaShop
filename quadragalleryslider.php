<?php
/**
 * ---------------------------------------------------------------------------------
 * 
 * 1997-2012 Quadra Informatique
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to ecommerce@quadra-informatique.fr so we can send you a copy immediately.
 *
 * @author Quadra Informatique <ecommerce@quadra-informatique.fr>
 * @copyright 1997-2012 Quadra Informatique
 * @version Release: $Revision: 1.0 $
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * ---------------------------------------------------------------------------------
*/

class QuadraGallerySlider extends Module {
	
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
	
	function install() {
		
		$tab = new Tab();
        $tab->id_parent = 1;
        $tab->name = array(Language::getIdByIso('fr') => 'Carrousel d\'images');
        $tab->class_name = 'AdminGallerySlider';
        $tab->module = 'quadragalleryslider';
        $tab->add();
        
		if (!parent::install())
			return false;
			
		Configuration::updateValue('PS_QUADRA_SLIDER_HEIGHT', 200);
        Configuration::updateValue('PS_QUADRA_SLIDER_WIDTH', 500);
        Configuration::updateValue('PS_QUADRA_V_DISPLAY', 0);
		//creation de la table
		$this->create_table();
            			
		return $this->registerHook('home');
	}

	function uninstall() {
		
		$this->uninstallModuleTab('AdminGallerySlider');
		$sliders = $this->select_all();
		if(isset($sliders)){
			foreach($sliders as $slider){
				$this->delete_row($slider['id_quadra_galleryslider']);
				$this->remove_images($slider['image']);
			}
		}
		$this->delete_table();
		Configuration::deleteByName('PS_QUADRA_SLIDER_HEIGHT');
        Configuration::deleteByName('PS_QUADRA_SLIDER_WIDTH');
        Configuration::deleteByName('PS_QUADRA_V_DISPLAY');
		return parent::uninstall();
	}
	/**
	 * all images saved in database
	 * @return unknown_type
	 */
	function select_all(){
		
		$queries = array();
		$queries = Db::getInstance()->ExecuteS('SELECT * FROM ' . _DB_PREFIX_ . 'quadra_galleryslider');
		if(!empty($queries)){
	        return $queries;
		}
		return NULL;
	}
	/**
	 * informations as per id
	 * @return unknown_type
	 */
	function retrieve_data($id){
		
		$queries = array();
		$queries = Db::getInstance()->ExecuteS('SELECT * FROM ' . _DB_PREFIX_ . 'quadra_galleryslider WHERE id_quadra_galleryslider ='.$id);
		if(!empty($queries)){
	        return $queries;
		}
		return NULL;
	}
	/**
	 * create table galleryslider
	 * @return unknown_type
	 */
	function create_table(){
		$query = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'quadra_galleryslider (
				`id_quadra_galleryslider` int(10) NOT NULL AUTO_INCREMENT,
				`image` varchar(255) NOT NULL,
                `thumb` varchar(255) NOT NULL,
                `title` varchar(255) ,
                `link`  varchar(255) ,
				PRIMARY KEY  (`id_quadra_galleryslider`))
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
		$_image_original = str_replace("images_big","images",$image);
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
	function delete_row($id){
		
		$errors= array();
		$query = 'DELETE FROM `'. _DB_PREFIX_ . 'quadra_galleryslider` WHERE id_quadra_galleryslider = '.'"'.$id.'"';
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
	 * resize the image in a big image and a small image for front display for image_generate
	 * @param $_FILES
	 * @param $width
	 * @param $height
	 * @return unknown_type
	 */
	function resize_generated_images($_image,$width,$height){

		$errors = array();
		$dest_dir_big = str_replace('/images/','/images_big/',$_image);
		$dest_dir_small= str_replace('/images/','/images_small/',$_image);
		$thumb_size= 20/100;
		if($width!= ""|| $height != "" ){
			//big_image
			if (!imageResize($_image,$dest_dir_big,$width,$height)) {
				$errors[]=$this->l('An error occurred while generating the resize of big').' "'.$file.'"';
			}
			//small_image
			if (!imageResize($_image,$dest_dir_small,$thumb_size*$width,$thumb_size*$height)) {
				$errors[]=$this->l('An error occurred while generating the resize of small').' "'.$file.'"';
			}
		}
		if(isset($errors)){
			return $errors;
		}
	}
	
	private function uninstallModuleTab($tabClass)
    {
        $idTab = Tab::getIdFromClassName($tabClass);
        if ($idTab != 0)
        {
        	$tab = new Tab($idTab);
            $tab->delete();
            return true;
        }
        return false;
    }

	function getContent() {
		
		$this->_html = '<h2>'.$this->displayName.'</h2><p>'.$this->description.'</p>';
		if(Tools::isSubmit('submitConf'.$this->name))
			$this->_postProcess();
		$this->_displayForm();
		return $this->_html;
	}
	/**
	 * regenerate images
	 * @return unknown_type
	 */
	function generate_images(){
		
		$datas = $this->select_all();
		if(isset($datas)){
			foreach($datas as $row){
				$_POST['title'] = $row['title'];
				$_POST['link'] = $row['link'];
				$image_base = $row['image'];
				$_image = _PS_ROOT_DIR_.str_replace("images_big","images",$image_base);

				$this->resize_generated_images($_image,$_POST['width'],$_POST['height']);
				$_image = str_replace(_PS_ROOT_DIR_,"",$_image);
				$_POST['img']= str_replace("images","images_big",$_image);
				$_POST['thumb']= str_replace("images","images_small",$_image);
				//enlever l'ancienne ligne 
				$this->delete_row($row['id_quadra_galleryslider']);
				//insertion des données dans la base
				foreach(array_keys($_POST) as $key){
					$this->insert_data($key);
					break;
				}
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
		Configuration::updateValue('PS_QUADRA_SLIDER_HEIGHT', $_POST['height']);
        Configuration::updateValue('PS_QUADRA_SLIDER_WIDTH', $_POST['width']);
		$this->generate_images();
	}

	protected function _displayForm() {
		
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
				<div style="float:left;">
				<label>'.$this->l('Vertical display').'</label>
				<div class="margin-form">';
			if(Configuration::get('PS_QUADRA_V_DISPLAY') == 1)
				$this->_html .= '<input type="checkbox" name="v_display" value="" checked=checked/>';
			else
				$this->_html .= '<input type="checkbox" name="v_display" value="" />';
			$this->_html .= '
			<div class="margin-form clear"><input type="submit" name="submitConf'.$this->name.'" value="'.$this->l('Save').'" class="button" /></div>
			</fieldset>
		</form>';
	}
	
	function hookHome($params) {
		
		global $smarty;
		$datas = $this->select_all();
		if(isset($datas) || $datas != "NULL"){
			foreach($datas as $query){
				$images[]= $query['image'];
				$thumbs[]= $query['thumb'];
				$titles[]= $query['title'];
				$links[]= $query['link'];
			}
		}	
		$smarty->assign('imgs',$images);
		$smarty->assign('thumbs',$thumbs);
		$smarty->assign('titles',$titles);
		$smarty->assign('links',$links);	
		
		$v_height = (Configuration::get('PS_QUADRA_SLIDER_HEIGHT')*20/100) + 20;
		$box_height = $v_height*count($thumbs);
		if($box_height < Configuration::get('PS_QUADRA_SLIDER_HEIGHT')){
			$box_height = Configuration::get('PS_QUADRA_SLIDER_HEIGHT') + 40;
		}
        $smarty->assign('jqZoomEnabled',Configuration::get('PS_DISPLAY_JQZOOM'));
        $smarty->assign('v_display',Configuration::get('PS_QUADRA_V_DISPLAY'));
        $smarty->assign('image_height',(Configuration::get('PS_QUADRA_SLIDER_HEIGHT')));
        $smarty->assign('v_height',$v_height);
        $smarty->assign('box_height',$box_height);
		
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
