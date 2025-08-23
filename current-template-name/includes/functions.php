<?php
if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function pagely_register_page_metas() {
    register_post_meta( '', 'pagely_page_metas', array(
        'single' => true,
        'type'   => 'array',
        'auth_callback' => function() {
            return current_user_can( 'edit_posts' );
        },
        'show_in_rest' => false,
    ));
}
add_action( 'init', 'pagely_register_page_metas' );


function pagely_add_settings_metabox() {
    add_meta_box(
        'pagely_settings',
        __( 'Pagely', 'pagely' ),
        'pagely_render_settings_metabox',
        ['post', 'page'],
        'side',
        'high',
    );
}
add_action( 'add_meta_boxes', 'pagely_add_settings_metabox' );

function pagely_render_settings_metabox( $post ) {
    $metas = get_post_meta( $post->ID, 'pagely_page_metas', true );
    $metas = is_array( $metas ) ? $metas : [];

    wp_nonce_field( 'pagely_save_settings', 'pagely_settings_nonce' );

    $fields = [
        'hide_page_title'     => [ 
            'label' => 'Hide Page Title', 
            'desc' => 'If checked, the page title will not appear on the frontend.' 
        ],
        'exclude_from_search' => [ 
            'label' => 'Exclude from Search', 
            'desc' => 'This page will not appear in WordPress search results.' 
        ],
        'exclude_from_archive'=> [ 
            'label' => 'Exclude from Archive', 
            'desc' => 'This page will not appear in category/tag/archive listings.' 
        ],
        'hide_admin_bar'      => [ 
            'label' => 'Hide Admin Bar', 
            'desc' => 'Hide the admin bar for this page when viewed by logged-in users.' 
        ],
    ];

    ?>
    <style>
        .pgly-checkbox-group {
            margin-bottom: 10px;
        }
        .pgly-checkbox-group label {
            display:flex;
            align-items: center;
            margin-bottom: 3px;
        }

        .pgly-checkbox-group input, .pgly-checkbox-group h2 {
            margin: 0 !important;
            padding: 0 !important;
        }

        .pgly-checkbox-group input[type=checkbox] {
            margin-right: 5px !important;
        }

        .pagely-settings-group small {
            font-size: 12px;
        }

    </style>
    <?php
    echo '<div class="pagely-settings-group">';
    foreach ( $fields as $key => $field ) {
        $checked = ! empty( $metas[$key] ) ? 'checked' : '';
        echo '<div class="pgly-checkbox-group"><label>';
        echo '<input type="checkbox" name="pagely_page_metas['. esc_attr($key) .']" value="1" '. $checked .' />';
        echo ' <h2><strong>'. esc_html($field['label']) .'</strong></h2>';
        echo '</label><small>'. esc_html($field['desc']) .'</small></div>';
    }
    echo '</div>';

    $note = isset( $metas['page_note'] ) ? esc_textarea( $metas['page_note'] ) : '';
    echo '<label for="pagely_page_note"><strong>'. __('Page Notes', 'current-template-name') .'</strong></label>';
    echo '<p style="margin-top:0;">'. __('Write some notes you may need later for the page.', 'current-template-name') .'</p>';
    echo '<textarea id="pagely_page_note" name="pagely_page_metas[page_note]" rows="4" style="width:100%;">'. $note .'</textarea>';
}

function pagely_save_settings_metabox( $post_id ) {
    if ( ! isset( $_POST['pagely_settings_nonce'] ) || 
        ! wp_verify_nonce( $_POST['pagely_settings_nonce'], 'pagely_save_settings' ) ) {
        return;
    }

    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;

    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    if ( isset( $_POST['pagely_page_metas'] ) && is_array( $_POST['pagely_page_metas'] ) ) {
        $metas = [
            'hide_page_title'     => ! empty($_POST['pagely_page_metas']['hide_page_title']) ? 1 : 0,
            'exclude_from_search' => ! empty($_POST['pagely_page_metas']['exclude_from_search']) ? 1 : 0,
            'exclude_from_archive'=> ! empty($_POST['pagely_page_metas']['exclude_from_archive']) ? 1 : 0,
            'hide_admin_bar'      => ! empty($_POST['pagely_page_metas']['hide_admin_bar']) ? 1 : 0,
            'page_note'           => sanitize_textarea_field( $_POST['pagely_page_metas']['page_note'] ?? '' ),
        ];
        update_post_meta( $post_id, 'pagely_page_metas', $metas );
    } else {
        delete_post_meta( $post_id, 'pagely_page_metas' );
    }
}
add_action( 'save_post', 'pagely_save_settings_metabox' );


add_action('rest_api_init', function() {
    register_rest_route('pagely/v1', '/save-scripts', [
        'methods'  => 'POST',
        'callback' => 'pagely_save_custom_scripts',
        'permission_callback' => function() {
            return current_user_can('edit_posts');
        },
    ]);

    register_rest_route('pagely/v1', '/get-scripts', [
        'methods'  => 'GET',
        'callback' => 'pagely_get_custom_scripts',
        'permission_callback' => function() {
            return current_user_can('edit_posts');
        },
    ]);
});

function pagely_save_custom_scripts( $request ) {
    $post_id = intval( $request->get_param( 'post_id' ) );

    if ( ! $post_id ) {
        return new WP_Error(
            'invalid_data',
            __( 'Invalid post ID', 'current-template-name' ),
            [ 'status' => 400 ]
        );
    }

    // Get CSS, JS, SCSS from the request
    $scripts = [
        'css'  => $request->get_param( 'css' ) ?: [ 'content' => '', 'enabled' => false ],
        'js'   => $request->get_param( 'js' ) ?: [ 'content' => '', 'enabled' => false ],
        'scss' => $request->get_param( 'scss' ) ?: [ 'content' => '', 'enabled' => false ],
    ];

    update_post_meta( $post_id, 'pagely_custom_scripts', $scripts );

    return [ 'success' => true ];
}


function pagely_get_custom_scripts($request) {
    $post_id = intval($request->get_param('post_id'));
    if (!$post_id) return new WP_Error('invalid_post', __('Invalid post ID', 'current-template-name'), ['status' => 400]);

    $scripts = get_post_meta($post_id, 'pagely_custom_scripts', true);
    if (!$scripts) $scripts = [
        'css'  => ['enabled' => false, 'content' => ''],
        'js'   => ['enabled' => false, 'content' => ''],
        'scss' => ['enabled' => false, 'content' => '']
    ];

    return $scripts;
}

/**
 * Compile SCSS → CSS using scssphp (if available).
 *
 * @param string $scss_code
 * @return string CSS or empty string on failure.
 */
function pgly_compile_scss_to_css( $scss_code ) {
	// Requires scssphp via Composer: scssphp/scssphp
	if ( ! class_exists( '\ScssPhp\ScssPhp\Compiler' ) ) {
		// Optional: error_log('[pgly] SCSS compiler not found.');
		return '';
	}

	try {
		$compiler = new \ScssPhp\ScssPhp\Compiler();

		// Allow import paths/vars to be customized if you use @import/@use
		$import_paths = (array) apply_filters(
			'pgly_scss_import_paths',
			[ get_stylesheet_directory() . '/', get_template_directory() . '/' ]
		);
		foreach ( $import_paths as $path ) {
			$compiler->setImportPaths( rtrim( (string) $path, '/' ) . '/' );
		}

		$variables = (array) apply_filters( 'pgly_scss_variables', [] );
		if ( ! empty( $variables ) && method_exists( $compiler, 'setVariables' ) ) {
			$compiler->setVariables( $variables );
		}

		$out = $compiler->compileString( (string) $scss_code );
		return method_exists( $out, 'getCss' ) ? (string) $out->getCss() : (string) $out;
	} catch ( \Throwable $e ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( '[pgly] SCSS compile error: ' . $e->getMessage() );
		}
		return '';
	}
}
