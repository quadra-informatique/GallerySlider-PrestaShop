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
 * @version Release: $Revision: 1.1 $
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * ---------------------------------------------------------------------------------
*/

include (dirname(__FILE__) . '/quadragalleryslider.php');

class AdminGallerySlider extends AdminTab {

    private $module = 'quadragalleryslider';
	
    public function __construct() {
        $this->table = 'quadragalleryslider';
   		$this->className = 'QuadraGallerySlider';
        $this->edit = true;
        $this->delete = true;

       	$this->fieldsDisplay = array(
       		'thumb' => array('title' => $this->l('IMAGE')),
			'title' => array('title' => $this->l('TITLE'), 'width' => 220, 'filter_key' => 'b!name'),
			'link' => array('title' => $this->l('LINK'), 'width' => 220, 'filter_key' => 'b!name')
        );
        
        $this->_join = 'LEFT JOIN `'._DB_PREFIX_.'quadragalleryslider_lang` i ON (i.`id_quadragalleryslider` = a.`id_quadragalleryslider`)';
        $this->_select = 'i.`id_lang`,i.`title`,i.`link`';
        parent::__construct();
    }
	/**
	 * control the uploaded files
	 * @return unknown_type
	 */
	function controlUpload($_FILES){
		
		if(isset($_FILES['img0'])){
			$_FILES['img'] = $_FILES['img0'];
		}
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
	function resizeImages($_FILES,$width,$height){
		
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
	 * action of the button resize images
	 * @return unknown_type
	 */
	function updateData($id){
		
		if(isset($_FILES['img0'])){
			$_FILES['img'] = $_FILES['img0'];
		}
		$NewFile = basename($_FILES['img']['name']);
		$rows = quadragalleryslider::retrieveData($id);
		foreach($rows as $row){
			$image_base = $row['image'];
			$_image = _PS_ROOT_DIR_.str_replace("images_big","images",$image_base);
		}
		if($NewFile != ""){
			$this->resizeImages($_FILES,Configuration::get('PS_QUADRA_SLIDER_WIDTH'),Configuration::get('PS_QUADRA_SLIDER_HEIGHT'));
			foreach(array_keys($_FILES) as $key) {
				$_POST['img']=_MODULE_DIR_."quadragalleryslider/images_big/".basename($_FILES['img']['name']);
				$_POST['thumb']=_MODULE_DIR_."quadragalleryslider/images_small/".basename($_FILES['img']['name']);
			}
		}else{
			$_image = str_replace(_PS_ROOT_DIR_,"",$_image);
			$_POST['img']= str_replace("images","images_big",$_image);
			$_POST['thumb']= str_replace("images","images_small",$_image);
		}
		//enlever l'ancienne ligne 
		quadragalleryslider::deleteRow($id);
		
		quadragalleryslider::insertData();
	}

	function addData(){
		
		if($_POST['title_1']!= "" || $_POST['link_1'] != "" || $_POST['title_2']!= "" || $_POST['link_2'] != ""){
			$this->resizeImages($_FILES,Configuration::get('PS_QUADRA_SLIDER_WIDTH'),Configuration::get('PS_QUADRA_SLIDER_HEIGHT'));
			foreach(array_keys($_FILES) as $key) {
				$_POST['img']=_MODULE_DIR_."quadragalleryslider/images_big/".basename($_FILES['img']['name']);
				$_POST['thumb']=_MODULE_DIR_."quadragalleryslider/images_small/".basename($_FILES['img']['name']);
			}
			//insertion des données dans la base
			quadragalleryslider::insertData();
		}
	}
	/**
	 * Display list
	 *
	 * @global string $currentIndex Current URL in order to keep current Tab
	 */
	public function displayList()
	{
		global $currentIndex;

		$this->displayTop();

		if ($this->edit AND (!isset($this->noAdd) OR !$this->noAdd))
			echo '<br /><a href="'.$currentIndex.'&add'.$this->table.'&token='.$this->token.'"><img src="../img/admin/add.gif" border="0" /> '.$this->l('Add new').'</a><br /><br />';
		/* Append when we get a syntax error in SQL query */
		if ($this->_list === false)
		{
			$this->displayWarning($this->l('Bad SQL query'));
			return false;
		}
		/* Display list header (filtering, pagination and column names) */
		$this->displayListHeader();
		if (!sizeof($this->_list))
			echo '<tr><td class="center" colspan="'.(sizeof($this->fieldsDisplay) + 2).'">'.$this->l('No items found').'</td></tr>';

		/* Show the content of the table */
		$this->displayListContent();

		/* Close list table and submit button */
		$this->displayListFooter();
	}
	
	public function displayListContent($token = NULL) {
    
    	global $currentIndex, $cookie;
    	$id_lang = $cookie->id_lang;
    	
    	$id_category = 1; // default categ
        $irow = 0;
        if ($this->_list AND isset($this->fieldsDisplay['position'])) {
            $positions = array_map(create_function('$elem', 'return (int)($elem[\'position\']);'), $this->_list);
            sort($positions);
        }
        if ($this->_list) {
            $isCms = false;
            if (preg_match('/cms/Ui', $this->identifier))
                $isCms = true;
            $keyToGet = 'id_' . ($isCms ? 'cms_' : '') . 'category' . (in_array($this->identifier, array('id_category', 'id_cms_category')) ? '_parent' : '');
        
            foreach ($this->_list AS $i=>$tr) {
            	if($tr['id_lang']== $id_lang){
		            $id = $tr[$this->identifier];
		            echo '<tr' . (array_key_exists($this->identifier, $this->identifiersDnd) ? ' id="tr_' . (($id_category = (int) (Tools::getValue('id_' . ($isCms ? 'cms_' : '') . 'category', '1'))) ? $id_category : '') . '_' . $id . '_' . $tr['position'] . '"' : '') . ($irow++ % 2 ? ' class="alt_row"' : '') . ' ' . ((isset($tr['color']) AND $this->colorOnBackground) ? 'style="background-color: ' . $tr['color'] . '"' : '') . '>
									<td class="center">';
		            if ($this->delete AND (!isset($this->_listSkipDelete) OR !in_array($id, $this->_listSkipDelete)))
		               echo '<input type="checkbox" name="' . $this->table . 'Box[]" value="' . $id . '" class="noborder" />';
		               echo '</td>';
		    	
			    	foreach ($this->fieldsDisplay AS $key => $params) {
			       		$tmp = explode('!', $key);
			            $key = isset($tmp[1]) ? $tmp[1] : $tmp[0];
			
			            echo '<td ' . (isset($params['position']) ? ' id="td_' . (isset($id_category) AND $id_category ? $id_category : 0) . '_' . $id . '"' : '') . ' class="' . ((!isset($this->noLink) OR !$this->noLink) ? 'pointer' : '') . ((isset($params['position']) AND $this->_orderBy == 'position') ? ' dragHandle' : '') . (isset($params['align']) ? ' ' . $params['align'] : '') . '" ';
			            if (!isset($params['position']) AND (!isset($this->noLink) OR !$this->noLink))
			               echo ' onclick="document.location = \'' . $currentIndex . '&' . $this->identifier . '=' . $id . ($this->view ? '&update' : '&update') . $this->table . '&token=' . ($token != NULL ? $token : $this->token) . '\'">' . (isset($params['prefix']) ? $params['prefix'] : '');
			            else
			               echo '>';
			                        
			    		if ($key =="thumb"){
			    			echo '<img src="'.$tr[$key].'">';
			    		}
			    		if ($key =="title"){
			    			echo $tr[$key];
			    		}
			    		if ($key =="link"){
			    			echo $tr[$key];
			    		}
			    	}
			    	if ($this->edit OR $this->delete OR ($this->view AND $this->view !== 'noActionColumn')) {
			        	echo '<td class="center" style="white-space: nowrap;">';
			               if ($this->edit)
			                  $this->_displayEditLink($token, $id);
			               if ($this->delete AND (!isset($this->_listSkipDelete) OR !in_array($id, $this->_listSkipDelete)))
			                  $this->_displayDeleteLink($token, $id);
			               if ($this->duplicate)
			                  $this->_displayDuplicate($token, $id);
			               echo '</td>';
			       }
	      		   echo '</tr>';
            	}
            }
        }
    }
public function display()
	{
		global $currentIndex, $cookie;

		// Include other tab in current tab
		if ($this->includeSubTab('display', array('submitAdd2', 'add', 'update', 'view'))){}

		// Include current tab
		elseif ((Tools::getValue('submitAdd'.$this->table) AND sizeof($this->_errors)) OR isset($_GET['add'.$this->table]))
		{
			if ($this->tabAccess['add'] === '1')
			{
				$this->displayForm();
				if ($this->tabAccess['view'])
					echo '<br /><br /><a href="'.((Tools::getValue('back')) ? Tools::getValue('back') : $currentIndex.'&token='.$this->token).'"><img src="../img/admin/arrow2.gif" /> '.((Tools::getValue('back')) ? $this->l('Back') : $this->l('Back to list')).'</a><br />';
			}
			else
				echo $this->l('You do not have permission to add here');
		}
		elseif (isset($_GET['update'.$this->table]))
		{
			if ($this->tabAccess['edit'] === '1' OR ($this->table == 'employee' AND $cookie->id_employee == Tools::getValue('id_employee')))
			{
				$this->displayForm();
				if ($this->tabAccess['view'])
					echo '<br /><br /><a href="'.((Tools::getValue('back')) ? Tools::getValue('back') : $currentIndex.'&token='.$this->token).'"><img src="../img/admin/arrow2.gif" /> '.((Tools::getValue('back')) ? $this->l('Back') : $this->l('Back to list')).'</a><br />';
			}
			else
				echo $this->l('You do not have permission to edit here');
		}
		elseif (isset($_GET['view'.$this->table]))
			$this->{'view'.$this->table}();

		else
		{
			$this->getList((int)($cookie->id_lang));
			$this->displayList();
			$this->displayOptionsList();
			$this->displayRequiredFields();
			$this->includeSubTab('display');
		}
	}
	/**
	 * Close list table and submit button
	 */
	public function displayListFooter($token = NULL)
	{
		echo '</table>';
		if ($this->delete)
			echo '<p><input type="submit" class="button" name="submitDel'.$this->table.'" value="'.$this->l('Delete selection').'" onclick="return confirm(\''.$this->l('Delete selected items?', __CLASS__, TRUE, FALSE).'\');" /></p>';
		echo '
				</td>
			</tr>
		</table>
		<input type="hidden" name="token" value="'.($token ? $token : $this->token).'" />
		</form>';
		if (isset($this->_includeTab) AND sizeof($this->_includeTab))
			echo '<br /><br />';
	}
		    
	protected function _displayForm() {
		
		global $currentIndex, $cookie;
		$languages = Language::getLanguages(false);
		$defaultLanguage = (int)(Configuration::get('PS_LANG_DEFAULT')); //default language of store
		
		echo '
		<form method="post" action="'.$currentIndex.'&token='.Tools::getValue('token').'" enctype="multipart/form-data">
		<input type="hidden" name="id_slider" id="id_slider" value="'.Tools::getValue('id_quadragalleryslider').'" />
		<fieldset>';
		
		if(Tools::isSubmit('add'.$this->table)){
			echo '
				<div style="float:left;">
					'.($this->l('Add') ).'
				</div><div style="float:left;">
				<table>
					<tr>
						<td style="vertical-align:top;width:150px;">
							<label>'.$this->l('Image').'</label>
						</td>
						<td>	
							<input type="file" name="img" />
						</td>
					</tr>
					<tr>
						<td style="vertical-align:top;width:150px;">		
							<label>'.$this->l('Image Title').'</label>
						</td>
						<td>';
							foreach ($languages as $language)	
								echo '<div id="title_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').';">
									<input type="text" name="title_'.$language['id_lang'].'" size="64" value="" />
								</div>';
							$this->displayFlags($languages, $defaultLanguage, 'title¤link', 'title');
							echo '
						</td>	
					</tr>
					<tr>
						<td style="vertical-align:top;width:150px;">
							<label>'.$this->l('Link').'</label>
						</td>
						<td>';
							foreach ($languages as $language)			
								echo '<div id="link_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').';">
										<input type="text" name="link_'.$language['id_lang'].'" size="64" value="" />
									</div>';
							$this->displayFlags($languages, $defaultLanguage, 'link¤title', 'link');
							echo '
						</td>
					</tr>
				</table>			
				</div>
				<hr class="clear"/>';
			
			echo '
			<div class="margin-form clear"><input type="submit" name="submitConfAdd" value="'.$this->l('Save').'" class="button" /></div>
			</fieldset>
		</form>';
		}
		else if(Tools::isSubmit('update'.$this->table)){
			
			$datas = quadragalleryslider::retrieveData($_GET['id_quadragalleryslider']);
			$texts = quadragalleryslider::selectForAllLanguages(Tools::getValue('id_quadragalleryslider'));
			if(isset($datas)){
				foreach ($datas as $i => $row) {
					
					$isFile2 = is_file($_SERVER['DOCUMENT_ROOT'].$row['image']);
					$isThumb2 = is_file($_SERVER['DOCUMENT_ROOT'].$row['thumb']);
					
				echo '
				<div style="float:left;">
						'.($isThumb2 ? '<img src="'.$row['thumb'].'"name="thumb['.$i.']" "/>' : $this->l('Add') ).'
				</div><div style="float:left;">
				<table>
					<tr>
						<td style="vertical-align:top;width:150px;">
							<label>'.$this->l('Image').'</label>
						</td>
						<td>	
							<input type="file" name="img'.$i.'" />
							'.($isFile2 ? '<input type="hidden" name="img['.$i.']" value="'.$row['image'].'"/>' : '' ).'
						</td>
					</tr>
					<tr>
						<td style="vertical-align:top;width:150px;">		
							<label>'.$this->l('Image Title').'</label>
						</td>
						<td>';
						foreach ($texts as $text) {
							foreach ($languages as $language)
								if($text['id_lang'] == $language['id_lang'])
									echo '<div id="title_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').';">
										<input type="text" name="title_'.$language['id_lang'].'" size="64" value="'.($text['title'] ? stripslashes(htmlspecialchars($text['title'])) : '').'" />
									</div>';
						}			
						$this->displayFlags($languages, $defaultLanguage, 'link¤title', 'title');
						echo '
						</td>	
					</tr>
					<tr>
						<td style="vertical-align:top;width:150px;">
							<label>'.$this->l('Link').'</label>
						</td>
						<td>';
						foreach ($texts as $text) {	
							foreach ($languages as $language)
								if($text['id_lang']== $language['id_lang'])				
									echo '<div id="link_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').';">
											<input type="text" name="link_'.$language['id_lang'].'" size="64" value="'.($text['link'] ? stripslashes(htmlspecialchars($text['link'])) : '').'" />
										</div>';
						}			
						$this->displayFlags($languages, $defaultLanguage, 'link¤title', 'link');
						echo '
						</td>
					</tr>
				</table>			
				</div>
				<hr class="clear"/>';
					
				}	
			}
			echo '
			<div class="margin-form clear"><input type="submit" name="submitConfUpdate" value="'.$this->l('Save').'" class="button" /></div>
			</fieldset>
		</form>';
		}
	}
    
	public function postProcess($token = NULL) {
		
		$id_quadragalleryslider = Tools::getValue('id_quadragalleryslider');
		$this->_displayForm();
		
		if(Tools::isSubmit('submitConfAdd'))
		{
			$this->addData();
			
		}elseif(Tools::isSubmit('submitConfUpdate'))
		{
			$this->updateData($_POST['id_slider']);
			
		}elseif(Tools::isSubmit('submitDel'.$this->table))
		{
			if (isset($_POST[$this->table.'Box'])){
				$selectedList = $_POST[$this->table.'Box'];
				foreach($selectedList as $item){
					$datas = quadragalleryslider::retrieveData($item);
					foreach($datas as $i => $row){
						quadragalleryslider::deleteRow($row['id_quadragalleryslider']);
						quadragalleryslider::removeImages($row['image']);
					}
				}
			}		
		}else if(Tools::isSubmit('delete'.$this->table))
		{
			$datas = quadragalleryslider::retrieveData($id_quadragalleryslider);
			foreach($datas as $i => $row){
				quadragalleryslider::deleteRow($row['id_quadragalleryslider']);
				quadragalleryslider::removeImages($row['image']);
			}
		}
        //return parent::postProcess();
    }
}

?>
