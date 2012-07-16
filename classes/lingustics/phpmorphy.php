<?php

define('PHPMORPHY_PATH', 'linguistics/phpmorphy-0.3.7');
define('PHPMORPHY_DICT_PATH', BORS_3RD_PARTY.'/linguistics/morphy-0.3.x-ru_RU-withjo-utf-8');

/**
	Морфология русских слов
	http://phpmorphy.sourceforge.net/
*/

class lingustics_phpmorphy
{
	var $morphy = NULL;

	function factory()
	{
		$instance = new lingustics_phpmorphy(NULL);

		require_once(PHPMORPHY_PATH.'/src/common.php');
		$opts = array(
			// storage type, follow types supported
			// PHPMORPHY_STORAGE_FILE - use file operations(fread, fseek) for dictionary access, this is very slow...
			// PHPMORPHY_STORAGE_SHM - load dictionary in shared memory(using shmop php extension), this is preferred mode
			// PHPMORPHY_STORAGE_MEM - load dict to memory each time when phpMorphy intialized, this useful when shmop ext. not activated. Speed same as for PHPMORPHY_STORAGE_SHM type
			'storage' => PHPMORPHY_STORAGE_FILE,
			// Extend graminfo for getAllFormsWithGramInfo method call
			'with_gramtab' => false,
			// Enable prediction by suffix
			'predict_by_suffix' => true, 
			// Enable prediction by prefix
			'predict_by_db' => true
		);

		// Path to directory where dictionaries located

		$dict_bundle = new phpMorphy_FilesBundle(PHPMORPHY_DICT_PATH, 'rus');

		// Create phpMorphy instance
		try
		{
			$instance->morphy = new phpMorphy($dict_bundle, $opts);
		}
		catch(phpMorphy_Exception $e)
		{
			bors_throw('Error occured while creating phpMorphy instance: ' . $e->getMessage());
		}

		return $instance;
	}

	function base_form($word)
	{
		return $this->morphy->getBaseForm(bors_upper($word));
	}

	function all_forms($word)
	{
		return $this->morphy->getAllForms(bors_upper($word));
	}

	function find_word($word)
	{
		return $this->morphy->findWord(bors_upper($word));
	}

	function word_form_by_grammems($paradigms, $case)
	{
		foreach($paradigms as $paradigm)
		{
			$forms = $paradigm->getWordFormsByGrammems($case); // 'ИМ'
			foreach($forms as $form)
				return $form->getWord();
		}

		return NULL;
	}
}
