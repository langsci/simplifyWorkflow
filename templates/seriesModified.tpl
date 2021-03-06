{**
 * plugins/generic/simplifyWorkflow/templates/seriesModified.tpl
 *
 * Copyright (c) 2016 Language Science Press
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Include series placement for submissions.
 *}
{if count($seriesOptions) > 1} {* only display the series picker if there are series configured for this press *}
	{fbvFormSection label="series.series"}
		{**{fbvElement type="select" id="seriesId" from=$seriesOptions required=true selected=$seriesId translate=false disabled=$readOnly size=$fbvStyles.size.SMALL}**}
		{fbvElement type="select" id="seriesId" required=true defaultLabel="" defaultValue="" from=$seriesOptions selected=$seriesId translate=false disabled=$readOnly size=$fbvStyles.size.SMALL}
	{/fbvFormSection}



	{if $includeSeriesPosition}
		{fbvFormSection label="submission.submit.seriesPosition"}
			{fbvElement type="text" id="seriesPosition" name="seriesPosition" label="submission.submit.seriesPosition.description" value=$seriesPosition maxlength="255" disabled=$readOnly}
		{/fbvFormSection}
	{/if}
{/if}
