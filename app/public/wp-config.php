<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'local' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', 'root' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '7I98BqynfkimkEju+0RgqAqLebREMIiF6+wBMtWmEcVZj075qDLHcWgrmQ09XyoDRhnqot60EXlUJTjItMrgHg==');
define('SECURE_AUTH_KEY',  'eaVoowaH2M1AqtjgITHNZca+j0kUCSIuWKjNuvQ18pOKdkFFJJpVl8/NbfeV76zDof7YBrV76+7yptOfZ/qZOA==');
define('LOGGED_IN_KEY',    't7PHoJN/n+anJ40pzyFyeHAW3KX4TiUTpYeap3Qlk1pyEfDfpCuz/UKdj8c5aycpJBzMB3OZlnatjni0CMP3bA==');
define('NONCE_KEY',        'xQf2nxb0eXao94CONPlYumy+7eZw0hll1jBRcEail0jgnmQZnX72PpmwjNgDVW5A1adMWafE6WKj3fPj186rOQ==');
define('AUTH_SALT',        '01XUYTMhm5mS0+F24p4S2cUEN5WoSPGhDLMo948C1dqzpC0h2Pf9V/JSpgV6EgXNOfQh5h3PvcY81TMrmQYBwQ==');
define('SECURE_AUTH_SALT', 'YphW4XYJ3R0p3FbjwZOxCfN9q0y51K+41Y6pIdGI3jFNA9X8dCZYcL/K4hL5qMwIeD+kjdcDoFTPeaRdqPoQdw==');
define('LOGGED_IN_SALT',   'yJAOPo/E2ipRwCq+TheUgUl74lkdAdOdzjT67qQUE4J0CwH81LVqEi/zL1nZq5fHEm4ZCnZ3uLZPr+RYHT/yqA==');
define('NONCE_SALT',       'y6Zs2/3DGI/Yfe+ASxTHtj6SOkAsD4PvoJ2dGET9b8PDPGWi4+Gtc/n8DQzOOXSjw+03FL+oFOX6V8OZCnmMfw==');

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';




/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
