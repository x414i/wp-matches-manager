/**
 * United Misrata Matches Manager — Tabs JS
 * Handles tab switching for the [united_matches view="tabs"] shortcode.
 * Vanilla JS, no jQuery dependency.
 *
 * @package UMMM
 */

( function () {
	'use strict';

	/**
	 * Initialize a single tabs widget.
	 *
	 * @param {HTMLElement} wrapper The .ummm-tabs-view element.
	 */
	function initTabs( wrapper ) {
		var buttons = wrapper.querySelectorAll( '.ummm-tabs__btn' );
		var panels  = wrapper.querySelectorAll( '.ummm-tabs__panel' );

		if ( ! buttons.length ) {
			return;
		}

		buttons.forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var targetId = btn.getAttribute( 'data-tab' );
				var targetPanel = document.getElementById( targetId );

				if ( ! targetPanel ) {
					return;
				}

				// Deactivate all.
				buttons.forEach( function ( b ) {
					b.classList.remove( 'ummm-tabs__btn--active' );
					b.setAttribute( 'aria-selected', 'false' );
				} );
				panels.forEach( function ( p ) {
					p.classList.remove( 'ummm-tabs__panel--active' );
				} );

				// Activate selected.
				btn.classList.add( 'ummm-tabs__btn--active' );
				btn.setAttribute( 'aria-selected', 'true' );
				targetPanel.classList.add( 'ummm-tabs__panel--active' );
			} );
		} );
	}

	/**
	 * Bootstrap all tabs widgets on the page.
	 */
	function init() {
		var wrappers = document.querySelectorAll( '.ummm-tabs-view' );
		wrappers.forEach( initTabs );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
}() );
