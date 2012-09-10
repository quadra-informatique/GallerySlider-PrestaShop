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
		$this->module_key = "f6f656f46178481af36fde4d8e95821e";

		parent::__construct();

		$this->displayName = $this->l('Block Images Gallery Slider');
		$this->description = $this->l('Display thumbs of uploaded images in homepage, and show a slider of the images');
	}
	
	function install() {
		
		$tab = new Tab();
        $tab->id_parent = 1;
        $tab->name = array(Language::getIdByIso('fr') => 'Carrousel d\'images',Language::getIdByIso('en') => 'Images carrousel');
        $tab->class_name = 'AdminGallerySlider';
        $tab->module = 'quadragalleryslider';
        $tab->add();
        
		if (!parent::install())
			return false;
			
		Configuration::updateValue('PS_QUADRA_SLIDER_HEIGHT', 200);
        Configuration::updateValue('PS_QUADRA_SLIDER_WIDTH', 500);
        Configuration::updateValue('PS_QUADRA_V_DISPLAY', 0);
		//creation de la table
		$this->createTable();
            			
		return $this->registerHook('home');
	}

	function uninstall() {
		
		$this->uninstallModuleTab('AdminGallerySlider');
		$sliders = $this->selectAll();
		if(isset($sliders)){
			foreach($sliders as $slider){
				$this->deleteRow($slider['id_quadragalleryslider']);
				$this->removeImages($slider['image']);
			}
		}
		$this->deleteTable();
		Configuration::deleteByName('PS_QUADRA_SLIDER_HEIGHT');
        Configuration::deleteByName('PS_QUADRA_SLIDER_WIDTH');
        Configuration::deleteByName('PS_QUADRA_V_DISPLAY');
		return parent::uninstall();
	}
	/**
	 * select all informations
	 * @return array
	 */
	function selectAll(){
		
		$queries = array();
		$queries = Db::getInstance()->ExecuteS(
						'SELECT * FROM ' . _DB_PREFIX_ . 'quadragalleryslider as a
						LEFT JOIN `'._DB_PREFIX_.'quadragalleryslider_lang`as i ON (i.`id_quadragalleryslider` = a.`id_quadragalleryslider`)'
						
		);
		if(!empty($queries)){
	        return $queries;
		}
		return NULL;
	}
	/**
	 * Select all titles and links
	 * @param $id
	 * @return array
	 */
	function selectForAllLanguages($id){
		
		$queries = array();
		$queries = Db::getInstance()->ExecuteS(
					'SELECT * FROM `'._DB_PREFIX_.'quadragalleryslider_lang` WHERE id_quadragalleryslider ='.$id );
		if(!empty($queries)){
	        return $queries;
		}
		return NULL;
	}
	/**
	 * Select all items 
	 * @param $id
	 * @return array
	 */
	function selectAllImages(){
		
		$queries = array();
		$queries = Db::getInstance()->ExecuteS(
					'SELECT * FROM ' . _DB_PREFIX_ . 'quadragalleryslider');
		if(!empty($queries)){
	        return $queries;
		}
		return NULL;
	}
	/**
	 * retrieve image and thum for a given id
	 * @param $id
	 * @return array
	 */
	function retrieveData($id){
		
		$queries = array();
		$queries = Db::getInstance()->ExecuteS('
					SELECT * FROM ' . _DB_PREFIX_ . 'quadragalleryslider 
					WHERE id_quadragalleryslider ='.$id);
		if(!empty($queries)){
	        return $queries;
		}
		return NULL;
	}
	/**
	 * create tables 
	 * @return unknown_type
	 */
	function createTable(){
		$query = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'quadragalleryslider (
				`id_quadragalleryslider` int(10) NOT NULL AUTO_INCREMENT,
				`image` varchar(255) NOT NULL,
                `thumb` varchar(255) NOT NULL,
				PRIMARY KEY  (`id_quadragalleryslider`))
			ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3';
		
		$query2 = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'quadragalleryslider_lang (
				`id_quadragalleryslider` int(10) NOT NULL,
				`id_lang` int(10) NOT NULL,
                `title` varchar(255) ,
                `link`  varchar(255) ,
                PRIMARY KEY (`id_quadragalleryslider`,`id_lang`),
  				KEY `id_lang` (`id_lang`))
			ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3';

        if (!Db::getInstance()->Execute($query) ||!Db::getInstance()->Execute($query2))
            return false;
        return true;    
	}
	/**
	 * delete tables from database
	 * @return unknown_type
	 */
	function deleteTable(){

		$query = 'DROP TABLE '. _DB_PREFIX_ .'quadragalleryslider';
		
		$query2 = 'DROP TABLE '. _DB_PREFIX_ .'quadragalleryslider_lang';
		
		if (!Db::getInstance()->Execute($query)||!Db::getInstance()->Execute($query2))
            return false;
        return true;
	}
	/**
	 * remove images from folder images,images_big,images_small
	 * @param $image
	 * @return unknown_type
	 */
	function removeImages($image){

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
	 * delete a row for the given id
	 * @param $id
	 * @return unknown_type
	 */
	function deleteRow($id){
		
		$languages = Language::getLanguages(false);
		
		$query = 'DELETE FROM `'. _DB_PREFIX_ . 'quadragalleryslider` WHERE id_quadragalleryslider = '.'"'.$id.'"';
		
		foreach($languages as $language){
			$query2 = 'DELETE FROM `'. _DB_PREFIX_ . 'quadragalleryslider_lang` WHERE id_quadragalleryslider = '.'"'.$id.'"';
		}
		if (!Db::getInstance()->Execute($query) || !Db::getInstance()->Execute($query2))
			return false;
		return true;
	}
	/**
	 * insert an image in database
	 * @param $id
	 * @return unknown_type
	 */	
	function insertData(){
		
		$languages = Language::getLanguages(false);
		
		Db::getInstance()->Execute("INSERT INTO " . _DB_PREFIX_ . "quadragalleryslider (image,thumb)
									VALUES(
										'".$_POST['img']."',
										'".$_POST['thumb']."')");
		
		$lastId = Db::getInstance()->getRow("SELECT id_quadragalleryslider FROM " . _DB_PREFIX_ . "quadragalleryslider ORDER BY id_quadragalleryslider DESC");
		
		foreach($languages as $language){
			Db::getInstance()->Execute("INSERT INTO " . _DB_PREFIX_ . "quadragalleryslider_lang (id_quadragalleryslider,id_lang,title,link)
						VALUES(
							'".$lastId['id_quadragalleryslider']."',
							'".$language['id_lang']."',
							'".$_POST['title_'.$language['id_lang']]."',
							'".$_POST['link_'.$language['id_lang']]."')");	
		}
		return true;	
	}
	/**
	 * resize the image in a big image and a small image for front display for image_generate
	 * @param $_FILES
	 * @param $width
	 * @param $height
	 * @return unknown_type
	 */
	function resizeGeneratedImages($_image,$width,$height){

		$errors = array();
		$dest_dir_big = str_replace('/images/','/images_big/',$_image);
		$dest_dir_small= str_replace('/images/','/images_small/',$_image);
		$thumb_size= 20/100;
		if($width!= ""|| $height != "" ){
			//big_image
			if (!imageResize($_image,$dest_dir_big,$width,$height)) {
				$errors[]=$this->l('An error occurred while generating the resize of big');
			}
			//small_image
			if (!imageResize($_image,$dest_dir_small,$thumb_size*$width,$thumb_size*$height)) {
				$errors[]=$this->l('An error occurred while generating the resize of small');
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
	function generateImages(){
		
		$datas = $this->selectAllImages();
		
		$languages = Language::getLanguages(false);
		if(isset($datas)){
			foreach($datas as $row){
				$texts = $this->selectForAllLanguages($row['id_quadragalleryslider']);
				foreach ($texts as $text) {
					foreach($languages as $language){
						if($language['id_lang']== $text['id_lang']){
							$_POST['title_'.$language['id_lang']] = $text['title'];
							$_POST['link_'.$language['id_lang']] = $text['link'];
						}	
					}
				}	
				$image_base = $row['image'];
				$_image = _PS_ROOT_DIR_.str_replace("images_big","images",$image_base);

				$this->resizeGeneratedImages($_image,$_POST['width'],$_POST['height']);
				$_image = str_replace(_PS_ROOT_DIR_,"",$_image);
				$_POST['img']= str_replace("images","images_big",$_image);
				$_POST['thumb']= str_replace("images","images_small",$_image);
				//enlever l'ancienne ligne 
				$this->deleteRow($row['id_quadragalleryslider']);
				//insertion des données dans la base
				$this->insertData();
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
		$this->generateImages();
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
		
		global $smarty,$cookie;
		$datas = $this->selectAll();
		if(isset($datas) || $datas != "NULL"){
			foreach($datas as $query){
				if($cookie->id_lang == $query['id_lang']){
					$images[]= $query['image'];
					$thumbs[]= $query['thumb'];
					$titles[]= $query['title'];
					$links[]= $query['link'];
				}	
			}
		}	
		$smarty->assign('imgs',$images);
		$smarty->assign('thumbs',$thumbs);
		$smarty->assign('titles',$titles);
		$smarty->assign('links',$links);	
		
		$v_height = (Configuration::get('PS_QUADRA_SLIDER_HEIGHT')*20/100) + 20;
		$box_height = $v_height*count($thumbs);
		if($box_height!= 0 && $box_height < Configuration::get('PS_QUADRA_SLIDER_HEIGHT')){
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
