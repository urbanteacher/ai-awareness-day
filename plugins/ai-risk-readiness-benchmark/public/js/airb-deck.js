/* AI Risk & Readiness Benchmark — "How it works" slide deck.
 * Lightweight, dependency-free carousel: fade + slide transitions,
 * dot navigation, prev/next, keyboard arrows, touch swipe.
 */
(function () {
	'use strict';

	function initDeck(deck) {
		var track = deck.querySelector('[data-deck-track]');
		if (!track) {
			return;
		}

		var slides = Array.prototype.slice.call(track.querySelectorAll('.airb__slide'));
		var prevBtn = deck.querySelector('[data-deck-prev]');
		var nextBtn = deck.querySelector('[data-deck-next]');
		var dotsWrap = deck.querySelector('[data-deck-dots]');
		var counter = deck.querySelector('[data-deck-counter]');

		if (slides.length === 0) {
			return;
		}

		// Single slide: nothing to navigate — hide controls.
		if (slides.length < 2) {
			var controls = deck.querySelector('.airb__deck-controls');
			if (controls) {
				controls.hidden = true;
			}
			slides[0].classList.add('is-active');
			return;
		}

		var index = 0;
		var dots = [];

		// Build dot indicators.
		slides.forEach(function (slide, i) {
			var dot = document.createElement('button');
			dot.type = 'button';
			dot.className = 'airb__deck-dot';
			dot.setAttribute('role', 'tab');
			dot.setAttribute('aria-label', 'Slide ' + (i + 1));
			dot.addEventListener('click', function () {
				go(i);
			});
			dotsWrap.appendChild(dot);
			dots.push(dot);
		});

		function render() {
			slides.forEach(function (slide, i) {
				var active = i === index;
				slide.classList.toggle('is-active', active);
				slide.setAttribute('aria-hidden', active ? 'false' : 'true');
			});
			dots.forEach(function (dot, i) {
				var active = i === index;
				dot.classList.toggle('is-active', active);
				dot.setAttribute('aria-selected', active ? 'true' : 'false');
			});
			if (counter) {
				counter.textContent = (index + 1) + ' / ' + slides.length;
			}
		}

		function go(target) {
			var count = slides.length;
			index = ((target % count) + count) % count; // wrap-around
			render();
		}

		if (prevBtn) {
			prevBtn.addEventListener('click', function () {
				go(index - 1);
			});
		}
		if (nextBtn) {
			nextBtn.addEventListener('click', function () {
				go(index + 1);
			});
		}

		deck.addEventListener('keydown', function (e) {
			if (e.key === 'ArrowLeft') {
				go(index - 1);
			} else if (e.key === 'ArrowRight') {
				go(index + 1);
			}
		});

		// Touch swipe.
		var startX = null;
		deck.addEventListener('touchstart', function (e) {
			startX = e.touches[0].clientX;
		}, { passive: true });
		deck.addEventListener('touchend', function (e) {
			if (startX === null) {
				return;
			}
			var delta = e.changedTouches[0].clientX - startX;
			if (Math.abs(delta) > 40) {
				go(delta < 0 ? index + 1 : index - 1);
			}
			startX = null;
		});

		render();
	}

	function init() {
		Array.prototype.slice.call(document.querySelectorAll('[data-deck]')).forEach(initDeck);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
