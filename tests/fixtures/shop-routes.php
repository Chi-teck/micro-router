<?php
declare(strict_types=1);

$routes['home'] = [
    'path' => '/',
    'requests' => [
        'GET->/' => [],
        'GET->/home' => null,
    ],
];

$routes['page.show'] = [
    'path' => '/page/{page_slug}',
    'requirements' => [
        'page_slug' => '[a-zA-Z0-9\-]+'
    ],
    'requests' => [
        'GET->/page/123' => ['page_slug' => '123'],
        'GET->/page/abc' => ['page_slug' => 'abc'],
        'GET->/page/a_z' => null,
    ],
];

$routes['about-us'] = [
    'path' => '/about-us',
    'requests' => [
        'GET->/about-us' => [],
        'POST->/about-us' => null,
    ],
];

$routes['contact-us'] = [
    'path' => '/contact-us',
    'requests' => [
        'GET->/contact-us' => [],
        'POST->/contact-us' => null,
    ],
];

$routes['contact-us.submit'] = [
    'methods' => ['POST'],
    'path' => '/contact-us',
    'requests' => [
        'GET->/contact-us' => null,
        'POST->/contact-us' => [],
    ],
];

$routes['blog.index'] = [
    'path' => '/blog',
    'requests' => [
        'GET->/blog' => [],
    ],
];

$routes['blog.recent'] = [
    'path' => '/blog/recent',
    'requests' => [
        'GET->/blog/recent' => [],
    ],
];

$routes['blog.post.show'] = [
    'path' => '/blog/post/{post_slug}',
    'requirements' => [
        'post_slug' => '[a-zA-Z0-9\-]+',
    ],
    'requests' => [
        'GET->/blog/post/january' => ['post_slug' => 'january'],
    ],
];

$routes['blog.post.comment'] = [
    'methods' => ['POST'],
    'path' => '/blog/post/{post_slug}/comment',
    'requirements' => [
        'post_slug' => '[a-zA-Z0-9\-]+',
    ],
    'requests' => [
        'POST->/blog/post/january/comment' => ['post_slug' => 'january'],
    ],
];

$routes['shop.index'] = [
    'path' => '/shop',
    'requests' => [
        'GET->/shop' => [],
    ],
];

$routes['shop.category.index'] = [
    'path' => '/shop/category',
    'requests' => [
        'GET->/shop/category' => [],
        'POST->/shop/category/123' => null,
        'POST->/shop/wrong' => null,
    ],
];

$routes['shop.category.search'] = [
    'path' => '/shop/category/search/{filter_by}:{filter_value}',
    'requirements' => [
        'filter_by' => '[a-zA-Z]+',
    ],
    'requests' => [
        'GET->/shop/category/search/color:red' => ['filter_by' => 'color', 'filter_value' => 'red'],
        'GET->/shop/category/search/123:red' => null,
    ],
];

$routes['shop.category.show'] = [
    'path' => '/shop/category/{category_id}',
    'requirements' => [
        'category_id' => '\d+',
    ],
    'requests' => [
        'GET->/shop/category/123' => ['category_id' => '123'],
        'GET->/shop/category/abc' => null,
    ],
];

$routes['shop.category.product.index'] = [
    'path' => '/shop/category/{category_id}/product',
    'requirements' => [
        'category_id' => '\d+',
    ],
    'requests' => [
        'GET->/shop/category/123/product' => ['category_id' => '123'],
        'GET->/shop/category/abc/product' => null,
    ],
];

$routes['shop.category.product.search'] = [
    'path' => '/shop/category/{category_id}/product/search/{filter_by}:{filter_value}',
    'requirements' => [
        'category_id' => '\d+',
        'filter_by' => '[a-zA-Z]+',
    ],
    'requests' => [
        'GET->/shop/category/123/product/search/color:red' => ['category_id' => 123, 'filter_by' => 'color', 'filter_value' => 'red'],
    ],
];

$routes['shop.product.index'] = [
    'path' => '/shop/product',
    'requests' => [
        'GET->/shop/product' => [],
        'GET->/shop' => null,
    ],
];

$routes['shop.product.search'] = [
    'path' => '/shop/product/search/{filter_by}:{filter_value}/{sort_by}',
    'requirements' => [
        'filter_by' => '[a-zA-Z]+',
    ],
    'defaults' => [
        'sort_by' => 'date',
    ],
    'requests' => [
        'GET->/shop/product/search/size:big' => ['filter_by' => 'size', 'filter_value' => 'big', 'sort_by' => 'date'],
        'GET->/shop/product/search/size:big/price' => ['filter_by' => 'size', 'filter_value' => 'big', 'sort_by' => 'price'],
        'GET->/shop/product/search/size_big' => null,
    ],
];

$routes['shop.product.show'] = [
    'path' => '/shop/product/{product_id}',
    'requirements' => [
        'product_id' => '\d+',
    ],
    'requests' => [
        'GET->/shop/product/123' => ['product_id' => 123],
        'GET->/shop/product/abc' => null,
        'GET->/shop/product' => null,
    ],
];

$routes['shop.cart.show'] = [
    'path' => '/shop/cart',
    'requests' => [
        'GET->/shop/cart' => [],
        'HEAD->/shop/cart' => [],
        'GET->/shop' => null,
    ],
];

$routes['shop.cart.add'] = [
    'methods' => ['PUT'],
    'path' => '/shop/cart',
    'requests' => [
        'PUT->/shop/cart' => [],
        'POST->/shop/cart' => null,
        'GET->/shop/cart' => null,
    ],
];

$routes['shop.cart.empty'] = [
    'methods' => ['DELETE'],
    'path' => '/shop/cart',
    'requests' => [
        'DELETE->/shop/cart' => [],
        'POST->/shop/cart' => null,
        'HEAD->/shop/cart' => null,
    ],
];

$routes['shop.cart.checkout.show'] = [
    'path' => '/shop/cart/checkout',
    'requests' => [
        'GET->/shop/cart/checkout' => [],
        'GET->/shop/cart' => null,
    ],
];

$routes['shop.cart.checkout.process'] = [
    'methods' => ['POST'],
    'path' => '/shop/cart/checkout',
    'requests' => [
        'POST->/shop/cart/checkout' => [],
        'GET->/shop/cart/checkout' => null,
    ],
];

$routes['admin.login'] = [
    'path' => '/admin/login',
    'POST->/shop/cart/checkout' => [],
    'requests' => [
        'GET->/admin/login' => [],
        'POST->/admin/login' => null,
    ],
];

$routes['admin.login.submit'] = [
    'methods' => ['POST'],
    'path' => '/admin/login',
    'requests' => [
        'POST->/admin/login' => [],
        'GET->/admin/login' => null,
    ],
];

$routes['admin.logout'] = [
    'path' => '/admin/logout',
    'requests' => [
        'GET->/admin/logout' => [],
        'POST->/admin/logout' => null,
    ],
];

$routes['admin.index'] = [
    'path' => '/admin',
    'requests' => [
        'GET->/admin' => [],
        'POST->/admin/' => null,
    ],
];

$routes['admin.product.index'] = [
    'path' => '/admin/product',
    'requests' => [
        'GET->/admin/product' => [],
    ],
];

$routes['admin.product.create'] = [
    'path' => '/admin/product/create',
    'requests' => [
        'GET->/admin/product/create' => [],
    ],
];

$routes['admin.product.store'] = [
    'methods' => ['POST'],
    'path' => '/admin/product',
    'requests' => [
        'POST->/admin/product' => [],
    ],
];

$routes['admin.product.show'] = [
    'path' => '/admin/product/{product_id}',
    'requirements' => [
        'product_id' => '\d+',
    ],
    'requests' => [
        'GET->/admin/product/123' => ['product_id' => 123],
    ],
];

$routes['admin.product.edit'] = [
    'path' => '/admin/product/{product_id}/edit',
    'requirements' => [
        'product_id' => '\d+',
    ],
    'requests' => [
        'GET->/admin/product/123/edit' => ['product_id' => 123],
    ],
];

$routes['admin.product.update'] = [
    'methods' => ['PUT', 'PATCH'],
    'path' => '/admin/product/{product_id}',
    'requirements' => [
        'product_id' => '\d+',
    ],
    'requests' => [
        'PUT->/admin/product/123' => ['product_id' => 123],
        'PATCH->/admin/product/123' => ['product_id' => 123],
        'GET->/admin/product/123' => null,
    ],
];

$routes['admin.product.destroy'] = [
    'methods' => ['DELETE'],
    'path' => '/admin/product/{product_id}',
    'requirements' => [
        'product_id' => '\d+',
    ],
    'requests' => [
        'DELETE->/admin/product/123' => ['product_id' => 123],
        'PATCH->/admin/product/123' => null,
        'GET->/admin/product/123' => null,
    ],
];

$routes['admin.category.index'] = [
    'path' => '/admin/category',
    'requests' => [
        'GET->/admin/category' => [],
    ],
];

$routes['admin.category.create'] = [
    'path' => '/admin/category/create',
    'requests' => [
        'GET->/admin/category/create' => [],
    ],
];

$routes['admin.category.store'] = [
    'methods' => ['POST'],
    'path' => '/admin/category',
    'requests' => [
        'POST->/admin/category' => [],
    ],
];

$routes['admin.category.show'] = [
    'path' => '/admin/category/{category_id}',
    'requirements' => [
        'category_id' => '\d+',
    ],
    'requests' => [
        'GET->/admin/category/456' => ['category_id' => 456],
    ],
];

$routes['admin.category.edit'] = [
    'path' => '/admin/category/{category_id}/edit',
    'requirements' => [
        'category_id' => '\d+',
    ],
    'requests' => [
        'GET->/admin/category/456/edit' => ['category_id' => 456],
    ],
];

$routes['admin.category.update'] = [
    'methods' => ['PUT', 'PATCH'],
    'path' => '/admin/category/{category_id}',
    'requirements' => [
        'category_id' => '\d+',
    ],
    'requests' => [
        'PUT->/admin/category/456' => ['category_id' => 456],
        'PATCH->/admin/category/456' => ['category_id' => 456],
        'PATCH->/admin/category/wrong' => null,
        'GET->/admin/category/456' => null,
    ],
];

$routes['admin.category.destroy'] = [
    'methods' => ['DELETE'],
    'path' => '/admin/category/{category_id}',
    'requirements' => [
        'category_id' => '\d+',
    ],
    'requests' => [
        'DELETE->/admin/category/456' => ['category_id' => 456],
        'GET->/admin/category/456' => null,
    ],
];

$routes['sitemap'] = [
    'path' => '/sitemap.xml',
    'requests' => [
        'GET->/sitemap.xml' => [],
        'GET->/sitemap' => null,
        'GET->/.xml' => null,
    ],
];

return $routes;
