(function () {
	'use strict';

	var PIPELINE = [
		{
			id: 0,
			name: 'Tokens',
			def: 'Text is broken into small chunks the model can process.',
		},
		{
			id: 1,
			name: 'Embeddings',
			def: 'Each chunk is converted into a list of numbers that capture meaning.',
		},
		{
			id: 2,
			name: 'Attention',
			def: 'Every word weighs how much it should look at every other word.',
		},
		{
			id: 3,
			name: 'Layers',
			def: 'Understanding builds through many stacked passes — grammar to meaning.',
		},
		{
			id: 4,
			name: 'Prediction',
			def: 'The model ranks probable next words and picks one to continue.',
		},
		{
			id: 5,
			name: 'Training',
			def: 'The model learned by predicting the next word billions of times on text.',
		},
	];

	function shuffle(arr) {
		var a = arr.slice();
		var i = a.length;
		var j;
		var t;
		while (i > 0) {
			j = Math.floor(Math.random() * i);
			i -= 1;
			t = a[i];
			a[i] = a[j];
			a[j] = t;
		}
		return a;
	}

	function escapeHtml(str) {
		return String(str)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;');
	}

	function initRoot(root) {
		if (!root || root.getAttribute('data-aiad-llm-order-ready') === '1') {
			return;
		}
		root.setAttribute('data-aiad-llm-order-ready', '1');

		var list = root.querySelector('[data-aiad-llm-order-list]');
		var feedback = root.querySelector('[data-aiad-llm-order-feedback]');
		var answerKey = root.querySelector('[data-aiad-llm-order-answer]');
		var checkBtn = root.querySelector('[data-aiad-llm-order-check]');
		var shuffleBtn = root.querySelector('[data-aiad-llm-order-shuffle]');
		var hintBtn = root.querySelector('[data-aiad-llm-order-hint]');

		if (!list || !checkBtn) {
			return;
		}

		var dragFrom = null;

		function getOrder() {
			var items = list.querySelectorAll('[data-step-id]');
			var order = [];
			var i;
			for (i = 0; i < items.length; i++) {
				order.push(parseInt(items[i].getAttribute('data-step-id'), 10));
			}
			return order;
		}

		function isCorrect() {
			var order = getOrder();
			var i;
			for (i = 0; i < PIPELINE.length; i++) {
				if (order[i] !== i) {
					return false;
				}
			}
			return true;
		}

		function renderAnswerKey() {
			if (!answerKey) {
				return;
			}
			var h = '<ol class="aiad-llm-order__key-list">';
			PIPELINE.forEach(function (step, i) {
				h +=
					'<li><strong>' +
					(i + 1) +
					'. ' +
					escapeHtml(step.name) +
					'</strong> — ' +
					escapeHtml(step.def) +
					'</li>';
			});
			h += '</ol>';
			answerKey.innerHTML = h;
		}

		function cardHtml(step, index) {
			return (
				'<li class="aiad-llm-order__item" draggable="true" data-step-id="' +
				step.id +
				'" data-index="' +
				index +
				'">' +
				'<span class="aiad-llm-order__position" aria-hidden="true">' +
				(index + 1) +
				'</span>' +
				'<div class="aiad-llm-order__body">' +
				'<p class="aiad-llm-order__def">' +
				escapeHtml(step.def) +
				'</p>' +
				'<p class="aiad-llm-order__name" hidden data-step-name>' +
				escapeHtml(step.name) +
				'</p>' +
				'</div>' +
				'<div class="aiad-llm-order__move" aria-label="Reorder">' +
				'<button type="button" class="aiad-llm-order__move-btn" data-move="up" aria-label="Move up">↑</button>' +
				'<button type="button" class="aiad-llm-order__move-btn" data-move="down" aria-label="Move down">↓</button>' +
				'</div></li>'
			);
		}

		function updatePositions() {
			var items = list.querySelectorAll('.aiad-llm-order__item');
			var i;
			for (i = 0; i < items.length; i++) {
				items[i].setAttribute('data-index', String(i));
				var pos = items[i].querySelector('.aiad-llm-order__position');
				if (pos) {
					pos.textContent = String(i + 1);
				}
				var up = items[i].querySelector('[data-move="up"]');
				var down = items[i].querySelector('[data-move="down"]');
				if (up) {
					up.disabled = i === 0;
				}
				if (down) {
					down.disabled = i === items.length - 1;
				}
			}
		}

		function clearFeedback() {
			if (feedback) {
				feedback.hidden = true;
				feedback.className = 'aiad-llm-order__feedback';
				feedback.textContent = '';
			}
			var items = list.querySelectorAll('.aiad-llm-order__item');
			var i;
			for (i = 0; i < items.length; i++) {
				items[i].classList.remove('is-wrong', 'is-right');
				var nameEl = items[i].querySelector('[data-step-name]');
				if (nameEl) {
					nameEl.hidden = true;
				}
			}
		}

		function buildList() {
			var shuffled = shuffle(PIPELINE);
			var h = '';
			shuffled.forEach(function (step, i) {
				h += cardHtml(step, i);
			});
			list.innerHTML = h;
			updatePositions();
			clearFeedback();
		}

		function showFeedback() {
			var order = getOrder();
			var allOk = isCorrect();
			var i;
			var items = list.querySelectorAll('.aiad-llm-order__item');

			if (!feedback) {
				return;
			}

			for (i = 0; i < items.length; i++) {
				items[i].classList.remove('is-wrong', 'is-right');
			}

			for (i = 0; i < PIPELINE.length; i++) {
				var item = list.querySelector('[data-step-id="' + i + '"]');
				if (!item) {
					continue;
				}
				var at = order.indexOf(i);
				var nameEl = item.querySelector('[data-step-name]');
				if (nameEl) {
					nameEl.hidden = false;
				}
				if (at === i) {
					item.classList.add('is-right');
				} else {
					item.classList.add('is-wrong');
				}
			}

			feedback.hidden = false;
			feedback.className =
				'aiad-llm-order__feedback' + (allOk ? ' aiad-llm-order__feedback--ok' : ' aiad-llm-order__feedback--miss');
			if (allOk) {
				feedback.textContent =
					'Perfect — you have the full pipeline from raw text to a trained model. That is the journey behind every chat response.';
			} else {
				feedback.textContent =
					'Not quite yet. Cards in green are in the right slot; red cards need to move. Use the arrows or drag, then check again.';
			}
			feedback.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
		}

		function moveItem(item, dir) {
			var items = Array.prototype.slice.call(list.querySelectorAll('.aiad-llm-order__item'));
			var idx = items.indexOf(item);
			if (idx < 0) {
				return;
			}
			var next = dir === 'up' ? idx - 1 : idx + 1;
			if (next < 0 || next >= items.length) {
				return;
			}
			if (dir === 'up') {
				list.insertBefore(item, items[next]);
			} else {
				list.insertBefore(items[next], item);
			}
			updatePositions();
			clearFeedback();
		}

		buildList();
		renderAnswerKey();

		checkBtn.addEventListener('click', showFeedback);

		if (shuffleBtn) {
			shuffleBtn.addEventListener('click', function () {
				buildList();
				if (answerKey) {
					answerKey.hidden = true;
				}
				if (hintBtn) {
					hintBtn.setAttribute('aria-expanded', 'false');
				}
			});
		}

		if (hintBtn && answerKey) {
			hintBtn.addEventListener('click', function () {
				var open = answerKey.hidden;
				answerKey.hidden = !open;
				hintBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
				hintBtn.textContent = open ? 'Hide answer key' : 'Show answer key';
			});
		}

		list.addEventListener('click', function (e) {
			var btn = e.target.closest('[data-move]');
			if (!btn) {
				return;
			}
			var item = btn.closest('.aiad-llm-order__item');
			if (item) {
				moveItem(item, btn.getAttribute('data-move'));
			}
		});

		list.addEventListener('dragstart', function (e) {
			var item = e.target.closest('.aiad-llm-order__item');
			if (!item) {
				return;
			}
			dragFrom = item;
			item.classList.add('is-dragging');
			e.dataTransfer.effectAllowed = 'move';
			e.dataTransfer.setData('text/plain', item.getAttribute('data-step-id') || '');
		});

		list.addEventListener('dragend', function () {
			if (dragFrom) {
				dragFrom.classList.remove('is-dragging');
			}
			dragFrom = null;
			var items = list.querySelectorAll('.aiad-llm-order__item');
			var i;
			for (i = 0; i < items.length; i++) {
				items[i].classList.remove('is-drag-over');
			}
		});

		list.addEventListener('dragover', function (e) {
			e.preventDefault();
			var over = e.target.closest('.aiad-llm-order__item');
			if (!over || over === dragFrom) {
				return;
			}
			var items = list.querySelectorAll('.aiad-llm-order__item');
			var i;
			for (i = 0; i < items.length; i++) {
				items[i].classList.remove('is-drag-over');
			}
			over.classList.add('is-drag-over');
		});

		list.addEventListener('drop', function (e) {
			e.preventDefault();
			var over = e.target.closest('.aiad-llm-order__item');
			if (!dragFrom || !over || over === dragFrom) {
				return;
			}
			var rect = over.getBoundingClientRect();
			var after = e.clientY > rect.top + rect.height / 2;
			if (after) {
				list.insertBefore(dragFrom, over.nextSibling);
			} else {
				list.insertBefore(dragFrom, over);
			}
			updatePositions();
			clearFeedback();
		});
	}

	function boot() {
		document.querySelectorAll('[data-aiad-llm-order]').forEach(initRoot);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})();
