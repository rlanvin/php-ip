<?php

class IPBlockIterator implements Iterator, Countable
{
	protected $position = 0;
	protected $current_block = null;

	protected $first_block = null;
	protected $number_of_blocks = 0;

	protected $class = '';

	public function __construct(IPBlock $first_block, $number_of_blocks)
	{
		$this->class = get_class($first_block);

		$this->first_block = $first_block;
		$this->number_of_blocks = $number_of_blocks;

		$this->position = 0;
		$this->current_block = $first_block;
	}

	public function count()
	{
		return $this->number_of_blocks;
	}

	public function rewind()
	{
		$this->position = 0;
		$this->current_block = $this->first_block;
	}

	public function current()
	{
		return $this->current_block;
	}

	public function key()
	{
		return $this->position;
	}

	public function next()
	{
		$this->position += 1;
		$this->current_block = new $this->class(
			$this->current_block->getLastIp()->plus(1),
			$this->current_block->getPrefix()
		);
	}

	public function valid()
	{
		return $this->position >= 0 && $this->position < $this->number_of_blocks;
	}
}