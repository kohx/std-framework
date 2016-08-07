{|@page|}

{|&notice|}

<h1>{|text|}</h1>
<form action="" method="post" enctype="multipart/form-data">
	<input type="text" name="name" value="kohei" /><br />
	<input type="text" name="password" value="121212" /><br />
	<input type="text" name="confirm" value="121212" /><br />
	male<input type="radio" name="gender" value="1" checked /><br />
	fumale<input type="radio" name="gender" value="2" /><br />
	<input type="text" name="age" value="38" /><br />
	<input type="text" name="email" value="asdf@fff.com" /><br />
	<input type="file" name="image1" /><br />
	<input type="file" name="image2[]" /><br />
	<input type="file" name="image2[]" /><br />
	<input type="file" name="image2[]" /><br />
	<button type="submit" name="" value="">submit</button>	
	{|token()|}
	{|rest('post')|}
</form>

<p>session name: {|user.name|} [{|user.age|}]</p>
<p>bind: {|bind|}</p>

<p>{|text|}</p>

<p>{|&text|}</p>

<p>{|things.category.type|}</p>

{|#things.category.type: type|}
<p>{|type|}</p>
{|#/|}

{|#shop|}
<p>{|shop.name|}</p>
{|#-|}
<p>---</p>
{|#/|}

{|#shop|}
<p>{|shop.name|}</p>
{|#/|}

{|^shop|}
<p>{|shop.name|}</p>
{|^/|}

{|*users:user|}
<p>{|user.name|}</p>
{|*/|}

<p>{|!limited = l(description, users.user, [], [123], [123, 'str'], 123, 'str', false, true, null, '',)|}</p>

<p>{|l(limited)|}</p>
<p>{|p(limited)|}</p>
<p>{|lp(limited)|}</p>

{|!things = fetch(xxx = [], yyy = [123,], aaa = [111, 222, 'str', val, false, true, null, ''], bbb = 'str', ccc = 123, ddd = true, eee = false, fff = null, ggg = '', hhh = var, iii = arr.arr,)|}

<p>{|!text|}</p>

{|?|} echo 'kohei'; {|?/|}