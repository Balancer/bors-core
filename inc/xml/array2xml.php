<?php

require_once('../strings.php'); // нужно только для bors_unplural

function array2xml($data, $root = NULL)
{
	$converter = new Array2XML();
	if($root)
		$converter->setRootName($root);
	return $converter->convert($data);
}

class Array2XML
{
    private $writer;
    private $version = '1.0';
    private $encoding = 'UTF-8';
    private $rootName = 'root';

    function __construct()
    {
		$this->writer = new XMLWriter();
    }

    public function convert($data)
    {
        $this->writer->openMemory();
        $this->writer->startDocument($this->version, $this->encoding);
        $this->writer->startElement($this->rootName);
		$this->writer->setIndent(true);

        if(is_array($data))
            $this->getXML($data);

        $this->writer->endElement();
        return $this->writer->outputMemory();
    }

    public function setVersion($version)   { $this->version = $version; }
    public function setEncoding($encoding) { $this->encoding = $encoding; }
    public function setRootName($rootName) { $this->rootName = $rootName; }

    private function getXML($data, $parent = NULL)
    {
        foreach($data as $key => $val)
        {
            if(is_numeric($key))
            {
            	if($parent)
	                $key = bors_unplural($parent);
            	else
	                $key = 'key'.$key;
            }

            if(is_array($val))
            {
                $this->writer->startElement($key);
                $this->getXML($val, $key);
                $this->writer->endElement();
            }
            else
                $this->writer->writeElement($key, $val);
        }
	}
}
