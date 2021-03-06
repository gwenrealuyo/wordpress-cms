<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Weglot\Client\Client;
use Weglot\Util\Regex\RegexEnum;

use WeglotWP\Helpers\Helper_Tabs_Admin_Weglot;
use WeglotWP\Helpers\Helper_Excluded_Type;

$options_available = [
	'exclude_urls'     => [
		'key'         => 'exclude_urls',
		'label'       => __( 'Exclusion URL', 'weglot' ),
		'description' => __( 'Add URL that you want to exclude from translations. You can use regular expression to match multiple URLs. ', 'weglot' ),
	],
	'exclude_blocks'   => [
		'key'         => 'exclude_blocks',
		'label'       => __( 'Exclusion Blocks', 'weglot' ),
		'description' => __( 'Enter the CSS selector of blocks you don\'t want to translate (like a sidebar, a menu, a paragraph, etc...)', 'weglot' ),
	],
	'auto_redirect'    => [
		'key'         => 'auto_redirect',
		'label'       => __( 'Auto redirection', 'weglot' ),
		'description' => __( 'Check if you want to redirect users based on their browser language.', 'weglot' ),
	],
	'email_translate'  => [
		'key'         => 'email_translate',
		'label'       => __( 'Translate email', 'weglot' ),
		'description' => __( 'Check to translate all emails who use function wp_mail', 'weglot' ),
	],
	'translate_amp'    => [
		'key'         => 'translate_amp',
		'label'       => __( 'Translate AMP', 'weglot' ),
		'description' => __( 'Translate AMP page', 'weglot' ),
	],
	'active_search'    => [
		'key'         => 'active_search',
		'label'       => __( 'Search WordPress', 'weglot' ),
		'description' => __( 'Allow your users to search in the language they use.', 'weglot' ),
	],
	'private_mode'     => [
		'key'         => 'private_mode',
		'label'       => __( 'Private mode', 'weglot' ),
		'description' => __( 'Check if your only want admin users to see the translations', 'weglot' ),
	],
	'active_wc_reload' => [
		'key'         => 'active_wc_reload',
		'label'       => __( '[WooCommerce] : Prevent reload cart', 'weglot' ),
		'description' => __( 'You should only enable this option if you have translation errors on your cart widget.', 'weglot' ),
	]
];

?>

<h3><?php esc_html_e( 'Translation Exclusion (Optional)', 'weglot' ); ?> </h3>
<hr>
<p><?php esc_html_e( 'By default, every page is translated. You can exclude parts of a page or a full page here.', 'weglot' ); ?></p>
<table class="form-table">
	<tbody>
	<tr valign="top">
		<th scope="row" class="titledesc">
			<label for="<?php echo esc_attr( $options_available['exclude_urls']['key'] ); ?>">
				<?php echo esc_html( $options_available['exclude_urls']['label'] ); ?>
			</label>
			<p class="sub-label"><?php echo esc_html( $options_available['exclude_urls']['description'] ); ?></p>
		</th>
		<td class="forminp forminp-text">
			<a class="btn btn-soft" href="https://dashboard.weglot.com/settings/exclusions" target="_blank"><span
					class="dashicons dashicons-admin-generic"></span> <?php esc_html_e( 'Manage URL to exclude', 'weglot' ); ?>
			</a>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row" class="titledesc">
			<label for="<?php echo esc_attr( $options_available['exclude_blocks']['key'] ); ?>">
				<?php echo esc_html( $options_available['exclude_blocks']['label'] ); ?>
			</label>
			<p class="sub-label"><?php echo esc_html( $options_available['exclude_blocks']['description'] ); ?></p>
		</th>
		<td class="forminp forminp-text">
			<div id="container-<?php echo esc_attr( $options_available['exclude_blocks']['key'] ); ?>">
				<?php
				if ( ! empty( $this->options[ $options_available['exclude_blocks']['key'] ] ) ) :
					foreach ( $this->options[ $options_available['exclude_blocks']['key'] ] as $option ) :
						?>
						<div class="item-exclude">
							<input
								type="text"
								placeholder=".my-class"
								name="<?php echo esc_attr( sprintf( '%s[excluded_blocks][][value]', WEGLOT_SLUG ) ); ?>"
								value="<?php echo esc_attr( $option ); ?>"
							>
							<button class="js-btn-remove js-btn-remove-exclude">
								<span class="dashicons dashicons-minus"></span>
							</button>
						</div>
					<?php
					endforeach;
				endif;
				?>
			</div>
			<button id="js-add-exclude-block" class="btn btn-soft"><span
					class="dashicons dashicons-plus-alt"></span> <?php esc_html_e( 'Add a block to exclude', 'weglot' ); ?>
			</button>
		</td>
	</tr>
	</tbody>
</table>

<h3><?php esc_html_e( 'Other options (Optional)', 'weglot' ); ?></h3>
<hr>
<table class="form-table">
	<tbody>
	<tr valign="top">
		<th scope="row" class="titledesc">
			<label for="<?php echo esc_attr( $options_available['auto_redirect']['key'] ); ?>">
				<?php echo esc_html( $options_available['auto_redirect']['label'] ); ?>
			</label>
		</th>
		<td class="forminp forminp-text">
			<input
				name="<?php echo esc_attr( sprintf( '%s[auto_switch]', WEGLOT_SLUG ) ); ?>"
				id="<?php echo esc_attr( $options_available['auto_redirect']['key'] ); ?>"
				type="checkbox"
				<?php checked( $this->options[ $options_available['auto_redirect']['key'] ], 1 ); ?>
			>
			<p class="description"><?php echo esc_html( $options_available['auto_redirect']['description'] ); ?></p>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row" class="titledesc">
			<label for="<?php echo esc_attr( $options_available['email_translate']['key'] ); ?>">
				<?php echo esc_html( $options_available['email_translate']['label'] ); ?>
			</label>
		</th>
		<td class="forminp forminp-text">
			<input
				name="<?php echo esc_attr( sprintf( '%s[custom_settings][translate_email]', WEGLOT_SLUG ) ); ?>"
				id="<?php echo esc_attr( $options_available['email_translate']['key'] ); ?>"
				type="checkbox"
				<?php checked( $this->options[ $options_available['email_translate']['key'] ], 1 ); ?>
			>
			<p class="description"><?php echo esc_html( $options_available['email_translate']['description'] ); ?></p>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row" class="titledesc">
			<label for="<?php echo esc_attr( $options_available['translate_amp']['key'] ); ?>">
				<?php echo esc_html( $options_available['translate_amp']['label'] ); ?>
			</label>
		</th>
		<td class="forminp forminp-text">
			<input
				name="<?php echo esc_attr( sprintf( '%s[custom_settings][translate_amp]', WEGLOT_SLUG ) ); ?>"
				id="<?php echo esc_attr( $options_available['translate_amp']['key'] ); ?>"
				type="checkbox"
				<?php checked( $this->options[ $options_available['translate_amp']['key'] ], 1 ); ?>
			>
			<p class="description"><?php echo esc_html( $options_available['translate_amp']['description'] ); ?></p>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row" class="titledesc">
			<label for="<?php echo esc_attr( $options_available['active_search']['key'] ); ?>">
				<?php echo esc_html( $options_available['active_search']['label'] ); ?>
			</label>
		</th>
		<td class="forminp forminp-text">
			<input
				name="<?php echo esc_attr( sprintf( '%s[custom_settings][translate_search]', WEGLOT_SLUG ) ); ?>"
				id="<?php echo esc_attr( $options_available['active_search']['key'] ); ?>"
				type="checkbox"
				<?php checked( $this->options[ $options_available['active_search']['key'] ], 1 ); ?>
			>
			<p class="description"><?php echo esc_html( $options_available['active_search']['description'] ); ?></p>
		</td>
	</tr>
	</tbody>
</table>

<template id="tpl-exclusion-url">
	<div class="item-exclude">
		<select
			name="<?php echo esc_attr( sprintf( '%s[excluded_paths][{KEY}][type]', WEGLOT_SLUG ) ); ?>"
		>
			<?php foreach ( Helper_Excluded_Type::get_excluded_type() as $ex_type ) : ?>
				<option
					value="<?php echo esc_attr( $ex_type ); ?>"><?php echo esc_attr( Helper_Excluded_Type::get_label_type( $ex_type ) ); ?></option>
			<?php endforeach; ?>
		</select>
		<input
			type="text"
			placeholder="/my-awesome-url"
			name="<?php echo esc_attr( sprintf( '%s[excluded_paths][{KEY}][value]', WEGLOT_SLUG ) ); ?>"
			value=""
		>
		<button class="js-btn-remove js-btn-remove-exclude">
			<span class="dashicons dashicons-minus"></span>
		</button>
	</div>
</template>

<template id="tpl-exclusion-block">
	<div class="item-exclude">
		<input
			type="text"
			placeholder=".my-class"
			name="<?php echo esc_attr( sprintf( '%s[excluded_blocks][][value]', WEGLOT_SLUG ) ); ?>"
			value=""
		>
		<button class="js-btn-remove js-btn-remove-exclude">
			<span class="dashicons dashicons-minus"></span>
		</button>
	</div>
</template>
