$(document).ready(function () {
  (function () {
    const $modal = $('.modal--battle-pass');
    const trackEl = document.querySelector('.modal--battle-pass .js-battle-pass-track');
    if (!$modal.length || !trackEl) return;

    const getTotalLevels = function () {
      const slideLevels = Array.prototype.slice.call(document.querySelectorAll('.modal--battle-pass .battle-pass-card[data-level]'))
        .map(function (el) {
          return parseInt(el.getAttribute('data-level'), 10);
        })
        .filter(function (level) {
          return !isNaN(level);
        });

      if (slideLevels.length) return Math.max.apply(null, slideLevels);

      return document.querySelectorAll('.modal--battle-pass .battle-pass__progress-item[data-level]').length;
    };

    const clamp = function (value, min, max) {
      return Math.min(max, Math.max(min, value));
    };

    const isMobileBp = function () {
      return window.matchMedia('(max-width: 767px)').matches;
    };

    /**
     * data-current-level: 0-based індекс слайда / кроку (0 = перший рівень).
     * Зірки data-level: 1-based → activeLevel = currentLevel + 1.
     */
    const getLevelState = function (modalEl) {
      const totalLevels = getTotalLevels();
      if (!totalLevels) {
        return { currentLevel: 0, activeLevel: 1, slideIndex: 0 };
      }

      const parsed = parseInt(modalEl.getAttribute('data-current-level'), 10);
      const currentLevel = clamp(Number.isNaN(parsed) ? 0 : parsed, 0, totalLevels - 1);
      const activeLevel = currentLevel + 1;
      const visibleSlides = isMobileBp() ? 1 : 3;
      const totalSlides = window.battlePassSwiper ? window.battlePassSwiper.slides.length : totalLevels;
      const maxSlide = Math.max(0, totalSlides - visibleSlides);
      const baseSlideIndex = isMobileBp() ? currentLevel : currentLevel - 1;
      const slideIndex = clamp(baseSlideIndex, 0, maxSlide);

      return { currentLevel, activeLevel, slideIndex };
    };

    const getInitialSlideIndex = function () {
      const modalEl = document.querySelector('.modal--battle-pass');
      if (!modalEl) return 0;
      return getLevelState(modalEl).slideIndex;
    };

    window.updateBattlePassUI = function () {
      const modalEl = document.querySelector('.modal--battle-pass');
      if (!modalEl) return;

      const totalLevels = getTotalLevels();
      if (!totalLevels) return;

      const { currentLevel, activeLevel } = getLevelState(modalEl);
      const isMobile = isMobileBp();
      let progress = 51;
      const progressFill = modalEl.querySelector('.battle-pass__progress-fill');
      const progressItems = modalEl.querySelectorAll('.battle-pass__progress-item[data-level]');
      const visibleStars = isMobile ? 1 : 3;

      let windowStart;
      let windowEnd;
      if (visibleStars === 1) {
        windowStart = activeLevel;
        windowEnd = activeLevel;
      } else {
        windowStart = clamp(activeLevel - 1, 1, Math.max(1, totalLevels - visibleStars + 1));
        windowEnd = windowStart + visibleStars - 1;
      }

      if (activeLevel === totalLevels) {
        progress = isMobile ? 98 : 99;
      } else if (!isMobile && currentLevel === 0) {
        progress = 11;
      }

      if (progressFill) {
        progressFill.style.setProperty('--progress', progress + '%');
      }

      Array.prototype.forEach.call(progressItems, function (item) {
        const level = parseInt(item.getAttribute('data-level'), 10);
        const isInVisibleWindow = level >= windowStart && level <= windowEnd;
        const isActive = level === activeLevel;

        item.classList.toggle('is-hidden', !isInVisibleWindow);
        item.classList.toggle('is-active', isActive);
      });
    };

    window.battlePassSwiper = new Swiper('.modal--battle-pass .js-battle-pass-track', {
      slidesPerView: 'auto',
      spaceBetween: 0,
      centeredSlides: false,
      initialSlide: getInitialSlideIndex(),
      direction: 'horizontal',
      navigation: {
        nextEl: '.modal--battle-pass .js-battle-pass-next',
        prevEl: '.modal--battle-pass .js-battle-pass-prev'
      },
      breakpoints: {
        0: {
          centeredSlides: true
        },
        768: {
          centeredSlides: false
        }
      }
    });

    const syncBattlePassToLevel = function () {
      if (!window.battlePassSwiper) return;
      const modalEl = document.querySelector('.modal--battle-pass');
      if (!modalEl) return;

      const { slideIndex } = getLevelState(modalEl);
      const swiper = window.battlePassSwiper;

      const applySlide = function () {
        swiper.slideTo(slideIndex, 0, false);
        window.updateBattlePassUI();
        swiper.update();
      };

      applySlide();

      if (isMobileBp()) {
        requestAnimationFrame(function () {
          swiper.updateSize();
          swiper.updateSlides();
          swiper.slideTo(slideIndex, 0, false);
          window.updateBattlePassUI();
          requestAnimationFrame(function () {
            swiper.update();
          });
        });
      }
    };

    $(document).on('beforeModalOpen', function (event, id) {
      if (id === 'modal--battle-pass') {
        syncBattlePassToLevel();
      }
    });

    const modalNode = document.querySelector('.modal--battle-pass');
    if (modalNode) {
      const observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
          if (mutation.type === 'attributes' && mutation.attributeName === 'data-current-level') {
            syncBattlePassToLevel();
          }
        });
      });
      observer.observe(modalNode, {attributes: true});
    }

    const refreshSwiperForViewport = function () {
      if (!window.battlePassSwiper) return;
      const modalEl = document.querySelector('.modal--battle-pass');
      if (!modalEl) return;
      const { slideIndex } = getLevelState(modalEl);
      window.battlePassSwiper.updateSize();
      window.battlePassSwiper.updateSlides();
      window.battlePassSwiper.slideTo(slideIndex, 0, false);
      window.battlePassSwiper.update();
      if (isMobileBp()) {
        requestAnimationFrame(function () {
          window.battlePassSwiper.updateSize();
          window.battlePassSwiper.slideTo(slideIndex, 0, false);
          window.battlePassSwiper.update();
        });
      }
    };

    const refreshBattlePassAfterViewportChange = function () {
      window.updateBattlePassUI();
      refreshSwiperForViewport();
    };

    let battlePassViewportTimer;
    const scheduleBattlePassViewportRefresh = function () {
      clearTimeout(battlePassViewportTimer);
      battlePassViewportTimer = setTimeout(refreshBattlePassAfterViewportChange, 120);
    };

    window.addEventListener('resize', scheduleBattlePassViewportRefresh);
    window.addEventListener('orientationchange', function () {
      scheduleBattlePassViewportRefresh();
      setTimeout(scheduleBattlePassViewportRefresh, 350);
    });

    const mqBattlePassMobile = window.matchMedia('(max-width: 767px)');
    const onMqBattlePassMobileChange = function () {
      refreshBattlePassAfterViewportChange();
    };
    if (typeof mqBattlePassMobile.addEventListener === 'function') {
      mqBattlePassMobile.addEventListener('change', onMqBattlePassMobileChange);
    } else if (typeof mqBattlePassMobile.addListener === 'function') {
      mqBattlePassMobile.addListener(onMqBattlePassMobileChange);
    }

    window.updateBattlePassUI();
  })();

  const initBattlePassFixedTooltip = function (config) {
    const modal = document.querySelector('.modal--battle-pass');
    if (!modal) return;

    const tip = modal.querySelector(config.tipSelector);
    const triggers = modal.querySelectorAll(config.triggerSelector);
    if (!tip || !triggers.length) return;

    const tipHome = { parent: tip.parentNode, next: tip.nextSibling };
    const GAP = 11;
    const VIEW_MARGIN = 12;
    const isDesktopHover = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
    let lastAnchorEl = null;

    const mountTipToBody = function () {
      if (tip.parentNode !== document.body) {
        document.body.appendChild(tip);
      }
    };

    const restoreTipHome = function () {
      if (!tipHome.parent || tip.parentNode !== document.body) return;
      tipHome.parent.insertBefore(tip, tipHome.next);
    };

    const clampNum = function (v, min, max) {
      return Math.min(max, Math.max(min, v));
    };

    const place = function (anchor) {
      lastAnchorEl = anchor;
      mountTipToBody();
      const tr = anchor.getBoundingClientRect();
      tip.classList.add('is-visible');
      tip.style.position = 'fixed';

      const tw = tip.offsetWidth;
      const th = tip.offsetHeight;
      let left = tr.left + tr.width / 2 - tw / 2;
      left = clampNum(left, VIEW_MARGIN, window.innerWidth - tw - VIEW_MARGIN);

      const roomBelow = window.innerHeight - VIEW_MARGIN - (tr.bottom + GAP + th);
      const roomAbove = tr.top - GAP - th - VIEW_MARGIN;
      const preferBottom = config.preferredPlacement === 'bottom';
      let top;

      if (preferBottom) {
        top = tr.bottom + GAP;
        tip.classList.add(config.belowClass);
        if (roomBelow < 0 && roomAbove >= 0) {
          top = tr.top - th - GAP;
          tip.classList.remove(config.belowClass);
        }
      } else {
        top = tr.top - th - GAP;
        tip.classList.remove(config.belowClass);
        if (roomAbove < 0 && roomBelow >= 0) {
          top = tr.bottom + GAP;
          tip.classList.add(config.belowClass);
        }
      }
      if (top < VIEW_MARGIN) top = VIEW_MARGIN;

      tip.style.left = Math.round(left) + 'px';
      tip.style.top = Math.round(top) + 'px';
    };

    const hide = function () {
      if (!tip.classList.contains('is-visible') && !lastAnchorEl) return;
      lastAnchorEl = null;
      tip.classList.remove(config.belowClass);
      tip.classList.remove('is-visible');
      tip.style.left = '';
      tip.style.top = '';
      tip.style.position = '';
      restoreTipHome();
    };

    Array.prototype.forEach.call(triggers, function (anchor) {
      if (isDesktopHover) {
        anchor.addEventListener('mouseenter', function () {
          place(anchor);
        });
        anchor.addEventListener('mouseleave', function () {
          hide();
        });
      } else {
        anchor.addEventListener('click', function (e) {
          if (typeof config.ignoreMobileClick === 'function' && config.ignoreMobileClick(anchor, e)) return;
          e.preventDefault();
          e.stopPropagation();
          if (tip.classList.contains('is-visible') && lastAnchorEl === anchor) {
            hide();
          } else {
            place(anchor);
          }
        });
      }
    });

    const onScrollOrResize = function () {
      if (tip.classList.contains('is-visible') && lastAnchorEl) {
        place(lastAnchorEl);
      }
    };
    window.addEventListener('scroll', onScrollOrResize, true);
    window.addEventListener('resize', onScrollOrResize);

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && tip.classList.contains('is-visible')) hide();
    });

    document.addEventListener('click', function (e) {
      if (!tip.classList.contains('is-visible')) return;
      const insideTrigger = Array.prototype.some.call(triggers, function (el) {
        return el.contains(e.target);
      });
      if (insideTrigger || tip.contains(e.target)) return;
      hide();
    }, true);

    document.addEventListener('click', function (e) {
      if (!tip.classList.contains('is-visible')) return;
      if (e.target.closest('.modal__overlay, .modal__hide, .modal__close, .js-modal-close')) {
        hide();
      }
    }, true);
  };

  initBattlePassFixedTooltip({
    triggerSelector: '.battle-pass__currency-info',
    tipSelector: '.battle-pass__currency-tooltip',
    belowClass: 'battle-pass__currency-tooltip--below',
    preferredPlacement: 'top'
  });

  (function initBattlePassGiftTimerText() {
    const giftNodes = document.querySelectorAll('.modal--battle-pass .battle-pass__head-gift[data-gift-state="timer"]');
    if (!giftNodes.length) return;

    const pad2 = function (value) {
      return String(value).padStart(2, '0');
    };

    const formatTimeLeft = function (secondsLeft) {
      const safeSeconds = Math.max(0, secondsLeft);
      const days = Math.floor(safeSeconds / 86400);
      const hours = Math.floor((safeSeconds % 86400) / 3600);
      const minutes = Math.floor((safeSeconds % 3600) / 60);
      const seconds = safeSeconds % 60;

      if (days > 0) return days + ' д. ' + pad2(hours) + ' год.';
      if (hours > 0) return pad2(hours) + ' год. ' + pad2(minutes) + ' хв.';
      return pad2(minutes) + ' хв. ' + pad2(seconds) + ' сек.';
    };

    const updateGiftTimerText = function (giftNode) {
      const timerValueNode = giftNode.querySelector('.battle-pass__head-gift-timer-value');
      if (!timerValueNode) return;

      const expiresAtRaw = giftNode.getAttribute('data-gift-expires-at');
      const expiresAt = parseInt(expiresAtRaw, 10);
      if (Number.isNaN(expiresAt)) return;

      const now = Math.floor(Date.now() / 1000);
      const secondsLeft = Math.max(0, expiresAt - now);
      timerValueNode.textContent = formatTimeLeft(secondsLeft);
    };

    const updateAllGiftTimerTexts = function () {
      Array.prototype.forEach.call(giftNodes, updateGiftTimerText);
    };

    updateAllGiftTimerTexts();
    setInterval(updateAllGiftTimerTexts, 1000);
  })();

  initBattlePassFixedTooltip({
    triggerSelector: '.js-battle-pass-gift-tip-trigger',
    tipSelector: '.battle-pass__head-gift-tooltip',
    belowClass: 'battle-pass__head-gift-tooltip--below',
    preferredPlacement: 'bottom',
    ignoreMobileClick: function (anchor) {
      return anchor.classList.contains('js-open-daily-reward');
    }
  });

  (function initBattlePassDailyRewardPopup() {
    const modal = document.querySelector('.modal--battle-pass');
    if (!modal) return;

    const popup = modal.querySelector('.battle-pass__daily-reward');
    const dialogTitle = modal.querySelector('.battle-pass__daily-reward-title');
    const dialogImage = modal.querySelector('.js-daily-reward-image');
    const dialogAmount = modal.querySelector('.js-daily-reward-amount');
    const dialogUnit = modal.querySelector('.js-daily-reward-unit');
    const dialogLabel = modal.querySelector('.js-daily-reward-label');
    const dialogButtonText = modal.querySelector('.js-daily-reward-button-text');
    const closeTriggers = modal.querySelectorAll('.js-battle-pass-daily-reward-close');
    if (!popup || !closeTriggers.length) return;

    const fillPopupFromTrigger = function (trigger) {
      if (!trigger) return;
      const title = trigger.getAttribute('data-daily-reward-title');
      const image = trigger.getAttribute('data-daily-reward-image');
      const imageAlt = trigger.getAttribute('data-daily-reward-image-alt');
      const amount = trigger.getAttribute('data-daily-reward-amount');
      const unit = trigger.getAttribute('data-daily-reward-unit');
      const label = trigger.getAttribute('data-daily-reward-label');
      const buttonText = trigger.getAttribute('data-daily-reward-button-text');

      if (dialogTitle && title) dialogTitle.textContent = title;
      if (dialogImage && image) dialogImage.setAttribute('src', image);
      if (dialogImage && imageAlt) dialogImage.setAttribute('alt', imageAlt);
      if (dialogAmount && amount) dialogAmount.textContent = amount;
      if (dialogUnit && unit) dialogUnit.textContent = unit;
      if (dialogLabel && label) dialogLabel.textContent = label;
      if (dialogButtonText && buttonText) dialogButtonText.textContent = buttonText;
    };

    const openPopup = function () {
      popup.classList.add('is-open');
      popup.setAttribute('aria-hidden', 'false');
      document.body.classList.add('is-open-modal');
    };

    const closePopup = function () {
      popup.classList.remove('is-open');
      popup.setAttribute('aria-hidden', 'true');
      if (typeof window.syncBodyModalLock === 'function') {
        window.syncBodyModalLock();
      } else if (!$('.modal').filter(function () { return $(this).css('display') !== 'none'; }).length) {
        document.body.classList.remove('is-open-modal');
      }
    };

    modal.addEventListener('click', function (e) {
      const openTrigger = e.target.closest('.js-open-daily-reward');
      if (!openTrigger || !modal.contains(openTrigger)) return;
      fillPopupFromTrigger(openTrigger);
      openPopup();
    });

    Array.prototype.forEach.call(closeTriggers, function (trigger) {
      trigger.addEventListener('click', function () {
        closePopup();
      });
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && popup.classList.contains('is-open')) {
        closePopup();
      }
    });
  })();

  (function initBattlePassAutoShow() {
    var modal = document.querySelector('.modal--battle-pass');
    if (!modal || modal.getAttribute('data-bp-auto-show') !== '1') {
      return;
    }

    var started = false;
    var opened = false;
    var retryTimer = null;

    function isOtherModalOpen() {
      // Age gate and any other site modal (login, cart, wheel, etc.)
      if ($('body').attr('data-age-gate') === '1') {
        return true;
      }

      if ($('.battle-pass__daily-reward.is-open').length) {
        return true;
      }

      var open = false;

      $('.modal').each(function () {
        var $modal = $(this);

        if ($modal.is(modal) || $modal.hasClass('modal--battle-pass')) {
          return;
        }

        // Confirmed age gate modal must not block
        if ($modal.hasClass('modal-age') && $('body').attr('data-age-gate') !== '1') {
          return;
        }

        if ($modal.css('display') !== 'none') {
          open = true;
          return false;
        }
      });

      return open;
    }

    function tryOpen() {
      if (opened) {
        return;
      }

      if (isOtherModalOpen()) {
        clearTimeout(retryTimer);
        retryTimer = setTimeout(tryOpen, 600);
        return;
      }

      opened = true;
      clearTimeout(retryTimer);

      if (typeof window.openSiteModal === 'function') {
        if (!window.openSiteModal($('.modal--battle-pass'))) {
          opened = false;
          retryTimer = setTimeout(tryOpen, 600);
        }ы
      } else {
        $('.modal--battle-pass').fadeIn(400);
        $('body').addClass('is-open-modal');
      }
    }

    function openAfterInteraction() {
      if (started) {
        return;
      }

      started = true;
      setTimeout(tryOpen, 2500);
    }

    ['scroll', 'touchstart', 'click', 'keydown'].forEach(function (evt) {
      window.addEventListener(evt, openAfterInteraction, { once: true, passive: true });
    });
  })();
});
