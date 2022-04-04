<?php
define( 'WP_CACHE', true );
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
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'u448707245_zlRCV' );

/** MySQL database username */
define( 'DB_USER', 'u448707245_OY6YS' );

/** MySQL database password */
define( 'DB_PASSWORD', 'tDAHSR5JiT' );

/** MySQL hostname */
define( 'DB_HOST', 'mysql' );

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
define( 'AUTH_KEY',          'dDIe{^p7SR|$]%IBU~xxQ44Ai~kK/l[~2awo$:%mJ*C^otQG,`.,t2yt^Yoc/.ba' );
define( 'SECURE_AUTH_KEY',   'Z|IB#dr@2:NsUuyJonwwsmL3<&Xc<DE1=aUdP%H[6xAim#/6<=a+( Zs+:8hC,Ku' );
define( 'LOGGED_IN_KEY',     'Jh]PJ<ISPRe2(gW`hNk4=>SOm~:~LG#zKgPc<Qu($TbS=dF8-(YPJCt;p`@{5k?m' );
define( 'NONCE_KEY',         '2<E6cCmcMVEK,{` zYu))zs)T&}a#Mj@i`qTNaeYt~g iI4dO[P1J&bCogdsG*z7' );
define( 'AUTH_SALT',         '6{}$#=oXloVp1}ID008ZU1WuLoL oXX1;Dk4yL6T[`=mu[WHmQ?c9L(dXN(YEkXp' );
define( 'SECURE_AUTH_SALT',  'ld& ,Tz|(xv.XrCh9_fgOW!sqG^y=Q=6u#WF@|qE$V2PF1=< +f=`O1(A:w30%=K' );
define( 'LOGGED_IN_SALT',    'fKbOOp{#-p13]:s-81hoXXS`|_#=@em~+Z>]PR.}i>]D8z1T^miu(oc}}dML/F5x' );
define( 'NONCE_SALT',        'nlzbKHjkqXs8!FV&yv_.,]qq8g9{^x3h!&msQS-)wZ_(r!fU%5Fr4p|JdVz{]Cge' );
define( 'WP_CACHE_KEY_SALT', 'MUJi|]h--5}]Pm.-ags4G}&{.WXp7S:Xm%(HZ(>wFV_qZg|m^viUL7Htt[kl8j:9' );

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';




define( 'WP_AUTO_UPDATE_CORE', 'minor' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
