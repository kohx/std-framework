<?php

/**
 * Paginate
 *
 * {{#archive.paginate : paginate}}
 * <p>
 * 	{{#paginate.first_page : first_page}}
 * 	{{#first_page.current}}<span>&Lt;</span>{{/#}}
 * 	{{^first_page.current}}<a href="{{first_page.url}}" title="{{first_page.index}}">&Lt;</a>{{/^}}
 * 	{{/#}}
 *
 * 	{{#paginate.prev_page : prev_page}}
 * 		{{#prev_page.current}}<span>&lt;</span>{{/#}}
 * 		{{^prev_page.current}}<a href="{{prev_page.url}}" title="{{prev_page.index}}">&lt;</a>{{/^}}
 * 	{{/#}}
 *
 * 	{{*paginate.pages : page}}
 * 		{{#page.current}}<span>{{page.index}}</span>{{/#}}
 * 		{{^page.current}}<a href="{{page.url}}" title="{{page.index}}">{{page.index}}</a>{{/^}}
 * 	{{/*}}
 *
 * 	{{#paginate.next_page : next_page}}
 * 		{{#next_page.current}}<span>&gt;</span>{{/#}}
 * 		{{^next_page.current}}<a href="{{next_page.url}}" title="{{next_page.index}}">&gt;</a>{{/^}}
 * 	{{/#}}
 *
 * 	{{#paginate.last_page : last_page}}
 * 		{{#last_page.current}}<span>&Gt;</span>{{/#}}
 * 		{{^last_page.current}}<a href="{{last_page.url}}" title="{{last_page.index}}">&Gt;</a>{{/^}}
 * 	{{/#}}
 * </p>
 * {{/#}}
 *
 * OR
 *
 * {{#archive.paginate : paginate}}
 * <p>
 *  ---
 * 	{{#paginate.follow_pre}}...{{/#}}
 * 	{{*paginate.follow_pages : page}}
 * 		{{#page.current}}<span>{{page.index}}</span>{{/#}}
 * 		{{^page.current}}<a href="{{page.url}}" title="{{page.index}}">{{page.index}}</a>{{/^}}
 * 	{{/*}}
 * 	{{#paginate.follow_suf}}...{{/#}}
 * 	---
 * </p>
 * {{/#}}
 *
 *
 * @package    Kohana/Paginate
 * @category
 * @author     kohx
 * @copyright  (c) 2013 kohx
 * @license
 */
require_once 'AutoLoader.php';

class Paginate {

	public $key = 'page';
	public $current_page = 1;
	public $total_items = 0;
	public $items_per_page = 10;
	public $total_pages;
	public $current_first_item;
	public $current_last_item;
	public $first_page;
	public $prev_page;
	public $next_page;
	public $last_page;
	public $offset;
	public $limit;
	public $pages = array();
	public $exist = FALSE;
	public $follow = 2;
	public $follow_pre = FALSE;
	public $follow_suf = FALSE;
	public $follow_pages = array();

	/**
	 * Creates a new Pagination object.
	 *
	 * $paginate = Pgn::factory(array(
	 * 		'total_items' => $total_items,
	 * 		'items_per_page' => $items_per_page,
	 * 		'follow' => $follow,
	 * 	));
	 *
	 * @param   array  configuration
	 * @return  Pagination
	 */
	public static function fact(array $config = array())
	{
		return new Paginate($config);
	}

	/**
	 * Creates a new Pagination object.
	 *
	 * @param   array  configuration
	 * @return  void
	 */
	public function __construct(array $config = array())
	{
		$config_file = Kohana::$config->load('paginate');

		$this->key = Arr::get($config_file, 'key', $this->key);

		$this->items_per_page = Arr::get($config_file, 'items_per_page', $this->items_per_page);
		$this->follow = Arr::get($config_file, 'follow', $this->follow);

		$this->total_items = Arr::get($config, 'total_items', $this->total_items);
		$this->items_per_page = Arr::get($config, 'items_per_page', $this->items_per_page);
		$this->follow = Arr::get($config, 'follow', $this->follow);

		$this->setup();
	}

	/**
	 * Loads configuration settings into the object and (re)calculates pagination if needed.
	 * Allows you to update config settings after a Pagination object has been constructed.
	 *
	 * @param   array   configuration
	 * @return  object  Pagination
	 */
	public function setup()
	{
		// Curent page
		$this->current_page = (int) Arr::get(Request::current()->query(), $this->key, 1);

		// param
		$this->total_pages = (int) ceil($this->total_items / $this->items_per_page);
		$this->current_first_item = (int) min((($this->current_page - 1) * $this->items_per_page) + 1, $this->total_items);
		$this->current_last_item = (int) min($this->current_first_item + $this->items_per_page - 1, $this->total_items);
		$this->offset = (int) (($this->current_page - 1) * $this->items_per_page);
		$this->limit = (int) (($this->offset + $this->items_per_page) > $this->total_items) ? ($this->total_items - $this->offset) : ($this->offset + $this->items_per_page);

		// First page
		$this->first_page = array(
			'index' => 1,
			'url' => $this->url(1),
			'current' => (bool) ($this->current_page === 1),
		);

		// Prev page
		$this->prev_page = array(
			'index' => $this->current_page - 1,
			'url' => $this->url($this->current_page - 1),
			'current' => (bool) ($this->current_page <= 1),
		);

		// Next page
		$this->next_page = array(
			'index' => $this->current_page + 1,
			'url' => $this->url($this->current_page + 1),
			'current' => (bool) ($this->current_page >= $this->total_pages),
		);

		// Last page
		$this->last_page = array(
			'index' => $this->total_pages,
			'url' => $this->url($this->total_pages),
			'current' => (bool) ($this->current_page === $this->total_pages)
		);

		// Pages
		for ($i = 0; $i < $this->total_pages; $i++)
		{
			$index = $i + 1;
			$this->pages[$i] = array(
				'index' => $index,
				'url' => $this->url($index),
				'current' => (bool) ($this->current_page == $index)
			);
		}

		// Follow pages
		$show = ($this->follow * 2) + 1;

		if ($this->total_pages > $show)
		{
			$start = 1 + $this->follow;
			$end = $this->total_pages - $this->follow;

			if ($this->current_page <= $start)
			{
				$offset = 1;
				$this->follow_suf = TRUE;
			}
			elseif ($this->current_page >= $end)
			{
				$offset = $end - $this->follow;
				$this->follow_pre = TRUE;
			}
			else
			{
				$offset = ($this->current_page - $this->follow);
				$this->follow_pre = TRUE;
				$this->follow_suf = TRUE;
			}

			$this->follow_pages = array_slice($this->pages, $offset - 1, $show);
		}
		else
		{
			$this->follow_pages = $this->pages;
		}

		// Exist
		$this->exist = (bool) ($this->total_pages > 1);

		return $this;
	}

	// Get URL
	public function url($index)
	{
		if ($index == 1)
		{
			$url = URL::site(Request::current()->uri()) . URL::query(array($this->key => NULL));
		}
		else
		{
			$url = URL::site(Request::current()->uri()) . URL::query(array($this->key => $index));
		}

		return $url;
	}

}

// End Pgt
