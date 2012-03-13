<?php

class bors_file_type extends bors_list
{
	function file() { return $this->id(); }
	function mime()
	{
		if(is_object($this->file()))
			return $this->file()->mime();

		if(function_exists('mime_content_type'))
			return @mime_content_type($this->file());

		return NULL;
	}

	function extension()
	{
		if(is_object($f = $this->file()))
			return bors_lower(array_pop(explode('.', $f->full_file_name())));

		return bors_lower(array_pop(explode('.', $f)));
	}

	function name()
	{
//		echo "mime({$this->file()})={$this->mime()}<br/>\n";
		switch($this->mime())
		{
			case 'application/pdf':
				return 'PDF';
			case 'application/msword':
			case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
				return 'DOC';
			case 'application/octet-stream':
			case 'application/download':
			case 'application/x-www-form-urlencoded':
				return $this->name_by_ext();
			case 'text/plain':
				return 'TEXT';
			default:
				if(!($mime = $this->mime()))
					return bors_upper($this->extension());
				list($foo, $type) = explode('/', $mime);
				return bors_upper($type);
		}
	}

	function name_by_ext()
	{
		switch($this->extension())
		{
			case 'docx':
			case 'doc':
				return 'DOC';
			case 'rtf':
				return 'RTF';
			case 'pdf':
				return 'PDF';
		}

		return '';
	}

	function title()
	{
		switch($this->name())
		{
			case 'PDF':
			case 'RTF':
				return ec('Документ ').$this->name();
			case 'DOC':
				return ec('Документ MS Word');
			case 'JPEG':
				return ec('Изображение JPEG');
		}

		return ec('Файл ').$this->name();
	}

	function icon($type = NULL)
	{
		if(!$type)
			$type = bors_lower($this->name());

		if(file_exists(BORS_CORE.'/shared'.($f = "/i16/file-types/$type.png")))
			return bors_image_file::load('/_bors'.$f);

		switch($this->name())
		{
			case 'DOC':
				return bors_image_file::load('/_bors/i16/file-types/doc.png');
		}

		return bors_image_file::load('/_bors/i16/file-types/other.png');
	}

	function css_class($type = NULL)
	{
		if(!$type)
			$type = bors_lower($this->name());

		if(file_exists(BORS_CORE."/shared/i16/file-types/$type.png"))
			return "file-{$type}";

		if(file_exists(BORS_EXT."/htdocs/_bors-ext/i16/file-types/$type.png"))
			return "file-{$type}";

		switch($this->name())
		{
			case 'DOC':
				return 'file-doc';
		}

		return 'file-other';
	}
}
