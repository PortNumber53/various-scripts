<?php

class ListNode
{
	public $data;
	public $next;

	public function __construct($data) {
		$this->data = $data;
		$this->next = null;
	}
}


class SinglyLinkedList
{
	public $root_node;
	public $count;

	public function __construct()
	{
		$this->root_node = null;
		$this->count = 0;
	}

	public function addNode($data)
	{
		if ($this->root_node === null) {
			$this->root_node = new ListNode($data);
			$this->count = 1;
		} else {
			$node = new ListNode($data);
			$current = $this->root_node;
			while ($current->next !== null) {
				$current = $current->next;
			}
			$current->next = &$node;
		}
	}

	public function getCount()
	{
		return $this->count;
	}

	public function getAll()
	{
		if ($this->root_node !== null) {
			$current = $this->root_node;
			while ($current !== null) {
				echo str_pad($current->data, 5, ' ', STR_PAD_LEFT);
				$current = $current->next;
			}
			echo "  Done\n";
		} else {
			echo "List is empty\n";
		}
	}

	public function reverseList()
	{
		$current = $this->root_node;
		$new = null;

		while ($current !== null) {
			$tmp = $current;  // remember this
			$current = $current->next; // walk the list
			$tmp->next = $new;  // "next" is the previous node
			$new = $tmp; // move auxiliary pointer
		}
		$this->root_node = $tmp;
	}

}
