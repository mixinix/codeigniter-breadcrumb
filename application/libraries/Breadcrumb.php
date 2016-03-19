<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @name        Codeigniter Breadcrumb
 * @description Breadcrumb PHP Class, Breadcrumb Codeigniter Library
 * @author      Mixinix
 * @link        http://github.com/mixinix
 * @license     MIT License Copyright (c) 2016 Mixinix
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

Class Breadcrumb {
	
	private $template = [], $html = '', $data = [], $parser = false, $insert = [], $replace = [], $remove = [];
	private $search = ['{uri}', '{title}', '{icon}'];
	//public $config = [], $prefix = '', $suffix = '';
	
	public function __construct($parser = false)
	{
		$this->parser = $parser;
	}

	public function delimiters($prefix = '', $suffix = '')
	{
		$this->prefix = $prefix;
		$this->suffix = $suffix;

		return $this;
	}
	
	public function add($arr = [])
	{
		if(is_array($arr))
		{
			$this->data[] = $arr;
		}
		else
		{
			$this->data[] = func_get_args();
		}

		return $this;
	}
	
	public function replace($pos, $range, $arr = [])
	{
		if(is_array($arr))
		{
			$this->replace[] = ['pos' => (int) $pos, 'range' => (int) $range, 'data' => $arr];
		}
		else
		{
			$arr = func_get_args();
			$pos = $arr[0];
			$range = $arr[1];
			array_shift($arr);
			array_shift($arr);
			$this->replace[] = ['pos' => (int) $pos, 'range' => (int) $range, 'data' => $arr];
		}

		return $this;
	}
	
	public function insert($pos, $arr = [])
	{
		if(is_array($arr))
		{
			$this->insert[] = ['pos' => (int) $pos, 'data' => $arr];
		}
		else
		{
			$arr = func_get_args();
			$pos = $arr[0];
			array_shift($arr);
			$this->insert[] = ['pos' => (int) $pos, 'data' => $arr];
		}

		return $this;
	}

	public function remove($pos)
	{
		if(is_array($pos))
		{
			$this->remove = $pos;
		}
		else
		{
			$this->remove = func_get_args();
		}

		return $this;
	}

	private function calculate()
	{
		if(count($this->insert) != 0)
		{
			foreach($this->insert as $k => $v)
			{
				array_splice($this->data, $v['pos'], 0, array($v['data']));
			}
		}
		
		if(count($this->replace) != 0)
		{
			foreach($this->replace as $k => $v)
			{
				array_splice($this->data, $v['pos'], $v['range'], array($v['data']));
			}
		}
		
		if(count($this->remove) != 0)
		{
			foreach($this->remove as $k => $v)
			{
				if($v == -1)
				{
					array_pop($this->data);
				}
				else
				{
					unset($this->data[$v]);
				}
			}
		}
		
		return $this->data;
	}
	
	private function build($arr = [])
	{
		$html = [];

		foreach($arr as $k => $v)
		{
			if(count($v) == 1)
			{
				$html[] = '<li class="active">'.$v[0].'</li>';
			}
			else if(count($v) == 3)
			{
				$html[] = '<li><span class="'.$v[2].'"></span>&nbsp;<a href="'.$v[0].'">'.$v[1].'</a></li>';
			}
			else
			{
				$html[] = '<li><a href="'.$v[0].'">'.$v[1].'</a></li>';
			}
		}

		$html = implode("", $html);

		return $html;
	}
	
	public function template($arr = [])
	{
		$this->template = $arr;

		return $this;
	}

	private function buildTemplate($length = 1)
	{
		$tpl = [];
		foreach($this->template as $k => $v)
		{
			if(count($v) == $length)
			{
				$tpl[$length] = ['tpl' => $k, 'search' => $v];
			}
		}

		return $tpl[$length];
	}
	
	public function get()
	{
		$output = '';
		if(count($this->template) != 0)
		{
			$str = '';

			foreach($this->calculate() as $k => $v)
			{
				if(count($v) == 1)
				{
					$str .= str_replace($this->buildTemplate(1)['search'], $v, $this->buildTemplate(1)['tpl']);
				}
				else if(count($v) == 2)
				{
					$str .= str_replace($this->buildTemplate(2)['search'], $v, $this->buildTemplate(2)['tpl']);
				}
				else if(count($v) == 3)
				{
					$str .= str_replace($this->buildTemplate(3)['search'], $v, $this->buildTemplate(3)['tpl']);
				}
				else
				{
					$str .= str_replace($this->buildTemplate(4)['search'], $v, $this->buildTemplate(4)['tpl']);
				}
			}
			$output = $str;
		}
		else
		{
			$output = $this->build($this->calculate());
		}
		if(isset($this->suffix))
			return $this->prefix."\n".$output."\n".$this->suffix;
		else
			return $output;
	}
	
	/*
	** Global Parser
	*/
	public function parser($str = null)
	{
		if(!is_null($str))
		{
			$this->html = $str;
		}
		
		$html = [];
		
		foreach($this->calculate() as $key => $v)
		{
			$html[] = str_replace($this->search, $v, $this->html);
		}

		$html = implode("\n", $html);

		if($this->parser)
		{
			return $html;
		}
	}
	
	public function search($arr)
	{
		if(is_array($arr))
		{
			$this->search = $arr;
		}
		else
		{
			$this->search = func_get_args();
		}

		return $this;
	}

	public function html($html)
	{
		$this->html = $html;

		return $this;
	}
}
