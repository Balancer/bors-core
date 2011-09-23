<?php

class bors_file_ini
{
	// Запись массива с секциями
	static function write_sections($file, $data)
	{
		$content = "";
		foreach($data as $section => $section_data)
		{
			$content .= "[".$section."]\n";
			foreach($section_data as $key => $value)
			{
				if(is_array($value))
				{
					for($i=0; $i<count($value); $i++)
						$content .= $key."[] = \"".addslashes($value[$i])."\"\n";
				}
				elseif($value)
					$content .= $key." = \"".addslashes($value)."\"\n";
				else
					$content .= $key." = \n";
			}
		}

		file_put_contents($file, $content);
	}

	// Запись массива без секций
	static function write($file, $data)
	{
		$content = "";
		foreach($data as $key => $value)
		{
			if(is_array($value))
			{
				for($i=0; $i<count($value); $i++)
					$content .= $key."[] = \"".addslashes($value[$i])."\"\n";
			}
			elseif($value)
				$content .= $key." = \"".addslashes($value)."\"\n";
			else
				$content .= $key." = \n";
		}

		file_put_contents($file, $content);
	}
}
