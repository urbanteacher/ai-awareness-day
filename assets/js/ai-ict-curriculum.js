(function () {
	'use strict';

	function initRoot(root) {
		if (!root || root.getAttribute('data-aiad-ict-curriculum-ready') === '1') {
			return;
		}
		root.setAttribute('data-aiad-ict-curriculum-ready', '1');

		// Tabs switching elements
		var tabKS2 = root.querySelector('#tab-ks2');
		var tabKS3 = root.querySelector('#tab-ks3');
		var panelKS2 = root.querySelector('#panel-ks2');
		var panelKS3 = root.querySelector('#panel-ks3');

		// Tab click events
		if (tabKS2 && tabKS3 && panelKS2 && panelKS3) {
			tabKS2.addEventListener('click', function () {
				tabKS2.classList.add('is-active');
				tabKS2.setAttribute('aria-selected', 'true');
				tabKS2.removeAttribute('tabindex');

				tabKS3.classList.remove('is-active');
				tabKS3.setAttribute('aria-selected', 'false');
				tabKS3.setAttribute('tabindex', '-1');

				panelKS2.classList.add('is-active');
				panelKS2.removeAttribute('hidden');

				panelKS3.classList.remove('is-active');
				panelKS3.setAttribute('hidden', 'true');
			});

			tabKS3.addEventListener('click', function () {
				tabKS3.classList.add('is-active');
				tabKS3.setAttribute('aria-selected', 'true');
				tabKS3.removeAttribute('tabindex');

				tabKS2.classList.remove('is-active');
				tabKS2.setAttribute('aria-selected', 'false');
				tabKS2.setAttribute('tabindex', '-1');

				panelKS3.classList.add('is-active');
				panelKS3.removeAttribute('hidden');

				panelKS2.classList.remove('is-active');
				panelKS2.setAttribute('hidden', 'true');
			});
		}

		// --- KEY STAGE 2 SCRATCH PLAYGROUND ---
		var scratchPlayground = root.querySelector('[data-scratch-playground]');
		if (scratchPlayground) {
			var selectRepeat = scratchPlayground.querySelector('.select-repeat');
			var selectTurn = scratchPlayground.querySelector('.select-turn');
			var btnRun = scratchPlayground.querySelector('[data-action="run-scratch"]');
			var btnReset = scratchPlayground.querySelector('[data-action="reset-scratch"]');
			var sprite = scratchPlayground.querySelector('[data-scratch-sprite]');
			var gem = scratchPlayground.querySelector('[data-scratch-gem]');
			var svg = scratchPlayground.querySelector('[data-scratch-svg]');
			var coords = scratchPlayground.querySelector('[data-scratch-coords]');
			var feedback = scratchPlayground.querySelector('[data-scratch-feedback]');
			var feedbackTabs = scratchPlayground.querySelectorAll('.feedback-tab-btn');
			var feedbackPanels = scratchPlayground.querySelectorAll('.feedback-tab-content');

			var isRunning = false;
			var pathPoints = [];

			// Initialize Grid dimensions
			var canvasWrap = scratchPlayground.querySelector('.grid-canvas-wrap');
			var canvasW = 320; 
			var canvasH = 320;

			function updateCanvasDimensions() {
				if (canvasWrap) {
					canvasW = canvasWrap.clientWidth || 320;
					canvasH = canvasWrap.clientHeight || 320;
				}
			}

			function resetScratchState() {
				isRunning = false;
				updateCanvasDimensions();
				
				var cx = canvasW / 2;
				var cy = canvasH / 2;

				// Start position: bottom-left of the square path (so it traces a square centered in the container)
				var startX = cx - 50;
				var startY = cy + 50;

				// Target Gem position: top-right of the square path
				var gemX = cx + 50;
				var gemY = cy - 50;

				if (sprite) {
					sprite.style.left = '0px';
					sprite.style.top = '0px';
					sprite.style.transform = 'translate(' + startX + 'px, ' + startY + 'px) translate(-50%, -50%) rotate(-45deg)'; // pointing up initially
				}

				if (gem) {
					gem.style.left = '0px';
					gem.style.top = '0px';
					gem.classList.remove('hidden');
					gem.style.transform = 'translate(' + gemX + 'px, ' + gemY + 'px) translate(-50%, -50%) scale(1)';
				}

				if (svg) {
					svg.setAttribute('width', canvasW);
					svg.setAttribute('height', canvasH);
					svg.innerHTML = ''; // Clear svg paths
				}

				if (coords) {
					coords.textContent = 'x: 0, y: 0';
				}

				if (feedback) {
					feedback.classList.add('hidden');
					feedback.querySelector('.feedback-status.success').classList.add('hidden');
					feedback.querySelector('.feedback-status.error').classList.add('hidden');
				}
			}

			// Window resize handler to keep coordinate dimensions up-to-date
			window.addEventListener('resize', function () {
				if (!isRunning) {
					resetScratchState();
				}
			});

			// Run Scratch loop
			function runScratch() {
				if (isRunning) return;
				isRunning = true;

				updateCanvasDimensions();
				resetScratchState();
				
				if (feedback) feedback.classList.add('hidden');

				var repeatVal = parseInt(selectRepeat.value, 10) || 4;
				var turnVal = parseInt(selectTurn.value, 10) || 90;

				var cx = canvasW / 2;
				var cy = canvasH / 2;

				var currentX = cx - 50;
				var currentY = cy + 50;
				var currentAngle = 0; // 0 = Up, 90 = Right, 180 = Down, 270 = Left

				pathPoints = [{ x: currentX, y: currentY }];

				// Create premium glowing SVG path element
				var pathEl = document.createElementNS('http://www.w3.org/2000/svg', 'path');
				pathEl.setAttribute('stroke', '#00f5ff');
				pathEl.setAttribute('stroke-width', '4');
				pathEl.setAttribute('fill', 'none');
				pathEl.setAttribute('stroke-linecap', 'round');
				pathEl.setAttribute('stroke-linejoin', 'round');
				pathEl.setAttribute('style', 'filter: drop-shadow(0 0 6px rgba(0, 245, 255, 0.8)); transition: stroke-dasharray 0.4s ease;');
				svg.appendChild(pathEl);

				var step = 0;
				var hitGem = false;

				function executeStep() {
					if (step >= repeatVal) {
						finishExecution();
						return;
					}

					// Move forward by 100 pixels in current direction
					var rad = (currentAngle * Math.PI) / 180;
					var dx = 100 * Math.sin(rad);
					var dy = -100 * Math.cos(rad); // y-axis is inverted in screen coordinates

					var nextX = currentX + dx;
					var nextY = currentY + dy;

					// Animate Rocket translation & rotation using transform
					if (sprite) {
						sprite.style.transform = 'translate(' + nextX + 'px, ' + nextY + 'px) translate(-50%, -50%) rotate(' + (currentAngle - 45) + 'deg)';
					}

					// Trace path points
					currentX = nextX;
					currentY = nextY;
					pathPoints.push({ x: currentX, y: currentY });

					// Update SVG Path content
					var dAttr = 'M ' + pathPoints[0].x + ' ' + pathPoints[0].y;
					for (var i = 1; i < pathPoints.length; i++) {
						dAttr += ' L ' + pathPoints[i].x + ' ' + pathPoints[i].y;
					}
					pathEl.setAttribute('d', dAttr);

					// Update coordinates label (relative to center as 0,0)
					if (coords) {
						var relX = Math.round(currentX - cx);
						var relY = Math.round(cy - currentY);
						coords.textContent = 'x: ' + relX + ', y: ' + relY;
					}

					// Check gem collision
					var gemX = cx + 50;
					var gemY = cy - 50;
					var dist = Math.sqrt(Math.pow(currentX - gemX, 2) + Math.pow(currentY - gemY, 2));
					if (dist < 15) {
						hitGem = true;
						if (gem) {
							gem.style.transform = 'translate(' + gemX + 'px, ' + gemY + 'px) translate(-50%, -50%) scale(0)';
							setTimeout(function () {
								gem.classList.add('hidden');
							}, 300);
						}
					}

					// Schedule rotation and next step
					setTimeout(function () {
						currentAngle = (currentAngle + turnVal) % 360;
						if (sprite && step < repeatVal - 1) {
							sprite.style.transform = 'translate(' + currentX + 'px, ' + currentY + 'px) translate(-50%, -50%) rotate(' + (currentAngle - 45) + 'deg)';
						}
						step++;
						setTimeout(executeStep, 400);
					}, 400);
				}

				function finishExecution() {
					isRunning = false;

					// Success Criteria:
					// 1. Traced a closed square: Repeat = 4, Turn = 90
					// 2. Visited/collected the gem (starts at top-right corner of square)
					var isSuccess = (repeatVal === 4 && turnVal === 90 && hitGem);

					if (feedback) {
						feedback.classList.remove('hidden');
						var successBlock = feedback.querySelector('.feedback-status.success');
						var errorBlock = feedback.querySelector('.feedback-status.error');

						if (isSuccess) {
							successBlock.classList.remove('hidden');
							errorBlock.classList.add('hidden');
						} else {
							successBlock.classList.add('hidden');
							errorBlock.classList.remove('hidden');

							var errorMsg = errorBlock.querySelector('.error-msg-text');
							if (errorMsg) {
								if (turnVal !== 90) {
									errorMsg.textContent = 'Logic Bug: Turning right by ' + turnVal + '° does not form a 90° square angle. The rocket drew a path with different angles and missed the perfect orbit. Set the turn angle to exactly 90°!';
								} else if (repeatVal < 4) {
									errorMsg.textContent = 'Loop Incomplete: The loop only repeated ' + repeatVal + ' times, leaving the square path open. A square has 4 sides, so you need exactly 4 iterations!';
								} else {
									errorMsg.textContent = 'Infinite Overlap: Repeating ' + repeatVal + ' times made the rocket loop past the starting point. It overlapped its own path. A square needs exactly 4 sides!';
								}
							}
						}

						// Scroll feedback into view
						feedback.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
					}
				}

				// Start animation loop
				executeStep();
			}

			// Bind click events
			btnRun.addEventListener('click', runScratch);
			btnReset.addEventListener('click', function () {
				if (isRunning) return;
				resetScratchState();
			});

			// Feedback tabs toggles (Scratch section)
			feedbackTabs.forEach(function (tab) {
				tab.addEventListener('click', function () {
					feedbackTabs.forEach(function (t) { t.classList.remove('is-active'); });
					tab.classList.add('is-active');

					var targetPanel = tab.getAttribute('data-feedback-tab');
					feedbackPanels.forEach(function (panel) {
						if (panel.getAttribute('data-feedback-panel') === targetPanel) {
							panel.classList.remove('hidden');
						} else {
							panel.classList.add('hidden');
						}
					});
				});
			});

			// Seed initial layout positioning
			setTimeout(resetScratchState, 100);
		}

		// --- KEY STAGE 3 PLAYGROUND ---
		var ks3Playground = root.querySelector('[data-ks3-playground]');
		if (ks3Playground) {
			var bitButtons = ks3Playground.querySelectorAll('.bit-toggle-btn');
			var binaryTotalEl = ks3Playground.querySelector('[data-binary-total]');
			var binaryFeedback = ks3Playground.querySelector('[data-binary-feedback]');
			
			var pythonSection = ks3Playground.querySelector('[data-python-section]');
			var pythonOverlay = ks3Playground.querySelector('[data-python-overlay]');
			var codeLines = ks3Playground.querySelectorAll('.py-line');
			var terminalOutput = ks3Playground.querySelector('[data-terminal-output]');
			var optButtons = ks3Playground.querySelectorAll('.python-opt-btn');
			
			var ks3Feedback = ks3Playground.querySelector('[data-ks3-feedback]');
			var ks3FeedbackTabs = ks3Playground.querySelectorAll('.feedback-tab-btn');
			var ks3FeedbackPanels = ks3Playground.querySelectorAll('.feedback-tab-content');

			var isUnlocked = false;
			var isPythonTracing = false;

			// PART A: Binary decoder logic
			bitButtons.forEach(function (btn) {
				btn.addEventListener('click', function () {
					if (isUnlocked) return; // Remain unlocked once solved

					btn.classList.toggle('is-active');
					var active = btn.classList.contains('is-active');
					btn.textContent = active ? '1' : '0';

					var statusEl = btn.parentNode.querySelector('.bit-status-label');
					if (statusEl) {
						statusEl.textContent = active ? 'ON' : 'OFF';
					}

					// Update place value term inside formula bar
					var placeVal = btn.getAttribute('data-bit-value');
					var formulaTerm = ks3Playground.querySelector('#term-' + placeVal);
					if (formulaTerm) {
						formulaTerm.textContent = (active ? '1' : '0') + ' × ' + placeVal;
						if (active) {
							formulaTerm.classList.add('is-highlighted');
						} else {
							formulaTerm.classList.remove('is-highlighted');
						}
					}

					// Calculate total
					var total = 0;
					bitButtons.forEach(function (b) {
						if (b.classList.contains('is-active')) {
							total += parseInt(b.getAttribute('data-bit-value'), 10);
						}
					});

					if (binaryTotalEl) {
						binaryTotalEl.textContent = total;
					}

					// Check if sum equals 11 (binary 1011)
					if (total === 11) {
						isUnlocked = true;
						
						// Disable bit button clicks
						bitButtons.forEach(function (b) { b.disabled = true; });

						// Feedback on binary success
                        if (binaryFeedback) {
                            binaryFeedback.classList.remove('hidden');
                            binaryFeedback.className = 'binary-feedback success';
                            binaryFeedback.innerHTML = '🔑 <strong>Decoded!</strong> Binary <code>1011</code> matches decimal 11 (8 + 0 + 2 + 1). Opening Python Engine...';
                        }

						// Unlock Python Editor Section
						if (pythonSection) {
							pythonSection.classList.remove('is-locked');
						}
						if (pythonOverlay) {
							pythonOverlay.style.transition = 'all 0.5s cubic-bezier(0.25, 0.8, 0.25, 1)';
							pythonOverlay.style.transform = 'translateY(-100%)';
							pythonOverlay.style.opacity = '0';
							setTimeout(function () {
								pythonOverlay.classList.add('hidden');
							}, 500);
						}
					} else {
						if (binaryFeedback) {
							binaryFeedback.classList.add('hidden');
						}
					}
				});
			});

			// PART B: Python trace logic
			optButtons.forEach(function (optBtn) {
				optBtn.addEventListener('click', function () {
					if (isPythonTracing) return;
					isPythonTracing = true;

					// Clear options styles
					optButtons.forEach(function (ob) {
						ob.className = 'python-opt-btn';
					});
					optBtn.classList.add('is-active');

					if (ks3Feedback) {
						ks3Feedback.classList.add('hidden');
					}

					// Reset traced lines
					codeLines.forEach(function (line) {
						line.classList.remove('is-traced');
					});

					// Clear terminal output
					var outText = terminalOutput.querySelector('.output-text');
					if (outText) {
						outText.textContent = 'Trace executing...';
						outText.classList.add('blink-cursor');
						terminalOutput.classList.remove('is-error');
					}

					var selectedValue = parseInt(optBtn.getAttribute('data-opt-value'), 10);

					// Tracing lines array: line numbers that are evaluated
					var traceSteps = [
						{ line: 2, delay: 400 },
						{ line: 3, delay: 800 },
						{ line: 4, delay: 1200 },
						{ line: 6, delay: 1800, text: 'temperature > 20 and not is_raining ... False' },
						{ line: 8, delay: 2400, text: 'temperature > 20 and is_raining ... True' },
						{ line: 9, delay: 3000, text: 'motor_speed = 50' },
						{ line: 13, delay: 3500, text: 'print(motor_speed)' }
					];

					traceSteps.forEach(function (step) {
						setTimeout(function () {
							codeLines.forEach(function (line) {
								line.classList.remove('is-traced');
							});
							var targetLine = ks3Playground.querySelector('.py-line[data-line="' + step.line + '"]');
							if (targetLine) {
								targetLine.classList.add('is-traced');
							}
							if (step.text && outText) {
								outText.textContent = '>>> ' + step.text;
							}
						}, step.delay);
					});

					// Finish tracing and validate answer
					setTimeout(function () {
						isPythonTracing = false;

						// Clear line trace highlight
						codeLines.forEach(function (line) {
							line.classList.remove('is-traced');
						});

						var isCorrect = (selectedValue === 50);

						if (outText) {
							outText.classList.remove('blink-cursor');
							outText.textContent = '50';
						}

						// Apply colors to multiple choice buttons
						optBtn.classList.remove('is-active');
						if (isCorrect) {
							optBtn.classList.add('is-correct');
						} else {
							optBtn.classList.add('is-wrong');
							// Highlight the correct one as well
							var correctBtn = ks3Playground.querySelector('.python-opt-btn[data-opt-value="50"]');
							if (correctBtn) {
								correctBtn.classList.add('is-correct');
							}
						}

						// Update feedback card
						if (ks3Feedback) {
							ks3Feedback.classList.remove('hidden');
							var successBlock = ks3Feedback.querySelector('.feedback-status.success');
							var errorBlock = ks3Feedback.querySelector('.feedback-status.error');

							if (isCorrect) {
								successBlock.classList.remove('hidden');
								errorBlock.classList.add('hidden');
							} else {
								successBlock.classList.add('hidden');
								errorBlock.classList.remove('hidden');
								var errorMsg = errorBlock.querySelector('.ks3-error-msg-text');
								if (errorMsg) {
									if (selectedValue === 0) {
										errorMsg.textContent = 'Trace Bug: You selected 0, which is the initial value of motor_speed. However, the conditions on lines 6 and 8 reassign the variable. The code changes motor_speed before printing!';
									} else if (selectedValue === 100) {
										errorMsg.textContent = 'Logic Bug: You selected 100. Let\'s evaluate line 6: "temperature > 20 and not is_raining". Since is_raining is True, "not is_raining" is False, making the entire "and" condition False. This branch is skipped!';
									} else if (selectedValue === 10) {
										errorMsg.textContent = 'Logic Bug: You selected 10. The "else" block on line 10 only executes if both the "if" (line 6) and "elif" (line 8) conditions are False. Since the "elif" condition evaluates to True, the "else" branch is bypassed entirely!';
									}
								}
							}

							ks3Feedback.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
						}

					}, 4200);
				});
			});

			// Feedback tabs toggles (KS3 section)
			ks3FeedbackTabs.forEach(function (tab) {
				tab.addEventListener('click', function () {
					ks3FeedbackTabs.forEach(function (t) { t.classList.remove('is-active'); });
					tab.classList.add('is-active');

					var targetPanel = tab.getAttribute('data-feedback-tab');
					ks3FeedbackPanels.forEach(function (panel) {
						if (panel.getAttribute('data-feedback-panel') === targetPanel) {
							panel.classList.remove('hidden');
						} else {
							panel.classList.add('hidden');
						}
					});
				});
			});
		}
	}

	function boot() {
		document.querySelectorAll('[data-aiad-ict-curriculum]').forEach(initRoot);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})();
