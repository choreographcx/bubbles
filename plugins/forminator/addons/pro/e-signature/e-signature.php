<?php
/**
 * E-Signature
 *
 * @package Forminator
 */

/**
 * Integration Name: E-Signature
 * Version: 1.0
 * Plugin URI:  https://wpmudev.com/
 * Description: E-Signature field for Forminator
 * Author: WPMU DEV
 * Author URI: http://wpmudev.com
 */
class Forminator_E_Signature {
	/**
	 * Forminator_E_Signature Instance
	 *
	 * @var self|null
	 */
	private static $_instance = null;

	/**
	 * Get Instance
	 *
	 * @since 1.0 Signature Integration
	 * @return self|null
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Forminator_E_Signature constructor.
	 *
	 * @return void
	 */
	private function __construct() {
		if ( ! FORMINATOR_PRO ) {
			add_filter( 'forminator_pro_fields', array( 'Forminator_E_Signature', 'add_pro_field' ) );
			return;
		}
		add_filter( 'forminator_fields', array( 'Forminator_E_Signature', 'add_signature_field' ) );
		add_filter( 'forminator_entry_meta_value_to_string', array( 'Forminator_E_Signature', 'meta_value_to_string' ), 10, 5 );
		add_filter( 'forminator_handle_specific_field_types', array( 'Forminator_E_Signature', 'handle_signature' ), 10, 3 );
	}

	/**
	 * Add signature field
	 *
	 * @param array $fields Fields.
	 *
	 * @return mixed
	 */
	public static function add_signature_field( $fields ) {
		if ( ! self::check_pdf_post() ) {
			require_once 'library/signature_field.php';
			$fields[] = new Forminator_Signature_Field();
		}

		return $fields;
	}

	/**
	 * Get pro fields for showing them as promo for pro version
	 *
	 * @param array $pro_fields Pro fields.
	 * @return array
	 */
	public static function add_pro_field( $pro_fields ) {
		if ( ! self::check_pdf_post() ) {
			require_once 'library/signature_field.php';
			$signature    = new Forminator_Signature_Field();
			$pro_fields[] = $signature->get_pro_field();
		}

		return $pro_fields;
	}

	/**
	 * Convert signature to string
	 *
	 * @param string $string_value Value.
	 * @param string $field_type Field type.
	 * @param array  $meta_value Meta value.
	 * @param bool   $allow_html Allow HTML.
	 * @param int    $truncate Truncate.
	 * @return string
	 */
	public static function meta_value_to_string( $string_value, $field_type, $meta_value, $allow_html, $truncate ) {
		if ( 'signature' === $field_type ) {
			$file = '';
			if ( isset( $meta_value['file'] ) ) {
				$file = $meta_value['file'];
			}
			if ( ! empty( $file ) && is_array( $file ) && isset( $file['file_url'] ) ) {
				$string_value = $file['file_url'];
				if ( $allow_html ) {
					// make image.
					$url       = $string_value;
					$file_name = basename( $url );
					$file_name = ! empty( $file_name ) ? $file_name : esc_html__( '(no filename)', 'forminator' );
					// truncate.
					if ( strlen( $file_name ) > $truncate ) {
						$file_name = substr( $file_name, 0, $truncate ) . '...';
					}
					$string_value = '<a href="' . esc_url( $url ) . '" target="_blank"><img src="' . esc_url( $url ) . '" alt="' . esc_attr( $file_name ) . '" width="100" /></a>';
				} elseif ( strlen( $string_value ) > $truncate ) {
					// truncate url.
					$string_value = substr( $string_value, 0, $truncate ) . '...';
				}
			} else {
				$string_value = '';
			}
		}

		return $string_value;
	}

	/**
	 * Handle signature
	 *
	 * @param array  $field_data Field data.
	 * @param object $form_field_obj Form Field.
	 * @param array  $field_array Field array.
	 * @return array|string
	 */
	public static function handle_signature( $field_data, $form_field_obj, $field_array ) {
		if ( 'signature' === $field_array['type'] ) {
			$upload_data = $form_field_obj->handle_sign_upload( $field_array );
			if ( ! empty( $upload_data['success'] ) && $upload_data['success'] ) {
				$field_data['file'] = $upload_data;
			} elseif ( isset( $upload_data['success'] ) && false === $upload_data['success'] ) {
				$response = array(
					'return'  => true,
					'message' => $upload_data['message'],
					'errors'  => array(),
					'success' => false,
				);

				return $response;
			} else {
				// no sign uploaded for this field_id.
				$field_data = '';
			}
		}

		return $field_data;
	}

	/**
	 * Check pdf form
	 *
	 * @return bool
	 */
	public static function check_pdf_post() {
		$form_id    = filter_input( INPUT_GET, 'id', FILTER_VALIDATE_INT );
		$form_model = Forminator_Base_Form_Model::get_model( $form_id );
		if ( is_object( $form_model ) ) {
			if ( 'pdf_form' === $form_model->status ) {
				return true;
			}
		}

		return false;
	}
}

Forminator_E_Signature::get_instance();