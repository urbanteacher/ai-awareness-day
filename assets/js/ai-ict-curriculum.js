(function () {
	'use strict';

	function initRoot(root) {
		if (!root || root.getAttribute('data-aiad-ict-curriculum-ready') === '1') {
			return;
		}
		root.setAttribute('data-aiad-ict-curriculum-ready', '1');

		// Tabs switching — generic handler for any number of tabs.
		var tablist = root.querySelector('.ict-widget__tabs');
		if (tablist) {
			var tabBtns = tablist.querySelectorAll('.ict-widget__tab-btn');
			tabBtns.forEach(function (btn) {
				btn.addEventListener('click', function () {
					tabBtns.forEach(function (other) {
						var isActive = other === btn;
						other.classList.toggle('is-active', isActive);
						other.setAttribute('aria-selected', isActive ? 'true' : 'false');
						if (isActive) {
							other.removeAttribute('tabindex');
						} else {
							other.setAttribute('tabindex', '-1');
						}

						var panel = root.querySelector('#' + other.getAttribute('aria-controls'));
						if (panel) {
							panel.classList.toggle('is-active', isActive);
							if (isActive) {
								panel.removeAttribute('hidden');
							} else {
								panel.setAttribute('hidden', 'true');
							}
						}
					});
				});
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

		// --- KEY STAGE 4 LOOP PLAYGROUND ---
		var ks4Playground = root.querySelector('[data-ks4-playground]');
		if (ks4Playground) {
			var LOOP_VALUES = [1, 2, 3, 4]; // range(1, 5)
			var CORRECT_TOTAL = 10;

			var ks4StepBtn = ks4Playground.querySelector('[data-ks4-step]');
			var ks4ResetBtn = ks4Playground.querySelector('[data-ks4-reset]');
			var ks4TraceBody = ks4Playground.querySelector('[data-ks4-trace-body]');
			var ks4TotalEl = ks4Playground.querySelector('[data-ks4-total]');
			var ks4Output = ks4Playground.querySelector('[data-ks4-output]');
			var ks4Hint = ks4Playground.querySelector('[data-ks4-hint]');
			var ks4CodeLines = ks4Playground.querySelectorAll('.ks4-line');
			var ks4Question = ks4Playground.querySelector('[data-ks4-question]');
			var ks4OptButtons = ks4Playground.querySelectorAll('.ks4-opt-btn');
			var ks4Feedback = ks4Playground.querySelector('[data-ks4-feedback]');
			var ks4FeedbackTabs = ks4Feedback ? ks4Feedback.querySelectorAll('.feedback-tab-btn') : [];
			var ks4FeedbackPanels = ks4Feedback ? ks4Feedback.querySelectorAll('.feedback-tab-content') : [];

			var ks4Index = 0;
			var ks4Total = 0;
			var ks4Answered = false;

			function ks4HighlightLine(lineNum) {
				ks4CodeLines.forEach(function (line) {
					line.classList.toggle('is-traced', line.getAttribute('data-ks4-line') === String(lineNum));
				});
			}

			function ks4Reset() {
				ks4Index = 0;
				ks4Total = 0;
				ks4Answered = false;

				if (ks4TraceBody) ks4TraceBody.innerHTML = '';
				if (ks4TotalEl) ks4TotalEl.textContent = '0';
				if (ks4Output) {
					ks4Output.textContent = 'Awaiting trace…';
					ks4Output.classList.remove('blink-cursor');
				}
				ks4HighlightLine(null);

				if (ks4Hint) {
					ks4Hint.textContent = 'Make your prediction below first — then press “Step Through Loop” to check it iteration by iteration.';
					ks4Hint.classList.remove('hidden');
				}
				// Prediction stays visible from the start so teachers guess before stepping.
				if (ks4Question) ks4Question.classList.remove('hidden');
				if (ks4Feedback) ks4Feedback.classList.add('hidden');
				ks4OptButtons.forEach(function (b) {
					b.className = 'python-opt-btn ks4-opt-btn';
				});
				if (ks4StepBtn) {
					ks4StepBtn.disabled = false;
					ks4StepBtn.querySelector('.btn-icon').textContent = '▶';
				}
			}

			function ks4FinishLoop() {
				ks4HighlightLine(4);
				if (ks4Output) {
					ks4Output.textContent = 'Loop finished — does it match your prediction?';
				}
				if (ks4Hint) {
					ks4Hint.textContent = 'The loop ran four times. Compare the final total with the answer you predicted.';
				}
				if (ks4StepBtn) {
					ks4StepBtn.disabled = true;
				}
			}

			function ks4Step() {
				if (ks4Index >= LOOP_VALUES.length) {
					return;
				}

				var i = LOOP_VALUES[ks4Index];
				ks4Total += i;

				// Highlight the loop body line.
				ks4HighlightLine(3);

				// Append a trace-table row.
				if (ks4TraceBody) {
					var row = document.createElement('tr');
					row.className = 'ks4-trace-row';
					var cellIter = document.createElement('td');
					cellIter.textContent = String(ks4Index + 1);
					var cellI = document.createElement('td');
					cellI.textContent = String(i);
					var cellTotal = document.createElement('td');
					cellTotal.textContent = String(ks4Total);
					cellTotal.className = 'ks4-cell-total';
					row.appendChild(cellIter);
					row.appendChild(cellI);
					row.appendChild(cellTotal);
					ks4TraceBody.appendChild(row);
				}

				if (ks4TotalEl) ks4TotalEl.textContent = String(ks4Total);
				if (ks4Output) ks4Output.textContent = 'i = ' + i + '  →  total = ' + ks4Total;
				if (ks4Hint) {
					ks4Hint.textContent = 'Iteration ' + (ks4Index + 1) + ' of ' + LOOP_VALUES.length +
						': added i (' + i + ') to total.';
				}

				ks4Index++;

				if (ks4Index >= LOOP_VALUES.length) {
					ks4FinishLoop();
				}
			}

			if (ks4StepBtn) ks4StepBtn.addEventListener('click', ks4Step);
			if (ks4ResetBtn) ks4ResetBtn.addEventListener('click', ks4Reset);

			// Prediction answer handling.
			ks4OptButtons.forEach(function (optBtn) {
				optBtn.addEventListener('click', function () {
					if (ks4Answered) return;
					ks4Answered = true;

					var selected = parseInt(optBtn.getAttribute('data-opt-value'), 10);
					var isCorrect = (selected === CORRECT_TOTAL);

					ks4OptButtons.forEach(function (b) {
						b.className = 'python-opt-btn ks4-opt-btn';
					});
					optBtn.classList.add(isCorrect ? 'is-correct' : 'is-wrong');
					if (!isCorrect) {
						var correctBtn = ks4Playground.querySelector('.ks4-opt-btn[data-opt-value="' + CORRECT_TOTAL + '"]');
						if (correctBtn) correctBtn.classList.add('is-correct');
					}

					if (ks4Output) ks4Output.textContent = String(CORRECT_TOTAL);

					if (ks4Feedback) {
						ks4Feedback.classList.remove('hidden');
						var successBlock = ks4Feedback.querySelector('.feedback-status.success');
						var errorBlock = ks4Feedback.querySelector('.feedback-status.error');

						if (isCorrect) {
							successBlock.classList.remove('hidden');
							errorBlock.classList.add('hidden');
						} else {
							successBlock.classList.add('hidden');
							errorBlock.classList.remove('hidden');
							var errorMsg = errorBlock.querySelector('.ks4-error-msg-text');
							if (errorMsg) {
								if (selected === 15) {
									errorMsg.textContent = 'Off-by-one: range(1, 5) stops BEFORE 5, so i is 1, 2, 3, 4 — never 5. That gives 1 + 2 + 3 + 4 = 10, not 15.';
								} else if (selected === 4) {
									errorMsg.textContent = 'That is the final value of i, not the running total. The variable "total" adds every value of i together as the loop runs.';
								} else if (selected === 0) {
									errorMsg.textContent = 'That is the starting value of total before the loop runs. The loop body updates total on each of its four passes.';
								} else {
									errorMsg.textContent = 'Follow the trace table row by row and add each value of i to total: 1 + 2 + 3 + 4 = 10.';
								}
							}
						}

						ks4Feedback.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
					}
				});
			});

			// Feedback tabs toggles (KS4 section).
			ks4FeedbackTabs.forEach(function (tab) {
				tab.addEventListener('click', function () {
					ks4FeedbackTabs.forEach(function (t) { t.classList.remove('is-active'); });
					tab.classList.add('is-active');

					var targetPanel = tab.getAttribute('data-feedback-tab');
					ks4FeedbackPanels.forEach(function (panel) {
						panel.classList.toggle('hidden', panel.getAttribute('data-feedback-panel') !== targetPanel);
					});
				});
			});

			ks4Reset();
		}

		// --- KEY STAGE 5 RECURSION / CALL STACK PLAYGROUND ---
		var ks5Playground = root.querySelector('[data-ks5-playground]');
		if (ks5Playground) {
			var CORRECT_RESULT = 24;
			// Ordered trace of factorial(4): four calls wind up, four returns unwind.
			var KS5_STEPS = [
				{ type: 'call', n: 4, line: 4 },
				{ type: 'call', n: 3, line: 4 },
				{ type: 'call', n: 2, line: 4 },
				{ type: 'call', n: 1, line: 3, base: true },
				{ type: 'return', n: 1, val: 1, line: 3 },
				{ type: 'return', n: 2, val: 2, line: 4 },
				{ type: 'return', n: 3, val: 6, line: 4 },
				{ type: 'return', n: 4, val: 24, line: 6, final: true }
			];

			var ks5StepBtn = ks5Playground.querySelector('[data-ks5-step]');
			var ks5ResetBtn = ks5Playground.querySelector('[data-ks5-reset]');
			var ks5StackEl = ks5Playground.querySelector('[data-ks5-stack]');
			var ks5Output = ks5Playground.querySelector('[data-ks5-output]');
			var ks5Hint = ks5Playground.querySelector('[data-ks5-hint]');
			var ks5Phase = ks5Playground.querySelector('[data-ks5-phase]');
			var ks5CodeLines = ks5Playground.querySelectorAll('.ks5-line');
			var ks5Question = ks5Playground.querySelector('[data-ks5-question]');
			var ks5OptButtons = ks5Playground.querySelectorAll('.ks5-opt-btn');
			var ks5Feedback = ks5Playground.querySelector('[data-ks5-feedback]');
			var ks5FeedbackTabs = ks5Feedback ? ks5Feedback.querySelectorAll('.feedback-tab-btn') : [];
			var ks5FeedbackPanels = ks5Feedback ? ks5Feedback.querySelectorAll('.feedback-tab-content') : [];

			var ks5StepIdx = 0;
			var ks5Frames = [];
			var ks5Answered = false;

			function ks5HighlightLine(lineNum) {
				ks5CodeLines.forEach(function (line) {
					line.classList.toggle('is-traced', line.getAttribute('data-ks5-line') === String(lineNum));
				});
			}

			function ks5RenderStack() {
				if (!ks5StackEl) return;
				ks5StackEl.innerHTML = '';

				if (ks5Frames.length === 0) {
					var empty = document.createElement('div');
					empty.className = 'ks5-stack-empty';
					empty.textContent = 'The stack is empty. Stepping will push factorial() calls on top of each other.';
					ks5StackEl.appendChild(empty);
					return;
				}

				// Render newest call on top.
				for (var i = ks5Frames.length - 1; i >= 0; i--) {
					var f = ks5Frames[i];
					var frame = document.createElement('div');
					frame.className = 'ks5-frame';
					if (i === ks5Frames.length - 1 && f.returned === null) {
						frame.className += ' is-top';
					}
					if (f.base && f.returned === null) {
						frame.className += ' is-base';
					}
					if (f.returned !== null) {
						frame.className += ' is-returning';
					}

					var name = document.createElement('span');
					name.className = 'ks5-frame-name';
					name.textContent = 'factorial(' + f.n + ')';
					frame.appendChild(name);

					if (f.returned !== null) {
						var ret = document.createElement('span');
						ret.className = 'ks5-frame-return';
						ret.textContent = '→ returns ' + f.returned;
						frame.appendChild(ret);
					} else if (f.base) {
						var baseTag = document.createElement('span');
						baseTag.className = 'ks5-frame-tag';
						baseTag.textContent = 'base case';
						frame.appendChild(baseTag);
					}

					ks5StackEl.appendChild(frame);
				}
			}

			function ks5Reset() {
				ks5StepIdx = 0;
				ks5Frames = [];
				ks5Answered = false;

				ks5RenderStack();
				ks5HighlightLine(null);
				if (ks5Output) {
					ks5Output.textContent = 'Awaiting trace…';
					ks5Output.classList.remove('blink-cursor');
				}
				if (ks5Phase) ks5Phase.textContent = 'Ready';
				if (ks5Hint) {
					ks5Hint.textContent = 'Make your prediction below first — then step through to watch the stack grow and unwind.';
				}
				// Prediction stays visible from the start so teachers guess before stepping.
				if (ks5Question) ks5Question.classList.remove('hidden');
				if (ks5Feedback) ks5Feedback.classList.add('hidden');
				ks5OptButtons.forEach(function (b) {
					b.className = 'python-opt-btn ks5-opt-btn';
				});
				if (ks5StepBtn) {
					ks5StepBtn.disabled = false;
					var icon = ks5StepBtn.querySelector('.btn-icon');
					if (icon) icon.textContent = '▶';
				}
			}

			function ks5Step() {
				if (ks5StepIdx >= KS5_STEPS.length) return;

				// Clear any frame resolved on the previous step (it has popped off).
				ks5Frames = ks5Frames.filter(function (f) { return !f.resolved; });

				var step = KS5_STEPS[ks5StepIdx];
				var desc = '';

				if (step.type === 'call') {
					ks5Frames.push({ n: step.n, returned: null, resolved: false, base: !!step.base });
					if (step.base) {
						if (ks5Phase) ks5Phase.textContent = 'Base case reached';
						desc = 'factorial(1) meets the base case (n <= 1) → returns 1';
					} else {
						if (ks5Phase) ks5Phase.textContent = 'Winding up (calls)';
						desc = 'factorial(' + step.n + ') must call factorial(' + (step.n - 1) + ') before it can return';
					}
				} else {
					var top = ks5Frames[ks5Frames.length - 1];
					if (top) {
						top.returned = step.val;
						top.resolved = true;
					}
					if (ks5Phase) ks5Phase.textContent = 'Unwinding (returns)';
					if (step.n === 1) {
						desc = 'factorial(1) returns 1';
					} else {
						desc = 'factorial(' + step.n + ') returns ' + step.n + ' × ' + (step.val / step.n) + ' = ' + step.val;
					}
				}

				ks5HighlightLine(step.line);
				ks5RenderStack();
				if (ks5Output) ks5Output.textContent = desc;
				if (ks5Hint) ks5Hint.textContent = desc;

				ks5StepIdx++;

				if (step.final) {
					if (ks5Phase) ks5Phase.textContent = 'Complete';
					if (ks5Output) ks5Output.textContent = String(CORRECT_RESULT);
					if (ks5Hint) ks5Hint.textContent = 'The stack is empty again. Does the result match your prediction?';
					if (ks5StepBtn) ks5StepBtn.disabled = true;
				}
			}

			if (ks5StepBtn) ks5StepBtn.addEventListener('click', ks5Step);
			if (ks5ResetBtn) ks5ResetBtn.addEventListener('click', ks5Reset);

			// Prediction answer handling.
			ks5OptButtons.forEach(function (optBtn) {
				optBtn.addEventListener('click', function () {
					if (ks5Answered) return;
					ks5Answered = true;

					var selected = parseInt(optBtn.getAttribute('data-opt-value'), 10);
					var isCorrect = (selected === CORRECT_RESULT);

					ks5OptButtons.forEach(function (b) {
						b.className = 'python-opt-btn ks5-opt-btn';
					});
					optBtn.classList.add(isCorrect ? 'is-correct' : 'is-wrong');
					if (!isCorrect) {
						var correctBtn = ks5Playground.querySelector('.ks5-opt-btn[data-opt-value="' + CORRECT_RESULT + '"]');
						if (correctBtn) correctBtn.classList.add('is-correct');
					}

					if (ks5Feedback) {
						ks5Feedback.classList.remove('hidden');
						var successBlock = ks5Feedback.querySelector('.feedback-status.success');
						var errorBlock = ks5Feedback.querySelector('.feedback-status.error');

						if (isCorrect) {
							successBlock.classList.remove('hidden');
							errorBlock.classList.add('hidden');
						} else {
							successBlock.classList.add('hidden');
							errorBlock.classList.remove('hidden');
							var errorMsg = errorBlock.querySelector('.ks5-error-msg-text');
							if (errorMsg) {
								if (selected === 10) {
									errorMsg.textContent = 'That adds the values (4 + 3 + 2 + 1 = 10). Factorial multiplies them: each call returns n × factorial(n - 1), not n + factorial(n - 1).';
								} else if (selected === 12) {
									errorMsg.textContent = 'That stops one call too early (4 × 3 = 12). The recursion keeps going until the base case: 4 × 3 × 2 × 1 = 24.';
								} else if (selected === 4) {
									errorMsg.textContent = 'That is just the starting value of n. factorial(4) multiplies n by every factorial below it down to the base case.';
								} else {
									errorMsg.textContent = 'Step through the call stack: the returns unwind as 1, 2, 6, then 24.';
								}
							}
						}

						ks5Feedback.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
					}
				});
			});

			// Feedback tabs toggles (KS5 section).
			ks5FeedbackTabs.forEach(function (tab) {
				tab.addEventListener('click', function () {
					ks5FeedbackTabs.forEach(function (t) { t.classList.remove('is-active'); });
					tab.classList.add('is-active');

					var targetPanel = tab.getAttribute('data-feedback-tab');
					ks5FeedbackPanels.forEach(function (panel) {
						panel.classList.toggle('hidden', panel.getAttribute('data-feedback-panel') !== targetPanel);
					});
				});
			});

			ks5Reset();
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
