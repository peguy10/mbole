<?php
/**
 * La configuration de base de votre installation WordPress.
 *
 * Ce fichier contient les réglages de configuration suivants : réglages MySQL,
 * préfixe de table, clés secrètes, langue utilisée, et ABSPATH.
 * Vous pouvez en savoir plus à leur sujet en allant sur
 * {@link https://fr.wordpress.org/support/article/editing-wp-config-php/ Modifier
 * wp-config.php}. C’est votre hébergeur qui doit vous donner vos
 * codes MySQL.
 *
 * Ce fichier est utilisé par le script de création de wp-config.php pendant
 * le processus d’installation. Vous n’avez pas à utiliser le site web, vous
 * pouvez simplement renommer ce fichier en "wp-config.php" et remplir les
 * valeurs.
 *
 * @link https://fr.wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Réglages MySQL - Votre hébergeur doit vous fournir ces informations. ** //
/** Nom de la base de données de WordPress. */
define( 'DB_NAME', 'mbole' );

/** Utilisateur de la base de données MySQL. */
define( 'DB_USER', 'root' );

/** Mot de passe de la base de données MySQL. */
define( 'DB_PASSWORD', '' );

/** Adresse de l’hébergement MySQL. */
define( 'DB_HOST', 'localhost' );

/** Jeu de caractères à utiliser par la base de données lors de la création des tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** Type de collation de la base de données.
  * N’y touchez que si vous savez ce que vous faites.
  */
define('DB_COLLATE', '');

/**#@+
 * Clés uniques d’authentification et salage.
 *
 * Remplacez les valeurs par défaut par des phrases uniques !
 * Vous pouvez générer des phrases aléatoires en utilisant
 * {@link https://api.wordpress.org/secret-key/1.1/salt/ le service de clés secrètes de WordPress.org}.
 * Vous pouvez modifier ces phrases à n’importe quel moment, afin d’invalider tous les cookies existants.
 * Cela forcera également tous les utilisateurs à se reconnecter.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'Vtjq<;!qp(>xH_MEx&T7F~;-8bO1%LFkw+GAT~yWK_o>6W!ENuhX|,jhjtn}F|;b' );
define( 'SECURE_AUTH_KEY',  '#e/tD*t~8KWhEl]@Ff]}%x)/tSUQr9:c~;nwJeR|.s{Q|cI{?Q4%_P7$(BNYUJnn' );
define( 'LOGGED_IN_KEY',    'X* XF=||=cfEZ#tv7[/sW*LLrO29f8mFwF6!hT2o%*faxIlUgbwdui0dvUNFT t.' );
define( 'NONCE_KEY',        'Qo,+i3/ffE}P}TI^Q%$l_o$ #=%7EWFlsKx~kxT?Tn&y+(XX1h|_,}S&}b:^30]w' );
define( 'AUTH_SALT',        '!LHt9cK;A13n3#)@DV~-cU<Kl!W_^:}x[:HM.0}6yt:N>P{+_!,_M}No38B:~smc' );
define( 'SECURE_AUTH_SALT', 'ZCKAO^IS~9A9RH3n!gSW>QG&=gd0s-7:y_>0Ia638ia-w2aLQE[$){VJ6cpxY6nS' );
define( 'LOGGED_IN_SALT',   'px}P;>CG3uE?#*6(58esgflnI@#ZRFsX194$=>&R[!#@aT-ApTpe^#yQ(A*`i3C3' );
define( 'NONCE_SALT',       '5iE#N<@eP GfpV/fu6itku(7t[JO^tx>WAO<J=~0FpdGEp|2Zts[4+Mq2&l<(2Uc' );
/**#@-*/

/**
 * Préfixe de base de données pour les tables de WordPress.
 *
 * Vous pouvez installer plusieurs WordPress sur une seule base de données
 * si vous leur donnez chacune un préfixe unique.
 * N’utilisez que des chiffres, des lettres non-accentuées, et des caractères soulignés !
 */
$table_prefix = 'wp_';

/**
 * Pour les développeurs et développeuses : le mode déboguage de WordPress.
 *
 * En passant la valeur suivante à "true", vous activez l’affichage des
 * notifications d’erreurs pendant vos essais.
 * Il est fortement recommandé que les développeurs et développeuses d’extensions et
 * de thèmes se servent de WP_DEBUG dans leur environnement de
 * développement.
 *
 * Pour plus d’information sur les autres constantes qui peuvent être utilisées
 * pour le déboguage, rendez-vous sur la documentation.
 *
 * @link https://fr.wordpress.org/support/article/debugging-in-wordpress/
 */
define('WP_DEBUG', false);

/* C’est tout, ne touchez pas à ce qui suit ! Bonne publication. */

/** Chemin absolu vers le dossier de WordPress. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Réglage des variables de WordPress et de ses fichiers inclus. */
require_once(ABSPATH . 'wp-settings.php');
