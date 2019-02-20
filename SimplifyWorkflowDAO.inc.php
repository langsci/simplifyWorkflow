<?php

/**
 * @file plugins/generic/simplifyWorkflow/SimplifyWorkflowDAO.inc.php
 *
 * Copyright (c) 2016 Language Science Press
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SimplifyWorkflowDAO
 *
 */

class SimplifyWorkflowDAO extends DAO {
	/**
	 * Constructor
	 */
	function SimplifyWorkflowDAO() {
		parent::DAO();
	}

	function setTermsToOpenAcess(){
		$this->update("UPDATE submission_files SET sales_type='openAccess'");
		$this->update("UPDATE submission_files SET direct_sales_price=0");
	}


	function assignParticipant($submissionId,$userGroupId,$userId) {

		// only assign participant if he/she is not already assigned
		$result = $this->retrieve(
			"SELECT  * FROM stage_assignments WHERE submission_id=".$submissionId." AND user_group_id=".$userGroupId." AND user_id=".$userId
		);

		if ($result->RecordCount() == 0) {
			$result->Close();
			$this->update('insert into stage_assignments (submission_id, user_group_id, user_id, date_assigned)
				values('.$submissionId.','.$userGroupId.','.$userId.',NOW())'
			);
			return true;
		} else {
			return false;
		}
	}

	function getRoleId($roleName,$contextId) {

		$result = $this->retrieve(
			'SELECT a.user_group_id FROM user_group_settings a LEFT JOIN user_groups b ON a.user_group_id=b.user_group_id WHERE a.locale="en_US" AND a.setting_name="name" AND b.context_id='.$contextId.' AND a.setting_value="'.$roleName.'"'
		);

		if ($result->RecordCount() == 0) {
			$result->Close();
			return null;
		} else {
			$row = $result->getRowAssoc(false);
			$userGroupId = $this->convertFromDB($row['user_group_id'],null);				 
			$result->Close();
			return $userGroupId;
		}
	}


	function getSeriesEditors($submissionId,$contextId) {

		$result = $this->retrieve(
			'SELECT user_id FROM section_editors
			 WHERE context_id='.$contextId.' AND
			 section_id = (SELECT series_id FROM submissions
			 WHERE submission_id='.$submissionId.');'
		);

		if ($result->RecordCount() == 0) {
			$result->Close();
			return null;
		} else {
			$rownr=0;
			$users = array();
			while (!$result->EOF) {
				$row = $result->getRowAssoc(false);
				$users[$rownr] = $this->convertFromDB($row['user_id'],null);
				$rownr = $rownr + 1;				 
				$result->MoveNext();
			}
			$result->Close();
			return $users;
		}
	}

	function getPressManagers($contextId) {

		$result = $this->retrieve(
			'SELECT user_id FROM user_user_groups WHERE user_group_id IN (SELECT a.user_group_id FROM user_group_settings a LEFT JOIN user_groups b ON a.user_group_id=b.user_group_id WHERE a.locale="en_US" AND a.setting_name="name" AND b.context_id=1 AND a.setting_value="Press Manager");'
		);

		if ($result->RecordCount() == 0) {
			$result->Close();
			return null;
		} else {
			$rownr=0;
			$users = array();
			while (!$result->EOF) {
				$row = $result->getRowAssoc(false);
				$users[$rownr] = $this->convertFromDB($row['user_id'],null);
				$rownr = $rownr + 1;				 
				$result->MoveNext();
			}
			$result->Close();
			return $users;
		}
	}

	// add publication format: PDF, digital, physical_format
	// add PDF and Bibliography and 20 chapters for Edited Volumes
	function addStandardValuesAfterSubmit($submission_id) {

		// is the submission an edited volume?
		$editedVolume = 2;
		$result = $this->retrieve(
			'SELECT edited_volume from submissions
			 WHERE submission_id = '.$submission_id
		);
		if ($result->RecordCount() == 0) {
			$result->Close();
			return null;
		} else {
			$row = $result->getRowAssoc(false);
			$editedVolume = $this->convertFromDB($row['edited_volume'],null);
			$result->Close();
		}

		// insert digital publication formats for the submission
		$numberOfDigitalFormats = 2;
		if ($editedVolume==1) {
			$numberOfDigitalFormats = 22;
		}

		for ($i=0; $i<$numberOfDigitalFormats; $i++) {
			$this->update('INSERT INTO publication_formats(submission_id, physical_format, entry_key,
						   product_composition_code,is_available,imprint)
						   VALUES('.$submission_id.',0, "DA","00",1,"Language Science Press")');			
		}

		// insert 1 hardcover format for the submission
		$this->update('INSERT INTO publication_formats(submission_id, physical_format, entry_key,
						   product_composition_code,is_available,imprint)
						   VALUES('.$submission_id.',1, "BB","00",1,"Language Science Press")');

		// insert 2 softcover formats for the submission
		for ($i=0; $i<2; $i++) {
		$this->update('INSERT INTO publication_formats(submission_id, physical_format, entry_key,
						   product_composition_code,is_available,imprint)
						   VALUES('.$submission_id.',1, "BC","00",1,"Language Science Press")');
		}

		// get publication format ids of that submission
		$results = $this->retrieve(
			'SELECT publication_format_id FROM publication_formats
			 WHERE submission_id = '.$submission_id .' order by publication_format_id'
		);
		$publicationFormatIds = array();
		if ($results->RecordCount() == 0) {
			$results->Close();
			return null;
		} else {
			while (!$results->EOF) {
				$row = $results->getRowAssoc(false);
				$publicationFormatIds[] = $this->convertFromDB($row['publication_format_id'],null);
				$results->MoveNext();
			}
			$results->Close();
		}

		// add names to the digital publication formats (table publication_format_settings);
		$this->update("INSERT INTO publication_format_settings
				VALUES(".$publicationFormatIds[0].",'en_US','name','PDF','string')");
		$this->update("INSERT INTO publication_format_settings
				VALUES(".$publicationFormatIds[1].",'en_US','name','Bibliography','string')");

		if ($editedVolume==1) {
			for ($chapter = 0; $chapter < 20; $chapter++) {
				$chapterNr = $chapter+1;
				$chapterName = 'Chapter '.$chapterNr;
				$this->update("INSERT INTO publication_format_settings
					VALUES(".$publicationFormatIds[2+$chapter].",'en_US','name','".$chapterName."','string')");
			}
		}

		// add names to the print publication formats
		$this->update("INSERT INTO publication_format_settings
				VALUES(".$publicationFormatIds[$numberOfDigitalFormats].",'en_US','name','Buy from amazon.de','string')");
		$this->update("INSERT INTO publication_format_settings
				VALUES(".$publicationFormatIds[$numberOfDigitalFormats+1].",'en_US','name','Buy from amazon.co.uk','string')");
		$this->update("INSERT INTO publication_format_settings
				VALUES(".$publicationFormatIds[$numberOfDigitalFormats+2].",'en_US','name','Buy from amazon.com','string')");

	}
}

?>
