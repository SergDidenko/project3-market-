<?php

class Pager
{
	private $page;
	private $max;
	private $start;
	private $link;
	private $total;

	function __construct($total, $page, $max=3, $link=5)
	{
		$this->total=$total;
		$this->page=$page;
		$this->max=$max;
		$this->link=$link;
	}
	public function getLinks()
	{
		if ($this->total == 0) {
			return null;
		}
		$start=$this->getStart();
		for ($i=0; $i < $this->getMaxpage(); $i++) { 
			$pagesArr[$i+1]=$i*$this->max;
		}
		$allPages=array_chunk($pagesArr, $this->link, true);
		$needChunk=$this->searchPage($allPages, $start);
		foreach ($allPages[$needChunk] as $pages => $offset) {
			$links[]=$pages;
		}
		return $links;
	}
	public function getStart()
	{
		return $this->start=($this->page - 1) * $this->max;
	}
	public function getMaxpage()
	{
		return ceil($this->total / $this->max);
	}
	public function getMax(){
		return $this->max;
	}
	private function searchPage($allPages, $start)
	{
		foreach ($allPages as $chunk => $pages) {
			if (in_array($start, $pages)) {
				return $chunk;
			}
		}
		return 0;
	}
}