/**
 * Herbalife Popup JavaScript
 * Version: 2.0.0
 */

(function() {
    'use strict';
    
    // Configuration from localized data
    const config = window.HerbalifePopup || {};
    const settings = config.settings || {};
    
    // State management
    const state = {
        isShown: false,
        viewCount: 0,
        isClosed: false
    };
    
    // Storage utilities
    const storage = {
        // Cookie utilities
        cookies: {
            set(name, value, days) {
                const date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                const expires = `expires=${date.toUTCString()}`;
                document.cookie = `${name}=${value};${expires};path=/;SameSite=Lax`;
            },
            
            get(name) {
                const nameEQ = `${name}=`;
                const ca = document.cookie.split(';');
                
                for (let i = 0; i < ca.length; i++) {
                    let c = ca[i].trim();
                    if (c.indexOf(nameEQ) === 0) {
                        return c.substring(nameEQ.length);
                    }
                }
                
                return null;
            }
        },
        
        // Session storage utilities
        session: {
            set(name, value) {
                try {
                    sessionStorage.setItem(name, value);
                } catch (e) {
                    // Fallback to memory if sessionStorage is not available
                    window[name] = value;
                }
            },
            
            get(name) {
                try {
                    return sessionStorage.getItem(name);
                } catch (e) {
                    // Fallback to memory if sessionStorage is not available
                    return window[name] || null;
                }
            }
        },
        
        // Get storage method based on settings
        set(name, value, days = null) {
            if (settings.sessionBased) {
                this.session.set(name, value);
            } else {
                this.cookies.set(name, value, days || settings.cookieDays || 30);
            }
        },
        
        get(name) {
            if (settings.sessionBased) {
                return this.session.get(name);
            } else {
                return this.cookies.get(name);
            }
        }
    };
    
    // Main popup controller
    const HerbalifePopupController = {
        init() {
            this.wrapper = document.getElementById('herbalife-popup-wrapper');
            if (!this.wrapper) return;
            
            this.loadState();
            
            if (this.shouldShow()) {
                this.setupTrigger();
                this.bindEvents();
            }
        },
        
        loadState() {
            if (settings.sessionBased) {
                // For session-based, check session storage
                state.isClosed = storage.get(settings.cookieName) === 'true';
                state.viewCount = parseInt(storage.get(settings.viewsCookieName) || '0', 10);
            } else {
                // For cookie-based, check cookies
                state.isClosed = storage.cookies.get(settings.cookieName) === 'true';
                state.viewCount = parseInt(storage.cookies.get(settings.viewsCookieName) || '0', 10);
            }
        },
        
        shouldShow() {
            return !state.isClosed && state.viewCount < settings.maxViews;
        },
        
        setupTrigger() {
            switch (settings.trigger) {
                case 'delay':
                    this.setupDelayTrigger();
                    break;
                case 'scroll':
                    this.setupScrollTrigger();
                    break;
                case 'exit':
                    this.setupExitTrigger();
                    break;
                default:
                    this.show();
            }
        },
        
        setupDelayTrigger() {
            setTimeout(() => this.show(), settings.delay || 0);
        },
        
        setupScrollTrigger() {
            let triggered = false;
            
            const checkScroll = () => {
                if (!triggered && window.scrollY >= (settings.scrollDistance || 150)) {
                    triggered = true;
                    this.show();
                    window.removeEventListener('scroll', checkScroll);
                }
            };
            
            window.addEventListener('scroll', checkScroll, { passive: true });
        },
        
        setupExitTrigger() {
            let triggered = false;
            
            document.addEventListener('mouseout', (e) => {
                if (!triggered && e.clientY <= 0 && e.relatedTarget === null) {
                    triggered = true;
                    this.show();
                }
            });
        },
        
        show() {
            if (state.isShown || !this.wrapper) return;
            
            state.isShown = true;
            state.viewCount++;
            
            this.wrapper.classList.add('active');
            this.wrapper.setAttribute('aria-hidden', 'false');
            
            // Update view count
            storage.set(settings.viewsCookieName, state.viewCount);
            
            // Track popup view
            this.track('view');
            
            // Focus management
            this.trapFocus();
        },
        
        hide() {
            if (!this.wrapper) return;
            
            this.wrapper.classList.remove('active');
            this.wrapper.setAttribute('aria-hidden', 'true');
            
            state.isClosed = true;
            
            // Store closed state
            storage.set(settings.cookieName, 'true', settings.cookieDays);
            
            // Track popup close
            this.track('close');
            
            // Restore focus
            this.releaseFocus();
        },
        
        bindEvents() {
            // Close button
            const closeBtn = this.wrapper.querySelector('.herbalife-popup-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', () => this.hide());
            }
            
            // Overlay click
            const overlay = this.wrapper.querySelector('.herbalife-popup-overlay');
            if (overlay) {
                overlay.addEventListener('click', () => this.hide());
            }
            
            // Escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && state.isShown) {
                    this.hide();
                }
            });
        },
        
        trapFocus() {
            const focusableElements = this.wrapper.querySelectorAll(
                'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
            );
            
            if (focusableElements.length === 0) return;
            
            const firstElement = focusableElements[0];
            const lastElement = focusableElements[focusableElements.length - 1];
            
            firstElement.focus();
            
            this.wrapper.addEventListener('keydown', (e) => {
                if (e.key !== 'Tab') return;
                
                if (e.shiftKey) {
                    if (document.activeElement === firstElement) {
                        e.preventDefault();
                        lastElement.focus();
                    }
                } else {
                    if (document.activeElement === lastElement) {
                        e.preventDefault();
                        firstElement.focus();
                    }
                }
            });
        },
        
        releaseFocus() {
            // Return focus to trigger element if available
            if (this.triggerElement) {
                this.triggerElement.focus();
            }
        },
        
        track(action) {
            if (!config.ajaxUrl || !config.nonce) return;
            
            const data = new FormData();
            data.append('action', 'herbalife_popup_track');
            data.append('nonce', config.nonce);
            data.append('popup_action', action);
            
            fetch(config.ajaxUrl, {
                method: 'POST',
                body: data,
                credentials: 'same-origin'
            }).catch(error => {
                console.error('Tracking error:', error);
            });
        }
    };
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            HerbalifePopupController.init();
        });
    } else {
        HerbalifePopupController.init();
    }
    
    // Expose controller for external use
    window.HerbalifePopupController = HerbalifePopupController;
})();