<?php

require_once(dirname(__FILE__).'/../strings.php'); // нужно только для bors_unplural

/** Вариант преобразования массива в XML с приоритетом параметров как атрибутов тэгов:

$data = array(
	'orders' => array(
		array('dealer_id' => 20, 'item_id' => array(
			array('amount' => 1, 'time' => '17.06.10', '_' => 99691),
			array('amount' => 2, 'time' => '25.06.10', '_' => 69639),
			array('amount' => 5, 'time' => '17.06.10', '_' => 101000),
		)),

		array('dealer_id' => 33, 'item_id' => array(
			array('amount' => 5, 'time' => '18.06.10', '_' => 89691),
			array('amount' => 7, 'time' => '19.06.10', '_' => 89639),
			array('amount' => 8, 'time' => '20.06.10', '_' => '<нету>'),
		)),
	),
);

преобразуется в

<?xml version="1.0" encoding="UTF-8"?>
<orders>
 <order dealer_id="20">
  <item_id>
   <item_id amount="1" time="17.06.10">99691</item_id>
   <item_id amount="2" time="25.06.10">69639</item_id>
   <item_id amount="5" time="17.06.10">101000</item_id>
  </item_id>
 </order>
 <order dealer_id="33">
  <item_id>
   <item_id amount="5" time="18.06.10">89691</item_id>
   <item_id amount="7" time="19.06.10">89639</item_id>
   <item_id amount="8" time="20.06.10"><![CDATA[<нету>]]></item_id>
  </item_id>
 </order>
</orders>

*/

if(class_exists('XMLWriter'))
{
	function array2xml_wp($data, $root = NULL)
	{
		if(is_null($root))
		{
			$root = @array_pop(array_keys($data));
			$data = $data[$root];
		}

		$converter = new Array2XMLWP();
		$converter->setRootName($root);

		return $converter->convert($data);
	}
}

class Array2XMLWP
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
    	if(is_null($parent))
    		$parent = $this->rootName;

		$cdata = NULL;

		foreach($data as $key => $val)
		{
            if(is_numeric($key))
            {
            	if($parent)
	                $key = bors_unplural($parent);
            	else
	                $key = 'key'.$key;

				$this->writer->startElement($key);
                $this->getXML($val, $key);
				$this->writer->endElement();

				continue;
            }

            if(is_array($val))
            {
                $this->getXML($val, $key);
            }
            else
            {
				if($key == '_')
					$cdata = $val;
				else
					$this->writer->writeAttribute($key, $val);
			}
        }

		if($cdata)
		{
			if(preg_match('/^[\w]+$/', $cdata))
                $this->writer->writeRaw($cdata);
			else
                $this->writer->writeCData($cdata);
		}
	}
}
