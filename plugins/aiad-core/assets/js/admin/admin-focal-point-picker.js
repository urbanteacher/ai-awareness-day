/**
 * Featured-image focal point picker (wp.components.FocalPointPicker) for timeline + resource.
 */
( function ( wp, $ ) {
	if ( ! wp || ! wp.element || ! wp.components ) {
		return;
	}

	const { createElement: el, render, unmountComponentAtNode, useState, useEffect } =
		wp.element;
	const { FocalPointPicker } = wp.components;

	const DEFAULT_POINT = { x: 0.5, y: 0.3 };

	function defaultPointForRoot( root ) {
		const x = parseFloat( root.dataset.defaultX );
		const y = parseFloat( root.dataset.defaultY );
		if ( ! Number.isNaN( x ) && ! Number.isNaN( y ) ) {
			return { x, y };
		}
		return { ...DEFAULT_POINT };
	}

	function parsePoint( raw ) {
		if ( ! raw ) {
			return { ...DEFAULT_POINT };
		}
		try {
			const p = typeof raw === 'string' ? JSON.parse( raw ) : raw;
			if ( p && typeof p.x === 'number' && typeof p.y === 'number' ) {
				return {
					x: Math.min( 1, Math.max( 0, p.x ) ),
					y: Math.min( 1, Math.max( 0, p.y ) ),
				};
			}
		} catch ( e ) {
			/* ignore */
		}
		return { ...DEFAULT_POINT };
	}

	function getFeaturedImageUrlFromDom() {
		const img = document.querySelector( '#postimagediv .inside img' );
		if ( ! img || ! img.src ) {
			return '';
		}
		if ( img.src.indexOf( 'media-button-image' ) !== -1 ) {
			return '';
		}
		return img.src;
	}

	function getFeaturedImageUrlFromEditor() {
		if ( ! wp.data ) {
			return getFeaturedImageUrlFromDom();
		}
		try {
			const imageId = wp.data
				.select( 'core/editor' )
				.getEditedPostAttribute( 'featured_media' );
			if ( ! imageId ) {
				return getFeaturedImageUrlFromDom();
			}
			const media = wp.data.select( 'core' ).getMedia( imageId );
			if ( media && media.source_url ) {
				return media.source_url;
			}
		} catch ( e ) {
			/* classic editor or not ready */
		}
		return getFeaturedImageUrlFromDom();
	}

	function FocalPointControl( { imageUrl, initialPoint, inputX, inputY, coordsEl } ) {
		const [ point, setPoint ] = useState( initialPoint );

		useEffect( () => {
			setPoint( initialPoint );
		}, [ initialPoint.x, initialPoint.y ] );

		function syncInputs( value ) {
			const xPct = Math.round( value.x * 100 );
			const yPct = Math.round( value.y * 100 );
			if ( inputX ) {
				inputX.value = String( xPct );
			}
			if ( inputY ) {
				inputY.value = String( yPct );
			}
			if ( coordsEl ) {
				coordsEl.textContent =
					xPct + '% · ' + yPct + '%';
			}
		}

		function handleChange( value ) {
			setPoint( value );
			syncInputs( value );
		}

		useEffect( () => {
			syncInputs( point );
		}, [] );

		if ( ! imageUrl ) {
			return null;
		}

		return el( FocalPointPicker, {
			url: imageUrl,
			value: point,
			onChange: handleChange,
			onDragStart: handleChange,
			onDrag: handleChange,
		} );
	}

	function mountRoot( root ) {
		const mountEl = root.querySelector( '.aiad-focal-point-picker-mount' );
		const emptyEl = root.querySelector( '.aiad-focal-point-picker-empty' );
		const context = root.dataset.focalContext || 'feed';
		const inputX = root.querySelector(
			'input[name="aiad_thumbnail_focal_' + context + '_x"]'
		);
		const inputY = root.querySelector(
			'input[name="aiad_thumbnail_focal_' + context + '_y"]'
		);
		const coordsEl = root.querySelector( '.aiad-focal-point-picker-coords' );

		if ( ! mountEl ) {
			return;
		}

		const imageUrl =
			getFeaturedImageUrlFromEditor() || root.dataset.imageUrl || '';
		let initialPoint = parsePoint( root.dataset.focalPoint );
		if ( ! root.dataset.focalPoint ) {
			initialPoint = defaultPointForRoot( root );
		}
		if ( inputX && inputY && inputX.value !== '' && inputY.value !== '' ) {
			initialPoint = {
				x: parseInt( inputX.value, 10 ) / 100,
				y: parseInt( inputY.value, 10 ) / 100,
			};
		}

		if ( ! imageUrl ) {
			if ( mountEl._aiadMounted ) {
				unmountComponentAtNode( mountEl );
				mountEl._aiadMounted = false;
			}
			mountEl.hidden = true;
			if ( emptyEl ) {
				emptyEl.hidden = false;
			}
			return;
		}

		mountEl.hidden = false;
		if ( emptyEl ) {
			emptyEl.hidden = true;
		}

		if ( mountEl._aiadMounted ) {
			unmountComponentAtNode( mountEl );
		}

		render(
			el( FocalPointControl, {
				imageUrl,
				initialPoint,
				inputX,
				inputY,
				coordsEl,
			} ),
			mountEl
		);
		mountEl._aiadMounted = true;
	}

	function mountAll() {
		document
			.querySelectorAll( '.aiad-focal-point-picker-root' )
			.forEach( mountRoot );
	}

	let scheduled = false;
	function scheduleMount() {
		if ( scheduled ) {
			return;
		}
		scheduled = true;
		window.requestAnimationFrame( function () {
			scheduled = false;
			mountAll();
		} );
	}

	$( function () {
		mountAll();

		$( document ).on( 'click', '#postimagediv a', scheduleMount );

		const featuredBox = document.getElementById( 'postimagediv' );
		if ( featuredBox && window.MutationObserver ) {
			const observer = new MutationObserver( scheduleMount );
			observer.observe( featuredBox, { childList: true, subtree: true } );
		}

		if ( wp.data && wp.data.subscribe ) {
			let lastFeatured = null;
			wp.data.subscribe( function () {
				try {
					const id = wp.data
						.select( 'core/editor' )
						.getEditedPostAttribute( 'featured_media' );
					if ( id !== lastFeatured ) {
						lastFeatured = id;
						scheduleMount();
					}
				} catch ( e ) {
					/* not block editor */
				}
			} );
		}
	} );
} )( window.wp, window.jQuery );
