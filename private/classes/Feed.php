<?php

/**
 * RSS and Atom feed helper.
 *
 * @package    Deraemon/RSS
 * @category   Base
 * @author     kohx by Deraemons
 * @copyright  (c) 2015-2018 Deraemons
 * @license    http://emon-cms.com/license
 */
class Feed {

	/**
	 * Parses a remote feed into an array.
	 *
	 * @param   string  $feed   remote feed URL
	 * @param   integer $limit  item limit to fetch
	 * @return  array
	 */
	public static function parse($feed_url, $limit = 0)
	{
		// Check if SimpleXML is installed
		if (!function_exists('simplexml_load_file'))
		{
			throw new Exception('SimpleXML must be installed!');
		}

		$results = array();

		// Disable error reporting while opening the feed
		$error_level = error_reporting(0);

		// Get file contents
		$feed_xml = file_get_contents($feed_url);

		// Restore error reporting
		error_reporting($error_level);

		// Feed_xml could not be loaded
		if ($feed_xml === false)
		{
			return array();
		}

		$feed = simplexml_load_string($feed_xml, 'SimpleXMLElement', LIBXML_NOCDATA);

		// Feed could not be loaded
		if ($feed === false)
		{
			return array();
		}

		$namespaces = $feed->getNamespaces(TRUE);

		if (isset($feed->item) AND $feed->item)
		{
			$feed_items = $feed->item;
		}
		elseif (isset($feed->entry) AND $feed->entry)
		{
			$feed_items = $feed->entry;
		}
		elseif (isset($feed->channel->item) AND $feed->channel->item)
		{
			$feed_items = $feed->channel->item;
		}

		// itelate feed_items
		$i = 0;
		foreach ($feed_items as $feed_item)
		{
			if ($limit > 0 AND $i++ === $limit)
			{
				break;
			}

			$item_fields = (array) $feed_item;

			// get namespaced tags
			foreach ($namespaces as $ns)
			{
				$item_fields += (array) $feed_item->children($ns);
			}

			// Delete PR
			if (strpos($item_fields['title'], 'PR:') === FALSE)
			{
				$date = '';
				if (isset($item_fields['pubDate']))
				{
					$date = date('Y-m-d H:i:s', strtotime($item_fields['pubDate']));
				}
				elseif (isset($item_fields['date']))
				{
					$date = date('Y-m-d H:i:s', strtotime($item_fields['date']));
				}
				elseif (isset($item_fields['updated']))
				{
					$date = date('Y-m-d H:i:s', strtotime($item_fields['updated']));
				}

				$link = '';
				if (is_string($item_fields['link']))
				{
					$link = $item_fields['link'];
				}
				elseif (is_object($item_fields['link']))
				{
					$link = (string) $item_fields['link']->attributes()->href;
				}
				elseif (is_array($item_fields['link']))
				{
					foreach ($item_fields['link'] as $child)
					{
						if ((string) $child->attributes()->rel == 'alternate')
						{
							$link = (string) $child->attributes()->href;
						}
					}
				}

				// 内容を取得
				$default_description = '';
				if (isset($item_fields['description']))
				{
					$default_description = $item_fields['description'];
				}
				elseif (isset($item_fields['content']))
				{
					$default_description = $item_fields['content'];
				}

				// imageの取得
				$feed_images = array();
				preg_match_all('/<img .*?src ?= ?[\'"]([^>]+)[\'"].*?>/i', $default_description, $images);
				foreach ($images[0] as $image)
				{
					preg_match('/<img.*?src=(["\'])(.+?)\1.*?>/i', $image, $src);
					$url = $src[2];
					if (strpos($url, 'agoda') === FALSE
							AND strpos($url, 'gif') === FALSE
							AND strpos($url, 'blogmura.com') === FALSE
							AND strpos($url, 'rssad.jp') === FALSE
							AND strpos($url, 'amazon.com') === FALSE
					)
					{
						$feed_images[] = $url;
					}
				}

				// タグの削除
				$description = str_replace(array("\r\n", "\r", "\n"), '', strip_tags($default_description));

				$results[] = array(
					'title' => $item_fields['title'],
					'description' => $description,
					'link' => $link,
					'date' => $date,
					'images' => $feed_images,
				);
			}
		}// itelate feed_items

		return $results;
	}

	/**
	 * Creates a feed from the given parameters.
	 * 
	 * 	<channel>
	 * 	title:			配信元となるサイトなどの名称を表記
	 * 	link:			配信元となるサイトなどのURLを表記
	 * 	language:		使用言語
	 * 	copyiright:		著作権表記
	 * 	pubDate:		配信元ファイルの最終更新日 ( RFC 822 フォーマット形式による)
	 * 	lastBuildDate:	RSS 2.0ファイルの最終更新日 ( RFC 822 フォーマット形式による)
	 * 	webMaster:		サイト管理人
	 * 	managingEditor: 編集者へのEメールアドレス
	 * 	category:		配信元の所属するカテゴリーをひとつ以上指定
	 * 	generator:		RSS 2.0 ファイル作成に使用したツール名
	 * 	cloud:			XML - RPC などを利用した更新通知サービスを利用する際に記述
	 * 	docs:			RSSの内容を記述したドキュメントの URL （通常は、RSSファイルのある場所）
	 * 	ttl:			キャッシュの有効期限
	 * 	rating:			チャンネルのPICSレーティング
	 * 	skipHours:		アグリゲータにスキップさせるアクセス予定時刻を記述
	 * 	skipDays:		アグリゲータにスキップさせるアクセス予定曜日を記述
	 * 	textInput:		入力用テキストボックスを指定
	 * 	image:			GIF、JPEGまたはPNGイメージを指定
	 * 
	 * 	<item>
	 * 	title:			ページ（ item ）のタイトル
	 * 	link			ページ（ item ）のリンク先
	 * 	description		概要
	 * 	pubDate			ページの最終更新日 ( RFC 822 フォーマット形式による)
	 * 	enclosure		ページに添付してあるメディアコンテンツのURL
	 * 	comments		関連するコメントページがあればそのURL
	 * 	author			著者へのEメールアドレス
	 * 	category		カテゴリーをひとつ以上指定
	 * 	source			情報元のリンク URL
	 * 	guid			ページの識別ID（ユニークID )
	 * 					※ guid には isPermaLink 属性を持たせることができます。
	 * 					この場合属性値は "true" を指定する事によって、URL をIDとすることが出来ます。
	 * 					例 : <guid isPermaLink="true">http://mrs.suzu841.com/rss</guid>
	 *
	 * @param   array   $info       feed information
	 * @param   array   $items      items to add to the feed
	 * @param   string  $encoding   define which encoding to use
	 * @return  string
	 */
	public static function create(array $info, $items, $encoding = 'UTF-8', $title = 'Generated Feed', $link = '', $generator = '')
	{

		$info += ['title' => $title, 'link' => $link, 'generator' => $generator];

		$feed = '<?xml version="1.0" encoding="' . $encoding . '"?><rss version="2.0"><channel></channel></rss>';
		$feed = simplexml_load_string($feed);

		foreach ($info as $name => $value)
		{
			if ($name === 'image')
			{
				// Create an image element
				$image = $feed->channel->addChild('image');

				if (!isset($value['link'], $value['url'], $value['title']))
				{
					throw new Exception('Feed images require a link, url, and title');
				}

				if (strpos($value['link'], '://') === false)
				{
					// Convert URIs to URLs
					$value['link'] = Request::uri($value['link'], true);
				}

				if (strpos($value['url'], '://') === FALSE)
				{
					// Convert URIs to URLs
					$value['url'] = Request::uri($value['url'], true);
				}

				// Create the image elements
				$image->addChild('link', $value['link']);
				$image->addChild('url', $value['url']);
				$image->addChild('title', $value['title']);
			}
			else
			{
				if (($name === 'pubDate' OR $name === 'lastBuildDate') AND ( is_int($value) OR ctype_digit($value)))
				{
					// Convert timestamps to RFC 822 formatted dates
					$value = date('r', $value);
				}
				elseif (($name === 'link' OR $name === 'docs') AND strpos($value, '://') === FALSE)
				{
					// Convert URIs to URLs
					$value = Request::uri($value, 'http');
				}

				// Add the info to the channel
				$feed->channel->addChild($name, $value);
			}
		}

		foreach ($items as $item)
		{
			// Add the item to the channel
			$row = $feed->channel->addChild('item');

			foreach ($item as $name => $value)
			{
				if ($name === 'pubDate' AND ( is_int($value) OR ctype_digit($value)))
				{
					// Convert timestamps to RFC 822 formatted dates
					$value = date('r', $value);
				}
				elseif (($name === 'link' OR $name === 'guid') AND strpos($value, '://') === FALSE)
				{
					// Convert URIs to URLs
					$value = URL::site($value, 'http');
				}
				
				if(is_array($value))
				{
					$value = reset($value);
				}

				// Add the info to the row
				$row->addChild($name, $value);
			}
		}

		if (function_exists('dom_import_simplexml'))
		{
			// Convert the feed object to a DOM object
			$feed = dom_import_simplexml($feed)->ownerDocument;

			// DOM generates more readable XML
			$feed->formatOutput = TRUE;

			// Export the document as XML
			$feed_xml = $feed->saveXML();
		}
		else
		{
			// Export the document as XML
			$feed_xml = $feed->asXML();
		}

		return $feed_xml;
	}

}
