/**
 * Special Event Popup - Frontend behavior.
 *
 * Handles display timing, visitor frequency rules, close interactions,
 * and a lightweight confetti animation for celebration popups.
 */
( function () {
	'use strict';

	var settings = window.sepPopupSettings || {};

	/**
	 * Read a cookie value by name.
	 */
	function getCookie( name ) {
		var match = document.cookie.match( '(?:^|; )' + name.replace( /([.$?*|{}()[\]\\/+^])/g, '\\$1' ) + '=([^;]*)' );
		return match ? decodeURIComponent( match[ 1 ] ) : null;
	}

	/**
	 * Set a cookie with the given number of days until expiry.
	 */
	function setCookie( name, value, days ) {
		var expires = '';
		if ( days ) {
			var date = new Date();
			date.setTime( date.getTime() + days * 24 * 60 * 60 * 1000 );
			expires = '; expires=' + date.toUTCString();
		}
		document.cookie = name + '=' + encodeURIComponent( value ) + expires + '; path=/; SameSite=Lax';
	}

	/**
	 * Determine whether the popup has already been shown according to the
	 * configured frequency, using localStorage (per visitor) or a cookie
	 * (per day / every visit tracking).
	 */
	function alreadySeen() {
		var cookieName = settings.cookieName || 'sep_popup_seen';

		if ( 'once_per_visitor' === settings.frequency ) {
			try {
				return window.localStorage && 'yes' === window.localStorage.getItem( cookieName );
			} catch ( e ) {
				return false;
			}
		}

		if ( 'once_per_day' === settings.frequency ) {
			return 'yes' === getCookie( cookieName );
		}

		// every_visit
		return false;
	}

	/**
	 * Record that the popup has been shown.
	 */
	function markAsSeen() {
		var cookieName = settings.cookieName || 'sep_popup_seen';

		if ( 'once_per_visitor' === settings.frequency ) {
			try {
				if ( window.localStorage ) {
					window.localStorage.setItem( cookieName, 'yes' );
				}
			} catch ( e ) {
				// Storage unavailable (e.g. private browsing) - fail silently.
			}
			return;
		}

		if ( 'once_per_day' === settings.frequency ) {
			setCookie( cookieName, 'yes', settings.cookieDays || 1 );
		}
	}

	/**
	 * Show the popup overlay and trigger optional confetti.
	 */
	function showPopup( overlay ) {
		overlay.removeAttribute( 'hidden' );

		// Allow the browser to apply the removed [hidden] before transitioning.
		window.requestAnimationFrame( function () {
			overlay.classList.add( 'sep-popup-visible' );
		} );

		markAsSeen();

		if ( settings.confetti ) {
			startConfetti( overlay.querySelector( '#sep-confetti-canvas' ) );
		}
	}

	/**
	 * Hide the popup overlay.
	 */
	function hidePopup( overlay ) {
		overlay.classList.remove( 'sep-popup-visible' );

		var onTransitionEnd = function () {
			overlay.setAttribute( 'hidden', 'hidden' );
			overlay.removeEventListener( 'transitionend', onTransitionEnd );
		};

		overlay.addEventListener( 'transitionend', onTransitionEnd );
	}

	/**
	 * Lightweight canvas confetti animation (no external libraries).
	 */
	function startConfetti( canvas ) {
		if ( ! canvas || ! canvas.getContext ) {
			return;
		}

		var ctx = canvas.getContext( '2d' );
		var colors = [ '#ff6b6b', '#feca57', '#48dbfb', '#1dd1a1', '#5f27cd', '#ff9ff3' ];
		var particles = [];
		var particleCount = 80;
		var running = true;

		function resize() {
			canvas.width = canvas.offsetWidth;
			canvas.height = canvas.offsetHeight;
		}

		resize();
		window.addEventListener( 'resize', resize );

		for ( var i = 0; i < particleCount; i++ ) {
			particles.push( {
				x: Math.random() * canvas.width,
				y: -Math.random() * canvas.height,
				size: 4 + Math.random() * 6,
				color: colors[ Math.floor( Math.random() * colors.length ) ],
				speedY: 2 + Math.random() * 3,
				speedX: -1 + Math.random() * 2,
				rotation: Math.random() * 360,
				rotationSpeed: -4 + Math.random() * 8,
			} );
		}

		function tick() {
			if ( ! running ) {
				return;
			}

			ctx.clearRect( 0, 0, canvas.width, canvas.height );

			var stillFalling = false;

			particles.forEach( function ( p ) {
				p.y += p.speedY;
				p.x += p.speedX;
				p.rotation += p.rotationSpeed;

				if ( p.y < canvas.height + p.size ) {
					stillFalling = true;
				}

				ctx.save();
				ctx.translate( p.x, p.y );
				ctx.rotate( ( p.rotation * Math.PI ) / 180 );
				ctx.fillStyle = p.color;
				ctx.fillRect( -p.size / 2, -p.size / 2, p.size, p.size * 0.6 );
				ctx.restore();
			} );

			if ( stillFalling ) {
				window.requestAnimationFrame( tick );
			} else {
				running = false;
				ctx.clearRect( 0, 0, canvas.width, canvas.height );
				window.removeEventListener( 'resize', resize );
			}
		}

		window.requestAnimationFrame( tick );
	}

	function init() {
		var overlay = document.getElementById( 'sep-popup-overlay' );

		if ( ! overlay ) {
			return;
		}

		if ( alreadySeen() ) {
			return;
		}

		var box = overlay.querySelector( '.sep-popup-box' );
		var closeBtn = overlay.querySelector( '.sep-popup-close' );

		if ( closeBtn && settings.closeButton ) {
			closeBtn.addEventListener( 'click', function () {
				hidePopup( overlay );
			} );
		}

		if ( settings.closeOutside ) {
			overlay.addEventListener( 'click', function ( event ) {
				if ( box && ! box.contains( event.target ) ) {
					hidePopup( overlay );
				}
			} );
		}

		if ( settings.closeEsc ) {
			document.addEventListener( 'keydown', function ( event ) {
				if ( 'Escape' === event.key && overlay.classList.contains( 'sep-popup-visible' ) ) {
					hidePopup( overlay );
				}
			} );
		}

		var delay = parseInt( settings.delaySeconds, 10 ) || 0;

		if ( delay > 0 ) {
			window.setTimeout( function () {
				showPopup( overlay );
			}, delay * 1000 );
		} else {
			showPopup( overlay );
		}
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
