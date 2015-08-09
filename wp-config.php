<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, and ABSPATH. You can find more information by visiting
 * {@link https://codex.wordpress.org/Editing_wp-config.php Editing wp-config.php}
 * Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'wordpress');

/** MySQL database username */
define('DB_USER', 'wp');

/** MySQL database password */
define('DB_PASSWORD', 'wp_56789');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

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
define('AUTH_KEY',         'pYD<}la%~e(TUp4Q%$,F|jV7^+DpVpRs8&UW`-^(Ls#a/&5MVjvX_y9R#|<pL.jp');
define('SECURE_AUTH_KEY',  'lG?#BiaBdyW9q(ekz2XVLM#lBZmdlg|?5_4LP:5B0&gag3wuQx$8^+?U8Ag^eYl<');
define('LOGGED_IN_KEY',    '~f<]yz+-Mu>m)QR^P4xjx+-FEnbmXA_q9S@0?W_<S%[,U.~NN<5-2>I|Mn6It`>N');
define('NONCE_KEY',        '4IKC:NE4pIYMX@:3Ji.~1UTHvoD?<,MK<(8JFkf A~GV:rPA=5U35f5<_Z{5KIY=');
define('AUTH_SALT',        'n20?j_vTVQ8iU)WQmRSxWKdMgqwy@.s;tm>tw ~5c&/:PpN$6M)MwYb31wNP7#T)');
define('SECURE_AUTH_SALT', '>2=&O-0-uXnmj@AZ(UfnO0ja{^e,EcsNAPd8+26M(i-aD.k:8dd.N1a1O{d?K*Gp');
define('LOGGED_IN_SALT',   'LHsO&qR64s@34v}*nLxZ_iZ@EW[v#EGVj*fyY&PU.@nlO`zn!)*[|=.tV(135@)?');
define('NONCE_SALT',       '=+IYN^dypY#|c$$I9PQl@HKt0Y{%MM:)-sE#`]M#GG,SffMq@hXW_+00jGn6 xv1');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', true);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

