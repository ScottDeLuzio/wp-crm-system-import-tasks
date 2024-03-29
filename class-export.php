<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
class WPCRM_System_Export_Tasks extends WPCRM_System_Export{
	/**
	 * Our export type. Used for export-type specific filters/actions
	 * @var string
	 * @since 2.1
	 */
	public $export_type = 'wpcrm-task';

	/**
	 * Set the CSV columns
	 *
	 * @access public
	 * @since 2.1
	 * @return array $cols All the columns
	 */
	public function csv_cols() {

		$cols = array(
			'task_name' 		=> __( 'Task Name', 'wp-crm-system-import-contacts' ),
			'assigned' 			=> __( 'Assigned to', 'wp-crm-system-import-contacts' ),
			'organization'		=> __( 'Organization Name', 'wp-crm-system-import-contacts' ),
			'organization_id'	=> __( 'Organization ID', 'wp-crm-system-import-contacts' ),
			'contact'			=> __( 'Contact Name', 'wp-crm-system-import-contacts' ),
			'contact_id'		=> __( 'Contact ID', 'wp-crm-system-import-contacts' ),
			'due_date'			=> __( 'Due Date', 'wp-crm-system-import-contacts' ),
			'start_date'		=> __( 'Start Date', 'wp-crm-system-import-contacts' ),
			'progress'			=> __( 'Progress', 'wp-crm-system-import-contacts' ),
			'priority'			=> __( 'Priority', 'wp-crm-system-import-contacts' ),
			'status'			=> __( 'Status', 'wp-crm-system-import-contacts' ),
			'description'		=> __( 'Description', 'wp-crm-system-import-contacts' )
		);

		if( defined( 'WPCRM_CUSTOM_FIELDS' ) ){
			$field_count = get_option( '_wpcrm_system_custom_field_count' );
			if( $field_count ){
				$custom_fields = array();
				for( $field = 1; $field <= $field_count; $field++ ){
					// Make sure we want this field to be imported.
					$field_scope = get_option( '_wpcrm_custom_field_scope_' . $field );
					$can_export = $field_scope == $this->export_type ? true : false;
					if( $can_export ){
						$custom_fields[] = get_option( '_wpcrm_custom_field_name_' . $field );
					}
				}
				$cols = array_merge( $cols, $custom_fields );
			}
		}

		$cols = apply_filters( 'wpcrm_system_export_cols_' . $this->export_type, $cols );

		return $cols;
	}

	/**
	 * Get the Export Data
	 *
	 * @access public
	 * @since 2.1
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {
		$get_ids = $this->get_cpt_post_ids();
		foreach ( $get_ids as $id ){
			$data[$id] = array(
				'task_name' 		=> get_the_title( $id ),
				'assigned' 			=> get_post_meta( $id, '_wpcrm_task-assignment', true ),
				'organization'		=> get_the_title( get_post_meta( $id, '_wpcrm_task-attach-to-organization', true ) ),
				'organization_id'	=> get_post_meta( $id, '_wpcrm_task-attach-to-organization', true ),
				'contact'			=> get_the_title( get_post_meta( $id, '_wpcrm_task-contact', true ) ),
				'contact_id'		=> get_post_meta( $id, '_wpcrm_task-contact', true ),
				'due_date'			=> date( get_option( 'wpcrm_system_php_date_format' ), get_post_meta( $id, '_wpcrm_task-due-date', true ) ),
				'start_date'		=> date( get_option( 'wpcrm_system_php_date_format' ), get_post_meta( $id, '_wpcrm_task-start-date', true ) ),
				'progress'			=> get_post_meta( $id, '_wpcrm_task-progress', true ),
				'priority'			=> get_post_meta( $id, '_wpcrm_task-priority', true ),
				'status'			=> get_post_meta( $id, '_wpcrm_task-status', true ),
				'description'		=> esc_html( get_post_meta( $id, '_wpcrm_task-description', true ) )
			);
			if( defined( 'WPCRM_CUSTOM_FIELDS' ) ){
				$field_count 	= get_option( '_wpcrm_system_custom_field_count' );
				if( $field_count ){
					for( $field = 1; $field <= $field_count; $field++ ){
						// Make sure we want this field to be imported.
						$field_scope 	= get_option( '_wpcrm_custom_field_scope_' . $field );
						$field_type		= get_option( '_wpcrm_custom_field_type_' . $field );
						$can_export 	= $field_scope == $this->export_type ? true : false;
						if( $can_export ){
							$value 	= get_post_meta( $id, '_wpcrm_custom_field_id_' . $field, true );
							switch ( $field_type ) {
								case 'datepicker':
									$export = date( get_option( 'wpcrm_system_php_date_format' ), $value );
									break;
								case 'repeater-date':
									if ( is_array( $value ) ){
										foreach ( $value as $key => $v ){
											$values[$key] = date( get_option( 'wpcrm_system_php_date_format' ), $v );
										}
										$export = implode( ',', $values );
									} else {
										$export = '';
									}
									break;
								case 'repeater-file':
								case 'repeater-text':
								case 'repeater-textarea':
									if ( is_array( $value ) ){
										$export = implode( ',', $value );
									} else {
										$export = '';
									}
									break;
								default:
									$export = $value;
									break;
							}
							$data[$id][] = $export;
						}
					}
				}
			}
		}

		$data = apply_filters( 'wpcrm_system_export_get_data', $data );
		$data = apply_filters( 'wpcrm_system_export_get_data_' . $this->export_type, $data );

		return $data;
	}

}