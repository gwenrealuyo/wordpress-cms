/**
 * Registers a new block provided a unique name and an object defining its behavior.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/#registering-a-block
 */
import { registerBlockType } from '@wordpress/blocks';

/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * Save the inner blocks data
 */
import { InnerBlocks } from '@wordpress/block-editor';

/**
 * Application imports
 */
import { Notice } from '@wordpress/components';
import EditBlock from './edit.js';
import {
	ACCESS_CONTROL_BLOCK_NAME,
	ACCESS_CONTROL_BLOCK_CATEGORY,
} from './block-settings.js';
import { doesAccessControlBlockNotHaveRuleBlocks } from './block-helpers';
import { DEFAULT_SCHEMA_MODE } from '@graphqlapi/components';
import { getEditableOnFocusComponentClass } from '@graphqlapi/components';
/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * All files containing `style` keyword are bundled together. The code used
 * gets applied both to the front of your site and to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './style.scss';

/**
 * Every block starts by registering a new block type definition.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/#registering-a-block
 */
registerBlockType( ACCESS_CONTROL_BLOCK_NAME, {
	/**
	 * This is the display title for your block, which can be translated with `i18n` functions.
	 * The block inserter will show this name.
	 */
	title: __( 'Access Control', 'graphql-api' ),

	/**
	 * This is a short description for your block, can be translated with `i18n` functions.
	 * It will be shown in the Block Tab in the Settings Sidebar.
	 */
	description: __(
		'Configure access control for the GraphQL schema\'s fields and directives',
		'graphql-api'
	),

	/**
	 * Blocks are grouped into categories to help users browse and discover them.
	 * The categories provided by core are `common`, `embed`, `formatting`, `layout` and `widgets`.
	 */
	category: ACCESS_CONTROL_BLOCK_CATEGORY,

	/**
	 * An icon property should be specified to make it easier to identify a block.
	 * These can be any of WordPress??? Dashicons, or a custom svg element.
	 */
	icon: 'admin-users',

	/**
	 * Block default attributes.
	 */
	attributes: {
		/**
		 * Same attribute name as defined in
		 * GraphQLAPI\GraphQLAPI\Services\Blocks\AccessControlBlock::ATTRIBUTE_NAME_SCHEMA_MODE
		 */
		schemaMode: {
			type: 'string',
			default: DEFAULT_SCHEMA_MODE,
		},
		/**
		 * List of selected fields, accessible by their type
		 *
		 * Same attribute name as defined in
		 * GraphQLAPI\GraphQLAPI\Services\Blocks\AbstractControlBlock::ATTRIBUTE_NAME_TYPE_FIELDS
		 */
		typeFields: {
			type: 'array',
			default: [],
		},
		/**
		 * List of selected directives
		 *
		 * Same attribute name as defined in
		 * GraphQLAPI\GraphQLAPI\Services\Blocks\AbstractControlBlock::ATTRIBUTE_NAME_DIRECTIVES
		 */
		directives: {
			type: 'array',
			default: [],
		},
		// Make it wide alignment by default
		align: {
			type: 'string',
			default: 'wide',
		},
	},

	/**
	 * Optional block extended support features.
	 */
	supports: {
		// Alignment options
		align: [ 'wide' ],
		// Remove the support for the custom className.
		customClassName: false,
		// Remove support for an HTML mode.
		html: false,
		// Only insert block through a template
		// inserter: false,
	},

	/**
	 * The edit function describes the structure of your block in the context of the editor.
	 * This represents what the editor will render when the block is used.
	 *
	 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#edit
	 *
	 * @param {Object} [props] Properties passed from the editor.
	 *
	 * @return {WPElement} Element to render.
	 */
	edit(props) {
		const { isSelected, className } = props;

		/**
		 * Pass as prop option "individual schema mode", to let components know if to
		 * add the <SchemaMode /> or not, and corresponding titles
		 */
		const isIndividualControlForSchemaModeEnabled = window.graphqlApiAccessControl ? window.graphqlApiAccessControl.isIndividualControlForSchemaModeEnabled : false;
		return (
			<div class={ className }>
				{ doesAccessControlBlockNotHaveRuleBlocks() &&
					<Notice status="warning" isDismissible={ false }>
						{ __('All Access Control Rule blocks are disabled', 'graphql-api') }
					</Notice>
				}
				<EditBlock
					selectLabel={ __('Define access for:', 'graphql-api') }
					configurationLabel={ isIndividualControlForSchemaModeEnabled ? __('Access Control Rules:', 'graphql-api') : __('Who can access:', 'graphql-api') }
					componentClassName={ getEditableOnFocusComponentClass(isSelected) }
					isIndividualControlForSchemaModeEnabled={ isIndividualControlForSchemaModeEnabled }
					{ ...props }
				/>
			</div>
		)
	},

	/**
	 * The save function defines the way in which the different attributes should be combined
	 * into the final markup, which is then serialized by the block editor into `post_content`.
	 *
	 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#save
	 *
	 * @return {WPElement} Element to render.
	 */
	save() {
		return (
			<InnerBlocks.Content />
		);
	},
} );

export { ACCESS_CONTROL_BLOCK_NAME, ACCESS_CONTROL_BLOCK_CATEGORY };
