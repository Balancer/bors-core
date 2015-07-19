<?php

class Array2XMLXW
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
	                $key = blib_grammar::singular($parent);
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
