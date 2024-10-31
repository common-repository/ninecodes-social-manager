/*eslint no-unused-vars: ["error", { "vars": "local", "varsIgnorePattern": "^toggle" }]*/
jQuery(function($) {
	'use strict';

	/**
	 * The Backbone View to toggle controls visibiity.
	 */
	var Toggle = {
			View: {}
		},
		ToggleControls,
		toggleControls;

	Toggle.View = Backbone.View.extend({
		el: $('#ninecodes-social-manager-wrap').find('.toggle-control')
	});

	ToggleControls = Toggle.View.extend({

		events: {
			'click': 'toggleControls'
		},

		/**
		 * Initialize the View.
		 * Get the target elements attached to the trigger ($el),
		 * and set the elements visibiity on page load.
		 *
		 * @return {Void} This is executed on initialization, and does not return anything.
		 */
		initialize: function() {

			this.$target = $(this.$el.data('toggle'));

			this.toggleControls();
		},

		/**
		 * The function that toggles the target elements
		 * following the trigger value.
		 *
		 * @return {Void} Nothing
		 */
		toggleControls: function() {

			this.$target.toggleClass('hide-if-js', !this.$el.is(':checked'));
		}
	});

	/**
	 * Instantiation
	 *
	 * @type {ToggleControls}
	 */
	toggleControls = new ToggleControls();
});
