<?php

/**
 * Description of Controller_Home
 *
 * @author okuda
 */
class Controller_Home extends Controller {

	/**
	 * Before
	 */
	public function before()
	{
		parent::before();

		$this->view = Wavebar::fact('home');
	}

	public function get_index($arguments)
	{

		// database query test

		$db = DB::fact();
		
		// Select
		$reslt_obj = $db->table('users') 
				->select(['now()', 'date'], 'users.*', ['users.id', 'user_id']);
		
		
		
		Debug::p($reslt_obj->get(null, 'id'));
		Debug::p($reslt_obj->get());
		Debug::p($reslt_obj->count());
		Debug::p($reslt_obj->get_query());
		die;

		// Insert1
//		$ids = $db->table('users')
//				->insert([
//			['username' => 'kohei1', 'displayname' => 'kohx1'],
//			['username' => 'kohei2', 'displayname' => 'kohx2'],
//			['username' => 'kohei3', 'displayname' => 'kohx3'],
//			['username' => 'kohei4', 'displayname' => 'kohx4'],
//			['username' => 'kohei5', 'displayname' => 'kohx5'],
//		]);
//
//		Debug::p($db->get_query());
//		Debug::p($ids);
//		die;
//		
//		$ids = $db->table('users')
//				->insert([
//			['id' => 1, 'username' => 'kohei1', 'displayname' => 'kohx1'],
//			['id' => 2, 'username' => 'kohei2', 'displayname' => 'kohx2'],
//			['id' => 3, 'username' => 'kohei3', 'displayname' => 'kohx3'],
//			['id' => 4, 'username' => 'kohei4', 'displayname' => 'kohx4'],
//			['id' => 5, 'username' => 'kohei5', 'displayname' => 'kohx5'],
//		]);
//				
//		Debug::p($db->get_query());
//		Debug::p($ids);
//		die;
//		 
		// Insert2
// 		$ids = $db->table('users')
//				->insert(
//				['username', 'displayname'], [
//			['kohei1', 'kohx1'],
//			['kohei2', 'kohx2'],
//		]);
//
//		Debug::p($db->get_query());
//		Debug::p($ids);
//		die;
//		
		// Update
//		Debug::timer()->start('pre1');
//		$ids = $db->table('users')
//				->where('id', 'in', [1,3,5,7,9])
//				->update(
//				['username' => 'kohei@', 'displayname' => 'kohx@']
//		);
//		Debug::p($ids);
//		Debug::p($db->get_query());
//		Debug::timer()->end('pre1');
//		Debug::timer()->show('pre1');
//		die;
//		
		// Delete
//		Debug::timer()->start();
//		$ids = $db->table('users')
//				->where('id', 'between', [1,5])
//				->delete();
//
//		Debug::p($db->get_query());
//		Debug::p($ids);
//		die;
//
//		$db = DB::fact()
//				->select(DB::ex('max(id)'), DB::ex('min(id)'))
//				->select([DB::ex('max(id)'), 'abg'])
//				->select(DB::ex('count(realname)'))
//				->select('username')
//				->select(['username', 'name'])
//				->where('id', 1)
//				->table('users')
//				->execute();	
//		Debug::v($db->get());
//		Debug::v($db->get_query());
//		$db = DB::fact()
//		->select()
//		->table('users')
//		->where('id', 2)
//		->or_where('id', 1)
//		->get();
//		$db = DB::fact()
//				->table('users')
//				->insert(
//					['username' => 'user2', 'realname' => 'user2 user2'],
//					['username' => 'user3', 'realname' => 'user3 user3'],
//					['username' => 'user4', 'realname' => 'user4 user4'],
//				);
//		$db = DB::fact()
//				->table('users')
//				->insert(['username' => 'user5', 'realname' => 'user5 user5']);
//
//				Debug::v($db);
//		$db = DB::fact()
//				->table('users')
//				->where('id', 22)
//				->update(['username' => 'user100', 'realname' => 'realname100',]);
//		$db = DB::fact()
//				->table('users')
//				->where('id', 1)
//				->or_where('id', 2)
//				->delete();




		$this->view
				->bind('bind', $bind)
				->set('header', '<h1>index get</h1>')
				->set('description ', 'description ')
				->set('text', 'text')
				->set('things', ['category' => ['type' => 'type']])
				->set('users', [['name' => 'kohei'], ['name' => 'neko'], ['name' => 'inu'], ['name' => 'tori']])
				->set('shop', ['name' => 'shop name'])
				->set('limited', 'xxxx ' . PHP_EOL . 'xxxxxxx xxxxxxxxxx xxxxxxx xxxxxxxxxxxxxxxx xxxxxxxxx xxxxxxx')
				->set('notice', Notice::render())
		;

		$this->set_header('name', 'asdfadf');
	}

	public function post_index($arguments)
	{

// -----------------------------------------------------------------------------
// test
//var_dump(Request::method());
//var_dump(Request::post());
//var_dump(Request::get());
//var_dump(Request::basepath());
//var_dump(Request::pathinfo());
//var_dump(Request::baseurl());
//var_dump(Request::baseurl(true));
//var_dump(Request::query_string());
//var_dump(Request::user_agent());
//var_dump(Request::accept_type());
//var_dump(Request::accept_lang());
//var_dump(Request::accept_encoding());
//
//
//foreach (apache_request_headers() as $header => $value)
//{
//	echo "$header: $value <br />\n";
//}
//echo '------------------------<br />';
//foreach (apache_response_headers() as $name => $value)
//{
//	echo "$name: $value <br />\n";
//}
//echo '------------------------<br />';
//foreach (getallheaders() as $name => $value) {
//    echo "$name: $value <br />\n";
//}
//echo '------------------------<br />';
//
//var_dump(http_response_code());
//		$str = preg_replace("/(\d{4})-(\d{2})-(\d{2})/e","Date('n/j/Y',strtotime('$0'))",$str);
//
//
//		$str = preg_replace_callback("/(\d{4})-(\d{2})-(\d{2})/", function($m)
//		{
//			return Date('n/j/Y', strtotime($m[0]));
//		}, $str);
//		$str = 'あああ　いいい　ううう　えええ　おおおおお';
//		echo Text::limit_words($str, 2);
//		$str = 'あいabうえおcdeかfきgくhけiこ';
//		echo Text::limit_chars($str, 5) . '<br>';
//		echo Text::censor('What the frick, man!', ['frick']) . '<br>';
//		echo Text::alternate('1234', 'asdf') . '<br>';
//		echo Text::alternate('1234', 'asdf') . '<br>';
//		echo Text::alternate('1234', 'asdf') . '<br>';
//		echo Text::alternate('1234', 'asdf') . '<br>';
//		echo Text::random() . '<br>';
//		echo Text::random('alnum', '36') . '<br>';
//		echo Text::random('alpha', '36') . '<br>';
//		echo Text::random('hexdec', '36') . '<br>';
//		echo Text::random('distinct', '36') . '<br>';
//		echo Text::ucfirst('asdf-qwer-zxcv-0001') . '<br>';
//		echo Text::reduce_slashes('//asdf//qwer//zxcv/0001') . '<br>';
//		echo Text::similar(['fred', 'fran', 'free']) . '<br>';
//		echo Text::similar(['redasd', 'sfranasd', 'fcreeasd']) . '<br>';
//		echo Text::auto_link_urls('asdf http://asdf.com cccc/cc www.links.com asdf adfa adf') . '<br>';
//		echo Text::auto_link_emails('asdf asdf@asdf.com cccc/cc www@links.com asdf adfa adf') . '<br>';
//		echo Text::auto_link('asdf http://asdf.com cccc/cc asdf asdf@asdf.com cccc/cc www.links.com asdf adfa adf www@links.com asdf adfa adf') . '<br>';
//		echo Text::auto_p('
//			asdf asdf asdf asdf asdf
//
//			asdf asdf asdf asdf asdf
//			sdf asdf asdf asdf asdf
//		');
//		echo Text::bytes(filesize(__FILE__)) . '<br>';
//		echo Text::bytes('2048') . '<br>';
//		echo Text::bytes('4194304', 'kB') . '<br>';
//		echo Text::bytes('4194304', 'MB') . '<br>';
//		echo Text::bytes('100663296', 'GB') . '<br>';
//		echo Text::bytes('100663296', 'GiB') . '<br>';
//		echo Text::bytes('100663296', 'GiB', '%01.4f %s') . '<br>';
//		echo Text::bytes('4194304') . '<br>';
//		echo Text::bytes('4194304', NULL, NULL, FALSE) . '<br>';
		//
//		echo Text::number(1) . '<br>';
//		echo Text::number(10) . '<br>';
//		echo Text::number(1024) . '<br>';
//		echo Text::number(5000632) . '<br>';
		//
//		Debug::v(Inflector::uncountable('cat'));
//		Debug::v(Inflector::uncountable('new'));
//		Debug::v(Inflector::uncountable('fish'));
		//
//		echo '3'. Inflector::singular('cats', 3) . '<br>';
//		echo '1'. Inflector::singular('fish', 1) . '<br>';
//		echo '2'. Inflector::singular('fish', 2) . '<br>';
//		echo '2'. Inflector::singular('dances', 2) . '<br>';
//		echo Inflector::plural('cat', 3) . '<br>';
//		echo Inflector::plural('fish', 3) . '<br>';
//		echo Inflector::plural('child', 3) . '<br>';
		//
//		echo Inflector::camelize('kittens in bed') . '<br>';
//		echo Inflector::decamelize('houseCat') . '<br>';
//		echo Inflector::underscore('five cats') . '<br>';
//		echo Inflector::humanize('kittens-are-cats') . '<br>';
//		echo Inflector::humanize('dogs_as_well') . '<br>';
		//
		//
//		Response::redirect('about/index');
//		Session::inst()->set('user', ['name' => 'kohei okuda', 'age' => '38',]);
//		Session::inst()->regenerate();
//		Session::inst()->delete('name');
//		Session::inst()->restart();
//		Debug::v(__('Hello, :user', array(':user' => 'KOHEI')));
//		Debug::v(Message::get('validation', 'alpha'));
//		$array = Validation::fact($_FILES);
//		$feeds = Feed::parse('http://feedblog.ameba.jp/rss/ameblo/cantiklumba/rss20.xml');
//		Feed::create(['kohei'], $feeds);
//		Debug::v(Request::current(true));
//		Debug::v(Request::referrer());
//		Debug::v($this->get_controller());
//		Debug::v($this->get_action());

		$user = '';
//		$user = Session::inst()->get_once('user');
//		Debug::v(ini_get('session.gc_maxlifetime'));
//		Debug::v(ini_get('session.cookie_lifetime'));
//		Debug::v(Text::user_agent('browser'));
//		Debug::v(Text::user_agent('version'));
//		Debug::v(Text::user_agent('platform'));
//		Debug::v(Text::user_agent('robot'));
//		Debug::v(Text::user_agent('mobile'));

		$set = '111';
		$bind = '!!!';

		// VALIDATION
		if (Request::method() !== 'GET')
		{
			if (!Security::check())
			{
				throw new Exception('not found.');
			}

			$post = Request::post();
			$post['image1'] = Request::file('image1');

			$images2 = Arr::rotete(Request::file('image2'), 'image2_');

			$validation = Validation::fact($post)
					->label('name', __('name'))
					->rule('name', 'not_empty')
					->rule('name', 'Model_Home::tvalid')
					->rule('name', function($a, $b, $c)
					{
						return true;
					}, [1, 2, 3], 'original')
					->label('password', __('password'))
					->rule('password', 'not_empty')
					->rule('confirm', 'matches', [':validation', 'password', 'confirm', __('confirm')])
					->label('gender', __('gender'))
					->rule('gender', 'in_array', [':value', [1, 2]], __(':label mast 1 or 2'))
					->label('age', __('age'))
					->rule('age', 'digit')
//					->rule('age', 'alpha')
					->rule('age', 'Model_Home::tvalid')
					->rule('age', 'max_length', [':value', 200])
					->rule('email', 'email')
					->rule('email', ['Model_Home', 'tvalid'])
					->rules('name', [
						['min_length', [':value', 4]],
						['max_length', [':value', 10]],
					])
//					->rule('image1', 'Upload::not_empty')
					->rule('image1', 'Upload::valid')
					->rule('image1', 'Upload::image')
					->rule('image1', 'Upload::type', [':value', 'jpg, jpeg, png, gif'])
					->rule('image1', 'Upload::size', [':value', '1GB'])
			;

			$validation_images = Validation::fact($images2);
			foreach (array_keys($images2) as $image)
			{
				$validation_images
//						->rule($image, 'Upload::not_empty')
						->rule($image, 'Upload::valid')
						->rule($image, 'Upload::image', [':value', 640, 480])
						->rule($image, 'Upload::size', [':value', '1GB'])
						->rule($image, 'Upload::type', [':value', 'jpg, png, gif']);
			}

			$check1 = $validation->check();
			$check2 = $validation_images->check();

			if ($check1 AND $check2)
			{
				$upload_dir = APPPATH . 'upload\\';
				$file = Upload::save(Arr::get($post, 'image1'), $upload_dir);

				if ($file)
				{
					$filename = File::sufname($file, '_o', true);

					Image::factory($file)
							->resize(200, 200, Image::AUTO)
							->save($filename);

					$filename = File::sufname($file, '_s', true);

					Image::factory($file)
							->resize(100, 100, Image::AUTO)
							->save($filename);

					// Delete the temporary file
					unlink($file);
				}
			}
			else
			{
				Notice::add(Notice::VALIDATION, __('title'), Arr::merge($validation->errors(), $validation_images->errors()));
			}
		}

		$this->view
				->bind('bind', $bind)
				->set('header', '<h1>index put</h1>')
				->set('description ', 'description ')
				->set('text', 'text')
				->set('things', ['category' => ['type' => 'type']])
				->set('users', [['name' => 'kohei'], ['name' => 'neko'], ['name' => 'inu'], ['name' => 'tori']])
				->set('shop', ['name' => 'shop name'])
				->set('limited', 'xxxx ' . PHP_EOL . 'xxxxxxx xxxxxxxxxx xxxxxxx xxxxxxxxxxxxxxxx xxxxxxxxx xxxxxxx')
				->set('set', $set)
				->set('user', $user)
				->set('notice', Notice::render())
		;

		$bind = 'bind';
	}

	public function after()
	{
		parent::after();
	}

}
