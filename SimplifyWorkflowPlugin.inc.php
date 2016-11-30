<?php

/**
 * @file plugins/generic/simplifyWorkflow/SimplifyWorkflowPlugin.inc.php
 *
 * Copyright (c) 2016 Language Science Press
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SimplifyWorkflowPlugin
 *
 */

import('lib.pkp.classes.plugins.GenericPlugin');
import('plugins.generic.simplifyWorkflow.SimplifyWorkflowDAO');

class SimplifyWorkflowPlugin extends GenericPlugin {
	/**
	 * Register the plugin.
	 * @param $category string
	 * @param $path string
	 */

	function register($category, $path) {

		if (parent::register($category, $path)) {
			if ($this->getEnabled()) {

				// overwrite templates that are called with display or include
				HookRegistry::register('TemplateManager::display', array(&$this, 'handleDisplayTemplate'));
				HookRegistry::register('TemplateManager::include', array(&$this, 'handleIncludeTemplate'));
				HookRegistry::register ('addparticipantform::Constructor', array(&$this, 'handleAddParticipantForm'));
				HookRegistry::register ('submissionfilesuploadform::Constructor', array(&$this,
										'handleSubmissionFilesUploadForm')); 
				HookRegistry::register ('catalogentrycatalogmetadataform::Constructor', array(&$this,'handleCatalogEntryForm'));
				HookRegistry::register ('catalogentryformatmetadataform::Constructor', array(&$this,'handlePublicationEntryForm'));

				// control database action
				HookRegistry::register ('eventlogdao::_insertobject', array(&$this, 'handleInsertObject'));

				// action at the end of the submission process
				HookRegistry::register('submissionsubmitstep3form::validate', array(&$this, 'handleAssignEditors'));

			}
			return true;
		}
		return false;
	}

	function handleDisplayTemplate($hookName, $args) {

		$request = $this->getRequest();
		$press = $request->getPress();

		$templateMgr =& $args[0];
		$template =& $args[1];

		switch ($template) {

			case 'workflow/workflow.tpl':	
				// include simplifyWorkflow.css
			$templateMgr->display($this->getTemplatePath() . 
				'workflowModified.tpl', 'text/html', 'TemplateManager::display');
				return true;
			case 'authorDashboard/authorDashboard.tpl':	
				// exclude internal review
				$templateMgr->display($this->getTemplatePath() . 
				'authorDashboardModified.tpl', 'text/html', 'TemplateManager::display');
				return true;
		}
		return false;
	}

	function handleIncludeTemplate($hookName, $args) {

		$templateMgr =& $args[0];
		$params =& $args[1];

		if (!isset($params['smarty_include_tpl_file'])) {
			return false;
		}

		switch ($params['smarty_include_tpl_file']) {
			case 'core:submission/form/step1.tpl':
				// do not display privacy statement
				$templateMgr->display($this->getTemplatePath() . 
				'coreStep1Modified.tpl', 'text/html', 'TemplateManager::include');
				return true;
			case 'submission/form/categories.tpl':
				// remove categories sections
				$templateMgr->display($this->getTemplatePath() . 
				'categoriesModified.tpl', 'text/html', 'TemplateManager::include');
				return true;
			case 'submission/form/series.tpl':
				// choosing a series is required
				$templateMgr->display($this->getTemplatePath() . 
				'seriesModified.tpl', 'text/html', 'TemplateManager::include');
				return true;
		}
		return false;
	}

	// templates loaded with setTemplate

	function handleAddParticipantForm($hookName, $args)  {
		// do not send a notification if a participant is added to a submission
		$form =& $args[0]; 
		$form->setTemplate($this->getTemplatePath() . 'addParticipantFormModified.tpl'); 
		return true;
	}

	function handleSubmissionFilesUploadForm($hookName, $args) {
		// preselect "Book manuscript" in file upload
		$form =& $args[0]; 
		$form->setTemplate($this->getTemplatePath() . 'fileUploadFormModified.tpl'); 
		return true;
	}

	function handleCatalogEntryForm($hookName, $args) {
		// commenting out audience and representatives in catalog entry, tab catalog
		$form =& $args[0]; 
		$form->setTemplate($this->getTemplatePath() . 'catalogMetadataFormFieldsModified.tpl'); 
		return true;
	}

	function handlePublicationEntryForm($hookName, $args) {
		// commenting out Market territories, digital information, sales right in publication format tabs
		// commenting out imprint (value is set via the db in function handleAssignEditors)
		// product availability set to "20" (available) and made invisible
		// product compostition code set to "00" (single item retail product) and made insivible
		$form =& $args[0]; 
		$form->setTemplate($this->getTemplatePath() . 'publicationMetadataFormFieldsModified.tpl'); 
		return true;
	}

	function handleInsertObject($hookName, $args) {

		$sql =& $args[0]; 
		$parameters =& $args[1];
		$message = ''; if (isset($parameters[5])) {$message =$parameters[5];} 

		if ($message=='submission.event.fileUploaded') {
			// terms (sales_type) of all submissions are to set to open "access"
			// in production stage -> publication formats -> uploaded files
			// direct_sales of all submissions are set to 0
			$simplifyWorkflowDAO = new SimplifyWorkflowDAO();
			$simplifyWorkflowDAO->setTermsToOpenAcess();
		}
		return true;
	}


	function handleAssignEditors($hookName, $args) {

		// series editors are now assigned by OMP -> not necessary anymore
		$submissionId = $args[0]->submissionId;
		$contextId = $this->getRequest()->getContext()->getId();

		$simplifyWorkflowDAO = new SimplifyWorkflowDAO;
 		$pressManagerId = $simplifyWorkflowDAO->getRoleId("Press Manager",$contextId);
 		//$seriesEditorId = $simplifyWorkflowDAO->getRoleId("Series Editor",$contextId);

		//$seriesEditors = $simplifyWorkflowDAO->getSeriesEditors($submissionId,$contextId);
		$pressManagers = $simplifyWorkflowDAO->getPressManagers($contextId);
 
		// assign a press manager as soon as a book is submitted
		// prefer id 86, otherwise take first press manager in the list 
		$preferedPressManagerId = 86;

		if (in_array($preferedPressManagerId,$pressManagers)) {
			$simplifyWorkflowDAO->assignParticipant($submissionId,$pressManagerId,$preferedPressManagerId);
		}
		// not necessary anymore, OMP does it by itself
		/* 
		else {
			if (sizeof($pressManagers)>0) {
				$simplifyWorkflowDAO->assignParticipant($submissionId,$pressManagerId,$pressManagers[0]);
			}
		}
		*/

		// assign all series editors of the series the submission is put in
		// not necessary anymore: OMP does it by itself
		/*for ($i=0; $i<sizeof($seriesEditors); $i++) {
			$simplifyWorkflowDAO->assignParticipant($submissionId,$seriesEditorId,$seriesEditors[$i]);
		}*/
	
		// add standard values:
		// publication format: names, digital, physical_format, composition code:00
		// entry key: DA,BB,BC
		$simplifyWorkflowDAO->addStandardValuesAfterSubmit($submissionId);

		return false;
	}


	/**
	 * @copydoc PKPPlugin::getDisplayName()
	 */
	function getDisplayName() {
		return __('plugins.generic.simplifyWorkflow.displayName');
	}

	/**
	 * @copydoc PKPPlugin::getDescription()
	 */
	function getDescription() {
		return __('plugins.generic.simplifyWorkflow.description');
	}

	/**
	 * @copydoc PKPPlugin::getTemplatePath
	 */
	function getTemplatePath() {
		return parent::getTemplatePath() . 'templates/';
	}

}

?>
