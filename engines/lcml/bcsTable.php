<?  
    require_once('error_log.php');

    class bcsTable
    {
        var $table_data;
        var $row_spans;
        var $col_spans;
        var $heads;
        var $rows;
        var $cols;
        var $row;
        var $col;

        var $table_width;
        var $table_border;

        function rewind()
        {
            $this->row = $this->col = 0;
        }

        function bcsTable()
        {
            $this->data      = array();
            $this->row_spans = array();
            $this->col_spans = array();
            $this->heads     = array();
            $this->rows = $this->cols = 0;
            $this->rewind();

            $this->table_width = '';
            $this->table_border = " class=\"btab\" cellSpacing=\"0\"";
        }

        function table_width($width)
        {
            $this->table_width = " width=\"$width\" style=\"width: $width\"";
        }

        function set_max()
        {
            if($this->col >= $this->cols)
            { 
//                if($this->row > 0)
//                    error_log_translate_warning(__FILE__."[".__LINE__."] Cols (".($this->col+1).") in line {$this->row} greater then maximum in one of previous lines ({$this->cols})!<br />");
       
	            $this->cols = $this->col + 1;

                if($this->rows == 0)
                    $this->rows = 1;
            }
            
            if($this->row >= $this->rows)
                $this->rows = $this->row + 1;
        }

        function next_col()
        {
			if(@$this->col_spans[$this->row][$this->col] > 1)
	            $this->col += $this->col_spans[$this->row][$this->col];
			else
    	        $this->col++;
        }

        function new_row()
        {
            $this->set_max();
//            echo "{$this->row} {$this->col} {$this->cols}\n";
//            if($this->row > 0 && $this->col != $this->cols-1)
//              error_log_translate_warning(__FILE__."[".__LINE__."] Cols ({$this->col}) in line {$this->row} less then maximum in one of previous lines ({$this->cols})!<br />");
            $this->col = 0;
            $this->row++;
        }

        function setData($data)
        {
            $this->set_max();
            $this->data[$this->row][$this->col] = $data;
        }

        function append($data)
        {
            $this->setData($data);
            $this->next_col();
        }

        function setColSpan($col_span)
        {
            $this->col_spans[$this->row][$this->col] = $col_span;
        }

        function setRowSpan($row_span)
        {
            $this->row_spans[$this->row][$this->col] = $row_span;
			for($i=1; $i<=$row_span; $i++)
				$this->row_spans[$this->row+$i][$this->col] = -1;
        }

        function setHead($head_bit=1)
        {
            $this->heads[$this->row][$this->col] = $head_bit;
        }

        function get_html()
        {
            $out = "<table{$this->table_width}{$this->table_border}>\n";
            for($r=0; $r < $this->rows; $r++)
            {
                $out .= "<tr>";
                for($c=0; $c < $this->cols-1; $c+=@$this->col_spans[$r][$c] > 1 ? $this->col_spans[$r][$c] : 1)
                {
					if(@$this->row_spans[$r][$c] < 0)
						continue;
						
					$data = empty($this->data[$r][$c]) ? '&nbsp;' : $this->data[$r][$c];
                    $tx = !empty($this->heads[$r][$c]) ? 'th' : 'td';
					$colspan = @$this->col_spans[$r][$c] > 1 ? " colSpan=\"".$this->col_spans[$r][$c]."\"" : "";
					$rowspan = @$this->row_spans[$r][$c] > 1 ? " rowSpan=\"".$this->row_spans[$r][$c]."\"" : "";
                    $out .= "<{$tx}{$colspan}{$rowspan}>{$data}</{$tx}>";
                }
                $out .= "</tr>\n";
            }
            $out .= "</table>\n";
            return $out;
        }
    }
