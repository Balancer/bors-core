<?php

//use HtmlObject;

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

		function __construct($args = array())
		{
			$this->layout = defval($args, 'layout');
//			var_dump($layout);

			$this->data	  = array();
			$this->row_spans = array();
			$this->col_spans = array();
			$this->heads	 = array();
			$this->rows = $this->cols = 0;
			$this->rewind();

			$this->table_width = '';
		}

		function table_width($width)
		{
			$this->table_width = $width;
		}

		function set_max()
		{
			if($this->col >= $this->cols)
			{
//				if($this->row > 0)
//					error_log_translate_warning(__FILE__."[".__LINE__."] Cols (".($this->col+1).") in line {$this->row} greater then maximum in one of previous lines ({$this->cols})!<br />");

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
//			echo "{$this->row} {$this->col} {$this->cols}\n";
//			if($this->row > 0 && $this->col != $this->cols-1)
//			  error_log_translate_warning(__FILE__."[".__LINE__."] Cols ({$this->col}) in line {$this->row} less then maximum in one of previous lines ({$this->cols})!<br />");
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
			for($i=1; $i<$row_span; $i++)
				$this->row_spans[$this->row+$i][$this->col] = -1;
		}

		function setHead($head_bit=1)
		{
			$this->heads[$this->row][$this->col] = $head_bit;
		}

		var $style = array();
		function addStyle($style) { $this->style[] = $style; }

		function get_html()
		{
//			$table = Table:: "<table{$this->table_width}{$this->table_border}>\n";
			$table = \HtmlObject\Element::table();
			if($w = $this->table_width)
			{
				$table->width($w);
				if(is_numeric($w))
					$w = "{$w}px";
				$this->addStyle("width:{$w}");
			}

			for($r=0; $r < $this->rows; $r++)
			{
				$tr = \HtmlObject\Element::tr();

				$rspans = 0;
				for($c=0; $c < $this->cols-1; $c++)
					if(@$this->row_spans[$r][$c]<0)
						$rspans++;

				for($c=0; $c < $this->cols-1-$rspans; $c += @$this->col_spans[$r][$c] > 1 ? $this->col_spans[$r][$c] : 1)
				{

//					echo "$r, $c: rss={$this->row_spans[$r][$c]} ";

//	Убрано из-за	http://balancer.ru/2007/12/10/post-1361199.html
//					if(@$this->row_spans[$r][$c] < 0)
//						continue;
// Тесты: http://balancer.ru/g/p2547411

					$data = @$this->data[$r][$c];
					if($data == '')
						$data = '&nbsp;';

					$tx = !empty($this->heads[$r][$c]) ? \HtmlObject\Element::th() : \HtmlObject\Element::td();
					if(@$this->col_spans[$r][$c] > 1)
						$tx->colspan($this->col_spans[$r][$c]);
					if(@$this->row_spans[$r][$c] > 1)
						$tx->rowspan($this->row_spans[$r][$c]);

					$tx->nest($data);

					$tr->nest($tx);
				}

				$table->nest($tr);
			}

			if($this->layout)
			{
				if($class = $this->layout->get('table_class'))
					$table->addClass($class);
//				else
//					$this->addstyle('border: 1px solid');
			}

			if($this->style)
				$table->style(join('; ', $this->style));

//			echo "<xmp>{$table}</xmp>";
			return $table;
		}
}
