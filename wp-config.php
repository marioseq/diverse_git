<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'diversewrd');

/** MySQL database username */
define('DB_USER', 'marioseq');

/** MySQL database password */
define('DB_PASSWORD', 'msq4555');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'BkINiG0bX7PzvOvGe7HXA1Nj1qe5CY8QMujz0cdNCixakxrgn2xBAQTfwPw7FY7Q');
define('SECURE_AUTH_KEY',  'mdlhZttNf0rFAtfhmhjRXgDia6h8zHSGoAlU244FWVGrJ1VAzmPJO3UbCXdL8I9X');
define('LOGGED_IN_KEY',    't19fGU17r86E8PqMjrkZhPxUs0yzU5j34OXeUowlI0t6a2poR6158nqM52Jn0iIu');
define('NONCE_KEY',        'yPMVaisRMQMza8YZK12g7nLAJ7riz8vyECg5nOFG5oXAqr3rYN5q3U4HqKdniU0X');
define('AUTH_SALT',        'ZqgnZi1F9KRVIqKdyHi3Z21qWaZWE2MgmRDBKJUkAfAhDHkEIk7nJMhCUz6lsFCu');
define('SECURE_AUTH_SALT', 'K4wBpjv6GPbe57XGw2Nb1VdsTMJJG5k2MA3q7SWNpdxeVRtWtSf5Vn4Vzszlx7jZ');
define('LOGGED_IN_SALT',   '8yjAtYhh1CR7eBVFdftzgM6FRmctH0dwO3ujZuLe0pmsGvG1186QyP00kqYffeGP');
define('NONCE_SALT',       '2q5MMlTViF36tb8UQPitXZ8pYHb256vzsTAkJLuNGmhfDH3zUForXhKlxlzWTfpq');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
