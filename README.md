# Flatfile

A simple Flatfile ORM for Kohana

## How to use it

This is a example with a Welcome controller.

### Create your model

Create a page model:

	| application
		| classes
			| Model
				Page.php

In your model:

	class Model_Page extend Flatfile {
		
		/**
		* Somes filters
		*
		* @return array
		**/

		public function filters()
		{
			return array(
				array(
					'content' => array('Flatfile::Markdown'),
				),
			);
		}
	}

### Create your content

In your root project:

	| content
		| pages
			hello-world.md

In your hello-world file:

	title: Hi!
	---
	Hello **World**

### Grab your content
Create your controller

	| application
		| classes
			| Controller
				Welcome.php

In your controller, in classic Kohana MVC design pattern
	
	class Controller_Welcome extends Controller {
		
		/**
		* Action call by defaults
		*
		* @return void
		**/
		
		public function action_index()
		{
			$view = View::factory('welcome/index')
				->bind('page', $page);
			$page = new Model_Page('hello-world');
			$this->response->body($view);
		}
	}


### Show your content

Create your View

	| application 
		| views
			| welcome
				index.php

In your view index

	<html>
		<head>
			<title>
				<?php echo $page->title; ?>
			</title>
		</head>
		<body>
			<?php echo $page->content; ?>
		</body>
	</html>

### The result

	<html>
		<head>
			<title>Hi!</title>
		</head>
		<body>
			Hello <strong>World</strong>	
		</body>
	</html>

## Filters

Similar usage to Kohana ORM.  

Flatfile embed a specifics filters : 

### Markdown
Process a Markdown transformation.

### str_to_list
Convert a comma separate list to an array of term and slug.

For example :

	tags: Laitue, Choux rouge

Be converted to 

	array(
		array(
			'name'	=> 'Laitue',
			'slug'	=> 'laitue',
		),
		array(
			'name'	=> 'Choux rouge',
			'slug'	=> 'choux-rouge',
		),
	)

Usefull, for example, to create a list of tags; in your view : 

	<uL>
	<?php foreach($page->tags as $tag): ?>
		<li>
			<a href="http://my_awesome_blog.com/posts/tag/<?php echo $tag['slug']; ?>">
				<?php echo $tag['name']; ?>
			</a>
		</li>
	<?php endforeach; ?>
	</ul>

### json_api
Give him a json API service address, it will return a result ready to use.

For example : 

	bloginfo: http://api.tumblr.com/v2/blog/blogdamientran.tumblr.com/info

Resulting to 

	stdClass Object
	(
		[blog] => stdClass Object
		(
			[title] => Damien Tran
			[name] => blogdamientran
			[posts] => 66
			[url] => http://blogdamientran.tumblr.com/
			[updated] => 1434963170
			[description] => damientran.com - palefroi.net
			[is_nsfw] => 
			[ask] => 
			[ask_page_title] => Ask me anything
			[ask_anon] => 
			[share_likes] => 1
			[likes] => 6
		)
	)

You can use the result in your view

	<a href="<?php echo $page->bloginfo->blog->url; ?>">
		See the blog <?php echo $page->bloginfo->blog->title; ?>
	</a>

## Flatfile methods
Flatfile contain somes usefull methods

### order

### query
#### Operators
##### like
#### Multiple queries
#### date and slug



### offset

### limit

### find

### find_all
