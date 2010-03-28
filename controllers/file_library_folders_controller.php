<?php
class FileLibraryFoldersController extends FileLibraryAppController {

	var $helpers = array('Html', 'Form');
	var $layout = 'admin';


	function admin_index() {
		
		if(isset($this->params['named']['context']) && $this->params['named']['context'] == 'popup') {
			$this->set('inPopup',true);
		}
		else {
			$this->set('inPopup',false);
		}
		
		$this->FileLibraryFolder->recursive = 0;
		$this->set('uploadedFileFolders', $this->paginate());
	}



	function admin_add() {
		
		if(isset($this->params['named']['context']) && $this->params['named']['context'] == 'popup') {
			$this->set('inPopup',true);
		}
		else {
			$this->set('inPopup',false);
		}
		
		if (!empty($this->data)) {
			$this->FileLibraryFolder->create();
			if ($this->FileLibraryFolder->save($this->data)) {
				$this->Session->setFlash(__('The FileLibraryFolder has been saved', true));
				$this->redirect(array('action'=>'index'));
			} else {
				$this->Session->setFlash(__('The FileLibraryFolder could not be saved. Please, try again.', true));
			}
		}
	}

	function admin_edit($id = null) {
		
		if(isset($this->params['named']['context']) && $this->params['named']['context'] == 'popup') {
			$this->set('inPopup',true);
		}
		else {
			$this->set('inPopup',false);
		}
		
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(__('Invalid FileLibraryFolder', true));
			$this->redirect(array('action'=>'index'));
		}
		if (!empty($this->data)) {
			if ($this->FileLibraryFolder->save($this->data)) {
				$this->Session->setFlash(__('The FileLibraryFolder has been saved', true));
				$this->redirect(array('action'=>'index'));
			} else {
				$this->Session->setFlash(__('The FileLibraryFolder could not be saved. Please, try again.', true));
			}
		}
		if (empty($this->data)) {
			$this->data = $this->FileLibraryFolder->read(null, $id);
		}
	}

	function admin_delete($id = null) {
		if (!$id) {
			$this->Session->setFlash(__('Invalid id for FileLibraryFolder', true));
			$this->redirect(array('action'=>'index'));
		}
		if ($this->FileLibraryFolder->delete($id)) {
			$this->Session->setFlash(__('FileLibraryFolder deleted', true));
			$this->redirect(array('action'=>'index'));
		}
	}

}
?>
