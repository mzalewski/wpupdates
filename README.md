## WordPress plugin updater

Add to wp-config.php:

```

define(
	'ANTIMATTER_WP_UPDATE_CONFIG',
	array(
		array(
			'slug'    => 'custom',
			'name'    => 'Demo Repo',
			'plugins' => array(
				'url'  => 'http://app/plugins',
				'tabs' => array(
					'featured' => 'Featured',
					'popular'  => 'Popular',
				),
			),
			'themes'  => array(
				'url'  => 'http://app/plugins',
				'tabs' => array(
					'popular' => 'Popular',
				),
			),
		),
		array(
			'slug'    => 'wporg',
			'plugins' => array(
				'tabs' => array(
					'featured' => 'Featured',
					'wp-org'   => 'WP.org',
				),
			),
			'themes'  => array(
				'tabs' => array(
					'new' => 'Latest',
				),
			),
		),

	)
);
```

## Notes
Handles plugin and theme browsing and installation
Core updates not yet supported
Theme filters not yet supported 

# To Do List
1. Split out code to be installed as a mu-plugin
2. Inject MU Plugin on plugin installation
3. Add support for core updates (will require redirecting URLs)
4. 
