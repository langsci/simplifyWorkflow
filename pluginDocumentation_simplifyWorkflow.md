Key data
============

- name of the plugin: Simplify Workflow Plugin
- author: Carola Fanselow
- current version: 1.0.0.0
- tested on OMP version: 1.2.0
- github link: https://github.com/langsci/simplifyWorkflow.git
- community plugin: no
- date: 2016/05/20

Description
============

This plugin implements a number of smaller changes to adapt OMP's workflow to the workflow of Language Science Press. The adaptions are highly specific to Language Science Press and can thus not be used by other installations without major adaptations. For a detailed documenation of the changes made to OMP see the Langsci wiki page.

Implementation
================

Hooks
-----
- used hooks: 9

		LoadHandler
		TemplateManager::display
		TemplateManager::include
		addparticipantform::Constructor
		submissionfilesuploadform::Constructor
		catalogentrycatalogmetadataform::Constructor
		catalogentryformatmetadataform::Constructor
		eventlogdao::_insertobject
		submissionsubmitstep3form::validate

New pages
------
- new pages: 0

Templates
---------
- templates that replace other templates: 8

		workflowModified.tpl replaces workflow/workflow.tpl
		authorDashboardModified.tpl replaces authorDashboard/authorDashboard.tpl
		coreStep1Modified.tpl replaces core:submission/form/step1.tpl
		categoriesModified.tpl replaces submission/form/categories.tpl
		seriesModified.tpl replaces submission/form/series.tpl
		addParticipantFormModified.tpl replaces lib/pkp/templates/controllers/grid/users/stageParticipant/addParticipantFormModified.tpl
		fileUploadFormModified.tpl replaces lib/pkp/templates/controllers/wizard/fileUpload/form/fileUploadForm.tpl
		catalogMetadataFormFieldsModified.tpl replaces templates/controllers/tab/catalogEntry/form/catalogMetadataFormFields.tpl

- templates that are modified with template hooks: 0
- new/additional templates: 0

Database access, server access
-----------------------------
- reading access to OMP tables: 8

		stage_assignments
		submissions
		user_user_groups
		user_group_settings
		user_groups
		section_editors
		submission_files
		publication_formats

- writing access to OMP tables: 4

		submission_files
		stage_assignments
		publication_format_settings
		publication_formats

- new tables: 0
- nonrecurring server access: no
- recurring server access: no
 
Classes, plugins, external software
-----------------------
- OMP classes used (php): 6
	
		GenericPlugin
		TemplateManager
		DAO
		StageParticipantGridHandler
		CatalogEntryTabHandler
		CategoriesListbuilderHandler

- OMP classes used (js, jqeury, ajax): 6

		AddParticipantFormHandler
		CatalogMetadataFormHandler
		AjaxFormHandler
		FileUploadFormHandler
		PublicationFormatMetadataFormHandler
		WorkflowHandler

- necessary plugins: 0
- optional plugins: 0
- use of external software: no
- file upload: no
 
Metrics
--------
- number of files: 18
- lines of code: 1595

Settings
--------
- settings: no

Plugin category
----------
- plugin category: generic

Other
=============
- does using the plugin require special (background)-knowledge?: yes, knowledge of the Langsci workflow and press manager's preferences
- access restrictions: no
- adds css: yes (to hide a number of sections in the workflow)


