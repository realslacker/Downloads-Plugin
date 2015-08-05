<?php
/*
 * Download Manager Plugin for WolfCMS <http://www.wolfcms.org>
 * Copyright (C) 2011 Shannon Brooks <shannon@brooksworks.com>
 */

//	security measure
if (!defined('IN_CMS')) { exit(); }

class DownloadsController extends PluginController {

	
	//	INIT **************************************************************************************
	//	*******************************************************************************************

	const VALID_SETTINGS	= 'download_path,download_uri,umask,filemode,dirmode,filetypes';
	const VALID_INPUT		= 'id,name,description,keywords,expires,active';
	
	const LOG_ERROR					= 3;
	const LOG_WARNING				= 4;
	const LOG_NOTICE				= 5;
	const LOG_INFO					= 6;
	
	public $settings;

	public function __construct() {
		self::__checkPermission();
		$this->setLayout('backend');
		$this->assignToLayout('sidebar', new View(DOWNLOADS_PLUGIN_ROOT.'/views/sidebar'));
		$this->__load_settings();
		
	}// Init */
	
	
	//	DISPLAY PAGES *****************************************************************************
	//	*******************************************************************************************
	
	//	redirect index to list of downloads
	public function index() {
		self::__checkPermission('downloads_view');
		
		$query = isset($_REQUEST['q']) && !empty($_REQUEST['q']) ? $_REQUEST['q'] : '' ;
		$page = isset($_REQUEST['pg']) && !empty($_REQUEST['pg']) ? (int)$_REQUEST['pg'] : 1 ;
		$order = isset($_REQUEST['order']) && !empty($_REQUEST['order']) ? $_REQUEST['order'] : '' ;
		
		$offset = ($page - 1) * 25;
		
		$downloads = array();
		$count = 0;
		$pages = 0;
		
		
		if ($result = downloadSearch($query,25,$offset,$order,true,true)) {
			$downloads = $result['downloads'];
			$count = $result['count'];
			$pages = ceil($count/25);
		}
		
		$this->display('downloads/views/index', array(
			'downloads' => $downloads,
			'count'		=> $count,
			'pages'		=> $pages,
			'page'		=> $page
		));
	}//*/

	//	display documentation
	public function documentation() {
		self::__checkPermission('downloads_settings');

		$tempdir = ini_get('upload_tmp_dir') ? ini_get('upload_tmp_dir') : sys_get_temp_dir();

		$this->display('downloads/views/documentation', array('download_path' => CMS_ROOT.'/'.$this->settings['download_path'], 'tempdir' => $tempdir) );
	}//*/

	//	displays settings of plugin
	public function settings() {
		self::__checkPermission('downloads_settings');
		$this->display('downloads/views/settings', $this->settings);
	}//*/
	
	//	displays the edit page for new record
	public function add() {
		self::__checkPermission('downloads_new');
		$this->display('downloads/views/edit',array('active'=>1));
	}//*/
	
	//	displays the edit page for updating record
	public function edit($id=null) {
		self::__checkPermission('downloads_edit');
		if (is_null($id)) {
			Flash::set('error',__('No ID specified!'));
			redirect(get_url('plugin/downloads'));
		}
		if (!$download = Download::findById($id)) {
			Flash::set('error',__('Could not find record!'));
			redirect(get_url('plugin/downloads'));
		}
		$download = (array)$download;
		$download['expires'] = !empty($download['expires']) ? date('m/d/Y',strtotime($download['expires'])) : '';
		$download['tags'] = $this->__gettags($download['id']);
		$this->display('downloads/views/edit',$download);
	}//*/
	
	//	returns autocomplete data
	public function autocomplete($which) {
		
		//	get the query
		if (empty($_GET['q'])) exit;
		$query = strtolower(preg_replace('/[^a-z0-9 _-]/','',$_GET['q']));
		
		//	which data are we looking for
		switch ($which) {
			
			case 'tags':
				$result = DownloadTag::find(array(
					'where' => 'downloadtags.name LIKE '.Record::escape("{$query}%")
				));
				foreach ($result as $tag) echo "{$tag->name}\n";
				exit;
			
			default:
				exit;
		}
	
	}//*/
	
	
	//	DATA MANIPULATION FUNCTIONS ***************************************************************
	// ********************************************************************************************

	//	delete a record
	public function delete($id=null) {
	
		//	make sure user has rights to delete
		self::__checkPermission('downloads_delete');
		
		//	check to make sure ID is set and valid
		if (is_null($id)) {
			$this->__log(__('error encountered deleting download').'; '.__('ID not specified'),self::LOG_ERROR);
			Flash::set('error',__('No ID specified!'));
			redirect(get_url('plugin/downloads'));
		}
		if (!$download = Download::findById($id)) {
			$this->__log(__('error encountered deleting download').'; '.__('could not find download by ID'),self::LOG_ERROR);
			Flash::set('error',__('Could not find record!'));
			redirect(get_url('plugin/downloads'));
		}
		
		//	delete the uploaded file
		$fullpath = CMS_ROOT."/{$this->settings['download_path']}/{$download->filename}";
		if (is_file($fullpath)) if (!unlink($fullpath)) {
			$this->__log(__('error encountered deleting download').'; '.__('Permission denied deleting file!'),self::LOG_ERROR);
			Flash::set('error',__('Permission denied deleting file!'));
			redirect(get_url('plugin/downloads'));
		}
		
		//	remove tag connections
		Record::deleteWhere('DownloadTagConnection','download_id='.Record::escape($download->id));

		//	delete record from database
		if (!$download->delete()) {
			$this->__log(__('error encountered deleting download').'; '.__('could not remove from database'),self::LOG_ERROR);
			Flash::set('error',__('Could not delete record!'));
			redirect(get_url('plugin/downloads'));
		}

		//	success!
		$this->__log(__('deleted download').' "'.$download->name.'"');
		Flash::set('success',__('Deleted file successfully!'));
		redirect(get_url('plugin/downloads'));

	}//*/
	
	//	update record
	public function update($id=null) {
	
		//	make sure user has rights to edit
		self::__checkPermission('downloads_edit');
		
		//	check to make sure ID is set and valid
		if (is_null($id)) {
			$this->__log(__('error encountered updating download').'; '.__('ID not specified'),self::LOG_ERROR);
			Flash::set('error',__('No ID specified!'));
			redirect(get_url('plugin/downloads'));
		}
		if (!$download = Download::findById($id)) {
			$this->__log(__('error encountered updating download').'; '.__('could not find download by ID'),self::LOG_ERROR);
			Flash::set('error',__('Could not find record!'));
			redirect(get_url('plugin/downloads'));
		}
		
		//	retrieve the new values from $_POST
		//$_POST['name'] = $download->name;
		$input = $this->__validate($_POST);
		$input['updated'] = date('Y-m-d H:i:s');
		
		//	check for uploaded file
		if (is_uploaded_file($_FILES['download']['tmp_name'])) {
			
			//	determine the destination directory
			$dstdir = CMS_ROOT."/{$this->settings['download_path']}/";
			
			//	remove the old file
			if (file_exists($dstdir.$download->filename)) if (!@unlink($dstdir.$download->filename)) {
				$this->__log(__('error encountered removing old file'),self::LOG_ERROR);
				Flash::set('error',__('Could not remove old file!'));
				redirect(get_url('plugin/downloads/edit/'.$id));
			}
			
			//	clear the hash in case the user is uploading the same file again
			$download->hash = '';
			$download->save();
			
			//	upload the new file
			if (!$upload = $this->__upload('download',$input['name'])) {
				redirect(get_url('plugin/downloads/edit/'.$id));
			}
			$download->filename = $upload['filename'];
			$download->hash = $upload['hash'];
			$download->save();
		}
		//	no uploaded file, did the name change?
		elseif ($input['name'] != $download->name) {
			if (!$download->filename = $this->__rename($download->filename,$input['name'])) {
				redirect(get_url('plugin/downloads/edit/'.$id));
			}
			$download->save();
		}
		
		//	update the record with the new values
		$download->setFromData($input);
		if (!$download->save()) {
			$this->__log(__('error encountered updating download'),self::LOG_ERROR);
			Flash::set('error',__('Could not update the record in the database.'));
			redirect(get_url('plugin/downloads/edit/'.$id));
		}
		
		//	add the tags
		$this->__storetags($_POST['tags'],$download->id);
		
		$this->__log(__('updated download').' "'.$download->name.'"');
		Flash::set('success',__('Record updated!'));
		redirect(get_url('plugin/downloads/edit/'.$id));
	}//*/

	//	create record
	public function create() {
	
		//	make sure user has rights to create
		self::__checkPermission('downloads_new');
		
		//	get the validated input
		$input = $this->__validate($_POST);
		
		//	attempt to upload the image
		if (!$upload = $this->__upload('download',$input['name'])) {
			redirect(get_url('plugin/downloads/add'));
		}
		$input = array_merge($input,$upload);
		
		//	set the created date
		$input['created'] = date('Y-m-d H:i:s');
		$input['updated'] = date('Y-m-d H:i:s');
		
		//	save the new record
		$download = new Download($input);
		if (!$download->save()) {
			
			//	determine the destination directory
			$dstdir = CMS_ROOT."/{$this->settings['download_path']}/";
			
			//	remove the uploaded file since save failed
			if (!unlink($dstdir.$download->filename)) {
				$this->__log(__('error encountered creating new download'),self::LOG_ERROR);
				Flash::set('error',__('Could not save record in database and permission denied removing uploaded file!'));
				redirect(get_url('plugin/downloads/add'));
			}
			
			$this->__log(__('error encountered creating new download'),self::LOG_ERROR);
			Flash::set('error',__('Could not save record in database!'));
			redirect(get_url('plugin/downloads/add'));
		}
		
		//	add the tags
		$this->__storetags($_POST['tags'],$download->id);
		
		//	pat on the back and send back to the list
		$this->__log(__('created new download').' "'.$download->name.'"');
		Flash::set('success',__('Record saved!'));
		redirect(get_url('plugin/downloads/edit/'.$download->id));

	}//*/

	//	save settings
	public function save_settings() {
		$this->__checkPermission('downloads_settings');
		
		//	clean any keys from the $_POST array that aren't valid
		$settings = self::__clean($_POST,self::VALID_SETTINGS);
		
		//	sanitize the path values
		$settings['download_path'] = self::__sanitize($settings['download_path']);
		$settings['download_uri'] = self::__sanitize($settings['download_uri']);
		
		//	cleanup tags
		$settings['filetypes'] = preg_replace('/[^a-z0-9,]/','',strtolower($settings['filetypes']));
		
		//	cleanup the masks
		$settings['umask'] = (int)$settings['umask'] == 0 ? 0 : sprintf("%04s",(int)$settings['umask']<=777 ? (int)$settings['umask'] : 0);
		$settings['dirmode'] = sprintf("%04s",(int)$settings['dirmode']<=777 && 111 <= (int)$settings['dirmode'] ? (int)$settings['dirmode'] : 755);
		$settings['filemode'] = sprintf("%04s",(int)$settings['filemode']<=777 && 111 <= (int)$settings['filemode'] ? (int)$settings['filemode'] : 644);
		
		if (Plugin::setAllSettings($settings, 'downloads')) {
			$this->log(__('modified plugin settings'));
			Flash::set('success',__('Settings saved.'));
		}
		else {
			$this->__log(__('encountered an error saving plugin settings'),self::LOG_ERROR);
			Flash::set('error',__('Could not save settings!'));
		}

		redirect(get_url('plugin/downloads/settings'));

	}//*/
	
	// UTILITY FUNCTIONS **************************************************************************
	// ********************************************************************************************

	//	check that user has permissions
	private static function __checkPermission($permission='downloads_view') {
		AuthUser::load();
		if ( ! AuthUser::isLoggedIn()) {
			redirect(get_url('login'));
		}
		if ( ! AuthUser::hasPermission($permission) ) {
			Flash::set('error', __('You do not have permission to access the requested page!'));
			if (! AuthUser::hasPermission('downloads_view') ) redirect(get_url());
			else redirect(get_url('plugin/downloads'));
		}
	}//*/

	//	log an event (uses dashboard plugin)
	//	default log level is LOG_INFO
	private function __log($message=null,$level=6) {
		Observer::notify('log_event', __('Download Manager').': :username '.$message.'.', 'downloads', $level);
	}//*/

	//	clean invalid keys from input by intersecting the array
	//	against array of valid keys
	private static function __clean($settings,$keys) {
		if (!is_array($settings)) return array();
		$valid = is_array($keys) ? $keys : explode(',',$keys);
		$valid = array_combine($valid,$valid);
		return array_intersect_key($settings, $valid);
	}//*/
	
	//	sanitize a path
	private static function __sanitize($path) {
		$path = explode('/',$path);
		foreach ($path as $k => $v) $path[$k] = trim($v," \t.");
		return implode('/',array_filter($path,'strlen'));
	}//*/
	
	//	read settings into $this->settings
	private function __load_settings() {
		if (!$this->settings = Plugin::getAllSettings('downloads')) {
			Flash::set('error', __('Unable to retrieve plugin settings.'));
			redirect(get_url('setting'));
			return;
		}
	}//*/
	
	//	renames and upload
	private function __rename($oldname,$newname,$overwrite=false) {
		
		if (empty($oldname) || empty($newname)) return false;
		
		//	determine the uploaded file extension
		$ext = strtolower(pathinfo($oldname,PATHINFO_EXTENSION));
		
		//	setup the new filename
		$newname = $this->__filename($newname,$ext);
		
		//	determine the destination directory
		$dstdir = CMS_ROOT."/{$this->settings['download_path']}/";
		
		//	check that the original file exists
		if (!file_exists($dstdir.$oldname)) {
			$this->__log(__('error encountered renaming file').'; '.__('original file does not exist'),self::LOG_ERROR);
			Flash::set('error',__('original file does not exist'));
			return false;
		}
		
		//	if overwrite isn't allowed check to see if file exists
		if (!$overwrite && file_exists($dstdir.$newname)) {
			$this->__log(__('error encountered renaming file').'; '.__('file already exists'),self::LOG_ERROR);
			Flash::set('error',__('file already exists'));
			return false;
		}
		
		//	attempt to rename the file
		if (!@rename($dstdir.$oldname,$dstdir.$newname)) {
			$this->__log(__('error encountered renaming file').'; '.__('file could not be renamed'),self::LOG_ERROR);
			Flash::set('error',__('file could not be renamed'));
			return false;
		}
		
		return $newname;
	}
	
	//	uploads a file to the server
	//	returns filename on success, false on error
	private function __upload($tagname,$filename,$overwrite=false) {
	
		//	if there is no uploaded file return false
		if (!is_uploaded_file($_FILES[$tagname]['tmp_name'])) {
			$this->__log(__('error encountered uploading file').'; '.__('no file was uploaded'),self::LOG_ERROR);
			Flash::set('error',__('No file was uploaded!'));
			return false;
		}
		
		//	if the uploaded file is not unique return false
		if (!$hash = $this->__unique($_FILES[$tagname]['tmp_name'])) {
			$this->__log(__('error encountered uploading file').'; '.__('the file was not unique; the file already exists'),self::LOG_ERROR );
			Flash::set('error',__('the file was not unique; the file already exists') . print_r($_FILES,1) );
			return false;
		}
		
		//	determine the uploaded file extension
		$ext = strtolower(pathinfo($_FILES[$tagname]['name'],PATHINFO_EXTENSION));
		
		//	check to make sure uploaded filetype is ok
		$valid_exts = explode(',',Plugin::getSetting('filetypes','downloads'));
		if (!in_array($ext,$valid_exts)) {
			$this->__log(__('error encountered uploading file').'; '.__('file was not an allowed type'),self::LOG_ERROR);
			Flash::set('error',__('Uploaded file not an allowed file type.'));
			return false;
		}
		
		//	setup the filename
		$filename = $this->__filename($filename,$ext);
				
		//	determine the destination directory
		$dstdir = CMS_ROOT."/{$this->settings['download_path']}/";
		
		//	if overwrite isn't allowed check to see if file exists
		if (!$overwrite && file_exists($dstdir.$filename)) {
			$this->__log(__('error encountered uploading file').'; '.__('file already exists'),self::LOG_ERROR);
			Flash::set('error',__('file already exists'));
			return false;
		}
		
		//	set the umask and move the uploaded file
		//	if there is a problem set the error, put the old file back and set an error
		umask(octdec($this->settings['umask']));
		if (!@move_uploaded_file($_FILES[$tagname]['tmp_name'],$dstdir.$filename)) {
			$this->__log(__('error encountered uploading file').'; '.__('could not move uploaded file'),self::LOG_ERROR);
			Flash::set('error',__('Could not move uploaded file!'));
			return false;
		}
		
		//	check to see if the file was uploaded and try to chmod the file
		//	if there is a problem with chmod output an error but don't stop since the
		//	file was already uploaded
		if (@!chmod($dstdir.$filename, octdec($this->settings['filemode']))) {
			$this->__log(__('error encountered uploading file').'; '.__('unable to change permissions'),self::LOG_WARNING);
			flash::set('error', __('File uploaded, however file permissions could not be changed.'));
		}
		
		//	return the image name
		$this->__log(__('uploaded file').' - '.$filename);
		return array('filename'=>$filename,'hash'=>$hash);

	}//*/
	
	//	gets safe file name from name
	private function __filename($name,$ext='') {
		return strtolower(trim(preg_replace('/[^0-9a-z]+/i','-',$name),'-').(!empty($ext)?".{$ext}":''));
	}//*/
	
	//	returns sha1 of specified file
	private function __sha1($filename) {
		if (!file_exists($filename)) return false;
		return sha1_file($filename);
	}//*/
	
	//	checks to make sure specified file's hash is unique in database
	private function __unique($filename) {
		if (!$hash = $this->__sha1($filename)) return false;
		if ($download = Download::findByHash($hash)) {
			$this->__log(__('error encountered uploading file').'; '.__('the file was not unique; the file already exists with ID: ').$download->id,self::LOG_ERROR);
			Flash::set('error',__('the file was not unique; the file already exists with ID: ').$download->id);
			return false;
		}
		return $hash;
	}//*/
	
	//	store tags into database
	private function __storetags($tags,$download_id=null) {
	
		//	if download_id is provided clear out old tags
		if (!is_null($download_id)) Record::deleteWhere('DownloadTagConnection','download_id='.Record::escape((int)$download_id));
		
		//	check to make sure there are some tags
		if (empty($tags)) return true;
		
		//	take either an array or comma separated list of tags
		if (!is_array($tags)) $tags = explode(',',$tags);
		$tags = preg_replace('/[^a-z0-9 _,-]/','',$tags);
		
		//	find or create tag and connect to download
		foreach ($tags as $tagname) {
			$tagname = trim(strtolower($tagname));
			//	check for minimum tag length; must be at least three characters
			if (strlen($tagname) >= 3) {
				if (!$tag = DownloadTag::findByName($tagname)) {
					$tag = new DownloadTag(array('name'=>$tagname));
					$tag->save();
				}
				
				if (!is_null($download_id)) {
					$connection = new DownloadTagConnection(array(
						'download_id'=>(int)$download_id,
						'tag_id'=>$tag->id
					));
					$connection->save();
				}
			}
		}
		
		return true;
	}//*/
	
	//	get tags from database
	private function __gettags($download_id,$return_array=false) {
		$results = DownloadTagConnection::findAllByDownloadId($download_id);
		$tags = array();
		foreach ($results as $connection) {
			$tag = DownloadTag::findById($connection->tag_id);
			$tags[$tag->id] = $tag->name;
		}
		if ($return_array) return $tags;
		return implode(', ',$tags);
	}//*/
	
	//	validate uploads
	private function __validate($input) {
		
		//	remove invalid keys from input array
		$input = $this->__clean($input,self::VALID_INPUT);
		
		//	setup path for redirect
		$redirect = 'plugin/downloads/edit'.(isset($input['id']) ? '/'.$input['id'] : '');
		
		//	clean the name and leave just the basics
		if (empty($input['name'])) {
			Flash::set('error',__('Name is Required'));
			redirect(get_url($redirect));
		}
		
		//	set the expires tag to null if it's empty otherwise reformat for the database
		$input['expires'] = preg_replace('/[^0-9]/','',$input['expires']);
		$input['expires'] = !empty($input['expires']) ? substr($input['expires'],4).'-'.substr($input['expires'],0,2).'-'.substr($input['expires'],2,2) : '';
		
		//	detect active checked or not
		$input['active'] = isset( $input['active'] ) ? 1 : 0;
		
		//return the array
		return $input;

	}//*/
	
}