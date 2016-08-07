<?php

//return array(
//	// The default route
//	'/' => array(
//		'controller' => 'home',
//		'action' => 'index',
//	),
//	// User edit
//	'/user/edit' => array(
//		'controller' => 'user',
//		'action' => 'edit',
//	),
//	// User :id
//	'/user/:id' => array(
//		'controller' => 'user',
//		'controller' => 'show',
//	),
//	// Item :action
//	'/item/:action' => array(
//		'controller' => 'item',
//	),
//	// Home :action :id
//	'/home/:action/:id' => array(
//		'controller' => 'home',
//		'func' => function($params)
//		{
//			$route = [
//				'controller' => $params['controller'],
//				'action' => $params['action'],
//				'id' => $params['id'],
//			];
//			return $route;
//		}
//			),
//			// :controller index
//			'/:controller' => array(
//				'action' => 'index',
//			),
//			// :controller :action :id
//			'/:controller/:action/:id' => array(),
//			// :controller :action
//			'/:controller/:action/' => array(),
//			// not found
//			'' => array(
//				'controller' => 404,
//				'action' => 'index',
//				'func' => function($params)
//				{
//					$segments = explode('/', trim($params['url'], '/'));
//
//					if (true)
//					{
//						return ['controller' => reset($segments), 'action' => end($segments)];
//					}
//				}),
//				);
				