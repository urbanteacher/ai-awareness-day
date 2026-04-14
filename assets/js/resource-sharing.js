/**
 * Resource sharing functionality with Web Share API and pre-written messages.
 *
 * @package AI_Awareness_Day
 */
(function() {
    'use strict';
    
    function handleResourceShare(btn) {
        var url = btn.getAttribute('data-url') || '';
        var title = btn.getAttribute('data-title') || '';
        var text = btn.getAttribute('data-text') || title; // Use pre-written message or fallback to title
        
        // Replace {URL} placeholder with actual URL if present
        if (text.indexOf('{URL}') !== -1) {
            text = text.replace('{URL}', url);
        }
        
        if (navigator.share && typeof navigator.share === 'function') {
            navigator.share({
                title: title,
                text: text, // Pre-filled message for WhatsApp, etc.
                url: url
            }).then(function() {
                // Success feedback
                var originalLabel = btn.getAttribute('aria-label');
                btn.setAttribute('aria-label', 'Shared!');
                setTimeout(function() {
                    btn.setAttribute('aria-label', originalLabel || 'Share this resource');
                }, 2000);
            }).catch(function() {
                // User cancelled or error - silently fail
            });
        } else {
            // Fallback: copy URL to clipboard
            copyToClipboard(url).then(function(ok) {
                var originalLabel = btn.getAttribute('aria-label');
                btn.setAttribute('aria-label', ok ? 'Link copied!' : 'Copy failed');
                setTimeout(function() {
                    btn.setAttribute('aria-label', originalLabel || 'Share this resource');
                }, 2000);
            });
        }
    }
    
    function copyToClipboard(text) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            return navigator.clipboard.writeText(text).then(
                function() { return true; },
                function() { return false; }
            );
        }
        // Fallback for older browsers
        var ta = document.createElement('textarea');
        ta.value = text;
        ta.setAttribute('readonly', '');
        ta.style.position = 'absolute';
        ta.style.left = '-9999px';
        document.body.appendChild(ta);
        ta.select();
        try {
            var success = document.execCommand('copy');
            document.body.removeChild(ta);
            return Promise.resolve(success);
        } catch (err) {
            document.body.removeChild(ta);
            return Promise.resolve(false);
        }
    }
    
    function handleResourcePrint(btn) {
        // Trigger browser print dialog
        window.print();
    }

    function handleSocialCard(btn) {
        var title = btn.getAttribute('data-title') || 'AI Awareness Day';
        var url = btn.getAttribute('data-url') || window.location.href;
        var theme = btn.getAttribute('data-theme') || 'AI';
        var keyStages = btn.getAttribute('data-key-stages') || '';

        var canvas = document.createElement('canvas');
        canvas.width = 1200;
        canvas.height = 630;
        var ctx = canvas.getContext('2d');
        if (!ctx) return;

        var gradient = ctx.createLinearGradient(0, 0, 1200, 630);
        gradient.addColorStop(0, '#0f172a');
        gradient.addColorStop(1, '#166534');
        ctx.fillStyle = gradient;
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        ctx.fillStyle = '#dcfce7';
        ctx.font = '700 32px sans-serif';
        ctx.fillText('AI Awareness Day Resource', 70, 100);

        ctx.fillStyle = '#ffffff';
        ctx.font = '700 58px sans-serif';
        var words = title.split(/\s+/);
        var line = '';
        var y = 190;
        words.forEach(function (w) {
            var test = line ? (line + ' ' + w) : w;
            if (ctx.measureText(test).width > 1060) {
                ctx.fillText(line, 70, y);
                line = w;
                y += 72;
            } else {
                line = test;
            }
        });
        if (line) ctx.fillText(line, 70, y);

        ctx.fillStyle = '#bbf7d0';
        ctx.font = '600 30px sans-serif';
        if (theme) {
            ctx.fillText('Theme: ' + theme, 70, 460);
        }
        if (keyStages) {
            ctx.fillText('Key stages: ' + keyStages, 70, 510);
        }

        ctx.fillStyle = '#e5e7eb';
        ctx.font = '500 20px sans-serif';
        ctx.fillText(url, 70, 575);

        canvas.toBlob(function (blob) {
            if (!blob) return;
            var link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'aiad-resource-card.png';
            link.click();
            setTimeout(function () {
                URL.revokeObjectURL(link.href);
            }, 1500);
        }, 'image/png');
    }
    
    function initResourceSharing() {
        // Attach click handlers to all share buttons
        var shareButtons = document.querySelectorAll('.resource-share-btn');
        if (shareButtons.length === 0) {
            // No buttons found yet, try again after a short delay
            setTimeout(initResourceSharing, 100);
            return;
        }
        
        shareButtons.forEach(function(btn) {
            // Remove any existing listeners to avoid duplicates
            var newBtn = btn.cloneNode(true);
            btn.parentNode.replaceChild(newBtn, btn);
            
            newBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                handleResourceShare(newBtn);
                return false;
            });
        });
        
        // Attach click handlers to all print buttons
        var printButtons = document.querySelectorAll('.resource-print-btn');
        printButtons.forEach(function(btn) {
            // Remove any existing listeners to avoid duplicates
            var newBtn = btn.cloneNode(true);
            btn.parentNode.replaceChild(newBtn, btn);
            
            newBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                handleResourcePrint(newBtn);
                return false;
            });
        });

        var socialCardButtons = document.querySelectorAll('.resource-social-card-btn');
        socialCardButtons.forEach(function (btn) {
            var newBtn = btn.cloneNode(true);
            btn.parentNode.replaceChild(newBtn, btn);
            newBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                handleSocialCard(newBtn);
                return false;
            });
        });
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(initResourceSharing, 50);
        });
    } else {
        // DOM already ready, but wait a bit for other scripts
        setTimeout(initResourceSharing, 50);
    }
    
    // Also try on window load as fallback
    window.addEventListener('load', function() {
        setTimeout(initResourceSharing, 100);
    });
})();
